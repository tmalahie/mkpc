<?php
$isUploaded = true;
if ((!isset($_FILES['image'])||$_FILES['image']['error']) && !empty($_POST['url'])) {
	$isUploaded = false;
	$fileContent = @file_get_contents($_POST['url']);
	if ($fileContent) {
		$file = tmpfile();
		$fileStream = stream_get_meta_data($file);
		$filePath = $fileStream['uri'];
		file_put_contents($filePath, $fileContent);
		$_FILES['image'] = array(
			'size' => @filesize($filePath),
			'tmp_name' => $filePath,
			'error' => null
		);
	}
}
function move_given_file($src,$dest) {
	global $isUploaded;
	if ($isUploaded)
		move_uploaded_file($src, $dest);
	else
		rename($src, $dest);
}
?>