<?php
/**
* Class => Menu
* CRUD for Menu Items that are for sale
*/
class edits  {

	public static function get($dbh, $pageID, $role){
  	if ($role >= 2){
    	$editlevel = 'order by edit desc';
  	}else{
    	$editlevel = 'and edit = 0';
  	}
    $stmt = $dbh->prepare("select * from edit where siteMenuID = :pageID $editlevel");
    $stmt->bindParam(':pageID', $pageID, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $out = array();
    foreach($data as $p){
      if (array_key_exists($p['section'], $out)) continue;
      $out[$p['section']]=$p;
    }
    return $out;
	}

	public static function getButtons(){
	  $out['editButton'] = '<i class="editButton fa fa-edit pull-right"></i>';
    $out['finalEditButtons'] = '<i class="cancelButton fa fa-times text-danger pull-right"></i><i class="approveButton fa fa-check text-success pull-right"></i>';
    return $out;
	}

	public static function save($dbh, $postData){
  	extract($postData);
		if ($edit == -1){ //Cancel the edit
      $stmt = $dbh->prepare("delete from edit where siteMenuID = :pageID and section = :divID and edit=1");
      $stmt->bindParam(':pageID', $pageID, PDO::PARAM_INT);
      $stmt->bindParam(':divID', $divID, PDO::PARAM_STR);
      $stmt->execute();
		}elseif($edit == 2){ //Approve the edit
      $stmt = $dbh->prepare("select id from edit where siteMenuID = :pageID and section = :divID and edit=1");
      $stmt->bindParam(':pageID', $pageID, PDO::PARAM_INT);
      $stmt->bindParam(':divID', $divID, PDO::PARAM_STR);
      $stmt->execute();
      if ($data = $stmt->fetchAll(PDO::FETCH_ASSOC)){ //make sure that there is an edited version before we move it.
        $stmt = $dbh->prepare("delete from edit where siteMenuID = :pageID and section = :divID and edit=0");
        $stmt->bindParam(':pageID', $pageID, PDO::PARAM_INT);
        $stmt->bindParam(':divID', $divID, PDO::PARAM_STR);
        $stmt->execute();
        $stmt = $dbh->prepare("update edit set edit = 0 where siteMenuID = :pageID and section = :divID and edit=1");
        $stmt->bindParam(':pageID', $pageID, PDO::PARAM_INT);
        $stmt->bindParam(':divID', $divID, PDO::PARAM_STR);
        $stmt->execute();
      }

		}else{
      $stmt = $dbh->prepare("select id from edit where siteMenuID = :pageID and section = :divID and edit=1");
      $stmt->bindParam(':pageID', $pageID, PDO::PARAM_INT);
      $stmt->bindParam(':divID', $divID, PDO::PARAM_STR);
      $stmt->execute();
      if ($data = $stmt->fetchAll(PDO::FETCH_ASSOC)){ //if we already have an edit we remove it.
        $stmt = $dbh->prepare("delete from edit where siteMenuID = :pageID and section = :divID and edit=1");
        $stmt->bindParam(':pageID', $pageID, PDO::PARAM_INT);
        $stmt->bindParam(':divID', $divID, PDO::PARAM_STR);
        $stmt->execute();
      }
      $stmt = $dbh->prepare("insert into edit (siteMenuID, section, text, edit) values (:pageID, :divID, :text, 1)");
      $stmt->bindParam(':pageID', $pageID, PDO::PARAM_INT);
      $stmt->bindParam(':divID', $divID, PDO::PARAM_STR);
      $stmt->bindParam(':text', $contents, PDO::PARAM_STR);
      $stmt->execute();

    }
	}
}
