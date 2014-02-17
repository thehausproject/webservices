<?php

/*
Title:	Get Device Info
Author:	Dylan Boltz
Date:	11/24/2013

The purpose of this code is to return device information for the devices
that a user has permission to see.

*/

include 'commonfunctions.php';

// Get Query Parameters
$user_token = $_GET['user_token'];

// Check that parameters are not null
if(empty($user_token)){
	output_error('Insufficient parameters provided');
}

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	output_error('Could not connect to database');
}

// Check that token is valid and get the user ID
$result = mysqli_query($con, "SELECT ID FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
$user_id = NULL;
if($row = mysqli_fetch_array($result)){
	$user_id = $row['ID'];
}else{
	output_error('Invalid user token');
}

// Get the devices that the user has permissions for and return this device information
$result = mysqli_query($con, "SELECT ID, DEVICE_ID, PERMISSION, ACCESS_EXPIRATION_DATE, ACCESS_CODE FROM DEVICE_PERMISSION WHERE USER_ID = " . $user_id);
$json_array = array();
$count = 0;
while($row = mysqli_fetch_array($result)){
	$permission_id = $row['ID'];
	$device_id = $row['DEVICE_ID'];
	$permission = $row['PERMISSION'];
	$access_code = $row['ACCESS_CODE'];
	$device_info = mysqli_query($con, "SELECT OWNER, TYPE, STATUS, NICKNAME, STATE, LAST_CHECKIN FROM DEVICE WHERE ID = " . $device_id);
	$device_row = mysqli_fetch_array($device_info);
	$owner = $device_row['OWNER'];
	$type = $device_row['TYPE'];
	$status = $device_row['STATUS'];
	$nickname = $device_row['NICKNAME'];
	$state = $device_row['STATE'];
	$last_checkin = strtotime($device_row['LAST_CHECKIN']);

	// Use the owner value to get the username of the device owner
	$user_info = mysqli_query($con, "SELECT USERNAME FROM USER WHERE ID = " . $owner);
	$user_row = mysqli_fetch_array($user_info);
	$username = $user_row['USERNAME'];

	// Check that the device has checked in within the past two seconds and mark it as offline if it hasn't.
	$current_date = new DateTime("now");
	$interval = intval($current_date) - $last_checkin;
	$seconds_diff = round((((($interval % 604800) % 86400) % 3600) % 60), 2);
	if($status != 'D' && abs($seconds_diff) > 6){
		mysqli_query($con, "UPDATE DEVICE SET STATUS = 'D' WHERE ID = '" . $device_id . "'");
		$status = 'D';
	}

	if(is_expired($con, $row['ACCESS_EXPIRATION_DATE'])){
		$permission = 'E';
		mysqli_query($con, "UPDATE DEVICE_PERMISSION SET PERMISSION = 'E' WHERE ID = " . $permission_id);
	}

	// Send back the device information in JSON format
	$device_properties = array('id' => $device_id, 'owner' => $username, 'type' => $type, 'status' => $status,
		'nickname' => $nickname, 'state' => $state, 'permission' => $permission, 'permission_id' => $permission_id);
	if($type == 'L'){
		$device_properties['access_code'] = $access_code;
	}
	array_push($json_array, $device_properties);
	$count++;

}

echo json_encode(array('devices' => $json_array));

?>