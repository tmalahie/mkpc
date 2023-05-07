<?php
$extensions = Array('png', 'gif', 'jpg', 'jpeg');
if (!isset($src))
	$src = 'map';
$nbExt = count($extensions);
for ($i=0;$i<$nbExt;$i++) {
	$ext = $extensions[$i];
	if (file_exists('images/uploads/'.$src.$id.'.'.$ext))
		break;
}
?>