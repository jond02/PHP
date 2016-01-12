<?php

//create a database connection
define('DB_SERVER', '');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_NAME', '');

$connection = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

//test if connection occurred
if ($connection->connect_errno) {
	die ('Database connection failed: ' . $connection->connect_error);	
}

function databaseErrorMsg($DBErrorMsg) {
	global $connection;	
	if ($DBErrorMsg) {
		$error =  "<p>" . $connection->connect_error . "</p>";
	} else {
		$error =  "<p>Error</p>";
	}
	return $error;
}

$DBErrorMsg = false;