<?php

ini_set('display_errors', 1);
error_reporting(E_ERROR | E_PARSE);

// Require composer dependencies
require __DIR__.'/../vendor/autoload.php';

// Instantiate the app
$app = require_once __DIR__.'/../app/start.php';

// AAAND run it!!!
$app->run();