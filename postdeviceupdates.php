<!--
Title:	Post Device Updates
Author:	Dylan Boltz
Date:	11/24/2013

The purpose of this code is to for a device to post updates about its current state.

-->

<?php

// Get Query Parameters
$passcode = $_GET['passcode'];
$access_code = $_GET['access_code'];

// Check that parameters are not null
if(is_null($passcode) || is_null($access_code)){
	echo "{\"error\":\"Insufficient parameters provided.\"}";
	exit;
}

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	echo "{\"error\":\"Could not connect to database.\"}";
	exit;
}

$result = mysqli_query($con, "SELECT ID FROM DEVICE WHERE PASSCODE = '" . $passcode . "'");
$device_id = NULL;
if($row = mysqli_fetch_array($result)){
	$device_id = $row['ID'];
}else{
	echo "{\"error\":\"No device with this passcode.\"}";
	exit;
}

// Get access code for this user permission
$result = mysqli_query($con, "SELECT ACCESS_CODE FROM DEVICE_PERMISSION WHERE DEVICE_ID = '" . $device_id . 
	"' AND ACCESS_CODE = '" . $access_code . "'");
if($row = mysqli_fetch_array($result)){
	mysqli_query($con, "UPDATE DEVICE SET STATE = 'UNLOCKED' WHERE ID = '" . $device_id . "'");
	echo "{\"result\":\"success\"}";
}else{
	echo "{\"error\":\"Invalid access code.\"}";
	exit;
}

?>