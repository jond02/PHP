<?php

function adjustOrder($ordQuery, $count) {
	global $connection;
	global $DBErrorMsg;
	$ids = array();

	if ($result = $connection->query($ordQuery)) {
		
		if ($count) {
			//update order with by count to fill in any gaps
			$i = 1;
			while ($row = $result->fetch_assoc()) {
				$ids[$row['id']] = $i;
				$i++;
			}

		} else {
			//update order from given point and move back one in list
			while ($row = $result->fetch_assoc()) {
				$ids[$row['id']] = $row['rel'] + 1;
			}
		}

		$whereList = implode(',', array_keys($ids));
		
		//build query for updating rel order
		$updateQ = "UPDATE view SET rel = CASE id ";
		
		foreach($ids as $id => $ordinal) {
			$updateQ .= sprintf("WHEN %d THEN %d ", $id, $ordinal);
		}
		
		$updateQ .= " END WHERE id IN ({$whereList})";

		if ($result = $connection->query($updateQ)) {

			//success
			return true;

		} else {
			echo $DBErrorMsg;
		}

	} else {
		echo $DBErrorMsg;
	}
}