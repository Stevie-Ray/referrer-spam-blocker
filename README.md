referrer-spam-blocker
====================================

Apache, Nginx, IIS, uWSGI, Caddy & Varnish blacklist + Google Analytics segments to prevent referrer spam traffic 👾

[![Latest Stable Version](https://img.shields.io/packagist/v/Stevie-Ray/referrer-spam-blocker.svg?style=square)](https://packagist.org/packages/Stevie-Ray/referrer-spam-blocker)
[![Build Status](https://travis-ci.org/Stevie-Ray/referrer-spam-blocker.svg)](https://travis-ci.org/Stevie-Ray/referrer-spam-blocker)
[![Dependency Status](https://www.versioneye.com/php/Stevie-Ray:referrer-spam-blocker/badge.svg?style=flat)](https://www.versioneye.com/php/Stevie-Ray:referrer-spam-blocker)
[![Code Quality](https://img.shields.io/scrutinizer/g/Stevie-Ray/referrer-spam-blocker.svg?style=flat)](https://scrutinizer-ci.com/g/Stevie-Ray/referrer-spam-blocker/?branch=master)
[![Packagist Downloads](https://img.shields.io/packagist/dt/Stevie-Ray/referrer-spam-blocker.svg?style=flat)](https://packagist.org/packages/Stevie-Ray/referrer-spam-blocker)
[![License](https://img.shields.io/packagist/l/Stevie-Ray/referrer-spam-blocker.svg?style=flat)](https://packagist.org/packages/Stevie-Ray/referrer-spam-blocker)

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


## Caddy (HTTP/2 Web Server with Automatic HTTPS): referral-spam.caddy

Move this file next to your Caddy config file given through -conf, and include it by doing:

     include ./referral-spam.caddy;

 Then start your caddy server. All the referrers will now be redirected to a 444 HTTP answer


## uWSGI: referral_spam.res

Include the file `referral_spam.res` into your vassal .ini configuration file:

```
ini = referral_spam.res:blacklist_spam
```

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
```

## Like it?

- [Buy me a beer](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4XC7KX75K6636) 🍺
