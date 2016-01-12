<?php
require_once '../includes/db_connection.php';
$value = 'false';

if (isset($_GET['template']) && isset($_GET['nav'])) {
	//get data
	$templateId = $connection->real_escape_string($_GET['template']);
	$navId = $connection->real_escape_string($_GET['nav']);

	//delete all existing values of navcat
	$query = "DELETE FROM...";
	
	if ($result = $connection->query($query)) {
			
		//get new values to enter
		$query = "SELECT...";
		
		if ($result = $connection->query($query)) {
				
			$insert = "INSERT INTO...";
			
			while ($row = $result->fetch_assoc()) {
				$insert .= "({$navId}, {$row['view_id']}), ";
			}
	
			$insert = substr($insert,0,strlen($insert) - 2);
			
			if ($result = $connection->query($insert)) {
				//return new view list	
				require_once 'buildViews.php';	
				$value = buildViews(true, false, $navId);
			}
		}	
	}
}
echo $value;