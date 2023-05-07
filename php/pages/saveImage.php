<?php
if (isset($isTemp) && $isTemp) {
	if (!isset($ext))
		$ext = $ext2;
	do {
		$tempName = 'temp'. rand(0,2147483647).'.'. $ext;
	} while (file_exists($tempName));
	if (isset($image))
		eval('image'.$ext2.'($image,$tempName);');
	elseif (isset($path))
		copy($path,$tempName);
}
else
	eval('image'.$ext2.'($image);');
?>