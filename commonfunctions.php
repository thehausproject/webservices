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

?>