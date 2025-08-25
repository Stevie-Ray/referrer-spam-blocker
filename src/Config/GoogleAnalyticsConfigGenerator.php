<?php

declare(strict_types=1);

namespace StevieRay\Config;

class GoogleAnalyticsConfigGenerator extends AbstractConfigGenerator implements MultiFileConfigGeneratorInterface
{
    private const int GOOGLE_ANALYTICS_LIMIT = 30000;

    #[\Override]
    public function getFilename(): string
    {
        return 'google-exclude-{index}.txt';
    }

    #[\Override]
    public function getDescription(): string
    {
        return 'Google Analytics exclude files';
    }

    #[\Override]
    public function generate(array $domains, string $date): string
    {
        $this->validateDomains($domains);
        $formattedDomains = $this->formatDomains($domains);

        $regexString = implode('|', $formattedDomains);
        $splits = $this->splitByCharacterLimit($regexString);

        // Return the first split as the main content
        return $splits[0] ?? '';
    }

    #[\Override]
    public function generateMultiple(array $domains, string $date): array
    {
        $this->validateDomains($domains);
        $formattedDomains = $this->formatDomains($domains);

        $regexString = implode('|', $formattedDomains);
        $splits = $this->splitByCharacterLimit($regexString);

        $files = [];
        foreach ($splits as $index => $split) {
            $filename = 'google-exclude-' . ($index + 1) . '.txt';
            $files[$filename] = $split;
        }

        return $files;
    }

    /**
     * Split the regex string by Google Analytics character limit
     *
     * @param string $regexString
     * @return array<string>
     */
    private function splitByCharacterLimit(string $regexString): array
    {
        $splits = [];
        $dataLength = strlen($regexString);
        $lastPosition = 0;

        while ($lastPosition < $dataLength) {
            // Check if remaining data fits within the limit
            if (($dataLength - $lastPosition) <= self::GOOGLE_ANALYTICS_LIMIT) {
                // Rest of the regex (no pipe at the end)
                $dataSplit = substr($regexString, $lastPosition);
                $lastPosition = $dataLength; // Break
            } else {
                // Search for the last occurrence of | in the boundary limits
                $pipePosition = strrpos(substr($regexString, $lastPosition, self::GOOGLE_ANALYTICS_LIMIT), '|');

                if ($pipePosition === false) {
                    // No pipe found, take the full limit
                    $dataSplit = substr($regexString, $lastPosition, self::GOOGLE_ANALYTICS_LIMIT);
                    $lastPosition += self::GOOGLE_ANALYTICS_LIMIT;
                } else {
                    $dataSplit = substr($regexString, $lastPosition, $pipePosition);
                    $lastPosition += $pipePosition + 1; // +1 to skip the pipe
                }
            }

            $splits[] = $dataSplit;
        }

        return $splits;
    }
}
