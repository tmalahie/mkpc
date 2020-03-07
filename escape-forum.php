<?php
include('initdb.php');
$getTopics = mysql_query('SELECT id,titre FROM mktopics ORDER BY id DESC');
while ($topic = mysql_fetch_array($getTopics))
	mysql_query('UPDATE mktopics SET titre="'. mysql_real_escape_string(iconv('windows-1252','utf-8',$topic['titre'])) .'" WHERE id='. $topic['id']);
$getMessages = mysql_query('SELECT id,topic,message FROM mkmessages ORDER BY topic DESC,id DESC');
while ($message = mysql_fetch_array($getMessages))
	mysql_query('UPDATE mkmessages SET message="'. mysql_real_escape_string(iconv('windows-1252','utf-8',$message['message'])) .'" WHERE id='. $message['id'] .' AND topic='. $message['topic']);
mysql_close();
?>