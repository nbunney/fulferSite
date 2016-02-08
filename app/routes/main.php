<?php
use Slim\Slim;
use Slim\Extras\Views\Twig as Twig;

$authenticate = function(\Slim\Route $route) use ($app) {
  $env = $app->environment();
  if(empty($_SESSION['jwt'])){
    $env['authenticated'] = false;
    return true;
  }

  $jwt=$_SESSION['jwt'];
  $key = $app->config('JWT_KEY');
  try{
    $token = JWT::decode($jwt, $key);
  }catch(Exception $e){
    return $app->response()->redirect($app->urlFor('logout'));
  }

  $env['authenticated'] = true;
  $env['token'] = (array)$token;
  return true;
};

function mergeEnv($data, $route){
  $app = Slim::getInstance();
  $env = $app->environment();
  $dbh = getConnection();
  $data['menu'] = sitemenu::get($dbh, 0, $env['authenticated']);
  if(!$data['pageInfo'] = sitemenu::getByRoute($dbh, $route, $env['authenticated'])){
    $app->render('notfound.html', $data);
  }
  if($env['authenticated']) $data = array_merge($env['token'], $data);
  if($env['token']['role'] >=2) $data['allowEdit']=true;
  $data['edits'] = edits::get($dbh, $data['pageInfo']['id'], $env['token']['role']);
  $data['buttons'] = edits::getButtons();
  $data['SITE_PATH'] = BASEURL;
  return $data;
}


/* Basic Routes are below here */
$app->get('/', $authenticate, function() use ($app){
  $data=[];
  $dbh = getConnection();
  $data = mergeEnv($data, 'home');
//dump($data); die();
  $app->render('home.html', $data);
})->name('home');



$app->post('/editSave', function() use ($app) {
  $dbh = getConnection();
  $editData = $app->request->post();
  edits::save($dbh, $editData);
});

$app->get('/login', function() use ($app) { //This should only flow over HTTPS connections as the password is coming in plain text.
  $data=[];
  $dbh = getConnection();
  $data = mergeEnv($data, 'home');
  $app->render('login.html', $data);
})->name('login');


$app->post('/login', function() use ($app) { //This should only flow over HTTPS connections as the password is coming in plain text.
  $userData = $app->request->post();
  $dbh = getConnection();
  users::login($dbh, $userData);
  $app->redirect($app->urlFor('home'));
});

$app->get('/logout', function() use ($app) {
  if (!empty($_SESSION['jwt'])) unset($_SESSION['jwt']);
  if (!empty($_SESSION['token'])) unset ($_SESSION['token']);
  $app->redirect($app->urlFor('home'));
})->name('logout');

$app->get('(/:page+)', $authenticate, function ($page) use ($app) {
  $data=array();
  $dbh = getConnection();
  $data = mergeEnv($data, $page[0]);
  if ($data['pageInfo']['templateID']){
    $template = sitemenu::getTemplate($dbh, $data['pageInfo']['templateID']);
  }else{
    $app->render('notfound.html', $data);
    die();
  }
  $app->render($template, $data);
});


