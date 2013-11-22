<!--
Title:	Delete Device
Author:	Dylan Boltz
Date:	11/21/2013

The purpose of this code is to delete a devic.

-->

<?php

// Get Query Parameters
$user_token = $_GET['user_token'];
$device_id = $_GET['device_id'];

// Check that parameters are not null
if(is_null($user_token) || is_null($device_id)){
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

// Check that token is correct
$result = mysqli_query($con, "SELECT ID FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
$id = NULL;
if($row = mysqli_fetch_array($result)){
	$id = $row['ID'];
}else{
	echo "{\"error\":\"Invalid user token.\"}";
	exit;
}

// Delete devices owned by the user and the permissions related to the device
$result = mysqli_query($con, "SELECT ID FROM DEVICE WHERE OWNER = " . $id);
if($row = mysqli_fetch_array($result)){
	$device_id = $row['ID'];
	mysqli_query($con, "DELETE FROM DEVICE WHERE ID = " . $device_id);
	mysqli_query($con, "DELETE FROM DEVICE_PERMISSION WHERE DEVICE_ID = " . $device_id);
}else{
	echo "{\"error\":\"User must own the device in order to delete it.\"}";
}

echo "{\"result\":\"success\"}";

?>