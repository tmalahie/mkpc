<?php
if (isset($isTemp) && $isTemp) {
	if (!isset($ext))
		$ext = $ext2;
	do {
		$tempName = 'temp'. rand(0,2147483647).'.'. $ext;
	} while (file_exists($tempName));
	eval('image'.$ext2.'($image,$tempName);');
}
else
	eval('image'.$ext2.'($image);');
?>