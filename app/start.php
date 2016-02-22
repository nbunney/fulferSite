<?php
session_start();
if (empty($_SESSION)) $_SESSION['id']=0;

date_default_timezone_set('America/Los_Angeles');
header('X-Frame-Options: SAMEORIGIN'); // FF 3.6.9+ Chrome 4.1+ IE 8+ Safari 4+ Opera 10.5+

ini_set('display_errors', 1);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$defaultConfig = array(
  'debug' => true,
  'routes.case_sensitive' => false,
  'view' => new \Slim\Views\Twig()
);

$config = array_merge($defaultConfig, require __DIR__.'/../.env.php');

//define('BASEURL', 'http://localhost:3000');

$app = new \Slim\Slim($config);


$app->view()->twigTemplateDirs = array('../app/templates', '../app/templates/base', '../app/templates/admin');
$twig = $app->view->getInstance();

$env = $app->environment();

require __DIR__.'/helpers/twigFilters.php';
require __DIR__.'/helpers/constants.php';
require __DIR__.'/helpers/db.php';

require __DIR__.'/routes/admin.php';
require __DIR__.'/routes/main.php';

require __DIR__.'/classes/class.sitemenu.php';
require __DIR__.'/classes/class.edits.php';
require __DIR__.'/classes/class.users.php';
require __DIR__.'/classes/class.upload.php';

$twig = $app->view()->getEnvironment();
$twig->addFunction('makeURL', new Twig_Function_Function('makeURL'));
$twig->addFunction('dump', new Twig_Function_Function('dump'));

return $app;