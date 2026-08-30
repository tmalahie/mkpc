<?php
header('Content-Type: text/plain');
include('../includes/session.php');
if ($id) {
	if (isset($_POST['key'])) {
		if (isset($_POST['options'])) $options = $_POST['options'];
		include('../includes/initdb.php');
		if (isset($options)) $_POST['options'] = $options;
		$key = intval($_POST['key']);
		if ($privateLink = mysql_fetch_array(mysql_query('SELECT player FROM `mkprivgame` WHERE id="'. $key .'"'))) {
			$canEdit = ($privateLink['player'] == $id);
			if (!$canEdit) {
				require_once('../includes/getRights.php');
				require_once('../includes/lounge/common.php');
				// A lounge link belongs to nobody, so moderators are the only people who can
				// repair a mogi whose room needs its rules changed mid-match.
				$canEdit = hasRight('lounge') && lounge_is_lounge_link($key);
			}
			if ($canEdit)
				include('../includes/updateGameOptions.php');
		}
		mysql_close();
		echo 1;
	}
}
?>