<?php

/*
Title:	Post Device Updates
Author:	Dylan Boltz
Date:	11/24/2013

The purpose of this code is to for a device to post updates about its current state.

*/

// Get Query Parameters
$passcode = $_POST['passcode'];
$access_code = $_POST['access_code'];

// Check that parameters are not null
if(is_null($passcode) || is_null($access_code)){
	echo json_encode(array('error' => 'Insufficient parameters provided'));
	exit;
}

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	echo json_encode(array('error' => 'Could not connect to database'));
	exit;
}

$result = mysqli_query($con, "SELECT ID FROM DEVICE WHERE PASSCODE = '" . $passcode . "'");
$device_id = NULL;
if($row = mysqli_fetch_array($result)){
	$device_id = $row['ID'];
}else{
	echo json_encode(array('error' => 'No device with this passcode'));
	exit;
}

// Get access code for this user permission
$result = mysqli_query($con, "SELECT ACCESS_CODE FROM DEVICE_PERMISSION WHERE DEVICE_ID = '" . $device_id . 
	"' AND ACCESS_CODE = '" . $access_code . "'");
if($row = mysqli_fetch_array($result)){
	mysqli_query($con, "UPDATE DEVICE SET STATE = 'UNLOCKED' WHERE ID = '" . $device_id . "'");
	echo json_encode(array('result' => 'success'));
}else{
	echo json_encode(array('error' => 'Invalid access code'));
	exit;
}

?>