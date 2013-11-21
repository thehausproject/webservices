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
	$result = mysqli_query($con, "SELECT ID, USERNAME FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
	$user_id = NULL;
	$username = NULL;
	if($row = mysqli_fetch_array($result)){
		$user_id = $row['ID'];
		$username = $row['USERNAME'];
	}else{
		echo "{\"error\":\"User token is invalid.\"}";
		exit;
	}

	// Check that device has not already been claimed
	$result = mysqli_query($con, "SELECT ID, OWNER, TYPE FROM DEVICE WHERE PASSCODE = '" . $passcode . "'");
	$device_id = NULL;
	$owner = NULL;
	$device_type = NULL;
	if($row = mysqli_fetch_array($result)){
		$device_id = $row['ID'];
		$owner = $row['OWNER'];
		$device_type = $row['TYPE'];
	}else{
		echo "{\"error\":\"Passcode is invalid.\"}";
		exit;
	}

	// Update the devicee's owner and nickname
	if(is_null($owner)){
		mysqli_query($con, "UPDATE DEVICE SET OWNER = " . intval($user_id) . ", NICKNAME = '" . $nickname .
			"' WHERE PASSCODE = '" . $passcode . "'");
	}else{
		echo "{\"error\":\"Device has already been claimed.\"}";
		exit;
	}

	// Create the administrative device permission for the device owner
	$url = "http://www.dylanboltz.com/haus/grantuserpermission.php?username=" . $username . "&user_token=" . $user_token . "&device_id=" .
		$device_id . "&permission_level=A";
	if($device_type == 'L'){
		$url = $url . "&access_code=99999999";
	}
	include $url;

}

mysqli_close($con);

?>