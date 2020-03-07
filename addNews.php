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
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>News Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/news.css" />

<?php
include('o_online.php');
?>
</head>
<body<?php
if (!$draftSaved) {
	?> onbeforeunload="if(document.forms[0].message.value&amp;&amp;!document.forms[0].querySelector('[type=submit]:not([name=draft]):not([name=undraft])').disabled)return '<?php echo addslashes($language ? 'Warning, the message you\'re writing won\'t be saved':'Attention, le message que vous êtes en train d\'écrire ne sera pas sauvegardé'); ?>'"<?php
}
?>>
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
	<div class="success"><?php echo $language ? 'Draft saved' : 'Votre brouillon a été enregistré. Vous pourrez le modifier à tout moment'; ?></div>
	<?php
}
?>
<h1><?php echo $language ? 'Add a news':'Ajouter une news'; ?></h1>
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
		echo $language ? '<p id="successSent">News created successfully<br />
		'. (hasRight('publisher') ? '':'This news will be published once validated by a moderator.<br />') .'
		<a href="news.php?id='. $iGenerated .'">Click here</a> to see the news.<br />
		<a href="listNews.php">Click here</a> to return to the news list.</p>' :
		'<p id="successSent">News créée avec succès<br />
		'. (hasRight('publisher') ? '':'Cette news sera en ligne une fois validée par un rédacteur.<br />') .'
		<a href="news.php?id='. $iGenerated .'">Cliquez ici</a> pour voir la news.<br />
		<a href="listNews.php">Cliquez ici</a> pour retourner à la liste des news.</p>';
	}
	else {
	$getDraft = mysql_query('SELECT * FROM `mknewsdraft` WHERE author="'. $id .'"');
	$draft = mysql_fetch_array($getDraft);
	if (!$draft)
		$draft = array();
	if (!$language) {
		$getPendingNews = mysql_query('SELECT title FROM `mknews` WHERE status="pending" AND author!="'. $id .'" ORDER BY id DESC');
		if (mysql_numrows($getPendingNews)) {
		?>
		<div id="advice-pending-news" class="info advice-pending-hidden">
			Conseil : avant de commencer, vérifiez qu'une news sur le même sujet n'est pas en cours de validation.
			<span class="advice-pending-show">
				[<a href="javascript:document.getElementById('advice-pending-news').className='info advice-pending-shown';void(0)">Voir</a>]
			</span>
			<span class="advice-pending-hide">
				[<a href="javascript:document.getElementById('advice-pending-news').className='info advice-pending-hidden';void(0)">Masquer</a>]
			</span>
			<ul class="pending-news-list"><?php
			while ($news = mysql_fetch_array($getPendingNews))
				echo '<li>'. $news['title'] .'</li>';
			?></ul>
		</div>
		<?php
		}
	}
	?>
<form method="post" action="addNews.php" onsubmit="if(!this.title.value){alert('<?php echo $language ? 'Please enter a title':'Veuillez entrer un titre'; ?>');return false}if(!this.message.value){alert('<?php echo $language ? 'Please enter a content':'Veuillez entrer un contenu'; ?>');return false}this.querySelector('[type=submit]:not([name=draft]):not([name=undraft])').disabled=true">
<table id="nMessage">
<tr><td class="mLabel"><label for="title"><?php echo $language ? 'Title':'Titre'; ?> :</label></td>
<td class="mInput"><input type="text" id="title" name="title" onchange="document.getElementById('mTitle').innerHTML=htmlspecialchars(this.value)" value="<?php echo htmlspecialchars($draft['title']); ?>" /></td></tr>
<tr><td class="mLabel"><label for="category"><?php echo $language ? 'Category':'Catégorie'; ?> :</label></td>
<td class="mInput">
	<select id="category" name="category" onchange="document.getElementById('mCategory').innerHTML=this.options[this.selectedIndex].text">
		<?php
		include('category_fields.php');
		$categories = mysql_query('SELECT id,name'. $language .' AS name,color FROM `mkcats`');
		$currentCategory = null;
		while ($category = mysql_fetch_array($categories)) {
			echo '<option value="'. $category['id'] .'"'. (($category['id']==$draft['category']) ? ' selected="selected"':'') .' style="color:'. $category['color'] .'">'. $category['name'] .'</option>';
			if (!$currentCategory)
				$currentCategory = $category;
			elseif ($category['id'] == $draft['category'])
				$currentCategory = $category;
		}
		?>
	</select>
</td></tr>
<tr><td class="mLabel">BBcode :<br /><a href="javascript:helpBbCode()"><?php echo $language ? 'Help':'Aide'; ?></a></td><td><?php
$isNews = true;
include('bbButtons.php');
?></td></tr>
<tr><td class="mLabel"><p><label for="message">Contenu :</label></p>
<p><?php
for ($i=0;$i<$nbSmileys;$i++)
	echo ' <a href="javascript:ajouter(\''. $smileys[$i] .'\')"><img src="images/smileys/smiley'. $i .'.png" alt="'. $smileys[$i] .'" /></a> ';
?>
<a href="javascript:moresmileys()" id="more-smileys"><?php echo $language ? 'More smileys':'Plus de smileys'; ?></a></p>
</td><td class="mInput"><textarea name="message" id="message" rows="10"><?php
	echo htmlspecialchars($draft['message']);
?></textarea></td></tr>
<?php
if (!empty($draft)) {
	?>
<tr><td colspan="2" class="mLabel">
	<input type="submit" class="mUndraft" name="undraft" value="<?php echo $language ? 'Delete draft':'Supprimer le brouillon'; ?>" onclick="return confirm('<?php echo $language ? 'Are you sure?':'Êtes-vous sûr ?'; ?>')" />
</td></tr>
	<?php
}
?>
<tr><td colspan="2" class="mLabel">
	<input type="submit" class="mDraft" name="draft" value="<?php echo $language ? 'Save draft':'Enregistrer le brouillon'; ?>" />
	<input type="button" value="<?php echo $language ? 'Preview':'Aper&ccedil;u'; ?>" onclick="apercu()" /> &nbsp; <input type="submit" value="<?php echo $language ? 'Send':'Envoyer'; ?>" />	
</td></tr>
</table>
</form>
<div class="news-container preview-msg" id="fMessages">
<div class="news-header">
	<h1 id="mTitle"><?php if ($draft) echo $draft['title']; ?></h1>
	<div class="news-author">
		Dans <strong id="mCategory"><?php echo $currentCategory['name']; ?></strong> par <strong><?php echo $getAuthor['nom']; ?></strong>
	</div>
	<div class="news-date">
		Publiée <span class="mDate"></span>
	</div>
</div>
<div class="news-content mBody"></div>
</div>
<p class="forumButtons" style="margin: 10px 0 0 23%">
	<a href="listNews.php"><?php echo $language ? 'Back to the news list':'Retour à la liste des news'; ?></a>
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