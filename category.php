<?php
if (isset($_GET['category'])) {
	include('getId.php');
	include('language.php');
	include('session.php');
	include('initdb.php');
	$categoryID = $_GET['category'];
	include('category_fields.php');
	if ($category = mysql_fetch_array(mysql_query('SELECT '. $categoryFields .',adminonly FROM `mkcategories` WHERE id="'. $categoryID .'"'))) {
		?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $category['nom']; ?> - Forum Mario Kart PC</title>
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
if ($id && $myIdentifiants) {
	mysql_query('INSERT IGNORE INTO `mkips` VALUES("'.$id.'","'.$myIdentifiants[0].'","'.$myIdentifiants[1].'","'.$myIdentifiants[2].'","'.$myIdentifiants[3].'")');
	mysql_query('INSERT IGNORE INTO `mkbrowsers` VALUES("'.$id.'","'.mysql_real_escape_string($_SERVER['HTTP_USER_AGENT']).'")');
}
?>
<main>
<h1><?php echo $category['nom']; ?></h1>
<?php
if ($id)
	include('rights-msg.php');
?>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Forum MKPC -->
<p class="pub"><ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-1340724283777764"
     data-ad-slot="4919860724"></ins></p>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
<p><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a></p>
<p id="category-description"><?php echo $category['description']; ?></p>
<?php
require_once('getRights.php');
if ($id && (!$category['adminonly']||hasRight('manager')))
	echo '<p class="forumButtons"><a href="newtopic.php?category='. $categoryID .'" class="action_button">'. ($language ? 'New topic':'Nouveau topic') .'</a></p>';
?>
<table id="listeTopics">
<col />
<col id="authors" />
<col id="nbmsgs" />
<col id="lastmsgs" />
<tr id="titres">
<td><?php echo $language ? 'Topics':'Sujets'; ?></td>
<td><?php echo $language ? 'Author':'Auteur'; ?></td>
<td class="topic-nbmsgs"><?php echo $language ? 'Msgs nb':'Nb msgs'; ?></td>
<td><?php echo $language ? 'Last message':'Dernier message'; ?></td>
</tr>
<?php
$page = isset($_GET['page'])&&is_numeric($_GET['page']) ? $_GET['page']:1;
$RES_PER_PAGE = 50;
$getNbTopics = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mktopics` WHERE category='. $categoryID .' AND language='. "language" . (hasRight('manager') ? '':' AND !private')));
$nbTopics = $getNbTopics['nb'];
$topics = mysql_query(
	'SELECT t.id,t.titre,t.dernier,t.nbMsgs, m.auteur, j.nom
	FROM `mktopics` t LEFT JOIN `mkmessages` m ON m.topic=t.id AND m.id=1
	LEFT JOIN `mkjoueurs` j ON m.auteur=j.id
	WHERE t.category='. $categoryID .' AND t.language='. "language" . (hasRight('manager') ? '':' AND !private') .'
	ORDER BY t.dernier DESC LIMIT '. (($page-1)*$RES_PER_PAGE).','.$RES_PER_PAGE
);
require_once('utils-date.php');
for ($i=0;$topic=mysql_fetch_array($topics);$i++) {
	echo '<tr class="'. (($i%2) ? 'fonce':'clair') .'"><td class="subjects">';
		echo '<a href="topic.php?topic='. $topic['id'] .'" class="fulllink">'. htmlspecialchars($topic['titre']) .'</a>';
	echo '</td><td>';
	if ($topic['nom'])
		echo '<a class="forum-auteur" href="profil.php?id='. $topic['auteur'] .'">'. $topic['nom'] .'</a>';
	else
		echo $language ? '<em>Deleted account</em>':'<em>Compte supprimé</em>';
	echo '</td><td>';
		echo $topic['nbMsgs'];
	echo '</td><td>';
	echo pretty_dates($topic['dernier']);
	echo '</td></tr>';
}
mysql_close();
?>
</table>
<?php
$nbPages = ceil($nbTopics/$RES_PER_PAGE);
if ($nbPages > 1) {
	?>
	<div class="topicPages"><p>
		Page : <?php
		$get = $_GET;
		foreach ($get as $k => $getk)
			$get[$k] = stripslashes($get[$k]);
		for ($i=1;$i<=$nbPages;$i++) {
			$get['page'] = $i;
			if ($i == $page)
				echo $i;
			else
				echo '<a href="?'. http_build_query($get) .'">'. $i .'</a>';
			echo ' &nbsp; ';
		}
		?>
	</p></div>
	<?php
}
?>
<p class="forumButtons">
<?php
if ($id && (!$category['adminonly']||hasRight('manager')))
	echo '<a href="newtopic.php?category='. $categoryID .'" class="action_button">'. ($language ? 'New topic':'Nouveau topic') .'</a>';
?>
<a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
<a href="index.php"><?php echo $language ? 'Back to the homepage':'Retour &agrave; l\'accueil'; ?></a>
</p>
</main>
<?php
include('footer.php');
?>
</body>
</html>
		<?php
	}
}
?>