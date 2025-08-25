<?php

declare(strict_types=1);

namespace StevieRay\Config;

class CaddyConfigGenerator extends AbstractConfigGenerator
{
    #[\Override]
    public function getFilename(): string
    {
        return 'referral-spam.caddy';
    }

    #[\Override]
    public function getDescription(): string
    {
        return 'Caddy v1 configuration file';
    }

    #[\Override]
    public function generate(array $domains, string $date): string
    {
        $this->validateDomains($domains);

        $header = $this->createHeader($date);
        $instructions = $this->generateInstructions();
        $redirectRules = $this->generateRedirectRules($domains);

        return $header . "\n" . $instructions . "\n" . $redirectRules;
    }

    /**
     * Generate installation instructions
     *
     * @return string
     */
    private function generateInstructions(): string
    {
        return "#\n" .
               "# Move this file next to your Caddy config file given through -conf, and include it by doing:\n" .
               "#\n" .
               "#     include ./referral-spam.caddy;\n" .
               "#\n" .
               "# Then start your caddy server. All the referrers will now be redirected to a 444 HTTP answer\n" .
               "#\n";
    }

    /**
     * Generate redirect rules for Caddy v1
     *
     * @param array<string> $domains
     * @return string
     */
    private function generateRedirectRules(array $domains): string
    {
        $redirRules = '';

        foreach ($domains as $domain) {
            if (!empty($redirRules)) {
                $redirRules .= "\n\t";
            }
            $redirRules .= 'if {>Referer} is ' . $domain;
        }

        return "redir 444 {\n\tif_op or\n\t" . $redirRules . "\n}";
    }
}
