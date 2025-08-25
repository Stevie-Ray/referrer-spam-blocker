<?php

declare(strict_types=1);

date_default_timezone_set('UTC');
require_once __DIR__ . '/vendor/autoload.php';

use StevieRay\CLI\CommandLineInterface;

$cli = new CommandLineInterface();
exit($cli->run($argv));
