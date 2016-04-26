<?php
ini_set('display_errors', true);
error_reporting(E_ALL);

class Generate
{

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

            // convert russian domains
            if (preg_match('/[А-Яа-яЁё]/u', $line)) {

                $IDN = new idna_convert();

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
     * @param string $date
     * @param array $lines
     */
    public function createApache($date, array $lines)
    {
        $file = __DIR__ . '/../.htaccess';
        $data = "# https://github.com/Stevie-Ray/apache-nginx-referral-spam-blacklist
# Updated " . $date . "\n
<IfModule mod_rewrite.c>\n
RewriteEngine On\n\n";
        foreach ($lines as $line) {
            if ($line === end($lines)) {
                $data .= "RewriteCond %{HTTP_REFERER} ^http(s)?://(www.)?.*" . preg_quote($line) . ".*$ [NC]\n";
            break;
            }

            $data .= "RewriteCond %{HTTP_REFERER} ^http(s)?://(www.)?.*" . preg_quote($line) . ".*$ [NC,OR]\n";
        }

        $data .= "RewriteRule ^(.*)$ – [F,L]

</IfModule>

<IfModule mod_setenvif.c>

";
        foreach ($lines as $line) {
            $data .= "SetEnvIfNoCase Referer " . preg_quote($line) . " spambot=yes\n";
        }
        $data .= "
</IfModule>

# Apache 2.2
<IfModule !mod_authz_core.c>
    <IfModule mod_authz_host.c>
        Order allow,deny
        Allow from all
        Deny from env=spambot
    </IfModule>
</IfModule>
# Apache 2.4
<IfModule mod_authz_core.c>
    <RequireAll>
        Require all granted
        Require not env spambot
    </RequireAll>
</IfModule>";
        if (is_readable($file) && is_writable($file)) {
            file_put_contents($file, $data);
            if (!chmod($file, 0644)) {
                trigger_error("Couldn't not set .htaccess permissions to 644");
            }

        } else {
            trigger_error("Permission denied");
        }
    }

    /**
     * @param string $date
     * @param array $lines
     */
    public function createNginx($date, array $lines)
    {
        $file = __DIR__ . '/../referral-spam.conf';
        $data = "# https://github.com/Stevie-Ray/apache-nginx-referral-spam-blacklist
# Updated " . $date . "
#
# /etc/nginx/referral-spam.conf
#
# With referral-spam.conf in /etc/nginx, include it globally from within /etc/nginx/nginx.conf:
#
#     include referral-spam.conf;
#
# Add the following to each /etc/nginx/site-available/your-site.conf that needs protection:
#
#      server {
#        if (\$bad_referer) {
#          return 444;
#        }
#      }
#
map \$http_referer \$bad_referer {
\tdefault 0;\n\n";
        foreach ($lines as $line) {
            $data .= "\t\"~*" . preg_quote($line) . "\" 1;\n";
        }
        $data .= "\n}";

        if (is_readable($file) && is_writable($file)) {
            file_put_contents($file, $data);
            if (!chmod($file, 0644)) {
                trigger_error("Couldn't not set referral-spam.conf permissions to 644");
            }
        } else {
            trigger_error("Permission denied");
        }
    }

    /**
     * @param array $lines
     */
    public function createGoogleExclude(array $lines)
    {
        $file = __DIR__ . '/../google-exclude.txt';
        $reqexLines = [];
        foreach ($lines as $line) {
            $reqexLines[] = preg_quote($line);
        }
        $data = implode('|', $reqexLines);

        if (is_readable($file) && is_writable($file)) {
            file_put_contents($file, $data);
        } else {
            trigger_error("Permission denied");
        }

    }
}

date_default_timezone_set('UTC');
$date = date('Y-m-d H:i:s');
$generator = new Generate();
require __DIR__ . '/vendor/autoload.php';
$lines = $generator->domainWorker();
$generator->createApache($date, $lines);
$generator->createNginx($date, $lines);
$generator->createGoogleExclude($lines);