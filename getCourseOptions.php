<?php
if (isset($_COOKIE['iSfx']) || isset($_COOKIE['bMusic'])) {
	if (!isset($_COOKIE['iSound'])) {
		$iSound = $_COOKIE['iSfx']*2 + $_COOKIE['bMusic'];
		setcookie('iSound', $iSound, 4294967295, '/');
		$_COOKIE['iSound'] = $iSound;
	}
	setcookie('iSfx', null, 0, '/');
	setcookie('bMusic', null, 0, '/');
}
?>
{
	quality: <?php echo (isset($_COOKIE['iQuality']) ? $_COOKIE['iQuality']:5); ?>,
	sound: <?php echo (isset($_COOKIE['iSound']) ? $_COOKIE['iSound']:0); ?>,
	fps: <?php echo (isset($_COOKIE['iFps']) ? $_COOKIE['iFps']:1); ?>,
	screenscale: <?php echo (isset($_COOKIE['iScreenScale']) ? $_COOKIE['iScreenScale']:'(screen.width<800)?((screen.width<480)?4:6):((screen.width<1500)?8:10)'); ?>
}