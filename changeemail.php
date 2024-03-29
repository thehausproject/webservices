<?php

/*
Title:	Change Email
Author:	Dylan Boltz
Date:	11/16/2013

The purpose of this code is change a user's email to the new one provided.

*/

include 'commonfunctions.php';

// Get Query Parameters
$user_token = $_POST['user_token'];
$new_email = $_POST['new_email'];
$password = $_POST['password'];

// Check that parameters are not null
if(empty($user_token) || empty($new_email) || empty($password)){
	output_error('Insufficient parameters provided');
}

$hashed_password = hash('sha256', $password);

// Create connection
$con=mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	output_error('Could not connect to database');
}else{
	// Connection is ok. Check password matches
	$result = mysqli_query($con, "SELECT USERNAME, PASSWORD, EMAIL FROM USER WHERE AUTH_TOKEN = '" . $user_token . "'");
	if($row = mysqli_fetch_array($result)){
		// Update email and token and return the new token
		if($row['PASSWORD'] == $hashed_password){
			$new_user_token = hash('sha256', $row['USERNAME'] . $password . $new_email);
			$update_query = "UPDATE USER SET EMAIL = '" . $new_email . "', AUTH_TOKEN = '" . $new_user_token .
				"' WHERE USERNAME = '" . $row['USERNAME'] . "'";
			mysqli_query($con, $update_query);
			echo json_encode(array('user_token' => $new_user_token));
		}else{
			output_error('Incorrect password');
		}
	}else{
		output_error('Invalid user token');
	}
}

mysqli_close($con);

?>