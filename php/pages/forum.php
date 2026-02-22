<?php
include('../includes/getId.php');
include('../includes/language.php');
include('../includes/session.php');
include('../includes/initdb.php');
if (isset($_POST['pseudo']) && isset($_POST['code'])) {
	include('../includes/utils-cooldown.php');
	if (!isLoginCooldowned() && ($getId = mysql_fetch_array(mysql_query('SELECT * FROM `mkjoueurs` WHERE nom="'.$_POST['pseudo'].'"'))) && password_verify($_POST['code'],$getId['code'])) {
		if ($getId['deleted'] && !isset($_GET['forced'])) {
			$warningDeleted = true;
		}
		else {
			$id = $getId['id'];
			$_SESSION['mkid'] = $id;
			require_once('../includes/credentials.php');
			setcookie('mkp', credentials_encrypt($id,$_POST['code']), 4294967295,'/');
			if ($getId['deleted'])
				mysql_query('UPDATE `mkjoueurs` SET deleted=0 WHERE id="'. $id .'"');
			function banIfBlackIp() {
				global $id, $getId, $identifiants;
				if (!$getId['banned'] && mysql_numrows(mysql_query('SELECT * FROM `ip_bans` WHERE ip1="'.$identifiants[0].'" AND ip2="'.$identifiants[1].'" AND ip3="'.$identifiants[2].'" AND ip4="'.$identifiants[3].'"'))) {
					mysql_query('UPDATE `mkjoueurs` SET banned=2 WHERE id="'.$id.'"');
					mysql_query('INSERT IGNORE INTO `ip_bans` VALUES('.$id.',"'.$identifiants[0].'","'.$identifiants[1].'","'.$identifiants[2].'","'.$identifiants[3].'")');
					mysql_query('INSERT IGNORE INTO `mkbans` VALUES('.$id.',"'. _('Auto-ban by IP') .'",NULL,NULL)');
				}
			}
			banIfBlackIp();
			include('../includes/setId.php');
			banIfBlackIp();
		}
	}
}
?>
<!DOCTYPE html>
<html lang="<?= P_("html language", "en") ?>">
<head>
<title><?= _("Mario Kart PC Forum") ?></title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css?reload=2" />
<?php
include('../includes/o_online.php');
?>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'forum';
include('../includes/menu.php');
if ($id && $myIdentifiants) {
	mysql_query('INSERT IGNORE INTO `mkips` VALUES("'.$id.'","'.$myIdentifiants[0].'","'.$myIdentifiants[1].'","'.$myIdentifiants[2].'","'.$myIdentifiants[3].'")');
	mysql_query('INSERT IGNORE INTO `mkbrowsers` VALUES("'.$id.'","'.mysql_real_escape_string($_SERVER['HTTP_USER_AGENT']).'")');
}
?>
<main>
<h1><?= _("Mario Kart PC Forum") ?></h1>
<?php
if ($id) {
	$getNom = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $id .'"'));
	?>
	<div class="forum-welcome">
		<?= F_('Welcome to the MKPC forum! If you haven\'t done it yet, please check out the <a href="{url}">rules</a> before posting.', url: "topic.php?topic=19829") ?>
	</div>
	<p id="compte"><span><?= $getNom['nom'] ?></span>
	<a href="profil.php?id=<?= $id ?>"><?= _('My profile') ?></a><br />
	<a href="logout.php"><?= _('Log out') ?></a>
	</p>
	<?php
	include('../includes/rights-msg.php');
}
else {
	$restoreAccount = "javascript:document.forms[0].action='?forced';document.forms[0].submit()";
	if (isset($warningDeleted)) {
		?>
		<p class="warning">
		<?= _("This account has been deleted. The connection to it has been disabled.") ?>
		<br />
		<?= F_('If you want to undo and restore it, you can still do it by clicking <a href="{url}">here</a>.', url: $restoreAccount) ?>
		</p>
		<?php
	}
	?>
	<br />
	<form method="post" action="forum.php"<?php if (isset($warningDeleted)) echo ' style="height:0;overflow:hidden"'; ?>>
	<table id="connexion">
	<?php
	if (isset($_POST['pseudo']) && isset($_POST['code']))
		echo '<caption style="color: #B00">'. _('Incorrect login or password') .'</caption>';
	else
		echo '<caption>'. _("You aren't logged in.") . "<br />" . _("Enter your login and password here:") .'</caption>';
	?>
	<tr><td class="ligne"><label for="pseudo"><?= _('Login:') ?></label></td><td><input type="text" name="pseudo" id="pseudo"<?php echo isset($_POST['pseudo']) ? ' value="'. htmlspecialchars($_POST['pseudo']) .'"':null; ?> /></td></tr>
	<tr><td class="ligne"><label for="code"><?= _('Password:') ?></label></td><td><input type="password" name="code" id="code"<?php echo isset($_POST['code']) ? ' value="'. htmlspecialchars($_POST['code']) .'"':null; ?> /></td></tr>
	<tr><td colspan="2"><input type="submit" value="<?= _('Submit') ?>" /></td></tr>
	<tr><td colspan="2">
		<a href="signup.php"><?= _('Register') ?></a> | 
		<a href="password-lost.php" style="font-weight: normal"><?= _('Forgot password') ?></a>
	</td></tr>
	</table>
	</form>
	<br />
	<?php
}
?>
<?php
require_once('../includes/utils-ads.php');
showRegularAdSection();
?>
<form method="get" action="recherche.php" class="forum-search">
	<p>
		<label for="search-content">
			<?= _('Search:') ?>
		</label>
		<input type="text" id="search-content" placeholder="<?= _('Topic title') ?>" name="content" />
		<input type="submit" value="Ok" class="action_button" />
		<a href="forum-search.php"><?= _('Advanced search') ?></a>
	</p>
