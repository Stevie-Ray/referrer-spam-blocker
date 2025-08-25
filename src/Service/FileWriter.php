<?php

declare(strict_types=1);

namespace StevieRay\Service;

use RuntimeException;

class FileWriter
{
    private string $outputDirectory;

    private int $filePermissions;

    public function __construct(string $outputDirectory, int $filePermissions = 0644)
    {
        $this->outputDirectory = rtrim($outputDirectory, '/');
        $this->filePermissions = $filePermissions;
    }

    /**
     * Write content to a file
     *
     * @param string $filename
     * @param string $content
     * @return void
     * @throws RuntimeException
     */
    public function writeFile(string $filename, string $content): void
    {
        $filePath = $this->outputDirectory . '/' . $filename;

        // Ensure the directory exists
        $this->ensureDirectoryExists(dirname($filePath));

        // Check if file is writable (or doesn't exist yet)
        if (file_exists($filePath) && !is_writable($filePath)) {
            throw new RuntimeException("Permission denied: cannot write to $filePath");
        }

        // Write the content
        if (file_put_contents($filePath, $content) === false) {
            throw new RuntimeException("Failed to write content to $filePath");
        }

        // Set file permissions
        if (!chmod($filePath, $this->filePermissions)) {
            throw new RuntimeException("Failed to set permissions on $filePath");
        }
    }

    /**
     * Write multiple files at once
     *
     * @param array<string, string> $files
     * @return void
     * @throws RuntimeException
     */
    public function writeFiles(array $files): void
    {
        foreach ($files as $filename => $content) {
            $this->writeFile($filename, $content);
        }
    }

    /**
     * Ensure directory exists and is writable
     *
     * @param string $directory
     * @return void
     * @throws RuntimeException
     */
    private function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                throw new RuntimeException("Failed to create directory: $directory");
            }
        }

        if (!is_writable($directory)) {
            throw new RuntimeException("Directory is not writable: $directory");
        }
    }

    /**
     * Get the output directory
     *
     * @return string
     */
    public function getOutputDirectory(): string
    {
        return $this->outputDirectory;
    }

    /**
     * Check if a file exists
     *
     * @param string $filename
     * @return bool
     */
    public function fileExists(string $filename): bool
    {
        return file_exists($this->outputDirectory . '/' . $filename);
    }
}
