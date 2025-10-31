<?php
include('../includes/session.php');
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
					? array("x","y","z","speed","speedinc","heightinc","rotation","rotincdir","rotinc","drift","driftinc","driftcpt","size","tourne","tombe","arme","stash","ballons","reserve","champi","etoile","megachampi")
					: array("x","y","z","speed","speedinc","heightinc","rotation","rotincdir","rotinc","drift","driftinc","driftcpt","size","tourne","tombe","arme","stash","tours","demitours","champi","etoile","megachampi","billball","place")
			);
		}
		$playerMapping = $paramsMapping['player'];
		$cpuMapping = $paramsMapping['player'];
		$cpuMapping[] = 'aipoint';
		$extraParamsMapping = array("finaltime");

		include('../includes/initdb.php');
		include('../includes/onlineUtils.php');
		$spectatorId = isset($payload['spectator']) ? intval($payload['spectator']) : 0;
		$course = getCourse(array(
			'spectator' => $spectatorId,
		));
		$lastconnect = isset($payload['lastcon']) ? intval($payload['lastcon']):0;
		if ($course) {
			$fLaps = (isset($payload['laps'])&&is_numeric($payload['laps'])) ? ($payload['laps']+1):4;
			$playerPayloads = array();
			if (isset($payload['player'])) {
				$playerPayloads[$id] = array(
					'param' => $payload['player'],
					'mapping' => $playerMapping,
					'value' => array()
				);
			}
			if (isset($payload['cpu'])) {
				foreach ($payload['cpu'] as $cpuId => $cpuPayload) {
					$cpuId = intval($cpuId);
					$playerPayloads[$cpuId] = array(
						'param' => $cpuPayload,
						'mapping' => $cpuMapping,
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
				if ($payloadData['driftcpt'] > 255) $payloadData['driftcpt'] = 255;
				$playerPayload['data'] = $payloadData;
			}
			unset($playerPayload);
			$mkState = mysql_fetch_array(mysql_query('SELECT time,map,cup FROM `mariokart` WHERE id='. $course));
			if (!$mkState) {
				$mkState = array(
					'map' => 0,
					'cup' => 0,
					'time' => $time
				);
			}
			$finished = ($mkState['map']==-1);
			$timeMs = microtime(true);
			$time = floor($timeMs);
			function timeInFrames($timeMs=null) {
				if (null === $timeMs) $timeMs = microtime(true);
				return round($timeMs*1000/67);
			}
			$lConnect = timeInFrames($timeMs);
			$limIdle = timeInFrames($timeMs-15);
			$limConnect = timeInFrames($timeMs-35);
			$newItems = array();
			if (!$finished) {
				if (isset($payload['item'])) {
					foreach ($payload['item'] as $item) {
						$holder = isset($item['holder']) ? intval($item['holder']):0;
						if (isset($item['id'])) {
							mysql_query('UPDATE items SET data=UNHEX('. $dbh->quote($item['data']) .'),holder="'. $holder .'",updated_at="'. timeInFrames() .'",updated_by="'.$id.'" WHERE id="'. intval($item['id']) .'" AND data!=""');
						}
						else {
							mysql_query('INSERT INTO items SET course="'. $course .'",type="'. intval($item['type']) .'",holder="'. $holder .'",updated_at="'. timeInFrames() .'",updated_by="'.$id.'",data=UNHEX('. $dbh->quote($item['data']) .')');
							$newItems[] = mysql_insert_id();
						}
					}
				}
				$winning = false;
				foreach ($playerPayloads as $playerId=>&$playerPayload) {
					$payloadData = $playerPayload['data'];
					$sql = 'UPDATE `mkplayers` SET ';
					$pWon = mysql_numrows(mysql_query('SELECT * FROM `mkplayers` WHERE id='.$playerId.' AND '. ($isBattle ? 'ballons=0':'tours>='.$fLaps)));
					$pWinning = !$pWon && ($isBattle ? ($payloadData['ballons']==0) : ($payloadData['tours']==$fLaps));
					if ($pWon)
						unset($payloadData['place']);
					foreach ($payloadData as $key => $value) {
						if (null !== $value)
							$sql .= $key .'='.$dbh->quote($value) .',';
					}
					if (isset($payload['extra'][$playerId])) {
						foreach ($payload['extra'][$playerId] as $key => $value) {
							if (in_array($key,$extraParamsMapping))
								$sql .= $key .'='.$dbh->quote($value) .',';
						}
					}
					if ($pWinning) {
						if ($playerId == $id)
							$winning = $pWinning;
						$getPlace = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS place FROM `mkplayers` WHERE course='.$course.' AND '. ($isBattle ? 'ballons!=0':'tours>='.$fLaps)));
						$sql .= 'place='.($getPlace['place']+1-$isBattle).',';
						$sql .= 'finalts='.$time.',';
					}
					$sql .= 'connecte='.$lConnect.' WHERE id="'. $playerId .'"'. (($playerId != $id) ? " AND controller=$id" : ((!$isBattle&&!$mkState['cup']) ? (' AND tours>='.($payloadData['tours']-1)) : ''));
					mysql_query($sql);
				}
				unset($playerPayload);
				if (!rand(0,99)) {
					// Run a GC some times to remove old deleted items
					mysql_query('DELETE FROM items WHERE course="'. $course .'" AND data="" AND updated_at<"'.$limIdle.'"');
				}
				if ($spectatorId)
					mysql_query('UPDATE `mkspectators` SET refresh_date=NOW() WHERE id='. $spectatorId);
				if ($winning && !$isBattle && ($mkState['time'] > $time)) {
					$mkState['time'] = $time;
					mysql_query('UPDATE `mariokart` SET time='.$time.' WHERE id='.$course.' AND time>'.$time);
				}
			}
			$players = mysql_query('SELECT * FROM `mkplayers` WHERE course='.$course.' ORDER BY id');
			echo '[[';
			$courseOptions = mysql_fetch_array(mysql_query('SELECT g.id,g.rules FROM `mkgameoptions` g INNER JOIN `mariokart` m ON m.link=g.id WHERE m.id="'. $course .'"'));
			$courseRules = new stdClass();
			if ($courseOptions)
				$courseRules = json_decode($courseOptions['rules']);
			require_once('../includes/onlineConsts.php');
			$isTeam = !empty($courseRules->team);
			$isTt = !empty($courseRules->timeTrial);
			$nbTeams = isset($courseRules->nbTeams) ? $courseRules->nbTeams : DEFAULT_TEAM_COUNT;
			if ($nbTeams > 2)
				$nbTeams = min($nbTeams, mysql_numrows($players));
			$racing = 0;
			$racingHumans = 0;
			$racingPerTeam = array_fill(0,$nbTeams,0);
			$virgule = false;
			while ($player=mysql_fetch_array($players)) {
				if ($player['team'] == -1)
					$isTeam = false;
				if ($player['connecte'] && ($player['connecte'] < $limIdle) && $player['controller'] && ($player['controller'] != $id) && !rand(0,10)) {
					$q = mysql_query('UPDATE mkplayers SET controller='.$id.',connecte='.$lastconnect.' WHERE id='.$player['id'].' AND controller='.$player['controller']);
					if (mysql_affected_rows()) {
						$player['controller'] = $id;
						$player['connecte'] = $lastconnect;
					}
				}
				$isControlledByMe = isset($playerPayloads[$player['id']]) && (($player['id'] == $id) || ($player['controller'] == $id));
				if (!$isControlledByMe && ($player['connecte'] >= $lastconnect)) {
					$variablePayload = null;
					if ($player['controller']) {
						$payloadMapping = $cpuMapping;
						if (($player['controller'] == $id) || isset($playerPayloads[$player['id']]))
							$variablePayload['controller'] = $player['controller'];
					}
					else
						$payloadMapping = $playerMapping;
					echo ($virgule ? ',':'').'[['.$player['id'].','.$player['connecte'].'],['.$player[$payloadMapping[0]];
					$nbPosts = count($payloadMapping);
					for ($i=1;$i<$nbPosts;$i++) {
						echo ',';
						$val = $player[$payloadMapping[$i]];
						switch ($payloadMapping[$i]) {
						case 'arme':
						case 'stash':
							echo '"'.$val.'"';
							break;
						default:
							echo $val;
						}
					}
					echo ']';
					if ($variablePayload) {
						echo ',';
						echo json_encode($variablePayload);
					}
					echo ']';
					$virgule = true;
				}
				if ($isBattle ? (($player['ballons'] > 0) && ($player['connecte'] > $limConnect)) : ($player['tours'] < $fLaps)) {
					if ($isTeam && $isBattle)
						$racingPerTeam[$player['team']]++;
					$racing++;
					if (!$player['controller'])
						$racingHumans++;
				}
				elseif ($isTt && ($player['finalts'] >= ($time-2)))
					$racing++;
			}
			if ($isTeam && $isBattle) {
				$racing = 0;
				foreach ($racingPerTeam as $iRacing)
					$racing += ($iRacing>0);
			}
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
			if (($racing < 2) || !$racingHumans || (!$isBattle&&($mkState['time'] <= ($time-35)))) {
				$finishing = !$finished;
				if ($finishing) {
					$mkState['time'] = $time+35;
					mysql_query('UPDATE `mkspectators` s INNER JOIN `mkjoueurs` j ON s.player=j.id AND j.course=0 SET j.course='. $course .' WHERE s.course='. $course .' AND s.state="queuing" AND s.refresh_date >= NOW()-INTERVAL 5 SECOND');
					mysql_query('UPDATE `mkspectators` SET state="pending" WHERE course='. $course .' AND state="joined"');
					mysql_query('UPDATE `mariokart` SET map=-1,time='.$mkState['time'].' WHERE id='. $course);
					$finished = true;
				}
			}
			if ($finished) {
				$nbScores = mysql_numrows($players);
				$isFriendly = !empty($courseRules->friendly);
				$isLocal = $isFriendly && !empty($courseRules->localScore);
				if ($finishing) {
					$nbPlaces = $nbScores;
					if ($isBattle) {
						$players = mysql_query('SELECT id FROM `mkplayers` WHERE course='.$course.' ORDER BY (connecte AND connecte<='.$limConnect .') DESC,(CASE WHEN ballons>0 THEN ballons+reserve ELSE -place END),connecte');
						while ($player = mysql_fetch_array($players)) {
							mysql_query('UPDATE `mkplayers` SET place='.$nbPlaces.' WHERE id='. $player['id']);
							$nbPlaces--;
						}
					}
					else {
						$players = mysql_query('SELECT id FROM `mkplayers` WHERE course='.$course.($isTt ? '':" AND tours<$fLaps").' ORDER BY '.($isTt ? '(finaltime>0),finaltime DESC,':'').'tours,demitours,place DESC,connecte');
						while ($player = mysql_fetch_array($players)) {
							mysql_query('UPDATE `mkplayers` SET place='.$nbPlaces.' WHERE id='. $player['id']);
							$nbPlaces--;
						}
					}
					mysql_query('UPDATE `mkplayers` SET connecte=0 WHERE course='. $course);
					mysql_query('UPDATE `mkjoueurs` SET choice_map=0 WHERE course='. $course);
					mysql_query('DELETE FROM `items` WHERE course='.$course.' AND (data!="" OR updated_at<"'.$lConnect.'")');
					if ($isLocal) {
						require_once('../includes/onlineStateUtils.php');
						incCourseState($courseOptions['id']);
					}
				}
				if ($spectatorId)
					mysql_query('UPDATE `mkspectators` SET state="joined" WHERE id="'. $spectatorId .'" AND state="pending"');
				function getScoreInc($i,$score,$nbScores,$total) {
					$coeff = (($nbScores-$i-1)/($nbScores-1))-($score/$total);
					$coeff *= pow(2,$coeff);
					return $coeff*(($coeff<0)?$score:max(20000-$score,5000))/80;
				}
				$players = mysql_query('SELECT p.id,j.nom,p.aPts,p.team,p.controller AS cpu,p.finaltime FROM `mkplayers` p LEFT JOIN `mkjoueurs` j ON p.id=j.id WHERE p.course='.$course.' ORDER BY p.place');
				$playersData = array();
				$allPlayersData = array();
				while ($player=mysql_fetch_array($players)) {
					$allPlayersData[] = $player;
					if (!$player['cpu'] || $isLocal)
						$playersData[] = $player;
				}
				$nbScores = count($playersData);
				if (!$isFriendly) {
					$total = 0;
					if ($isTeam) {
						$totalPerTeam = array_fill(0,$nbTeams,0);
						$xpPerTeam = array_fill(0,$nbTeams,1);
						$nbScoresPerTeam = array_fill(0,$nbTeams,0);
						foreach ($playersData as $player) {
							$team = $player['team'];
							$totalPerTeam[$team] += $player['aPts'];
							$xpPerTeam[$team] *= $player['aPts'];
							$nbScoresPerTeam[$team]++;
							$total += $player['aPts'];
						}
						$ptsPerTeam = array_fill(0,$nbTeams,0);
						if (min($nbScoresPerTeam)) {
							$avgPerTeam = array();
							foreach ($xpPerTeam as $i=>$xp)
								$avgPerTeam[$i] = pow($xp,1/$nbScoresPerTeam[$i]);
							foreach ($playersData as $i=>$player) {
								$team = $player['team'];
								$ptsPerTeam[$team] += getScoreInc($i,$avgPerTeam[$team],$nbScores,$total);
							}
						}
						else
							$avgPerTeam = array_fill(0,$nbTeams,0);
						$ptsProrata = array();
						$ptsProrataTotal = array_fill(0,$nbTeams,0);
						$totalAvgTeams = array_sum($avgPerTeam);
						foreach ($playersData as $i=>$player) {
							$team = $player['team'];
							$ranking = ($ptsPerTeam[$team] > 0) ? 0:1;
							$totalOtherTeams = $totalAvgTeams - $avgPerTeam[$team];
							$iProrata = getScoreInc($ranking,$player['aPts'],$nbTeams,$player['aPts']+$totalOtherTeams);
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
						foreach ($playersData as $player)
							$total += $player['aPts'];
					}
				}
				echo ',[';
				$pts_ = 'pts_'.($isBattle ? 'battle':'vs');
				$i = 0;
				$cpuIds = array();
				foreach ($allPlayersData as $player) {
					if ($player['cpu'])
						$cpuIds[] = intval($player['id']);
				}
				sort($cpuIds);
				$cpuRankById = array_flip($cpuIds);
				include('../includes/onlineRulesUtils.php');
				foreach ($allPlayersData as $v=>$player) {
					if ($player['cpu'])
						$playerName = getCpuName($cpuRankById[$player['id']], $courseRules);
					else
						$playerName = $player['nom'];
					if (!$player['cpu'] || $isLocal) {
						$score = $player['aPts'];
						if ($isFriendly) {
							if ($isLocal) {
								if (empty($courseRules->ptDistrib->value)) {
									$maxPts = round($nbScores*1.25);
									$xPts = ($nbScores-$i-1)/($nbScores-1);
									$inc = round($maxPts*(exp($xPts)-1)/(M_E-1));
									if ($nbScores == 12) {
										// hardcoded scores to fit wii point system
										$incs = array(15,12,10,8,7,6,5,4,3,2,1,0);
										$inc = $incs[$i];
									}
								}
								else {
									if (isset($courseRules->ptDistrib->value[$i]))
										$inc = $courseRules->ptDistrib->value[$i];
									else
										$inc = 0;
								}
							}
							else
								$inc = 0;
						}
						elseif ($isTeam) {
							$team = $player['team'];
							$inc = $ptsPerTeam[$team]*$ptsProrata[$i]/$ptsProrataTotal[$team];
						}
						elseif ($total)
							$inc = getScoreInc($i,$score,$nbScores,$total);
						else
							$inc = 0;
						$inc = round($inc);
						$i++;
					}
					else
						$inc = 0;
					echo ($v ? ',':'') .'['.$player['id'].','.json_encode($playerName).','.$player['aPts'].','.$inc.','.$player['team'].','.$player['finaltime'].']';
					$nPts = $player['aPts']+$inc;
					if ($finishing) {
						$shouldLog = $isFriendly && !$player['cpu'];
						if (($nPts != $player['aPts']) || $isLocal) {
							if ($isLocal)
								mysql_query('INSERT INTO `mkgamerank` SET game='. $courseOptions['id'] .',player='. $player['id'] .',pts='.$nPts.' ON DUPLICATE KEY UPDATE pts=VALUES(pts)');
							elseif (!$player['cpu']) {
								$q = mysql_query('UPDATE `mkjoueurs` SET '.$pts_.'='.$nPts.' WHERE id='.$player['id'].' AND '.$pts_.'='.$player['aPts']);
								$shouldLog = mysql_affected_rows();
							}
							else
								$shouldLog = false;
						}
						if ($shouldLog)
							mysql_query('INSERT INTO `mkmatches` VALUES(NULL, '. $player['id'] .','. $course .','. $i .',NULL)');
					}
				}
				echo '],'.($mkState['time']-$time);
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