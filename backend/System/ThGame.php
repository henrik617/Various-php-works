<?php
	
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	
	require_once('DataStore.php');
	require_once('SMTPMail.php');
	
	class ThGame {

		protected $_store;
		protected $_passphrase;
		protected $_pemfile;
		protected $_report_email;
		protected $_appversion;
		protected $_showAgreement;
		public function __construct(){
			$this->_store = new DataStore();

			include('configuration.php');
			$this->_passphrase = $__PASSPHRASE;
			$this->_pemfile = $__PEMFILENAME;
			$this->_report_email = $__REPORT_EMAIL;
			$this->_appversion = $__APPVERSION;
			$this->_showAgreement = $__NewToU;
		}
		
		/* User functions */
		
		public function getAppVersion(){
			
			$result = json_encode(array('result'=>array('version'=>$this->_appversion, 'showAgreement'=>$this->_showAgreement)));
			return $result;
		}
		public function userLogin($userName, $socialId, $socialName, $email, $photoUrl, $deviceToken, $deviceType, $socialType){
			if($this->_store->isUserExist($socialId, $socialType)){
				$user = $this->_store->getUserBySocialId($socialId, $socialType);
				$user->setAccessToken($this->generateRandomString());
				$user->setLastSeenDate(date('Y-m-d H:i:s'));
				$this->_store->updateUser($user);
				
				$this->registerUserDevice($user->getUserId(), $deviceToken, $deviceType);
				return $user;
			}else{
				
				$user = new User();
				
				$user->setUserName($userName);
				
				$user->setSocialId($socialId);
				$user->setSocialEmail($email);
				$user->setSocialName($socialName);
				$user->setSocialType($socialType);
				
				$user->setPictureUrl($photoUrl);
				$user->setAccessToken($this->generateRandomString());
				$userId = $this->_store->addUser($user);
								
				if($userId > 0){
					$user->setUserId($userId);
					if($deviceToken && $deviceToken != '')
						$this->registerUserDevice($userId, $deviceToken, $deviceType);
					return $user;
				}
				return null;				
			}
		}
		
		function generateRandomString($length = 30) {
		    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		    $charactersLength = strlen($characters);
		    $randomString = '';
		    for ($i = 0; $i < $length; $i++) {
		        $randomString .= $characters[rand(0, $charactersLength - 1)];
		    }
		    return $randomString;
		}
		
		public function isTokenValid($access_token){
			return ($this->_store->isTokenValid($access_token));
		}
        public function updateProfileImage($userId, $profileUrl){
            $user = $this->getUserById($userId);
            $user->setPictureUrl($profileUrl);
			$user->setAccessToken($this->generateRandomString());
			if($this->_store->updateUser($user))
				return $user;
			return null;
        }
		
		public function updateUserPublicLeaderboardOption($userId, $option){
            $user = $this->getUserById($userId);
            $user->setPublicLeaderboard($option);
			$user->setAccessToken($this->generateRandomString());
			if($this->_store->updateUser($user))
				return $user;
			return null;            
		}
		
		public function updateUser($userId, $userName, $profileUrl, $publicLeaderboard=1){
            $user = $this->getUserById($userId);
			if($userName != "")
				$user->setUserName($userName);
			$user->setAccessToken($this->generateRandomString());
			
			if(!empty($profileUrl))
					$user->setPictureUrl($profileUrl);
			
			$user->setPublicLeaderboard($publicLeaderboard);
			
            $this->_store->updateUser($user);
            return $user;
		}
		public function getAllUsers(){
			return $this->_store->getAllUsers();
		}
		public function getUserById($userId){
			return $this->_store->getUserById($userId);
		}
		public function searchUsers($userId, $keyword){
			$results = $this->_store->searchUsersByName($keyword);
			
			$users = array();
			foreach($results as $user){
				$isFriend = $this->_store->isFriend($userId, $user['userId']);
				$user['isFriend'] = $isFriend;
				
				$users[] = $user;
			}
			
			return $users;
		}
		public function addUserFriend($userId, $friendId){
			$result = $this->_store->addFriend($userId, $friendId);
			
			if($result > 0)
				return "Success";
			else
				return "Fail";
		}
		
		public function updateLanguage($userId, $language){
			$user = $this->getUserById($userId);
			$user->setLanguage($language);
			
			return $this->_store->updateLanguage($user);
		}
		
		/* Admin functions */
		
		
		public function admin_getAllUsers(){
			$users = array();
			$results = $this->_store->getAllUsers();
			if(count($results) > 0){
				foreach($results as $user){
					$tokenArray = $this->_store->getTokenByUserId($user['userId']);
					if(count($tokenArray) > 0){
						$user['device'] = 'iOS';
					}else{
						$user['device'] = 'No Device';
					}
					
					$users[] = $user;
				}
			}
			
			return $users;
		}
		public function admin_sendNotification($userIds, $message){
			
			$temp = array();
			foreach($userIds as $userId){
				$user = $this->_store->getUserById($userId);
				$tokens = $this->_store->getTokenByUserId($userId);
				if(count($tokens) == 0)
					continue;
				
				foreach($tokens as $deviceToken){
					if($deviceToken && $deviceToken!=''){
						
						if(in_array($deviceToken, $temp)){
							
						}else{
							$this->insertNotifications(-1, $userId, 'Admin', 0, $message, '', '', $user->getLanguage(), '', '');
							$this->sendPushNotification($deviceToken, $message, 0, 0, $userId);
							
							$temp[] = $deviceToken;
						}
					}
				}
			}
		}
		
		public function admin_addUserSegment($sname, $userIds) {
			return $this->_store->addUserSegment($sname, $userIds);
		}
		public function admin_updateUserSegment($id, $sname, $userIds){
			return $this->_store->updateUserSegment($id, $sname, $userIds);
		}
		public function admin_deleteUserSegment($id){
			return $this->_store->deleteUserSegment($id);					
		}		
				
		public function admin_getSegmentUsers($id){
			return $this->_store->getSegmentUsers($id);
		}
		
		public function admin_getUserSegment($id){
			return $this->_store->getUserSegment($id);
		}
		
		public function admin_getAllUserSegments(){
			return $this->_store->getAllUserSegments();
		}
		
		/* Friend functions  */
		public function getUserFriends($userId){
			return $this->_store->getUserFriends($userId);
		}
		public function addFriend($friendUserId){
			
			return $this->_store->addFriend($userId, $friendUserId);
		}
		public function deleteFriend($friendId){
			return $this->_store->deleteFriend($friendId);
		}
		public function isFriend($user_id, $friend_id){
			return $this->_store->isFriend($user_id, $friend_id);
		}
		
		/* Group functions */
		public function getAllGroups(){
			return $this->_store->getAllGroups();
		}
		public function getPublicGroups($userId){
			return $this->getGroupsByUserId($userId, 1);
		}
		public function getPrivateGroups($userId){
			return $this->getGroupsByUserId($userId, 0);
		}
		
		public function getGroupsByUserId($userId, $groupType){
			$groups = $this->_store->getGroupsByUserId($userId, $groupType);
			
			$groupData = array();
			if(count($groups) > 0){
				foreach($groups as $group){
				
					
					$groupId = $group['groupId'];
					
					$groupPlayers = $this->getGroupPlayers($groupId);
					$groupChallenges = $this->getChallengesByGroup($userId, $groupId);
					$isFavoriteGroup = $this->_store->isFavoriteGroup($groupId, $userId);
					
					$groupNotificationSetting = $this->getUserNotificationSetting($userId, $groupId, 0);
					
					$group['notificationSetting'] = $groupNotificationSetting;
					$group['players'] = $groupPlayers;
					$group['challenges'] = $groupChallenges;
					$group['isFavorite'] = $isFavoriteGroup;
					$groupData[] = $group;
				}
			}
			return $groupData;
		}
		
		public function createGroup($title, $creatorId, $groupImage, $groupType){
			$group = new Group();
			$group->setGroupTitle($title);
			$group->setCreatorId($creatorId);
			$group->setGroupImage($groupImage);
			$group->setGroupType($groupType);
			
			$groupId = $this->_store->addGroup($group);
			$group->setGroupId($groupId);

			$this->addPlayer($groupId, $creatorId, '');
			
			//$this->_store->insertUserNotificationSetting($creatorId, $groupId, 0, true);
			
			return $group;
		}
		public function getGroup($groupId){
			$group = $this->_store->getGroupById($groupId);
			
			return $group;
		}
		public function editGroup($groupId, $groupTitle, $groupImage){
			$group = $this->_store->getGroupById($groupId);
			
			$group->setGroupTitle($groupTitle);
			if($groupImage != "")
				$group->setGroupImage($groupImage);
			$group->setLastUpdateDate(date('Y-m-d h:i:s'));

			$this->_store->updateGroup($group);
			
			$user = $this->_store->getUserById($group->getCreatorId());
			$deviceToken = $this->_store->getTokenByUserId($group->getCreatorId());
			
			$message = array('who'=>$user->getUserName(), 'do'=>1, 'what'=>$group->getGroupTitle(), 'where'=>'', 'by'=>'');
			
			$this->sendGroupNotification($groupId, $message, -1, $user->getUserId());

			return $group;
		}
		public function updateGroupStatus($groupId, $status){
			$group = $this->_store->getGroupById($groupId);
			
			$group->setGroupStatus($status);
			$group->setLastUpdateDate(date('Y-m-d h:i:s'));

			return $this->_store->updateGroup($group);
		}
		public function deleteGroup($groupId){
			
			$group = $this->_store->getGroupById($groupId);
			$user = $this->_store->getUserById($group->getCreatorId());
			$deviceToken = $this->_store->getTokenByUserId($group->getCreatorId());
			$message = $user->getUserName() . " has deleted group '". $group->getGroupTitle()."'.";
			//$this->sendGroupNotification($groupId, $message);
			
			$result = $this->_store->deleteGroup($groupId);
			return $result;
		}
		
		public function updateGroupFavorite($groupId, $userId, $isFavorite){
			
			
			if($isFavorite){
				$success = $this->_store->addFavoriteGroup($groupId, $userId);
			}else{
				$success = $this->_store->removeFavoriteGroup($groupId, $userId);
			}
			
			if($success > 0) 
				return json_encode(array('result'=>array('success'=>1)));
			else
				return json_encode(array('result'=>array('success'=>0)));
		}
		/* Challenge functions */

		/**
		**		Challenge Status 
				0:	playing(active)
				1:	finished(inactive)
				3:	timesup(inactive)
		*/
		public function getAllChallenges(){
			return $this->_store->getAllChallenges();
		}
		public function getChallengesByGroup($userId, $groupId){
			$result = array();
			$challenges = $this->_store->getChallengesByGroup($groupId);
			
			if(count($challenges) > 0){
				foreach($challenges as $challenge){
				
					$isAccept = 0;
					$notificationSetting = 1;
					if($challenge->getChallengeType() > 0){
						$isAccept = $this->isUserAccepteChallenge($challenge->getChallengeId(), $userId);
					}
					$notificationSetting = $this->getUserNotificationSetting($userId, $groupId, $challenge->getChallengeId());	
				
					$temp = $challenge->toArray();
					$temp['isAccept'] = $isAccept;
					$temp['notificationSetting'] = $notificationSetting;
					$result[] = $temp;
				}
			}
			
			return $result;
		}
		public function getActiveChallengeByGroup($groupId){
			return $this->_store->getActiveChallengeByGroup($groupId);
		}
		public function createChallenge($groupId, $shortTitle, $description, $duration, $durationType, $challengeImage, $creatorId, $challengeType, $acceptDuration, $acceptDurationType, $rewardDescription, $rewardType, $resendOption){
			$challenge = new Challenge();
			$challenge->setGroupId($groupId);
			$challenge->setChallengeDescription($description);
			$challenge->setChallengeDuration($duration);
			$challenge->setChallengeDurationType($durationType);
			$challenge->setChallengeImage($challengeImage);
			$challenge->setChallengeCreator($creatorId);
			
			$challenge->setShortTitle($shortTitle);
			$challenge->setChallengeType($challengeType);
			$challenge->setAcceptDuration($acceptDuration);
			$challenge->setAcceptDurationType($acceptDurationType);
			$challenge->setRewardDescription($rewardDescription);
			$challenge->setRewardType($rewardType);
			$challenge->setResendOption($resendOption);
			
			$challengeId = $this->_store->addChallenge($challenge);
			if($challengeId > 0){
				
				$players = $this->getGroupPlayers($groupId);
				foreach($players as $group_player){
					$this->setUserNotificationSetting($group_player['userId'], $group_player['groupId'], $challengeId, true);
				}
				
				$challenge->setChallengeId($challengeId);
				$player = $this->_store->getPlayerById($challenge->getChallengeCreator());
				$playerName = $this->_store->getPlayerNameById($creatorId);
				$groupTitle = $this->_store->getGroupById($challenge->getGroupId())->getGroupTitle();
				$message = array('who'=>$playerName, 'do'=>3, 'what'=>$challenge->getChallengeDescription(), 'where'=>$groupTitle, 'by'=>'');
				$this->sendGroupNotification($challenge->getGroupId(), $message, $challengeId, $player->getUserId());
				return $challenge;
			}else{
				return null;
			}
		}
		public function getChallenge($challengeId){
			$challenge = $this->_store->getChallengeById($challengeId);
			return $challenge;
		}									  
		public function editChallenge($challengeId, $description, $duration, $durationType, $challengeImage, $acceptDuration, $acceptDurationType, $resendOption){
			$challenge = $this->_store->getChallengeById($challengeId);
			if($challenge){
				$challenge->setChallengeDescription($description);
				$challenge->setChallengeDuration($duration);
				$challenge->setChallengeDurationType($durationType);
				$challenge->setChallengeImage($challengeImage);
				
				$challenge->setAcceptDuration($acceptDuration);
				$challenge->setAcceptDurationType($acceptDurationType);
				$challenge->setResendOption($resendOption);
				
				$this->_store->updateChallenge($challenge);
				
				$player = $this->_store->getPlayerById($challenge->getChallengeCreator());
				$playerName = $this->_store->getPlayerNameById($challenge->getChallengeCreator());
				$groupTitle = $this->_store->getGroupById($challenge->getGroupId())->getGroupTitle();
				$message = array('who'=>$playerName, 'do'=>4, 'what'=>$challenge->getChallengeDescription(), 'where'=>$groupTitle, 'by'=>'');
				$this->sendGroupNotification($challenge->getGroupId(), $message, $challengeId, $player->getUserId());
				return $challenge;
			}
			return false;
		}

		public function challengeTimeUp($challengeId){
			
			$status = 3;
			$challenge = $this->updateChallengeStatus($challengeId, $status);
			$groupTitle = $this->_store->getGroupById($challenge->getGroupId())->getGroupTitle();
			if($challenge != null){
				$message = array('who'=>$challenge->getChallengeDescription(), 'do'=>5, 'what'=>'', 'where'=>$groupTitle, 'by'=>'');
				$this->sendGroupNotification($challenge->getGroupId(), $message, $challengeId, -1);
			}
			
		}
		public function challengeFinished($challengeId){
			
			$status = 1;
			$challenge = $this->updateChallengeStatus($challengeId, $status);
			$groupTitle = $this->_store->getGroupById($challenge->getGroupId())->getGroupTitle();
			if($challenge != null){
				$message = array('who'=>$challenge->getChallengeDescription(), 'do'=>6, 'what'=>'', 'where'=>$groupTitle, 'by'=>'');
				$this->sendGroupNotification($challenge->getGroupId(), $message, $challengeId, -1);
			}
			
		}
		public function updateChallengeStatus($challengeId, $status){
			//0:playing, 1:timeup, 2:complte, 3:giveup
			$challenge = $this->_store->getChallengeById($challengeId);
			$challenge->setChallengeStatus($status);
			if($this->_store->updateChallenge($challenge)){
				if($status == 1){
					$playStatusArray = $this->_store->getChallengeStatusByPlayerStatus($challengeId);
					if(count($playStatusArray)>0){
						foreach($playStatusArray as $playerStatus){
							$this->updatePlayerStatus($playerStatus['challengeId'], $playerStatus['playerId'], 0, 3);
						}
					}
				}
				return $challenge;
			}
			return null;
		}

		public function deleteChallenge($challengeId){
			$challenge = $this->_store->getChallengeById($challengeId);
			if($challenge == null)
				return false;
			$playerName = $this->_store->getPlayerNameById($challenge->getChallengeCreator());
			$groupTitle = $this->_store->getGroupById($challenge->getGroupId())->getGroupTitle();
			$message = $playerName . " has deleted the challenge '". $challenge->getChallengeDescription()."' in '" . $groupTitle ."' Group.";
			//$this->sendGroupNotification($challenge->getGroupId(), $message, $challengeId);

			$result = $this->_store->deleteChallenge($challengeId);
			return $result;
		}

		/***	Special Challenge Functions		***/
		public function acceptChallenge($challengeId, $userId){
			$id = $this->_store->insertChallengePlayers($challengeId, $userId);
			
			$challenge = $this->_store->getChallengeById($challengeId);
			$player = $this->_store->getPlayerByGroupAndUserId($challenge->getGroupId(), $userId);
			
			
			$challengeStatus = $this->_store->getChallengeStatusByPlayer($challengeId, $player->getPlayerId());
			$challengeStatus->setPlayerScore(0);
			$challengeStatus->setPlayerStatus(0);
			
			$this->_store->updateChallengeStatus($challengeStatus);
			
			return json_encode(array('result'=>array('success'=>1)));
		}
		public function isUserAccepteChallenge($challengeId, $userId){
			$result = $this->_store->getChallengeIdByUserId($challengeId, $userId);
			if($result > 0)
				return 1;
			return 0;
		}
		
		/***	Player Functions	****/

		public function addPlayer($groupId, $userId, $invitor){
			
			$player = $this->_store->getPlayerByGroupAndUserId($groupId, $userId);
			if($player)
				$playerId = $player->getPlayerId();
			else{
				$player = new Player();
				$player->setGroupId($groupId);
				$player->setUserId($userId);

				$playerId = $this->_store->addPlayer($player);
			}
			
			$this->setUserNotificationSetting($userId, $groupId, 0, true);
			
			$groupChallenges = $this->_store->getChallengesByGroup($groupId);

			if($groupChallenges != null){
				foreach($groupChallenges as $challenge){
					if($challenge->getChallengeStatus() != 0)
						continue;
					$challengeStatus = new ChallengeStatus();
					$challengeStatus->setChallengeId($challenge->getChallengeId());
					$challengeStatus->setPlayerId($playerId);

					if($challenge->getChallengeType() == 0)
						$challengeStatus->setPlayerStatus(0);
					else
						$challengeStatus->setPlayerStatus(-1);
						
					$this->_store->addChallengeStatus($challengeStatus);
					
					$this->setUserNotificationSetting($userId, $groupId, $challenge->getChallengeId(), true);
				}
			}
			if($invitor != ''){
				$sender = $this->_store->getUserById($invitor);
				$user = $this->_store->getUserById($userId);
				$tokens = $this->_store->getTokenByUserId($userId);
				
				if(count($tokens) > 0){
					$group = $this->_store->getGroupById($groupId);
					$message = $this->insertNotifications($invitor, $userId, $user->getUserName(), 12, $group->getGroupTitle(), '', $sender->getUserName(), $user->getLanguage(), $groupId, '');
					foreach($tokens as $token){
						$this->sendPushNotification($token, $message, $groupId, 0, $user->getUserId());
					}
				}
				
				
			}
			
			return $playerId;
		}
		
		/**
			$do :
					1: update group
					2: delete group
					3: create new challenge
					4: update challenge
					5: challenge expire(time up)
					6: challenge finish
					7: delete challenge
					8: complete challenge
					9: giveup challenge
					10: comment on group
					11: invite to the group
					12: added to the group
			$language:
					0: English
					1: German
					2: Turkish
					
		**/
		
		public function generateMessage($who, $do, $what, $where, $by, $language){
			
			$message = '';
			
			if($do == 0){
				$message = "Notification From Admin: " . $what;
			}if($do == 1){
				if($language == 0){
					$message = $who . " has updated Group '" . $what . "'.";
				}else if($language == 1){
					$message = $who . " aktualisierte die Gruppe '" . $what . "'.";
				}else if($language == 2){
					$message = "Grup " . $what . " " . $who . " tarafından güncellendi.";
				}
			}else if($do == 2){
				if($language == 0){
					$message = $who . " has deleted Group '" . $what . "'.";
				}else if($language == 1){
					$message = $who . " löschte die Gruppe '" . $what . "'.";
				}else if($language == 2){
					$message = "Grup " . $what . " " . $who . "tarafından silindi.";
				}
			}else if($do == 3){
				if($language == 0){
					$message = $who . " has created a new Challenge '" . $what . "' in Group '" . $where . "'.";
				}else if($language == 1){
					$message = $who . " erstellte neue Aufgabe '" . $what . "' in Gruppe '" . $where . "'.";
				}else if($language == 2){
					$message = $who . " " . $where . " grubunda yeni bir görev oluşturdu: " . $what . ".";
				}
			}else if($do == 4){
				if($language == 0){
					$message = $who . " has updated the Challenge '" . $what . "' in Group '" . $where . "'.";
				}else if($language == 1){
					$message = $who . " aktualisierte die Aufgabe '" . $what . "' in Gruppe '" . $where . "'.";
				}else if($language == 2){
					$message = $who . " " . $where . " grubundaki " . $what ." görevini güncelledi.";
				}
			}else if($do == 5){
				if($language == 0){
					$message = "Time's up!. The Challenge '" . $who . "' in Group '" . $where . "' has expired.";
				}else if($language == 1){
					$message = "die Aufgabe!. die Aufgabe '" . $who . "' in Gruppe '" . $where . "' ist Fertiggestellt.";
				}else if($language == 2){
					$message = $where . " grubundaki " . $who . " görevinin süresi doldu.";
				}
			}else if($do == 6){
				if($language == 0){
					$message = "The Challenge '" . $who . "' in Group '" . $where . "' has finished.";
				}else if($language == 1){
					$message = "die Aufgabe '" . $who . "' in Gruppe '" . $where . "' ist Abgeschlossen.";
				}else if($language == 2){
					$message = $where . " grubundaki " . $who . " görevi tamamlandı.";
				}
			}else if($do == 7){
				if($language == 0){
					$message = $who . " has deleted the Challenge '" . $what . "' in Group '" . $where . "'";
				}else if($language == 1){
					$message = $who . "  löschte die Aufgabe '" . $what . "' in Gruppe '" . $where . "'";
				}else if($language == 2){
					$message = $who . " " . $where . " grubundaki $what  görevini sildi.";
				}
			}else if($do == 8){
				if($language == 0){
					$message = $who . " has completed the Challenge '" . $what . "' in Group '" . $where . "'";
				}else if($language == 1){
					$message = $who . " vollendete die Aufgabe '" . $what . "' in Gruppe '" . $where . "'";
				}else if($language == 2){
					$message = $who . " " . $where . " grubundaki " . $what . " görevini tamamladı.";
				}
			}else if($do == 9){
				if($language == 0){
					$message = $who . " has given up the Challenge '" . $what . "' in Group '" . $where . "'";
				}else if($language == 1){
					$message = $who . " gab die Aufgabe '" . $what . "' in Gruppe '" . $where . "'";
				}else if($language == 2){
					$message = $who . " " . $where . " grubundaki " . $what . " görevini tamamlayamadı. Pes etti.";
				}
			}else if($do == 10){
				if($language == 0){
					$message = $who . " has commented on Group '" . $where . "'";
				}else if($language == 1){
					$message = $who . " kommentierte on Gruppe '" . $where . "'";
				}else if($language == 2){
					$message = $who . " " . $where . " grubunda yorum yaptı.";
				}
			}else if($do == 11){
				if($language == 0){
					$message = $who . " has been invited to Group '" . $what . "'";
				}else if($language == 1){
					$message = $who . " wurde zur Gruppe '" . $what . "' eingeladen";
				}else if($language == 2){
					$message = $who . " " . $what . " grubuna davet edildi.";
				}
			}else if($do == 12){
				if($language == 0){
					$message = $who . " has been added to Group '" . $what . "' by " . $by;
				}else if($language == 1){
					$message = $who . " wurde zur Gruppe '" . $what . "' von by " . $by . " hinzugefügt.";
				}else if($language == 2){
					$message = $who . " " . $by . " tarafından " . $what . " grubuna davet edildi.";
				}
			}
			
			return $message;
		}
		
		public function removePlayer($playerId){
			return $this->_store->deletePlayer($playerId);
		}
		public function getPlayerId($groupId, $userId){
			$player = $this->_store->getPlayerByGroupAndUserId($groupId, $userId);
			if($player)
				return $player->getPlayerId();
			return -1;
		}
		public function inviteFriend($groupId, $socialId){
			$user = $this->_store->getUserBySocialId($socialId);
			$result = $this->addPlayer($groupId, $user->getUserId());
			
			$group = $this->_store->getGroupById($groupId);
			
			$user = $this->_store->getUserById($group->getCreatorId());
			$deviceToken = $this->_store->getTokenByUserId($group->getCreatorId());
			$message = $user->getUserName() . " has invited you to the group '". $group->getGroupTitle()."'.";
			//$this->sendPushNotification($deviceToken, $message, $groupdId, 0, $user->getUserId());
			
			return $result;
		}
		public function getGroupPlayers($groupId){
			return $this->_store->getPlayersByGroup($groupId);
		}
		public function updatePlayer($player){
			return $this->_store->updatePlayer($player);
		}
		public function getTopPlayers($groupId){
			return $this->_store->getTopPlayersByGroup($groupId);
		}

		/***	Challenge Status Functions	***/
		/**
		**		Player Status
				0:	progress
				1:	complete
				2:	giveup
				3:	timesup
		*/
		public function getChallengeStatusByChallenge($challengeId){
			return $this->_store->getChallengeStatusByChallengeId($challengeId);
		}


		public function updatePlayerStatus($challengeId, $playerId, $playerScore, $playerStatus){

			$challengeStatus = $this->_store->getChallengeStatusByPlayer($challengeId, $playerId);
			$challengeStatus->setPlayerScore($playerScore);
			$challengeStatus->setPlayerStatus($playerStatus);
			
			$this->_store->updateChallengeStatus($challengeStatus);

			$player = $this->_store->getPlayerById($playerId);
			$player->setGroupScore($player->getGroupScore()+$playerScore);
			if($playerStatus == 1)
				$player->setChallengeCompleted($player->getChallengeCompleted()+1);
			if($playerStatus == 2)
				$player->setChallengeGiveup($player->getChallengeGiveup()+1);
			$player->setChallengePlayed($player->getChallengePlayed()+1);
			$this->updatePlayer($player);
			return $challengeStatus;
		}

		public function completeChallenge($challengeId, $playerId, $comment){
			$playerStatus = 1;
			$playerScore = 0;
			
			$player = $this->_store->getPlayerById($playerId);
			$challengeStatus = $this->_store->getChallengeStatusByPlayer($challengeId, $playerId);
			
			if($challengeStatus->getPlayerStatus() == 0){
				$playerScore = $this->calculatePlayerScore($challengeId, $playerId);
				
				$challengeStatus = new ChallengeStatus();
				$challengeStatus->setChallengeId($challengeId);
				$challengeStatus->setPlayerId($playerId);
				$challengeStatus->setPlayerScore($playerScore);
				$challengeStatus->setPlayerStatus($playerStatus);
				
				$player->setGroupScore($player->getGroupScore()+$playerScore);
				$player->setChallengePlayed($player->getChallengePlayed()+1);
				$player->setChallengeCompleted($player->getChallengeCompleted()+1);
				
			}else if($challengeStatus->getPlayerStatus() == 2){
				$playerScore = $this->calculatePlayerScore($challengeId, $playerId);
				
				$challengeStatus->setPlayerStatus($playerStatus);
				$challengeStatus->setPlayerScore($playerScore);
				
				$player->setGroupScore($player->getGroupScore()+$playerScore);
				$player->setChallengeGiveup($player->getChallengeGiveup()-1);
				$player->setChallengeCompleted($player->getChallengeCompleted()+1);	
				
			}
			
			$this->_store->updateChallengeStatus($challengeStatus);
			$this->updatePlayer($player);
			
			/*
			if($this->_store->getChallengeStatusByStatus($challengeId, 0) == null){
				$this->challengeFinished($challengeId);
			}
			*/
			
			$challenge = $this->_store->getChallengeById($challengeId);
			$group = $this->_store->getGroupById($challenge->getGroupId());
			
			// Add Group Comment
			$comment = "I completed the challenge '" . $challenge->getChallengeDescription() . "'";
			
			if($group->getGroupType() == 0)
				$this->addGroupComment($challenge->getGroupId(), $playerId, $comment,0);
			else
				$this->addChallengeComment($challenge->getGroupId(), $challengeId, $playerId, $comment,0);
			
			// Push Notification
			$playerName = $this->_store->getPlayerNameById($playerId);
			$groupTitle = $group->getGroupTitle();
			//$message = $playerName . " has completed the challenge '". $challenge->getChallengeDescription()."' 'in " . $groupTitle."' Group.";
			$message = array('who'=>$playerName, 'do'=>8, 'what'=>$challenge->getChallengeDescription(), 'where'=>$groupTitle, 'by'=>'');
			$this->sendGroupNotification($challenge->getGroupId(), $message, $challengeId, $player->getUserId());
			
			return $challengeStatus;
		}
		public function giveupChallenge($challengeId, $playerId, $comment){
			$playerScore = 0;
			$playerStatus = 2;

			$player = $this->_store->getPlayerById($playerId);
			$challengeStatus = $this->_store->getChallengeStatusByPlayer($challengeId, $playerId);
			
			if($challengeStatus->getPlayerStatus() == 0){
				
				$challengeStatus = new ChallengeStatus();
				$challengeStatus->setChallengeId($challengeId);
				$challengeStatus->setPlayerId($playerId);
				$challengeStatus->setPlayerScore($playerScore);
				$challengeStatus->setPlayerStatus($playerStatus);
				
				$player->setGroupScore($player->getGroupScore()+$playerScore);
				$player->setChallengePlayed($player->getChallengePlayed()+1);
				$player->setChallengeGiveup($player->getChallengeCompleted()+1);
				
			}else if($challengeStatus->getPlayerStatus() == 1){
				$prevScore = $challengeStatus->getPlayerScore();
				
				$challengeStatus->setPlayerStatus($playerStatus);
				$challengeStatus->setPlayerScore($playerScore);
				
				$player->setGroupScore($player->getGroupScore() - $prevScore);
				$player->setChallengeGiveup($player->getChallengeGiveup()+1);
				$player->setChallengeCompleted($player->getChallengeCompleted()-1);	
				
			}
			
			$this->_store->updateChallengeStatus($challengeStatus);
			$this->updatePlayer($player);
			
			/*
			if($this->_store->getChallengeStatusByStatus($challengeId, 0) == null){
				$this->challengeFinished($challengeId);
			}
			*/
			
			$challenge = $this->_store->getChallengeById($challengeId);
			
			$comment = "I give up the challenge '" . $challenge->getChallengeDescription() . "'";
			$this->addGroupComment($challenge->getGroupId(), $playerId, $comment,0);
				
			$playerName = $this->_store->getPlayerNameById($playerId);
			$groupTitle = $this->_store->getGroupById($challenge->getGroupId())->getGroupTitle();
			//$message = $playerName . " has given up the challenge '". $challenge->getChallengeDescription()."' in '" . $groupTitle."' Group.";
			$message = array('who'=>$playerName, 'do'=>9, 'what'=>$challenge->getChallengeDescription(), 'where'=>$groupTitle, 'by'=>'');
			$this->sendGroupNotification($challenge->getGroupId(), $message, $challengeId, $player->getUserId());
			
			return $challengeStatus;
		}
		
		public function sendReport($userId, $groupId, $challengeId, $description, $screenshot){
			
			$reportId = $this->_store->insertReport($userId, $groupId, $challengeId, $description, $screenshot);
			
			if($reportId > 0){
				
				$user = $this->_store->getUserById($userId);
				$mailContest = $user->getUserName() . ' Reported offensive content <br>';
				$mailContest .= 'Report Id:' . $reportId . '<br>';
				$mailContest .= 'User Id:' . $userId . '<br>';
				$mailContest .= 'Group Id:' . $groupId . '<br>';
				$mailContest .= 'ChallengeId Id:' . $challengeId . '<br>';
				$mailContest .= 'Description:' . $description . '<br>';
				
				$attach = dirname(__FILE__) . '/../images/report/' . $screenshot;
				$this->sendEmail($this->_report_email, $this->_report_email, 'Offensive Content Report', $mailContest, $attach);
				
				return json_encode(array('result'=>array('success'=>1, 'report_id'=>$reportId)));
			}else{
				return json_encode(array('result'=>array('success'=>0, 'report_id'=>0)));
			}
		}
		public function calculatePlayerScore($challengeId, $playerId){
			/**
			**	o	(M/C)*k
				o	M is a constant value. (e.g: 100)
				o	C: number of the players who completed the challenge
				o	k is factor which can be 1 or 2.
						2 for the first person who completed
						1 for all others
			*/
			/*
			$M = 100;
			$k = 1;
			$c = 0;
			$statusArr = $this->_store->getChallengeStatusByStatus($challengeId, 1);
			if($statusArr == null)
				$k = 2;
			else
				$c = count($statusArr);
			$c++;
			$score = round(((float)$M/(float)$c)*$k);

			return $score;
			*/
			
			$N = 200;
			$M = 100;
			
			$D = 10;
			
			$challenge = $this->_store->getChallengeById($challengeId);
			$group = $this->_store->getGroupById($challenge->getGroupId());
			
			if($group->getGroupType() == 0)
				$D = 10;
			else
				$D = 1;
			
			$statusArr = $this->_store->getChallengeStatusByStatus($challengeId, 1);
			if($statusArr == null){
				return $N;
			}
			
			$score = $N - $D * count($statusArr);
			return ($score > 0) ? $score : 0;
			
		}
		/***	Group Comment Functions		***/
		public function getGroupComments($groupId, $pageIndex){
			return $this->_store->getGroupCommentsByGroupId($groupId, $pageIndex);
		}
		public function getChallengeComments($challengeId, $pageIndex){
			return $this->_store->getChallengeCommentsByChallengeId($challengeId, $pageIndex);
		}
		public function addGroupComment($groupId, $playerId, $groupComment, $type){
			$player = $this->_store->getPlayerById($playerId);
			
			$comment = new Comment();
			$comment->setGroupId($groupId);
			$comment->setPlayerId($playerId);
			$comment->setComment($groupComment);
			$comment->setType($type);
			$commentId = $this->_store->addGroupComment($comment);
			
			
			$playerName = $this->_store->getPlayerNameById($playerId);
			$groupTitle = $this->_store->getGroupById($groupId)->getGroupTitle();
			$message = array('who'=>$playerName, 'do'=>10, 'what'=>'', 'where'=>$groupTitle, 'by'=>'');
			
			$this->sendGroupNotification($groupId, $message, -1, $player->getUserId());
			
			return $commentId;
		}
		public function addChallengeComment($groupId, $challengeId, $playerId, $groupComment, $type){
			$player = $this->_store->getPlayerById($playerId);
			
			$comment = new Comment();
			$comment->setGroupId($groupId);
			$comment->setChallengeId($challengeId);
			$comment->setPlayerId($playerId);
			$comment->setComment($groupComment);
			$comment->setType($type);
			$commentId = $this->_store->addGroupComment($comment);
			
			
			$playerName = $this->_store->getPlayerNameById($playerId);
			$groupTitle = $this->_store->getGroupById($groupId)->getGroupTitle();
			$message = array('who'=>$playerName, 'do'=>10, 'what'=>'', 'where'=>$groupTitle, 'by'=>'');
			
			
			$this->sendGroupNotification($groupId, $message, $challengeId, $player->getUserId());
			
			return $commentId;
		}
		
		/***	Group Quote Functions		***/
		function updateGroupQuote($groupId, $playerId, $groupQuote){
			
			$quote = $this->_store->getGroupQuoteByGroupId($groupId);
			if(!$quote){
				$quote = new Quote();
				$quote->setGroupId($groupId);
				$quote->setPlayerId($playerId);
				$quote->setGroupQuote($groupQuote);
			
				$quoteId = $this->_store->addGroupQuote($quote);
				$quote->setQuoteId($quoteId);
			}else{
				$quote->setGroupQuote($groupQuote);
				$this->_store->updateGroupQuote($quote);
			}
			return $quote;
		}
		function getGroupQuotes($groupId){
			$groupQuotes = array();
	        $topPlayers = $this->getTopPlayers($groupId);
			if(count($topPlayers)>0){
				foreach($topPlayers as $player){
					$groupQuote = array();
					$groupQuote['groupId'] = $groupId;
					$groupQuote['playerId'] = $player['playerId'];
					$quote = $this->_store->getGroupQuoteByPlayerId($groupId, $player['playerId']);
					if($quote)
						$groupQuote['quote'] = $quote->getGroupQuote();
					
					$groupQuotes[] = $groupQuote;
				}
			}
			return $groupQuotes;
		}
		function getGroupQuote($groupId){
			return $this->_store->getGroupQuoteByGroupId($groupId);
		}
		
		/***	User Device Functions	***/
		public function registerUserDevice($userId, $token, $deviceType){
			if($token != ''){
				if(!$this->_store->isTokenExist($userId, $token))
					$this->_store->insertToken($userId, $token, $deviceType);
			}
		}

		public function getNotifications($receiverId, $limit=50){
			$notifications = $this->_store->getNotificationByReceiver($receiverId, $limit);
			
			$user = $this->_store->getUserById($receiverId);
			$data = array();
			foreach($notifications as $notification){
				
				$message = $this->generateMessage($notification->getWho(), $notification->getDo(), $notification->getWhat(), $notification->getWhere(), $notification->getBy(), $user->getLanguage());
				$array = array('message'=>$message, 'send_time'=>$notification->getSendTime(), 'groupId'=>$notification->getGroupId(), 'challengeId'=>$notification->getChallengeId());
				$data[] = $array;
			}
			return json_encode(array('result'=>array('notifications'=>$data)));
		}
		
		public function insertNotifications($from, $to, $who, $do, $what, $where, $by, $language, $groupId='', $challengeId=''){
			$this->_store->insertNotification($from, $to, $who, $do, $what, $where, $by, $groupId, $challengeId);
			
			return $this->generateMessage($who, $do, $what, $where, $by, $language);
		}
		
		/***	User Notification Settings Functions	***/
		public function setUserNotificationSetting($userId, $groupId, $challengeId, $status){
			
			if($this->_store->getUserNotificationSetting($userId, $groupId, $challengeId) == -1){
				$this->_store->insertUserNotificationSetting($userId, $groupId, $challengeId, $status);
			}else{
				$this->_store->updateUserNotificationSetting($userId, $groupId, $challengeId, $status);
			}
			
			return json_encode(array('result'=>array('success'=>1)));
		}
		public function getUserNotificationSetting($userId, $groupId, $challengeId){
			return $this->_store->getUserNotificationSetting($userId, $groupId, $challengeId);			
		}
		
		/***	Score detail Functions 	*****/
		
		public function getPlayerScoreDetail($groupId, $playerId){
			$groupChallenges = $challenges = $this->_store->getChallengesByGroup($groupId);
			
			$scoreDetail = array();
			
			if(count($groupChallenges) > 0){
				foreach($groupChallenges as $challenge){
					$challengeStatus = $this->_store->getChallengeStatusByPlayer($challenge->getChallengeId(), $playerId);
					
					if($challengeStatus != null)
						$temp = $challengeStatus->toArray();
						$temp['challengeDescription'] = $challenge->getChallengeDescription();
						$scoreDetail[] = $temp;
				}
			}
						
			return json_encode(array('result'=>array('success'=>1, 'score'=>$scoreDetail)));
		}
		
		/***	Score detail Functions For Each Users 	*****/
		
		private function getUserGroups($userId){
			$publicGroups = $this->getGroupsByUserId($userId, 1);
			$privateGroups = $this->getGroupsByUserId($userId, 0);
			
			$userGroups = array();
			
			foreach($publicGroups as $group){
				if($group["joined"] == 0)
					continue;
				$userGroups[] = $group;
			}
			
			foreach($privateGroups as $group){
				$userGroups[] = $group;
			}
			
			return $userGroups;
		}
		public function getUserScoreDetail($userId){
			
			$userGroups = $this->getUserGroups($userId);
			
			$scoreDetail = array();
			foreach($userGroups as $group){
				
				$player = null;
				
				$groupPlayers = $group['players'];
				foreach($groupPlayers as $groupPlayer){
					if($groupPlayer['userId'] == $userId){
						$player = $groupPlayer;
						break;
					}
				}
				
				if($player != null){
					$groupChallenges = $group["challenges"];
			
					if(count($groupChallenges) > 0){
						foreach($groupChallenges as $challenge){
							$challengeStatus = $this->_store->getChallengeStatusByPlayer($challenge['challengeId'], $player['playerId']);
					
							if($challengeStatus != null)
								$temp = $challengeStatus->toArray();
								$temp['challengeDescription'] = $challenge['challengeDescription'];
								$temp['groupTitle'] = $group['groupTitle'];
								$scoreDetail[] = $temp;
						}
					}
				}
				
			}			
						
			return json_encode(array('result'=>array('success'=>1, 'score'=>$scoreDetail)));
		}
		
		public function getUserPublicScoreDetail($userId){
			
			$publicGroups = $this->getGroupsByUserId($userId, 1);
			$userGroups = array();
			
			foreach($publicGroups as $group){
				if($group["joined"] == 0)
					continue;
				$userGroups[] = $group;
			}
			
			$scoreDetail = array();
			foreach($userGroups as $group){
				
				$player = null;
				
				$groupPlayers = $group['players'];
				foreach($groupPlayers as $groupPlayer){
					if($groupPlayer['userId'] == $userId){
						$player = $groupPlayer;
						break;
					}
				}
				
				if($player != null){
					$groupChallenges = $group["challenges"];
			
					if(count($groupChallenges) > 0){
						foreach($groupChallenges as $challenge){
							
							$challengeStatus = $this->_store->getChallengeStatusByPlayer($challenge['challengeId'], $player['playerId']);

							if($challengeStatus == null)
								continue;
								
							$temp = $challengeStatus->toArray();
							$temp['challengeDescription'] = $challenge['challengeDescription'];
							$temp['groupTitle'] = $group['groupTitle'];
							$scoreDetail[] = $temp;
						}
					}
				}
				
			}			
						
			return json_encode(array('result'=>array('success'=>1, 'score'=>$scoreDetail)));
		}
		
		public function getFriendScores($userId){
			
			$friendScores = array();
			
			$friends = $this->getUserFriends($userId);
			foreach($friends as $friend){
				$friendUserId = $friend['friendInfo']['userId'];
				$userGroups = $this->getUserGroups($friendUserId);
				
				$totalScore = 0;
				$completed = 0;
				foreach($userGroups as $group){
					
					$player = null;
					
					$groupPlayers = $group['players'];
					foreach($groupPlayers as $groupPlayer){
						if($groupPlayer['userId'] == $friendUserId){
							$player = $groupPlayer;
							break;
						}
					}
					
					if($player != null){
						$totalScore += $player['groupScore'];
						$completed += $player['challengeCompleted'];
					}
				}
				
				if($totalScore == 0)
					continue;
				
				$friend['totalScore'] = $totalScore;
				$friend['challengeCompleted'] = $completed;
				
				$friendScores[] = $friend;
			}
			
			return json_encode(array('result'=>array('success'=>1, 'friendScores'=>$friendScores)));
		}
		
		public function getPublicGroupScores(){
			
			$publicGroupScores = array();
			
			$publicGroups = $this->getGroupsByUserId('-1', 1);
			
			$temp = array();
			foreach($publicGroups as $group){
				
				$groupPlayers = $group['players'];
				
				foreach($groupPlayers as $player){
					
					$key = 'user' . $player['userId'];
					
					$user = $this->getUserById($player['userId']);
					if($user->getPublicLeaderboard() == 0)
						continue;
					if (array_key_exists($key, $temp)) {	
						$arrayData = $temp[$key];
						$arrayData['publicScore'] += $player['groupScore'];
						$arrayData['challengeCompleted'] += $player['challengeCompleted'];
						
						$temp[$key] = $arrayData;
					}else{
						$arrayData['userId'] = $player['userId'];
						$arrayData['playerName'] = $player['playerName'];
						$arrayData['photoUrl'] = $player['photoUrl'];
						$arrayData['publicScore'] = $player['groupScore'];
						$arrayData['challengeCompleted'] = $player['challengeCompleted'];
						
						$temp[$key] = $arrayData;
					}
				}
			}
			
			foreach($temp as $score){
				$publicGroupScores[] = $score;
			}
			
			return json_encode(array('result'=>array('success'=>1, 'publicGroupScores'=>$publicGroupScores)));
		}
		
		public function getActiveChallenges($userId){
			
			$result = array();
			
			$challenges = $this->_store->getActiveChallengeByNotificationReceiver($userId);
			
			if(count($challenges) > 0){
				foreach($challenges as $challenge){
					$isAccept = 0;
					$notificationSetting = 1;
					$isAccept = $this->isUserAccepteChallenge($challenge['challengeId'], $userId);
					$notificationSetting = $this->getUserNotificationSetting($userId, $challenge['groupId'], $challenge['challengeId']);	
		
					$temp = $challenge;
					$temp['isAccept'] = $isAccept;
					$temp['notificationSetting'] = $notificationSetting;
					$result[] = $temp;
				}
			}
			
			
			return json_encode(array('result'=>array('challenges'=>$result)));
		}
		/***	Push Notification	***/
		public function sendGroupNotification($groupId, $message, $challengeId, $senderId){
			$groupPlayers = $this->getGroupPlayers($groupId);
			
			foreach($groupPlayers as $player){
				if($player['userId'] == $senderId)
					continue;
				
				
				if($challengeId > 0)
					$groupNotificationSetting = $this->getUserNotificationSetting($player['userId'], $groupId, $challengeId);
				else
					$groupNotificationSetting = $this->getUserNotificationSetting($player['userId'], $groupId, 0);
				
				
				if($groupNotificationSetting != 1)
					continue;
				
				if($challengeId > 0){
					$challengeNotificationSetting = $this->getUserNotificationSetting($player['userId'], $groupId, $challengeId);
					
					if($challengeNotificationSetting != 1)
						continue;
				}
				
				
				$user = $this->_store->getUserById($player['userId']);
				$msg = $this->insertNotifications($senderId, $player['userId'], $message['who'], $message['do'], $message['what'], $message['where'], $message['by'], $user->getLanguage(), $groupId, $challengeId);
				
				$tokens = $this->_store->getTokenByUserId($player['userId']);
				if(count($tokens) == 0)
					continue;
				foreach($tokens as $deviceToken){
					if($deviceToken && $deviceToken!=''){
						$this->sendPushNotification($deviceToken, $msg, $groupId, $challengeId, $player['userId']);
					}
				}
			}
		}

		public function sendPushNotification($deviceToken, $message, $groupId, $challengeId, $userId){
			//$deviceToken = '6b862241a523f43e163aa761056536ddb117c5fe8ad309d652f966fa37fe9089';

			// Put your private key's passphrase here:
			//$passphrase = 'censorpro';

			// Put your alert message here:
			//$message = 'My first push notification!';

			////////////////////////////////////////////////////////////////////////////////
			if(!ctype_xdigit($deviceToken)){
				return;
			}				
			
			$ctx = stream_context_create();
			stream_context_set_option($ctx, 'ssl', 'local_cert', dirname(__FILE__) . '/' . $this->_pemfile);
			stream_context_set_option($ctx, 'ssl', 'passphrase', $this->_passphrase);

			// Open a connection to the APNS server
			$fp = stream_socket_client(
				'ssl://gateway.push.apple.com:2195', $err,
				$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

			if (!$fp)
				exit("Failed to connect: $err $errstr" . PHP_EOL);

			//echo 'Connected to APNS' . PHP_EOL;

			// Create the payload body
			$body['aps'] = array(
				'alert' => array(
									'body' 			=> rawurldecode($message),
									'groupId' 		=> $groupId,
									'challengeId' 	=> $challengeId,
									'userId'		=> $userId
								),
				'sound' => 'default',
				'badge'	=> 1
				);

			// Encode the payload as JSON
			$payload = json_encode($body);
			// Build the binary notification
			$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

			// Send it to the server
			$result = fwrite($fp, $msg, strlen($msg));
			
			/*
			if (!$result)
				echo 'Message not delivered' . PHP_EOL;
			else
				echo 'Message successfully delivered' . PHP_EOL;
			*/
			
			// Close the connection to the server
			fclose($fp);
		}
		public function sendEmail($from, $to, $subject, $body, $attach=''){
			SMTPMail::sendMail($from, $to, $subject, $body, $attach, '','' , true);

		}
	}
	
?>