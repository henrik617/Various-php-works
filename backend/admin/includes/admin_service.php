<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	date_default_timezone_set('UTC');
	
	require_once('../../System/ThGame.php');
    

	$service = new ThGame();
	
	$access_token = '';
	$action = '';
	if($_GET && isset($_GET['action'])){
		$action = $_GET['action'];
	}
	if($action == 'createSegment'){

		if (isset($_POST['chk_usergroup'])) {
		    $userArray = $_POST['chk_usergroup'];
			
			$segmentName = $_POST['segmentName'];
			if($segmentName == ''){
				echo 0;
			}else{
				$id = $service->admin_addUserSegment($segmentName, $userArray);
				if($id > 0)
					echo 1;
				else
					echo 0;
			}
		}
	}else if($action == 'deleteSegment'){

		$id = $_POST['id'];
		echo $service->admin_deleteUserSegment($id);
	}else if($action == 'updateSegment'){

		$id = $_POST['sid'];
		$userArray = $_POST['chk_usergroup'];
		$segmentName = $_POST['segmentName'];
	
		$result = $service->admin_updateUserSegment($id, $segmentName, $userArray);
		if($result)
			echo 1;
		else
			echo 0;
	}else if($action == 'getSegmentUsers'){

		$id = $_POST['id'];
		
		$result = $service->admin_getSegmentUsers($id);
		echo json_encode($result);
	}else if($action == 'sendNotification'){

		$result = 0;
		if (isset($_POST['chk_usergroup'])) {
		    $userArray = $_POST['chk_usergroup'];
			
			$message = $_POST['notification'];
			if($message == ''){
				$result = 0;
			}else{
				$result = $service->admin_sendNotification($userArray, $message);
				$result = 1;
			}
		}
		
		echo $result;
	}
	
?>