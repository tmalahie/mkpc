<?php
include('../includes/session.php');
include('../includes/language.php');
include('../includes/initdb.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>Calendrier de l'Avent - Mario Kart PC</title>
<?php
$hdescription = $language ? "It's Christmas on Mario Kart PC! To celebrate, this unique event gives you access to 1 challenge per day! Win as many challenges as possible to earn up to 400+ challenge points!" : "C'est Noël sur Mario Kart PC ! Pour fêter ça, cet événement inédit vous donne accès à 1 défi par jour ! Remportez un maximum de défis pour gagner jusqu'à 400+ points défis !";
include('../includes/heads.php');
$year = isset($_GET['y']) ? $_GET['y'] : 2025;
$over = false;
date_default_timezone_set('Europe/Paris');
if ($year < date('Y')) {
	$day = 25;
	$over = true;
}
else {
	if (date('n') == 12)
		$day = date('j');
	else
		$day = 0;
}
$dayStr = $day;
if ($language) {
	if ($day == 1 || $day == 21)
		$dayStr .= "st";
	else if ($day == 2 || $day == 22)
		$dayStr .= "nd";
	else if ($day == 3 || $day == 23)
		$dayStr .= "rd";
	else
		$dayStr .= "th";
	$dayStr = "December " . $dayStr;
}
else {
	if ($day == 1)
		$dayStr .= "er";
	$dayStr .= " Décembre";
}
include('../includes/advent-challenges.php');
$adventChallengesUntil = get_challenges_until($day);
?>
<script src="scripts/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<style type="text/css">
.advent-description {
	margin-left: 20px;
	font-size: 1.2em;
	margin-bottom: 10px;
}
.advent-description strong {
	color: #C80;
}
.advent-countdown img {
	position: relative;
	top: 2px;
	margin-right: 5px;
}
.advent-countdown {
	margin-left: 20px;
	color: #800;
	font-weight: bold;
}
.advent-completed {
	margin-left: 20px;
	color: #080;
	font-weight: bold;
}
.advent-calendar {
	position: relative;
}
.advent-calendar-bg {
	width: 100%;
}
.advent-square {
	position: absolute;
	background-repeat: no-repeat;
	background-position: center;
	width: 14.5%;
	height: 10.6%;
	cursor: pointer;
}
.advent-square span {
	display: none;
}
.advent-square-open span, .advent-square-success span {
	position: absolute;
	left: 10%;
	top: 8%;
	color: #3D0B02;
	display: inline-block;
	font-weight: bold;
	font-size: 1.5vw;
	font-family: Arial;
	opacity: 0.8;
}
.advent-square-open {
	background-image: url("images/advent-calendar/window.jpg");
	background-size: cover;
}
.advent-square-success {
	background-image: url("images/advent-calendar/star.png"), url("images/advent-calendar/window.jpg");
	background-size: 90%, cover;
}
.advent-begin {
	text-align: center;
	font-size: 1.4em;
	margin: 5px;
}
.pub {
	text-align: center;
	overflow: hidden;
}
#advent-challenge-mask {
	display: none;
	position: fixed;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	background-color: rgba(0,0,0, 0.5);
}
#advent-challenge-cross {
	position: absolute;
	right: 5px;
	top: 0px;
	color: red;
	font-size: 1.4em;
	text-decoration: none;
}
#advent-challenge-ctn {
	position: absolute;
	background-color: white;
	width: 400px;
	padding: 5px 12px;
	max-width: 100%;
	left: 50%;
	top: 50%;
	-webkit-transform: translate(-50%,-50%);
	-moz-transform: translate(-50%,-50%);
	-o-transform: translate(-50%,-50%);
	-ms-transform: translate(-50%,-50%);
	transform: translate(-50%,-50%);
	border-radius: 5px;
	text-align: center;
}
#advent-challenge-title-ctn {
	text-align: center;
}
#advent-challenge-title-ctn img {
	height: 1.6em;
	position: relative;
	top: 4px;
}
#advent-challenge-title {
	display: inline-block;
	font-size: 1.25em;
	margin-left: 4px;
	margin-top: 5px;
	margin-bottom: 10px;
}
#advent-challenge-name:not(:empty) {
	margin-top: 5px;
	font-size: 1.1em;
	color: #005656;
	text-decoration: underline;
}
#advent-challenge-body {
	font-size: 1.1em;
	margin-top: 0.25em;
}
#advent-challenge-body a {
	font-weight: bold;
}
#advent-challenge-body strong {
	color: #C80;
}
#advent-challenge-state {
	font-weight: bold;
	margin-bottom: 5px;
	text-align: center;
}
#advent-challenge-img img {
	width: 200px;
	height: 80px;
}
#advent-challenge-extra {
	margin-top: 5px;
	margin-bottom: 2px;
	font-size: 0.8em;
}
#advent-challenge-link {
	margin-top: 0.75em;
	margin-bottom: 0.5em;
}
#advent-challenge-button {
	padding: 5px 10px;
	color: white;
	background-color: #001060;
	border-radius: 5px;
	text-decoration: none;
	font-size: 1.5em;
	font-family: Arial;
	font-weight: bold;
}
#advent-challenge-button:hover {
	background-color: #003679;
}
</style>
<script type="text/javascript">
var language = <?php echo $language ? 1:0; ?>;
var openedSquare;
var allChallenges = <?php
	echo json_encode($adventChallengesUntil);
