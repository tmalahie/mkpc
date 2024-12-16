<?php
if (isset($_GET['category'])) {
	include('../includes/getId.php');
	include('../includes/language.php');
	include('../includes/session.php');
	include('../includes/initdb.php');
	$categoryID = intval($_GET['category']);
	include('../includes/category_fields.php');
	require_once('../includes/getRights.php');
	if ($category = mysql_fetch_array(mysql_query('SELECT '. $categoryFields .' FROM `mkcategories` WHERE id="'. $categoryID .'"'.(hasRight('manager') ? '':' AND adminonly=0')))) {
		?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?= _('Mario Kart PC Forum') ?></title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css?reload=2" />
<link rel="stylesheet" type="text/css" href="styles/profil.css" />

<?php
include('../includes/o_online.php');
?>
</head>
<body onbeforeunload="if(document.forms[0].message.value&amp;&amp;!document.forms[0].querySelector('[type=submit]').disabled)return '<?php echo addslashes($language ? 'Warning, the message you\'re writing won\'t be saved':'Attention, le message que vous êtes en train d\'écrire ne sera pas sauvegardé'); ?>'">
<?php
include('../includes/header.php');
$page = 'forum';
include('../includes/menu.php');
?>
<main>
<?php
if ($id) {
	include('../includes/smileys.php');
	?>
<h1><?php echo $language ? 'New topic':'Nouveau topic'; ?></h1>
<?php
require_once('../includes/utils-ads.php');
showRegularAdSection();
?>
	<?php
	$showForm = false;
	$showNavLinks = true;

	$banned = mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"'));
	if ($banned['banned'])
		include('../includes/ban_msg.php');
	elseif (isset($_POST['titre']) && isset($_POST['message']) && trim($_POST['titre']) && trim($_POST['message'])) {
		include('../includes/utils-cooldown.php');
		require_once('../includes/forum-checks.php');
		if (isMsgCooldowned(array('newtopic' => 1))) {
			logCooldownEvent('forum_topic');
			printMsgCooldowned();
		}
		elseif (isNewUserCooldowned($id)) {
			printNewUserCooldowned();
			$showForm = true;
		}
		elseif (($checks=checkMessageContent($_POST['message'])) && !$checks['success']) {
			printCheckFailDetails($checks);
			$showForm = true;
		}
		else {
			include('../includes/idempotency.php');
			$topicId = withRequestIdempotency(array(
				'is_cache_stale' => function($topicId) {
					return !mysql_numrows(mysql_query('SELECT * FROM `mktopics` WHERE id="'.$topicId.'"'));
				},
				'callback' => function() use($id, $categoryID, $language) {
					$private = (isset($_POST['admin']) && hasRight('manager')) ? 1:0;
					mysql_query('INSERT INTO `mktopics` VALUES(NULL, "'. $_POST['titre'] .'","'. $categoryID .'",'. $language .','.$private.',0,1,NULL)');
					$iGenerated = mysql_insert_id();
					mysql_query('INSERT INTO `mkmessages` VALUES(1, '. $iGenerated .', "'.$id.'", NULL, "'. $_POST['message'] .'")');
					mysql_query('UPDATE `mkprofiles` SET nbmessages=nbmessages+1,last_connect=NULL WHERE id="'.$id.'"');
					mysql_query('INSERT INTO `mkfollowers` VALUES("'. $id .'","'. $iGenerated .'")');
					$getFollowers = mysql_query('SELECT follower FROM `mkfollowusers` WHERE followed="'. $id .'"');
					while ($follower = mysql_fetch_array($getFollowers))
						mysql_query('INSERT INTO `mknotifs` SET type="follower_topic", user="'. $follower['follower'] .'", link="'.$iGenerated.'"');
					preg_match_all('#\B@([a-zA-Z0-9\-_]+?)#isU', stripcslashes($_POST['message']), $mentions);
					foreach ($mentions[1] as $pseudo) {
						$getMids = mysql_query('SELECT id FROM `mkjoueurs` WHERE id!='. $id .' AND nom="'. $pseudo .'"');
						if ($getMid=mysql_fetch_array($getMids))
							mysql_query('INSERT INTO `mknotifs`  SET type="forum_mention", user="'. $getMid['id'] .'", link="'.$iGenerated.',1"');
					}
					preg_match_all('#\[quote=(.+)\].*\[\/quote\]#isU', stripcslashes($_POST['message']), $quotes);
					foreach ($quotes[1] as $pseudo) {
						$getMids = mysql_query('SELECT id FROM `mkjoueurs` WHERE id!='. $id .' AND nom="'. $pseudo .'"');
						if ($getMid=mysql_fetch_array($getMids))
							mysql_query('INSERT INTO `mknotifs`  SET type="forum_quote", user="'. $getMid['id'] .'", link="'.$iGenerated.',1"');
					}
					return $iGenerated;
				}
			));
			echo $language ? '<p id="successSent">Message sent successfully<br />
			<a href="topic.php?topic='. $topicId .'">Click here</a> to go to the topic.<br />
			<a href="category.php?category='. $categoryID .'">Click here</a> to return to the category.<br />
			<a href="forum.php">Click here</a> to return to the forum.</p>' :
			'<p id="successSent">Message envoy&eacute; avec succ&egrave;s<br />
			<a href="topic.php?topic='. $topicId .'">Cliquez ici</a> pour acc&eacute;der au topic.<br />
			<a href="category.php?category='. $categoryID .'">Cliquez ici</a> pour retourner à la catégorie.<br />
			<a href="forum.php">Cliquez ici</a> pour retourner au forum.</p>';
		}
	}
	else {
		$showForm = true;
	}
	if ($showForm) {
		include('../includes/utils-moderation.php');
		printForumReplyNotices();
	?>
<form method="post" action="newtopic.php?category=<?php echo $categoryID; ?>" onsubmit="this.querySelector('[type=submit]').disabled=true">
<table id="nMessage">
<tr><td class="mLabel"><label for="titre"><?php echo $language ? 'Title':'Titre'; ?> :</label></td>
<td class="mInput"><input type="text" id="titre" name="titre"<?php if (isset($_POST['titre'])) echo ' value="'. htmlspecialchars($_POST['titre']) .'"'; ?> required /></td></tr>
<tr><td class="mLabel">BBcode :<br /><a href="javascript:helpBbCode()"><?php echo $language ? 'Help':'Aide'; ?></a></td><td><?php include('../includes/bbButtons.php'); ?></td></tr>
<tr><td class="mLabel"><p><label for="message">Message :</label></p>
<p><?php
for ($i=0;$i<$nbSmileys;$i++)
	echo ' <a href="javascript:ajouter(\''. $smileys[$i] .'\')"><img src="images/smileys/smiley'. $i .'.png" alt="'. $smileys[$i] .'" /></a> ';
?>
<a href="javascript:moresmileys()" id="more-smileys"><?php echo $language ? 'More smileys':'Plus de smileys'; ?></a></p>
</td><td class="mInput"><textarea name="message" id="message" rows="10" required><?php if (isset($_POST['message'])) echo htmlspecialchars($_POST['message']); ?></textarea></td></tr>
<?php
if (hasRight('manager')) {
	?>
	<tr>
	 <td class="mLabel"><input type="checkbox" id="admin_cb" name="admin" /></td>
	 <td class="mInput">
	  <label for="admin_cb"><?php echo ($language ? 'Admin only':'Visible uniquement par les admins'); ?></label>
	 </td>
	</tr>
	<?php
}
?>
<tr><td colspan="2" class="mLabel"><input type="button" value="<?php echo $language ? 'Preview':'Aper&ccedil;u'; ?>" onclick="apercu()" /> &nbsp; <input type="submit" value="<?php echo $language ? 'Send':'Envoyer'; ?>" /></td></tr>
</table>
</form>
	<?php
	include('../includes/preview-msg.php');
}
if ($showNavLinks) {
	?>
<p class="forumButtons" style="margin: 10px 0 0 23%">
	<a href="category.php?category=<?php echo $categoryID; ?>"><?php echo $language ? 'Back to '. $category['nom']:'Retour à '. $category['nom']; ?></a><br />
	<a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a>
</p>
	<?php
	}
}
else
	include('../includes/needCo.php');
?>
</main>
<?php
include('../includes/footer.php');
mysql_close();
?>
<script type="text/javascript" src="scripts/msg.php"></script>
</body>
</html>
		<?php
	}
}
?>