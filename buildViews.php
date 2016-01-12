<?php

function getViews($order) {
	global $connection;
	global $DBErrorMsg;	
	$returnAr = array();
	
	for($i = 1; $i < 5; $i++) {
		
		$viewType = $i;
		$query = "SELECT...";
		$query = ($order === 'REL') ? $query . ' ORDER BY rel;' : $query . ' ORDER BY view;';
		$views = array();
	
		if ($result = $connection->query($query)) {
				
			while ($row = $result->fetch_assoc()) {
				$views[$row['id']] = htmlentities($row['view']);
			}
			$returnAr['view' . $i] = $views;
	
		} else {
			echo databaseErrorMsg($DBErrorMsg);
		}
	}
	return $returnAr;
}

function getViewTypes() {
	global $connection;
	global $DBErrorMsg;	
	
	$query = "SELECT...";	
	$views = array();
	
	if ($result = $connection->query($query)) {
		
		while ($row = $result->fetch_assoc()) {
			$views[$row['id']] = htmlentities($row['view_type']);
		}
	} else {
		echo databaseErrorMsg($DBErrorMsg);
	}
	
	return $views;
}

function getViewList($viewTypeId, $admin, $navId) {
	global $connection;
	global $DBErrorMsg;	
	
	$query = "SELECT...";
	
	$viewData = '';
	
	$result = $connection->query($query);
	
	if ($result && $result->num_rows !== 0) {
		//navcat has been assigned a list	
		$viewData = '<ul data-viewid="' . $viewTypeId . '">';
		
		while ($row = $result->fetch_assoc()) {
			$viewData .= '<li data-viewid="' . htmlentities($row['view_id']) . '">' . htmlentities($row['view']);
			if ($admin) {
				$viewData .= ' <span class="glyphicon glyphicon-remove viewAdminDelete"> </span>';
			}
			$viewData .= '</li>';
		}
		$viewData .= '</ul>';
		
	} else {
		//give default values for all except prop
		//could also check for error here
		if ($viewTypeId !== 3) {
				
			$query = "SELECT...";
						
			if ($result = $connection->query($query)) {
				
				//create generic list and record in DB for next time
				$viewData = '<ul data-viewid="' . $viewTypeId . '">';	
				$insert = 'INSERT INTO...';
				
				while ($row = $result->fetch_assoc()) {
					$viewData .= '<li data-viewid="' . htmlentities($row['id']) . '">' . htmlentities($row['view']);
					if ($admin) {
						$viewData .= ' <span class="glyphicon glyphicon-remove viewAdminDelete"></span>';
					}
					$viewData .= '</li>';
					$insert .= '(' . $navId . ', ' . $row['id'] . '), ';
				}
				
				$viewData .= '</ul>';
				
				$insert = substr($insert,0,strlen($insert) - 2);
				if ($result = $connection->query($insert)) {
					
				}
				
			} else {
				echo databaseErrorMsg($DBErrorMsg);
			}
		} else {
			//provide the container for proprietary views
			$viewData .= '<ul data-viewid="' . $viewTypeId . '"></ul>';
		}
	}
	return $viewData;
}

function getViewTemplates() {
	global $connection;
	global $DBErrorMsg;	
	
	$query = "SELECT...";	
			
	if ($result = $connection->query($query)) {
		
		$list = '<select class="form-control shotListSelect" data-viewid="template"><optgroup label="Templates">';
		
		while ($row = $result->fetch_assoc()) {
			$list .= '<option value="' . htmlentities($row['id']). '">' . htmlentities($row['category']) . '</option>';
		}
		
		$list .= '</optgroup></select> ';
		
	} else {
		echo databaseErrorMsg($DBErrorMsg);
	}
	return $list;						
}			

