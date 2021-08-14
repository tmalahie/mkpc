<?php
$getCourse = mysql_fetch_array(mysql_query('SELECT course,banned FROM `mkjoueurs` WHERE id="'.$id.'"'));
$course = $getCourse['course'];
if ($course && !$getCourse['banned']) {
	$isBattle = isset($_POST['battle']);
	$pts_ = 'pts_'.($isBattle ? 'battle':'vs');
	if (mysql_numrows(mysql_query('SELECT * FROM `mariokart` WHERE id='.$course.' AND time<='. time())))
		mysql_query('UPDATE `mkjoueurs` SET course=0 WHERE course='.$course.' AND choice_map=0');
	function getMapData() {
		global $course;
		$res = mysql_fetch_array(mysql_query('SELECT m.map,m.time,m.link,o.rules FROM `mariokart` m LEFT JOIN `mkgameoptions` o ON m.link=o.id WHERE m.id='. $course));
		if (!$res['rules'])
			$res['rules'] = '{}';
		return $res;
	}
	$getMap = getMapData();
	$map = $getMap['map'];
	$time = $getMap['time'];
	$continuer = ($map == -1);
	$allChosen = true;
	$joueurs = mysql_query('SELECT choice_map FROM `mkjoueurs` WHERE course='. $course .' ORDER BY id');
	for ($i=0;$joueur=mysql_fetch_array($joueurs);$i++) {
		if (!$joueur['choice_map']) {
			$continuer = false;
			$allChosen = false;
			break;
		}
	}
	$now = round(microtime(true)*1000);
	$courseRules = json_decode($getMap['rules']);
	if ($continuer) {
		$map = rand(0, mysql_numrows($joueurs)-1);
		$time = $now+5000;
		if (!empty($courseRules->manualTeams))
			$time += 12000;
		mysql_query('UPDATE `mariokart` SET map='. $map .', time='.$time.' WHERE id='. $course);
		$isLocal = !empty($courseRules->friendly) && !empty($courseRules->localScore);
		$joueurs = mysql_query('SELECT j.id,'.($isLocal ? 'IFNULL(r.pts,0) AS pts':'j.'.$pts_.' AS pts').' FROM `mkjoueurs` j LEFT JOIN `mkplayers` p ON j.id=p.id'. ($isLocal ? ' LEFT JOIN `mkgamerank` r ON r.game='.$getMap['link'].' AND j.id=r.player':'') .' WHERE j.course='. $course .' ORDER BY p.place,j.id');
		$nConnect = round($time/67);
		$playerIds = array();
		$toUpdate0 = 'connecte='.$nConnect.',tours=1,ballons=1,reserve=4,aipoint=0,finaltime=0,finalts=0';
		for ($i=0;$joueur=mysql_fetch_array($joueurs);$i++) {
			$playerIds[] = $joueur['id'];
			$toUpate = 'course='.$course.',controller=0,aPts='. $joueur['pts'] .','.$toUpdate0;
			mysql_query('INSERT INTO `mkplayers` SET id='. $joueur['id'].','.$toUpate.',place=0 ON DUPLICATE KEY UPDATE '.$toUpate);
		}
		mysql_query('DELETE p FROM `mkplayers` p INNER JOIN `mkjoueurs` j ON p.id=j.id WHERE p.course='.$course.' AND j.course!='.$course);
		$nbPlayers = $i;
		if (isset($courseRules->cpuCount) && ($nbPlayers > 1) && ($nbPlayers < $courseRules->cpuCount)) {
			$cpuIds = array();
			$maxiter = 10;
			while (!($minAvailableId = mysql_fetch_array(mysql_query('SELECT min_id FROM `mkgamecpu` WHERE course='. $course)))) {
				mysql_query(
					"INSERT IGNORE INTO `mkgamecpu` (
						SELECT $course AS course,
						max_id AS new_min_id,
						max_id+10 AS new_max_id
						FROM `mkgamecpu`
						ORDER BY min_id DESC LIMIT 1
					)"
				);
				$maxiter--;
				if (!$maxiter) {
					echo -1;
					mysql_close();
					exit;
				}
			}
			$cpuId = $minAvailableId['min_id'];
			do {
				$toUpate = 'course='.$course.',controller='.$playerIds[($i-$nbPlayers)%$nbPlayers].','.$toUpdate0;
				mysql_query('INSERT INTO `mkplayers` SET id='.$cpuId.','.$toUpate.',aPts='.($isLocal ? 0:5000).',place=-1 ON DUPLICATE KEY UPDATE '.$toUpate);
				$cpuIds[] = $cpuId;
				$cpuId++;
				$i++;
			} while ($i < $courseRules->cpuCount);
			$cpuIdsString = implode(',', $cpuIds);
			mysql_query('UPDATE `mkplayers` p LEFT JOIN `mkjoueurs` j ON p.id=j.id SET p.course=(CASE WHEN p.id IN ('. $cpuIdsString .') THEN '. $course .' ELSE 0 END) WHERE p.id IN ('. $cpuIdsString .') OR (p.course='.$course.' AND j.id IS NULL)');
			if ($isLocal)
				mysql_query('UPDATE `mkplayers` p LEFT JOIN `mkgamerank` r ON r.game='.$getMap['link'].' AND p.id=r.player SET p.aPts=IFNULL(r.pts,0) WHERE p.course='.$course.' AND p.controller!=0');
		}
		else
			mysql_query('DELETE FROM `mkplayers` WHERE course='. $course .' AND controller!=0');
		mysql_query('SET @place=0');
		mysql_query(
			'UPDATE mkplayers p INNER JOIN
			(SELECT id,(@place:=@place+1) AS nplace FROM mkplayers WHERE course='.$course.' ORDER BY place,id) t
			ON t.id=p.id SET p.place=t.nplace'
		);
	}
	function listPlayers() {
		global $course, $pts_;
		$joueurs = mysql_query(
			'(SELECT j.id,j.'.$pts_.' AS pts,j.joueur,IFNULL(p.place,1) AS place,IFNULL(p.team,-1) AS team,j.choice_map,j.choice_rand,j.nom,0 AS controller FROM `mkjoueurs` j LEFT JOIN `mkplayers` p ON j.id=p.id WHERE j.course='. $course .')
			UNION
			(SELECT id,5000 AS pts,NULL AS joueur,IFNULL(place,1) AS place,IFNULL(team,-1) AS team,1 AS choice_map,1 AS choice_rand,NULL AS nom,controller FROM `mkplayers` WHERE course='. $course .' AND controller!=0)
			ORDER BY id'
		);
		$joueursData = array();
		while ($joueur=mysql_fetch_array($joueurs))
			$joueursData[] = $joueur;
		return $joueursData;
	}
	$joueursData = listPlayers();
	$nbPlayers = 0;
	foreach ($joueursData as &$joueur) {
		if (!$joueur['controller'])
			$nbPlayers++;
	}
	unset($joueur);
	$minPlayers = isset($courseRules->minPlayers) ? $courseRules->minPlayers : 2;
	$nbJoueurs = count($joueursData);
	$enoughPlayers = ($nbPlayers >= $minPlayers);
	if ($continuer && $enoughPlayers) {
		if (!empty($courseRules->team)) {
			foreach ($joueursData as &$joueur)
				$joueur['score'] = $joueur['pts'];
			unset($joueur);
			$sJoueurs = array();
			foreach ($joueursData as $i=>$joueur)
				$sJoueurs[] = $i;
			function sortPlayerIds($i1,$i2) {
				global $joueursData, $courseRules;
				if (!empty($courseRules->manualTeams)) {
					$t1 = ($joueursData[$i1]['team']!=-1);
					$t2 = ($joueursData[$i2]['team']!=-1);
					if ($t1 && !$t2) return -1;
					if (!$t1 && $t2) return 1;
				}
				$s1 = $joueursData[$i1]['score'];
				$s2 = $joueursData[$i2]['score'];
				if ($s1 == $s2) return 0;
				return ($s1 < $s2) ? 1:-1;
			}
			usort($sJoueurs,'sortPlayerIds');
			require_once('onlineConsts.php');
			$nbTeams = isset($courseRules->nbTeams) ? $courseRules->nbTeams : DEFAULT_TEAM_COUNT;
			if ($nbTeams > $nbJoueurs)
				$nbTeams = $nbJoueurs;
			$maxJoueursInTeam = ceil($nbJoueurs/$nbTeams);
			$teamScores = array_fill(0,$nbTeams,0);
			$teamNbs = array_fill(0,$nbTeams,0);
			$teamId = 0;
			foreach ($sJoueurs as $i) {
				$joueur = &$joueursData[$i];
				if (empty($courseRules->manualTeams) || ($joueur['team']==-1))
					$joueur['team'] = $teamId;
				else
					$teamId = $joueur['team'];
				$teamScores[$teamId] += $joueur['score'];
				$teamNbs[$teamId]++;
				if ($teamNbs[$teamId] >= $maxJoueursInTeam)
					$teamScores[$teamId] = INF;
				$teamId = array_search(min($teamScores),$teamScores);
				unset($joueur);
			}
			for ($i=0;$i<$nbTeams;$i++) {
				if ($teamNbs[$i] == 0) {
					$teamId = array_search(max($teamNbs),$teamNbs);
					foreach ($joueursData as &$joueur) {
						if ($joueur['team'] == $teamId) {
							$joueur['team'] = $i;
							$teamNbs[$teamId]--;
							$teamNbs[$i]++;
							break;
						}
					}
					unset($joueur);
				}
			}
			foreach ($joueursData as $joueur)
				mysql_query('UPDATE `mkplayers` SET team="'. $joueur['team'] .'" WHERE id="'. $joueur['id'] .'"');
			if (!empty($courseRules->manualTeams)) {
				include('onlineStateUtils.php');
				setCourseExtra($course, array('state' => 'selecting_teams'));
			}
		}
		else
			mysql_query('UPDATE `mkplayers` SET team=-1 WHERE course='. $course);
	}
	if ($enoughPlayers && $allChosen) {
		usleep(100000);
		$joueursData = listPlayers();
		$getMap = getMapData();
		$map = $getMap['map'];
		$now = round(microtime(true)*1000);
	}
	$courseRules = json_decode($getMap['rules']);
	echo '[[';
	$cpuInc = 0;
	foreach ($joueursData as $i=>$joueur) {
		if ($joueur['controller']) {
			if (!isset($persosList)) {
				include('onlineRulesUtils.php');
				ob_start();
				include('getPersos.php');
				$persosList = json_decode(ob_get_clean(), true);
				$persosList = array_keys($persosList);
				srand($course);
				shuffle($persosList);
				srand($now);
				$nbPersos = count($persosList);
			}
			$joueur['joueur'] = empty($courseRules->cpuChars[$cpuInc]) ? $persosList[$cpuInc%$nbPersos] : $courseRules->cpuChars[$cpuInc];
			$joueur['nom'] = getCpuName($cpuInc, $courseRules);
			$cpuInc++;
		}
		echo ($i ? ',':'').'['.$joueur['id'].',"'.$joueur['joueur'].'",'.$joueur['choice_map'].','.$joueur['choice_rand'].','.$joueur['place'].','.json_encode($joueur['nom']).','.$joueur['team'].','.$joueur['controller'].']';
	}
	echo '],'.$map.','.($time-$now).','.round($time/67);
	echo ',{';
	$minPlayers = isset($courseRules->minPlayers) ? $courseRules->minPlayers : 2;
	echo 'minPlayers:'.$minPlayers;
	if (!empty($courseRules->manualTeams))
		echo ',manualTeams:1';
	if (isset($courseRules->nbTeams))
		echo ',nbTeams:'.$courseRules->nbTeams;
	if (!empty($courseRules->cc))
		echo ',cc:'.$courseRules->cc;
	echo '}';
	echo ']';
	if ($continuer && !$enoughPlayers) {
		mysql_query('UPDATE `mariokart` SET map=-1,time='. time() .' WHERE id='. $course);
		mysql_query('UPDATE `mkjoueurs` j LEFT JOIN `mkplayers` p ON j.id=p.id SET '.(($nbPlayers<2) ? 'j.choice_map=0,':'').'p.connecte=0 WHERE j.course='. $course);
		mysql_query('DELETE p FROM `mkplayers` p LEFT JOIN `mkjoueurs` j ON p.id=j.id WHERE p.course='. $course .' AND j.id IS NULL');
	}
}
else
	echo -1;
mysql_close();
?>