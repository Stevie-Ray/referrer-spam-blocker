<?php

declare(strict_types=1);

namespace StevieRay\Domain;

use Algo26\IdnaConvert\Exception\AlreadyPunycodeException;
use Algo26\IdnaConvert\Exception\InvalidCharacterException;
use Algo26\IdnaConvert\ToIdn;
use Algo26\IdnaConvert\EncodingHelper\ToUtf8;
use RuntimeException;

class DomainProcessor
{
    private const string DEFAULT_DOMAINS_FILE = __DIR__ . '/../domains.txt';
    private const string IDN_PATTERN = '/[А-Яа-яЁёöɢ]/u';

    private readonly string $domainsFile;

    private readonly ToIdn $idnConverter;

    private readonly ToUtf8 $encodingHelper;

    public function __construct(?string $domainsFile = null)
    {
        $this->domainsFile = $domainsFile ?? self::DEFAULT_DOMAINS_FILE;
        $this->idnConverter = new ToIdn();
        $this->encodingHelper = new ToUtf8();
    }

    /**
     * Process domains from the domains.txt file
     *
     * @return array<string>
     * @throws AlreadyPunycodeException
     * @throws InvalidCharacterException
     * @throws RuntimeException
     */
    public function processDomains(): array
    {
        $domains = $this->readDomainsFile();
        $processedDomains = $this->processDomainList($domains);
        $uniqueDomains = $this->deduplicateAndSort($processedDomains);

        $this->updateDomainsFile($uniqueDomains);

        return $uniqueDomains;
    }

    /**
     * Read domains from the domains.txt file
     *
     * @return array<string>
     * @throws RuntimeException
     */
    private function readDomainsFile(): array
    {
        $handle = @fopen($this->domainsFile, 'r');
        if (!$handle) {
            throw new RuntimeException('Error opening file ' . $this->domainsFile);
        }

        $domains = [];
        while (($line = fgets($handle)) !== false) {
            $line = (string) $line; // Ensure string type
            $cleanedLine = preg_replace('/\s\s+/', ' ', $line);
            if ($cleanedLine !== null) {
                $domain = trim($cleanedLine);
                if ($domain !== '') {
                    $domains[] = $domain;
                }
            }
        }
        fclose($handle);

        return $domains;
    }

    /**
     * Process a list of domains (IDN conversion, validation)
     *
     * @param array<string> $domains
     * @return array<string>
     * @throws AlreadyPunycodeException
     * @throws InvalidCharacterException
     */
    private function processDomainList(array $domains): array
    {
        $processedDomains = [];

        foreach ($domains as $domain) {
            $processedDomain = $this->processDomain($domain);
            if ($processedDomain !== null) {
                $processedDomains[] = $processedDomain;
            }
        }

        return $processedDomains;
    }

    /**
     * Process a single domain
     *
     * @param string $domain
     * @return string|null
     * @throws AlreadyPunycodeException
     * @throws InvalidCharacterException
     */
    private function processDomain(string $domain): ?string
    {
        $domain = trim($domain);

        if ($domain === '') {
            return null;
        }

        // Convert internationalized domain names
        if (preg_match(self::IDN_PATTERN, $domain)) {
            /** @var string $convertedDomain */
            $convertedDomain = $this->encodingHelper->convert($domain);
            $domain = $this->idnConverter->convert($convertedDomain);
        }

        // Basic domain validation
        if (!$this->isValidDomain($domain)) {
            return null;
        }

        return $domain;
    }

    /**
     * Check if a domain is valid
     *
     * @param string $domain
     * @return bool
     */
    private function isValidDomain(string $domain): bool
    {
        // Basic domain validation - can be enhanced with more sophisticated checks
        return $domain !== '' &&
               strlen($domain) <= 253 &&
               preg_match(
                   '/^[a-zA-Z0-9]([a-zA-Z0-9\-]*[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]*[a-zA-Z0-9])?)*$/',
                   $domain
               ) === 1;
    }

    /**
     * Deduplicate and sort domains
     *
     * @param array<string> $domains
     * @return array<string>
     */
    private function deduplicateAndSort(array $domains): array
    {
        $uniqueDomains = array_unique($domains);
        sort($uniqueDomains, SORT_STRING);

        return $uniqueDomains;
    }

    /**
     * Update the domains.txt file with processed domains
     *
     * @param array<string> $domains
     * @return void
     */
    private function updateDomainsFile(array $domains): void
    {
        if (!is_writable($this->domainsFile)) {
            throw new RuntimeException('Permission denied: cannot write to ' . $this->domainsFile);
        }

        $content = implode("\n", $domains) . "\n";
        if (file_put_contents($this->domainsFile, $content) === false) {
            throw new RuntimeException('Failed to write to ' . $this->domainsFile);
        }
    }
}
