<?php
include('initdb.php');
if (isset($_SERVER['HTTP_REFERER']) && ($_SERVER['HTTP_REFERER'] != '')) {
	function startsWith($haystack, $needle) {
		// search backwards starting from haystack length characters from the end
		return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}
	if (!startsWith($_SERVER['HTTP_REFERER'],'https://mkpc.malahieude.net/'))
		mysql_query('INSERT INTO `previouspages` VALUES("'. mysql_real_escape_string($_SERVER['HTTP_REFERER']) .'")');
}
include('language.php');
include('session.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" href="styles/slider.css" />
<link rel="stylesheet" href="styles/photoswipe.css" />
<?php
include('o_online.php');
?>
</head>
<body>
<?php
include('header.php');
$page = 'home';
$homepage = true;
include('menu.php');
if ($id && $myIdentifiants) {
	mysql_query('INSERT INTO `mkips` VALUES("'.$id.'","'.$myIdentifiants[0].'","'.$myIdentifiants[1].'","'.$myIdentifiants[2].'","'.$myIdentifiants[3].'")');
	mysql_query('INSERT INTO `mkbrowsers` VALUES("'.$id.'","'.mysql_real_escape_string($_SERVER['HTTP_USER_AGENT']).'")');
}
$slidesPath = 'images/slides';
?>
<main>
	<section id="left_section">
		<div class="fp-slider">
			<div class="fp-slides-container">
				<div class="fp-slides">
					<div class="fp-slides-items">
						<div class="fp-thumbnail" style="background: url('<?php echo $slidesPath; ?>/diapo1.jpg?reload=1') top">
						</div>
						<div class="fp-content-wrap">
							<div class="fp-content">
								<h3 class="fp-title"><?php echo $language ? 'A Mario Kart Game for browser':'Un jeu de Mario Kart sur navigateur'; ?></h3>
								<p>
									<?php
									if ($language) {
										?>
										A computer version of the famous racing game by Nintendo.<br />
										This is <strong>completely free</strong> and does not require <strong>any download</strong>. All you need is a web browser!
										<?php
									}
									else {
										?>
										Une version ordi du célèbre jeu de course de Nintendo.<br />
										Ce jeu est <strong>entièrement gratuit</strong> et ne requiert <strong>aucun téléchargement</strong>, un simple navigateur internet suffit.
										<?php
									}
									?>
								</p>
							</div>
							<div class="fp-prev-next-wrap">
								<a class="fp-next" href="#fp-next"></a>
								<a class="fp-prev" href="#fp-prev"></a>
							</div>
						</div>
					</div>
					
					<div class="fp-slides-items">
						<div class="fp-thumbnail" data-img="<?php echo $slidesPath; ?>/diapo2.jpg?reload=1">
						</div>
						<div class="fp-content-wrap">
							<div class="fp-content">
								<h3 class="fp-title"><?php echo $language ? 'Crazy races full of fun!':'Des courses acharnées et pleines de fun !'; ?></h3>
								<p>
									<?php
									if ($language) {
										?>
										Try to be the fastest while avoiding the items!<br />
										Find all the <strong>56 tracks</strong> from the original games <strong>Super Mario Kart</strong>, <strong>Mario Kart Super Circuit</strong> and <strong>Mario Kart DS</strong>.
										<?php
									}
									else {
										?>
										Tentez d'être le plus rapide tout en évitant les objets !<br />
										Retrouvez l'intégralité des <strong>56 circuits</strong> repris du jeu original <strong>Super Mario Kart</strong>, <strong>Mario Kart Super Circuit</strong> et <strong>Mario Kart DS</strong>.
										<?php
									}
									?>
								</p>
							</div>
							<div class="fp-prev-next-wrap">
								<a class="fp-next" href="#fp-next"></a>
								<a class="fp-prev" href="#fp-prev"></a>
							</div>
						</div>
					</div>
					
					<div class="fp-slides-items">
						<div class="fp-thumbnail" data-img="<?php echo $slidesPath; ?>/diapo3.png?reload=1">
						</div>
						<div class="fp-content-wrap">
							<div class="fp-content">
								<h3 class="fp-title"><?php echo $language ? 'Win all the Grand Prix!':'Remportez tous les grands prix !'; ?></h3>
								<p>
									<?php
									if ($language) {
										?>
										Face off the cpu on the <strong>14 grands prix</strong> tournaments and try to win the gold cup!<br />
										Win enough cups to unlock the <strong>15 secret characters</strong>!
										<?php
									}
									else {
										?>
										Affrontez les ordis sur les <strong>14 grands prix</strong> et tentez de gagner la coupe en or !<br />
										Remportez suffisament de coupes pour débloquer les <strong>15 persos secrets</strong> !
										<?php
									}
									?>
								</p>
							</div>
							<div class="fp-prev-next-wrap">
								<a class="fp-next" href="#fp-next"></a>
								<a class="fp-prev" href="#fp-prev"></a>
							</div>
						</div>
					</div>
					
					<div class="fp-slides-items">
						<div class="fp-thumbnail" data-img="<?php echo $slidesPath; ?>/diapo4.png?reload=1">
						</div>
						<div class="fp-content-wrap">
							<div class="fp-content">
								<h3 class="fp-title"><?php echo $language ? 'Create your own tracks!':'Créez vos propres circuits !'; ?></h3>
								<p>
									<?php
									if ($language) {
										?>
										With the <strong>track builder</strong>, the possibilities are infinite: the only limit is your own imagination<br />
										Try other peoples' creations thanks to the integrated <strong>sharing tool</strong>.
										<?php
									}
									else {
										?>
										Avec l'<strong>éditeur de circuits</strong> et d'arènes, les possibilités sont infinies ; votre imagination est la seule limite !<br />
										Essayer les créations des autres grâce à l'<strong>outil de partage intégré</strong>.
										<?php
									}
									?>
								</p>
							</div>
							<div class="fp-prev-next-wrap">
								<a class="fp-next" href="#fp-next"></a>
								<a class="fp-prev" href="#fp-prev"></a>
							</div>
						</div>
					</div>
					
					<div class="fp-slides-items">
						<div class="fp-thumbnail" data-img="<?php echo $slidesPath; ?>/diapo5.jpg?reload=1">
						</div>
						<div class="fp-content-wrap">
							<div class="fp-content">
								<h3 class="fp-title"><?php echo $language ? 'Face players from around the world!':'Affrontez les joueurs du monde entier !'; ?></h3>
								<p>
									<?php
									if ($language) {
										?>
										Fight the other players with the <strong>online mode</strong>!<br />
										Win as many races as possible and <strong>climb in the official ranking</strong>!
										<?php
									}
									else {
										?>
										Battez-vous contre d'autres joueurs avec le <strong>mode en ligne</strong> !<br />
										Remportez un maximum de course afin de <strong>grimper dans le classement</strong> officiel !
										<?php
									}
									?>
								</p>
							</div>
							<div class="fp-prev-next-wrap">
								<a class="fp-next" href="#fp-next"></a>
								<a class="fp-prev" href="#fp-prev"></a>
							</div>
						</div>
					</div>
					
					<div class="fp-slides-items">
						<div class="fp-thumbnail" data-img="<?php echo $slidesPath; ?>/diapo6.jpg?reload=1">
						</div>
						<div class="fp-content-wrap">
							<div class="fp-content">
								<h3 class="fp-title"><?php echo $language ? 'Make the best scores in time trial!':'Réalisez les meilleurs temps en contre-la-montre !'; ?></h3>
								<p>
									<?php
									if ($language) {
										?>
										<strong>Finish the race course</strong> as fast as you can!<br />
										<strong>Compare your scores</strong> with the community, and face other players' ghosts!
										<?php
									}
									else {
										?>
										<strong>Bouclez les 3 tours</strong> le plus rapidement possible !<br />
										<strong>Comparez votre score</strong> avec la communauté, et affrontez les fantômes des autres joueurs !
										<?php
									}
									?>
								</p>
							</div>
							<div class="fp-prev-next-wrap">
								<a class="fp-next" href="#fp-next"></a>
								<a class="fp-prev" href="#fp-prev"></a>
							</div>
						</div>
					</div>
					
					<div class="fp-slides-items">
						<div class="fp-thumbnail" data-img="<?php echo $slidesPath; ?>/diapo7.jpg?reload=1">
						</div>
						<div class="fp-content-wrap">
							<div class="fp-content">
								<h3 class="fp-title"><?php echo $language ? 'Release your fighter talents!':'Montrez vos talents de combattant !'; ?></h3>
								<p>
									<?php
									if ($language) {
										?>
										<strong>Destroy the balloons</strong> of your opponents with your own items without getting hit by theirs<br />
										The last player standing wins!
										<?php
									}
									else {
										?>
										<strong>Détruisez les ballons</strong> de votre adversaire en évitant de vous faire toucher !<br />
										Soyez le dernier survivant pour remporter la partie !
										<?php
									}
									?>
								</p>
							</div>
							<div class="fp-prev-next-wrap">
								<a class="fp-next" href="#fp-next"></a>
								<a class="fp-prev" href="#fp-prev"></a>
							</div>
						</div>
					</div>
					
					<div class="fp-slides-items">
						<div class="fp-thumbnail" style="background-image: url('<?php echo $slidesPath; ?>/diapo8.png?reload=1')">
						</div>
						<div class="fp-content-wrap">
							<div class="fp-content">
								<h3 class="fp-title"><?php echo $language ? 'Face off your friends with the local multiplayer mode!':'Affrontez vos amis grâce au mode multijoueur !'; ?></h3>
								<p>
									<?php
									if ($language) {
										?>
										Prove your friends that you're the best!<br />
										Face them in <strong>multiplayer</strong> on VS races or on battle mode.
										<?php
									}
									else {
										?>

										Montrez à vos amis que vous êtes le meilleur !<br />
										Affrontez-les en <strong>multijoueur</strong> en course VS ou sur les batailles de ballons.
										<?php
									}
									?>
								</p>
							</div>
							<div class="fp-prev-next-wrap">
								<a class="fp-next" href="#fp-next"></a>
								<a class="fp-prev" href="#fp-prev"></a>
							</div>
						</div>
					</div>
				</div>
				<div class="fp-nav">
					<span class="fp-pager"></span>
				</div>
			</div>
		</div>
		<div id="toBegin"><a href="mariokart.php">
		&#9660;&nbsp;<?php echo $language ? 'Click on the game box to begin': 'Cliquez sur la bo&icirc;te du jeu pour commencer'; ?>&nbsp;&#9660;<br />
		<img src="images/mkpc_box.jpg" alt="Acc&eacute;der au jeu" style="width:310px;position: relative;top:2px" /><br />
		&#9650;&nbsp;<?php echo $language ? 'Click on the game box to begin': 'Cliquez sur la bo&icirc;te du jeu pour commencer'; ?>&nbsp;&#9650;</a></div>
		<?php
		if ($language) {
			?>
			<p><em>Note that this game is translated by a 17-year-old French student.<br />
			There may be some mistakes. If you see one, please inform me on this <a href="topic.php?topic=1">forum topic</a>.</em></p>
			<?php
		}
		?>
		<h1><img src="images/about.png" alt="about" /> <?php echo $language ? 'What\'s Mario Kart PC?':'Mario Kart PC, c\'est quoi ?'; ?></h1>
		<div>
			<?php
			if ($language) {
				?>
				<p>You might know Mario Kart, the funniest racing game series of all time!
				Mario Kart PC uses the same base as the original games but is playable on your browser, and <strong>for free</strong>.</p>
				<p>Most of the modes from Mario Kart have been included: Grand Prix, VS, Battle mode, Time Trials, and more!<br />
				There's also a brand new mode: the <strong>track builder</strong>! Place straight lines and turns, add items, boost panels and more!
				Everything is customizable! The only limit is your own imagination!<br />
				You can share your tracks, and try other people's tracks thanks to the <a href="creations.php">sharing tool</a>. Thousands of custom tracks are already available!</p>
				<p>Finally, you can face players from the whole world thanks to the <strong>multiplayer online mode</strong>! Climb the <a href="bestscores.php">rankings</a> and become world champion!</p>
				<?php
			}
			else {
				?>
				<p>Vous connaissez certainement Mario Kart, le jeu de course le plus fun de tous les temps !
				Mario Kart PC reprend les mêmes principes que le jeu original mais il est jouable sur navigateur, et <strong>gratuitement</strong>.</p>
				<p>La plupart des modes issus de Mario Kart ont été repris : Grand Prix, courses VS, batailles de ballons, contre-la-montre...<br />
				Et un dernier mode inédit : l'<strong>éditeur de circuits</strong> ! Placez les lignes droites et les virages, ajoutez les objets, insérez des accélérateurs...
				Tout est personnalisable ! Votre imagination est la seule limite !<br />
				Vous pouvez également partager vos créations et essayer celles des autres grâce à l'<a href="creations.php">outil de partage</a>.
				Plusieurs milliers de circuits ont déjà été partagés !</p>
				<p>Enfin, il est possible d'affronter les joueurs du monde entier grâce au <strong>mode multijoueurs en ligne</strong> ! Grimpez dans le <a href="bestscores.php">classement</a> et devenez champion du monde !</p>
				<?php
			}
			?>
		</div>
		<h1><img src="images/camera.png" alt="camera" /> <?php echo $language ? 'Some screenshots':'Quelques screenshots'; ?></h1>
		<div>
			<?php
			if ($language)
				echo 'Here are some screenshots of the game to give you a quick preview of what it looks like:';
			else
				echo 'Une image vaut mieux qu\'un long discours, voici donc quelques captures d\'écran issues du jeu afin que vous ayez un aperçu de ce à quoi ça ressemble :';
			?>
			<table id="screenshots" class="demo-gallery">
				<?php
				for ($i=1;$i<=12;$i++) {
					if (!(($i-1)%3))
						echo '<tr>';
					echo '<td>';
					$url_img = "images/screenshots/ss$i.png";
					$url_thumb = 'images/screenshots/ss'.$i.'xs.png';
					echo '<a href="'. $url_img .'" data-size="960x468" data-med="'. $url_img .'" data-med-size="240x117" class="demo-gallery__photo demo-gallery__img--main"><img src="'.$url_thumb.'" alt="Screenshot '. $i .'" /></a>';
					echo '</td>';
					if (!($i%3))
						echo '</tr>';
				}
				?>
			</table>
		</div>
		<h1><img src="images/thanks.png" alt="Special thanks" /> <?php echo $language ? 'Special thanks':'Remerciements'; ?></h1>
		<div>
			<?php
			if ($language) {
				?>
				A big thanks to Nintendo, these three sites and these artists without which Mario Kart PC would have probably never existed !
				<ul>
					<li><a href="https://web.archive.org/web/20101104055946/http://blog.nihilogic.dk/">Nihilogic</a> for the <a href="https://web.archive.org/web/20100208144516/http://www.nihilogic.dk/labs/mariokart/">basic Mario Kart</a></li>
					<li><a href="http://www.snesmaps.com/">SNESMaps</a> for the <a href="http://www.snesmaps.com/maps/SuperMarioKart/SuperMarioKartMapSelect.html">track images</a></li>
					<li><a href="http://www.vgmusic.com/">VGmusic</a> for the <a href="http://www.vgmusic.com/music/console/nintendo/snes/index-sz.html#Super_Mario_Kart">musics</a></li>
					<li>And <a href="credits.php">many more</a>!</li>
				</ul>
				<?php
			}
			else {
				?>
				Un grand merci à Nintendo, ces 3 sites et ces artistes sans lesquels Mario Kart PC n'aurait probablement jamais existé !
				<ul>
					<li><a href="https://web.archive.org/web/20101104055946/http://blog.nihilogic.dk/">Nihilogic</a> pour le <a href="https://web.archive.org/web/20100208144516/http://www.nihilogic.dk/labs/mariokart/">Mario Kart de départ</a></li>
					<li><a href="http://www.snesmaps.com/">SNESMaps</a> pour les <a href="http://www.snesmaps.com/maps/SuperMarioKart/SuperMarioKartMapSelect.html">images des circuits</a></li>
					<li><a href="https://downloads.khinsider.com/">Khinsider</a> pour les <a href="https://downloads.khinsider.com/search?search=mario+kart">musiques</a></li>
					<li>Et <a href="credits.php">bien d\'autres</a> !</li>
				</ul>
				<?php
			}
			?>
		</div>
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
		<h1><img src="images/partners.png" alt="Partners" /> <?php echo $language ? 'Partners':'Partenaires'; ?></h1>
		<div>
			<?php
			if ($language) {
				?>
				Mario Kart PC currently has 2 partners:
				<ul>
					<li>
						<a href="http://www.vanilladome.fr/">Vanilla Dome</a>, the social network of Mario!
						Forum, musics, quiz, encyclopedia... Everything related directly or indirectly to Mario is available on this site!
					</li>
					<li>
						<a href="http://www.planete-toad.fr/">Planète Toad</a>: Community site about Mario and the Nintendo universe in general. Offers exclusive services: publications system, shop, "Shrooms" currency.
					</li>
				</ul>
				<?php
			}
			else {
				?>
				Mario Kart PC compte actuellement 2 partenaires :
				<ul>
					<li>
						<a href="http://www.vanilladome.fr/">Vanilla Dome</a> : le réseau social Mario !
						Forum, musiques, quiz, encyclopédie... Tout ce qui touche de près ou de loin à Mario est disponible sur ce site !
					</li>
					<li>
						<a href="http://www.planete-toad.fr/">Planète Toad</a> : Site communautaire sur Mario et l'univers de Nintendo en général. Propose des services exclusifs : système de publications, boutique, monnaie "Champis".
					</li>
				</ul>
				<?php
			}
			?>
			<p>
				<?php
				if ($language)
					echo 'You want to become partner of Mario Kart PC ? Contact me by email at <a href="mailto:t.malahieude@gmail.com?subject=Mario%20Kart%20PC%20partnership">t.malahieude@gmail.com</a>.';
				else
					echo 'Vous souhaitez devenir partenaire de Mario Kart PC ? Contactez-moi par e-mail à <a href="mailto:t.malahieude@gmail.com?subject=Partenariat%20Mario%20Kart%20PC">t.malahieude@gmail.com</a>.';
				?>
			</p>
		</div>
		<h1><img src="images/follow.png" alt="Follow" /> <?php echo $language ? 'Follow us':'Nous suivre'; ?></h1>
		<div>
			<?php
			if ($language) {
				?>
				<ul>
					<li><a href="https://www.youtube.com/channel/UCRFoW7uwHuP1mg0qSaJ4jNg">Official Youtube Channel</a> : find out videos about the game and informations about the website and its events. The channel is maintained by members, if you want to participate, tell it on the <a href="topic.php?topic=3392">official topic</a>.</li>
					<li><a href="https://twitter.com/MarioKartPC">Twitter Page</a> and <a href="https://www.facebook.com/groups/126497814060671/">Facebook Group</a> of the game: follow then to be informed of the latest news concerning the site!</li>
					<li><a href="http://fr.wiki-mario-kart-pc.wikia.com/">MKPC Wiki</a>: find out all the information about the game and its history. This site is maintained by the community, if you want to contribute, tell it on the <a href="topic.php?topic=343">this topic</a>!</li>
				</ul>
				<?php
			}
			else {
				?>
				<ul>
					<li><a href="https://www.youtube.com/channel/UCRFoW7uwHuP1mg0qSaJ4jNg">Chaîne Youtube Officielle</a> : retrouvez des vidéos sur le jeu et des informations sur le site et ses évenements. La chaîne est alimentée par les membres, si vous voulez participez, parlez-en sur <a href="topic.php?topic=3392">le topic officiel</a>.</li>
					<li><a href="https://twitter.com/MarioKartPC">Page Twitter</a> et <a href="https://www.facebook.com/groups/126497814060671/">Groupe Facebook</a> du jeu : suivez-les pour être au courant des dernières actualités du site !</li>
					<li><a href="http://fr.wiki-mario-kart-pc.wikia.com/">Wiki MKPC</a> : retrouvez toutes les informations sur le jeu et son histoire. Ce site est maintenu par les membres, si vous voulez contribuer, parlez-en sur <a href="topic.php?topic=343">ce topic</a> !</li>
				</ul>
				<?php
			}
			?>
		</div>
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
		<h1><img src="images/gamepad.png" alt="Gamepad" /> <?php echo $language ? 'Go to the game':'Accéder au jeu'; ?></h1>
		<div>
			<?php
			if ($language) {
				?>
				To start playing, it's very simple, just click on &quot;the game&quot; in the menu above. Or more simply, click here:<br />
				<a href="mariokart.php" class="action_button button_game">Start playing now &gt;</a>
				<?php
			}
			else {
				?>
				Pour commencer à jouer, c'est très simple, cliquez sur &quot;Le jeu&quot; dans le menu en haut. Ou plus simplement, cliquez là :<br />
				<a href="mariokart.php" class="action_button button_game">Commencer à jouer &gt;</a>
				<?php
			}
			?>
		</div>
	</section>
	<section id="right_section">
		<?php
		require_once('utils-date.php');
		/*if ($id) {
			$majHourParis = '2020-04-04 18:00:00';
			$majDay = to_local_tz($majHourParis, 'd');
			$now = new \Datetime('now', new \DateTimeZone(get_client_tz()));
			$isToday = ($majDay == $now->format('d'));
			$majHour = to_local_tz($majHourParis, $language ? 'gA':'G\h');
			if ($language) {
			?>
			<div class="subsection">
				<div id="official_message" style="font-size: 0.9em">
					<a href="https://youtu.be/k1-v-mG-4fU" target="_blank">Special trailer</a> scheduled for <strong><?php echo ($isToday ? 'today':'tomorrow'); ?> at <?php echo $majHour; ?></strong>!<br />
					Don't miss the video
					which will give all the info on the next big update. See you soon <span style="font-size:1.3em;line-height:1em;position:relative;top:0.05em">☺</span>
				</div>
			</div>
			<?php
			}
			else {
			?>
			<div class="subsection">
				<div id="official_message" style="font-size: 0.9em">
					<a href="https://youtu.be/k1-v-mG-4fU" target="_blank">Bande annonce spéciale</a> prévue <strong><?php echo ($isToday ? 'aujourd\'hui':'demain'); ?> à <?php echo $majHour; ?></strong>&nbsp;!<br />
					Ne manquez pas cette vidéo qui donnera toutes les infos sur la prochaine grosse MàJ. À très vite <span style="font-size:1.3em;line-height:1em;position:relative;top:0.05em">☺</span>
				</div>
			</div>
			<?php
			}
		}*/
		function uc_strlen($str) {
			return strlen(preg_replace("/%u([0-9a-fA-F]{4})/", ".", $str));
		}
		function uc_substr($str, $l) {
			preg_match_all('/%u[0-9a-fA-F]{4}/', $str, $positions, PREG_OFFSET_CAPTURE);
			$positions = $positions[0];
			$res = mb_substr(preg_replace("/%u([0-9a-fA-F]{4})/", ".", $str), 0,$l);
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
			return escapeUtf8(decodeUtf8(controlLength($str,$len)));
		}
		function decodeUtf8($str) {
			return iconv('windows-1252', 'utf-8', $str);
		}
		function escapeUtf8($str) {
			return preg_replace("/%u([0-9a-fA-F]{4})/", "&#x\\1;", htmlspecialchars($str));
		}
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
		date_default_timezone_set(get_client_tz());

		$today = time();
		$cYear = date('Y', $today);
		$cMonth = date('m', $today);
		$cDay = date('d', $today);
		$curDate = $cYear.'-'.$cMonth.'-'.$cDay;
		date_default_timezone_set('UTC');
		$getBirthdays = mysql_query('SELECT j.id,j.nom,p.identifiant,p.identifiant2,p.identifiant3,p.identifiant4,p.nbmessages FROM `mkprofiles` p INNER JOIN `mkjoueurs` j ON p.id=j.id WHERE birthdate IS NOT NULL AND DAY(birthdate)='. $cDay .' AND MONTH(birthdate)='. $cMonth .' AND j.banned=0 AND j.deleted=0 AND last_connect>=DATE_SUB("'.$curDate.'",INTERVAL 1 YEAR) AND TIMESTAMPDIFF(SECOND,last_connect,"'.$curDate.'")<=TIMESTAMPDIFF(SECOND,IFNULL(sub_date,"2016-01-01"),last_connect)+7*24*3600 ORDER BY p.nbmessages DESC, p.id ASC');
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
				<?php echo $language ? "It's the birthday of":"C'est l'anniversaire de"; ?>
				<?php
				for ($i=0;$i<$nbBirthdays;$i++) {
					$birthday = $birthdaysList[$i];
					if ($i)
						echo ($i==$nbBirthdays-1) ? ($language ? " and ":" et "):", ";
					echo '<a href="profil.php?id='. $birthday['id'] .'">'. $birthday['nom'] .'</a>';
				}
				echo ($language ? '!':'&nbsp;!');
				?>
			</div>
			<?php
		}
		?>
		<?php
		display_sidebar('Forum', 'forum.php');
		?>
			<h1><?php echo $language ? 'Last topics':'Derniers topics'; ?></h1>
			<div id="forum_section" class="right_subsection">
				<?php
				require_once('getRights.php');
				$sql = 'SELECT id,titre, nbmsgs, category, dernier FROM `mktopics` WHERE language='. "language" . (hasRight('manager') ? '':' AND !private') .' ORDER BY dernier DESC LIMIT 10';
				if ($language)
					$sql = 'SELECT * FROM ('. $sql .') t ORDER BY (category=4) DESC, dernier DESC';
				$topics = mysql_query($sql);
				while ($topic = mysql_fetch_array($topics)) {
					if ($auteur = mysql_fetch_array(mysql_query('SELECT auteur FROM `mkmessages` WHERE topic='. $topic['id'] .' ORDER BY id DESC LIMIT 1'))) {
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
				}
				?>
			</div>
			<a class="right_section_actions action_button" href="forum.php"><?php echo $language ? 'Go to the forum':'Accéder au forum'; ?></a>
		</div>
		<div class="subsection">
		<?php
		display_sidebar('News', 'listNews.php');
		?>
			<h1><?php echo $language ? 'Last news':'Dernières news'; ?></h1>
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
						<h3><?php echo $language ? 'In':'Dans'; ?> <strong><?php echo $news['catname']; ?></strong> <?php echo ($name ? ($language ? 'by':'par') .' <strong>'. $name['nom'].'</strong> ':'').pretty_dates_short($news['publication_date'],array('lower'=>true)); ?></h3>
						<div class="creation_comments" title="<?php echo $nbMsgs. ' '.($language ? 'comment':'commentaire'). (($nbMsgs>1) ? 's':''); ?>"><img src="images/comments.png" alt="Messages" /> <?php echo $nbMsgs; ?></div>
					</a>
					<?php
				}
				date_default_timezone_set('UTC');
				if (!$nbnews)
					echo '<div style="text-align:center;margin-top:55px">'. ($language ? 'No news yet':'Aucune news pour l\'instant').'</div>';
				?>
			</div>
			<?php
			if (hasRight('publisher')) {
				$getPendingNews = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mknews WHERE status="pending"'));
				if ($getPendingNews['nb'])
					echo '<p class="nb-pending-news"><a href="listNews.php#pending-news">'. $getPendingNews['nb'] .' '. ($language ? 'pending':'news') .'</a> '. ($language ? 'news':'en attente de validation') .'</p>';
			}
			?>
			<a class="right_section_actions action_button" href="listNews.php"><?php echo $language ? 'All news':'Toutes les news'; ?></a>
		</div>
		<div class="subsection">
			<?php
			display_sidebar($language ? 'Track builder':'Éditeur de circuit', 'creations.php');
			?>
			<h1><?php echo $language ? 'Latest creations':'Dernières créations'; ?></h1>
			<div id="creations_section" class="right_subsection">
				<table>
					<?php
					function getNom($circuit) {
						global $language;
						return ($circuit['nom'] ? controlLengthUtf8($circuit['nom'],25):($language ? 'Untitled':'Sans titre'));
					}
					function getAuteur($circuit) {
						global $language;
						return ($circuit['auteur'] ? ($language ? 'By':'Par') .' <strong>'. controlLengthUtf8($circuit['auteur'],15) .'</strong>':'');
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
						$res = array();
						$nLines = count($lines);
						$logb = log(1.7);
						foreach ($lines as &$line) {
							$publishedSince = time()-strtotime($line['publication_date']);
							$publishedSince = max($publishedSince,0);
							$recency = 8-log($publishedSince/2000)/$logb;
							$recency = min(max($recency,3),8);
							$note = $line['note'];
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
						include('creation_line.php');
					}
					include('utils-circuits.php');
					$nbByType = array(1,1,2,2,3,3,2,2);
					$tracksList = listCreations(1,$nbByType,null,$aCircuits);
					$tracksList = sortLines($tracksList);
					$tracksList = array_slice($tracksList,0,14);
					foreach ($tracksList as $line)
						showLine($line);
					?>
				</table>
			</div>
			<a class="right_section_actions action_button" href="creations.php"><?php echo $language ? 'Display all':'Afficher tout'; ?></a>
			<h1><?php echo $language ? 'Last challenges':'Derniers défis'; ?></h1>
			<div id="challenges_section" class="right_subsection">
				<?php
				require_once('utils-challenges.php');
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
					<a href="<?php echo 'challengeTry.php?challenge='.$challenge['id']; ?>" title="<?php echo htmlspecialchars($challengeDetails['description']['main']); ?>"<?php if (isset($challengeDetails['succeeded'])) echo ' class="challenges_section_succeeded"'; ?>>
						<h2><?php echo htmlspecialchars(controlLength($challengeDetails['description']['main'],100)); ?></h2>
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
					$s = ($getPendingChallenges['nb']>=2) ? 's':'';
					echo '<p class="nb-pending-news"><a href="challengesList.php?moderate">'. $getPendingChallenges['nb'] .' '. ($language ? 'pending':"défi$s") .'</a> '. ($language ? "challenge$s":'en attente de validation') .'</p>';
				}
				/*$getRecheckingChallenges = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkclrecheck r INNER JOIN mkchallenges c ON r.id=c.id WHERE c.status="active"'));
				if ($getRecheckingChallenges['nb']) {
					$s = ($getRecheckingChallenges['nb']>=2) ? 's':'';
					echo '<p class="nb-pending-news" style="position:relative;top:-8px"><a href="challengesList.php?moderate=2">'. $getRecheckingChallenges['nb'] .' '. ($language ? "challenge$s":"défi$s") .'</a> '. ($language ? 'to revalidate':'à revalider') .'</p>';
				}*/
			}
			?>
			<a class="right_section_actions action_button" href="challengesList.php"><?php echo $language ? 'Display all':'Afficher tout'; ?></a>
			<div id="challenge_ranking"><a href="challengeRanking.php"><?php echo $language ? 'Challenge points - Leaderboard':'Classement des points défis'; ?></a></div>
			<h1><?php echo $language ? 'Recent activity':'Activité récente'; ?></h1>
			<div id="comments_section" class="right_subsection">
				<?php
				//$getComments = mysql_query('SELECT c.circuit,c.type,c.message,c.temps,c.nom,c.date FROM ((SELECT mkcomments.circuit,mkcomments.type COLLATE latin1_general_ci AS type,mkcomments.message COLLATE latin1_general_ci AS message,mkcomments.date,mkjoueurs.nom COLLATE latin1_general_ci AS nom,NULL as temps FROM `mkcomments` INNER JOIN `mkjoueurs` ON mkcomments.auteur=mkjoueurs.id) UNION ALL (SELECT circuit,type,NULL as message,date,nom,temps FROM `mkrecords`) ORDER BY date DESC) as c GROUP BY c.type,c.circuit ORDER BY c.date DESC LIMIT 14');
				$getComments = mysql_query('SELECT circuit,type,message,time,name,date,recency FROM ((SELECT mkcomments.circuit,mkcomments.type,mkcomments.message,mkcomments.date,mkjoueurs.nom AS name,NULL as time, (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(date))*1 AS recency FROM `mkcomments` INNER JOIN `mkjoueurs` ON mkcomments.auteur=mkjoueurs.id ORDER BY mkcomments.id DESC LIMIT 30) UNION ALL (SELECT circuit,type,NULL as message,date,name,time,(UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(date))*2 AS recency FROM `mkrecords` WHERE type!="" AND best=1 ORDER BY id DESC LIMIT 30) ORDER BY recency) as c GROUP BY c.type,c.circuit ORDER BY recency LIMIT 14');
				function zerofill($nb,$l) {
					$nb .= '';
					while (strlen($nb) < $l)
						$nb = '0'. $nb;
					return $nb;
				}
				function getRank($n) {
					global $language;
					$dec = $n%100;
					if ($language) {
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
					else {
						if ($n > 1)
							return 'e';
						else
							return 'er';
					}
					return $res;
				}
				while ($comment = mysql_fetch_array($getComments)) {
					if ($getCircuit = mysql_fetch_array(mysql_query('SELECT *'. (($comment['type']=="mkcircuits") ? ',!type as is_circuit':'') .' FROM `'. $comment['type'] .'` WHERE id='. $comment['circuit'] .' AND nom IS NOT NULL'))) {
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
							$records = mysql_query('SELECT time FROM `mkrecords` WHERE circuit="'. $comment['circuit'] .'" AND type="'. $comment['type'] .'" AND best=1');
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
							<h3><?php echo ($language ? 'By':'Par') .' <strong>'. htmlspecialchars(controlLength($comment['name'],10)) .'</strong>'; ?> <?php echo ($getCircuit['nom'] ? (($language ? 'in':'dans') . ' <strong>'. controlLengthUtf8($getCircuit['nom'],20) .'</strong>'):''); ?> <?php echo pretty_dates_short($comment['date'],array('lower'=>true)); ?></h3>
						</a>
						<?php
					}
				}
				?>
			</div>
		</div>
		<div class="subsection rank_vs" id="rankings_section">
			<?php
			display_sidebar($language ? 'Online mode':'Mode en ligne', 'online.php');
			$activePlayers = array(array(),array());
			if ($id) {
				$time = time();
				$limCoTime = floor(($time-35)*1000/67);
				require_once('public_links.php');
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
					if (!$activeCourses[$course]) {
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
			function gamePkSort(&$k1,&$k2) {
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
			<h1>Top 10</h1>
			<div class="ranking_tabs">
				<?php
				function print_badge($game) {
					global $activePlayers;
					$nbActivePlayers = count($activePlayers[$game]);
					if ($nbActivePlayers)
						echo '<span class="ranking_badge"><span>'.$nbActivePlayers.'</span></span>';
				}
				function get_creation_string(&$params) {
					global $language, $publicLinksData;
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
					if ($getNom = mysql_fetch_array(mysql_query('SELECT nom FROM `'.$table.'` WHERE id='.$params['cup'])))
						$res = $getNom['nom'];
					if (!$res) $res = $language ? 'Untitled':'Sans titre';
					return controlLengthUtf8($res,30);
				}
				function get_mode_string(&$params) {
					global $language, $publicLinksData;
					$link = $params['link'];
					$modeNames = array(
						'team' => ($language ? 'Team':'équipe'),
						'friendly' => ($language ? 'Friendly':'amical')
					);
					$publicLinkData = $publicLinksData[$link];
					$enabledModes = array();
					foreach ($modeNames as $option => $value) {
						if ($publicLinkData->$option)
							$enabledModes[$option] = $value;
					}
					if (empty($enabledModes))
						return $language ? 'Normal':'normal';
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
					echo $nbActivePlayers.' '.($language ? 'member':'membre') . (($nbActivePlayers>1) ? 's':'');
					echo '</span>';
					if (!empty($params['cup'])) {
						echo ' ';
						if ($params['game'])
							$theCircuit = $language ? 'the arena':'l\'arène';
						else {
							$isMCup = ($params['mode']==8);
							$isSingle = (($params['mode']%4)>=2);
							if ($isMCup)
								$theCircuit = $language ? 'the multicup':'la multicoupe';
							elseif ($isSingle)
								$theCircuit = $language ? 'the circuit':'le circuit';
							else
								$theCircuit = $language ? 'the cup':'la coupe';
						}
						echo ($language ? 'in ':'sur ') . $theCircuit;
						echo ' ';
						echo '<strong>';
						echo get_creation_string($params);
						echo '</strong>';
					}
					elseif (!empty($params)) {
						echo ' ';
						echo $language ? 'in':'en mode';
						echo ' ';
						echo get_mode_string($params);
						echo $language ? ' mode':'';
					}
				}
				function print_join_button(&$params) {
					global $language;
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
					echo '<a class="action_button" href="'. $url .'">'. ($language ? 'Join':'Rejoindre') .'</a>';
				}
				function print_active_players($game,$type) {
					global $language, $activePlayers, $activePlayersByLink;
					if (!empty($activePlayers[$game])) {
						echo '<div class="ranking_current" id="ranking_current_'.$type.'">';
						$firstPlayer = reset($activePlayers[$game]);
						if ((count($activePlayersByLink[$game]) < 2) && !$firstPlayer['link'] && !$firstPlayer['cup']) {
							echo '<span class="ranking_list">';
							echo ($language ? 'Currently online:':'Actuellement en ligne :');
							echo ' ';
							print_players_raw($activePlayers[$game]);
							print_join_button($firstPlayer);
							echo '</span>';
							echo ' ';
						}
						else {
							echo ($language ? 'Currently online:':'Actuellement en ligne :');
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
					<?php echo $language ? 'VS mode':'Course VS'; ?>
				</a><a class="ranking_tab tab_battle" href="javascript:dispRankTab(1)">
					<?php echo $language ? 'Battle':'Bataille'; ?>
					<?php print_badge(1); ?>
				</a><a class="ranking_tab tab_clm" href="javascript:dispRankTab(2)">
					<?php echo $language ? 'Time Trial':'CLM'; ?>
				</a>
			</div>
			<div id="currently_online">
			<?php
			print_active_players(0,'vs');
			print_active_players(1,'battle');
			?>
			</div>
			<div id="top10" class="right_subsection">
				<?php
				$modeIds = array('vs','battle','clm');
				for ($i=0;$i<3;$i++) {
					$modeId = $modeIds[$i];
					$isBattle = ($i===1);
					$isClm = ($i===2);
					$pts_ = 'pts_'.$modeId;
					?>
					<table id="top_<?php echo $modeId; ?>">
						<tr>
							<th><?php echo $language ? 'Rank':'Rang'; ?></th>
							<th><?php echo $language ? 'Nick':'Pseudo'; ?></th>
							<th>Score</th>
						</tr>
						<?php
						if ($isClm)
						$players = mysql_query('SELECT t.player AS id,j.nom,t.score AS pts FROM `mkttranking` t INNER JOIN `mkjoueurs` j ON t.player=j.id WHERE j.deleted=0 ORDER BY t.score DESC LIMIT 10');
						else
							$players = mysql_query('SELECT id,nom,'.$pts_.' AS pts FROM `mkjoueurs` WHERE deleted=0 ORDER BY '.$pts_.' DESC LIMIT 10');
						$place = 0;
						$lastScore = INF;
						for ($j=1;$player=mysql_fetch_array($players);$j++) {
							if ($player['pts'] < $lastScore) {
								$place = $j;
								$lastScore = $player['pts'];
							}
							echo '<tr><td class="top10position">'. $place .'</td><td><a href="profil.php?id='. $player['id'] .'">'. $player['nom'] .'</a></td><td>'. $player['pts'] .'</td></tr>';
						}
						?>
					</table>
					<?php
				}
				?>
			</div>
			<a class="right_section_actions action_button action_gotovs" href="bestscores.php"><?php echo $language ? 'Display all':'Afficher tout'; ?></a>
			<a class="right_section_actions action_button action_gotobattle" href="bestscores.php?battle"><?php echo $language ? 'Display all':'Afficher tout'; ?></a>
			<a class="right_section_actions action_button action_gotoclm" href="classement.global.php"><?php echo $language ? 'Display all':'Afficher tout'; ?></a>
		</div>
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
		<div class="subsection">
			<div class="flag_counter">
				<h4><?php echo $language ? 'Visitors since november 2017':'Visiteurs depuis novembre 2017'; ?></h4>
				<img src="https://s01.flagcounter.com/countxl/XMvG/bg_FFFFFF/txt_000000/border_CCCCCC/columns_3/maxflags_9/viewers_3/labels_0/pageviews_0/flags_0/percent_0/" alt="<?php echo $language ? 'Visitors':'Visiteurs'; ?>" />
				<a class="right_section_actions action_button" href="topic.php?topic=2288"><?php echo $language ? 'Learn more':'En savoir plus'; ?></a>
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
include('footer.php');
mysql_close();
?>
<script>
var loadingMsg = "<?php echo $language ? 'Loading':'Chargement'; ?>";
</script>
<script async src="scripts/creations.js"></script>
<script async src="scripts/posticons.js"></script>

<script src="scripts/jquery.min.js"></script>
<script async src="scripts/slider.js"></script>
<script async src="scripts/photoswipe.min.js"></script>
<script async src="scripts/init-diapos.js"></script>
<script async src="scripts/sidebars.js?realod=1"></script>
<script type="text/javascript">
var last_tz = '<?php echo isset($_COOKIE['tz']) ? addslashes($_COOKIE['tz']):''; ?>';
</script>
<script async src="scripts/timezones.js"></script>
</body>
</html>