<?php

/*
Title:	Get Video Device Info
Author:	Dylan Boltz
Date:	4/6/2013

The purpose of this code is to return the information for a particular video device.

*/

include 'commonfunctions.php';

// Get Query Parameters
$user_token = $_GET['user_token'];
$device_id = $_GET['device_id'];

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

// Check that token is valid and get the user ID
$result = mysqli_query($con, "SELECT ID FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
$user_id = NULL;
if($row = mysqli_fetch_array($result)){
	$user_id = $row['ID'];
}else{
	output_error('Invalid user token');
}

$result = mysqli_query($con, "SELECT IP_ADDRESS, PORT, USERNAME, PASSWORD FROM VIDEO_DEVICE WHERE DEVICE_ID = ". $device_id);
$row = mysqli_fetch_array($result);
echo json_encode(array('ip_address' => $row['IP_ADDRESS'], 'port' => $row['PORT'], 'username' => $row['USERNAME'], 'password' => $row['PASSWORD']));

?>