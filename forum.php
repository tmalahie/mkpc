<?php
include('getId.php');
include('language.php');
include('session.php');
include('initdb.php');
if (isset($_POST['pseudo']) && isset($_POST['code'])) {
	if (($getId = mysql_fetch_array(mysql_query('SELECT * FROM `mkjoueurs` WHERE nom="'.$_POST['pseudo'].'"'))) && password_verify($_POST['code'],$getId['code'])) {
		if ($getId['deleted'] && !isset($_GET['forced'])) {
			$warningDeleted = true;
		}
		else {
			$id = $getId['id'];
			$_SESSION['mkid'] = $id;
			require_once('credentials.php');
			setcookie('mkp', credentials_encrypt($id,$_POST['code']), 4294967295,'/');
			if ($getId['deleted'])
				mysql_query('UPDATE `mkjoueurs` SET deleted=0 WHERE id="'. $id .'"');
			function banIfBlackIp() {
				global $id, $getId, $identifiants, $language;
				if (!$getId['banned'] && mysql_numrows(mysql_query('SELECT * FROM `ip_bans` WHERE ip1="'.$identifiants[0].'" AND ip2="'.$identifiants[1].'" AND ip3="'.$identifiants[2].'" AND ip4="'.$identifiants[3].'"'))) {
					mysql_query('UPDATE `mkjoueurs` SET banned=2 WHERE id="'.$id.'"');
					mysql_query('INSERT IGNORE INTO `ip_bans` VALUES('.$id.',"'.$identifiants[0].'","'.$identifiants[1].'","'.$identifiants[2].'","'.$identifiants[3].'")');
					mysql_query('INSERT IGNORE INTO `mkbans` VALUES('.$id.',"'. ($language ? 'Auto-ban by IP' : 'Auto-ban par IP') .'",NULL)');
				}
			}
			banIfBlackIp();
			include('setId.php');
			banIfBlackIp();
		}
	}
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>Forum Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css?reload=2" />
<?php
include('o_online.php');
?>
</head>
<body>
<?php
include('header.php');
$page = 'forum';
include('menu.php');
if ($id && $myIdentifiants) {
	mysql_query('INSERT IGNORE INTO `mkips` VALUES("'.$id.'","'.$myIdentifiants[0].'","'.$myIdentifiants[1].'","'.$myIdentifiants[2].'","'.$myIdentifiants[3].'")');
	mysql_query('INSERT IGNORE INTO `mkbrowsers` VALUES("'.$id.'","'.mysql_real_escape_string($_SERVER['HTTP_USER_AGENT']).'")');
}
?>
<main>
<h1>Forum Mario Kart PC</h1>
<?php
if ($id) {
	$getNom = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $id .'"'));
	?>
	<div class="forum-welcome">
		<?php
		if ($language) {
			?>
			Welcome to the MKPC forum! If you haven't done it yet, please check out the <a href="topic.php?topic=2448">rules</a> before posting.
			<?php
		}
		else {
			?>
			Bienvenue sur le forum MKPC ! Si ce n'est pas déjà fait, prenez le temps de consulter le <a href="topic.php?topic=2448">règlement</a> avant de poster.
			<?php
		}
		?>
	</div>
	<p id="compte"><span><?php echo $getNom['nom']; ?></span>
	<a href="profil.php?id=<?php echo $id; ?>"><?php echo $language ? 'My profile':'Mon profil'; ?></a><br />
	<a href="logout.php"><?php echo $language ? 'Log out':'D&eacute;connexion'; ?></a>
	</p>
	<?php
	include('rights-msg.php');
}
else {
	$restoreAccount = "javascript:document.forms[0].action='?forced';document.forms[0].submit()";
	if (isset($warningDeleted)) {
		?>
		<p class="warning">
		<?php
		if ($language) {
			?>
			This account has been deleted. The connection to it has been disabled.<br />
			If you want to undo and restore it, you can still do it by clicking <a href="<?php echo $restoreAccount; ?>">here</a>.
			<?php
		}
		else {
			?>
			Ce compte a été supprimé. La connexion a celui-ci a donc été desactivée.<br />
			Si vous souhaitez revenir en arrière et le restaurer, vous pouvez toujours le faire en cliquant <a href="<?php echo $restoreAccount; ?>">ici</a>.
			<?php
		}
		?>
		</p>
		<?php
	}
	?>
	<br />
	<form method="post" action="forum.php"<?php if (isset($warningDeleted)) echo ' style="height:0;overflow:hidden"'; ?>>
	<table id="connexion">
	<?php
	if (isset($_POST['pseudo']) && isset($_POST['code']))
		echo '<caption style="color: #B00">'. ($language ? 'Incorrect login or password':'Pseudo ou mot de passe incorrect') .'</caption>';
	else
		echo '<caption>'. ($language ? 'You aren\'t logged in.<br />Enter your login and password here :':'Vous n\'&ecirc;tes pas connect&eacute;<br />Entrez votre pseudo et code ici :') .'</caption>';
	?>
	<tr><td class="ligne"><label for="pseudo"><?php echo $language ? 'Login':'Pseudo'; ?> :</label></td><td><input type="text" name="pseudo" id="pseudo"<?php echo isset($_POST['pseudo']) ? ' value="'. $_POST['pseudo'] .'"':null; ?> /></td></tr>
	<tr><td class="ligne"><label for="code"><?php echo $language ? 'Password':'Code'; ?> :</label></td><td><input type="password" name="code" id="code"<?php echo isset($_POST['code']) ? ' value="'. $_POST['code'] .'"':null; ?> /></td></tr>
	<tr><td colspan="2"><input type="submit" value="<?php echo $language ? 'Submit':'Valider'; ?>" /></td></tr>
	<tr><td colspan="2">
		<a href="signup.php"><?php echo $language ? 'Register':'Inscription'; ?></a> | 
		<a href="password-lost.php" style="font-weight: normal"><?php echo $language ? 'Forgot password':'Mot de passe perdu'; ?></a>
	</td></tr>
	</table>
	</form>
	<br />
	<?php
}
?>
<!-- Forum MKPC -->
<p class="pub"><ins class="adsbygoogle adsbymkpc"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-1340724283777764"
     data-ad-slot="4919860724">
	 <a href="https://www.mariokarting.com/" target="_blank"><img src="images/events/ads/mariokarting.png" srcset="images/events/ads/mariokarting@2x.png 2x" /></a>
