<?php

// Get Query Parameters
$username = $_GET['username'];
$password = $_GET['password'];
$email = $_GET['email'];

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
	$result = mysqli_query($con, "SELECT * FROM USER WHERE USERNAME = '" . $username . "' OR EMAIL = '" . $email . "'");
	if(mysqli_fetch_array($result)){
		echo "{\"error\":\"A user with this username or email already exists.\"}";
	}else{
		$auth_key = hash('sha256', $username . $password . $email);
		$insert_query = "INSERT INTO USER (USERNAME, PASSWORD, EMAIL, AUTH_TOKEN) VALUES ('" . $username . "', '" . $hashed_password . "', '" . $email . "', '" . $auth_key . "')";
		$result = mysqli_query($con, $insert_query);
		echo "{\"auth_key\":" . $auth_key . "}";
	}
}

mysqli_close($con);
?>