<?php

require_once '../includes/db_connection.php';

function addTemplate() {
	global $connection;
	$id = 0;
	if (isset($_GET['name'])) {

		$name = $connection->real_escape_string($_GET['name']);
		$query = "INSERT INTO...";
		
		if (!$result = $connection->query($query)) {
			return;
		}
		//get new id
		$query = "SELECT..."; 
		if ($result = $connection->query($query)) {

			while ($row = $result->fetch_assoc()) {
				$id = $row['catid'];
			}

		} else {
			return;
		}
	}
	return $id;
}

echo addTemplate();