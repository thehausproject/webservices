<!--
Title:	Grant User Permission
Author:	Dylan Boltz
Date:	11/21/2013

The purpose of this code is to grant a user permission for a device.

-->

<?php

// Get Query Parameters
$username = $_GET['username'];
$user_token = $_GET['user_token'];
$device_id = $_GET['device_id'];
$permission_level = $_GET['permission_level'];
$expiration_date = $_GET['expiration_date'];
$access_code = $_GET['access_code'];

// Check that parameters are not null
if(is_null($username) || is_null($user_token) || is_null($device_id) || 
	is_null($permission_level)){
	echo "{\"error\":\"Insufficient parameters provided.\"}";
	exit;
}

// Check if the optional arguments are null so that the NULL string
//	value may be substituted easily in the insert query
if(is_null($expiration_date)){
	$expiration_date = "NULL";
}
if(is_null($access_code)){
	$access_code = "NULL";
}

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	echo "{\"error\":\"Could not connect to database.\"}";
	exit;
}

// More preliminary error checking to follow

// Check that token is valid
$result = mysqli_query($con, "SELECT ID FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
$user_id = NULL;
if($row = mysqli_fetch_array($result)){
	$user_id = $row['ID'];
}else{
	echo "{\"error\":\"Invalid user token.\"}";
	exit;
}

// Check that device id is valid
$result = mysqli_query($con, "SELECT OWNER, TYPE FROM DEVICE WHERE ID = '" . $device_id . "'");
$device_owner = NULL;
$device_type = NULL;
if($row = mysqli_fetch_array($result)){
	$device_owner = $row['OWNER'];
	$device_type = $row['TYPE'];
}else{
	echo "{\"error\":\"Invalid device ID.\"}";
	exit;
}

// Check if user granting permission is administrator
$result = mysqli_query($con, "SELECT PERMISSION, ACCESS_EXPIRATION_DATE FROM DEVICE_PERMISSION WHERE USER_ID = '" . $user_id . 
	"' AND DEVICE_ID = '" . $device_id . "'");
$user_permission = NULL;
$user_expiration_date = NULL;
if($row = mysqli_fetch_array($result)){
	$user_permission = $row['PERMISSION'];
	$user_expiration_date = $row['ACCESS_EXPIRATION_DATE'];
	if($user_permission != 'A'){
		echo "{\"error\":\"User does not have permission to grant access.\"}";
		exit;
	}
}

// If user is administrator, check that the permission has not expired
$current_date = NULL;
if($user_permission == 'A' && !is_null($user_expiration_date)){
	$expiration_date_obj = new DateTime($user_expiration_date);
	$current_date = new DateTime("now");
	if($current_date > $expiration_date_obj){
		echo "{\"error\":\"User does not have permission to grant access.\"}";
		// And update permission in table to 'E' for expired
		mysqli_query($con, "UPDATE DEVICE_PERMISSION SET PERMISSION = 'E' WHERE USER_ID = '" . $user_id .
			"' AND DEVICE_ID = '" . $device_id . "')");
		exit;
	}
}

// If user granting access is not administrator, check that the user owns the device
if(is_null($user_permission)){
	if(intval($user_id) != intval($device_owner)){
		echo "{\"error\":\"User does not have permission to grant access.\"}";
		exit;
	}
}

// Check that the user being assigned the permission exists
$result = mysqli_query($con, "SELECT ID FROM USER WHERE USERNAME = '" . $username . "'");
$granted_user_id = NULL;
if($row = mysqli_fetch_array($result)){
	$granted_user_id = $row['ID'];
}else{
	echo "{\"error\":\"User " . $username . " does not exist.\"}";
	exit;
}

// Check that permission level value is valid
if($permission_level != 'A' && $permission_level != 'R' && $permission_level != 'W'){
	echo "{\"error\":\"Invalid permision level.\"}";
	exit;
}

// Check that expiration date is valid
if($expiration_date != "NULL"){
	if(preg_match('#^(?P<year>\d{2}|\d{4})([- /.])(?P<month>\d{1,2})\2(?P<day>\d{1,2})$#', $expiration_date, $matches)
	           && checkdate($matches['month'],$matches['day'],$matches['year'])){
		$expiration_date_obj = new DateTime($expiration_date);
		if($expiration_date_obj > $current_date){
			echo "{\"error\":\"Expiration date is invalid. Date has already passed.\"}";
			exit;
		}
	}else{
		echo "{\"error\":\"Expiration date format is invalid. Use MySQL format.\"}";
		exit;
	}
}

// Check that access code is valid and does not exist for another user of the device
if($device_type == 'L'){
	if(strlen($access_code) == 8){
		$result = mysqli_query($con, "SELECT ID FROM DEVICE_PERMISSION WHERE ACCESS_CODE = '" . $access_code . 
			"' AND DEVICE_ID = '" . $device_id . "'");
		if(mysqli_fetch_array($result)){
			echo "{\"error\":\"Identical access code already exists for another user of this device.\"}";
			exit;
		}
	}else{
		echo "{\"error\":\"Access code must be exactly 8 characters in length.\"}";
		exit;
	}
}

// If a permission already exists for this user for this device, then delete it and create this new one
$result = mysqli_query($con, "SELECT ID FROM DEVICE_PERMISSION WHERE USER_ID = '" . $granted_user_id . 
	"' AND DEVICE_ID = '" . $device_id . "'");
if($row = mysqli_fetch_array($result)){
	$id = $row['ID'];
	mysqli_query($con, "DELETE FROM DEVICE_PERMISSION WHERE ID = " . $id);
}

// Finally.  If we made it this far it means that the device permission is ready to be written.
$insert_query = "INSERT INTO DEVICE_PERMISSION (USER_ID, DEVICE_ID, PERMISSION, ACCESS_EXPIRATION_DATE, 
	ACCESS_GRANTED_BY, ACCESS_CODE) VALUES (" . $user_id . ", " . $device_id . ", '" . $permission_level .
	"', '" . $expiration_date . "', " . $user_id . ", ";
if($access_code == "NULL"){
	$insert_query = $insert_query . $access_code;
}else{
	$insert_query = $insert_query . "'" . $access_code . "'";
}
$insert_query = $insert_query . ")";

mysqli_query($con, $insert_query);
echo "{\"result\":\"success\"}";

?>