<?php
require_once '../includes/db_connection.php';
require_once './viewAdminAdjustOrder.php';

function deleteView() {
	global $connection;
	global $DBErrorMsg;
	$message = 'false';
	if (isset($_GET['id']) && isset($_GET['ty'])) {
		
		$id = $connection->real_escape_string($_GET['id']);
		$viewType = $connection->real_escape_string($_GET['ty']);
		
		$query = "DELETE FROM...;
					DELETE FROM...;
					DELETE FROM...";
		
		if ($result = $connection->multi_query($query)) {
			
			//adjust rel_order to fill in any gaps
			$ordQ = "SELECT...";
			
			$adj = adjustOrder($ordQ, true);
			if ($adj !== true) {
				return;
			}
			
			//everything was successful, update message
			$message = 'true';
		}
	}
	return $message;
}

echo deleteView();