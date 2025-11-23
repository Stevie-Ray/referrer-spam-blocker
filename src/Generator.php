<?php

declare(strict_types=1);

namespace StevieRay;

use StevieRay\Domain\DomainProcessor;
use StevieRay\Service\FileWriter;
use StevieRay\Config\ConfigGeneratorInterface;
use StevieRay\Config\MultiFileConfigGeneratorInterface;
use StevieRay\Config\ApacheConfigGenerator;
use StevieRay\Config\NginxConfigGenerator;
use StevieRay\Config\VarnishConfigGenerator;
use StevieRay\Config\IISConfigGenerator;
use StevieRay\Config\UwsgiConfigGenerator;
use StevieRay\Config\CaddyConfigGenerator;
use StevieRay\Config\CaddyV2ConfigGenerator;
use StevieRay\Config\GoogleAnalyticsConfigGenerator;
use StevieRay\Config\HAProxyConfigGenerator;
use StevieRay\Config\TraefikConfigGenerator;
use StevieRay\Config\LighttpdConfigGenerator;
use Algo26\IdnaConvert\Exception\AlreadyPunycodeException;
use Algo26\IdnaConvert\Exception\InvalidCharacterException;
use RuntimeException;

class Generator
{
    private readonly DomainProcessor $domainProcessor;

    private readonly FileWriter $fileWriter;

    /** @var array<ConfigGeneratorInterface> */
    private readonly array $configGenerators;

    public function __construct(string $outputDirectory)
    {
        $this->domainProcessor = new DomainProcessor();
        $this->fileWriter = new FileWriter($outputDirectory);
        $this->configGenerators = $this->initializeConfigGenerators();
    }

    /**
     * Generate all configuration files
     *
     * @return void
     * @throws AlreadyPunycodeException
     * @throws InvalidCharacterException
     * @throws RuntimeException
     */
    public function generateFiles(): void
    {
        $date = date('Y-m-d H:i:s');
        $domains = $this->domainProcessor->processDomains();

        foreach ($this->configGenerators as $generator) {
            $this->generateConfigFile($generator, $domains, $date);
        }
    }

    /**
     * Generate a specific configuration file
     *
     * @param ConfigGeneratorInterface $generator
     * @param array<string> $domains
     * @param string $date
     * @return void
     * @throws RuntimeException
     */
    private function generateConfigFile(ConfigGeneratorInterface $generator, array $domains, string $date): void
    {
        try {
            if ($generator instanceof MultiFileConfigGeneratorInterface) {
                // Handle multiple files for generators like Google Analytics
                $files = $generator->generateMultiple($domains, $date);
                $this->fileWriter->writeFiles($files);
            } else {
                // Handle single file
                $content = $generator->generate($domains, $date);
                $this->fileWriter->writeFile($generator->getFilename(), $content);
            }
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Failed to generate {$generator->getDescription()}: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Initialize all configuration generators
     *
     * @return array<ConfigGeneratorInterface>
     */
    private function initializeConfigGenerators(): array
    {
        return [
            new ApacheConfigGenerator(),
            new NginxConfigGenerator(),
            new VarnishConfigGenerator(),
            new IISConfigGenerator(),
            new UwsgiConfigGenerator(),
            new CaddyConfigGenerator(),
            new CaddyV2ConfigGenerator(),
            new HAProxyConfigGenerator(),
            new TraefikConfigGenerator(),
            new LighttpdConfigGenerator(),
            new GoogleAnalyticsConfigGenerator(),
        ];
    }

    /**
     * Get available configuration generators
     *
     * @return array<ConfigGeneratorInterface>
     */
    public function getConfigGenerators(): array
    {
        return $this->configGenerators;
    }

    /**
     * Get statistics about the generation process
     *
     * @return array{
     *   total_domains: int<0, max>,
     *   config_files: int<0, max>,
     *   output_directory: string,
     *   generated_files: array<array-key, string>
     * }
     */
    public function getStatistics(): array
    {
        $domains = $this->domainProcessor->processDomains();

        return [
            'total_domains' => count($domains),
            'config_files' => count($this->configGenerators),
            'output_directory' => $this->fileWriter->getOutputDirectory(),
            'generated_files' => $this->getGeneratedFileList(),
        ];
    }

    /**
     * Get list of generated files
     *
     * @return array<string>
     */
    private function getGeneratedFileList(): array
    {
        $files = [];

        foreach ($this->configGenerators as $generator) {
            if ($generator instanceof MultiFileConfigGeneratorInterface) {
                // Count Google Analytics files
                $index = 1;
                while ($this->fileWriter->fileExists("google-exclude-{$index}.txt")) {
                    $files[] = "google-exclude-{$index}.txt";
                    $index++;
                }
            } elseif ($this->fileWriter->fileExists($generator->getFilename())) {
                $files[] = $generator->getFilename();
            }
        }

        return $files;
    }

    /**
     * Generate only specific configuration types
     *
     * @param array<string> $types
     * @return void
     * @throws AlreadyPunycodeException
     * @throws InvalidCharacterException
     * @throws RuntimeException
     */
    public function generateSpecificConfigs(array $types): void
    {
        $date = date('Y-m-d H:i:s');
        $domains = $this->domainProcessor->processDomains();

        $allowedTypes = array_map('strtolower', $types);

        foreach ($this->configGenerators as $generator) {
            $generatorType = strtolower($generator->getDescription());

            foreach ($allowedTypes as $type) {
                if (str_contains($generatorType, $type)) {
                    $this->generateConfigFile($generator, $domains, $date);

                    break;
                }
            }
        }
    }
}
