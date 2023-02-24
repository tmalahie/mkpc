<?php
include('language.php');
include('session.php');
include('initdb.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<title><?php echo $language ? 'Advent calendar leaderboard':'Classement calendrier de l\'avent'; ?> - Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<link rel="stylesheet" type="text/css" href="styles/autocompletion.css" />

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
$year = isset($_GET['y']) ? $_GET['y'] : 2022;
$day = isset($_GET['d']) ? $_GET['d'] : null;
$get = $_GET;
unset($get['page']);
foreach ($get as $k => $getk)
	$get[$k] = stripslashes($get[$k]);
?>
<main>
	<h1><?php echo $language ? 'Advent calendar - top players':'Calendrier de l\'avent - meilleurs joueurs'; ?></h1>
	<div class="ranking-modes">
		<?php
		$yGet = $get;
		if (isset($yGet['d']))
			$yGet['d'] = 1;
		if ($year == 2018) {
			$yGet['y'] = 2022;
			?>
			<a href="?<?php echo http_build_query($yGet); ?>">2022</a><span>
			2018</span>
			<?php
		}
		else {
			$yGet['y'] = 2018;
			?>
			<span>2022</span><a
			href="?<?php echo http_build_query($yGet); ?>">2018</a>
			<?php
		}
		?>
	</div>
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
	<?php
	if ($day) {
		?>
		<form method="get" action="ranking-advent.php">
		<p>
			<?php
			$dGet = $get;
			unset($dGet['d']);
			foreach ($dGet as $k=>$val)
				echo '<input type="hidden" name="'.htmlspecialchars($k).'" value="'.htmlspecialchars($val).'" />';
			?>
			<label for="joueur"><strong><?php echo $language ? 'See day':'Voir jour'; ?></strong></label> : <select name="d" onchange="this.form.submit()">
			<?php
			if ($getMaxD = mysql_fetch_array(mysql_query('SELECT MAX(day) AS d FROM mkadvent WHERE year="'. $year .'"'))) {
				$maxD = $getMaxD['d'];
				for ($d=1;$d<=$maxD;$d++)
					echo '<option value="'.$d.'"'. ($d == $day ? ' selected="selected"' : '') .'>'.$d.'</option>';
			}
			?>
			</select> <input type="submit" value="<?php echo $language ? 'Validate':'Valider'; ?>" class="action_button" />
		</p>
		</form>
		<?php
	}
	else {
		?>
		<form method="post" action="?<?php echo http_build_query($get) ?>">
		<p><label for="joueur"><strong><?php echo $language ? 'See member':'Voir membre'; ?></strong></label> : <input type="text" name="joueur" id="joueur" value="<?php echo $joueur; ?>" /> <input type="submit" value="<?php echo $language ? 'Validate':'Valider'; ?>" class="action_button" /></p>
		</form>
		<?php
	}
	?>
	<?php
	if ($day)
		$records = mysql_query('SELECT j.id,j.nom,c.code,a.date FROM mkjoueurs j INNER JOIN mkprofiles p ON j.id=p.id LEFT JOIN mkcountries c ON c.id=p.country INNER JOIN mkadvent a ON j.id=a.user AND a.year="'.$year.'" AND a.day="'.$day.'" ORDER BY date');
	else
		$records = mysql_query('SELECT j.id,j.nom,c.code,IFNULL(COUNT(*),0) AS nb FROM mkjoueurs j INNER JOIN mkprofiles p ON j.id=p.id LEFT JOIN mkcountries c ON c.id=p.country INNER JOIN mkadvent a ON j.id=a.user AND a.year="'.$year.'"'. ($joueur ? ' WHERE j.nom="'.$joueur.'"':'').' GROUP BY j.id ORDER BY nb DESC');
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
	<td><?php
	if ($day)
		echo $language ? 'Date':'Date';
	else
		echo $language ? 'Challenges':'Défis';
	?></td>
	</tr>
	<?php
		if ($joueur) {
			$getPlaces = mysql_query('SELECT user,COUNT(*) AS nb FROM mkadvent a INNER JOIN mkjoueurs j ON a.user=j.id WHERE a.year="'.$year.'" GROUP BY user HAVING(nb>0 AND (nb>'. $record['nb'] .' OR (nb='. $record['nb'] .' AND user<'. $record['id'] .')))');
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
	<td><?php
	if ($day)
		echo $record['date'];
	else
		echo $record['nb'];
	?></td>
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
	<p>	
	<a href="?<?php
	unset($get['page']);
	$dGet = $get;
	if ($day)
		unset($dGet['d']);
	else
		$dGet['d'] = 1;
	echo http_build_query($dGet);
	?>"><?php
	if ($day)
		echo $language ? 'Global ranking':'Classement global';
	else
		echo $language ? 'Ranking day by day':'Classement jour par jour';
	?></a><br />
	<a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('footer.php');
?>
<script type="text/javascript" src="scripts/jquery.min.js"></script>
<script type="text/javascript" src="scripts/autocompletion.js"></script>
<script type="text/javascript">
function showMonth() {
	$("#month-selector").slideDown();
}
var joueurs = [<?php
$joueurs = mysql_query('SELECT nom FROM `mkjoueurs` j INNER JOIN mkadvent a ON j.id=a.user AND a.year="'.$year.'" GROUP BY j.id ORDER BY nom');
$v = false;
while ($iJoueur = mysql_fetch_array($joueurs)) {
	if ($v)
		echo ',';
	echo '"'. str_replace('"','\\"',str_replace('\\','\\\\',$iJoueur['nom'])) .'"';
	$v = true;
}
?>];
$(function() {
	autocompletion(document.forms[0].joueur, joueurs, {
		"maxResults" : 10,
		"matcher" : function(value,search) {
			return (value.toLowerCase().indexOf(search.toLowerCase()) == 0);
		},
		"onSelect" : function(id,value) {
			document.forms[0].submit();
		}
	})
});
</script>
<?php
mysql_close();
?>
</body>
</html>