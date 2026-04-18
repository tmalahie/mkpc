<?php
if (isset($_GET['id'])) {
	$decorId = intval($_GET['id']);
	include('../includes/initdb.php');
	include('../includes/getId.php');
	require_once('../includes/collabUtils.php');
	$collabSuffix = '';
	$hasWriteGrants = false;
	if ($decor = mysql_fetch_array(mysql_query('SELECT * FROM `mkdecors` WHERE id="'. $decorId .'"'))) {
		if ($decor['identifiant'] == $identifiants[0]) {
			$hasWriteGrants = true;
		}
		else {
			$collab = getCollabLinkFromQuery('mkdecors', $decor['extra_parent_id'] ?? $decorId);
			$hasWriteGrants = isset($collab['rights']['edit']);
			if ($collab) $collabSuffix = '&collab='. $collab['key'];
		}
		if ($hasWriteGrants) {
			require_once('../includes/utils-decors.php');
			$options = $decor['options'] ? json_decode($decor['options'], true) : array();
			if (isset($_GET['enable']))
				unset($options['no_map_icon']);
			else
				$options['no_map_icon'] = 1;
			$newOptionsJson = !empty($options) ? json_encode($options) : '';
			mysql_query('UPDATE `mkdecors` SET options="'. mysql_real_escape_string($newOptionsJson) .'" WHERE id="'. $decorId .'"');
		}
	}
	mysql_close();
	if (isset($_GET['enable']))
		header('location: decorSprite.php?id='. $decorId .'&map'. $collabSuffix);
	else
		header('location: decorOptions.php?id='. $decorId . $collabSuffix);
}
?>
