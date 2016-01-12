<?php
require_once '../includes/db_connection.php';

function welcomeSubmit() {
	global $connection;
	$message = 'false';
	$sec_i = 1;
	$par_i = 1;
	if (isset($_POST['WelcSubmit'])) {

		//delete from table
		$query = "DELETE FROM...";
		if (!$result = $connection->query($query)) {
			return false;
		}

		//insert new data
		$query = "INSERT INTO...";
		foreach ($_POST as $key => $value) {
			
			if ($key === 'welcome_message') {
				
				$query .= "(1, '{$connection->real_escape_string($value)}', 1),";
			
			} elseif (strpos($key, 'secName') !== false) {
				
				$query .= "(2, '{$connection->real_escape_string($value)}', {$sec_i}),";
				$sec_i += 1;
			} elseif (strpos($key, 'parName') !== false) {
				
				$query .= "(3, '{$connection->real_escape_string($value)}', {$par_i}),";
				$par_i += 1;
			}
		}
		$query = substr($query, 0, -1);

		if (!$result = $connection->query($query)) {
			return false;
		}
	}
	header('Location: ../index.php');
    exit;  
}

welcomeSubmit();