</ins></p>
<form method="get" action="recherche.php" class="forum-search">
	<p>
		<label for="search-content">
			<?php echo $language ? 'Search':'Recherche'; ?>:
		</label>
		<input type="text" id="search-content" placeholder="<?php echo $language ? 'Topic title':'Titre du topic'; ?>" name="content" />
		<input type="submit" value="Ok" class="action_button" />
		<a href="forum-search.php"><?php echo $language ? 'Advanced search':'Recherche avancée'; ?></a>
	</p>
</form>
<table id="listeTopics">
<col id="categories" />
<col id="nbmsgs" />
<col id="lastmsgs" />
<tr id="titres">
<td><?php echo $language ? 'Category':'Catégorie'; ?></td>
<td><?php echo $language ? 'Topics nb':'Nb topics'; ?></td>
<td><?php echo $language ? 'Last message':'Dernier message'; ?></td>
</tr>
<?php
include('category_fields.php');
require_once('utils-date.php');
require_once('getRights.php');
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
	$beginMonth = new DateTime('now', new DateTimeZone('Europe/Paris'));
	$beginMonth->modify('first day of this month');
	$getNbMessages = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkmessages`'));
	$getActivestPlayer = mysql_fetch_array(mysql_query('SELECT j.id,j.nom,p.nbmessages AS nb FROM `mkprofiles` p INNER JOIN `mkjoueurs` j ON p.id=j.id ORDER BY p.nbmessages DESC, p.id ASC LIMIT 1'));
	$getMonthlyActivestPlayer = mysql_fetch_array(mysql_query('SELECT j.id,j.nom,m.nb FROM (SELECT auteur,COUNT(*) AS nb FROM mkmessages WHERE date>="'. $beginMonth->format('Y-m-d') .'" GROUP BY auteur) m INNER JOIN mkjoueurs j ON m.auteur=j.id ORDER BY nb DESC, j.id ASC LIMIT 1'));
	$getPosters = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkprofiles` WHERE nbmessages>0'));
	if ($language) {
		$month = $beginMonth->format('F');
		echo "<li>The forum has a total of <strong>". $getNbMessages['nb'] ." messages</strong> split into <strong>". $nbTopics ." topics</strong> and posted by <strong>". $getPosters['nb'] ." members</strong>.</li>";
		echo "<li>The most active member is <a href=\"profil.php?id=". $getActivestPlayer['id'] ."\">". $getActivestPlayer['nom'] ."</a> with <strong>". $getActivestPlayer['nb'] ." messages</strong> posted in total.<a href=\"ranking-forum.php\"><img src=\"images/cups/cup1.png\" alt=\"Classement\" />Ranking of most active members<img src=\"images/cups/cup1.png\" alt=\"Classement\" /></a></li>";
		if ($getMonthlyActivestPlayer)
			echo "<li>The most active member of the month is <a href=\"profil.php?id=". $getMonthlyActivestPlayer['id'] ."\">". $getMonthlyActivestPlayer['nom'] ."</a> with <strong>". $getMonthlyActivestPlayer['nb'] ." message". ($getMonthlyActivestPlayer['nb']>1?"s":"") ."</strong> since ". $month ." 1<small class=\"superscript\">st</small>.<a href=\"ranking-forum.php?month=last\"><img src=\"images/cups/cup2.png\" alt=\"Classement\" />Ranking of month's most active members<img src=\"images/cups/cup2.png\" alt=\"Classement\" /></a></li>";
	}
	else {
		$m = $beginMonth->format('m');
		$months = array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');
		$month = $months[$m-1];
		echo "<li>Le forum comptabilise <strong>". $getNbMessages['nb'] ." messages</strong> répartis dans <strong>". $nbTopics ." topics</strong> et postés par <strong>". $getPosters['nb'] ." membres</strong>.</li>";
		echo "<li>Le membre le plus actif est <a href=\"profil.php?id=". $getActivestPlayer['id'] ."\">". $getActivestPlayer['nom'] ."</a> avec <strong>". $getActivestPlayer['nb'] ." messages</strong> postés au total.<a href=\"ranking-forum.php\"><img src=\"images/cups/cup1.png\" alt=\"Classement\" />Classement des membres les plus actifs<img src=\"images/cups/cup1.png\" alt=\"Classement\" /></a></li>";
		if ($getMonthlyActivestPlayer)
			echo "<li>Le membre le plus actif du mois est <a href=\"profil.php?id=". $getMonthlyActivestPlayer['id'] ."\">". $getMonthlyActivestPlayer['nom'] ."</a> avec <strong>". $getMonthlyActivestPlayer['nb'] ." message". ($getMonthlyActivestPlayer['nb']>1?"s":"") ."</strong> depuis le 1<small class=\"superscript\">er</small> ". $month .".<a href=\"ranking-forum.php?month=last\"><img src=\"images/cups/cup2.png\" alt=\"Classement\" />Classement des plus actifs du mois<img src=\"images/cups/cup2.png\" alt=\"Classement\" /></a></li>";
	}
	?>
</ul>
<p class="forumButtons">
<a href="index.php"><?php echo $language ? 'Back to the homepage':'Retour &agrave; l\'accueil'; ?></a>
</p>
</main>
<script type="text/javascript" src="scripts/adsbymkpc.js"></script>
<?php
mysql_close();
include('footer.php');
?>
</body>
</html>