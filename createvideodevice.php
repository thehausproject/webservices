<?php

/*
Title:	Create Video Device
Author:	Dylan Boltz
Date:	4/6/2014

The purpose of this code is to allow a user to create a video device in the system.

*/

include 'commonfunctions.php';

// Get Query Parameters
$user_token = $_POST['user_token'];
$nickname = $_POST['nickname'];
$ip_address = $_POST['ip_address'];
$port = $_POST['port'];
$d_username = $_POST['d_username'];
$d_password = $_POST['d_password'];

// Check that parameters are not null
if(empty($user_token) || empty($nickname) || empty($ip_address) || empty($port) || empty($d_password) || empty($d_username)){
	output_error('Insufficient parameters provided');
}

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	output_error('Could not connect to database');
}else{
	// Retrieve user id based on the user token
	$result = mysqli_query($con, "SELECT ID, USERNAME FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
	$user_id = NULL;
	$username = NULL;
	if($row = mysqli_fetch_array($result)){
		$user_id = $row['ID'];
		$username = $row['USERNAME'];
	}else{
		output_error('User token is invalid');
	}

	
	mysqli_query($con, "INSERT INTO DEVICE (OWNER, TYPE, STATUS, NICKNAME) VALUES (" . $user_id . ", 'V', 'U', '" . $nickname . "')");
	$device_id = mysqli_insert_id($con);

	// Make the entry for this video device in the video device table
	mysqli_query($con, "INSERT INTO VIDEO_DEVICE (DEVICE_ID, IP_ADDRESS, PORT, USERNAME, PASSWORD) VALUES (" . $device_id . ", '" . 
		$ip_address . "', " . $port . ", '" . $d_username . "', '" . $d_password . "')");

	// Create the administrative device permission for the device owner
	$url = "http://www.dylanboltz.com/haus/grantuserpermission.php";
	$postvars = "username=" . $username . "&user_token=" . $user_token . "&device_id=" . $device_id . "&permission_level=A";
	if($device_type == 'L'){
		$postvars = $postvars . "&access_code=99999999";
	}
	
	$ch = curl_init( $url );
	curl_setopt( $ch, CURLOPT_POST, 1);
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $postvars);
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt( $ch, CURLOPT_HEADER, 0);
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

	$response = curl_exec( $ch );
	echo $response;
}

mysqli_close($con);

?>