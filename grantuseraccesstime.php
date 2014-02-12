<?php

/*
Title:	Grant User Access Time
Author:	Dylan Boltz
Date:	2/10/2014

The purpose of this code is to grant specific access time to a user.  The access time permission maps to
an actual device permission.

*/

include 'commonfunctions.php';

// Query parameters
$user_token = $_POST['user_token'];
$permission_id = $_POST['permission_id'];
$days = $_POST['days'];
$starttime = $_POST['starttime'];
$endtime = $_POST['endtime'];

// Check that parameters are not null
// This includes checking that one time has a value and one does not
if(is_null($user_token) || is_null($days) || is_null($permission_id) || (is_null($starttime) && !is_null($endtime)) ||
	(is_null($endtime) && !is_null($starttime))){
	output_error('Insufficient parameters provided');
}

$hastimes = false;
if(!is_null($starttime) || !is_null($endtime)){
	$starttime = intval($starttime);
	$endtime = intval($endtime);
	$hastimes = true;
}

// Error check the start and end times
if($hastimes){
	if($starttime < 1 || $starttime > 86400 || $endtime < 1 || $endtime > 86400){
		output_error('A start or end time provided is out of range (1 - 86400)');
	}else if($starttime > $endtime){
		output_error('The start time cannot be after the end time');
	}
}

// Check that all days are valid
$split_days = str_split($days);
foreach($split_days as $day){
	if(is_null(get_day_name($day))){
		output_error('A character exists in days that is invalid');
	}
}

// Remove any duplicate days
$split_days = array_unique($split_days);
$days = implode($split_days);

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	output_error('Could not connect to database');
}

// Check that token is valid
$result = mysqli_query($con, "SELECT ID FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
$user_id = NULL;
if($row = mysqli_fetch_array($result)){
	$user_id = $row['ID'];
}else{
	output_error('Invalid user token');
}

// Check that the permission ID is valid
$access_id = NULL;
$result = mysqli_query($con, "SELECT ID FROM DEVICE_ACCESS WHERE DEVICE_PERMISSION_ID = '" . $permission_id . "'");
if($row = mysqli_fetch_array($result)){
	$access_id = $row['ID'];
}else{
	output_error('Invalid permission ID');
}

// Get the device ID
$result = mysqli_query($con, "SELECT DEVICE_ID FROM DEVICE_PERMISSION WHERE ID = " . $permission_id);
$row = mysqli_fetch_array($result);
$device_id = $row['DEVICE_ID'];

// Check that the user assigning access restriction is permitted to do so
// Check if user granting permission is administrator
$result = mysqli_query($con, "SELECT PERMISSION, ACCESS_EXPIRATION_DATE FROM DEVICE_PERMISSION WHERE USER_ID = '" . $user_id . 
	"' AND DEVICE_ID = '" . $device_id . "'");
$user_permission = NULL;
$user_expiration_date = NULL;
if($row = mysqli_fetch_array($result)){
	$user_permission = $row['PERMISSION'];
	$user_expiration_date = $row['ACCESS_EXPIRATION_DATE'];
	if($user_permission != 'A'){
		output_error('User does not have permission to restrict access');
	}
}else{
	output_error('User does not have permission to restrict access');
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
		output_error('User does not have permission to restrict access');
	}
}

// Change the all access flag in device access table to false
mysqli_query($con, "UPDATE DEVICE_ACCESS SET ALL_ACCESS = 0 WHERE ID = " . $access_id);

// Insert new row into device days
$allaccessday = 1;
if($hastimes){
	$allaccessday = 0;
}
mysqli_query($con, "INSERT INTO DEVICE_ACCESS_DAY (DEVICE_ACCESS_ID, DAY, ALL_ACCESS) VALUES (" .
	$access_id . ", '" . $days . "', " . $allaccessday . ")");

// Insert new row into device time if necessary
if($hastimes){
	$access_day_id = mysqli_insert_id($con);
	mysqli_query($con, "INSERT INTO DEVICE_ACCESS_TIME(ACCESS_DAY_ID, START_TIME, END_TIME) VALUES(" .
		$access_day_id . ", " . $starttime . ", " . $endtime . ")");
}

echo json_encode(array('result' => 'success'));

?>