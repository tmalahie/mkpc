<?php
include('language.php');
include('session.php');
include('initdb.php');
require_once('getRights.php');
if (($news = mysql_fetch_array(mysql_query('SELECT title,category,author,content,status,reject_reason,locked,publication_date FROM `mknews` WHERE id="'. $_GET['id'] .'"'))) && (($news['status']=='accepted')||($news['author']==$id)||hasRight('publisher'))) {
	$newsId = +$_GET['id'];
	$categoryID = $news['category'];
	$category = mysql_fetch_array(mysql_query('SELECT name'. $language .' AS name,color FROM `mkcats` WHERE id="'. $categoryID .'"'));
	$author = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $news['author'] .'"'));
	include('tokens.php');
	assign_token();
	if ($id) {
		mysql_query('DELETE FROM `mknotifs` WHERE user="'. $id .'" AND (type="news_comment" OR type="answer_newscom") AND link IN (SELECT id FROM `mknewscoms` WHERE news="'. $newsId .'")');
		mysql_query('DELETE FROM `mknotifs` WHERE user="'. $id .'" AND type="news_moderated" AND link="'. $newsId .'"');
		mysql_query('DELETE FROM `mknotifs` WHERE user="'. $id .'" AND type="follower_news" AND link="'. $newsId .'"');
		mysql_query('DELETE n FROM `mkreactions` r INNER JOIN `mknotifs` n ON n.type="new_reaction" AND n.link=r.id WHERE r.type="news" AND r.link="'. $newsId .'" AND n.user="'. $id .'"');
		mysql_query('DELETE n FROM `mknewscoms` c INNER JOIN `mkreactions` r ON r.type="newscom" AND r.link=c.id INNER JOIN `mknotifs` n ON n.type="new_reaction" AND n.link=r.id WHERE c.news="'. $newsId .'" AND c.author="'. $id .'" AND n.user="'. $id .'"');
		if ($news['status'] == 'accepted')
			mysql_query('INSERT INTO `mknewsread` SET user='.$id.',date="'.$news['publication_date'].'" ON DUPLICATE KEY UPDATE date=GREATEST(date,VALUES(date))');
	}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo htmlspecialchars($news['title']) ?></title>
<?php
/*preg_match('#\[img\]([^\[]*)\[/img\]#isU', $news['content'], $getThumbnail);
if ($getThumbnail) {
	function img_url_exists($url) {
    	$ch = curl_init(trim($url));

		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		curl_exec($ch);
		$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$contentType = strtolower(curl_getinfo($ch, CURLINFO_CONTENT_TYPE));
		curl_close($ch);

		return ($retcode == 200) && in_array($contentType, array('image/png','image/jpg','image/jpeg','image/gif','image/bmp'));
	}

	if (img_url_exists($getThumbnail[1]))
		$hthumbnail = $getThumbnail[1];
}*/
include('utils-description.php');
$hthumbnail = false;
$hdescription = removeBbCode($news['content']);
include('heads.php');
?>
<meta property="og:type" content="article" />
<meta property="og:title" content="<?php echo htmlspecialchars($news['title']) ?>" />
<meta name="og:description" content="<?php echo htmlspecialchars($hdescription); ?>" />
<link rel="stylesheet" type="text/css" href="styles/news.css?reload=1" />
<link rel="stylesheet" type="text/css" href="styles/profil.css" />
<script type="text/javascript" src="scripts/topic.js?reload=1"></script>
<?php
include('o_online.php');
?>
</head>
<body>
<?php
include('header.php');
$page = 'home';
include('menu.php');
?>
<main>
<div class="news-container">
<div class="news-header">
	<h1><?php echo htmlspecialchars($news['title']); ?></h1>
	<?php
	require_once('utils-date.php');
	if (($news['author']==$id) || hasRight('publisher')) {
		?>
		<div class="news-options">
			<a class="news-edit" href="editNews.php?id=<?php echo $newsId; ?>"><?php echo $language ? 'Edit':'Modifier'; ?></a>
			<a class="news-del" href="supprNews.php?id=<?php echo $newsId; ?>" onclick="return confirm('<?php echo $language ? 'Delete news?':'Supprimer la news ?'; ?>')"><?php echo $language ? 'Delete':'Supprimer'; ?></a>
		</div>
		<?php
	}
	?>
	<div class="news-author">
		<?php echo $language ? 'In':'Dans'; ?> <strong style="color:<?php echo $category['color']; ?>;opacity:0.8"><?php echo $category['name']; ?></strong>
		<?php if ($author['nom']) {echo $language ? 'by':'par'; ?> <a href="profil.php?id=<?php echo $news['author']; ?>"><?php echo $author['nom']; ?></a><?php } ?>
	</div>
	<div class="news-date">
		<?php echo $language ? 'Published':'Publié'; ?> <?php echo pretty_dates($news['publication_date'],array('lower'=>true)); ?>
	</div>
