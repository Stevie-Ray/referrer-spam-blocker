# Contributing
 
If you'd like to add a new referrer spammer to the list, [click here to edit the domains.txt file](https://github.com/Stevie-Ray/referrer-spam-blocker/edit/master/generator/domains.txt) and create a pull request. Alternatively you can create a [new issue](https://github.com/Stevie-Ray/referrer-spam-blocker/issues/new). In your issue or pull request please explain where the referrer domain appeared and why you think it is a spammer. **Please open one pull request per new domain**.
 
If you open a pull request, it is appreciated if you execute the **run.php** file with PHP. It sorts the domains, creates the Nginx and Apache files and checks if somebody already reported the domain.

## Using the generator

### Install

Requirements:

* PHP must be installed on your machine
* You must have installed [composer](https://getcomposer.org/), PHP's package manager

Install your dependencies:

```sh
composer install
```

Run the command to generate the files:

```
php run.php
```
