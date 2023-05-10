<?php
header('Content-Type: text/plain');
include('../includes/session.php');
if ($id && isset($_POST['message'])) {
	include('../includes/initdb.php');
	if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $id .'"'))) {
		$pseudo = $getPseudo['nom'];
		$message = htmlspecialchars($_POST["message"]);
		$message = str_replace('%u20AC', '&euro;', $message);
		if ($message != null) {
			$message = nl2br($message);
			$message = str_replace("\n", null, $message);
			$message = str_replace("\r\n", null, $message);
			$message = str_replace("\r", null, $message);
			$message = str_replace("\t", "    ", $message);
			$message = preg_replace('#[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}#', '<a href="mailto:$0">$0</a>', $message);
			$message = preg_replace('#((https?|ftp|gopher|telnet|ms-help)://[\w\d:\#@%/;$()~_?\+\-=\\\.&]*)#', '<a href="$0" target="_blank" rel="noopener noreferrer">$0</a>', $message);
			$smileys = Array(':)', ':d', ';)', ':o', ':p', ':s', ':(', '8)', ':$', ':}', '|)', '*[');
			$styles = explode(",", $donnees["style"]);
			$taille = isset($_POST["taille"]) ? $_POST["taille"] : 10;
			$police = isset($_POST["police"]) ? $_POST["police"] : 'Arial';
			for ($i=0;$i<12;$i++) {
				$smiley = $smileys[$i];
				$message = str_replace(strtoupper($smiley), $smiley, $message);
				$message = str_replace($smiley, '<img src="images/smileys/smiley'.$i.'.png" alt="'. $smiley .'" style="width: '.$taille.'pt" />', $message);
			}
			$message = str_replace("  ", "&nbsp; ", $message);
			$styleMore = '';
			$colorMore = '';
			$styles = Array('gras', 'souligne', 'italique');
			for ($i=0;$i<3;$i++) {
				if (isset($_POST["s$i"]))
					$styleMore .= $styles[$i].',';
			}
			for ($i=0;$i<3;$i++)
				$colorMore .= ", '".(isset($_POST["c$i"]) ? $_POST["c$i"]:0)."'";
			mysql_query("INSERT INTO `minichat` VALUES (NULL,'$pseudo', '$message', '$styleMore'$colorMore, '$taille', '$police')");
			mysql_query("DELETE FROM `writting` WHERE pseudo='$pseudo'");
			mysql_query('INSERT INTO `writting` VALUES("'.$pseudo.'",'.time().',0)');
		}
		mysql_close();
		echo '1';
	}
}
?>
