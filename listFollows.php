<?php
if (isset($_GET['user'])) {
	include('session.php');
	include('language.php');
	include('initdb.php');
	if ($getInfos = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $_GET['user'] .'"'))) {
		?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/profil.css" />
<style type="text/css">
.following-topics {
	text-align: center;
}
</style>

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
	require_once('getRights.php');
	function controlLength($str,$maxLength) {
		$pts = '...';
		if (mb_strlen($str) > $maxLength)
			return mb_substr($str,0,$maxLength-mb_strlen($pts)).$pts;
		return $str;
	}
	$today = time();
	$getTopics = mysql_query('SELECT id,titre,dernier, nbmsgs FROM `mkfollowers` INNER JOIN `mktopics` ON topic=id WHERE user="'. $_GET['user'] .'" AND NOT EXISTS(SELECT * FROM mkmessages WHERE id=1 AND mkfollowers.topic=mkmessages.topic AND user=auteur)'. (hasRight('manager') ? '':' AND !private') .' ORDER BY dernier DESC');
	//$getTopics = mysql_query('SELECT id,titre,dernier, nbmsgs FROM `mkfollowers` INNER JOIN `mktopics` ON topic=id WHERE user=user AND NOT EXISTS(SELECT * FROM mkmessages WHERE id=1000 AND mkfollowers.topic=mkmessages.topic AND user=auteur)'. (hasRight('manager') ? '':' AND !private') .' ORDER BY dernier DESC');
	$topics = array();
	while ($topic = mysql_fetch_array($getTopics))
		$topics[] = $topic;
	$nbTopics = count($topics);
	$page = isset($_GET['page']) ? $_GET['page']:1;
	$topicsPerPage = 30;
	$topics = array_slice($topics,($page-1)*$topicsPerPage,$topicsPerPage);
	$nbPages = ceil($nbTopics/$topicsPerPage);
	?>
	<h1><?php echo $language ? 'Topics followed by':'Topics suivis par'; ?> <?php echo $getInfos['nom']; ?> (<?php echo $nbTopics; ?>)</h1>
	<div class="following-topics">
	<?php
	require_once('utils-date.php');
	foreach ($topics as $topic) {
		if ($auteur = mysql_fetch_array(mysql_query('SELECT auteur FROM `mkmessages` WHERE topic='. $topic['id'] .' ORDER BY id DESC LIMIT 1'))) {
			$name = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id='. $auteur['auteur']));
			$nbMsgs = $topic['nbmsgs'];
			?>
			<a href="topic.php?topic=<?php echo $topic['id']; ?>" title="<?php echo $topic['titre']; ?>">
				<h2><?php echo htmlspecialchars(controlLength($topic['titre'],40)); ?></h2>
				<h3><?php echo $language ? 'Last message':'Dernier message'; ?> <?php echo ($name ? ($language ? 'by':'par') .' <strong>'. $name['nom'].'</strong> ':'').pretty_dates_short($topic['dernier'],array('lower'=>true)); ?></h3>
				<div class="creation_comments" title="<?php echo $nbMsgs. ' message'. (($nbMsgs>1) ? 's':''); ?>"><img src="images/comments.png" alt="Messages" /> <?php echo $nbMsgs; ?></div>
			</a>
			<?php
		}
	}
	?>
	</div>
	<?php
	if ($nbPages > 1) {
		?>
		<div class="topicPages"><p>
			Page : <?php
			$get = $_GET;
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
	<div class="comments-list">
		<a href="profil.php?id=<?php echo urlencode($_GET['user']); ?>"><?php echo $language ? 'Back to the profile':'Retour au profil'; ?></a><br />
		<a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a>
	</div>
</main>
		<?php
		include('footer.php');
	}
	mysql_close();
}
?>
</body>
</html>