<?php
if (isset($_GET['key']) && ($_GET['key'] == 918875298));
else exit;
session_start();
$id = isset($_SESSION['mkid']) ? $_SESSION['mkid']:null;
include('language.php');
include('initdb.php');
mysql_set_charset('utf8');
if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $id .'"')))
	$myPseudo = $getPseudo['nom'];
else
	$myPseudo = null;
if (isset($_COOKIE['mkp'])) {
	require_once('credentials.php');
	$myCredentials = credentials_decrypt($_COOKIE['mkp']);
	if (!$myPseudo) {
		if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. mysql_real_escape_string($myCredentials[0]) .'"')))
			$myPseudo = $getPseudo['nom'];
	}
	$myCode = $myCredentials[1];
}
if ($id && ($getBan=mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'.$id.'" AND banned')))) {
	include('getId.php');
	if ($getBan['banned'] == 1)
		include('ban_ip.php');
	echo 'Access denied';
	mysql_close();
	exit;
}
$isCup = false;
$isBattle = false;
$isSingle = false;
$isMCup = false;
if (isset($_GET['mid'])) {
	$isCup = true;
	$isMCup = true;
	$nid = $_GET['mid'];
}
elseif (isset($_GET['sid'])) {
	$isCup = true;
	$nid = $_GET['sid'];
	$complete = false;
}
elseif (isset($_GET['cid'])) {
	$isCup = true;
	$nid = $_GET['cid'];
	$complete = true;
}
elseif (isset($_GET['id'])) {
	$isCup = true;
	$nid = $_GET['id'];
	$complete = false;
	$isSingle = true;
}
elseif (isset($_GET['i'])) {
	$isCup = true;
	$nid = $_GET['i'];
	$complete = true;
	$isSingle = true;
}
if (isset($_GET['battle']))
	$isBattle = true;
if (isset($_GET['key'])) {
	$privateLink = $_GET['key'];
	if ($privateLinkData = mysql_fetch_array(mysql_query('SELECT * FROM `mkprivgame` WHERE id="'.$privateLink.'"'))) {
		if ($id)
			mysql_query('UPDATE `mkprivgame` SET last_used_date=NULL WHERE id="'.$privateLink.'"');
	}
	else {
		echo 'Private link is invalid or has expired';
		mysql_close();
		exit;
	}
}
if ($isCup)
	$NBCIRCUITS = ($isSingle?1:4);
else {
	include_once('circuitNames.php');
	$NBCIRCUITS = $nbVSCircuits;
}
if ($isCup) {
	$circuitsData = array();
	$cupIDs = array();
	$trackIDs = array();
	if ($isMCup) {
		$getMCup = mysql_fetch_array(mysql_query('SELECT nom,mode FROM `mkmcups` WHERE id="'. $nid .'"'));
		$complete = ($getMCup['mode'] == 1);
		$getTracks = mysql_query('SELECT c.* FROM `mkmcups_tracks` t INNER JOIN `mkcups` c ON t.cup=c.id WHERE t.mcup="'. $nid .'" ORDER BY t.ordering');
		$getCup = array('nom' => $getMCup['nom']);
		while ($getTrack = mysql_fetch_array($getTracks)) {
			$cupIDs[] = $getTrack['id'];
			for ($i=0;$i<4;$i++)
				$trackIDs[] = $getTrack['circuit'.$i];
		}
		$NBCIRCUITS = mysql_numrows($getTracks)*4;
	}
	elseif ($isSingle) {
		$getCup = mysql_fetch_array(mysql_query('SELECT nom FROM `'. ($complete ? ($isBattle?'arenes':'circuits'):'mkcircuits') .'` WHERE id="'. $nid .'"'));
		$trackIDs[] = $nid;
	}
	else {
		$getCup = mysql_fetch_array(mysql_query('SELECT nom,circuit0,circuit1,circuit2,circuit3 FROM `mkcups` WHERE id="'. $nid .'"'));
		$cupIDs[] = $nid;
		for ($i=0;$i<4;$i++)
			$trackIDs[] = $getCup['circuit'.$i];
	}
	if ($complete) {
		if (!empty($trackIDs)) {
			$table = $isBattle?'arenes':'circuits';
			$getCircuits = mysql_query('SELECT c.*,d.data FROM `'.$table.'` c LEFT JOIN `'.$table.'_data` d ON c.id=d.id WHERE c.id IN ('. implode(',',$trackIDs) .')');
			$allTracks = array();
			while ($getCircuit = mysql_fetch_array($getCircuits))
				$allTracks[$getCircuit['ID']] = $getCircuit;
			foreach ($trackIDs as $trackID) {
				if (isset($allTracks[$trackID]))
					$circuitsData[] = $allTracks[$trackID];
				else {
					mysql_close();
					exit;
				}
			}
		}
	}
	else {
		$getCircuits = mysql_query('SELECT id,map,laps,nom FROM `mkcircuits` WHERE id IN ('. implode(',',$trackIDs) .') AND ' . ($isBattle?'type':'!type'));
		$allTracks = array();
		while ($getCircuit = mysql_fetch_array($getCircuits))
			$allTracks[$getCircuit['id']] = $getCircuit;
		foreach ($trackIDs as $trackID) {
			if (isset($allTracks[$trackID]))
				$circuitsData[] = $allTracks[$trackID];
			else {
				mysql_close();
				exit;
			}
		}
		$lettres = Array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'o', 't');
		$nbLettres = count($lettres);
		for ($i=0;$i<$NBCIRCUITS;$i++) {
			$circuit = &$circuitsData[$i];
			$pieces = mysql_query('SELECT * FROM `mkp` WHERE circuit="'.$circuit['id'].'"');
			while ($piece = mysql_fetch_array($pieces))
				$circuit['p'.$piece['id']] = $piece['piece'];
			for ($j=0;$j<$nbLettres;$j++) {
				$lettre = $lettres[$j];
				$getInfos = mysql_query('SELECT x,y FROM `mk'.$lettre.'` WHERE circuit="'. $circuit['id'] .'"');
				for ($k=0;$info=mysql_fetch_array($getInfos);$k++)
					$circuit[$lettre.$k] = $info['x'].','.$info['y'];
			}
			if ($isBattle) {
				$getPos = mysql_query('SELECT * FROM `mkr` WHERE circuit="'.$circuit['id'].'"');
				while ($pos = mysql_fetch_array($getPos)) {
					$circuit['s'.$pos['id']] = $pos['s'];
					$circuit['r'.$pos['id']] = $pos['r'];
				}
			}
			unset($circuit);
		}
	}
}
$simplified = ($isCup && !$complete);
$delNotif = true;
if (isset($privateLink)) {
	$delNotif = false;
	if ($getOptions = mysql_fetch_array(mysql_query('SELECT rules,public FROM `mkgameoptions` WHERE id="'.$privateLink.'"'))) {
		$linkOptions = json_decode($getOptions['rules']);
		if ($getOptions['public']) {
			$linkOptions->public = 1;
			$delNotif = true;
		}
	}
}
if ($id && $delNotif)
	mysql_query('DELETE FROM `mknotifs` WHERE user="'. $id .'" AND type="currently_online"');
