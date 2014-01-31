<?php

/*
Title:	Post Device Status
Author:	Dylan Boltz
Date:	11/21/2013

The purpose of this code is to post a device status.

*/

// Get Query Parameters
$user_token = $_POST['user_token'];
$device_id = $_POST['device_id'];
$status = $_POST['status'];

// Check that parameters are not null
if(is_null($user_token) || is_null($device_id) || is_null($status)){
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

// Get the user ID, ensuring that the user token is valid
$result = mysqli_query($con, "SELECT ID FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
$user_id = NULL;
if($row = mysqli_fetch_array($result)){
	$user_id = $row['ID'];
}else{
	echo json_encode(array('error' => 'Invalid user token'));
	exit;
}

// Ensure that the device exists
$result = mysqli_query($con, "SELECT ID FROM DEVICE WHERE ID = '" . $device_id . "'");
if(!mysqli_fetch_array($result)){
	echo json_encode(array('error' => 'Invalid device ID provided'));
	exit;
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
		echo json_encode(array('error' => 'Permission level is not high enough to perform this action'));
		exit;
	}
}else{
	echo json_encode(array('error' => 'No permission granted to this user'));
	exit;
}

// Check that the status provided is valid
if($status != 'U' && $status != 'D' && $status != 'L'){
	echo json_encode(array('error' => 'Invalid state provided'));
	exit;
}

// Update the device status
mysqli_query($con, "UPDATE DEVICE SET STATUS = '" . $status . "' WHERE ID = " . $device_id);
echo json_encode(array('result' => 'success'));

?>