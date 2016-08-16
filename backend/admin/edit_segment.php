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
	        $('form').on('submit', function (e) {
				
				e.preventDefault();
				
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
					$("#send_result").text('Please select the users!');
					$("#send_result").show();
				}else if($('#segmentName').val() == ''){
					$("#send_result").removeClass('success').addClass('error');
					$("#send_result").text('Please input segment name!');
					$("#send_result").show();
				}else{
		          	
					$("#send_result").text('');
				  	$body.addClass("loading");
			  
		          	$.ajax({
		            	type: 'post',
		            	url: 'includes/admin_service.php?action=updateSegment',
		            	data: $('form').serialize(),
		            	success: function (response) {
							//alert('Sent Push Notification to users');
							$body.removeClass("loading");
							if(response == 1){
								$("#send_result").removeClass('error').addClass('success');
								$("#send_result").text('update Segment successfully');
								$("#send_result").show();
							}else{
								$("#send_result").removeClass('success').addClass('error');
								$("#send_result").text('Faild to update new segment');
								$("#send_result").show();
							}
							
		              		
		            	}
		          	});
				}
	        });

	    });
	    
	  	function toggle(source) {
	  	  	checkboxes = document.getElementsByName('chk_usergroup[]');
	  	  	for(var i=0, n=checkboxes.length;i<n;i++) {
	  	    	checkboxes[i].checked = source.checked;
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
			
			$id = $_GET['id'];
			$segment = $server->admin_getUserSegment($id);
			$segmentUsers = $segment['userIds'];
	?>
	
	<div id="page-wrap">
		
	<div>
	<a href="javascript:history.back(1);" class="logout">Go Back</a>
	</div>

	<center><h1>Edit Segment</h1></center>
	
	
	<form id="FSForm">
		
		<input type="hidden" name="sid" value="<?php echo $id;?>" id="sid">
		<!--
		
		<input type="submit" name="segment1" value="Segment1" class="submit_button" id="FSSegment1">
		<input type="submit" name="segment2" value="Segment2" class="submit_button" id="FSSegment2">
		-->
		<label for="notification" style="margin-top: 30px;">Segment Name: </label>
		<textarea rows="4" cols="100" id="segmentName" name="segmentName"><?php echo $segment['sname']; ?></textarea>
		
		<div  style="float: right;" class="block-container">
			<span id="send_result"></span>
			
			<input type="submit" name="submit" value="Save" class="submit_button" id="FSSaveSegment">
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
					<td>
						<?php

							if(in_array($user['userId'], $segmentUsers )){
						?>
							<input type="checkbox" name="chk_usergroup[]" value="<?php echo $user['userId']; ?>" checked />    &nbsp;
						<?php
							}
							else{
							
						?>
							<input type="checkbox" name="chk_usergroup[]" value="<?php echo $user['userId']; ?>" />    &nbsp;
						<?php
							}
						?>
					</td>
					<td><?php echo $i . ''; ?></td>
					<td><?php echo $user['userName']; ?></td>
					<td><?php echo $user['fbEmail']; ?></td>
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