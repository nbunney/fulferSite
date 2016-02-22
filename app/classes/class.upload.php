<?php
/**
* Class => upload
*/


class upload  {

  public static function handleUpload($targetDir){
    header('Content-type: application/json');
    if (!isset($_FILES['files']['error']) || is_array($_FILES['files']['error'])) {
        throw new RuntimeException('Invalid parameters.');
    }

    switch ($_FILES['files']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
        $finfo->file($_FILES['files']['tmp_name']),
        array(
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ),
        true
    )) {
        throw new RuntimeException('Invalid file format.');
    }

    $name = sprintf('%s.%s', sha1_file($_FILES['files']['tmp_name']), $ext);

    if (!move_uploaded_file($_FILES['files']['tmp_name'], $targetDir.'/'.$name)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    return json_encode(array('name'=>$name));
  }

}