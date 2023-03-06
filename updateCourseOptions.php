<?php
header('Content-Type: text/plain');
if (isset($_POST['difficulty']))
	setcookie('mkdifficulty',$_POST['difficulty'],4294967295,'/');
if (isset($_POST['players']))
	setcookie('mkplayers',$_POST['players'],4294967295,'/');
if (isset($_POST['team']))
	setcookie('mkteam',$_POST['team'],4294967295,'/');
echo 1;
?>