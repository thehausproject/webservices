<?php

/*
Title:	Post Device Updates
Author:	Dylan Boltz
Date:	11/24/2013

The purpose of this code is to for a device to post updates about its current state.

*/

include 'commonfunctions.php';

// Get Query Parameters
$passcode = $_GET['passcode'];
$access_code = $_GET['access_code'];

// Check that parameters are not null
if(empty($passcode) || empty($access_code)){
	output_error('Insufficient parameters provided');
}

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	output_error('Could not connect to database');
}

$result = mysqli_query($con, "SELECT ID FROM DEVICE WHERE PASSCODE = '" . $passcode . "'");
$device_id = NULL;
if($row = mysqli_fetch_array($result)){
	$device_id = $row['ID'];
}else{
	output_error('No device with this passcode');
}

// Get access code for this user permission
$result = mysqli_query($con, "SELECT ID, ACCESS_EXPIRATION_DATE, ACCESS_CODE FROM DEVICE_PERMISSION WHERE DEVICE_ID = '" . $device_id . 
	"' AND ACCESS_CODE = '" . $access_code . "'");
if($row = mysqli_fetch_array($result)){
	if(!can_access_now($con, $row['ID'])){
		output_error('Access is restricted at this time');
	}
	//Check that the permission is not expired
	if(is_expired($con, $row['ACCESS_EXPIRATION_DATE'])){
		$permission = 'E';
		mysqli_query($con, "UPDATE DEVICE_PERMISSION SET PERMISSION = 'E' WHERE ID = " . $row['ID']);
		output_error('Permission is expired');
	}
	mysqli_query($con, "UPDATE DEVICE SET STATE = 'UNLOCKED' WHERE ID = '" . $device_id . "'");
	echo json_encode(array('result' => 'success'));
}else{
	output_error('Invalid access code');
}

?>