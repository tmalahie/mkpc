<?php
	if ($banned['banned'] == 1)
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
?>