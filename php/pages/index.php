<?php
include('../includes/language.php');
require_once('../includes/rateLimit.php');
$rateLimitWrapper = handleRateLimit();
include('../includes/initdb.php');
if (isset($_SERVER['HTTP_REFERER']) && ($_SERVER['HTTP_REFERER'] != '')) {
	function startsWith($haystack, $needle) {
		// search backwards starting from haystack length characters from the end
		return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}
	if (!startsWith($_SERVER['HTTP_REFERER'],'https://mkpc.malahieude.net/'))
		mysql_query('INSERT INTO `previouspages` VALUES("'. mysql_real_escape_string($_SERVER['HTTP_REFERER']) .'")');
}
include('../includes/session.php');
?>
<!DOCTYPE html>
<html lang="<?= P_("html language", "en") ?>">
<head>
<title>Mario Kart PC</title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" href="styles/splide.min.css" />
<link rel="stylesheet" href="styles/slider.css" />
<link rel="stylesheet" href="styles/photoswipe.css" />
<?php
include('../includes/o_online.php');
?>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'home';
$homepage = true;
include('../includes/menu.php');
if ($id && $myIdentifiants) {
	mysql_query('INSERT IGNORE INTO `mkips` VALUES("'.$id.'","'.$myIdentifiants[0].'","'.$myIdentifiants[1].'","'.$myIdentifiants[2].'","'.$myIdentifiants[3].'")');
	mysql_query('INSERT IGNORE INTO `mkbrowsers` VALUES("'.$id.'","'.mysql_real_escape_string($_SERVER['HTTP_USER_AGENT']).'")');
}
$slidesPath = 'images/slides';
$placeholderPath = 'images/pages/pixel.png';
?>
<main>
	<section id="left_section">
		<div class="splide" role="group" aria-label="Splide Basic HTML Example">
			<div class="splide__track">
				<ul class="splide__list">
					<li class="splide__slide">
						<div class="splide__slide__container">
							<div class="splide__banner">
								<img src="<?= $placeholderPath ?>" data-splide-lazy="<?= $slidesPath ?>/diapo1.jpg" data-splide-lazy-srcset="<?= $slidesPath ?>/diapo1-640w.jpg 640w, <?= $slidesPath ?>/diapo1.jpg 960w" class="top" alt="Slide 1">
							</div>
							<div class="splide__description">
								<h3><?= _('A Mario Kart Game for browser') ?></h3>
								<div>
									<?= _("A computer version of the famous racing game by Nintendo."); ?><br/>
									<?= _("This game is <strong>completely free</strong> and does not require <strong>any downloads</strong>. All you need is a web browser!"); ?>
								</div>
							</div>
						</div>
					</li>
										
					<li class="splide__slide">
						<div class="splide__slide__container">
							<div class="splide__banner">
								<img src="<?= $placeholderPath ?>" data-splide-lazy="<?= $slidesPath ?>/diapo2.png" data-splide-lazy-srcset="<?= $slidesPath ?>/diapo2-640w.png 640w, <?= $slidesPath ?>/diapo2.png 960w" alt="Slide 2">
							</div>
							<div class="splide__description">
								<h3><?= _('Crazy races full of fun!') ?></h3>
								<div>
									<?= _("Try to be the fastest while avoiding items!") ?>
									<br />
									<?= _("Race on all the <strong>56 tracks</strong> from the original games <strong>Super Mario Kart</strong>, <strong>Mario Kart Super Circuit</strong> and <strong>Mario Kart DS</strong>.") ?>
								</div>
							</div>
						</div>
					</li>
										
					<li class="splide__slide">
						<div class="splide__slide__container">
							<div class="splide__banner">
								<img src="<?= $placeholderPath ?>" data-splide-lazy="<?= $slidesPath ?>/diapo3.png" alt="Slide 3">
							</div>
							<div class="splide__description">
								<h3><?= _('Win all the cups!') ?></h3>
								<div>
									<?= _("Face off with the CPUs on the <strong>14 grand prix</strong> tournaments and try to win the gold trophy!") ?>
									<br />
									<?= _("Win enough cups to unlock the <strong>15 secret characters</strong>!") ?>
								</div>
							</div>
						</div>
					</li>
										
					<li class="splide__slide">
						<div class="splide__slide__container">
							<div class="splide__banner">
								<img src="<?= $placeholderPath ?>" data-splide-lazy="<?= $slidesPath ?>/diapo4.png" alt="Slide 4">
							</div>
							<div class="splide__description">
								<h3><?= _('Create your own tracks!') ?></h3>
								<div>
									<?= _("With the <strong>track builder</strong>, the possibilities are endless; the only limit is your imagination!") ?>
									<br />
									<?= _("You can <strong>share</strong> your tracks or try other people's creations!") ?>
								</div>
							</div>
						</div>
					</li>
										
					<li class="splide__slide">
						<div class="splide__slide__container">
							<div class="splide__banner">
								<img src="<?= $placeholderPath ?>" data-splide-lazy="<?= $slidesPath ?>/diapo5.png" class="top smooth" alt="Slide 5">
							</div>
							<div class="splide__description">
								<h3><?= _('Face players from around the world!') ?></h3>
								<div>
									<?= _("Race and battle in <strong>online mode</strong>!") ?>
									<br />
									<?= _("Win as many races as possible and <strong>climb in the official ranking</strong>!") ?>
								</div>
							</div>
						</div>
					</li>
										
					<li class="splide__slide">
						<div class="splide__slide__container">
							<div class="splide__banner">
								<img src="<?= $placeholderPath ?>" data-splide-lazy="<?= $slidesPath ?>/diapo6.png" class="smooth" alt="Slide 6">
							</div>
							<div class="splide__description">
								<h3><?= _('Make the best scores in time trial!') ?></h3>
								<div>
									<?= _("<strong>Finish the race track</strong> as fast as you can!") ?>
									<br />
									<?= _("<strong>Compare your scores</strong> with the community, and face other players' ghosts!") ?>
								</div>
							</div>
						</div>
					</li>
										
					<li class="splide__slide">
						<div class="splide__slide__container">
							<div class="splide__banner">
								<img src="<?= $placeholderPath ?>" data-splide-lazy="<?= $slidesPath ?>/diapo7.png" alt="Slide 7">
							</div>
							<div class="splide__description">
								<h3><?= _('Release your fighter talents!') ?></h3>
								<div>
									<?= _("<strong>Destroy your opponents</strong>' balloons with items, without getting hit by their items!") ?>
									<br />
									<?= _("The last player standing wins!") ?>
								</div>
							</div>
						</div>
					</li>
										
					<li class="splide__slide">
						<div class="splide__slide__container">
							<div class="splide__banner">
								<img src="<?= $placeholderPath ?>" data-splide-lazy="<?= $slidesPath ?>/diapo8.png" class="center smooth" alt="Slide 8">
							</div>
							<div class="splide__description">
								<h3><?= _('Face off your friends with the local multiplayer mode!') ?></h3>
								<div>
									<?= _("Prove to your friends that you're the best!")?>
									<br />
									<?= _("Face them in <strong>multiplayer</strong> in VS races or in battle mode.") ?>
								</div>
							</div>
						</div>
					</li>
				</ul>
			</div>
		</div>
		<h1>Mario Kart PC</h1>
		<div id="toBegin"><a href="mariokart.php">
		&#9660;&nbsp;<?= _('Click on the game box to begin') ?>&nbsp;&#9660;<br />
		<img src="images/mkpc_box.jpg" alt="<?= _('Start game') ?>" /><br />
		&#9650;&nbsp;<?= _('Click on the game box to begin') ?>&nbsp;&#9650;</a></div>
		<h2><img src="images/about.png" alt="" /> <?= _('What\'s Mario Kart PC?') ?></h2>
		<div>
			<p>
				<?= _("You might know Mario Kart, the most fun racing game series of all time! Mario Kart PC uses the same base as the original games but is playable on your browser, and <strong>for free</strong>.") ?>
			</p>
			</p>
				<?= _("Most of the modes from Mario Kart have been included: Grand Prix, VS, Battle mode, Time Trials, and more!") ?>
				<br />
				
				<?= _("There's also a brand new mode: the <strong>track builder</strong>! Place straight lines and turns, add items, boost panels and more! Everything is customizable! The only limit is your own imagination!") ?>
				<br />
				
				<?= F_('You can share your tracks, and try other people\'s tracks thanks to the <a href="{url}">sharing tool</a>. Thousands of custom tracks are already available!', url: "creations.php") ?>
			</p>
			<p>
				<?= F_('Finally, you can face players from the whole world thanks to the <strong>multiplayer online mode</strong>! Climb the <a href="{url}">rankings</a> and become world champion!', url: "bestscores.php") ?>
			</p>
		</div>
		<h2><img src="images/camera.png" alt="" /> <?= _('Some screenshots') ?></h2>
		<div>
			<?= _('Here are some screenshots of the game to give you a quick preview of what it looks like:') ?>
			<div id="screenshots" class="demo-gallery">
				<?php
				for ($i=1;$i<=12;$i++) {
					echo '<div>';
					$url_img = "images/screenshots/ss$i.png";
					$url_thumb = 'images/screenshots/ss'.$i.'xs.png';
					echo '<a href="'. $url_img .'" data-size="960x468" data-med="'. $url_img .'" data-med-size="240x117" class="demo-gallery__photo demo-gallery__img--main"><img src="'.$url_thumb.'" alt="Screenshot '. $i .'" /></a>';
					echo '</div>';
				}
				?>
			</div>
		</div>
		<br />
		<?php
		function hasEuLegislation() {
			if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
				$euLangs = array('fr-FR', 'en-GB', 'de-DE', 'es-ES', 'fr-BE', 'nl-BE', 'nl-NL', 'it-IT', 'pl-PL', 'pt-PT', 'fr-CH', 'de-CH', 'it-CH', 'rm-CH');
				$euLangsString = implode('|', $euLangs);
				return preg_match('#(^|,)'.$euLangsString.'(,|$)#', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			}
			return false;
		}
		$shouldShowAds = isset($identifiants) || !hasEuLegislation();
		if ($shouldShowAds) {
			?>
			<div class="pub_section">
				<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
				<!-- Mario Kart PC -->
				<ins class="adsbygoogle"
					 style="display:inline-block;width:728px;height:90px"
					 data-ad-client="ca-pub-1340724283777764"
					 data-ad-slot="4919860724"
					 ></ins>
				<script>
				(adsbygoogle = window.adsbygoogle || []).push({});
				</script>
			</div>
			<?php
		}
		?>
		<h2><img src="images/thanks.png" alt="" /> <?= _('Special thanks') ?></h2>
		<div>
			<?= _("A big thanks to Nintendo, these three sites and these artists without which Mario Kart PC would have probably never existed !") ?>
				<ul>
					<li>
						<?= F_('<a href="{url_main_site}">Nihilogic</a> for the <a href="{url_mario_kart}">basic Mario Kart</a>', url_main_site: "https://web.archive.org/web/20101104055946/http://blog.nihilogic.dk/", url_mario_kart: "https://web.archive.org/web/20100208144516/http://www.nihilogic.dk/labs/mariokart/") ?>
					</li>
					<li>
						<?= F_('<a href="{url_main_site}">SNESMaps</a> for the <a href="{url_mario_kart}">track images</a>', url_main_site: "http://www.snesmaps.com/", url_mario_kart: "http://www.snesmaps.com/maps/SuperMarioKart/SuperMarioKartMapSelect.html") ?>
					</li>
					<li>
						<?= F_('<a href="{url_main_site}">Khinsider</a> for the <a href="{url_mario_kart}">musics</a>', url_main_site: "https://downloads.khinsider.com/", url_mario_kart: "https://downloads.khinsider.com/search?search=mario+kart") ?>
					</li>
					<li>
						<?= F_('And <a href="{url}">many more</a>!', url: "credits.php") ?>
					</li>
				</ul>
		</div>
		<h2><img src="images/follow.png" alt="" /> <?= _('Follow us') ?></h2>
		<div>
			<ul>
				<li>
					<?= F_('<a href="{url}">Discord Server</a> of the site: join it to chat with the community and be informed about updates and events.', url: "https://discord.gg/VkeAxaj") ?>
				</li>
				<li>
					<?= F_('<a href="{url_youtube}">Official Youtube Channel</a>: find videos on the game and information about the website and its events. The channel is maintained by members, if you want to participate, tell it on the <a href="{url_topic}">official topic</a>.', url_youtube: "https://www.youtube.com/channel/UCRFoW7uwHuP1mg0qSaJ4jNg", url_topic: "topic.php?topic=3392") ?>
				</li>
				<li>
					<?= F_('<a href="{url}">Github repo</a> of the site. Follow all the ongoing developments here, and if you can code, don\'t hesitate to contribute to the project!', url: "https://github.com/tmalahie/mkpc") ?>
				</li>
				<li>
					<?= F_('<a href="{url_wiki}">MKPC Wiki</a>: find out all the information about the game and its history. This site is maintained by the community, if you want to contribute, tell it on <a href="{url_topic}">this topic</a>!', url_wiki: "http://fr.wiki-mario-kart-pc.wikia.com/", url_topic: "topic.php?topic=343") ?>
				</li>
			</ul>
			<p>
				<em>
				<?= F_('This site is mostly maintained by French members, if you see some translation errors in the game or the site, don\'t hesitate to report them on this <a href="{url_topic}">forum topic</a>', url_topic: "topic.php?topic=1") ?>
				</em>
			</p>
		</div>
		<?php
		if ($shouldShowAds) {
			?>
		<div class="pub_section">
			<!-- Mario Kart PC -->
			<ins class="adsbygoogle"
					style="display:inline-block;width:728px;height:90px"
					data-ad-client="ca-pub-1340724283777764"
					data-ad-slot="4919860724"
					></ins>
			<script>
			(adsbygoogle = window.adsbygoogle || []).push({});
			</script>
		</div>
			<?php
		}
		?>
		<h2><img src="images/gamepad.png" alt="" /> <?= _('Go to the game') ?></h2>
		<div>
			<?= _("To start playing, it's very simple, just click on &quot;Play game&quot; in the menu above. Or more simply, click here:") ?><br />
				<a href="mariokart.php" class="action_button button_game"><?= _("Start playing now &gt;") ?></a>
		</div>
	</section>
	<section id="right_section">
		<?php
		require_once('../includes/utils-date.php');
		/*if ($id) {
			$alreadyClosed = mysql_fetch_array(mysql_query('SELECT read_date FROM mkofficialmsgread WHERE player = '.$id.' AND message="lounge_2024"'));
			if (!$alreadyClosed) {
				?>
				<div class="subsection">
					<div id="official_message">
						<a href="javascript:closeOfficialMessage('lounge_2024')" class="close">&times;</a>
						<?php
						if ($language) {
								?>
								The new <strong>MKPC lounge</strong> has started!<br />
								Play with other members in online games and climb in the rankings!<br />
								For more information, check <a href="topic.php?topic=15006">this topic</a>
								and join the official <a href="https://discord.gg/JvDS9PGY8x" target="_blank">Discord server</a> of the lounge.
								<?php
						}
						else {
								?>
								Le nouveau <strong>lounge MKPC</strong> a commencé&nbsp;!<br />
								Affrontez d'autres membres sur des courses en ligne et grimpez dans le classement&nbsp;!<br />
								Pour plus d'informations, lisez <a href="topic.php?topic=15006">ce topic</a>
								et rejoignez le <a href="https://discord.gg/JvDS9PGY8x" target="_blank">serveur Discord</a> officiel du lounge.
								<?php
						}
						?>
					</div>
				</div>
				<?php
			}
		}*/
		function uc_strlen($str) {
			return strlen(preg_replace("#(%u[0-9a-fA-F]{4})+#", ".", $str));
		}
		function uc_substr($str, $l) {
			preg_match_all('#(%u[0-9a-fA-F]{4})+#', $str, $positions, PREG_OFFSET_CAPTURE);
			$positions = $positions[0];
			$res = mb_substr(preg_replace("#(%u[0-9a-fA-F]{4})+#", ".", $str), 0,$l);
			foreach ($positions as $position) {
				if ($position[1] >= strlen($res))
					return $res;
				$res = mb_substr($res,0,$position[1]).$position[0].mb_substr($res,$position[1]+1);
			}
			return $res;
		}
		function controlLength($str,$maxLength) {
			$pts = '...';
			if (uc_strlen($str) > $maxLength)
				return uc_substr($str,$maxLength-strlen($pts)).$pts;
			return $str;
		}
		function controlLengthUtf8($str,$len) {
			return htmlEscapeCircuitNames(controlLength($str,$len));
		}
		require_once('../includes/circuitEscape.php');
		function display_sidebar($title,$link=null) {
			?>
			<table class="sidebar_container">
				<tr><td class="sidebar_icon"><img src="images/sidebar_icon.png" alt="<?php echo $title; ?>" /></td>
				<td class="sidebar_title"><?php
				if ($link)
					echo '<a href="'. $link .'">'. $title .'</a>';
				else
					echo $title;
				?></td></tr>
			</table>
			<?php
		}
		?>
		<div class="subsection">
		<?php
		if ($id) {
			if (($getWarn = mysql_fetch_array(mysql_query('SELECT seen FROM mkwarns WHERE player="'. $id .'"'))) && !$getWarn['seen']) {
				?>
				<div class="warning-top-message">
					<?= F_('You have received a warning for inappropriate behavior. Please <a href={url}>click here</a> to find it out.', url: 'forum.php?warn#compte'); ?>
				</div>
				<?php
			}

			$today = time();
			$cDate = new DateTime('@'.$today);
			$cDate->setTimezone(new DateTimeZone(get_client_tz()));
			$cYear = $cDate->format('Y');
			$cMonth = $cDate->format('m');
			$cDay = $cDate->format('d');
			$curDate = $cYear.'-'.$cMonth.'-'.$cDay;
			$getBirthdays = mysql_query('SELECT j.id,j.nom,p.identifiant,p.identifiant2,p.identifiant3,p.identifiant4,p.nbmessages FROM `mkprofiles` p INNER JOIN `mkjoueurs` j ON p.id=j.id WHERE birthdate IS NOT NULL AND DAY(birthdate)='. $cDay .' AND MONTH(birthdate)='. $cMonth .' AND j.banned=0 AND j.deleted=0 AND last_connect>=DATE_SUB("'.$curDate.'",INTERVAL 6 MONTH) AND TIMESTAMPDIFF(SECOND,last_connect,"'.$curDate.'")<=TIMESTAMPDIFF(SECOND,IFNULL(sub_date,"2016-01-01"),last_connect)*0.25+7*24*3600 ORDER BY p.nbmessages DESC, p.id ASC');
			$dc = array();
			$birthdaysList = array();
			while ($getBirthday = mysql_fetch_array($getBirthdays)) {
				$dId = $getBirthday['identifiant'].'_'.$getBirthday['identifiant2'].'_'.$getBirthday['identifiant3'].'_'.$getBirthday['identifiant4'];
				if (!isset($dc[$dId])) {
					$dc[$dId] = $getBirthday;
					$birthdaysList[] = $getBirthday;
				}
			}
			$nbBirthdays = count($birthdaysList);
			if ($nbBirthdays) {
				?>
				<div class="birthdays-list">
					<img src="images/ic_birthday.png" alt="birthday" />
					<?= _("Happy birthday to") ?>
					<?php
					for ($i=0;$i<$nbBirthdays;$i++) {
						$birthday = $birthdaysList[$i];
						if ($i)
							echo ($i==$nbBirthdays-1) ? _(" and ") : ", ";
						echo '<a href="profil.php?id='. $birthday['id'] .'">'. $birthday['nom'] .'</a>';
					}
					echo P_("final exclamation point in a sentence", '!');
					?>
				</div>
				<?php
			}
		}
		date_default_timezone_set('UTC');
		display_sidebar('Forum', 'forum.php');
		?>
			<h2><?= _('Latest topics') ?></h2>
			<div id="forum_section" class="right_subsection">
				<?php
				require_once('../includes/getRights.php');
				$sql = 'SELECT t.id,t.titre, t.nbmsgs, t.category, t.dernier FROM `mktopics` t ' . (hasRight('manager') ? '':' WHERE !t.private') .' ORDER BY t.dernier DESC LIMIT 10';
				if ($language)
					$sql = 'SELECT * FROM ('. $sql .') t ORDER BY (category=4) DESC, dernier DESC';
				$getTopics = mysql_query($sql);
				$topics = array();
				$topicIds = array();
				while ($topic = mysql_fetch_array($getTopics)) {
					$topics[] = $topic;
					$topicIds[] = $topic['id'];
				}
				$lastMsgByTopic = array();
				$topicIdsString = implode(',', $topicIds);
				if ($topicIdsString) {
					$getLastMessages = mysql_query('SELECT m.topic,j.nom FROM (SELECT topic,MAX(id) AS maxid FROM mkmessages WHERE topic IN ('. $topicIdsString .') GROUP BY topic) mm LEFT JOIN mkmessages m ON m.topic=mm.topic AND m.id=mm.maxid LEFT JOIN mkjoueurs j ON m.auteur=j.id');
					while ($message = mysql_fetch_array($getLastMessages))
						$lastMsgByTopic[$message['topic']] = $message;
				}
				foreach ($topics as $topic) {
					$nbMsgs = $topic['nbmsgs'];
					$message = $lastMsgByTopic[$topic['id']];
					?>
					<a href="topic.php?topic=<?= $topic['id'] ?>" title="<?= $topic['titre'] ?>">
						<h2><?php echo htmlspecialchars(controlLength($topic['titre'],40)); ?></h2>
						<h3>
							<?php
								if ($message['nom']) {
									printf(F_("Latest message by <strong>{message}</strong>", message: $message['nom']));
								} else {
									printf(_("Latest message"));
								}
								echo ' ';
								echo pretty_dates_short($topic['dernier'],array('lower'=>true));
							?>
						</h3>
						<div class="creation_comments" title="<?= FN_("{count} message", "{count} messages", count: $nbMsgs) ?>"><img src="images/comments.png" alt="Messages" /> <?= $nbMsgs; ?></div>
					</a>
					<?php
				}
				unset($topics);
				unset($lastMsgByTopic);
				?>
			</div>
			<a class="right_section_actions action_button" href="forum.php"><?= _('Go to the forum') ?></a>
		</div>
		<div class="subsection">
		<?php
		display_sidebar('News', 'listNews.php');
		?>
			<h2><?= _('Latest news') ?></h2>
			<div id="news_section" class="right_subsection">
				<?php
				date_default_timezone_set('Europe/Paris');
				$getNews = mysql_query('SELECT n.id,n.title,n.nbcomments,
					name'. $language .' AS name,author,
					category,c.name'. $language .' AS catname,
					n.publication_date
					FROM `mknews` n
					INNER JOIN `mkcats` c ON n.category=c.id
					WHERE status="accepted"
					ORDER BY n.publication_date DESC
					LIMIT 8
				');
				$lastNewsDate = time();
				if ($id) {
					$lastNewsDate -= 7*86400;
					if ($lastNewsRead = mysql_fetch_array(mysql_query('SELECT date FROM `mknewsread` WHERE user='.$id)))
						$lastNewsDate = max($lastNewsDate,strtotime($lastNewsRead['date']));
				}
				$nbnews = 0;
				while ($news = mysql_fetch_array($getNews)) {
					$nbnews++;
					$name = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id='. $news['author']));
					$nbMsgs = $news['nbcomments'];
					$isNew = (strtotime($news['publication_date']) > $lastNewsDate);
					?>
					<a href="news.php?id=<?php echo $news['id']; ?>" title="<?php echo htmlspecialchars($news['title']); ?>"<?php if ($isNew) echo ' class="news_new"'; ?>>
						<h2><?php echo htmlspecialchars(controlLength($news['title'],40)); ?></h2>
						<h3>
							<?php
								if ($name) {
									printf(P_("Categories", "In <strong>%s</strong> by <strong>%s</strong>"), $news['catname'], $name['nom']);
								} else {
									printf(P_("Categories", "In <strong>%s</strong>"), $name['nom']);
								}
							?>
							<?= pretty_dates_short($news['publication_date'],array('lower'=>true)); ?>
						</h3>
						<div class="creation_comments" title="<?= FN_("{count} comment", "{count} comments", count: $nbMsgs)?>">
							<img src="images/comments.png" alt="Messages" /> <?php echo $nbMsgs; ?>
						</div>
					</a>
					<?php
				}
				date_default_timezone_set('UTC');
				if (!$nbnews)
					echo '<div style="text-align:center;margin-top:55px">'. _('No news yet').'</div>';
				?>
			</div>
			<?php
			if (hasRight('publisher')) {
				$getPendingNews = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mknews WHERE status="pending"'));
				if ($getPendingNews['nb']) {
					?>
					<p class="nb-pending-news">
						<?= F_('<a href="{url}">{count} pending</a> news', count: $getPendingNews['nb'], url: 'listNews.php#pending-news') ?>
					</p>
					<?php
				}
			}
			?>
			<a class="right_section_actions action_button" href="listNews.php"><?= _('All news') ?></a>
		</div>
		<?php
		/*if ($id) {
			?>
		<div class="subsection">
			<?php
			display_sidebar('MKPC Tri-Nations', 'news.php?id=15069');
			?>
			<h2><?= _('Current bracket') ?></h2>
			<div id="tri-nations" class="right_subsection">
			<table>
					<tr>
						<th><?= _('Rank') ?></th>
						<th><?= _('Team') ?></th>
						<th>Pts</th>
						<th class="pl-l" title="<?= _('Wins - Ties - Losses') ?>"><?= _('W-T-L') ?></th>
						<th class="pl-xl" title="<?= _('Score difference') ?>"><?= _('Diff') ?></th>
					</tr>
					<?php
					$plRanking = array(
						array(
							'icon' => 'ea.png',
							'name' => _('Eurasia'),
							'score' => 9,
							'wins' => 3,
							'losses' => 1,
							'ties' => 0,
							'diff' => 148
						),
						array(
							'icon' => 'fr.png',
							'name' => _('France'),
							'score' => 9,
							'wins' => 3,
							'losses' => 1,
							'ties' => 0,
							'diff' => 68
						),
						array(
							'icon' => 'am.png',
							'name' => _('Americas'),
							'score' => 0,
							'wins' => 0,
							'losses' => 4,
							'ties' => 0,
							'diff' => -216
						),
					);
					usort($plRanking, function($team1, $team2) {
						return ($team2['score']+$team2['diff']/1000) <=> ($team1['score']+$team1['diff']/1000);
					});
					foreach ($plRanking as $i=>$team) {
						?>
						<tr>
						<td><?php echo ($i+1); ?></td>
						<td>
							<div>
								<img src="images/events/tri-nations/<?php echo $team['icon']; ?>" alt="<?php echo $team['name']; ?>" />
								<?php echo $team['name']; ?>
							</div>
						</td>
						<td><?php echo $team['score']; ?></td>
						<td class="pl-l"><?php echo $team['wins'].'-'.$team['ties'].'-'.$team['losses']; ?></td>
						<td class="pl-xl"><?php echo $team['diff']; ?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<div class="link-extra"><a href="https://discord.gg/dPerbeFc36" target="_blank"><?= _("Tournament's Discord Server") ?></a></div>
		</div>
			<?php
		}*/
		?>
		<div class="subsection">
			<?php
			display_sidebar(_('Track builder'), 'creations.php');
			?>
			<h2><?= _('Latest creations') ?></h2>
			<div id="creations_section" class="right_subsection">
				<table>
					<?php
					function getNom($circuit) {
						$maxL = 25;
						$res = ($circuit['nom'] ? controlLengthUtf8($circuit['nom'],$maxL):(_('Untitled')));
						if (isset($circuit['prefix']) && (uc_strlen($circuit['nom'])+mb_strlen($circuit['prefix']) <= $maxL))
							$res = '<small>'. $circuit['prefix'] .' </small>' . $res;
						return $res;
					}
					function getAuteur($circuit) {
						if ($circuit['auteur']) {
							return F_("By <strong>{author}</strong>", author: controlLengthUtf8($circuit['auteur'],15));
						}
						return '';
					}
					function cmp_creation($line1, $line2) {
						$score1 = $line1['score'];
						$score2 = $line2['score'];
						if ($score1 < $score2)
							return 1;
						if ($score2 < $score1)
							return -1;
						$time1 = strtotime($line1['publication_date']);
						$time2 = strtotime($line2['publication_date']);
						if ($time1 < $time2)
							return 1;
						if ($time1 < $time2)
							return -1;
						return 0;
					}
					function sortLines($lines) {
						$logb = log(1.7);
						foreach ($lines as &$line) {
							$publishedSince = time()-strtotime($line['publication_date']);
							$publishedSince = max($publishedSince,0);
							$recency = 8-log($publishedSince/2000)/$logb;
							$recency = min(max($recency,3),8);
							$note = $line['note']-1;
							$nbnotes = max($line['nbnotes'],1);
							if ($note == -1) {
								if ($recency == 8)
									$note = $recency;
								else
									$note = 2;
							}
							elseif ($recency > $note) {
								if ($note >= 2.6)
									$note = $recency;
								elseif ($note <= 1.4)
									$nbnotes = max($nbnotes,2);
							}
							$line['score'] = ($recency+$note*$nbnotes)/(1+$nbnotes);
						}
						usort($lines, 'cmp_creation');
						return $lines;
					}
					function showLine($line) {
						global $language, $today;
						$circuit = $line;
						include('../includes/creation_line.php');
					}
					require_once('../includes/utils-circuits.php');
					$nbByType = array(1,1,2,2,3,3,2,2,1,1,1,1);
					$tracksList = listCreations(1,$nbByType,null,$aCircuits);
					$tracksList = sortLines($tracksList);
					$tracksList = array_slice($tracksList,0,14);
					foreach ($tracksList as $line)
						showLine($line);
					?>
				</table>
			</div>
			<a class="right_section_actions action_button" href="creations.php"><?= _('Display all') ?></a>
			<h2><?= _('Latest challenges') ?></h2>
			<div id="challenges_section" class="right_subsection">
				<?php
				require_once('../includes/utils-challenges.php');
				$getChallenges = mysql_query('SELECT c.*,l.type,l.circuit FROM mkchallenges c INNER JOIN mkclrace l ON c.clist=l.id WHERE c.status="active" AND l.type!="" ORDER BY date DESC LIMIT 15');
				$challengeParams = array(
					'circuit' => true,
					'circuit.raw' => true
				);
				if ($id) {
					$challengeParams['winners'] = true;
					$challengeParams['id'] = $id;
				}
				while ($challenge = mysql_fetch_array($getChallenges)) {
					$challengeDetails = getChallengeDetails($challenge, $challengeParams);
					?>
					<a href="<?php echo 'challengeTry.php?challenge='.$challenge['id']; ?>" title="<?php echo $challengeDetails['description']['main']; ?>"<?php if (isset($challengeDetails['succeeded'])) echo ' class="challenges_section_succeeded"'; ?>>
						<h2><?php echo controlLength($challengeDetails['description']['main'],100); ?></h2>
						<h3><?php echo ucfirst(($challengeDetails['circuit']['author'] ? (($language ? 'by':'par') .' <strong>'. controlLengthUtf8($challengeDetails['circuit']['author'],10) .'</strong> '):'') . ($challengeDetails['circuit']['name'] ? (($language ? 'in':'dans') . ' <strong>'. controlLengthUtf8($challengeDetails['circuit']['name'],30-min(10,strlen($challengeDetails['circuit']['author']))-strlen($challengeDetails['difficulty']['name'])) .'</strong>'):'')); ?> - <strong><?php echo $challengeDetails['difficulty']['name']; ?></strong></h3>
					</a>
					<?php
				}
				?>
			</div>
			<?php
			if (hasRight('clvalidator')) {
				$getPendingChallenges = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkchallenges WHERE status="pending_moderation"'));
				if ($getPendingChallenges['nb']) {
					echo '<p class="nb-pending-news">';
					echo FN_('<a href="{url}">{count} pending</a> challenge', '<a href="{url}">{count} pending</a> challenges', count: $getPendingChallenges['nb'], url: 'challengesList.php?moderate');
					echo '</p>';
				}
			}
			?>
			<a class="right_section_actions action_button" href="challengesList.php"><?= _('Display all') ?></a>
			<div id="challenge_ranking"><a href="challengeRanking.php"><?= _('Challenge points - Leaderboard') ?></a></div>
			<h2><?= _('Recent activity') ?></h2>
			<div id="comments_section" class="right_subsection">
				<?php
				//$getComments = mysql_query('SELECT c.circuit,c.type,c.message,c.temps,c.nom,c.date FROM ((SELECT mkcomments.circuit,mkcomments.type COLLATE latin1_general_ci AS type,mkcomments.message COLLATE latin1_general_ci AS message,mkcomments.date,mkjoueurs.nom COLLATE latin1_general_ci AS nom,NULL as temps FROM `mkcomments` INNER JOIN `mkjoueurs` ON mkcomments.auteur=mkjoueurs.id) UNION ALL (SELECT circuit,type,NULL as message,date,nom,temps FROM `mkrecords`) ORDER BY date DESC) as c GROUP BY c.type,c.circuit ORDER BY c.date DESC LIMIT 14');
				$getComments = mysql_query('SELECT class,circuit,type,message,time,name,date,recency FROM ((SELECT NULL AS class,mkcomments.circuit,mkcomments.type,mkcomments.message,mkcomments.date,mkjoueurs.nom AS name,NULL as time, (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(date))*1 AS recency FROM `mkcomments` INNER JOIN `mkjoueurs` ON mkcomments.auteur=mkjoueurs.id ORDER BY mkcomments.id DESC LIMIT 30) UNION ALL (SELECT class,circuit,type,NULL as message,date,name,time,(UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(date))*2 AS recency FROM `mkrecords` WHERE type!="" AND best=1 ORDER BY id DESC LIMIT 30) ORDER BY recency) as c GROUP BY c.type,c.circuit ORDER BY recency LIMIT 14');
				function zerofill($nb,$l) {
					$nb .= '';
					while (strlen($nb) < $l)
						$nb = '0'. $nb;
					return $nb;
				}
				function getRank($n) {
					$languageForOrdinals = P_("language for ordinals", "en");
					$dec = $n%100;
					if ($languageForOrdinals == "fr") {
						if ($n > 1)
							return 'e';
						else
							return 'er';
					}
					else
					{
						if (($dec >= 10) && ($dec < 20))
							return 'th';
						switch ($n%10) {
						case 1 :
							return 'st';
						case 2 :
							return 'nd';
						case 3 :
							return 'rd';
						default :
							return 'th';
						}
					}
					return $n;
				}
				require_once('../includes/utils-cups.php');
				while ($comment = mysql_fetch_array($getComments)) {
					if (($getCircuit = fetchCreationData($comment['type'], $comment['circuit'])) && ($getCircuit['name'] !== null)) {
						switch ($comment['type']) {
						case 'mkmcups' :
							$url = getCupPage($getCircuit['mode']) . '.php?mid='. $getCircuit['id'];
							break;
						case 'mkcups' :
							$url = getCupPage($getCircuit['mode']) . '.php?cid='. $getCircuit['id'];
							break;
						case 'mkcircuits' :
							$url = ($getCircuit['type'] ? 'arena.php':'circuit.php') . '?id='. $getCircuit['id'];
							break;
						case 'arenes' :
							$url = 'battle.php?i='. $getCircuit['ID'];
							break;
						case 'circuits' :
							$url = 'map.php?i='. $getCircuit['ID'];
						}
						if ($comment['message'] !== null) {
							$type = 'comments';
							$message = $comment['message'];
						}
						else {
							$type = 'records';
							$timeMS = $comment['time'];
							$ms = $timeMS%1000;
							$secs = floor($timeMS/1000)%60;
							$mins = floor($timeMS/60000);
							$records = mysql_query('SELECT time FROM `mkrecords` WHERE class="'. $comment['class'] .'" AND circuit="'. $comment['circuit'] .'" AND type="'. $comment['type'] .'" AND best=1');
							$place = 1;
							$nbRecords = 0;
							while ($record = mysql_fetch_array($records)) {
								if ($record['time'] < $comment['time'])
									$place++;
								$nbRecords++;
							}
							$message = $mins.':'.zerofill($secs,2).':'.zerofill($ms,3) .' ('. $place.'<sup>'.getRank($place).'</sup>' .' '.($language ? 'out of':'sur').' '. $nbRecords .')';
						}
						?>
						<a href="<?php echo $url; ?>"<?php if ($type == 'comments') echo ' title="'. htmlspecialchars($message) .'"'; ?>>
							<h2><img src="images/<?php echo $type; ?>.png" alt="<?php echo $type; ?>" /> <?php echo ($type == 'comments') ? htmlspecialchars(controlLength($message,40)) : $message; ?></h2>
							<h3><?php echo ($language ? 'By':'Par') .' <strong>'. htmlspecialchars(controlLength($comment['name'],10)) .'</strong>'; ?> <?php echo ($getCircuit['name'] ? (($language ? 'in':'dans') . ' <strong>'. controlLengthUtf8($getCircuit['name'],20) .'</strong>'):''); ?> <?php echo pretty_dates_short($comment['date'],array('lower'=>true)); ?></h3>
						</a>
						<?php
					}
				}
				?>
			</div>
		</div>
		<div class="subsection rank_vs" id="rankings_section">
			<?php
			display_sidebar(_('Online mode'), 'online.php');
			$activePlayers = array(array(),array());
			if ($id) {
				$time = time();
				$limCoTime = floor(($time-35)*1000/67);
				require_once('../includes/public_links.php');
				$getPlayingUsers = mysql_query('(
					SELECT j.id,j.nom,j.course,j.pts_vs,j.pts_battle,
					0 AS connecte,m.mode,m.cup,m.time,m.link,0 AS state
					FROM mariokart m INNER JOIN mkjoueurs j ON m.id=j.course
					WHERE map=-1 AND time>='.($time-1).' AND m.link IN ('.$publicLinksString.') AND j.id!='.$id.'
				) UNION (
					SELECT j.id,j.nom,j.course,j.pts_vs,j.pts_battle,
					0 AS connecte,m.mode,m.cup,m.time,m.link,1 AS state
					FROM mariokart m INNER JOIN mkjoueurs j ON m.id=j.course
					WHERE time>='.(($time-1)*1000).' AND m.link IN ('.$publicLinksString.') AND j.id!='.$id.'
				) UNION (
					SELECT j.id,j.nom,j.course,j.pts_vs,j.pts_battle,p.connecte,m.mode,m.cup,m.time,m.link,2 AS state
					FROM mkjoueurs j INNER JOIN mariokart m ON j.course=m.id
					INNER JOIN mkplayers p ON j.id=p.id
					WHERE p.connecte>='.$limCoTime.' AND m.link IN ('.$publicLinksString.') AND j.id!='.$id.'
				)');
				$activeCourses = array();
				$allPlayers = array();
				$limTimes = array(
					0 => $time+25,
					2 => floor(($time-5)*1000/67)
				);
				while ($playingUser = mysql_fetch_array($getPlayingUsers)) {
					$playingUser['game'] = $playingUser['cup'] ? (($playingUser['mode']%8 >= 4) ? 1:0) : $playingUser['mode'];
					$playingUser['pk'] = $playingUser['link'].':'.$playingUser['mode'].':'.$playingUser['cup'];
					$playingUser['pts'] = $playingUser['pts_'.($playingUser['game'] ? 'battle':'vs')];
					$course = $playingUser['course'];
					if (!isset($activeCourses[$course])) {
						$activeCourses[$course] = array(
							'active' => false,
							'players' => array()
						);
					}
					$activeCourses[$course]['players'][$playingUser['id']] = $playingUser;
					if (!$activeCourses[$course]['active']) {
						switch ($playingUser['state']) {
						case 0:
							$isActiveCourse = (count($activeCourses[$course]['players'])>=2) || ($playingUser['time']>=$limTimes[0]);
							break;
						case 1:
							$isActiveCourse = true;
							break;
						case 2:
							$isActiveCourse = ($playingUser['connecte']>=$limTimes[2]);
							break;
						}
						if ($isActiveCourse)
							$activeCourses[$course]['active'] = true;
					}
				}
				foreach ($activeCourses as &$activeCourse) {
					if ($activeCourse['active']) {
						foreach ($activeCourse['players'] as &$activePlayer) {
							$game = $activePlayer['game'];
							$playerId = $activePlayer['id'];
							$activePlayers[$game][$playerId] = $activePlayer;
						}
					}
				}
			}
			function gamePkSort($k1,$k2) {
				$p1 = explode(':',$k1);
				$p2 = explode(':',$k2);
				for ($i=0;$i<3;$i++) {
					if ($p1[$i] < $p2[$i])
						return -1;
					elseif ($p2[$i] < $p1[$i])
						return 1;
				}
				return 0;
			}
			$activePlayersByLink = array();
			foreach ($activePlayers as $game=>$players) {
				$playersWithLink = array();
				foreach ($players as $player)
					$playersWithLink[$player['pk']][] = $player;
				uksort($playersWithLink, 'gamePkSort');
				$activePlayersByLink[$game] = $playersWithLink;
			}
			?>
			<h2>Top 10</h2>
			<div class="ranking_tabs">
				<?php
				function print_badge($game) {
					global $activePlayers;
					$nbActivePlayers = count($activePlayers[$game]);
					if ($nbActivePlayers)
						echo '<span class="ranking_badge"><span>'.$nbActivePlayers.'</span></span>';
				}
				function get_creation_string(&$params) {
					global $language;
					$isMCup = ($params['mode']==8);
					$isBattle = $params['game'];
					$isSingle = (($params['mode']%4)>=2);
					$complete = (($params['mode']%2)>=1);
					if ($isBattle)
						$table = $complete ? 'arenes':'mkcircuits';
					elseif ($isMCup)
						$table = 'mkmcups';
					elseif ($isSingle)
						$table = $complete ? 'circuits':'mkcircuits';
					else
						$table = 'mkcups';
					$res = '';
					if ($getNom = fetchCreationData($table,$params['cup'], array('select' => '1')))
						$res = $getNom['name'];
					if (!$res) $res = _('Untitled');
					return controlLengthUtf8($res,30);
				}
				function get_mode_string(&$params) {
					global $publicLinksData;
					$link = $params['link'];
					$modeNames = array(
						'cc' => '${value}cc',
						'mirror' => _('Mirror'),
						'team' => _('Team'),
						'friendly' => _('Friendly')
					);
					$publicLinkData = $publicLinksData[$link];
					$enabledModes = array();
					foreach ($modeNames as $option => $value) {
						if (isset($publicLinkData->$option))
							$enabledModes[$option] = str_replace('${value}', $publicLinkData->$option, $value);
					}
					if (empty($enabledModes))
						return _('Normal');
					else
						return implode('+',$enabledModes);
				}
				function print_players_raw($players, &$params=array()) {
					global $language;
					$nbActivePlayers = count($players);
					$i = 0;
					$title = '';
					foreach ($players as $activePlayer) {
						if ($i)
							$title .= ', ';
						$pts = number_format($activePlayer['pts'],0,'.',($language ? ',':'&nbsp;'));
						$title .= $activePlayer['nom'] .' ('. $pts .' pts)';
						$i++;
					}
					echo '<span class="ranking_activeplayernb" title="'. $title .'">';
					echo FN_("{count} member", "{count} members", count: $nbActivePlayers);
					echo '</span>';
					if (!empty($params['cup'])) {
						echo ' ';
						if ($params['game'])
							$theCircuit = _('the arena');
						else {
							$isMCup = ($params['mode']==8);
							$isSingle = (($params['mode']%4)>=2);
							if ($isMCup)
								$theCircuit = _('the multicup');
							elseif ($isSingle)
								$theCircuit = _('the circuit');
							else
								$theCircuit = _('the cup');
						}
						echo P_("circuit", "in ") . $theCircuit;
						echo ' ';
						echo '<strong>';
						echo get_creation_string($params);
						echo '</strong>';
					}
					elseif (!empty($params)) {
						echo F_(" in {mode} mode", mode: get_mode_string($params));
					}
				}
				function print_join_button(&$params) {
					$url = 'online.php';
					$urlParams = array();
					if ($params['cup']) {
						$isMCup = ($params['mode']==8);
						$isSingle = (($params['mode']%4)>=2);
						$complete = (($params['mode']%2)>=1);
						$urlParams[] = ($isMCup?'mid':($isSingle?($complete?'i':'id'):($complete?"cid":"sid")))."=".$params['cup'];
					}
					if ($params['game'])
						$urlParams[] = 'battle';
					if ($params['link'])
						$urlParams[] = 'key='.$params['link'];
					if (!empty($urlParams))
						$url .= '?'.implode('&',$urlParams);
					echo '<a class="action_button" href="'. $url .'">'. _('Join') .'</a>';
				}
				function print_active_players($game,$type) {
					global $activePlayers, $activePlayersByLink;
					if (!empty($activePlayers[$game])) {
						echo '<div class="ranking_current" id="ranking_current_'.$type.'">';
						$firstPlayer = reset($activePlayers[$game]);
						if ((count($activePlayersByLink[$game]) < 2) && !$firstPlayer['link'] && !$firstPlayer['cup']) {
							echo '<span class="ranking_list">';
							echo _('Currently online:');
							echo ' ';
							print_players_raw($activePlayers[$game]);
							print_join_button($firstPlayer);
							echo '</span>';
							echo ' ';
						}
						else {
							echo _('Currently online:');
							echo '<ul class="ranking_list_game">';
							foreach ($activePlayersByLink[$game] as $players) {
								echo '<li>';
								$params = reset($players);
								print_players_raw($players, $params);
								print_join_button($params);
								echo '</li>';
							}
							echo '</ul>';
						}
						echo '</div>';
					}
				}
				?>
				<a class="ranking_tab tab_vs" href="javascript:dispRankTab(0)">
					<?= _('VS mode') ?>
				</a><a class="ranking_tab tab_battle" href="javascript:dispRankTab(1)">
					<?= _('Battle') ?>
					<?php print_badge(1); ?>
				</a><a class="ranking_tab tab_clm tab_clm150" href="javascript:dispRankTab(currenttabcc)">
					<?= _('Time Trial') ?>
				</a>
			</div>
			<div id="currently_online">
			<?php
			print_active_players(0,'vs');
			print_active_players(1,'battle');
			?>
			</div>
			<div id="clm_cc">
			<a class="clm_cc_150" href="javascript:dispRankTab(2)">150cc</a> <span>|</span>
			<a class="clm_cc_200" href="javascript:dispRankTab(3)">200cc</a>
			</div>
			<div id="top10" class="right_subsection">
				<?php
				$modeIds = array('vs','battle','clm150','clm200');
				for ($i=0;$i<4;$i++) {
					$modeId = $modeIds[$i];
					$isBattle = ($i===1);
					$isClm = ($i>=2);
					$pts_ = 'pts_'.$modeId;
					?>
					<table id="top_<?php echo $modeId; ?>">
						<tr>
							<th><?= _('Rank') ?></th>
							<th><?= _('Nick') ?></th>
							<th><?= _('Score') ?></th>
						</tr>
						<?php
						if ($isClm) {
							$cc = ($i===3) ? 200 : 150;
							$players = mysql_query('SELECT t.player AS id,j.nom,t.score AS pts FROM `mkttranking` t INNER JOIN `mkjoueurs` j ON t.player=j.id WHERE t.class="'.$cc.'" AND j.deleted=0 ORDER BY t.score DESC LIMIT 10');
						}
						else
							$players = mysql_query('SELECT id,nom,'.$pts_.' AS pts FROM `mkjoueurs` WHERE deleted=0 ORDER BY '.$pts_.' DESC LIMIT 10');
						$place = 0;
						$lastScore = INF;
						for ($j=1;$player=mysql_fetch_array($players);$j++) {
							if ($player['pts'] < $lastScore) {
								$place = $j;
								$lastScore = $player['pts'];
							}
							echo '<tr><td class="top10position">'. $place .'</td><td><a href="profil.php?id='. $player['id'] .'">'. controlLength($player['nom'],20) .'</a></td><td>'. $player['pts'] .'</td></tr>';
						}
						?>
					</table>
					<?php
				}
				?>
			</div>
			<a class="right_section_actions action_button action_gotovs" href="bestscores.php"><?= _('Display all'); ?></a>
			<a class="right_section_actions action_button action_gotobattle" href="bestscores.php?battle"><?= _('Display all'); ?></a>
			<a class="right_section_actions action_button action_gotoclm150" href="classement.global.php?cc=150"><?= _('Display all'); ?></a>
			<a class="right_section_actions action_button action_gotoclm200" href="classement.global.php?cc=200"><?= _('Display all'); ?></a>
		</div>
		<?php
		if ($shouldShowAds) {
			?>
		<div class="pub_section">
			<!-- Pub latérale MKPC -->
			<ins class="adsbygoogle"
			     style="display:inline-block;width:300px;height:250px"
			     data-ad-client="ca-pub-1340724283777764"
			     data-ad-slot="4492555127"></ins>
			<script>
			(adsbygoogle = window.adsbygoogle || []).push({});
			</script>
		</div>
			<?php
		}
		?>
		<div class="subsection">
			<div class="flag_counter">
				<h3><?= _('Visitors since november 2017') ?></h3>
				<img src="https://s01.flagcounter.com/countxl/XMvG/bg_FFFFFF/txt_000000/border_CCCCCC/columns_3/maxflags_9/viewers_3/labels_0/pageviews_0/flags_0/percent_0/" alt="<?= _('Visitors') ?>" />
				<a class="right_section_actions action_button" href="topic.php?topic=2288"><?= _('Learn more') ?></a>
			</div>
		</div>
	</section>
	<div id="gallery" class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="pswp__bg"></div>
		<div class="pswp__scroll-wrap">
			<div class="pswp__container">
				<div class="pswp__item"></div>
				<div class="pswp__item"></div>
				<div class="pswp__item"></div>
			</div>
			<div class="pswp__ui pswp__ui--hidden">
				<div class="pswp__top-bar">
					<div class="pswp__counter"></div>
					<button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
					<button class="pswp__button pswp__button--share" title="Share"></button>
					<button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
					<button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
					<div class="pswp__preloader">
						<div class="pswp__preloader__icn">
							<div class="pswp__preloader__cut">
								<div class="pswp__preloader__donut"></div>
							</div>
						</div>
					</div>
				</div>
				<div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
					<div class="pswp__share-tooltip">
					</div>
				</div>
				<button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button>
				<button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button>
				<div class="pswp__caption">
					<div class="pswp__caption__center"></div>
				</div>
			</div>
		</div>
	</div>
</main>
<?php
include('../includes/footer.php');
mysql_close();
?>
<script>
var loadingMsg = "<?= _('Loading') ?>";
</script>
<script defer src="scripts/creations.js"></script>
<script defer src="scripts/posticons.js?reload=1"></script>
<script defer src="scripts/officials.js"></script>

<script defer src="scripts/jstz.min.js"></script>
<script defer src="scripts/splide.min.js"></script>
<script defer src="scripts/slider.js"></script>
<script defer src="scripts/photoswipe.min.js"></script>
<script defer src="scripts/init-diapos.js"></script>
<script defer src="scripts/sidebars.js"></script>
<script type="text/javascript">
var last_tz = '<?php echo isset($_COOKIE['tz']) ? addslashes($_COOKIE['tz']):''; ?>';
</script>
<script defer src="scripts/timezones.js"></script>
</body>
</html>
<?php
if ($rateLimitWrapper)
	$rateLimitWrapper();
?>