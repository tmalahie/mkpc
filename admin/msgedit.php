<?php
if (isset($_GET['id'])) {
	include('auth.php');
	$msgId = $_GET['id'];
	$message = mysql_fetch_array(mysql_query('SELECT message,sender,receiver,seen FROM mkchats WHERE id="'. $msgId .'"'));
	if ($message && ($message['sender']==$id)) {
		if (isset($_POST['message'])) {
			mysql_query('UPDATE `mkchats` SET message="'. $_POST['message'] .'" WHERE id="'. $msgId .'"');
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
			<?php
			if ($message['seen'])
				echo '<strong style="color:#C00">Attention, le message a déjà été vu</strong><br />';
			?>
			<form method="post">
				<textarea cols="40" rows="3" name="message" placeholder="Modifier..." required="required"><?php
					echo htmlspecialchars($message['message']);
				?></textarea><br />
				<input type="submit" value="Envoyer" /> &nbsp; - &nbsp;
				<a href="messages.php?id1=<?php echo $message['sender']; ?>&amp;id2=<?php echo $message['receiver']; ?>">Retour</a>
			</form>
		</body>
		</html>
		<?php
	}
}
?>