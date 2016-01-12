<?php

if (isset($_GET['canon']) && isset($_GET['active'])) {
	
	require_once ('../includes/db_connection.php');
	require_once ('../includes/functions.php');

	function walkTheCategories($canon, $active) {
		global $connection;
		global $DBErrorMsg;
		//query for canon children
		if ($active === '1') {
			//get deactivated nav
			$data = getNavData(3, $canon, true);
		} else {
			//get active nav
			$data = getNavData(1, $canon, true);
		}
		
		$parentId = getCanonId($canon);

		foreach ($data as $subCanon => $id) {
			//deactivate
			$query = "UPDATE...";
			if (!$result = $connection->query($query)) {
				echo databaseErrorMsg($DBErrorMsg);
			}
			//recursively find all children
			walkTheCategories($subCanon, $active);
		}
	}
	//update root
	function updateRoot($canon, $active) {
		global $connection;
		global $DBErrorMsg;

		$query = "UPDATE...";
		if ($result = $connection->query($query)) {
			return true;
		} else {
			echo databaseErrorMsg($DBErrorMsg);
		}	
	}

	$canon = $connection->real_escape_string($_GET['canon']);
	$active = $connection->real_escape_string($_GET['active']);

	updateRoot($canon, $active);
	walkTheCategories($canon, $active);
}