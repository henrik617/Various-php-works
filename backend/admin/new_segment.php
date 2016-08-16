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
		            	url: 'includes/admin_service.php?action=createSegment',
		            	data: $('form').serialize(),
		            	success: function (response) {
							
							//alert('Sent Push Notification to users');
							$body.removeClass("loading");
							if(response == 1){
								$("#send_result").removeClass('error').addClass('success');
								$("#send_result").text('Create Segment successfully');
								$("#send_result").show();
							}else{
								$("#send_result").removeClass('success').addClass('error');
								$("#send_result").text('Faild to Create new segment');
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
	?>
	
	<div id="page-wrap">
		
	<div>
	<a href="javascript:history.back(1);" class="logout">Go Back</a>
	</div>

	<center><h1>Create Segment</h1></center>
	
	
	<form id="FSForm">
		
		<!--
		<input type="submit" name="All" value="All" class="submit_button" id="FSSegmentAll">
		<input type="submit" name="segment1" value="Segment1" class="submit_button" id="FSSegment1">
		<input type="submit" name="segment2" value="Segment2" class="submit_button" id="FSSegment2">
		-->
		<label for="notification" style="margin-top: 30px;">Segment Name: </label>
		<textarea rows="4" cols="100" id="segmentName" name="segmentName"></textarea>
		
		<div  style="float: right;" class="block-container">
			<span id="send_result"></span>
			
			<input type="submit" name="submit" value="Create" class="submit_button" id="FSCreateSegment">
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