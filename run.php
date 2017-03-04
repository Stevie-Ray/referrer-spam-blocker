<?php

date_default_timezone_set('UTC');
require_once __DIR__ . '/vendor/autoload.php';

$generator = new StevieRay\Generator(__DIR__);
$generator->generateFiles();