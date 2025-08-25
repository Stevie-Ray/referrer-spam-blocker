<?php

declare(strict_types=1);

namespace StevieRay\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use StevieRay\Service\FileWriter;
use RuntimeException;

class FileWriterTest extends TestCase
{
    private string $tempDir;

    private FileWriter $fileWriter;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/filewriter-test-' . uniqid();
        mkdir($this->tempDir, 0755, true);
        $this->fileWriter = new FileWriter($this->tempDir);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    public function testWriteFile(): void
    {
        $filename = 'test.txt';
        $content = 'Test content';

        $this->fileWriter->writeFile($filename, $content);

        $filePath = $this->tempDir . '/' . $filename;
        $this->assertFileExists($filePath);
        $this->assertEquals($content, file_get_contents($filePath));
        $this->assertEquals('0644', substr(sprintf('%o', fileperms($filePath)), -4));
    }

    public function testWriteFileWithSubdirectory(): void
    {
        $filename = 'subdir/test.txt';
        $content = 'Test content';

        $this->fileWriter->writeFile($filename, $content);

        $filePath = $this->tempDir . '/' . $filename;
        $this->assertFileExists($filePath);
        $this->assertEquals($content, file_get_contents($filePath));
    }

    public function testWriteFiles(): void
    {
        $files = [
            'file1.txt' => 'Content 1',
            'file2.txt' => 'Content 2',
            'subdir/file3.txt' => 'Content 3',
        ];

        $this->fileWriter->writeFiles($files);

        foreach ($files as $filename => $content) {
            $filePath = $this->tempDir . '/' . $filename;
            $this->assertFileExists($filePath);
            $this->assertEquals($content, file_get_contents($filePath));
        }
    }

    public function testWriteFileWithUnwritableDirectory(): void
    {
        // Create a read-only directory
        $readOnlyDir = $this->tempDir . '/readonly';
        mkdir($readOnlyDir, 0444);

        $fileWriter = new FileWriter($readOnlyDir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Directory is not writable');

        $fileWriter->writeFile('test.txt', 'content');
    }

    public function testWriteFileWithUnwritableFile(): void
    {
        $filename = 'test.txt';
        $filePath = $this->tempDir . '/' . $filename;

        // Create a read-only file
        file_put_contents($filePath, 'existing content');
        chmod($filePath, 0444);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Permission denied: cannot write to');

        $this->fileWriter->writeFile($filename, 'new content');
    }

    public function testGetOutputDirectory(): void
    {
        $this->assertEquals($this->tempDir, $this->fileWriter->getOutputDirectory());
    }

    public function testFileExists(): void
    {
        $filename = 'test.txt';

        $this->assertFalse($this->fileWriter->fileExists($filename));

        $this->fileWriter->writeFile($filename, 'content');

        $this->assertTrue($this->fileWriter->fileExists($filename));
    }

    public function testCustomFilePermissions(): void
    {
        $fileWriter = new FileWriter($this->tempDir, 0600);
        $filename = 'test.txt';
        $content = 'Test content';

        $fileWriter->writeFile($filename, $content);

        $filePath = $this->tempDir . '/' . $filename;
        $this->assertEquals('0600', substr(sprintf('%o', fileperms($filePath)), -4));
    }

    public function testWriteFileWithTrailingSlash(): void
    {
        $fileWriter = new FileWriter($this->tempDir . '/');
        $filename = 'test.txt';
        $content = 'Test content';

        $fileWriter->writeFile($filename, $content);

        $filePath = $this->tempDir . '/' . $filename;
        $this->assertFileExists($filePath);
        $this->assertEquals($content, file_get_contents($filePath));
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
