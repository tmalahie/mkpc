<?php
include('language.php');
include('session.php');
include('initdb.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Time trial ranking':'Classement contre-la-montre'; ?> - Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />
<style type="text/css">
.details {
    width: 20px;
}
.details:hover {
    opacity: 0.7;
}
.ranking-modes-ctn {
	text-align: center;
	margin-bottom: 10px;
}
.ranking-modes-ctn > div {
	display: inline-flex;
	align-items: center;
}
.ranking-modes-ctn > div > span {
	font-weight: bold;
	margin-right: 6px;
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
$cc = isset($_GET['cc']) ? intval($_GET['cc']) : 150;
if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $id .'"')))
	$myPseudo = $getPseudo['nom'];
else
	$myPseudo = null;
?>
<main>
    <h1><?php echo $language ? 'Time Trial - Global ranking':'Contre-la-montre - Classement global'; ?></h1>
    <p>
    <?php
    if ($language) {
        ?>
        This page shows a leaderboard of top players in time trial.<br />
        This leaderboard is based on a score calculation which depends on your rank on each circuit. See <a href="topic.php?topic=5318">this topic</a> for further info.
        <?php
    }
    else {
        ?>
        Cette page affiche le classement des meilleurs joueurs en contre la montre.<br />
        Ce classement se base sur un calcul de score dépendant de votre place sur chaque circuit. Voir <a href="topic.php?topic=5318">ce topic</a> pour en savoir plus.
        <?php
    }
    ?>
    </p>
	<!-- Forum MKPC -->
	<div class="pub"><script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
	<!-- Mario Kart PC -->
	<ins class="adsbygoogle"
	     style="display:inline-block;width:468px;height:60px"
	     data-ad-client="ca-pub-1340724283777764"
	     data-ad-slot="6691323567"></ins>
	<script>
	(adsbygoogle = window.adsbygoogle || []).push({});
	</script></div>
	<div class="ranking-modes-ctn">
	<div>
	<span><?php echo $language ? 'Class:':'Cylindrée :'; ?></span>
	<div class="ranking-modes">
		<?php
		if ($cc == 150) {
			?>
			<span>150cc</span><a href="classement.global.php?cc=200">200cc</a>
			<?php
		}
		else {
			?>
			<a href="classement.global.php?cc=150">150cc</a><span>200cc</span>
			<?php
		}
		?>
	</div>
	</div>
	</div>
	<form method="post" action="classement.global.php?cc=<?php echo $cc; ?>">
	<blockquote>
	<p><label for="joueur"><strong><?php echo $language ? 'See player':'Voir joueur'; ?></strong></label> : <input type="text" name="joueur" id="joueur" value="<?php echo ($joueur ? $joueur:$myPseudo); ?>" /> <input type="submit" value="<?php echo $language ? 'Validate':'Valider'; ?>" class="action_button" /></p>
	</blockquote>
	</form>
	<?php
	$records = mysql_query('SELECT t.player,j.nom,t.score,c.code FROM `mkttranking` t INNER JOIN `mkjoueurs` j ON t.player=j.id INNER JOIN `mkprofiles` p ON p.id=j.id LEFT JOIN `mkcountries` c ON c.id=p.country WHERE class="'. $cc .'" AND '. ($joueur ? 'j.nom="'.$joueur.'"':'j.deleted=0') .' ORDER BY t.score DESC,t.player');
	if ($joueur) {
		if ($record = mysql_fetch_array($records))
			$nb_temps = $records ? 1:0;
		else {
			$joueur = null;
			$nb_temps = 0;
		}
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
	<td style="width:20px"><?php echo $language ? 'Details':'Détails'; ?></td>
	</tr>
	<?php
		if ($joueur) {
			$getPlaces = mysql_query('SELECT t.player FROM `mkttranking` t INNER JOIN `mkjoueurs` j ON t.player=j.id WHERE class="'. $cc .'" AND (t.score>'.$record['score'].' OR (t.score='.$record['score'].' AND t.player<'.$record['player'].')) AND j.deleted=0');
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
	<td><a href="profil.php?id=<?php echo $record['player']; ?>" class="recorder"><?php
	if ($record['code'])
		echo '<img src="images/flags/'.$record['code'].'.png" alt="'.$record['code'].'" /> ';
		echo $joueur;
	?></a></td>
	<td style="width:auto"><?php echo $record['score'] ?></td>
	<td style="width:auto" title="<?php echo $language ? 'See records':'Voir les temps'; ?>"><a href="classement.php?user=<?php echo $record['player']; ?>&amp;cc=<?php echo $cc; ?>&amp;pts"><img src="images/details.png" class="details" alt="Preview" /></a></td>
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
	<td><a href="profil.php?id=<?php echo $record['player']; ?>" class="recorder"><?php
	if ($record['code'])
		echo '<img src="images/flags/'.$record['code'].'.png" alt="'.$record['code'].'" onerror="this.style.display=\'none\'" /> ';
		echo $record['nom'];
	?></a></td>
	<td><?php echo $record['score'] ?></td>
	<td style="width:auto" title="<?php echo $language ? 'See records':'Voir les temps'; ?>"><a href="classement.php?user=<?php echo $record['player']; ?>&amp;cc=<?php echo $cc; ?>&amp;pts"><img src="images/details.png" class="details" alt="Preview" /></a></td>
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
		echo '<a href="?cc='.$cc.'&amp;page='.$page.'">'.$page.'</a>';
	}
	else {
		function pageLink($page, $isCurrent) {
			global $cc;
			echo ($isCurrent ? '<span>'.$page.'</span>' : '<a href="?cc='.$cc.'&amp;page='.$page.'">'.$page.'</a>').'&nbsp; ';
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
	<p>
        <a href="classement.php?cc=<?php echo $cc; ?>"><?php echo $language ? 'Ranking circuit by circuit':'Classement circuit par circuit'; ?></a><br />
        <a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a>
    </p>
</main>
<?php
include('footer.php');
?>
<script type="text/javascript" src="scripts/auto-complete.min.js"></script>
<script type="text/javascript" src="scripts/autocomplete-player.js"></script>
<script type="text/javascript">
autocompletePlayer('#joueur');
</script>
<?php
mysql_close();
?>
</body>
</html>