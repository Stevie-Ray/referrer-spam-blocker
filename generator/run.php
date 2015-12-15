<?php

class Generate
{

    public function createApache()
    {
        date_default_timezone_set('UTC');
        $date = date('Y-m-d H:i:s');
        $file = '../.htaccess';

        $data = "# https://github.com/Stevie-Ray/apache-nginx-referral-spam-blacklist
# Updated " . $date . "\n
<IfModule mod_rewrite.c>\n
RewriteEngine On\n\n";
        $handle = fopen(__DIR__ . "/domains.txt", "r");
        if (!$handle) {
            throw new \RuntimeException('Error opening file domains.txt');
        }

        while (($line = fgets($handle)) !== false) {
            $line = preg_quote(trim(preg_replace('/\s\s+/', ' ', $line)));
            if (empty($line)) {
                continue;
            }
            $data .= "RewriteCond %{HTTP_REFERER} ^http(s)?://(www.)?.*" . $line . ".*$ [NC,OR]\n";
        }
        fclose($handle);

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

        // Write the contents back to the
        file_put_contents($file, $data);
    }

    public function createNginx()
    {
        date_default_timezone_set('UTC');
        $date = date('Y-m-d H:i:s');
        $file = '../referral-spam.conf';

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
#     server {
#       if (\$bad_referer) {
#         return 444;
#       }
#     }
#
map \$http_referer \$bad_referer {
    default 0;\n\n";

        $handle = fopen(__DIR__ . "/domains.txt", "r");
        if (!$handle) {
            throw new \RuntimeException('Error opening file domains.txt');
        }

        while (($line = fgets($handle)) !== false) {
            $line = preg_quote(trim(preg_replace('/\s\s+/', ' ', $line)));
            if (empty($line)) {
                continue;
            }
            $data .= "\t\"~*" . $line . "\" 1;\n";
        }
        fclose($handle);

        $data .= "\n}";


        // Write the contents back to the
        file_put_contents($file, $data);
    }
}

$generator = new Generate();
$generator->createApache();
$generator->createNginx();
