<?php
require_once '../includes/db_connection.php';

function reassign() {
	global $connection;
	$message = 'false';

	if (isset($_GET['nid']) && isset($_GET['file'])) {
		//get data
		$noteId = $connection->real_escape_string($_GET['nid']);
		$file = $connection->real_escape_string($_GET['file']);

		//enter text in db, remove unsafe characters
		$query = "UPDATE...";

		if ($result = $connection->query($query)) {
			$message = 'true';
		} 
	}
	return $message;
}
echo reassign();

