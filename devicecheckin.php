<?php

/*
Title:	Device Check In
Author:	Dylan Boltz
Date:	11/24/2013

The purpose of this code is to for a device to check in.

*/

include 'commonfunctions.php';

// Get Query Parameters
$passcode = $_GET['passcode'];

// Check that parameters are not null
if(empty($passcode)){
	output_error('Insufficient parameters provided');
}

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	output_error('Could not connect to database');
}

$result = mysqli_query($con, "SELECT ID, TYPE, STATE FROM DEVICE WHERE PASSCODE = '" . $passcode . "'");
$device_id = NULL;
$device_type = NULL;
$device_state = NULL;
if($row = mysqli_fetch_array($result)){
	$device_id = $row['ID'];
	$device_type = $row['TYPE'];
	$device_state = $row['STATE'];
}else{
	output_error('No device with this passcode');
}

echo json_encode(array('state' => $device_state));

if($device_type == 'L'){
	if($device_state == "UNLOCKED"){
		mysqli_query($con, "UPDATE DEVICE SET STATE = 'LOCKED' WHERE ID = '" . $device_id . "'");
	}
}

mysqli_query($con, "UPDATE DEVICE SET LAST_CHECKIN = NOW(), STATUS = 'U' WHERE ID = '" . $device_id . "'");

?>