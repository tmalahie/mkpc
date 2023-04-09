<?php
include('getId.php');
include('language.php');
include('session.php');
include('initdb.php');
require_once('getRights.php');
$isModerator = hasRight('moderator');
$topicId = isset($_GET['topic']) ? intval($_GET['topic']) : 0;
$topic = mysql_fetch_array(mysql_query('SELECT titre,category,private,locked FROM `mktopics` WHERE id="'. $topicId .'"'. (hasRight('manager') ? '':' AND !private')));
$titreTopic = isset($topic['titre']) ? $topic['titre'] : '';
if ($getFirstMessage=mysql_fetch_array(mysql_query('SELECT auteur,message FROM `mkmessages` WHERE topic="'. $topicId .'" AND id=1 LIMIT 1'))) {
	if ($topic) {
		include('utils-description.php');
		$hthumbnail = false;
		$hdescription = removeBbCode($getFirstMessage['message']);
	}
	function pageLink($page, $isCurrent) {
		global $topicId;
		return ($isCurrent ? ' '.$page.'&nbsp;' : '<a href="?topic='. $topicId .'&amp;page='.$page.'"> '.$page.'&nbsp;</a>');
	}
	$messages = mysql_query('SELECT auteur,id,message,date
		FROM `mkmessages` WHERE topic="'. $topicId .'" ORDER BY id');
	$nbMsgs = 20;
	if (isset($_GET['page']) && is_numeric($_GET['page']))
		$cPage = $_GET['page'];
	elseif (isset($_GET['message']) && is_numeric($_GET['message']))
		$cPage = ceil(mysql_numrows(mysql_query('SELECT * FROM `mkmessages` WHERE topic="'. $topicId .'" AND id<="'. $_GET['message'] .'"'))/$nbMsgs);
	else
		$cPage = 1;
	$nbPages = ceil(mysql_numrows($messages)/$nbMsgs);
	require_once('utils-paging.php');
	$allPages = makePaging($cPage,$nbPages);
	$pagesDiv = '<div class="topicPages"><p>Page :&nbsp;';
	foreach ($allPages as $i=>$block) {
		if ($i)
			$pagesDiv .= ' ...&nbsp; ';
		foreach ($block as $p)
			$pagesDiv .= pageLink($p, $p==$cPage);
	}
	$pagesDiv .= '</p></div>';
	$fin = $cPage*$nbMsgs;
	$debut = $fin-$nbMsgs;
	$pageMessages = array();
	for ($i=0;$message=mysql_fetch_array($messages);$i++) {
		if ($i >= $debut) {
			if ($i < $fin) {
				$message['topic'] = $topicId;
				$pageMessages[] = $message;
			}
			else
				break;
		}
	}

	if (!empty($pageMessages)) {
		$minId = $pageMessages[0]['id'];
		$maxId = end($pageMessages)['id'];
	}

	require_once('reactions.php');
	populateReactionsData('topic', $pageMessages);

	include('category_fields.php');
	$categoryID = isset($topic['category']) ? $topic['category']:'';
	$category = mysql_fetch_array(mysql_query('SELECT '. $categoryFields .' FROM `mkcategories` WHERE id="'. $categoryID .'"'));
	include('tokens.php');
	assign_token();
	$isFollower = mysql_numrows(mysql_query('SELECT * FROM mkfollowers WHERE user="'. $id .'" AND topic="'. $topicId .'"'));
	$getFollowers = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkfollowers WHERE topic="'. $topicId .'" AND user!="'. $getFirstMessage['auteur'] .'"'));
	if ($isFollower)
		mysql_query('DELETE FROM `mknotifs` WHERE user="'. $id .'" AND type="answer_forum" AND link LIKE "'. $topicId .',%"');
	mysql_query('DELETE FROM `mknotifs` WHERE user="'. $id .'" AND type="follower_topic" AND link="'. $topicId .'"');
	mysql_query('DELETE FROM `mknotifs` WHERE user="'. $id .'" AND type="new_followtopic" AND link LIKE "'. $topicId .',%"');
	$topicNotifs = mysql_query(
		'(SELECT id,link FROM `mknotifs` WHERE user="'. $id .'" AND type IN ("forum_mention","forum_quote") AND link LIKE "'. $topicId .',%")' .
		' UNION ' .
		'(SELECT n.id,r.link FROM `mkreactions` r INNER JOIN `mknotifs` n ON n.type="new_reaction" AND r.id=n.link WHERE r.type="topic" AND r.link LIKE "'. $topicId .',%" AND n.user="'. $id .'")'
	);
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
<title><?php echo $topic ? htmlspecialchars($titreTopic).' - '. _('MKPC Forum'): _('Mario Kart PC Forum'); ?></title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css?reload=2" />
<link rel="stylesheet" type="text/css" href="styles/profil.css" />
<script type="text/javascript" src="scripts/topic.js?reload=1"></script>
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
<h1 data-testid="topic-title"><?php echo htmlspecialchars($titreTopic); ?></h1>
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
					<a href="repondre.php?topic=<?php echo $topicId; ?>" class="action_button"><?php echo $language ? 'Reply':'Répondre'; ?></a>
				</div>
					<?php
				}
				?>
				<div class="buttonGroup">
					<label class="topic_follower" title="<?php echo $language ? 'Receive a notification when a new message is posted':'Recevoir une notification quand un nouveau message est posté'; ?>">
						<span class="topic_follow">
							<input type="checkbox" class="follow_topic_checkbox" name="follow_topic"<?php echo $isFollower ? ' checked="checked"':''; ?> onchange="followTopic(<?php echo $topicId; ?>, this.checked, <?php echo ($getFirstMessage['auteur']==$id)?'true':'false'; ?>)" />
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
					<a href="lock.php?topic=<?php echo $topicId; ?>&amp;value=<?php echo $newLockValue; ?>" class="action_button lock_topic" onclick="return confirm(this.innerHTML+' ?')"><?php echo $lockunlock . ($language ? ' topic':' le topic'); ?></a>
				</div>
					<?php
				}
				?>
			</div>
			<?php
		}
		?>
		<?php
		printReactionUI();
		echo $pagesDiv;
		include('bbCode.php');
		include('avatars.php');
		echo '<div id="fMessages">';
		foreach ($pageMessages as $message) {
			$mayEdit = $message['auteur']==$id || $isModerator;
			print_forum_msg($message, array(
				'mayEdit' => $mayEdit,
				'mayQuote' => !$topic['locked'],
				'mayReact' => true,
				'mayReport' => !$mayEdit
			));
		}
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
				<a href="repondre.php?topic=<?php echo $topicId; ?>" class="action_button"><?php echo $language ? 'Reply':'Répondre'; ?></a>
			</div>
				<?php
			}
			?>
			<div class="buttonGroup">
				<label class="topic_follower" title="<?php echo $language ? 'Receive a notification when a new message is posted':'Recevoir une notification quand un nouveau message est posté'; ?>">
					<span class="topic_follow">
						<input type="checkbox" class="follow_topic_checkbox" name="follow_topic"<?php echo $isFollower ? ' checked="checked"':''; ?> onchange="followTopic(<?php echo $topicId; ?>, this.checked, <?php echo ($getFirstMessage['auteur']==$id)?'true':'false'; ?>)" /> <?php echo $language ? 'Follow the topic' : 'Suivre le topic'; ?>
					</span>
					<span class="nb_followers"><?php echo $getFollowers['nb']; ?></span>
				</label>
			</div>
			<?php
			if (null !== $newLockValue) {
				?>
			<div class="buttonGroup">
				<a href="lock.php?topic=<?php echo $topicId; ?>&amp;value=<?php echo $newLockValue; ?>" class="action_button lock_topic" onclick="return confirm(this.innerHTML+' ?')"><?php echo $lockunlock . ($language ? ' topic':' le topic'); ?></a>
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
</body>
</html>