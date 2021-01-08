<?php
session_start();
$id = $_SESSION['mkid'];
if ($id) {
	$payload = json_decode(file_get_contents('php://input'),true);
	if ($payload) {
		$isBattle = isset($payload['battle']);
		if (!isset($payload['v'])) $payload['v'] = 0;
		switch ($payload['v']) {
		default:
			$paramsMapping = array(
				'keys' => array(
					'player' => 'p',
					'items' => 'i'
				),
				'player' => $isBattle
					? array("x","y","z","speed","speedinc","heightinc","rotation","rotincdir","rotinc","size","tourne","tombe","arme","ballons","reserve","champi","etoile","megachampi")
					: array("x","y","z","speed","speedinc","heightinc","rotation","rotincdir","rotinc","size","tourne","tombe","arme","tours","demitours","champi","etoile","megachampi","billball","place")
			);
		}
		$playerMapping = $paramsMapping['player'];
		$cpuMapping = $paramsMapping['player'];
		$cpuMapping[] = 'aipoint';

		include('initdb.php');
		$getCourse = mysql_fetch_array(mysql_query('SELECT course FROM `mkplayers` WHERE id="'.$id.'"'));
		$course = $getCourse['course'];
		$lastconnect = isset($payload['lastcon']) ? $payload['lastcon']:0;
		if ($course) {
			$fLaps = (isset($payload['laps'])&&is_numeric($payload['laps'])) ? ($payload['laps']+1):4;
			$playerPayloads = array();
			if (isset($payload['player'])) {
				$playerPayloads[] = array(
					'param' => $payload['player'],
					'mapping' => $playerMapping,
					'id' => $id,
					'value' => array()
				);
			}
			if (isset($payload['cpu'])) {
				foreach ($payload['cpu'] as $cpuId => $cpuPayload) {
					$playerPayloads[] = array(
						'param' => $cpuPayload,
						'mapping' => $cpuMapping,
						'id' => $cpuId,
						'value' => array()
					);
				}
			}
			foreach ($playerPayloads as &$playerPayload) {
				$payloadData = array();
				$payloadParam = $playerPayload['param'];
				$payloadMapping = $playerPayload['mapping'];
				foreach ($payloadMapping as $key)
					$payloadData[$key] = null;
				foreach ($payloadMapping as $i => $key)
					$payloadData[$key] = isset($payloadParam[$i]) ? $payloadParam[$i]:null;
				if (!$isBattle)
					if ($payloadData['tours'] > $fLaps) $payloadData['tours'] = $fLaps;
				$playerPayload['data'] = $payloadData;
			}
			unset($playerPayload);
			$finished = mysql_fetch_array(mysql_query('SELECT id FROM `mariokart` WHERE id='. $course .' AND map=-1 LIMIT 1'));
			$timeMs = microtime(true);
			$time = floor($timeMs);
			function timeInFrames($timeMs=null) {
				if (null === $timeMs) $timeMs = microtime(true);
				return round($timeMs*1000/67);
			}
			$lConnect = timeInFrames($timeMs);
			$limConnect = timeInFrames($timeMs-35);
			$newItems = array();
			if (!$finished) {
				if (isset($payload['item'])) {
					foreach ($payload['item'] as $item) {
						$holder = isset($item['holder']) ? $id:0;
						if (isset($item['id'])) {
							mysql_query('UPDATE items SET data=UNHEX("'. $item['data'] .'"),holder="'. $holder .'",updated_at="'. timeInFrames() .'",updated_by="'.$id.'" WHERE id="'. $item['id'] .'" AND data!=""');
						}
						else {
							mysql_query('INSERT INTO items SET course="'. $course .'",type="'. $item['type'] .'",holder="'. $holder .'",updated_at="'. timeInFrames() .'",updated_by="'.$id.'",data=UNHEX("'. $item['data'] .'")');
							$newItems[] = mysql_insert_id();
						}
					}
				}
				$winning = false;
				foreach ($playerPayloads as &$playerPayload) {
					$payloadData = $playerPayload['data'];
					$sql = 'UPDATE `mkplayers` SET ';
					foreach ($payloadData as $key => $value) {
						if (null !== $value)
							$sql .= $key .'="'.$value .'",';
					}
					$pWinning = $isBattle ? (($payloadData['ballons']==0) && !mysql_numrows(mysql_query('SELECT * FROM `mkplayers` WHERE id='.$id.' AND ballons=0'))) : (($payloadData['tours']==$fLaps) && !mysql_numrows(mysql_query('SELECT * FROM `mkplayers` WHERE id='.$id.' AND tours>='.$fLaps)));
					if ($pWinning) {
						if ($playerPayload['id'] == $id)
							$winning = $pWinning;
						$sql .= 'place='.(mysql_numrows(mysql_query('SELECT * FROM `mkplayers` WHERE course='.$course.' AND '. ($isBattle ? 'ballons!=0':'tours>='.$fLaps)))+1-$isBattle).',';
					}
					$sql .= 'connecte='.$lConnect.' WHERE id="'. $playerPayload['id'] .'"';
					mysql_query($sql);
				}
				if (!rand(0,99)) {
					// Run a GC some times to remove old deleted items
					mysql_query('DELETE FROM items WHERE course="'. $course .'" AND data="" AND updated_at<"'.$limConnect.'"');
				}
				if ($winning && !$isBattle)
					mysql_query('UPDATE `mariokart` SET time='.$time.' WHERE id='.$course.' AND time>'.$time);
			}
			$joueurs = mysql_query('SELECT * FROM `mkplayers` WHERE course='.$course.' ORDER BY id');
			echo '[[';
			$courseOptions = mysql_fetch_array(mysql_query('SELECT g.id,g.rules FROM `mkgameoptions` g INNER JOIN `mariokart` m ON m.link=g.id WHERE m.id="'. $course .'"'));
			$courseRules = new stdClass();
			if ($courseOptions)
				$courseRules = json_decode($courseOptions['rules']);
			$isTeam = !empty($courseRules->team);
			$racing = 0;
			$racingHumans = 0;
			$racingPerTeam = array(0,0);
			$virgule = false;
			while ($joueur=mysql_fetch_array($joueurs)) {
				$payloadMapping = $joueur['controller'] ? $cpuMapping : $playerMapping;
				if ($joueur['team'] == -1)
					$isTeam = false;
				if (($joueur['id'] != $id) && ($joueur['controller'] != $id) && ($joueur['connecte'] >= $lastconnect)) {
					echo ($virgule ? ',':'').'[['.$joueur['id'].','.$joueur['connecte'].'],['.$joueur[$payloadMapping[0]];
					$nbPosts = count($payloadMapping);
					for ($i=1;$i<$nbPosts;$i++) {
						echo ',';
						$val = $joueur[$payloadMapping[$i]];
						switch ($payloadMapping[$i]) {
						case 'arme':
							echo '"'.$val.'"';
							break;
						default:
							echo $val;
						}
					}
					echo ']]';
					$virgule = true;
				}
				if ($isBattle ? (($joueur['ballons'] > 0) && ($joueur['connecte'] > $limConnect)) : ($joueur['tours'] < $fLaps)) {
					if ($isTeam && $isBattle)
						$racingPerTeam[$joueur['team']]++;
					$racing++;
					if (!$joueur['controller'])
						$racingHumans++;
				}
			}
			if ($isTeam && $isBattle)
				$racing = ($racingPerTeam[0]>0)+($racingPerTeam[1]>0);
			echo '],[';
			echo json_encode($newItems);
			echo ',';
			$getUpdatedItems = mysql_query('SELECT id,type,holder,HEX(data) AS data,updated_at FROM items WHERE course="'. $course .'" AND updated_at>="'. $lastconnect .'" AND updated_by!="'. $id .'" ORDER BY id');
			$updatedItems = array();
			while ($updatedItem = mysql_fetch_array($getUpdatedItems)) {
				$updatedItems[] = array(
					$updatedItem['id'],
					$updatedItem['type'],
					$updatedItem['holder'],
					$updatedItem['updated_at'],
					$updatedItem['data']
				);
			}
			echo json_encode($updatedItems);
			echo '],'.$lConnect;
			$finishing = false;
			if (($racing < 2) || !$racingHumans || (!$isBattle&&mysql_numrows(mysql_query('SELECT time FROM `mariokart` WHERE id='. $course .' AND time<='.($time-35))))) {
				$finishing = !$finished;
				if ($finishing) {
					mysql_query('UPDATE `mariokart` SET map=-1,time='.($time+35).' WHERE id='. $course);
					$finished = true;
				}
			}
			if ($finished) {
				$nbScores = mysql_numrows($joueurs);
				if ($finishing) {
					$nbPlaces = $nbScores;
					if ($isBattle) {
						$joueurs = mysql_query('SELECT id FROM `mkplayers` WHERE course='.$course.' ORDER BY (connecte AND connecte<='.$limConnect .') DESC,(CASE WHEN ballons>0 THEN ballons+reserve ELSE -place END),connecte');
						while ($joueur = mysql_fetch_array($joueurs)) {
							mysql_query('UPDATE `mkplayers` SET place='.$nbPlaces.' WHERE id='. $joueur['id']);
							$nbPlaces--;
						}
					}
					else {
						$joueurs = mysql_query('SELECT id FROM `mkplayers` WHERE course='.$course.' AND tours<'.$fLaps.' ORDER BY tours,demitours,place DESC,connecte');
						while ($joueur = mysql_fetch_array($joueurs)) {
							mysql_query('UPDATE `mkplayers` SET place='.$nbPlaces.' WHERE id='. $joueur['id']);
							$nbPlaces--;
						}
					}
					mysql_query('UPDATE `mkplayers` SET connecte=0 WHERE course='. $course);
					mysql_query('UPDATE `mkjoueurs` SET choice_map=0 WHERE course='. $course);
					mysql_query('DELETE FROM `items` WHERE course='.$course.' AND (data!="" OR updated_at<"'.$lConnect.'")');
				}
				function getScoreInc($i,$score,$nbScores,$total) {
					$coeff = (($nbScores-$i-1)/($nbScores-1))-($score/$total);
					$coeff *= pow(2,$coeff);
					return $coeff*(($coeff<0)?$score:max(20000-$score,5000))/80;
				}
				$isFriendly = !empty($courseRules->friendly);
				$isLocal = $isFriendly && !empty($courseRules->localScore);
				$joueurs = mysql_query('SELECT p.id,j.nom,p.aPts,p.team,p.controller AS cpu FROM `mkplayers` p LEFT JOIN `mkjoueurs` j ON p.id=j.id WHERE p.course='.$course.' ORDER BY p.place');
				$playersData = array();
				$allPlayersData = array();
				while ($joueur=mysql_fetch_array($joueurs)) {
					$allPlayersData[] = $joueur;
					if (!$joueur['cpu'] || $isLocal)
						$playersData[] = $joueur;
				}
				$nbScores = count($playersData);
				if (!$isFriendly) {
					$total = 0;
					if ($isTeam) {
						$totalPerTeam = array(0,0);
						$xpPerTeam = array(1,1);
						$nbScoresPerTeam = array(0,0);
						foreach ($playersData as $joueur) {
							$team = $joueur['team'];
							$totalPerTeam[$team] += $joueur['aPts'];
							$xpPerTeam[$team] *= $joueur['aPts'];
							$nbScoresPerTeam[$team]++;
							$total += $joueur['aPts'];
						}
						$avgPerTeam = array(
							pow($xpPerTeam[0],1/$nbScoresPerTeam[0]),
							pow($xpPerTeam[1],1/$nbScoresPerTeam[1])
						);
						$ptsPerTeam = array(0,0);
						foreach ($playersData as $i=>$joueur) {
							$team = $joueur['team'];
							$ptsPerTeam[$team] += getScoreInc($i,$avgPerTeam[$team],$nbScores,$total);
						}
						$ptsProrata = array();
						$ptsProrataTotal = array(0,0);
						foreach ($playersData as $i=>$joueur) {
							$team = $joueur['team'];
							$ranking = ($ptsPerTeam[$team] > 0) ? 0:1;
							$iProrata = getScoreInc($ranking,$joueur['aPts'],2,$joueur['aPts']+$avgPerTeam[1-$team]);
							if ($ranking)
								$iProrata = min($iProrata,-1);
							else
								$iProrata = max($iProrata,1);
							$ptsProrata[$i] = $iProrata;
							$ptsProrataTotal[$team] += $iProrata;
						}
					}
					else {
						$total = 0;
						foreach ($playersData as $joueur)
							$total += $joueur['aPts'];
					}
				}
				echo ',[';
				$pts_ = 'pts_'.($isBattle ? 'battle':'vs');
				$i = 0;
				$cpuIds = array();
				foreach ($allPlayersData as $joueur) {
					if ($joueur['cpu'])
						$cpuIds[] = +$joueur['id'];
				}
				sort($cpuIds);
				$cpuRankById = array_flip($cpuIds);
				foreach ($allPlayersData as $v=>$joueur) {
					if ($joueur['cpu'])
						$playerName = 'CPU ' . ($cpuRankById[$joueur['id']]+1);
					if (!$joueur['cpu'] || $isLocal) {
						$playerName = $joueur['nom'];
						$score = $joueur['aPts'];
						if ($isFriendly) {
							if ($isLocal) {
								$maxPts = round($nbScores*1.25);
								$xPts = ($nbScores-$i-1)/($nbScores-1);
								$inc = round($maxPts*(exp($xPts)-1)/(M_E-1));
								if ($nbScores == 12) {
									// hardcoded scores to fit wii point system
									$incs = array(15,12,10,8,7,6,5,4,3,2,1,0);
									$inc = $incs[$i];
								}
							}
							else
								$inc = 0;
						}
						elseif ($isTeam) {
							$team = $joueur['team'];
							$inc = $ptsPerTeam[$team]*$ptsProrata[$i]/$ptsProrataTotal[$team];
						}
						else
							$inc = getScoreInc($i,$score,$nbScores,$total);
						$inc = round($inc);
						$i++;
					}
					else
						$inc = 0;
					echo ($v ? ',':'') .'['.$joueur['id'].',"'.$playerName.'",'.$joueur['aPts'].','.$inc.','.$joueur['team'].']';
					$nPts = $joueur['aPts']+$inc;
					if ($finishing) {
						$shouldLog = $isFriendly;
						if (($nPts != $joueur['aPts']) || $isLocal) {
							if ($isLocal)
								mysql_query('INSERT INTO `mkgamerank` SET game='. $courseOptions['id'] .',player='. $joueur['id'] .',pts='.$nPts.' ON DUPLICATE KEY UPDATE pts=VALUES(pts)');
							elseif (!$joueur['cpu']) {
								$q = mysql_query('UPDATE `mkjoueurs` SET '.$pts_.'='.$nPts.' WHERE id='.$joueur['id'].' AND '.$pts_.'='.$joueur['aPts']);
								$shouldLog = mysql_affected_rows();
							}
							else
								$shouldLog = false;
						}
						if ($shouldLog)
							mysql_query('INSERT INTO `mkmatches` VALUES(NULL, '. $joueur['id'] .','. $course .','. ($i+1) .',NULL)');
					}
				}
				$getTime = mysql_fetch_array(mysql_query('SELECT time FROM `mariokart` WHERE id='.$course));
				echo '],'.($getTime['time']-$time);
			}
			echo ']';
		}
		else
			echo -1;
		mysql_close();
	}
}
else
	echo -1;
?>