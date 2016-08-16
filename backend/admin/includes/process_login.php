<?php

include_once 'db_connect.php';
include_once 'functions.php';
 
sec_session_start(); // Our custom secure way of starting a PHP session.
 
if (isset($_POST['username'], $_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password']; // The hashed password.
 
 	$loginResult = login($username, $password, $mysqli);
    if ($loginResult == "success") {
        // Login success 
        header('Location: ../admin.php');
    } else {
        // Login failed 
        header('Location: ../index.php?error='.$loginResult);
    }
} else {
    // The correct POST variables were not sent to this page. 
    echo 'Invalid Request';
}

?>