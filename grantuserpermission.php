<?php

/*
Title:	Grant User Permission
Author:	Dylan Boltz
Date:	11/21/2013

The purpose of this code is to grant a user permission for a device.

*/

include 'commonfunctions.php';

// Get Query Parameters
$username = $_POST['username'];
$user_token = $_POST['user_token'];
$device_id = $_POST['device_id'];
$permission_level = $_POST['permission_level'];
$expiration_date = $_POST['expiration_date'];
$access_code = $_POST['access_code'];

// Check that parameters are not null
if(empty($username) || empty($user_token) || empty($device_id) || 
	empty($permission_level)){
	output_error('Insufficient parameters provided');
}

// Check if the optional arguments are null so that the NULL string
//	value may be substituted easily in the insert query
if(empty($expiration_date)){
	$expiration_date = "NULL";
}
if(empty($access_code)){
	$access_code = "NULL";
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

// Check that device id is valid
$result = mysqli_query($con, "SELECT OWNER, TYPE FROM DEVICE WHERE ID = '" . $device_id . "'");
$device_owner = NULL;
$device_type = NULL;
if($row = mysqli_fetch_array($result)){
	$device_owner = $row['OWNER'];
	$device_type = $row['TYPE'];
}else{
	output_error('Invalid device ID');
}

// Check if user owns device
$is_owner = false;
if(intval($user_id) == intval($device_owner)){
	$is_owner = true;
}

// Check if user granting permission is administrator
$result = mysqli_query($con, "SELECT PERMISSION, ACCESS_EXPIRATION_DATE FROM DEVICE_PERMISSION WHERE USER_ID = '" . $user_id . 
	"' AND DEVICE_ID = '" . $device_id . "'");
$user_permission = NULL;
$user_expiration_date = NULL;
if($row = mysqli_fetch_array($result)){
	$user_permission = $row['PERMISSION'];
	$user_expiration_date = $row['ACCESS_EXPIRATION_DATE'];
	if($user_permission != 'A' && !$is_owner){
		output_error('User does not have permission to grant access');
	}
}else{
	if(!$is_owner){
		output_error('User does not have permission to grant access');
	}
}

// If user is administrator, check that the permission has not expired
$current_date = NULL;
if($user_permission == 'A' && !empty($user_expiration_date) && $user_expiration_date != '0000-00-00'){
	$expiration_date_obj = new DateTime($user_expiration_date);
	$current_date = new DateTime("now");
	if($current_date > $expiration_date_obj){
		// And update permission in table to 'E' for expired
		mysqli_query($con, "UPDATE DEVICE_PERMISSION SET PERMISSION = 'E' WHERE USER_ID = '" . $user_id .
			"' AND DEVICE_ID = '" . $device_id . "')");
		output_error('User does not have permission to grant access');
	}
}

// Check that the user being assigned the permission exists
$result = mysqli_query($con, "SELECT ID FROM USER WHERE USERNAME = '" . $username . "'");
$granted_user_id = NULL;
if($row = mysqli_fetch_array($result)){
	$granted_user_id = $row['ID'];
}else{
	echo json_encode(array('error' => $username . ' does not exist'));
	exit;
}

// Check that permission level value is valid
if($permission_level != 'A' && $permission_level != 'R' && $permission_level != 'W'){
	output_error('Invalid permission level');
}

// Check that expiration date is valid
if($expiration_date != "NULL"){
	if(preg_match('#^(?P<year>\d{2}|\d{4})([- /.])(?P<month>\d{1,2})\2(?P<day>\d{1,2})$#', $expiration_date, $matches)
	           && checkdate($matches['month'],$matches['day'],$matches['year'])){
		$expiration_date_obj = new DateTime($expiration_date);
		if($current_date > $expiration_date_obj){
			output_error('Expiration date has already passed');
		}
	}else{
		output_error('Expiration date must be in MySQL format');
	}
}

// If a permission already exists for this user for this device, then delete it and create this new one
$isupdate = false;
$id = -1;
$result = mysqli_query($con, "SELECT ID FROM DEVICE_PERMISSION WHERE USER_ID = '" . $granted_user_id . 
	"' AND DEVICE_ID = '" . $device_id . "'");
if($row = mysqli_fetch_array($result)){
	$id = $row['ID'];
	$isupdate = true;
}

// Check that access code is valid and does not exist for another user of the device
if($device_type == 'L'){
	if(strlen($access_code) == 8){
		$result = mysqli_query($con, "SELECT ID FROM DEVICE_PERMISSION WHERE ACCESS_CODE = '" . $access_code . 
			"' AND DEVICE_ID = '" . $device_id . "' AND ID != " . $id);
		if(mysqli_fetch_array($result)){
			output_error('Identical access code already exists for another user of this device');
		}
	}else{
		output_error('Access code must be exactly 8 characters in length');
	}
}

// Execute this code if the record is being updated and not created new
if($isupdate){
	mysqli_query($con, "UPDATE DEVICE_PERMISSION SET PERMISSION = '" . $permission_level . "', ACCESS_EXPIRATION_DATE = '" .
	$expiration_date . "', ACCESS_GRANTED_BY = " . $user_id . ", ACCESS_CODE = '" . $access_code . "' WHERE ID = " . $id);
}else{

	// Execute this code if the permission is new
	$insert_query = "INSERT INTO DEVICE_PERMISSION (USER_ID, DEVICE_ID, PERMISSION, ACCESS_EXPIRATION_DATE, 
		ACCESS_GRANTED_BY, ACCESS_CODE) VALUES (" . $granted_user_id . ", " . $device_id . ", '" . $permission_level .
		"', '" . $expiration_date . "', " . $user_id . ", ";
	if($access_code == "NULL"){
		$insert_query = $insert_query . $access_code;
	}else{
		$insert_query = $insert_query . "'" . $access_code . "'";
	}
	$insert_query = $insert_query . ")";
	mysqli_query($con, $insert_query);
}

// Create a device access record for this permission with all access granted by default
// Check 0 condition, as we don't want to create a new record for a permission update
$permission_id = mysqli_insert_id($con);
if($permission_id != 0){
	$insert_query = "INSERT INTO DEVICE_ACCESS (DEVICE_PERMISSION_ID, ALL_ACCESS) VALUES (". $permission_id . ", 1)";
	mysqli_query($con, $insert_query);
}

echo json_encode(array('result' => 'success'));

?>