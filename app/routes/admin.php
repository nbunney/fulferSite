<?php
use Slim\Slim;
use Slim\Extras\Views\Twig as Twig;

$authenticateAdmin = function(\Slim\Route $route) use ($app) {
    if(empty($_SESSION['jwt'])){
      return $app->response()->redirect($app->urlFor('login'));
    }

    $jwt=$_SESSION['jwt'];
    $key = $app->config('JWT_KEY');
    try{
      $token = JWT::decode($jwt, $key);
    }catch(Exception $e){
      return $app->response()->redirect($app->urlFor('logout'));
    }
    $_SESSION['token'] = (array)$token;

    if (!$token->id) {
      return $app->response()->redirect($app->urlFor('login'));
    }

    if ($token->role < 2) { //Allow admin and super users in.
      return $app->response()->redirect($app->urlFor('home'));
    }
    return true;
};

/* Admin pages secured by authenticate middle ware */
$app->get('/admin/', $authenticateAdmin, function() use ($app) {
  return $app->response()->redirect($app->urlFor('adminsitemenu'));
});


$app->get('/admin/sitemenu/', $authenticateAdmin, function() use ($app) {
  $dbh = getConnection();
  $data['menu'] = sitemenu::getAdmin($dbh);
  if(!empty($_SESSION['token'])) $data = array_merge($data, $_SESSION['token']);
  $app->render('adminSiteMenu.html', $data);
})->name('adminsitemenu');

$app->get('/admin/sitemenu/load/:itemID', $authenticateAdmin, function($itemID) use ($app) {
  $dbh = getConnection();
  $data['item'] = sitemenu::getItem($dbh, $itemID);
  $data['templates'] = sitemenu::getTemplates($dbh);
  if(!empty($_SESSION['token'])) $data = array_merge($data, $_SESSION['token']);
  $app->render('adminSiteMenuShow.html', $data);
});

$app->post('/admin/sitemenu/create/', $authenticateAdmin, function() use ($app) {
  $dbh = getConnection();
  $itemData = $app->request->post();
  sitemenu::create($dbh, $itemData);
});

$app->post('/admin/sitemenu/sort/', $authenticateAdmin, function() use ($app) {
  $dbh = getConnection();
  $itemData = $app->request->post();
  $data['item'] = sitemenu::sort($dbh, $itemData);
});

$app->put('/admin/sitemenu/:itemID', $authenticateAdmin, function($itemID) use ($app) {
  $dbh = getConnection();
  $itemData = $app->request->put();
  sitemenu::updateItem($dbh, $itemID, $itemData);
});

$app->delete('/admin/sitemenu/:itemID', $authenticateAdmin, function($itemID) use ($app) {
  $dbh = getConnection();
  sitemenu::deleteItem($dbh, $itemID);
});

$app->get('/admin/sitemenu/setShow/:itemID/:show', $authenticateAdmin, function($itemID, $show) use ($app) {
  $dbh = getConnection();
  sitemenu::setShow($dbh, $itemID, $show);
  die();
});

$app->put('/admin/sitemenu/setHidden/:itemID/:hidden', $authenticateAdmin, function($itemID, $hidden) use ($app) {
  $dbh = getConnection();
  sitemenu::setHidden($dbh, $itemID, $hidden);
  die();
});

$app->post('/admin/sitemenu/setPic/:itemID', $authenticateAdmin, function($itemID) use ($app) {
  $dbh = getConnection();
  $userData = $app->request->post();
  sitemenu::setPic($dbh, $itemID, $userData);
  die();
});

$app->get('/admin/sitemenu/setSecure/:itemID/:secure', $authenticateAdmin, function($itemID, $secure) use ($app) {
  $dbh = getConnection();
  sitemenu::setSecure($dbh, $itemID, $secure);
  die();
});

$app->get('/admin/sitemenu/setMenuSide/:itemID/:side', $authenticateAdmin, function($itemID, $side) use ($app) {
  $dbh = getConnection();
  sitemenu::setMenuSide($dbh, $itemID, $side);
  die();
});

$app->get('/admin/users/', $authenticateAdmin, function() use ($app) {
  $dbh = getConnection();
  $data['users'] = users::getAdminUsers($dbh);
  if(is_array($_SESSION['token'])) $data = array_merge($data, $_SESSION['token']);
  $app->render('adminUsers.html', $data);
});

$app->get('/admin/users/load/:id', $authenticateAdmin, function($id) use ($app) {
  $dbh = getConnection();
  if (is_numeric($id)){
    $data['user'] = users::getInfo($dbh, $id);
  }else{
    $data['new'] = true;
  }
  $data['roles'] = users::getRoles($dbh);
  if(is_array($_SESSION['token'])) $data = array_merge($data, $_SESSION['token']);
  $app->render('adminUserShow.html', $data);
});

$app->get('/admin/users/setStat/:userID/:status', $authenticateAdmin, function($userID, $status) use ($app) {
  $dbh = getConnection();
  users::setStatus($dbh, $userID, $status);
  die();
});

$app->post('/admin/users/New/', $authenticateAdmin, function() use ($app) {
  $dbh = getConnection();
  $userData = $app->request->post();
  users::create($dbh, $userData);
  die();
});

$app->post('/admin/users/:userID/', $authenticateAdmin, function($userID) use ($app) {
  $dbh = getConnection();
  $userData = $app->request->post();
  users::update($dbh, $userID, $userData);
  die();
});

$app->get('/admin/users/setRole/:userID/:roleID/', $authenticateAdmin, function($userID, $roleID) use ($app) {
  $dbh = getConnection();
  users::setRole($dbh, $userID, $roleID);
  die();
});

$app->get('/admin/users/del/:userID/', $authenticateAdmin, function($userID) use ($app) {
  $dbh = getConnection();
  users::delete($dbh, $userID);
  die();
});

$app->post('/upload/:directory/', $authenticateAdmin, function($directory) use ($app) {
  $env = $app->environment();
  echo upload::handleUpload($env['baseDir'].'img/'.$directory.'/');
});
