<?php
header('Content-Type: text/plain');
$res = -1;
if (isset($_POST['id'])) {
	include('../includes/session.php');
	if ($id) {
		include('../includes/initdb.php');
		if ($getPts = mysql_fetch_array(mysql_query('SELECT j.pts_vs,j.pts_battle FROM `mkjoueurs` j WHERE j.id="'. $_POST['id'] .'"'))) {
			echo '['.$getPts['pts_vs'].','.$getPts['pts_battle'].']';
		}
		mysql_close();
	} else echo '[]';
}
else echo '[]';
?>