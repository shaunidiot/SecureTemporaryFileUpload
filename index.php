<?php
include 'config.php';
if (!empty($_FILES)) {
    if (file_exists($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
        if (strpos($_FILES["file"]["name"], '.') !== false) {
            if ($_FILES["file"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
            } else {
				$expiry_form_value = trim($_POST['expiry']);
				if (!is_numeric($expiry_form_value)) {
					$expiry_form_value = 1;
				}
				$expiry_date = '';
				switch ($expiry_form_value) {
					case 1: // one hour
						$expiry_date = strtotime('now') + 3600;  //2600 = 60 min X 60 sec
						break;
					case 2: //half a day
						$expiry_date = strtotime('now') + 43200;  
						break;
					case 3: // one day
						$expiry_date = strtotime('now') + 86400;  
						break;
					default;
						$expiry_date = strtotime('now') + 3600;  
						break;
				}
                $uploadedFileName = explode(".", $_FILES["file"]["name"]);
                //echo "file name : " . $_FILES["file"]["name"] . "<br>";
                //echo "temp name : " . $_FILES['file']['tmp_name'] . "<br>";
                $extension = strtolower(end($uploadedFileName));

                if (in_array($_FILES["file"]["type"], $valid_file_mime_types)) {
                    if (in_array($extension, $allowedFileExtensions)) {
                        // echo "FILE";
                        $fileRandomName = generateRandomString(true) . "." . $extension;
                        $destination = "uploads/" . $fileRandomName;
						if (move_uploaded_file($_FILES["file"]["tmp_name"], $destination)) {
							$key = keygen();
							$file_link = insertFile($fileRandomName, $_FILES["file"]["name"], $key, $expiry_date);
							if ($file_link) {
								echo "Successful. Link <a href='?id=".$key."'>Link</a>";
							} else {
								echo "Database error. Contact admin";
							}
						} else {
							echo "Unable to uploade file";
                        }
                    }
                } else
                    if (in_array($_FILES["file"]["type"], $valid_image_mime_types)) {
                        if (in_array($extension, $allowedImageExtensions)) {
                            // echo "IMAGE";
                            list($width, $height, $type, $attr) = getimagesize($_FILES['file']['tmp_name']);
                            // echo $width . "<br>" . $height . "<br>" . $type . "<br>" . $attr;
                            if (is_numeric($width) && is_numeric($height)) {
                                $fileRandomName = generateRandomString(true) . "." . $extension;
                                $destination = "uploads/" . $fileRandomName;
                                if (move_uploaded_file($_FILES["file"]["tmp_name"], $destination)) {
									$key = keygen();
									$file_link = insertFile($fileRandomName, $_FILES["file"]["name"], $key, $expiry_date);
                                    if ($file_link) {
                                        redirect(FULL_URL . '?id='.$key);
                                    } else {
                                        echo "Database error. Contact admin";
                                    }
                                } else {
                                    echo "Unable to uploade file";
                                }
                            } else {
                                echo "Image have no height or width?!";
                            }
                        } else {
                            echo "Image mime not in whitelist";
                        }

                    } else {
                        echo "Invalid file. Extension not in whitelist." . $_FILES["file"]["type"];
                    }
            }
        } else {
            echo "Invalid file. No extensions.";
        }
    }
}
if (isset($_GET['id'])) {
	if (trim($_GET['id']) != '') {
		$id = trim($_GET['id']);
		$file = fileExist($id);
		//print_r($file);
		if ($file['error'] == FILE_NOT_EXIST) {
			echo 'File has been deleted, or do not exist';
		} else if ($file['error'] == FILE_EXPIRED) {
			echo 'File has been expired and deleted';
			if (file_exists(UPLOAD_FOLDER . $file['name'])) {   
				unlink(UPLOAD_FOLDER . $file['name']);
			}
		} else if ($file['error'] == FILE_SUCCESS) {
			$include_countdown = true;
			$countdown_unix = $file['file_info']['expiry_date'];
			//echo $file['file_info']['full_name'];
?>
<table class="table table-condensed">
	<tr><td><label class="control-label">File : </label><td> <?=$file['file_info']['full_name'];?></tr>
	<!--<tr><td><label class="control-label">Expiring on : </label><td> <?=date('dS F Y [G:i:s a]', $file['file_info']['expiry_date']);?></tr>-->
	<tr><td><label class="control-label">Expiring on : </label><td><span id="file_expiry"></span></td></tr>
	<tr><td><label class="control-label">Download : </label><td> <a href="file.php?id=<?=$file['file_info']['key_file'];?>">Link</a></tr>
	<tr><td><label class="control-label">Share : </label><td><input type="text" id="shareText" class="form-control" onClick="clickToSelectAll('shareText');" value="<?=FULL_URL . '?id=' . $file['file_info']['key_file'];?>" readonly="readonly"/></tr>
</table>

<?php
		}
	}
} else {
?>
            <form action="index.php" method="post" enctype="multipart/form-data">
    				<div class="input-group">
    					<span class="input-group-btn">
    						<span class="btn btn-primary btn-file">
    							Browse <input type="file" name="file" id="file" />
    						</span>
    					</span>
    					<input type="text" class="form-control" readonly>
    				</div>
					<br>
					<div class="row">
						<div class="col-md-1">
							<button type="submit" name="submit" class="btn btn-sm btn-primary">Submit</button>
						</div>
						<div class="col-md-2">
							<select class="form-control" name="expiry">
									<option value="1" selected="true">One hour</option>
									<option value="2">Half a day</option>
									<option value="3">One day</option>
							</select> 
						</div>

					</div>
                </form>
<?php 
}
include 'template/footer.tpl';?>