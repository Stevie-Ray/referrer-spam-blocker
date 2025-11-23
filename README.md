<h1 align="center">Referrer Spam Blocker :robot:</h1>

<p align="center">Apache, Nginx, IIS, uWSGI, Caddy, Varnish, HAProxy, Traefik & Lighttpd blacklist + Google Analytics segments to prevent referrer spam traffic</p>

<br />

[![Latest Stable Version](https://img.shields.io/packagist/v/stevie-ray/referrer-spam-blocker)](https://packagist.org/packages/Stevie-Ray/referrer-spam-blocker)
[![Build Status](https://travis-ci.org/Stevie-Ray/referrer-spam-blocker.svg)](https://travis-ci.org/Stevie-Ray/referrer-spam-blocker)
[![Libraries.io dependency status for latest release](https://img.shields.io/librariesio/release/github/stevie-ray/referrer-spam-blocker)](https://libraries.io/github/Stevie-Ray/referrer-spam-blocker)
[![Code Quality](https://img.shields.io/scrutinizer/g/Stevie-Ray/referrer-spam-blocker)](https://scrutinizer-ci.com/g/Stevie-Ray/referrer-spam-blocker/?branch=master)
[![Packagist](https://img.shields.io/packagist/dt/Stevie-Ray/referrer-spam-blocker)](https://packagist.org/packages/stevie-ray/referrer-spam-blocker/stats)
[![License](https://img.shields.io/packagist/l/stevie-ray/referrer-spam-blocker)](https://packagist.org/packages/Stevie-Ray/referrer-spam-blocker)
- - - -

## Apache: .htaccess

.htaccess is a configuration file for use on web servers running Apache. This file is usually found in the root “public_html” folder of your website. The .htaccess file uses two modules to prevent referral spam, mod_rewrite and mod_setenvif. Decide which method is most suitable with your Apache server configuration. This file is **Apache 2.4** ready, where mod_authz_host got deprecated.


## Nginx: referral-spam.conf

With `referral-spam.conf` in `/etc/nginx`, include it globally from within `/etc/nginx/nginx.conf`:

```conf
http {
	include referral-spam.conf;
}
```

Add the following to each `/etc/nginx/site-available/your-site.conf` that needs protection:

```conf
server {
	if ($bad_referer) {
		return 444;
	}
}
```


## Varnish: .refferal-spam.vcl

Add `referral-spam.vcl` to **Varnish 4** default file: `default.vcl` by adding the following code right underneath your default backend definitions

```conf
include "referral-spam.vcl";
sub vcl_recv { call block_referral_spam; }
```


## IIS (Internet Information Services): web.config

The web.config file is located in the root directory of your Windows Server web application.


## Caddy (HTTP/2 Web Server with Automatic HTTPS): referral-spam.caddy and referral-spam.caddy2

Move this file next to your Caddy config file, and include it by doing:

    # For Caddy 1:
     include ./referral-spam.caddy;
    # For Caddy 2:
     import ./referral-spam.caddy2

Then start your caddy server. All the referrers will now be redirected to a 444 HTTP answer


## uWSGI: referral_spam.res

Include the file `referral_spam.res` into your vassal .ini configuration file:

```
ini = referral_spam.res:blacklist_spam
```

## HAProxy: referral-spam.haproxy

Use it in your HAProxy config by adding all domains.txt items, in any frontend, listen or backend block:

```
acl spam_referer hdr_sub(referer) -i -f /etc/haproxy/referral-spam.haproxy
http-request deny if spam_referer
```

## Traefik: referral-spam.traefik.yml

Traefik doesn't have native support for blocking based on Referer header. You'll need to use a Traefik plugin or custom middleware.

The generated file contains a YAML list of domains that should be blocked. Use this with:
- A Traefik plugin that supports Referer header blocking
- A ForwardAuth middleware pointing to a service that checks the Referer header
- A custom middleware implementation

See the generated file for detailed instructions and example configurations.

## Lighttpd: referral-spam.lighttpd.conf

Include this file in your main `lighttpd.conf`:

```conf
include "referral-spam.lighttpd.conf"
```

Make sure `mod_rewrite` is enabled in your `server.modules`:

```conf
server.modules = ("mod_rewrite", ...)
```

The configuration blocks referrer spam by redirecting requests with spam referrers. For better performance with large domain lists, consider using `mod_magnet`.

## OpenLiteSpeed: .htaccess

OpenLiteSpeed is Apache-compatible and supports `.htaccess` files. Simply use the Apache `.htaccess` file (see Apache section above).

Make sure `mod_rewrite` is enabled in your OpenLiteSpeed configuration:
- Admin Panel > Server > Modules > mod_rewrite (enable)

## Options for Google Analytics 'ghost' spam

The above methods don't stop the Google Analytics **ghost** referral spam (because they are hitting Analytics directly and don't touching your website). You should use filters in Analytics to prevent **ghost** referral spam and hide spam form the **past**. 
Because Google Analytics segments are limited to *30.000* characters the exclude list is separated into multiple parts. 

Navigate to your Google Analytics Admin panel and add these Segments:

Filter | Session | **Include**
------------ | ------------- | -------------
Hostname | matches regex | ```your-website\.com|www\.your-website\.com```

Filter | Session | **Exclude**
------------ | ------------- | -------------
Source | matches regex |Copy all the domains from [google-exclude-1.txt](https://raw.githubusercontent.com/Stevie-Ray/referrer-spam-blocker/master/google-exclude-1.txt) to this field

Do the same for [google-exclude-2.txt](https://raw.githubusercontent.com/Stevie-Ray/referrer-spam-blocker/master/google-exclude-2.txt). Please note there may be more files in the future. 

You can also prevent **ghost** referral spam by:

  * [Adding a filter](https://support.google.com/analytics/answer/1033162)
  * [Enabeling bot and Spider Filtering](https://plus.google.com/+GoogleAnalytics/posts/2tJ79CkfnZk)

## Command Line Interface

```bash
# Basic usage
php run.php
php run.php --types apache,nginx
php run.php --dry-run
php run.php --output /path/to/configs

# Options: -h (help), -v (version), --dry-run, -o (output), -t (types)
# Supported types: apache, nginx, varnish, iis, uwsgi, caddy, caddy2, haproxy, traefik, lighttpd, google
```

## Testing

The project includes comprehensive testing and code quality tools:

```bash
# Run tests
composer test
composer test-coverage

# Code quality
composer phpstan
composer phpcs
composer phpcbf
composer quality
```

Tests cover unit testing, configuration generation, domain processing, and file operations. Quality tools include PHPStan (Level 8), PHP CodeSniffer (PSR-12), and Psalm for static analysis.

## Programmatic Usage

```php
use StevieRay\Generator;

$generator = new Generator('/path/to/output');
$generator->generateFiles();
$generator->generateSpecificConfigs(['apache', 'nginx']);
$stats = $generator->getStatistics();
```

## Intregrate in a Dockerfile

You can also integrate these configuration file in your Docker repo, so you will get always the most updated version when you build your image.
For `Apache, Nginx, Varnish 4` or `IIS` add the following line to your `Dockerfile`

```conf
# Apache: Download .htaccess to /usr/local/apache2/htdocs/
ADD https://raw.githubusercontent.com/Stevie-Ray/referrer-spam-blocker/master/.htaccess /usr/local/apache2/htdocs/

# Nginx: Download referral-spam.conf to /etc/nginx/
ADD https://raw.githubusercontent.com/Stevie-Ray/referrer-spam-blocker/master/referral-spam.conf /etc/nginx/

# Varnish 4: Download referral-spam.vcl to /etc/varnish/
ADD https://raw.githubusercontent.com/Stevie-Ray/referrer-spam-blocker/master/referral-spam.vcl /etc/varnish/

# IIS: Download web.config to /sitepath/ (change sitepath accordingly)
ADD https://raw.githubusercontent.com/Stevie-Ray/referrer-spam-blocker/master/web.config /sitepath/

# Caddy: Download referral-spam.caddy to /sitepath/ (next to your Caddy config file given through -conf)
ADD https://raw.githubusercontent.com/Stevie-Ray/referrer-spam-blocker/master/referral-spam.caddy /sitepath/

# uWSGI: Download referral_spam.res to /sitepath/ (change sitepath accordingly)
ADD https://raw.githubusercontent.com/Stevie-Ray/referrer-spam-blocker/master/referral_spam.res /sitepath/

# HAProxy: Download referral-spam.haproxy to /etc/haproxy/
ADD https://raw.githubusercontent.com/Stevie-Ray/referrer-spam-blocker/master/referral-spam.haproxy /etc/haproxy/

# Traefik: Download referral-spam.traefik.yml to /sitepath/ (use with Traefik plugin)
ADD https://raw.githubusercontent.com/Stevie-Ray/referrer-spam-blocker/master/referral-spam.traefik.yml /sitepath/

# Lighttpd: Download referral-spam.lighttpd.conf to /etc/lighttpd/
ADD https://raw.githubusercontent.com/Stevie-Ray/referrer-spam-blocker/master/referral-spam.lighttpd.conf /etc/lighttpd/

# OpenLiteSpeed: Use the Apache .htaccess file (OpenLiteSpeed is Apache-compatible)
ADD https://raw.githubusercontent.com/Stevie-Ray/referrer-spam-blocker/master/.htaccess /sitepath/
```

## Like it?

- [Buy me a beer](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4XC7KX75K6636) 🍺
