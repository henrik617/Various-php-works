<?php
	
	include_once 'includes/db_connect.php';
	include_once 'includes/functions.php';
	
	sec_session_start(); 

?>
<!DOCTYPE html>
<html>

<head>
	<meta charset='UTF-8'>
	
	<title>Admin Panel</title>
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link rel="stylesheet" href="css/style1.css">
	
	<script src="jquery-1.9.1.min.js"></script>
	<script>
		
		$(function () {
			$body = $("body");
			$("#sel_segment").change();
		});
		
		function sendNotification(){
			checkboxes = document.getElementsByName('chk_usergroup[]');
			var flag = false;
			for (var i=0; i<checkboxes.length; i++) {
				if(checkboxes[i].checked == true){
					flag = true;
					break;
				}
					
			}
			if(flag == false){
				$("#send_result").removeClass('success').addClass('error');
				$("#send_result").text('Please select the notification receivers!');
				$("#send_result").show();
			}else if($('#notification').val() == ''){
				$("#send_result").removeClass('success').addClass('error');
				$("#send_result").text('Please input notification text!');
				$("#send_result").show();
			}else{
	          	
				$("#send_result").text('');
			  	$body.addClass("loading");
		  
	          	$.ajax({
	            	type: 'post',
	            	url: 'includes/admin_service.php?action=sendNotification',
	            	data: $('#FSForm').serialize(),
	            	success: function (response) {
						//alert('Sent Push Notification to users');
						if(response == 1){
							$("#send_result").removeClass('error').addClass('success');
							$("#send_result").text('Sent push notifications successfully');
							$("#send_result").show();
						}else{
							$("#send_result").removeClass('success').addClass('error');
							$("#send_result").text('Please input notification text!');
							$("#send_result").show();
						}
						
	              		$body.removeClass("loading");
	            	}
	          	});
			}
		}
	  	function toggle(source) {
	  	  	checkboxes = document.getElementsByName('chk_usergroup[]');
	  	  	for(var i=0, n=checkboxes.length;i<n;i++) {
	  	    	checkboxes[i].checked = source.checked;
	  	  	}
	  	}
		function selectSegment(source) {
			
			checkboxes = document.getElementsByName('chk_usergroup[]');
	  	  	for(var i=0, n=checkboxes.length;i<n;i++) {
					checkboxes[i].checked = false;
	  	  	}
			
			id = source.value;
			if(id != 0){
				$body.addClass("loading");
	          	$.ajax({
	            	type: 'post',
	            	url: 'includes/admin_service.php?action=getSegmentUsers',
	            	data: {'id':id},
	            	success: function (response) {
						var users = jQuery.parseJSON(response);
					
				  	  	
				  	  	for(var i=0, n=checkboxes.length;i<n;i++) {
							if(users.indexOf(checkboxes[i].value) > -1)
								checkboxes[i].checked = true;
				  	  	}
					
	              		$body.removeClass("loading");
	            	}
	          	});
			}
          	
		}
	</script>
</head>

<body>

	<?php
	
		if(login_check($mysqli) == true) {
		        
			require_once('../System/ThGame.php');
			$server = new ThGame();
			$users = $server->admin_getAllUsers();
		
			$segments = $server->admin_getAllUserSegments();
	?>
	
	<div id="page-wrap">
		
	<div>
	<a href="includes/logout.php" class="logout">Log out</a>
	</div>

	<center><h1>Send Push Notification</h1></center>
	
	
	<form id="FSForm">
		
		<!--
		<input type="submit" name="All" value="All" class="submit_button" id="FSSegmentAll">
		<input type="submit" name="segment1" value="Segment1" class="submit_button" id="FSSegment1">
		<input type="submit" name="segment2" value="Segment2" class="submit_button" id="FSSegment2">
		-->
		<label for="notification" style="margin-top: 30px;">Notification Text: </label>
		<textarea rows="4" cols="100" id="notification" name="notification"></textarea>
		
		<div class="block-container">
			<div class="block1">
				<label for="sel_segment" style="float:left">Select Segment:</label> &nbsp;
				<select name="sel_segment" id="sel_segment" onChange="selectSegment(this);">
					<option value="0">None</option>
					
					<?php
						if(count($segments) > 0){
							foreach($segments as $segment){
								echo "<option value='" . $segment['id'] . "'>" . $segment['sname'] . "</option>";
							}
						}
						
					?>
				</select>
			</div>
			<div class="block2">
			
			
					<span id="send_result" ></span>
			
					<input type="button" name="submit" value="Send" class="submit_button" id="FSSend" style="float:right;" onClick="sendNotification();">
					<input type="button" name="segment" value="Segments" class="submit_button" id="FSSegment" onclick="window.location.href='segments.php'" style="float:right;">
					
			</div>
		
		
		</div>
		<table>
			<tr>
				<th><input type="checkbox" onClick="toggle(this);" /> Select All<br/> </th>
				<th>No.</th>
				<th>User Name</th>
				<th>Email</th>
				<th>User Type</th>
				<th>User Device</th>
			</tr>
			<?php
				$i = 0;
				foreach($users as $user){
					$i++;
			?>
				<tr>
					<td><input type="checkbox" name="chk_usergroup[]" value="<?php echo $user['userId']; ?>" />    &nbsp;</td>
					<td><?php echo $i . ''; ?></td>
					<td><?php echo $user['userName']; ?></td>
					<td><?php echo $user['socialEmail']; ?></td>
					<td><?php echo $user['userType']; ?></td>
					<td><?php echo $user['device']; ?></td>
				</tr>
			<?php
				}
			?>
		</table>
	</form>
	</div>
	
	<?php
		} else { 
		        echo 'You are not authorized to access this page, please login.';
		}
	?>
	
	<div class="modal"></div>
	
</body>
</html>