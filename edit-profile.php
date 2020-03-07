<?php
include('getId.php');
include('language.php');
include('session.php');
include('initdb.php');
if ($getProfile = mysql_fetch_array(mysql_query('SELECT YEAR(birthdate) AS y0,MONTH(birthdate) AS m0,DAY(birthdate) AS d0,birthdate,description,email,country FROM `mkprofiles` WHERE id="'. $id .'"'))) {
	if ($getProfile['country']) {
		if ($getCountryCode = mysql_fetch_array(mysql_query('SELECT code FROM mkcountries WHERE id='. $getProfile['country'])))
			$selCountry = $getCountryCode['code'];
	}
	?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Edit profile':'Modifier mon profil'; ?> - Mario Kart PC</title>
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
	//elseif ($email && mysql_numrows(mysql_query('SELECT * FROM `mkprofiles` WHERE id!="'. $id .'" AND email="'. $email .'"')))
	//	$error = ($language ? 'This email address is already taken':'Cette adress email existe déjà');
	elseif ($birthdate && (($age < 2) || ($age > 150)))
		$error = ($language ? 'Please enter a valid birth date':'Veuillez entrer une date de naissance valide');
	if (!isset($error)) {
		if ($getCountryId = mysql_fetch_array(mysql_query('SELECT id FROM mkcountries WHERE code="'. $country .'"')))
			$countryId = $getCountryId['id'];
		else
			$countryId = 0;
		mysql_query('UPDATE `mkprofiles` SET email="'. $email .'",country="'.$countryId.'",description="'. $description .'",birthdate='. ($birthdate ? '"'.$birthdate.'"':'NULL') .' WHERE id="'.$id.'"');
		$success = $language ? 'Profile updated successfully':'Profil mis à jour avec succès';
	}
}
else {
	$email = $getProfile['email'];
	$description = $getProfile['description'];
	$d0 = $getProfile['d0'];
	$m0 = $getProfile['m0'];
	$y0 = $getProfile['y0'];
	$date0 = $getProfile['birthdate'];
}
?>
<main>
<form method="post" class="advanced-search" action="edit-profile.php">
	<?php
	if (isset($success))
		echo '<div class="success">'. $success .'</div>';
	elseif (isset($error))
		echo '<div class="warning">'. $error .'</div>';
	?>
	<h1><?php echo $language ? 'Edit profile':'Modifier le profil'; ?></h1>
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
			<td class="ligne">
				<label for="birthdate"><?php echo $language ? 'Birth date':'Date de naissance'; ?></label>
			</td>
			<td>
				<input type="number" name="d0" class="search-xs-2" min="1" max="31" value="<?php echo htmlspecialchars($d0); ?>" /> /
				<input type="number" name="m0" class="search-xs-2" min="1" max="12" value="<?php echo htmlspecialchars($m0); ?>" /> /
				<input type="number" name="y0" class="search-xs-4" min="1000" max="9999" value="<?php echo htmlspecialchars($y0); ?>" /><br />
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
		<a href="profil.php?id=<?php echo $id; ?>"><?php echo $language ? 'Back to your profile':'Retour à votre profil'; ?></a><br />
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