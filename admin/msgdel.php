<?php
if (isset($_GET['id'])) {
	include('auth.php');
	$msgId = $_GET['id'];
	$message = mysql_fetch_array(mysql_query('SELECT message,sender,receiver,seen FROM mkchats WHERE id="'. $msgId .'"'));
	if ($message && ($message['sender']==$id)) {
		if (isset($_GET['force'])) {
			mysql_query('DELETE FROM `mkchats` WHERE id="'. $msgId .'"');
			header('location: messages.php?id1='. $message['sender'] .'&id2='. $message['receiver']);
		}
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8" />
		<title>Admin MKPC</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		</head>
		<body>
			Supprimer le message &laquo; <em><?php echo htmlspecialchars($message['message']); ?></em> &raquo; ?<br />
			<?php
			if ($message['seen'])
				echo '<strong style="color:#C00">Attention, le message a déjà été vu</strong><br />';
			?>
			<a href="?id=<?php echo $_GET['id']; ?>&amp;force">Oui</a> - <a href="messages.php?id1=<?php echo $message['sender']; ?>&amp;id2=<?php echo $message['receiver']; ?>">Non</a>
		</body>
		</html>
		<?php
	}
}
?>