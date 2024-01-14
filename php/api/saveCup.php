<?php
header('Content-Type: text/plain');
if (isset($_POST['nom']) && isset($_POST['auteur']) && isset($_POST['mode'])) {
	include('../includes/getId.php');
	include('../includes/initdb.php');
	include('../includes/ip_banned.php');
	$mode = $_POST['mode'];
	if (isBanned()) {
		mysql_close();
		exit;
	}
	$save = true;
	$andWhere = '';
	switch ($mode) {
	case 0:
		$table = 'mkcircuits';
		$andWhere = ' AND !type';
		break;
	case 1:
		$table = 'circuits';
		break;
	case 2:
		$table = 'mkcircuits';
		$andWhere = ' AND type';
		break;
	case 3:
		$table = 'arenes';
		break;
	default:
		mysql_close();
		exit;
	}
	$currentCup = null;
	require_once('../includes/collabUtils.php');
	if (isset($_POST['id'])) {
		$requireOwner = !hasCollabGrants('mkcups', $_POST['id'], $_POST['collab'], 'edit');
		$currentCup = mysql_fetch_array(mysql_query('SELECT * FROM mkcups WHERE id="'. $_POST['id'] .'"'. ($requireOwner ? (' AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"') : '')));
	}
	for ($i=0;$i<4;$i++) {
		if (!isset($_POST['cid'.$i])) {
			$save = false;
			break;
		}
		$cId = $_POST['cid'.$i];
		if ($currentCup && in_array($cId, array($currentCup['circuit0'],$currentCup['circuit1'],$currentCup['circuit2'],$currentCup['circuit3'])))
			$requireOwner = false;
		else
			$requireOwner = !hasCollabGrants($table, $cId, $_POST['collabs'][$cId], 'use');
		if (!mysql_numrows(mysql_query('SELECT * FROM `'. $table .'` WHERE id="'. $cId .'"'. ($requireOwner ? (' AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"') : ''). $andWhere))) {
			$save = false;
			break;
		}
	}
	$cupId = -1;
	if ($save) {
		setcookie('mkauteur', $_POST['auteur'], 4294967295,'/');
		if (isset($_POST['id'])) {
			if ($currentCup) {
				mysql_query('UPDATE `mkcups` SET circuit0="'. $_POST['cid0'] .'",circuit1="'. $_POST['cid1'] .'",circuit2="'. $_POST['cid2'] .'",circuit3="'. $_POST['cid3'] .'",nom="'. $_POST['nom'] .'",auteur="'. $_POST['auteur'] .'" WHERE id="'. $_POST['id'] .'"');
				$cupId = intval($_POST['id']);
			}
		}
		else {
			include('../includes/idempotency.php');
			$cupId = withRequestIdempotency(array(
				'is_cache_stale' => function($cupId) {
					return !mysql_numrows(mysql_query('SELECT * FROM `mkcups` WHERE id="'.$cupId.'"'));
				},
				'callback' => function() use($identifiants, $mode) {
					mysql_query('INSERT INTO `mkcups` VALUES(NULL,CURRENT_TIMESTAMP(),'.$identifiants[0].','.$identifiants[1].','.$identifiants[2].','.$identifiants[3].',0,0,0,0,0,"'. $mode .'","'. $_POST['cid0'] .'","'. $_POST['cid1'] .'","'. $_POST['cid2'] .'","'. $_POST['cid3'] .'","'. $_POST['nom'] .'","'. $_POST['auteur'] .'")');
					$cupId = mysql_insert_id();
					include('../includes/session.php');
					if ($id) {
						$getFollowers = mysql_query('SELECT follower FROM `mkfollowusers` WHERE followed="'. $id .'"');
						while ($follower = mysql_fetch_array($getFollowers))
							mysql_query('INSERT INTO `mknotifs` SET type="follower_circuit", user="'. $follower['follower'] .'", link="3,'.$cupId.'"');
					}
					return $cupId;
				}
			));
		}
		if ($cupId != -1) {
			if (isset($_POST['cl'])) {
				include('../includes/challenge-associate.php');
				challengeAssociate('mkcups',$cupId,$_POST['cl']);
			}
			include('../includes/postCircuitUpdate.php');
			postCircuitUpdate('mkcups', $cupId);
		}
	}
	echo $cupId;
	mysql_close();
}
?>