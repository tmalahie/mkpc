<?php
if (isset($_GET['pseudo'])) {
	include('initdb.php');
	if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM mkjoueurs WHERE nom="'. $_GET['pseudo'] .'"')))
		$profileId = $getId['id'];
	elseif ($getId = mysql_fetch_array(mysql_query('SELECT id FROM mknewnicks WHERE oldnick="'. $_GET['pseudo'] .'"'))) {
		$gotOldNick = $_GET['pseudo'];
		$profileId = $getId['id'];
	}
	else {
		include('session.php');
		include('language.php');
		echo $language ? 'This nick does not exist':'Ce pseudo n\'existe pas';
		mysql_close();
	}
	unset($_GET['pseudo']);
}
elseif (isset($_GET['id'])) {
	include('initdb.php');
	$profileId = intval($_GET['id']);
}
if (isset($profileId)) {
	include('session.php');
	$me = ($id == $profileId);
	include('language.php');
	include('avatars.php');
	if ($id != $profileId) {
		if (isset($_GET['unignore'])) {
			mysql_query('DELETE FROM `mkignores` WHERE ignorer="'. $id .'" AND ignored="'. $profileId .'"');
			$unignored = true;
		}
		elseif (isset($_GET['ignore']))
			mysql_query('INSERT IGNORE INTO `mkignores` SET ignorer="'. $id .'",ignored="'. $profileId .'"');
	}
	if ($getInfos = mysql_fetch_array(mysql_query('SELECT nom,pts_vs,pts_battle,pts_challenge,banned,deleted FROM `mkjoueurs` WHERE id="'. $profileId .'"'))) {
		if ($getInfos['banned'])
			$error = $language ? 'This account has been banned.':'Ce compte a été banni.';
		elseif (mysql_fetch_array(mysql_query('SELECT * FROM `mkignores` WHERE ignorer="'.$id.'" AND ignored="'.$profileId.'"')))
			$error = $language ? 'You are ignoring '. $getInfos['nom'] .'. You won\'t see his messages in MKPC chat nor in online mode. <a href="?id='. urlencode($profileId) .'&amp;unignore=1">Unignore</a>':'Vous venez d\'ignorer '. $getInfos['nom'] .'. Vous ne verrez plus ses messages dans le chat MKPC ni dans le mode en ligne. <a href="?id='. urlencode($profileId) .'&amp;unignore=1">Désignorer</a>';
		$getProfile = mysql_fetch_array(mysql_query('SELECT identifiant,identifiant2,identifiant3,identifiant4,nbmessages,description,country,birthdate,sub_date,NULLIF(DATE(last_connect),0) AS last_connect FROM `mkprofiles` WHERE id="'. $profileId .'"'));
		if ($getProfile['identifiant'] === null)
			$getProfile['identifiant'] = -1;
		require_once('getRights.php');
		$userRights = getUserRights($profileId);
		$isModerator = hasRight('moderator');
		if ($id == $profileId) {
			if (isset($_FILES['avatar'])) {
				if (!$_FILES['avatar']['error']) {
					$poids = $_FILES['avatar']['size'];
					if ($poids < 2000000) {
						$uploadSrc = $_FILES['avatar']['tmp_name'];
						list($w,$h) = getimagesize($uploadSrc);
						if ($w*$h < 4000000) {
							$imageType = exif_imagetype($uploadSrc);
							$exts = array(1 => 'gif', 2 => 'jpg', 3 => 'png');
							if (isset($exts[$imageType])) {
								$ext = $exts[$imageType];
								function resize_img($original_src,$thumb_src, $minw,$minh, $ext) {
									list($width, $height) = getimagesize($original_src);
									if ($width*$minh > $height*$minw) {
										$newHeight = $minh;
										$newWidth = round($minh*$width/$height);
									}
									else {
										$newWidth = $minw;
										$newHeight = round($minw*$height/$width);
									}
									if (($newWidth > $width) || ($newHeight > $height)) {
										$newWidth = $width;
										$newHeight = $height;
									}
									$thumb = imagecreatetruecolor($newWidth,$newHeight);

									switch ($ext) {
									case 'gif':
										$source = imagecreatefromgif($original_src);
										break;
									case 'jpg':
										$source = imagecreatefromjpeg($original_src);
										break;
									case 'png':
										$source = imagecreatefrompng($original_src);
										break;
									default :
										return;
									}

									imagealphablending($thumb, false);
									imagesavealpha($thumb,true);
									$transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
									imagefilledrectangle($thumb, 0, 0, $newWidth,$newWidth, $transparent);
									imagecopyresampled($thumb, $source, 0,0, 0,0, $newWidth,$newHeight, $width, $height);

									imagepng($thumb, $thumb_src);
									imagedestroy($thumb);
									imagedestroy($source);
								}
								$avatarName = $getInfos['nom'].uniqid().'.'.$ext;
								$oldAvatar = get_avatar_img($id);
								if ($oldAvatar) {
									@unlink(AVATAR_DIR.$oldAvatar['ld']);
									@unlink(AVATAR_DIR.$oldAvatar['hd']);
								}
								move_uploaded_file($uploadSrc, AVATAR_DIR.$avatarName);
								resize_img(AVATAR_DIR.$avatarName,AVATAR_DIR.to_ld($avatarName), AVATAR_MINW,AVATAR_MINH, $ext);
								mysql_query('UPDATE `mkprofiles` SET avatar="'. $avatarName .'" WHERE id="'. $id .'"');
								clear_avatar_cache($id);
							}
							else
								$error = $language ? 'Your image must be in the png, jpg or gif format' : 'Votre image doit être au format png, jpg ou gif';
						}
						else
							$error = $language ? 'Your image musn\'t exceed 2000×2000 in dimension':'Votre image ne doit pas dépasser 1732×1732px';
					}
					else
						$error = $language ? 'Your image musn\'t exceed 2 MB.':'Votre image ne doit pas dépasser 2 Mo.';
				}
			}
		}
		function plural($nb) {
			return ($nb >= 2) ? 's':'';
		}
		function toUtf8($str) {
			return $str;
		}
		function controlLength($str,$maxLength) {
			$pts = '...';
			if (mb_strlen($str) > $maxLength)
				return mb_substr($str,0,$maxLength-mb_strlen($pts)).$pts;
			return $str;
		}
		function controlLengthUtf8($str,$len) {
			return toUtf8(controlLength($str,$len));
		}
		function toPlace($place) {
			global $language;
			$res = $place .'<sup>';
			if ($language) {
				$centaines = $place%100;
				if (($centaines >= 10) && ($centaines < 20))
					$res .= 'th';
				else {
					switch ($place%10) {
					case 1 :
						$res .= 'st';
						break;
					case 2 :
						$res .= 'nd';
						break;
					case 3 :
						$res .= 'rd';
						break;
					default :
						$res .= 'th';
					}
				}
			}
			else
				$res .= 'e'. ($place>1 ? null:'r');
			$res .= '</sup>';
			return $res;
		}
		require_once('circuitEscape.php');
		function escapeUtf8($str) {
			return htmlspecialchars(escapeCircuitNames($str));
		}
		if (isset($_GET['followed'])) {
			if ($_GET['followed'])
				$success = $language ? 'You are now following '. $getInfos['nom'] .'! You will receive a notification each time they post a topic, news, a circuit or a character.':'Vous suivez maintenant '. $getInfos['nom'] .' ! Vous recevrez une notification chaque fois qu\'il poste un topic, une news, un circuit ou un perso.';
			else
				$success = $language ? 'You have stopped following '. $getInfos['nom']:'Vous ne suivez plus '. $getInfos['nom'];
		}
		elseif (isset($unignored)) {
			$success = $language ? 'You have stopped ignoring '. $getInfos['nom'] .'. <a href="?id='. urlencode($profileId) .'&amp;ignore=1">Reignore</a>':'Vous avez désignoré '. $getInfos['nom'] .'. <a href="?id='. urlencode($profileId) .'&amp;ignore=1">Réignorer</a>';
			$successCenter = true;
		}
		include('bbCode.php');
		?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? $getInfos['nom'].'\'s profile':'Profil de '. $getInfos['nom']; ?> - Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/challenge-creations.css" />
<link rel="stylesheet" type="text/css" href="styles/profil.css" />

<script type="text/javascript" src="scripts/topic.js"></script>
<script type="text/javascript" src="scripts/profile.js"></script>
<script type="text/javascript" src="scripts/posticons.js"></script>

<?php
include('o_online.php');
?>
<script type="text/javascript">
var loadingMsg = "<?php echo $language ? 'Loading':'Chargement'; ?>";
</script>
<script type="text/javascript" src="scripts/creations.js"></script>
</head>
<body>
<?php
include('header.php');
$page = 'forum';
include('menu.php');
?>
<main>
	<?php
	if (!$getInfos['deleted']) {
	if (isset($success))
		echo '<div class="success"'. (isset($successCenter) ? ' style="text-align:center"':'') .'>'. $success .'</div>';
	elseif (isset($error))
		echo '<div class="warning">'. $error .'</div>';
	?>
	<div class="profile-container">
		<div class="profile-summary">
			<h1><?php echo $language ? $getInfos['nom'].'\'s profile' : 'Profil de '. $getInfos['nom']; ?></h1>
			<?php
			$getLastNicks = mysql_query('SELECT oldnick,date FROM mknewnicks WHERE id="'. $profileId .'" ORDER BY date DESC');
			$dateByNick = array();
			$oldNicks = array();
			$nickDate = time();
			$maxDT = $isModerator ? 300 : 86400;
			while ($nick = mysql_fetch_array($getLastNicks)) {
				$newDate = strtotime($nick['date']);
				$dt = $nickDate-$newDate;
				$nickDate = $newDate;
				if ($dt < $maxDT)
					array_pop($oldNicks);
				$nickName = $nick['oldnick'];
				$oldNicks[] = $nickName;
				if (!isset($dateByNick[$nickName]))
					$dateByNick[$nickName] = $newDate;
			}
			if (isset($gotOldNick)) array_unshift($oldNicks,$gotOldNick);
			$limitDate = time()-86400*14;
			foreach ($oldNicks as $i => $nickName) {
				if (isset($dateByNick[$nickName]) && ($dateByNick[$nickName] < $limitDate))
					unset($oldNicks[$i]);
			}
			$oldNicks = array_values(array_unique($oldNicks));
			$currentNickKey = array_search($getInfos['nom'], $oldNicks);
			if ($currentNickKey !== false)
				array_splice($oldNicks, $currentNickKey,1);
			$nbNicks = count($oldNicks);
			if ($nbNicks) {
				//$oldNicks = array($oldNicks[$nbNicks-1]);
				//$nbNicks = 1;
				$s = ($nbNicks>=2) ? 's':'';
				?>
				<div class="last-nick"><?php
				echo $language ? 'Last nick'.$s.':' : 'Ancien'.$s.' pseudo'.$s.' :';
				echo ' ';
				echo implode(', ', $oldNicks);
				?></div>
				<?php	
			}
			?>
			<div class="avatar-container">
				<?php print_avatar($profileId,AVATAR_M); ?>
				<?php
				$avatarSrc = get_avatar_img($profileId);
				if ($me || $isModerator) {
					if ($me) {
						echo '<form method="post" enctype="multipart/form-data" action="profil.php?id='. $id .'" class="avatar-edit'. ($avatarSrc ? ' preview-avatar':'') .'"'. ($avatarSrc ? ' onclick="apercu(\''. AVATAR_DIR.$avatarSrc['hd'] .'\')"':'') .'>';
						echo '<label for="editAvatar" class="edit" onclick="event.stopPropagation()">
						'. ($language ? 'Edit':'Modifier') .'
						<input type="file" name="avatar" id="editAvatar" onchange="this.form.submit()" />
						</label>';
					}
					else
						echo '<form class="'. ($avatarSrc ? 'avatar-edit preview-avatar':'') .'"'. ($avatarSrc ? ' onclick="apercu(\''. AVATAR_DIR.$avatarSrc['hd'] .'\')"':'') .'>';
					if ($avatarSrc) {
						if ($me)
							$warningMsg = ($language ? 'Delete your avatar?':'Supprimer votre avatar ?');
						else
							$warningMsg = ($language ? 'Delete '. $getInfos['nom'] .'\\\'s avatar?':'Supprimer l\\\'avatar de '. $getInfos['nom'] .' ?');
						echo '<br /><a href="delAvatar.php'. ($me ? '':'?id='.$profileId) .'" class="suppr" onclick="event.stopPropagation();return confirm(\''. $warningMsg .'\')">'. ($language ? 'Delete':'Supprimer') .'</a>';
					}
					echo '</form>';
				}
				else {
					if ($avatarSrc) {
						?>
						<div class="preview-avatar" onclick="apercu('<?php echo AVATAR_DIR.$avatarSrc['hd']; ?>')">
						</div>
						<?php
					}
				}
				?>
			</div>
			<div class="infos-container">
				<br />
				<h2><?php echo $language ? 'General stats':'Stats générales'; ?></h2>
				<div class="player-followers">
				<?php
				$followedUsers = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb, '.($id?'SUM(follower='.$id.')':'0').' AS userisfollower FROM `mkfollowusers` WHERE followed="'. $profileId .'"'));
				$s = plural($followedUsers['nb']);
				$view = $language ? 'View' : 'Voir';
				echo '<img src="images/followers.png" alt="Followers" />';
				echo '<strong>'. $followedUsers['nb'] . ' '. ($language ? 'follower'.$s : 'abonné'.$s) .'</strong>';
				if ($me) {
					if ($followedUsers['nb'])
						echo ' <a class="all-follows" href="listFollowers.php">['.$view.']</a>';
				}
				elseif ($id) {
					$isFollower = $followedUsers['userisfollower'];
					echo ' <a class="follow-user'. ($isFollower ? ' followed':'') .'" href="follow-user.php?user='. $profileId . ($isFollower?'':'&amp;follow') .'"><span>'.($isFollower?'&ndash;':'+').'</span>'. ($language ? ($isFollower?'Unfollow':'Follow'):($isFollower?'Ne plus suivre':'Suivre')) .'</a></span>';
				}
				?>
				</div>
				<div class="player-followers">
				<?php
				$followingUsers = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkfollowusers` WHERE follower="'. $profileId .'"'));
				$s = plural($followingUsers['nb']);
				echo '<img src="images/followed.png" alt="Following" />';
				echo '<strong>'. $followingUsers['nb'] . ' '. ($language ? 'following' : 'abonnement'.$s) .'</strong>';
				if ($me) {
					if ($followingUsers['nb'])
						echo ' <a class="all-follows" href="listFollowed.php">['.$view.']</a>';
				}
				?>
				</div>
				<?php
				$pts = $getInfos['pts_vs'];
				$place = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS cnt FROM `mkjoueurs` WHERE (pts_vs!=5000) AND (pts_vs>"'. $pts .'" OR (pts_vs="'. $pts .'" AND id<"'. $profileId .'")) AND deleted=0'));
				$place = 1+$place['cnt'];
				echo '<div class="player-league">';
					echo '<img src="images/vs_pts.png" alt="VS" />';
					echo '<strong>'. $pts . ' pts</strong> ';
					echo '- <strong style="color:'.get_league_color($pts).'">'.get_league_name($pts).'</strong><sup><a href="javascript:helpLeagues()">[?]</a></sup> ';
					echo '- '. toPlace($place);
				echo '</div>';
				$pts = $getInfos['pts_battle'];
				$place = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS cnt FROM `mkjoueurs` j WHERE (j.pts_battle!=5000) AND (j.pts_battle>"'. $pts .'" OR (j.pts_battle="'. $pts .'" AND j.id<"'. $profileId .'")) AND j.deleted=0'));
				$place = 1+$place['cnt'];
				echo '<div class="player-league">';
					echo '<img src="images/battle_pts.png" alt="Battle" />';
					echo '<strong>'. $pts . ' pts</strong> ';
					echo '- <strong style="color:'.get_league_color($pts).'">'.get_league_name($pts).'</strong><sup><a href="javascript:helpLeagues()">[?]</a></sup> ';
					echo '- '. toPlace($place);
				echo '</div>';
				$pts = $getInfos['pts_challenge'];
				$place = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS cnt FROM `mkjoueurs` j WHERE (j.pts_challenge!=0) AND (j.pts_challenge>"'. $pts .'" OR (j.pts_challenge="'. $pts .'" AND j.id<"'. $profileId .'")) AND j.deleted=0'));
				$place = 1+$place['cnt'];
				$whereIP = 'identifiant="'.$getProfile['identifiant'].'" AND identifiant2='.$getProfile['identifiant2'].' AND identifiant3='.$getProfile['identifiant3'].' AND identifiant4='.$getProfile['identifiant4'].'';
				$nbClSucceess = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkclwin` WHERE player="'. $profileId .'" AND creator=0'));
				$nbClCreate = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkclrace` l INNER JOIN `mkchallenges` c ON c.clist=l.id WHERE '. $whereIP .' AND c.status="active"'));
				if ($pts) {
					echo '<div class="player-league">';
						echo '<img src="images/cups/cup1.png" alt="Challenges" />';
						$s = ($pts>=2) ? 's':'';
						if ($language)
							echo '<strong>'. $pts . ' pt'. $s .'</strong>';
						else
							echo '<strong>'. $pts . ' pt'. $s .'</strong>';
						if ($nbClSucceess['nb'] || !$nbClCreate['nb']) {
							$s = ($nbClSucceess['nb']>=2) ? 's':'';
							echo ' - <strong>'. $nbClSucceess['nb'] .' '. ($language ? 'challenge':'défi') . $s .'</strong> '. ($language ? 'won':'réussi'.$s);
						}
						else {
							$s = ($nbClCreate['nb']>=2) ? 's':'';
							echo ' - <strong>'. $nbClCreate['nb'] .' '. ($language ? 'challenge':'défi') . $s .'</strong> '. ($language ? 'created':'créé'.$s);
						}
						echo ' - '. toPlace($place);
					echo '</div>';
				}
				$getRecordsByCc = mysql_query('SELECT class,COUNT(*) AS nb FROM `mkrecords` WHERE player="'. $profileId .'" AND type="" AND best=1 GROUP BY class ORDER BY class');
				while ($recordByCc = mysql_fetch_array($getRecordsByCc)) {
					$cc = $recordByCc['class'];
					if ($getTtRank = mysql_fetch_array(mysql_query('SELECT t.class,t.score,1+COUNT(j.id) AS rank FROM mkttranking t LEFT JOIN mkttranking t2 ON t.class=t2.class AND (t.score<t2.score OR (t.score=t2.score AND t.player>t2.player)) LEFT JOIN mkjoueurs j ON t2.player=j.id AND j.deleted=0 WHERE t.player='. $profileId .' AND t.class='. $cc))) {
						echo '<div class="player-league">';
						echo '<img src="images/records.png" alt="Time trial" />';
						echo $cc . ($language ? 'cc:':'cc :');
						echo ' <strong>'. $getTtRank['score'] . ' pt'. plural($getTtRank['score']) .'</strong>';
						echo ' - <strong>'. $recordByCc['nb'] . ' record'. plural($recordByCc['nb']) .'</strong>';
						echo ' - '. toPlace($getTtRank['rank']);
						echo '</div>';
					}
				}
				$rkname = get_forum_rkname($getProfile['nbmessages']);
				$rkimg = get_forum_rkimg($getProfile['nbmessages']);
				echo '<div class="player-rank">';
					echo '<img src="images/messages.png" alt="Forum messages" title="Forum" />';
					echo '<strong>'. $getProfile['nbmessages'] . ' message'. ($getProfile['nbmessages']>=2?'s':'') .'</strong> ';
					echo ' - <span><img src="images/ranks/'. $rkimg .'.gif" alt="'. $rkname .'" class="mNbmsgsRk" /></span>';
					echo $rkname.'</strong><sup><a href="javascript:helpRanks()">[?]</a></sup>';
				echo '</div>';
				$getNews = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mknews` WHERE author="'. $profileId .'" AND status="accepted"'));
				if ($getNews['nb']) {
					?>
					<div><?php
					echo '<strong>'. $getNews['nb'] . ' ' . ($language ? 'news</strong> published' : 'news</strong> publiée'.plural($getNews['nb']));
					?></div>
					<?php
				}
				?>
				<div>
					<?php
					$mkCircuits = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkcircuits` WHERE '.$whereIP));
					$circuits = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `circuits` WHERE nom IS NOT NULL AND '.$whereIP));
					$arenes = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `arenes` WHERE nom IS NOT NULL AND '.$whereIP));
					$nbCircuits = $mkCircuits['nb'] + $circuits['nb'] + $arenes['nb'];
					echo '<strong>'. $nbCircuits . ' '. ($language ? 'created </strong> circuit'. plural($nbCircuits):'circuit'.plural($nbCircuits) .'</strong> créé'. plural($nbCircuits));
					$mkCups = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkcups` WHERE '. $whereIP));
					$nbCups = $mkCups['nb'];
					echo ' - <strong>'. $nbCups . ' '. ($language ? ' cup'. plural($nbCups).'</strong>':'coupe'. plural($nbCups) .'</strong>');
					?>
				</div>
				<?php
				if ($nbClSucceess['nb'] && $nbClCreate['nb']) {
					echo '<div>
						<strong>'. $nbClCreate['nb'] . ' '. ($language ? 'challenge'. plural($nbClCreate['nb']) .'</strong> created':'défi'.plural($nbClCreate['nb']) .'</strong> créé'. plural($nbClCreate['nb'])) .
					'</div>';
				}
				?>
				<div>
					<?php
					$mkPersos = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkchars` WHERE '.$whereIP.' AND author IS NOT NULL'));
					$nbPersos = $mkPersos['nb'];
					echo '<strong>'. $nbPersos . ' '. ($language ? 'shared </strong> character'. plural($nbPersos):'perso'.plural($nbPersos) .'</strong> partagé'. plural($nbPersos));
					?>
				</div>
				<div>
					<?php
					$lastComments = mysql_query('SELECT id,circuit,type,message,date FROM `mkcomments` WHERE auteur="'. $profileId .'" ORDER BY id DESC');
					$nbComments = mysql_numrows($lastComments);
					$displayedComments = 0;
					$comments = array();
					while ($comment = mysql_fetch_array($lastComments)) {
						if ($getCircuit = mysql_fetch_array(mysql_query('SELECT *'. (($comment['type']=="mkcircuits") ? ',!type as is_circuit':'') .' FROM `'. $comment['type'] .'` WHERE id='. $comment['circuit']))) {
							$comment['circuit_data'] = $getCircuit;
							$comments[] = $comment;
							$displayedComments++;
							if ($displayedComments >= 3)
								break;
						}
						else
							$nbComments--;
					}
					echo '<strong>'. $nbComments . ' '. ($language ? 'comment'.plural($nbComments) .'</strong> on circuits' : 'commentaire'.plural($nbComments) .'</strong> sur les circuits');
					?>
				</div>
				<div>
					<?php
					$followedTopics = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkfollowers` INNER JOIN `mktopics` ON topic=id WHERE user="'. $profileId .'" AND NOT EXISTS(SELECT * FROM mkmessages WHERE id=1 AND mkfollowers.topic=mkmessages.topic AND user=auteur)'));
					$s = plural($followedTopics['nb']);
					echo '<strong>'. $followedTopics['nb'] . ' '. ($language ? 'topic'.$s .'</strong> followed' : 'topic'.$s .'</strong> suivi'. $s);
					?>
				</div>
			</div>
			<div class="persos-container">
				<br />
				<h2 class="no-underline"><u><?php echo $language ? 'About':'Infos persos'; ?></u><?php
				if ($me)
					echo ' <a href="edit-profile.php">['. ($language ? 'Edit':'Modifier') .']</a></span>';
				?></h2>
				<?php
				$oneData = false;
				if ($getProfile['description']) {
					$oneData = true;
					?>
					<div class="description-container">
						<h3>Description :</h3>
						<div class="profile-description">
							<?php echo bbcode($getProfile['description']); ?>
						</div>
					</div>
					<?php
				}
				if ($getProfile['country']) {
					if ($countryData = mysql_fetch_array(mysql_query('SELECT code,name_'. ($language ? 'en':'fr') .' AS name FROM mkcountries WHERE id='.$getProfile['country']))) {
						?>
						<div>
							<div class="country-ic" style="background-image:url('images/flags/<?php echo $countryData['code'] ?>.png')"></div>
							<?php echo htmlspecialchars($countryData['name']); ?>
						</div>
						<?php
					}
				}
				if ($getProfile['birthdate']) {
					$oneData = true;
					function getAge($date) {
						date_default_timezone_set('Europe/Paris');
						$ts = strtotime($date);
						$y0 = date('Y',$ts);
						$m0 = date('m',$ts);
						$d0 = date('d',$ts);
						$y1 = date('Y');
						$m1 = date('m');
						$d1 = date('d');
						$res = $y1-$y0;
						if ($m1 < $m0 || (($m1 == $m0) && ($d1 < $d0)))
							$res--;
						return $res;
					}
					$age = getAge($getProfile['birthdate']);
					?>
					<div>
						<strong><?php
						echo $age . ' ' . ($language ? 'years old':'ans');
						?></strong>
						 (<?php echo ($language ? 'Born on':'Né le') . ' ' . preg_replace("#^(\d{4})-(\d{2})-(\d{2})$#", "$3/$2/$1", $getProfile['birthdate']); ?>)
					</div>
					<?php
				}
				if ($getProfile['sub_date']) {
					$oneData = true;
					?>
					<div>
						<?php echo ($language ? 'Registered since':'Inscrit depuis le'); ?> <strong><?php echo preg_replace("#^(\d{4})-(\d{2})-(\d{2})$#", "$3/$2/$1", $getProfile['sub_date']); ?></strong>
					</div>
					<?php
				}
				if ($getProfile['last_connect']) {
					$oneData = true;
					?>
					<div>
						<?php echo ($language ? 'Last connection':'Dernière connexion '); ?>: <strong><?php echo preg_replace("#^(\d{4})-(\d{2})-(\d{2})$#", "$3/$2/$1", $getProfile['last_connect']); ?></strong>
					</div>
					<?php
				}
				if (isset($userRights['admin'])) {
					$oneData = true;
					?>
					<div>
						<strong><?php echo ($language ? 'Administrator':'Administrateur'); ?></strong> <?php echo $language ? 'of the site':'du site'; ?>
					</div>
					<?php
				}
				elseif (isset($userRights['moderator'])) {
					$oneData = true;
					?>
					<div>
						<strong><?php echo ($language ? 'Moderator':'Modérateur'); ?></strong> <?php echo $language ? 'of the site':'du site'; ?>
					</div>
					<?php
				}
				elseif (isset($userRights['organizer'])) {
					$oneData = true;
					?>
					<div>
						<strong><?php echo ($language ? 'Event host':'Animateur'); ?></strong> <?php echo $language ? 'of the site':'du site'; ?>
					</div>
					<?php
				}
				if (isset($userRights['publisher'])) {
					$oneData = true;
					?>
					<div>
						<strong><?php echo ($language ? 'News':'Rédacteur'); ?></strong> <?php echo $language ? 'publisher':'de news'; ?>
					</div>
					<?php
				}
				if (isset($userRights['clvalidator'])) {
					$oneData = true;
					?>
					<div>
						<strong><?php echo ($language ? 'Validator':'Validateur'); ?></strong> <?php echo $language ? 'of challenges':'de défis'; ?>
					</div>
					<?php
				}
				$getAwards = mysql_query('SELECT a.name,a.link,p.value FROM mkawarded p INNER JOIN mkawards a ON p.award=a.id WHERE p.user="'. $profileId .'" ORDER BY a.ordering DESC');
				while ($award = mysql_fetch_array($getAwards)) {
					$oneData = true;
					?>
					<div>
						<strong><?php echo htmlspecialchars($award['name']); ?></strong><?php
						if ($award['link'])
							echo '<sup><a href="'. htmlspecialchars($award['link']) .'">[?]</a></sup>';
						?> : <?php
						echo htmlspecialchars($award['value']);
						?>
					</div>
					<?php
				}
				if (!$oneData)
					echo '<em class="no-persos">'. ($language ? 'No personnal info entered':'Aucune info perso renseignée') .'</em>';
				?>
				<?php
				if ($me) {
					?>
					<br />
					<h2>Options</h2>
					<ul>
						<li><a href="edit-profile.php"><?php echo $language ? 'Edit personal info':'Modifier mes infos persos'; ?></a></li>
						<li><a href="edit-nick.php"><?php echo $language ? 'Change your nickname':'Modifier mon pseudo'; ?></a></li>
						<li><a href="nick-color.php"><?php echo $language ? 'Edit nick color':'Modifier la couleur du pseudo'; ?></a></li>
						<li><a href="password.php"><?php echo $language ? 'Change password':'Modifier mot de passe'; ?></a></li>
						<li><a href="signout.php"><?php echo $language ? 'Delete account':'Supprimer compte'; ?></a></li>
					</ul>
					<?php
				}
				elseif ($id) {
					?>
					<br />
					<h2>Options</h2>
					<ul>
						<?php
						if (mysql_fetch_array(mysql_query('SELECT * FROM `mkignores` WHERE ignorer="'. $id .'" AND ignored="'. $profileId .'"')))
							echo '<li><a href="?id='. $profileId .'&amp;unignore">'. ($language ? 'Stop ignoring ' . $getInfos['nom']:'Ne plus ignorer ' . $getInfos['nom']) .'</a></li>';
						else
							echo '<li><a href="?id='. $profileId .'&amp;ignore">'. ($language ? 'Ignore ' . $getInfos['nom']:'Ignorer ' . $getInfos['nom']) .'</a></li>';
						if ($isModerator)
							echo '<li><a href="edit-profile.php?member='. $profileId .'">'. ($language ? 'Edit profile':'Modifier le profil') .'</a></li>';
						?>
					</ul>
					<?php
				}
				?>
			</div>
			<p class="forumButtons profile-back">
				&lt; <a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a>
			</p>
		</div>
		<div class="profile-feed">
			<?php
			$getLastMessages = mysql_query(
				'SELECT mkmessages.id,titre,auteur,topic,message,date
				FROM `mkmessages` INNER JOIN `mktopics` ON mkmessages.topic=mktopics.id WHERE auteur="'. $profileId .'"'. (hasRight('manager') ? '':' AND !private') .' ORDER BY date DESC LIMIT 3'
			);
			$lastMessages = array();
			while ($message = mysql_fetch_array($getLastMessages)) {
				$message['infosDate'] = ' '. ($language ? 'in':'dans') . ' <a href="topic.php?topic='. $message['topic'] .'&message='. $message['id'] .'" title="'. $message['titre'] .'">'. controlLength($message['titre'], 35) .'</a>';
				$lastMessages[] = $message;
			}
			if (!empty($lastMessages)) {
				?>
				<h2><?php echo $language ? 'Last messages on the forum':'Derniers messages sur le forum'; ?>&nbsp;:</h2>
				<?php
				require_once('reactions.php');
				printReactionUI();
				populateReactionsData('topic', $lastMessages);
				foreach ($lastMessages as $message)
					print_forum_msg($message,false);
				?>
				<h3><a href="forum-search.php?author=<?php echo $getInfos['nom']; ?>#search-results"><?php echo $language ? 'See all their messages':'Voir tous ses messages'; ?></a></h3>
				<?php
			}
			else
				echo '<h2><em>'. ($language ? 'No message on the forum':'Aucun message sur le forum') .'</em></h2>';
			?>
			<hr />
			<?php
			include('utils-circuits.php');
			$nbDisplayedCircuits = 5;
			$nbCircuitTypes = count($aCircuits);
			$aParams = array(
				'pids' => array($getProfile['identifiant'],$getProfile['identifiant2'],$getProfile['identifiant3'],$getProfile['identifiant4']),
				'tri' => 1,
				'max_circuits' => $nbCircuitTypes*$nbDisplayedCircuits
			);
			$nbTracksByType = array_fill(0,$nbCircuitTypes, $nbDisplayedCircuits);
			$bestCircuits = listCreations(1,$nbTracksByType,null,$aCircuits,$aParams);
			if (!empty($bestCircuits)) {
				$bestCircuits = array_slice($bestCircuits, 0,$nbDisplayedCircuits);
				?>
				<h2><?php echo $language ? 'Best created circuits':'Meilleurs circuits créés'; ?>&nbsp;:</h2>
				<?php
				echo '<table class="profile-circuits">';
				echo '<tr>';
				for ($i=0;$i<$nbDisplayedCircuits;$i++) {
					if (isset($bestCircuits[$i])) {
						$circuit = $bestCircuits[$i];
						$isCup = (strpos($circuit['cicon'], ',') !== false);
						echo '<td';
						if (isset($circuit['icon'])) {
							$allMapSrcs = $circuit['icon'];
							foreach ($allMapSrcs as $j=>$jMapSrc)
								$allMapSrcs[$j] = "url('images/creation_icons/$jMapSrc')";
							echo ' style="background-image:'.implode(',',$allMapSrcs).'"';
						}
						else
							echo ' data-cicon="'.$circuit['cicon'].'"';
						echo ' class="creation-td '. ($isCup ? 'cup-td':'circuit-td') .'">';
							echo '<a href="'. $circuit['href'] .'">';
							if ($circuit['nom']) {
								echo '<div class="circuit-name">';
								echo escapeUtf8(controlLengthUtf8($circuit['nom'],30));
								echo '</div>';
							}
							$note = $circuit['note'];
							$nbNotes = $circuit['nbnotes'];
							$noteTitle = $nbNotes ? (round($note*100)/100).'/5 '. ($language ? 'on':'sur') .' '. $nbNotes .' vote'. ($nbNotes>1 ? 's':'') : ($language ? 'Unrated':'Non noté');
							echo '<div class="circuit-rate" title="'. $noteTitle .'">';
							$lNote = floor($note);
							for ($j=1;$j<=$note;$j++) {
								echo '<div class="circuit-star">';
								echo '<img src="images/ministar1.png" alt="star0" />';
								echo '</div>';
							}
							$x = $note - $lNote;
							if ($x) {
								echo '<div class="circuit-star">';
								echo '<img src="images/ministar0.png" alt="star0" />';
								echo '<div style="width: '. round(15*$x) .'px"><img src="images/ministar1.png" alt="star1" /></div>';
								echo '</div>';
								$lNote++;
							}
							for ($j=$lNote;$j<5;$j++) {
								echo '<div class="circuit-star">';
								echo '<img src="images/ministar0.png" alt="star0" />';
								echo '</div>';
							}
							echo '</div>';
							echo '<div class="circuit-nbcomments" title="'. $circuit['nbcomments'] .' '. ($language ? 'comment':'commentaire') . plural($circuit['nbcomments']) .'">';
							echo '<img src="images/comments.png" alt="comments" /> '. $circuit['nbcomments'];
							echo '</div>';
							echo '<div class="circuit-preview" title="'. ($language ? 'Preview':'Aperçu') .'" onclick="apercu('. htmlspecialchars(json_encode($circuit['srcs'])) .');return false">';
							echo '<img src="images/preview.png" alt="preview" />';
							echo '</div>';
							echo '</a>';
						echo '</td>';
					}
					else
						echo '<td>&nbsp;</td>';
				}
				echo '</tr>';
				echo '</table>';
				?>
				<h3><a href="creations.php?user=<?php echo urlencode($profileId); ?>&amp;tri=1"><?php echo $language ? 'See all their circuits':'Voir tous ses circuits'; ?></a></h3>
				<?php
			}
			else
				echo '<h2><em>'. ($language ? 'No created circuit':'Aucun circuit créé') .'</em></h2>';

			function print_challenge($challenge, &$challengeParams) {
				global $language;
				$challengeDetails = getChallengeDetails($challenge, $challengeParams);
				$circuit = $challengeDetails['circuit'];
				$isCup = (strpos($circuit['cicon'], ',') !== false);
				?>
				<a class="challenges-list-item"  href="challengeTry.php?challenge=<?php echo $challengeDetails['id']; ?>">
					<div class="challenges-item-circuit creation_icon <?php echo ($isCup ? 'creation_cup':'single_creation'); ?>"<?php
						if (isset($circuit['icon'])) {
							$allMapSrcs = $circuit['icon'];
							foreach ($allMapSrcs as $i=>$iMapSrc)
								$allMapSrcs[$i] = "url('images/creation_icons/$iMapSrc')";
							echo ' style="background-image:'.implode(',',$allMapSrcs).'"';
						}
						else
							echo ' data-cicon="'.$circuit['cicon'].'"';
					?>></div>
					<div class="challenges-item-description">
						<div>
						<?php
						if ($challengeDetails['name'])
							echo '<h2>'. htmlspecialchars($challengeDetails['name']) .'</h2>';
						echo '<h3>';
						echo '<strong>'. ($circuit['name'] ? $circuit['name']:($language ? 'Untitled':'Sans titre')) .'</strong> : ';
						echo $challengeDetails['description']['main'];
						echo '</h3>';
						if (isset($challengeDetails['description']['extra']))
							echo '<h4>'. $challengeDetails['description']['extra'] .'</h4>';
						?>
						</div>
					</div>
					<div class="challenges-item-action">
						<div class="challenges-item-difficulty challenges-item-difficulty-<?php echo $challengeDetails['difficulty']['level']; ?>" >
							<img src="images/challenges/difficulty<?php echo $challengeDetails['difficulty']['level']; ?>.png" alt="<?php echo $challengeDetails['difficulty']['name']; ?>" />
							<?php echo $challengeDetails['difficulty']['name']; ?>
						</div>
						<?php
						$note = $challengeDetails['rating']['avg'];
						$nbNotes = $challengeDetails['rating']['nb'];
						$noteTitle = $nbNotes ? (round($note*100)/100).'/5 '. ($language ? 'on':'sur') .' '. $nbNotes .' vote'. ($nbNotes>1 ? 's':'') : ($language ? 'Unrated':'Non noté');
						?>
						<div class="challenges-item-rating">
							<table>
								<tr title="<?php echo $noteTitle; ?>">
									<?php
									for ($i=1;$i<=$note;$i++)
										echo '<td class="star1"></td>';
									$rest = $note-floor($note);
									if ($rest) {
										$w1 = 3+round(9*$rest);
										echo '<td class="startStar" style="width: '. $w1 .'px;"></td>';
										echo '<td class="endStar" style="width: '. (15-$w1) .'px;"></td>';
										$note++;
									}
									for ($i=$note;$i<5;$i++)
										echo '<td class="star0" title="'. $noteTitle .'"></td>';
									?>
								</tr>
							</table>
						</div>
					</div>
				</a>
				<?php
			}
			$challengeParams = array(
				'rating' => true,
				'circuit' => true
			);
			if ($nbClCreate['nb']) {
				$getChallenges = mysql_query('SELECT c.*,l.type,l.circuit FROM mkchallenges c INNER JOIN mkclrace l ON c.clist=l.id WHERE l.type!="" AND c.status="active" AND '. $whereIP .' ORDER BY c.avgrating DESC, c.nbratings DESC, c.date DESC LIMIT 3');
				if (mysql_numrows($getChallenges)) {
					?>
					<hr />
					<h2><?php echo $language ? 'Best created challenges':'Meilleurs défis créés'; ?>&nbsp;:</h2>
					<div class="challenges-list">
					<?php
					require_once('utils-challenges.php');
					while ($challenge = mysql_fetch_array($getChallenges))
						print_challenge($challenge, $challengeParams);
					?>
					</div>
					<h3><a href="challengesList.php?author=<?php echo urlencode($profileId); ?>&amp;ordering=rating"><?php echo $language ? 'See all their challenges':'Voir tous ses défis'; ?></a></h3>
					<?php
				}
			}
			if ($nbComments) {
				echo '<hr />';
				?>
				<h2><?php echo $language ? 'Last circuit comments':'Derniers commentaires sur les circuits'; ?>&nbsp;:</h2>
				<div class="circuit-comments">
				<?php
				require_once('utils-date.php');
				foreach ($comments as $comment) {
					$getCircuit = $comment['circuit_data'];
					switch ($comment['type']) {
					case 'mkmcups' :
						$url = ($getCircuit['mode'] ? 'map.php':'circuit.php') . '?mid='. $getCircuit['id'];
						break;
					case 'mkcups' :
						$url = ($getCircuit['mode'] ? 'map.php':'circuit.php') . '?cid='. $getCircuit['id'];
						break;
					case 'mkcircuits' :
						$url = ($getCircuit['is_circuit'] ? 'circuit.php':'arena.php') . '?id='. $getCircuit['id'];
						break;
					case 'arenes' :
						$url = 'battle.php?i='. $getCircuit['ID'];
						break;
					case 'circuits' :
						$url = 'map.php?i='. $getCircuit['ID'];
					}
					?>
						<a class="circuit-comment" href="<?php echo $url; ?>">
							<div class="circuit-comment-msg"><?php echo str_replace('  ',' &nbsp;',nl2br(htmlspecialchars($comment['message']))); ?></div>
							<div class="circuit-comment-infos"><img src="images/comments.png" alt="comments"> <?php
							if ($getCircuit['nom']) {
								echo ($language ? 'In':'Dans'); ?> <strong><?php echo escapeUtf8(toUtf8($getCircuit['nom'])) ?></strong><?php
							}
							?> <?php echo pretty_dates($comment['date'], array('lower'=>true)); ?></div>
						</a>
					<?php
				}
				?>
				</div>
				<h3><a href="listComments.php?user=<?php echo urlencode($profileId); ?>"><?php echo $language ? 'See all their comments':'Voir tous ses commentaires'; ?></a></h3>
				<?php
			}
			if ($nbClSucceess['nb']) {
				$getChallenges = mysql_query('SELECT c.*,l.type,l.circuit FROM mkchallenges c INNER JOIN mkclrace l ON c.clist=l.id INNER JOIN mkclwin w ON w.challenge=c.id AND w.player="'. $profileId .'" AND w.creator=0 WHERE l.type!="" AND c.status="active" ORDER BY w.date DESC LIMIT 3');
				if (mysql_numrows($getChallenges)) {
					?>
					<hr />
					<h2><?php echo $language ? 'Last completed challenges':'Derniers défis réussis'; ?>&nbsp;:</h2>
					<div class="challenges-list">
					<?php
					require_once('utils-challenges.php');
					while ($challenge = mysql_fetch_array($getChallenges))
						print_challenge($challenge, $challengeParams);
					?>
					</div>
					<h3><a href="challengesList.php?winner=<?php echo urlencode($profileId); ?>"><?php echo $language ? 'See all completed challenges':'Voir tous les défis réussis'; ?></a></h3>
					<?php
				}
			}
			?>
			<hr />
			<?php
			$bestScores = mysql_query('SELECT r.perso,r.time,r.class,r.circuit,1+COUNT(r2.circuit) AS place FROM `mkrecords` r LEFT JOIN `mkrecords` r2 ON r.class=r2.class AND r.type=r2.type AND r.circuit=r2.circuit AND r2.time<r.time AND r2.best=1 WHERE r.player="'.$profileId.'" AND r.type="" AND r.best=1 GROUP BY r.class,r.circuit ORDER BY place LIMIT 3');
			if (mysql_numrows($bestScores)) {
				require_once('persos.php');
				function getSpriteSrc($playerName) {
					if (substr($playerName, 0,3) == 'cp-')
						return PERSOS_DIR . $playerName . ".png";
					return "images/sprites/sprite_" . $playerName . ".png";
				}
				?>
				<h2><?php echo $language ? 'Best scores in time trial':'Meilleurs temps en contre-la-montre'; ?>&nbsp;:</h2>
				<table class="clm-records">
					<tr>
						<td><?php echo $language ? 'Rank':'Place'; ?></td>
						<td><?php echo $language ? 'Class':'Cylindrée'; ?></td>
						<td>Circuit</td>
						<td><?php echo $language ? 'Character':'Personnage'; ?></td>
						<td><?php echo $language ? 'Time':'Temps'; ?></td>
					</tr>
				<?php
				include_once('circuitNames.php');
				while ($record = mysql_fetch_array($bestScores)) {
					?>
					<tr>
						<td><?php echo toPlace($record['place']); ?></td>
						<td><?php echo $record['class']; ?>cc</td>
						<td><?php
							echo $circuitNames[$record['circuit']-1];
						?></td>
						<td>
							<div>
								<img src="<?php echo getSpriteSrc($record['perso']); ?>" alt="mario" onload="spriteLoad(this)">
							</div>
						</td>
						<td>
							<?php
							$getTime = $record['time'];
							$sec = floor($getTime/1000);
							$mls = round($getTime-$sec*1000);
							$min = floor($sec/60);
							$sec -= $min*60;
							if ($sec < 10)
								$sec = '0'.$sec;
							if ($mls < 10)
								$mls = '00'.$mls;
							else if ($mls < 100)
								$mls = '0'.$mls;
							echo $min.':'.$sec.':'.$mls;
							?>
						</td>
					</tr>
					<?php
				}
				?>
				</table>
				<h3><a href="classement.php?user=<?php echo urlencode($profileId); ?>"><?php echo $language ? 'See all their scores':'Voir tous ses temps'; ?></a></h3>
				<?php
			}
			else
				echo '<h2><em>'. ($language ? 'No time trial score':'Aucun temps en contre-la-montre') .'</em></h2>';
			?>
			<hr />
			<?php
			$today = time();
			$topics = mysql_query('SELECT id,titre,dernier,nbmsgs FROM `mkfollowers` INNER JOIN `mktopics` ON topic=id WHERE user="'. $profileId .'" AND NOT EXISTS(SELECT * FROM mkmessages WHERE id=1 AND mkfollowers.topic=mkmessages.topic AND user=auteur)'. (hasRight('manager') ? '':' AND !private') .' ORDER BY dernier DESC LIMIT 6');
			if (mysql_numrows($topics)) {
				?>
				<h2><?php echo $language ? 'Last followed topics':'Derniers topics suivis'; ?>&nbsp;:</h2>
				<div class="following-topics">
				<?php
				while ($topic = mysql_fetch_array($topics)) {
					$auteur = mysql_fetch_array(mysql_query('SELECT auteur FROM `mkmessages` WHERE topic='. $topic['id'] .' ORDER BY id DESC LIMIT 1'));
					$name = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id='. $auteur['auteur']));
					$nbMsgs = $topic['nbmsgs'];
					?>
					<a href="topic.php?topic=<?php echo $topic['id']; ?>" title="<?php echo $topic['titre']; ?>">
						<h2><?php echo htmlspecialchars(controlLength($topic['titre'],40)); ?></h2>
						<h3><?php echo $language ? 'Last message':'Dernier message'; ?> <?php echo ($name ? ($language ? 'by':'par') .' <strong>'. $name['nom'].'</strong> ':'').pretty_dates_short($topic['dernier'],array('lower'=>true)); ?></h3>
						<div class="creation_comments" title="<?php echo $nbMsgs. ' message'. (($nbMsgs>1) ? 's':''); ?>"><img src="images/comments.png" alt="Messages" /> <?php echo $nbMsgs; ?></div>
					</a>
					<?php
				}
				?>
				</div>
				<h3><a href="listFollows.php?user=<?php echo urlencode($profileId); ?>"><?php echo $language ? 'See all followed topics':'Voir tous les topics suivis'; ?></a></h3>
				<?php
			}
			else
				echo '<h2><em>'. ($language ? 'No followed topics':'Aucun topic suivi') .'</em></h2>';
			?>
			<?php
			$getNews = mysql_query('SELECT n.id,n.title,n.nbcomments,
					name'. $language .' AS name,author,
					category,c.name'. $language .' AS catname,
					n.publication_date
					FROM `mknews` n
					INNER JOIN `mkcats` c ON n.category=c.id
					WHERE author="'. $profileId .'" AND status="accepted"
					ORDER BY n.publication_date DESC
					LIMIT 6
				');
			if (mysql_numrows($getNews)) {
				?>
				<hr />
				<h2><?php echo $language ? 'Last published news':'Dernières news publiées'; ?>&nbsp;:</h2>
				<div class="published-news">
				<?php
				while ($news = mysql_fetch_array($getNews)) {
					$nbMsgs = $news['nbcomments'];
					?>
					<a href="news.php?id=<?php echo $news['id']; ?>" title="<?php echo $news['title']; ?>">
						<h2><?php echo htmlspecialchars(controlLength($news['title'],40)); ?></h2>
						<h3><?php echo $language ? 'In':'Dans'; ?> <strong><?php echo $news['catname']; ?></strong> <?php echo pretty_dates_short($news['publication_date'],array('lower'=>true)); ?></h3>
						<div class="creation_comments" title="<?php echo $nbMsgs. ' ' . ($language ? 'comment':'commentaire') . (($nbMsgs>1) ? 's':''); ?>"><img src="images/comments.png" alt="Messages" /> <?php echo $nbMsgs; ?></div>
					</a>
					<?php
				}
				?>
				</div>
				<h3><a href="listPublish.php?user=<?php echo urlencode($profileId); ?>"><?php echo $language ? 'See all published news':'Voir toutes les news publiées'; ?></a></h3>
				<?php
			}
			?>
		</div>
	</div>
		<?php
	}
	else {
			echo '<div class="warning" style="line-height: 1.6em; margin: 5px 0">';
			echo ($language ? 'This account has been deleted.':'Ce compte a été supprimé.');
			echo '<br /><a href="forum.php">'. ($language ? 'Back to the forum':'Retour au forum') .'</a>';
			echo '</div>';
		?>
		<?php
	}
	?>
</main>
		<?php
		include('footer.php');
	}
	mysql_close();
	?>
</body>
</html>
	<?php
}
?>
