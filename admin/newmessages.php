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
$msgs = mysql_query('SELECT c.sender,c.message,c.date,j.nom FROM `mkchats` c INNER JOIN `mkjoueurs` j ON c.sender=j.id WHERE receiver="'. $id .'" AND seen=0');
while ($getMsg = mysql_fetch_array($msgs)) {
	echo '<p>['. $getMsg['date'] .'] <a href="messages.php?id1='. $id .'&amp;id2='. $getMsg['sender'] .'">';
	echo $getMsg['nom'];
	echo '</a> : ';
	echo nl2br(htmlspecialchars($getMsg['message']));
	echo '</p>';
}
mysql_close();
?>
</body>
</html>