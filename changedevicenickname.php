<?php

/*
Title:	Change Nickname
Author:	Dylan Boltz
Date:	11/21/2013

The purpose of this code is to allow a user to claim their registered devices.

*/

include 'commonfunctions.php';

// Get Query Parameters
$device_id = $_POST['device_id'];
$user_token = $_POST['user_token'];
$new_nickname = $_POST['new_nickname'];

// Check that parameters are not null
if(empty($device_id) || empty($user_token) || empty($new_nickname)){
	output_error('Insufficient parameters provided');
}

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	output_error('Could not connect to database');
}

$result = mysqli_query($con, "SELECT ID FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
$user_id = NULL;
if($row = mysqli_fetch_array($result)){
	$user_id = $row['ID'];
}else{
	output_error('User token is invalid');
}

$result = mysqli_query($con, "SELECT OWNER FROM DEVICE WHERE ID = '" . $device_id . "'");
if($row = mysqli_fetch_array($result)){
	$owner = $row['OWNER'];
	if(intval($owner) != intval($user_id)){
		output_error('User must be the device owner to change the nickname');
	}
}else{
	output_error('Invalid device ID');
}

$update_query = "UPDATE DEVICE SET NICKNAME = '" . $new_nickname . "' WHERE ID = " . $device_id;
mysqli_query($con, $update_query);

echo json_encode(array('result' => 'success'));

?>