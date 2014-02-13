<?php

/*
Title:	Edit User Access Time
Author:	Dylan Boltz
Date:	2/10/2014

The purpose of this code is to edit an existing access restriction.

*/

include 'commonfunctions.php';

// Query parameters
$user_token = $_POST['user_token'];
$access_day_id = $_POST['access_day_id'];
$days = $_POST['days'];
$starttime = $_POST['starttime'];
$endtime = $_POST['endtime'];

// Check that parameters are not null
// This includes checking that one time has a value and one does not
if(is_null($user_token) || is_null($days) || is_null($access_day_id) || (is_null($starttime) && !is_null($endtime)) ||
	(is_null($endtime) && !is_null($starttime))){
	output_error('Insufficient parameters provided');
}

$hastimes = false;
if(!is_null($starttime) || !is_null($endtime)){
	$starttime = intval($starttime);
	$endtime = intval($endtime);
	$hastimes = true;
}

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	output_error('Could not connect to database');
}

// Check that the user token is valid
$user_id = validate_user_token($con, $user_token);

// Get device ID
$device_id = NULL;
$result = mysqli_query($con, "SELECT DEVICE_ID FROM DEVICE_PERMISSION WHERE ID = (SELECT DEVICE_PERMISSION_ID FROM DEVICE_ACCESS WHERE ID = 
	(SELECT DEVICE_ACCESS_ID FROM DEVICE_ACCESS_DAY WHERE  ID = " . $access_day_id . "))");
if($row = mysqli_fetch_array($result)){
	$device_id = $row['DEVICE_ID'];
}else{
	output_error('Invalid access day ID');
}

// Check that this user has permission to alter this access restriction
validate_administrator($con, $user_id, $device_id);

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
	if(is_null(get_day_num($day))){
		output_error('A character exists in days that is invalid');
	}
}

// Remove any duplicate days
$split_days = array_unique($split_days);
$days = implode($split_days);

// Update days
mysqli_query($con, "UPDATE DEVICE_ACCESS_DAY SET DAY = '" . $days . "' WHERE ID = " . $access_day_id);

// Change all access flag to 0 if times are included, or 1 if they aren't
// Also update times if they are included
if($hastimes){
	$result = mysqli_query($con, "SELECT ID FROM DEVICE_ACCESS_TIME WHERE ACCESS_DAY_ID = " . $access_day_id);
	if($row = mysqli_fetch_array($result)){
		mysqli_query($con, "UPDATE DEVICE_ACCESS_TIME SET START_TIME = " . $starttime . ", END_TIME = " . 
			$endtime . " WHERE ACCESS_DAY_ID = " . $access_day_id);
	}else{
		mysqli_query($con, "UPDATE DEVICE_ACCESS_DAY SET ALL_ACCESS = 0 WHERE ID = " . $access_day_id);
		mysqli_query($con, "INSERT INTO DEVICE_ACCESS_TIME(ACCESS_DAY_ID, START_TIME, END_TIME) VALUES(" .
			$access_day_id . ", " . $starttime . ", " . $endtime . ")");
	}
	
}else{
	mysqli_query($con, "UPDATE DEVICE_ACCESS_DAY SET ALL_ACCESS = 1 WHERE ID = " . $access_day_id);
	mysqli_query($con, "DELETE FROM DEVICE_ACCESS_TIME WHERE ACCESS_DAY_ID = " . $access_day_id);
}

return_success();

?>