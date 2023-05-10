<?php
header('Content-Type: application/json');
include('../includes/session.php');
$res = null;
if ($id && isset($_POST['peer'])) {
	include('../includes/initdb.php');
	include('../includes/onlineUtils.php');
	$course = getCourse(array('check_ban' => true));
	if ($course) {
		if ($getPeer = mysql_fetch_array(mysql_query('SELECT muted FROM `mkchatvoc` WHERE id="'.$_POST['peer'].'" AND course='.$course .' AND player='.$id))) {
			$res = array(
				'muted' => $getPeer['muted']
			);
		}
	}
	mysql_close();
}
echo json_encode($res);
?>