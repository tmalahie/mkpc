<?php
$getBanned = mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"');
$isBanned = mysql_fetch_array($getBanned);
if ($isBanned && $isBanned['banned']) {
    if ($isBanned['banned'] == 1)
        include('ban_ip.php');
    $getBanDetails = mysql_fetch_array(mysql_query('SELECT msg, end_date FROM `mkbans` WHERE player='. $id));
    $banReason = $getBanDetails['msg'] ?? null;
    $banEndDate = $getBanDetails['end_date'] ?? null;
    $currentDate = date('Y-m-d');
    if ($language) {
        ?>
        <p class="warning">
            <strong>You have been banned!</strong><br />
            <?php if ($banReason): ?>
                Reason: <strong style="background-color: #FCC;display:inline-block;margin:5px;padding:5px"><?php echo nl2br(htmlspecialchars($banReason)); ?></strong><br />
            <?php endif; ?>
            Ends at: <strong><?php echo $banEndDate ? htmlspecialchars($banEndDate) : 'Permanent'; ?></strong><br />
            Be careful: in case of recurrence, your account will be deleted.<br />
        </p>
        <?php
    } else {
        ?>
        <p class="warning">
            <strong>Vous avez été banni!</strong><br />
            <?php if ($banReason): ?>
                Raison: <strong style="background-color: #FCC;display:inline-block;margin:5px;padding:5px"><?php echo nl2br(htmlspecialchars($banReason)); ?></strong><br />
            <?php endif; ?>
            Fin: <strong><?php echo $banEndDate ? htmlspecialchars($banEndDate) : 'Permanent'; ?></strong><br />
            Attention: en cas de récidive, votre compte sera supprimé définitivement.<br />
        </p>
        <?php
    }
}
elseif ($getWarnMsg = mysql_fetch_array(mysql_query('SELECT msg FROM mkwarns WHERE player='. $id .' AND msg!=""'))) {
	?>
	<?php
	if ($language) {
		?>
		<p class="notice">The moderation team has given you the following warning:<br />
		<strong style="background-color: #FEC;display:inline-block;margin:5px;padding:5px"><?php
		echo nl2br(htmlspecialchars($getWarnMsg['msg']));
		?></strong><br />
		Caution, if you don't change your behavior, you might have your account banned from the site.</p>
		<?php
	}
	else {
		?>
		<p class="notice">L'équipe de modération vous a donné l'avertissement suivant :<br />
		<strong style="background-color: #FEC;display:inline-block;margin:5px;padding:5px"><?php
		echo nl2br(htmlspecialchars($getWarnMsg['msg']));
		?></strong><br />
		Attention, en cas de récidive, votre compte pourrait être banni du site.</p>
		<?php
	}
	if (isset($_GET['warn']))
		mysql_query('UPDATE mkwarns SET seen=1 WHERE player='.$id);
}
require_once('getRights.php');
if (hasRight('manager')) {
	if (hasRight('admin'))
		$roleName = $language ? 'administrator':'administrateur';
	elseif (hasRight('moderator'))
		$roleName = $language ? 'moderator':'modérateur';
	else
		$roleName = $language ? 'event host':'animateur';
	if ($language) {
		?>
		<div class="success">
		You are now <?php echo $roleName; ?>! <a href="admin.php">Click here</a> to go to the admin page.
		</div>
		<?php
	}
	else {
		?>
		<div class="success">
		Vous êtes maintenant <?php echo $roleName; ?> ! <a href="admin.php">Cliquez ici</a> pour vous rendre sur la page admin.
		</div>
		<?php
	}
}
?>