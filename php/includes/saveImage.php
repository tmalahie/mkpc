<?php
if (isset($isTemp) && $isTemp) {
	if (!isset($ext))
		$ext = $ext2;
	$tempFile = tmpfile();
	$tempStream = stream_get_meta_data($tempFile);
	$tempName = $tempStream['uri'];
	if (isset($image))
		eval('image'.$ext2.'($image,$tempName);');
	elseif (isset($path))
		copy($path,$tempName);
}
else
	eval('image'.$ext2.'($image);');
?>