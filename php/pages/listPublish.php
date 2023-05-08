<?php
if (isset($_GET['user'])) {
	include('../includes/session.php');
	include('../includes/language.php');
	include('../includes/initdb.php');
	if ($getInfos = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $_GET['user'] .'"'))) {
		?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'News published by':'News publiées par'; ?> <?php echo $getInfos['nom']; ?> - Mario Kart PC</title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/profil.css" />
<style type="text/css">
.published-news {
	text-align: center;
}
.commentPages {
	width: 95%;
}
</style>

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
	function controlLength($str,$maxLength) {
		$pts = '...';
		if (strlen($str) > $maxLength)
			return substr($str,0,$maxLength-strlen($pts)).$pts;
		return $str;
	}
	$today = time();
	$news = array();
	$getNews = mysql_query('SELECT n.id,n.title,n.nbcomments,
			name'. $language .' AS name,author,
			category,c.name'. $language .' AS catname,
			n.publication_date
			FROM `mknews` n
			INNER JOIN `mkcats` c ON n.category=c.id
			WHERE author="'. $_GET['user'] .'" AND status="accepted"
			ORDER BY n.publication_date DESC
		');
	while ($new = mysql_fetch_array($getNews))
		$news[] = $new;
	$nbNews = count($news);
	$page = isset($_GET['page']) ? max(intval($_GET['page']),1):1;
	$newsPerPage = 30;
	$news = array_slice($news,($page-1)*$newsPerPage,$newsPerPage);
	$nbPages = ceil($nbNews/$newsPerPage);
	?>
	<h1><?php echo $language ? 'News published by':'News publiées par'; ?> <?php echo $getInfos['nom']; ?> (<?php echo $nbNews; ?>)</h1>
	<div class="published-news">
	<?php
	require_once('../includes/utils-date.php');
	foreach ($news as $new) {
		$nbMsgs = $new['nbcomments'];
		?>
		<a href="news.php?id=<?php echo $new['id']; ?>" title="<?php echo $new['title']; ?>">
			<h2><?php echo htmlspecialchars(controlLength($new['title'],40)); ?></h2>
			<h3><?php echo $language ? 'In':'Dans'; ?> <strong><?php echo $new['catname']; ?></strong> <?php echo pretty_dates_short($new['publication_date'],array('lower'=>true)); ?></h3>
			<div class="creation_comments" title="<?php echo $nbMsgs. ' ' . ($language ? 'comment':'commentaire') . (($nbMsgs>1) ? 's':''); ?>"><img src="images/comments.png" alt="Messages" /> <?php echo $nbMsgs; ?></div>
		</a>
		<?php
	}
	?>
	</div>
	<?php
	if ($nbPages > 1) {
		?>
		<div class="commentPages"><p>
			Page : <?php
			$get = $_GET;
			for ($i=1;$i<=$nbPages;$i++) {
				$get['page'] = $i;
				if ($i == $page)
					echo $i;
				else
					echo '<a href="?user='. urlencode($_GET['user']) .'&amp;'. http_build_query($get) .'">'. $i .'</a>';
				echo ' &nbsp; ';
			}
			?>
		</p></div>
		<?php
	}
	?>
	<div class="comments-list">
		<a href="profil.php?id=<?php echo urlencode($_GET['user']); ?>"><?php echo $language ? 'Back to the profile':'Retour au profil'; ?></a><br />
		<a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a>
	</div>
</main>
		<?php
		include('../includes/footer.php');
	}
	mysql_close();
}
?>
</body>
</html>