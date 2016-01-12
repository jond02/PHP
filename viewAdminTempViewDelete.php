<?php
require_once '../includes/db_connection.php';

function tempDeleteView() {
	global $connection;
	global $DBErrorMsg;
	$message = 'false';
	
	if (isset($_GET['vid']) && isset($_GET['tid'])) {
	
		$viewId = $connection->real_escape_string($_GET['vid']);
		$templateId = $connection->real_escape_string($_GET['tid']);
		
		$query = "DELETE FROM...";
		
		if ($result = $connection->query($query)) {
			$message = 'true';
		} else {
			$message = $DBErrorMsg;
		}
	}
	return $message;
}

echo tempDeleteView();