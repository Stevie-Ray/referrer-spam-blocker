<h1 align="center">Referrer Spam Blocker Generator :robot:</h1>

<p align="center"><strong>Converts <em>domains.txt</em> to valid server configuration</strong></p>

<p align="center">Supported Servers: Apache, Nginx, IIS, uWSGI, Caddy & Varnish (+ Google Analytics segments)</p>


<br />

[![Latest Stable Version](https://img.shields.io/packagist/v/stevie-ray/referrer-spam-blocker)](https://packagist.org/packages/Stevie-Ray/referrer-spam-blocker)
[![Build Status](https://travis-ci.org/Stevie-Ray/referrer-spam-blocker.svg)](https://travis-ci.org/Stevie-Ray/referrer-spam-blocker)
[![Libraries.io dependency status for latest release](https://img.shields.io/librariesio/release/github/stevie-ray/referrer-spam-blocker)](https://libraries.io/github/Stevie-Ray/referrer-spam-blocker)
[![Code Quality](https://img.shields.io/scrutinizer/g/Stevie-Ray/referrer-spam-blocker)](https://scrutinizer-ci.com/g/Stevie-Ray/referrer-spam-blocker/?branch=master)
[![Packagist](https://img.shields.io/packagist/dt/Stevie-Ray/referrer-spam-blocker)](https://packagist.org/packages/stevie-ray/referrer-spam-blocker/stats)
[![License](https://img.shields.io/packagist/l/stevie-ray/referrer-spam-blocker)](https://packagist.org/packages/Stevie-Ray/referrer-spam-blocker)
- - - -

## Install

First, create a fork of the repository and copy the files to your local machine.

```sh
git clone https://github.com/yourname/referrer-spam-blocker.git
```

Install the [composer](https://getcomposer.org/) dependencies.

Tip: run these commands to globally install `composer` on your system:

```sh
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

Use the `composer` install in the root folder:

```sh
composer install
```


## Usage

In your CLI (or webbrowser) run the **run.php** file to generate the files.
This can be done using a local server like [MAMP](https://www.mamp.info/en/) or on a remote server.

```sh
php run.php
```

The new domains are now included in your local files. Commit and push your work to your repository and create a [pull request](https://github.com/Stevie-Ray/referrer-spam-blocker/pulls/).


## Contributing
 
If you'd like to add a new referrer spammer to the list, [click here to edit the domains.txt file](https://github.com/Stevie-Ray/referrer-spam-blocker/edit/master/src/domains.txt) and create a pull request. Alternatively you can create a [new issue](https://github.com/Stevie-Ray/referrer-spam-blocker/issues/new). In your issue or pull request please explain where the referrer domain appeared and why you think it is a spammer. **Please open one pull request per new domain**.
 
If you open a pull request, it is appreciated if you run the **run.php** file. It sorts the domains, creates the files and checks if somebody already reported the domain.


## Like it?

- [Buy me a beer](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4XC7KX75K6636) 🍺


