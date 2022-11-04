<?php
include('getId.php');
include('language.php');
include('session.php');
include('initdb.php');
$userId = $id;
if (isset($_GET['member'])) {
	require_once('getRights.php');
	if (!hasRight('moderator')) {
		echo "Vous n'&ecirc;tes pas mod&eacute;rateur";
		mysql_close();
		exit;
	}
	$userId = intval($_GET['member']);
	$member = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $userId .'"'));
}
if ($getProfile = mysql_fetch_array(mysql_query('SELECT YEAR(birthdate) AS y0,MONTH(birthdate) AS m0,DAY(birthdate) AS d0,birthdate,description,email,country FROM `mkprofiles` WHERE id="'. $userId .'"'))) {
	if ($getProfile['country']) {
		if ($getCountryCode = mysql_fetch_array(mysql_query('SELECT code FROM mkcountries WHERE id='. $getProfile['country'])))
			$selCountry = $getCountryCode['code'];
	}
	?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php
	if (empty($member))
		echo $language ? 'Edit my profile':'Modifier mon profil';
	else
		echo $language ? "Edit ". $member['nom'] ."'s profile":"Modifier le profil de ". $member['nom'];
?> - Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/profil.css" />
<link rel="stylesheet" type="text/css" href="styles/forms.css" />
<script type="text/javascript" src="scripts/topic.js"></script>
<?php
include('o_online.php');
?>
</head>
<body>
<?php
include('header.php');
$page = 'forum';
include('menu.php');

function zerofill($s,$l) {
	while (strlen($s) < $l)
		$s = '0'.$s;
	return $s;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = isset($_POST['email']) ? $_POST['email']:'';
	$country = isset($_POST['country']) ? $_POST['country']:'';
	$description = isset($_POST['description']) ? $_POST['description']:'';
	$d0 = isset($_POST['d0']) ? $_POST['d0']:'';
	$m0 = isset($_POST['m0']) ? $_POST['m0']:'';
	$y0 = isset($_POST['y0']) ? $_POST['y0']:'';
	$birthdate = $y0 ? zerofill($y0,4).'-'.zerofill($m0?$m0:1,2).'-'.zerofill($d0?$d0:1,2).' 00:00:00':'';
	function getAge($date) {
		$ts = strtotime($date);
		$y0 = date('Y',$ts);
		$m0 = date('m',$ts);
		$d0 = date('d',$ts);
		$y1 = date('Y');
		$m1 = date('m');
		$d1 = date('d');
		$res = $y1-$y0;
		if ($m1 < $m0 || (($m1 == $m0) && ($d1 < $d0)))
			$res--;
		return $res;
	}
	$age = getAge($birthdate);
	if ($email && !preg_match("#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#i", $email))
		$error = ($language ? 'Please enter a valid email address':'Veuillez entrer une adresse email valide');
	//elseif ($email && mysql_numrows(mysql_query('SELECT * FROM `mkprofiles` WHERE id!="'. $userId .'" AND email="'. $email .'"')))
	//	$error = ($language ? 'This email address is already taken':'Cette adress email existe déjà');
	elseif ($birthdate && (($age < 2) || ($age > 150)))
		$error = ($language ? 'Please enter a valid birth date':'Veuillez entrer une date de naissance valide');
	if (!isset($error)) {
		$getBanned = mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"');
		$isBanned = mysql_fetch_array($getBanned);
		if ($isBanned && $isBanned['banned'])
			$error = $language ? 'You have been banned, you cannot edit your profile.':'Vous avez été banni, vous ne pouvez pas modifier votre profil.';
		else {
			if ($getCountryId = mysql_fetch_array(mysql_query('SELECT id FROM mkcountries WHERE code="'. $country .'"')))
				$countryId = $getCountryId['id'];
			else
				$countryId = 0;
			mysql_query('UPDATE `mkprofiles` SET email="'. $email .'",country="'.$countryId.'",description="'. $description .'",birthdate='. ($birthdate ? '"'.$birthdate.'"':'NULL') .' WHERE id="'.$userId.'"');
			if (isset($_GET['member']))
				mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Profile '. $_GET['member'] .'")');
			$success = $language ? 'Profile updated successfully':'Profil mis à jour avec succès';
		}
	}
}
else {
	$email = $getProfile['email'];
	$description = $getProfile['description'];
	$d0 = $getProfile['d0'];
	$m0 = $getProfile['m0'];
	$y0 = $getProfile['y0'];
	if ($d0 === null) $d0 = '';
	if ($m0 === null) $m0 = '';
	if ($y0 === null) $y0 = '';
	$date0 = $getProfile['birthdate'];
}
?>
<main>
<form method="post" class="advanced-search" action="">
	<?php
	if (isset($success))
		echo '<div class="success">'. $success .'</div>';
	elseif (isset($error))
		echo '<div class="warning">'. $error .'</div>';
	?>
	<h1><?php
		if (empty($member))
			echo $language ? 'Edit profile':'Modifier le profil';
		else
			echo $language ? "Edit ". $member['nom'] ."'s profile":"Modifier le profil de ". $member['nom'];
	?></h1>
	<table class="signup">
		<tr>
			<td class="ligne">
				<label for="email"><?php echo $language ? 'Email address<br /><em style="font-size:0.7em">(won\'t appear on profile)</em>':'Adresse email<br /><em style="font-size:0.7em">(n\'apparaîtra pas sur le profil)</em>'; ?></label>
			</td>
			<td>
				<input type="text" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" />
			</td>
		</tr>
		<tr>
			<td class="ligne">
				<label for="country"><?php echo $language ? 'Country':'Pays'; ?></label>
			</td>
			<td>
				<select type="text" name="country" id="country"><?php
				include('list-countries.php');
				?></select>
			</td>
		</tr>
		<tr>
			<td class="ligne birthdate">
				<label for="birthdate"><?php echo $language ? 'Birth date':'Date de naissance'; ?></label>
			</td>
			<td>
				<?php
				if ($language) {
					?>
					<input type="number" name="y0" id="birthdate" class="search-xs-4 noarrow" placeholder="YYYY" min="1000" max="9999" value="<?php echo htmlspecialchars($y0); ?>" /> /
					<input type="number" name="m0" class="search-xs-2 noarrow" placeholder="MM" min="1" max="12" value="<?php echo htmlspecialchars($m0); ?>" /> /
					<input type="number" name="d0" class="search-xs-2 noarrow" placeholder="DD" min="1" max="31" value="<?php echo htmlspecialchars($d0); ?>" /><br />
					<?php
				}
				else {
					?>
					<input type="number" name="d0" id="birthdate" class="search-xs-2 noarrow" placeholder="JJ" min="1" max="31" value="<?php echo htmlspecialchars($d0); ?>" /> /
					<input type="number" name="m0" class="search-xs-2 noarrow" placeholder="MM" min="1" max="12" value="<?php echo htmlspecialchars($m0); ?>" /> /
					<input type="number" name="y0" class="search-xs-4 noarrow" placeholder="AAAA" min="1000" max="9999" value="<?php echo htmlspecialchars($y0); ?>" /><br />
					<?php
				}
				?>
			</td>
		</tr>
		<tr>
			<td class="ligne">
				<label for="description"><?php echo $language ? 'Personal description':'Description de vous'; ?></label>
			</td>
			<td>
				<textarea name="description" rows="5"><?php
				echo htmlspecialchars(stripslashes(str_replace("\\r","\r", str_replace("\\n","\n", str_replace("\\t","\t", $description)))));
				?></textarea>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="submit" class="action_button" value="<?php echo $language ? 'Submit':'Valider'; ?>" />
			</td>
		</tr>
	</table>
	<p class="forumButtons">
		<?php
		if (isset($_GET['member']))
			echo '<a href="admin.php">'. ($language ? 'Back to the admin page':'Retour à la page admin') .'</a><br />';
		?>
		<a href="profil.php?id=<?php echo $userId; ?>"><?php
		if (empty($member))
			echo $language ? 'Back to your profile':'Retour à votre profil';
		else
			echo $language ? "Back to ". $member['nom'] ."'s profile" : "Retour au profil de " . $member['nom'];
		?></a><br />
		<a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a>
	</p>
</form>
</main>
<?php
include('footer.php');
}
mysql_close();
?>
</body>
</html>