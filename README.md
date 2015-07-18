apache-nginx-referral-spam-blacklist-block
==========================================

These files are created to collect and prevent referral spam traffic sources on a server level. 

NOTE:
This method doesn't stop the Analytics referral spam (because they are hitting Analytics directly and not even touching your website / server). You should also use filters in Analytics to prevent referral spam. 


## Other options for Google Analytics

If this method fails you can prevent referral spam by:

  * [Add a filter ](https://support.google.com/analytics/answer/1033162)
  * [Bot and Spider Filtering](https://plus.google.com/+GoogleAnalytics/posts/2tJ79CkfnZk) 

## Downloading

If you just want the blacklist, see the [latest release](https://github.com/Stevie-Ray/apache-nginx-referral-spam-blacklist-block/releases). Otherwise, if you need to make changes to the blacklist, clone the repo with:

```sh
git clone --recursive https://github.com/Stevie-Ray/apache-nginx-referral-spam-blacklist-block.git
```

## Nginx: referral-spam.conf usage

With `blacklist.conf` in `/etc/nginx`, include it globally from within `/etc/nginx/nginx.conf`:

```conf
http {
	include blacklist.conf;
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

## Apache: .htaccess usage
.htaccess is a configuration file for use on web servers running Apache. 
This file is usually found in the root “public_html” folder of your website. 

##  Contributing

If you'd like to help, [contribute feedback](https://github.com/Stevie-Ray/apache-nginx-referral-spam-blacklist-block/issues), or just fork the repository, then add useful stuff and send a [pull request](https://github.com/Stevie-Ray/apache-nginx-referral-spam-blacklist-block/pulls).