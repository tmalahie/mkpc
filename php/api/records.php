<?php
header('Content-Type: text/plain');
$body = file_get_contents('php://input');
if (isset($_POST["name"]) && isset($_POST["perso"]) && isset($_POST["time"])) {
	setcookie('mkrecorder', $_POST['name'], 4294967295,'/');
	include('../includes/initdb.php');
	include('../includes/getId.php');
	include('../includes/session.php');
	include('../includes/utils-hash.php');
	if (!isHashValid($body)) {
		logHashInvalid($body);
		mysql_close();
		echo -1;
		exit;
	}
	if ($id) {
		$getBanned = mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"');
		$isBanned = mysql_fetch_array($getBanned);
		if ($isBanned && $isBanned['banned']) {
			echo -1;
			mysql_close();
			exit;
		}
	}
	$cc = isset($_POST['cc']) ? $_POST['cc'] : 150;
	if (!in_array($cc,array(150,200))) {
		echo -1;
		mysql_close();
		exit;
	}
	if (isset($_POST['circuit'])) {
		$type = '';
		$map = $_POST["circuit"];
	}
	elseif (isset($_POST['creation'])) {
		$map = $_POST['creation'];
		if (mysql_numrows(mysql_query('SELECT * FROM `mkcircuits` WHERE id="'. $map .'"')))
			$type = 'mkcircuits';
		else
			unset($map);
	}
	elseif (isset($_POST['map'])) {
		$map = $_POST['map'];
		if (mysql_numrows(mysql_query('SELECT * FROM `circuits` WHERE id="'. $map .'"')))
			$type = 'circuits';
		else
			unset($map);
	}
	if (isset($map)) {
		$name = ucwords($_POST["name"]);
		$time = round($_POST["time"]);
		$existingNick = mysql_query('SELECT j.id FROM `mkjoueurs` j INNER JOIN `mkprofiles` p ON j.id=p.id INNER JOIN `mkrecords` r ON r.name=j.nom AND p.identifiant=r.identifiant AND p.identifiant2=r.identifiant2 AND p.identifiant3=r.identifiant3 AND p.identifiant4=r.identifiant4 WHERE j.nom="'.$name.'" AND j.id!="'.$id.'" AND (p.identifiant!='.$identifiants[0].' OR p.identifiant2!='.$identifiants[1].' OR p.identifiant3!='.$identifiants[2].' OR p.identifiant4!='.$identifiants[3].') LIMIT 1');
		if (mysql_fetch_array($existingNick))
			echo 1;
		else {
			$player = 0;
			if ($getByName = mysql_fetch_array(mysql_query('SELECT j.id FROM mkjoueurs j INNER JOIN mkprofiles p ON j.id=p.id WHERE j.nom="'.$name.'" AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3])))
				$player = $getByName['id'];
			elseif ($id)
				$player = $id;
			elseif ($getByIp = mysql_fetch_array(mysql_query('SELECT id FROM mkprofiles WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3].' ORDER BY nbmessages DESC LIMIT 1')))
				$player = $getByIp['id'];
			mysql_query('UPDATE `mkrecords` SET best=0 WHERE class="'.$cc.'" AND type="'.$type.'" AND circuit="'.$map.'" AND player="'.$player.'" AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'" AND time > '.$time);
			$isBestScore = mysql_query('SELECT id FROM `mkrecords` WHERE class="'.$cc.'" AND type="'.$type.'" AND circuit="'.$map.'" AND player="'.$player.'" AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'" AND best=1');
			if (mysql_fetch_array($isBestScore))
				echo 0;
			else {
				mysql_query('INSERT INTO `mkrecords` SET name="'.$name.'",identifiant="'. $identifiants[0] .'",identifiant2="'. $identifiants[1] .'",identifiant3="'. $identifiants[2] .'",identifiant4="'. $identifiants[3] .'",player='.$player.',perso="'.$_POST["perso"].'",class="'.$cc.'",type="'.$type.'",circuit="'.$map.'",time="'.$time.'",best=1');
				$rId = mysql_insert_id();
				$rank = mysql_numrows(mysql_query("SELECT * FROM `mkrecords` WHERE class='$cc' AND circuit='$map' AND type='$type' AND time < $time AND best=1"));
				if (!$type && ($rank < 5)) {
					$playersToAlert = mysql_query("SELECT identifiant,identifiant2,identifiant3,identifiant4,MIN(time) AS record FROM (
						SELECT identifiant,identifiant2,identifiant3,identifiant4,time FROM `mkrecords`
						WHERE circuit='$map' AND class='$cc' AND type='$type' AND best=1 ORDER BY time LIMIT 6
					) t GROUP BY identifiant,identifiant2,identifiant3,identifiant4");
					$getLastRecord = mysql_query('SELECT MIN(time) AS record FROM `mkrecords` WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3].' AND class="'.$cc.'" AND circuit="'.$map.'" AND player="'.$player.'" AND id!="'. $rId .'"');
					$lastRecord = mysql_fetch_array($getLastRecord);
					$lastTime = $lastRecord['record'];
					if (null === $lastTime)
						$lastTime = INF;
					while ($player = mysql_fetch_array($playersToAlert)) {
						if (($player['record'] <= $lastTime) && ($player['record'] > $time)) {
							if (($player['identifiant'] != $identifiants[0]) || ($player['identifiant2'] != $identifiants[1]) || ($player['identifiant3'] != $identifiants[2]) || ($player['identifiant4'] != $identifiants[3]))
								mysql_query('INSERT INTO `mknotifs` SET type="new_record", identifiant="'. $player['identifiant'] .'",identifiant2="'. $player['identifiant2'] .'",identifiant3="'. $player['identifiant3'] .'",identifiant4="'. $player['identifiant4'] .'", link="'.$rId.'"');
						}
					}
				}
				if (!$type) {
					mysql_query('SET @i=0, @c=0, @d=0, @t=0');
					mysql_query('START TRANSACTION');
					mysql_query('DELETE FROM mkttranking WHERE class="'. $cc .'"');
					mysql_query(
						"INSERT INTO mkttranking
						SELECT player,'$cc' AS class,SUM(CASE WHEN rank=1 THEN 10 ELSE CASE WHEN nb=2 THEN 0 ELSE ROUND(8*pow(1-(rank-2)/(nb-2),4/3)) END END) AS score FROM
						(SELECT MIN(r.rank) AS rank,c.nb,r.circuit,r.player FROM (
						SELECT (@d:=(CASE WHEN circuit=@c AND time=@t THEN @d+1 ELSE 0 END)) AS _,
							(@i:=(CASE WHEN circuit=@c THEN @i+1 ELSE 1 END))-@d AS rank,
							(@c:=circuit) AS circuit,player,
							(@t:=time) AS t FROM mkrecords
							WHERE class='$cc' AND type='$type' AND best=1 ORDER BY circuit,time
						) r INNER JOIN (
							SELECT circuit,COUNT(*) AS nb FROM mkrecords WHERE class='$cc' AND type='$type' AND best=1 GROUP BY circuit
						) c ON r.circuit=c.circuit
						GROUP BY r.player,r.circuit HAVING(r.player!=0)) t
						GROUP BY player HAVING(score>0) ORDER BY score DESC"
					);
					mysql_query('COMMIT');
				}
				echo "[".(1+$rank).",".mysql_numrows(mysql_query("SELECT * FROM `mkrecords` WHERE class='$cc' AND circuit='$map' AND type='$type' AND best=1"))."]";
			}
		}
	}
	else
		echo -1;
	mysql_close();
}
?>