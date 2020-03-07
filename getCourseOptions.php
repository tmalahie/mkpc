{
	quality: <?php echo (isset($_COOKIE['iQuality']) ? $_COOKIE['iQuality']:5); ?>,
	music: <?php echo (isset($_COOKIE['bMusic']) ? $_COOKIE['bMusic']:0); ?>,
	sfx: <?php echo (isset($_COOKIE['iSfx']) ? $_COOKIE['iSfx']:(isset($_COOKIE['bMusic']) ? $_COOKIE['bMusic']:0)); ?>,
	screenscale: <?php echo (isset($_COOKIE['iScreenScale']) ? $_COOKIE['iScreenScale']:'(screen.width<480)?4:6'); ?>
}