if (isset($_SESSION['mklink'])) {
	if (isset($privateLink) && ($privateLink == $_SESSION['mklink']))
		$linkAccepted = true;
	unset($_SESSION['mklink']);
}
function escapeUtf8($str) {
	return preg_replace("/%u([0-9a-fA-F]{4})/", "&#x\\1;", htmlspecialchars($str));
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
   <head>
	   <title>Course en ligne Mario Kart PC</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="author" content="Timothé Malahieude" />
<meta name="description" content="Jeu de Mario Kart gratuit en ligne" />
<meta name="keywords" content="Mario, Kart, PC, jeu, course, jeu gratuit, multijoueur" />
<meta name="viewport" content="width=device-width, user-scalable=no" />
<meta name="thumbnail" content="https://mkpc.malahieude.net/images/screenshots/ssfr1.png" />
<meta property="og:image" content="https://mkpc.malahieude.net/images/mkthumbnail.jpg" />
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" /><meta name="robots" content="noindex" />
	
<link rel="stylesheet" media="screen" type="text/css" href="styles/mariokart.css" />
<style type="text/css">
.wait {
	position: absolute;
	text-align: center;
	color: #DDD;
	z-index: 20001;
	visibility: hidden;
}
@media (max-width: 850px) {
	.online-chat {
		display: none;
	}
}
</style>

<script type="text/javascript" src="mk/newmaps.js"></script>
	<script type="text/javascript">
var language = false;
var course = "VS";
var lCircuits = ["Circuit Mario 1","Plaine Donut 1","Plage Koopa 1","\u00cele Choco 1","Lac Vanille 1","Vall\u00e9e Fant\u00f4me 1","Circuit Mario 2","Ch\u00e2teau de Bowser 1","Plaine Donut 2","Ch\u00e2teau de Bowser 2","\u00cele Choco 2","Circuit Mario 3","Plage Koopa 2","Lac Vanille 2","Vall\u00e9e Fant\u00f4me 2","Plaine Donut 3","Vall\u00e9e Fant\u00f4me 3","Circuit Mario 4","Ch\u00e2teau de Bowser 3","Route Arc-en-Ciel","Circuit Peach","Plage Maskass","Bord du Fleuve","Ch\u00e2teau de Bowser I","Circuit Mario","Lac Boo","Pays Fromage","Ch\u00e2teau de Bowser II","Circuit Luigi","Jardin volant","\u00cele Cheep-Cheep","Pays Cr\u00e9puscule","Royaume Sorbet","Route Ruban","D\u00e9sert Yoshi","Ch\u00e2teau de Bowser III","Bord du Lac","Jet\u00e9e cass\u00e9e","Ch\u00e2teau de Bowser IV","Route Arc-en-Ciel","Circuit en 8","Cascade Yoshi","Plage Cheep-Cheep","Manoir de Luigi","D\u00e9sert du Soleil","Quartier Delfino","Flipper Waluigi","Corniche Champignon","Alpes DK","Horloge Tic-Tac","Circuit Mario","Bateau Volant","Stade Wario","Jardin Peach","Ch\u00e2teau de Bowser","Route Arc-en-Ciel"];
	var cp = {
	"mario":[0.5,0.5,0.5,0.5],
	"luigi":[0.625,0.5,0.375,0.5],
	"peach":[0.75,0.375,0.75,0.25],
	"toad":[0.625,0.375,0.625,0],
	"yoshi":[0.5,0.5,0.5,0.5],
	"bowser":[0,1,0.125,1],
	"donkey-kong":[0.25,0.875,0,0.875],
	"daisy":[1,0.375,1,0.25],
	"wario":[0.5,0.75,0,0.75],
	"koopa":[0.375,0.5,0.625,0.375],
	"waluigi":[0.875,0.25,0.625,0.625],
	"maskass":[0.625,0.5,0.375,0.5],
	"birdo":[0.875,0.125,0.875,0.5],
	"roi_boo":[0.375,0.75,0.125,0.75],
	"frere_marto":[0.125,0.625,0.375,0.625],		"bowser_skelet":[0.25,0.875,0.125,0.875],
		"flora_piranha":[0.25,1,0,1],
		"link":[0.875,0.5,0.125,0.625],
			"bowser_jr":[0.75,0.375,0.5,0.375],
	"harmonie":[0,0.625,0.5,0.625],
	"diddy-kong":[0.5,0.375,0.75,0],
	"skelerex":[0.25,0.5,0.75,0.25],
	"funky-kong":[0.25,0.75,0.25,0.875],
	"toadette":[0.75,0.25,0.75,0]
};
var pUnlocked = [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1];
var baseOptions = <?php include('getCourseOptions.php'); ?>;
var page = "OL";
var PERSOS_DIR = "images/sprites/uploads/";
var mId = <?php echo $id ? $id:'null'; ?>;
var mPseudo = "<?php echo $myPseudo; ?>", mCode = "<?php echo $myCode; ?>";
var isSingle = false;
var isBattle = false;
var isCup = false;
var complete = false;
var simplified = false;
var nid = null;
var shareLink = {
	key: <?php echo isset($privateLink) ? "'$privateLink'":'null'; ?>,
	player: <?php echo isset($privateLinkData) ? $privateLinkData['player']:'null'; ?>,
	options: <?php echo isset($linkOptions) ? json_encode($linkOptions):'null'; ?>,
	url:"<?php echo (isset($_SERVER['HTTPS'])?'https':'http') . '://' . $_SERVER['HTTP_HOST'] . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH); ?>",
	accepted: <?php echo isset($linkAccepted) ? 'true':'false'; ?>,
	params: [<?php
	$params = array();
	if ($isMCup)
		$params[] = '"mid='.$nid.'"';
	else {
		if ($isCup) {
			if ($isSingle) {
				if ($complete)
					$params[] = '"i='.$nid.'"';
				else
					$params[] = '"id='.$nid.'"';
			}
			else {
				if ($complete)
					$params[] = '"cid='.$nid.'"';
				else
					$params[] = '"sid='.$nid.'"';
			}
		}
	}
	if ($isBattle)
		$params[] = '"battle"';
	echo implode(',',$params);
	?>]
};
var NBCIRCUITS = 56;
var isds = 1;
	var aListMaps = listMaps;
	listMaps = function() {
		var a=1,n=56;		var aMaps = aListMaps();
		var res = {};
		for (var i=0;i<n;i++)
			res["map"+(i+a)] = aMaps["map"+(i+a)];
		return res;
	}
	</script>
<script type="text/javascript" src="scripts/mk.ds.js"></script><script type="text/javascript">document.addEventListener("DOMContentLoaded", MarioKart);</script>
</head>
<body>
<div id="mariokartcontainer"></div>

<div id="virtualkeyboard"></div>

<p id="waitrace" class="wait">Il vous reste <span id="racecountdown">30</span> seconde(s) pour choisir la prochaine course</p>
<p id="waitteam" class="wait">Il vous reste <span id="teamcountdown">10</span> seconde(s) pour choisir les équipes</p>
<p id="temps0"></p>
<p id="compteur0"></p>
<table id="infos0" cellspacing="1" cellpadding="0" style="visibility: hidden">
<tr><td></td></tr></table>

<form name="modes" method="get" action="#null" onsubmit="return false">
<div id="options-ctn">
<table cellpadding="3" cellspacing="0" border="0" id="options">
<tr>
<td id="pQuality">&nbsp;</td>
<td id="vQuality">
</td>
<td rowspan="4" id="commandes">
<strong>Se diriger</strong> : Fl&egrave;ches directionnelles<br />
<strong>Utiliser un objet</strong> : Barre d'espace<br />
<strong><em>OU</em></strong> : Clic gauche<br />
<strong>Sauter/déraper</strong> : Ctrl<br />
<strong>Vue arri&egrave;re/avant</strong> : X<br />
<strong>Quitter</strong> : &Eacute;chap</td>
	</tr>
<tr><td id="pSize">
</td>
<td id="vSize">
&nbsp;
</td></tr>
<tr><td id="pMusic">
&nbsp;
</td>
<td id="vMusic">
&nbsp;
</td></tr>
<tr><td id="pSfx">
&nbsp;
</td>
<td id="vSfx">
&nbsp;
</td></tr>
</table>
</div>
<div id="vPub"><script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Mario Kart PC -->
<ins class="adsbygoogle"
     style="display:inline-block;width:468px;height:60px"
     data-ad-client="ca-pub-1340724283777764"
     data-ad-slot="6691323567"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script></div>
</form>
<table id="objet0" border="1" cellpadding="0" cellspacing="0">
<tr>	<td id="roulette0" valign="middle"></td>	</tr>
</table>
<div id="dMaps"></div>
<p id="infoPlace0"></p>
<div id="lakitu0"><div></div></div>
<div id="drift0">
	<img alt="." src="images/drift.png" class="driftimg" />
</div>
<div id="scroller0" width="100px" height="100px" style="width: 100px; height: 100px; overflow: hidden; position: absolute; visibility: hidden">
	<div style="position: absolute; left: 0; top: 0">
		<img class="aObjet" alt="." src="images/items/fauxobjet.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/banane.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/carapace.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/bobomb.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/carapacerouge.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/carapacebleue.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/champi.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/megachampi.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/etoile.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/eclair.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/billball.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/fauxobjet.gif" />
	</div>
</div>
<p id="presentation">
<strong><a href="index.php">Mario Kart PC</a></strong><br />
Affrontez jusqu'&agrave; 8 joueurs dans 6 modes de jeu in&eacute;dits !<br />
Dans les tournois du <strong>Grand Prix</strong>, gagnez 5 coupes de 4 courses afin de d&eacute;bloquer les 9 persos secrets !<br />
Avec le mode <strong>contre-la-montre</strong>, battez les <a href="classement.php" target="_blank">records</a> des autres joueurs et devenez champion du monde !<br />
En <strong>course VS</strong>, battez-vous contre les ordis et/ou contre un ami sur pas moins de 20 courses !<br />
En <strong>mode bataille</strong>, d&eacute;truisez les ballons de vos adversaires dans des combats acharn&eacute;s !<br />
Avec l'<strong>&eacute;diteur de circuit</strong>, cr&eacute;ez autant de circuits et ar&egrave;nes que vous voulez, avec votre imagenation pour seule limite !<br />
En <strong><a href="online.php">mode en ligne</a></strong>, affrontez les joueurs du monde entier et grimpez dans le <a href="bestscores.php" target="_blank">classement</a> !
	</p><div style="text-align: right;position: fixed;z-index:9999999;bottom: 0;width: auto;right: 1%;cursor: pointer;line-height: 0;display:block !important;"><a title="Hosted on free web hosting 000webhost.com. Host your own website for FREE." target="_blank" href="https://www.000webhost.com/?utm_source=000webhostapp&utm_campaign=000_logo&utm_medium=website&utm_content=footer_img"><img src="https://cdn.000webhost.com/000webhost/logo/footer-powered-by-000webhost-white2.png" alt="www.000webhost.com"></a></div><script>function getCookie(t){for(var e=t+"=",n=decodeURIComponent(document.cookie).split(";"),o=0;o<n.length;o++){for(var a=n[o];" "==a.charAt(0);)a=a.substring(1);if(0==a.indexOf(e))return a.substring(e.length,a.length)}return""}getCookie("hostinger")&&(document.cookie="hostinger=;expires=Thu, 01 Jan 1970 00:00:01 GMT;",location.reload());var wordpressAdminBody=document.getElementsByClassName("wp-admin")[0],notification=document.getElementsByClassName("notice notice-success is-dismissible"),hostingerLogo=document.getElementsByClassName("hlogo"),mainContent=document.getElementsByClassName("notice_content")[0],wpSidebar=document.getElementById("adminmenuwrap"),wpTopBarRight=document.getElementById("wp-admin-bar-top-secondary");if(null!=wordpressAdminBody&&notification.length>0&&null!=mainContent){var googleFont=document.createElement("link");googleFontHref=document.createAttribute("href"),googleFontRel=document.createAttribute("rel"),googleFontHref.value="https://fonts.googleapis.com/css?family=Roboto:300,400,600",googleFontRel.value="stylesheet",googleFont.setAttributeNode(googleFontHref),googleFont.setAttributeNode(googleFontRel);var css="@media only screen and (max-width: 576px) {#main_content {max-width: 320px !important;} #main_content h1 {font-size: 30px !important;} #main_content h2 {font-size: 40px !important; margin: 20px 0 !important;} #main_content p {font-size: 14px !important;} #main_content .content-wrapper {text-align: center !important;}} @media only screen and (max-width: 781px) {#main_content {margin: auto; justify-content: center; max-width: 445px;} .upgrade-btn-sidebar {display: none;} #wp-toolbar .top-bar-upgrade-btn {width: 52px; height: 46px !important; padding: 0 !important;} .top-bar-upgrade-btn__text {display: none;} .dashicons-star-filled.top-bar-upgrade-btn__icon::before {font-size: 28px; margin-top: 10px; width: 28px; height: 28px;}} @media only screen and (max-width: 1325px) {.web-hosting-90-off-image-wrapper {position: absolute; max-width: 95% !important;} .notice_content {justify-content: center;} .web-hosting-90-off-image {opacity: 0.3;}} @media only screen and (min-width: 769px) {.notice_content {justify-content: space-between;} #main_content {margin-left: 5%; max-width: 445px;} .web-hosting-90-off-image-wrapper {position: absolute; right: 0; display: flex; padding: 0 5%}} @media only screen and (max-width: 960px) {.upgrade-btn-sidebar {border-radius: 0 !important; padding: 10px 0 !important; margin: 0 !important;} .upgrade-btn-sidebar__icon {display: block !important; margin: auto;} .upgrade-btn-sidebar__text {display: none;}}  .web-hosting-90-off-image {max-width: 90%; margin-top: 20px;} .content-wrapper {z-index: 5} .notice_content {display: flex; align-items: center;} * {-webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;} .upgrade_button_red_sale{box-shadow: 0 2px 12px -6px #cc292f; max-width: 350px; border: 0; border-radius: 3px; background-color: #6747c7 !important; padding: 15px 55px !important;  margin-bottom: 48px; font-size: 14px; font-weight: 800; color: #ffffff;} .upgrade_button_red_sale:hover{color: #ffffff !important; background: rgba(103,71,199, 0.9) !important;} .upgrade-btn-sidebar {text-align:center;background-color:#ff4546;max-width: 350px;border-radius: 3px;border: 0;padding: 12px; margin: 20px 10px;display: block; font-size: 12px;color: #ffffff;font-weight: 700;text-decoration: none;} .upgrade-btn-sidebar:hover, .upgrade-btn-sidebar:focus, .upgrade-btn-sidebar:active {background-color: rgba(255,69,70, 0.9); color: #ffffff;} .upgrade-btn-sidebar__icon {display: none;} .top-bar-upgrade-btn {height: 100% !important; display: inline-block !important; padding: 0 10px !important; color: #ffffff; cursor: pointer;} .top-bar-upgrade-btn:hover, .top-bar-upgrade-btn:active, .top-bar-upgrade-btn:focus {background-color: #ff4546 !important; color: #ffffff !important;} .top-bar-upgrade-btn__icon {margin-right: 6px;}",style=document.createElement("style"),sheet=window.document.styleSheets[0];style.styleSheet?style.styleSheet.cssText=css:style.appendChild(document.createTextNode(css)),document.getElementsByTagName("head")[0].appendChild(style),document.getElementsByTagName("head")[0].appendChild(googleFont);var button=document.getElementsByClassName("upgrade_button_red")[0],link=button.parentElement;link.setAttribute("href","https://www.hostinger.com/hosting-starter-offer?utm_source=000webhost&utm_medium=panel&utm_campaign=000-wp"),link.innerHTML='<button class="upgrade_button_red_sale">Upgrade Now</button>',(notification=notification[0]).setAttribute("style","background-color: #f8f8f8; border-left-color: #6747c7 !important;"),notification.className="notice notice-error is-dismissible";var mainContentHolder=document.getElementById("main_content");mainContentHolder.setAttribute("style","padding: 0;"),hostingerLogo[0].remove();var h1Tag=notification.getElementsByTagName("H1")[0];h1Tag.className="000-h1",h1Tag.innerHTML="Limited Time Offer",h1Tag.setAttribute("style","color: #32454c;  margin-top: 48px; font-size: 48px; font-weight: 700;");var h2Tag=document.createElement("H2");h2Tag.innerHTML="From $0.79/month",h2Tag.setAttribute("style","color: #32454c; margin: 20px 0 45px 0; font-size: 48px; font-weight: 700;"),h1Tag.parentNode.insertBefore(h2Tag,h1Tag.nextSibling);var paragraph=notification.getElementsByTagName("p")[0];paragraph.innerHTML="Don’t miss the opportunity to enjoy up to <strong>4x WordPress Speed, Free SSL and all premium features</strong> available for a fraction of the price!",paragraph.setAttribute("style",'font-family: "Roboto", sans-serif; font-size: 18px; font-weight: 300; color: #6f7c81; margin-bottom: 20px;');var list=notification.getElementsByTagName("UL")[0];list.remove();var org_html=mainContent.innerHTML,new_html='<div class="content-wrapper">'+mainContent.innerHTML+'</div><div class="web-hosting-90-off-image-wrapper"><img class="web-hosting-90-off-image" src="https://cdn.000webhost.com/000webhost/promotions/wp-inject-default-img.png"></div>';mainContent.innerHTML=new_html;var saleImage=mainContent.getElementsByClassName("web-hosting-90-off-image")[0];wpSidebar.insertAdjacentHTML("beforeend",'<a href="https://www.hostinger.com/hosting-starter-offer?utm_source=000webhost&amp;utm_medium=panel&amp;utm_campaign=000-wp-sidebar" target="_blank" class="upgrade-btn-sidebar"><span class="dashicons dashicons-star-filled upgrade-btn-sidebar__icon"></span><span class="upgrade-btn-sidebar__text">Upgrade</span></a>'),wpTopBarRight.insertAdjacentHTML("beforebegin",'<a class="top-bar-upgrade-btn" href="https://www.hostinger.com/hosting-starter-offer?utm_source=000webhost&amp;utm_medium=panel&amp;utm_campaign=000-wp-top-bar" target="_blank"><span class="ab-icon dashicons-before dashicons-star-filled top-bar-upgrade-btn__icon"></span><span class="top-bar-upgrade-btn__text">Go Premium</span></a>')}</script><script type="text/javascript" src="https://a.opmnstr.com/app/js/api.min.js" data-campaign="f6brbmuxflyqoriatchv" data-user="71036" async></script></body>
</html>