?>;
function openSquare(square) {
	var state = +$(square).data("state");
	var day = +$(square).data("day");
	openedSquare = square
	if (!state && allChallenges[day])
		square.className = "advent-square advent-square-open";
	populateChallenge(square);
	$("#advent-challenge-mask").fadeIn();
}
function populateChallenge(square) {
	var state = +$(square).data("state");
	var day = +$(square).data("day");
	var dayStr = day;
	if (language) {
		if (day == 1 || day == 21)
			dayStr += "st";
		else if (day == 2 || day == 22)
			dayStr += "nd";
		else if (day == 3 || day == 23)
			dayStr += "rd";
		else
			dayStr += "th";
		dayStr = "December " + dayStr;
	}
	else {
		if (day == 1)
			dayStr += "er";
		dayStr += " Décembre";
	}
	$("#advent-challenge-title").html((language ? 'Challenge of  ':'Défi du ')+dayStr);
	var challenge = allChallenges[day];
	if (challenge) {
		switch (state) {
		case 2:
			$("#advent-challenge-state").css("color","green");
			$("#advent-challenge-state").text(language ? "This challenge has been completed!":"Ce défi a été réussi !");
			$("#advent-challenge-state").show();
			break;
		default:
			$("#advent-challenge-state").hide();
			break;
		}
		$("#advent-challenge-name").html(challenge.name);
		var description = challenge.description;
		$("#advent-challenge-body").html(description);
		if (challenge.img) {
			var $adventChallengeImg = $("#advent-challenge-img img");
			$adventChallengeImg.attr("src",challenge.img);
			if (challenge.imgW)
				$adventChallengeImg.css("width",challenge.imgW);
			else
				$adventChallengeImg.css("width","");
			if (challenge.imgH)
				$adventChallengeImg.css("height",challenge.imgH);
			else
				$adventChallengeImg.css("height","");
			$("#advent-challenge-img").show();
		}
		else
			$("#advent-challenge-img").hide();
		if (challenge.extra) {
			$("#advent-challenge-extra").text(challenge.extra);
			$("#advent-challenge-extra").show();
		}
		else
			$("#advent-challenge-extra").hide();
		if (state < 2) {
			var link = "mariokart.php";
			if (challenge.link)
				link = challenge.link;
			$("#advent-challenge-button").attr("href", link);
			$("#advent-challenge-body > a:first-child").attr("href", link);
			$("#advent-challenge-link").show();
		}
		else
			$("#advent-challenge-link").hide();
	}
	else {
		$("#advent-challenge-link").hide();
		$("#advent-challenge-extra").hide();
		$("#advent-challenge-img").hide();
		$("#advent-challenge-name").html("");
		$("#advent-challenge-body").html("");
		$("#advent-challenge-state").css("color","#800");
		$("#advent-challenge-state").text(language ? "It's too early for this challenge":"Il est trop tôt pour ce défi");
		$("#advent-challenge-state").show();
	}
}
function closeSquare() {
	if (openedSquare) {
		var square = openedSquare;
		openedSquare = null;
		$("#advent-challenge-mask").fadeOut(function() {
			var state = +$(square).data("state");
			if (!state)
				square.className = "advent-square";
			square = null;
		});
	}
}
document.onkeydown = function(e) {
	if (e.keyCode == 27)
		closeSquare();
}
</script>
<?php
include('../includes/o_online.php');
?>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'home';
include('../includes/menu.php');
?>
<main>
	<h1><?php echo $language ? 'Advent Calendar':'Calendrier de l\'avent'; ?></h1>
	<div class="advent-description">
		<?php
		$nbCompleted = 0;
		require_once('../includes/advent-topic.php');
		if ($id) {
			if ($getNbCompleted = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkadvent WHERE user='.$id.' AND year="'.$year.'" AND day<='.$day)))
				$nbCompleted = $getNbCompleted['nb'];
		}
		if ($over) {
			if ($language) {
				?>
				The event <strong>Advent Calendar</strong> is now closed. You can find the results <a href="ranking-advent.php">here</a>, congrats to all particiants!<br />
				If you missed the event and want to learn more about it, go to <a href="<?php echo $adventTopicUrl; ?>">this topic</a>.<br />
				<?php
			}
			else {
				?>
				L'événement <strong>Calendrier de l'avent</strong> est terminé. Les résultats sont disponibles <a href="ranking-advent.php">ici</a>, bravo à tous les participants !<br />
				Si vous avez manqué l'événement et que vous voulez en savoir plus, rendez-vous sur <a href="<?php echo $adventTopicUrl; ?>">ce topic</a>.<br />
				<?php
			}
		}
		else {
			if ($language) {
				?>
				It's Christmas on Mario Kart PC!<br />
				On this occasion, a unique event is organized on the site: the <strong>Advent Calendar</strong>!<br />
				This event gives you access to 1 challenge per day until December 25.<br />
				As a present, each successful challenge gives you <strong>twice as many points</strong> as normal, and even more if you complete a lot of challenges!<br />
				To learn more, check out <a href="<?php echo $adventTopicUrl; ?>">this topic</a>.
				<?php
			}
			else {
				?>
				C'est Noël sur Mario Kart PC !<br />
				À cette occasion, un événement inédit est organisé sur le site : le <strong>Calendrier de l'Avent</strong> !<br />
				Cet événement vous donne accès à <strong>1 défi par jour</strong> jusqu'au 25 décembre.<br />
				En cadeau, chaque défi réussi vous rapporte <strong>2 fois plus</strong> de points qu'en temps normal, et encore plus si vous réussissez beaucoup de défis !<br />
				Pour en savoir plus, rendez-vous sur <a href="<?php echo $adventTopicUrl; ?>">ce topic</a>.<br />
				<?php
			}
		}
		?>
	</div>
	<?php
	if ($nbCompleted) {
		?>
		<div class="advent-completed"><?php
		if ($nbCompleted == 24) {
			if ($day < 24)
				echo $language ? 'Well done, you have completed all the challenges so far! See you tomorrow for the next challenge.':'Bravo, vous avez réussi tous les défis pour l\'instant ! Rendez-vous demain pour le prochain défi.';
			else
				echo $language ? 'You have completed all the challenges, congratulations!!':'Vous avez réussi tous les défis, félicitations !!';
		}
		else {
			$plural = ($nbCompleted>=2) ? 's':'';
			echo $language ? 'You have completed '. $nbCompleted .' challenge'. $plural .' out of '. 24 .'!':'Vous avez réussi '. $nbCompleted .' défi'. $plural .' sur '. 24;
		}
		?></div>
		<?php
	}
	require_once('../includes/utils-ads.php');
	showRegularAdSection();
	?>
	<div class="advent-begin">
		&#9660;&nbsp;<?php echo $language ? 'Click on the day number to begin': 'Cliquez sur le numéro du jour pour jouer'; ?>&nbsp;&#9660;
	</div>
	<div class="advent-calendar">
		<img src="images/advent-calendar/calendar.jpg" class="advent-calendar-bg" />
		<?php
		$x0 = 8;
		$y0 = 8;
		$u = 23.2;
		$v = 12.8;
		$squareDays = array(14,2,19,11,17,22,24,18,23,12,8,20,7,21,13,6,10,5,16,4,9,3,15,1);
		$completedDays = array();
		if ($id) {
			$getCompletedDays = mysql_query('SELECT day FROM mkadvent WHERE year="'.$year.'" AND user='. $id);
			while ($completedDay = mysql_fetch_array($getCompletedDays))
				$completedDays[$completedDay['day']] = true;
		}
		foreach ($squareDays as $i=>$d) {
			$x = $x0 + $u*($i%4);
			$y = $y0 + $v*floor($i/4);
			$d = $squareDays[$i];
			$className = 'advent-square';
			$state = 0;
			if (isset($completedDays[$d])) {
				$className .= ' advent-square-success';
				$state = 2;
			}
			elseif ($day > $d) {
				$className .= ' advent-square-open';
				$state = 1;
			}
			?>
			<div data-day="<?php echo $d; ?>" data-state="<?php echo $state; ?>" class="<?php echo $className; ?>" style="left:<?php echo $x; ?>%;top:<?php echo $y; ?>%" onclick="openSquare(this)">
				<span><?php echo $d; ?></span>
			</div>
			<?php
		}
		?>
	</div>
	<div id="advent-challenge-mask" onclick="closeSquare()">
		<div id="advent-challenge-ctn" onclick="event.stopPropagation()">
			<a id="advent-challenge-cross" href="#null" onclick="closeSquare();return false">&times;</a>
			<div id="advent-challenge-title-ctn">
				<img src="images/advent-calendar/star.png" alt="star" />
				<h1 id="advent-challenge-title">Défi du 1 décembre</h1>
			</div>
			<div id="advent-challenge-state">Ce défi a été réussi</div>
			<div id="advent-challenge-img"><img alt="star" /></div>
			<div id="advent-challenge-name"></div>
			<div id="advent-challenge-body">Finissez le <strong>Circuit Mario 1</strong> en mode <strong>Contre-la-Montre</strong> en moins de <strong>40 secondes</strong>.</div>
			<div id="advent-challenge-extra">En mode difficile, avec 8 joueurs</div>
			<div id="advent-challenge-link"><a id="advent-challenge-button"><?php echo $language ? 'Challenge accepted!' : 'Relever le défi'; ?></a></div>
		</div>
	</div>
</main>
<?php
include('../includes/footer.php');
?>
<?php
mysql_close();
?>
</body>
</html>