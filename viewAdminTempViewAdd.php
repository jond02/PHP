<?php
require_once '../includes/db_connection.php';

function tempAddView() {
	global $connection;
	global $DBErrorMsg;
	$message = 'false';
	
	if (isset($_GET['vid']) && isset($_GET['tid'])) {
	
		$viewId = $connection->real_escape_string($_GET['vid']);
		$templateId = $connection->real_escape_string($_GET['tid']);
		
		$query = "INSERT INTO...";
		
		if ($result = $connection->query($query)) {
			$message = 'true';
		} else {
			$message = $DBErrorMsg;
		}
	}
	return $message;
}

echo tempAddView();