<?php
header('location: /leaderboard/challenges?'. $_SERVER['QUERY_STRING']);
include('language.php');
include('session.php');
include('initdb.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Challenges leaderboard':'Classement défis'; ?> - Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />
<style type="text/css">
#ranking_explain {
	max-width: 650px;
	margin-left: auto;
	margin-right: auto;
	text-align: justify;
}
#ranking_info {
	display: none;
	margin-top: 10px;
}
#ranking_info ul {
	margin: 5px 0;
	padding-left: 25px;
}
</style>

<?php
include('o_online.php');
?>
</head>
<body>
<?php
include('header.php');
$page = 'game';
include('menu.php');
$page = isset($_GET['page']) ? max(intval($_GET['page']),1):1;
$joueur = isset($_POST['joueur']) ? $_POST['joueur']:null;
if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $id .'"')))
	$myPseudo = $getPseudo['nom'];
else
	$myPseudo = null;
$get = $_GET;
foreach ($get as $k => $getk)
	$get[$k] = stripslashes($get[$k]);
?>
<main>
	<h1><?php echo $language ? 'Challenge points - Leaderboard':'Classement des points défis'; ?></h1>
	<p>
		<?php
		require_once('challenge-consts.php');
		if ($language) {
			?>
			<div id="ranking_explain">
				This page displays the ranking of the players with the most points in the MKPC challenge mode
				<a href="#null" onclick="document.getElementById('ranking_info').style.display=document.getElementById('ranking_info').style.display?'':'block';return false" style="position:relative;top:-1px">[Read more]</a>.
				<div id="ranking_info">
					<a href="challengesList.php">Challenges</a> are actions to perform in the game (Ex: &quot;Complete a track in less than 1:30&quot;).
					They are created by members thanks to the <strong>challenge editor</strong>. Anyone can create challenges, including you!<br />
					When you complete a challenge, you win a certain amount of <strong>challenge points</strong> depending on the difficulty of the challenge. Your position in the ranking is determined by your number of challenge points.
					<ul>
						<?php
						$challengeDifficulties = getChallengeDifficulties();
						$challengeRewards = getChallengeRewards();
						foreach ($challengeDifficulties as $i=>$difficulty)
							echo '<li>A challenge <strong>'. $difficulty .'</strong> gives you <strong>'. $challengeRewards[$i] .' pt'. ($challengeRewards[$i]>=2 ? 's':'') .'</strong>.</li>';
						?>
					</ul>
				</div>
			</div>
			<?php
		}
		else {
			?>
			<div id="ranking_explain">
				Cette page affiche le classement des joueurs ayant le plus de points dans le mode défis de MKPC
				<a href="#null" onclick="document.getElementById('ranking_info').style.display=document.getElementById('ranking_info').style.display?'':'block';return false" style="position:relative;top:-1px">[En savoir plus]</a>.
				<div id="ranking_info">
					Les <a href="challengesList.php">défis</a> sont des actions à réaliser sur le jeu (Ex : &quot;Finir un circuit en moins de 1:30&quot;).
					Ils sont créés par les membres via l'<strong>éditeur de défis</strong>. N'importe qui peut créer des défis, vous aussi !<br />
					Lorsque vous réussissez un défi, vous gagnez un certain nombre de <strong>points défis</strong> en fonction de la difficulté. Ce sont ces points défis qui déterminent votre place dans le classement.
					<ul>
						<?php
						$challengeDifficulties = getChallengeDifficulties();
						$challengeRewards = getChallengeRewards();
						foreach ($challengeDifficulties as $i=>$difficulty)
							echo '<li>Un défi <strong>'. $difficulty .'</strong> rapporte <strong>'. $challengeRewards[$i] .' pt'. ($challengeRewards[$i]>=2 ? 's':'') .'</strong>.</li>';
						?>
					</ul>
				</div>
			</div>
			<?php
		}
		?>
	</p>
	<!-- Forum MKPC -->
	<p class="pub"><script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
	<!-- Mario Kart PC -->
	<ins class="adsbygoogle"
	     style="display:inline-block;width:468px;height:60px"
	     data-ad-client="ca-pub-1340724283777764"
	     data-ad-slot="6691323567"></ins>
	<script>
	(adsbygoogle = window.adsbygoogle || []).push({});
	</script></p>
	<form method="post" action="challengeRanking.php">
	<p><label for="joueur"><strong><?php echo $language ? 'See player':'Voir joueur'; ?></strong></label> : <input type="text" name="joueur" id="joueur" value="<?php echo ($joueur ? $joueur:$myPseudo); ?>" /> <input type="submit" value="<?php echo $language ? 'Validate':'Valider'; ?>" class="action_button" /></p>
	</form>
	<?php
	$records = mysql_query('SELECT j.id,j.nom,j.pts_challenge AS nb,c.code FROM `mkprofiles` p INNER JOIN `mkjoueurs` j ON p.id=j.id LEFT JOIN `mkcountries` c ON c.id=p.country WHERE '. ($joueur ? 'j.nom="'.$joueur.'"':'j.pts_challenge>0 AND j.deleted=0') .' ORDER BY j.pts_challenge DESC,j.id');
	if ($joueur) {
		if ($record = mysql_fetch_array($records))
			$nb_temps = $records ? 1:0;
		else
			$joueur = null;
	}
	else
		$nb_temps = mysql_numrows($records);
	if ($nb_temps) {
	?>
	<table>
	<tr id="titres">
	<td>Place</td>
	<td><?php echo $language ? 'Nick':'Pseudo'; ?></td>
	<td>Score</td>
	</tr>
	<?php
		if ($joueur) {
			$getPlaces = mysql_query('SELECT id FROM `mkjoueurs` WHERE (pts_challenge>0) AND (pts_challenge>'. $record['nb'] .' OR (pts_challenge='. $record['nb'] .' AND id<'. $record['id'] .')) AND deleted=0');
			$place = 1+mysql_numrows($getPlaces);
			$page = 0;
		}
		else
			$place = ($page-1)*20;
		$i = 0;
		$fin = $place+20;
		require_once('utils-leaderboard.php');
		if ($joueur) {
		?>
	<tr class="clair">
	<td><?php print_rank($place); ?></td>
	<td><a href="profil.php?id=<?php echo $record['id']; ?>" class="recorder"><?php
	if ($record['code'])
		echo '<img src="images/flags/'.$record['code'].'.png" alt="'.$record['code'].'" /> ';
		echo $joueur;
	?></a></td>
	<td><?php echo $record['nb'] ?></td>
	</tr>
		<?php
		}
		else {
			while ($record=mysql_fetch_array($records)) {
				$i++;
				if ($i > $place) {
					$place++;
					?>
	<tr class="<?php echo (($i%2) ? 'clair':'fonce') ?>">
	<td><?php print_rank($place); ?></td>
	<td><a href="profil.php?id=<?php echo $record['id']; ?>" class="recorder"><?php
	if ($record['code'])
		echo '<img src="images/flags/'.$record['code'].'.png" alt="'.$record['code'].'" onerror="this.style.display=\'none\'" /> ';
		echo $record['nom'];
	?></a></td>
	<td><?php echo $record['nb'] ?></td>
	</tr>
					<?php
					if ($i == $fin)
						break;
				}
			}
		}
	?>
	<tr><td colspan="4" id="page"><strong>Page : </strong> 
	<?php
	if ($joueur) {
		$page = ceil($place/20);
		$get['page'] = $page;
		echo '<a href="?'. http_build_query($get) .'">'.$page.'</a>';
	}
	else {
		function pageLink($page, $isCurrent) {
			global $get;
			$get['page'] = $page;
			echo ($isCurrent ? '<span>'.$page.'</span>' : '<a href="?'. http_build_query($get) .'">'.$page.'</a>').'&nbsp; ';
		}
		$limite = ceil($nb_temps/20);
		require_once('utils-paging.php');
		$allPages = makePaging($page,$limite);
		foreach ($allPages as $i=>$block) {
			if ($i)
				echo '...&nbsp; ';
			foreach ($block as $p)
				pageLink($p, $p==$page);
		}
	}
	?>
	</td></tr>
	<?php
	}
	else
		echo $language ? '<p><strong>No results found for this search</strong></p>':'<p><strong>Aucun r&eacute;sultat trouv&eacute; pour cette recherche</strong></p>';
	?>
	</table>
	<p><a href="challengesList.php"><?php echo $language ? 'Back to challenge list':'Retour à la liste des défis'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('footer.php');
?>
<script type="text/javascript" src="scripts/auto-complete.min.js"></script>
<script type="text/javascript" src="scripts/autocomplete-dummy.js"></script>
<script type="text/javascript">
var joueurs = [<?php
$joueurs = mysql_query('SELECT nom FROM `mkjoueurs` WHERE pts_challenge>0 AND deleted=0 ORDER BY nom');
$v = false;
while ($iJoueur = mysql_fetch_array($joueurs)) {
	if ($v)
		echo ',';
	echo '"'. str_replace('"','\\"',str_replace('\\','\\\\',$iJoueur['nom'])) .'"';
	$v = true;
}
?>];
autocompleteDummy("#joueur", joueurs);
</script>
<?php
mysql_close();
?>
</body>
</html>