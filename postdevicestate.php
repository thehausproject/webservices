<!--
Title:	Post Device State
Author:	Dylan Boltz
Date:	11/21/2013

The purpose of this code is to post a device state.

-->

<?php

// Get Query Parameters
$user_token = $_GET['user_token'];
$device_id = $_GET['device_id'];
$state = $_GET['state'];

// Check that parameters are not null
if(is_null($user_token) || is_null($device_id) || is_null($state)){
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

// Get the user ID
$result = mysqli_query($con, "SELECT ID FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
$user_id = NULL;
if($row = mysqli_fetch_array($result)){
	$user_id = $row['ID'];
}else{
	echo "{\"error\":\"Invalid user token.\"}";
	exit;
}

// Get the device type and ensure that the state is valid
$result = mysqli_query($con, "SELECT TYPE FROM DEVICE WHERE ID = '" . $device_id . "'");
if($row = mysqli_fetch_array($result)){
	$device_type = $row['TYPE'];
	if($device_type == 'L'){
		if($state != "LOCKED" && $state != "UNLOCKED"){
			echo "{\"error\":\"Invalid state provided.\"}";
		}
	}
}else{
	echo "{\"error\":\"Invalid device ID provided.\"}";
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
		echo "{\"error\":\"Permission level not high enough to perform this action.\"}";
		exit;
	}
}else{
	echo "{\"error\":\"No permission granted to this user.\"}";
	exit;
}

// Update the device state
mysqli_query($con, "UPDATE DEVICE SET STATE = '" . $state . "' WHERE ID = '" . $device_id . "'");
echo "{\"result\":\"success.\"}";