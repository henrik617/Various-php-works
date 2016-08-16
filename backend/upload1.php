<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
 
// Include Database handler


$appid = $_POST['appid'];      //$_GET['appid']
$plaid = $_POST['plaid'];


$target_path = "../Censorpro/";
//$target_path = '/var/www/httpsdocs/t4fapi/images/';
 
$datetime = date("YmdHis");        // YYYYmmddHHmmss, 20010310171618
// Define a unique file name containing app id and current date time
// 1_20141120121212_larry.jpg
$target_filename =$appid."_".$datetime."_".basename($_FILES['userfile']['name']);
$target_path_and_filename = $target_path.$target_filename;
 
// $_FILES['userfile']['tmp_name'] is temporary location of uploaded file
if(move_uploaded_file($_FILES['userfile']['tmp_name'], $target_path_and_filename)) {
 /*
        // Code to store upload file name
        $plaapp_image = $target_filename;
        if ($db->storeImage($appid, $plaid, $plaapp_image)) {
                $data = array("result"=>array("message"=>"The file ".  basename( $_FILES['userfile']['name'])." has been uploaded", "fileName"=>basename( $_FILES['userfile']['name']),"success"=>1));
        }else{
                $data = array("result"=> array("message"=>"There was an error uploading the photo, please try again.", "success"=>0));
        }
		*/
}else{
        $data = array("result"=> array("message"=>"There was an error uploading the photo, please try again!", "success"=>0));
}
//echo json_encode($data);
 
?>