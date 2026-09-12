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
<html lang="<?= $locale ?>">
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
								<h3><?= t('home.mario_kart_game_browser') ?></h3>
								<div>
									<?= t('home.computer_version_famous_racing_game'); ?><br/>
									<?= t('home.this_game_completely_free_does'); ?>
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
								<h3><?= t('home.crazy_races_full_fun') ?></h3>
								<div>
									<?= t('home.try_fastest_while_avoiding_items') ?>
									<br />
									<?= t('home.race_all_56_tracks_original') ?>
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
								<h3><?= t('home.win_all_cups') ?></h3>
								<div>
									<?= t('home.face_off_cpus_14_grand') ?>
									<br />
									<?= t('home.win_enough_cups_unlock_15') ?>
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
								<h3><?= t('home.create_your_own_tracks') ?></h3>
								<div>
									<?= t('home.track_builder_possibilities_are_endless') ?>
									<br />
									<?= t('home.you_can_share_your_tracks') ?>
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
								<h3><?= t('home.face_players_around_world') ?></h3>
								<div>
									<?= t('home.race_battle_online_mode') ?>
									<br />
									<?= t('home.win_as_many_races_as') ?>
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
								<h3><?= t('home.make_best_scores_time_trial') ?></h3>
								<div>
									<?= t('home.finish_race_track_as_fast') ?>
									<br />
									<?= t('home.compare_your_scores_community_face') ?>
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
								<h3><?= t('home.release_your_fighting_talents') ?></h3>
								<div>
									<?= t('home.destroy_your_opponents_balloons_your') ?>
									<br />
									<?= t('home.last_player_standing_wins') ?>
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
								<h3><?= t('home.face_off_your_friends_local') ?></h3>
								<div>
									<?= t('home.prove_your_friends_that_youre')?>
									<br />
									<?= t('home.face_them_multiplayer_vs_races') ?>
								</div>
							</div>
						</div>
					</li>
				</ul>
			</div>
		</div>
		<h1>Mario Kart PC</h1>
		<div id="toBegin"><a href="mariokart.php">
		&#9660;&nbsp;<?= t('home.click_game_box_begin') ?>&nbsp;&#9660;<br />
		<img src="images/mkpc_box.jpg" alt="<?= t('home.start_game') ?>" /><br />
		&#9650;&nbsp;<?= t('home.click_game_box_begin') ?>&nbsp;&#9650;</a></div>
		<h2><img src="images/about.png" alt="" /> <?= t('home.whats_mario_kart_pc') ?></h2>
		<div>
			<p>
				<?= t('home.you_might_know_mario_kart') ?>
			</p>
			</p>
				<?= t('home.most_modes_mario_kart_have') ?>
				<br />
				
				<?= t('home.theres_also_brand_new_mode') ?>
				<br />
				
				<?= t('home.you_can_share_your_tracks_try', url: "creations.php") ?>
			</p>
			<p>
				<?= t('home.finally_you_can_face_players', url: "bestscores.php") ?>
			</p>
		</div>
		<h2><img src="images/camera.png" alt="" /> <?= t('home.some_screenshots') ?></h2>
		<div>
			<?= t('home.here_are_some_screenshots_game') ?>
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
		<h2><img src="images/thanks.png" alt="" /> <?= t('home.special_thanks') ?></h2>
		<div>
			<?= t('home.big_thanks_nintendo_these_three') ?>
				<ul>
					<li>
						<?= t('home.nihilogic_mario_kart_original_poc', url_main_site: "https://web.archive.org/web/20101104055946/http://blog.nihilogic.dk/", url_mario_kart: "https://web.archive.org/web/20100208144516/http://www.nihilogic.dk/labs/mariokart/") ?>
					</li>
					<li>
						<?= t('home.snesmaps_track_images', url_main_site: "http://www.snesmaps.com/", url_mario_kart: "http://www.snesmaps.com/maps/SuperMarioKart/SuperMarioKartMapSelect.html") ?>
					</li>
					<li>
						<?= t('home.khinsider_music', url_main_site: "https://downloads.khinsider.com/", url_mario_kart: "https://downloads.khinsider.com/search?search=mario+kart") ?>
					</li>
					<li>
						<?= t('home.many_more', url: "credits.php") ?>
					</li>
				</ul>
		</div>
		<h2><img src="images/follow.png" alt="" /> <?= t('home.follow_us') ?></h2>
		<div>
			<ul>
				<li>
					<?= t('home.discord_server_site_join_it', url: "https://discord.gg/VkeAxaj") ?>
				</li>
				<li>
					<?= t('home.official_youtube_channel_find_videos', url_youtube: "https://www.youtube.com/channel/UCRFoW7uwHuP1mg0qSaJ4jNg", url_topic: "topic.php?topic=3392") ?>
				</li>
				<li>
					<?= t('home.github_repo_site_follow_all', url: "https://github.com/tmalahie/mkpc") ?>
				</li>
				<li>
					<?= t('home.mkpc_wiki_find_out_all', url_wiki: "http://fr.wiki-mario-kart-pc.wikia.com/", url_topic: "topic.php?topic=343") ?>
				</li>
			</ul>
			<p>
				<em>
				<?= t('home.this_site_mostly_maintained_french', url_topic: "topic.php?topic=1") ?>
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
		<h2><img src="images/gamepad.png" alt="" /> <?= t('home.go_game') ?></h2>
		<div>
			<?= t('home.start_playing_its_very_simple') ?><br />
				<a href="mariokart.php" class="action_button button_game"><?= t('home.start_playing_now') ?></a>
		</div>
	</section>
	<section id="right_section">
		<?php
		require_once('../includes/utils-date.php');
		/*if ($id) {
			//$today = time();
			//if (($today > 1607310000) && ($today < 1607914800)) {
			$getMkwcVotes = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkwcbets WHERE player = ' . $id .' AND console="mkw"'));
			$alreadyVoted = ($getMkwcVotes['nb'] >= 1);
			if (!$alreadyVoted) {
				if ($language) {
				?>
				<div class="subsection">
					<div id="official_message" style="font-size: 0.9em; text-align: left">
						The <strong>2025 Mario Kart World Cup</strong> has begun!<br />
						Come and <a href="mkwc.php">vote here</a> for your favorite team!<br />
						For more information, read the related <a href="news.php?id=15280">news</a>.
					</div>
				</div>
				<?php
				}
				else {
				?>
				<div class="subsection">
					<div id="official_message" style="font-size: 0.9em; text-align: left">
						La <strong>Coupe Du Monde 2025 de Mario Kart</strong> à débuté !<br />
						Venez <a href="mkwc.php">voter ici</a> pour votre équipe préférée !<br />
						Pour plus d'information, consultez la <a href="news.php?id=15280">news</a> associée.
					</div>
				</div>
				<?php
				}
			}
		}*/
	require_once('../includes/home-utils.php');
	function uc_strlen($str) { return home_uc_strlen($str); }
	function controlLength($str, $maxLength) { return home_controlLength($str, $maxLength); }
	function controlLengthUtf8($str, $len) { return home_controlLengthUtf8($str, $len); }
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
			if (($getWarn = mysql_fetch_array(mysql_query('SELECT seen FROM mkwarns WHERE player="'. $id .'" AND (end_date IS NULL OR end_date>=CURDATE())'))) && !$getWarn['seen']) {
				?>
				<div class="warning-top-message">
					<?= t('home.you_have_received_warning_inappropriate', url: 'forum.php?warn#compte'); ?>
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
					<?= t('home.happy_birthday') ?>
					<?php
					for ($i=0;$i<$nbBirthdays;$i++) {
						$birthday = $birthdaysList[$i];
						if ($i)
							echo ($i==$nbBirthdays-1) ? t('home.birthday_and') : ", ";
						echo '<a href="profil.php?id='. $birthday['id'] .'">'. $birthday['nom'] .'</a>';
					}
					echo t('home.birthday_exclamation');
					?>
				</div>
				<?php
			}
		}
		date_default_timezone_set('UTC');
		display_sidebar('Forum', 'forum.php');
		?>
			<h2><?= t('home.latest_topics') ?></h2>
			<div id="forum_section" class="right_subsection" data-section="topics" data-offset="10" data-limit="10">
				<?php
				$topics = getLatestTopics(10, 0, $id);
				echo renderTopicItems($topics);
				unset($topics);
				?>
			</div>
			<a class="right_section_actions action_button" href="forum.php"><?= t('home.go_forum') ?></a>
		</div>
		<div class="subsection">
		<?php
		display_sidebar('News', 'listNews.php');
		?>
			<h2><?= t('home.latest_news') ?></h2>
			<div id="news_section" class="right_subsection" data-section="news" data-offset="8" data-limit="8">
				<?php
				$newsList = getLatestNews(8, 0, $id);
				if (count($newsList) > 0) {
					echo renderNewsItems($newsList);
				} else {
					echo '<div style="text-align:center;margin-top:55px">' . t('home.no_news_yet') . '</div>';
				}
				unset($newsList);
				?>
			</div>
			<?php
			if (hasRight('publisher')) {
				$getPendingNews = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mknews WHERE status="pending"'));
				if ($getPendingNews['nb']) {
					?>
					<p class="nb-pending-news">
						<?= t('home.pending_news', count: $getPendingNews['nb'], url: 'listNews.php#pending-news') ?>
					</p>
					<?php
				}
			}
			?>
			<a class="right_section_actions action_button" href="listNews.php"><?= t('home.all_news') ?></a>
		</div>
		<?php
		/*if ($id) {
			?>
		<div class="subsection">
			<?php
			display_sidebar('MKPC Tri-Nations', 'news.php?id=15069');
			?>
			<h2><?= t('home.current_bracket') ?></h2>
			<div id="tri-nations" class="right_subsection">
			<table>
					<tr>
						<th><?= t('home.rank') ?></th>
						<th><?= t('home.team') ?></th>
						<th>Pts</th>
						<th class="pl-l" title="<?= t('home.wins_ties_losses') ?>"><?= t('home.w_t_l') ?></th>
						<th class="pl-xl" title="<?= t('home.score_difference') ?>"><?= t('home.diff') ?></th>
					</tr>
					<?php
					$plRanking = array(
						array(
							'icon' => 'ea.png',
							'name' => t('home.eurasia'),
							'score' => 9,
							'wins' => 3,
							'losses' => 1,
							'ties' => 0,
							'diff' => 148
						),
						array(
							'icon' => 'fr.png',
							'name' => t('home.france'),
							'score' => 9,
							'wins' => 3,
							'losses' => 1,
							'ties' => 0,
							'diff' => 68
						),
						array(
							'icon' => 'am.png',
							'name' => t('home.americas'),
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
			<div class="link-extra"><a href="https://discord.gg/dPerbeFc36" target="_blank"><?= t('home.tournaments_discord_server') ?></a></div>
		</div>
			<?php
		}*/
		?>
		<div class="subsection">
			<?php
			display_sidebar(t('home.track_builder'), 'creations.php');
			?>
			<h2><?= t('home.latest_creations') ?></h2>
			<div id="creations_section" class="right_subsection" data-section="creations" data-offset="14" data-limit="14">
				<table>
					<?php
					function getNom($circuit) { return home_getNom($circuit, $GLOBALS['language']); }
					function getAuteur($circuit) { return home_getAuteur($circuit, $GLOBALS['language']); }
					
					$tracksList = getLatestCreations(14, 0);
					echo renderCreationItems($tracksList);
					?>
				</table>
			</div>
			<a class="right_section_actions action_button" href="creations.php"><?= t('home.display_all') ?></a>
			<h2><?= t('home.latest_challenges') ?></h2>
			<div id="challenges_section" class="right_subsection" data-section="challenges" data-offset="15" data-limit="15">
				<?php
				$challenges = getLatestChallenges(15, 0, $id);
				echo renderChallengeItems($challenges);
				?>
			</div>
			<?php
			if (hasRight('clvalidator')) {
				$getPendingChallenges = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkchallenges WHERE status="pending_moderation"'));
				if ($getPendingChallenges['nb']) {
					echo '<p class="nb-pending-news">';
					echo t('home.pending_challenge_count', count: $getPendingChallenges['nb'], url: 'challengesList.php?moderate');
					echo '</p>';
				}
			}
			?>
			<a class="right_section_actions action_button" href="challengesList.php"><?= t('home.display_all') ?></a>
			<div id="challenge_ranking"><a href="challengeRanking.php"><?= t('home.challenge_points_leaderboard') ?></a></div>
			<h2><?= t('home.recent_activity') ?></h2>
			<div id="comments_section" class="right_subsection" data-section="activity" data-offset="14" data-limit="14">
				<?php
				$activities = getRecentActivity(14, 0);
				echo renderActivityItems($activities);
				?>
			</div>
		</div>
		<div class="subsection rank_vs" id="rankings_section">
			<?php
			display_sidebar(t('common.online_mode'), 'online.php');
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
			// A gathering ranked lineup belongs in the same list as any other online game, so it
			// is collected here and folded into the VS tab below. Only advertised to someone who
			// could join it: past the entry criteria, and inside that tier's MMR band.
			$loungeQueues = array();
			$loungeMulticup = 0;
			if ($id) {
				require_once('../includes/lounge/common.php');
				$loungeMulticup = lounge_get_season_multicup();
				if ($loungeMulticup)
					$loungeQueues = lounge_open_queues_for($id);
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
					if (!$res) $res = t('common.untitled');
					return controlLengthUtf8($res,30);
				}
				function get_mode_string(&$params) {
					global $publicLinksData;
					$link = $params['link'];
					$modeNames = array(
						'cc' => '${value}cc',
						'mirror' => t('home.mirror'),
						'team' => t('home.team'),
						'friendly' => t('home.friendly')
					);
					$publicLinkData = $publicLinksData[$link];
					$enabledModes = array();
					foreach ($modeNames as $option => $value) {
						if (isset($publicLinkData->$option))
							$enabledModes[$option] = str_replace('${value}', $publicLinkData->$option, $value);
					}
					if (empty($enabledModes))
						return t('home.normal');
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
						$title .= $activePlayer['nom'] .' ('. $pts .' pt'. (($pts != 1) ? 's':'') .')';
						$i++;
					}
					echo '<span class="ranking_activeplayernb" title="'. $title .'">';
					echo t('home.member_count', count: $nbActivePlayers);
					echo '</span>';
					if (!empty($params['cup'])) {
						echo ' ';
						if ($params['game'])
							$theCircuit = t('common.arena');
						else {
							$isMCup = ($params['mode']==8);
							$isSingle = (($params['mode']%4)>=2);
							if ($isMCup)
								$theCircuit = t('common.multicup');
							elseif ($isSingle)
								$theCircuit = t('common.circuit');
							else
								$theCircuit = t('common.cup');
						}
						echo t('home.online_game_on_track') . $theCircuit;
						echo ' ';
						echo '<strong>';
						echo get_creation_string($params);
						echo '</strong>';
					}
					elseif (!empty($params)) {
						echo t('home.online_game_mode', mode: get_mode_string($params));
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
					echo '<a class="action_button" href="'. $url .'">'. t('home.join') .'</a>';
				}
				// The trophy stands in for the bullet, so a ranked lineup is tellable from a normal
				// game at a glance, and doubles as the way to find out what one is.
				function print_lounge_line($loungeQueue) {
					global $language, $loungeMulticup;
					echo '<li class="ranked_game">';
					echo '<a class="ranked_game_icon ranking_fancytitle" href="topic.php?topic=15006" target="_blank"'
						.' title="'. _('Ranked game - click for details') .'">'
						.'<img src="images/cups/cup1.png" alt="'. _('Ranked') .'" /></a>';
					$loungeNames = array();
					foreach ($loungeQueue['members'] as $loungeMember)
						$loungeNames[] = $loungeMember['name'] .' (MMR '. $loungeMember['mmr'] .')';
					echo '<span class="ranking_activeplayernb" title="'. htmlspecialchars(implode(', ', $loungeNames)) .'">';
					echo FN_("{count} member", "{count} members", count: $loungeQueue['players']);
					echo '</span> ';
					echo P_("circuit", "in ");
					echo '<strong>'. htmlspecialchars($language ? $loungeQueue['label_en'] : $loungeQueue['label_fr']) .'</strong>';
					// ranked.php's destination: online.php is where a character gets picked, and
					// the lounge opens over it once one has been
					echo '<a class="action_button" href="online.php?mid='. $loungeMulticup .'&amp;ranked">'. _('Join') .'</a>';
					echo '</li>';
				}
				function print_active_players($game,$type) {
					global $activePlayers, $activePlayersByLink, $loungeQueues;
					$lounge = $game ? array() : $loungeQueues;
					if (!empty($activePlayers[$game]) || !empty($lounge)) {
						echo '<div class="ranking_current" id="ranking_current_'.$type.'">';
						$firstPlayer = !empty($activePlayers[$game]) ? reset($activePlayers[$game]) : null;
						if ($firstPlayer && empty($lounge) && (count($activePlayersByLink[$game]) < 2) && !$firstPlayer['link'] && !$firstPlayer['cup']) {
							echo '<span class="ranking_list">';
							echo t('home.currently_online');
							echo ' ';
							print_players_raw($activePlayers[$game]);
							print_join_button($firstPlayer);
							echo '</span>';
							echo ' ';
						}
						else {
							echo t('home.currently_online');
							echo '<ul class="ranking_list_game">';
							if (!empty($activePlayers[$game])) {
								foreach ($activePlayersByLink[$game] as $players) {
									echo '<li>';
									$params = reset($players);
									print_players_raw($players, $params);
									print_join_button($params);
									echo '</li>';
								}
							}
							foreach ($lounge as $loungeQueue)
								print_lounge_line($loungeQueue);
							echo '</ul>';
						}
						echo '</div>';
					}
				}
				?>
				<a class="ranking_tab tab_vs" href="javascript:dispRankTab(0)">
					<?= t('home.vs_mode') ?>
				</a><a class="ranking_tab tab_battle" href="javascript:dispRankTab(1)">
					<?= t('home.battle') ?>
					<?php print_badge(1); ?>
				</a><a class="ranking_tab tab_clm tab_clm150" href="javascript:dispRankTab(currenttabcc)">
					<?= t('home.time_trial') ?>
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
							<th><?= t('home.rank') ?></th>
							<th><?= t('home.nick') ?></th>
							<th><?= t('home.score') ?></th>
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
			<a class="right_section_actions action_button action_gotovs" href="bestscores.php"><?= t('home.display_all'); ?></a>
			<a class="right_section_actions action_button action_gotobattle" href="bestscores.php?battle"><?= t('home.display_all'); ?></a>
			<a class="right_section_actions action_button action_gotoclm150" href="classement.global.php?cc=150"><?= t('home.display_all'); ?></a>
			<a class="right_section_actions action_button action_gotoclm200" href="classement.global.php?cc=200"><?= t('home.display_all'); ?></a>
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
				<h3><?= t('home.visitors_since_november_2017') ?></h3>
				<img src="https://s01.flagcounter.com/countxl/XMvG/bg_FFFFFF/txt_000000/border_CCCCCC/columns_3/maxflags_9/viewers_3/labels_0/pageviews_0/flags_0/percent_0/" alt="<?= t('home.visitors') ?>" />
				<a class="right_section_actions action_button" href="topic.php?topic=2288"><?= t('home.learn_more') ?></a>
			</div>
		</div>
	</section>
	<?php
	if (!$id) {
		?>
		<div id="final_call_to_action">
			<a href="mariokart.php">
				<img src="images/gamepad.png" alt="Play" />
				<span><?= t('home.click_here_start_playing') ?></span>
			</a>
		</div>
		<?php
	}
	?>
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
var loadingMsg = "<?= t('home.loading') ?>";
</script>
<script defer src="scripts/creations.js"></script>
<script defer src="scripts/home-sections.js"></script>
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