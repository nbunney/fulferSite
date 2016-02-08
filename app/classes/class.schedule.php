<?php
/**
* Class => Schedule
* CRUD for Menu Schedule
*/
class schedule  {

	public static function getWeek($dbh, $weekID){
    $stmt = $dbh->prepare("select m.*, w.weekID
                             from menu m left join menuWeek w on w.menuID = m.id and w.weekID = :weekID
                             where m.status = 1
                            order by m.menuCategoryID, w.sOrder asc");
    $stmt->bindParam(':weekID', $weekID, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($data as $i=>$p){
      $data[$i]['week']['id'] = $p['weekID'];
    }
    return $data;
	}

  public static function getWeekInfo($dbh, $id){
    $stmt = $dbh->prepare("select * from week where id=:id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public static function getWeeks($dbh, $show=false){
    if ($show){
      $showSQL = "and stat = 1";
      $showLimit = "limit 4";
    }
    $stmt = @$dbh->prepare("select * from week where monday > CURDATE() $showSQL and monday <= DATE_ADD(CURDATE(), INTERVAL 62 DAY) $showLimit");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($data as $p){
      $out[$p['id']]=$p;
    }
    return $out;
  }

  public static function setMenu($dbh, $weekID, $catID, $itemData){
    $stmt = $dbh->prepare("delete from menuWeek where weekID=:weekID and catID=:catID");
    $stmt->bindParam(':weekID', $weekID, PDO::PARAM_INT);
    $stmt->bindParam(':catID', $catID, PDO::PARAM_INT);
    $stmt->execute();

    extract($itemData);

    if(!empty($item)) foreach($item as $x=>$i){
      $stmt = $dbh->prepare("insert into menuWeek (weekID, catID, menuID, sOrder) values (:weekID, :catID, :menuID, :sOrder)");
      $stmt->bindParam(':weekID', $weekID, PDO::PARAM_INT);
      $stmt->bindParam(':catID', $catID, PDO::PARAM_INT);
      $stmt->bindParam(':menuID', $i, PDO::PARAM_INT);
      $stmt->bindParam(':sOrder', $x, PDO::PARAM_INT);
      $stmt->execute();
      echo "TEST $weekID, $catID, $i, $x";
    }

  }

  public static function setWeekStatus($dbh, $weekID, $status){
    $stmt = $dbh->prepare("update week set stat = :status where id = :weekID");
    $stmt->bindParam(':weekID', $weekID, PDO::PARAM_INT);
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    $stmt->execute();
  }

}
