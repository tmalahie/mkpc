<?php
include('../includes/session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('../includes/language.php');
include('../includes/initdb.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	mysql_close();
	exit;
}
require_once('../includes/getRights.php');
if (!hasRight('organizer')) {
	echo "Vous n'&ecirc;tes pas animateur";
	mysql_close();
	exit;
}
$editting = isset($_GET['id']);
if ($editting) {
	$award = mysql_fetch_array(mysql_query('SELECT * FROM mkawards WHERE id="'. $_GET['id'] .'"'));
	if (!$award) {
		echo 'Award not found';
		mysql_close();
		exit;
	}
}
else
	$award = array();
if (isset($_POST['name']) && isset($_POST['link']) && isset($_POST['notif_msg_en']) && isset($_POST['notif_msg_fr'])) {
	if ($editting) {
		mysql_query('UPDATE mkawards SET link="'. $_POST['link'] .'",name="'. $_POST['name'] .'",notif_msg_en="'. $_POST['notif_msg_en'] .'",notif_msg_fr="'. $_POST['notif_msg_fr'] .'" WHERE id="'. $_GET['id'] .'"');
		mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "EAward '. $_GET['id'] .'")');
		header('location: awards.php?award-edited');
	}
	else {
		$newOrdering = mysql_fetch_array(mysql_query('SELECT 1+MAX(ordering) AS ordering FROM mkawards'));
		if (!$newOrdering['ordering']) $newOrdering['ordering'] = 0;
		mysql_query('INSERT INTO mkawards SET ordering="'. $newOrdering['ordering'] .'",link="'. $_POST['link'] .'",name="'. $_POST['name'] .'",notif_msg_en="'. $_POST['notif_msg_en'] .'",notif_msg_fr="'. $_POST['notif_msg_fr'] .'"');
		mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "CAward '. mysql_insert_id() .'")');
		header('location: awards.php?award-created');
	}
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Manage awards':'Gérer les récompenses'; ?> - Mario Kart PC</title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/forms.css" />
<style type="text/css">
.advanced-search ::placeholder {
    color: #CA8;
    opacity: 1;
}
.advanced-search :-ms-input-placeholder {
    color: #CA8;
}
.advanced-search ::-ms-input-placeholder {
    color: #CA8;
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
	<form method="post" class="advanced-search" action="">
		<h1><?php
			if ($editting)
				echo $language ? 'Edit award':'Modifier récompense';
			else
				echo $language ? 'New award':'Nouvelle récompense';
		?></h1>
		<table>
			<tr>
				<td class="ligne">
					<label for="name"><?php echo $language ? 'Name':'Nom'; ?></label>
				</td>
				<td>
					<input type="text" name="name" id="name" value="<?php if (isset($award['name'])) echo htmlspecialchars($award['name']); ?>" placeholder="Oscars" required="required" />
				</td>
			</tr>
			<tr>
				<td class="ligne">
					<label for="link"><?php echo $language ? '&quot;[?]&quot; link':'Lien &quot;[?]&quot;'; ?><br /><em style="font-size:0.8em">(<?php echo $language ? 'Optional':'Facultatif'; ?>)</em></label>
				</td>
				<td>
					<input type="text" name="link" id="link" value="<?php if (isset($award['link'])) echo htmlspecialchars($award['link']); ?>" placeholder="https://mkpc.malahieude.net/news.php?id=14430" />
				</td>
			</tr>
			<tr>
				<td class="ligne">
					<label for="notif_msg_en"><?php echo $language ? 'Notification message EN':'Message de notification EN'; ?><br /><em style="font-size:0.8em">(<?php echo $language ? 'Optional':'Facultatif'; ?>)</em></label>
				</td>
				<td>
					<textarea name="notif_msg_en" id="notif_msg_en" placeholder="Congratulations for your correct prediction for MKWC 2023"><?php if (isset($award['notif_msg_en'])) echo htmlspecialchars($award['notif_msg_en']); ?></textarea>
				</td>
			</tr>
			<tr>
				<td class="ligne">
					<label for="notif_msg_fr"><?php echo $language ? 'Notification message FR':'Message de notification FR'; ?><br /><em style="font-size:0.8em">(<?php echo $language ? 'Optional':'Facultatif'; ?>)</em></label>
				</td>
				<td>
					<textarea name="notif_msg_fr" id="notif_msg_fr" placeholder="Félicitations pour votre prédiction correcte à la MKWC 2023 !"><?php if (isset($award['notif_msg_fr'])) echo htmlspecialchars($award['notif_msg_fr']); ?></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input type="submit" class="action_button" value="<?php echo $language ? 'Submit':'Valider'; ?>" />
				</td>
			</tr>
		</table>
		<p class="forumButtons">
			<a href="awards.php"><?php echo $language ? 'Back to awards list':'Retour à la liste des récompenses'; ?></a><br />
			<a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
			<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a>
		</p>
	</form>
</main>
<?php
include('../includes/footer.php');
?>
<?php
mysql_close();
?>
</body>
</html>