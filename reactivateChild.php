<?php

if (isset($_GET['chcan']) && isset($_GET['pcan'])) {
	
	require_once ('../includes/db_connection.php');

	function reactivate($chid, $pid) {
		global $connection;
		global $DBErrorMsg;

		$query = "UPDATE...";
		if ($result = $connection->query($query)) {
			return 'true';
		} else {
			return 'false';
		}	
	}

	function getCanonId($canon) {
		global $connection;
		global $DBErrorMsg;
	
		$query = "SELECT...";
		if ($result = $connection->query($query)) {
	
			while ($row = $result->fetch_assoc()) {
				return $row['navcat_id'];
			}
	
		} else {
			echo databaseErrorMsg($DBErrorMsg);
		}
	}
	
	$chid = getCanonId($connection->real_escape_string($_GET['chcan']));
	$pid = getCanonId($connection->real_escape_string($_GET['pcan']));

	echo reactivate($chid, $pid);
}