apache-nginx-referral-spam-blacklist
====================================

These files are created to collect and prevent referral spam traffic sources on a server level. 

- - - -

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

## Options for Google Analytics 'ghost' spam
**NOTE:**
This method doesn't stop the Google Analytics **'ghost'** referral spam (because they are hitting Analytics directly and not even touching your website / server). You should also use filters in Analytics to prevent referral spam. 

You can prevent referral spam by:

  * [Adding a filter](https://support.google.com/analytics/answer/1033162)
  * [Enabeling bot and Spider Filtering](https://plus.google.com/+GoogleAnalytics/posts/2tJ79CkfnZk) 
  * [Adding a segment](https://www.google.com/analytics/gallery/#posts/search/%3F_.term%3Dspam%26_.start%3D0%26_.count%3D250%26_.viewId%3DeA5T2yD9TeOkCdY1zzFm0A/), the best (temporary) solution, there getting more aggressive.
  
Use the link above or go to your Google Analytics Admin panel and add a Segment.
  
![screen shot 2015-07-27 at 20 50 08](https://cloud.githubusercontent.com/assets/5747715/8914771/6a3a32a8-34a1-11e5-86ee-315a89fd5058.png)
  

Filter | Session | **Include**
------------ | ------------- | -------------
Hostname | matches regex | ```your-website.com|www.your-website.com|other-possible-way.com|googleusercontent.com ]```

Filter | Session | **Exclude**
------------ | ------------- | -------------
Hostname | matches regex |```semalt.com|anticrawler.org|best-seo-offer|best-seo-solution|7makemoneyonline|-musicas*-gratis|kambasoft|savetubevideo|ranksonic|success-seo|medispainstitute|offers.bycontext|100dollars-seo|buttons-for-website|buttons-for-your-website|sitevaluation|semaltmedia|videos-for-your-business|www.Get-Free-Traffic-Now.com|maxthon.com```

 and so on..

## Downloading

If you want to download both files, see the [latest zip](https://github.com/Stevie-Ray/apache-nginx-referral-spam-blacklist/archive/master.zip). Otherwise, if you need to make changes to these files, clone the repo with:

```sh
git clone --recursive https://github.com/Stevie-Ray/apache-nginx-referral-spam-blacklist-block.git
```

##  Contributing

If you'd like to help, [contribute feedback](https://github.com/Stevie-Ray/apache-nginx-referral-spam-blacklist/issues), or just fork the repository, then add useful stuff and send a [pull request](https://github.com/Stevie-Ray/apache-nginx-referral-spam-blacklist/pulls).