<?php if(!isset($_SESSION)) {session_start();}

function checkForErrors($onOrOff) {
	if ($onOrOff) {
		error_reporting(E_ALL);
		ini_set('display_errors',1);
	} else {
		error_reporting(E_ERROR | E_PARSE);
	}
}

checkForErrors(false);

$gloNavcatId = 1;

function getBreadcrumbs() {
	global $connection;
	global $DBErrorMsg;
	global $gloNavcatId;
			
	function createList($category, $list) {
		global $connection;
		global $DBErrorMsg;	
		
		if ($category !== 'products') {
			//query db to get parent
			$query = "SELECT...";

			if ($result = $connection->query($query)) {
					
				while ($row = $result->fetch_assoc()) {
					//create category list
					$parent = $row['canonical_name'];
					if ($parent !== 'products') {
						$list = '<li><a href="index.php?nav=' . htmlentities($row['canonical_name']) . '">' . htmlentities($row['name']) . '</a> 
						<span class="glyphicon glyphicon-menu-right"></span> </li>' . $list;
					}
				}
								
			} else {
				echo databaseErrorMsg($DBErrorMsg);
			}
			
			//get list recursively
			$returnList = createList($parent, $list);
		} else {
			//Added on last recursion cycle
			$returnList = '<ol class="breadcrumb"><li><a href="index.php">Main Menu</a> <span class="glyphicon glyphicon-menu-right"></span> </li>' . $list;
		}	
		return $returnList;
	} //createList
	
	if (isset($_GET['nav'])) {
		
		$nav = $connection->real_escape_string($_GET['nav']);

		//get current category
		$list = '';
		$query = "SELECT...";

		if ($result = $connection->query($query)) {
			while ($row = $result->fetch_assoc()) {
				//create category list
				$list = '<li data-navcatid="' . $row['navcat_id'] . '" data-canon="' . $row['canonical_name'] . '" class="active">' . $row['name'] . '</li></ol>';
				$gloNavcatId = $row['navcat_id'];
			}				
		} else {
			echo databaseErrorMsg($DBErrorMsg);
		}

		if ($list === '') {
			//incorrect nav entered
			return false;
		}

		return createList($nav, $list);
	} else {
		//current category is main menu. Retain spacing so there isn't a jump when bc is added
		return '<ol class="breadcrumb" style="visibility: hidden;"><li>BC</li> <span class="glyphicon glyphicon-menu-right"></span> </ol>';
	}
}

function getNav() {
	global $connection;
	global $DBErrorMsg;	
	$returnAr = array();	

	//find what nav to use
	if (isset($_GET['nav'])) {

		//get nav child categories for selected parent		
		$navQuery = "SELECT...";
	} else {
		//use top level nav
		$navQuery = "SELECT...";				
	}

	if ($result = $connection->query($navQuery)) {
	
		$returnAr[0] = $result->num_rows;
		$navData = '';
	
		while ($row = $result->fetch_assoc()) {
			//create category list
			$navData .= "<li data-canon=\"{$row['canonical_name']}\"><a href=\"index.php?nav={$row['canonical_name']}\">{$row['name']}</a></li>";
		}
		$returnAr[1] = $navData;

	} else {
		echo databaseErrorMsg($DBErrorMsg);
	}
	return $returnAr;
}

function navAdmin($navCount) {
	$data = '';	
	for ($i = 0; $i < $navCount; $i++) {
		$data .= '<li><a><span class="glyphicon glyphicon-remove"> </span> <span class="glyphicon glyphicon-plus navPlus"> </span></a></li>';
	}
	return $data;
}