</div>
<div class="news-content">
<?php
include('bbCode.php');
$isNews = true;
echo bbcode($news['content']);
if ($news['status'] == 'accepted') {
	require_once('reactions.php');
	$newsReactions = getReactions('news', $newsId);
	echo '<div class="news-reactions">';
	printReactions('news',$newsId, $newsReactions);
	echo '</div>';
}
?>
</div>
</div>
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
if ($news['status'] == 'accepted') {
	printReactionUI();
	if (hasRight('moderator') || ($news['author']==$id && $news['locked']!=1)) {
		switch ($news['locked']) {
		case 0:
			$newLockValue = ($news['author'] == $id) ? 2:1;
			break;
		case 1:
		case 2:
			$newLockValue = 0;
			break;
		}
		$lockunlock = ($language ? ($newLockValue?'Lock':'Unlock'):($newLockValue?'Locker':'Unlocker'));
		?>
<a href="lock.php?news=<?php echo $newsId; ?>&amp;value=<?php echo $newLockValue; ?>" class="action_button news-lock" onclick="return confirm(this.innerHTML+' ?')"><?php echo $lockunlock . ($language ? ' comments':' les commentaires'); ?></a>
		<?php
	}
	?>
<div class="news-comments">
<?php
$getComments = mysql_query('SELECT c.id,c.author,c.message,c.date,j.nom FROM `mknewscoms` c LEFT JOIN `mkjoueurs` j ON c.author=j.id WHERE news="'. $newsId .'" ORDER BY c.id DESC');
$comments = array();
while ($comment = mysql_fetch_array($getComments))
	$comments[] = $comment;
populateReactionsData('newscom', $comments);
$nbComments = count($comments);
include('avatars.php');
echo '<div class="news-nbcomments">'. ($language ? 'Comments':'Commentaires') .' ('. $nbComments .')</div>';
if (!$nbComments)
	echo '<div class="news-nocomment">'. ($language ? 'No comments yet. Be the first one to give your opinion !':'Aucun commentaire. Soyez le premier &agrave; donner votre avis !') .'</div>';
?>
<form method="post" action="addNewscom.php?news=<?php echo $newsId; ?>" id="news-comment-ctn-0" class="news-comment-ctn news-comment-editting">
	<div class="news-avatar"><?php
		if ($id && !$news['locked']) {
			echo '<a href="profil.php?id='.$id.'">';
			print_avatar($id, 40);
			echo '</a>';
		}
	?></div>
	<div class="news-comment">
		<div class="news-edit">
			<?php
			if ($id) {
				if ($news['locked'])
					echo '<div class="news-commenttopost">'. ($language ? 'Comment section has been locked for this news':'La section commentaire a été lockée pour cette news.') .'</div>';
				else
					echo '<textarea name="comment" required="required" placeholder="'. ($language ? 'Add a comment...':'Ajouter un commentaire...') .'"></textarea>';
			}
			else
				echo '<div class="news-commenttopost">'. ($language ? '<a href="forum.php">Log-in</a> to post a comment' : '<a href="forum.php">Connectez-vous</a> pour poster un commentaire') .'</div>';
			?>
		</div>
		<?php
		if ($id && !$news['locked']) {
			?>
			<div class="news-comment-info">
				<div class="news-comment-date">&nbsp;</div>
				<div class="news-comment-post news-comment-options news-edit">
					<button type="submit" class="news-sendcomment"><?php echo $language ? 'Send':'Envoyer'; ?></button>
				</div>
			</div>
			<?php
		}
		?>
	</div>
</form>
<?php
include('smileys.php');
foreach ($comments as $comment) {
	$canEdit = (($comment['author']==$id)&&!$news['locked']) || hasRight('moderator');
	?>
	<form method="post" action="editNewscom.php?id=<?php echo $comment['id']; ?>" id="news-comment-ctn-<?php echo $comment['id']; ?>" class="news-comment-ctn">
		<div class="news-avatar"><?php
			if ($comment['nom'])
				echo '<a href="profil.php?id='.$comment['author'].'">';
			print_avatar($comment['author'], 40);
			if ($comment['nom'])
				echo '</a>';
		?></div>
		<div class="news-comment" data-id="<?php echo $comment['id']; ?>">
			<div class="news-noedit">
				<?php
				if ($comment['nom']) {
					?>
					<a class="news-commenter" href="profil.php?id=<?php echo $comment['author']; ?>"><?php echo $comment['nom']; ?></a>
					<?php
				}
				?>
				<span class="news-message"><?php
				$msg = nl2br(htmlspecialchars($comment['message']));
				for ($i=0;$i<$nbSmileys2;$i++)
					$msg = str_replace(':'.$smileyNames[$i].':', '<img src="images/smileys/smiley'.$i.'.gif" alt="'. $smileyNames[$i] .'" />', $msg);
				for ($i=0;$i<$nbSmileys;$i++)
					$msg = str_replace($smileys[$i], '<img src="images/smileys/smiley'.$i.'.png" alt="Smiley" />', $msg);
				for ($i=0;$i<$nbSmileys2;$i++)
					$msg = str_replace(':'.$i.':', '<img src="images/smileys/smiley'.$i.'.gif" alt="'. $smileyNames[$i] .'" />', $msg);
				$msg = preg_replace('#(https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~\#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~\#?&\/\/=]*))#', '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>', $msg);
				$msg = preg_replace('#([a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4})#i', '<a href="mailto:$1">$1</a>', $msg);
				echo $msg;
				?></span>
			</div>
			<?php
			if ($canEdit) {
				?>
				<div class="news-comment-options news-edit">
					<textarea name="comment" required="required"><?php echo htmlspecialchars($comment['message']); ?></textarea>
				</div>
				<?php
			}
			?>
			<div class="news-comment-info">
				<div class="news-comment-date"><?php echo pretty_dates($comment['date']); ?></div>
				<div class="news-comment-post news-comment-options news-edit">
					<button type="submit" class="news-sendcomment"><?php echo $language ? 'Send':'Valider'; ?></button>
					<img src="images/forum/delete.png" alt="<?php echo $language ? 'Undo':'Annuler'; ?>" title="<?php echo $language ? 'Undo':'Annuler'; ?>" role="button" class="news-undocomment" onclick="undoEditComment(<?php echo $comment['id']; ?>)" />
				</div>
			</div>
			<div class="news-comment-reactions"><?php printReactions('newscom', $comment['id'], $comment['reactions']); ?></div>
			<?php
			if ($id) {
				?>
				<div class="news-comment-options news-noedit">
					<?php
					if ($canEdit) {
						?>
						<img src="images/forum/edit.png" alt="<?php echo $language ? 'Edit':'Modifier'; ?>" title="<?php echo $language ? 'Edit':'Modifier'; ?>" role="button" onclick="editComment(<?php echo $comment['id']; ?>)" />
						<img src="images/forum/delete.png" alt="<?php echo $language ? 'Delete':'Supprimer'; ?>" title="<?php echo $language ? 'Delete':'Supprimer'; ?>" role="button" onclick="delComment(<?php echo $comment['id']; ?>)" />
						<?php
					}
					?>
					<img src="images/forum/react.png" alt="<?php echo $language ? 'React':'Réagir'; ?>" title="<?php echo $language ? 'Add reaction':'Ajouter une réaction'; ?>" role="button" onclick="openReactions('newscom','<?php echo $comment['id']; ?>',this)" />
				</div>
				<?php
			}
			?>
		</div>
	</form>
	<?php
}
?>
</div>
<?php
}
elseif ($news['status'] == 'pending') {
	?>
	<p class="info" id="news-status">
		<?php echo $language ? 'This news is awaiting validation':'Cette news est en attente de validation'; ?><br />
		<?php
		if (hasRight('publisher')) {
			?>
			<a href="acceptNews.php?id=<?php echo $newsId; ?>" class="news-moderate news-accept"><?php echo $language ? 'Accept':'Accepter'; ?></a>
			<a href="javascript:rejectNews(<?php echo $newsId; ?>)" class="news-moderate news-reject"><?php echo $language ? 'Reject':'Refuser'; ?></a>
			<?php
		}
		?>
	</p>
	<?php
}
elseif ($news['status'] == 'rejected') {
	?>
	<div class="error" id="news-status">
		<?php echo $language ? 'This news has been rejected':'Cette news a été refusée'; ?><?php
		if ($news['reject_reason'])
			echo $language ? ' for the following reason:<br /><div class="reject-reason">'. $news['reject_reason'] .'</div>':' pour la raison suivante :<br /><div class="reject-reason">'. $news['reject_reason'] .'</div>';
		else
			echo '.';
		?><br />
		<?php echo $language ? 'You can still edit the news to make it be re-evaluated.':'Vous pouvez toujours modifier la news afin que celle-ci repasse en modération.'; ?>
	</div>
	<?php
}
?>
<p>
	<a href="listNews.php"><?php echo $language ? 'Back to the news list':'Retour à la liste des news'; ?></a>
</p>
<?php
mysql_close();
?>
</main>
<?php
include('footer.php');
?>
<script type="text/javascript" src="scripts/jquery.min.js"></script>
<script type="text/javascript" src="scripts/news.js"></script>
</body>
</html>
<?php
}
mysql_close();
?>