<!--
Title:	Change Password
Author:	Dylan Boltz
Date:	11/16/2013

The purpose of this code is change a user's password to the new one provided.

-->

<?php

// Get Query Parameters
$user_token = $_GET['user_token'];
$old_password = $_GET['old_password'];
$new_password = $_GET['new_password'];

// Check that parameters are not null
if(is_null($user_token) || is_null($old_password) || is_null($new_password)){
	echo "{\"error\":\"Insufficient parameters provided.\"}";
	exit;
}

$hashed_old_password = hash('sha256', $old_password);
$hashed_new_password = hash('sha256', $new_password);

// Create connection
$con=mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	echo "{\"error\":\"Could not connect to database.\"";
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
			echo "{\"user_token\":" . $new_user_token . "}";
		}else{
			echo "{\"error\":\"Existing password supplied is incorrect.\"}";
		}
	}else{
		echo "{\"error\":\"User does not exist.\"}";
	}
}

mysqli_close($con);

?>