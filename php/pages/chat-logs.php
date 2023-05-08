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
if (!hasRight('moderator')) {
	echo "Vous n'&ecirc;tes pas mod&eacute;rateur";
	mysql_close();
	exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Online chat logs':'Logs chat en ligne'; ?> - Mario Kart PC</title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />

<?php
include('../includes/o_online.php');
?>
<style type="text/css">
#chat-logs {
	max-width: 500px;
	text-align: left;
	margin: auto;
	background-color: black;
	color: white;
	padding: 0.2em 0;
}
#chat-logs div {
	opacity: 0.9;
	margin: 0.3em 0;
	padding: 0 0.5em;
	overflow: hidden;
}
#chat-logs > .highlight {
	opacity: 1;
	font-weight: bold;
	background-color: #333;
	padding: 0.2em 0.5em;
}
#chat-logs > .highlight-alert {
	background-color: #620;
}
#chat-logs a {
	color: yellow;
}
#chat-logs a:hover {
	color: #FEFF9F;
}
#chat-logs hr {
	padding: 0.5em 0;
	background-color: white;
	border: none
}
.chat-pages {
	background-color: black;
	color: white;
	max-width: 500px;
	margin: 1em auto;
	padding: 0.25em;
}
.chat-pages a {
	color: yellow;
}
.chat-pages a:hover {
	color: #FEFF9F;
}
.mute-form-ctn {
	margin-bottom: 1em;
}
#mute-form {
	display: none;
	margin-top: 0.25em;
}
#chat-filter {
	line-height: 1.5em;
	text-align: left;
	max-width: 400px;
	margin: 0 auto;
}
h2 {
	margin-bottom: 0.5em;
}
</style>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'forum';
include('../includes/menu.php');
?>
<main>
	<?php
	$memberId = 0;
	if (isset($_GET['blacklist'])) {
		if ($getLog = mysql_fetch_array(mysql_query('SELECT l.date,l.player,l.course,l.message,l.chat,j.nom FROM mkbadmsglog l LEFT JOIN mkjoueurs j ON l.player=j.id WHERE l.id="'. $_GET['blacklist'] .'"'))) {
			$memberId = $getLog['player'];
			$memberNick = $getLog['nom'];
			$courseFilter = $getLog['course'];
		}
	}
	elseif (isset($_GET['pseudo']))
		$memberNick = $_GET['pseudo'];
	?>
	<h1><?php echo $language ? 'See online chat logs':'Voir les logs du mode en ligne'; ?></h1>
	<form method="get" action="chat-logs.php">
	<blockquote>
	<p id="chat-filter"><label for="pseudo"><strong><?php echo $language ? 'See player':'Voir joueur'; ?></strong></label> : <input type="text" name="pseudo" id="pseudo" value="<?php if (isset($memberNick)) echo htmlspecialchars($memberNick); ?>" /> <input type="submit" value="<?php echo $language ? 'Validate' : 'Valider'; ?>" class="action_button" />
	<br /><strong><?php echo $language ? '&nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; <small>&nbsp; &nbsp;</small>OR':'&nbsp; &nbsp; &nbsp; &nbsp;  &nbsp; &nbsp; &nbsp;OU'; ?> : <a href="?all"><?php echo $language ? 'See all logs' : 'Voir tous les logs'; ?></a></strong></p>
	</blockquote>
	</form>
	<?php
	if (isset($_GET['blacklist'])) {
		if (isset($memberNick))
			echo '<h2 id="context">'. ($language ? 'See logs in context for ' : 'Voir les logs en contexte pour ') .' '. htmlspecialchars($memberNick) .'</h2>';
	}
	elseif (isset($_GET['pseudo'])) {
		if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM `mkjoueurs` WHERE nom="'. $_GET['pseudo'] .'"'))) {
			$memberId = $getId['id'];
			echo '<h2>'. ($language ? 'Online chat log of' : 'Logs chat en ligne de') .' '. htmlspecialchars($memberNick) .'</h2>';
			?>
			<div>
				<?php
				if ($getChatMute = mysql_fetch_array(mysql_query('SELECT end_date FROM mkmuted WHERE player='. $memberId .' AND end_date>NOW()'))) {
					$endDate = $getChatMute['end_date'];
					?>
					<p style="line-height: 1.5em">
						<?php echo $language ? "This player is muted until: $endDate":"Ce joueur est muté jusqu'au : $endDate"; ?>
						<br />
						<input type="button" value="<?php echo $language ? 'Unmute':'De-muter'; ?>" class="action_button" onclick="unmute(<?php echo $memberId; ?>)" />
					</p>
					<?php
				}
				else {
					?>
					<div class="mute-form-ctn">
					<a href="javascript:void(0)" onclick="toggleMute()"><?php echo $language ? 'Mute player':'Muter le joueur'; ?>...</a>
					<form id="mute-form" onsubmit="mute(event, <?php echo $memberId; ?>)">
						<label for="mute-time"><?php echo $language ? "Mute for":"Muter pendant"; ?></label> : 
						<input type="text" size="1" name="mute-time" id="mute-time" value="1" />
						<select name="unit">
							<option value="1"><?php echo $language ? 'minutes':'minutes'; ?></option>
							<option value="60"><?php echo $language ? 'hours':'heures'; ?></option>
							<option value="1440" selected><?php echo $language ? 'days':'jours'; ?></option>
							<option value="10080"><?php echo $language ? 'weeks':'semaines'; ?></option>
						</select>
						<input type="submit" value="<?php echo $language ? 'Ok':'Ok'; ?>" class="action_button" />
					</form>
					</div>
					<?php
				}
				?>
			</div>
			<?php
		}
	}
	if ($memberId || isset($_GET['all'])) {
		echo '<div id="chat-logs">';
		require_once('../includes/public_links.php');
		if (isset($courseFilter))
			$chatIds = array($courseFilter);
		else {
			$sql = 'SELECT c.course FROM `mkchat` c LEFT JOIN mkracehist r1 ON c.course=r1.id LEFT JOIN mariokart r2 ON c.course=r2.id WHERE '. ($memberId ? " c.auteur=$memberId AND" : "") .' IFNULL(r1.link,r2.link) IN ('.$publicLinksString.') AND IFNULL(r1.cup,r2.cup)=0' . ($memberId ? '' : ' ORDER BY c.course DESC LIMIT 2000');
			$sql = "SELECT DISTINCT course FROM ($sql) t";
			$getChats = mysql_query($sql);
			$chatIds = array();
			while ($chat = mysql_fetch_array($getChats)) {
				$chatIds[] = $chat['course'];
			}
		}
		if (empty($chatIds)) {
			echo '&nbsp; ' . ($language ? 'No result found' : 'Aucun résultat trouvé');
			$nbPages = 0;
		}
		else {
			$chatIdsString = implode(',', $chatIds);
			$getNbConvs = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkchat` WHERE course IN ('. $chatIdsString .')'));
			$resPerPage = 100;
			$nbPages = ceil($getNbConvs['nb'] / $resPerPage);
			$logPage = -1;
			if (!empty($getLog)) {
				$getNbConvsBeforeLog = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkchat` WHERE course IN ('. $chatIdsString .') AND date<="'. $getLog['date'] .'"'));
				$logPage = max(ceil($getNbConvsBeforeLog['nb'] / $resPerPage), 1);
			}
			if (isset($_GET['page']))
				$currentPage = max(intval($_GET['page']),1);
			elseif ($logPage === -1)
				$currentPage = 1;
			else
				$currentPage = $logPage;
			$getConvs = mysql_query('SELECT c.date,c.course,c.auteur,c.message,j.nom FROM `mkchat` c LEFT JOIN `mkjoueurs` j ON c.auteur=j.id WHERE c.course IN ('. $chatIdsString .')'.(empty($getLog['chat']) ? '' : ' AND c.id!='.$getLog['chat']).' ORDER BY c.course DESC,c.id ASC LIMIT '.(($currentPage-1)*$resPerPage).','. $resPerPage);
			$lastCourse = 0;
			function show_censored_message() {
				global $getLog;
				echo '<div class="highlight highlight-alert"><a href="profil.php?id='. $getLog['player'] .'">'. $getLog['nom'] .'</a>: '. $getLog['message'] .'</div>';
			}
			$showCensored = ($logPage == $currentPage);
			while ($conv = mysql_fetch_array($getConvs)) {
				if ($lastCourse != $conv['course']) {
					if ($lastCourse)
						echo '<hr />';
					$lastCourse = $conv['course'];
				}
				if ($showCensored && (strtotime($conv['date']) > strtotime($getLog['date']))) {
					show_censored_message();
					$showCensored = false;
				}
				echo '<div'. ($conv['auteur'] == $memberId ? ' class="highlight"' : '') .'><a href="profil.php?id='. $conv['auteur'] .'">'. $conv['nom'] .'</a>: '. $conv['message'] .'</div>';
			}
			if ($showCensored)
				show_censored_message();
		}
		echo '</div>';
		if ($nbPages > 1) {
			?>
			<div class="chat-pages">
				Page : <?php
				$get = $_GET;
				require_once('../includes/utils-paging.php');
				$allPages = makePaging($currentPage,$nbPages,4);
				foreach ($allPages as $i=>$block) {
					if ($i)
						echo '...&nbsp; &nbsp;';
					foreach ($block as $p) {
						$get['page'] = $p;
						if ($p == $currentPage)
							echo "<strong>$p</strong>";
						else
							echo '<a href="?'. http_build_query($get) .'">'. $p .'</a>';
						echo ' &nbsp; ';
					}
				}
				?>
			</div>
			<?php
		}
	}
	?>
	<p><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('../includes/footer.php');
?>
<script type="text/javascript" src="scripts/auto-complete.min.js"></script>
<script type="text/javascript" src="scripts/autocomplete-player.js"></script>
<script type="text/javascript">
autocompletePlayer('#pseudo');
function toggleMute() {
	document.getElementById('mute-form').style.display = document.getElementById('mute-form').style.display == 'block' ? 'none':'block';
}
function mute(e, id) {
	e.preventDefault();
	var $form = e.target;
	var time = $form.elements["mute-time"].value;
	var unit = $form.elements["unit"].value;
	$form.querySelector('input[type="submit"]').disabled = true;
	var duration = time*unit;
	if (duration > 0) {
		o_xhr("mute.php", "member="+ id +"&duration="+ duration, function(reponse) {
			document.location.reload();
		});
	}
}
function unmute(id) {
	o_xhr("unmute.php", "member="+ id, function(reponse) {
		document.location.reload();
	});
}
</script>
<?php
mysql_close();
?>
</body>
</html>