<?php

declare(strict_types=1);

namespace StevieRay\Tests\Unit\Config;

use PHPUnit\Framework\TestCase;
use StevieRay\Config\ApacheConfigGenerator;

class ApacheConfigGeneratorTest extends TestCase
{
    private ApacheConfigGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new ApacheConfigGenerator();
    }

    public function testGetFilename(): void
    {
        $this->assertEquals('.htaccess', $this->generator->getFilename());
    }

    public function testGetDescription(): void
    {
        $this->assertEquals('Apache .htaccess configuration file', $this->generator->getDescription());
    }

    public function testGenerateWithValidDomains(): void
    {
        $domains = ['example.com', 'test.org'];
        $date = '2024-01-01 12:00:00';

        $result = $this->generator->generate($domains, $date);

        $this->assertStringContainsString('# https://github.com/Stevie-Ray/referrer-spam-blocker', $result);
        $this->assertStringContainsString('# Updated 2024-01-01 12:00:00', $result);
        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $result);
        $this->assertStringContainsString('RewriteEngine On', $result);
        $this->assertStringContainsString(
            'RewriteCond %{HTTP_REFERER} ^http(s)?://(www.)?.*example\.com.*$ [NC,OR]',
            $result
        );
        $this->assertStringContainsString(
            'RewriteCond %{HTTP_REFERER} ^http(s)?://(www.)?.*test\.org.*$ [NC]',
            $result
        );
        $this->assertStringContainsString('RewriteRule ^(.*)$ – [F,L]', $result);
        $this->assertStringContainsString('<IfModule mod_setenvif.c>', $result);
        $this->assertStringContainsString('SetEnvIfNoCase Referer example\\.com spambot=yes', $result);
        $this->assertStringContainsString('SetEnvIfNoCase Referer test\\.org spambot=yes', $result);
        $this->assertStringContainsString('# Apache 2.2', $result);
        $this->assertStringContainsString('# Apache 2.4', $result);
    }

    public function testGenerateWithEmptyDomains(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Domains array cannot be empty');

        $this->generator->generate([], '2024-01-01 12:00:00');
    }

    public function testGenerateWithSingleDomain(): void
    {
        $domains = ['example.com'];
        $date = '2024-01-01 12:00:00';

        $result = $this->generator->generate($domains, $date);

        $this->assertStringContainsString(
            'RewriteCond %{HTTP_REFERER} ^http(s)?://(www.)?.*example\.com.*$ [NC]',
            $result
        );
        $this->assertStringNotContainsString('[NC,OR]', $result);
    }

    public function testGenerateWithSpecialCharacters(): void
    {
        $domains = ['example.com', 'test-domain.org'];
        $date = '2024-01-01 12:00:00';

        $result = $this->generator->generate($domains, $date);

        $this->assertStringContainsString(
            'RewriteCond %{HTTP_REFERER} ^http(s)?://(www.)?.*test\-domain\.org.*$ [NC]',
            $result
        );
    }

    public function testGenerateModRewriteSection(): void
    {
        $domains = ['example.com', 'test.org', 'domain.net'];
        $date = '2024-01-01 12:00:00';

        $result = $this->generator->generate($domains, $date);

        // Check that all domains are included
        $this->assertStringContainsString('example\\.com', $result);
        $this->assertStringContainsString('test\\.org', $result);
        $this->assertStringContainsString('domain\\.net', $result);

        // Check that only the last domain doesn't have [NC,OR]
        $this->assertStringContainsString('example\\.com.*$ [NC,OR]', $result);
        $this->assertStringContainsString('test\\.org.*$ [NC,OR]', $result);
        $this->assertStringContainsString('domain\\.net.*$ [NC]', $result);
    }

    public function testGenerateModSetEnvIfSection(): void
    {
        $domains = ['example.com', 'test.org'];
        $date = '2024-01-01 12:00:00';

        $result = $this->generator->generate($domains, $date);

        $this->assertStringContainsString('SetEnvIfNoCase Referer example\\.com spambot=yes', $result);
        $this->assertStringContainsString('SetEnvIfNoCase Referer test\\.org spambot=yes', $result);
    }

    public function testGenerateAuthSection(): void
    {
        $domains = ['example.com'];
        $date = '2024-01-01 12:00:00';

        $result = $this->generator->generate($domains, $date);

        // Check Apache 2.2 section
        $this->assertStringContainsString('# Apache 2.2', $result);
        $this->assertStringContainsString('<IfModule !mod_authz_core.c>', $result);
        $this->assertStringContainsString('<IfModule mod_authz_host.c>', $result);
        $this->assertStringContainsString('Order allow,deny', $result);
        $this->assertStringContainsString('Allow from all', $result);
        $this->assertStringContainsString('Deny from env=spambot', $result);

        // Check Apache 2.4 section
        $this->assertStringContainsString('# Apache 2.4', $result);
        $this->assertStringContainsString('<IfModule mod_authz_core.c>', $result);
        $this->assertStringContainsString('<RequireAll>', $result);
        $this->assertStringContainsString('Require all granted', $result);
        $this->assertStringContainsString('Require not env spambot', $result);
    }
}
