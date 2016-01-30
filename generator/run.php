<?php
class Generate
{

    /**
     * @return array
     */
    public function domainWorker()
    {
        $domainsFile = __DIR__ . "/domains.txt";
        $handle = fopen($domainsFile, "r");
        if (! $handle) {
            throw new \RuntimeException('Error opening file ' . $domainsFile);
        }
        $lines = array();
        while (($line = fgets($handle)) !== false) {
            $line = trim(preg_replace('/\s\s+/', ' ', $line));

            // convert russian domains
            if (preg_match('/[А-Яа-яЁё]/u', $line)){

                $IDN = new idna_convert();

                $line = $IDN->encode($line);

                echo $line."\n\n";

            }

            if (empty($line)) {
                continue;
            }
            $lines[] = $line;
        }
        fclose($handle);
        $uniqueLines = array_unique($lines, SORT_STRING);
        sort($uniqueLines, SORT_STRING);
        file_put_contents($domainsFile, implode("\n", $uniqueLines));
        return $lines;
    }
    /**
     * @param string $date
     * @param array  $lines
     */
    public function createApache($date, array $lines)
    {
        $file = __DIR__ . '/../.htaccess';
        $data = "# https://github.com/Stevie-Ray/apache-nginx-referral-spam-blacklist
# Updated " . $date . "\n
<IfModule mod_rewrite.c>\n
RewriteEngine On\n\n";
        foreach ($lines as $line) {
            $data .= "RewriteCond %{HTTP_REFERER} ^http(s)?://(www.)?.*" . preg_quote($line) . ".*$ [NC,OR]\n";
        }
        $data .= "</IfModule>
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
        file_put_contents($file, $data);
    }
    /**
     * @param string $date
     * @param array  $lines
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
        file_put_contents($file, $data);
    }
    /**
     * @param string $date
     * @param array  $lines
     */
    public function createGoogleExclude($date, array $lines)
    {
        $file = __DIR__ . '/../google-exclude.txt';
        $reqexLines = [];
        foreach ($lines as $line) {
            $reqexLines[] = preg_quote($line);
        }
        $data = implode('|', $reqexLines);

        file_put_contents($file, $data);
    }
}
date_default_timezone_set('UTC');
$date = date('Y-m-d H:i:s');
$generator = new Generate();
require_once('idna_convert.class.php');
$lines = $generator->domainWorker();
$generator->createApache($date, $lines);
$generator->createNginx($date, $lines);
$generator->createGoogleExclude($date, $lines);