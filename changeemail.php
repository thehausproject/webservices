<!--
Title:	Change Email
Author:	Dylan Boltz
Date:	11/16/2013

The purpose of this code is change a user's email to the new one provided.

-->

<?php

// Get Query Parameters
$user_token = $_GET['user_token'];
$new_email = $_GET['new_email'];
$password = $_GET['password'];

// Check that parameters are not null
if(is_null($user_token) || is_null($new_email) || is_null($password)){
	echo "{\"error\":\"Insufficient parameters provided.\"}";
	exit;
}

$hashed_password = hash('sha256', $password);

// Create connection
$con=mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	echo "{\"error\":\"Could not connect to database.\"}";
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
			echo "{\"user_token\":" . $new_user_token . "}";
		}else{
			echo "{\"error\":\"Incorrect Password.\"}";
		}
	}else{
		echo "{\"error\":\"User does not exist.\"}";
	}
}

mysqli_close($con);

?>