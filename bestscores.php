<?php
include('translation.php');
include('session.php');
include('initdb.php');
$isBattle = isset($_GET['battle']);
$game = $isBattle ? 'battle':'vs';
$pts_ = 'pts_'.$game;
?>
<!DOCTYPE html>
<html lang="<?php echo languageCode(); ?>">
<head>
<title><?php echo translate('page_title', array(
	'content' => translate('online_mode_leaderboard')
)); ?></title>
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
$page = isset($_GET['page']) ? max(intval($_GET['page']),1):1;
$joueur = isset($_POST['joueur']) ? $_POST['joueur']:null;
if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $id .'"')))
	$myPseudo = $getPseudo['nom'];
else
	$myPseudo = null;
?>
<main>
	<h1><?php echo translate('leaderboard_mkpc'); ?></h1>
	<div class="ranking-modes">
		<?php
		if ($isBattle) {
			?>
			<a href="bestscores.php"><?php echo translate('vs_mode'); ?></a><span>
			<?php echo translate('battle_mode'); ?></span>
			<?php
		}
		else {
			?>
			<span><?php echo translate('vs_mode'); ?></span><a
			href="bestscores.php?battle"><?php echo translate('battle_mode'); ?></a>
			<?php
		}
		?>
	</div>
	<form method="post" action="bestscores.php<?php if ($isBattle) echo '?battle'; ?>">
	<blockquote>
	<p><label for="joueur"><strong><?php echo translate('see_player'); ?></strong></label> : <input type="text" name="joueur" id="joueur" value="<?php echo ($joueur ? $joueur:$myPseudo); ?>" /> <input type="submit" value="<?php echo translate('validate'); ?>" class="action_button" /></p>
	</blockquote>
	</form>
	<?php
	$RES_PER_PAGE = 20;
	$offset = ($page-1)*$RES_PER_PAGE;
	$where = $joueur ? 'j.nom="'.$joueur.'"':'(j.'.$pts_.'!=5000) AND j.deleted=0';
	$records = mysql_query('SELECT j.id,j.nom,j.'.$pts_.' AS pts,c.code FROM `mkjoueurs` j INNER JOIN `mkprofiles` p ON p.id=j.id LEFT JOIN `mkcountries` c ON c.id=p.country WHERE '. $where .' ORDER BY j.'.$pts_.' DESC,j.id LIMIT '. $offset .','.$RES_PER_PAGE);
	if ($joueur) {
		if ($record = mysql_fetch_array($records))
			$nb_temps = $records ? 1:0;
		else {
			$joueur = null;
			$nb_temps = 0;
		}
	}
	else {
		$countPlayers = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkjoueurs` j WHERE '. $where));
		$nb_temps = $countPlayers['nb'];
	}
	if ($nb_temps) {
	?>
	<table>
	<tr id="titres">
	<td>Place</td>
	<td><?php echo translate('nick'); ?></td>
	<td>Score</td>
	</tr>
	<?php
		if ($joueur) {
			$getPlaces = mysql_query('SELECT j.id,j.nom FROM `mkjoueurs` j WHERE (j.'.$pts_.'!=5000) AND (j.'.$pts_.'>'. $record['pts'] .' OR (j.'.$pts_.'='. $record['pts'] .' AND j.id<'. $record['id'] .')) AND j.deleted=0');
			$place = 1+mysql_numrows($getPlaces);
			$page = 0;
		}
		else
			$place = $offset;
		$i = 0;
		$fin = $place+$RES_PER_PAGE;
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
	<td><?php echo $record['pts'] ?></td>
	</tr>
		<?php
		}
		else {
			while ($record=mysql_fetch_array($records)) {
				$i++;
				$place++;
				?>
	<tr class="<?php echo (($i%2) ? 'clair':'fonce') ?>">
	<td><?php print_rank($place); ?></td>
	<td><a href="profil.php?id=<?php echo $record['id']; ?>" class="recorder"><?php
	if ($record['code'])
		echo '<img src="images/flags/'.$record['code'].'.png" alt="'.$record['code'].'" onerror="this.style.display=\'none\'" /> ';
		echo $record['nom'];
	?></a></td>
	<td><?php echo $record['pts'] ?></td>
	</tr>
				<?php
			}
		}
	?>
	<tr><td colspan="4" id="page"><strong>Page : </strong> 
	<?php
	if ($joueur) {
		$page = ceil($place/$RES_PER_PAGE);
		echo '<a href="?'. ($isBattle ? 'battle&amp;':'') .'page='.$page.'">'.$page.'</a>';
	}
	else {
		function pageLink($page, $isCurrent) {
			global $isBattle;
			echo ($isCurrent ? '<span>'.$page.'</span>' : '<a href="?'. ($isBattle ? 'battle&amp;':'') .'page='.$page.'">'.$page.'</a>').'&nbsp; ';
		}
		$limite = ceil($nb_temps/$RES_PER_PAGE);
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
		echo '<p><strong>'. translate('no_search_result') .'</strong></p>';
	?>
	</table>
	<p><a href="online.php<?php echo ($isBattle ? '?battle':''); ?>"><?php echo translate('back_online_home'); ?></a><br />
	<a href="index.php"><?php echo translate('back_mkpc'); ?></a></p>
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