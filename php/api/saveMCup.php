<?php
header('Content-Type: text/plain');
if (isset($_POST['nom']) && isset($_POST['auteur']) && isset($_POST['mode'])) {
	include('../includes/getId.php');
	include('../includes/initdb.php');
	include('../includes/ip_banned.php');
	require_once('../includes/collabUtils.php');
	$mode = $_POST['mode'];
	if (isBanned()) {
		mysql_close();
		exit;
	}
	$save = true;
	$maxCups = 40;
	$currentMCup = null;
	if (isset($_POST['id'])) {
		$requireOwner = !hasCollabGrants('mkmcups', $_POST['id'], $_POST['collab'], 'edit');
		$currentMCup = mysql_fetch_array(mysql_query('SELECT * FROM mkmcups WHERE id="'. $_POST['id'] .'"'. ($requireOwner ? (' AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"') : '')));
		if ($currentMCup) {
			$currentCups = array();
			$getCups = mysql_query('SELECT cup FROM `mkmcups_tracks` WHERE mcup="'. $_POST['id'] .'" ORDER BY ordering');
			while ($cup = mysql_fetch_array($getCups))
				$currentCups[$cup['cup']] = true;
			$currentMCup['cups'] = $currentCups;
		}
	}
	if (isset($_POST['cid'.$maxCups])) unset($_POST['cid'.$maxCups]);
	for ($i=0;isset($_POST['cid'.$i]);$i++) {
		$cId = $_POST['cid'.$i];
		if (isset($currentMCup['cups'][$cId]))
			$requireOwner = false;
		else
			$requireOwner = !hasCollabGrants('mkcups', $cId, $_POST['collabs'][$cId], 'use');
		if (!mysql_numrows(mysql_query('SELECT * FROM `mkcups` WHERE id="'. $_POST['cid'. $i] .'"'. ($requireOwner ? (' AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"') : '') . ($mode ? '':' AND mode="'. $mode .'"')))) {
			$save = false;
			break;
		}
	}
	if ($save) {
		$nbCups = $i;
		$optionsJson = isset($_POST['opt']) ? $_POST['opt']:'';
		setcookie('mkauteur', $_POST['auteur'], 4294967295,'/');
		if (isset($_POST['id'])) {
			if ($currentMCup) {
				mysql_query('UPDATE `mkmcups` SET nom="'. $_POST['nom'] .'",auteur="'. $_POST['auteur'] .'",options="'.$optionsJson.'" WHERE id="'. $_POST['id'] .'"');
				$cupId = intval($_POST['id']);
			}
			else
				$save = false;
		}
		else {
			include('../includes/idempotency.php');
			$isNew = false;
			$cupId = withRequestIdempotency(array(
				'is_cache_stale' => function($cupId) {
					return !mysql_numrows(mysql_query('SELECT * FROM `mkmcups` WHERE id="'. $cupId .'"'));
				},
				'callback' => function() use($identifiants, $mode, $optionsJson, &$isNew) {
					mysql_query('INSERT INTO `mkmcups` VALUES(NULL,CURRENT_TIMESTAMP(),'.$identifiants[0].','.$identifiants[1].','.$identifiants[2].','.$identifiants[3].',0,0,0,0,0,"'. $mode .'","'. $_POST['nom'] .'","'. $_POST['auteur'] .'","'.$optionsJson.'")');
					$cupId = mysql_insert_id();
					include('../includes/session.php');
					if ($id) {
						$getFollowers = mysql_query('SELECT follower FROM `mkfollowusers` WHERE followed="'. $id .'"');
						while ($follower = mysql_fetch_array($getFollowers))
							mysql_query('INSERT INTO `mknotifs` SET type="follower_circuit", user="'. $follower['follower'] .'", link="4,'.$cupId.'"');
					}
					$isNew = true;
					return $cupId;
				}
			));
			if (!$isNew) {
				echo $cupId;
				mysql_close();
				exit;
			}
		}
	}
	if ($save) {
		if (isset($_POST['id']))
			mysql_query('DELETE FROM `mkmcups_tracks` WHERE mcup="'. $cupId .'"');
		for ($i=0;isset($_POST['cid'.$i]);$i++)
			mysql_query('INSERT INTO `mkmcups_tracks` VALUES("'.$cupId.'",'.$i.',"'.$_POST['cid'.$i].'")');
		if (isset($_POST['cl'])) {
			include('../includes/challenge-associate.php');
			challengeAssociate('mkmcups',$cupId,$_POST['cl']);
		}
		require_once('../includes/cache_creations.php');
		@unlink(cachePath("mcuppreview$cupId.png"));
		include('../includes/postCircuitUpdate.php');
		postCircuitUpdate('mkmcups', $cupId);
		echo $cupId;
	}
	else
		echo -1;
	mysql_close();
}
?>