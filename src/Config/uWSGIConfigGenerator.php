<?php

declare(strict_types=1);

namespace StevieRay\Config;

class UwsgiConfigGenerator extends AbstractConfigGenerator
{
    #[\Override]
    public function getFilename(): string
    {
        return 'referral_spam.res';
    }

    #[\Override]
    public function getDescription(): string
    {
        return 'uWSGI configuration file';
    }

    #[\Override]
    public function generate(array $domains, string $date): string
    {
        $this->validateDomains($domains);
        $formattedDomains = $this->formatDomains($domains);

        $header = $this->createHeader($date);
        $instructions = $this->generateInstructions();
        $routes = $this->generateRoutes($formattedDomains);

        return $header . "\n" . $instructions . "\n" . $routes;
    }

    /**
     * Generate installation instructions
     *
     * @return string
     */
    private function generateInstructions(): string
    {
        return "#\n" .
               "# Put referral-spam.res in /path/to/vassals, then include it from within\n" .
               "# /path/to/vassals/vassal.ini:\n" .
               "#\n" .
               "# ini = referral_spam.res:blacklist_spam\n\n";
    }

    /**
     * Generate the route rules for uWSGI
     *
     * @param array<string> $domains
     * @return string
     */
    private function generateRoutes(array $domains): string
    {
        $content = "[blacklist_spam]\n";

        foreach ($domains as $domain) {
            $content .= 'route-referer = (?i)' . $domain . " break:403 Forbidden\n";
        }

        $content .= 'route-label = referral_spam';

        return $content;
    }
}
