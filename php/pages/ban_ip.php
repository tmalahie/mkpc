<?php
if (isset($id) && $id && isset($identifiants))
	mysql_query('INSERT INTO `ip_bans` VALUES("'. $id .'","'. $identifiants[0] .'","'. $identifiants[1] .'","'. $identifiants[2] .'","'. $identifiants[3] .'")');
?>