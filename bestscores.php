<?php
include('language.php');
include('session.php');
include('initdb.php');
$isBattle = isset($_GET['battle']);
$game = $isBattle ? 'battle':'vs';
$pts_ = 'pts_'.$game;
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />

<?php
include('o_online.php');
?>
</head>
<body>
<?php
include('header.php');
$page = 'game';
include('menu.php');
$page = isset($_GET['page']) ? $_GET['page']:1;
$joueur = isset($_POST['joueur']) ? $_POST['joueur']:null;
if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $id .'"')))
	$myPseudo = $getPseudo['nom'];
else
	$myPseudo = null;
?>
<main>
	<h1><?php echo $language ? 'Ranking Mario Kart PC':'Classement Mario Kart PC'; ?></h1>
	<div class="ranking-modes">
		<?php
		if ($isBattle) {
			?>
			<a href="bestscores.php"><?php echo $language ? 'VS mode':'Course VS'; ?></a><span>
			<?php echo $language ? 'Battle mode':'Bataille de ballons'; ?></span>
			<?php
		}
		else {
			?>
			<span><?php echo $language ? 'VS mode':'Course VS'; ?></span><a
			href="bestscores.php?battle"><?php echo $language ? 'Battle mode':'Bataille de ballons'; ?></a>
			<?php
		}
		?>
	</div>
	<form method="post" action="bestscores.php<?php if ($isBattle) echo '?battle'; ?>">
	<blockquote>
	<p><label for="joueur"><strong><?php echo $language ? 'See player':'Voir joueur'; ?></strong></label> : <input type="text" name="joueur" id="joueur" value="<?php echo ($joueur ? $joueur:$myPseudo); ?>" /> <input type="submit" value="<?php echo $language ? 'Validate':'Valider'; ?>" class="action_button" /></p>
	</blockquote>
	</form>
	<?php
	$records = mysql_query('SELECT j.id,j.nom,j.'.$pts_.' AS pts,c.code FROM `mkjoueurs` j INNER JOIN `mkprofiles` p ON p.id=j.id LEFT JOIN `mkcountries` c ON c.id=p.country WHERE '. ($joueur ? 'j.nom="'.$joueur.'"':'(j.'.$pts_.'!=5000) AND j.deleted=0') .' ORDER BY j.'.$pts_.' DESC,j.id');
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
			$getPlaces = mysql_query('SELECT j.id,j.nom FROM `mkjoueurs` j WHERE (j.'.$pts_.'!=5000) AND (j.'.$pts_.'>'. $record['pts'] .' OR (j.'.$pts_.'='. $record['pts'] .' AND j.id<'. $record['id'] .')) AND j.deleted=0');
			$place = 1+mysql_numrows($getPlaces);
			$page = 0;
		}
		else
			$place = ($page-1)*20;
		$i = 0;
		$fin = $place+20;
		if ($joueur) {
		?>
	<tr class="clair">
	<td><?php echo $place .'<sup>e'. ($place>1 ? null:'r') .'</sup>' ?></td>
	<td><a href="profil.php?id=<?php echo $record['id']; ?>" class="recorder"><?php
	if ($record['code'])
		echo '<img src="images/flags/'.$record['code'].'.png" alt="'.$record['code'].'" /> ';
		echo $joueur;
	?></a></td>
	<td><?php echo $record['pts'] ?></td>
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
	<td><?php
		echo $place .'<sup>';
		if ($language) {
			$centaines = $place%100;
			if (($centaines >= 10) && ($centaines < 20))
				echo 'th';
			else {
				switch ($place%10) {
				case 1 :
					echo 'st';
					break;
				case 2 :
					echo 'nd';
					break;
				case 3 :
					echo 'rd';
					break;
				default :
					echo 'th';
				}
			}
		}
		else
			echo 'e'. ($place>1 ? null:'r');
		echo '</sup>';
	?></td>
	<td><a href="profil.php?id=<?php echo $record['id']; ?>" class="recorder"><?php
	if ($record['code'])
		echo '<img src="images/flags/'.$record['code'].'.png" alt="'.$record['code'].'" onerror="this.style.display=\'none\'" /> ';
		echo $record['nom'];
	?></a></td>
	<td><?php echo $record['pts'] ?></td>
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
		echo '<a href="?'. ($isBattle ? 'battle&amp;':'') .'page='.$page.'">'.$page.'</a>';
	}
	else {
		function pageLink($page, $isCurrent) {
			global $isBattle;
			echo ($isCurrent ? '<span>'.$page.'</span>' : '<a href="?'. ($isBattle ? 'battle&amp;':'') .'page='.$page.'">'.$page.'</a>').'&nbsp; ';
		}
		$limite = ceil($nb_temps/20);
		if ($limite >= 10) {
			$intervalle = 3;
			$debut = $page-$intervalle;
			if ($debut <= 1)
				$debut = 1;
			else {
				pageLink(1, false);
				if ($debut != 2)
					echo '...&nbsp; ';
			}
			$fin = $debut + $intervalle*2;
			if ($fin > $limite) {
				$fin = $limite;
				$debut = $fin-$intervalle*2;
			}
			for ($i=$debut;$i<=$fin;$i++)
				pageLink($i, $i==$page);
			if ($fin < $limite) {
				if ($fin != ($limite-1))
					echo '...&nbsp; ';
				pageLink($limite, false);
			}
		}
		else {
			for ($i=1;$i<=$limite;$i++)
				pageLink($i, $i==$page);
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
	<p><a href="online.php<?php echo ($isBattle ? '?battle':''); ?>"><?php echo $language ? 'Back to the online mode home':'Retour &agrave; l\'accueil du mode en ligne'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
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