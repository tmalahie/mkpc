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
if (isset($_GET['id']) && is_numeric($_GET['id']))
	$id = $_GET['id'];
elseif (isset($_GET['pseudo']) && ($getId=mysql_fetch_array(mysql_query('SELECT * FROM `mkjoueurs` WHERE nom="'.$_GET['pseudo'].'"'))))
	$id = $getId['id'];
$chats = mysql_query(
	'SELECT t.last_id,t.other,j.nom,c.message,(c.seen OR c.sender='.$id.') AS seen,t.nbmsgs FROM (
		SELECT MAX(t.max_id) AS last_id,other,SUM(t.nb) AS nbmsgs FROM (
				SELECT receiver AS other,MAX(id) AS max_id,COUNT(*) AS nb FROM mkchats WHERE sender='.$id.' GROUP BY receiver
				UNION
				SELECT sender AS other,MAX(id) AS max_id,COUNT(*) AS nb FROM mkchats WHERE receiver='.$id.' GROUP BY sender
			) t
			GROUP BY other
		) t
		INNER JOIN mkchats c ON c.id=t.last_id INNER JOIN mkjoueurs j ON j.id=t.other
		HAVING(other NOT IN(SELECT ignored FROM `mkignores` WHERE ignorer='.$id.')
	)
	ORDER BY last_id DESC'
);
while ($chat = mysql_fetch_array($chats)) {
	echo '<a href="messages.php?id1='. $id .'&amp;id2='. $chat['other'] .'">';
	echo $chat['nom'];
	echo '</a> ['. $chat['nbmsgs'] .' message'. ($chat['nbmsgs']>1 ? 's':'') .']<br /><small>'. htmlspecialchars($chat['message']) .'</small><br />';
}
?>
</body>
</html>