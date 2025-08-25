<?php

declare(strict_types=1);

namespace StevieRay\Config;

class CaddyV2ConfigGenerator extends AbstractConfigGenerator
{
    #[\Override]
    public function getFilename(): string
    {
        return 'referral-spam.caddy2';
    }

    #[\Override]
    public function getDescription(): string
    {
        return 'Caddy v2 configuration file';
    }

    #[\Override]
    public function generate(array $domains, string $date): string
    {
        $this->validateDomains($domains);

        $header = $this->createHeader($date);
        $instructions = $this->generateInstructions();
        $blockerRules = $this->generateBlockerRules($domains);

        return $header . "\n" . $instructions . "\n" . $blockerRules;
    }

    /**
     * Generate installation instructions
     *
     * @return string
     */
    private function generateInstructions(): string
    {
        return "#\n" .
               "# Move this file next to your main Caddyfile, and include it by doing:\n" .
               "#\n" .
               "#     import ./referral-spam.caddy2\n" .
               "#\n" .
               "# Then start your caddy server. All the referrers will now be redirected to a 444 HTTP answer\n" .
               "#\n";
    }

    /**
     * Generate blocker rules for Caddy v2
     *
     * @param array<string> $domains
     * @return string
     */
    private function generateBlockerRules(array $domains): string
    {
        $redirRules = [];

        foreach ($domains as $domain) {
            $redirRules[] = str_replace('.', '\.', $domain);
        }

        $redirRulesString = implode('|', $redirRules);

        return "@blocker {\n" .
               '    header_regexp Referer "^' . $redirRulesString . "$\"\n" .
               "}\n" .
               "respond @blocker \"Traffic blocked\" 444 {\n" .
               "     close\n" .
               '}';
    }
}
