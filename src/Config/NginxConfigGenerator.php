<?php

declare(strict_types=1);

namespace StevieRay\Config;

class NginxConfigGenerator extends AbstractConfigGenerator
{
    #[\Override]
    public function getFilename(): string
    {
        return 'referral-spam.conf';
    }

    #[\Override]
    public function getDescription(): string
    {
        return 'Nginx configuration file';
    }

    #[\Override]
    public function generate(array $domains, string $date): string
    {
        $this->validateDomains($domains);
        $formattedDomains = $this->formatDomains($domains);

        $header = $this->createHeader($date);
        $instructions = $this->generateInstructions();
        $mapSection = $this->generateMapSection($formattedDomains);

        return $header . "\n" . $instructions . "\n" . $mapSection;
    }

    /**
     * Generate installation instructions
     *
     * @return string
     */
    private function generateInstructions(): string
    {
        return "#\n" .
               "# /etc/nginx/referral-spam.conf\n" .
               "#\n" .
               "# With referral-spam.conf in /etc/nginx, include it globally from within /etc/nginx/nginx.conf:\n" .
               "#\n" .
               "#     include referral-spam.conf;\n" .
               "#\n" .
               "# Add the following to each /etc/nginx/site-available/your-site.conf that needs protection:\n" .
               "#\n" .
               "#      server {\n" .
               "#        if (\$bad_referer) {\n" .
               "#          return 444;\n" .
               "#        }\n" .
               "#      }\n" .
               "#\n";
    }

    /**
     * Generate the map section for Nginx
     *
     * @param array<string> $domains
     * @return string
     */
    private function generateMapSection(array $domains): string
    {
        $content = "map \$http_referer \$bad_referer {\n\tdefault 0;\n\n";

        foreach ($domains as $domain) {
            $content .= "\t\"~*" . $domain . "\" 1;\n";
        }

        $content .= "\n}";

        return $content;
    }
}
