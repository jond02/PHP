<?php
require_once '../includes/db_connection.php';
$value = 'false';

if (isset($_GET['viewid']) && isset($_GET['nav'])) {
	//get data
	$viewId = $connection->real_escape_string($_GET['viewid']);
	$navId = $connection->real_escape_string($_GET['nav']);

	//check if view is already part of navcat
	$query = "SELECT...";
	$result = $connection->query($query);
	if ($result && $result->num_rows < 1) {
		
		$query = "INSERT INTO...";
		if ($result = $connection->query($query)) {
				
			//success, get name
			$query = "SELECT...";
			if ($result = $connection->query($query)) {
				
				while($obj = $result->fetch_object()) {
					$value = $obj->view;
				}
			}
		}		
	} 
}
echo $value;
