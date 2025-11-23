<?php

declare(strict_types=1);

namespace StevieRay\Config;

class TraefikConfigGenerator extends AbstractConfigGenerator
{
    #[\Override]
    public function getFilename(): string
    {
        return 'referral-spam.traefik.yml';
    }

    #[\Override]
    public function getDescription(): string
    {
        return 'Traefik middleware configuration file';
    }

    #[\Override]
    public function generate(array $domains, string $date): string
    {
        $this->validateDomains($domains);
        $formattedDomains = $this->formatDomains($domains);

        $header = $this->createHeader($date);
        $instructions = $this->generateInstructions();
        $middleware = $this->generateMiddleware($formattedDomains);

        return $header . "\n" . $instructions . "\n" . $middleware;
    }

    /**
     * Generate installation instructions
     *
     * @return string
     */
    private function generateInstructions(): string
    {
        return "#\n" .
               "# Traefik Referrer Spam Blocker Configuration\n" .
               "#\n" .
               "# Traefik doesn't have native support for blocking based on Referer header.\n" .
               "# You'll need to use a Traefik plugin or custom middleware.\n" .
               "#\n" .
               "# Option 1: Use Traefik Plugin (Recommended)\n" .
               "# Install a plugin that supports Referer header blocking, such as:\n" .
               "# - traefik-plugin-referrer-spam-blocker\n" .
               "# - Or create a custom plugin using this domain list\n" .
               "#\n" .
               "# Option 2: Use ForwardAuth Middleware\n" .
               "# Create a service that checks the Referer header and returns 403 if spam:\n" .
               "#\n" .
               "#     http:\n" .
               "#       middlewares:\n" .
               "#         referral-spam-auth:\n" .
               "#           forwardAuth:\n" .
               "#             address: \"http://your-auth-service:8080\"\n" .
               "#\n" .
               "# Option 3: Use Custom Headers Middleware\n" .
               "# This file contains the domain list that should be blocked.\n" .
               "# Use it with your custom middleware implementation.\n" .
               "#\n";
    }

    /**
     * Generate Traefik domain list
     *
     * @param array<string> $domains
     * @return string
     */
    private function generateMiddleware(array $domains): string
    {
        $content = "# Referrer Spam Domain List\n";
        $content .= "# Use this list with a Traefik plugin or custom middleware\n";
        $content .= "#\n\n";
        $content .= "domains:\n";

        foreach ($domains as $domain) {
            $content .= "  - " . $domain . "\n";
        }

        $content .= "\n# Example plugin configuration (YAML format):\n";
        $content .= "#\n";
        $content .= "# http:\n";
        $content .= "#   middlewares:\n";
        $content .= "#     referral-spam-blocker:\n";
        $content .= "#       plugin:\n";
        $content .= "#         referralSpamBlocker:\n";
        $content .= "#           domainsFile: \"./referral-spam.traefik.yml\"\n";
        $content .= "#           action: \"deny\"\n";
        $content .= "#           statusCode: 444\n";

        return $content;
    }
}
