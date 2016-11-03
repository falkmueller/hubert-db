<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
//load autoloader
require_once '../vendor/autoload.php';
//init app
$app = new hubert\app();
$app->loadConfig(__dir__.'/config/');
//run and emit app
$app->emit($app->run());
