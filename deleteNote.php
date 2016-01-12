<?php
require_once '../includes/db_connection.php';
$message = 'false';
if (isset($_GET['noteid'])) {
	$query = "DELETE FROM..."; 

	if ($result = $connection->query($query)) {
		//success	
		$message = 'true';
	}
}
echo $message;