<?php
require_once '../includes/db_connection.php';
$error = false;
if (isset($_GET['note']) && isset($_GET['nav']) && isset($_GET['file'])) {
	//get data
	$newNoteReturn = urldecode($_GET['note']);
	$newNote = $connection->real_escape_string($newNoteReturn);
	$navId = $connection->real_escape_string($_GET['nav']);
	$filename = $connection->real_escape_string($_GET['file']);
	$error = '';

	//get rel order
	$relOrder;
	$query = "SELECT...";
	
	if ($result = $connection->query($query)) {
			
		while ($row = $result->fetch_assoc()) {
			$relOrder = $row['rel'];
		}		
	} else {
		$error = true;
	}
	$relOrder = (isset($relOrder)) ? $relOrder + 1 : 1;
	
	//enter text in db, remove unsafe characters
	$query = "INSERT INTO..."; 

	if ($result = $connection->query($query) && !$error) {
		//success	
		//echo $newNoteReturn;
	} else {
		$error = true;
	}
	
	$query = "SELECT...";
	
	if ($result = $connection->query($query)) {
		
		while ($row = $result->fetch_assoc()) {
			$noteId = $row['noteid'];
		}		
	} else {
		$error = true;
	}
	
} else {
	//change to message?
	$error = true;
}

if (isset($noteId)) {
	echo $noteId;
} else {
	echo $error;
}
