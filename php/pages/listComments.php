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
<title><?php echo $language ? 'List of comments from':'Liste des commentaires de'; ?> <?php echo $getInfos['nom']; ?> - Mario Kart PC</title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/profil.css" />

<?php
include('../includes/o_online.php');
?>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'forum';
include('../includes/menu.php');
?>
<main>
	<div class="comments-list">
		<?php
		require_once('../includes/circuitEscape.php');
		$lastComments = mysql_query('SELECT id,circuit,type,message,date FROM `mkcomments` WHERE auteur="'. $_GET['user'] .'" ORDER BY id DESC');
		$comments = array();
		require_once('../includes/utils-cups.php');
		while ($comment = mysql_fetch_array($lastComments)) {
			if ($getCircuit = fetchCreationData($comment['type'], $comment['circuit'])) {
				$comment['circuit_data'] = $getCircuit;
				$comments[] = $comment;
			}
		}
		$nbComments = count($comments);
		$page = isset($_GET['page']) ? max(intval($_GET['page']),1):1;
		$commentsPerPage = 20;
		$comments = array_slice($comments,($page-1)*$commentsPerPage,$commentsPerPage);
		$nbPages = ceil($nbComments/$commentsPerPage);
		?>
		<h1><?php echo $language ? 'List of comments from':'Liste des commentaires de'; ?> <?php echo $getInfos['nom']; ?> (<?php echo $nbComments; ?>)</h1>
		<?php
		if ($nbComments) {
			require_once('../includes/utils-date.php');
			?>
			<div class="circuit-comments">
			<?php
			require_once('../includes/utils-cups.php');
			foreach ($comments as $comment) {
				$getCircuit = $comment['circuit_data'];
				switch ($comment['type']) {
				case 'mkmcups' :
					$url = getCupPage($getCircuit['mode']) . '.php?mid='. $getCircuit['id'];
					break;
				case 'mkcups' :
					$url = getCupPage($getCircuit['mode']) . '.php?cid='. $getCircuit['id'];
					break;
				case 'mkcircuits' :
					$url = ($getCircuit['type'] ? 'arena.php':'circuit.php') . '?id='. $getCircuit['id'];
					break;
				case 'arenes' :
					$url = 'battle.php?i='. $getCircuit['ID'];
					break;
				case 'circuits' :
					$url = 'map.php?i='. $getCircuit['ID'];
				}
				?>
				<a class="circuit-comment" href="<?php echo $url; ?>">
					<div class="circuit-comment-msg"><?php echo nl2br(htmlspecialchars($comment['message'])); ?></div>
					<div class="circuit-comment-infos"><img src="images/comments.png" alt="comments"> <?php
					if ($getCircuit['name']) {
						echo ($language ? 'In':'Dans'); ?> <strong><?php echo htmlEscapeCircuitNames($getCircuit['name']) ?></strong><?php
					}
					?> <?php echo pretty_dates($comment['date'],array('lower'=>true)); ?></div>
				</a>
				<?php
			}
			?>
			</div>
			<?php
			if ($nbPages > 1) {
				?>
				<div class="commentPages"><p>
					<?php echo $language ? 'Page: &nbsp;':'Page : ';
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
		}
		else
			echo '<h2><em>'. ($language ? 'No comment on circuits':'Aucun commentaire sur les circuits') .'</em></h2>';
		?>
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