</form>
<table id="listeTopics">
<col id="categories" />
<col id="nbmsgs" />
<col id="lastmsgs" />
<tr id="titres">
<td><?= _('Category') ?></td>
<td><?= _('Topics nb') ?></td>
<td><?= _('Last message') ?></td>
</tr>
<?php
include('../includes/category_fields.php');
require_once('../includes/utils-date.php');
require_once('../includes/getRights.php');
$categories = mysql_query('SELECT id,'. $categoryFields .' FROM `mkcategories` ORDER BY '. $orderingField);
$nbTopics = 0;
for ($i=0;$category=mysql_fetch_array($categories);$i++) {
	$catWhere = 'category='. $category['id'] .' AND language='. "language" . (hasRight('manager') ? '':' AND !private');
	$nbMsgs = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mktopics` WHERE '. $catWhere));
	$nbTopics += $nbMsgs['nb'];
	$lastMsg = mysql_fetch_array(mysql_query('SELECT dernier FROM `mktopics` WHERE '. $catWhere .' ORDER BY dernier DESC LIMIT 1'));
	echo '<tr class="'. (($i%2) ? 'fonce':'clair') .'"><td class="subjects">';
		echo '<a href="category.php?category='. $category['id'] .'">'. $category['nom'] .'</a>';
		echo '<div class="category-description">'. $category['description'] .'</div>';
	echo '</td><td>';
		echo $nbMsgs['nb'];
	echo '</td><td>';
		if ($lastMsg)
			echo pretty_dates($lastMsg['dernier']);
	echo '</td></tr>';
}
?>
</table>
<ul class="forumStats">
	<?php
	$timeZone = new DateTimeZone('Europe/Paris');
	$beginMonth = new DateTime('now', $timeZone);
	$beginMonth->modify('first day of this month');

	$getNbMessages = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkmessages`'));

	$getTopPlayer = mysql_fetch_array(mysql_query('SELECT j.id,j.nom,p.nbmessages AS nb FROM `mkprofiles` p INNER JOIN `mkjoueurs` j ON p.id=j.id ORDER BY p.nbmessages DESC, p.id ASC LIMIT 1'));

	$getMonthlyTopPlayer = mysql_fetch_array(mysql_query('SELECT j.id,j.nom,m.nb FROM (SELECT auteur,COUNT(*) AS nb FROM mkmessages WHERE date>="'. $beginMonth->format('Y-m-d') .'" GROUP BY auteur) m INNER JOIN mkjoueurs j ON m.auteur=j.id ORDER BY nb DESC, j.id ASC LIMIT 1'));

	$getPosters = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkprofiles` WHERE nbmessages>0'));

	$monthFormatter = new IntlDateFormatter(
		P_("locale for ICU", 'en_EN'),
		timezone: $timeZone,
		pattern: "MMMM",
	);
	$month = $monthFormatter->format($beginMonth);

	echo "<li>";
	echo F_(
		"The forum has a total of <strong>{nbMessages} messages</strong> split into <strong>{nbTopics} topics</strong> and posted by <strong>{nbPosters} members</strong>.",
		nbMessages: $getNbMessages['nb'],
		nbTopics: $nbTopics,
		nbPosters: $getPosters['nb'],
	);
	echo "</li>";

	require_once('../includes/trials.php');
	if (isTrialDisabled('hidemsgcount')) {
		echo "<li>";
		echo F_(
			'The most active member is <a href="{urlToProfile}">{topPlayer}</a> with <strong>{topPlayerMessagesWithCount}</strong> posted in total.',
			urlToProfile: "profil.php?id=". $getTopPlayer['id'],
			topPlayer: $getTopPlayer['nom'],
			topPlayerMessagesWithCount: FN_('{count} message', '{count} messages', count: $getTopPlayer['nb']),
		);
		echo '<a href="ranking-forum.php">';
		echo '<img src="images/cups/cup1.png" alt="' . _("Ranking") . '"/>';
		echo _("Ranking of most active members");
		echo '<img src="images/cups/cup1.png" alt="' . _("Ranking") .  '"/></a>';
		echo "</li>";

		if ($getMonthlyTopPlayer) {
			echo "<li>";
			echo F_(
				'The most active member of the month is <a href="{urlToProfile}">{monthlyTopPlayer}</a> with <strong>{monthlyTopPlayerMessagesWithCount}</strong> since {month} the 1<small class="superscript">st</small>.',
				urlToProfile: "profil.php?id=". $getMonthlyTopPlayer['id'],
				month: $month,
				monthlyTopPlayer: $getMonthlyTopPlayer['nom'],
				monthlyTopPlayerMessagesWithCount: FN_('{count} message', '{count} messages', count: $getMonthlyTopPlayer['nb']),
			);
			echo '<a href="ranking-forum.php?month=last">';
			echo '<img src="images/cups/cup2.png" alt="' . _("Classement") . '"/>';
			echo _("Ranking of month's most active members");
			echo '<img src="images/cups/cup2.png" alt="' . _("Classement") .  '"/></a>';
			echo "</li>";
		}
	}
	?>
</ul>
<p class="forumButtons">
<a href="index.php"><?= _('Back to the homepage') ?></a>
</p>
</main>
<?php
mysql_close();
include('../includes/footer.php');
?>
</body>
</html>
