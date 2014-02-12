<?php

/*
Title:	Delete User
Author:	Dylan Boltz
Date:	11/21/2013

The purpose of this code is to delete a user.  Deleteing a user also results in the deletion of all
devices, device permissions, and access permissions associated with the user and the devices that
the user owns.

*/

include 'commonfunctions.php';

// Get Query Parameters
$user_token = $_POST['user_token'];
$password = $_POST['password'];

// Check that parameters are not null
if(is_null($user_token) || is_null($password)){
	output_error('Insufficient parameters provided');
}

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	output_error('Could not connect to database');
}

// Check that token and password are correct
$result = mysqli_query($con, "SELECT ID, PASSWORD FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
$id = NULL;
if($row = mysqli_fetch_array($result)){
	$id = $row['ID'];
	$db_password = $row['PASSWORD'];
	$hashed_password = hash('sha256', $password);
	if($db_password != $hashed_password){
		output_error('Incorrect password');
	}
}else{
	output_error('Invalid user token');
}

// Delete the user
mysqli_query($con, "DELETE FROM USER WHERE ID = " . $id);

// Delete devices owned by the user and the permissions related to the device
$result = mysqli_query($con, "SELECT ID FROM DEVICE WHERE OWNER = " . $id);
while($row = mysqli_fetch_array($result)){
	$device_id = $row['ID'];
	mysqli_query($con, "DELETE FROM DEVICE WHERE ID = " . $device_id);
	$result2 = mysqli_query($con, "SELECT ID FROM DEVICE_PERMISSION WHERE DEVICE_ID = " . $device_id);
	while($row2 = mysqli_fetch_array($result2)){
		delete_permission_restrictions($con, $row2['ID']);
	}
	mysqli_query($con, "DELETE FROM DEVICE_PERMISSION WHERE DEVICE_ID = " . $device_id);
}

echo json_encode(array('result' => 'success'));

?>