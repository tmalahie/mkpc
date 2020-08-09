<?php
session_start();
$id = $_SESSION['mkid'];
if ($id) {
	$payload = json_decode(file_get_contents('php://input'),true);
	if ($payload) {
		$isBattle = isset($payload['battle']);
		switch ($payload['v']) {
		default:
			$paramsMapping = array(
				'keys' => array(
					'player' => 'p',
					'items' => 'i'
				),
				'player' => $isBattle
					? array("x","y","z","speed","speedinc","heightinc","rotation","rotincdir","rotinc","size","tourne","tombe","ballons","reserve","champi","etoile","megachampi")
					: array("x","y","z","speed","speedinc","heightinc","rotation","rotincdir","rotinc","size","tourne","tombe","tours","demitours","champi","etoile","megachampi","billball","eclair","place")
			);
		}
		define('KEY_PLAYER', $paramsMapping['keys']['player']);
		define('KEY_ITEMS', $paramsMapping['keys']['items']);
		$playerMapping = $paramsMapping['player'];

		include('initdb.php');
		$getCourse = mysql_fetch_array(mysql_query('SELECT course FROM `mkplayers` WHERE id="'.$id.'"'));
		$course = $getCourse['course'];
		$lastconnect = isset($payload['lastcon']) ? $payload['lastcon']:0;
		if ($course) {
			$fLaps = (isset($payload['laps'])&&is_numeric($payload['laps'])) ? ($payload['laps']+1):4;
			if (isset($payload['tours']) && ($payload['tours'])>$fLaps) $payload['tours'] = $fLaps;
			$finished = mysql_fetch_array(mysql_query('SELECT id FROM `mariokart` WHERE id='. $course .' AND map=-1 LIMIT 1'));
			$timeMs = microtime(true);
			$time = floor($timeMs);
			function timeInFrames($timeMs=null) {
				if (null === $timeMs) $timeMs = microtime(true);
				return round($timeMs*1000/67);
			}
			$lConnect = timeInFrames($timeMs);
			$limConnect = timeInFrames(($timeMs-35));
			if (!$finished) {
				$newItems = array();
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
				if (isset($payload['player'])) {
					$sql = 'UPDATE `mkplayers` SET ';
					foreach ($playerMapping as $i => $key)
						$sql .= $key .'="'.$payload['player'][$i] .'",';
					$sql .= 'arme=-1,iUse=-1';
					$winning = $isBattle ? (($payload['ballons']==0) && !mysql_numrows(mysql_query('SELECT * FROM `mkplayers` WHERE id='.$id.' AND ballons=0'))) : (($payload['tours']==$fLaps) && !mysql_numrows(mysql_query('SELECT * FROM `mkplayers` WHERE id='.$id.' AND tours>='.$fLaps)));
					if ($winning)
						$sql .= ',place='.(mysql_numrows(mysql_query('SELECT * FROM `mkplayers` WHERE course='.$course.' AND '. ($isBattle ? 'ballons!=0':'tours>='.$fLaps)))+1-$isBattle);
					$sql .= ',connecte='.$lConnect.' WHERE id="'. $id .'"';
					mysql_query($sql);
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
			$racingPerTeam = array(0,0);
			$virgule = false;
			while ($joueur=mysql_fetch_array($joueurs)) {
				if ($joueur['team'] == -1)
					$isTeam = false;
				if (($joueur['id'] != $id) && ($joueur['connecte'] >= $lastconnect)) {
					echo ($virgule ? ',':'').'[['.$joueur['id'].','.$joueur['connecte'].','.$joueur['arme'].','.$joueur['iUse'].'],['.$joueur[$playerMapping[0]];
					$nbPosts = count($playerMapping);
					for ($i=1;$i<$nbPosts;$i++)
						echo ','. $joueur[$playerMapping[$i]];
					echo ']]';
					$virgule = true;
				}
				if ($isBattle ? (($joueur['ballons'] > 0) && ($joueur['connecte'] > $limConnect)) : ($joueur['tours'] < $fLaps)) {
					if ($isTeam && $isBattle)
						$racingPerTeam[$joueur['team']]++;
					$racing++;
				}
			}
			if ($isTeam && $isBattle)
				$racing = ($racingPerTeam[0]>0)+($racingPerTeam[1]>0);
			echo '],[';
			echo json_encode($newItems);
			echo ',';
			$getUpdatedItems = mysql_query('SELECT id,type,holder,HEX(data) AS data FROM items WHERE course="'. $course .'" AND updated_at>="'. $lastconnect .'" AND updated_by!="'. $id .'"');
			$updatedItems = array();
			while ($updatedItem = mysql_fetch_array($getUpdatedItems)) {
				$updatedItems[] = array(
					$updatedItem['id'],
					$updatedItem['type'],
					$updatedItem['holder'],
					$updatedItem['data']
				);
			}
			echo json_encode($updatedItems);
			echo '],'.$lConnect;
			$finishing = false;
			if (($racing < 2) || (!$isBattle&&mysql_numrows(mysql_query('SELECT time FROM `mariokart` WHERE id='. $course .' AND time<='.($time-35))))) {
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
					mysql_query('UPDATE `mkjoueurs` j LEFT JOIN `mkplayers` p ON j.id=p.id SET j.choice_map=0,p.connecte=0 WHERE j.course='. $course);
					mysql_query('DELETE FROM `items` WHERE course='.$course);
				}
				function getScoreInc($i,$score,$nbScores,$total) {
					$coeff = (($nbScores-$i-1)/($nbScores-1))-($score/$total);
					$coeff *= pow(2,$coeff);
					return $coeff*(($coeff<0)?$score:max(20000-$score,5000))/80;
				}
				$isFriendly = !empty($courseRules->friendly);
				$isLocal = $isFriendly && !empty($courseRules->localScore);
				$joueurs = mysql_query('SELECT p.id,j.nom,p.aPts,p.team FROM `mkplayers` p INNER JOIN `mkjoueurs` j ON p.id=j.id WHERE p.course='.$course.' ORDER BY p.place');
				$joueursData = array();
				while ($joueur=mysql_fetch_array($joueurs))
					$joueursData[] = $joueur;
				$nbScores = count($joueursData);
				if (!$isFriendly) {
					$total = 0;
					if ($isTeam) {
						$totalPerTeam = array(0,0);
						$xpPerTeam = array(1,1);
						$nbScoresPerTeam = array(0,0);
						foreach ($joueursData as $joueur) {
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
						foreach ($joueursData as $i=>$joueur) {
							$team = $joueur['team'];
							$ptsPerTeam[$team] += getScoreInc($i,$avgPerTeam[$team],$nbScores,$total);
						}
						$ptsProrata = array();
						$ptsProrataTotal = array(0,0);
						foreach ($joueursData as $i=>$joueur) {
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
						foreach ($joueursData as $joueur)
							$total += $joueur['aPts'];
					}
				}
				echo ',[';
				$i = 0;
				$pts_ = 'pts_'.($isBattle ? 'battle':'vs');
				foreach ($joueursData as $i=>$joueur) {
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
					echo ($i ? ',':'') .'['.$joueur['id'].',"'.$joueur['nom'].'",'.$joueur['aPts'].','.$inc.','.$joueur['team'].']';
					$nPts = $joueur['aPts']+$inc;
					if ($finishing) {
						$shouldLog = $isFriendly;
						if (($nPts != $joueur['aPts']) || $isLocal) {
							if ($isLocal)
								mysql_query('INSERT INTO `mkgamerank` SET game='. $courseOptions['id'] .',player='. $joueur['id'] .',pts='.$nPts.' ON DUPLICATE KEY UPDATE pts=VALUES(pts)');
							else {
								$q = mysql_query('UPDATE `mkjoueurs` SET '.$pts_.'='.$nPts.' WHERE id='.$joueur['id'].' AND '.$pts_.'='.$joueur['aPts']);
								$shouldLog = mysql_affected_rows($q);
							}
						}
						if ($shouldLog)
							mysql_query('INSERT INTO `mkmatches` VALUES(NULL, '. $joueur['id'] .','. $course .','. $i .',NULL)');
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