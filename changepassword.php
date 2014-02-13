<?php

/*
Title:	Change Password
Author:	Dylan Boltz
Date:	11/16/2013

The purpose of this code is change a user's password to the new one provided.

*/

include 'commonfunctions.php';

// Get Query Parameters
$user_token = $_POST['user_token'];
$old_password = $_POST['old_password'];
$new_password = $_POST['new_password'];

// Check that parameters are not null
if(empty($user_token) || empty($old_password) || empty($new_password)){
	output_error('Insufficient parameters provided');
}

$hashed_old_password = hash('sha256', $old_password);
$hashed_new_password = hash('sha256', $new_password);

// Create connection
$con=mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	output_error('Could not connect to database');
}else{
	// Connection is ok. Check old password matches
	$result = mysqli_query($con, "SELECT USERNAME, PASSWORD, EMAIL FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
	if($row = mysqli_fetch_array($result)){
		// Update database and return new user token
		if($row['PASSWORD'] == $hashed_old_password){
			$new_user_token = hash('sha256', $row['USERNAME'] . $new_password . $row['EMAIL']);
			$update_query = "UPDATE USER SET PASSWORD = '" . $hashed_new_password . "', AUTH_TOKEN = '" . $new_user_token .
				"' WHERE USERNAME = '" . $row['USERNAME'] . "'";
			mysqli_query($con, $update_query);
			echo json_encode(array('user_token' => $new_user_token));
		}else{
			output_error('Existing password supplied is incorrect');
		}
	}else{
		output_error('User token is incorrect');
	}
}

mysqli_close($con);

?>