<?php

namespace StevieRay;

use Mso\IdnaConvert\IdnaConvert;

class Generator
{
    private $projectUrl = "https://github.com/Stevie-Ray/referrer-spam-blocker";

    /** @var string string */
    private $outputDir;

    /**
     * @param string $outputDir
     */
    public function __construct($outputDir)
    {
        $this->outputDir = $outputDir;
    }

    public function generateFiles()
    {
        $date = date('Y-m-d H:i:s');
        $lines = $this->domainWorker();
        $this->createApache($date, $lines);
        $this->createNginx($date, $lines);
        $this->createVarnish($date, $lines);
        $this->createIIS($date, $lines);
        $this->createuWSGI($date, $lines);
        $this->createGoogleExclude($lines);
        $this->createCaddyfile($date, $lines);
    }

    /**
     * @return array
     */
    public function domainWorker()
    {
        $domainsFile = __DIR__ . "/domains.txt";

        $handle = fopen($domainsFile, "r");
        if (!$handle) {
            throw new \RuntimeException('Error opening file ' . $domainsFile);
        }
        $lines = array();
        while (($line = fgets($handle)) !== false) {
            $line = trim(preg_replace('/\s\s+/', ' ', $line));

            // convert internationalized domain names
            if (preg_match('/[А-Яа-яЁёɢ]/u', $line)) {

                $IDN = new IdnaConvert();

                $line = $IDN->encode($line);

            }

            if (empty($line)) {
                continue;
            }
            $lines[] = $line;
        }
        fclose($handle);
        $uniqueLines = array_unique($lines, SORT_STRING);
        sort($uniqueLines, SORT_STRING);
        if (is_writable($domainsFile)) {
            file_put_contents($domainsFile, implode("\n", $uniqueLines));
        } else {
            trigger_error("Permission denied");
        }

        return $lines;
    }

    /**
     * @param string $filename
     * @param string $data
     */
    protected function writeToFile($filename, $data)
    {
        $file = $this->outputDir . '/' . $filename;
        if (is_writable($file)) {
            file_put_contents($file, $data);
            if (!chmod($file, 0644)) {
                trigger_error("Couldn't not set " . $filename . " permissions to 644");
            }
        } else {
            trigger_error("Permission denied");
        }
    }

    /**
     * @param string $date
     * @param array $lines
     */
    public function createApache($date, array $lines)
    {
        $data = "# " . $this->projectUrl . "\n# Updated " . $date . "\n\n" .
            "<IfModule mod_rewrite.c>\n\nRewriteEngine On\n\n";
        foreach ($lines as $line) {
            if ($line === end($lines)) {
                $data .= "RewriteCond %{HTTP_REFERER} ^http(s)?://(www.)?.*" . preg_quote($line) . ".*$ [NC]\n";
                break;
            }

            $data .= "RewriteCond %{HTTP_REFERER} ^http(s)?://(www.)?.*" . preg_quote($line) . ".*$ [NC,OR]\n";
        }

        $data .= "RewriteRule ^(.*)$ – [F,L]\n\n</IfModule>\n\n<IfModule mod_setenvif.c>\n\n";
        foreach ($lines as $line) {
            $data .= "SetEnvIfNoCase Referer " . preg_quote($line) . " spambot=yes\n";
        }
        $data .= "\n</IfModule>\n\n# Apache 2.2\n<IfModule !mod_authz_core.c>\n\t<IfModule mod_authz_host.c>\n\t\t" .
            "Order allow,deny\n\t\tAllow from all\n\t\tDeny from env=spambot\n\t</IfModule>\n</IfModule>\n# " .
            "Apache 2.4\n<IfModule mod_authz_core.c>\n\t<RequireAll>" .
            "\n\t\tRequire all granted\n\t\tRequire not env spambot\n\t</RequireAll>\n</IfModule>";

        $this->writeToFile('.htaccess', $data);
    }

    /**
     * @param string $date
     * @param array $lines
     */
    public function createNginx($date, array $lines)
    {
        $data = "# " . $this->projectUrl . "\n# Updated " . $date . "\n#\n# /etc/nginx/referral-spam.conf\n#\n" .
            "# With referral-spam.conf in /etc/nginx, include it globally from within /etc/nginx/nginx.conf:\n#\n" .
            "#     include referral-spam.conf;\n#\n" .
            "# Add the following to each /etc/nginx/site-available/your-site.conf that needs protection:\n#\n" .
            "#      server {\n#        if (\$bad_referer) {\n#          return 444;\n#        }\n#      }\n" .
            "#\nmap \$http_referer \$bad_referer {\n\tdefault 0;\n\n";
        foreach ($lines as $line) {
            $data .= "\t\"~*" . preg_quote($line) . "\" 1;\n";
        }
        $data .= "\n}";

        $this->writeToFile('referral-spam.conf', $data);
    }

