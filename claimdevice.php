<!--
Title:	Claim Device
Author:	Dylan Boltz
Date:	11/19/2013

The purpose of this code is to allow a user to claim their registered devices.

-->

<?php

// Get Query Parameters
$user_token = $_GET['user_token'];
$passcode = $_GET['passcode'];
$nickname = $_GET['nickname'];

// Check that parameters are not null
if(is_null($user_token) || is_null($passcode) || is_null($nickname)){
	echo "{\"error\":\"Insufficient parameters provided.\"}";
	exit;
}

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	echo "{\"error\":\"Could not connect to database.\"}";
}else{
	// Retrieve user id based on the user token
	$result = mysqli_query($con, "SELECT ID FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
	$user_id = 0;
	if($row = mysqli_fetch_array($result)){
		$user_id = $row['ID'];
	}else{
		echo "{\"error\":\"User token is invalid.\"}";
		exit;
	}

	// Check that device has not already been claimed
	$result = mysqli_query($con, "SELECT OWNER FROM DEVICE WHERE PASSCODE = '" . $passcode . "'");
	$owner = 0;
	if($row = mysqli_fetch_array($result)){
		$owner = $row['OWNER'];
	}else{
		echo "{\"error\":\"Passcode is invalid.\"}";
		exit;
	}

	// Update the devicee's owner and nickname
	if(is_null($owner)){
		mysqli_query($con, "UPDATE DEVICE SET OWNER = " . intval($user_id) . ", NICKNAME = '" . $nickname .
			"' WHERE PASSCODE = '" . $passcode . "'");
		echo "{\"result\":\"success\"}";
	}else{
		echo "{\"error\":\"Device has already been claimed.\"}";
	}

	// Create the administrative device permission for the device owner
	
}

mysqli_close($con);

?>