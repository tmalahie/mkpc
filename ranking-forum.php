<?php
include('language.php');
include('session.php');
include('initdb.php');
$mode = 0;
if (isset($_GET['month'])) {
	if ($_GET['month'] == 'last')
		$mode = 1;
	elseif (isset($_GET['year']) && is_numeric($_GET['month']) && is_numeric($_GET['year'])) {
		$mode = 2;
		$month = $_GET['month'];
		$year = $_GET['year'];
	}
}
$sqlFilter = '';
$thisMonth = new DateTime('now', new DateTimeZone('Europe/Paris'));
switch ($mode) {
case 1:
	$beginMonth = clone $thisMonth;
	$beginMonth->modify('first day of this month');
	$sqlFilter = 'date>="'. $beginMonth->format('Y-m-d') .'"';
	break;
case 2:
	$beginMonth = DateTime::createFromFormat('Y-m-d', $year.'-'.$month.'-01');
	if ($beginMonth) {
		$endMonth = clone $beginMonth;
		$endMonth->modify('+1 month');
		$sqlFilter = 'date>="'. $beginMonth->format('Y-m-d') .'" AND date<"'. $endMonth->format('Y-m-d') .'"';
	}
	else
		$mode = 0;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Forum activity ranking':'Classement activité forum'; ?> - Mario Kart PC</title>
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
$page = 'forum';
include('menu.php');
$page = isset($_GET['page']) ? $_GET['page']:1;
$joueur = isset($_POST['joueur']) ? $_POST['joueur']:null;
$get = $_GET;
foreach ($get as $k => $getk)
	$get[$k] = stripslashes($get[$k]);
if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $id .'"')))
	$myPseudo = $getPseudo['nom'];
else
	$myPseudo = null;
?>
<main>
	<h1><?php echo $language ? 'Ranking most active members':'Classement membres les plus actifs'; ?></h1>
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
	<div class="ranking-modes stripped">
		<?php
		$m = $thisMonth->format('m');
		$y = $thisMonth->format('Y');
		if ($language)
			$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
		else
			$months = array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
		$monthName = $months[$m-1];
		switch ($mode) {
		case 0:
			?>
			<span><?php echo $language ? 'All time':'Tout'; ?></span><a
			href="ranking-forum.php?month=last"><?php echo $monthName; ?></a><a
			href="javascript:showMonth()"><?php echo $language ? 'More...':'Plus...'; ?></a>
			<?php
			break;
		case 1:
			?>
			<a href="ranking-forum.php"><?php echo $language ? 'All time':'Tout'; ?></a><span>
			<?php echo $monthName; ?></span><a
			href="javascript:showMonth()"><?php echo $language ? 'More...':'Plus...'; ?></a>
			<?php
			break;
		case 2:
			?>
			<a href="ranking-forum.php"><?php echo $language ? 'All time':'Tout'; ?></a><a
			href="ranking-forum.php?month=last"><?php echo $monthName; ?></a><span>
			<?php echo $language ? 'More...':'Plus...'; ?></span>
			<?php
		}
		?>
	</div>
	<form method="get" id="month-selector" style="display:<?php echo ($mode==2 ? 'block':'none') ?>">
		<?php
		$params = array();
		if ($mode == 2) {
			$params['month'] = $month;
			$params['year'] = $year;
		}
		else {
			$lastMonth = clone $thisMonth;
			$lastMonth->modify('-1 month');
			$params['month'] = $lastMonth->format('m');
			$params['year'] = $lastMonth->format('Y');
		}
		?>
		<label for="month"><strong><?php echo $language ? 'Month':'Mois'; ?></strong></label> :
		<select name="month" id="month">
			<?php
			foreach ($months as $i=>$fMonth) {
				$sMonth = str_pad($i+1,2,'0',STR_PAD_LEFT);
				echo '<option'. ($sMonth==$params['month'] ? ' selected="selected"':'') .' value="'. $sMonth .'">'. $fMonth .'</option>';
			}
			?>
		</select>
		&nbsp;
		<label for="year"><strong><?php echo $language ? 'Year':'Année'; ?></strong></label> :
		<select name="year" id="year">
			<?php
			for ($i=2012;$i<=$y;$i++)
				echo '<option'. ($i==$params['year'] ? ' selected="selected"':'') .' value="'. $i .'">'. $i .'</option>';
			?>
		</select>
		<input type="submit" value="Ok" class="action_button" />
	</form>
	<form method="post" action="?<?php echo http_build_query($get) ?>">
	<p><label for="joueur"><strong><?php echo $language ? 'See member':'Voir membre'; ?></strong></label> : <input type="text" name="joueur" id="joueur" value="<?php echo ($joueur ? $joueur:$myPseudo); ?>" /> <input type="submit" value="<?php echo $language ? 'Validate':'Valider'; ?>" class="action_button" /></p>
	</form>
	<?php
	if ($mode) {
		if ($joueur)
			$records = mysql_query('SELECT j.id,j.nom,c.code,IFNULL(COUNT(m.id),0) AS nb FROM mkjoueurs j INNER JOIN mkprofiles p ON j.id=p.id LEFT JOIN mkcountries c ON c.id=p.country LEFT JOIN mkmessages m ON j.id=m.auteur AND '. $sqlFilter .' WHERE j.nom="'.$joueur.'" GROUP BY j.id');
		else
			$records = mysql_query('SELECT j.id,j.nom,c.code,m.nb FROM (SELECT auteur,COUNT(*) AS nb FROM mkmessages WHERE '. $sqlFilter .' GROUP BY auteur) m INNER JOIN mkjoueurs j ON m.auteur=j.id INNER JOIN mkprofiles p ON m.auteur=p.id LEFT JOIN mkcountries c ON c.id=p.country ORDER BY nb DESC,j.id');
	}
	else
		$records = mysql_query('SELECT j.id,j.nom,p.nbmessages AS nb,c.code FROM `mkprofiles` p INNER JOIN `mkjoueurs` j ON p.id=j.id LEFT JOIN `mkcountries` c ON c.id=p.country WHERE '. ($joueur ? 'j.nom="'.$joueur.'"':'p.nbmessages>0') .' ORDER BY p.nbmessages DESC,p.id');
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
	<td>Messages</td>
	</tr>
	<?php
		if ($joueur) {
			if ($mode)
				$getPlaces = mysql_query('SELECT auteur,COUNT(*) AS nb FROM mkmessages m INNER JOIN mkjoueurs j ON m.auteur=j.id WHERE '. $sqlFilter .' GROUP BY auteur HAVING(nb>0 AND (nb>'. $record['nb'] .' OR (nb='. $record['nb'] .' AND auteur<'. $record['id'] .')))');
			else
				$getPlaces = mysql_query('SELECT id FROM `mkprofiles` WHERE (nbmessages>0) AND (nbmessages>'. $record['nb'] .' OR (nbmessages='. $record['nb'] .' AND id<'. $record['id'] .'))');
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
	<p><a href="forum.php<?php echo ($isBattle ? '?battle':''); ?>"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('footer.php');
?>
<script type="text/javascript" src="scripts/jquery.min.js"></script>
<script type="text/javascript" src="scripts/auto-complete.min.js"></script>
<script type="text/javascript" src="scripts/autocomplete-dummy.js"></script>
<script type="text/javascript">
function showMonth() {
	$("#month-selector").slideDown();
}
var joueurs = [<?php
$joueurs = mysql_query('SELECT nom FROM `mkprofiles` p INNER JOIN `mkjoueurs` j ON j.id=p.id WHERE p.nbmessages>0 ORDER BY nom');
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