<?php
include('../includes/session.php');
if (!$id) {
	echo 'You aren\'t logged in';
	exit;
}
include('../includes/language.php');
include('../includes/initdb.php');
require_once('../includes/getRights.php');
if (!hasRight('lounge')) {
	echo $language ? 'You aren\'t a lounge moderator' : 'Vous n\'&ecirc;tes pas mod&eacute;rateur du lounge';
	mysql_close();
	exit;
}
include('../includes/lounge/common.php');

function loungeLog($action) {
	global $id;
	mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. intval($id) .', "'. $action .'")');
}

$notice = null;
$target = null;
if (isset($_POST['player'])) {
	$target = mysql_fetch_array(mysql_query(
		'SELECT id, nom FROM `mkjoueurs` WHERE nom="'. $_POST['player'] .'" AND deleted=0'
	));
	if (!$target)
		$notice = $language ? 'No such member.' : 'Membre introuvable.';
}

if ($target && isset($_POST['action'])) {
	$playerId = intval($target['id']);
	$state = lounge_get_player_state($playerId);
	// the row may not exist yet for a player who has never queued
	mysql_query(
		'INSERT IGNORE INTO `mklounge_players` (player, season, mmr, peak_mmr)
		VALUES ("'. $playerId .'", "'. LOUNGE_CURRENT_SEASON .'",
			"'. lounge_mmr_sql(LOUNGE_DEFAULT_MMR) .'", "'. lounge_mmr_sql(LOUNGE_DEFAULT_MMR) .'")'
	);
	switch ($_POST['action']) {
	case 'mmr':
		$delta = floatval($_POST['mmr_delta']);
		$after = max(LOUNGE_MMR_MIN, $state['mmr'] + $delta);
		mysql_query(
			'UPDATE `mklounge_players`
			SET mmr="'. lounge_mmr_sql($after) .'", peak_mmr=GREATEST(peak_mmr, "'. lounge_mmr_sql($after) .'")
			WHERE player="'. $playerId .'" AND season="'. LOUNGE_CURRENT_SEASON .'"'
		);
		loungeLog('LoungeMmr '. $playerId .' '. round($state['mmr']) .' '. round($after));
		$notice = ($language ? 'MMR set to ' : 'MMR fix&eacute; &agrave; ') . round($after);
		break;
	case 'strikes':
		$strikes = max(0, intval($_POST['strikes']));
		mysql_query(
			'UPDATE `mklounge_players` SET strikes="'. $strikes .'"
			WHERE player="'. $playerId .'" AND season="'. LOUNGE_CURRENT_SEASON .'"'
		);
		loungeLog('LoungeStrikes '. $playerId .' '. $strikes);
		$notice = ($language ? 'Strikes set to ' : 'Strikes fix&eacute;s &agrave; ') . $strikes;
		break;
	case 'ban':
		$minutes = max(1, intval($_POST['ban_minutes']));
		mysql_query(
			'UPDATE `mklounge_players` SET banned_until=(NOW() + INTERVAL '. $minutes .' MINUTE)
			WHERE player="'. $playerId .'" AND season="'. LOUNGE_CURRENT_SEASON .'"'
		);
		loungeLog('LoungeBan '. $playerId .' '. $minutes);
		$notice = $language ? 'Banned from ranked.' : 'Banni du class&eacute;.';
		break;
	case 'unban':
		mysql_query(
			'UPDATE `mklounge_players` SET banned_until=NULL, strikes=0
			WHERE player="'. $playerId .'" AND season="'. LOUNGE_CURRENT_SEASON .'"'
		);
		loungeLog('LoungeUnban '. $playerId);
		$notice = $language ? 'Ban lifted.' : 'Bannissement lev&eacute;.';
		break;
	}
}

