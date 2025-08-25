<?php

declare(strict_types=1);

namespace StevieRay\Config;

class IISConfigGenerator extends AbstractConfigGenerator
{
    #[\Override]
    public function getFilename(): string
    {
        return 'web.config';
    }

    #[\Override]
    public function getDescription(): string
    {
        return 'IIS web.config file';
    }

    #[\Override]
    public function generate(array $domains, string $date): string
    {
        $this->validateDomains($domains);
        $formattedDomains = $this->formatDomains($domains);

        $header = '<!-- ' . self::PROJECT_URL . " -->\n<!-- Updated " . $date . " -->\n";
        $xmlContent = $this->generateXmlContent($domains, $formattedDomains);

        return $header . $xmlContent;
    }

    /**
     * Generate the XML content for IIS configuration
     *
     * @param array<string> $domains
     * @param array<string> $formattedDomains
     * @return string
     */
    private function generateXmlContent(array $domains, array $formattedDomains): string
    {
        $content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
                   "<configuration>\n\t<system.webServer>\n\t\t<rewrite>\n\t\t\t<rules>\n";

        foreach ($domains as $index => $domain) {
            $content .= "\t\t\t\t<rule name=\"Referrer Spam " . $domain . '" stopProcessing="true">' .
                       '<match url=".*" /><conditions><add input="{HTTP_REFERER}" pattern="(' .
                       $formattedDomains[$index] . ")\"/></conditions><action type=\"AbortRequest\" /></rule>\n";
        }

        $content .= "\t\t\t</rules>\n\t\t</rewrite>\n\t</system.webServer>\n</configuration>";

        return $content;
    }
}
