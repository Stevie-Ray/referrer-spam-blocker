<?php

declare(strict_types=1);

namespace StevieRay\Config;

abstract class AbstractConfigGenerator implements ConfigGeneratorInterface
{
    protected const PROJECT_URL = 'https://github.com/Stevie-Ray/referrer-spam-blocker';

    /**
     * Escape a domain for use in configuration files
     *
     * @param string $domain
     * @return string
     */
    protected function escapeDomain(string $domain): string
    {
        return preg_quote($domain, '/');
    }

    /**
     * Create a header comment for configuration files
     *
     * @param string $date
     * @return string
     */
    protected function createHeader(string $date): string
    {
        return '# ' . self::PROJECT_URL . "\n# Updated " . $date . "\n";
    }

    /**
     * Validate that domains array is not empty
     *
     * @param array<string> $domains
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateDomains(array $domains): void
    {
        if (empty($domains)) {
            throw new \InvalidArgumentException('Domains array cannot be empty');
        }
    }

    /**
     * Format domains for use in configuration
     *
     * @param array<string> $domains
     * @return array<string>
     */
    protected function formatDomains(array $domains): array
    {
        return array_map([$this, 'escapeDomain'], $domains);
    }
}
