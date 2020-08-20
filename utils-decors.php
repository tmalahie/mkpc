<?php
define('DECORS_DIR', 'images/sprites/uploads/');
function decor_sprite_srcs($hash) {
	$res = array (
		'hd' => DECORS_DIR.$hash.'.png',
		'ld' => DECORS_DIR.$hash.'-ld.png'
	);
	if (!file_exists($res['ld']))
		$res['ld'] = $res['hd'];
	return $res;
}
?>