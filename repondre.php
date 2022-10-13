<?php
include('getId.php');
include('language.php');
include('session.php');
if (isset($_GET['topic'])) {
	?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>Forum Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/profil.css" />

<?php
include('o_online.php');
?>
</head>
<body onbeforeunload="if(document.forms[0].message.value&amp;&amp;!document.forms[0].querySelector('[type=submit]').disabled)return '<?php echo addslashes($language ? 'Warning, the message you\'re writing won\'t be saved':'Attention, le message que vous êtes en train d\'écrire ne sera pas sauvegardé'); ?>'">
<?php
include('header.php');
?>
<?php
$page = 'forum';
include('initdb.php');
include('menu.php');
?>
<main>
	<?php
	if ($id) {
		include('smileys.php');
		?>
<h1><?php echo $language ? 'Reply':'R&eacute;pondre'; ?></h1>
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
		require_once('getRights.php');
		include('category_fields.php');
		$getTopic = mysql_fetch_array(mysql_query('SELECT * FROM `mktopics` WHERE id="'. $_GET['topic'] .'" AND !locked'. (hasRight('manager') ? '':' AND !private')));
		if (!$getTopic)
			echo $language ? '<p style="text-align: center">This subject doesn\'t exist or has been deleted.':'Ce sujet n\'existe pas ou plus.</p>';
		else {
			$category = mysql_fetch_array(mysql_query('SELECT id,'. $categoryFields .',adminonly FROM `mkcategories` WHERE id="'. $getTopic['category'] .'"'));
			$getBanned = mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"');
			if (($banned=mysql_fetch_array($getBanned)) && $banned['banned'])
				include('ban_msg.php');
			elseif (isset($_POST['message'])) {
				$getAI = mysql_fetch_array(mysql_query('SELECT id FROM `mkmessages` WHERE topic="'. $_GET['topic'] .'" ORDER BY id DESC LIMIT 1'));
				$ainc = $getAI['id']+1;
				$maxiter = 10;
				while (!mysql_query('INSERT INTO `mkmessages` VALUES('. $ainc .', "'. $_GET['topic'] .'", "'.$id.'", NULL, "'. $_POST['message'] .'")')) {
					$ainc++;
					$maxiter--;
					if (!$maxiter)
						die('Unknown error');
				}
				mysql_query('UPDATE `mkprofiles` SET nbmessages=nbmessages+1,last_connect=NULL WHERE id="'.$id.'"');
				$getLastMessage = mysql_fetch_array(mysql_query('SELECT date FROM `mkmessages` WHERE topic="'. $_GET['topic'] .'" ORDER BY id DESC limit 1'));
				mysql_query('UPDATE `mktopics` SET dernier="'.$getLastMessage['date'].'",nbmsgs=nbmsgs+1 WHERE id="'. $_GET['topic'] .'"');
				$getFollowers = mysql_query('SELECT * FROM `mkfollowers` WHERE topic="'. $_GET['topic'] .'" AND user!="'. $id .'"');
				while ($follower = mysql_fetch_array($getFollowers))
					mysql_query('INSERT INTO `mknotifs` SET type="answer_forum", user="'. $follower['user'] .'", link="'.$_GET['topic'].','. ($ainc) .'"');
				preg_match_all('#\B@([a-zA-Z0-9\-_]+?)#isU', stripcslashes($_POST['message']), $mentions);
				foreach ($mentions[1] as $pseudo) {
					$getMids = mysql_query('SELECT id FROM `mkjoueurs` WHERE id!='. $id .' AND nom="'. $pseudo .'"');
					if ($getMid=mysql_fetch_array($getMids))
						mysql_query('INSERT INTO `mknotifs`  SET type="forum_mention", user="'. $getMid['id'] .'", link="'.$_GET['topic'].','. ($ainc) .'"');
				}
				preg_match_all('#\[quote=(.+)\].*\[\/quote\]#isU', stripcslashes($_POST['message']), $quotes);
				foreach ($quotes[1] as $pseudo) {
					$getMids = mysql_query('SELECT id FROM `mkjoueurs` WHERE id!='. $id .' AND nom="'. $pseudo .'"');
					if ($getMid=mysql_fetch_array($getMids))
						mysql_query('INSERT INTO `mknotifs`  SET type="forum_quote", user="'. $getMid['id'] .'", link="'.$_GET['topic'].','. ($ainc) .'"');
				}
				echo $language ? '<p id="successSent">Message sent successfully<br />
				<a href="topic.php?topic='. $_GET['topic'].'&amp;page='. ceil(mysql_numrows(mysql_query('SELECT * FROM `mkmessages` WHERE topic='. $_GET['topic']))/20) .'">Click here</a> to go to the topic.<br />
				<a href="category.php?category='. $category['id'] .'">Click here</a> to return to the category.<br />
				<a href="forum.php">Click here</a> to return to the forum.</p>' :
				'<p id="successSent">Message envoy&eacute; avec succ&egrave;s<br />
				<a href="topic.php?topic='. $_GET['topic'] .'&amp;page='. ceil(mysql_numrows(mysql_query('SELECT * FROM `mkmessages` WHERE topic='. $_GET['topic']))/20) .'">Cliquez ici</a> pour acc&eacute;der au topic.<br />
				<a href="category.php?category='. $category['id'] .'">Cliquez ici</a> pour retourner à la catégorie.<br />
				<a href="forum.php">Cliquez ici</a> pour retourner au forum.</p>';
			}
			else {
				function isOldTopic(&$topic) {
					global $id;
					$getFirstMessage = mysql_fetch_array(mysql_query('SELECT auteur FROM `mkmessages` WHERE topic="'. $topic['id'] .'" AND id=1 LIMIT 1'));
					if ($getFirstMessage['auteur'] == $id) return false;
					return strtotime($topic['dernier']) < (time() - 90*24*3600);
				}
				if (!isset($_GET['force']) && !$category['adminonly'] && isOldTopic($getTopic)) {
				?>
				<p style="text-align: center">
				<?php
				include('utils-date.php');
				if ($language) {
					?>
					It looks like you're trying to reply to an old topic.
					The last message has been posted on <?php echo to_local_tz($getTopic['dernier'], 'Y-m-d'); ?>.<br />
					It's generally a bad idea to revive old topics, you might want to create a
					<a href="newtopic.php?category=<?php echo $getTopic['category']; ?>">new topic</a> instead.<br />
					In case of doubt, please read the <a href="topic.php?topic=2448">forum's rules</a> topic.
					<?php
				}
				else {
					?>
					Il semblerait que vous essayez de répondre à un ancien topic.
					Le dernier message remonte au <?php echo to_local_tz($getTopic['dernier'], 'd/m/Y'); ?>.<br />
					C'est généralement une mauvaise idée de déterrer des vieux topics, il est peut-être préférable de créer un
					<a href="newtopic.php?category=<?php echo $getTopic['category']; ?>">nouveau topic</a> à la place.<br />
					En cas de doute, réferrez-vous au <a href="topic.php?topic=2448">règlement</a> du forum.
					<?php
				}
				?>
				</p>
				<p style="text-align: center; margin-bottom:2em">
				<?php
				if ($language) {
					?>
					If you think it's still appropriate to reply to this topic, <a href="?<?php echo http_build_query($_GET); ?>&amp;force">click here</a>.
					<?php
				}
				else {
					?>
					Si vous pensez toujours que répondre à ce topic est approprié dans ce contexte, <a href="?<?php echo http_build_query($_GET); ?>&amp;force">cliquez ici</a>.
					<?php
				}
				?>
				</p>
				<?php
				}
				else {
			?>
<form method="post" action="repondre.php?topic=<?php echo $_GET['topic']; ?>" onsubmit="if(!this.message.value){alert('<?php echo $language ? 'Please enter a message':'Veuillez entrer un message'; ?>');return false}this.querySelector('[type=submit]').disabled=true">
<table id="nMessage">
<tr><td class="mLabel">BBcode :<br /><a href="javascript:helpBbCode()"><?php echo $language ? 'Help':'Aide'; ?></a></td><td><?php include('bbButtons.php'); ?></td></tr>
<tr><td class="mLabel"><p><label for="message">Message :</label></p>
<p><?php
for ($i=0;$i<$nbSmileys;$i++)
	echo ' <a href="javascript:ajouter(\''. $smileys[$i] .'\')"><img src="images/smileys/smiley'. $i .'.png" alt="'. $smileys[$i] .'" /></a> ';
?>
<a href="javascript:moresmileys()" id="more-smileys"><?php echo $language ? 'More smileys':'Plus de smileys'; ?></a></p>
</td><td class="mInput"><textarea name="message" id="message" rows="10"><?php
if (isset($_GET['quote'])) {
	if ($getMessage = mysql_fetch_array(mysql_query('SELECT j.nom,m.message FROM `mkmessages` m LEFT JOIN `mkjoueurs` j ON m.auteur=j.id WHERE m.id="'. $_GET['quote'] .'" AND m.topic="'. $_GET['topic'] .'"')))
		echo '[quote'. ($getMessage['nom'] ? '='.$getMessage['nom']:'') .']' . htmlspecialchars($getMessage['message']) . '[/quote]'."\n";
}
?></textarea></td></tr>
<tr><td colspan="2" class="mLabel"><input type="button" value="<?php echo $language ? 'Preview':'Aper&ccedil;u'; ?>" onclick="apercu()" /> &nbsp; <input type="submit" value="<?php echo $language ? 'Send':'Envoyer'; ?>" /></td></tr>
</table>
</form>
					<?php
					include('preview-msg.php');
				}
				?>
				<p class="forumButtons">
				<a href="topic.php?topic=<?php echo $_GET['topic']; ?>"><?php echo $language ? 'Back to the topic':'Retour au topic'; ?></a><br />
				<a href="category.php?category=<?php echo $category['id']; ?>"><?php echo $language ? 'Back to '. $category['nom']:'Retour à '. $category['nom']; ?></a><br />
				<a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a>
				</p>
				<?php
			}
		}
	}
	else
		include('needCo.php');
?>
</main>
<?php
mysql_close();
include('footer.php');
?>
<script type="text/javascript" src="scripts/msg.php"></script>
</body>
</html>
	<?php
}
?>