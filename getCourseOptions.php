<?php
$defaultScreenScale = '(screen.width<800)?((screen.width<480)?4:6):((screen.width<1500)?8:10)';
$settingKeys = array('iQuality'=>'5', 'iScreenScale'=>null, 'bMusic'=>'0', 'iSfx'=>'0');
$isCookieSet = false;
foreach ($settingKeys as $settingKey=>$settingDef) {
	if (isset($_COOKIE[$settingKey])) {
		$isCookieSet = true;
		break;
	}
}
if ($isCookieSet) {
	?>
{
	quality: <?php echo (isset($_COOKIE['iQuality']) ? $_COOKIE['iQuality']:5); ?>,
	music: <?php echo (isset($_COOKIE['bMusic']) ? $_COOKIE['bMusic']:0); ?>,
	sfx: <?php echo (isset($_COOKIE['iSfx']) ? $_COOKIE['iSfx']:(isset($_COOKIE['bMusic']) ? $_COOKIE['bMusic']:0)); ?>,
	screenscale: <?php echo (isset($_COOKIE['iScreenScale']) ? $_COOKIE['iScreenScale']:$defaultScreenScale); ?>
};
<?php
foreach ($settingKeys as $settingKey=>$settingDef) {
	if (isset($_COOKIE[$settingKey]) && ($_COOKIE[$settingKey] !== $settingDef))
		echo 'localStorage.setItem("'. $settingKey .'", "'. $_COOKIE[$settingKey] .'");';
}
?>
setTimeout(function() {
	xhr("changeParam.php", "clear=1", function(reponse) {
		return (reponse == 1);
	});
}, 1000);
<?php
}
else {
	?>
{
	quality: localStorage.getItem("iQuality") ? +localStorage.getItem("iQuality") : 5,
	music: localStorage.getItem("bMusic") ? +localStorage.getItem("bMusic"):0,
	sfx: localStorage.getItem("iSfx") ? +localStorage.getItem("iSfx"):0,
	screenscale: localStorage.getItem("iScreenScale") ? +localStorage.getItem("iScreenScale"):<?php echo $defaultScreenScale; ?>
}
	<?php
}