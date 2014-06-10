<?php 
define('FILE_NOT_EXIST', 3845);
define('FILE_EXPIRED', 3846);
define('FILE_SUCCESS', 3840);

define ('FULL_URL', 'http://localhost/tempupload/');
define ('FULL_URL_UPLOADS', 'http://localhost/tempupload/uploads/');

ini_set('max_execution_time', 0);
$db = new mysqli('localhost', 'root', '', 'tempupload');

if ($db->connect_errno > 0) {
	die('Unable to connect to database [' . $db->connect_error . ']');
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
			} else if (time() >= $expiry_date) {
				$data['error'] = FILE_EXPIRED;
			} else {
				$data['error'] = FILE_SUCCESS;
			}
			$data['file_info'] = array('name'=>$name, 'full_name'=>$full_name, 'expiry_date'=>$expiry_date, 'key_file'=>$key_file);
			return $data;
			$stmt->close();
		} else {
			echo "EXECUTE failed: (" . $db->errno . ") " . $db->error;
		}
	} else {
		echo "PREPARE failed: (" . $db->errno . ") " . $db->error;
	}
}


if (!isset($_SERVER['HTTP_REFERER'])) {
	if (isset($_GET['id']) && trim($_GET['id']) != '') {
		redirect('index.php?id=' . trim($_GET['id']));
	} else {
		redirect(FULL_URL);
	}
} else {
	if (isset($_GET['id']) && trim($_GET['id']) != '') {
		$file = fileExist($_GET['id']);
		$url = FULL_URL_UPLOADS . $file['file_info']['name'];
		header("Content-disposition: attachment; filename=\"" . $file['file_info']['full_name'] . "\""); 
		readfile($url);
		exit();
	} else {
		redirect(FULL_URL);
	}
}