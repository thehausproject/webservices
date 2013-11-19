<!--
Title:	Create Device
Author:	Dylan Boltz
Date:	11/19/2013

The purpose of this code is to create a device.  This is called when a new device is plugged in.

-->

<?php

// Get Query Parameters
$passcode = $_GET['passcode'];
$type = $_GET['type'];

// Check that parameters are not null
if(is_null($passcode) || is_null($type)){
	echo "{\"error\":\"Insufficient parameters provided.\"}";
	exit;
}else if(strlen($passcode) != 16){
	echo "{\"error\":\"Invalid passcode.\"}";
	exit;
}else{
	// Check that the type is valid
	if($type != 'T' && $type != 'L' && $type != 'V'){
		echo "{\"error\":\"Invalid type.\"}";
	exit;
	}
}

// Create connection
$con=mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	echo "{\"error\":\"Could not connect to database.\"";
}else{
	// Connection is ok
	if($type == 'L'){
		$insert_query = "INSERT INTO DEVICE (PASSCODE, TYPE) VALUES ('" . $passcode . "', '" . $type . "')";
	}else{
		//  Implement in later cycles for temperatature
		//	and video devices
	}
	
	mysqli_query($con, $insert_query);
	echo "{\"result\":\"success\"}";
}

mysqli_close($con);

?>