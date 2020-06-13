<?php
session_start();
$id = $_SESSION['mkid'];
if ($id) {
	function areset($posts) {
		$nbPosts = count($posts);
		for ($i=0;$i<$nbPosts;$i++) {
			if (!isset($_POST[$posts[$i]]))
				return false;
		}
		return true;
	}
	$isBattle = isset($_POST['battle']);
	$params = $isBattle
		? Array('x','y','z','speed','speedinc','heightinc','rotation','rotincdir','rotinc','size','tourne','tombe','ballons','reserve','champi','etoile','megachampi')
		: Array('x','y','z','speed','speedinc','heightinc','rotation','rotincdir','rotinc','size','tourne','tombe','tours','demitours','champi','etoile','megachampi','billball','eclair','place');
	if (areset($params)) {
		include('initdb.php');
		$getCourse = mysql_fetch_array(mysql_query('SELECT course FROM `mkplayers` WHERE id="'.$id.'"'));
		$course = $getCourse['course'];
		if ($course) {
			$fLaps = (isset($_POST['laps'])&&is_numeric($_POST['laps'])) ? ($_POST['laps']+1):4;
			if (isset($_POST['tours']) && ($_POST['tours'])>$fLaps) $_POST['tours'] = $fLaps;
			$tables = Array('bananes', 'fauxobjets', 'carapaces', 'carapacesRouge', 'carapacesBleue', 'bobombs');
			$nbTables = count($tables);
			$finished = mysql_numrows(mysql_query('SELECT * FROM `mariokart` WHERE id='. $course .' AND map=-1'));
			$time = time();
			$lConnect = round(($time+microtime())*1000/67);
			$limConnect = round((($time-35)+microtime())*1000/67);
			if (!$finished) {
				$alphaB = 'abcdef';
				for ($i=0;$i<$nbTables;$i++) {
					for ($j=0;isset($_POST[$alphaB[$i].$j]);$j++)
						mysql_query('DELETE FROM `'.$tables[$i].'` WHERE id="'.$_POST[$alphaB[$i].$j].'"');
				}
				$lastInsertedObjects = array();
				for ($i=0;$i<$nbTables;$i++) {
					$lettre = $alphaB[$i];
					$table = $tables[$i];
					$champs = mysql_query('SELECT * FROM `'. $table.'` LIMIT 1');
					$nbChamps = mysql_num_fields($champs);
					$nChamps = $nbChamps-1;
					for ($j=0;true;$j++) {
						$continuer = true;
						for ($k=0;$k<$nChamps;$k++) {
							if (!isset($_POST[$lettre.$j.'_'.$k])) {
								$continuer = false;
								break;
							}
						}
						if ($continuer) {
							$oID = $_POST[$lettre.$j.'_0'];
							if ($oID != -1) {
								$sql = 'UPDATE `'.$table.'` SET ';
								for ($k=2;$k<$nbChamps;$k++)
									$sql .= (($k!=2) ? ',':'') . mysql_field_name($champs, $k) .'='. $_POST[$lettre.$j.'_'.($k-1)];
								mysql_query($sql .' WHERE id='. $oID);
							}
							else {
								$sql = 'INSERT INTO `'.$table.'` (`course`';
								for ($k=2;$k<$nbChamps;$k++)
									$sql .= ',`'.mysql_field_name($champs, $k).'`';
								$sql .= ') VALUES ('. $course;
								for ($k=2;$k<$nbChamps;$k++)
									$sql .= ',"'.$_POST[$lettre.$j.'_'.($k-1)].'"';
								mysql_query($sql .')');
								$lastInsertedObjects[$table] = mysql_insert_id();
							}
						}
						else
							break;
					}
				}
				function getLastOne($table) {
					global $lastInsertedObjects;
					if (isset($lastInsertedObjects[$table]))
						return $lastInsertedObjects[$table];
					return -1;
				}
				$sql = 'UPDATE `mkplayers` SET ';
				$nbPosts = count($params);
				for ($i=0;$i<$nbPosts;$i++)
					$sql .= $params[$i] .'="'.$_POST[$params[$i]] .'",';
				if (isset($_POST['i'])&&isset($_POST['j']))
					$sql .= 'arme="'.$_POST['i'].'",iUse="'.(($_POST['j']!=-1)?$_POST['j']:getLastOne($tables[$_POST['i']])).'"';
				else
					$sql .= 'arme=-1,iUse=-1';
				$winning = $isBattle ? (($_POST['ballons']==0) && !mysql_numrows(mysql_query('SELECT * FROM `mkplayers` WHERE id='.$id.' AND ballons=0'))) : (($_POST['tours']==$fLaps) && !mysql_numrows(mysql_query('SELECT * FROM `mkplayers` WHERE id='.$id.' AND tours>='.$fLaps)));
				if ($winning)
					$sql .= ',place='.(mysql_numrows(mysql_query('SELECT * FROM `mkplayers` WHERE course='.$course.' AND '. ($isBattle ? 'ballons!=0':'tours>='.$fLaps)))+1-$isBattle);
				$sql .= ',connecte='.$lConnect.' WHERE id="'. $id .'"';
				mysql_query($sql);
				if ($winning && !$isBattle)
					mysql_query('UPDATE `mariokart` SET time='.$time.' WHERE id='.$course.' AND time>'.$time);
			}
			$joueurs = mysql_query('SELECT * FROM `mkplayers` WHERE course='.$course.' ORDER BY id');
			echo '[[';
			$getCourseOptions = mysql_fetch_array(mysql_query('SELECT g.rules FROM `mkgameoptions` g INNER JOIN `mariokart` m ON m.link=g.id WHERE m.id="'. $course .'"'));
			$courseRules = new stdClass();
			if ($getCourseOptions)
				$courseRules = json_decode($getCourseOptions['rules']);
			$isTeam = !empty($courseRules->team);
			$isFriendly = !empty($courseRules->friendly);
			$racing = 0;
			$racingPerTeam = array(0,0);
			$virgule = false;
			while ($joueur=mysql_fetch_array($joueurs)) {
				if ($joueur['team'] == -1)
					$isTeam = false;
				if ($joueur['id'] != $id) {
					echo ($virgule ? ',':'').'[['.$joueur['id'].','.$joueur['connecte'].','.$joueur['arme'].','.$joueur['iUse'].'],['.$joueur['x'];
					$nbPosts = count($params);
					for ($i=1;$i<$nbPosts;$i++)
						echo ','. $joueur[$params[$i]];
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
			for ($i=0;$i<$nbTables;$i++) {
				echo ($i ? ',':'').'[';
				$getObj = mysql_query('SELECT * FROM `'.$tables[$i].'` WHERE course='.$course.' ORDER BY id');
				$nbChamps = mysql_num_fields($getObj);
				if ($champ = mysql_fetch_array($getObj)) {
					echo '['.$champ['id'];
					for ($j=2;$j<$nbChamps;$j++)
						echo ','. $champ[mysql_field_name($getObj, $j)];
					echo ']';
					while ($champ = mysql_fetch_array($getObj)) {
					echo ',['.$champ['id'];
						for ($j=2;$j<$nbChamps;$j++)
							echo ','. $champ[mysql_field_name($getObj, $j)];
						echo ']';
					}
				}
				echo ']';
			}
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
					for ($i=0;$i<$nbTables;$i++)
						mysql_query('DELETE FROM `'.$tables[$i].'` WHERE course='.$course);
				}
				function getScoreInc($i,$score,$nbScores,$total) {
					$coeff = (($nbScores-$i-1)/($nbScores-1))-($score/$total);
					$coeff *= pow(2,$coeff);
					return $coeff*(($coeff<0)?$score:max(20000-$score,5000))/80;
				}
				$joueurs = mysql_query('SELECT p.id,j.nom,p.aPts,p.team FROM `mkplayers` p INNER JOIN `mkjoueurs` j ON p.id=j.id WHERE p.course='.$course.' ORDER BY p.place');
				$joueursData = array();
				while ($joueur=mysql_fetch_array($joueurs))
					$joueursData[] = $joueur;
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
				foreach ($joueursData as $joueur) {
					$score = $joueur['aPts'];
					if ($isFriendly)
						$inc = 0;
					elseif ($isTeam) {
						$team = $joueur['team'];
						$inc = $ptsPerTeam[$team]*$ptsProrata[$i]/$ptsProrataTotal[$team];
					}
					else
						$inc = getScoreInc($i,$score,$nbScores,$total);
					$inc = round($inc);
					echo ($i ? ',':'') .'['.$joueur['id'].',"'.$joueur['nom'].'",'.$joueur['aPts'].','.$inc.','.$joueur['team'].']';
					$nPts = $joueur['aPts']+$inc;
					$i++;
					if ($finishing) {
						$shouldLog = $isFriendly;
						if ($nPts != $joueur['aPts']) {
							mysql_query('UPDATE `mkjoueurs` SET '.$pts_.'='.$nPts.' WHERE id='.$joueur['id']);
							$shouldLog = true;
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