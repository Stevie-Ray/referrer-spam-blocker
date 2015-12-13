<?php

class generator
{

    public function test()
    {
        date_default_timezone_set('UTC');
        $date = date('Y-m-d');
        $file = '.htaccess';

        $data = "# https://github.com/Stevie-Ray/apache-nginx-referral-spam-blacklist
# Updated " . $date . "\r\n
<IfModule mod_rewrite.c>\r\n
RewriteEngine On\r\n
";

        $handle = fopen("generator/domains.txt", "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $line = trim(preg_replace('/\s\s+/', ' ', $line));
                $string = "RewriteCond %{HTTP_REFERER} ^http(s)?://(www.)?.*" . $line . ".*$ [NC,OR]
";
                $data .= $string;
            }
            fclose($handle);
        } else {
            // error opening the file.
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

        // Write the contents back to the
        file_put_contents($file, $data);
    }
}

