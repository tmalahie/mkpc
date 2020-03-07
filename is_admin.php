<?php
$IS_ADMIN = false;
function isAdmin() {
	global $id, $IS_ADMIN;
	if ($id) {
		if ($player = mysql_fetch_array(mysql_query('SELECT admin FROM `mkjoueurs` WHERE id="'. $id .'"'))) {
			if ($player['admin']==1) {
				$IS_ADMIN = true;
				return true;
			}
		}
	}
	return false;
}
?>