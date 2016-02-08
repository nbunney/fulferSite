<?php
/**
* Class => claimform
*/

use com\tecnick\tcpdf;

class theClaim {
  public $page=0;
}

$theClaim = new theClaim();


class claimform  {

  public static function getClaim($dbh, $id){
    $stmt = $dbh->prepare("select c.*, s.name as statusName
                             from claimForm c
                                  left join claimFormStatus s on s.id = c.status
                            where c.id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_STR);
    $stmt->execute();
  	$data = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $dbh->prepare("select *
                             from claimFormImage
                            where claimFormID = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_STR);
    $stmt->execute();
  	$data['images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

  	return $data;
  }

  public static function get($dbh, $all=false){
    if (!$all) $where = " where status < 3 ";
    $stmt = $dbh->prepare("select c.*, s.name as statusName
                             from claimForm c
                                  left join claimFormStatus s on s.id = c.status
                             $where
                             group by c.id
                             order by c.status asc, c.claimDate asc");
    $stmt->execute();
  	return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function getStatus($dbh){
    $stmt = $dbh->prepare("select * from claimFormStatus");
    $stmt->execute();
  	return $stmt->fetchAll(PDO::FETCH_ASSOC);

  }

  public static function setNotes($dbh, $id, $notes){
    $stmt = $dbh->prepare("update claimForm set notes=:notes where id = :id");
    $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
  }

  public static function setStatus($dbh, $id, $status){
    $stmt = $dbh->prepare("update claimForm set status=:status where id = :id");
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
  }

  public static function process($dbh, $data){
     try{
      $date = date('Y-m-d', strtotime($data['claimDate']));

      $stmt = $dbh->prepare("insert into claimForm (companyName, claimDate, custClaimNo, contactPerson, contactNumber, bolNumbers, cLotNumbers, product, productCode, totalLbs, description, productCost, totalProductCost, freightCharges, disposalCost, binCharges, otherCharges, totalClaim) values (:companyName, :claimDate, :custClaimNo, :contactPerson, :contactNumber, :bolNumbers, :cLotNumbers, :product, :productCode, :totalLbs, :description, :productCost, :totalProductCost, :freightCharges, :disposalCost, :binCharges, :otherCharges, :totalClaim)");
      $stmt->bindParam(':companyName', $data['companyName'], PDO::PARAM_STR);
      $stmt->bindParam(':claimDate', $date, PDO::PARAM_STR);
      $stmt->bindParam(':custClaimNo', $data['custClaimNo'], PDO::PARAM_STR);
      $stmt->bindParam(':contactPerson', $data['contactPerson'], PDO::PARAM_STR);
      $stmt->bindParam(':contactNumber', $data['contactNumber'], PDO::PARAM_STR);
      $stmt->bindParam(':bolNumbers', $data['bolNumbers'], PDO::PARAM_STR);
      $stmt->bindParam(':cLotNumbers', $data['cLotNumbers'], PDO::PARAM_STR);
      $stmt->bindParam(':product', $data['product'], PDO::PARAM_STR);
      $stmt->bindParam(':productCode', $data['productCode'], PDO::PARAM_STR);
      $stmt->bindParam(':totalLbs', $data['totalLbs'], PDO::PARAM_STR);
      $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
      $stmt->bindParam(':productCost', $data['productCost'], PDO::PARAM_STR);
      $stmt->bindParam(':totalProductCost', $data['totalProductCost'], PDO::PARAM_STR);
      $stmt->bindParam(':freightCharges', $data['freight'], PDO::PARAM_STR);
      $stmt->bindParam(':disposalCost', $data['disposalCost'], PDO::PARAM_STR);
      $stmt->bindParam(':binCharges', $data['binCharges'], PDO::PARAM_STR);
      $stmt->bindParam(':otherCharges', $data['otherCharges'], PDO::PARAM_STR);
      $stmt->bindParam(':totalClaim', $data['totalClaim'], PDO::PARAM_STR);
      $stmt->execute();
      $id = $dbh->lastInsertId();


      if(is_array($data['formImage'])) foreach($data['formImage'] as $i=>$image){
        $ext = end(explode('.', $image));
        $newImage = "claimFormImage-$id-$i.$ext";
        @rename(__DIR__.'/../../public_html/img/claimform/'.$image, __DIR__.'/../../public_html/img/claimform/'.$newImage);
        @rename(__DIR__.'/../../public_html/img/claimform/thumbnail/'.$image, __DIR__.'/../../public_html/img/claimform/thumbnail/'.$newImage);
        $stmt = $dbh->prepare("insert into claimFormImage (claimFormID, imageName) values (:claimFormID, :imageName)");
        $stmt->bindParam(':claimFormID', $id, PDO::PARAM_INT);
        $stmt->bindParam(':imageName', $newImage, PDO::PARAM_STR);
        $stmt->execute();
      }

    }catch (Exception $e){
      print_r($dbh->errorInfo());
      return array('error'=>'There was a problem saving your claim form please try again.');
    }
    return array('success'=>'Your claim form has been entered.  Your claim number is ' . $id . '.');

  }

	public static function generate($dbh, $appID, $method='I'){
    global $theClaim;

	  $data = self::getClaim($dbh, $appID);
    //if (strlen($socialnumber) > 20) $socialnumber = $this->decrypt($socialnumber);

    // initiate PDF
    $pdf = new claimPDF();
    $pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
    // add a page
    $theClaim->page=1;
    $pdf->AddPage();

    $pdf->SetFont("Helvetica", "", 20);

    $pdf->SetXY(143, 19);
    $pdf->Write(5, $data['id']);

    $pdf->SetFont("Helvetica", "", 11);

    $pdf->SetXY(135, 84.5);
    $pdf->Write(5, $data['companyName']);

    $pdf->SetXY(38, 97);
    $pdf->Write(5, date('m/d/Y', strtotime($data['claimDate'])));

    $pdf->SetXY(140, 97);
    $pdf->Write(5, $data['custClaimNo']);

    $pdf->SetXY(38, 109);
    $pdf->Write(5, $data['contactPerson']);

    $pdf->SetXY(140, 109);
    $pdf->Write(5, $data['contactNumber']);

    $pdf->SetXY(38, 121);
    $pdf->Cell(60, 12, $data['bolNumbers'], 0, 0, 'L', false, '', 0, false, 'T', 'T');

    $pdf->SetXY(142, 121);
    $pdf->Cell(60, 22, $data['cLotNumbers'], 0, 0, 'L', false, '', 0, false, 'T', 'T');

    $pdf->SetXY(38, 140);
    $pdf->Write(5, $data['product']);

    $pdf->SetXY(38, 152);
    $pdf->Write(5, $data['productCode']);

    $pdf->SetXY(140, 152);
    $pdf->Write(5, $data['totalLbs']);

    $pdf->SetXY(10, 164);
    $pdf->Cell(195, 12, $data['description'], 0, 0, 'L', false, '', 0, false, 'T', 'T');

    $pdf->SetXY(10, 174);
    $pdf->Cell(30, 12, $data['productCost']);

    $pdf->SetXY(40, 174);
    $pdf->Cell(30, 12, $data['totalProductCost']);

    $pdf->SetXY(70, 174);
    $pdf->Cell(30, 12, $data['freightCharges']);

    $pdf->SetXY(100, 174);
    $pdf->Cell(30, 12, $data['disposalCost']);

    $pdf->SetXY(130, 174);
    $pdf->Cell(30, 12, $data['binCharges']);

    $pdf->SetXY(160, 174);
    $pdf->Cell(30, 12, $data['otherCharges']);

    $pdf->SetXY(190, 174);
    $pdf->Cell(30, 12, $data['totalClaim']);

    $theClaim->page=2;

    foreach($data['images'] as $i=>$file){
      if ($i%2==0) $pdf->AddPage();
      $top = ($i%2) ? 145 : 20;

      $img = __DIR__.'/../../public_html/img/claimform/'.$file['imageName'];
      $pdf->Image($img, 20, $top, 0, 120);

    }

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

}
class claimPDF extends FPDI {
    var $_tplIdx;

    function Header() {
        global $theClaim;
        $page = $theClaim->page;
        if($page==1){
          $this->setSourceFile('media/ClaimForm.pdf');
          $this->_tplIdx = $this->importPage($page);
          $this->useTemplate($this->_tplIdx);
        }
    }

    function Footer() {}

}