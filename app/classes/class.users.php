<?php
/**
* Class => Menu
* CRUD for Menu Items that are for sale
*/

use Slim\Slim;

class users  {

  public static function getAdminUsers($dbh){
    $stmt = $dbh->prepare("select * from user order by lname");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function getInfo($dbh, $id) {
		$stmt = $dbh->prepare("SELECT * FROM user u WHERE u.id=:id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
	}

	public static function getRoles($dbh){
		$stmt = $dbh->prepare('select * from userRole order by id asc');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public static function setStatus($dbh, $userID, $status){
  	$stmt = $dbh->prepare('update user set status = :status where id = :userID');
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    $stmt->execute();
	}

	public static function setRole($dbh, $userID, $roleID){
  	$stmt = $dbh->prepare('update user set role = :roleID where id = :userID');
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->bindParam(':roleID', $roleID, PDO::PARAM_INT);
    $stmt->execute();
	}

	public static function delete($dbh, $userID){
  	$stmt = $dbh->prepare('delete from user where id = :userID');
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
	}

  public static function create($dbh, $userData){
    extract($userData);
    $options = ['cost' => 12];
    $password = password_hash($password, PASSWORD_BCRYPT, $options);
    $stmt = $dbh->prepare("insert into user (uname, password, fname, lname, email, phone, address, city, state, zip, lastlogin, role, status) values
                                            (:uname, :password, :fname, :lname, :email, :phone, :address, :city, :state, :zip, NOW(), 0, 1)");
    $stmt->bindParam(':uname', $uname, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->bindParam(':fname', $fname, PDO::PARAM_STR, 30);
    $stmt->bindParam(':lname', $lname, PDO::PARAM_STR, 30);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
    $stmt->bindParam(':address', $address, PDO::PARAM_STR);
    $stmt->bindParam(':city', $city, PDO::PARAM_STR);
    $stmt->bindParam(':state', $state, PDO::PARAM_STR);
    $stmt->bindParam(':zip', $zip, PDO::PARAM_STR);
    $stmt->execute();
  }

  public static function update($dbh, $userID, $userData){
    extract($userData);
    $options = ['cost' => 12];
    if($password){
      $password = password_hash($password, PASSWORD_BCRYPT, $options);
      $stmt = $dbh->prepare("update user set uname=:uname, password=:password, fname=:fname, lname=:lname, email=:email, phone=:phone, address=:address, city=:city, state=:state, zip=:zip where id = :userID");
      $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    }else{
      $stmt = $dbh->prepare("update user set uname=:uname, fname=:fname, lname=:lname, email=:email, phone=:phone, address=:address, city=:city, state=:state, zip=:zip where id = :userID");
    }
    $stmt->bindParam(':uname', $uname, PDO::PARAM_STR);
    $stmt->bindParam(':fname', $fname, PDO::PARAM_STR, 30);
    $stmt->bindParam(':lname', $lname, PDO::PARAM_STR, 30);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
    $stmt->bindParam(':address', $address, PDO::PARAM_STR);
    $stmt->bindParam(':city', $city, PDO::PARAM_STR);
    $stmt->bindParam(':state', $state, PDO::PARAM_STR);
    $stmt->bindParam(':zip', $zip, PDO::PARAM_STR);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
  }

  public static function login($dbh, $userData){
    $app = Slim::getInstance();
    extract($userData);
    $stmt = $dbh->prepare("select id, password, email, uname, fname, lname, role from user where email like :email or uname like :uname");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':uname', $email, PDO::PARAM_STR);
    $stmt->execute();
    if($token = $stmt->fetch(PDO::FETCH_ASSOC)){
      if(!password_verify($password, $token['password'])){
        $app->flash('error', 'Invalid password for account');
        $app->redirect($app->urlFor('home'));
      }
      $key = $app->config('JWT_KEY');
      $jwt = JWT::encode($token, $key);
      $_SESSION['jwt'] = $jwt;
      $app->urlFor('adminsitemenu');
    }else{
      $app->flash('error', 'Invalid username or email');
      $app->redirect($app->urlFor('home'));
    }
  }

}
