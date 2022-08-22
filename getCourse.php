<?php
include('session.php');
if ($id) {
	include('initdb.php');
	include('getCourseParams.php');
	include('onlineUtils.php');
	$cupSQL = ' AND cup="'. $nid .'" AND mode='. $nmode .' AND link='. $nlink;
	$course = getCourse();
	$requestedCourse = isset($_POST['course']) ? intval($_POST['course']) : 0;
	$cas = 0;
	$switchCourse = false;
	$noJoin = isset($_POST['nojoin']);
	if ($noJoin) $linkOptions->rules->maxPlayers += 1000; // hack to remove max player restriction if spectator mode enabled

	if ($course && $spectatorId)
		$switchCourse = true;
	if (!$course && $requestedCourse) {
		$course = $requestedCourse;
		$switchCourse = true;
	}
	if (!$course && !$linkOptions->public) {
		// Course privée, on impose l'ID de course s'il existe déjà
		$alreadyCreated = mysql_fetch_array(mysql_query('SELECT id FROM `mariokart` WHERE 1'. $cupSQL));
		if ($alreadyCreated) {
			$course = $alreadyCreated['id'];
			$switchCourse = true;
		}
	}
	$newSpectatorId = 0;
	function switchCourseIfNeeded($newCourse = null) {
		global $switchCourse,$course,$id, $noJoin,$spectatorId,$newSpectatorId;
		if (null === $newCourse) $newCourse = $course;
		if ($noJoin) {
			mysql_query('UPDATE `mkjoueurs` SET course=0 WHERE id='.$id.' AND choice_map=0');
			$newSpectatorId = joinSpectatorMode($newCourse);
		}
		else {
			if ($switchCourse) {
				mysql_query('UPDATE `mkjoueurs` SET course='.$newCourse.',choice_map=0 WHERE id='.$id);
				mysql_query('UPDATE `mkplayers` SET course='.$newCourse.' WHERE id='.$id);
			}
			if (!$spectatorId)
				mysql_query('DELETE FROM `mkspectators` WHERE course='.$newCourse.' AND player='.$id);
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
		global $newSpectatorId;
		echo '{"found":true,"time":'.max($remainingTtime,12).($newSpectatorId ? ',"spectator":'.$newSpectatorId:'').'}';
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
			return intval($nbPlayers['nb']);
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
			return intval($nbPlayers['nb']);
		return 0;
	}
	function get_remaining_players($course, &$getTime, $shouldBeActive=true) {
		global $id;
		//return mysql_numrows(mysql_query('SELECT * FROM `mkjoueurs` j LEFT JOIN `mkplayers` p ON j.id=p.id WHERE j.course='. $course .' AND j.id!="'.$id.'" AND (p.connecte>='. $lConnect . ($getTime['map']==-1&&$getTime['time']>=($time-5)&&$getTime['time']<($time+1000) ? ' OR p.connecte IS NULL OR p.connecte=0':'') .')'));
		return mysql_numrows(get_remaining_player_query($course,$getTime,$shouldBeActive,$id));
	}
	function store_pending_players($nbJoueurs) {
		global $pendingPlayers;
		if ($nbJoueurs)
			$pendingPlayers = $nbJoueurs+1;
	}
	function check_private_race_condition($course = null) {
		global $linkOptions, $nlink, $cupSQL;
		if (!$linkOptions->public) {
			$alreadyCreated = mysql_fetch_array(mysql_query('SELECT id FROM `mariokart` WHERE '. ($course ? "id!=$course" : '1') . $cupSQL));
			if ($alreadyCreated)
				return false;
			if ($nlink && !$course) {
				require_once('apc.php');
				if (!apcu_add("course.insert.$nlink", 1, 1))
					return false;
			}
		}
		return true;
	}
	if (!$course)
		$cas = 1; // Il faudra créer une nouvelle course, si on n'en trouve pas une disponible (INSERT INTO mariokart)
	elseif (!mysql_numrows(mysql_query('SELECT * FROM `mkjoueurs` WHERE course='. $course .' AND id!='.$id)))
		$cas = 2; // Plus de joueurs connecté sur la course actuelle. cas=2 : On pourra garder cette course, si on n'en trouve pas une disponible
	elseif (($getTime=mysql_fetch_array(mysql_query('SELECT time,map,cup,mode,link FROM `mariokart` WHERE id='. $course))) &&
		!get_remaining_players($course, $getTime)) {
		// Tous les joueurs de la course actuelle sont AFK, on peut les kicker
		mysql_query('UPDATE `mariokart` SET map=-1,time='.$time.' WHERE id='. $course);
		mysql_query('UPDATE `mkjoueurs` SET course=0,choice_map=0 WHERE course='. $course);
		mysql_query('DELETE FROM `mkplayers` WHERE course='. $course);
		mysql_query('DELETE FROM `mkchat` WHERE course='. $course);
		mysql_query('DELETE FROM `items` WHERE course='.$course);
		$cas = 2;
	}
	elseif ($getTime && $getTime['map']==-1 && $getTime['cup']==$nid && $getTime['mode']==$nmode && $getTime['link']==$nlink && $getTime['time']>=($time+10)) {
		// La course actuelle est sur le point de démarrer
		// On vérifie que le nombre des joueurs match les conditions de la partie
		if ($switchCourse && (get_remaining_players($course, $getTime, false) >= $linkOptions->rules->maxPlayers))
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
					if (check_private_race_condition($course)) {
						mysql_query('UPDATE `mariokart` SET time='. ($time+35) .', map=-1,cup='. $nid .',mode='. $nmode .',link='. $nlink .' WHERE id='. $course);
						mysql_query('UPDATE `mkjoueurs` SET choice_map=0 WHERE course='.$course);
						mysql_query('UPDATE `mkjoueurs` SET course=0 WHERE course='.$course.' AND id!="'.$id.'"');
						mysql_query('DELETE FROM `mkplayers` WHERE course='. $course);
						mysql_query('DELETE FROM `mkchat` WHERE course='. $course);
						switchCourseIfNeeded();
						$cas = 2;
					}
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
					if (!$noJoin)
						mysql_query('UPDATE `mkjoueurs` j LEFT JOIN `mkplayers` p ON j.id=p.id SET j.course='.$courses['id'].',p.course='.$courses['id'].',p.connecte=0,j.choice_map=0 WHERE j.id="'. $id .'"');
					$search = false;
					$tempsRestant = ($courses['time']-$time);
					if ($tempsRestant > 35) {
						mysql_query('UPDATE `mariokart` SET time='. ($time+35) .' WHERE id='. $courses['id']);
						$tempsRestant = 35;
					}
					update_lastco();
					switchCourseIfNeeded($courses['id']);
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
					if (!check_private_race_condition())
						break;
					mysql_query('INSERT INTO `mariokart` VALUES (null, -1, '. ($time+35) .','. $nid .','. $nmode .','. $nlink .')');
					$pID = mysql_insert_id();
				}
				if (!$noJoin) {
					mysql_query('UPDATE `mkjoueurs` j LEFT JOIN `mkplayers` p ON j.id=p.id SET j.course='.$pID.',p.course='.$pID.',p.connecte=0,j.choice_map=0 WHERE j.id="'. $id .'"');
					sendCourseNotifs();
				}
				break;
			case 2 :
				if (!check_private_race_condition($course))
					break;
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