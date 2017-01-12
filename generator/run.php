<?php

use Mso\IdnaConvert\IdnaConvert;

class Generate
{

    private $projectUrl = "https://github.com/Stevie-Ray/referrer-spam-blocker";

    public function generateFiles()
    {

        date_default_timezone_set('UTC');
        $date = date('Y-m-d H:i:s');

        $lines = $this->domainWorker();

        $this->createApache($date, $lines);
        $this->createNginx($date, $lines);
        $this->createVarnish($date, $lines);
        $this->createIIS($date, $lines);
        $this->createuWSGI($date, $lines);
        $this->createGoogleExclude($lines);
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
     * @param $file
     * @param $data
     */
    protected function writeToFile($file, $data)
    {
        if (is_writable($file)) {
            file_put_contents($file, $data);
            if (!chmod($file, 0644)) {
                trigger_error("Couldn't not set " . basename($file) . " permissions to 644");
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
        $file = __DIR__ . '/../.htaccess';
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

        $this->writeToFile($file, $data);
    }

    /**
     * @param string $date
     * @param array $lines
     */
    public function createNginx($date, array $lines)
    {
        $file = __DIR__ . '/../referral-spam.conf';
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

        $this->writeToFile($file, $data);
    }

    /**
     * @param string $date
     * @param array $lines
     */
    public function createVarnish($date, array $lines)
    {
        $file = __DIR__ . '/../referral-spam.vcl';

        $data = "# " . $this->projectUrl . "\n# Updated " . $date . "\nsub block_referral_spam {\n\tif (\n";
        foreach ($lines as $line) {
            if ($line === end($lines)) {
                $data .= "\t\treq.http.Referer ~ \"(?i)" . preg_quote($line) . "\"\n";
                break;
            }

            $data .= "\t\treq.http.Referer ~ \"(?i)" . preg_quote($line) . "\" ||\n";
        }

        $data .= "\t) {\n\t\t\treturn (synth(444, \"No Response\"));\n\t}\n}";

        $this->writeToFile($file, $data);
    }

    /**
     * @param string $date
     * @param array $lines
     */
    public function createIIS($date, array $lines)
    {
        $file = __DIR__ . '/../web.config';

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

        $this->writeToFile($file, $data);
    }


    /**
     * @param string $date
     * @param array $lines
     */
    public function createuWSGI($date, array $lines)
    {
        $file = __DIR__ . '/../referral_spam.res';

        $data = "# " . $this->projectUrl . "\n# Updated " . $date . "\n#\n" .
			"# Put referral-spam.res in /path/to/vassals, then include it from within /path/to/vassals/vassal.ini:\n" .
			"#\n# ini = referral_spam.res:blacklist_spam\n#\n\n" .
			"[blacklist_spam]\n";
        foreach ($lines as $line) {
        	$data .= "route-referer = ~*" . preg_quote($line) . " break:403 Forbidden\n";
        }
        $data .= "route-label = referral_spam";

        $this->writeToFile($file, $data);
    }

    /**
     * @param array $lines
     */
    public function createGoogleExclude(array $lines)
    {
        $file = __DIR__ . '/../google-exclude.txt';
        $regexLines = [];
        foreach ($lines as $line) {
            $regexLines[] = preg_quote($line);
        }
        $data = implode('|', $regexLines);

        $this->writeToFile($file, $data);
    }
}

require __DIR__ . '/vendor/autoload.php';

$generator = new Generate();
$generator->generateFiles();