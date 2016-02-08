<?php
/**
* Class => SiteMenu
* CRUD for Site Menu Items that are for sale
*/
class sitemenu  {

  public static function getByRoute($dbh, $routeName, $authenticated){
    $authSQL = ($authenticated) ? '' : ' and secure = 0';
    $stmt = $dbh->prepare("select * from siteMenu where route like :route $authSQL");
    $stmt->bindParam(':route', $routeName, PDO::PARAM_STR);
    $stmt->execute();
    if (!$data = $stmt->fetch(PDO::FETCH_ASSOC)){
      return false;
    }
    return $data;
  }

  public static function getItem($dbh, $itemID){
    $stmt = $dbh->prepare("select * from siteMenu where id=:id");
    $stmt->bindParam(':id', $itemID, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public static function create($dbh, $itemData){

    $stmt = $dbh->prepare("select * from siteMenu where route = ''");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($data as $p){
      $route = preg_replace("/[^A-Za-z0-9]/", '', $p['name']);
      $stmt = $dbh->prepare("update siteMenu set route=:route where id = :id");
      $stmt->bindParam(':id', $p['id'], PDO::PARAM_INT);
      $stmt->bindParam(':route', $route, PDO::PARAM_INT);
      $stmt->execute();

    }


    $stmt = $dbh->prepare("select max(sOrder) as sOrder from siteMenu");
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $sOrder = $data['sOrder']+1;

    extract($itemData);
    $route = preg_replace("/[^A-Za-z0-9]/", '', $name);
    $stmt = $dbh->prepare("insert into siteMenu (parentMenuID, name, h1Tag, titleTag, description, sOrder, route) values (:parentMenuID, :name, :h1Tag, :titleTag, :description, :sOrder, :route)");
    $stmt->bindParam(':parentMenuID', $parentMenuID, PDO::PARAM_INT);
    $stmt->bindParam(':sOrder', $sOrder, PDO::PARAM_INT);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':h1Tag', $name, PDO::PARAM_STR);
    $stmt->bindParam(':titleTag', $name, PDO::PARAM_STR);
    $stmt->bindParam(':description', $name, PDO::PARAM_STR);
    $stmt->bindParam(':route', $route, PDO::PARAM_STR);
    $stmt->execute();
  }

	public static function getAdmin($dbh, $start=0){
    $stmt = $dbh->prepare("select * from siteMenu where parentMenuID=:start order by sOrder");
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->execute();
    if($out = $stmt->fetchAll(PDO::FETCH_ASSOC)){
      foreach($out as $i=>$c){
        $out[$i]['submenu'] = self::getAdmin($dbh, $c['id']);
      }
      return $out;
    }
	}

	public static function get($dbh, $start=0, $authenticated=0){

  	$authSQL = ($authenticated) ? "" : " and secure=0 ";
    $stmt = $dbh->prepare("select * from siteMenu where parentMenuID=:start and hidden=0 $authSQL order by sOrder");
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->execute();
    if($out = $stmt->fetchAll(PDO::FETCH_ASSOC)){
      foreach($out as $i=>$c){
        if ($c['route']=='home') $out[$i]['route']='';
        $out[$i]['submenu'] = self::getAdmin($dbh, $c['id']);
      }
      return $out;
    }
	}

  public static function updateItem($dbh, $itemID, $itemData){
    extract($itemData);
    if(!isset($route)){
      $stmt = $dbh->prepare("select route from siteMenu where id = :id");
      $stmt->bindParam(':id', $itemID, PDO::PARAM_INT);
      $stmt->execute();
      $info = $stmt->fetch(PDO::FETCH_ASSOC);
      extract($info);
    }
    $stmt = $dbh->prepare("update siteMenu
                              set name=:name,
                                  subtext=:subtext,
                                  route=:route,
                                  h1Tag=:h1Tag,
                                  titleTag=:titleTag,
                                  description=:description,
                                  templateID=:templateID
                            where id=:id");
    $stmt->bindParam(':id', $itemID, PDO::PARAM_INT);
    $stmt->bindParam(':name', $name, PDO::PARAM_INT);
    $stmt->bindParam(':subtext', $subtext, PDO::PARAM_INT);
    $stmt->bindParam(':route', $route, PDO::PARAM_INT);
    $stmt->bindParam(':h1Tag', $h1Tag, PDO::PARAM_INT);
    $stmt->bindParam(':titleTag', $titleTag, PDO::PARAM_INT);
    $stmt->bindParam(':description', $description, PDO::PARAM_INT);
    $stmt->bindParam(':templateID', $templateID, PDO::PARAM_INT);
    $stmt->execute();
  }

  public static function deleteItem($dbh, $itemID){
    $stmt = $dbh->prepare("select id from siteMenu where parentMenuID=:id");
    $stmt->bindParam(':id', $itemID, PDO::PARAM_INT);
    $stmt->execute();
    $subs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($subs as $s){
      $stmt = $dbh->prepare("select id from siteMenu where parentMenuID=:id");
      $stmt->bindParam(':id', $s['id'], PDO::PARAM_INT);
      $stmt->execute();
      $subs2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($subs2 as $x){
        $stmt = $dbh->prepare("delete from siteMenu where id=:id");
        $stmt->bindParam(':id', $x['id'], PDO::PARAM_INT);
        $stmt->execute();
      }
      $stmt = $dbh->prepare("delete from siteMenu where id=:id");
      $stmt->bindParam(':id', $s['id'], PDO::PARAM_INT);
      $stmt->execute();
    }
    $stmt = $dbh->prepare("delete from siteMenu where id=:id");
    $stmt->bindParam(':id', $itemID, PDO::PARAM_INT);
    $stmt->execute();
  }

  public static function getTemplates($dbh){
    $stmt = $dbh->prepare("select * from template");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function getTemplate($dbh, $templateID){
    $stmt = $dbh->prepare("select name from template where id = $templateID");
    $stmt->execute();
    $t = $stmt->fetch(PDO::FETCH_ASSOC);
    return $t['name'].'.html';
  }

  public static function setPic($dbh, $itemID, $userData){
    dump($userData);
    extract($userData);
    $stmt = $dbh->prepare("update siteMenu set image=:image where id=:id");
    $stmt->bindParam(':id', $itemID, PDO::PARAM_INT);
    $stmt->bindParam(':image', $file, PDO::PARAM_INT);
    $stmt->execute();
  }

  public static function setShow($dbh, $itemID, $show){
    $stmt = $dbh->prepare("update siteMenu set showH1=:show where id=:id");
    $stmt->bindParam(':id', $itemID, PDO::PARAM_INT);
    $stmt->bindParam(':show', $show, PDO::PARAM_INT);
    $stmt->execute();
  }

  public static function setHidden($dbh, $itemID, $hidden){
    $stmt = $dbh->prepare("update siteMenu set hidden=:hidden where id=:id");
    $stmt->bindParam(':id', $itemID, PDO::PARAM_INT);
    $stmt->bindParam(':hidden', $hidden, PDO::PARAM_INT);
    $stmt->execute();
  }

  public static function setSecure($dbh, $itemID, $secure){
    $stmt = $dbh->prepare("update siteMenu set secure=:secure where id=:id");
    $stmt->bindParam(':id', $itemID, PDO::PARAM_INT);
    $stmt->bindParam(':secure', $secure, PDO::PARAM_INT);
    $stmt->execute();
  }

  public static function setMenuSide($dbh, $itemID, $side){
    $stmt = $dbh->prepare("update siteMenu set menuSide=:side where id=:id");
    $stmt->bindParam(':id', $itemID, PDO::PARAM_INT);
    $stmt->bindParam(':side', $side, PDO::PARAM_INT);
    $stmt->execute();
  }


  public static function sort($dbh, $itemData){
    extract($itemData);
    foreach($item as $i=>$id){
      echo "$i=>$id\n";
      $stmt = $dbh->prepare("update siteMenu set sOrder=:order where id=:id");
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      $stmt->bindParam(':order', $i, PDO::PARAM_INT);
      $stmt->execute();
    }
  }

}
