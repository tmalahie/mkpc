<?php
include('../includes/session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('../includes/language.php');
include('../includes/initdb.php');
require_once('../includes/getRights.php');
if (!hasRight('moderator')) {
	echo "Vous n'&ecirc;tes pas mod&eacute;rateur";
	mysql_close();
	exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Nick change history':'Historique changements de pseudo'; ?> - Mario Kart PC</title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />
<style type="text/css">
table a {
    color: #820;
}
table a:hover {
    color: #F60;
}
</style>
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
	<h1><?php echo $language ? 'Nick change history':'Historique changements de pseudo'; ?></h1>
	<table>
	<tr id="titres">
	<td><?php echo $language ? 'Current nick':'Pseudo actuel'; ?></td>
	<td><?php echo $language ? 'Previous nick':'Pseudo précédent'; ?></td>
	<td>Date</td>
	</tr>
	<?php
	$nickHistory = mysql_query('SELECT n.oldnick,j.id,j.nom,n.date FROM mknewnicks n INNER JOIN mkjoueurs j ON n.id=j.id ORDER BY n.date DESC LIMIT 200');
	while ($joueur = mysql_fetch_array($nickHistory)) {
		?>
		<tr>
		<td><a href="profil.php?id=<?php echo $joueur['id']; ?>"><?php echo $joueur['nom']; ?></a></td>
		<td><?php echo $joueur['oldnick']; ?></td>
		<td><?php echo $joueur['date']; ?></td>
		</tr>
		<?php
	}
	?>
	</table>
	<p><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('../includes/footer.php');
?>
<?php
mysql_close();
?>
</body>
</html>