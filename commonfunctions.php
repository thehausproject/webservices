<?php

/*
Title:	Common Functions
Author:	Dylan Boltz
Date:	1/31/2014

The purpose of this code is to provide functions that are called often.  As more functions are added
it may be necessary to break this file into multiple files according to the nature of different groups
of functions.

*/

function output_error($errormessage){
	http_response_code(400);
	echo json_encode(array('error' => $errormessage));
	exit;
}

function return_success(){
	echo json_encode(array('result' => 'success'));
}

function get_day_num($day){
	$day_nums = array('S' => 0, 'M' => 1, 'T' => 2, 'W' => 3, 
		'R' => 4, 'F' => 5, 'A' => 6);
	return $day_nums[$day];
}

// Returns true if no access restrictions apply at the time of the request
function can_access_now($con, $permission_id){
	// Check to see if the permission is all access (no restrictions applied)
	$result = mysqli_query($con, "SELECT ID, ALL_ACCESS FROM DEVICE_ACCESS WHERE DEVICE_PERMISSION_ID = " . $permission_id);
	$row = mysqli_fetch_array($result);
	if($row['ALL_ACCESS'] == 1){
		return true;
	}
	$access_id = $row['ID'];

	// Retrieve the day restrictions
	$result = mysqli_query($con, "SELECT ID, DAY, ALL_ACCESS FROM DEVICE_ACCESS_DAY WHERE DEVICE_ACCESS_ID = " . $access_id);
	while($row = mysqli_fetch_array($result)){
		$days = $row['DAY'];

		// Check if today is in the list of valid days
		$today_works = false;
		$days = str_split($days);
		foreach($days as $day){
			if(get_day_num($day) == date("w", time())){
				$today_works = true;
			}
		}
		if($today_works && $row['ALL_ACCESS'] == 1){
			return true;
		}
		$access_day_id = $row['ID'];

		if($row['ALL_ACCESS'] == 0){
			// If we made it this far, then the day is correct, but access still depends on time
			$result = mysqli_query($con, "SELECT START_TIME, END_TIME, TIME_TO_SEC(TIME(NOW())) AS TIME FROM DEVICE_ACCESS_TIME 
				WHERE ACCESS_DAY_ID = " . $access_day_id);
			while($row2 = mysqli_fetch_array($result)){
				$starttime = $row2['START_TIME'];
				$endtime = $row2['END_TIME'];
				$timenow = $row2['TIME'] + 1 + 7200;
				if($timenow >= $starttime && $timenow <= $endtime){
					return true;
				}
			}
		}
	}
	return false;
}

function delete_permission_restrictions($con, $permission_id){
	// Delete any access records that existed for the permission in a cascading fashion
	$result = mysqli_query($con, "SELECT ID, ALL_ACCESS FROM DEVICE_ACCESS WHERE DEVICE_PERMISSION_ID = " . $permission_id);
	if($row = mysqli_fetch_array($result)){
		if($row['ALL_ACCESS'] == '0'){
			$result2 = mysqli_query($con, "SELECT ID, ALL_ACCESS FROM DEVICE_ACCESS_DAY WHERE DEVICE_ACCESS_ID = " . $row['ID']);
			while($row2 = mysqli_fetch_array($result2)){
				if($row2['ALL_ACCESS'] == '0'){
					mysqli_query($con, "DELETE FROM DEVICE_ACCESS_TIME WHERE ACCESS_DAY_ID = " . $row2['ID']);
				}
				mysqli_query($con, "DELETE FROM DEVICE_ACCESS_DAY WHERE ID = " . $row2['ID']);
			}
		}
		mysqli_query($con, "DELETE FROM DEVICE_ACCESS WHERE ID = " . $row['ID']);
	}
}

function validate_administrator($con, $user_id, $device_id){
	$user_expiration_date = NULL;
	$result = mysqli_query($con, "SELECT PERMISSION, ACCESS_EXPIRATION_DATE FROM DEVICE_PERMISSION WHERE USER_ID = '" . $user_id . 
		"' AND DEVICE_ID = '" . $device_id . "'");
	$user_permission = NULL;
	$user_expiration_date = NULL;
	if($row = mysqli_fetch_array($result)){
		$user_permission = $row['PERMISSION'];
		$user_expiration_date = $row['ACCESS_EXPIRATION_DATE'];
		if($user_permission != 'A'){
			output_error('User does not have permission to grant access');
		}
	}else{
		output_error('User does not have permission to grant access');
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
			output_error('User does not have permission to grant access');
		}
	}
}

?>