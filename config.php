<?php
define('FILE_NOT_EXIST', 3845);
define('FILE_EXPIRED', 3846);
define('FILE_SUCCESS', 3840);

define ('FULL_URL', 'http://localhost/tempupload/');
define ('FULL_URL_UPLOADS', 'http://localhost/tempupload/uploads/');
define ('UPLOAD_FOLDER', 'uploads/');

$countdown_unix = '';
$include_countdown = false;
include 'template/header.tpl';
ini_set('max_execution_time', 0);
$db = new mysqli('localhost', 'root', '', 'tempupload');

if ($db->connect_errno > 0) {
	die('Unable to connect to database [' . $db->connect_error . ']');
}

function redirect($link) {
	echo "<script language='javascript'>
    window.location.href='".$link."';
    </script>";
}
function getValue($sql) {
	global $db;
	$result = $db->query($sql);
	$value = $result->fetch_array(MYSQLI_NUM);
	return is_array($value) ? $value[0] : "";
}

$valid_image_mime_types = array(
"image/gif",
"image/png",
"image/jpeg",
"image/jpg",
"image/pjpeg",
"image/pjpg");

$allowedImageExtensions = array(
"gif",
"jpeg",
"jpg",
"png");

$valid_file_mime_types = array(
"application/x-compressed",
"application/x-zip-compressed",
"application/zip",
"multipart/x-zip",
"application/msword",
"application/mspowerpoint",
"application/powerpoint",
"application/vnd.ms-powerpoint",
"application/x-mspowerpoint",
"application/pdf");
$allowedFileExtensions = array(
"zip",
"rar",
"doc",
"docx",
"ppt",
"pptx",
"pdf");

/*
CHECK HTACCESS IN UPLOAD FOLDER TOO
*/

function keygen($length = 8) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, strlen($characters) - 1)];
	}
	$nameExist = checkIfKeyExist($randomString);
	while ($nameExist) {
		$randomString = keygen();
		if (!checkIfKeyExist($randomString)) {
			$nameExist = false;
		}
	}
	return $randomString;
}

function checkIfKeyExist($key) {
	global $db;
	$query = "SELECT key_file FROM data WHERE key_file = ?";
	if ($stmt = $db->prepare($query)) {
		$stmt->bind_param('s', $key);
		if ($stmt->execute()) {
			$stmt->store_result();
			$key_check = '';
			$stmt->bind_result($key_check);
			$stmt->fetch();
			if ($stmt->num_rows < 1) {
				return false;
			} else {
				return true;
			}
			$stmt->close();
		} else {
			echo "EXECUTE failed: (" . $db->errno . ") " . $db->error;
		}
	} else {
		echo "PREPARE failed: (" . $db->errno . ") " . $db->error;
	}
}

function generateRandomString($checkName, $length = 10) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, strlen($characters) - 1)];
	}
	if ($checkName) {
		$nameExist = checkIfNameExist($randomString);
		while ($nameExist) {
			$randomString = generateRandomString($checkName);
			if (!checkIfNameExist($randomString)) {
				$nameExist = false;
			}
		}
		return $randomString;
	}
	return $randomString;
}

function checkIfNameExist($name) {
	global $db;
	$query = "SELECT name FROM data WHERE name = ?";
	if ($stmt = $db->prepare($query)) {
		$stmt->bind_param('s', $name);
		if ($stmt->execute()) {
			$stmt->store_result();
			$name_check = '';
			$stmt->bind_result($name_check);
			$stmt->fetch();
			if ($stmt->num_rows < 1) {
				return false;
			} else {
				return true;
			}
			$stmt->close();
		} else {
			echo "EXECUTE failed: (" . $db->errno . ") " . $db->error;
		}
	} else {
		echo "PREPARE failed: (" . $db->errno . ") " . $db->error;
	}
}
function insertFile($name, $full_name, $key, $expiry) {
	/*
$stmt = $mysqli->prepare("INSERT INTO SampleTable VALUES (?)");
$stmt->bind_param('s', $sample);   // bind $sample to the parameter

// escape the POST data for added protection
$sample = isset($_POST['sample'])
		? $mysqli->real_escape_string($_POST['sample'])
		: '';

/* execute prepared statement 
$stmt->execute();
*/
	global $db;
	$q = "INSERT INTO data (name, full_name, key_file, expiry_date) VALUES ('" . trim($name) . "', '" . $db->real_escape_string(trim($full_name)) . "' , '".$key."', '".$expiry."')";
	if (!$db->query($q)) {
		return false;
		//echo "INSERT failed: (" . $db->errno . ") " . $db->error;
	} else {
		return $db->insert_id;
	}

}


function fileExist($key) {
	$data = array();
	global $db;
	$query = "SELECT * FROM data WHERE key_file = ?";
	if ($stmt = $db->prepare($query)) {
		$stmt->bind_param('s', $key);
		if ($stmt->execute()) {
			$stmt->store_result();
			$id = ''; $name = '';  $full_name = '';  $key_file = '';  $expiry_date = '';
			$stmt->bind_result($id, $name, $full_name, $key_file, $expiry_date);
			$stmt->fetch();
			if ($stmt->num_rows < 1) {
				$data['error'] = FILE_NOT_EXIST;
				return $data;
			} else if (time() >= $expiry_date) {
				$data['error'] = FILE_EXPIRED;
				$data['expiry'] = $expiry_date;
				$data['time'] = time();
				return $data;
			} else {
				$data['error'] = FILE_SUCCESS;
				$data['file_info'] = array('name'=>$name, 'full_name'=>$full_name, 'expiry_date'=>$expiry_date, 'key_file'=>$key_file);
				return $data;
			}
			$stmt->close();
		} else {
			echo "EXECUTE failed: (" . $db->errno . ") " . $db->error;
		}
	} else {
		echo "PREPARE failed: (" . $db->errno . ") " . $db->error;
	}
}
?>