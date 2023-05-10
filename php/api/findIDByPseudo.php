<?php
header('Content-Type: text/plain');
$res = -1;
if (isset($_POST['pseudo'])) {
	include('../includes/session.php');
	if ($id) {
		include('../includes/initdb.php');
		if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM `mkjoueurs` WHERE nom="'. $_POST['pseudo'] .'"'))) {
			$res = $getId['id'];
			if ($res == $id)
				$res = -2;
		}
		mysql_close();
	}
}
echo $res;
?>