if (isset($_POST['release'])) {
	$queueId = intval($_POST['release']);
	$queue = mysql_fetch_array(mysql_query('SELECT status FROM `mklounge_queues` WHERE id="'. $queueId .'"'));
	if ($queue) {
		if ($queue['status'] === 'launched')
			lounge_abandon_match($queueId);
		else {
			mysql_query('UPDATE `mklounge_queues` SET status="cancelled" WHERE id="'. $queueId .'"');
			mysql_query(
				'UPDATE `mklounge_queue_members` SET dropped_at=NOW()
				WHERE queue="'. $queueId .'" AND dropped_at IS NULL'
			);
		}
		loungeLog('LoungeRelease '. $queueId);
		$notice = $language ? 'Queue released.' : 'File lib&eacute;r&eacute;e.';
	}
}

$state = $target ? lounge_get_player_state(intval($target['id'])) : null;
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'CT Lounge moderation':'Mod&eacute;ration CT Lounge'; ?> - Mario Kart PC</title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />
<style type="text/css">
h2 {
	margin-bottom: 5px;
}
.lounge-notice {
	font-weight: bold;
	color: #0A0;
}
.lounge-stats {
	margin: 5px 0 15px;
	font-size: 1.1em;
}
.lounge-stats span {
	margin: 0 6px;
	white-space: nowrap;
}
.lounge-actions {
	display: inline-block;
	text-align: left;
	background-color: #F90;
	border: double 4px black;
	padding: 5px 15px;
	color: black;
}
.lounge-actions form {
	margin: 8px 0;
}
.lounge-actions label {
	font-weight: bold;
}
.lounge-actions input[type="number"] {
	width: 70px;
}
main table form {
	margin: 0;
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
	<h1><?php echo $language ? 'CT Lounge moderation':'Mod&eacute;ration CT Lounge'; ?></h1>
	<?php if ($notice) { ?>
	<p class="lounge-notice"><?php echo $notice; ?></p>
	<?php } ?>
	<p><?php echo $language
		? 'Adjust a rating, hand out or lift a ranked ban, or release a queue that got stuck. Every action is recorded in the <a href="admin-logs.php?role=lounge">admin logs</a>.'
		: 'Ajustez un classement, donnez ou levez un bannissement du mode class&eacute;, ou lib&eacute;rez une file bloqu&eacute;e. Chaque action est enregistr&eacute;e dans les <a href="admin-logs.php?role=lounge">logs d\'administration</a>.'; ?></p>

	<h2><?php echo $language ? 'Member':'Membre'; ?></h2>
	<form method="post" action="admin-lounge.php">
		<input type="text" name="player" id="player" required="required"
			value="<?php echo $target ? htmlspecialchars($target['nom']) : ''; ?>" />
		<input type="submit" class="action_button" value="<?php echo $language ? 'Search':'Rechercher'; ?>" />
	</form>
	<?php if ($target) { ?>
	<h3><a href="profil.php?id=<?php echo intval($target['id']); ?>"><?php echo htmlspecialchars($target['nom']); ?></a></h3>
	<p class="lounge-stats">
		<span><strong><?php echo $state['mmr']; ?></strong> MMR</span>
		<?php if ($state['rank']) { ?>
		<span style="color:<?php echo $state['rank']['color']; ?>"><strong><?php
			echo $language ? $state['rank']['label_en'] : $state['rank']['label_fr']; ?></strong></span>
		<?php } ?>
		<span><?php echo $state['games']; ?> <?php echo $language ? 'mogis':'mogis'; ?></span>
		<span><?php echo $state['strikes']; ?> strike<?php echo $state['strikes'] > 1 ? 's':''; ?></span>
		<?php if ($state['banned_until']) { ?>
		<span style="color:#F44"><strong><?php echo $language ? 'banned until ':'banni jusqu\'au '; ?><?php
			echo htmlspecialchars($state['banned_until']); ?></strong></span>
		<?php } ?>
	</p>
	<div class="lounge-actions">
		<form method="post" action="admin-lounge.php">
			<input type="hidden" name="player" value="<?php echo htmlspecialchars($target['nom']); ?>" />
			<input type="hidden" name="action" value="mmr" />
			<label for="mmr_delta"><?php echo $language ? 'Adjust MMR by':'Ajuster le MMR de'; ?></label>
			<input type="number" name="mmr_delta" id="mmr_delta" step="0.01" value="0" />
			<input type="submit" class="action_button" value="<?php echo $language ? 'Apply':'Appliquer'; ?>" />
		</form>
		<form method="post" action="admin-lounge.php">
			<input type="hidden" name="player" value="<?php echo htmlspecialchars($target['nom']); ?>" />
			<input type="hidden" name="action" value="strikes" />
			<label for="strikes"><?php echo $language ? 'Set strikes to':'Fixer les strikes &agrave;'; ?></label>
			<input type="number" name="strikes" id="strikes" min="0" value="<?php echo $state['strikes']; ?>" />
			<input type="submit" class="action_button" value="<?php echo $language ? 'Apply':'Appliquer'; ?>" />
		</form>
		<form method="post" action="admin-lounge.php">
			<input type="hidden" name="player" value="<?php echo htmlspecialchars($target['nom']); ?>" />
			<input type="hidden" name="action" value="ban" />
			<label for="ban_minutes"><?php echo $language ? 'Ban from ranked for':'Bannir du class&eacute; pendant'; ?></label>
			<input type="number" name="ban_minutes" id="ban_minutes" min="1" value="<?php echo LOUNGE_BAN_MINUTES; ?>" />
			min
			<input type="submit" class="action_button" value="<?php echo $language ? 'Ban':'Bannir'; ?>" />
		</form>
		<form method="post" action="admin-lounge.php">
			<input type="hidden" name="player" value="<?php echo htmlspecialchars($target['nom']); ?>" />
			<input type="hidden" name="action" value="unban" />
			<input type="submit" class="action_button" value="<?php
				echo $language ? 'Lift ban and clear strikes':'Lever le bannissement et effacer les strikes'; ?>" />
		</form>
	</div>
	<?php } ?>

	<h2><?php echo $language ? 'Queues in progress':'Queues en cours'; ?></h2>
	<?php
	$queues = mysql_query(
		'SELECT q.id, q.status, q.launched_at, t.code AS tier,
			(SELECT COUNT(*) FROM `mklounge_queue_members` m WHERE m.queue=q.id AND m.dropped_at IS NULL) AS members
		FROM `mklounge_queues` q
		INNER JOIN `mklounge_tiers` t ON t.id=q.tier
		WHERE q.status NOT IN ("cancelled","finished")
		ORDER BY q.id DESC'
	);
	if (!mysql_numrows($queues)) {
		echo '<p>'. ($language ? 'Nothing in progress.' : 'Rien en cours.') .'</p>';
	}
	else {
	?>
	<table>
		<tr id="titres">
			<td>#</td>
			<td><?php echo $language ? 'Tier':'Tier'; ?></td>
			<td><?php echo $language ? 'Status':'Statut'; ?></td>
			<td><?php echo $language ? 'Players':'Joueurs'; ?></td>
			<td></td>
		</tr>
	<?php
		$dark = false;
		while ($queue = mysql_fetch_array($queues)) {
			$dark = !$dark;
	?>
		<tr class="<?php echo $dark ? 'fonce':'clair'; ?>">
			<td><?php echo $queue['id']; ?></td>
			<td><?php echo htmlspecialchars($queue['tier']); ?></td>
			<td><?php echo htmlspecialchars($queue['status']); ?></td>
			<td><?php echo $queue['members']; ?></td>
			<td><form method="post" action="admin-lounge.php">
				<?php if ($target) { ?>
				<input type="hidden" name="player" value="<?php echo htmlspecialchars($target['nom']); ?>" />
				<?php } ?>
				<input type="hidden" name="release" value="<?php echo $queue['id']; ?>" />
				<input type="submit" class="action_button action_delete"
					value="<?php echo $language ? 'Release':'Lib&eacute;rer'; ?>" />
			</form></td>
		</tr>
	<?php } ?>
	</table>
	<?php } ?>

	<p><a href="mariokart.php"><?php echo $language ? 'Back to online mode':'Retour au mode en ligne'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('../includes/footer.php');
?>
<script type="text/javascript" src="scripts/auto-complete.min.js"></script>
<script type="text/javascript" src="scripts/autocomplete-player.js"></script>
<script type="text/javascript">
autocompletePlayer('#player', {
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
