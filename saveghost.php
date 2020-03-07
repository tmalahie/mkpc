<?php
if (isset($_POST['map']) && isset($_POST['perso'])) {
	for ($n=0;isset($_POST['p'.$n]);$n++);
	if ($n < 10000) {
		include('initdb.php');
		include('getId.php');
		include('session.php');
		$map = $_POST['map'];
		$time = $n*67;
		$player = 0;
		if ($id)
			$player = $id;
		elseif ($getByIp = mysql_fetch_array(mysql_query('SELECT id FROM mkprofiles WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3].' ORDER BY nbmessages DESC LIMIT 1')))
			$player = $getByIp['id'];
		if (isset($_POST['time']) && ($_POST['time'] >= $time))
			$time = $_POST['time'];
		if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM `mkghosts` WHERE circuit="'.$map.'" AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]))) {
			$cID = $getId['id'];
			mysql_query('UPDATE `mkghosts` SET perso="'. $_POST['perso'] .'", player='. $player .', time="'.$time.'" WHERE id='. $cID);
			mysql_query('DELETE FROM `mkghostdata` WHERE ghost='. $cID);
		}
		else {
			mysql_query('INSERT INTO `mkghosts` SET identifiant='.$identifiants[0].',identifiant2='.$identifiants[1].',identifiant3='.$identifiants[2].',identifiant4='.$identifiants[3].',player='.$player.',circuit='.$map.',perso="'.$_POST['perso'].'",time="'.$time.'"');
			$cID = mysql_insert_id();
		}
		$sqlBatch = "INSERT INTO `mkghostdata` VALUES";
		for ($i=0;$i<$n;$i++) {
			$infos = split('_', $_POST['p'.$i]);
			if ($i)
				$sqlBatch .= ',';
			$sqlBatch .= "($cID,$i,'".$infos[0]."','".$infos[1]."','".$infos[2]."','".$infos[3]."',b'".($infos[4]?$infos[4]:0)."')";
		}
		mysql_query($sqlBatch);
		mysql_close();
	}
	echo 1;
}
?>