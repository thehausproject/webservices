<!--
Title:	Login
Author:	Dylan Boltz
Date:	11/16/2013

The purpose of this code is to return a user their authorization token if the
correct login credentials were provided.

-->

<?php

// Get Query Parameters
$username = $_GET['username'];
$password = $_GET['password'];

// Check that parameters are not null
if(is_null($username) || is_null($password)){
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
	// Connection is ok. Check that username and password match
	$result = mysqli_query($con, "SELECT USERNAME, PASSWORD, AUTH_TOKEN FROM USER WHERE USERNAME = '" . $username . "'");
	if($row = mysqli_fetch_array($result)){
		// Return user token if login matches
		if($row['USERNAME'] == $username && $row['PASSWORD'] == $hashed_password){
			echo "{\"user_token\":" . $row['AUTH_TOKEN'] . "}";
		}else{
			echo "{\"error\":\"Invalid username/password.\"}";
		}
	}else{
		echo "{\"error\":\"Invalid username/password.\"}";
	}
}

mysqli_close($con);

?>