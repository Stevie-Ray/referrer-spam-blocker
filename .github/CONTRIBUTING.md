# Contributing
 
If you'd like to add a new referrer spammer to the list, [click here to edit the domains.txt file](https://github.com/Stevie-Ray/referrer-spam-blocker/edit/master/generator/domains.txt) and create a pull request. Alternatively you can create a [new issue](https://github.com/Stevie-Ray/referrer-spam-blocker/issues/new). In your issue or pull request please explain where the referrer domain appeared and why you think it is a spammer. **Please open one pull request per new domain**.
 
If you open a pull request, it is appreciated if you run the **generator/run.php** file. It sorts the domains, creates the Nginx and Apache files and checks if somebody already reported the domain.

## Using the generator

### Install

Install the [composer](https://getcomposer.org/) dependencies.

Run these commands to globally install `composer` on your system:

```sh
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

Use the `composer` install command in the `generator` folder:

```sh
cd generator
composer install
```

In your webbrowser run the **generator/run.php** file to generate the files.