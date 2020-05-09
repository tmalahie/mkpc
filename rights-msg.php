<?php
$getBanned = mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"');
$isBanned = mysql_fetch_array($getBanned);
if ($isBanned && $isBanned['banned']) {
	if ($isBanned['banned'] == 1)
		include('ban_ip.php');
	if ($language) {
		?>
		<p class="warning"><?php
			$getBanMsg = mysql_fetch_array(mysql_query('SELECT msg FROM `mkbans` WHERE player='. $id));
			if ($getBanMsg && $getBanMsg['msg']) {
				?>
				<p class="warning">You have been banned for the following reason:<br />
				<strong style="background-color: #FCC;display:inline-block;margin:5px;padding:5px"><?php
				$getBanMsg = mysql_fetch_array(mysql_query('SELECT msg FROM `mkbans` WHERE player='. $id));
				echo nl2br(htmlspecialchars($getBanMsg['msg']));
				?></strong><br />
				Therefore, you can not post messages until further notice.</p>
				<?php
			}
			else {
				?>
				You have been banned temporarily because of inappropriate behavior.<br />
				Therefore, you can not post messages until further notice.<br />
				Be careful : in case of recurrence, your account will be deleted.
				<?php
			}
			?>
		</p>
		<?php
	}
	else {
		?>
		<p class="warning"><?php
			$getBanMsg = mysql_fetch_array(mysql_query('SELECT msg FROM `mkbans` WHERE player='. $id));
			if ($getBanMsg && $getBanMsg['msg']) {
				?>
				Vous avez été banni pour la raison suivante :<br />
				<strong style="background-color: #FCC;display:inline-block;margin:5px;padding:5px"><?php
				$getBanMsg = mysql_fetch_array(mysql_query('SELECT msg FROM `mkbans` WHERE player='. $id));
				echo nl2br(htmlspecialchars($getBanMsg['msg']));
				?></strong><br />
				Par conséquent, vous ne pourrez plus poster de message jusqu'à nouvel ordre.
				<?php
			}
			else {
				?>
				Vous avez été banni temporairement suite à un comportement innaproprié sur le site.<br />
				Par conséquent, vous ne pouvez plus poster de message jusqu'à nouvel ordre.<br />
				Attention : en cas de récidive, votre compte sera supprimé définitivement.
				<?php
			}
			?>
		</p>
		<?php
	}
}
require_once('getRights.php');
if (hasRight('manager')) {
	if (hasRight('admin'))
		$roleName = $language ? 'administrator':'administrateur';
	elseif (hasRight('moderator'))
		$roleName = $language ? 'moderator':'modérateur';
	else
		$roleName = $language ? 'animator':'animateur';
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