function buildViews($displayAdmin, $returnArray, $navId) {
		
	$js = '';
	
	if ($displayAdmin) {
		//get multi-dim array of views for select inputs and js
		$viewsAr = getViews(false);
		$js = '<script>';
	}

	$views = getViewTypes();
	$viewHTML = '';
	
	//build shot list
	foreach ($views as $key => $value) {
		//header
		$viewHTML .= '<p data-viewid="' . $key . '" class="shotListViews">' . $value . '</p>';
		//shot list
		$viewHTML .= getViewList($key, $displayAdmin, $navId);
		
		if ($displayAdmin) {
			
			//add admin controls
			$viewHTML .= '<select data-viewid= "' . $key . '" class="form-control shotListSelect"><optgroup label="' . $value . '">';
			$viewjs = array();
			$idjs = array();
			
			foreach($viewsAr['view' . $key] as $keySel => $valueSel) {
				$viewHTML .= '<option value="' . $keySel . '">' . $valueSel . '</option>';
				//view arrays for js
				$viewjs[] = $valueSel;
				$idjs[] = $keySel;
				
			}
			$viewHTML .= '</optgroup></select> ';
			$viewHTML .= '<span data-viewid="' . $key . '" class="glyphicon glyphicon-plus viewAdminAdd"></span>';
			$js .= "var viewNames{$key} = " . json_encode($viewjs) . ";\n";
			$js .= "var viewIds{$key} = " . json_encode($idjs) . ";\n";
		}
	}
	
	if ($displayAdmin) {
		//include template options
		$viewHTML .= '<p class="shotListViews">Templates</p>';
		$viewHTML .= getViewTemplates();
		$viewHTML .= '<span data-viewid="template" class="glyphicon glyphicon-plus"></span>';
		$js .= 'var viewTypes = ' . json_encode($views) . ";\n";
		$js .= '</script>';
	}
	
	if ($returnArray) {
		//for page initialization
		$returnAr = array();
		$returnAr[0] = $js;
		$returnAr[1] = $viewHTML;
		$returnAr[2] = $views;
		return $returnAr;
	} else {
		//for js ajax
		return $viewHTML;
	}
}

function getDefinitions() {
	global $connection;
	global $DBErrorMsg;
	
	$query = "SELECT...";
	$defs = array();
	
	if ($result = $connection->query($query)) {
	
		while ($row = $result->fetch_assoc()) {
			$defs[$row['id']] = htmlentities($row['definition']);
		}
	} else {
		echo databaseErrorMsg($DBErrorMsg);
	}
	
	return $defs;	
}

function jsViewsAdmin() {
	//create js vars for admin page
	$js = array();
	$views = getViewTypes();
	$viewsAr = getViews('REL');

	foreach ($views as $key => $value) {

		$viewjs = array();
		$idjs = array();
			
		foreach($viewsAr['view' . $key] as $keySel => $valueSel) {
			//view arrays for js
			$viewjs[] = $valueSel;
			$idjs[] = $keySel;
		}
		$js["viewNames{$key}"] = $viewjs;
		$js["viewIds{$key}"] = $idjs;
	}
	$js['viewTypes'] = $views;
	return $js;
}


function viewAdmin() {

	$viewTypes = getViewTypes();
	$views = getViews(false);
	$sectionHTML = '<div data-viewtype="update_type" class="view_section">
			<table class="view_table">
				<tbody><tr>
					<td class="header_td">
						<p class="view_header">update_viewname</p>
					</td>
					<td class="btn_td light_td">
						<input type="button" class="btn main_btn btn_section_del" value="Delete">
						<input type="button" class="btn main_btn btn_section_edit" value="Edit">
						<input type="button" class="btn main_btn btn_section_add" value="Add">
					</td>
				</tr></tbody></table>
			<select class="form-control views_select" update_size>update_opts</select>
		</div>';

	$viewsOutput = '';

	foreach($viewTypes as $key => $value) {
		$viewsOutput .= $sectionHTML;
		//take view out of the name
		$viewsOutput = str_replace('update_viewname', str_replace(' View', '', $value), $viewsOutput);
		//set the size of the select
		switch ($key) {
			case 1: $viewsOutput = str_replace('update_size', 'size=2', $viewsOutput); $viewsOutput = str_replace('update_type', '1', $viewsOutput); break;
			case 2: $viewsOutput = str_replace('update_size', 'size=6', $viewsOutput); $viewsOutput = str_replace('update_type', '2', $viewsOutput); break;
			case 3: $viewsOutput = str_replace('update_size', 'size=7', $viewsOutput); $viewsOutput = str_replace('update_type', '3', $viewsOutput); break;
			case 4: $viewsOutput = str_replace('update_size', 'size=5', $viewsOutput); $viewsOutput = str_replace('update_type', '4', $viewsOutput); break;
		}
		//create the options for the select
		$optList = '';
		foreach($views['view' . $key] as $id => $name) {
			$optList .= '<option value="' . $id . '">' . $name . '</option>';
		}
		$viewsOutput = str_replace('update_opts', $optList, $viewsOutput);
	}
	return $viewsOutput;
}

