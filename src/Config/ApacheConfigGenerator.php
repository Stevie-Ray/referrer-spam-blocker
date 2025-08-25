<?php

declare(strict_types=1);

namespace StevieRay\Config;

class ApacheConfigGenerator extends AbstractConfigGenerator
{
    #[\Override]
    public function getFilename(): string
    {
        return '.htaccess';
    }

    #[\Override]
    public function getDescription(): string
    {
        return 'Apache .htaccess configuration file';
    }

    #[\Override]
    public function generate(array $domains, string $date): string
    {
        $this->validateDomains($domains);
        $formattedDomains = $this->formatDomains($domains);

        $header = $this->createHeader($date);
        $modRewriteSection = $this->generateModRewriteSection($formattedDomains);
        $modSetEnvIfSection = $this->generateModSetEnvIfSection($formattedDomains);
        $authSection = $this->generateAuthSection();

        return $header . "\n\n" . $modRewriteSection . "\n\n" . $modSetEnvIfSection . "\n\n" . $authSection;
    }

    /**
     * Generate mod_rewrite section
     *
     * @param array<string> $domains
     * @return string
     */
    private function generateModRewriteSection(array $domains): string
    {
        $content = "<IfModule mod_rewrite.c>\n\nRewriteEngine On\n\n";

        $lastIndex = count($domains) - 1;
        foreach ($domains as $index => $domain) {
            $flag = ($index === $lastIndex) ? '[NC]' : '[NC,OR]';
            $content .= 'RewriteCond %{HTTP_REFERER} ^http(s)?://(www.)?.*' . $domain . '.*$ ' . $flag . "\n";
        }

        $content .= "RewriteRule ^(.*)$ – [F,L]\n\n</IfModule>";

        return $content;
    }

    /**
     * Generate mod_setenvif section
     *
     * @param array<string> $domains
     * @return string
     */
    private function generateModSetEnvIfSection(array $domains): string
    {
        $content = "<IfModule mod_setenvif.c>\n\n";

        foreach ($domains as $domain) {
            $content .= 'SetEnvIfNoCase Referer ' . $domain . " spambot=yes\n";
        }

        $content .= "\n</IfModule>";

        return $content;
    }

    /**
     * Generate authorization section for both Apache 2.2 and 2.4
     *
     * @return string
     */
    private function generateAuthSection(): string
    {
        return "# Apache 2.2\n" .
               "<IfModule !mod_authz_core.c>\n" .
               "\t<IfModule mod_authz_host.c>\n" .
               "\t\tOrder allow,deny\n" .
               "\t\tAllow from all\n" .
               "\t\tDeny from env=spambot\n" .
               "\t</IfModule>\n" .
               "</IfModule>\n" .
               "# Apache 2.4\n" .
               "<IfModule mod_authz_core.c>\n" .
               "\t<RequireAll>\n" .
               "\t\tRequire all granted\n" .
               "\t\tRequire not env spambot\n" .
               "\t</RequireAll>\n" .
               '</IfModule>';
    }
}
