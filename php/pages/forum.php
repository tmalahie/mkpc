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
					mysql_query('INSERT IGNORE INTO `mkbans` VALUES('.$id.',"'. t('forum.auto_ban_ip') .'",NULL,NULL)');
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
<html lang="<?= $locale ?>">
<head>
<title><?= t('common.mario_kart_pc_forum') ?></title>
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
<h1><?= t('common.mario_kart_pc_forum') ?></h1>
<?php
if ($id) {
	$getNom = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $id .'"'));
	?>
	<div class="forum-welcome">
		<?= t('forum.welcome_mkpc_forum_if_you', url: "topic.php?topic=19829") ?>
	</div>
	<p id="compte"><span><?= $getNom['nom'] ?></span>
	<a href="profil.php?id=<?= $id ?>"><?= t('forum.my_profile') ?></a><br />
	<a href="logout.php"><?= t('forum.log_out') ?></a>
	</p>
	<?php
	include('../includes/rights-msg.php');
}
else {
	$restoreAccount = "javascript:document.forms[0].action='?forced';document.forms[0].submit()";
	if (isset($warningDeleted)) {
		?>
		<p class="warning">
		<?= t('forum.this_account_has_been_deleted') ?>
		<br />
		<?= t('forum.if_you_want_undo_restore', url: $restoreAccount) ?>
		</p>
		<?php
	}
	?>
	<br />
	<form method="post" action="forum.php"<?php if (isset($warningDeleted)) echo ' style="height:0;overflow:hidden"'; ?>>
	<table id="connexion">
	<?php
	if (isset($_POST['pseudo']) && isset($_POST['code']))
		echo '<caption style="color: #B00">'. t('forum.incorrect_login_password') .'</caption>';
	else
		echo '<caption>'. t('forum.you_arent_logged') . "<br />" . t('forum.enter_your_login_password_here') .'</caption>';
	?>
	<tr><td class="ligne"><label for="pseudo"><?= t('forum.login') ?></label></td><td><input type="text" name="pseudo" id="pseudo"<?php echo isset($_POST['pseudo']) ? ' value="'. htmlspecialchars($_POST['pseudo']) .'"':null; ?> /></td></tr>
	<tr><td class="ligne"><label for="code"><?= t('forum.password') ?></label></td><td><input type="password" name="code" id="code"<?php echo isset($_POST['code']) ? ' value="'. htmlspecialchars($_POST['code']) .'"':null; ?> /></td></tr>
	<tr><td colspan="2"><input type="submit" value="<?= t('forum.submit') ?>" /></td></tr>
	<tr><td colspan="2">
		<a href="signup.php"><?= t('forum.register') ?></a> | 
		<a href="password-lost.php" style="font-weight: normal"><?= t('forum.forgot_password') ?></a>
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
			<?= t('forum.search') ?>
		</label>
		<input type="text" id="search-content" placeholder="<?= t('forum.topic_title') ?>" name="content" />
		<input type="submit" value="Ok" class="action_button" />
		<a href="forum-search.php"><?= t('forum.advanced_search') ?></a>
	</p>
</form>
<table id="listeTopics">
<col id="categories" />
<col id="nbmsgs" />
<col id="lastmsgs" />
<tr id="titres">
<td><?= t('forum.category') ?></td>
<td><?= t('forum.topics_nb') ?></td>
<td><?= t('forum.last_message') ?></td>
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
		$locale,
		timezone: $timeZone,
		pattern: "MMMM",
	);
	$month = $monthFormatter->format($beginMonth);

	echo "<li>";
	echo t('forum.forum_has_total_messages_split',
		nbMessages: $getNbMessages['nb'],
		nbTopics: $nbTopics,
		nbPosters: $getPosters['nb'],
	);
	echo "</li>";

	if (hasRight('moderator')) {
		echo "<li>";
		echo t('forum.most_active_member_posted_total',
			urlToProfile: "profil.php?id=". $getTopPlayer['id'],
			topPlayer: $getTopPlayer['nom'],
			topPlayerMessagesWithCount: t('common.message_count', count: $getTopPlayer['nb']),
		);
		echo '<a href="ranking-forum.php">';
		echo '<img src="images/cups/cup1.png" alt="' . t('forum.ranking') . '"/>';
		echo t('forum.ranking_most_active_members');
		echo '<img src="images/cups/cup1.png" alt="' . t('forum.ranking') .  '"/></a>';
		echo "</li>";

		if ($getMonthlyTopPlayer) {
			echo "<li>";
			echo t('forum.most_active_member_month_since',
				urlToProfile: "profil.php?id=". $getMonthlyTopPlayer['id'],
				month: $month,
				monthlyTopPlayer: $getMonthlyTopPlayer['nom'],
				monthlyTopPlayerMessagesWithCount: t('common.message_count', count: $getMonthlyTopPlayer['nb']),
			);
			echo '<a href="ranking-forum.php?month=last">';
			echo '<img src="images/cups/cup2.png" alt="' . t('forum.ranking') . '"/>';
			echo t('forum.ranking_months_most_active_members');
			echo '<img src="images/cups/cup2.png" alt="' . t('forum.ranking') .  '"/></a>';
			echo "</li>";
		}
	}
	?>
</ul>
<p class="forumButtons">
<a href="index.php"><?= t('forum.back_homepage') ?></a>
</p>
</main>
<?php
mysql_close();
include('../includes/footer.php');
?>
</body>
</html>
