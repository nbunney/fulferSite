<?php
/**
* Class => News
* Site to get remove and save News items from FAROO
*/

class news  {

  public static function addTerm($dbh, $term){
    $stmt = $dbh->prepare("insert into newsTerm (term) values (:term)");
    $stmt->bindParam(':term', $term, PDO::PARAM_STR);
    $stmt->execute();
  }

  public static function delTerm($dbh, $termID){
    $stmt = $dbh->prepare("delete from newsTerm where id = :termID");
    $stmt->bindParam(':termID', $termID, PDO::PARAM_INT);
    $stmt->execute();
  }

  public static function getTerms($dbh){
    $stmt = $dbh->prepare("select * from newsTerm");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function getNews($dbh, $home=false, $page = 1){
    $limit = '';
    if ($home){
      $pageSize = 5;
      $startAt = $pageSize * ($page - 1);
      $limit = "limit $startAt, $pageSize";
    }
    $stat = ($home) ? " status = 1 " : " status = 0 ";
    $sql ="select *
             from news
            where $stat
              and newsDate >= (NOW() - INTERVAL 90 DAY)
           $limit";

    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $data;
  }

  public static function setNewsStat($dbh, $newsID, $status){
    $stmt = $dbh->prepare("update news set status=:status where id = :id");
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':id', $newsID, PDO::PARAM_STR);
    $stmt->execute();
  }

  public static function getNewBing($dbh, $key){
    $terms = self::getTerms($dbh);
    foreach($terms as $term){
      $response  = json_decode(self::getBing($key, $term['term']));
      foreach($response->d->results as $result){
        $date = substr($result->Date, 0, 10);
        $stmt = $dbh->prepare("insert into news (msdnID, newsDate, title, description, url, source)
                                      values (:msdnID, :newsDate, :title, :description, :url, :source)
                                   on duplicate key
                               update title=:title2");
        $stmt->bindParam(':msdnID', $result->ID, PDO::PARAM_STR);
        $stmt->bindParam(':newsDate', $date, PDO::PARAM_STR);
        $stmt->bindParam(':title', $result->Title, PDO::PARAM_STR);
        $stmt->bindParam(':title2', $result->Title, PDO::PARAM_STR);
        $stmt->bindParam(':description', $result->Description, PDO::PARAM_STR);
        $stmt->bindParam(':url', $result->Url, PDO::PARAM_STR);
        $stmt->bindParam(':source', $result->Source, PDO::PARAM_STR);
        $stmt->execute();
      }
    }
  }

  private static function getBing($key, $term){
    $acctKey = $key;
    $rootUri = 'https://api.datamarket.azure.com/Bing/Search';
    $query = $term;
    $serviceOp ='News';
    $market ='en-us';
    $query = urlencode("'$query'");
    $market = urlencode("'$market'");
    $requestUri = "$rootUri/$serviceOp?\$format=json&Query=$query&Market=$market";
    $auth = base64_encode("$acctKey:$acctKey");
    $data = array(
                'http' => array(
                            'request_fulluri' => true,
                            'ignore_errors' => true,
                            'header' => "Authorization: Basic $auth"
                            )
                );
    $context = stream_context_create($data);
    return file_get_contents($requestUri, 0, $context);
  }

}
