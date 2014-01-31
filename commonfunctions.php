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

?>