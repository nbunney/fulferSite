<?php

##############################
# Courses Class to get and set info for golf courses
# Nathan Bunney Oct 24, 2012
#
#
##############################

use com\tecnick\tcpdf;

class theApp {
  public $page=0;
}

$theApp = new theApp();

class application {

	##########
	# Public Methods
	##########

  public static function create($dbh, $jobID){
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
  	$stmt = $dbh->prepare("insert into application (jobID, appDate, ipAddress) values (:jobID, now(), :ipAddress)");
    $stmt->bindParam(':jobID', $jobID, PDO::PARAM_INT);
    $stmt->bindParam(':ipAddress', $ip, PDO::PARAM_STR);
    $stmt->execute();
    return $dbh->lastInsertId();
  }

  public static function searchApps($name){
    if (strlen($name)<2) return array();
    $nameSearch = "%$name%";
  	$stmt = $dbh->prepare("select *
  	                         from application
  	                        where firstname like :name1
  	                           or lastname like :name2
  	                           or middlename like :name3
  	                           or email like :name4
  	                        order by appDate desc
  	                        limit 300");

    $stmt->bindParam(':name1', $nameSearch, PDO::PARAM_STR);
    $stmt->bindParam(':name2', $nameSearch, PDO::PARAM_STR);
    $stmt->bindParam(':name3', $nameSearch, PDO::PARAM_STR);
    $stmt->bindParam(':name4', $nameSearch, PDO::PARAM_STR);
    $stmt->execute();

  	return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function getJobListWithApps($dbh){
		$stmt = $dbh->prepare("select j.*, t.name jobType, count(a.id) as tot, count(b.id) as new
              		           from job j,
              		                jobType t,
              		                application a,
              		                application b
              		          where j.jobTypeID = t.id
               		            and j.active = 1
               		            and j.openings > 0
               		            and a.jobID = j.id
               		            and b.status != -1 /* Not Rejected */
               		            and b.jobID = j.id
               		            and b.status = 0   /* New */
              		            group by t.id, j.name");

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

	public static function getByJobs($dbh, $jobID = false){
	  if ($jobID){
    	$stmt = $dbh->prepare("select * from application where jobID = :jobID and signature is not NULL order by appDate desc");
      $stmt->bindParam(':jobID', $jobID, PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
	  }

  	$stmt = $dbh->prepare("select id, jobID, appDate, lastname, firstname, middlename, othername from application where signature is not NULL order by appDate desc");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
  	foreach($data as $i=>$p){
      //$p['socialnumber'] = $this->decrypt($p['socialnumber']);
    	$out[$p['jobID']][] = $p;
    	unset($data[$i]);
  	}
  	return $out;
	}

	public static function status($dbh, $appID, $status){
  	$stmt = $dbh->prepare("update application set status = :status where id = :appID");
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':appID', $appID, PDO::PARAM_STR);
    $stmt->execute();
	}

	public static function get($dbh, $appID){
	  if (!$appID) return array();
  	$stmt = $dbh->prepare("select a.*, j.jobTypeID, j.name jobName from application a, job j where j.id = a.jobID and a.id = :appID");
    $stmt->bindParam(':appID', $appID, PDO::PARAM_STR);
    $stmt->execute();
  	return $stmt->fetch(PDO::FETCH_ASSOC);
	}


	public static function saveResume($dbh, $appID, $filename){
   	$stmt = $dbh->prepare("update application set resumefile=:filename where id = :appID");
    $stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
    $stmt->bindParam(':appID', $appID, PDO::PARAM_STR);
    $stmt->execute();
	}

	public static function saveFile($dbh, $appID, $filename){
   	$stmt = $dbh->prepare("update application set otherfile=:filename where id = :appID");
    $stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
    $stmt->bindParam(':appID', $appID, PDO::PARAM_STR);
    $stmt->execute();
	}

	public static function update($dbh, $appID, $stepID, $dir, $data=array()){
  	switch ($stepID){
  	  case 1:
  	    $state = substr($data['state'], 0, 2);
      	$stmt = $dbh->prepare("update application set firstname=:firstname,
      	                                              lastname=:lastname,
      	                                              middlename=:middlename,
      	                                              othername=:othername,
      	                                              referral=:referral,
      	                                              address=:address,
      	                                              city=:city,
      	                                              state=:state,
      	                                              zip=:zip,
      	                                              friendsRelatives=:friendsRelatives,
      	                                              phone=:phone,
      	                                              email=:email,
      	                                              dateStart=:dateStart,
      	                                              expectedPay=:expectedPay,
      	                                              employeedNow=:employeedNow,
      	                                              contactEmployer=:contactEmployer,
      	                                              workFullTime=:workFullTime,
      	                                              workPartTime=:workPartTime,
      	                                              workSeasonal=:workSeasonal,
      	                                              haveYouWorkPreviously=:haveYouWorkPreviously,
      	                                              workedHereWhen=:workedHereWhen,
      	                                              appliedHere=:appliedHere,
      	                                              appliedHereWhen=:appliedHereWhen
      	                                        where id = :appID");

        $stmt->bindParam(':firstname', $data['firstname'], PDO::PARAM_STR);
        $stmt->bindParam(':lastname', $data['lastname'], PDO::PARAM_STR);
        $stmt->bindParam(':middlename', $data['middlename'], PDO::PARAM_STR);
        $stmt->bindParam(':othername', $data['othername'], PDO::PARAM_STR);
        $stmt->bindParam(':referral', $data['referral'], PDO::PARAM_STR);
        $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);
        $stmt->bindParam(':city', $data['city'], PDO::PARAM_STR);
        $stmt->bindParam(':state', $state, PDO::PARAM_STR);
        $stmt->bindParam(':zip', $data['zip'], PDO::PARAM_STR);
        $stmt->bindParam(':friendsRelatives', $data['friendsRelatives'], PDO::PARAM_STR);
        $stmt->bindParam(':phone', $data['phone'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindParam(':dateStart', $data['dateStart'], PDO::PARAM_STR);
        $stmt->bindParam(':expectedPay', $data['expectedPay'], PDO::PARAM_STR);
        $stmt->bindParam(':employeedNow', $data['employeedNow'], PDO::PARAM_STR);
        $stmt->bindParam(':contactEmployer', $data['contactEmployer'], PDO::PARAM_STR);
        $stmt->bindParam(':workFullTime', $data['workFullTime'], PDO::PARAM_STR);
        $stmt->bindParam(':workPartTime', $data['workPartTime'], PDO::PARAM_STR);
        $stmt->bindParam(':workSeasonal', $data['workSeasonal'], PDO::PARAM_STR);
        $stmt->bindParam(':haveYouWorkPreviously', $data['haveYouWorkPreviously'], PDO::PARAM_STR);
        $stmt->bindParam(':workedHereWhen', $data['workedHereWhen'], PDO::PARAM_STR);
        $stmt->bindParam(':appliedHere', $data['appliedHere'], PDO::PARAM_STR);
        $stmt->bindParam(':appliedHereWhen', $data['appliedHereWhen'], PDO::PARAM_STR);
        $stmt->bindParam(':appID', $appID, PDO::PARAM_STR);

        break;
  	  case 2:
  	    $highschoolyears = intval($data['highschoolyears']);
  	    $collegeuniversityyearscompleted = intval($data['collegeuniversityyearscompleted']);
  	    $othercollegeyears = intval($data['othercollegeyears']);
      	$stmt = $dbh->prepare("update application set highestgrade=:highestgrade,
                      	                              highschool=:highschool,
                      	                              highschoolloc=:highschoolloc,
                      	                              highschoolyears=:highschoolyears,
                      	                              coursesubject=:coursesubject,
                      	                              coursesubjectgraduated=:coursesubjectgraduated,
                      	                              collegeuniversity=:collegeuniversity,
                      	                              collegeuniversityloc=:collegeuniversityloc,
                      	                              collegeuniversityyearscompleted=:collegeuniversityyearscompleted,
                      	                              collegeuniversitycourse=:collegeuniversitycourse,
                      	                              collegegraduated=:collegegraduated,
                      	                              collegegraduatedMonth=:collegegraduatedMonth,
                      	                              collegegraduatedYear=:collegegraduatedYear,
                      	                              othercollege=:othercollege,
                      	                              othercollegeloc=:othercollegeloc,
                      	                              othercollegeyears=:othercollegeyears,
                      	                              othercollegecourse=:othercollegecourse,
                      	                              othercollegecompleted=:othercollegecompleted,
                      	                              othercollegecompletedMonth=:othercollegecompletedMonth,
                      	                              othercollegecompletedYear=:othercollegecompletedYear,
                      	                              describeRelevantTraining=:describeRelevantTraining,
                      	                              describeHonorsReceived=:describeHonorsReceived,
                      	                              listProfessionalActivities=:listProfessionalActivities,
                      	                              language=:language,
                      	                              languagespeak=:languagespeak,
                      	                              languageread=:languageread,
                      	                              languagewrite=:languagewrite,
                      	                              language2=:language2,
                      	                              language2speak=:language2speak,
                      	                              language2read=:language2read,
                      	                              language2write=:language2write,
                      	                              language3=:language3,
                      	                              language3speak=:language3speak,
                      	                              language3read=:language3read,
                      	                              language3write=:language3write,
                      	                              military=:military,
                      	                              militaryRank=:militaryRank,
                      	                              DLState=:DLState,
                      	                              DLNumber=:DLNumber,
                      	                              DLExpiration=:DLExpiration,
                      	                              DrivingRestrictions=:DrivingRestrictions,
                      	                              nonCompeteAgreement=:nonCompeteAgreement,
                      	                              over18=:over18,
                      	                              commitedCrime=:commitedCrime,
                      	                              commitedCrimeExplain=:commitedCrimeExplain,
                      	                              awaitingTrial=:awaitingTrial,
                      	                              explainTrial=:explainTrial,
                      	                              actofViolence=:actofViolence,
                      	                              illegaldrugsWhy=:illegaldrugsWhy,
                      	                              usedIllegalDrugs=:usedIllegalDrugs,
                      	                              abletoperformduties=:abletoperformduties
                      	                        where id = :appID");

        $stmt->bindParam(':highestgrade', $data['highestgrade'], PDO::PARAM_STR);
        $stmt->bindParam(':highschool', $data['highschool'], PDO::PARAM_STR);
        $stmt->bindParam(':highschoolloc', $data['highschoolloc'], PDO::PARAM_STR);
        $stmt->bindParam(':highschoolyears', $highschoolyears, PDO::PARAM_STR);
        $stmt->bindParam(':coursesubject', $data['coursesubject'], PDO::PARAM_STR);
        $stmt->bindParam(':coursesubjectgraduated', $data['coursesubjectgraduated'], PDO::PARAM_STR);
        $stmt->bindParam(':collegeuniversity', $data['collegeuniversity'], PDO::PARAM_STR);
        $stmt->bindParam(':collegeuniversityloc', $data['collegeuniversityloc'], PDO::PARAM_STR);
        $stmt->bindParam(':collegeuniversityyearscompleted', $collegeuniversityyearscompleted, PDO::PARAM_STR);
        $stmt->bindParam(':collegeuniversitycourse', $data['collegeuniversitycourse'], PDO::PARAM_STR);
        $stmt->bindParam(':collegegraduated', $data['collegegraduated'], PDO::PARAM_STR);
        $stmt->bindParam(':collegegraduatedMonth', $data['collegegraduatedMonth'], PDO::PARAM_STR);
        $stmt->bindParam(':collegegraduatedYear', $data['collegegraduatedYear'], PDO::PARAM_STR);
        $stmt->bindParam(':othercollege', $data['othercollege'], PDO::PARAM_STR);
        $stmt->bindParam(':othercollegeloc', $data['othercollegeloc'], PDO::PARAM_STR);
        $stmt->bindParam(':othercollegeyears', $othercollegeyears, PDO::PARAM_STR);
        $stmt->bindParam(':othercollegecourse', $data['othercollegecourse'], PDO::PARAM_STR);
        $stmt->bindParam(':othercollegecompleted', $data['othercollegecompleted'], PDO::PARAM_STR);
        $stmt->bindParam(':othercollegecompletedMonth', $data['othercollegecompletedMonth'], PDO::PARAM_STR);
        $stmt->bindParam(':othercollegecompletedYear', $data['othercollegecompletedYear'], PDO::PARAM_STR);
        $stmt->bindParam(':describeRelevantTraining', $data['describeRelevantTraining'], PDO::PARAM_STR);
        $stmt->bindParam(':describeHonorsReceived', $data['describeHonorsReceived'], PDO::PARAM_STR);
        $stmt->bindParam(':listProfessionalActivities', $data['listProfessionalActivities'], PDO::PARAM_STR);
        $stmt->bindParam(':language', $data['language'], PDO::PARAM_STR);
        $stmt->bindParam(':languagespeak', $data['languagespeak'], PDO::PARAM_STR);
        $stmt->bindParam(':languageread', $data['languageread'], PDO::PARAM_STR);
        $stmt->bindParam(':languagewrite', $data['languagewrite'], PDO::PARAM_STR);
        $stmt->bindParam(':language2', $data['language2'], PDO::PARAM_STR);
        $stmt->bindParam(':language2speak', $data['language2speak'], PDO::PARAM_STR);
        $stmt->bindParam(':language2read', $data['language2read'], PDO::PARAM_STR);
        $stmt->bindParam(':language2write', $data['language2write'], PDO::PARAM_STR);
        $stmt->bindParam(':language3', $data['language3'], PDO::PARAM_STR);
        $stmt->bindParam(':language3speak', $data['language3speak'], PDO::PARAM_STR);
        $stmt->bindParam(':language3read', $data['language3read'], PDO::PARAM_STR);
        $stmt->bindParam(':language3write', $data['language3write'], PDO::PARAM_STR);
        $stmt->bindParam(':military', $data['military'], PDO::PARAM_STR);
        $stmt->bindParam(':militaryRank', $data['militaryRank'], PDO::PARAM_STR);
        $stmt->bindParam(':DLState', $data['DLState'], PDO::PARAM_STR);
        $stmt->bindParam(':DLNumber', $data['DLNumber'], PDO::PARAM_STR);
        $stmt->bindParam(':DLExpiration', $data['DLExpiration'], PDO::PARAM_STR);
        $stmt->bindParam(':DrivingRestrictions', $data['DrivingRestrictions'], PDO::PARAM_STR);
        $stmt->bindParam(':nonCompeteAgreement', $data['nonCompeteAgreement'], PDO::PARAM_STR);
        $stmt->bindParam(':over18', $data['over18'], PDO::PARAM_STR);
        $stmt->bindParam(':commitedCrime', $data['commitedCrime'], PDO::PARAM_STR);
        $stmt->bindParam(':commitedCrimeExplain', $data['commitedCrimeExplain'], PDO::PARAM_STR);
        $stmt->bindParam(':awaitingTrial', $data['awaitingTrial'], PDO::PARAM_STR);
        $stmt->bindParam(':explainTrial', $data['explainTrial'], PDO::PARAM_STR);
        $stmt->bindParam(':actofViolence', $data['actofViolence'], PDO::PARAM_STR);
        $stmt->bindParam(':usedIllegalDrugs', $data['usedIllegalDrugs'], PDO::PARAM_STR);
        $stmt->bindParam(':illegaldrugsWhy', $data['illegaldrugsWhy'], PDO::PARAM_STR);
        $stmt->bindParam(':abletoperformduties', $data['abletoperformduties'], PDO::PARAM_STR);
        $stmt->bindParam(':appID', $appID, PDO::PARAM_STR);

      	break;
      case 3:
      $stmt = $dbh->prepare("update application
                                set companyname1 = :companyname1,
                                    companyaddress1 = :companyaddress1,
                                    companyphone1 = :companyphone1,
                                    datestarted1 = :datestarted1,
                                    dateended1 = :dateended1,
                                    startwages1 = :startwages1,
                                    startposition1 = :startposition1,
                                    endedwages1 = :endedwages1,
                                    endedposition1 = :endedposition1,
                                    supervisorname1 = :supervisorname1,
                                    maywecontact1 = :maywecontact1,
                                    responsibilities1 = :responsibilities1,
                                    reasonforleaving1 = :reasonforleaving1,
                                    companyname2 = :companyname2,
                                    companyaddress2 = :companyaddress2,
                                    companyphone2 = :companyphone2,
                                    datestarted2 = :datestarted2,
                                    dateended2 = :dateended2,
                                    startwages2 = :startwages2,
                                    startposition2 = :startposition2,
                                    endedwages2 = :endedwages2,
                                    endedposition2 = :endedposition2,
                                    supervisorname2 = :supervisorname2,
                                    maywecontact2 = :maywecontact2,
                                    responsibilities2 = :responsibilities2,
                                    reasonforleaving2 = :reasonforleaving2,
                                    companyname3 = :companyname3,
                                    companyaddress3 = :companyaddress3,
                                    companyphone3 = :companyphone3,
                                    datestarted3 = :datestarted3,
                                    dateended3 = :dateended3,
                                    startwages3 = :startwages3,
                                    startposition3 = :startposition3,
                                    endedwages3 = :endedwages3,
                                    endedposition3 = :endedposition3,
                                    supervisorname3 = :supervisorname3,
                                    maywecontact3 = :maywecontact3,
                                    responsibilities3 = :responsibilities3,
                                    reasonforleaving3 = :reasonforleaving3,
                                    employmentGaps = :employmentGaps
                              where id = :appID");

        $stmt->bindParam(':companyname1', $data['companyname1'], PDO::PARAM_STR);
        $stmt->bindParam(':companyaddress1', $data['companyaddress1'], PDO::PARAM_STR);
        $stmt->bindParam(':companyphone1', $data['companyphone1'], PDO::PARAM_STR);
        $stmt->bindParam(':datestarted1', $data['datestarted1'], PDO::PARAM_STR);
        $stmt->bindParam(':dateended1', $data['dateended1'], PDO::PARAM_STR);
        $stmt->bindParam(':startwages1', $data['startwages1'], PDO::PARAM_STR);
        $stmt->bindParam(':startposition1', $data['startposition1'], PDO::PARAM_STR);
        $stmt->bindParam(':endedwages1', $data['endedwages1'], PDO::PARAM_STR);
        $stmt->bindParam(':endedposition1', $data['endedposition1'], PDO::PARAM_STR);
        $stmt->bindParam(':supervisorname1', $data['supervisorname1'], PDO::PARAM_STR);
        $stmt->bindParam(':maywecontact1', $data['maywecontact1'], PDO::PARAM_STR);
        $stmt->bindParam(':responsibilities1', $data['responsibilities1'], PDO::PARAM_STR);
        $stmt->bindParam(':reasonforleaving1', $data['reasonforleaving1'], PDO::PARAM_STR);
        $stmt->bindParam(':companyname2', $data['companyname2'], PDO::PARAM_STR);
        $stmt->bindParam(':companyaddress2', $data['companyaddress2'], PDO::PARAM_STR);
        $stmt->bindParam(':companyphone2', $data['companyphone2'], PDO::PARAM_STR);
        $stmt->bindParam(':datestarted2', $data['datestarted2'], PDO::PARAM_STR);
        $stmt->bindParam(':dateended2', $data['dateended2'], PDO::PARAM_STR);
        $stmt->bindParam(':startwages2', $data['startwages2'], PDO::PARAM_STR);
        $stmt->bindParam(':startposition2', $data['startposition2'], PDO::PARAM_STR);
        $stmt->bindParam(':endedwages2', $data['endedwages2'], PDO::PARAM_STR);
        $stmt->bindParam(':endedposition2', $data['endedposition2'], PDO::PARAM_STR);
        $stmt->bindParam(':supervisorname2', $data['supervisorname2'], PDO::PARAM_STR);
        $stmt->bindParam(':maywecontact2', $data['maywecontact2'], PDO::PARAM_STR);
        $stmt->bindParam(':responsibilities2', $data['responsibilities2'], PDO::PARAM_STR);
        $stmt->bindParam(':reasonforleaving2', $data['reasonforleaving2'], PDO::PARAM_STR);
        $stmt->bindParam(':companyname3', $data['companyname3'], PDO::PARAM_STR);
        $stmt->bindParam(':companyaddress3', $data['companyaddress3'], PDO::PARAM_STR);
        $stmt->bindParam(':companyphone3', $data['companyphone3'], PDO::PARAM_STR);
        $stmt->bindParam(':datestarted3', $data['datestarted3'], PDO::PARAM_STR);
        $stmt->bindParam(':dateended3', $data['dateended3'], PDO::PARAM_STR);
        $stmt->bindParam(':startwages3', $data['startwages3'], PDO::PARAM_STR);
        $stmt->bindParam(':startposition3', $data['startposition3'], PDO::PARAM_STR);
        $stmt->bindParam(':endedwages3', $data['endedwages3'], PDO::PARAM_STR);
        $stmt->bindParam(':endedposition3', $data['endedposition3'], PDO::PARAM_STR);
        $stmt->bindParam(':supervisorname3', $data['supervisorname3'], PDO::PARAM_STR);
        $stmt->bindParam(':maywecontact3', $data['maywecontact3'], PDO::PARAM_STR);
        $stmt->bindParam(':responsibilities3', $data['responsibilities3'], PDO::PARAM_STR);
        $stmt->bindParam(':reasonforleaving3', $data['reasonforleaving3'], PDO::PARAM_STR);
        $stmt->bindParam(':employmentGaps', $data['employmentGaps'], PDO::PARAM_STR);
        $stmt->bindParam(':appID', $appID, PDO::PARAM_STR);


        break;
      case 4:
        //Move the resume file if it exists.
        if ($_FILES['resume']['name'] > ''){
          $nameArray=explode('.', $_FILES['resume']['name']);
          $ext = strtolower(end($nameArray));
          if ($ext == 'pdf' or $ext == 'doc' or $ext == 'docx'){
            $newFileName = "Resume-".$appID.".".$ext;
            move_uploaded_file($_FILES['resume']['tmp_name'], $dir.$newFileName);
            self::saveResume($dbh, $appID, $newFileName);
          }
        }

        if ($_FILES['otherfile']['name'] > ''){
          $nameArray=explode('.', $_FILES['otherfile']['name']);
          $ext = strtolower(end($nameArray));
          if ($ext == 'pdf' or $ext == 'doc' or $ext == 'docx'){
            $newFileName = "File-".$appID.".".$ext;
            move_uploaded_file($_FILES['otherfile']['tmp_name'], $dir.$newFileName);
            self::saveFile($dbh, $appID, $newFileName);
          }
        }

        //move the other file if it exists.

        $stmt = $dbh->prepare("update application
                                  set Reference1Name=:Reference1Name,
                                      Reference1Address=:Reference1Address,
                                      Reference1Phone=:Reference1Phone,
                                      Reference1Aquainted=:Reference1Aquainted,
                                      Reference2Name=:Reference2Name,
                                      Reference2Address=:Reference2Address,
                                      Reference2Phone=:Reference2Phone,
                                      Reference2Aquainted=:Reference2Aquainted,
                                      Reference3Name=:Reference3Name,
                                      Reference3Address=:Reference3Address,
                                      Reference3Phone=:Reference3Phone,
                                      Reference3Aquainted=:Reference3Aquainted
                                where id = :appID");
        $stmt->bindParam(':Reference1Name', $data['Reference1Name'], PDO::PARAM_STR);
        $stmt->bindParam(':Reference1Address', $data['Reference1Address'], PDO::PARAM_STR);
        $stmt->bindParam(':Reference1Phone', $data['Reference1Phone'], PDO::PARAM_STR);
        $stmt->bindParam(':Reference1Aquainted', $data['Reference1Aquainted'], PDO::PARAM_STR);
        $stmt->bindParam(':Reference2Name', $data['Reference2Name'], PDO::PARAM_STR);
        $stmt->bindParam(':Reference2Address', $data['Reference2Address'], PDO::PARAM_STR);
        $stmt->bindParam(':Reference2Phone', $data['Reference2Phone'], PDO::PARAM_STR);
        $stmt->bindParam(':Reference2Aquainted', $data['Reference2Aquainted'], PDO::PARAM_STR);
        $stmt->bindParam(':Reference3Name', $data['Reference3Name'], PDO::PARAM_STR);
        $stmt->bindParam(':Reference3Address', $data['Reference3Address'], PDO::PARAM_STR);
        $stmt->bindParam(':Reference3Phone', $data['Reference3Phone'], PDO::PARAM_STR);
        $stmt->bindParam(':Reference3Aquainted', $data['Reference3Aquainted'], PDO::PARAM_STR);
        $stmt->bindParam(':appID', $appID, PDO::PARAM_STR);
        break;
      case 5:
        $stmt = $dbh->prepare("update application
                                  set waiverecords=:waiverecords,
      	                              appDate=now(),
      	                              signature=:signature
                                where id = :appID");

        $stmt->bindParam(':waiverecords', $data['waiverecords'], PDO::PARAM_STR);
        $stmt->bindParam(':signature', $data['signature'], PDO::PARAM_STR);
        $stmt->bindParam(':appID', $appID, PDO::PARAM_STR);
        unset($_SESSION['app-'.$data['jobID']], $_SESSION['step-'.$data['jobID']]);

        break;
    }

    $stmt->execute();


  	if ($stepID==5) return true;
  	return false;
	}

	public static function generate($dbh, $appID, $method='I'){
	  $data = self::get($dbh, $appID);
    //if (strlen($socialnumber) > 20) $socialnumber = $this->decrypt($socialnumber);
    $socialnumber=''; //Blank out social on forms per Maria 6/6/2013
    // initiate PDF
    $pdf = new PDF();
    $pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
    // add a page
    $pdf->AddPage();

    $pdf->SetFont("Helvetica", "", 11);


    $pdf->SetXY(33, 30);
    $pdf->Write(5, date('m/d/Y', strtotime($data['appDate'])));

    $pdf->SetXY(85, 30);
    $pdf->Write(5, $data['jobName']);

    $pdf->SetXY(30, 36);
    $pdf->Write(5, $data['lastname']);

    $pdf->SetXY(110, 36);
    $pdf->Write(5, $data['firstname']);

    $pdf->SetXY(170, 36);
    $pdf->Write(5, $data['middlename']);

    $pdf->SetXY(30, 62);
    $pdf->Write(5, $data['address']);

    $pdf->SetXY(105, 62);
    $pdf->Write(5, $data['city']);

    $pdf->SetXY(162, 62);
    $pdf->Write(5, $data['state']);

    $pdf->SetXY(187, 62);
    $pdf->Write(5, $data['zip']);

    $pdf->SetXY(70, 69);
    $pdf->Write(5, $data['years']);

    $pdf->SetXY(105, 69);
    $pdf->Write(5, $data['phone']);

    $pdf->SetXY(122, 76);
    $pdf->Write(5, $data['email']);

    $pdf->SetFont("Helvetica", "", 10);

    $highestx = 59+($data['highestgrade']*5.4);
    if ($data['highestgrade']>8) $highestx+=19;
    if ($data['highestgrade']>12) $highestx+=20;

    $pdf->SetXY($highestx, 152);
    $pdf->Write(5, 'Highest');

    $pdf->SetXY(46, 175);
    $pdf->Write(5, $data['highschool']);

    $pdf->SetXY(46, 184);
    $pdf->Write(5, $data['highschoolloc']);

    $pdf->SetXY(101, 175);
    $pdf->Write(5, $data['highschoolyears']);

    $pdf->SetXY(129, 175);
    $pdf->Write(5, $data['coursesubject']);

    $pdf->SetXY(174.5, 175);
    $pdf->write(5, (($data['coursesubjectgraduated']=='Yes') ? 'X' : ''));

    $pdf->SetXY(174.5, 184);
    $pdf->write(5, (($data['coursesubjectgraduated']=='GED') ? 'X' : ''));

    $pdf->SetXY(46, 193);
    $pdf->Write(5, $data['collegeuniversity']);

    $pdf->SetXY(46, 202);
    $pdf->Write(5, $data['collegeuniversityloc']);

    $pdf->SetXY(101, 193);
    $pdf->Write(5, $data['collegeuniversityyearscompleted']);

    $pdf->SetXY(129, 193);
    $pdf->Write(5, $data['collegeuniversitycourse']);

    $pdf->SetXY(174.5, 191);
    $pdf->write(5, (($data['collegegraduated']=='Yes') ? 'X' : ''));

    $pdf->SetXY(184, 194);
    $pdf->Write(5, $data['collegegraduatedMonth']);

    $pdf->SetXY(196, 194);
    $pdf->Write(5, $data['collegegraduatedYear']);

    $pdf->SetXY(174.5, 202);
    $pdf->write(5, (($data['collegegraduated']=='InP') ? 'X' : ''));


    $pdf->SetXY(46, 210);
    $pdf->Write(5, $data['othercollege']);

    $pdf->SetXY(46, 222);
    $pdf->Write(5, $data['othercollegeloc']);

    $pdf->SetXY(101, 214);
    $pdf->Write(5, $data['othercollegeyears']);

    $pdf->SetXY(129, 210);
    $pdf->Write(5, $data['othercollegecourse']);

    $pdf->SetXY(174.5, 209);
    $pdf->write(5, (($data['othercollegecompleted']=='Yes') ? 'X' : ''));

    $pdf->SetXY(184, 213);
    $pdf->Write(5, $data['othercollegecompletedMonth']);

    $pdf->SetXY(194, 213);
    $pdf->Write(5, $data['othercollegecompletedYear']);

    $pdf->SetXY(174.5, 219);
    $pdf->write(5, (($data['othercollegecompleted']=='Pro') ? 'X' : ''));


    $pdf->SetXY(128, 260.5);
    $pdf->Write(5, $data['language']);

    $pdf->SetXY(163, 260.5);
    $pdf->write(5, (($data['languagespeak']) ? 'X' : ''));

    $pdf->SetXY(177, 260.5);
    $pdf->write(5, (($data['languageread']) ? 'X' : ''));

    $pdf->SetXY(190, 260.5);
    $pdf->write(5, (($data['languagewrite']) ? 'X' : ''));


    $pdf->AddPage();

    $y=0;

    $pdf->SetXY(10, ($y+46));
    $pdf->Write(5, $data['companyname1']);

    $pdf->SetXY(10, ($y+55));
    $pdf->MultiCell(50, 10, $data['companyaddress1'], 0, 'L');

    $pdf->SetXY(10, ($y+64));
    $pdf->Write(5, $data['companyphone1']);

    $pdf->SetXY(10, ($y+72));
    $pdf->Write(5, $data['supervisorname1']);

    $pdf->SetXY(60, ($y+52));
    $pdf->MultiCell( 80, 35, $data['responsibilities1'], 0, 'L');


    $pdf->SetXY(141, ($y+56));
    $pdf->MultiCell( 60, 25, $data['reasonforleaving1'], 0, 'L');

    $pdf->SetXY(160, ($y+64));
    $pdf->write(5, "{$data['startwages1']} -> {$data['endedwages1']}" );

    if ($data['maywecontact1']=='Yes'){
      $pdf->SetXY(146, ($y+73));
    }else{
      $pdf->SetXY(178, ($y+73));
    }
    $pdf->write(5, 'X' );

    $y=33;

    $pdf->SetXY(10, ($y+46));
    $pdf->Write(5, $data['companyname2']);

    $pdf->SetXY(10, ($y+55));
    $pdf->MultiCell(50, 10, $data['companyaddress2'], 0, 'L');

    $pdf->SetXY(10, ($y+64));
    $pdf->Write(5, $data['companyphone2']);

    $pdf->SetXY(10, ($y+72));
    $pdf->Write(5, $data['supervisorname2']);

    $pdf->SetXY(60, ($y+52));
    $pdf->MultiCell( 80, 35, $data['responsibilities2'], 0, 'L');

    $pdf->SetXY(141, ($y+56));
    $pdf->MultiCell( 60, 25, $data['reasonforleaving2'], 0, 'L');

    $pdf->SetXY(160, ($y+64));
    $pdf->write(5, "{$data['startwages2']} -> {$data['endedwages2']}" );

    if ($data['maywecontact2']=='Yes'){
      $pdf->SetXY(140, ($y+73));
    }else{
      $pdf->SetXY(178, ($y+73));
    }
    $pdf->write(5, 'X' );



    $y=66.5;

    $pdf->SetXY(10, ($y+46));
    $pdf->Write(5, $data['companyname3']);

    $pdf->SetXY(10, ($y+55));
    $pdf->MultiCell(50, 10, $data['companyaddress3'], 0, 'L');

    $pdf->SetXY(10, ($y+64));
    $pdf->Write(5, $data['companyphone3']);

    $pdf->SetXY(10, ($y+72));
    $pdf->Write(5, $data['supervisorname3']);

    $pdf->SetXY(60, ($y+52));
    $pdf->MultiCell( 80, 35, $data['responsibilities3'], 0, 'L');

    $pdf->SetXY(141, ($y+56));
    $pdf->MultiCell( 60, 25, $data['reasonforleaving3'], 0, 'L');

    $pdf->SetXY(160, ($y+64));
    $pdf->write(5, "{$data['startwages3']} -> {$data['endedwages3']}" );

    if ($data['maywecontact3']=='Yes'){
      $pdf->SetXY(140, ($y+73));
    }else{
      $pdf->SetXY(178, ($y+73));
    }
    $pdf->write(5, 'X' );



    $pdf->SetXY(44, 265);
    $pdf->MultiCell( 95, 10, "Signed Online as {$data['signature']} from {$data['ipAddress']}", 0, 'L' );

    $pdf->SetXY(150, 265);
    $pdf->Write(5, $data['appDate']);

    $pdf->AddPage();

    $pdf->SetXY(26, 42);
    $pdf->Write(5, $data['lastname']);

    $pdf->SetXY(101, 42);
    $pdf->Write(5, $data['firstname']);

    $pdf->SetXY(172, 42);
    $pdf->Write(5, substr($data['middlename'], 0, 1));

    $pdf->SetXY(115, 50);
    $pdf->Write(5, $data['zip']);

    $pdf->SetXY(48, 57);
    $pdf->Write(5, $data['jobName']);

    $pdf->SetXY(48, 57);
    $pdf->Write(5, $data['jobName']);


    $filename = $data['firstname'].'_'.$data['lastname'].'_app.pdf';

    if ($method=='F'){
      $pdf->Output(__DIR__.'../../public_html/img/pdf/'.$filename, 'F');
      return ('img/pdf/'.$filename);
    }
    elseif($method=='I'){
      $pdf->Output(__DIR__.'../../public_html/img/pdf/'.$filename, 'I');
      die();
    }
	}

	##########
	# Protected Methods
	##########
/*
  private function encrypt($string){
    return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5(ENCKEY), $string, MCRYPT_MODE_CBC, md5(md5(ENCKEY))));
  }

  private function decrypt($string){
    return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5(ENCKEY), base64_decode($string), MCRYPT_MODE_CBC, md5(md5(ENCKEY))), "\0");
  }
*/
}
class PDF extends FPDI {
    var $_tplIdx;

    function Header() {
        global $theApp;
        $theApp->page++;
        $this->setSourceFile('media/ApplicationNew.pdf');
        $this->_tplIdx = $this->importPage($theApp->page);
        $this->useTemplate($this->_tplIdx);
    }

    function Footer() {}

}
?>