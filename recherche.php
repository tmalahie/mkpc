<?php
if (isset($_GET['content'])) {
	include('getId.php');
	include('language.php');
	include('session.php');
	include('initdb.php');
	include('category_fields.php');
	$content = stripslashes($_GET['content']);
	?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>Recherche - Forum Mario Kart PC</title>
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
?>
<main>
<h1>Forum Mario Kart PC</h1>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Forum MKPC -->
<p class="pub"><ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-1340724283777764"
     data-ad-slot="4919860724"></ins></p>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
<form method="get" action="recherche.php" class="forum-search">
	<p>
		<label for="search-content">
			<?php echo $language ? 'Search':'Recherche'; ?>:
		</label>
		<input type="text" id="search-content" placeholder="<?php echo $language ? 'Topic title':'Titre du topic'; ?>" name="content" value="<?php echo htmlspecialchars($content); ?>" />
		<input type="submit" value="Ok" class="action_button" />
		<a href="forum-search.php"><?php echo $language ? 'Advanced search':'Recherche avancée'; ?></a>
	</p>
</form>
<p><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a></p>
<table id="listeTopics">
<col />
<col id="authors" />
<col id="nbmsgs" />
<col id="lastmsgs" />
<tr id="titres">
<td><?php echo $language ? 'Subjects':'Sujets'; ?></td>
<td><?php echo $language ? 'Author':'Auteur'; ?></td>
<td><?php echo $language ? 'Msgs nb':'Nb msgs'; ?></td>
<td><?php echo $language ? 'Last message':'Dernier message'; ?></td>
</tr>
<?php
function toSQLSearch($search) {
    $search = str_replace('"', '""', $search);
    $search = str_replace('\\', '\\\\\\\\', $search);
    $search = str_replace('%', '\\%', $search);
    $search = '%'. $search .'%';
    return $search;
}
require_once('getRights.php');
$page = isset($_GET['page'])&&is_numeric($_GET['page']) ? $_GET['page']:1;
$RES_PER_PAGE = 50;
$getNbTopics = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mktopics` WHERE titre LIKE "'. toSQLSearch($content) .'" AND language='. "language" . (hasRight('manager') ? '':' AND !private')));
$nbTopics = $getNbTopics['nb'];
$topics = mysql_query(
	'SELECT t.id,t.titre,t.dernier,t.nbMsgs, m.auteur, j.nom
	FROM `mktopics` t LEFT JOIN `mkmessages` m ON m.topic=t.id AND m.id=1
	LEFT JOIN `mkjoueurs` j ON m.auteur=j.id
	WHERE t.titre LIKE "'. toSQLSearch($content) .'" AND t.language='. "language" . (hasRight('manager') ? '':' AND !private') .'
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
<a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
<a href="index.php"><?php echo $language ? 'Back to home':'Retour &agrave; l\'accueil'; ?></a>
</p>
</main>
<?php
include('footer.php');
?>
</body>
</html>
	<?php
}
?>