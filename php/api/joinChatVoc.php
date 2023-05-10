<?php
header('Content-Type: text/plain');
include('../includes/session.php');
$res = 0;
if ($id) {
	include('../includes/initdb.php');
	include('../includes/onlineUtils.php');
	$course = getCourse(array('check_ban' => true));
	if ($course) {
		$muted = empty($_POST['muted']) ? 0 : 1;
		mysql_query('DELETE v,p FROM `mkchatvoc` v LEFT JOIN `mkchatvocpeer` p ON v.id=p.sender OR v.id=p.receiver WHERE v.player='.$id.' AND v.course='.$course);
		mysql_query('INSERT IGNORE INTO `mkchatvoc` SET course='.$course.', player='.$id.', muted="'. $muted .'"');
		if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM `mkchatvoc` WHERE course='.$course.' AND player='.$id.' ORDER BY id LIMIT 1'))) {
			$res = $getId['id'];
			mysql_query('DELETE FROM `mkchatvocpeer` WHERE sender="'.$res.'" OR receiver="'.$res.'"');
		}
	}
	mysql_close();
}
echo $res;
?>