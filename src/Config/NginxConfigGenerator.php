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

        $header = $this->createHeader($date);
        $instructions = $this->generateInstructions();
        $mapSection = $this->generateMapSection($domains);

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
               "# IMPORTANT: You must increase the map hash bucket size to support the large domain list.\n" .
               "# Add the following to your /etc/nginx/nginx.conf (in the http block):\n" .
               "#\n" .
               "#     map_hash_bucket_size 128;\n" .
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
               "#\n" .
               "# Performance note: This configuration uses a performance-optimized approach with\n" .
               "# hostname matching instead of thousands of regex patterns. Only one regex is\n" .
               "# evaluated per request to extract the domain from the Referer header.\n" .
               "#\n";
    }

    /**
     * Generate the map section for Nginx using performance-optimized approach
     *
     * This uses two maps:
     * 1. Extract the domain from the Referer header (one regex evaluation)
     * 2. Match against domain hosts using hostnames parameter (no regex)
     *
     * This reduces from thousands of regex evaluations to just 1 per request.
     *
     * @param array<string> $domains
     * @return string
     */
    private function generateMapSection(array $domains): string
    {
        // First map: Extract domain from Referer header (one regex evaluation)
        $content = "# Extract the domain from the Referer header\n";
        $content .= "map \$http_referer \$http_referer_host {\n";
        $content .= "\t\"~^(?:https?://)?([^/]+)\" \$1;\n";
        $content .= "}\n\n";

        // Second map: Match against domain hosts using hostnames (no regex)
        $content .= "# Match against spam domains using hostname matching\n";
        $content .= "map \$http_referer_host \$bad_referer {\n";
        $content .= "\thostnames;\n";
        $content .= "\tdefault 0;\n\n";

        foreach ($domains as $domain) {
            // Leading dot ensures we block both bare domains and subdomains
            $content .= "\t." . $domain . " 1;\n";
        }

        $content .= "\n}";

        return $content;
    }
}
