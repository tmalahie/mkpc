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
<?php include('../includes/heads.php'); ?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<style type="text/css">
.lounge-admin-form { margin: 0 0 20px; }
.lounge-admin-form input[type=text], .lounge-admin-form input[type=number] { width: 120px; }
.lounge-admin-stats { margin: 0 0 15px; }
.lounge-admin-notice { color: #0a0; font-weight: bold; }
.lounge-admin-table { border-collapse: collapse; }
.lounge-admin-table td, .lounge-admin-table th { border: solid 1px #999; padding: 4px 8px; }
</style>
</head>
<body>
<?php include('../includes/header.php'); ?>
<main>
<h1><?php echo $language ? 'CT Lounge moderation':'Mod&eacute;ration CT Lounge'; ?></h1>
<?php if ($notice) { ?>
<p class="lounge-admin-notice"><?php echo $notice; ?></p>
<?php } ?>

<h2><?php echo $language ? 'Member':'Membre'; ?></h2>
<form method="post" action="admin-lounge.php" class="lounge-admin-form">
	<input type="text" name="player" value="<?php echo $target ? htmlspecialchars($target['nom']) : ''; ?>" />
	<input type="submit" value="<?php echo $language ? 'Search':'Rechercher'; ?>" />
</form>

<?php if ($target) { ?>
<h3><?php echo htmlspecialchars($target['nom']); ?></h3>
<p class="lounge-admin-stats">
	<strong><?php echo $state['mmr']; ?> MMR</strong>
	<?php if ($state['rank']) { ?>
		&ndash; <strong style="color:<?php echo $state['rank']['color']; ?>"><?php
			echo $language ? $state['rank']['label_en'] : $state['rank']['label_fr']; ?></strong>
	<?php } ?>
	&ndash; <?php echo $state['games']; ?> <?php echo $language ? 'mogis':'mogis'; ?>
	&ndash; <?php echo $state['strikes']; ?> strike<?php echo $state['strikes'] > 1 ? 's':''; ?>
	<?php if ($state['banned_until']) { ?>
		&ndash; <strong><?php echo $language ? 'banned until ':'banni jusqu\'au '; ?><?php echo $state['banned_until']; ?></strong>
	<?php } ?>
</p>

<form method="post" action="admin-lounge.php" class="lounge-admin-form">
	<input type="hidden" name="player" value="<?php echo htmlspecialchars($target['nom']); ?>" />
	<input type="hidden" name="action" value="mmr" />
	<label><?php echo $language ? 'Adjust MMR by':'Ajuster le MMR de'; ?>
		<input type="number" name="mmr_delta" step="0.01" value="0" /></label>
	<input type="submit" value="<?php echo $language ? 'Apply':'Appliquer'; ?>" />
</form>

<form method="post" action="admin-lounge.php" class="lounge-admin-form">
	<input type="hidden" name="player" value="<?php echo htmlspecialchars($target['nom']); ?>" />
	<input type="hidden" name="action" value="strikes" />
	<label><?php echo $language ? 'Set strikes to':'Fixer les strikes &agrave;'; ?>
		<input type="number" name="strikes" min="0" value="<?php echo $state['strikes']; ?>" /></label>
	<input type="submit" value="<?php echo $language ? 'Apply':'Appliquer'; ?>" />
</form>

<form method="post" action="admin-lounge.php" class="lounge-admin-form">
	<input type="hidden" name="player" value="<?php echo htmlspecialchars($target['nom']); ?>" />
	<input type="hidden" name="action" value="ban" />
	<label><?php echo $language ? 'Ban from ranked for':'Bannir du class&eacute; pendant'; ?>
		<input type="number" name="ban_minutes" min="1" value="<?php echo LOUNGE_BAN_MINUTES; ?>" />
		<?php echo $language ? 'minutes':'minutes'; ?></label>
	<input type="submit" value="<?php echo $language ? 'Ban':'Bannir'; ?>" />
</form>

<form method="post" action="admin-lounge.php" class="lounge-admin-form">
	<input type="hidden" name="player" value="<?php echo htmlspecialchars($target['nom']); ?>" />
	<input type="hidden" name="action" value="unban" />
	<input type="submit" value="<?php echo $language ? 'Lift ban and clear strikes':'Lever le bannissement et effacer les strikes'; ?>" />
</form>
<?php } ?>

<h2><?php echo $language ? 'Queues in progress':'Files en cours'; ?></h2>
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
<table class="lounge-admin-table">
	<tr>
		<th>#</th>
		<th><?php echo $language ? 'Tier':'Tier'; ?></th>
		<th><?php echo $language ? 'Status':'Statut'; ?></th>
		<th><?php echo $language ? 'Players':'Joueurs'; ?></th>
		<th></th>
	</tr>
<?php while ($queue = mysql_fetch_array($queues)) { ?>
	<tr>
		<td><?php echo $queue['id']; ?></td>
		<td><?php echo htmlspecialchars($queue['tier']); ?></td>
		<td><?php echo $queue['status']; ?></td>
		<td><?php echo $queue['members']; ?></td>
		<td><form method="post" action="admin-lounge.php">
			<input type="hidden" name="release" value="<?php echo $queue['id']; ?>" />
			<input type="submit" value="<?php echo $language ? 'Release':'Lib&eacute;rer'; ?>" />
		</form></td>
	</tr>
<?php } ?>
</table>
<?php } ?>

<p><a href="admin.php"><?php echo $language ? 'Back to admin':'Retour &agrave; l\'administration'; ?></a></p>
</main>
<?php
include('../includes/footer.php');
mysql_close();
?>
