<?php
function url_to_file_payload($url) {
	$fileContent = @file_get_contents($url);
	if ($fileContent) {
		$file = tmpfile();
		$fileStream = stream_get_meta_data($file);
		$filePath = $fileStream['uri'];
		file_put_contents($filePath, $fileContent);
		return array(
			'url' => $url,
			'size' => @filesize($filePath),
			'tmp_name' => $filePath,
			'tmp_file' => $file,
			'error' => 0
		);
	}
	else {
		return array(
			'url' => $url,
			'size' => 0,
			'tmp_name' => null,
			'error' => 1
		);
	}
}
?>