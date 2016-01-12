<?php
require_once '../includes/db_connection.php';
$message = 'false';
if (isset($_GET['imgid'])) {
	//get file name 
	$query = "SELECT...";
	if ($result = $connection->query($query)) {
			
		while ($row = $result->fetch_assoc()) {
			$img = '../studio_images/' . $row['file_name'];
		}
		//delete from DB
		$query = "DELETE FROM..."; 
		if ($result = $connection->query($query)) {
			//success	
			$message = 'true';
			//delete from server
			if (is_file($img)){
				unlink($img);
			}			
		}
	}
}
echo $message;