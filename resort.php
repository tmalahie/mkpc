<?php
include('initdb.php');
$topics = mysql_query('SELECT * FROM `mktopics`');
while ($topic = mysql_fetch_array($topics)) {
	$getLastMessage = mysql_fetch_array(mysql_query('SELECT date FROM `mkmessages` WHERE topic="'. $topic['id'] .'" ORDER BY id DESC limit 1'));
	mysql_query('UPDATE `mktopics` SET dernier="'.$getLastMessage['date'].'" WHERE id="'. $topic['id'] .'"');
}
mysql_close();
echo 1;
?>