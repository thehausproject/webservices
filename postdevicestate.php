<?php

/*
Title:	Post Device State
Author:	Dylan Boltz
Date:	11/21/2013

The purpose of this code is to post a device state.

*/

include 'commonfunctions.php';

// Get Query Parameters
$user_token = $_POST['user_token'];
$device_id = $_POST['device_id'];
$state = $_POST['state'];

// Check that parameters are not null
if(is_null($user_token) || is_null($device_id) || is_null($state)){
	output_error('Insufficient parameters provided');
}

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	output_error('Could not connect to database');
}

// Get the user ID, ensuring that the user token is valid
$result = mysqli_query($con, "SELECT ID FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
$user_id = NULL;
if($row = mysqli_fetch_array($result)){
	$user_id = $row['ID'];
}else{
	output_error('Invalid user token');
}

// Get the device type and ensure that the state is valid
$result = mysqli_query($con, "SELECT TYPE FROM DEVICE WHERE ID = '" . $device_id . "'");
if($row = mysqli_fetch_array($result)){
	$device_type = $row['TYPE'];
	if($device_type == 'L'){
		if($state != "LOCKED" && $state != "UNLOCKED"){
			output_error('Invalid state provided');
		}
	}
}else{
	output_error('Invalid device ID provided');
}

// Verify that a permission exists for the user making the request
$result = mysqli_query($con, "SELECT ID, PERMISSION FROM DEVICE_PERMISSION WHERE USER_ID = '" . $user_id . 
	"' AND DEVICE_ID = '" . $device_id . "'");
$device_permission_id = NULL;
$user_permission = NULL;
if($row = mysqli_fetch_array($result)){
	$device_permission_id = $row['ID'];
	$user_permission = $row['PERMISSION'];
	//Check that the user permission is administrator or write
	if($user_permission != 'A' && $user_permission != 'W'){
		output_error('Permission level not high enough to perform this action');
	}
}else{
	output_error('No permission granted to this user');
}

// Check to make sure no access restrictions prevent this
if(!can_access_now($con, $device_permission_id)){
	output_error('Access is restricted at this time');
}

// Update the device state
mysqli_query($con, "UPDATE DEVICE SET STATE = '" . $state . "' WHERE ID = " . $device_id);
echo json_encode(array('result' => 'success'));

?>