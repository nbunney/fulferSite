<?php
/**
* Class => Menu
* CRUD for Menu Items that are for sale
*/
class menu  {

  public static function getByWeeks($dbh, $weeks){
    $out = [];
    foreach($weeks as $week){
      $data = self::getHome($dbh, $week['id']);
      $out[$week['id']] = $data['cats'];
    }
    return $out;
  }

	public static function getHome($dbh, $weekID=false){
  	if (!$weekID){
      $stmt = $dbh->prepare("select * from week where monday >= curdate() and stat = 1 order by monday asc limit 1");
    }else{
      $stmt = $dbh->prepare("select * from week where id = :weekID");
      $stmt->bindParam(':weekID', $weekID, PDO::PARAM_INT);
    }
    $stmt->execute();
    $wInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    $weekID = $wInfo['id'];

    $stmt = $dbh->prepare("select * from menuCategory where onMenu=1 order by sorder asc");
    $stmt->execute();
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($cats as $i=>$c){

      $stmt = $dbh->prepare("select m.*, w.weekID
                             from menu m,
                                  menuWeek w
                            where w.menuID = m.id and w.weekID = :weekID
                              and menuCategoryID = :catID
                              and m.status = 1
                            order by m.menuCategoryID, w.sOrder asc");
      $stmt->bindParam(':catID', $c['id'], PDO::PARAM_INT);
      $stmt->bindParam(':weekID', $weekID, PDO::PARAM_INT);
      $stmt->execute();
      $cats[$i]['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    $out['cats'] = $cats;
    $out['week'] = $wInfo;
    return $out;
	}


	public static function getCurrent($dbh, $all=false){
  	$where = ($all) ? '' : "where onMenu=1";
    $stmt = $dbh->prepare("select * from menuCategory $where order by sorder asc");
    $stmt->execute();
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($cats as $i=>$c){
      $stmt = $dbh->prepare("select * from menu where menuCategoryID = :id");
      $stmt->bindParam(':id', $c['id'], PDO::PARAM_INT);
      $stmt->execute();
      $cats[$i]['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return $cats;
	}

	public static function getAllCats($dbh){
    $stmt = $dbh->prepare("select * from menuCategory order by sorder asc");
    $stmt->execute();
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $cats;
	}

  public static function getCat($dbh, $id){
    $stmt = $dbh->prepare("select * from menuCategory where id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public static function getMenu($dbh, $id){
    $stmt = $dbh->prepare("select * from menu where id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public static function create($dbh, $itemData){
    extract($itemData);
    $stmt = $dbh->prepare("insert into menu (menuCategoryID, name) values (:menuCategoryID, :name)");
    $stmt->bindParam(':menuCategoryID', $menuCategoryID, PDO::PARAM_INT);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->execute();
    return $dbh->lastInsertId();
  }

  public static function update($dbh, $id, $itemData){
    extract($itemData);
    if (!$smallPrice) $smallPrice=0;
    if (!$mediumPrice) $mediumPrice=0;
    if (!$largePrice) $largePrice=0;
    if (!$feastPrice) $feastPrice=0;
    $stmt = $dbh->prepare("update menu set name=:name, subName=:subName, description=:description, sizeDescription=:sizeDescription, smallPrice=:smallPrice, mediumPrice=:mediumPrice, largePrice=:largePrice, feastPrice=:feastPrice where id=:id");
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':subName', $subName, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':sizeDescription', $sizeDescription, PDO::PARAM_STR);
    $stmt->bindParam(':smallPrice', $smallPrice, PDO::PARAM_STR);
    $stmt->bindParam(':mediumPrice', $mediumPrice, PDO::PARAM_STR);
    $stmt->bindParam(':largePrice', $largePrice, PDO::PARAM_STR);
    $stmt->bindParam(':feastPrice', $feastPrice, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
  }

  public static function updateImage($dbh, $id, $itemData){
    extract($itemData);
    $stmt = $dbh->prepare("update menu set image=:image where id=:id");
    $stmt->bindParam(':image', $image, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

  }

  public static function delete($dbh, $id){
    $stmt = $dbh->prepare("delete from menuWeek where menuID = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = $dbh->prepare("delete from menu where id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

  }


  public static function setType($dbh, $id, $type, $status){
    $type = preg_replace("/[^A-Za-z0-9]/", '',$type);
    $stmt = $dbh->prepare("update menu set $type = :status where id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->execute();
  }

  public static function setStatus($dbh, $id, $status){
    $stmt = $dbh->prepare("update menu set status = :status where id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->execute();
  }

  public static function updateCat($dbh, $id, $itemData){
    extract($itemData);
    $stmt = $dbh->prepare("update menuCategory set name=:name, subName=:subName where id=:id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':subName', $subName, PDO::PARAM_STR);
    $stmt->execute();
  }

  public static function setCatIcon($dbh, $id, $icon){
    $stmt = $dbh->prepare("update menuCategory set icon=:icon where id=:id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':icon', $icon, PDO::PARAM_STR);
    $stmt->execute();
  }

  public static function sortCats($dbh, $items){
    foreach($items as $i=>$c){
      $stmt = $dbh->prepare("update menuCategory set sorder=:i where id=:c");
      $stmt->bindParam(':i', $i, PDO::PARAM_INT);
      $stmt->bindParam(':c', $c, PDO::PARAM_INT);
      $stmt->execute();
    }
  }

  public static function createCat($dbh, $name){
    $stmt = $dbh->prepare("insert into menuCategory (name) values (:name)");
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->execute();
    return $dbh->lastInsertId();
  }

  public static function setDOpen($dbh, $id, $stat){
    $stmt = $dbh->prepare("update menuCategory set defaultOpen=:stat where id=:id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':stat', $stat, PDO::PARAM_INT);
    $stmt->execute();
  }

  public static function setOnMenu($dbh, $id, $stat){
    $stmt = $dbh->prepare("update menuCategory set onMenu=:stat where id=:id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':stat', $stat, PDO::PARAM_INT);
    $stmt->execute();
  }

  public static function makeWeeks($dbh){
    $dateFormat = 'Y-m-d';
    $start_date = date($dateFormat, strtotime('-6 days'));
    foreach(range(0,365) as $day) {
      $internalDate = strtotime("{$start_date} + {$day} days");
      if (date('D', $internalDate)=='Mon'){
        $monday = date($dateFormat, $internalDate);
        $stmt = $dbh->prepare("insert ignore into week (monday) values (:monday)");
        $stmt->bindParam(':monday', $monday, PDO::PARAM_STR);
        $stmt->execute();
      }
    }
  }

  public static function setPrices($dbh, $data){
    foreach($data as $i=>$p){
      list($catID, $field) = explode('_', $i);
      $p = ($p) ? $p : '0';
      $stmt = $dbh->prepare("insert into menuPrices (categoryID, $field) values (:catID, :value) on duplicate key update $field = :value2");
      $stmt->bindParam(':catID', $catID, PDO::PARAM_INT);
      $stmt->bindParam(':value', $p, PDO::PARAM_INT);
      $stmt->bindParam(':value2', $p, PDO::PARAM_INT);
      $stmt->execute();
    }
  }

  public static function getPrices($dbh){
    $stmt = $dbh->prepare("select * from menuPrices");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $out = [];

    if(!empty($data)) foreach($data as $p){
      $out[$p['categoryID']] = $p;
    }
    return $out;
  }
}
