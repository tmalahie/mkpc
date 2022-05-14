<?php
include('session.php');
$res = 0;
if ($id) {
	include('initdb.php');
	if ($getCourse = mysql_fetch_array(mysql_query('SELECT course,banned FROM `mkjoueurs` j LEFT JOIN mkmuted m ON j.id=m.player WHERE j.id="'.$id.'" AND m.player IS NULL'))) {
		if (!$getCourse['banned']) {
			$muted = empty($_POST['muted']) ? 0 : 1;
			mysql_query('DELETE v,p FROM `mkchatvoc` v LEFT JOIN `mkchatvocpeer` p ON v.id=p.sender OR v.id=p.receiver WHERE v.player='.$id.' AND v.course='.$getCourse['course']);
			mysql_query('INSERT IGNORE INTO `mkchatvoc` SET course='.$getCourse['course'].', player='.$id.', muted="'. $muted .'"');
			if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM `mkchatvoc` WHERE course='.$getCourse['course'].' AND player='.$id.' ORDER BY id LIMIT 1'))) {
				$res = $getId['id'];
				mysql_query('DELETE FROM `mkchatvocpeer` WHERE sender="'.$res.'" OR receiver="'.$res.'"');
			}
		}
	}
	mysql_close();
}
echo $res;
?>