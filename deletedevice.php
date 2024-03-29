<?php

/*
Title:	Delete Device
Author:	Dylan Boltz
Date:	11/21/2013

The purpose of this code is to delete a device.

*/

include 'commonfunctions.php';

// Get Query Parameters
$user_token = $_POST['user_token'];
$device_id = $_POST['device_id'];

// Check that parameters are not null
if(empty($user_token) || empty($device_id)){
	output_error('Insufficient parameters provided');
}

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	output_error('Could not connect to database');
}

// Check that token is correct
$result = mysqli_query($con, "SELECT ID FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
$id = NULL;
if($row = mysqli_fetch_array($result)){
	$id = $row['ID'];
}else{
	output_error('Invalid user token');
}

// Delete devices owned by the user and the permissions related to the device
$result = mysqli_query($con, "SELECT ID FROM DEVICE WHERE OWNER = " . $id);
if($row = mysqli_fetch_array($result)){
	$device_id = $row['ID'];
	mysqli_query($con, "DELETE FROM DEVICE WHERE ID = " . $device_id);
	$result2 = mysqli_query($con, "SELECT ID FROM DEVICE_PERMISSION WHERE DEVICE_ID = " . $device_id);
	while($row2 = mysqli_fetch_array($result2)){
		delete_permission_restrictions($con, $row2['ID']);
	}
	mysqli_query($con, "DELETE FROM DEVICE_PERMISSION WHERE DEVICE_ID = " . $device_id);
}else{
	output_error('User must own the device in order to delete it');
}

echo json_encode(array('result' => 'success'));

?>