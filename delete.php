<?php
include('language.php');
include('session.php');
include('initdb.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Deletion':'Suppression'; ?></title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />

<?php
include('o_online.php');
?>
</head>
<body>
<?php
include('header.php');
$page = 'forum';
include('menu.php');
?>
<main>
<h1><?php echo $language ? 'Deletion':'Suppression'; ?></h1>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Forum MKPC -->
<p class="pub"><ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-1340724283777764"
     data-ad-slot="4919860724"></ins></p>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
<?php
if (isset($_GET['id']) && isset($_GET['topic']) && ($_GET['id'] > 1)) {
	$getBanned = mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"');
	if (($banned=mysql_fetch_array($getBanned)) && $banned['banned'])
		include('ban_msg.php');
	else {
		if (!isset($_SESSION['csrf']) || !isset($_GET['token']) || ($_SESSION['csrf'] != $_GET['token'])) {
			echo '<p style="text-align: center">'. ($language ? 'Invalid token, please try again' : 'Token invalide, veuillez réessayer') .'</p>';
		}
		else {
			require_once('getRights.php');
			$msg = mysql_fetch_array(mysql_query('SELECT * FROM `mkmessages` WHERE id="'. $_GET['id'] .'" AND topic="'. $_GET['topic'] .'"'));
			$q = mysql_query('DELETE FROM `mkmessages` WHERE id="'. $_GET['id'] .'" AND topic="'. $_GET['topic'] .'"'. (hasRight('moderator') ? '':' AND auteur="'. $id .'"'));
			if (mysql_affected_rows($q)) {
				if (hasRight('moderator'))
					mysql_query('INSERT INTO `mklogs` VALUES(NULL, '. $id .', "Suppr '. $_GET['topic'] .' '. $_GET['id'] .'")');
				$getLastMessage = mysql_fetch_array(mysql_query('SELECT date FROM `mkmessages` WHERE topic="'. $_GET['topic'] .'" ORDER BY id DESC limit 1'));
				mysql_query('UPDATE `mktopics` SET dernier="'.$getLastMessage['date'].'",nbmsgs=nbmsgs-1 WHERE id="'. $_GET['topic'] .'"');
				$getCat = mysql_fetch_array(mysql_query('SELECT category FROM `mktopics` WHERE id="'. $_GET['topic'] .'"'));
				mysql_query('UPDATE `mkprofiles` SET nbmessages=nbmessages-1 WHERE id="'.$msg['auteur'].'"');
				echo $language ? '<p id="successSent">Message deleted successfully<br />
				<a href="topic.php?topic='. $_GET['topic'] .'&amp;page='. ceil(mysql_numrows(mysql_query('SELECT * FROM `mkmessages` WHERE topic="'. $_GET['topic'] .'" AND id<'. $_GET['id']))/20) .'">Click here</a> to go to the topic.<br />
				<a href="category.php?category='. $getCat['category'] .'">Click here</a> to return to the category.<br />
				<a href="forum.php">Click here</a> to return to the forum.</p>':
				'<p id="successSent">Message supprim&eacute; avec succ&egrave;s<br />
				<a href="topic.php?topic='. $_GET['topic'] .'&amp;page='. ceil(mysql_numrows(mysql_query('SELECT * FROM `mkmessages` WHERE topic="'. $_GET['topic'] .'" AND id<'. $_GET['id']))/20) .'">Cliquez ici</a> pour acc&eacute;der au topic.<br />
				<a href="category.php?category='. $getCat['category'] .'">Cliquez ici</a> pour retourner à la catégorie.<br />
				<a href="forum.php">Cliquez ici</a> pour retourner au forum.</p>';
			}
			else
				echo '<p style="text-align: center">'. ($language ? 'Error while deleting message.':'Erreur lors de la suppression du message.') .'</p>';
		}
	}
}
?>
</main>
<?php
include('footer.php');
mysql_close();
?>
</body>
</html>