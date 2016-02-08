<?php
/**
* Class => Orders
* CRUD for Customer Orders
*/
class orders  {

  public static function saveWeek($dbh, $userID, $weekID, $itemID, $itemData){
    $stmt = $dbh->prepare("delete from cart where userID = :userID and weekID = :weekID and menuID = :itemID");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->bindParam(':weekID', $weekID, PDO::PARAM_INT);
    $stmt->bindParam(':itemID', $itemID, PDO::PARAM_INT);
    $stmt->execute();
    extract($itemData);
    $small=(is_numeric($small)) ? $small : 0;
    $medium=(is_numeric($medium)) ? $medium : 0;
    $large=(is_numeric($large)) ? $large : 0;
    $feast=(is_numeric($feast)) ? $feast : 0;
    $stmt = $dbh->prepare("insert into cart (userID, weekID, menuID, small, medium, large, feast) values (:userID, :weekID, :itemID, :small, :medium, :large, :feast)");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->bindParam(':weekID', $weekID, PDO::PARAM_INT);
    $stmt->bindParam(':itemID', $itemID, PDO::PARAM_INT);
    $stmt->bindParam(':small', $small, PDO::PARAM_INT);
    $stmt->bindParam(':medium', $medium, PDO::PARAM_INT);
    $stmt->bindParam(':large', $large, PDO::PARAM_INT);
    $stmt->bindParam(':feast', $feast, PDO::PARAM_INT);
    $stmt->execute();
  }

  public static function getClientOrders($dbh, $userID, $weeks){
    $out=[];
    foreach($weeks as $week){
      $weekID = $week['id'];
      $stmt = $dbh->prepare("select * from cart where userID = :userID and weekID = :weekID");
      $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
      $stmt->bindParam(':weekID', $weekID, PDO::PARAM_INT);
      $stmt->execute();
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach($data as $p){
        $out[$weekID][$p['menuID']] = $p;
      }
    }
    return $out;
  }

  public static function getWeek($dbh, $userID, $weekID){
    $stmt = $dbh->prepare("select * from cart where userID = :userID and weekID = :weekID");
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->bindParam(':weekID', $weekID, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $out=[];

    foreach($data as $p){
      $out[$p['menuID']] = $p;
    }

    return $out;
  }

}
