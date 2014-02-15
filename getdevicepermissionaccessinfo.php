<?php

/*
Title:	Get Device Permission Access Info
Author:	Dylan Boltz
Date:	2/12/2014

The purpose of this code is to return all access restrictions on a given permission.

*/

include 'commonfunctions.php';

// Get Query Parameters
$user_token = $_GET['user_token'];
$permission_id = $_GET['permission_id'];

// Check that parameters are not null
if(empty($user_token) || empty($permission_id)){
	output_error('Insufficient parameters provided');
}

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	output_error('Could not connect to database');
}

// Validate the user token and permission ID
// Also validates that user has permission to see the restrictions
$user_id = validate_user_token($con, $user_token);
$device_all_access = NULL;
$device_access_id = NULL;
$result = mysqli_query($con, "SELECT ID, ALL_ACCESS FROM DEVICE_ACCESS WHERE DEVICE_PERMISSION_ID = " . $permission_id);
if($row = mysqli_fetch_array($result)){
	$device_all_access = $row['ALL_ACCESS'];
	$device_access_id = $row['ID'];
}else{
	output_error('Permission ID is invalid');
}
$result = mysqli_query($con, "SELECT ID FROM DEVICE_PERMISSION WHERE ID = " . $permission_id .
	" AND USER_ID = " . $user_id);
if(!($row = mysqli_fetch_array($result))){
	$result2 = mysqli_query($con, "SELECT DEVICE_ID FROM DEVICE_PERMISSION WHERE ID = " . $permission_id);
	$row2 = mysqli_fetch_array($result2);
	$device_id = $row2['DEVICE_ID'];
	$result2 = mysqli_query($con, "SELECT ID FROM DEVICE_PERMISSION WHERE DEVICE_ID = " . $device_id . 
		" AND USER_ID = " . $user_id . " AND PERMISSION = 'A'");
	if(!($row3 = mysqli_fetch_array($result2))){
		output_error('This permission does not pertain to this user');
	}
}

// Gather access restriction information for the permission ID provided
$access_data = array();
if($device_all_access == 0){
	$result = mysqli_query($con, "SELECT ID, DAY, ALL_ACCESS FROM DEVICE_ACCESS_DAY WHERE DEVICE_ACCESS_ID = " . $device_access_id);
	while($row = mysqli_fetch_array($result)){
		$access_day_id = $row['ID'];
		$day = $row['DAY'];
		$all_access_day = $row['ALL_ACCESS'];
		$temp_arr = array("access_day_id" => $access_day_id, "days" => $day, "all_access" => $all_access_day);

		// Now add the time information to the array, if applicable
		if($all_access_day == 0){
			$result2 = mysqli_query($con, "SELECT START_TIME, END_TIME FROM DEVICE_ACCESS_TIME WHERE
				ACCESS_DAY_ID = " . $access_day_id);
			$row2 = mysqli_fetch_array($result2);
			$temp_arr['starttime'] = $row2['START_TIME'];
			$temp_arr['endtime'] = $row2['END_TIME'];
		}
		array_push($access_data, $temp_arr);
	}
}else{
	echo json_encode(array('all_access' => 1));
	exit;
}

echo json_encode(array('all_access' => 0, "access_restrictions" => $access_data));