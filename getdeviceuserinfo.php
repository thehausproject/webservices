<?php

/*
Title:	Get Device User Info
Author:	Dylan Boltz
Date:	11/24/2013

The purpose of this code is to return information about all
the user permissions associated with the device.

*/

include 'commonfunctions.php';

// Get Query Parameters
$device_id = $_GET['device_id'];
$user_token = $_GET['user_token'];

// Check that parameters are not null
if(empty($device_id) || empty($user_token)){
	output_error('Insufficient parameters provided');
}

// Create connection
$con = mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	output_error('Could not connect to database');
}

// Check that the user requesting this information is an administrator
$result = mysqli_query($con, "SELECT ID FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
$requesting_user_id = NULL;
if($row = mysqli_fetch_array($result)){
	$requesting_user_id = $row['ID'];
}else{
	output_error('Invalid user token');
}

$result = mysqli_query($con, "SELECT PERMISSION FROM DEVICE_PERMISSION WHERE USER_ID = " . $requesting_user_id
	 . " AND DEVICE_ID = " . $device_id);
if($row = mysqli_fetch_array($result)){
	$requesting_user_permission = $row['PERMISSION'];
	if($requesting_user_permission != 'A'){
		output_error('User with this token does not have permission to view device user permissions');
	}
}else{
	output_error('User with this token does not have permission to view device user permissions');
}

// Get and return all the associated device permissions
$result = mysqli_query($con, "SELECT ID, USER_ID, PERMISSION, DATE_AUTHORIZED, ACCESS_EXPIRATION_DATE, " . 
	"ACCESS_GRANTED_BY, ACCESS_CODE FROM DEVICE_PERMISSION WHERE DEVICE_ID = " . $device_id);
$json_array = array();
$count = 0;
while($row = mysqli_fetch_array($result)){
	$permission_id = $row['ID'];
	$user_id = $row['USER_ID'];
	$permission = $row['PERMISSION'];
	$date_authorized = $row['DATE_AUTHORIZED'];
	$access_expiration_date = $row['ACCESS_EXPIRATION_DATE'];
	$access_granted_by = $row['ACCESS_GRANTED_BY'];
	$access_code = $row['ACCESS_CODE'];

	// Get the username from the user_id
	$user_info = mysqli_query($con, "SELECT USERNAME FROM USER WHERE ID = " . $user_id);
	$username = NULL;
	if($row_next = mysqli_fetch_array($user_info)){
		$username = $row_next['USERNAME'];
	}else{
		$username = "User does not exist";
	}

	// Get the username of the user who created the permission using access_granted_by
	$user_info = mysqli_query($con, "SELECT USERNAME FROM USER WHERE ID = " . $user_id);
	$granted_by_username = NULL;
	if($row_next = mysqli_fetch_array($user_info)){
		$granted_by_username = $row_next['USERNAME'];
	}else{
		$granted_by_username = "User does not exist";
	}

	// Check that permission is not expired
	if(is_expired($con, $access_expiration_date)){
		$permission = 'E';
		mysqli_query($con, "UPDATE DEVICE_PERMISSION SET PERMISSION = 'E' WHERE ID = " . $permission_id);
	}

	array_push($json_array, array('id' => $permission_id, 'user' => $username, 'permission' => $permission,
		'date_authorized' => $date_authorized, 'access_expiration_date' => $access_expiration_date,
		'access_granted_by' => $granted_by_username));
	$count++;
}

echo json_encode(array('permissions' => $json_array));

?>