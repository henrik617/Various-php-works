<?php
	$type = $_POST['type'];
	
	if($type == "user"){
		$target_path = 'images/user/';
	}else if($type == "group"){
		$target_path = 'images/group/';
	}else if($type == "challenge"){
		$target_path = 'images/challenge/';
	}else if($type == "comment"){
		$target_path = 'images/comment/';
    }else if($type == "report"){
        $target_path = 'images/report/';
    }else{
		$target_path = 'images/other/';
	}
	$target_path = $target_path . basename( $_FILES['userfile']['name']);
	
	if(move_uploaded_file($_FILES['userfile']['tmp_name'], $target_path)) {
		$data = array("result"=>array("message"=>"The file ".  basename( $_FILES['userfile']['name'])." has been uploaded", "fileName"=>basename( $_FILES['userfile']['name']),"success"=>1));
	}else{
		$data = array("result"=> array("message"=>"There was an error uploading the file, please try again!", "success"=>0));
	}
    echo json_encode($data);
?>