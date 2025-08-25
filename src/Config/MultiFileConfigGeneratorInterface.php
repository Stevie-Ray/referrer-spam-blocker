<?php

declare(strict_types=1);

namespace StevieRay\Config;

interface MultiFileConfigGeneratorInterface extends ConfigGeneratorInterface
{
    /**
     * Generate configuration content for multiple files
     *
     * @param array<string> $domains
     * @param string $date
     * @return array<string, string> Array of filename => content
     */
    public function generateMultiple(array $domains, string $date): array;
}
