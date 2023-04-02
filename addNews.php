<?php
include('language.php');
include('session.php');
include('initdb.php');
require_once('getRights.php');
if ($id) {
	$getBanned = mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"'));
	if ($getBanned && $getBanned['banned']) {
		mysql_close();
		exit;
	}
	if (isset($_POST['title']) && isset($_POST['category']) && isset($_POST['message']) && (isset($_POST['draft'])||isset($_POST['undraft']))) {
		mysql_query('DELETE FROM `mknewsdraft` WHERE author="'. $id .'"');
		if (isset($_POST['draft'])) {
			mysql_query('INSERT INTO `mknewsdraft`
				SET title="'. $_POST['title'] .'",
				category="'. $_POST['category'] .'",
				message="'. $_POST['message'] .'",
				author="'. $id .'"'
			);
			$draftSaved = true;
		}
	}
	$getAuthor = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $id .'"'));
	?>
<!DOCTYPE html>
<html lang="<?= P_("html language", "en") ?>">
<head>
<title><?= _("News Mario Kart PC") ?></title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/news.css" />

<?php
include('o_online.php');
?>
</head>
<body
<?php
if (!isset($draftSaved)) {
	?>
	onbeforeunload="if(document.forms[0].message.value&amp;&amp;!document.forms[0].querySelector('[type=submit]:not([name=draft]):not([name=undraft])').disabled)return '<?= addslashes(_('Warning, the message you\'re writing won\'t be saved')) ?>'"<?php
}
?>
>
<?php
include('header.php');
$page = 'home';
include('menu.php');
?>
<main>
<?php
include('smileys.php');
if (isset($draftSaved)) {
	?>
	<div class="success"><?= _('Draft saved') ?></div>
	<?php
}
?>
<h1><?= _('Add a news') ?></h1>
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
	if (isset($_POST['title']) && isset($_POST['category']) && isset($_POST['message']) && !isset($_POST['draft']) && !isset($_POST['undraft'])) {
		include('utils-cooldown.php');
		if (isNewsCooldowned()) {
			logCooldownEvent('news');
			mysql_close();
			printNewsCooldowned();
			exit;
		}
		mysql_query('DELETE FROM `mknewsdraft` WHERE author="'. $id .'"');
		mysql_query('INSERT INTO `mknews` SET
			title="'. $_POST['title'] .'",
			category="'. $_POST['category'] .'",
			author="'. $id .'",
			content="'. $_POST['message'] .'",
			status="'. (hasRight('publisher') ? 'accepted':'pending') .'"
		');
		$iGenerated = mysql_insert_id();
		mysql_query('UPDATE `mkprofiles` SET last_connect=NULL WHERE id='. $id);
		if (hasRight('publisher')) {
			$getFollowers = mysql_query('SELECT follower FROM `mkfollowusers` WHERE followed="'. $id .'"');
			while ($follower = mysql_fetch_array($getFollowers))
				mysql_query('INSERT INTO `mknotifs` SET type="follower_news", user="'. $follower['follower'] .'", link="'.$iGenerated.'"');
		}
		echo '<p id="successSent">';
		echo _("News created successfully");
		echo "<br />";
		if (!hasRight('publisher')) {
			echo _('This news will be published once validated by a moderator.');
			echo "<br />";
		}
		echo F_('<a href="{url}">Click here</a> to see the news.', url: "news.php?id=". $iGenerated);
		echo "<br />";
		echo F_('<a href="{url}">Click here</a> to return to the news list.', url: "listNews.php");
		echo "</p>";
	}
	else {
	$getDraft = mysql_query('SELECT * FROM `mknewsdraft` WHERE author="'. $id .'"');
	$draft = mysql_fetch_array($getDraft);
	if (!$draft)
		$draft = array();
		$getPendingNews = mysql_query('SELECT title FROM `mknews` WHERE status="pending" AND author!="'. $id .'" ORDER BY id DESC');
		if (mysql_numrows($getPendingNews)) {
		?>
		<div id="advice-pending-news" class="info advice-pending-hidden">
			<?= _("Tip: before beginning, check that a news on the same subject is not pending validation.") ?>
			<span class="advice-pending-show">
				[<a href="javascript:document.getElementById('advice-pending-news').className='info advice-pending-shown';void(0)"><?= _('Show') ?></a>]
			</span>
			<span class="advice-pending-hide">
				[<a href="javascript:document.getElementById('advice-pending-news').className='info advice-pending-hidden';void(0)"><?= _('Hide'); ?></a>]
			</span>
			<ul class="pending-news-list"><?php
			while ($news = mysql_fetch_array($getPendingNews))
				echo '<li>'. $news['title'] .'</li>';
			?></ul>
		</div>
		<?php
		}
	?>
<form method="post" action="addNews.php" onsubmit="if(!this.title.value){alert('<?= _('Please enter a title') ?>');return false}if(!this.message.value){alert('<?= _('Please enter a content') ?>');return false}this.querySelector('[type=submit]:not([name=draft]):not([name=undraft])').disabled=true">
<table id="nMessage">
<tr><td class="mLabel"><label for="title"><?= _('Title') ?> :</label></td>
<td class="mInput"><input type="text" id="title" name="title" onchange="document.getElementById('mTitle').innerHTML=htmlspecialchars(this.value)" value="<?php if ($draft) echo htmlspecialchars($draft['title']); ?>" /></td></tr>
<tr><td class="mLabel"><label for="category"><?= _('Category') ?> :</label></td>
<td class="mInput">
	<select id="category" name="category" onchange="document.getElementById('mCategory').innerHTML=this.options[this.selectedIndex].text">
		<?php
		include('category_fields.php');
		$categories = mysql_query('SELECT id,name'. $language .' AS name,color FROM `mkcats`');
		$currentCategory = null;
		while ($category = mysql_fetch_array($categories)) {
			$isCurrentCat = $draft && ($category['id']==$draft['category']);
			echo '<option value="'. $category['id'] .'"'. ($isCurrentCat ? ' selected="selected"':'') .' style="color:'. $category['color'] .'">'. $category['name'] .'</option>';
			if (!$currentCategory)
				$currentCategory = $category;
			elseif ($isCurrentCat)
				$currentCategory = $category;
		}
		?>
	</select>
</td></tr>
<tr><td class="mLabel">BBcode :<br /><a href="javascript:helpBbCode()"><?= _('Help') ?></a></td><td><?php
$isNews = true;
include('bbButtons.php');
?></td></tr>
<tr><td class="mLabel"><p><label for="message"><?= _('Content'); ?> :</label></p>
<p><?php
for ($i=0;$i<$nbSmileys;$i++)
	echo ' <a href="javascript:ajouter(\''. $smileys[$i] .'\')"><img src="images/smileys/smiley'. $i .'.png" alt="'. $smileys[$i] .'" /></a> ';
?>
<a href="javascript:moresmileys()" id="more-smileys"><?= _('More smileys') ?></a></p>
</td><td class="mInput"><textarea name="message" id="message" rows="10"><?php
	if ($draft)
		echo htmlspecialchars($draft['message']);
?></textarea></td></tr>
<?php
if ($draft) {
	?>
<tr><td colspan="2" class="mLabel">
	<input type="submit" class="mUndraft" name="undraft" value="<?= _('Delete draft') ?>" onclick="return confirm('<?= _('Delete the draft?') ?>')" />
</td></tr>
	<?php
}
?>
<tr><td colspan="2" class="mLabel">
	<input type="submit" class="mDraft" name="draft" value="<?= _('Save draft') ?>" />
	<input type="button" value="<?= _('Preview') ?>" onclick="apercu()" /> &nbsp; <input type="submit" value="<?= _('Send') ?>" />	
</td></tr>
</table>
</form>
<div class="news-container preview-msg" id="fMessages">
<div class="news-header">
	<h1 id="mTitle"><?php if ($draft) echo $draft['title']; ?></h1>
	<div class="news-author">
		<?= F_(
			"In <strong {cssAttributes}>{categoryName}</strong> by <strong>{author}</strong>",
			cssAttributes: 'id="mCategory"',
			categoryName: $currentCategory['name'],
			author: $getAuthor['nom'],
		); ?>
	</div>
	<div class="news-date">
		<?= _('Published') ?> <span class="mDate"></span>
	</div>
</div>
<div class="news-content mBody"></div>
</div>
<p class="forumButtons" style="margin: 10px 0 0 23%">
	<a href="listNews.php"><?= _('Back to the news list') ?></a>
</p>
	<?php
}
?>
</main>
<?php
include('footer.php');
?>
<script type="text/javascript">window.isNews = true;</script>
<script type="text/javascript" src="scripts/msg.php"></script>
</body>
</html>
	<?php
}
else
	echo 'Access denied';
mysql_close();
?>