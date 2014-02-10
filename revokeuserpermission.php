<?php

/*
Title:	Revoke User Permission
Author:	Dylan Boltz
Date:	2/8/2013

The purpose of this code is to revoke a user permission for a device.

*/

include 'commonfunctions.php';

// Get Query Parameters
$user_token = $_POST['user_token'];
$permission_id = $_POST['permission_id'];

// Check that parameters are not null
if(is_null($user_token) || is_null($permission_id)){
	output_error('Insufficient parameters provided');
}

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	output_error('Could not connect to database');
}

// More preliminary error checking to follow

// Check that token is valid
$result = mysqli_query($con, "SELECT ID FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
$user_id = NULL;
if($row = mysqli_fetch_array($result)){
	$user_id = $row['ID'];
}else{
	output_error('Invalid user token');
}

// Get the device ID
$device_id = NULL;
$result = mysqli_query($con, "SELECT DEVICE_ID FROM DEVICE_PERMISSION WHERE ID = '" . $permission_id . "'");
if($row = mysqli_fetch_array($result)){
	$device_id = $row['DEVICE_ID'];
}else{
	output_error('Invalid permission ID');
}

// Check if user revoking permission is administrator
$user_expiration_date = NULL;
$result = mysqli_query($con, "SELECT PERMISSION, ACCESS_EXPIRATION_DATE FROM DEVICE_PERMISSION WHERE USER_ID = '" . $user_id . 
	"' AND DEVICE_ID = '" . $device_id . "'");
$user_permission = NULL;
$user_expiration_date = NULL;
if($row = mysqli_fetch_array($result)){
	$user_permission = $row['PERMISSION'];
	$user_expiration_date = $row['ACCESS_EXPIRATION_DATE'];
	if($user_permission != 'A'){
		output_error('User does not have permission to grant access');
	}
}

// If user is administrator, check that the permission has not expired
$current_date = NULL;
if($user_permission == 'A' && !is_null($user_expiration_date) && $user_expiration_date != '0000-00-00'){
	$expiration_date_obj = new DateTime($user_expiration_date);
	$current_date = new DateTime("now");
	if($current_date > $expiration_date_obj){
		// And update permission in table to 'E' for expired
		mysqli_query($con, "UPDATE DEVICE_PERMISSION SET PERMISSION = 'E' WHERE USER_ID = '" . $user_id .
			"' AND DEVICE_ID = '" . $device_id . "')");
		output_error('User does not have permission to grant access');
	}
}

// Delete the permission record
mysqli_query($con, "DELETE FROM DEVICE_PERMISSION WHERE ID = '" . $permission_id . "'");

// Delete any access records that existed for the permission in a cascading fashion
$result = mysqli_query($con, "SELECT ID, ALL_ACCESS FROM DEVICE_ACCESS WHERE DEVICE_PERMISSION_ID = " . $permission_id);
if($row = mysqli_fetch_array($result)){
	if($row['ALL_ACCESS'] == '0'){
		$result = mysqli_query($con, "SELECT ID, ALL_ACCESS FROM DEVICE_ACCESS_DAY WHERE DEVICE_ACCESS_ID = " . $row['ID']);
		while($row2 = mysqli_fetch_array($result)){
			if($row2['ALL_ACCESS'] == '0'){
				mysqli_query($con, "DELETE FROM DEVICE_ACCESS_TIME WHERE ACCESS_DAY_ID = " . $row2['ID']);
			}
			mysqli_query($con, "DELETE FROM DEVICE_ACCESS_DAY WHERE ID = " . $row2['ID']);
		}
	}
	mysqli_query($con, "DELETE FROM DEVICE_ACCESS WHERE ID = " . $row['ID']);
}

echo json_encode(array('result' => 'success'));

?>