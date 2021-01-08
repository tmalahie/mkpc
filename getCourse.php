<?php
include('session.php');
if ($id) {
	require_once('onlineConsts.php');
	$isCup = false;
	$isMCup = false;
	$isSingle = false;
	$isBattle = isset($_POST['battle']);
	include('initdb.php');
	if (isset($_POST['mid']) && is_numeric($_POST['mid'])) {
		$nid = $_POST['mid'];
		if (mysql_numrows(mysql_query('SELECT * FROM mkmcups WHERE id='. $nid))) {
			$isCup = true;
			$isMCup = true;
		}
	}
	elseif (isset($_POST['cid']) && is_numeric($_POST['cid'])) {
		$nid = $_POST['cid'];
		if (mysql_numrows(mysql_query('SELECT * FROM mkcups WHERE id='. $nid .' AND mode=1'))) {
			$isCup = true;
			$complete = true;
		}
	}
	elseif (isset($_POST['sid']) && is_numeric($_POST['sid'])) {
		$nid = $_POST['sid'];
		if (mysql_numrows(mysql_query('SELECT * FROM mkcups WHERE id='. $nid .' AND mode=0'))) {
			$isCup = true;
			$complete = false;
		}
	}
	elseif (isset($_POST['id']) && is_numeric($_POST['id'])) {
		$nid = $_POST['id'];
		if (mysql_numrows(mysql_query('SELECT * FROM mkcircuits WHERE id='. $nid))) {
			$isCup = true;
			$complete = false;
			$isSingle = true;
		}
	}
	elseif (isset($_POST['i']) && is_numeric($_POST['i'])) {
		$nid = $_POST['i'];
		if (mysql_numrows(mysql_query('SELECT * FROM '.($isBattle?'arenes':'circuits').' WHERE id='. $nid))) {
			$isCup = true;
			$complete = true;
			$isSingle = true;
		}
	}
	elseif (isset($_POST['meta'])) {
		if (!isset($_POST['key']))
			$_POST['key'] = 191057616;
		$nid = 8;
		$isCup = true;
		$isMCup = true;
	}
	else
		$nid = 0;
	$nlink = 0;
	$linkOptions = new stdClass();
	$linkOptions->public = true;
	$linkOptions->rules = new stdClass();
	if (isset($_POST['key']) && is_numeric($_POST['key'])) {
		$nlink = $_POST['key'];
		if (!mysql_numrows(mysql_query('SELECT * FROM `mkprivgame` WHERE id='.$nlink)))
			$nlink = 0;
		elseif ($getOptions = mysql_fetch_array(mysql_query('SELECT rules,public FROM `mkgameoptions` WHERE id='.$nlink))) {
			$linkOptions->rules = json_decode($getOptions['rules']);
			$linkOptions->public = $getOptions['public'];
		}
	}
	if (!isset($linkOptions->rules->minPlayers))
		$linkOptions->rules->minPlayers = DEFAULT_MIN_PLAYERS;
	if (!isset($linkOptions->rules->maxPlayers))
		$linkOptions->rules->maxPlayers = DEFAULT_MAX_PLAYERS;
	$nmode = $isCup ? ($isMCup?8:($complete?1:0)+($isSingle?2:0)+($isBattle?4:0)):($isBattle ? 1:0);
	$cupSQL = ' AND cup="'. $nid .'" AND mode='. $nmode .' AND link='. $nlink;
	$time = time();
	$lConnect = round(($time+microtime()-30)*1000/67);
	$getCourse = mysql_fetch_array(mysql_query('SELECT course FROM `mkjoueurs` WHERE id="'.$id.'"'));
	$course = $getCourse['course'];
	$cas = 0;
	$switchCourse = false;
	if (!$course && !$linkOptions->public) {
		// Course privée, on impose l'ID de course s'il existe déjà
		$alreadyCreated = mysql_fetch_array(mysql_query('SELECT id FROM `mariokart` WHERE 1'. $cupSQL));
		if ($alreadyCreated) {
			$course = $alreadyCreated['id'];
			$switchCourse = true;
		}
	}
	function switchCourseIfNeeded() {
		global $switchCourse,$course,$id;
		if ($switchCourse) {
			mysql_query('UPDATE `mkjoueurs` SET course='.$course.' WHERE id='.$id);
			mysql_query('UPDATE `mkplayers` SET course='.$course.' WHERE id='.$id);
		}
		unset($_SESSION['date']);
	}
	function sendCourseNotifs() {
		global $id,$linkOptions;
		if ($linkOptions->public)
			include('putCourseNotifs.php');
	}
	function update_lastco() {
		global $id;
		mysql_query('UPDATE `mkprofiles` SET last_connect=NULL WHERE id='. $id);
		mysql_query('UPDATE `mkplayers` SET team=-1 WHERE id='. $id);
	}
	function return_success($remainingTtime) {
		echo '{"found":true,"time":'.max($remainingTtime,12).'}';
	}
	function return_failure() {
		global $pendingPlayers, $linkOptions;
		$activePlayers = get_active_players();
		echo '{"found":false'.($activePlayers?(',"nb_players":'.$activePlayers):'').($pendingPlayers?(',"pending_players":'.$pendingPlayers.',"min_players":'.$linkOptions->rules->minPlayers):'').'}';
	}
	function get_active_players() {
		global $time, $nmode, $nid, $nlink;
		$getPlayers = mysql_query(
			'SELECT COUNT(DISTINCT p.id) AS nb FROM mkplayers p
			INNER JOIN mariokart m ON p.course=m.id
			WHERE p.connecte>='.floor(($time-35)*1000/67).' AND p.controller=0
			AND m.cup="'. $nid .'" AND m.mode='. $nmode .' AND m.link='. $nlink
		);
		if ($nbPlayers = mysql_fetch_array($getPlayers))
			return +$nbPlayers['nb'];
		return 0;
	}
	function get_highest_players() {
		global $time, $nmode, $nid, $nlink, $isBattle, $linkOptions;
		if (!$linkOptions->public)
			return 0;
		$getPlayers = mysql_query(
			'SELECT p.course,m.time,COUNT(p.id) AS nb FROM mkplayers p
			INNER JOIN mariokart m ON p.course=m.id
			WHERE p.connecte>='.floor(($time-35)*1000/67).'
			AND m.cup="'. $nid .'" AND m.mode='. $nmode .' AND m.link='. $nlink .' AND (m.time>='.(($time-($isBattle?300:210))*1000).' OR m.time<'.($time+1000).')
			GROUP BY p.course HAVING(nb<'.(+$linkOptions->rules->maxPlayers).')
			ORDER BY nb DESC LIMIT 1'
		);
		if ($nbPlayers = mysql_fetch_array($getPlayers))
			return +$nbPlayers['nb'];
		return 0;
	}
	function get_remaining_players($course, &$getTime, $shouldBeActive=true) {
		global $id, $time, $lConnect;
		//return mysql_numrows(mysql_query('SELECT * FROM `mkjoueurs` j LEFT JOIN `mkplayers` p ON j.id=p.id WHERE j.course='. $course .' AND j.id!="'.$id.'" AND (p.connecte>='. $lConnect . ($getTime['map']==-1&&$getTime['time']>=($time-5)&&$getTime['time']<($time+1000) ? ' OR p.connecte IS NULL OR p.connecte=0':'') .')'));
		return mysql_numrows(mysql_query('SELECT * FROM `mkjoueurs` j LEFT JOIN `mkplayers` p ON j.id=p.id WHERE j.course='. $course .' AND j.id!="'.$id.'"'.($shouldBeActive ? (' AND (p.connecte>='. $lConnect . ($getTime['map']==-1&&$getTime['time']>=($time-5)&&$getTime['time']<($time+1000) ? ' OR p.connecte IS NULL OR p.connecte=0':'') .')'):'')));
	}
	function store_pending_players($nbJoueurs) {
		global $pendingPlayers;
		if ($nbJoueurs)
			$pendingPlayers = $nbJoueurs+1;
	}
	if (!$course)
		$cas = 1; // Il faudra créer une nouvelle course, si on n'en trouve pas une disponible (INSERT INTO mariokart)
	elseif (mysql_numrows(mysql_query('SELECT * FROM `mkjoueurs` WHERE course='. $course)) == 1)
		$cas = 2; // Plus de joueurs connecté sur la course actuelle. cas=2 : On pourra garder cette course, si on n'en trouve pas une disponible
	elseif (($getTime=mysql_fetch_array(mysql_query('SELECT time,map,cup,mode,link FROM `mariokart` WHERE id='. $course))) &&
		!get_remaining_players($course, $getTime)) {
		// Tous les joueurs de la course actuelle sont AFK, on peut les kicker
		mysql_query('UPDATE `mariokart` SET map=-1 WHERE id='. $course);
		mysql_query('UPDATE `mkjoueurs` SET course=0,choice_map=0 WHERE course='. $course);
		mysql_query('DELETE FROM `mkplayers` WHERE course='. $course);
		mysql_query('DELETE FROM `mkchat` WHERE course='. $course);
		mysql_query('DELETE FROM `items` WHERE course='.$course);
		$cas = 2;
	}
	elseif ($getTime && $getTime['map']==-1 && $getTime['cup']==$nid && $getTime['mode']==$nmode && $getTime['link']==$nlink && $getTime['time']>=($time+10)) {
		// La course actuelle est sur le point de démarrer
		// On vérifie que le nombre des joueurs match les conditions de la partie
		if (!$linkOptions->public && (get_remaining_players($course, $getTime, false) >= $linkOptions->rules->maxPlayers))
			$cas = 1; // Trop de joueurs, il faudra rejoindre une autre game
		else {
			$nbJoueurs = get_remaining_players($course, $getTime);
			if (($nbJoueurs+1) < $linkOptions->rules->minPlayers) {
				$cas = 2; // Pas assez de joueurs, on attend
				store_pending_players($nbJoueurs);
			}
			else {
				// C'est bon, on affiche le temps restant pour choisir la map
				$tempsRestant = ($getTime['time']-$time);
				if ($tempsRestant > 35) {
					mysql_query('UPDATE `mariokart` SET time='. ($time+35) .' WHERE id='. $course);
					$tempsRestant = 35;
				}
				update_lastco();
				switchCourseIfNeeded();
				return_success($tempsRestant);
				mysql_close();
				exit;
			}
		}
	}
	elseif (!mysql_numrows(mysql_query('SELECT * FROM `mkplayers` WHERE course='. $course .' AND id!="'.$id.'" AND connecte>='. $lConnect))) {
		// Dead code, normalement
		//mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL,1,"Error 404")');
		if (isset($_SESSION['date'])) {
			$ecart = $time-$_SESSION['date'];
			if ($ecart < 40) {
				if ($ecart >= 25) {
					mysql_query('UPDATE `mariokart` SET time='. ($time+35) .', map=-1,cup='. $nid .',mode='. $nmode .',link='. $nlink .' WHERE id='. $course);
					mysql_query('UPDATE `mkjoueurs` SET choice_map=0 WHERE course='.$course);
					mysql_query('UPDATE `mkjoueurs` SET course=0 WHERE course='.$course.' AND id!="'.$id.'"');
					mysql_query('DELETE FROM `mkplayers` WHERE course='. $course);
					mysql_query('DELETE FROM `mkchat` WHERE course='. $course);
					for ($i=0;$i<$nbTables;$i++)
						mysql_query('DELETE FROM `'.$tables[$i].'` WHERE course='.$course);
					switchCourseIfNeeded();
					$cas = 2;
				}
			}
			else
				$_SESSION['date'] = $time;
		}
		else
			$_SESSION['date'] = $time;
	}
	if ($cas) {
		$pID = 0;
		if ($cas == 1) {
			$getCourses = mysql_query('SELECT mariokart.id FROM `mariokart` LEFT JOIN `mkjoueurs` j ON j.course=mariokart.id WHERE map=-1 AND j.id IS NULL' . $cupSQL);
			if ($courses = mysql_fetch_array($getCourses))
				$pID = $courses['id'];
		}
		$getCourses = mysql_query('SELECT mariokart.id,COUNT(j.id) AS nb,mariokart.time FROM `mariokart` INNER JOIN `mkjoueurs` j ON j.course=mariokart.id WHERE map=-1 AND time>='. ($time+10) .' AND mariokart.id!='. $course . $cupSQL .' GROUP BY mariokart.id ORDER BY nb DESC');
		$search = true;
		$maxPlayers = get_highest_players();
		// On recherche les courses disponibles (évitons de créer des nouvelles courses si ce n'est pas nécessaire...)
		while ($courses = mysql_fetch_array($getCourses)) {
			$nbJoueurs = $courses['nb'];
			if (($nbJoueurs >= $maxPlayers) && ($nbJoueurs < $linkOptions->rules->maxPlayers)) {
				if (($nbJoueurs+1) >= $linkOptions->rules->minPlayers) {
					// Cette course est sur le point de démarrer, on l'assigne !
					mysql_query('UPDATE `mkjoueurs` j LEFT JOIN `mkplayers` p ON j.id=p.id SET j.course='.$courses['id'].',p.course='.$courses['id'].',p.connecte=0,j.choice_map=0 WHERE j.id="'. $id .'"');
					$search = false;
					$tempsRestant = ($courses['time']-$time);
					if ($tempsRestant > 35) {
						mysql_query('UPDATE `mariokart` SET time='. ($time+35) .' WHERE id='. $courses['id']);
						$tempsRestant = 35;
					}
					update_lastco();
					switchCourseIfNeeded();
					return_success($tempsRestant);
					break;
				}
				else
					store_pending_players($nbJoueurs);
			}
		}
		if ($search) {
			switch ($cas) {
			case 1 :
				if (!$pID) {
					if (!$linkOptions->public) {
						$alreadyCreated = mysql_fetch_array(mysql_query('SELECT id FROM `mariokart` WHERE 1'. $cupSQL));
						if ($alreadyCreated)
							break;
					}
					mysql_query('INSERT INTO `mariokart` VALUES (null, -1, '. ($time+35) .','. $nid .','. $nmode .','. $nlink .')');
					$pID = mysql_insert_id();
				}
				mysql_query('UPDATE `mkjoueurs` j LEFT JOIN `mkplayers` p ON j.id=p.id SET j.course='.$pID.',p.course='.$pID.',p.connecte=0,j.choice_map=0 WHERE j.id="'. $id .'"');
				sendCourseNotifs();
				break;
			case 2 :
				mysql_query('UPDATE `mariokart` SET time='. ($time+35) .', map=-1,cup='. $nid .',mode='. $nmode .',link='. $nlink .' WHERE id='. $course);
				switchCourseIfNeeded();
			}
			return_failure();
		}
	}
	else
		return_failure();
	mysql_close();
}
?>