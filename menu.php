<nav>
	<div id="menu_left">
		<?php
		function flag($chosen,$nLanguage, $src,$alt) {
			global $page, $homepage;
			if ($homepage) {
				$url = $nLanguage ? 'en.php':'fr.php';
				$alt = $nLanguage ? 'Home - Mario Kart PC':'Accueil - Mario Kart PC';
			}
			else
				$url = 'changeLanguage.php?nLanguage='. $nLanguage .'&amp;page='. $page;
			echo ($chosen ? '':'<a href="'. $url .'"'. ($homepage ? ' title="'.$alt.'"':'') .'>') .'<img id="'. ($chosen ? 'chosen':'toChoose') .'" src="images/'.$src.'" alt="'. $alt .'" />'. ($chosen ? '':'</a>');
		}
		flag($language, 1,'english.png','English');
		echo ' <span>&nbsp;</span>';
		flag(!$language, 0,'french.png','Fran&ccedil;ais');
		include('notifsSQL.php');
		if (!isset($page))
			$page = null;
		?>
	</div>
	<div id="menu_right">
		<?php
		function getFollowerFromIp($row) {
			global $id;
			if ($getUser = mysql_fetch_array(mysql_query('SELECT p.id FROM mkprofiles p INNER JOIN mkfollowusers f ON p.id=f.followed WHERE f.follower="'. $id .'" AND p.identifiant='. $row['identifiant'] .' AND p.identifiant2='. $row['identifiant2'] .' AND p.identifiant3='. $row['identifiant3'] .' AND p.identifiant4='. $row['identifiant4'] .' ORDER BY p.id')))
				return $getUser['id'];
			return null;
		}
		$ignoredNotifs = array();
		if ($id) {
			$getNotifMute = mysql_query('SELECT type FROM `mknotifmute` WHERE user="'. $id .'"');
			while ($notifMute = mysql_fetch_array($getNotifMute))
				$ignoredNotifs[$notifMute['type']] = true;
		}
		function canSeeTopic($topic) {
			return (!$topic['private'] || canSeePrivateTopics());
		}
		function canSeePrivateTopics() {
			global $id;
			static $canSeePrivateTopics;
			if (null !== $canSeePrivateTopics)
				return $canSeePrivateTopics;
			$canSeePrivateTopics = false;
			if ($id && mysql_fetch_array(mysql_query('SELECT player FROM `mkrights` WHERE player="'. $id .'" AND privilege IN ("admin","moderator","organizer")')))
				$canSeePrivateTopics = true;
			return $canSeePrivateTopics;
		}
		$myNotifications = mysql_query('SELECT * FROM `mknotifs` WHERE '. $idsSQL .' ORDER BY id DESC');
		$notifsData = Array();
		require_once('apc.php');
		if ($id || $myIdentifiants) {
			$notifsCacheKey = 'notif:'.($id?$id:0).':'.($myIdentifiants?$myIdentifiants[0]:0);
			$notifsCacheValueRaw = apcu_fetch($notifsCacheKey);
			$myNotifsCache = $notifsCacheValueRaw ? explode(':',$notifsCacheValueRaw) : null;
		}
		else {
			$notifsCacheKey = null;
			$myNotifsCache = null;
		}
		$cacheNotifId = $myNotifsCache ? $myNotifsCache[1]:-1;
		$useNotifCache = false;
		$relyOnCacheThreshold = 25;
		$clearCacheThreshold = 10;
		function clearNotifCache() {
			global $notifsCacheKey,$myNotifsCache,$cacheNotifId;
			if ($myNotifsCache) {
				apcu_delete($notifsCacheKey);
				$myNotifsCache = null;
				$cacheNotifId = -1;
			}
		}
		while ($myNotif = mysql_fetch_array($myNotifications)) {
			$notifData = Array();
			$toDelete = false;
			switch ($myNotif['type']) {
			case 'new_reaction':
				if ($notifReaction = mysql_fetch_array(mysql_query('SELECT type,member,link FROM `mkreactions` WHERE id="'. $myNotif['link'] .'"'))) {
					$notifData['reaction'] = $notifReaction;
					$myNotif['link'] = $notifData['reaction']['link'];
					switch ($notifReaction['type']) {
					case 'topic':
						$myNotif['type'] = 'reaction_topic';
						break;
					case 'news':
						$myNotif['type'] = 'reaction_news';
						break;
					case 'newscom':
						$myNotif['type'] = 'reaction_newscom';
						break;
					case 'trackcom':
						$myNotif['type'] = 'reaction_trackcom';
						break;
					}
				}
				break;
			}
			if (!empty($ignoredNotifs[$myNotif['type']])) {
				$toDelete = true;
				clearNotifCache();
			}
			else {
				switch ($myNotif['type']) {
					case 'answer_comment' :
					case 'circuit_comment' :
					case 'reaction_trackcom' :
						if ($comment = mysql_fetch_array(mysql_query('SELECT auteur,type,circuit FROM `mkcomments` WHERE id="'. $myNotif['link'] .'"'))) {
							$notifData['sender'] = $comment['auteur'];
							if ($getCircuit = mysql_fetch_array(mysql_query('SELECT *'. (($comment['type']=='mkcircuits') ? ',!type AS is_circuit':'') .' FROM `'. $comment['type'] .'` WHERE id="'. $comment['circuit'] .'"'))) {
								if ($myNotif['type'] == 'answer_comment') {
									if ($myIdentifiants && ($getCircuit['identifiant'] == $myIdentifiants[0]) && ($getCircuit['identifiant2'] == $myIdentifiants[1]) && ($getCircuit['identifiant3'] == $myIdentifiants[2]) && ($getCircuit['identifiant4'] == $myIdentifiants[3]))
										$toDelete = true;
								}
								if (!$toDelete) {
									$notifData['title'] = $getCircuit['nom'];
									switch ($comment['type']) {
										case 'mkcircuits':
											$notifData['link'] = ($getCircuit['is_circuit'] ? 'circuit':'arena') .'.php?id='. $getCircuit['id'];
											$notifData['type_circuit'] = ($getCircuit['is_circuit'] ? 'circuit':($language ? 'course':'arène'));
											$notifData['the_circuit'] = ($language ? 'the ':($getCircuit['is_circuit'] ? 'le ':'l\''));
											break;
										case 'circuits':
											$notifData['link'] = 'map.php?i='. $getCircuit['ID'];
											$notifData['type_circuit'] = 'circuit';
											$notifData['the_circuit'] = ($language ? 'the ':'le ');
											break;
										case 'arenes':
											$notifData['link'] = 'battle.php?i='. $getCircuit['ID'];
											$notifData['type_circuit'] = ($language ? 'course':'arène');
											$notifData['the_circuit'] = ($language ? 'the ':'l\'');
											break;
										case 'mkcups':
											$notifData['link'] = ($getCircuit['mode'] ? 'map.php':'circuit.php') .'?cid='. $getCircuit['id'];
											$notifData['type_circuit'] = ($language ? 'cup':'coupe');
											$notifData['the_circuit'] = ($language ? 'the ':'la ');
											break;
										case 'mkmcups':
											$notifData['link'] = ($getCircuit['mode'] ? 'map.php':'circuit.php') .'?mid='. $getCircuit['id'];
											$notifData['type_circuit'] = ($language ? 'cup':'coupe');
											$notifData['the_circuit'] = ($language ? 'the ':'la ');
											break;
									}
								}
							}
							else
								$toDelete = true;
						}
						else
							$toDelete = true;
						break;
					case 'answer_forum' :
					case 'forum_mention' :
					case 'forum_quote' :
					case 'reaction_topic' :
						$linkData = explode(',', $myNotif['link']);
						if ($notifMsg = mysql_fetch_array(mysql_query('SELECT * FROM `mkmessages` WHERE topic="'. $linkData[0] .'" AND id="'. $linkData[1] .'"'))) {
							if (($topicData = mysql_fetch_array(mysql_query('SELECT titre,private FROM `mktopics` WHERE id="'. $notifMsg['topic'] .'"'))) && canSeeTopic($topicData)) {
								$notifData['sender'] = $notifMsg['auteur'];
								$notifData['title'] = $topicData['titre'];
								if ($myNotif['type'] == 'answer_forum') {
									$getFirstMessage_ = mysql_fetch_array(mysql_query('SELECT auteur FROM `mkmessages` WHERE topic="'. $notifMsg['topic'] .'" AND id=1 LIMIT 1'));
									$notifData['mine'] = ($getFirstMessage_['auteur']==$id);
									$notifData['link'] = 'topic.php?topic='. $notifMsg['topic'];
								}
								else {
									if (isset($notifData['reaction']))
										$notifData['sender'] = $notifData['reaction']['member'];
									$notifData['link'] = 'topic.php?topic='. $notifMsg['topic'] .'&amp;page='. ceil(mysql_numrows(mysql_query('SELECT * FROM `mkmessages` WHERE topic="'. $linkData[0] .'" AND id<='. $linkData[1]))/20);
								}
							}
							else
								$toDelete = true;
						}
						else
							$toDelete = true;
						break;
					case 'follower_topic' :
						if ($notifMsg = mysql_fetch_array(mysql_query('SELECT * FROM `mkmessages` WHERE topic="'. $myNotif['link'] .'" AND id=1'))) {
							if (($topicData = mysql_fetch_array(mysql_query('SELECT titre,private FROM `mktopics` WHERE id="'. $notifMsg['topic'] .'"'))) && canSeeTopic($topicData)) {
								$notifData['sender'] = $notifMsg['auteur'];
								$notifData['title'] = $topicData['titre'];
								$notifData['link'] = 'topic.php?topic='. $notifMsg['topic'] .'&amp;src=follow';
							}
							else
								$toDelete = true;
						}
						else
							$toDelete = true;
						break;
					case 'follower_news' :
						if ($notifNews = mysql_fetch_array(mysql_query('SELECT * FROM `mknews` WHERE id="'. $myNotif['link'] .'"'))) {
							$notifData['sender'] = $notifNews['author'];
							$notifData['title'] = $notifNews['title'];
							$notifData['link'] = 'news.php?id='. $notifNews['id'] .'&amp;src=follow';
						}
						else
							$toDelete = true;
						break;
					case 'follower_circuit' :
						$linkData = explode(',', $myNotif['link']);
						$circuitType = $linkData[0];
						$circuitId = $linkData[1];
						switch ($circuitType) {
						case 0:
							$notifCircuit = mysql_fetch_array(mysql_query('SELECT id,nom,type,identifiant,identifiant2,identifiant3,identifiant4 FROM `mkcircuits` WHERE id="'. $circuitId .'"'));
							$designation = $notifCircuit['type'] ? ($language ? 'the arena':'l\'arène'):($language ? 'the circuit':'le circuit');
							$link = ($notifCircuit['type'] ? 'arena':'circuit') .'.php?id='. $notifCircuit['id'];
							break;
						case 1:
							$notifCircuit = mysql_fetch_array(mysql_query('SELECT id,nom,identifiant,identifiant2,identifiant3,identifiant4 FROM `circuits` WHERE id="'. $circuitId .'" AND nom IS NOT NULL'));
							$designation = $language ? 'the circuit':'le circuit';
							$link = 'map.php?i='. $notifCircuit['id'];
							break;
						case 2:
							$notifCircuit = mysql_fetch_array(mysql_query('SELECT id,nom,identifiant,identifiant2,identifiant3,identifiant4 FROM `arenes` WHERE id="'. $circuitId .'" AND nom IS NOT NULL'));
							$designation = $language ? 'the arena':'l\'arène';
							$link = 'battle.php?i='. $notifCircuit['id'];
							break;
						case 3:
							$notifCircuit = mysql_fetch_array(mysql_query('SELECT id,nom,mode,identifiant,identifiant2,identifiant3,identifiant4 FROM `mkcups` WHERE id="'. $circuitId .'"'));
							$designation = $language ? 'the cup':'la coupe';
							if ($notifCircuit['mode'])
								$link = 'map.php?cid='. $notifCircuit['id'];
							else
								$link = 'circuit.php?cid='. $notifCircuit['id'];
							break;
						case 4:
							$notifCircuit = mysql_fetch_array(mysql_query('SELECT id,nom,mode,identifiant,identifiant2,identifiant3,identifiant4 FROM `mkmcups` WHERE id="'. $circuitId .'"'));
							$designation = $language ? 'the multicup':'la multicoupe';
							if ($notifCircuit['mode'])
								$link = 'map.php?mid='. $notifCircuit['id'];
							else
								$link = 'circuit.php?mid='. $notifCircuit['id'];
							break;
						}
						if ($notifCircuit) {
							$notifData['sender'] = getFollowerFromIp($notifCircuit);
							if ($notifData['sender']) {
								$notifData['title'] = $notifCircuit['nom'];
								$notifData['the_circuit'] = $designation;
								$notifData['link'] = $link.'&amp;src=follow';
							}
							else
								$toDelete = true;
						}
						else
							$toDelete = true;
						break;
					case 'follower_challenge' :
						if ($clData = mysql_fetch_array(mysql_query('SELECT * FROM `mkchallenges` WHERE id="'. $myNotif['link'] .'" AND status="active"'))) {
							$notifData['link'] = 'challengeTry.php?challenge='. $clData['id'];
							$notifData['challenge'] = $clData;
							if ($clRaceData = mysql_fetch_array(mysql_query('SELECT identifiant,identifiant2,identifiant3,identifiant4 FROM `mkclrace` WHERE id="'. $clData['clist'] .'"')))
								$notifData['sender'] = getFollowerFromIp($clRaceData);
						}
						else
							$toDelete = true;
						break;
					case 'follower_perso' :
						$notifPerso = mysql_fetch_array(mysql_query('SELECT id,name,identifiant,identifiant2,identifiant3,identifiant4 FROM `mkchars` WHERE id="'. $myNotif['link'] .'" AND author IS NOT NULL'));
						if ($notifPerso) {
							$notifData['sender'] = getFollowerFromIp($notifPerso);
							if ($notifData['sender']) {
								$notifData['title'] = $notifPerso['name'];
								$notifData['link'] = 'mariokart.php?src=follow';
							}
							else
								$toDelete = true;
						}
						else
							$toDelete = true;
						break;
					case 'news_comment' :
					case 'answer_newscom' :
					case 'reaction_newscom' :
						if ($notifMsg = mysql_fetch_array(mysql_query('SELECT c.author,news,title FROM `mknewscoms` c INNER JOIN `mknews` n ON c.news=n.id WHERE c.id="'. $myNotif['link'] .'"'))) {
							if (isset($notifData['reaction']))
								$notifData['sender'] = $notifData['reaction']['member'];
							else
								$notifData['sender'] = $notifMsg['author'];
							$notifData['link'] = 'news.php?id='. $notifMsg['news'] .'#news-comment-ctn-0';
							$notifData['title'] = $notifMsg['title'];
						}
						else
							$toDelete = true;
						break;
					case 'news_moderated' :
					case 'reaction_news' :
						if ($newsData = mysql_fetch_array(mysql_query('SELECT * FROM `mknews` WHERE id="'. $myNotif['link'] .'" AND status!="pending"'))) {
							if (isset($notifData['reaction']))
								$notifData['sender'] = $notifData['reaction']['member'];
							$notifData['link'] = 'news.php?id='. $newsData['id'];
							$notifData['title'] = $newsData['title'];
						}
						else
							$toDelete = true;
						break;
					case 'challenge_moderated' :
						if ($clData = mysql_fetch_array(mysql_query('SELECT * FROM `mkchallenges` WHERE id="'. $myNotif['link'] .'" AND validation!="" AND status IN ("active","pending_completion")'))) {
							$notifData['link'] = 'challengeDetails.php?ch='. $clData['id'];
							$notifData['challenge'] = $clData;
						}
						else
							$toDelete = true;
						break;
					case 'new_followuser' :
						if ($getFollower = mysql_fetch_array(mysql_query('SELECT * FROM `mkfollowusers` WHERE follower="'. $myNotif['link'] .'" AND followed="'. $id .'"'))) {
							$notifData['link'] = 'listFollowers.php';
							$notifData['sender'] = $getFollower['follower'];
						}
						else
							$toDelete = true;
						break;
					case 'new_followtopic' :
						$linkData = explode(',', $myNotif['link']);
						if ($getFollower = mysql_fetch_array(mysql_query('SELECT * FROM `mkfollowers` WHERE topic="'. $linkData[0] .'" AND user="'. $linkData[1] .'"'))) {
							if (($topicData = mysql_fetch_array(mysql_query('SELECT titre,private FROM `mktopics` WHERE id="'. $linkData[0] .'"'))) && canSeeTopic($topicData)) {
								$notifData['link'] = 'topic.php?topic='. $getFollower['topic'] .'&amp;src=followed';
								$notifData['sender'] = $getFollower['user'];
								$notifData['title'] = $topicData['titre'];
							}
							else
								$toDelete = true;
						}
						else
							$toDelete = true;
						break;
					case 'currently_online' :
						$userId = $myNotif['link'];
						$currentCoTime = time();
						$minCoTime = floor(($currentCoTime-15)*1000/67);

						require_once('public_links.php');
						$getPlayingMode = mysql_query('(
							SELECT m.id,m.mode,m.cup,m.link
							FROM mkjoueurs j
							INNER JOIN mariokart m ON m.id=j.course
							WHERE m.map=-1 AND m.time>='.$currentCoTime.' AND m.link IN ('.$publicLinksString.') AND j.id='.$userId.'
						) UNION (
							SELECT m.id,m.mode,m.cup,m.link
							FROM mkjoueurs j
							INNER JOIN mariokart m ON m.id=j.course
							WHERE m.time>='.($currentCoTime*1000).' AND m.link IN ('.$publicLinksString.') AND j.id='.$userId.'
						) UNION (
							SELECT m.id,m.mode,m.cup,m.link
							FROM mkjoueurs j INNER JOIN mariokart m ON j.course=m.id AND m.mode=0 INNER JOIN mkplayers p ON j.id=p.id
							WHERE p.connecte>='.$minCoTime.' AND m.link IN ('.$publicLinksString.') AND j.id='.$userId.'
						) ORDER BY id DESC');
						if ($playingMode = mysql_fetch_array($getPlayingMode)) {
							$nlink = $playingMode['link'];
							if ($playingMode['cup']) {
								$isMCup = ($playingMode['mode']==8);
								$isBattle = ($playingMode['mode']%8>=4);
								$isSingle = (($playingMode['mode']%4)>=2);
								$complete = (($playingMode['mode']%2)>=1);
								$notifData['link'] = 'online.php?'.($isMCup?'mid':($isSingle?($complete?'i':'id'):($complete?"cid":"sid")))."=".$playingMode['cup'].($isBattle?'&battle':'').($nlink?('&key='.$nlink):'');
								$notifData['creation'] = array(
									'id' => $playingMode['cup'],
									'mcup' => $isMCup,
									'complete' => $complete,
									'single' => $isSingle
								);
							}
							else {
								$isBattle = ($playingMode['mode']==1);
								$notifData['link'] = 'online.php'.($isBattle?('?battle'.($nlink?('&key='.$nlink):'')):($nlink?('?key='.$nlink):''));
							}
							$notifData['battle'] = $isBattle;
							$notifData['sender'] = $userId;
						}
						else
							$toDelete = true;
						break;
					case 'new_record' :
						if ($rData = mysql_fetch_array(mysql_query('SELECT r.* FROM `mkrecords` r LEFT JOIN mkrecords r2 ON r2.identifiant='.$identifiants[0].' AND r2.identifiant2='.$identifiants[1].' AND r2.identifiant3='.$identifiants[2].' AND r2.identifiant4='.$identifiants[3].' AND r2.class=r.class AND r2.type=r.type AND r2.circuit=r.circuit AND r2.time<r.time WHERE r.id="'. $myNotif['link'] .'" AND r2.id IS NULL'))) {
							$notifData['link'] = 'classement.php?map='. $rData['circuit'] .'&cc='. $rData['class'];
							$notifData['record'] = $rData;
							$notifData['sender'] = htmlspecialchars($rData['name']);
							$notifData['raw_names'] = true;
						}
						else
							$toDelete = true;
						break;
					case 'new_reaction':
						$toDelete = true;
						break;
				}
			}
			if ($toDelete)
				mysql_query('DELETE FROM `mknotifs` WHERE id="'. $myNotif['id'] .'"');
			else {
				$notifData['id'] = $myNotif['id'];
				$linkExists = false;
				$n = count($notifsData);
				for ($i=0;$i<$n;$i++) {
					$iNotif = &$notifsData[$i];
					if ($iNotif['link'] == $notifData['link']) {
						$linkExists = true;
						break;
					}
				}
				if ($linkExists) {
					$senderExists = false;
					$n = count($iNotif['list']);
					for ($j=0;$j<$n;$j++) {
						$jNotif = $iNotif['list'][$j];
						if ($jNotif['sender'] == $notifData['sender']) {
							$senderExists = true;
							break;
						}
					}
					if (!$senderExists)
						$iNotif['list'][] = $notifData;
				}
				else {
					$notifData['type'] = $myNotif['type'];
					$notifData['list'] = Array($notifData);
					$notifsData[] = $notifData;
				}
				if ($myNotif['id'] <= $cacheNotifId) {
					if ($myNotif['id'] == $cacheNotifId) {
						if (count($notifsData) >= $clearCacheThreshold) {
							$useNotifCache = true;
							break;
						}
					}
					clearNotifCache();
				}
			}
		}
		function get_people_names(&$notifData) {
			$list = $notifData['list'];
			$res = Array();
			if (empty($notifData['raw_names'])) {
				if (count($list)) {
					$idsSql = '';
					$o = '';
					foreach ($list as $iNotif) {
						$idsSql .= $o;
						$o = ' OR ';
						$idsSql .= 'id="'. (isset($iNotif['sender']) ? $iNotif['sender']:'') .'"';
					}
					$getUsers = mysql_query('SELECT nom FROM `mkjoueurs` WHERE '. $idsSql);
					while ($user = mysql_fetch_array($getUsers))
						$res[] = $user['nom'];
				}
			}
			else {
				foreach ($list as $iNotif)
					$res[] = $iNotif['sender'];
			}
			return $res;
		}
		function join_people_names($names) {
			global $language;
			$nbPersons = count($names);
			if ($nbPersons) {
				if ($nbPersons == 1)
					return '<strong>'. $names[0] .'</strong>';
				$res = '';
				for ($i=0;$i<$nbPersons;$i++) {
					if ($i) {
						if ($i == ($nbPersons-1))
							$res .= $language ? ' and ':' et ';
						else
							$res .= $language ? ',':', ';
					}
					$res .= '<strong>'. $names[$i] .'</strong>';
				}
			}
			else
				$res = '<em>'. ($language ? 'Deleted account':'Compte supprimé') .'</em>';
			return $res;
		}
		$nbNotifs = count($notifsData);
		require_once('circuitEscape.php');
		function decodeAndEscapeCircuitNames($str) {
			global $language;
			if ($str)
				return htmlspecialchars(escapeCircuitNames(utf8_encode($str)));
			return $language ? 'Untitled':'Sans titre';
		}
		for ($i=0;$i<$nbNotifs;$i++) {
			$notifData = $notifsData[$i];
			$n = count($notifData['list']);
			$notifsData[$i]['ids'] = array();
			for ($j=0;$j<$n;$j++)
				$notifsData[$i]['ids'][] = $notifData['list'][$j]['id'];
			$names = get_people_names($notifData);
			$namesJoined = join_people_names($names);
			switch ($notifData['type']) {
			case 'answer_forum' :
				$verb = ($language ? 'answered':((count($names)>1) ? 'ont répondu':'a répondu'));
				$notifsData[$i]['content'] = $namesJoined .' '. $verb .' '. ($notifData['mine'] ? ($language ? 'to your topic':'à votre topic') : ($language ? 'to the topic':'au topic')) .' <strong>'. htmlspecialchars($notifData['title']) .'</strong>';
				break;
			case 'forum_mention' :
				$verb = ($language ? 'mention':((count($names)>1) ? 'ont mentionné':'a mentionné'));
				$notifsData[$i]['content'] = $namesJoined .' '. $verb .' '. ($language ? 'your name in the topic':'votre pseudo dans le topic') .' <strong>'. htmlspecialchars($notifData['title']) .'</strong>';
				break;
			case 'forum_quote' :
				$verb = ($language ? 'quoted you':((count($names)>1) ? 'vous ont cité':'vous a cité'));
				$notifsData[$i]['content'] = $namesJoined .' '. $verb .' '. ($language ? 'in the topic':'sur le topic') .' <strong>'. htmlspecialchars($notifData['title']) .'</strong>';
				break;
			case 'reaction_topic' :
				$verb = ($language ? 'reacted':((count($names)>1) ? 'ont réagi':'a réagi'));
				$notifsData[$i]['content'] = $namesJoined .' '. $verb .' '. ($language ? 'to your message in the topic':'à votre message sur le topic') .' <strong>'. htmlspecialchars($notifData['title']) .'</strong>';
				break;
			case 'circuit_comment' :
				$verb = ($language ? 'commented':((count($names)>1) ? 'ont commenté':' a commenté'));
				$notifsData[$i]['content'] = $namesJoined .' '. $verb .' '. ($language ? 'your':'votre') .' '. $notifData['type_circuit'] .' <strong>'. decodeAndEscapeCircuitNames($notifData['title']) .'</strong>';
				break;
			case 'reaction_trackcom' :
				$verb = ($language ? 'reacted':((count($names)>1) ? 'ont réagi':' a réagi'));
				$notifsData[$i]['content'] = $namesJoined .' '. $verb .' '. ($language ? 'to your comment on':'à votre commentaire sur') .' '. $notifData['the_circuit'] . $notifData['type_circuit'] .' <strong>'. decodeAndEscapeCircuitNames($notifData['title']) .'</strong>';
				break;
			case 'news_comment' :
				$verb = ($language ? 'commented':((count($names)>1) ? 'ont commenté':' a commenté'));
				$notifsData[$i]['content'] = $namesJoined .' '. $verb .' '. ($language ? 'your':'votre') .' news <strong>'. htmlspecialchars($notifData['title']) .'</strong>';
				break;
			case 'news_moderated' :
				$newsTitle = htmlspecialchars($notifData['title']);
				$moderated = ($notifData['status']=='accepted') ? ($language?'accepted':'acceptée') : ($language?'rejected':'refusée');
				$notifsData[$i]['content'] = ($language ? "Your news <strong>$newsTitle</strong> has been $moderated":"Votre news <strong>$newsTitle</strong> a été $moderated");
				break;
			case 'reaction_news' :
				$verb = ($language ? 'reacted':((count($names)>1) ? 'ont réagi':'a réagi'));
				$thenews = ($language ? 'to your news':'à votre news');
				$notifsData[$i]['content'] = $namesJoined .' '. $verb .' '. $thenews .' <strong>'. htmlspecialchars($notifData['title']) .'</strong>';
				break;
			case 'challenge_moderated' :
				$clData = $notifData['challenge'];
				$clName = htmlspecialchars($clData['name']);
				if (!$clName) {
					require_once('utils-challenges.php');
					$clFulldata = getChallengeDetails($clData);
					$clName = $clFulldata['description']['main'];
					if (strlen($clName) >= 70)
						$clName = substr($clName, 0,67).'...';
				}
				$moderated = ($clData['status']=='active') ? ($language?'accepted':'accepté') : ($language?'rejected':'refusé');
				$notifsData[$i]['content'] = ($language ? "Your challenge <strong>$clName</strong> has been $moderated":"Votre défi <strong>$clName</strong> a été $moderated");
				break;
			case 'answer_comment' :
				$verb = ($language ? 'also commented':((count($names)>1) ? 'ont également commenté':'a également commenté'));
				$notifsData[$i]['content'] = $namesJoined .' '. $verb .' '. $notifData['the_circuit'] . $notifData['type_circuit'] .' <strong>'. decodeAndEscapeCircuitNames($notifData['title']) .'</strong>';
				break;
			case 'answer_newscom' :
				$verb = ($language ? 'also commented':((count($names)>1) ? 'ont également commenté':'a également commenté'));
				$thenews = ($language ? 'the news':'la news');
				$notifsData[$i]['content'] = $namesJoined .' '. $verb .' '. $thenews .' <strong>'. htmlspecialchars($notifData['title']) .'</strong>';
				break;
			case 'reaction_newscom' :
				$verb = ($language ? 'reacted':((count($names)>1) ? 'ont réagi':'a réagi'));
				$thenews = ($language ? 'to your comment in the news':'à votre commentaire sur la news');
				$notifsData[$i]['content'] = $namesJoined .' '. $verb .' '. $thenews .' <strong>'. htmlspecialchars($notifData['title']) .'</strong>';
				break;
			case 'follower_topic' :
				$notifsData[$i]['content'] = $namesJoined .' '. ($language ? 'published the topic':'a publié le topic') .' <strong>'. htmlspecialchars($notifData['title']) .'</strong>';
				break;
			case 'follower_news' :
				$notifsData[$i]['content'] = $namesJoined .' '. ($language ? 'published the news':'a publié la news') .' <strong>'. htmlspecialchars($notifData['title']) .'</strong>';
				break;
			case 'follower_circuit' :
				$notifsData[$i]['content'] = $namesJoined .' '. ($language ? 'published ':'a publié ') . $notifData['the_circuit'] .' <strong>'. decodeAndEscapeCircuitNames($notifData['title']) .'</strong>';
				break;
			case 'follower_challenge' :
				$clData = $notifData['challenge'];
				$clName = htmlspecialchars($clData['name']);
				if (!$clName) {
					require_once('utils-challenges.php');
					$clFulldata = getChallengeDetails($clData);
					$clName = $clFulldata['description']['main'];
					if (strlen($clName) >= 70)
						$clName = substr($clName, 0,67).'...';
				}
				$notifsData[$i]['content'] = $namesJoined .' '. ($language ? 'published the challenge ':'a publié le défi') .' <strong>'. $clName .'</strong>';
				break;
			case 'follower_perso' :
				$notifsData[$i]['content'] = $namesJoined .' '. ($language ? 'published the character':'a publié le perso') .' <strong>'. htmlspecialchars($notifData['title']) .'</strong>';
				break;
			case 'new_followuser' :
				$verb = ($language ? ((count($names)>1) ? 'follow you on MKPC':'now follows you on MKPC'):((count($names)>1) ? 'vous suivent sur MKPC':'vous suit sur MKPC'));
				$notifsData[$i]['content'] = $namesJoined .' '. $verb;
				break;
			case 'new_followtopic' :
				$verb = ($language ? ((count($names)>1) ? 'follow your topic':'follows your topic'):((count($names)>1) ? 'suivent votre topic':'suit votre topic')) .' <strong>'. htmlspecialchars($notifData['title']) .'</strong>';
				$notifsData[$i]['content'] = $namesJoined .' '. $verb;
				break;
			case 'currently_online' :
				$verb = $language ? ((count($names)>1) ? 'are':'is'):((count($names)>1) ? 'jouent':'joue');
				$additionnal = '';
				if (isset($notifData['creation'])) {
					$notifCreation = $notifData['creation'];
					if ($notifCreation['mcup'])
						$getName = mysql_fetch_array(mysql_query('SELECT nom FROM `mkmcups` WHERE id="'. $notifCreation['id'] .'"'));
					elseif ($notifCreation['single']) {
						if ($notifCreation['complete']) {
							if ($notifData['battle'])
								$getName = mysql_fetch_array(mysql_query('SELECT nom FROM `arenes` WHERE id="'. $notifCreation['id'] .'"'));
							else
								$getName = mysql_fetch_array(mysql_query('SELECT nom FROM `circuits` WHERE id="'. $notifCreation['id'] .'"'));
						}
						else
							$getName = mysql_fetch_array(mysql_query('SELECT nom FROM `mkcircuits` WHERE id="'. $notifCreation['id'] .'"'));
					}
					else
						$getName = mysql_fetch_array(mysql_query('SELECT nom FROM `mkcups` WHERE id="'. $notifCreation['id'] .'"'));
					$creationName = null;
					if ($getName)
						$creationName = $getName['nom'];
					$creationName = decodeAndEscapeCircuitNames($getName['nom']);
					$theCreation = '';
					if ($notifCreation['single']) {
						if ($notifData['battle'])
							$theCreation = $language?'the arena':'l\'arène';
						else
							$theCreation = $language?'the circuit':'le circuit';
					}
					else
						$theCreation = $language?'the cup':'la coupe';
					$additionnal = $language ? (' in '. $theCreation .' <strong>'. $creationName .'</strong>'):(' sur '. $theCreation .' <strong>'. $creationName .'</strong>');
				}
				elseif ($notifData['battle'])
					$additionnal = $language ? ' in <strong>battle</strong> mode':' en mode <strong>bataille</strong>';
				$notifsData[$i]['content'] = $namesJoined .' '. $verb .' '. ($language ? 'currently playing online':'actuellement en ligne') . $additionnal;
				break;
			case 'new_record':
				include_once('circuitNames.php');
				$verb = ($language ? 'broke':((count($names)>1) ? 'ont battu':' a battu'));
				$notifsData[$i]['content'] = $namesJoined .' '. $verb .' '. ($language ? 'your record on the circuit':'votre record sur le circuit') .' <strong>'. $circuitNames[$notifData['record']['circuit']-1] .'</strong>';
				break;
			}
		}
		if ($notifsCacheKey) {
			if ($useNotifCache) {
				$nbNotifs = $myNotifsCache[0];
				foreach ($notifsData as $notifData) {
					$nbNotifs++;
					if ($notifData['id'] <= $cacheNotifId)
						break;
				}
			}
			else {
				if (count($notifsData) >= $relyOnCacheThreshold) {
					$newCacheId = $notifsData[$relyOnCacheThreshold-1]['id'];
					$nbNotifsCache = $nbNotifs-$relyOnCacheThreshold;
					apcu_store($notifsCacheKey, "$nbNotifsCache:$newCacheId", min(round(60000+$nbNotifsCache*100),500000));
				}
				else
					clearNotifCache();
			}
		}
		?>
		<div id="notifs-bubble" class="<?php echo $nbNotifs ? 'notifs':'no-notifs'; ?>">
			<div id="notifs-nb-alert">
				<?php echo $nbNotifs; ?>
			</div>
			<div class="notifs-container">
				<div id="no-notif">
					<?php echo $language ? 'No notifications':'Aucune notification'; ?>
					<?php if ($id) echo '<a href="notif-settings.php"><img src="images/notif-settings.png" alt="Settings" title="'. ($language ? 'Notification settings':'Paramètres de notifications') .'" /></a>'; ?>
				</div>
				<div id="nb-notifs">
					<strong><?php echo $nbNotifs; ?></strong> notification<?php echo ($nbNotifs>1 ? 's':''); ?>
					<?php if ($id) echo '<a href="notif-settings.php"><img src="images/notif-settings.png" alt="Settings" title="'. ($language ? 'Notification settings':'Paramètres de notifications') .'" /></a>'; ?>
				</div>
				<div id="notifs-list">
				<?php
				foreach ($notifsData as $i=>$notifData) {
					if ($i >= $relyOnCacheThreshold)
						break;
					$link = preg_replace('#(?:&amp;|\?)src=\w+$#', '', $notifData['link']);
					?>
					<a class="notif-container" id="notif-<?php echo $notifData['id']; ?>"  data-id="<?php echo $notifData['id']; ?>" data-ids="<?php echo implode(',',$notifData['ids']); ?>" href="<?php echo $link; ?>">
						<div class="notif-options"><span class="close-notif" onclick="closeNotif(event,this);">&times;</span></div>
						<div class="notif-value"><?php echo $notifData['content']; ?></div>
					</a>
					<?php
				}
				?>
				</div>
				<div id="notifs-options">
					<input type="button" value="<?php echo $language ? 'Mark everything as read':'Tout marquer comme lu'; ?>" onclick="closeNotifs()" />
				</div>
			</div>
		</div>
	</div>
	<div id="menu_center" role="menubar">
		<a href="index.php"<?php if ($page=='home') echo ' id="thispage"'; ?> role="menuitem"><?php echo $language ? 'Home':'Accueil'; ?></a>
		<a href="mariokart.php"<?php if ($page=='game') echo ' id="thispage"'; ?> role="menuitem"><?php echo $language ? 'Play game':'Le jeu'; ?></a>
		<a href="forum.php"<?php if ($page=='forum') echo ' id="thispage"'; ?> role="menuitem">Forum</a>
	</div>
</nav>