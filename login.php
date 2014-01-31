<?php

/*
Title:	Login
Author:	Dylan Boltz
Date:	11/16/2013

The purpose of this code is to return a user their authorization token if the
correct login credentials were provided.

*/

// Get Query Parameters
$username = $_POST['username'];
$password = $_POST['password'];

// Check that parameters are not null
if(is_null($username) || is_null($password)){
	echo json_encode(array('error' => 'Insufficient parameters provided'));
	exit;
}

$hashed_password = hash('sha256', $password);

// Create connection
$con=mysqli_connect("localhost","dylanbo1_haus","burningdownthehaus","dylanbo1_haus");

// Check connection
if (mysqli_connect_errno($con)){
	echo json_encode(array('error' => 'Could not connect to database'));
}else{
	// Connection is ok. Check that username and password match
	$result = mysqli_query($con, "SELECT USERNAME, PASSWORD, AUTH_TOKEN FROM USER WHERE USERNAME = '" . $username . "'");
	if($row = mysqli_fetch_array($result)){
		// Return user token if login matches
		if($row['USERNAME'] == $username && $row['PASSWORD'] == $hashed_password){
			echo json_encode(array('user_token' => $row['AUTH_TOKEN']));
		}else{
			echo json_encode(array('error' => 'Invalid username/password'));
		}
	}else{
		echo json_encode(array('error' => 'Invalid username/password'));
	}
}

mysqli_close($con);

?>