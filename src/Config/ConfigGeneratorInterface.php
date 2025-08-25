<?php

declare(strict_types=1);

namespace StevieRay\Config;

interface ConfigGeneratorInterface
{
    /**
     * Generate configuration content for the server
     *
     * @param array<string> $domains
     * @param string $date
     * @return string
     */
    public function generate(array $domains, string $date): string;

    /**
     * Get the filename for this configuration
     *
     * @return string
     */
    public function getFilename(): string;

    /**
     * Get the description of this configuration type
     *
     * @return string
     */
    public function getDescription(): string;
}
