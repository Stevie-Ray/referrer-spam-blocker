<?php

declare(strict_types=1);

namespace StevieRay\Tests\Unit;

use PHPUnit\Framework\TestCase;
use StevieRay\Generator;
use StevieRay\Config\ConfigGeneratorInterface;

class GeneratorTest extends TestCase
{
    private string $tempDir;

    private Generator $generator;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/generator-test-' . uniqid();
        mkdir($this->tempDir, 0755, true);
        $this->generator = new Generator($this->tempDir);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    public function testGetConfigGenerators(): void
    {
        $generators = $this->generator->getConfigGenerators();

        $this->assertCount(8, $generators); // 8 different config types

        foreach ($generators as $generator) {
            $this->assertInstanceOf(ConfigGeneratorInterface::class, $generator);
        }
    }

    public function testGetStatistics(): void
    {
        $stats = $this->generator->getStatistics();

        $this->assertArrayHasKey('total_domains', $stats);
        $this->assertArrayHasKey('config_files', $stats);
        $this->assertArrayHasKey('output_directory', $stats);
        $this->assertArrayHasKey('generated_files', $stats);

        $this->assertIsInt($stats['total_domains']);
        $this->assertIsInt($stats['config_files']);
        $this->assertIsString($stats['output_directory']);
        $this->assertIsArray($stats['generated_files']);

        $this->assertEquals(8, $stats['config_files']); // 8 different config types
        $this->assertEquals($this->tempDir, $stats['output_directory']);
    }

    public function testGenerateSpecificConfigs(): void
    {
        $types = ['apache', 'nginx'];

        $this->generator->generateSpecificConfigs($types);

        // Check that only Apache and Nginx files were created
        $this->assertFileExists($this->tempDir . '/.htaccess');
        $this->assertFileExists($this->tempDir . '/referral-spam.conf');

        // Check that other files were not created
        $this->assertFileDoesNotExist($this->tempDir . '/referral-spam.vcl');
        $this->assertFileDoesNotExist($this->tempDir . '/web.config');
    }

    public function testGenerateSpecificConfigsWithInvalidType(): void
    {
        $types = ['apache', 'invalid-type'];

        $this->generator->generateSpecificConfigs($types);

        // Should still generate Apache config
        $this->assertFileExists($this->tempDir . '/.htaccess');
    }

    public function testGenerateSpecificConfigsWithEmptyArray(): void
    {
        $types = [];

        $this->generator->generateSpecificConfigs($types);

        // Should not generate any files
        $files = scandir($this->tempDir);
        if ($files === false) {
            $files = [];
        }
        $files = array_filter($files, fn ($file) => $file !== '.' && $file !== '..');

        $this->assertEmpty($files);
    }

    public function testGenerateFiles(): void
    {
        $this->generator->generateFiles();

        // Check that all expected files were created
        $expectedFiles = [
            '.htaccess',
            'referral-spam.conf',
            'referral-spam.vcl',
            'web.config',
            'referral_spam.res',
            'referral-spam.caddy',
            'referral-spam.caddy2',
        ];

        foreach ($expectedFiles as $file) {
            $this->assertFileExists($this->tempDir . '/' . $file);
        }

        // Check that Google Analytics files were created (at least one)
        $googleFiles = glob($this->tempDir . '/google-exclude-*.txt');
        $this->assertNotEmpty($googleFiles);
    }

    public function testGeneratedFilesHaveContent(): void
    {
        $this->generator->generateFiles();

        $files = [
            '.htaccess',
            'referral-spam.conf',
            'referral-spam.vcl',
            'web.config',
            'referral_spam.res',
            'referral-spam.caddy',
            'referral-spam.caddy2',
        ];

        foreach ($files as $file) {
            $filePath = $this->tempDir . '/' . $file;
            $content = file_get_contents($filePath);

            $this->assertNotEmpty($content);
            if ($content !== false) {
                $this->assertStringContainsString('https://github.com/Stevie-Ray/referrer-spam-blocker', $content);
            }
        }
    }

    public function testGeneratedFilesHaveCorrectPermissions(): void
    {
        $this->generator->generateFiles();

        $files = [
            '.htaccess',
            'referral-spam.conf',
            'referral-spam.vcl',
            'web.config',
            'referral_spam.res',
            'referral-spam.caddy',
            'referral-spam.caddy2',
        ];

        foreach ($files as $file) {
            $filePath = $this->tempDir . '/' . $file;
            $permissions = fileperms($filePath);
            $permissions = substr(sprintf('%o', $permissions), -4);

            $this->assertEquals('0644', $permissions);
        }
    }

    public function testGoogleAnalyticsFilesAreSplitCorrectly(): void
    {
        $this->generator->generateFiles();

        $googleFiles = glob($this->tempDir . '/google-exclude-*.txt');
        $this->assertNotEmpty($googleFiles);

        if (is_array($googleFiles)) {
            foreach ($googleFiles as $file) {
                $content = file_get_contents($file);
                $this->assertNotEmpty($content);

                // Check that each file doesn't exceed Google Analytics limit
                if ($content !== false) {
                    $this->assertLessThanOrEqual(30000, strlen($content));
                }
            }
        }
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $scanResult = scandir($dir);
        if ($scanResult === false) {
            $files = [];
        } else {
            $files = array_diff($scanResult, ['.', '..']);
        }
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
