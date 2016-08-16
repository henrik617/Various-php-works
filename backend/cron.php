<?php
	//date_default_timezone_set('UTC');
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	error_reporting(E_ALL ^ E_DEPRECATED);

	require_once('System/ThGame.php');

	$service = new ThGame();
	$groups = $service->getAllGroups();
	foreach($groups as $group){

		$task = $service->getActiveTaskByGroup($group['groupId']);
		if($task == null)
			continue;
		$taskDuration = $task->getTaskDuration();
		$durationType = $task->getTaskDurationType();

		$createDate = new DateTime($task->getCreateDate());
		echo $createDate->format("Y-m-d H:i:s") . '<br>';
		if($durationType == 0)
			$diff = new DateInterval('P'.$taskDuration.'D');
		else if($durationType == 1)
			$diff = new DateInterval('PT'.$taskDuration.'H');
		else if($durationType == 2)
			$diff = new DateInterval('PT'.$taskDuration.'M');
		
		$expireDate = $createDate->add($diff);
		echo $expireDate->format("Y-m-d H:i:s") . '<br>';
		
		$now = new DateTime();
		echo $now->format("Y-m-d H:i:s") . '<br>';
		
		if($now > $expireDate){
			$service->taskTimeUp($task->getTaskId());
		}
	}
	
	
	/*
	var_dump($service->CreateTask(1, 'Find Nemo', 2, 1));
	var_dump($service->CreateTask(2, 'Find Nemo1', 2, 1));
	var_dump($service->CreateTask(3, 'Find Nemo1', 2, 1));
	var_dump($service->CreateTask(1, 'Find Nemo1', 2, 1));
	*/
	//$group = $service->createGroup('New Group', 1);
	//var_dump($group->toArray());
	//var_dump($service->addPlayer(1,1));
?>