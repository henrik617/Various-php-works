<?php
	
	include_once 'includes/db_connect.php';
	include_once 'includes/functions.php';
	
	sec_session_start(); 

?>
<!DOCTYPE html>
<html>

<head>
	<meta charset='UTF-8'>
	
	<title>Segments</title>
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link rel="stylesheet" href="css/style1.css">
	
	<script src="jquery-1.9.1.min.js"></script>
	<script>
	
		$(function () {

			$body = $("body");
	        

	    });
	    
		function deleteSegment(source){
			
          	$.ajax({
            	type: 'post',
            	url: 'includes/admin_service.php?action=deleteSegment',
            	data: {'id': source.id},
            	success: function (response) {
					//alert('Sent Push Notification to users');
					alert(response);
              		$body.removeClass("loading");
					
					location.reload();
            	}
          	});
		}
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
			$segments = $server->admin_getAllUserSegments();
	?>
	
	<div id="page-wrap">
		
	<div>
	<a href="javascript:history.back(1);" class="logout">Go Back</a>
	</div>

	<center><h1>Segments</h1></center>
	
	
	<form id="FSForm">
		
		<!--
		<input type="submit" name="All" value="All" class="submit_button" id="FSSegmentAll">
		<input type="submit" name="segment1" value="Segment1" class="submit_button" id="FSSegment1">
		<input type="submit" name="segment2" value="Segment2" class="submit_button" id="FSSegment2">
		-->
		
		<div  style="float: right;" class="block-container">
			<span id="send_result"></span>
			<input type="button" name="new" value="Create" class="submit_button" id="FSCreate" onclick="window.location.href='new_segment.php'">
		</div>
		
			
		<table>
			<tr>
				<th>No.</th>
				<th>Segment Name</th>
				<th>Number of Receivers</th>
				<th>Action</th>
			</tr>
			<?php
				$i = 0;
				if(count($segments) > 0){
					foreach($segments as $segment){
							$i++;
					?>
						<tr>
					
							<td><?php echo $i . ''; ?></td>
							<td><?php echo $segment['sname']; ?></td>
							<td><?php echo count($segment['userIds']); ?></td>
							<td><input type="button" name="deleteButton" value="Delete" id="<?php echo $segment['id']; ?>" onClick="deleteSegment(this);"> &nbsp;&nbsp;
								<input type="button" name="editButton" value="Edit" id="editButton" onclick="window.location.href='edit_segment.php?id=<?php echo $segment['id']; ?>'"></td>
						</tr>
					<?php
					}
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