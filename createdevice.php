<?php

/*
Title:	Create Device
Author:	Dylan Boltz
Date:	11/19/2013

The purpose of this code is to create a device.  This is called when a new device is plugged in.

*/

// Get Query Parameters
$passcode = $_POST['passcode'];
$type = $_POST['type'];

// Check that parameters are not null
if(is_null($passcode) || is_null($type)){
	echo json_encode(array('error' => 'Insufficient parameters provided'));
	exit;
}else if(strlen($passcode) != 16){
	echo json_encode(array('error' => 'Invalid passcode'));
	exit;
}else{
	// Check that the type is valid
	if($type != 'T' && $type != 'L' && $type != 'V'){
		echo json_encode(array('error' => 'Invalid type'));
	exit;
	}
}

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	echo json_encode(array('error' => 'Could not connect to database'));
}else{
	// Check that passcode does not already exist
	$result = mysqli_query($con, "SELECT ID FROM DEVICE WHERE PASSCODE = '" . $passcode . "'");
	if($row = mysqli_fetch_array($result)){
		echo json_encode(array('error' => 'A device with this passcode already exists'));
		exit;
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