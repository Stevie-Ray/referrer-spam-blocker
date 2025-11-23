<?php

declare(strict_types=1);

namespace StevieRay\Config;

class HAProxyConfigGenerator extends AbstractConfigGenerator
{
    #[\Override]
    public function getFilename(): string
    {
        return 'referral-spam.haproxy';
    }

    #[\Override]
    public function getDescription(): string
    {
        return 'HAProxy configuration file';
    }

    #[\Override]
    public function generate(array $domains, string $date): string
    {
        $this->validateDomains($domains);
        $formattedDomains = $this->formatDomains($domains);

        $header = $this->createHeader($date);
        $instructions = $this->generateInstructions();
        $domainList = $this->generateDomainList($formattedDomains);

        return $header . "\n" . $instructions . "\n" . $domainList;
    }

    /**
     * Generate installation instructions
     *
     * @return string
     */
    private function generateInstructions(): string
    {
        return "#\n" .
               "# Use it in your HAProxy config by adding all domains.txt items,\n" .
               "# in any frontend, listen or backend block:\n" .
               "#\n" .
               "#     acl spam_referer hdr_sub(referer) -i -f /etc/haproxy/referral-spam.haproxy\n" .
               "#     http-request deny if spam_referer\n" .
               "#\n";
    }

    /**
     * Generate the domain list for HAProxy
     *
     * @param array<string> $domains
     * @return string
     */
    private function generateDomainList(array $domains): string
    {
        return implode("\n", $domains) . "\n";
    }
}
