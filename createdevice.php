<?php

/*
Title:	Create Device
Author:	Dylan Boltz
Date:	11/19/2013

The purpose of this code is to create a device.  This is called when a new device is plugged in.

*/

include 'commonfunctions.php';

// Get Query Parameters
$passcode = $_POST['passcode'];
$type = $_POST['type'];

// Check that parameters are not null
if(is_null($passcode) || is_null($type)){
	output_error('Insufficient parameters provided');
}else if(strlen($passcode) != 16){
	output_error('Invalid passcode');
}else{
	// Check that the type is valid
	if($type != 'T' && $type != 'L' && $type != 'V'){
		output_error('Invalid type');
	}
}

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	output_error('Could not connect to database');
}else{
	// Check that passcode does not already exist
	$result = mysqli_query($con, "SELECT ID FROM DEVICE WHERE PASSCODE = '" . $passcode . "'");
	if($row = mysqli_fetch_array($result)){
		output_error('A device with this passcode already exists');
	}

	if($type == 'L'){
		$insert_query = "INSERT INTO DEVICE (PASSCODE, TYPE, STATE) VALUES ('" . $passcode . "', '" . $type . "', 'LOCKED')";
	}else{
		//  Implement in later cycles for temperatature
		//	and video devices
	}
	
	mysqli_query($con, $insert_query);
	echo json_encode(array('result' => 'success'));
}

mysqli_close($con);

?>