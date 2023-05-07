<?php
include('language.php');
include('session.php');
include('initdb.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>	">
<head>
<title><?php echo $language ? 'Topic deleted':'Suppression'; ?></title>
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
<h1><?php echo $language ? 'Topic deleted':'Suppression'; ?></h1>
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
if (isset($_GET['topic'])) {
	$getBanned = mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"');
	if (($banned=mysql_fetch_array($getBanned)) && $banned['banned'])
		include('ban_msg.php');
	else {
		require_once('getRights.php');
		if (!isset($_SESSION['csrf']) || !isset($_GET['token']) || ($_SESSION['csrf'] != $_GET['token'])) {
			echo '<p style="text-align: center">'. ($language ? 'Invalid token, please try again' : 'Token invalide, veuillez réessayer') .'</p>';
		}
		else {
			$topicId = intval($_GET['topic']);
			if ($firstMessage = mysql_fetch_array(mysql_query('SELECT auteur FROM `mkmessages` WHERE topic="'. $topicId .'" ORDER BY id LIMIT 1'))) {
				if (($firstMessage['auteur'] == $id) || hasRight('moderator')) {
					if ($firstMessage['auteur'] != $id)
						mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Suppr '. $topicId .'")');
					$allMsgs = mysql_query('SELECT auteur,COUNT(auteur) AS nb FROM `mkmessages` WHERE topic="'. $topicId .'" GROUP BY auteur');
					while ($msg = mysql_fetch_array($allMsgs))
						mysql_query('UPDATE `mkprofiles` SET nbmessages=nbmessages-'.$msg['nb'].' WHERE id="'.$msg['auteur'].'"');
					$getCat = mysql_fetch_array(mysql_query('SELECT category FROM `mktopics` WHERE id="'. $topicId .'"'));
					mysql_query('DELETE FROM `mkfollowers` WHERE topic="'. $topicId .'"');
					mysql_query('DELETE FROM `mkmessages` WHERE topic="'. $topicId .'"');
					mysql_query('DELETE FROM `mktopics` WHERE id="'. $topicId .'"');
					mysql_query('DELETE FROM `mkreactions` WHERE type="topic" AND link LIKE "'. $topicId .',%"');
					mysql_query('DELETE FROM `mkreports` WHERE type="topic" AND link LIKE "'. $topicId .',%"');
					mysql_query('DELETE FROM `mkreportshist` WHERE type="topic" AND link LIKE "'. $topicId .',%"');
					echo $language ? '<p id="successSent">Topic deleted successfully<br />
					<a href="category.php?category='. $getCat['category'] .'">Click here</a> to return to the category.<br />
					<a href="forum.php">Click here</a> to return to the forum.</p>':
					'<p id="successSent">Topic supprim&eacute; avec succ&egrave;s<br />
					<a href="category.php?category='. $getCat['category'] .'">Cliquez ici</a> pour retourner à la catégorie.<br />
					<a href="forum.php">Cliquez ici</a> pour retourner au forum.</p>';
				}
				else
					echo '<p style="text-align: center">'. ($language ? 'Error while deleting topic.':'Erreur lors de la suppression du topic.') .'</p>';
			}
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