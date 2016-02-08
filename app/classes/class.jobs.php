<?php
##############################
# Model Class to open positions
# Nathan Bunney
#
#
##############################


class jobs {

	##########
	# Public Methods
	##########

  public static function report($dbh){
    $stmt = $dbh->prepare("select DATE_FORMAT(cDate,'%b %Y') as cDate, jobType, count(*) as number
                             from appShort
                            group by DATE_FORMAT(cDate,'%b %Y'), jobType
                            order by YEAR(cDate) desc, MONTH(cDate) desc, jobType");
    $stmt->execute();

		$data = $stmt->fetch(PDO::FETCH_ASSOC);

    foreach($data as $p){
      if($p['jobType']=='') $p['jobType']='Other';
      if($p['cDate']=='') $p['cDate']='Before July 27, 2015';
      $out['short'][$p['cDate']][$p['jobType']]+=$p['number'];
    }


    $stmt = $dbh->prepare("select jt.name, DATE_FORMAT(a.appDate,'%b %Y') as cDate, count(*) as number
                             from application a,
                                  job j,
                                  jobType jt
                            where a.jobID = j.id
                              and j.jobTypeID = jt.id
                            group by jt.name, DATE_FORMAT(a.appDate,'%b %Y')
                            order by YEAR(a.appDate) desc, MONTH(a.appDate) desc, jt.name");
    $stmt->execute();
		$data = $stmt->fetch(PDO::FETCH_ASSOC);

    foreach($data as $p){
      switch($p['name']){
        case 'Full Time':
          $name = 'fulltime';
          break;
        case 'Part Time':
          $name = 'parttime';
          break;
        case 'Per Diem':
          $name = 'perdiem';
          break;
        default:
          $name = 'volunteer';

      }
      $out['full'][$p['cDate']][$name]=$p['number'];
    }

    return $out;
  }


	public static function get($dbh, $jobID=false) {
    $out = array();
	  if ($jobID>0){
  		$stmt = $dbh->prepare("select j.*, t.name jobType
                		           from job j,
                		                jobType t
                		          where j.jobTypeID = t.id
                		            and j.id = :jobID");
      $stmt->bindParam(':jobID', $jobID, PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_ASSOC);
	  }

	  if ($jobID==-1){ //This is for the admin area
  		$stmt = $dbh->prepare("select j.*, t.name jobType
                		           from job j,
                		                jobType t
                		          where j.jobTypeID = t.id
                		          order by j.active desc, j.name asc");
      $stmt->execute();
      $out = $stmt->fetchAll(PDO::FETCH_ASSOC);

  		if (!empty($data)) foreach($data as $p){
    		$out[$p['id']] = $p;
    	  $stmt = $dbh->prepare("select count(*) cnt from application where jobID = :jobID and signature is not NULL and stat=0");
        $stmt->bindParam(':jobID', $p['id'], PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);



    	  $out[$p['id']]['newApps'] = $res['cnt'];
  		}
  		return $out;
	  }

		$stmt = $dbh->prepare("select j.*, t.name jobType
              		           from job j,
              		                jobType t
              		          where j.jobTypeID = t.id
               		            and j.active = 1
               		            and j.openings > 0
              		            order by t.id, j.name");

    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if (!empty($data)) foreach($data as $p){
  		$out[$p['id']] = $p;
		}
		return $out;
	}

	public static function getTypes($dbh){
    $stmt = $dbh->prepare("select * from jobType");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($data as $p){
      $out[$p['id']] = $p;
    }
    return $out;

  }

  public static function getType($dbh, $typeID){
    $stmt = $dbh->prepare("select * from jobType where id = :typeID");
    $stmt->bindParam(':typeID', $typeID, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

	public static function add($dbh, $data){
	  //extract($data);

	  $stmt = $dbh->prepare("select max(sorder)+1 as sorder from job");
    $stmt->execute();
    $ret = $stmt->fetch(PDO::FETCH_ASSOC);

	  $nextOrder = $ret['sorder'];
	  if (!$nextOrder) $nextOrder = 1;

    $datePosted = date('Y-m-d');

  	$stmt = $dbh->prepare("insert into job (name, datePosted) values (:name, :datePosted)");
    $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
    $stmt->bindParam(':datePosted', $datePosted, PDO::PARAM_STR);

    $stmt->execute();

    return $dbh->lastInsertId();
	}

	public static function update($dbh, $jobID, $data){
  	$stmt = $dbh->prepare("update job
  	           set name=:name, location=:location, description=:description, requirements=:requirements, openings=:openings, jobTypeID=:jobTypeID, datePosted=:datePosted, cityState=:cityState
  	         where id = :jobID");

    $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
    $stmt->bindParam(':location', $data['location'], PDO::PARAM_STR);
    $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
    $stmt->bindParam(':requirements', $data['requirements'], PDO::PARAM_STR);
    $stmt->bindParam(':openings', $data['openings'], PDO::PARAM_STR);
    $stmt->bindParam(':jobTypeID', $data['jtID'], PDO::PARAM_STR);
    $stmt->bindParam(':datePosted', $data['datePosted'], PDO::PARAM_STR);
    $stmt->bindParam(':cityState', $data['cityState'], PDO::PARAM_STR);
    $stmt->bindParam(':jobID', $jobID, PDO::PARAM_INT);

    $stmt->execute();
	}

  public static function setActive($dbh, $jobID, $status){
    $stmt = $dbh->prepare("update job set active = :status where id = :jobID");
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':jobID', $jobID, PDO::PARAM_INT);
    $stmt->execute();
  }

  public static function remove($dbh, $jobID){
    $stmt = $dbh->prepare("update job set active = 0 where id = :jobID");
    $stmt->bindParam(':jobID', $jobID, PDO::PARAM_INT);
    $stmt->execute();
  }

	##########
	# Protected Methods
	##########


}
?>