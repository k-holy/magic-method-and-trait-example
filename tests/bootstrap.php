<?php
error_reporting(E_ALL | E_STRICT | E_DEPRECATED);

$loader = include realpath(__DIR__ . '/../vendor/autoload.php');
$loader->add('Acme\\Test', __DIR__);
