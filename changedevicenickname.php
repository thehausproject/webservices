<!--
Title:	Change Nickname
Author:	Dylan Boltz
Date:	11/21/2013

The purpose of this code is to allow a user to claim their registered devices.

-->

<?php

// Get Query Parameters
$device_id = $_GET['device_id'];
$user_token = $_GET['user_token'];
$new_nickname = $_GET['new_nickname'];

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	echo "{\"error\":\"Could not connect to database.\"}";
	exit;
}

$result = mysqli_query($con, "SELECT ID FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
$user_id = NULL;
if($row = mysqli_fetch_array($result)){
	$user_id = $row['ID'];
}else{
	echo "{\"error\":\"User token is invalid.\"}";
	exit;
}

$result = mysqli_query($con, "SELECT OWNER FROM DEVICE WHERE ID = '" . $device_id . "'");
if($row = mysqli_fetch_array($result)){
	$owner = $row['OWNER'];
	if(intval($owner) != intval($user_id)){
		echo "{\"error\":\"User must be the device owner to change the nickname.\"}";
		exit;
	}
}else{
	echo "{\"error\":\"Invalid device ID.\"}";
	exit;
}

$update_query = "UPDATE DEVICE SET NICKNAME = '" . $new_nickname . "' WHERE ID = " . $device_id;
mysqli_query($con, $update_query);

echo "{\"result\":\"success\"}";

?>