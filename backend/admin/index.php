<?php

	include_once 'includes/db_connect.php';
	include_once 'includes/functions.php';
 
	sec_session_start();
 
	if (login_check($mysqli) == true) {
	    $logged = 'in';
	} else {
	    $logged = 'out';
	}

?>
<!DOCTYPE html>
<!--[if lt IE 7]> <html class="lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]> <html class="lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]> <html class="lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <title>Login Form</title>
  <link rel="stylesheet" href="css/style.css">
  <!--[if lt IE 9]><script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
</head>
<body>
	        
  <section class="container">
    <div class="login">
      <h1>Login to Admin Panel</h1>
      <form action="includes/process_login.php" method="post" name="login_form">
        <p><input type="text" name="username" value="" placeholder="Username"></p>
        <p><input type="password" name="password" value="" placeholder="Password"></p>
        
        <p class="submit"><input type="submit" name="commit" value="Login"></p>
      </form>
	  
  	<?php
      	if (isset($_GET['error'])) {
          	echo '<p class="error">*' . $_GET['error'] .'</p>';
      	}
      ?>
    </div>
  </section>

</body>
</html>
