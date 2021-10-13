<?php
include('session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('language.php');
include('initdb.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	mysql_close();
	exit;
}
require_once('getRights.php');
if (!hasRight('organizer')) {
	echo "Vous n'&ecirc;tes pas animateur";
	mysql_close();
	exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Manage awards':'Gérer les récompenses'; ?> - Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<style type="text/css">
#ban_msg {
	display: none;
}
#awards {
	max-width: 350px;
}
#awarded {
	max-width: 600px;
}
.titres {
	background-color: #F90;
	font-size: 12px;
	padding: 2px;
}
.titres td:nth-child(2) {
	width: 300px;
}
.aw-table .action_button {
	font-size: 0.8em;
	padding: 3px 7px;
}
.action_button.action_delete {
	background-color: #C30;
}
.action_button.action_delete:hover {
	background-color: #820;
}
.aw-table {
	margin-bottom: 10px;
	width: 100%;
}
.aw-table a {
	color: #F60;
}
.aw-table a:hover {
	color: #F90;
}
.aw-table .action_button {
	color: white;
}
.aw-table .action_button:hover {
	color: white;
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
$ban = isset($_POST['joueur']) ? $_POST['joueur']:null;
if ($ban) {
	if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM `mkjoueurs` WHERE nom="'. $ban .'"'))) {
		mysql_query('UPDATE `mkjoueurs` SET banned=2 WHERE id='. $getId['id']);
		mysql_query('DELETE FROM `mkbans` WHERE player="'. $getId['id'] .'"');
		mysql_query('INSERT INTO `mkbans` VALUES('. $getId['id'] .',"'. $_POST['msg'] .'")');
		mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Ban '. $getId['id'] .'")');
		if (isset($_POST['ip'])) {
			$getIp = mysql_fetch_array(mysql_query('SELECT identifiant,identifiant2,identifiant3,identifiant4 FROM `mkprofiles` WHERE id="'.$getId['id'].'"'));
			mysql_query('INSERT INTO `ip_bans` VALUES('.$getId['id'].','.$getIp['identifiant'].','.$getIp['identifiant2'].','.$getIp['identifiant3'].','.$getIp['identifiant4'].')');
		}
	}
}
$unban = isset($_GET['unban']) ? $_GET['unban']:null;
if ($unban) {
	mysql_query('UPDATE `mkjoueurs` SET banned=0 WHERE id="'. $unban .'"');
	mysql_query('DELETE FROM `ip_bans` WHERE player="'. $unban .'"');
	mysql_query('DELETE FROM `mkbans` WHERE player="'. $unban .'"');
	mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Unban '. $unban .'")');
}
?>
<main>
	<?php
	if (isset($_GET['award-created']))
		$success = $language ? 'Award created successfully':'Récompense créée avec succès';
	elseif (isset($_GET['award-edited']))
		$success = $language ? 'Award edited successfully':'Récompense modifiée avec succès';
	elseif (isset($_GET['award-deleted']))
		$success = $language ? 'Award deleted successfully':'Récompense supprimée avec succès';
	elseif (isset($_GET['awarded-created']))
		$success = $language ? 'Award attributed successfully':'Récompense attribuée avec succès';
	elseif (isset($_GET['awarded-edited']))
		$success = $language ? 'Award edited successfully':'Récompense modifiée avec succès';
	elseif (isset($_GET['awarded-deleted']))
		$success = $language ? 'Award deleted successfully':'Récompense supprimée avec succès';
	if (isset($success))
		echo '<div class="success">'. $success .'</div>';
	?>
	<h1><?php echo $language ? 'Manage awards':'Gérer les récompenses'; ?></h1>
	<table class="aw-table" id="awards">
		<tr class="titres">
			<td style="width:40%"><?php echo $language ? 'Title':'Titre'; ?></td>
			<td style="width:60%"><?php echo $language ? 'Action':'Action'; ?></td>
		</tr>
	<?php
	$getAwards = mysql_query('SELECT * FROM `mkawards` ORDER BY ordering DESC');
	while ($award = mysql_fetch_array($getAwards)) {
		?>
		<tr>
			<td><?php
				echo $award['name'];
				if ($award['link'])
					echo '<sup><a href="'. htmlspecialchars($award['link']) .'">[?]</a></sup>';
			?></td>
			<td>
				<a class="action_button" href="award-edit.php?id=<?php echo $award['id']; ?>"><?php echo $language ? 'Edit':'Modifier'; ?></a>
				<a class="action_button action_delete" href="award-del.php?id=<?php echo $award['id']; ?>" onclick="return confirm('<?php echo $language ? 'Delete':'Supprimer'; ?> &quot;<?php echo addslashes(htmlspecialchars($award['name'])); ?>&quot; ?')"><?php echo $language ? 'Delete':'Supprimer'; ?></a>
			</td>
		</tr>
		<?php
	}
	?>
	</table>
	<a class="action_button" href="award-edit.php" style="margin-left: 100px">+ <?php echo $language ? 'New award':'Nouvelle récompense'; ?></a>
	<h1><?php echo $language ? 'Manage awarded people':'Gérer les membres récompensés'; ?></h1>
	<table class="aw-table" id="awarded">
		<tr class="titres">
			<td style="width: 15%"><?php echo $language ? 'Member':'Membre'; ?></td>
			<td style="width: 15%"><?php echo $language ? 'Event':'Événement'; ?></td>
			<td style="width: 40%"><?php echo $language ? 'Award(s)':'Récompense(s)'; ?></td>
			<td style="width: 30%"><?php echo $language ? 'Action':'Action'; ?></td>
		</tr>
	<?php
	$getAwarded = mysql_query('SELECT j.nom,a.name,p.user,p.award,p.value FROM mkawarded p INNER JOIN mkawards a ON p.award=a.id INNER JOIN mkjoueurs j ON p.user=j.id ORDER BY a.ordering DESC');
	while ($awarded = mysql_fetch_array($getAwarded)) {
		?>
		<tr>
			<td>
				<a href="profil.php?id=<?php echo $awarded['user']; ?>"><?php echo $awarded['nom']; ?></a>
			</td>
			<td>
				<?php echo $awarded['name']; ?>
			</td>
			<td>
				<?php echo $awarded['value']; ?>
			</td>
			<td>
				<a class="action_button" href="awarded-edit.php?user=<?php echo $awarded['user']; ?>&amp;award=<?php echo $awarded['award']; ?>"><?php echo $language ? 'Edit':'Modifier'; ?></a>
				<a class="action_button action_delete" href="awarded-del.php?user=<?php echo $awarded['user']; ?>&amp;award=<?php echo $awarded['award']; ?>" onclick="return confirm('<?php echo $language ? 'Delete':'Supprimer'; ?> &quot;<?php echo addslashes(htmlspecialchars($awarded['value'])); ?>&quot; ?')"><?php echo $language ? 'Delete':'Supprimer'; ?></a>
			</td>
		</tr>
		<?php
	}
	?>
	</table>
	<a class="action_button" href="awarded-edit.php" style="margin-left: 100px">+ <?php echo $language ? 'New assigned award':'Attribuer une récompense'; ?></a>
	<p><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('footer.php');
mysql_close();
?>
</body>
</html>