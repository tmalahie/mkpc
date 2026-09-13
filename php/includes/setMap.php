<?php
include('onlineUtils.php');
$course = getCourse(array('check_ban' => true));
if ($course) {
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
	$continuer = ($map == -1) && !$spectatorId;
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
		$nbPlayers = mysql_numrows($joueurs);
		$map = rand(0, $nbPlayers-1);
		$time = $now+5000;
		if (!empty($courseRules->manualTeams)) {
			require_once('onlineStateUtils.php');
			$nbJoueurs = $nbPlayers;
			if (isset($courseRules->cpuCount) && ($nbPlayers < $courseRules->cpuCount))
				$nbJoueurs = $courseRules->cpuCount;
			$time += getTeamSelectionTime($nbJoueurs);
		}
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
		// A ranked lineup is fixed at launch, so a member missing at the start of a race keeps
		// their place on the grid: their own kart, under AI control, with the points they have
		// already scored. Coming back reclaims it above, where a present player is re-seeded
		// with controller=0.
		$subIds = array();
		if (!empty($courseRules->lounge) && $nbPlayers) {
			require_once('lounge/common.php');
			foreach (lounge_absent_members($getMap['link'], $course) as $sub) {
				$toUpate = 'course='.$course.',controller='.$playerIds[count($subIds)%$nbPlayers].',aPts='. $sub['pts'] .','.$toUpdate0;
				mysql_query('INSERT INTO `mkplayers` SET id='. $sub['id'] .','.$toUpate.',place=0 ON DUPLICATE KEY UPDATE '.$toUpate);
				$subIds[] = $sub['id'];
				$i++;
			}
		}
		if (isset($courseRules->cpuCount) && ($nbPlayers > 1) && ($i < $courseRules->cpuCount)) {
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
			mysql_query('DELETE FROM `mkplayers` WHERE course='. $course .' AND controller!=0'. ($subIds ? ' AND id NOT IN ('. implode(',', $subIds) .')' : ''));
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
			(SELECT p.id,5000 AS pts,IF(j.joueur IN ("","0"),"mario",j.joueur) AS joueur,IFNULL(p.place,1) AS place,IFNULL(p.team,-1) AS team,1 AS choice_map,1 AS choice_rand,j.nom,p.controller FROM `mkplayers` p LEFT JOIN `mkjoueurs` j ON j.id=p.id WHERE p.course='. $course .' AND p.controller!=0 AND IFNULL(j.course,0)!='. $course .')
			ORDER BY id'
		);
		$joueursData = array();
		while ($joueur=mysql_fetch_array($joueurs))
			$joueursData[] = $joueur;
		return $joueursData;
	}
	$joueursData = listPlayers();
	// `mariokart.map` is an index into the very list the client is handed, and that list now
	// carries substitutes and CPUs - none of which picked anything, and all of which report a
	// placeholder choice. Draw the course only among the karts that really chose one.
	if ($continuer) {
		$choosers = array();
		foreach ($joueursData as $i=>$joueur) {
			if (!$joueur['controller'])
				$choosers[] = $i;
		}
		if ($choosers) {
			$map = $choosers[array_rand($choosers)];
			mysql_query('UPDATE `mariokart` SET map='. $map .' WHERE id='. $course);
		}
	}
	// Teams settled before the room opened (the lounge's captain draft). Seeding them here
	// makes the balancing pass below keep them, the same way it keeps a manual pick.
	$fixedTeams = array();
	if (isset($courseRules->fixedTeams)) {
		foreach ((array) $courseRules->fixedTeams as $playerId => $team)
			$fixedTeams[intval($playerId)] = intval($team);
	}
	$keepTeams = !empty($courseRules->manualTeams) || $fixedTeams;
	$nbPlayers = 0;
	foreach ($joueursData as &$joueur) {
		if (isset($fixedTeams[intval($joueur['id'])]))
			$joueur['team'] = $fixedTeams[intval($joueur['id'])];
		// A substitute stands in its member's place, so the room is not short of them: only a
		// place nobody is filling at all - a numbered CPU, which has no account behind it -
		// leaves the lineup below strength.
		if (!$joueur['controller'] || !is_null($joueur['nom']))
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
				global $joueursData, $keepTeams;
				if ($keepTeams) {
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
				if (!$keepTeams || ($joueur['team']==-1))
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
				require_once('onlineStateUtils.php');
				setCourseExtra($course, array('state' => 'selecting_teams'));
			}
		}
		else
			mysql_query('UPDATE `mkplayers` SET team=-1 WHERE course='. $course);
		
		if (!empty($courseRules->localScore)) {
			require_once('onlineStateUtils.php');
			initCourseState($getMap['link']);
		}
	}
	if ($spectatorId && ($nbPlayers > $minPlayers))
		mysql_query('UPDATE `mkjoueurs` SET course=0 WHERE id="'.$id.'" AND course="'.$course.'"');
	if ($allChosen) {
		if ($enoughPlayers) {
			usleep(100000);
			$joueursData = listPlayers();
			$getMap = getMapData();
			$map = $getMap['map'];
			$now = round(microtime(true)*1000);
		}
		elseif (($map == -1) && $spectatorId)
			$map = -2;
	}
	$courseRules = json_decode($getMap['rules']);
	echo '[[';
	$cpuInc = 0;
	foreach ($joueursData as $i=>$joueur) {
		// A bot standing in for an absent member is not a CPU: it races under their name and
		// their character, and never takes one of the numbered CPU slots.
		$isSub = $joueur['controller'] && !is_null($joueur['nom']);
		if ($joueur['controller'] && is_null($joueur['nom'])) {
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
		echo ($i ? ',':'').'['.$joueur['id'].',"'.$joueur['joueur'].'",'.$joueur['choice_map'].','.$joueur['choice_rand'].','.$joueur['place'].','.json_encode($joueur['nom']).','.$joueur['team'].','.$joueur['controller'].','.($isSub ? 1:0).']';
	}
	echo '],'.$map.','.($time-$now).','.round($time/67);
	echo ',{';
	$minPlayers = isset($courseRules->minPlayers) ? $courseRules->minPlayers : 2;
	echo 'minPlayers:'.$minPlayers;
	if (!empty($courseRules->manualTeams)) {
		require_once('onlineStateUtils.php');
		$nbJoueurs = count($joueursData);
		$selectionTime = getTeamSelectionTime($nbJoueurs);
		echo ',manualTeams:1';
		echo ',selectionTime:'.$selectionTime;
	}
	if (!empty($courseRules->localScore)) {
		require_once('onlineStateUtils.php');
		// The winning choice is what the client resolves the course from
		// (choixJoueurs[rCode[1]][2]), so the same expression records it here.
		if ($allChosen && $enoughPlayers && ($map >= 0) && isset($joueursData[$map]))
			pushCourseTrack($getMap['link'], $joueursData[$map]['choice_map']);
		$courseState = getCourseState($getMap['link']);
		echo ',raceCount:' . $courseState['raceCount'];
		echo ',tracks:' . json_encode(getCourseTracks($courseState));
	}
	if (!empty($courseRules->friendlyFire))
		echo ',friendlyFire:1';
	if (isset($courseRules->nbTeams))
		echo ',nbTeams:'.$courseRules->nbTeams;
	if (!empty($courseRules->teamOpts))
		echo ',teamOpts:'.json_encode($courseRules->teamOpts);
	if (!empty($courseRules->cc))
		echo ',cc:'.$courseRules->cc;
	if (!empty($courseRules->mirror))
		echo ',mirror:'.$courseRules->mirror;
	echo '}';
	echo ']';
	if ($continuer) {
		if (!$enoughPlayers) {
			mysql_query('UPDATE `mariokart` SET map=-1,time='. time() .' WHERE id='. $course);
			mysql_query('UPDATE `mkjoueurs` j LEFT JOIN `mkplayers` p ON j.id=p.id SET '.(($nbPlayers<2) ? 'j.choice_map=0,':'').'p.connecte=0 WHERE j.course='. $course);
			mysql_query('DELETE p FROM `mkplayers` p LEFT JOIN `mkjoueurs` j ON p.id=j.id WHERE p.course='. $course .' AND j.id IS NULL');
		}
		mysql_query('DELETE FROM `mkspectators` WHERE course='.$course.' AND state!="joined"');
	}
}
else
	echo -1;
mysql_close();
?>