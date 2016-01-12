<?php

require_once '../includes/db_connection.php';

function DeleteTemplate() {
	global $connection;
	$message = 'false';
	if (isset($_GET['id'])) {

		$id = $connection->real_escape_string($_GET['id']);

		$query = "DELETE FROM...;
					DELETE FROM...;";

		if ($result = $connection->multi_query($query)) {
			$message = 'true';
		}
	}
	return $message;
}

echo DeleteTemplate();