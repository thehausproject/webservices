<?php

/*
Title:	Revoke User Access Time
Author:	Dylan Boltz
Date:	2/12/2014

The purpose of this code is to revoke a user access restriction on a device.

*/

include 'commonfunctions.php';

// Get Query Parameters
$user_token = $_POST['user_token'];
$access_day_id = $_POST['access_day_id'];

// Check that parameters are not null
if(empty($user_token) || empty($access_day_id)){
	output_error('Insufficient parameters provided');
}

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	output_error('Could not connect to database');
}

// Check that token is valid
$result = mysqli_query($con, "SELECT ID FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
$user_id = NULL;
if($row = mysqli_fetch_array($result)){
	$user_id = $row['ID'];
}else{
	output_error('Invalid user token');
}

// Get device ID
$device_id = NULL;
$result = mysqli_query($con, "SELECT DEVICE_ID FROM DEVICE_PERMISSION WHERE ID = (SELECT DEVICE_PERMISSION_ID FROM DEVICE_ACCESS WHERE ID = 
	(SELECT DEVICE_ACCESS_ID FROM DEVICE_ACCESS_DAY WHERE  ID = " . $access_day_id . "))");
if($row = mysqli_fetch_array($result)){
	$device_id = $row['DEVICE_ID'];
}else{
	output_error('Invalid access day ID');
}

// Ensure that this user has permission to do this
validate_administrator($con, $user_id, $device_id);

// Check that access restriction exists
$device_access_id = NULL;
$all_access = NULL;
$result = mysqli_query($con, "SELECT DEVICE_ACCESS_ID, ALL_ACCESS FROM DEVICE_ACCESS_DAY WHERE ID = " . $access_day_id);
if($row = mysqli_fetch_array($result)){
	$device_access_id = $row['DEVICE_ACCESS_ID'];
	$all_access = $row['ALL_ACCESS'];
}else{
	output_error('Invalid access day ID');
}

// Delete the access time that may exist for this access day
if($all_access == 0){
	mysqli_query($con, "DELETE FROM DEVICE_ACCESS_TIME WHERE ACCESS_DAY_ID = " . $access_day_id);
}

// Delete the day access record
mysqli_query($con, "DELETE FROM DEVICE_ACCESS_DAY WHERE ID = " . $access_day_id);

// Reset the ALL_ACCESS flag to 1 if there are no other access day records for this device access record
$result = mysqli_query($con, "SELECT ID FROM DEVICE_ACCESS_DAY WHERE DEVICE_ACCESS_ID = " . $device_access_id);
if(!($row = mysqli_fetch_array($result))){
	mysqli_query($con, "UPDATE DEVICE_ACCESS SET ALL_ACCESS = 1 WHERE ID = " . $device_access_id);
}

return_success();

?>