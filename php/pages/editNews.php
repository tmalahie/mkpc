<?php
include('../includes/language.php');
include('../includes/session.php');
include('../includes/initdb.php');
require_once('../includes/getRights.php');
if (isset($_GET['id']) && ($news=mysql_fetch_array(mysql_query('SELECT * FROM `mknews` WHERE id="'. $_GET['id'] .'"'))) && (hasRight('publisher')||($news['author']==$id))) {
	$getAuthor = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $news['author'] .'"'));
	?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>News Mario Kart PC</title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/news.css" />

<?php
include('../includes/o_online.php');
?>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'home';
include('../includes/menu.php');
?>
<main>
<?php
include('../includes/smileys.php');
?>
<h1><?php echo $language ? 'Edit a news':'Modifier une news'; ?></h1>
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
	$getBanned = mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"'));
	if ($getBanned['banned'])
		include('../includes/ban_msg.php');
	elseif (isset($_POST['title']) && isset($_POST['category']) && isset($_POST['message'])) {
		$revalidate = ($news['status']=='rejected');
		mysql_query('UPDATE `mknews` SET
			title="'. $_POST['title'] .'",
			category="'. $_POST['category'] .'",
			'. ($revalidate ? 'status="pending",creation_date=NULL,':'') .'
			'. ($news['status']!='accepted' ? 'publication_date=NULL,':'') .'
			content="'. $_POST['message'] .'"
			WHERE id="'. $_GET['id'] .'"
		');
		if ($news['author'] != $id)
			mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "ENews '. $_GET['id'] .'")');
		$iGenerated = intval($_GET['id']);
		echo $language ? '<p id="successSent">News edited successfully<br />
		<a href="news.php?id='. $iGenerated .'">Click here</a> to see the news.<br />
		<a href="listNews.php">Click here</a> to return to the news list.</p>' :
		'<p id="successSent">News modifiée avec succès<br />
		'. ($revalidate ? 'La news a été remise en attente de validation.<br />':'') .'
		<a href="news.php?id='. $iGenerated .'">Cliquez ici</a> pour voir la news.<br />
		<a href="listNews.php">Cliquez ici</a> pour retourner à la liste des news.</p>';
	}
	else {
	?>
<form method="post" action="editNews.php?id=<?php echo urlencode($_GET['id']); ?>" onsubmit="if(!this.title.value){alert('<?php echo $language ? 'Please enter a title':'Veuillez entrer un titre'; ?>');return false}if(!this.message.value){alert('<?php echo $language ? 'Please enter a content':'Veuillez entrer un contenu'; ?>');return false}this.querySelector('[type=submit]:not([name=draft])').disabled=true">
<table id="nMessage">
<tr><td class="mLabel"><label for="title"><?php echo $language ? 'Title':'Titre'; ?> :</label></td>
<td class="mInput"><input type="text" id="title" name="title" onchange="document.getElementById('mTitle').innerHTML=htmlspecialchars(this.value)" value="<?php echo htmlspecialchars($news['title']); ?>" /></td></tr>
<tr><td class="mLabel"><label for="category"><?php echo $language ? 'Category':'Catégorie'; ?> :</label></td>
<td class="mInput">
	<select id="category" name="category" onchange="document.getElementById('mCategory').innerHTML=this.options[this.selectedIndex].text">
		<?php
		include('../includes/category_fields.php');
		$categories = mysql_query('SELECT id,name'. $language .' AS name,color FROM `mkcats`');
		$currentCategory = null;
		while ($category = mysql_fetch_array($categories)) {
			echo '<option value="'. $category['id'] .'"'. (($category['id']==$news['category']) ? ' selected="selected"':'') .' style="color:'. $category['color'] .'">'. $category['name'] .'</option>';
			if ($category['id'] == $news['category'])
				$currentCategory = $category;
		}
		?>
	</select>
</td></tr>
<tr><td class="mLabel">BBcode :<br /><a href="javascript:helpBbCode()"><?php echo $language ? 'Help':'Aide'; ?></a></td><td><?php
$isNews = true;
include('../includes/bbButtons.php');
?></td></tr>
<tr><td class="mLabel"><p><label for="message">Contenu :</label></p>
<p><?php
for ($i=0;$i<$nbSmileys;$i++)
	echo ' <a href="javascript:ajouter(\''. $smileys[$i] .'\')"><img src="images/smileys/smiley'. $i .'.png" alt="'. $smileys[$i] .'" /></a> ';
?>
<a href="javascript:moresmileys()" id="more-smileys"><?php echo $language ? 'More smileys':'Plus de smileys'; ?></a></p>
</td><td class="mInput"><textarea name="message" id="message" rows="10"><?php
	echo htmlspecialchars($news['content']);
?></textarea></td></tr>
<tr><td colspan="2" class="mLabel"><input type="button" value="<?php echo $language ? 'Preview':'Aper&ccedil;u'; ?>" onclick="apercu()" /> &nbsp; <input type="submit" value="<?php echo $language ? 'Send':'Envoyer'; ?>" /></td></tr>
</table>
</form>
<div class="news-container preview-msg" id="fMessages">
<div class="news-header">
	<h1 id="mTitle"></h1>
	<div class="news-author">
		<?php echo $language ? 'In':'Dans'; ?> <strong id="mCategory"><?php echo $currentCategory['name']; ?></strong> <?php echo $language ? 'by':'par'; ?> <strong><?php echo $getAuthor['nom']; ?></strong>
	</div>
	<div class="news-date">
		<?php echo $language ? 'Published':'Publié'; ?> <span class="mDate"></span>
	</div>
</div>
<div class="news-content mBody"></div>
</div>
<p class="forumButtons" style="margin: 10px 0 0 23%">
	<a href="news.php?id=<?php echo urlencode($_GET['id']); ?>"><?php echo $language ? 'Back to the news':'Retour à la news'; ?></a><br />
	<a href="listNews.php"><?php echo $language ? 'Back to the news list':'Retour à la liste des news'; ?></a>
</p>
	<?php
}
?>
</main>
<?php
include('../includes/footer.php');
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