function getImages($imgType, $rowCount) {
	global $connection;
	global $DBErrorMsg;
	global $views;

	if (isset($_GET['nav']) && $rowCount === 0) {
		//if for main page without nav and that a product level category is selected
		$imgQuery = "SELECT...";
		$imgData = '';
		
		if ($result = $connection->query($imgQuery)) {
			while ($row = $result->fetch_assoc()) {
				//get this from the DB
				switch ($row['image_type']) {
					case 1: $imgType = $views[1]; Break;
					case 2: $imgType = $views[2]; Break;
					case 3: $imgType = $views[3]; Break;
					case 4: $imgType = $views[4]; Break;
				}
				//images, height="50px" after first url to constrain thumbnail height
				$imgData .= '<li><a href="#">
				<img 
					src="studio_images/' . htmlentities($row['file_name']) . 
					'" alt="styleguide image" .
					" data-large="studio_images/' . htmlentities($row['file_name']) .   
					'" data-description="' . $imgType . ' - ' . htmlentities($row['view']) . 
					'" data-viewid="' . htmlentities($row['view_id']) . 
					'" data-imgid="' . htmlentities($row['id']) . 
					'" />
					</a></li>';
			}

			//check if category has no content
			if (!$imgData) {
				$imgData = '<li><a href="#"><img src="studio_images/default/add_content.jpg" data-large="studio_images/default/add_content.jpg" 
					alt="default_image" data-imgid="add_content" data-description="" /></a></li>';
			}
		} else {
			echo databaseErrorMsg($DBErrorMsg);
		}		
	}
	return $imgData;
}

function getNotes($displayAdmin) {
	global $connection;
	global $DBErrorMsg;
	$noteHTML = '';
	$query = "SELECT...";
	$notesData = array();

	if ($result = $connection->query($query)) {
				
		while ($row = $result->fetch_array(MYSQLI_ASSOC)){	
			$notesData[] = array('label' => htmlentities($row['file_name']), 'id' => htmlentities($row['id']), 'note' => htmlentities($row['note'])); 						
		}	

	} else {
		echo databaseErrorMsg($DBErrorMsg);
	}
	return json_encode($notesData, JSON_FORCE_OBJECT);
}

function adminLogin() {
	//set session var for admin
	if (isset($_POST['admin_ps']) || isset($_POST['log_out'])) {
		
		if (isset($_POST['admin_ps'])) {
			
			if ($_POST['admin_ps'] === 'BigTree2') {
				//check password
				$_SESSION['styleGuideAdmin'] = true;

			} else {
				//wrong password
				$_SESSION['styleGuideAdmin'] = false;
				return true;
			}
			
		} else if (isset($_POST['log_out'])) {
			//log out
			$_SESSION['styleGuideAdmin'] = false;
		}
	
	} else if (!isset($_SESSION['styleGuideAdmin'])) {
		//create session var for the first time 
		$_SESSION['styleGuideAdmin'] = false;
	}
	return false;
}

function getUpdateLog() {
	global $connection;
	global $DBErrorMsg;	
	global $gloNavcatId;	
	
	$log = '';
	$query = "SELECT...";
				
	if ($result = $connection->query($query)) {
		while ($row = $result->fetch_array(MYSQLI_ASSOC)){
			if ($row['idate'] !== '0/0/0000') {
				$log .= "<tr><td>Admin</td><td>{$row['type']}</td><td>{$row['idate']}</td></tr>";
			}
		}	
	} else {
		echo databaseErrorMsg($DBErrorMsg);
	}		
	return $log;
}

//navAdmin controller

function controllerNavHTML($opt) {

	//get nav for current page
	$canon = (isset($_GET['nav'])) ? $_GET['nav'] : false;
	
	$data = getNavData($opt, $canon, false); 
	$html = '<option value=""> </option>';
	foreach ($data as $key => $value) {
		$html .= '<option value="' . $key . '">' . $value . '</option>';
	}
	return $html;
}

function controllerParentDeact() {
	//get list of product pages
	$canon = (isset($_GET['nav'])) ? $_GET['nav'] : false;
	$data = getNavData(2, $canon, false);
	$returnData = array();

	//check if any have deactivated children
	foreach ($data as $key => $value) {
		$deact = getParentDeact($key);
		
		foreach ($deact as $deKey => $deValue) {
			$returnData[$key][$deKey] = $deValue;
		}
	}
	return json_encode($returnData);
}

//navAdmin model

