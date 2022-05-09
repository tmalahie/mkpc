<?php
include('session.php');
$res = null;
if ($id && isset($_POST['peer'])) {
	include('initdb.php');
	if ($getCourse = mysql_fetch_array(mysql_query('SELECT course,banned FROM `mkjoueurs` j LEFT JOIN mkmuted m ON j.id=m.player WHERE j.id="'.$id.'" AND m.player IS NULL'))) {
		if (!$getCourse['banned']) {
            if ($getPeer = mysql_fetch_array(mysql_query('SELECT muted FROM `mkchatvoc` WHERE id="'.$_POST['peer'].'" AND course='.$getCourse['course'] .' AND player='.$id))) {
                $res = array(
                    'muted' => $getPeer['muted']
                );
            }
		}
	}
	mysql_close();
}
echo json_encode($res);
?>