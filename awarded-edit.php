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
$editting = isset($_GET['user']) && isset($_GET['award']);
if ($editting) {
	$awarded = mysql_fetch_array(mysql_query('SELECT j.nom,a.user,a.award,a.value FROM mkawarded a INNER JOIN mkjoueurs j ON a.user=j.id WHERE user="'. $_GET['user'] .'" AND award="'. $_GET['award'] .'"'));
	if (!$awarded) {
		echo 'Award not found';
		exit;
	}
}
else
	$awarded = array();
if (isset($_POST['nom']) && isset($_POST['award']) && isset($_POST['value'])) {
	$getMember = mysql_fetch_array(mysql_query('SELECT id FROM `mkjoueurs` WHERE nom="'. $_POST['nom'] .'"'));
	$getAward = mysql_fetch_array(mysql_query('SELECT id FROM `mkawards` WHERE id="'. $_POST['award'] .'"'));
	if ($getMember && $getAward) {
		if ($editting) {
			mysql_query('UPDATE mkawarded SET user="'. $getMember['id'] .'",award="'. $getAward['id'] .'",value="'. $_POST['value'] .'" WHERE user="'. $_GET['user'] .'" AND award="'. $_GET['award'] .'"');
			mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "EAwarded '. $getMember['id'] .' '. $getAward['id'] .'")');
			header('location: awards.php?awarded-edited');
		}
		else {
			mysql_query('INSERT INTO mkawarded VALUES('.$getMember['id'].',"'. $getAward['id'] .'","'. $_POST['value'] .'")');
			mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "CAwarded '. $getMember['id'] .' '. $getAward['id'] .'")');
			header('location: awards.php?awarded-created');
		}
	}
	else
		$awarded = $_POST;
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
<link rel="stylesheet" type="text/css" href="styles/forms.css" />
<link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />
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
include('o_online.php');
?>
</head>
<body>
<?php
include('header.php');
$page = 'forum';
include('menu.php');
?>
<main>
	<form method="post" class="advanced-search" action="">
		<h1><?php
			if ($editting)
				echo $language ? 'Edit member award':'Modifier récompense d\'un membre';
			else
				echo $language ? 'New assigned award':'Attribuer une récompense';
		?></h1>
		<table>
			<tr>
				<td class="ligne">
					<label for="nom"><?php echo $language ? 'Member':'Membre'; ?></label>
				</td>
				<td>
					<input type="text" name="nom" id="nom" value="<?php if (isset($awarded['nom'])) echo htmlspecialchars($awarded['nom']); ?>" placeholder="Wagar" />
				</td>
			</tr>
			<tr>
				<td class="ligne">
					<label for="award"><?php echo $language ? 'Event':'Événement'; ?></label>
				</td>
				<td>
					<select name="award">
						<?php
						$awards = mysql_query('SELECT id,name FROM `mkawards` ORDER BY ordering DESC');
						$selectedAward = isset($awarded['award']) ? $awarded['award']:null;
						while ($award = mysql_fetch_array($awards))
							echo '<option value="'.$award['id'].'"'. (($award['id']===$selectedAward)?' selected="selected"':'') .'>'.$award['name'].'</option>';
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="ligne">
					<label for="value"><?php echo $language ? 'Award(s)':'Récompense(s)'; ?></label>
				</td>
				<td>
					<input type="text" name="value" id="value" value="<?php if (isset($awarded['value'])) echo htmlspecialchars($awarded['value']); ?>" placeholder="Meilleur coureur, Joueur de l'année" />
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
include('footer.php');
?>
<script type="text/javascript" src="scripts/auto-complete.min.js"></script>
<script type="text/javascript" src="scripts/autocomplete-player.js"></script>
<script type="text/javascript">
autocompletePlayer('#nom', {
	onSelect: function(event, term, item) {
		preventSubmit(event);
	}
});
</script>
<?php
mysql_close();
?>
</body>
</html>