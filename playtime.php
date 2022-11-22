<?php
include('language.php');
include('session.php');
include('initdb.php');
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
?>
<main>
	<h1><?php echo $language ? 'Members playtime':'Temps de jeu des membres'; ?></h1>
	<?php
    $members = array(1,3586,6013,32396,40764,49980);
	$getPlayTimes = mysql_query('SELECT j.nom,g.player,SUM(time) AS playtime FROM mkgametime g INNER JOIN mkjoueurs j ON g.player=j.id WHERE g.player IN ('. implode(',',$members) .') GROUP BY g.player ORDER BY playtime DESC');
	?>
	<table>
	<tr id="titres">
	<td><?php echo $language ? 'Nick':'Pseudo'; ?></td>
	<td><?php echo $language ? 'Play time':'Temps de jeu'; ?></td>
	</tr>
	<?php
	$i = 0;
	while ($record=mysql_fetch_array($getPlayTimes)) {
		$i++;
		?>
	<tr class="<?php echo (($i%2) ? 'clair':'fonce') ?>">
	<td><a href="profil.php?id=<?php echo $record['player']; ?>" class="recorder"><?php
		echo $record['nom'];
	?></a></td>
	<td><?php
    $playTimeInH = $record['playtime']/3600000;
    $playTimeInH = round($playTimeInH*4)/4;
    $h = floor($playTimeInH);
    $m = round(60*($playTimeInH-$h));
    if ($m < 10) $m = "0$m";
    echo $h.':'.$m;
    ?></td>
	</tr>
		<?php
	}
	?>
	</td></tr>
	</table>
    <br />
</main>
<?php
include('footer.php');
mysql_close();
?>
</body>
</html>