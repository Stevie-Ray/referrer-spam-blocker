apache-nginx-referral-spam-blacklist [![Build Status](https://travis-ci.org/Stevie-Ray/apache-nginx-referral-spam-blacklist.svg)](https://travis-ci.org/Stevie-Ray/apache-nginx-referral-spam-blacklist)
====================================

Generator to create Apache and Nginx files plus a Google Analytics segment to prevent referral spam traffic.

- - - -

## Apache: .htaccess usage

.htaccess is a configuration file for use on web servers running Apache. This file is usually found in the root “public_html” folder of your website. The .htaccess file uses two modules to prevent referral spam, mod_rewrite and mod_setenvif. Decide which method is most suitable with your Apache server configuration. This file is **Apache 2.4** ready, where mod_authz_host got deprecated.


## Nginx: referral-spam.conf usage

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


## Options for Google Analytics 'ghost' spam

**NOTE:**
The above methods don't stop the Google Analytics **ghost** referral spam (because they are hitting Analytics directly and don't touching your website). You should use filters in Analytics to prevent **ghost** referral spam. 


Navigate to your Google Analytics Admin panel and add a Segment:

Filter | Session | **Include**
------------ | ------------- | -------------
Hostname | matches regex | ```your-website\.com|www\.your-website\.com```

Filter | Session | **Exclude**
------------ | ------------- | -------------
Source | matches regex |Copy all the domains from [google-exclude.txt](https://raw.githubusercontent.com/Stevie-Ray/apache-nginx-referral-spam-blacklist/master/google-exclude.txt) to this field

You can also prevent **ghost** referral spam by:

  * [Adding a filter](https://support.google.com/analytics/answer/1033162)
  * [Enabeling bot and Spider Filtering](https://plus.google.com/+GoogleAnalytics/posts/2tJ79CkfnZk) 


## Contributing
 
If you'd like to add a new referrer spammer to the list, [click here to edit the domains.txt file](https://github.com/Stevie-Ray/apache-nginx-referral-spam-blacklist/edit/master/generator/domains.txt) and create a pull request. Alternatively you can create a [new issue](https://github.com/Stevie-Ray/apache-nginx-referral-spam-blacklist/issues/new). In your issue or pull request please explain where the referrer domain appeared and why you think it is a spammer. **Please open one pull request per new domain**.
 
If you open a pull request, it is appreciated if you run the **generator/run.php** file. It sorts the domains, creates the Nginx and Apache files and checks if somebody already reported the domain.


## Like it?

- [Buy me a beer](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4XC7KX75K6636) 🍺