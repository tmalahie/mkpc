<?php
$defaultScreenScale = '(screen.width<800)?((screen.width<480)?4:6):((screen.width<1500)?8:10)';
?>
{
	quality: localStorage.getItem("iQuality") ? +localStorage.getItem("iQuality") : 5,
	music: localStorage.getItem("bMusic") ? +localStorage.getItem("bMusic"):0,
	sfx: localStorage.getItem("iSfx") ? +localStorage.getItem("iSfx"):0,
	screenscale: localStorage.getItem("iScreenScale") ? +localStorage.getItem("iScreenScale"):<?php echo $defaultScreenScale; ?>
}