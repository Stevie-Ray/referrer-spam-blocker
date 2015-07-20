apache-nginx-referral-spam-blacklist
====================================

These files are created to collect and prevent referral spam traffic sources on a server level. 

- - - -

**NOTE:**
This method doesn't stop the Analytics referral spam (because they are hitting Analytics directly and not even touching your website / server). You should also use filters in Analytics to prevent referral spam. 


## Other options for Google Analytics

If this method fails you can prevent referral spam by:

  * [Add a filter ](https://support.google.com/analytics/answer/1033162)
  * [Bot and Spider Filtering](https://plus.google.com/+GoogleAnalytics/posts/2tJ79CkfnZk) 

## Downloading

If you want to download both files, see the [latest zip](https://github.com/Stevie-Ray/apache-nginx-referral-spam-blacklist/archive/master.zip). Otherwise, if you need to make changes to these files, clone the repo with:

```sh
git clone --recursive https://github.com/Stevie-Ray/apache-nginx-referral-spam-blacklist-block.git
```

## Apache: .htaccess usage
.htaccess is a configuration file for use on web servers running Apache. 
This file is usually found in the root “public_html” folder of your website. The .htaccess file uses two modules to prevent referral spam, mod_rewrite and mod_setenvif. Decide which method is most suitable with your Apache server configuration. **Please note:** The usage of mod_authz_host has changed with Apache 2.4.

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

##  Contributing

If you'd like to help, [contribute feedback](https://github.com/Stevie-Ray/apache-nginx-referral-spam-blacklist/issues), or just fork the repository, then add useful stuff and send a [pull request](https://github.com/Stevie-Ray/apache-nginx-referral-spam-blacklist/pulls).