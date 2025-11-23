<?php

declare(strict_types=1);

namespace StevieRay\Config;

class LighttpdConfigGenerator extends AbstractConfigGenerator
{
    #[\Override]
    public function getFilename(): string
    {
        return 'referral-spam.lighttpd.conf';
    }

    #[\Override]
    public function getDescription(): string
    {
        return 'Lighttpd configuration file';
    }

    #[\Override]
    public function generate(array $domains, string $date): string
    {
        $this->validateDomains($domains);
        $formattedDomains = $this->formatDomains($domains);

        $header = $this->createHeader($date);
        $instructions = $this->generateInstructions();
        $rewriteRules = $this->generateRewriteRules($formattedDomains);

        return $header . "\n" . $instructions . "\n" . $rewriteRules;
    }

    /**
     * Generate installation instructions
     *
     * @return string
     */
    private function generateInstructions(): string
    {
        return "#\n" .
               "# Lighttpd Referrer Spam Blocker Configuration\n" .
               "#\n" .
               "# Include this file in your main lighttpd.conf:\n" .
               "#\n" .
               "#     include \"referral-spam.lighttpd.conf\"\n" .
               "#\n" .
               "# Make sure mod_rewrite is enabled in your server.modules:\n" .
               "#\n" .
               "#     server.modules = (\"mod_rewrite\", ...)\n" .
               "#\n";
    }

    /**
     * Generate rewrite rules for Lighttpd
     *
     * @param array<string> $domains
     * @return string
     */
    private function generateRewriteRules(array $domains): string
    {
        $content = "# Block referrer spam\n";
        $content .= "# Note: Lighttpd has regex size limits, so we split into multiple conditions\n";
        $content .= "# If you have many domains, consider using mod_magnet for better performance\n\n";

        // Split domains into chunks to avoid regex size limits
        $chunks = array_chunk($domains, 100);
        $chunkIndex = 0;

        foreach ($chunks as $chunk) {
            $chunkIndex++;
            $content .= "# Block group " . $chunkIndex . "\n";
            $content .= "\$HTTP[\"referer\"] =~ \"(";

            $patterns = [];
            foreach ($chunk as $domain) {
                $patterns[] = $domain;
            }

            $content .= implode('|', $patterns);
            $content .= ")\" {\n";
            $content .= "    url.redirect = (\"^/(.*)\" => \"http://127.0.0.1/\")\n";
            $content .= "}\n\n";
        }

        $content .= "# Alternative: Use mod_magnet for better performance with large domain lists\n";
        $content .= "# See: https://redmine.lighttpd.net/projects/lighttpd/wiki/Docs_ModMagnet\n";

        return $content;
    }
}
