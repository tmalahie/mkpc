<?php
include('language.php');
include('session.php');
include('initdb.php');
$countryId = null;
if (isset($_GET['country'])) {
	$country = $_GET['country'];
	$_POST['country'] = $country;
	if ($getCountryId = mysql_fetch_array(mysql_query('SELECT id FROM mkcountries WHERE code="'. $country .'"')))
		$countryId = $getCountryId['id'];
}
$sort = isset($_GET['sort']) ? $_GET['sort']:null;
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />

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
$place = ($page-1)*20;
?>
<main>
	<h1><?php echo $language ? 'Search member by country':'Rechercher membre par pays'; ?></h1>
	<form method="get" action="findByCountry.php">
	<blockquote>
		<div style="margin: 4px 0"><label><?php echo $language ? 'Country:':'Pays :'; ?>
		<select name="country">
		<?php
		include('list-countries.php');
		?>
		</select></div>
		<div style="margin: 4px 0"><label><?php echo $language ? 'Sort by:':'Trier par :'; ?>
		<select name="sort">
			<option value="connect"><?php echo $language ? 'Last connect':'Dernière connexion'; ?>
			<option value="pts"<?php if ($sort == 'pts') echo ' selected="selected"'; ?>><?php echo $language ? 'Online score':'Score en ligne'; ?>
		</select></div>
		<div style="margin-top: 8px">
			<input type="submit" class="action_button" value="<?php echo $language ? 'Search':'Rechercher'; ?>" />
		</div>
	</blockquote>
	</form>
	<?php
	$queries = array();
	for ($i=0;$i<2;$i++)
		$queries[] = 'SELECT '. ($i ? 'COUNT(*) AS nb':'j.id,j.nom,j.pts_vs AS pts,c.code,DATE(p.last_connect) AS last_connect') .' FROM `mkjoueurs` j INNER JOIN `mkprofiles` p ON p.id=j.id LEFT JOIN `mkcountries` c ON c.id=p.country WHERE j.deleted=0'. ($countryId ? ' AND p.country="'. $countryId .'"':'') .' ORDER BY '. ($sort=='pts' ? 'j.pts_vs':'p.last_connect') .' DESC,j.id DESC'. ($i ? '':' LIMIT '. $place.',20');
	$records = mysql_query($queries[0]);
	$getNb = mysql_fetch_array(mysql_query($queries[1]));
	$nb_temps = $getNb['nb'];
	?>
	<table>
	<tr id="titres">
	<td><?php echo $language ? 'Nick':'Pseudo'; ?></td>
	<td><?php echo $language ? 'Online score':'Score en ligne'; ?></td>
	<td><?php echo $language ? 'Last connection':'Dernière connexion'; ?></td>
	</tr>
	<?php
	$i = 0;
	while ($record=mysql_fetch_array($records)) {
		$i++;
		?>
	<tr class="<?php echo (($i%2) ? 'clair':'fonce') ?>">
	<td><a href="profil.php?id=<?php echo $record['id']; ?>" class="recorder"><?php
	if ($record['code'])
		echo '<img src="images/flags/'.$record['code'].'.png" alt="'.$record['code'].'" onerror="this.style.display=\'none\'" /> ';
		echo $record['nom'];
	?></a></td>
	<td><?php echo $record['pts'] ?></td>
	<td><?php echo $record['last_connect'] ?></td>
	</tr>
		<?php
	}
	?>
	<tr><td colspan="3" id="page"><strong>Page : </strong> 
	<?php
	function pageLink($page, $isCurrent) {
		$get = $_GET;
		$get['page'] = $page;
		echo ($isCurrent ? '<span>'.$page.'</span>' : '<a href="?'. http_build_query($get) .'"">'.$page.'</a>').'&nbsp; ';
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
	?>
	</td></tr>
	</table>
	<p>
		<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a>
	</p>
</main>
<?php
include('footer.php');
mysql_close();
?>
</body>
</html>