<?php
if (isset($_GET['id'])) {
	include('initdb.php');
	include('language.php');
	$lang = $language ? 'en':'fr';
	$getPoll = mysql_query('SELECT *,title_'.$lang.' AS title,question_'.$lang.' AS question  FROM `mkpolls` WHERE id="'. $_GET['id'] .'"');
	if ($poll  = mysql_fetch_array($getPoll)) {
		$getChamps = mysql_query('SELECT * FROM `mkpollres` WHERE poll="'. $_GET['id'] .'"');
		$type = $poll['type'];
		$over = $poll['over'];

		include('session.php');
		if (!$id) {
			echo "Vous n'&ecirc;tes pas connect&eacute;";
			exit;
		}
		include('initdb.php');
		$isConnected = (mysql_query('SELECT id FROM `mkjoueurs` WHERE id="'. $id .'"'));
		if (!($isConnected=mysql_fetch_array($isConnected))) {
			echo "Vous n'&ecirc;tes pas connect&eacute;";
			mysql_close();
			exit;
		}
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			mysql_query('DELETE FROM `mkvotes` WHERE user="'. $id .'" AND answer IN (SELECT id FROM `mkpollres` WHERE poll="'. $_GET['id'] .'")');
			while ($champ = mysql_fetch_array($getChamps)) {
				if (($type == 'radio')?($_POST['vote']==$champ['id']):isset($_POST['vote'.$champ['id']]))
					mysql_query('INSERT INTO `mkvotes` VALUES("'. $id .'",'.$champ['id'].')');
			}
			$getChamps = mysql_query('SELECT * FROM `mkpollres` WHERE poll="'. $_GET['id'] .'"');
		}
		$getSelected = mysql_query('SELECT * FROM `mkvotes` WHERE user="'. $id .'" AND answer IN (SELECT id FROM `mkpollres` WHERE poll="'. $_GET['id'] .'")');
		$selected = array();
		while ($selectedAnswer = mysql_fetch_array($getSelected)) {
			$selected[$selectedAnswer['answer']] = true;
		}
	?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php if (!$over)echo $poll['title'].' - '; ?>Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<style type="text/css">
.reset_button {
	display: inline-block;
	padding: 5px 10px;
	background-color: #C00;
	font-weight: bold;
	color: white;
	border-radius: 5px;
	text-decoration: none;
	cursor: pointer;
	opacity: 0.6;
}
.reset_button:hover {
	background-color: #800;
	color: white;
}
</style>

<?php
include('o_online.php');
?>
<script type="text/javascript">
function resetForm() {
	var elements = document.forms[0].elements;
	for (var i=0;i<elements.length;i++) {
		var element = elements[i];
		if ((element.type == "radio") || (element.type == "checkbox"))
			element.checked = false;
	}
}
</script>
</head>
<body>
<?php
include('header.php');
$page = 'forum';
include('menu.php');
?>
<main>
	<?php
	if (!$over) {
		if ($_SERVER['REQUEST_METHOD'] === 'POST')
			echo '<p class="success">'. ($language ? 'Your vote has been taken into account. You can change it at any time.':'Votre vote a été pris en compte. Vous pouvez le changer à tout moment.') .'</p>';
	?>
	<h1><?php echo $poll['title']; ?></h1>
	<p><strong><?php echo $poll['question']; ?></strong></p>
	<form method="post" action="">
		<p>
			<?php
			$v = '';
			while ($champ = mysql_fetch_array($getChamps)) {
				$key = ($type == 'radio' ? 'vote':'vote'.$champ['id']);
				?>
				<label><input type="<?php echo $type; ?>" name="<?php echo $key; ?>"<?php echo ($type == 'radio' ? ' value="'. $champ['id'] .'"':''); ?><?php if ($selected[$champ['id']]) echo ' checked="checked"'; ?> /> <?php echo $champ['answer']; ?></label><br />
				<?php
			}
			?>
		</p>
		<p>
			<input type="submit" value="<?php echo $language ? 'Vote':'Voter'; ?>" class="action_button" />
			<input type="button" value="<?php echo $language ? 'Reset':'Réinitialiser'; ?>" class="reset_button" onclick="resetForm()" />
		</p>
	</form>
	<p><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
	<a href="mariokart.html"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
	<?php
	}
	else
		echo '<p>Le sondage est termin&eacute;.</p>';
	?>
</main>
<?php
include('footer.php');
?>
<?php
mysql_close();
?>
</body>
</html>
		<?php
	}
}
?>