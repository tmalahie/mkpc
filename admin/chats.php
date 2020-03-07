<?php
include('auth.php');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
<title>Admin MKPC</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php
$chats = mysql_query('SELECT m.sender,m.receiver,COUNT(m.id) AS nbmessages,MAX(m.id) AS maxID FROM `mkchats` m GROUP BY sender,receiver ORDER BY maxID DESC'. (isset($_GET['all']) ? '':' LIMIT 100'));
$nbmessages = Array();
$messages = Array();
while ($chat = mysql_fetch_array($chats)) {
	if (isset($nbmessages[$chat['sender'].'_'.$chat['receiver']]))
		$nbmessages[$chat['sender'].'_'.$chat['receiver']] += $chat['nbmessages'];
	else {
		$nbmessages[$chat['receiver'].'_'.$chat['sender']] = $chat['nbmessages'];
		$messages[] = $chat;
	}
}
foreach ($messages as $chat) {
	$getPseudo1 = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id='. $chat['sender']));
	$getPseudo2 = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id='. $chat['receiver']));
	echo '<a href="messages.php?id1='. $chat['sender'] .'&amp;id2='. $chat['receiver'] .'">';
	echo $getPseudo1['nom'] .' &gt; '. $getPseudo2['nom'];
	echo '</a> ['. $nbmessages[$chat['receiver'].'_'.$chat['sender']] .' messages]<br />';
}
?>
</body>
</html>