function getParentDeact($canon) {
	global $connection;
	global $DBErrorMsg;
	$data = array();

	$canon = $connection->real_escape_string($canon);
	
	$query = "SELECT...";
	if ($result = $connection->query($query)) {
		
		while ($row = $result->fetch_assoc()) {
			//create category list
			$data[$row['canonical_name']] = $row['name'];
		}
		
	} else {
		return databaseErrorMsg($DBErrorMsg);
	}
	return $data;
}

function getNavData($opt, $canon, $ids) {
	global $connection;
	global $DBErrorMsg;
	$data = array();
	
	//find what nav to use
	switch ($opt) {
		case 1:
			//all nav
			if ($canon) {
				$query = "SELECT...";
			} else {
				$query = "SELECT...";
			}
			break;
		case 2:
			//navs that are product pages
			if ($canon) {
				$query = "SELECT...";
			} else {
				$query = "SELECT...";
			}
			break;
		case 3:
			//deactivated nav
			if ($canon) {
				$query = "SELECT...";
			} else {
				$query = "SELECT...";
			}
		break;
	}
	
	if ($result = $connection->query($query)) {
	
		if ($ids) {
			//return id data
			while ($row = $result->fetch_assoc()) {
				//create category list
				$data[$row['canonical_name']] = $row['child_id'];
			}			
		} else {
			//return name data
			while ($row = $result->fetch_assoc()) {
				//create category list
				$data[$row['canonical_name']] = $row['name'];
			}	
		}

	} else {
		echo databaseErrorMsg($DBErrorMsg);
	}
	return $data;
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

function getWelcomeData() {
	global $connection;

	$data = array();
	$query = "SELECT...";

	if ($connection->multi_query($query)) {

	    do {
	        // store first result set
	        if ($result = $connection->store_result()) {
	            
	            while ($row = $result->fetch_assoc()) {
			        
			        //check which query
			        if (isset($row['text_content'])) {
						$data['title'] = $row['text_content'];
					} else {
						$data[$row['sec']] = $row['par'];
					}
	            }
	        	$result->free();
	        }
	        if (!$connection->more_results()) {
            	break;
        	}
  
	    } while ($connection->next_result());

	} else {
		return false;
	}
	return $data;
}

function createWelcomeForm() {
	global $connection;

	$data = getWelcomeData();
	$returnHTML;
	$html1 = '<input type="submit" class="welcFormSubmit" name="WelcSubmit" value="Save">
				<input type="text" placeholder="Welcome message" name="welcome_message" class="form-control welcMessage" value="replaceWelcome">';

	$html2 = '<div class="secDiv">
				<input type="text" placeholder="Section title" name="replaceSecName" class="form-control secTitle" value="replaceSection">
			 	<textarea class="form-control welcText" name="replaceParName">replaceTextarea</textarea>
				<span class="glyphicon glyphicon-plus secAdd"></span>
				<span class="glyphicon glyphicon-remove secRemove"></span>
			</div>';
	$i = 1;
	foreach($data as $key => $value) {
		//check which query
		if ($key === 'title') {
			$returnHTML = $html1;
			$returnHTML = str_replace('replaceWelcome', $value, $returnHTML);
		} else {
			$returnHTML .= $html2;
			$returnHTML = str_replace('replaceSection', $key, $returnHTML);
			$returnHTML = str_replace('replaceSecName', 'secName' . $i, $returnHTML);
			$returnHTML = str_replace('replaceTextarea', $value, $returnHTML);
			$returnHTML = str_replace('replaceParName', 'parName' . $i, $returnHTML);
			$i++;
		}
	}
	return $returnHTML;
}

function createWelcomeText() {
	global $connection;

	$data = getWelcomeData();
	$returnHTML;
	$html1 = '<h3>replaceWelcome</h3>';
	$html2 = '<h4>replaceSection</h5><p>replacePar</p>';

	foreach($data as $key => $value) {
		//check which query
		if ($key === 'title') {
			$returnHTML = $html1;
			$returnHTML = str_replace('replaceWelcome', $value, $returnHTML);
		} else {
			$returnHTML .= $html2;
			$returnHTML = str_replace('replaceSection', $key, $returnHTML);
			$returnHTML = str_replace('replacePar', $value, $returnHTML);
		}
	}
	return $returnHTML;
}