function getTemplateData() {
	global $connection;
	global $DBErrorMsg;
	$views = array();
	
	//get list of views
	$qCats = "SELECT id FROM studio_view_template_types ORDER BY id";
	
	if ($result1 = $connection->query($qCats)) {

		while ($row1 = $result1->fetch_assoc()) {
			
			//get view ids
			$qViewIds = "...";
			
			if ($result2 = $connection->query($qViewIds)) {
			
				$ar = array();
				while ($row2 = $result2->fetch_assoc()) {
					
					//sort views by view type
					switch ($row2['view_type_id']) {
						case '1': $ar['1'][] = $row2['view_id']; break;
						case '2': $ar['2'][] = $row2['view_id']; break;
						case '3': $ar['3'][] = $row2['view_id']; break;
						case '4': $ar['4'][] = $row2['view_id']; break;
					}
				}
				//save in return array
				$views[$row1['id'] . 'ids' . '1'] = isset($ar['1']) ? $ar['1'] : array();
				$views[$row1['id'] . 'ids' . '2'] = isset($ar['2']) ? $ar['2'] : array();
				$views[$row1['id'] . 'ids' . '3'] = isset($ar['3']) ? $ar['3'] : array();
				$views[$row1['id'] . 'ids' . '4'] = isset($ar['4']) ? $ar['4'] : array();
				
			} else {
				echo databaseErrorMsg($DBErrorMsg);
			}
		}

	} else {
		echo databaseErrorMsg($DBErrorMsg);
	}
	return $views;
}

function viewAdminTemplates() {

	$viewTypes = getViewTypes();
	$sectionHTML = '<div data-viewtype="update_type" class="view_section">
			<table class="view_table">
				<tbody><tr>
					<td class="header_td">
						<p class="view_header">update_viewname</p>
					</td>
					<td class="btn_td light_td">
						<input type="button" class="btn main_btn btn_template_del" value="Delete">
						<input type="button" class="btn main_btn btn_template_add" value="Add">
					</td>
				</tr></tbody></table>
			<select class="form-control views_select" update_size></select>
		</div>';
	$viewsOutput = '';

	foreach($viewTypes as $key => $value) {
		$viewsOutput .= $sectionHTML;
		//take view out of the name
		$viewsOutput = str_replace('update_viewname', str_replace(' View', '', $value), $viewsOutput);
		//set the size of the select
		switch ($key) {
			case 1: $viewsOutput = str_replace('update_size', 'size=2', $viewsOutput); $viewsOutput = str_replace('update_type', '1', $viewsOutput); break;
			case 2: $viewsOutput = str_replace('update_size', 'size=6', $viewsOutput); $viewsOutput = str_replace('update_type', '2', $viewsOutput); break;
			case 3: $viewsOutput = str_replace('update_size', 'size=7', $viewsOutput); $viewsOutput = str_replace('update_type', '3', $viewsOutput); break;
			case 4: $viewsOutput = str_replace('update_size', 'size=5', $viewsOutput); $viewsOutput = str_replace('update_type', '4', $viewsOutput); break;
		}
	}
	return $viewsOutput;
}

function getTemplateTypes() {
	global $connection;
	global $DBErrorMsg;

	$query = "SELECT...";
		
	if ($result = $connection->query($query)) {

		$list;
		while ($row = $result->fetch_assoc()) {
			$list .= '<option value="' . htmlentities($row['id']). '">' . htmlentities($row['category']) . '</option>';
		}

	} else {
		echo databaseErrorMsg($DBErrorMsg);
	}
	return $list;
}

function getAllViews() {
	global $connection;
	global $DBErrorMsg;
	
	$query = "SELECT...";
	
	if ($result = $connection->query($query)) {
	
		$list = array();
		while ($row = $result->fetch_assoc()) {
			$list[$row['id']] = $row['view'];
		}
		
	} else {
		echo databaseErrorMsg($DBErrorMsg);
	}
	return $list;
}
