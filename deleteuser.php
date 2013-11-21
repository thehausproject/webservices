<!--
Title:	Delete Device
Author:	Dylan Boltz
Date:	11/21/2013

The purpose of this code is to delete a user.  Deleteing a user also results in the deletion of all
devices, device permissions, and access permissions associated with the user and the devices that
the user owns.

-->

<?php

// Get Query Parameters
$user_token = $_GET['user_token'];
$password = $_GET['password'];

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	echo "{\"error\":\"Could not connect to database.\"}";
	exit;
}

// Check that token and password are correct
$result = mysqli_query($con, "SELECT ID, PASSWORD FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
$id = NULL;
if($row = mysqli_fetch_array($result)){
	$id = $row['ID'];
	$db_password = $row['PASSWORD'];
	$hashed_password = hash('sha256', $password);
	if($db_password != $hashed_password){
		echo "{\"error\":\"Incorrect password.\"}";
		exit;
	}
}else{
	echo "{\"error\":\"Invalid user token.\"}";
	exit;
}

// Delete the user
mysqli_query($con, "DELETE FROM USER WHERE ID = " . $id);

// Delete devices owned by the user and the permissions related to the device
$result = mysqli_query($con, "SELECT ID FROM DEVICE WHERE OWNER = " . $id);
while($row = mysqli_fetch_array($result)){
	$device_id = $row['ID'];
	mysqli_query($con, "DELETE FROM DEVICE WHERE ID = " . $device_id);
	mysqli_query($con, "DELETE FROM DEVICE_PERMISSION WHERE DEVICE_ID = " . $device_id);
}

echo "{\"result\":\"success\"}";

?>