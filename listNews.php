<?php
include('language.php');
include('session.php');
include('initdb.php');
require_once('getRights.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>News Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/news.css?reload=1" />
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
<h1>Mario Kart PC - Liste des news</h1>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Forum MKPC -->
<p class="pub"><ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-1340724283777764"
     data-ad-slot="4919860724"></ins></p>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
<form method="get" action="listNews.php" class="news-search">
	<p>
		<label for="search-content">
			<?php echo $language ? 'Search':'Recherche'; ?>:
		</label>
		<input type="text" id="search-content" placeholder="<?php echo $language ? 'News title':'Titre de la news'; ?>" name="search" value="<?php echo isset($_GET['search']) ? $_GET['search']:''; ?>" />
		<input type="submit" value="Ok" class="action_button" />
	</p>
</form>
<p class="newsButtons">
<?php
if ($id)
	echo '<a href="addNews.php" class="action_button">'. ($language ? 'Add a news':'Créer une news') .'</a>';
?>
</p>
<table class="listNews listPublish">
<col class="listNews-types" />
<col class="listNews-infos" />
<col class="listNews-author" />
<col class="listNews-nbcoms news-nopo" />
<col class="news-nomo listNews-publish" />
<tr class="listNews-titres">
<td><?php echo $language ? 'Type':'Type'; ?></td>
<td><?php echo $language ? 'Info':'Info'; ?></td>
<td><?php echo $language ? 'Author':'Auteur'; ?></td>
<td class="news-nopo"><?php echo $language ? 'Coms nb':'Nb coms'; ?></td>
<td class="news-nomo"><?php echo $language ? 'Date':'Date'; ?></td>
</tr>
<?php
require_once('utils-date.php');
$RES_PER_PAGE = 50;
$page = isset($_GET['page'])&&is_numeric($_GET['page']) ? $_GET['page']:1;
$sqlSelect = 'n.id,n.title,
	n.nbcomments,n.author,j.nom,
	name'. $language .' AS name,
	category,c.name'. $language .' AS catname,c.color,n.publication_date';
$sqlFrom = '`mknews` n
	INNER JOIN `mkcats` c ON n.category=c.id
	LEFT JOIN `mkjoueurs` j ON n.author=j.id
	WHERE status="accepted"';
if (!empty($_GET['search']))
	$sqlFrom .= ' AND title LIKE "%'. $_GET['search'] .'%"';
$sqlOrder = 'ORDER BY n.publication_date DESC LIMIT '.(($page-1)*$RES_PER_PAGE).','.$RES_PER_PAGE;
$news = mysql_query('SELECT '. $sqlSelect .' FROM '. $sqlFrom .' '. $sqlOrder);
$getNbNews = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM '. $sqlFrom));
$nbNews = $getNbNews['nb'];
for ($i=0;$pieceOfNews=mysql_fetch_array($news);$i++) {
	echo '<tr class="'. (($i%2) ? 'fonce':'clair') .'"><td style="color:'. $pieceOfNews['color'] .'">';
		echo $pieceOfNews['catname'];
	echo '</td><td>';
		echo '<a class="fulllink" href="news.php?id='. $pieceOfNews['id'] .'">'. $pieceOfNews['title'] .'</a>';
	echo '</td><td class="news-publisher">';
		echo $pieceOfNews['nom'] ? '<a href="profil.php?id='.$pieceOfNews['author'].'">'.$pieceOfNews['nom'].'</a>':'<em>'. ($language ? 'Deleted account':'Compte supprimé') .'</em>';
	echo '</td><td class="news-nopo">';
		echo $pieceOfNews['nbcomments'];
	echo '</td><td class="news-nomo">';
	echo pretty_dates($pieceOfNews['publication_date']);
	echo '</td></tr>';
}
if (!$i) {
	echo '<tr class="clair">';
	echo '<td colspan="3">'. ($language ? 'No result found for this search':'Aucun résultat trouvé pour cette recherche') .'</td>';
	echo '<td class="news-nopo"></td>';
	echo '<td class="news-nomo"></td>';
	echo '</tr>';
}
?>
</table>
<?php
$nbPages = ceil($nbNews/$RES_PER_PAGE);
if ($nbPages > 1) {
	?>
	<div class="newsPages"><p>
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
if ($id) {
	$getPendingNews = mysql_query('SELECT n.id,n.title,
		name'. $language .' AS name,
		category,c.name'. $language .' AS catname,c.color,
		status
		FROM `mknews` n
		INNER JOIN `mkcats` c ON n.category=c.id
		WHERE author="'. $id .'" AND status!="accepted"
		ORDER BY id DESC
	');
	if (mysql_numrows($getPendingNews)) {
		?>
		<h2>Vos news en attente</h2>
		<table class="listNews">
		<col class="listNews-cats" />
		<col class="listNews-infos" />
		<col class="listNews-dates" />
		<tr class="listNews-titres">
		<td><?php echo $language ? 'Type':'Type'; ?></td>
		<td><?php echo $language ? 'Info':'Info'; ?></td>
		<td><?php echo $language ? 'Status':'Statut'; ?></td>
		</tr>
		<?php
		for ($i=0;$pieceOfNews=mysql_fetch_array($getPendingNews);$i++) {
			echo '<tr class="'. (($i%2) ? 'fonce':'clair') .'"><td style="color:'. $pieceOfNews['color'] .'">';
				echo $pieceOfNews['catname'];
			echo '</td><td>';
				echo '<a class="fulllink" href="news.php?id='. $pieceOfNews['id'] .'">'. $pieceOfNews['title'] .'</a>';
			echo '</td><td>';
			echo '<span class="news-status news-'.$pieceOfNews['status'].'">';
			if ($pieceOfNews['status'] == 'pending')
				echo $language ? 'Waiting for validation' : 'En attente de validation';
			elseif ($pieceOfNews['status'] == 'rejected')
				echo $language ? 'Rejected ' : 'Refusée';
			if ($pieceOfNews['status'] == 'rejected')
				echo ' <a class="news-reject-details" href="news.php?id='. $pieceOfNews['id'] .'#news-status">[?]</a>';
			echo '</span>';
			echo '</td></tr>';
		}
		?>
		</table>
		<?php
	}
}
?>
<?php
if (hasRight('publisher')) {
	$getPendingNews = mysql_query('SELECT n.id,n.title,
		n.nbcomments,n.author,j.nom,
		name'. $language .' AS name,
		category,c.name'. $language .' AS catname,c.color,n.publication_date,
		status
		FROM `mknews` n
		INNER JOIN `mkcats` c ON n.category=c.id
		LEFT JOIN `mkjoueurs` j ON n.author=j.id
		WHERE status="pending"
		ORDER BY publication_date
	');
	if (mysql_numrows($getPendingNews)) {
		?>
		<h2 id="pending-news">News en attente de validation</h2>
		<table class="listNews">
		<col class="listNews-cats" />
		<col class="listNews-infos" />
		<col class="listNews-writer" />
		<col class="news-nomo listNews-dates" />
		<tr class="listNews-titres">
		<td><?php echo $language ? 'Type':'Type'; ?></td>
		<td><?php echo $language ? 'Info':'Info'; ?></td>
		<td><?php echo $language ? 'Author':'Auteur'; ?></td>
		<td class="news-nomo"><?php echo $language ? 'Date':'Date'; ?></td>
		</tr>
		<?php
		for ($i=0;$pieceOfNews=mysql_fetch_array($getPendingNews);$i++) {
			echo '<tr class="'. (($i%2) ? 'fonce':'clair') .'"><td style="color:'. $pieceOfNews['color'] .'">';
				echo $pieceOfNews['catname'];
			echo '</td><td>';
				echo '<a class="fulllink" href="news.php?id='. $pieceOfNews['id'] .'">'. $pieceOfNews['title'] .'</a>';
			echo '</td><td class="news-publisher">';
				echo $pieceOfNews['nom'] ? '<a class="news-writer-name" href="profil.php?id='.$pieceOfNews['author'].'">'.$pieceOfNews['nom'].'</a>':'<em>'. ($language ? 'Deleted account':'Compte supprimé') .'</em>';
			echo '</td><td class="news-nomo">';
			echo pretty_dates($pieceOfNews['publication_date']);
			echo '</td></tr>';
		}
		?>
		</table>
		<?php
	}
}
?>
<p class="newsButtons">
<?php
if ($id)
	echo '<a href="addNews.php" class="action_button">'. ($language ? 'Add a news':'Créer une news') .'</a>';
?>
<a href="index.php"><?php echo $language ? 'Back to home':'Retour &agrave; l\'accueil'; ?></a>
</p>
</main>
<?php
mysql_close();
include('footer.php');
?>
</body>
</html>