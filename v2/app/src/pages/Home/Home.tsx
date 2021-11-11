
import ClassicPage from "../../components/ClassicPage/ClassicPage";
import { useLanguage } from "../../hooks/useLanguage";
import "./Home.css"
import mkpcBox from "../../images/main/mkpc_box.jpg"
import sidebarIcon from "../../images/icons/sidebar_icon.png"
import diapo1 from "../../images/main/slides/diapo1.jpg"
import diapo2 from "../../images/main/slides/diapo2.jpg"
import diapo3 from "../../images/main/slides/diapo3.png"
import diapo4 from "../../images/main/slides/diapo4.png"
import diapo5 from "../../images/main/slides/diapo5.jpg"
import diapo6 from "../../images/main/slides/diapo6.jpg"
import diapo7 from "../../images/main/slides/diapo7.jpg"
import diapo8 from "../../images/main/slides/diapo8.png"

function SectionBar({ title, link }) {
  return (
    <table className="sidebar_container">
      <tbody>
        <tr><td className="sidebar_icon"><img src={sidebarIcon} alt={title} /></td>
          <td className="sidebar_title">{
            link ? <a href={link}>{title}</a> : title
          }</td></tr>
      </tbody>
    </table>
  );
}

function Home() {
  const language = useLanguage();
  const comments = [];
  function formatDate(d, options) {
    return "";
  }

  return (
    <ClassicPage page="home">
      <section id="left_section">
        <div className="fp-slider">
          <div className="fp-slides-container">
            <div className="fp-slides">
              <div className="fp-slides-items">
                <div className="fp-thumbnail" style={{ background: "url(" + diapo1 + ") top" }}>
                </div>
                <div className="fp-content-wrap">
                  <div className="fp-content">
                    <h3 className="fp-title">{language ? 'A Mario Kart Game for browser' : 'Un jeu de Mario Kart sur navigateur'}</h3>
                    <p>
                      {language ? <>										A computer version of the famous racing game by Nintendo.<br />
                        This game is <strong>completely free</strong> and does not require <strong>any downloads</strong>. All you need is a web browser!
                      </> : <>										Une version ordi du célèbre jeu de course de Nintendo.<br />
                        Ce jeu est <strong>entièrement gratuit</strong> et ne requiert <strong>aucun téléchargement</strong>, un simple navigateur internet suffit.
                      </>}
                    </p>
                  </div>
                  <div className="fp-prev-next-wrap">
                    <a className="fp-next" href="#fp-next"></a>
                    <a className="fp-prev" href="#fp-prev"></a>
                  </div>
                </div>
              </div>

              <div className="fp-slides-items">
                <div className="fp-thumbnail" data-img={diapo2}>
                </div>
                <div className="fp-content-wrap">
                  <div className="fp-content">
                    <h3 className="fp-title">{language ? 'Crazy races full of fun!' : 'Des courses acharnées et pleines de fun !'}</h3>
                    <p>
                      {language ? <>										Try to be the fastest while avoiding the items!<br />
                        Find all the <strong>56 tracks</strong> from the original games <strong>Super Mario Kart</strong>, <strong>Mario Kart Super Circuit</strong> and <strong>Mario Kart DS</strong>.
                      </> : <>										Tentez d'être le plus rapide tout en évitant les objets !<br />
                        Retrouvez l'intégralité des <strong>56 circuits</strong> repris du jeu original <strong>Super Mario Kart</strong>, <strong>Mario Kart Super Circuit</strong> et <strong>Mario Kart DS</strong>.
                      </>}
                    </p>
                  </div>
                  <div className="fp-prev-next-wrap">
                    <a className="fp-next" href="#fp-next"></a>
                    <a className="fp-prev" href="#fp-prev"></a>
                  </div>
                </div>
              </div>

              <div className="fp-slides-items">
                <div className="fp-thumbnail" data-img={diapo3}>
                </div>
                <div className="fp-content-wrap">
                  <div className="fp-content">
                    <h3 className="fp-title">{language ? 'Win all the Grand Prix!' : 'Remportez tous les grands prix !'}</h3>
                    <p>
                      {language ? <>										Face off the cpu on the <strong>14 grands prix</strong> tournaments and try to win the gold cup!<br />
                        Win enough cups to unlock the <strong>15 secret characters</strong>!
                      </> : <>										Affrontez les ordis sur les <strong>14 grands prix</strong> et tentez de gagner la coupe en or !<br />
                        Remportez suffisament de coupes pour débloquer les <strong>15 persos secrets</strong> !
                      </>}
                    </p>
                  </div>
                  <div className="fp-prev-next-wrap">
                    <a className="fp-next" href="#fp-next"></a>
                    <a className="fp-prev" href="#fp-prev"></a>
                  </div>
                </div>
              </div>

              <div className="fp-slides-items">
                <div className="fp-thumbnail" data-img={diapo4}>
                </div>
                <div className="fp-content-wrap">
                  <div className="fp-content">
                    <h3 className="fp-title">{language ? 'Create your own tracks!' : 'Créez vos propres circuits !'}</h3>
                    <p>
                      {language ? <>										With the <strong>track builder</strong>, the possibilities are infinite: the only limit is your own imagination.<br />
                        Try other peoples' creations thanks to the integrated <strong>sharing tool</strong>.
                      </> : <>										Avec l'<strong>éditeur de circuits</strong> et d'arènes, les possibilités sont infinies ; votre imagination est la seule limite !<br />
                        Essayer les créations des autres grâce à l'<strong>outil de partage intégré</strong>.
                      </>}
                    </p>
                  </div>
                  <div className="fp-prev-next-wrap">
                    <a className="fp-next" href="#fp-next"></a>
                    <a className="fp-prev" href="#fp-prev"></a>
                  </div>
                </div>
              </div>

              <div className="fp-slides-items">
                <div className="fp-thumbnail" data-img={diapo5}>
                </div>
                <div className="fp-content-wrap">
                  <div className="fp-content">
                    <h3 className="fp-title">{language ? 'Face players from around the world!' : 'Affrontez les joueurs du monde entier !'}</h3>
                    <p>
                      {language ? <>										Fight other players in <strong>online mode</strong>!<br />
                        Win as many races as possible and <strong>climb in the official ranking</strong>!
                      </> : <>										Battez-vous contre d'autres joueurs avec le <strong>mode en ligne</strong> !<br />
                        Remportez un maximum de course afin de <strong>grimper dans le classement</strong> officiel !
                      </>}
                    </p>
                  </div>
                  <div className="fp-prev-next-wrap">
                    <a className="fp-next" href="#fp-next"></a>
                    <a className="fp-prev" href="#fp-prev"></a>
                  </div>
                </div>
              </div>

              <div className="fp-slides-items">
                <div className="fp-thumbnail" data-img={diapo6}>
                </div>
                <div className="fp-content-wrap">
                  <div className="fp-content">
                    <h3 className="fp-title">{language ? 'Make the best scores in time trial!' : 'Réalisez les meilleurs temps en contre-la-montre !'}</h3>
                    <p>
                      {language ? <>										<strong>Finish the race track</strong> as fast as you can!<br />
                        <strong>Compare your scores</strong> with the community, and face other players' ghosts!
                      </> : <>										<strong>Bouclez les 3 tours</strong> le plus rapidement possible !<br />
                        <strong>Comparez votre score</strong> avec la communauté, et affrontez les fantômes des autres joueurs !
                      </>}
                    </p>
                  </div>
                  <div className="fp-prev-next-wrap">
                    <a className="fp-next" href="#fp-next"></a>
                    <a className="fp-prev" href="#fp-prev"></a>
                  </div>
                </div>
              </div>

              <div className="fp-slides-items">
                <div className="fp-thumbnail" data-img={diapo7}>
                </div>
                <div className="fp-content-wrap">
                  <div className="fp-content">
                    <h3 className="fp-title">{language ? 'Release your fighter talents!' : 'Montrez vos talents de combattant !'}</h3>
                    <p>
                      {language ? <>										<strong>Destroy the balloons</strong> of your opponents with your own items without getting hit by theirs<br />
                        The last player standing wins!
                      </> : <>										<strong>Détruisez les ballons</strong> de votre adversaire en évitant de vous faire toucher !<br />
                        Soyez le dernier survivant pour remporter la partie !
                      </>}
                    </p>
                  </div>
                  <div className="fp-prev-next-wrap">
                    <a className="fp-next" href="#fp-next"></a>
                    <a className="fp-prev" href="#fp-prev"></a>
                  </div>
                </div>
              </div>

              <div className="fp-slides-items">
                <div className="fp-thumbnail" style={{ background: "url('" + diapo8 + "') top" }}>
                </div>
                <div className="fp-content-wrap">
                  <div className="fp-content">
                    <h3 className="fp-title">{language ? 'Face off your friends with the local multiplayer mode!' : 'Affrontez vos amis grâce au mode multijoueur !'}</h3>
                    <p>
                      {language ? <>										Prove your friends that you're the best!<br />
                        Face them in <strong>multiplayer</strong> on VS races or on battle mode.
                      </> : <>
                        Montrez à vos amis que vous êtes le meilleur !<br />
                        Affrontez-les en <strong>multijoueur</strong> en course VS ou sur les batailles de ballons.
                      </>}
                    </p>
                  </div>
                  <div className="fp-prev-next-wrap">
                    <a className="fp-next" href="#fp-next"></a>
                    <a className="fp-prev" href="#fp-prev"></a>
                  </div>
                </div>
              </div>
            </div>
            <div className="fp-nav">
              <span className="fp-pager"></span>
            </div>
          </div>
        </div>
        <h1>Mario Kart PC</h1>
        <div id="toBegin"><a href="mariokart.php">
          &#9660;&nbsp;{language ? 'Click on the game box to begin' : 'Cliquez sur la boîte du jeu pour commencer'}&nbsp;&#9660;<br />
          <img src={mkpcBox} alt={language ? "Go to the game" : "Accéder au jeu"} style={{ width: 310, position: "relative", top: 2 }} /><br />
          &#9650;&nbsp;{language ? 'Click on the game box to begin' : 'Cliquez sur la boîte du jeu pour commencer'}&nbsp;&#9650;</a></div>
        <h2><img src="images/about.png" alt="" /> {language ? 'What\'s Mario Kart PC?' : 'Mario Kart PC, c\'est quoi ?'}</h2>
        <div>
          {language ? <>				<p>You might know Mario Kart, the most fun racing game series of all time!
            Mario Kart PC uses the same base as the original games but is playable on your browser, and <strong>for free</strong>.</p>
            <p>Most of the modes from Mario Kart have been included: Grand Prix, VS, Battle mode, Time Trials, and more!<br />
              There's also a brand new mode: the <strong>track builder</strong>! Place straight lines and turns, add items, boost panels and more!
              Everything is customizable! The only limit is your own imagination!<br />
              You can share your tracks, and try other people's tracks thanks to the <a href="creations.php">sharing tool</a>. Thousands of custom tracks are already available!</p>
            <p>Finally, you can face players from the whole world thanks to the <strong>multiplayer online mode</strong>! Climb the <a href="bestscores.php">rankings</a> and become world champion!</p>
          </> : <>				<p>Vous connaissez certainement Mario Kart, le jeu de course le plus fun de tous les temps !
            Mario Kart PC reprend les mêmes principes que le jeu original mais il est jouable sur navigateur, et <strong>gratuitement</strong>.</p>
            <p>La plupart des modes issus de Mario Kart ont été repris : Grand Prix, courses VS, batailles de ballons, contre-la-montre...<br />
              Et un dernier mode inédit : l'<strong>éditeur de circuits</strong> ! Placez les lignes droites et les virages, ajoutez les objets, insérez des accélérateurs...
              Tout est personnalisable ! Votre imagination est la seule limite !<br />
              Vous pouvez également partager vos créations et essayer celles des autres grâce à l'<a href="creations.php">outil de partage</a>.
              Plusieurs milliers de circuits ont déjà été partagés !</p>
            <p>Enfin, il est possible d'affronter les joueurs du monde entier grâce au <strong>mode multijoueurs en ligne</strong> ! Grimpez dans le <a href="bestscores.php">classement</a> et devenez champion du monde !</p>
          </>}
        </div>
        <h2><img src="images/camera.png" alt="" /> {language ? 'Some screenshots' : 'Quelques screenshots'}</h2>
        <div>
          {language ? 'Here are some screenshots of the game to give you a quick preview of what it looks like:' : 'Une image vaut mieux qu\'un long discours, voici donc quelques captures d\'écran issues du jeu afin que vous ayez un aperçu de ce à quoi ça ressemble :'}
          <table id="screenshots" className="demo-gallery">
            <tbody>
              {/*<?php
				for ($i=1;$i<=12;$i++) {
					if (!(($i-1)%3))
						echo '<tr>';
					echo '<td>';
					$url_img = "images/screenshots/ss$i.png";
					$url_thumb = 'images/screenshots/ss'.$i.'xs.png';
					echo '<a href="'. $url_img .'" data-size="960x468" data-med="'. $url_img .'" data-med-size="240x117" className="demo-gallery__photo demo-gallery__img--main"><img src="'.$url_thumb.'" alt="Screenshot '. $i .'" /></a>';
					echo '</td>';
					if (!($i%3))
						echo '</tr>';
				}
      ?>*/}
            </tbody>
          </table>
        </div>
        <br />
        <div className="pub_section">
          <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
          {/* Mario Kart PC */}
          <ins className="adsbygoogle"
            style={{ display: "inline-block", width: 728, height: 90 }}
            data-ad-client="ca-pub-1340724283777764"
            data-ad-slot="4919860724"
          ></ins>
          <script>
            (adsbygoogle = window.adsbygoogle || []).push({ });
          </script>
        </div>
        <h2><img src="images/thanks.png" alt="" /> {language ? 'Special thanks' : 'Remerciements'}</h2>
        <div>
          {language ? <>				A big thanks to Nintendo, these three sites and these artists without which Mario Kart PC would have probably never existed !
            <ul>
              <li><a href="https://web.archive.org/web/20101104055946/http://blog.nihilogic.dk/">Nihilogic</a> for the <a href="https://web.archive.org/web/20100208144516/http://www.nihilogic.dk/labs/mariokart/">basic Mario Kart</a></li>
              <li><a href="http://www.snesmaps.com/">SNESMaps</a> for the <a href="http://www.snesmaps.com/maps/SuperMarioKart/SuperMarioKartMapSelect.html">track images</a></li>
              <li><a href="http://www.vgmusic.com/">VGmusic</a> for the <a href="http://www.vgmusic.com/music/console/nintendo/snes/index-sz.html#Super_Mario_Kart">musics</a></li>
              <li>And <a href="credits.php">many more</a>!</li>
            </ul>
          </> : <>				Un grand merci à Nintendo, ces 3 sites et ces artistes sans lesquels Mario Kart PC n'aurait probablement jamais existé !
            <ul>
              <li><a href="https://web.archive.org/web/20101104055946/http://blog.nihilogic.dk/">Nihilogic</a> pour le <a href="https://web.archive.org/web/20100208144516/http://www.nihilogic.dk/labs/mariokart/">Mario Kart de départ</a></li>
              <li><a href="http://www.snesmaps.com/">SNESMaps</a> pour les <a href="http://www.snesmaps.com/maps/SuperMarioKart/SuperMarioKartMapSelect.html">images des circuits</a></li>
              <li><a href="https://downloads.khinsider.com/">Khinsider</a> pour les <a href="https://downloads.khinsider.com/search?search=mario+kart">musiques</a></li>
              <li>Et <a href="credits.php">bien d'autres</a> !</li>
            </ul>
          </>}
        </div>
        <h2><img src="images/follow.png" alt="" /> {language ? 'Follow us' : 'Nous suivre'}</h2>
        <div>
          {language ? <>				<ul>
            <li><a href="https://discord.gg/VkeAxaj">Discord Server</a> of the site: join it to chat with the community and be informed about updates and events.</li>
            <li><a href="https://www.youtube.com/channel/UCRFoW7uwHuP1mg0qSaJ4jNg">Official Youtube Channel</a> : find out videos about the game and informations about the website and its events. The channel is maintained by members, if you want to participate, tell it on the <a href="topic.php?topic=3392">official topic</a>.</li>
            <li><a href="https://twitter.com/MarioKartPC">Twitter Page</a> and <a href="https://www.facebook.com/groups/126497814060671/">Facebook Group</a> of the game: follow then to be informed of the latest news concerning the site!</li>
            <li><a href="http://fr.wiki-mario-kart-pc.wikia.com/">MKPC Wiki</a>: find out all the information about the game and its history. This site is maintained by the community, if you want to contribute, tell it on the <a href="topic.php?topic=343">this topic</a>!</li>
          </ul>
          </> : <>				<ul>
            <li><a href="https://discord.gg/VkeAxaj">Serveur Discord</a> du site : rejoignez-le pour discuter avec la communauté et être informé des mises à jours et événements.</li>
            <li><a href="https://www.youtube.com/channel/UCRFoW7uwHuP1mg0qSaJ4jNg">Chaîne Youtube Officielle</a> : retrouvez des vidéos sur le jeu et des informations sur le site et ses évenements. La chaîne est alimentée par les membres, si vous voulez participez, parlez-en sur <a href="topic.php?topic=3392">le topic officiel</a>.</li>
            <li><a href="https://twitter.com/MarioKartPC">Page Twitter</a> et <a href="https://www.facebook.com/groups/126497814060671/">Groupe Facebook</a> du jeu : suivez-les pour être au courant des dernières actualités du site !</li>
            <li><a href="http://fr.wiki-mario-kart-pc.wikia.com/">Wiki MKPC</a> : retrouvez toutes les informations sur le jeu et son histoire. Ce site est maintenu par les membres, si vous voulez contribuer, parlez-en sur <a href="topic.php?topic=343">ce topic</a>&nbsp;!</li>
          </ul>
          </>}
          {language ? <>				<p><em>This site is mostly maintained by French members, if you see some translation errors in the game or the site, don't hesitate to report them on this <a href="topic.php?topic=1">forum topic</a>.</em></p>
          </> : <></>}
        </div>
        <div className="pub_section">
          { /* Mario Kart PC */}
          <ins className="adsbygoogle"
            style={{ display: "inline-block", width: 728, height: 90 }}
            data-ad-client="ca-pub-1340724283777764"
            data-ad-slot="4919860724"
          ></ins>
          <script>
            (adsbygoogle = window.adsbygoogle || []).push({ });
          </script>
        </div>
        <h2><img src="images/gamepad.png" alt="" /> {language ? 'Go to the game' : 'Accéder au jeu'}</h2>
        <div>
          {language ? <>				To start playing, it's very simple, just click on &quot;Play game&quot; in the menu above. Or more simply, click here:<br />
            <a href="mariokart.php" className="action_button button_game">Start playing now &gt;</a>
          </> : <>				Pour commencer à jouer, c'est très simple, cliquez sur &quot;Le jeu&quot; dans le menu en haut. Ou plus simplement, cliquez là :<br />
            <a href="mariokart.php" className="action_button button_game">Commencer à jouer &gt;</a>
          </>}
        </div>
      </section>
      <section id="right_section">
        {/*<?php
		require_once('utils-date.php');
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
			return escapeUtf8(decodeUtf8(controlLength($str,$len)));
		}
		function decodeUtf8($str) {
			return iconv('windows-1252', 'utf-8', $str);
		}
		require_once('circuitEscape.php');
		function escapeUtf8($str) {
			return htmlspecialchars(escapeCircuitNames($str));
		}
    ?>*/}
        <div className="subsection">
          {/*<?php
		if ($id) {
			date_default_timezone_set(get_client_tz());

			$today = time();
			$cYear = date('Y', $today);
			$cMonth = date('m', $today);
			$cDay = date('d', $today);
			$curDate = $cYear.'-'.$cMonth.'-'.$cDay;
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
				<div className="birthdays-list">
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
		}
		date_default_timezone_set('UTC');
    ?>*/}
          <SectionBar title="Forum" link="forum.php" />
          <h2>{language ? 'Last topics' : 'Derniers topics'}</h2>
          <div id="forum_section" className="right_subsection">
            {/*<?php
				require_once('getRights.php');
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
					<a href="topic.php?topic=<?php echo $topic['id']; ?>" title="<?php echo $topic['titre']; ?>">
						<h2><?php echo htmlspecialchars(controlLength($topic['titre'],40)); ?></h2>
						<h3>{ language ? 'Last message':'Dernier message' } <?php echo ($message['nom'] ? ($language ? 'by':'par') .' <strong>'. $message['nom'].'</strong> ':'').pretty_dates_short($topic['dernier'],array('lower'=>true)); ?></h3>
						<div className="creation_comments" title="<?php echo $nbMsgs. ' message'. (($nbMsgs>1) ? 's':''); ?>"><img src="images/comments.png" alt="Messages" /> <?php echo $nbMsgs; ?></div>
					</a>
					<?php
				}
				unset($topics);
				unset($lastMsgByTopic);
      ?>*/}
          </div>
          <a className="right_section_actions action_button" href="forum.php">{language ? 'Go to the forum' : 'Accéder au forum'}</a>
        </div>
        <div className="subsection">
          <SectionBar title="News" link="listNews.php" />
          <h2>{language ? 'Latest news' : 'Dernières news'}</h2>
          <div id="news_section" className="right_subsection">
            {/*<?php
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
					<a href="news.php?id=<?php echo $news['id']; ?>" title="<?php echo htmlspecialchars($news['title']); ?>"<?php if ($isNew) echo ' className="news_new"'; ?>>
						<h2><?php echo htmlspecialchars(controlLength($news['title'],40)); ?></h2>
						<h3>{ language ? 'In':'Dans' } <strong><?php echo $news['catname']; ?></strong> <?php echo ($name ? ($language ? 'by':'par') .' <strong>'. $name['nom'].'</strong> ':'').pretty_dates_short($news['publication_date'],array('lower'=>true)); ?></h3>
						<div className="creation_comments" title="<?php echo $nbMsgs. ' '.($language ? 'comment':'commentaire'). (($nbMsgs>1) ? 's':''); ?>"><img src="images/comments.png" alt="Messages" /> <?php echo $nbMsgs; ?></div>
					</a>
					<?php
				}
				date_default_timezone_set('UTC');
				if (!$nbnews)
					echo '<div style="text-align:center;margin-top:55px">'. ($language ? 'No news yet':'Aucune news pour l\'instant').'</div>';
      ?>*/}
          </div>
          {/*<?php
			if (hasRight('publisher')) {
				$getPendingNews = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mknews WHERE status="pending"'));
				if ($getPendingNews['nb'])
					echo '<p className="nb-pending-news"><a href="listNews.php#pending-news">'. $getPendingNews['nb'] .' '. ($language ? 'pending':'news') .'</a> '. ($language ? 'news':'en attente de validation') .'</p>';
			}
    ?>*/}
          <a className="right_section_actions action_button" href="listNews.php">{language ? 'All news' : 'Toutes les news'}</a>
        </div>
        <div className="subsection">
          <SectionBar title={language ? 'Track builder' : 'Éditeur de circuit'} link="creations.php" />
          <h2>{language ? 'Latest creations' : 'Dernières créations'}</h2>
          <div id="creations_section" className="right_subsection">
            <table>
              <tbody>
                {/*<?php
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
						include('creation_line.php');
					}
					include('utils-circuits.php');
					$nbByType = array(1,1,2,2,3,3,2,2);
					$tracksList = listCreations(1,$nbByType,null,$aCircuits);
					$tracksList = sortLines($tracksList);
					$tracksList = array_slice($tracksList,0,14);
					foreach ($tracksList as $line)
						showLine($line);
        ?>*/}
              </tbody>
            </table>
          </div>
          <a className="right_section_actions action_button" href="creations.php">{language ? 'Display all' : 'Afficher tout'}</a>
          <h2>{language ? 'Last challenges' : 'Derniers défis'}</h2>
          <div id="challenges_section" className="right_subsection">
            {/*<?php
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
					<a href="<?php echo 'challengeTry.php?challenge='.$challenge['id']; ?>" title="<?php echo htmlspecialchars($challengeDetails['description']['main']); ?>"<?php if (isset($challengeDetails['succeeded'])) echo ' className="challenges_section_succeeded"'; ?>>
						<h2><?php echo htmlspecialchars(controlLength($challengeDetails['description']['main'],100)); ?></h2>
						<h3><?php echo ucfirst(($challengeDetails['circuit']['author'] ? (($language ? 'by':'par') .' <strong>'. controlLengthUtf8($challengeDetails['circuit']['author'],10) .'</strong> '):'') . ($challengeDetails['circuit']['name'] ? (($language ? 'in':'dans') . ' <strong>'. controlLengthUtf8($challengeDetails['circuit']['name'],30-min(10,strlen($challengeDetails['circuit']['author']))-strlen($challengeDetails['difficulty']['name'])) .'</strong>'):'')); ?> - <strong><?php echo $challengeDetails['difficulty']['name']; ?></strong></h3>
					</a>
					<?php
				}
      ?>*/}
          </div>
          {/*
			if (hasRight('clvalidator')) {
				$getPendingChallenges = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkchallenges WHERE status="pending_moderation"'));
				if ($getPendingChallenges['nb']) {
					$s = ($getPendingChallenges['nb']>=2) ? 's':'';
					echo '<p className="nb-pending-news"><a href="challengesList.php?moderate">'. $getPendingChallenges['nb'] .' '. ($language ? 'pending':"défi$s") .'</a> '. ($language ? "challenge$s":'en attente de validation') .'</p>';
				}
			*/}
          <a className="right_section_actions action_button" href="challengesList.php">{language ? 'Display all' : 'Afficher tout'}</a>
          <div id="challenge_ranking"><a href="challengeRanking.php">{language ? 'Challenge points - Leaderboard' : 'Classement des points défis'}</a></div>
          <h2>{language ? 'Recent activity' : 'Activité récente'}</h2>
          <div id="comments_section" className="right_subsection">
            {comments.map((comment) => (
              <a href={comment.url} title={comment.message}>
                <h2><img src="images/<?php echo $type; ?>.png" alt={comment.type} /> {comment.message /* TODO control length */}</h2>
                <h3>{language ? 'By' : 'Par'} <strong>{comment.name /* TODO control length */}</strong> {comment.circuit.name && <>{language ? 'in' : 'dans'}{" "}<strong>{comment.circuit.name/* TODO control length */}</strong></>}{" "}{formatDate(comment.date, { prefix: true, short: true })}</h3>
              </a>
            ))}
          </div>
        </div>
        <div className="subsection rank_vs" id="rankings_section">
          <h2>Top 10</h2>
          <div className="ranking_tabs">
            <a className="ranking_tab tab_vs" href="javascript:dispRankTab(0)">
              {language ? 'VS mode' : 'Course VS'}
              {/* print_badge(0); */}
            </a><a className="ranking_tab tab_battle" href="javascript:dispRankTab(1)">
              {language ? 'Battle' : 'Bataille'}
              {/* print_badge(1); */}
            </a><a className="ranking_tab tab_clm tab_clm150" href="javascript:dispRankTab(currenttabcc)">
              {language ? 'Time Trial' : 'CLM'}
            </a>
          </div>
          <div id="currently_online">
            {/*
			print_active_players(0,'vs');
			print_active_players(1,'battle');
			*/}
          </div>
          <div id="clm_cc">
            <a className="clm_cc_150" href="javascript:dispRankTab(2)">150cc</a> <span>|</span>
            <a className="clm_cc_200" href="javascript:dispRankTab(3)">200cc</a>
          </div>
          <div id="top10" className="right_subsection">
            {['vs', 'battle', 'clm150', 'clm200'].map((modeId) => (
              <table id={"top_" + modeId} key={modeId}>
                <tbody>
                  <tr>
                    <th>{language ? 'Rank' : 'Rang'}</th>
                    <th>{language ? 'Nick' : 'Pseudo'}</th>
                    <th>Score</th>
                  </tr>
                  {/* TODO players */}
                </tbody>
              </table>
            ))}
          </div>
          <a className="right_section_actions action_button action_gotovs" href="bestscores.php">{language ? 'Display all' : 'Afficher tout'}</a>
          <a className="right_section_actions action_button action_gotobattle" href="bestscores.php?battle">{language ? 'Display all' : 'Afficher tout'}</a>
          <a className="right_section_actions action_button action_gotoclm150" href="classement.global.php?cc=150">{language ? 'Display all' : 'Afficher tout'}</a>
          <a className="right_section_actions action_button action_gotoclm200" href="classement.global.php?cc=200">{language ? 'Display all' : 'Afficher tout'}</a>
        </div>
        <div className="pub_section">
          {/* Pub latérale MKPC */}
          <ins className="adsbygoogle"
            style={{ display: "inline-block", width: 300, height: 250 }}
            data-ad-client="ca-pub-1340724283777764"
            data-ad-slot="4492555127"></ins>
          <script>
            (adsbygoogle = window.adsbygoogle || []).push({ });
          </script>
        </div>
        <div className="subsection">
          <div className="flag_counter">
            <h4>{language ? 'Visitors since november 2017' : 'Visiteurs depuis novembre 2017'}</h4>
            <img src="https://s01.flagcounter.com/countxl/XMvG/bg_FFFFFF/txt_000000/border_CCCCCC/columns_3/maxflags_9/viewers_3/labels_0/pageviews_0/flags_0/percent_0/" alt={language ? 'Visitors' : 'Visiteurs'} />
            <a className="right_section_actions action_button" href="topic.php?topic=2288">{language ? 'Learn more' : 'En savoir plus'}</a>
          </div>
        </div>
      </section>
      <div id="gallery" className="pswp" role="dialog" aria-hidden="true">
        <div className="pswp__bg"></div>
        <div className="pswp__scroll-wrap">
          <div className="pswp__container">
            <div className="pswp__item"></div>
            <div className="pswp__item"></div>
            <div className="pswp__item"></div>
          </div>
          <div className="pswp__ui pswp__ui--hidden">
            <div className="pswp__top-bar">
              <div className="pswp__counter"></div>
              <button className="pswp__button pswp__button--close" title="Close (Esc)"></button>
              <button className="pswp__button pswp__button--share" title="Share"></button>
              <button className="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
              <button className="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
              <div className="pswp__preloader">
                <div className="pswp__preloader__icn">
                  <div className="pswp__preloader__cut">
                    <div className="pswp__preloader__donut"></div>
                  </div>
                </div>
              </div>
            </div>
            <div className="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
              <div className="pswp__share-tooltip">
              </div>
            </div>
            <button className="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button>
            <button className="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button>
            <div className="pswp__caption">
              <div className="pswp__caption__center"></div>
            </div>
          </div>
        </div>
      </div>
    </ClassicPage>
  );
}

export default Home;