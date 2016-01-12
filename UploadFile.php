<?php
namespace styleguidephp;

class UploadFile {

	protected $destination;
	protected $messages = array();
	protected $maxSize = 12582912; //12 MB for individual files, can't be a calculation.
	//protected $maxSize = 2097152; //for localhost
	protected $permittedTypes = array('image/jpeg','image/gif','image/png'); //'image/webp','image/tiff' - php can't reduce the size of tiffs
	protected $newName;
	protected $typeCheckingOn = true; //turn off to not type mime types
	protected $notTrusted = array('bin', 'cgi', 'exe', 'js', 'pl', 'php', 'py', 'sh');
	protected $suffix = '.upload';
	protected $renameDuplicates;

	public $nav;

	public function __construct($uploadFolder) {

		$nav = (isset($_GET['nav'])) ? $this->nav = $_GET['nav'] : $nav = 'none';
		if (!is_dir($uploadFolder) || !is_writable($uploadFolder)) {
			throw new \Exception("$uploadFolder must be a valid, writeable folder.");
		}
		if ($uploadFolder[strlen($uploadFolder) - 1] != '/') {
			$uploadFolder .= '/';
		}
		$this->destination = $uploadFolder;
	}
	
	public function setMaxSize($bytes) {

		$serverMax = self::convertToBytes(ini_get('upload_max_filesize'));
		
		if ($bytes > $serverMax) {
			throw new \Exception('Maximum size cannot exceed server limit for individual files: ' . self::convertFromBytes($serverMax));
		}
		
		if (is_numeric($bytes) && $bytes > 0) {
			$this->maxSize = $bytes;
		}
	}
	
	public static function convertToBytes($val) {

		$val = trim($val);
		$last = strtolower($val[strlen($val) - 1]);
		if (in_array($last, array('g', 'm', 'k'))) {
			switch ($last) {
				//fall through to calculate
				case 'g':
					$val *= 1024;
				case 'm':
					$val *= 1024;
				case 'k':
					$val *= 1024;
			}
		}
		return $val;
	}
	
	public static function convertFromBytes($bytes) {

		$bytes /= 1024;
		if ($bytes > 1024) {
			return number_format($bytes/1024, 1) . ' MB';
		} else {
			return number_format($bytes, 1) . ' KB';
		}
	}
	
	public function allowAllTypes($suffix = null) {

		$this->typeCheckingOn = false;
		if (!is_null($suffix)) {
			if (strpos($suffix, '.') === 0 || $suffix === '') {
				$this->suffix = $suffix;
			} else {
				$this->suffix = ".$suffix";
			}
		}
	}

	public function upload($renameDuplicates = true) {

		$this->renameDuplicates = $renameDuplicates;
		$uploaded = current($_FILES);
		if (is_array($uploaded['name'])) {
			//multiple files
			foreach($uploaded['name'] as $key => $value) {
				$currentFile['name'] = $uploaded['name'][$key];
				$currentFile['type'] = $uploaded['type'][$key];
				$currentFile['tmp_name'] = $uploaded['tmp_name'][$key];
				$currentFile['error'] = $uploaded['error'][$key];
				$currentFile['size'] = $uploaded['size'][$key];
				if ($this->checkFile($currentFile)) {
					$this->moveFile($currentFile);
				}
			}
		} else {
			//single file
			if ($this->checkFile($uploaded)) {
				$this->moveFile($uploaded);
			}
		}
	}
	
	public function getMessages() {
		return $this->messages;
	}
	
	protected function checkFile($file) {

		if ($file['error'] !== 0) {
			$this->getErrorMessage($file);
			return false;
		}
		if (!$this->checkSize($file)) {
			return false;
		}
		if ($this->typeCheckingOn) {
			if (!$this->checkType($file)) {
				return false;	
			}
		}
		$this->checkName($file);
		
		return true;
	}
	
	protected function getErrorMessage($file) {

		switch($file['error']) {
			case 1:
			case 2:
				//intentional fall through
				$this->messages[] = $file['name'] . ' is too big: (max: ' . self::convertFromBytes($this->maxSize) . ').';
				break;
			case 3:
				$this->messages[] = $file['name'] . ' was only partially uploaded.';
				break;
			case 4:
				$this->messages[] = 'No file submitted.';
				break;				
			default:
				$this->messages[] = 'Sorry, there was a problem uploading ' . $file['name'];
				break;
		}
	}
	
	protected function checkSize($file) {

		if ($file['size'] === 0) {
			$this->messages[] = $file['name'] . ' is empty';
			return false;
		} elseif ($file['size'] > $this->maxSize) {
			$this->messages[] = $file['name'] . ' exceeds the maximum size for a file (' . self::convertFromBytes($this->maxSize) . ')';
			return false;
		} else {
			return true;
		}
	}
	
