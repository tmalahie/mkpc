<?php
include('../includes/language.php');
include('../includes/session.php');
include('../includes/initdb.php');
require_once('../includes/getRights.php');
if (isset($_GET['id']) && ($news=mysql_fetch_array(mysql_query('SELECT * FROM `mknews` WHERE id="'. $_GET['id'] .'"'))) && (hasRight('publisher')||($news['author']==$id))) {
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
<h1><?php echo $language ? 'News deleted':'Suppression'; ?></h1>
<?php
require_once('../includes/utils-ads.php');
showRegularAdSection();
?>
<?php
	mysql_query('DELETE FROM `mknews` WHERE id="'. $_GET['id'] .'"');
	if ($news['author'] != $id)
		mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "SNews '. $_GET['id'] .'")');
	echo $language ? '<p id="successSent">News deleted successfully<br />
	<a href="listNews.php">Click here</a> to return to the news list.</p>' :
	'<p id="successSent">News supprimée avec succès<br />
	<a href="listNews.php">Cliquez ici</a> pour retourner à la liste des news.</p>';
?>
</main>
<?php
include('../includes/footer.php');
?>
</body>
</html>
	<?php
}
else
	echo 'Access denied';
mysql_close();
?>