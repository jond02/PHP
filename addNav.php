<?php
require_once ('../includes/db_connection.php');

function addNav() {
	global $connection;
	
	if (isset($_GET['name']) && isset($_GET['pc'])) {
	
	$name = $connection->real_escape_string($_GET['name']);
	$parCan = $connection->real_escape_string($_GET['pc']);
	$parId = getCanonId($parCan);
	
	//get min id fron navcats and minus 1
	$query = "SELECT...";
	
	if ($result = $connection->query($query)) {
		$row = $result->fetch_assoc();
		$newId = (int)$row['navid'];
		$newId -= 1;
	} else {
		return 'false';
	}
	
	//create canonical name
	$nameCan = strtolower($name);
	$nameCan = str_replace(' ', '-', $nameCan);
	$nameCan = str_replace("'", '', $nameCan);
	
	//check for duplicate name
	if ($exisiting = checkExisting()) {
	
		if (in_array($nameCan, $exisiting)) {
			$i = 1;
			
			do {
				$nameTest = $nameCan . '-' . $i++;
			} while (in_array($nameTest, $exisiting));
			
			$nameCan = $nameTest;
		}
	}
	
	$query = "INSERT...";
	if (!$result = $connection->query($query)) {
		return 'false';
	}
	//get rel order and add 1
	$query = "SELECT...";
	if ($result = $connection->query($query)) {
		$row = $result->fetch_assoc();
		$rel = (int)$row['rel_order'];
		$rel += 1;
	} else {
		return 'false';
	}
	
	$query = "INSERT INTO...";
	if (!$result = $connection->query($query)) {
		return 'false';
	}	
	
	return $nameCan;
	
	} else {
		return 'false';
	}
}

function checkExisting() {
	global $connection;
	$returnAr = array();
	$query = "SELECT...";
	if ($result = $connection->query($query)) {

		while ($row = $result->fetch_assoc()) {
			$returnAr[] = $row['canonical_name'];
		}
		return $returnAr;

	} else {
		return false;
	}
}

function getCanonId($canon) {
	global $connection;

	$query = "SELECT...";
	if ($result = $connection->query($query)) {

		while ($row = $result->fetch_assoc()) {
			return $row['navcat_id'];
		}

	} else {
		return 'false';
	}
}

echo addNav();

