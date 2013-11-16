<!--
Title:	Create User
Author:	Dylan Boltz
Date:	11/16/2013

The purpose of this code is to create a new user for HAUS.  Basic information validation should be done before
calling this function.  Not all validations are performed on this end.

Validations performed:
	-- Query parameters are not null
	-- Identical username or email do not already exist for another user

-->

<?php

// Get Query Parameters
$username = $_GET['username'];
$password = $_GET['password'];
$email = $_GET['email'];


// Check that parameters are not null
if(is_null($username) || is_null($password) || is_null($email)){
	echo "{\"error\":\"Insufficient parameters provided.\"}";
	exit;
}

$hashed_password = hash('sha256', $password);

// Create connection
$con=mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	echo "{\"error\":\"Could not connect to database.\"";
}else{
	// Connection is ok. Check that there are no existing users with the
	// same username or email
	$result = mysqli_query($con, "SELECT * FROM USER WHERE USERNAME = '" . $username . "' OR EMAIL = '" . $email . "'");
	if(mysqli_fetch_array($result)){
		echo "{\"error\":\"A user with this username or email already exists.\"}";
	}else{
		// No existing users. Create the new user in the database.
		$auth_key = hash('sha256', $username . $password . $email);
		$insert_query = "INSERT INTO USER (USERNAME, PASSWORD, EMAIL, AUTH_TOKEN) VALUES ('" . $username . "', '" . $hashed_password . "', '" . $email . "', '" . $auth_key . "')";
		$result = mysqli_query($con, $insert_query);

		// Returns the user authorization token.
		echo "{\"auth_key\":" . $auth_key . "}";
	}
}

mysqli_close($con);
?>