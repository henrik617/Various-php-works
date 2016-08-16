<?php

	header( 'Content-Type: text/html; charset=utf-8' ); 
	//date_default_timezone_set('UTC');
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	require_once('System/ThGame.php');
	
	$service = new ThGame();
	var_dump($service->getAllGroups());
	//var_dump($service->getTopScorer(67));
	
	//$service->sendPushNotification('0bf423e6f3e6f8f9ff2ee7ed840247409a4c66101c8177cdfb47648362634f2f', 'message');
	/*
	//var_dump($service->CreateTask(1, 'Find Nemo', 2, 1));
	//var_dump($service->CreateTask(2, 'Find Nemo1', 2, 1));
	//var_dump($service->CreateTask(3, 'Find Nemo1', 2, 1));
	//var_dump($service->CreateTask(1, 'Find Nemo1', 2, 1));
	*/
	$group = $service->createGroup('啊啊書法家', 1);
	var_dump($group->toArray());
	//var_dump($service->addPlayer(1,1));
?>