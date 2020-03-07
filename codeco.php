<?php
if (isset($_POST['connecte']) && in_array($_POST['connecte'], array(0,1,2))) {
	include('session.php');
	if ($id) {
		include('initdb.php');
		mysql_query('DELETE FROM `mkconnectes` WHERE id="'. $id .'"');
		if ($_POST['connecte'] >= 1)
			mysql_query('INSERT INTO `mkconnectes` VALUES("'. $id .'",'. time() .')');
		else {
			mysql_query('DELETE FROM `mkconvs` WHERE sender="'. $id .'"');
			mysql_query('UPDATE `mkconvs` SET writting=NULL WHERE receiver="'. $id .'"');
		}
		mysql_query('UPDATE `mkjoueurs` SET online="'. $_POST['connecte'] .'" WHERE id="'. $id .'"');
		mysql_close();
	}
	echo 1;
}
?>