    /**
     * @param string $date
     * @param array $lines
     */
    public function createVarnish($date, array $lines)
    {
        $data = "# " . $this->projectUrl . "\n# Updated " . $date . "\nsub block_referral_spam {\n\tif (\n";
        foreach ($lines as $line) {
            if ($line === end($lines)) {
                $data .= "\t\treq.http.Referer ~ \"(?i)" . preg_quote($line) . "\"\n";
                break;
            }

            $data .= "\t\treq.http.Referer ~ \"(?i)" . preg_quote($line) . "\" ||\n";
        }

        $data .= "\t) {\n\t\t\treturn (synth(444, \"No Response\"));\n\t}\n}";

        $this->writeToFile('referral-spam.vcl', $data);
    }

    /**
     * @param string $date
     * @param array $lines
     */
    public function createIIS($date, array $lines)
    {
        $data = "<!-- " . $this->projectUrl . " -->\n<!-- Updated " . $date . " -->\n" .
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
            "<configuration>\n\t<system.webServer>\n\t\t<rewrite>\n\t\t\t<rules>\n";
        foreach ($lines as $line) {

            $data .= "\t\t\t\t<rule name=\"Referrer Spam " . $line . "\" stopProcessing=\"true\">" .
                "<match url=\".*\" /><conditions><add input=\"{HTTP_REFERER}\" pattern=\"(" .
                preg_quote($line) .
                ")\"/></conditions><action type=\"AbortRequest\" /></rule>\n";
        }

        $data .= "\t\t\t</rules>\n\t\t</rewrite>\n\t</system.webServer>\n</configuration>";

        $this->writeToFile('web.config', $data);
    }


    /**
     * @param string $date
     * @param array $lines
     */
    public function createuWSGI($date, array $lines)
    {
        $data = "# " . $this->projectUrl . "\n# Updated " . $date . "\n#\n" .
            "# Put referral-spam.res in /path/to/vassals, then include it from within /path/to/vassals/vassal.ini:\n" .
            "#\n# ini = referral_spam.res:blacklist_spam\n\n" .
            "[blacklist_spam]\n";
        foreach ($lines as $line) {
            $data .= "route-referer = (?i)" . preg_quote($line) . " break:403 Forbidden\n";
        }
        $data .= "route-label = referral_spam";

        $this->writeToFile('referral_spam.res', $data);
    }

    /**
     * @param array $lines
     */
    public function createGoogleExclude(array $lines)
    {

        $regexLines = [];

        foreach ($lines as $line) {
            $regexLines[] = preg_quote($line);
        }
        $data = implode('|', $regexLines);

        $googleLimit = 30000;
        $dataLength = strlen($data);

        // keep track of the last split
        $lastPosition = 0;
        for ($x = 1; $lastPosition < $dataLength; $x++) {

            // already in the boundary limits?
            if (($dataLength - $lastPosition) >= $googleLimit) {
                // search for the last occurrence of | in the boundary limits
                $pipePosition = strrpos(substr($data, $lastPosition, $googleLimit), '|');

                $dataSplit = substr($data, $lastPosition, $pipePosition);

                // without trailing pipe at the beginning of next round
                $lastPosition = $lastPosition + $pipePosition + 1;
            } else {
                // Rest of the regex (no pipe at the end)
                $dataSplit = substr($data, $lastPosition);
                $lastPosition = $dataLength; // Break
            }

            $this->writeToFile('google-exclude-' . $x . '.txt', $dataSplit);
        }

    }

    /**
     * @param string $date
     * @param array $lines
     */
    public function createCaddyfile($date, array $lines)
    {
      $redir_rules = "";

      foreach ($lines as $line) {
        if ( !empty($redir_rules ) ) $redir_rules .= "\n\t";
        $redir_rules .= "if {>Referer} is $line";
      }

      $data = <<<EOT
# $this->projectUrl
# Updated $date
#
# Move this file next to your Caddy config file given through -conf, and include it by doing:
#
#     include ./referral-spam.caddy;
#
# Then start your caddy server. All the referrers will now be redirected to a 444 HTTP answer
#
redir 444 {
	if_op or
	$redir_rules
}
EOT;
      $this->writeToFile('referral-spam.caddy', $data);
    }
}