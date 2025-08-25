<?php

declare(strict_types=1);

namespace StevieRay\Config;

class VarnishConfigGenerator extends AbstractConfigGenerator
{
    #[\Override]
    public function getFilename(): string
    {
        return 'referral-spam.vcl';
    }

    #[\Override]
    public function getDescription(): string
    {
        return 'Varnish configuration file';
    }

    #[\Override]
    public function generate(array $domains, string $date): string
    {
        $this->validateDomains($domains);
        $formattedDomains = $this->formatDomains($domains);

        $header = $this->createHeader($date);
        $subroutine = $this->generateSubroutine($formattedDomains);

        return $header . "\n" . $subroutine;
    }

    /**
     * Generate the Varnish subroutine
     *
     * @param array<string> $domains
     * @return string
     */
    private function generateSubroutine(array $domains): string
    {
        $content = "sub block_referral_spam {\n\tif (\n";

        $lastIndex = count($domains) - 1;
        foreach ($domains as $index => $domain) {
            $separator = ($index === $lastIndex) ? "\n" : " ||\n";
            $content .= "\t\treq.http.Referer ~ \"(?i)" . $domain . '"' . $separator;
        }

        $content .= "\t) {\n\t\t\treturn (synth(444, \"No Response\"));\n\t}\n}";

        return $content;
    }
}
