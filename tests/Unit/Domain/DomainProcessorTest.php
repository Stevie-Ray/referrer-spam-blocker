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

    private string $tempMatomoFile;

    protected function setUp(): void
    {
        $this->tempDomainsFile = sys_get_temp_dir() . '/test-domains.txt';
        $this->tempMatomoFile = sys_get_temp_dir() . '/test-matomo-domains.txt';
        $this->processor = new DomainProcessor($this->tempDomainsFile);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempDomainsFile)) {
            unlink($this->tempDomainsFile);
        }
        if (file_exists($this->tempMatomoFile)) {
            unlink($this->tempMatomoFile);
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

    public function testProcessDomainsMergesMatomoDomainsWithoutRemovingLocalDomains(): void
    {
        $this->createTempDomainsFile(['local.example', 'shared.example']);
        $matomoContent = "shared.example\r\nmatomo.example\r\n\r\ninvalid-domain-\r\n";
        file_put_contents($this->tempMatomoFile, $matomoContent);
        $processor = new DomainProcessor($this->tempDomainsFile, $this->tempMatomoFile);

        $result = $processor->processDomains();

        $this->assertSame(['local.example', 'matomo.example', 'shared.example'], $result);
        $this->assertSame(implode("\n", $result) . "\n", file_get_contents($this->tempDomainsFile));
        $this->assertSame($result, $processor->processDomains());
        $this->assertSame($matomoContent, file_get_contents($this->tempMatomoFile));
    }

    public function testMissingMatomoListDoesNotChangeLocalDomains(): void
    {
        $this->createTempDomainsFile(['local.example']);
        $processor = new DomainProcessor($this->tempDomainsFile, '/non/existent/matomo-spammers.txt');

        try {
            $processor->processDomains();
            $this->fail('Expected an exception for the missing Matomo list.');
        } catch (RuntimeException $e) {
            $this->assertSame('Error opening file /non/existent/matomo-spammers.txt', $e->getMessage());
            $this->assertSame("local.example\n", file_get_contents($this->tempDomainsFile));
        }
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
