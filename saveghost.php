<?php
if (isset($_POST['map']) && isset($_POST['perso'])) {
	for ($n=0;isset($_POST['p'.$n]);$n++);
	if ($n < 10000) {
		include('initdb.php');
		include('getId.php');
		include('session.php');
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
			if (is_array($timesJson) && (3 === count($timesJson)))
				$times = $_POST['times'];
		}
		if (!isset($times)) {
			$fakeTime = round($time/3);
			$times = "[$fakeTime,$fakeTime,$fakeTime]";
		}
		if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM `mkghosts` WHERE class="'.$cc.'" AND circuit="'.$map.'" AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]))) {
			$cID = $getId['id'];
			mysql_query('UPDATE `mkghosts` SET perso="'. $_POST['perso'] .'", player='. $player .', time="'.$time.'", lap_times="'.$times.'" WHERE id='. $cID);
			mysql_query('DELETE FROM `mkghostdata` WHERE ghost='. $cID);
		}
		else {
			mysql_query('INSERT INTO `mkghosts` SET identifiant='.$identifiants[0].',identifiant2='.$identifiants[1].',identifiant3='.$identifiants[2].',identifiant4='.$identifiants[3].',player='.$player.',class='.$cc.',circuit='.$map.',perso="'.$_POST['perso'].'",time="'.$time.'",lap_times="'.$times.'"');
			$cID = mysql_insert_id();
		}
		$sqlBatch = "INSERT INTO `mkghostdata` VALUES";
		for ($i=0;$i<$n;$i++) {
			$infos = explode('_', $_POST['p'.$i]);
			if ($i)
				$sqlBatch .= ',';
			$sqlBatch .= "($cID,$i,'".$infos[0]."','".$infos[1]."','".$infos[2]."','".$infos[3]."',b'".(empty($infos[4])?0:$infos[4])."')";
		}
		mysql_query($sqlBatch);
		mysql_close();
	}
	echo 1;
}
?>