	protected function checkType($file) {

		if (in_array($file['type'], $this->permittedTypes)) {
			return true;
		} else {
			$this->messages[] = $file['name'] . ' is not a permitted file type.';
			return false;	
		}	
	}
	
	protected function checkName($file) {

		$this->newName = null;
		$noSpaces = str_replace(' ', '_', $file['name']);
		//added to put all images in same folder
		$noSpaces = $this->nav . $noSpaces;
		if ($noSpaces !== $file['name']) {
			$this->newName = $noSpaces;
		}
		$nameParts = pathinfo($noSpaces);
		$extension = isset($nameParts['extension']) ? $nameParts['extension'] : '';
		if (!$this->typeCheckingOn && !empty($this->suffix)) {
			if (in_array($extension, $this->notTrusted) || empty($extension)) {
				$this->newName = $noSpaces . $this->suffix;
			}
		}
		if ($this->renameDuplicates) {
			$name = isset($this->newName) ? $this->newName : $file['name'];
			$exisiting = scandir($this->destination);
			if (in_array($name, $exisiting)) {
				$i = 1;
				do {
					$this->newName = $nameParts['filename'] . '_' . $i++;
					if (!empty($extension)) {
						$this->newName .= ".$extension";
					}
					if (in_array($extension, $this->notTrusted)) {
						$this->newName .= $this->suffix;
					}
				} while (in_array($this->newName, $exisiting));
			}
		}
	}
	
	protected function insertDBdata($file, $filename) {
		
		//upload to DB
		global $connection;
		global $DBErrorMsg;
		$navID;
		$query = "SELECT...";

		if ($result = $connection->query($query)) {
			while ($row = $result->fetch_assoc()) {
				$navID = $row['navcat_id'];
			}		
		} else {
			echo databaseErrorMsg($DBErrorMsg);
		}
		
		//get image type
		$extension = pathinfo($filename,PATHINFO_EXTENSION);
		//file name is the name that the radio button is given for POST
		//POST converts . and spaces to _ , convert name to use _
		$temp = str_replace('.' . $extension,'_' . $extension, $file['name']);
		//check for other periods and spaces
		$temp = str_replace('.','_', $temp);
		$temp = str_replace(' ','_', $temp);
		//gets the image type number stored in POST
		$imgType = $_POST[$temp];
		$imgViewType = $_POST[$temp . '_sel'];
		
		$relOrder;
		$query = "SELECT...";

		if ($result = $connection->query($query)) {
			while ($row = $result->fetch_assoc()) {
				$relOrder = $row['rel'];
			}		
		} else {
			echo databaseErrorMsg($DBErrorMsg);
		}
		$relOrder = (is_numeric($relOrder)) ? $relOrder + 1 : 1;
		
		$query = "INSERT INTO... 
					VALUES..."; 
		
		if (!$result = $connection->query($query)) {	
			echo databaseErrorMsg($DBErrorMsg);
		}

	}
	
	protected function moveFileOriginal($file) {

		$filename = isset($this->newName) ? $this->newName : $file['name'];
		$success = move_uploaded_file($file['tmp_name'], $this->destination . $filename);
		if ($success) {
			$result = $file['name'] . ' was uploaded successfully';
			if (!is_null($this->newName)) {
				$result	.= ', and was renamed ' . $this->newName;
			}
			$result .= '.';
			$this->messages[] = $result;

			$this->insertDBdata($file, $filename);
		} else {
			$this->messages[] = 'Could not upload ' . $file['name'];
		}
	}

	protected function reduceSize($filename, $file) {
	    
	    $quality = 85;
	    switch ($file['type']) {
	        case 'image/gif':
	            $image = imagecreatefromgif($file['tmp_name']);
	            break;
	        case 'image/jpeg':
	            $image = imagecreatefromjpeg($file['tmp_name']);
	            break;
	        case 'image/png':
	            $image = imagecreatefrompng($file['tmp_name']);
	            break;
	    } 

	    imageinterlace($image, true);
	    $result = imagejpeg($image, $this->destination . $filename, round($quality));
	            
	    if (!$result) {
	        $this->messages[] = 'Unable to save image: ' . $filename;
	    }
	    // Free up memory
		imagedestroy($image);

	    return $result;
	}

	protected function moveFile($file) {

		$filename = isset($this->newName) ? $this->newName : $file['name'];
		$success = $this->reduceSize($filename, $file);
		if ($success) {
			$result = $file['name'] . ' was uploaded successfully';
			if (!is_null($this->newName)) {
				$result	.= ', and was renamed ' . $this->newName;
			}
			$result .= '.';
			$this->messages[] = $result;

			$this->insertDBdata($file, $filename);
		} else {
			$this->messages[] = 'Could not upload ' . $file['name'];
		}
	}
}
