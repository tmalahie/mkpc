<?php
header('Content-Type: text/plain');
if (isset($_POST['map']) && isset($_POST['perso'])) {
	for ($n=0;isset($_POST['p'.$n]);$n++);
	if ($n < 10000) {
		include('../includes/initdb.php');
		include('../includes/getId.php');
		include('../includes/session.php');
		if ($id) {
			$getBanned = mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"');
			$isBanned = mysql_fetch_array($getBanned);
			if ($isBanned && $isBanned['banned']) {
				echo 1;
				mysql_close();
				exit;
			}
		}
		$map = intval($_POST['map']);
		$type = isset($_POST['type']) ? $_POST['type'] : '';
		$cc = isset($_POST['cc']) ? $_POST['cc'] : 150;
		if (!in_array($cc,array(150,200))) {
			echo 1;
			mysql_close();
			exit;
		}
		$time = $n*67;
		$player = 0;
		if ($id)
			$player = $id;
		elseif ($getByIp = mysql_fetch_array(mysql_query('SELECT id FROM mkprofiles WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3].' ORDER BY nbmessages DESC LIMIT 1')))
			$player = $getByIp['id'];
		if (isset($_POST['time']) && ($_POST['time'] >= $time))
			$time = $_POST['time'];
		if (isset($_POST['times'])) {
			$timesJson = json_decode($_POST['times']);
			if (is_array($timesJson) && (!empty($timesJson)))
				$times = $_POST['times'];
		}
		if (!isset($times)) {
			$fakeTime = round($time/3);
			$times = "[$fakeTime,$fakeTime,$fakeTime]";
		}
		$getId = mysql_fetch_array(mysql_query('SELECT id FROM `mkghosts` WHERE class="'.$cc.'" AND type="'.$type.'" AND circuit="'.$map.'" AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]));
		if ($getId)
			$cID = $getId['id'];
		else
			$cID = -1;
		require_once('../includes/tt-quotas.php');
		$totalGhostTime = tt_used_quota(array(
			'ghost' => $cID
		));
		if ($totalGhostTime > MAX_GHOST_TIME) {
			echo -1;
			mysql_close();
			exit;
		}
		if ($cID > 0) {
			mysql_query('UPDATE `mkghosts` SET perso="'. $_POST['perso'] .'", player='. $player .', time="'.$time.'", lap_times="'.$times.'" WHERE id='. $cID);
			mysql_query('DELETE FROM `mkghostsdata` WHERE id='. $cID);
		}
		else {
			mysql_query('INSERT INTO `mkghosts` SET identifiant='.$identifiants[0].',identifiant2='.$identifiants[1].',identifiant3='.$identifiants[2].',identifiant4='.$identifiants[3].',player='.$player.',class='.$cc.',type="'.$type.'",circuit='.$map.',perso="'.$_POST['perso'].'",time="'.$time.'",lap_times="'.$times.'"');
			$cID = mysql_insert_id();
		}
		$ptsData = array();
		for ($i=0;$i<$n;$i++) {
			$infos = explode('_', $_POST['p'.$i]);
			$ptData = array($infos[0],$infos[1],round($infos[2],3),$infos[3]);
			if (!empty($infos[4])) {
				$eInfos = $infos[4];
				$extra = array();
				if (!empty($eInfos[0]))
					$extra['f'] = 1; // fall
				if (!empty($eInfos[1])) {
					if (!empty($eInfos[2]))
						$extra['d'] = 1; // drift right
					elseif (!empty($eInfos[3]))
						$extra['d'] = -1; // drift left
					else
						$extra['d'] = 0; // drift straight
				}
				$ptData[] = $extra;
			}
			$ptsData[] = $ptData;
		}
		mysql_query('INSERT INTO `mkghostsdata` SET id="'. $cID .'",data="'. mysql_real_escape_string(gzcompress(json_encode($ptsData))) .'"');
		mysql_close();
	}
	echo 1;
}
?>
