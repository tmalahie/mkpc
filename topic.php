<?php
include('getId.php');
include('language.php');
include('session.php');
include('initdb.php');
require_once('getRights.php');
$isModerator = hasRight('moderator');
$topic = mysql_fetch_array(mysql_query('SELECT titre,category,private,locked FROM `mktopics` WHERE id="'. $_GET['topic'] .'"'. (hasRight('manager') ? '':' AND !private')));
$titreTopic = $topic['titre'];
if ($getFirstMessage=mysql_fetch_array(mysql_query('SELECT auteur,message FROM `mkmessages` WHERE topic="'. $_GET['topic'] .'" AND id=1 LIMIT 1'))) {
	if ($topic) {
		include('utils-description.php');
		$hthumbnail = false;
		$hdescription = removeBbCode($getFirstMessage['message']);
	}
	function pageLink($page, $this) {
		return ($this ? ' '.$page.'&nbsp;' : '<a href="?topic='. $_GET['topic'] .'&amp;page='.$page.'"> '.$page.'&nbsp;</a>');
	}
	$messages = mysql_query('SELECT auteur,id,message,date
		FROM `mkmessages` WHERE topic="'. $_GET['topic'] .'" ORDER BY id');
	$nbMsgs = 20;
	$pagesDiv = '<div class="topicPages"><p>Page :&nbsp;';
	if (isset($_GET['page']))
		$cPage = $_GET['page'];
	elseif (isset($_GET['message']))
		$cPage = ceil(mysql_numrows(mysql_query('SELECT * FROM `mkmessages` WHERE topic="'. $_GET['topic'] .'" AND id<="'. $_GET['message'] .'"'))/$nbMsgs);
	else
		$cPage = 1;
	$nbPages = ceil(mysql_numrows($messages)/$nbMsgs);
	$intervalle = 3;
	if ($nbPages <= ($intervalle*2+2)) {
		for ($i=1;$i<=$nbPages;$i++)
			$pagesDiv .= pageLink($i, ($cPage==$i));
	}
	else {
		$debut = $cPage-$intervalle;
		if ($debut <= 1)
			$debut = 1;
		else {
			$pagesDiv .= pageLink(1, false);
			if ($debut != 2)
				$pagesDiv .= ' ...&nbsp;';
		}
		$fin = $debut + $intervalle*2;
		if ($fin > $nbPages) {
			$fin = $nbPages;
			$debut = $fin-$intervalle*2;
		}
		for ($i=$debut;$i<=$fin;$i++)
			$pagesDiv .= pageLink($i, $i==$cPage);
		if ($fin < $nbPages) {
			if ($fin != ($nbPages-1))
				$pagesDiv .= ' ...&nbsp; ';
			$pagesDiv .= pageLink($nbPages, false);
		}
	}
	$pagesDiv .= '</p></div>';
	$fin = $cPage*$nbMsgs;
	$debut = $fin-$nbMsgs;
	$pageMessages = array();
	for ($i=0;$message=mysql_fetch_array($messages);$i++) {
		if ($i >= $debut) {
			if ($i < $fin) {
				$message['message'] = $message['message'];
				$pageMessages[] = $message;
			}
			else
				break;
		}
	}

	$minId = $pageMessages[0]['id'];
	$maxId = end($pageMessages)['id'];

	include('category_fields.php');
	$categoryID = $topic['category'];
	$category = mysql_fetch_array(mysql_query('SELECT '. $categoryFields .' FROM `mkcategories` WHERE id="'. $categoryID .'"'));
	include('tokens.php');
	assign_token();
	$isFollower = mysql_numrows(mysql_query('SELECT * FROM mkfollowers WHERE user="'. $id .'" AND topic="'. $_GET['topic'] .'"'));
	$getFollowers = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkfollowers WHERE topic="'. $_GET['topic'] .'" AND user!="'. $getFirstMessage['auteur'] .'"'));
	if ($isFollower)
		mysql_query('DELETE FROM `mknotifs` WHERE user="'. $id .'" AND type="answer_forum" AND link LIKE "'. $_GET['topic'] .',%"');
	mysql_query('DELETE FROM `mknotifs` WHERE user="'. $id .'" AND type="follower_topic" AND link="'. $_GET['topic'] .'"');
	mysql_query('DELETE FROM `mknotifs` WHERE user="'. $id .'" AND type="new_followtopic" AND link LIKE "'. $_GET['topic'] .',%"');
	$topicNotifs = mysql_query('SELECT id,link FROM `mknotifs` WHERE user="'. $id .'" AND (type="forum_mention" OR type="forum_quote") AND link LIKE "'. $_GET['topic'] .',%"');
	while ($topicNotif=mysql_fetch_array($topicNotifs)) {
		$linkData = explode(',', $topicNotif['link']);
		$msgId = $linkData[1];
		if (($msgId >= $minId) && ($msgId <= $maxId))
			mysql_query('DELETE FROM `mknotifs` WHERE id="'. $topicNotif['id'] .'"');
	}
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $topic ? htmlspecialchars($titreTopic).' - Forum MKPC':'Forum Mario Kart PC'; ?></title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/profil.css" />
<script type="text/javascript" src="scripts/topic.js"></script>
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
	<?php
	if ($topic) {
		?>
<h1><?php echo htmlspecialchars($titreTopic); ?></h1>
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
if ($topic['private'])
 echo '<p class="success">Ce topic est visible uniquement par les administrateurs<br />&nbsp;</p>';
if ($topic['locked'])
 echo '<p class="warning">'. ($language ? 'This topic has been locked':'Ce topic a été locké, vous ne pouvez plus y poster de message.') .'</p>';
?>
		<?php
		if ($id) {
			?>
			<div class="forumButtons">
				<?php
				if (!$topic['locked']) {
					?>
				<div class="buttonGroup">
					<a href="repondre.php?topic=<?php echo $_GET['topic']; ?>" class="action_button"><?php echo $language ? 'Reply':'Répondre'; ?></a>
				</div>
					<?php
				}
				?>
				<div class="buttonGroup">
					<label class="topic_follower" title="<?php echo $language ? 'Receive a notification when a new message is posted':'Recevoir une notification quand un nouveau message est posté'; ?>">
						<span class="topic_follow">
							<input type="checkbox" class="follow_topic_checkbox" name="follow_topic"<?php echo $isFollower ? ' checked="checked"':''; ?> onchange="followTopic(<?php echo urlencode($_GET['topic']); ?>, this.checked, <?php echo ($getFirstMessage['auteur']==$id)?'true':'false'; ?>)" />
							<?php echo $language ? 'Follow the topic' : 'Suivre le topic'; ?>
						</span>
						<span class="nb_followers"><?php echo $getFollowers['nb']; ?></span>
					</label>
				</div>
				<?php
				$newLockValue = null;
				if ($isModerator || ($getFirstMessage['auteur']==$id && $topic['locked']!=1)) {
					switch ($topic['locked']) {
					case 0:
						$newLockValue = ($getFirstMessage['auteur'] == $id) ? 2:1;
						break;
					case 1:
					case 2:
						$newLockValue = 0;
						break;
					}
				}
				if (null !== $newLockValue) {
					$lockunlock = ($language ? ($newLockValue?'Lock':'Unlock'):($newLockValue?'Locker':'Unlocker'));
					?>
				<div class="buttonGroup">
					<a href="lock.php?topic=<?php echo $_GET['topic']; ?>&amp;value=<?php echo $newLockValue; ?>" class="action_button lock_topic" onclick="return confirm(this.innerHTML+' ?')"><?php echo $lockunlock . ($language ? ' topic':' le topic'); ?></a>
				</div>
					<?php
				}
				?>
			</div>
			<?php
		}
		echo $pagesDiv;
		include('bbCode.php');
		include('avatars.php');
		echo '<div id="fMessages">';
		foreach ($pageMessages as $message)
			print_forum_msg($message,($message['auteur']==$id || $isModerator),!$topic['locked']);
		echo '</div>';
		echo $pagesDiv;
		?>
	<p class="forumButtons"><?php
	if ($id) {
		?>
		<div class="forumButtons">
			<?php
			if (!$topic['locked']) {
				?>
			<div class="buttonGroup">
				<a href="repondre.php?topic=<?php echo $_GET['topic']; ?>" class="action_button"><?php echo $language ? 'Reply':'Répondre'; ?></a>
			</div>
				<?php
			}
			?>
			<div class="buttonGroup">
				<label class="topic_follower" title="<?php echo $language ? 'Receive a notification when a new message is posted':'Recevoir une notification quand un nouveau message est posté'; ?>">
					<span class="topic_follow">
						<input type="checkbox" class="follow_topic_checkbox" name="follow_topic"<?php echo $isFollower ? ' checked="checked"':''; ?> onchange="followTopic(<?php echo urlencode($_GET['topic']); ?>, this.checked, <?php echo ($getFirstMessage['auteur']==$id)?'true':'false'; ?>)" /> <?php echo $language ? 'Follow the topic' : 'Suivre le topic'; ?>
					</span>
					<span class="nb_followers"><?php echo $getFollowers['nb']; ?></span>
				</label>
			</div>
			<?php
			if (null !== $newLockValue) {
				?>
			<div class="buttonGroup">
				<a href="lock.php?topic=<?php echo $_GET['topic']; ?>&amp;value=<?php echo $newLockValue; ?>" class="action_button lock_topic" onclick="return confirm(this.innerHTML+' ?')"><?php echo $lockunlock . ($language ? ' topic':' le topic'); ?></a>
			</div>
				<?php
			}
			?>
		</div>
		<?php
	}
	?>
	<a href="category.php?category=<?php echo $categoryID; ?>"><?php echo $language ? 'Back to '. $category['nom']:'Retour à '. $category['nom']; ?></a><br />
	<a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a>
	</p>
	<?php
	}
	else
		echo $language ? 'This topic doesn\'t exist or has been deleted.':'Ce topic n\'existe pas ou plus.';
	mysql_close();
	?>
</main>
<?php
include('footer.php');
?>
<script type="text/javascript">
function confirmSuppr() {
	return confirm("<?php echo $language ? 'Are you sure you want to delete this message ?':'Voulez-vous vraiment supprimer ce message ?'; ?>");
}
</script>
</body>
</html>