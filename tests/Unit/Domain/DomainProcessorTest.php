<?php

declare(strict_types=1);

namespace StevieRay\Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use StevieRay\Domain\DomainProcessor;
use RuntimeException;

class DomainProcessorTest extends TestCase
{
    private DomainProcessor $processor;

    private string $tempDomainsFile;

    protected function setUp(): void
    {
        $this->tempDomainsFile = sys_get_temp_dir() . '/test-domains.txt';
        $this->processor = new DomainProcessor($this->tempDomainsFile);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempDomainsFile)) {
            unlink($this->tempDomainsFile);
        }
    }

    public function testProcessDomainsWithValidDomains(): void
    {
        $domains = [
            'example.com',
            'test.org',
            'domain.net',
        ];

        $this->createTempDomainsFile($domains);

        $result = $this->processor->processDomains();

        $this->assertCount(3, $result);
        $this->assertEquals(['domain.net', 'example.com', 'test.org'], $result);
    }

    public function testProcessDomainsWithDuplicates(): void
    {
        $domains = [
            'example.com',
            'test.org',
            'example.com', // Duplicate
            'domain.net',
            'test.org',    // Duplicate
        ];

        $this->createTempDomainsFile($domains);

        $result = $this->processor->processDomains();

        $this->assertCount(3, $result);
        $this->assertEquals(['domain.net', 'example.com', 'test.org'], $result);
    }

    public function testProcessDomainsWithEmptyLines(): void
    {
        $domains = [
            'example.com',
            '',
            'test.org',
            '   ',
            'domain.net',
        ];

        $this->createTempDomainsFile($domains);

        $result = $this->processor->processDomains();

        $this->assertCount(3, $result);
        $this->assertEquals(['domain.net', 'example.com', 'test.org'], $result);
    }

    public function testProcessDomainsWithInvalidDomains(): void
    {
        $domains = [
            'example.com',
            'invalid-domain-', // Invalid
            'test.org',
            'domain.net',
        ];

        $this->createTempDomainsFile($domains);

        $result = $this->processor->processDomains();

        $this->assertCount(3, $result);
        $this->assertEquals(['domain.net', 'example.com', 'test.org'], $result);
    }

    public function testProcessDomainsWithInternationalizedDomains(): void
    {
        $domains = [
            'example.com',
            'xn--e1aybc.xn--p1ai', // Valid punycode for тест.рф
            'test.org',
        ];

        $this->createTempDomainsFile($domains);

        $result = $this->processor->processDomains();

        $this->assertCount(3, $result);
        $this->assertContains('example.com', $result);
        $this->assertContains('xn--e1aybc.xn--p1ai', $result);
        $this->assertContains('test.org', $result);
    }

    public function testProcessDomainsWithNonExistentFile(): void
    {
        $processor = new DomainProcessor('/non/existent/file.txt');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error opening file /non/existent/file.txt');

        $processor->processDomains();
    }

    public function testProcessDomainsWithUnwritableFile(): void
    {
        $domains = ['example.com'];
        $this->createTempDomainsFile($domains);

        // Make file read-only
        chmod($this->tempDomainsFile, 0444);
        $processor = new DomainProcessor($this->tempDomainsFile);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Permission denied: cannot write to');

        $processor->processDomains();
    }

    /**
     * @param array<string> $domains
     */
    private function createTempDomainsFile(array $domains): void
    {
        $content = implode("\n", $domains) . "\n";
        file_put_contents($this->tempDomainsFile, $content);
    }
}
