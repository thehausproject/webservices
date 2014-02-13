<?php

/*
Title:	Claim Device
Author:	Dylan Boltz
Date:	11/19/2013

The purpose of this code is to allow a user to claim their registered devices.

*/

include 'commonfunctions.php';

// Get Query Parameters
$user_token = $_POST['user_token'];
$passcode = $_POST['passcode'];
$nickname = $_POST['nickname'];

// Check that parameters are not null
if(empty($user_token) || empty($passcode) || empty($nickname)){
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
		output_error('Passcode is invalid');
	}

	// Update the devicee's owner and nickname
	if(empty($owner)){
		mysqli_query($con, "UPDATE DEVICE SET OWNER = " . intval($user_id) . ", NICKNAME = '" . $nickname .
			"' WHERE PASSCODE = '" . $passcode . "'");
	}else{
		output_error('Device has already been claimed');
	}

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