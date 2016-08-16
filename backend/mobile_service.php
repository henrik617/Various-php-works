<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	date_default_timezone_set('UTC');
	
	require_once('System/configuration.php');
	require_once('System/ThGame.php');
    

	$service = new ThGame();
	
	$access_token = '';
	$action = '';
	if($_GET && isset($_GET['action'])){
		$action = $_GET['action'];
	}
	if($action == 'userLogin'){
		
		$userName = ($_GET && isset($_GET['userName'])) ? $_GET['userName']:'';
		$socialId = ($_GET && isset($_GET['socialId'])) ? $_GET['socialId']:'';
		$socialName = ($_GET && isset($_GET['socialName'])) ? $_GET['socialName']:'';
		$email = ($_GET && isset($_GET['email'])) ? $_GET['email']:'';
		$photoUrl = ($_GET && isset($_GET['picture'])) ? $_GET['picture']:'';
		$deviceToken = ($_GET && isset($_GET['token'])) ? $_GET['token']:'';
        $deviceType = ($_GET && isset($_GET['device_type'])) ? $_GET['device_type']:'';
		$socialType = ($_GET && isset($_GET['socialType'])) ? $_GET['socialType']:'';
		userLogin($userName, $socialId, $socialName, $email, $photoUrl, $deviceToken, $deviceType, $socialType);
	}else if($action == 'getAppVersion'){
		
		getAppVersion();
	}else{
		
		if($_GET && isset($_GET['access_token'])){
			$access_token = $_GET['access_token'];
		}
		if($access_token == ''){
			echo json_encode(array('result'=>'Bad Access!'));
			exit;
		}else{
	        global $service;
			
			if(($access_token != $_VISITORY_TOKEN) && (!$service->isTokenValid($access_token))){
				echo json_encode(array('result'=>'Bad Access!'));
				exit;
			}
		}
		
		if($action == 'updateProfileImage'){
			$userId = ($_GET && isset($_GET['user_id'])) ? $_GET['user_id']:'';
			$profileUrl = ($_GET && isset($_GET['profile_url'])) ? $_GET['profile_url']:'';
			updateProfileImage($userId, $profileUrl);
		}else if($action == 'updateUser'){
			$userId = ($_GET && isset($_GET['user_id'])) ? $_GET['user_id']:'';
			$userName = ($_GET && isset($_GET['user_name'])) ? $_GET['user_name']:'';
			$profileUrl = ($_GET && isset($_GET['profile_url'])) ? $_GET['profile_url']:'';
			$publicLeaderboard = ($_GET && isset($_GET['public_leaderboard'])) ? $_GET['public_leaderboard']:'';
			updateUser($userId, $userName, $profileUrl, $publicLeaderboard);
		}else if($action == 'updateLanguage'){
			$userId = ($_GET && isset($_GET['user_id'])) ? $_GET['user_id']:'';
			$language = ($_GET && isset($_GET['language'])) ? $_GET['language']:0;

			updateLanguage($userId, $language);
			
		}else if($action == 'getFriends'){
			$userId = ($_GET && isset($_GET['user_id'])) ? $_GET['user_id']:'';
			getFriends($userId);
			
		}else if($action == 'searchUsers'){
			$userId = ($_GET && isset($_GET['user_id'])) ? $_GET['user_id']:'';
			$keyword = ($_GET && isset($_GET['keyword'])) ? $_GET['keyword']:'';
			searchUsers($userId, $keyword);
			
		}else if($action == 'addUserFriend'){
			$userId = ($_GET && isset($_GET['user_id'])) ? $_GET['user_id']:'';
			$friendId = ($_GET && isset($_GET['friendId'])) ? $_GET['friendId']:'';
			addUserFriend($userId, $friendId);
			
		}else if($action == 'getGroups'){
			$userId = ($_GET && isset($_GET['user_id'])) ? $_GET['user_id']:'';
			$groupType = ($_GET && isset($_GET['groupType'])) ? $_GET['groupType']:0;
			
			if($groupType == 0)
				getPrivateGroups($userId);
			else 
				getPublicGroups($userId);
			
		}else if($action == 'createGroup'){

			$userId = ($_GET && isset($_GET['user_id'])) ? $_GET['user_id']:'';
			$title = ($_GET && isset($_GET['title'])) ? $_GET['title']:'';
			$groupImage = ($_GET && isset($_GET['group_image'])) ? $_GET['group_image']:'';
			$groupType = ($_GET && isset($_GET['groupType'])) ? $_GET['groupType']:0;
			
			createGroup($title, $userId, $groupImage, $groupType);

		}else if($action == 'editGroup'){

			$groupId = ($_GET && isset($_GET['group_id'])) ? $_GET['group_id']:0;
			$title = ($_GET && isset($_GET['title'])) ? $_GET['title']:'';
			$groupImage = ($_GET && isset($_GET['group_image'])) ? $_GET['group_image']:'';
			editGroup($groupId, $title, $groupImage);

		}else if($action == 'getGroup'){

			$groupId = ($_GET && isset($_GET['group_id'])) ? $_GET['group_id']:0;
			getGroup($groupId);

		}else if($action == 'deleteGroup'){

			$groupId = ($_GET && isset($_GET['group_id'])) ? $_GET['group_id']:0;
			deleteGroup($groupId);

		}else if($action == 'updateGroupStatus'){

			$groupId = ($_GET && isset($_GET['group_id'])) ? $_GET['group_id']:0;
			$status = ($_GET && isset($_GET['status'])) ? $_GET['status']:0;
			updateGroupStatus($groupId, $status);

		}else if($action == 'login'){
		
		}else if($action == 'getChallenges'){
			$userId = ($_GET && isset($_GET['user_id'])) ? $_GET['user_id']:'';
			$groupId = ($_GET && isset($_GET['group_id'])) ? $_GET['group_id']:0;
			getChallenges($userId, $groupId);

		}else if($action == 'getChallenge'){

			$challenge_id = ($_GET && isset($_GET['challenge_id'])) ? $_GET['challenge_id']:0;
			getChallenge($challenge_id);

		}else if($action == 'createChallenge'){

			$groupId = ($_GET && isset($_GET['group_id'])) ? $_GET['group_id']:0;
			$shortTitle = ($_GET && isset($_GET['short_title'])) ? $_GET['short_title']:'';
			$description = ($_GET && isset($_GET['description'])) ? $_GET['description']:'';
			$duration = ($_GET && isset($_GET['duration'])) ? $_GET['duration']:0;
			$durationType = ($_GET && isset($_GET['duration_type'])) ? $_GET['duration_type']:0;
			$challengeImage = ($_GET && isset($_GET['challenge_image'])) ? $_GET['challenge_image']:'';
			$creatorId = ($_GET && isset($_GET['creator_id'])) ? $_GET['creator_id']:0;
			$challengeType = ($_GET && isset($_GET['challenge_type'])) ? $_GET['challenge_type']:0;
			$acceptDuration = ($_GET && isset($_GET['accept_duration'])) ? $_GET['accept_duration']:0;
			$acceptDurationType = ($_GET && isset($_GET['accept_duration_type'])) ? $_GET['accept_duration_type']:0;
			$rewardDescription = ($_GET && isset($_GET['reward_description'])) ? $_GET['reward_description']:'';
			$rewardType = ($_GET && isset($_GET['reward_type'])) ? $_GET['reward_type']:0;
			$resendOption = ($_GET && isset($_GET['resend_option'])) ? $_GET['resend_option']:0;
			createChallenge($groupId, $shortTitle, $description, $duration, $durationType, $challengeImage, $creatorId, $challengeType, $acceptDuration, $acceptDurationType, $rewardDescription, $rewardType, $resendOption);

		}else if($action == 'editChallenge'){

			$challengeId = ($_GET && isset($_GET['challenge_id'])) ? $_GET['challenge_id']:0;
			$description = ($_GET && isset($_GET['description'])) ? $_GET['description']:'';
			$duration = ($_GET && isset($_GET['duration'])) ? $_GET['duration']:0;
			$durationType = ($_GET && isset($_GET['duration_type'])) ? $_GET['duration_type']:0;
			$acceptDuration = ($_GET && isset($_GET['accept_duration'])) ? $_GET['accept_duration']:0;
			$acceptDurationType = ($_GET && isset($_GET['accept_duration_type'])) ? $_GET['accept_duration_type']:0;
			$challengeImage = ($_GET && isset($_GET['challenge_image'])) ? $_GET['challenge_image']:'';
			$resendOption = ($_GET && isset($_GET['resend_option'])) ? $_GET['resend_option']:0;

			editChallenge($challengeId, $description, $duration, $durationType, $challengeImage, $acceptDuration, $acceptDurationType, $resendOption);

		}else if($action == 'completeChallenge'){

			$challengeId = ($_GET && isset($_GET['challenge_id'])) ? $_GET['challenge_id']:0;
			$playerId = ($_GET && isset($_GET['player_id'])) ? $_GET['player_id']:0;
			$comment = ($_GET && isset($_GET['comment'])) ? $_GET['comment']:"";
			completeChallenge($challengeId, $playerId, $comment);

		}else if($action == 'giveupChallenge'){

			$challengeId = ($_GET && isset($_GET['challenge_id'])) ? $_GET['challenge_id']:0;
			$playerId = ($_GET && isset($_GET['player_id'])) ? $_GET['player_id']:0;
			$comment = ($_GET && isset($_GET['comment'])) ? $_GET['comment']:"";
			giveupChallenge($challengeId, $playerId, $comment);

		}else if($action == 'getTopPlayers'){

			$groupId = ($_GET && isset($_GET['group_id'])) ? $_GET['group_id']:0;
			getTopPlayers($groupId);

		}else if($action == 'updateChallengeStatus'){

			$challengeId = ($_GET && isset($_GET['challenge_id'])) ? $_GET['challenge_id']:0;
			$status = ($_GET && isset($_GET['status'])) ? $_GET['status']:0;
			updateChallengeStatus($challengeId, $status);

		}else if($action == 'deleteChallenge'){

			$challengeId = ($_GET && isset($_GET['challenge_id'])) ? $_GET['challenge_id']:0;
			deleteChallenge($challengeId);
		}else if($action == 'getPlayers'){

			$groupId = ($_GET && isset($_GET['group_id'])) ? $_GET['group_id']:0;
			getGroupPlayers($groupId);
		}else if($action == 'getPlayerId'){

			$groupId = ($_GET && isset($_GET['group_id'])) ? $_GET['group_id']:0;
			$userId = ($_GET && isset($_GET['user_id'])) ? $_GET['user_id']:0;
			getPlayerId($groupId, $userId);
		}else if($action == 'addPlayers'){

			$groupId = ($_GET && isset($_GET['group_id'])) ? $_GET['group_id']:0;
			$userIds = ($_GET && isset($_GET['user_ids'])) ? $_GET['user_ids']:'';
			$invitor = ($_GET && isset($_GET['invitor'])) ? $_GET['invitor']:'';
			if($userIds != ''){
				$ids = explode(',', $userIds);
				addPlayers($groupId, $ids, $invitor);
			}
		}else if($action == 'removePlayer'){

			$playerId = ($_GET && isset($_GET['player_id'])) ? $_GET['player_id']:'';
			if($playerId != ''){
				removePlayer($playerId);
			}
		}else if($action == 'getChallengeStatus'){

			$challengeId = ($_GET && isset($_GET['challenge_id'])) ? $_GET['challenge_id']:0;
			if($challengeId != 0){
				getChallengeStatus($challengeId);
			}
		}else if($action == 'updatePlayerStatus'){
			$challengeId = ($_GET && isset($_GET['challenge_id'])) ? $_GET['challenge_id']:0;
			$playerId = ($_GET && isset($_GET['player_id'])) ? $_GET['player_id']:0;
			$playerScore = ($_GET && isset($_GET['player_score'])) ? $_GET['player_score']:0;
			$playerStatus = ($_GET && isset($_GET['player_status'])) ? $_GET['player_status']:0;
		
			updatePlayerStatus($challengeId, $playerId, $playerScore, $playerStatus);
		}else if($action == 'getGroupComments'){
			$groupId = ($_GET && isset($_GET['group_id'])) ? $_GET['group_id']:0;
			$pageIndex = ($_GET && isset($_GET['page_index'])) ? $_GET['page_index']:0;
			getGroupComments($groupId, $pageIndex);
			
		}else if($action == 'getChallengeComments'){
			$challengeId = ($_GET && isset($_GET['challenge_id'])) ? $_GET['challenge_id']:0;
			$pageIndex = ($_GET && isset($_GET['page_index'])) ? $_GET['page_index']:0;
			getChallengeComments($challengeId, $pageIndex);
			
		}else if($action == 'addGroupComment'){
			$groupId = ($_GET && isset($_GET['group_id'])) ? $_GET['group_id']:0;
			$playerId = ($_GET && isset($_GET['player_id'])) ? $_GET['player_id']:0;
			$comment = ($_GET && isset($_GET['comment'])) ? $_GET['comment']:0;
			$type = ($_GET && isset($_GET['type'])) ? $_GET['type']:0;
			addGroupComment($groupId, $playerId, $comment, $type);
			
		}else if($action == 'addChallengeComment'){
			$groupId = ($_GET && isset($_GET['group_id'])) ? $_GET['group_id']:0;
			$challengeId = ($_GET && isset($_GET['challenge_id'])) ? $_GET['challenge_id']:0;
			$playerId = ($_GET && isset($_GET['player_id'])) ? $_GET['player_id']:0;
			$comment = ($_GET && isset($_GET['comment'])) ? $_GET['comment']:0;
			$type = ($_GET && isset($_GET['type'])) ? $_GET['type']:0;
			
			addChallengeComment($groupId, $challengeId, $playerId, $comment, $type);
			
		}else if($action == 'addGroupQuote'){
			$groupId = ($_GET && isset($_GET['group_id'])) ? $_GET['group_id']:0;
			$playerId = ($_GET && isset($_GET['player_id'])) ? $_GET['player_id']:0;
			$quote = ($_GET && isset($_GET['quote'])) ? $_GET['quote']:0;
			addGroupQuote($groupId, $playerId, $comment);
		}else if($action == 'getGroupQuote'){
			$groupId = ($_GET && isset($_GET['group_id'])) ? $_GET['group_id']:0;
			getGroupQuotes($groupId);
		}else if($action == 'updateGroupQuote'){
			$groupId = ($_GET && isset($_GET['group_id'])) ? $_GET['group_id']:0;
			$playerId = ($_GET && isset($_GET['player_id'])) ? $_GET['player_id']:0;
			$quote = ($_GET && isset($_GET['quote'])) ? $_GET['quote']:0;
			updateGroupQuote($groupId, $playerId, $quote);
		}else if($action == 'sendReport'){
			$userId = ($_GET && isset($_GET['user_id'])) ? $_GET['user_id']:0;
			$groupId = ($_GET && isset($_GET['group_id'])) ? $_GET['group_id']:0;
			$challengeId = ($_GET && isset($_GET['challenge_id'])) ? $_GET['challenge_id']:0;
			$description = ($_GET && isset($_GET['description'])) ? $_GET['description']:'';
			$screenshot = ($_GET && isset($_GET['screenshot'])) ? $_GET['screenshot']:'';
			sendReport($userId, $groupId, $challengeId, $description, $screenshot);
		}else if($action == 'getNotifications'){
			$receiverId = ($_GET && isset($_GET['user_id'])) ? $_GET['user_id']:0;
			getNotifications($receiverId);
		}else if($action == 'acceptChallenge'){
			$userId = ($_GET && isset($_GET['user_id'])) ? $_GET['user_id']:0;
			$challengeId = ($_GET && isset($_GET['challenge_id'])) ? $_GET['challenge_id']:0;
			acceptChallenge($challengeId, $userId);
		}else if($action == 'setNotificationSetting'){
			$userId = ($_GET && isset($_GET['user_id'])) ? $_GET['user_id']:0;
			$groupId = ($_GET && isset($_GET['group_id'])) ? $_GET['group_id']:0;
			$challengeId = ($_GET && isset($_GET['challenge_id'])) ? $_GET['challenge_id']:0;
			$status = ($_GET && isset($_GET['status'])) ? $_GET['status']:0;
			setNotificationSetting($userId, $groupId, $challengeId, $status);
			
		}else if($action == 'getPlayerScoreDetail'){
			
			$playerId = ($_GET && isset($_GET['player_id'])) ? $_GET['player_id']:0;
			$groupId = ($_GET && isset($_GET['group_id'])) ? $_GET['group_id']:0;
			getPlayerScoreDetail($groupId, $playerId);
			
		}else if($action == 'updateGroupFavorite'){
			
			$groupId = ($_GET && isset($_GET['groupId'])) ? $_GET['groupId']:'';
			$userId = ($_GET && isset($_GET['userId'])) ? $_GET['userId']:'';
			$isFavorite = ($_GET && isset($_GET['favorite'])) ? $_GET['favorite']:'';
			
			updateGroupFavorite($groupId, $userId, $isFavorite);
		}else if($action == 'getUserScoreDetail'){
			
			$userId = ($_GET && isset($_GET['user_id'])) ? $_GET['user_id']:0;
			getUserScoreDetail($userId);
			
		}else if($action == 'getFriendScores'){
			
			$userId = ($_GET && isset($_GET['user_id'])) ? $_GET['user_id']:0;
			getFriendScores($userId);
			
		}else if($action == 'getPublicGroupScores'){
			
			getPublicGroupScores();
			
		}else if($action == 'getUserPublicScoreDetail'){
			
			$userId = ($_GET && isset($_GET['user_id'])) ? $_GET['user_id']:0;
			getUserPublicScoreDetail($userId);
			
		}else if($action == 'getActiveChallenges'){
			
			$userId = ($_GET && isset($_GET['user_id'])) ? $_GET['user_id']:0;
			getActiveChallenges($userId);
			
		}else if($action == 'updateUserPublicLeaderboardOption'){
			
			$userId = ($_GET && isset($_GET['user_id'])) ? $_GET['user_id']:0;
			$option = ($_GET && isset($_GET['option'])) ? $_GET['option']:0;
			updateUserPublicLeaderboardOption($userId, $option);
			
		}
	}
	

	// Functions
	
	function getAppVersion(){
		global $service;
		$result = $service->getAppVersion();
		
		echo $result;
		exit;
	}
	function userLogin($userName, $socialId, $socialName, $email, $photoUrl, $deviceToken, $deviceType, $socialType){
		global $service;
		$user = $service->userLogin($userName, $socialId, $socialName, $email, $photoUrl, $deviceToken, $deviceType, $socialType);
		
		$arrayData = array();
		$arrayData['userId'] = $user->getUserId();
		$arrayData['userName'] = $user->getUserName();
		$arrayData['pictureUrl'] = $user->getPictureUrl();
		$arrayData['accessToken'] = $user->getAccessToken();
		$arrayData['language'] = $user->getLanguage();
		$arrayData['userType'] = $user->getUserType();
		$arrayData['socialType'] = $user->getSocialType();
		$arrayData['publicLeaderboard'] = $user->getPublicLeaderboard();
		echo json_encode(array('result'=>$arrayData));
		exit;
	}
	function searchUsers($userId, $keyword){
		
		global $service;
		$users = $service->searchUsers($userId, $keyword);
		
		echo json_encode(array('result'=>$users));
		exit;
	}
	function addUserFriend($userId, $friendId){
		global $service;
		$result = $service->addUserFriend($userId, $friendId);
		
		echo json_encode(array('result'=>$result));
		exit;
	}
	
	function updateLanguage($userId, $language){
		global $service;
		$result = $service->updateLanguage($userId, $language);
		if($result == null)
			echo json_encode(array('result'=>array('success'=>0)));
		else
			echo json_encode(array('result'=>array('success'=>1)));
		exit;
	}
	function getAllGroups(){
		global $service;
		$result = $service->getAllGroups();
		
		echo json_encode(array('result'=>$result));
		exit;
	}
	function getPublicGroups($userId){
		global $service;
		$result = $service->getPublicGroups($userId);
		echo json_encode(array('result'=>$result));
		exit;
	}
	function getPrivateGroups($userId){
		global $service;
		$result = $service->getPrivateGroups($userId);
		echo json_encode(array('result'=>$result));
		exit;
	}
	function createGroup($title, $userId, $groupImage, $groupType){
		global $service;
		$result = $service->createGroup($title, $userId, $groupImage, $groupType);
		
		echo json_encode(array('result'=>$result->toArray()));
		exit;
	}
	function getGroup($groupId){
		global $service;
		$result = $service->getGroup($groupId);
		
		echo json_encode(array('result'=>$result->toArray()));
		exit;
	}
	function editGroup($groupId, $title, $groupImage){
		global $service;
		$result = $service->editGroup($groupId, $title, $groupImage);
		
		echo json_encode(array('result'=>$result->toArray()));
		exit;
	}
	function deleteGroup($groupId){
		global $service;
		$result = $service->deleteGroup($groupId);
		
		echo json_encode(array('result'=>$result));
		exit;
	}
	function updateGroupStatus($groupId, $status){

		global $service;
		$result = $service->updateGroupStatus($groupId, $status);
		echo json_encode(array('result'=>$result));
		exit;
		
	}
	function getChallenges($userId, $groupId){

		global $service;
		$result = $service->getChallengesByGroup($userId, $groupId);
		
		echo json_encode(array('result'=>$result));
		exit;
	}
	function createChallenge($groupId, $shortTitle, $description, $duration, $durationType, $challengeImage, $creatorId, $challengeType, $acceptDuration, $acceptDurationType, $rewardDescription, $rewardType, $resendOption){

		global $service;
		$result = $service->createChallenge($groupId, $shortTitle, $description, $duration, $durationType, $challengeImage, $creatorId, $challengeType, $acceptDuration, $acceptDurationType, $rewardDescription, $rewardType, $resendOption);
		
		if($result != null)
			echo json_encode(array('result'=>$result->toArray()));
		else
			echo json_encode(array('result'=>array()));
		exit;
	}
	function editChallenge($challengeId, $description, $duration, $durationType, $challengeImage, $acceptDuration, $acceptDurationType, $resendOption){

		global $service;
		$challenge = $service->editChallenge($challengeId, $description, $duration, $durationType, $challengeImage, $acceptDuration, $acceptDurationType, $resendOption);
		
		echo json_encode(array('result'=>$challenge->toArray()));
		exit;
	}
	function getChallenge($challengeId){

		global $service;
		$challenge = $service->getChallenge($challengeId);
		
		echo json_encode(array('result'=>$challenge->toArray()));
		exit;
	}
	function updateChallengeStatus($challengeId, $status){

		global $service;
		$challenge = $service->updateChallengeStatus($challengeId, $status);
		
		if($challenge == null)
			echo json_encode(array('result'=>array()));
		else
			echo json_encode(array('result'=>$challenge->toArray()));
		exit;
	}
	function deleteChallenge($challengeId){
		global $service;
		$result = $service->deleteChallenge($challengeId);
		
		echo json_encode(array('result'=>$result));
		exit;
	}

	function getFriends($userId){
		global $service;
		
		$result = $service->getUserFriends($userId);
		
		echo json_encode(array('result'=>$result));
		exit;
	}
	function getPlayerId($groupId, $userId){
		global $service;
		$playerId = $service->getPlayerId($groupId, $userId);
		
		echo json_encode(array('result'=>$playerId));
		exit;
	}
	function getGroupPlayers($groupId){
		global $service;
		$result = $service->getGroupPlayers($groupId);
		
		echo json_encode(array('result'=>$result));
		exit;
	}
	function addPlayer($groupId, $userId){
		global $service;
		$result = $service->addPlayer($groupId, $userId);
		
			echo json_encode(array('result'=>$result));
		exit;
	}
	function removePlayer($playerId){
		global $service;
		$result = $service->removePlayer($playerId);
		
		echo json_encode(array('result'=>$result));
		exit;
	}
	function addPlayers($groupId, $userIds, $invitor){
		global $service;
		foreach($userIds as $userId){
			$result = $service->addPlayer($groupId, $userId, $invitor);
		}
		echo json_encode(array('result'=>$result));
		exit;
	}

	function getChallengeStatus($challengeId){
		global $service;
		$result = $service->getChallengeStatusByChallenge($challengeId);
		echo json_encode(array('result'=>$result));
		exit;
	}

	function updatePlayerStatus($challengeId, $playerId, $playerScore, $playerStatus){
		global $service;
		$result = $service->updatePlayerStatus($challengeId, $playerId, $playerScore, $playerStatus);
		echo json_encode(array('result'=>$result->toArray()));
		exit;
	}
	function completeChallenge($challengeId, $playerId, $comment){
		global $service;
		$result = $service->completeChallenge($challengeId, $playerId, $comment);
		echo json_encode(array('result'=>$result->toArray()));
		exit;
	}
	function giveupChallenge($challengeId, $playerId, $comment){
		global $service;
		$result = $service->giveupChallenge($challengeId, $playerId, $comment);
		echo json_encode(array('result'=>$result->toArray()));
		exit;
	}
	function getTopPlayers($groupId){
		global $service;
		$result = $service->getTopPlayers($groupId);
		echo json_encode(array('result'=>$result));
		exit;
	}
	function getGroupComments($groupId, $pageIndex){
		global $service;
		$result = $service->getGroupComments($groupId, $pageIndex);
		echo json_encode(array('result'=>$result));
		exit;
	}
	function getChallengeComments($challengeId, $pageIndex){
		global $service;
		$result = $service->getChallengeComments($challengeId, $pageIndex);
		echo json_encode(array('result'=>$result));
		exit;
	}
	function addGroupComment($groupId, $playerId, $comment, $type){
		global $service;
		$result = $service->addGroupComment($groupId, $playerId, $comment, $type);
		echo json_encode(array('result'=>$result));
		exit;
	}
	function addChallengeComment($groupId, $challengeId, $playerId, $comment, $type){
		global $service;
		$result = $service->addChallengeComment($groupId, $challengeId, $playerId, $comment, $type);
		echo json_encode(array('result'=>$result));
		exit;
	}
    function updateProfileImage($userId, $profileUrl){
        global $service;
		$result = $service->updateProfileImage($userId, $profileUrl);
		echo json_encode(array('result'=>$result->toArray()));
		exit;
    }
    function updateUser($userId, $userName, $profileUrl, $publicLeaderboard){
        global $service;
		
		$user = $service->updateUser($userId, $userName, $profileUrl, $publicLeaderboard);
		$arrayData = array();
		$arrayData['userId'] = $user->getUserId();
		$arrayData['userName'] = $user->getUserName();
		$arrayData['pictureUrl'] = $user->getPictureUrl();
		$arrayData['publicLeaderboard'] = $user->getPublicLeaderboard();
		$arrayData['accessToken'] = $user->getAccessToken();
		
		echo json_encode(array('result'=>$arrayData));
		exit;
    }
	function updateGroupQuote($groupId, $playerId, $quote){
        global $service;
		$result = $service->updateGroupQuote($groupId, $playerId, $quote);
		echo json_encode(array('result'=>$result->toArray()));
		exit;
	}
	function getGroupQuotes($groupId){
        global $service;
		$result = $service->getGroupQuote($groupId);
		if($result != null)
			echo json_encode(array('result'=>$result->toArray()));
		else
			echo json_encode(array('result'=>array()));
		exit;
	}
	function sendReport($userId, $groupId, $challengeId, $description, $screenshot){
        global $service;
		$result = $service->sendReport($userId, $groupId, $challengeId, $description, $screenshot);
		
		echo $result;
		exit;
	}
	function getNotifications($receiverId){
        global $service;
		$result = $service->getNotifications($receiverId);
		
		echo $result;
		exit;
	}
	function acceptChallenge($challengeId, $userId){
        global $service;
		$result = $service->acceptChallenge($challengeId, $userId);
		
		echo $result;
		exit;
	}
	function setNotificationSetting($userId, $groupId, $challengeId, $status){
        global $service;
		$result = $service->setUserNotificationSetting($userId, $groupId, $challengeId, $status);
		
		echo $result;
		exit;
	}
	
	function getPlayerScoreDetail($groupId, $playerId){
        global $service;
		$result = $service->getPlayerScoreDetail($groupId, $playerId);
		
		echo $result;
		exit;
	}
	function updateGroupFavorite($groupId, $userId, $isFavorite){
		global $service;
		$result = $service->updateGroupFavorite($groupId, $userId, $isFavorite);;
		
		echo json_encode(array('result'=>$result));
		exit;
	}
	
	// Phase 6 New Functions
	function getUserScoreDetail($userId){
        global $service;
		$result = $service->getUserScoreDetail($userId);
		
		echo $result;
		exit;
	}
	
	function getFriendScores($userId){
        global $service;
		$result = $service->getFriendScores($userId);
		
		echo $result;
		exit;
	}
	function getPublicGroupScores(){
        global $service;
		$result = $service->getPublicGroupScores();
		
		echo $result;
		exit;
	}	
	
	function getUserPublicScoreDetail($userId){
		global $service;
		$result = $service->getUserPublicScoreDetail($userId);
		
		echo $result;
		exit;
	}
	
	function getActiveChallenges($userId){
		global $service;
		$result = $service->getActiveChallenges($userId);
		
		echo $result;
		exit;
	}
	
	function updateUserPublicLeaderboardOption($userId, $option){
		global $service;
		$result = $service->updateUserPublicLeaderboardOption($userId, $option);
		
		if($result == null)
			echo json_encode(array('result'=>array('success'=>0)));
		else{
			
			echo json_encode(array('result'=>array('success'=>1, 'accessToken'=>$result->getAccessToken())));
		}
			
		exit;
	}
	
?>