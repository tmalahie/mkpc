<?php
include('initdb.php');
include('fetchSaves.php');
include('language.php');
if (isset($_COOKIE['iQuality']))
	$quality = $_COOKIE['iQuality'];
else
	$quality = 5;
if (isset($_COOKIE['iScreenScale']))
	$size = $_COOKIE['iScreenScale'];
elseif (isset($_POST['mobile'])) {
	$size = 4;
	setcookie('iScreenScale', $size, 4294967295, '/');
}
else
	$size = 6;
if (isset($_COOKIE['bMusic']))
	$music = $_COOKIE['bMusic'];
else
	$music = 0;
if (isset($_COOKIE['iSfx']))
	$sfx = $_COOKIE['iSfx'];
else
	$sfx = $music;
if (isset($_COOKIE['mkrecorder']))
	$recorder = $_COOKIE['mkrecorder'];
elseif (isset($_COOKIE['mkpseudo']))
	$recorder = $_COOKIE['mkpseudo'];
elseif (isset($_COOKIE['mkauteur']))
	$recorder = $_COOKIE['mkauteur'];
else
	$recorder = '';
if (isset($_COOKIE['mkdifficulty']))
	$difficulty = $_COOKIE['mkdifficulty'];
else
	$difficulty = 1;
if (isset($_COOKIE['mkplayers']))
	$players = $_COOKIE['mkplayers'];
else
	$players = 8;
echo '["'. $mkSaves .'",';
include('getPersos.php');
echo ',';
include('getLocks.php');
echo ','. $language .','.$quality.','.$size.','.($music ? 'true':'false').','.($sfx ? 'true':'false').',"'.str_replace('"', '\\"', str_replace('\\', '\\\\', $recorder)).'",'. $difficulty .','. $players .']';
mysql_close();
?>