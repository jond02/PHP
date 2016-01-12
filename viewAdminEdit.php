<?php
require_once '../includes/db_connection.php';
require_once './viewAdminAdjustOrder.php';

function generateQuery($newName, $relOrder, $def, $viewId) {
	
	$query = "UPDATE...";
	if ($relOrder) {
		$query .= " rel_order = $relOrder,";
	}
	$query .= " definition = '{$def}' WHERE id = $viewId";
	return $query;
}

function createView($viewType, $newName, $def) {
	global $connection;
	global $DBErrorMsg;
	
	$query = "";
	if ($result = $connection->query($query)) {	
		
		while ($row = $result->fetch_assoc()) {
			$rel = $row['rel'] + 1;
		}
		
		$query = "INSERT INTO...";
		if ($result = $connection->query($query)) {
			
			$query = "SELECT...";
			if ($result = $connection->query($query)) {
				
				while ($row = $result->fetch_assoc()) {
					return $row['maxid'];
				}
				
			} else {
				echo $DBErrorMsg;
			}
			
		} else {
			echo $DBErrorMsg;
		}
		
	} else {
		echo $DBErrorMsg;
	}
		
}

function editView() {
	global $connection;
	global $DBErrorMsg;
	
	$message = 'false';
	if (isset($_GET['ord']) && isset($_GET['up']) && isset($_GET['ty']) && isset($_GET['def'])) {
		
		$order = $connection->real_escape_string($_GET['ord']);
		$newName = $connection->real_escape_string($_GET['up']);
		$viewType = $connection->real_escape_string($_GET['ty']);
		$def = $connection->real_escape_string($_GET['def']);
		
		if (isset($_GET['id'])) {
			$viewId = $connection->real_escape_string($_GET['id']);
		} else {
			//create new view and return id
			$viewId = createView($viewType, $newName, $def);
			if (!(is_int(intval($viewId)))) {
				return;
			}
		}
		
		if ($order === '_same') {

			$query = generateQuery($newName, false, $def, $viewId);

		} elseif ($order === '_last') {

			//get max relOrder
			$relOrder;
			$relQ = "SELECT...";
			
			if ($result = $connection->query($relQ)) {
				while ($row = $result->fetch_assoc()) {
					$relOrder = $row['ro'];
				}
				$relOrder += 1;
				$query = generateQuery($newName, $relOrder, $def, $viewId);

			} else {
				echo $DBErrorMsg;
			}

		} elseif ($order === '_first') {

			//get list of views to update
			$ordQ = "SELECT...";
			$adj = adjustOrder($ordQ, false);
			
			if ($adj === true) {
				//success, create query
				$query = generateQuery($newName, 1, $def, $viewId);
			}

		} elseif (is_int(intval($order))) {

			//find what rel_order should be based on the view id
			$ordQ = "SELECT...";
			if ($result = $connection->query($ordQ)) {
				
				while ($row = $result->fetch_assoc()) {
					
					//if first view was selected then set to 1 instead of going to 0
					if ($row['rel_order'] === 1) {
						$relOrder = 1;
					} else {
						$relOrder = $row['rel_order'];
					}
					
					//get list of views to update
					$ordQ = "SELECT...";
					$adj = adjustOrder($ordQ, false);

					if ($adj === true) {
						//success, create query
						$query = generateQuery($newName, $relOrder, $def, $viewId);		
					}
				}
			} else {
				echo $DBErrorMsg;
			}

		}

		//update view being edited
		if (isset($query)) {
			//send query
			if ($result = $connection->query($query)) {
				
				//adjust rel_order to fill in any gaps
				$ordQ = "SELECT...";

				$adj = adjustOrder($ordQ, true);
				if ($adj !== true) {
					return;
				}

				//Success, send back id
				$message = $viewId;

			} else {
				echo $DBErrorMsg;
			}
		}
	}
	return $message;
}

echo editView();


