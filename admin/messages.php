<?php
if (isset($_GET['id1']) && isset($_GET['id2'])) {
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
	$getPseudo1 = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $_GET['id1'] .'"'));
	$pseudo1 = $getPseudo1['nom'];
	$getPseudo2 = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $_GET['id2'] .'"'));
	$pseudo2 = $getPseudo2['nom'];
	echo '<h2>'. $getPseudo1['nom'].' -&gt; '. $getPseudo2['nom'].'</h2>';
	$withMe = false;
	if ($_GET['id1'] == $id) {
		$withMe = true;
		$other = $_GET['id2'];
	}
	elseif ($_GET['id2'] == $id) {
		$withMe = true;
		$other = $_GET['id1'];
	}
	if ($withMe) {
		mysql_query('UPDATE `mkchats` SET seen=1 WHERE sender="'. $other .'" AND receiver="'. $id .'"');
		if (isset($_POST['message']) && $_POST['message']) {
			$isIgnored = mysql_numrows(mysql_query('SELECT * FROM `mkignores` WHERE ignorer="'. $other .'" AND ignored="'. $id .'"'));
			$seen = $isIgnored ? 1 : 0;
			mysql_query('INSERT INTO `mkchats` VALUES(NULL,"'. $id .'","'. $other .'","'.$_POST['message'].'",NULL,'. $seen .')');
		}
		?>
		<form method="post">
			<textarea cols="40" rows="3" name="message" placeholder="R&eacute;pondre..." required="required"></textarea><br />
			<input type="submit" value="Envoyer" />
		</form>
		<?php
	}
	$msgs = mysql_query('SELECT id,sender,receiver,date,message,seen FROM `mkchats` WHERE (sender="'. $_GET['id1'] .'" AND receiver="'. $_GET['id2'] .'") OR (sender="'. $_GET['id2'] .'" AND receiver="'. $_GET['id1'] .'") ORDER BY id DESC'. (isset($_GET['all']) ? '':' LIMIT 200'));
	while ($getMsg = mysql_fetch_array($msgs)) {
		echo '<p>['. $getMsg['date'] .']<strong>';
		if ($getMsg['sender'] == $_GET['id1'])
			echo $pseudo1;
		else
			echo $pseudo2;
		echo '</strong> : ';
		echo nl2br(htmlspecialchars($getMsg['message']));
		echo ' ';
		echo '<em style="color:#AAA">';
		echo $getMsg['seen'] ? '&#10003;':'&times;';
		echo '</em>';
		if ($getMsg['sender'] == $id) {
			echo ' &nbsp; &nbsp; &nbsp; &nbsp; <span style="color:#AAA">[<a style="color:#3F9" href="msgedit.php?id='. $getMsg['id'] .'">&#x270E;</a>';
			echo ' - <a style="color:#F53" href="msgdel.php?id='. $getMsg['id'] .'">&times;</a>]';
		}
		echo '</p>';
	}
	mysql_close();
	?>
	</body>
	</html>
	<?php
}
?>