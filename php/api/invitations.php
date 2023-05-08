<?php
header('Content-Type: text/plain');
include('../includes/session.php');
if ($id) {
	include('../includes/initdb.php');
	for ($i=0;isset($_POST['j'.$i]);$i++) {
		if (!mysql_numrows(mysql_query('SELECT * FROM `mkignores` WHERE ignorer="'. $_POST['j'.$i] .'" AND ignored="'. $id .'"')))
			mysql_query('INSERT IGNORE INTO `mkinvitations` VALUES('. $id .',"'. $_POST['j'.$i] .'",-1,'.time().',"",'.(isset($_POST['battle'])?1:0).')');
	}
	mysql_close();
}
echo 1;
?>