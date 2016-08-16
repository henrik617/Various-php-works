<?php
	
	require_once('MySQL.php');
	require_once('Basic/Comment.php');
	require_once('Basic/Group.php');
	require_once('Basic/User.php');
	require_once('Basic/Player.php');
	require_once('Basic/Notification.php');
	require_once('Basic/ChallengeStatus.php');
	require_once('Basic/Quote.php');
	require_once('Basic/Challenge.php');
	
	class DataStore{

		protected $_db = null;
		protected $_PAGE_LIMIT = 10;
		public function __construct() {
			
			include('configuration.php');
			$this->_db = new MySQL($__DB_NAME, $__DB_USER, $__DB_PASS, $__DB_HOST);
		}

		//-----------------	User Mangement	---------------//

		public function addUser($user) {

			$values = array(
				'username'	=>	$user->getUserName(),
				'social_username'	=>	$user->getSocialName(),
				'social_userid'	=>	$user->getSocialId(),
				'social_email'	=>	$user->getSocialEmail(),
				'user_create_date' => $user->getCreateDate(),
				'picture_url'	=>	$user->getPictureUrl(),
				'is_blocked'	=>	$user->isBlocked(),
				'user_last_seen'	=>	$user->getLastSeenDate(),
				'user_device'	=>	$user->getUserDevice(),
				'payment_amount'	=>	$user->getPaymentAmount(),
				'payment_currency'	=>	$user->getPaymentCurrency(),
				'last_payment_date' => $user->getLastPaymentDate(),
				'access_token' => $user->getAccessToken(),
				'language' => $user->getLanguage(),
				'user_type' => $user->getUserType(),
				'social_type' => $user->getSocialType(),
				'public_leaderboard' => $user->getPublicLeaderboard()
			);

			$this->_db->Insert('users', $values);

			return $this->_db->LastInsertID();
		}
		public function isTokenValid($access_token) {
			$where = array('access_token' => $access_token);
			$this->_db->Select('users', $where);
			$result = $this->_db->ArrayResult();
	
			if(empty($result))
				return false;

			return true;
		}
		public function updateUser($user) {

			$where = array('userid' => $user->getUserId());
			$values = array(
				'username'	=>	$user->getUserName(),
				'social_username'	=>	$user->getSocialName(),
				'social_userid'	=>	$user->getSocialId(),
				'social_email'	=>	$user->getSocialEmail(),
				'user_create_date' => $user->getCreateDate(),
				'picture_url'	=>	$user->getPictureUrl(),
				'is_blocked'	=>	$user->isBlocked(),
				'user_last_seen'	=>	$user->getLastSeenDate(),
				'user_device'	=>	$user->getUserDevice(),
				'payment_amount'	=>	$user->getPaymentAmount(),
				'payment_currency'	=>	$user->getPaymentCurrency(),
				'last_payment_date' => $user->getLastPaymentDate(),
				'access_token' => $user->getAccessToken(),
				'language' => $user->getLanguage(),
				'user_type' => $user->getUserType(),
				'social_type' => $user->getSocialType(),
				'public_leaderboard' => $user->getPublicLeaderboard()
			);
			
			
			return $this->_db->Update('users', $values, $where);
		}
		
		public function updateLanguage($user) {

			$where = array('userid' => $user->getUserId());
			$values = array(
				'language' => $user->getLanguage()				
			);
			return $this->_db->Update('users', $values, $where);
		}
		
		public function getUserByEmail($email) {
			$where = array('social_email' => $email);
			$this->_db->Select('users', $where);
			$result = $this->_db->ArrayResult();
	
			if(empty($result))
				return null;

			$user = new User();
			$user->setUserId($result['userid']);
			$user->setUserName($result['username']);
			$user->setSocialName($result['social_username']);
			$user->setSocialId($result['social_userid']);
			$user->setSocialEmail($result['social_email']);
			$user->setCreateDate($result['user_create_date']);
			$user->setPictureUrl($result['picture_url']);
			$user->setBlocked($result['is_blocked']);
			$user->setLastSeenDate($result['user_last_seen']);
			$user->setUserDevice($result['user_device']);
			$user->setPaymentAmount($result['payment_amount']);
			$user->setPaymentCurrency($result['payment_currency']);
			$user->setLastPaymentDate($result['last_payment_date']);
			$user->setLanguage($result['language']);
			$user->setUserType($result['user_type']);
			$user->setSocialType($result['social_type']);
			$user->setPublicLeaderboard($result['public_leaderboard']);
			
			return $user;
		}
		public function getUserBySocialId($socialId, $socialType) {
			$where = array('social_userid' => $socialId, 'social_type' => $socialType);
			$this->_db->Select('users', $where);
			$result = $this->_db->ArrayResult();
	
			if(empty($result))
				return null;

			$user = new User();
			$user->setUserId($result['userid']);
			$user->setUserName($result['username']);
			$user->setSocialName($result['social_username']);
			$user->setSocialId($result['social_userid']);
			$user->setSocialEmail($result['social_email']);
			$user->setCreateDate($result['user_create_date']);
			$user->setPictureUrl($result['picture_url']);
			$user->setBlocked($result['is_blocked']);
			$user->setLastSeenDate($result['user_last_seen']);
			$user->setUserDevice($result['user_device']);
			$user->setPaymentAmount($result['payment_amount']);
			$user->setPaymentCurrency($result['payment_currency']);
			$user->setLastPaymentDate($result['last_payment_date']);
			$user->setLanguage($result['language']);
			$user->setUserType($result['user_type']);
			$user->setSocialType($result['social_type']);
			$user->setPublicLeaderboard($result['public_leaderboard']);
			
			return $user;
		}
		public function getUserById($userId){
			
			$where = array('userid' => $userId);
			$this->_db->Select('users', $where);
			$result = $this->_db->ArrayResult();
	
			if(empty($result))
				return null;

			$user = new User();
			$user->setUserId($result['userid']);
			$user->setUserName($result['username']);
			$user->setSocialName($result['social_username']);
			$user->setSocialId($result['social_userid']);
			$user->setSocialEmail($result['social_email']);
			$user->setCreateDate($result['user_create_date']);
			$user->setPictureUrl($result['picture_url']);
			$user->setBlocked($result['is_blocked']);
			$user->setLastSeenDate($result['user_last_seen']);
			$user->setUserDevice($result['user_device']);
			$user->setPaymentAmount($result['payment_amount']);
			$user->setPaymentCurrency($result['payment_currency']);
			$user->setLastPaymentDate($result['last_payment_date']);
			$user->setLanguage($result['language']);
			$user->setUserType($result['user_type']);
			$user->setSocialType($result['social_type']);
			$user->setPublicLeaderboard($result['public_leaderboard']);
			
			return $user;
		}
		
		public function searchUsersByName($keyword){
			
			$where = "`social_username` LIKE '%" . $keyword . "%' or 'username' LIKE '%" . $keyword ."%'";
			$this->_db->SelectCustom('users', $where);
			
			$results = $this->_db->ArrayResults();
			
			if(empty($results))
				return array();
			foreach($results as $result){
				//if($result['userid'] == 1)
				//	continue;
				$user = new User();
				$user->setUserId($result['userid']);
				$user->setUserName($result['username']);
				$user->setSocialName($result['social_username']);
				$user->setSocialId($result['social_userid']);
				$user->setSocialEmail($result['social_email']);
				$user->setCreateDate($result['user_create_date']);
				$user->setPictureUrl($result['picture_url']);
				$user->setBlocked($result['is_blocked']);
				$user->setLastSeenDate($result['user_last_seen']);
				$user->setUserDevice($result['user_device']);
				$user->setPaymentAmount($result['payment_amount']);
				$user->setPaymentCurrency($result['payment_currency']);
				$user->setLastPaymentDate($result['last_payment_date']);
				$user->setLanguage($result['language']);
				$user->setUserType($result['user_type']);
				$user->setSocialType($result['social_type']);
				$user->setPublicLeaderboard($result['public_leaderboard']);
				
				$users[] = $user->toArray();
			}
			return $users;
		}
		/*
		public function isUserExist($email) {
			$where = array('social_email' => $email);
			$this->_db->Select('users', $where);
			$result = $this->_db->ArrayResult();
			if(empty($result))
				return false;
			return true;
		}
		*/
		public function isUserExist($socialId, $socialType) {
			$where = array('social_userid' => $socialId, 'social_type' => $socialType);
			$this->_db->Select('users', $where);
			$result = $this->_db->ArrayResult();
			if(empty($result))
				return false;
			return true;
		}

		public function getAllUsers(){
			$users = array();
			$where = array();
			$this->_db->Select('users', $where, 'user_last_seen desc');
			$results = $this->_db->ArrayResults();
			if(empty($results))
				return null;
			foreach($results as $result){
				//if($result['userid'] == 1)
				//	continue;
				$user = new User();
				$user->setUserId($result['userid']);
				$user->setUserName($result['username']);
				$user->setSocialName($result['social_username']);
				$user->setSocialId($result['social_userid']);
				$user->setSocialEmail($result['social_email']);
				$user->setCreateDate($result['user_create_date']);
				$user->setPictureUrl($result['picture_url']);
				$user->setBlocked($result['is_blocked']);
				$user->setLastSeenDate($result['user_last_seen']);
				$user->setUserDevice($result['user_device']);
				$user->setPaymentAmount($result['payment_amount']);
				$user->setPaymentCurrency($result['payment_currency']);
				$user->setLastPaymentDate($result['last_payment_date']);
				$user->setLanguage($result['language']);
				$user->setUserType($result['user_type']);
				$user->setSocialType($result['social_type']);
				$user->setPublicLeaderboard($result['public_leaderboard']);
				
				$users[] = $user->toArray();
			}
			return $users;
		}

		//---------------------	User Mangement End	-------------------------------//

		//-----------------	Friend Mangement	---------------//

		public function addFriend($user_id, $friend_user_id) {

			$values = array(
				'user_id'	=>	$user_id,
				'friend_user_id'	=>	$friend_user_id,
			);
			$this->_db->Insert('friends', $values);

			$values = array(
				'user_id'	=>	$friend_user_id,
				'friend_user_id'	=>	$user_id,
			);
			$this->_db->Insert('friends', $values);
			
			return $this->_db->LastInsertID();
		}
		
		public function getUserFriends($userId) {
			$where = array('user_id' => $userId, 'link_deactive' => 0);
			$this->_db->Select('friends', $where);
			$results = $this->_db->ArrayResults();
	
			$friends = array();
			if(empty($results))
				return $friends;
			
			foreach($results as $result){
				$friend = array();
				$friendInfo = $this->getUserById($result['friend_user_id']);
				$deviceTokenInfo = $this->getTokenByUserId($result['friend_user_id']);
				
				$friend['friendId'] = $result['friend_user_id'];
				$friend['linkTimeStamp'] = $result['link_ts'];
				$friend['friendInfo'] = $friendInfo->toArray();
				$friend['deviceToken'] = $deviceTokenInfo;
				$friends[] = $friend;
			}
			
			return $friends;
		}
		
		public function deleteFriend($id){
			
			$where = array('id' => $id);
			$result = $this->_db->Delete('friend', $where);
			
			return $result;
		}
		
		public function isFriend($user_id, $friend_id){
			$users = array();
			$where = array('user_id' => $user_id, 'friend_user_id'=>$friend_id);
			$this->_db->Select('friends', $where);
			$result = $this->_db->ArrayResult();
			if(empty($result))
				return false;
			return true;
		}
		
		//---------------------	Friend Mangement End	-------------------------------//
		
		//---------------------	User Segment Mangement Start	-------------------------------//
		
		public function addUserSegment($sname, $userIds) {

			$values = array(
				'sname'		=>	$sname,
				'user_ids'	=>	serialize($userIds)
			);
			$this->_db->Insert('user_segment', $values);

			return $this->_db->LastInsertID();
		}
		public function updateUserSegment($id, $sname, $userIds){
			$where = array('id' => $id);
			$values = array(
				'sname'		=>	$sname,
				'user_ids'	=>	serialize($userIds)
			);
			return $this->_db->Update('user_segment', $values, $where);
		}
		public function deleteUserSegment($id){
			$where = array('id' => $id);
			return $this->_db->Delete('user_segment', $where);					
		}		
				
		public function getAllUserSegments(){
	
			$segments = array();
			$where = array();
			$this->_db->Select('user_segment', $where);
			$results = $this->_db->ArrayResults();

			if(empty($results))
				return $segments;
			
			foreach($results as $result){
				$temp['id'] = $result['id'];
				$temp['sname'] = $result['sname'];
				$temp['userIds'] = unserialize($result['user_ids']);
				
				$segments[] = $temp;
			}
			return $segments;
		}
		
		public function getUserSegment($id){
	
			$segment = array();
			$where = array('id' => $id);
			$this->_db->Select('user_segment', $where);
			$result = $this->_db->ArrayResult();

			if(empty($result))
				return $segment;
			
			$segment['id'] = $result['id'];
			$segment['sname'] = $result['sname'];
			$segment['userIds'] = unserialize($result['user_ids']);
			
			return $segment;
		}
		
		public function getSegmentUsers($id){
	
			$users = array();
			$where = array('id' => $id);
			$this->_db->Select('user_segment', $where);
			$result = $this->_db->ArrayResult();

			if(empty($result))
				return users;

			return unserialize($result['user_ids']);
		}
		
		//---------------------	User Segment Mangement End	-------------------------------//
		
		//---------------------	Group Mangement Start------------------------------//
		
		public function addGroup($group){
			$values = array(
				'group_title'		=>	$group->getGroupTitle(),
				'group_image'		=>	$group->getGroupImage(),
				'group_creator_id'	=>	$group->getCreatorId(),
				'group_type'		=>	$group->getGroupType(),
				'group_create_date'	=>	$group->getCreateDate(),
				'group_status'		=>	$group->getGroupStatus(),
				'group_lastupdate' 	=> $group->getLastUpdateDate()
			);
			$this->_db->Insert('groups', $values);

			return $this->_db->LastInsertID();
		}
		public function updateGroup($group){
			$where = array('group_id' => $group->getGroupId());
			$values = array(
				'group_title'	=>	$group->getGroupTitle(),
//				'group_image'	=>	$group->getGroupImage(),
				'group_creator_id'	=>	$group->getCreatorId(),
				'group_create_date'	=>	$group->getCreateDate(),
				'group_status'	=>	$group->getGroupStatus(),
				'group_lastupdate' => $group->getLastUpdateDate()
			);
			return $this->_db->Update('groups', $values, $where);
		}
		public function deleteGroup($groupId){
			/*
			$where = array('group_id' => $groupId);
			return $this->_db->Delete('groups', $where);
			*/
			
			$where = array('group_id' => $groupId);
			$values = array(
				'deleted' => 1
			);
			return $this->_db->Update('groups', $values, $where);
		}
		public function getGroupById($groupId){
			
			$where = array('group_id' => $groupId, 'deleted'=>0);
			$orderBy = 'group_priority desc, group_create_date desc';
			$this->_db->Select('groups', $where, $orderBy);
			$result = $this->_db->ArrayResult();
	
			if(empty($result))
				return null;

			$group = new Group();
			$group->setGroupId($result['group_id']);
			$group->setGroupTitle($result['group_title']);
			$group->setGroupImage($result['group_image']);
			$group->setCreatorId($result['group_creator_id']);
			$group->setGroupType($result['group_type']);
			$group->setCreateDate($result['group_create_date']);
			$group->setGroupStatus($result['group_status']);
			$group->setLastUpdateDate($result['group_lastupdate']);
			
			return $group;
		}
		public function getAllGroups(){
			$groups = array();
			$where = array('deleted'=>0);
			$orderBy = 'group_priority desc, group_create_date desc';
			$this->_db->Select('groups', $where, $orderBy);
			$results = $this->_db->ArrayResults();
			if(empty($results))
				return null;
			foreach($results as $result){
				$group = new Group();
				$group->setGroupId($result['group_id']);
				$group->setGroupTitle($result['group_title']);
				$group->setGroupImage($result['group_image']);
				$group->setCreatorId($result['group_creator_id']);
				$group->setGroupType($result['group_type']);
				$group->setCreateDate($result['group_create_date']);
				$group->setGroupStatus($result['group_status']);
				$group->setLastUpdateDate($result['group_lastupdate']);
				$groups[] = $group->toArray();
			}
			return $groups;
		}
		public function getGroupsByUserId($userId, $groupType){
			$groups = array();
			$where = array('deleted'=>0, 'group_type'=>$groupType);
			$orderBy = 'group_priority desc, group_create_date desc';
			$this->_db->Select('groups', $where, $orderBy);
			$results = $this->_db->ArrayResults();
			if(empty($results))
				return null;
			foreach($results as $result){
				
				$group = new Group();
				$group->setGroupId($result['group_id']);
				$group->setGroupTitle($result['group_title']);
				$group->setGroupImage($result['group_image']);
				$group->setCreatorId($result['group_creator_id']);
				$group->setGroupType($result['group_type']);
				$group->setCreateDate($result['group_create_date']);
				$group->setGroupStatus($result['group_status']);
				$group->setLastUpdateDate($result['group_lastupdate']);
				$temp = $group->toArray();
				
				if($groupType == 1){
					if($userId == "-1"){
						$temp['joined'] = 0;
					}else{
						if($this->getPlayerByGroupAndUserId($result['group_id'], $userId)){
							$temp['joined'] = 1;
						}else{
							$temp['joined'] = 0;
						}
					}
					$groups[] = $temp;
					
				}else{
					if($this->getPlayerByGroupAndUserId($result['group_id'], $userId)){
						$groups[] = $temp;
					}
				}
				
			}
			return $groups;
		}
		public function addFavoriteGroup($groupId, $userId){
			
			$values = array(
				'group_id'		=>	$groupId,
				'user_id'		=>	$userId
			);
			$this->_db->Insert('group_favorite', $values);

			return $this->_db->LastInsertID();
			
		}
		public function removeFavoriteGroup($groupId, $userId){
			
			$where = array(
				'group_id'		=>	$groupId,
				'user_id'		=>	$userId
			);
			return $this->_db->Delete('group_favorite', $where);

		}
		
		public function isFavoriteGroup($groupId, $userId){
			
			$where = array(
				'group_id'		=>	$groupId,
				'user_id'		=>	$userId
			);
			
			$this->_db->Select('group_favorite', $where);
			$result = $this->_db->ArrayResult();
			
			if(empty($result))
				return false;
			return true;
		}
		//---------------------	Group Mangement End------------------------------//

		//---------------------	Player Mangement Start------------------------------//
		
		public function addPlayer($player){

			$values = array(
				'group_id'	=>	$player->getGroupId(),
				'user_id'	=>	$player->getUserId(),
				'group_score'	=>	$player->getGroupScore(),
				'challenge_played'	=>	$player->getChallengePlayed(),
				'challenge_completed'	=>	$player->getChallengeCompleted(),
				'challenge_giveup'	=>	$player->getChallengeGiveup()
			);
			$this->_db->Insert('group_players', $values);

			return $this->_db->LastInsertID();
		}
		public function deletePlayer($playerId){

			$where = array('player_id' => $playerId);
			$this->_db->Delete('group_players', $where);
		}
		public function updatePlayer($player){
			$where = array('player_id' => $player->getPlayerId());
			$values = array(
				'group_id'	=>	$player->getGroupId(),
				'user_id'	=>	$player->getUserId(),
				'group_score'	=>	$player->getGroupScore(),
				'challenge_played'	=>	$player->getChallengePlayed(),
				'challenge_completed'	=>	$player->getChallengeCompleted(),
				'challenge_giveup'	=>	$player->getChallengeGiveup(),
			);
			$this->_db->Update('group_players', $values, $where);
		}
		public function getPlayerById($playerId){
			
			$where = array('player_id' => $playerId);
			$this->_db->Select('group_players', $where);
			$result = $this->_db->ArrayResult();
	
			if(empty($result))
				return null;

			$player = new Player();
			$player->setPlayerId($result['player_id']);

			$user = $this->getUserById($result['user_id']);
			$player->setPlayerName($user->getUserName());
			$player->setPhotoUrl($user->getPictureUrl());

			$player->setGroupId($result['group_id']);
			$player->setUserId($result['user_id']);
			$player->setGroupScore($result['group_score']);
			$player->setChallengePlayed($result['challenge_played']);
			$player->setChallengeCompleted($result['challenge_completed']);
			$player->setChallengeGiveup($result['challenge_giveup']);

			return $player;
		}
		public function getPlayerByGroupAndUserId($groupId, $userId){
			$where = array('group_id' => $groupId, 'user_id' => $userId);
			$this->_db->Select('group_players', $where);
			$result = $this->_db->ArrayResult();
			if(empty($result))
				return null;

			$player = new Player();
			$player->setPlayerId($result['player_id']);

			$user = $this->getUserById($result['user_id']);
			$player->setPlayerName($user->getUserName());
			$player->setPhotoUrl($user->getPictureUrl());

			$player->setGroupId($result['group_id']);
			$player->setUserId($result['user_id']);
			$player->setGroupScore($result['group_score']);
			$player->setChallengePlayed($result['challenge_played']);
			$player->setChallengeCompleted($result['challenge_completed']);
			$player->setChallengeGiveup($result['challenge_giveup']);

			return $player;
		}
		public function getPlayerNameById($playerId){
			$where = array('player_id' => $playerId);
			$this->_db->Select('group_players', $where);
			$result = $this->_db->ArrayResult();
	
			if(empty($result))
				return "";
			$user = $this->getUserById($result['user_id']);
			if($user)
				return $user->getUserName();
			return "";
		}
		public function getPlayersByGroup($groupId){
			$players = array();
			$where = array('group_id' => $groupId);
			$this->_db->Select('group_players', $where);
			$results = $this->_db->ArrayResults();
			if(empty($results))
				return null;

			foreach($results as $result){
				$player = new Player();
				$player->setPlayerId($result['player_id']);

				$user = $this->getUserById($result['user_id']);
				$player->setPlayerName($user->getUserName());
				$player->setPhotoUrl($user->getPictureUrl());

				$player->setGroupId($result['group_id']);
				$player->setUserId($result['user_id']);
				$player->setGroupScore($result['group_score']);
				$player->setChallengePlayed($result['challenge_played']);
				$player->setChallengeCompleted($result['challenge_completed']);
				$player->setChallengeGiveup($result['challenge_giveup']);

				$players[] = $player->toArray();
			}
			return $players;
		}
		public function getTopPlayersByGroup($groupId){
			$players = array();
			$where = array('group_id' => $groupId);
			$orderBy = 'group_score desc';
			$limit = 1;
			$this->_db->Select('group_players', $where, $orderBy, $limit);
			$results = $this->_db->ArrayResults();
			if(empty($results))
				return null;
			foreach($results as $result){
				$player = new Player();
				$player->setPlayerId($result['player_id']);

				$user = $this->getUserById($result['user_id']);
				$player->setPlayerName($user->getUserName());
				$player->setPhotoUrl($user->getPictureUrl());

				$player->setGroupId($result['group_id']);
				$player->setUserId($result['user_id']);
				$player->setGroupScore($result['group_score']);
				$player->setChallengePlayed($result['challenge_played']);
				$player->setChallengeCompleted($result['challenge_completed']);
				$player->setChallengeGiveup($result['challenge_giveup']);
				$players[] = $player->toArray();
			}
			return $players;
		}

		//---------------------	Player Mangement End------------------------------//

		//---------------------	Challenge Mangement Start------------------------------//
		
		public function addChallenge($challenge){
			$values = array(
				'group_id'					=>	$challenge->getGroupId(),
				'challenge_description'		=>	$challenge->getChallengeDescription(),
				'challenge_duration'		=>	$challenge->getChallengeDuration(),
				'challenge_duration_type'	=>	$challenge->getChallengeDurationType(),
				'challenge_image'			=>	$challenge->getChallengeImage(),
				'challenge_creator'			=>	$challenge->getChallengeCreator(),
				'challenge_create_date'		=>	$challenge->getCreateDate(),
				'challenge_status'			=>	$challenge->getChallengeStatus(),
				
				'challenge_accept_duration'			=>	$challenge->getAcceptDuration(),
				'challenge_accept_duration_type'	=>	$challenge->getAcceptDurationType(),
				'challenge_short_title'				=>	$challenge->getShortTitle(),
				'challenge_type'					=>	$challenge->getChallengeType(),
				'challenge_reward_surprise_flag'	=>	$challenge->getRewardType(),
				'challenge_reward_description'		=>	$challenge->getRewardDescription(),
				'resend_option'						=>	$challenge->getResendOption()
			);
			
			$this->_db->Insert('group_challenges', $values);
			$challengeId = $this->_db->LastInsertID();

			$status = 0;
			if($challenge->getChallengeType() == 0)
				$status = 0;
			else
				$status = -1;
			
			$players = $this->getPlayersByGroup($challenge->getGroupId());
			foreach($players as $player){
				$challengeStatus = new ChallengeStatus();
				$challengeStatus->setChallengeId($challengeId);
				$challengeStatus->setPlayerId($player['playerId']);
				$challengeStatus->setPlayerStatus($status);
				$this->addChallengeStatus($challengeStatus);
			}
			return $challengeId;
		}
		public function updateChallenge($challenge){
			$where = array('challenge_id' => $challenge->getChallengeId());
			$values = array(
				'group_id'					=>	$challenge->getGroupId(),
				'challenge_description'		=>	$challenge->getChallengeDescription(),
				'challenge_duration'		=>	$challenge->getChallengeDuration(),
				'challenge_duration_type'	=>	$challenge->getChallengeDurationType(),
				'challenge_image'			=>	$challenge->getChallengeImage(),
				'challenge_creator'			=>	$challenge->getChallengeCreator(),
				'challenge_create_date'		=>	$challenge->getCreateDate(),
				'challenge_status'			=>	$challenge->getChallengeStatus(),
				
				'challenge_accept_duration'			=>	$challenge->getAcceptDuration(),
				'challenge_accept_duration_type'	=>	$challenge->getAcceptDurationType(),
				'challenge_short_title'				=>	$challenge->getShortTitle(),
				'challenge_type'					=>	$challenge->getChallengeType(),
				'challenge_reward_surprise_flag'	=>	$challenge->getRewardType(),
				'challenge_reward_description'		=>	$challenge->getRewardDescription(),
				'resend_option'						=>	$challenge->getResendOption()
			);
			
			return $this->_db->Update('group_challenges', $values, $where);
		}
		public function deleteChallenge($challengeId){

			/*
			$this->deleteChallengeStatusByChallenge($challengeId);

			$where = array('challenge_id' => $challengeId);
			return $this->_db->Delete('group_challenges', $where);
			*/
			
			$where = array('challenge_id' => $challengeId);
			$values = array(
				'deleted'		=>	1
			);
			return $this->_db->Update('group_challenges', $values, $where);
		}
		public function getAllChallenges(){
			$groups = array();
			$where = array('deleted'=>0);
			
			$orderBy = 'challenge_priority desc, challenge_create_date desc';
			$this->_db->Select('group_challenges', $where, $orderBy);
			
			$results = $this->_db->ArrayResults();
			if(empty($results))
				return null;
			foreach($results as $result){
				$challenge = new Challenge();
				$challenge->setChallengeId($result['challenge_id']);
				$challenge->setGroupId($result['group_id']);
				$challenge->setChallengeDescription($result['challenge_description']);
				$challenge->setChallengeDuration($result['challenge_duration']);
				$challenge->setChallengeDurationType($result['challenge_duration_type']);
				$challenge->setChallengeImage($result['challenge_image']);
				$challenge->setChallengeCreator($result['challenge_creator']);
				$challenge->setCreateDate($result['challenge_create_date']);
				$challenge->setChallengeStatus($result['challenge_status']);
				
				$challenge->setShortTitle($result['challenge_short_title']);
				$challenge->setChallengeType($result['challenge_type']);
				$challenge->setAcceptDuration($result['challenge_accept_duration']);
				$challenge->setAcceptDurationType($result['challenge_accept_duration_type']);
				$challenge->setRewardDescription($result['challenge_reward_description']);
				$challenge->setRewardType($result['challenge_reward_surprise_flag']);
				$challenge->setResendOption($result['resend_option']);
				
				$challenges[] = $challenge->toArray();
			}
			return $challenges;
		}
		public function getChallengesByGroup($groupId){
			$groups = array();
			$where = array('group_id'=>$groupId, 'deleted'=>0);
			
			$orderBy = 'challenge_priority desc, challenge_create_date desc';
			$this->_db->Select('group_challenges', $where, $orderBy);
			
			$results = $this->_db->ArrayResults();
			if(empty($results))
				return null;
			foreach($results as $result){
				$challenge = new Challenge();
				$challenge->setChallengeId($result['challenge_id']);
				$challenge->setGroupId($result['group_id']);
				$challenge->setChallengeDescription($result['challenge_description']);
				$challenge->setChallengeDuration($result['challenge_duration']);
				$challenge->setChallengeDurationType($result['challenge_duration_type']);
				$challenge->setChallengeImage($result['challenge_image']);
				$challenge->setChallengeCreator($result['challenge_creator']);
				$challenge->setCreateDate($result['challenge_create_date']);
				$challenge->setChallengeStatus($result['challenge_status']);
				
				$challenge->setShortTitle($result['challenge_short_title']);
				$challenge->setChallengeType($result['challenge_type']);
				$challenge->setAcceptDuration($result['challenge_accept_duration']);
				$challenge->setAcceptDurationType($result['challenge_accept_duration_type']);
				$challenge->setRewardDescription($result['challenge_reward_description']);
				$challenge->setRewardType($result['challenge_reward_surprise_flag']);
				$challenge->setResendOption($result['resend_option']);
				
				$challenges[] = $challenge;
			}
			return $challenges;
		}
		public function getActiveChallengeByGroup($groupId){
			$groups = array();
			$where = array('group_id'=>$groupId, 'challenge_status'=>0, 'deleted'=>0);
			
			$orderBy = 'challenge_priority desc, challenge_create_date desc';
			$this->_db->Select('group_challenges', $where, $orderBy);
			
			$result = $this->_db->ArrayResult();
			if(empty($result))
				return null;
			
			$challenge = new Challenge();
			$challenge->setChallengeId($result['challenge_id']);
			$challenge->setGroupId($result['group_id']);
			$challenge->setChallengeDescription($result['challenge_description']);
			$challenge->setChallengeDuration($result['challenge_duration']);
			$challenge->setChallengeDurationType($result['challenge_duration_type']);
			$challenge->setChallengeImage($result['challenge_image']);
			$challenge->setChallengeCreator($result['challenge_creator']);
			$challenge->setCreateDate($result['challenge_create_date']);
			$challenge->setChallengeStatus($result['challenge_status']);
			
			$challenge->setShortTitle($result['challenge_short_title']);
			$challenge->setChallengeType($result['challenge_type']);
			$challenge->setAcceptDuration($result['challenge_accept_duration']);
			$challenge->setAcceptDurationType($result['challenge_accept_duration_type']);
			$challenge->setRewardDescription($result['challenge_reward_description']);
			$challenge->setRewardType($result['challenge_reward_surprise_flag']);
			$challenge->setResendOption($result['resend_option']);
			
			return $challenge;
		}
		public function getChallengeById($challengeId){
			
			$where = array('challenge_id' => $challengeId, 'deleted'=>0);
			
			$orderBy = 'challenge_priority desc, challenge_create_date desc';
			$this->_db->Select('group_challenges', $where, $orderBy);
			
			$result = $this->_db->ArrayResult();
	
			if(empty($result))
				return null;

			$challenge = new Challenge();
			$challenge->setChallengeId($result['challenge_id']);
			$challenge->setGroupId($result['group_id']);
			$challenge->setChallengeDescription($result['challenge_description']);
			$challenge->setChallengeDuration($result['challenge_duration']);
			$challenge->setChallengeDurationType($result['challenge_duration_type']);
			$challenge->setChallengeImage($result['challenge_image']);
			$challenge->setChallengeCreator($result['challenge_creator']);
			$challenge->setCreateDate($result['challenge_create_date']);
			$challenge->setChallengeStatus($result['challenge_status']);
			
			$challenge->setShortTitle($result['challenge_short_title']);
			$challenge->setChallengeType($result['challenge_type']);
			$challenge->setAcceptDuration($result['challenge_accept_duration']);
			$challenge->setAcceptDurationType($result['challenge_accept_duration_type']);
			$challenge->setRewardDescription($result['challenge_reward_description']);
			$challenge->setRewardType($result['challenge_reward_surprise_flag']);
			$challenge->setResendOption($result['resend_option']);
			
			return $challenge;
		}
		
		//---------------------	Challenge Mangement End------------------------------//

		//---------------------	Challenge Status Mangement Start------------------------------//
		
		public function addChallengeStatus($challengeStatus){
			$values = array(
				'challenge_id'		=>	$challengeStatus->getChallengeId(),
				'player_id'		=>	$challengeStatus->getPlayerId(),
				'player_score'		=>	$challengeStatus->getPlayerScore(),
				'player_status'	=>	$challengeStatus->getPlayerStatus()
			);
			
			$this->_db->Insert('challenge_status', $values);

			return $this->_db->LastInsertID();
		}
		public function updateChallengeStatus($challengeStatus){
			$where = array('challenge_id' => $challengeStatus->getChallengeId(), 'player_id' => $challengeStatus->getPlayerId());
			$values = array(
				'challenge_id'		=>	$challengeStatus->getChallengeId(),
				'player_id'			=>	$challengeStatus->getPlayerId(),
				'player_score'		=>	$challengeStatus->getPlayerScore(),
				'player_status'		=>	$challengeStatus->getPlayerStatus(),
				'last_update_time'	=>	date('Y-m-d H:i:s')
			);

			$this->_db->Update('challenge_status', $values, $where);
		}
		public function deleteChallengeStatusByChallenge($challengeId){
			$where = array('challenge_id' => $challengeId);
			$this->_db->Delete('challenge_status', $where);
		}
		public function getChallengeStatusByPlayer($challengeId, $playerId){
			
			$where = array('challenge_id' => $challengeId, 'player_id' => $playerId);
			$this->_db->Select('challenge_status', $where);
			$result = $this->_db->ArrayResult();
	
			if(empty($result))
				return null;

			$challengeStatus = new ChallengeStatus();
			$challengeStatus->setChallengeStatusId($result['challenge_status_id']);
			$challengeStatus->setChallengeId($result['challenge_id']);
			$challengeStatus->setPlayerId($result['player_id']);
			$challengeStatus->setPlayerScore($result['player_score']);
			$challengeStatus->setPlayerStatus($result['player_status']);
			$challengeStatus->setUpdateTime($result['last_update_time']);

			return $challengeStatus;
		}
		public function getChallengeStatusById($challengeStatusId){
			
			$where = array('challenge_status_id' => $challengeStatusId);
			$this->_db->Select('challenge_status', $where);
			$result = $this->_db->ArrayResult();
	
			if(empty($result))
				return null;

			$challengeStatus = new ChallengeStatus();
			$challengeStatus->setChallengeStatusId($result['challenge_status_id']);
			$challengeStatus->setChallengeId($result['challenge_id']);
			$challengeStatus->setPlayerId($result['player_id']);
			$challengeStatus->setPlayerScore($result['player_score']);
			$challengeStatus->setPlayerStatus($result['player_status']);
			$challengeStatus->setUpdateTime($result['last_update_time']);
			
			return $challengeStatus;
		}
		public function getChallengeStatusByPlayerStatus($challenge_id){
			
			$where = array('player_status' => 0, 'challenge_id' => $challenge_id);
			$this->_db->Select('challenge_status', $where);
			
			$results = $this->_db->ArrayResults();
			if(empty($results))
				return null;
			foreach($results as $result){
				$challengeStatus = new ChallengeStatus();
				$challengeStatus->setChallengeStatusId($result['challenge_status_id']);
				$challengeStatus->setChallengeId($result['challenge_id']);
				$challengeStatus->setPlayerId($result['player_id']);
				$challengeStatus->setPlayerScore($result['player_score']);
				$challengeStatus->setPlayerStatus($result['player_status']);
				$challengeStatus->setUpdateTime($result['last_update_time']);
				
				$temp = $challengeStatus->toArray();
				//$temp['player_name'] = $this->getPlayerNameById($result['player_id']);

				$statusArr[] = $temp;
			}
			return $statusArr;
		}
		public function getChallengeStatusByChallengeId($challengeId){
			$statusArr = array();			
			$where = array('challenge_id' => $challengeId);
			$this->_db->Select('challenge_status', $where);
			$results = $this->_db->ArrayResults();
			if(empty($results))
				return null;
			foreach($results as $result){
				$challengeStatus = new ChallengeStatus();
				$challengeStatus->setChallengeStatusId($result['challenge_status_id']);
				$challengeStatus->setChallengeId($result['challenge_id']);
				$challengeStatus->setPlayerId($result['player_id']);
				$challengeStatus->setPlayerScore($result['player_score']);
				$challengeStatus->setPlayerStatus($result['player_status']);
				$challengeStatus->setUpdateTime($result['last_update_time']);
				
				$temp = $challengeStatus->toArray();
				//$temp['player_name'] = $this->getPlayerNameById($result['player_id']);

				$statusArr[] = $temp;
			}
			return $statusArr;
		}
		
		public function getChallengeStatusByStatus($challengeId, $status){
			$statusArr = array();			
			$where = array('challenge_id' => $challengeId, 'player_status' => $status);
			$this->_db->Select('challenge_status', $where);
			$results = $this->_db->ArrayResults();
			if(empty($results))
				return null;
			foreach($results as $result){
				$challengeStatus = new ChallengeStatus();
				$challengeStatus->setChallengeStatusId($result['challenge_status_id']);
				$challengeStatus->setChallengeId($result['challenge_id']);
				$challengeStatus->setPlayerId($result['player_id']);
				$challengeStatus->setPlayerScore($result['player_score']);
				$challengeStatus->setPlayerStatus($result['player_status']);
				$challengeStatus->setUpdateTime($result['last_update_time']);
				
				$temp = $challengeStatus->toArray();
				//$temp['player_name'] = $this->getPlayerNameById($result['player_id']);

				$statusArr[] = $temp;
			}
			return $statusArr;
		}
		//---------------------	Challenge Status Mangement End------------------------------//

		//---------------------	Quote Status Mangement Start------------------------------//
		
		public function addGroupQuote($quote){
			$values = array(
				'group_id'		=>	$quote->getGroupId(),
				'player_id'		=>	$quote->getPlayerId(),
				'group_quote'	=>	$quote->getGroupQuote()
			);
			$this->_db->Insert('group_quotes', $values);

			return $this->_db->LastInsertID();
		}
		public function updateGroupQuote($quote){
			$where = array('group_quote_id' => $quote->getQuoteId());
			$values = array(
				'group_id'		=>	$quote->getGroupId(),
				'player_id'		=>	$quote->getPlayerId(),
				'group_quote'	=>	$quote->getGroupQuote()
			);
			$this->_db->Update('group_quotes', $values, $where);
		}
		public function getGroupQuoteById($quoteId){
			
			$where = array('group_quote_id' => $quoteId);
			$this->_db->Select('group_quotes', $where);
			$result = $this->_db->ArrayResult();
	
			if(empty($result))
				return null;

			$quote = new Quote();
			$quote->setQuoteId($result['group_quote_id']);
			$quote->setGroupId($result['group_id']);
			$quote->setPlayerId($result['player_id']);
			$quote->setGroupQuote($result['group_quote']);

			return $quote;
		}
		public function getGroupQuoteByPlayerId($groupId, $playerId){
			
			$where = array('group_id' => $groupId, 'player_id' => $playerId);
			$this->_db->Select('group_quotes', $where);
			$result = $this->_db->ArrayResult();
	
			if(empty($result))
				return null;

			$quote = new Quote();
			$quote->setQuoteId($result['group_quote_id']);
			$quote->setGroupId($result['group_id']);
			$quote->setPlayerId($result['player_id']);
			$quote->setGroupQuote($result['group_quote']);

			return $quote;
		}
		
		public function getGroupQuotesByGroupId($groupId){
			$quoteArr = array();			
			$where = array('group_id' => $groupId);
			$this->_db->Select('group_quotes', $where);
			$results = $this->_db->ArrayResults();
	
			if(empty($results))
				return null;
			foreach($results as $result){
				$quote = new Quote();
				$quote->setQuoteId($result['group_quote_id']);
				$quote->setGroupId($result['group_id']);
				$quote->setPlayerId($result['player_id']);
				$quote->setGroupQuote($result['group_quote']);

				$quoteArr[] = $quote;
			}
			return $quoteArr;
		}
		public function getGroupQuoteByGroupId($groupId){
			$quoteArr = array();			
			$where = array('group_id' => $groupId);
			$this->_db->Select('group_quotes', $where);
			$result = $this->_db->ArrayResult();
	
			if(empty($result))
				return null;
			
				$quote = new Quote();
				$quote->setQuoteId($result['group_quote_id']);
				$quote->setGroupId($result['group_id']);
				$quote->setPlayerId($result['player_id']);
				$quote->setGroupQuote($result['group_quote']);

			return $quote;
		}
		//---------------------	Quote Status Mangement End------------------------------//

		//---------------------	Comment Status Mangement Start------------------------------//
		
		public function addGroupComment($comment){
			$values = array(
				'group_id'				=>	$comment->getGroupId(),
				'challenge_id'			=>	$comment->getChallengeId(),
				'player_id'				=>	$comment->getPlayerId(),
				'comment'				=>	$comment->getComment(),
				'type'					=>	$comment->getType(),
				'is_blocked'			=>	$comment->isBlocked(),
				'comment_create_date'	=>	$comment->getCreateDate()
			);
			$this->_db->Insert('group_comments', $values);

			return $this->_db->LastInsertID();
		}
		public function updateGroupComment($comment){
			$where = array('comment_id' => $comment->getCommentId());
			$values = array(
				'group_id'				=>	$comment->getGroupId(),
				'challenge_id'				=>	$comment->getChallengeId(),
				'player_id'				=>	$comment->getPlayerId(),
				'comment'				=>	$comment->getComment(),
				'type'					=>	$comment->getType(),
				'is_blocked'			=>	$comment->isBlocked(),
				'comment_create_date'	=>	$comment->getCreateDate()
			);
			$this->_db->Update('group_comments', $values, $where);
		}
		public function getGroupCommentById($commentId){
			
			$where = array('comment_id' => $commentId);
			$this->_db->Select('group_comments', $where);
			$result = $this->_db->ArrayResult();
	
			if(empty($result))
				return null;

			$comment = new Quote();
			$comment->setCommentId($result['comment_id']);
			$comment->setGroupId($result['group_id']);
			$comment->setChallengeId($result['challenge_id']);
			$comment->setPlayerId($result['player_id']);
			$comment->setComment($result['comment']);
			$comment->setType($result['type']);
			$comment->setBlocked($result['is_blocked']);
			$comment->setCreateDate($result['comment_create_date']);
			return $comment;
		}
		public function getGroupCommentsByGroupId($groupId, $pageIndex=0){
			
			$limit = $this->_PAGE_LIMIT * $pageIndex . ', ' . $this->_PAGE_LIMIT;
			$commentArr = array();			
			$where = array('group_id' => $groupId, 'is_blocked' => 0);
			$this->_db->Select('group_comments', $where, 'comment_create_date DESC', $limit);
			$results = $this->_db->ArrayResults();
	
			if(empty($results))
				return null;
			foreach($results as $result){
				
				if($result['challenge_id'] > 0)
					continue;
				$comment = new Comment();
				$comment->setCommentId($result['comment_id']);
				$comment->setGroupId($result['group_id']);
				$comment->setChallengeId($result['challenge_id']);
				$comment->setPlayerId($result['player_id']);
				$comment->setComment($result['comment']);
				$comment->setType($result['type']);
				$comment->setBlocked($result['is_blocked']);
				$comment->setCreateDate($result['comment_create_date']);

				$commentArr[] = $comment->toArray();
			}
			return $commentArr;
		}
		
		public function getChallengeCommentsByChallengeId($challengeId, $pageIndex=0){
			
			$limit = $this->_PAGE_LIMIT * $pageIndex . ', ' . $this->_PAGE_LIMIT;
			$commentArr = array();			
			$where = array('challenge_id' => $challengeId, 'is_blocked' => 0);
			$this->_db->Select('group_comments', $where, 'comment_create_date DESC', $limit);
			$results = $this->_db->ArrayResults();
	
			if(empty($results))
				return $commentArr;
			foreach($results as $result){
				$comment = new Comment();
				$comment->setCommentId($result['comment_id']);
				$comment->setGroupId($result['group_id']);
				$comment->setChallengeId($result['challenge_id']);
				$comment->setPlayerId($result['player_id']);
				$comment->setComment($result['comment']);
				$comment->setType($result['type']);
				$comment->setBlocked($result['is_blocked']);
				$comment->setCreateDate($result['comment_create_date']);

				$commentArr[] = $comment->toArray();
			}
			return $commentArr;
		}
			
		//---------------------	Comment Status Mangement End------------------------------//

		//---------------------	User Device Mangement Start------------------------------//
		public function isTokenExist($userId, $token){
			$tokenArr = array();			
			$where = array('user_id' => $userId, 'device_token' => $token);
			$this->_db->Select('user_device', $where);
			$result = $this->_db->ArrayResult();
	
			if(empty($result))
				return false;
			
			return true;
		}
		public function insertToken($userId, $token, $deviceType){
			$values = array(
				'user_id'		=>	$userId,
				'device_token'		=>	$token,
				'device_type'	=>	$deviceType
			);
			$this->_db->Insert('user_device', $values);

			return $this->_db->LastInsertID();
		}
		public function getTokenByUserId($userId){
			$tokenArr = array();			
			$where = array('user_id' => $userId);
			$this->_db->Select('user_device', $where);
			$results = $this->_db->ArrayResults();
	
			if(empty($results))
				return null;
			foreach($results as $result){
				$tokenArr[] = $result['device_token'];
			}
			return $tokenArr;
		}
		
		//---------------------	User Device Mangement End------------------------------//
		
		//---------------------	Report Mangement Start------------------------------//
		
		public function insertReport($userId, $groupId, $challengeId, $description, $screenshot){
			
			$values = array(
				'reporting_user'		=>	$userId,
				'group_id'		=>	$groupId,
				'challenge_id'	=>	$challengeId,
				'report_desc'	=>	$description,
				'screenshot'	=> 	$screenshot
			);
			$this->_db->Insert('reported', $values);

			return $this->_db->LastInsertID();
		}
		
		//---------------------	Report Mangement End------------------------------//
		
		//---------------------	Notification Mangement Start------------------------------//
		
		public function insertNotification($from, $to, $who, $do, $what, $where, $by, $groupId, $challengeId){
			
			$values = array(
				'from'		=>	$from,
				'to'		=>	$to,
				'who'		=>	$who,
				'do'		=>	$do,
				'what'		=>	$what,
				'where'		=>	$where,
				'by'		=>	$by,
				'send_time' => 	date('Y-m-d H:i:s'),
				'groupId'		=>	$groupId,
				'challengeId'		=>	$challengeId
			);
			$this->_db->Insert('notifications', $values);

			return $this->_db->LastInsertID();
		}
		public function getNotificationByReceiver($to, $limit=50){
			$limit = '0, ' . $limit;
			$notificationArr = array();			
			$where = array('to' => $to);
			
			$this->_db->Select('notifications', $where, 'send_time DESC', $limit);
			$results = $this->_db->ArrayResults();
	
			if(empty($results))
				return $notificationArr;
			foreach($results as $result){
				$notification = new Notification();
				$notification->setNotificationId($result['id']);
				$notification->setFrom($result['from']);
				$notification->setTo($result['to']);
				$notification->setWho($result['who']);
				$notification->setDo($result['do']);
				$notification->setWhat($result['what']);
				$notification->setWhere($result['where']);
				$notification->setBy($result['by']);
				$notification->setSendTime($result['send_time']);
				$notification->setGroupId($result['groupId']);
				$notification->setChallengeId($result['challengeId']);
				
				$notificationArr[] = $notification;
			}
			return $notificationArr;
		}
		
		public function getActiveChallengeByNotificationReceiver($to, $limit=50){
			
			$challengeArr = array();			
			
			$limit = '0, ' . $limit;
			$days_ago = date('Y-m-d H:i:s', strtotime("-1 week"));
			$where = "`to` = " . $to . " and `challengeId` > 0 and `send_time` > '" . $days_ago . "'";
			$this->_db->SelectCustom('notifications', $where, 'send_time DESC', $limit);
			$results = $this->_db->ArrayResults();
	
			if(empty($results))
				return $challengeArr;
			
			$challengeIds = array();
			foreach($results as $result){
				
				if(in_array($result['challengeId'], $challengeIds))
					continue;
				
				$challengeIds[] = $result['challengeId'];
				$challenge = $this->getChallengeById($result['challengeId']);
				if($challenge == null)
					continue;
				
				$temp = $challenge->toArray();
				$temp['groupId'] = $result['groupId'];
				$challengeArr[] = $temp;
			}
			return $challengeArr;
		}
		
		//---------------------	Notification Mangement End------------------------------//
		
		//---------------------	Special Challenge Players Mangement Start------------------------------//
		
		public function insertChallengePlayers($challengeId, $userId){
			
			$values = array(
				'challenge_id'		=>	$challengeId,
				'user_id'		=>	$userId
			);
			$this->_db->Insert('special_challenge_players', $values);

			return $this->_db->LastInsertID();
		}
		public function getChallengeIdByUserId($challengeId, $userId){
		
			$where = array(
				'challenge_id'		=>	$challengeId,
				'user_id'		=>	$userId
			);
			$this->_db->Select('special_challenge_players', $where);
			$result = $this->_db->ArrayResult();
			if(empty($result))
				return -1;
			
			return $result['id'];
		}
		
		//---------------------	Special Challenge Players Mangement End------------------------------//
		
		//---------------------	User Notification Settings Mangement Start------------------------------//
		
		public function getUserNotificationSetting($userId, $groupId, $challengeId){
		
			$where = array(
				'user_id'		=>	$userId,
				'group_id'		=>	$groupId,
				'challenge_id'		=>	$challengeId
			);
			
			$this->_db->Select('user_notification_settings', $where);
			$result = $this->_db->ArrayResult();

			if(empty($result))
				return -1;
			
			return $result['status'];
		}
		
		public function insertUserNotificationSetting($userId, $groupId, $challengeId, $status){
			
			$values = array(
				'user_id'		=>	$userId,
				'group_id'		=>	$groupId,
				'challenge_id'		=>	$challengeId,
				'status'		=>	$status
			);
			
			$this->_db->Insert('user_notification_settings', $values);

			return $this->_db->LastInsertID();
		}
		public function updateUserNotificationSetting($userId, $groupId, $challengeId, $status){
		
			if($challengeId == 0){
				$where = array(
					'user_id'		=>	$userId,
					'group_id'		=>	$groupId
				);
			}else{
				$where = array(
					'user_id'		=>	$userId,
					'group_id'		=>	$groupId,
					'challenge_id'		=>	$challengeId
				);
			}
			
			$values = array(
				'status'		=>	$status
			);
			$this->_db->Update('user_notification_settings', $values, $where);
		}
		
		//---------------------	User Notification Settings Mangement End------------------------------//
	}
?>