<?php
include('getId.php');
include('session.php');
include('language.php');
include('persos.php');
include('initdb.php');
require_once('utils-challenges.php');
if (isset($_GET['cl']))
    $clId = $_GET['cl'];
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/perso-editor.css" />
<style type="text/css">
body {
    width: 96%;
}
.reward-explain {
    margin-bottom: 16px;
    font-size: 1.2rem;
}
.rewards-list {
    display: flex;
    flex-direction: column;
    align-items: center;
}
.rewards-list-item {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    align-items: center;
    width: 100%;
    max-width: 700px;
    margin: 1px 0;
    padding: 1px 0;
}
.rewards-list-item:nth-child(2n) {
    background-color: #6273C3;
}
.rewards-list-item:nth-child(2n+1) {
    background-color: #6A83CC;
}
.rewards-list-item:nth-child(2n).reward-list-item-success {
    background-color: #00ac80;
}
.rewards-list-item:nth-child(2n+1).reward-list-item-success {
    background-color: #0dbd8b;
}
.reward-item-description > div::-webkit-scrollbar {
	width: 10px;
}
.reward-item-description > div::-webkit-scrollbar-track {
	-webkit-box-shadow: inset 0 0 5px rgba(0,0,0,0.5);
	border-radius: 5px;
}
.reward-item-description > div::-webkit-scrollbar-thumb {
	border-radius: 5px;
	-webkit-box-shadow: inset 0 0 5px rgba(0,0,0,0.3);
	background-color: #9AF;
}
.reward-item-description::-moz-scrollbar {
	width: 12px;
}
.reward-item-description::-moz-scrollbar-track {
	-moz-box-shadow: inset 0 0 5px rgba(0,0,0,0.3);
	border-radius: 5px;
}
.reward-item-description::-moz-scrollbar-thumb {
	border-radius: 5px;
	-moz-box-shadow: inset 0 0 5px rgba(0,0,0,0.5);
	background-color: #9AF;
}
.reward-item-description::-ms-scrollbar {
	width: 12px;
}
.reward-item-description::-ms-scrollbar-track {
	-ms-box-shadow: inset 0 0 5px rgba(0,0,0,0.3);
	border-radius: 5px;
}
.reward-item-description::-ms-scrollbar-thumb {
	border-radius: 5px;
	-ms-box-shadow: inset 0 0 5px rgba(0,0,0,0.5);
	background-color: #9AF;
}
.reward-item-description::-o-scrollbar {
	width: 12px;
}
.reward-item-description::-o-scrollbar-track {
	-o-box-shadow: inset 0 0 5px rgba(0,0,0,0.3);
	border-radius: 5px;
}
.reward-item-description::-o-scrollbar-thumb {
	border-radius: 5px;
	-o-box-shadow: inset 0 0 5px rgba(0,0,0,0.5);
	background-color: #9AF;
}
.reward-item-description::-scrollbar {
	width: 12px;
}
.reward-item-description::-scrollbar-track {
	-box-shadow: inset 0 0 5px rgba(0,0,0,0.3);
	border-radius: 5px;
}
.reward-item-description::-scrollbar-thumb {
	border-radius: 5px;
	-box-shadow: inset 0 0 5px rgba(0,0,0,0.5);
	background-color: #9AF;
}
.reward-list-item-success .reward-item-description > div::-webkit-scrollbar-thumb {
	background-color: #7E8;
}
.reward-list-item-success .reward-item-description > div::-moz-scrollbar-thumb {
	background-color: #7E8;
}
.reward-list-item-success .reward-item-description > div::-ms-scrollbar-thumb {
	background-color: #7E8;
}
.reward-list-item-success .reward-item-description > div::-o-scrollbar-thumb {
	background-color: #7E8;
}
.reward-list-item-success .reward-item-description > div::-scrollbar-thumb {
	background-color: #7E8;
}
.reward-item-circuit {
    width: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.reward-item-description {
    font-size: 1rem;
    flex: 1;
    text-align: left;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.reward-item-description strong {
    color: #CEF;
}
.reward-list-item-success {
    color: #BED;
}
.reward-list-item-success strong {
    color: #CFE;
}
.reward-item-circuit .reward-item-success {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 32px;
	height: 32px;
	border-radius: 16px;
	text-align: center;
	background-color: rgb(51, 160, 51);
	border: solid 1px rgb(192,255,204);
	color: white;
	font-size: 24px;
    opacity: 0.85;
    cursor: default;
}
.reward-list-item-success .reward-item-circuit {
	opacity: 0.8;
}
.reward-item-description > div {
    padding-right: 5px;
    overflow: auto;
}
.reward-item-action {
    width: 80px;
}
.reward-item-description {
    padding: 2px 10px;
}
.reward-item-action {
    padding: 5px 10px;
}
.creation_icon.single_creation {
	background-repeat: no-repeat;
	background-position: center;
	background-size: cover;
}
.creation_icon.creation_cup {
	background-color: black;
	background-repeat: no-repeat;
	background-position: top left, top right, bottom left, bottom right;
	background-size: 50% 50%;
	background-size: calc(50% + 1px) calc(50% + 1px);
}
.reward-item-rewarded {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    font-size: 15px;
}
.reward-item-rewarded img {
    height: 15px;
    margin-right: 5px;
    position: relative;
    top: -1px;
}
.reward-item-perso {
    margin-top: 2px;
    height: 28px;
}
.reward-item-author {
    font-size: 0.8rem;
	max-width: 80px;
	overflow: hidden;
	text-overflow: ellipsis;
    white-space: nowrap;
    opacity: 0.8;
}
.reward-item-try {
    margin-top: 4px;
	display: inline-block;
	padding: 3px 10px;
	background-color: #1C9;
	font-weight: bold;
	color: white;
	border-radius: 5px;
	text-decoration: none;
    cursor: pointer;
    font-size: 0.9rem;
}
.reward-item-try:hover {
	background-color: #0B8;
	color: white;
}
.reward-pages a {
    text-decoration: none;
}
@media screen and (max-width: 599px) {
	.reward-item-circuit {
		display: none;
	}
}
@media screen and (min-width: 600px) {
    .rewards-list-item {
        height: 80px;
        overflow: hidden;
    }
    .reward-item-circuit {
        height: 80px;
    }
    .reward-item-description {
        height: 76px;
    }
}
#fancy-title {
    position: fixed;
    background-color: #35a23e;
    color: white;
    padding: 5px;
    font-size: 0.8rem;
    margin-left: 5px;
    margin-right: 5px;
    border-radius: 5px;
}
#fancy-title.empty {
    background-color: #048;
}
</style>
<script type="text/javascript">
function showFancyTitle(e) {
    var $elt = e.currentTarget;
    var $fancyTitle = document.getElementById("fancy-title");
    if (!$fancyTitle) {
        $fancyTitle = document.createElement("div");
        $fancyTitle.id = "fancy-title";
        document.body.appendChild($fancyTitle);
    }
    if ($elt.dataset.players) {
        $fancyTitle.className = "";
        $fancyTitle.innerHTML = "<?php echo $language ? 'Unlocked by:' : 'Débloqué par :'; ?>" +
        $elt.dataset.players.split(",").map(function(p) { return "<div><small>✔</small>&nbsp;"+ p +"</div>"; }).join("");
    }
    else {
        $fancyTitle.className = "empty";
        $fancyTitle.innerHTML = "<?php echo $language ? 'Unlocked by nobody<br />Be the 1st one!' : 'Débloqué par personne<br />Soyez le 1er !'; ?>";
    }
    var rect = $elt.getBoundingClientRect();
    $fancyTitle.style.left = (rect.left-20) +"px";
    $fancyTitle.style.top = (rect.bottom+5) +"px";
}
function hideFancyTitle(e) {
    var $fancyTitle = document.getElementById("fancy-title");
    document.body.removeChild($fancyTitle);
}
</script>
<?php
if (empty($clId))
    include('o_online.php');
?>
<title><?php echo $language ? 'Unlockable characters':'Persos à débloquer'; ?></title>
</head>
<body>
<h1><?php echo $language ? 'Unlockable characters':'Persos à débloquer'; ?></h1>
<?php
if (empty($clId)) {
    ?>
    <div class="reward-explain"><?php
    if ($language) {
        ?>
        This page shows the list of unlockable characters created by members.
        To unlock them, you have to complete the challenges listed below.
        To learn more about how the challenges work, <a href="challengesList.php">click here</a>.
        <?php
    }
    else {
        ?>
        Cette page affiche la liste des persos à débloquer créés par les membres.
        Pour les débloquer, vous devrez réaliser les défis affichés ci-dessous.
        Pour en savoir plus sur le fonctionnement des défis, <a href="challengesList.php">cliquez ici</a>.
        <?php
    }
    ?></div>
    <?php
}
$playerId = +$id;
$rewardsPerPage = 20;
$currentPage = isset($_GET['page']) ? $_GET['page']:1;
$currentCursor = ($currentPage-1)*$rewardsPerPage;
$rewardsSQL = array(
    'data' => array(
        'columns' => 'l.*,r.id,r.clist,r.charid,w.player,c.name,c.sprites',
        'limit' => ' LIMIT '.$currentCursor.','. $rewardsPerPage
    ),
    'nb' => array(
        'columns' => 'COUNT(*) AS nb',
        'limit' => ''
    )
);
$getRewards = array();
foreach ($rewardsSQL as $key=>$rewardSQL)
    $getRewards[$key] = mysql_query('SELECT '.$rewardSQL['columns'].' FROM mkclrewards r LEFT JOIN mkclrewarded w ON w.reward=r.id AND w.player='. $playerId .' INNER JOIN mkclrace l ON l.id=r.clist INNER JOIN mkchars c ON r.charid=c.id'. (empty($clId) ? '':' WHERE r.clist='.$clId) .' ORDER BY r.id DESC'.$rewardSQL['limit']);
$getRewardsData = $getRewards['data'];
$getNbRewards = mysql_fetch_array($getRewards['nb']);
$nbRewards = $getNbRewards['nb'];
$nbPages = ceil($nbRewards/$rewardsPerPage);
?>
<div class="rewards-list">
<?php
while ($reward = mysql_fetch_array($getRewardsData)) {
    $circuit = getCircuitPayload($reward);
    $myCicuit = ($circuit['identifiant'] == $identifiants[0]) && ($circuit['identifiant2'] == $identifiants[1]) && ($circuit['identifiant3'] == $identifiants[2]) && ($circuit['identifiant4'] == $identifiants[3]);
    $isCompleted = ($reward['player'] || $myCicuit);
    $isCup = (strpos($circuit['cicon'], ',') !== false);
    $challenges = mysql_query('SELECT l.*,c.* FROM mkclrewardchs r INNER JOIN mkchallenges c ON r.challenge=c.id INNER JOIN mkclrace l ON l.id=c.clist WHERE r.reward='. $reward['id']);
    ?>
    <div class="rewards-list-item<?php if ($isCompleted) echo ' reward-list-item-success'; ?>">
        <?php
        if (empty($clId)) {
            ?>
        <div class="reward-item-circuit creation_icon <?php echo ($isCup ? 'creation_cup':'single_creation'); ?>"<?php
            if (isset($circuit['icon'])) {
                $allMapSrcs = $circuit['icon'];
                foreach ($allMapSrcs as $i=>$iMapSrc)
                    $allMapSrcs[$i] = "url('images/creation_icons/$iMapSrc')";
                echo ' style="background-image:'.implode(',',$allMapSrcs).'"';
            }
            else
                echo ' data-cicon="'.$circuit['cicon'].'"';
        ?>><?php
        if ($isCompleted)
            echo '<div class="reward-item-success">✔</div>';
        ?></div>
            <?php
        }
        ?>
        <div class="reward-item-description"><div>
        <?php
        $challengeParams = array('circuit' => true);
        while ($challenge = mysql_fetch_array($challenges)) {
            $challengeData = getChallengeDetails($challenge, $challengeParams);
            $circuitName = $challengeData['circuit']['name'];
            echo '<strong>'. ($circuitName ? $circuitName : ($language ? 'Untitled':'Sans titre')) .'</strong>: ';
            echo $challengeData['description']['main'].'<br />';
        }
        ?>
        </div></div>
        <div class="reward-item-action">
            <?php
            $getPlayers = mysql_query('SELECT j.nom FROM mkclrewarded r INNER JOIN mkjoueurs j ON r.player=j.id WHERE r.reward='. $reward['id']);
            $playerNames = array();
            while ($player = mysql_fetch_array($getPlayers))
                $playerNames[] = $player['nom'];
            ?>
            <div class="reward-item-rewarded" data-players="<?php
                echo implode(',', $playerNames);
            ?>" onmouseover="showFancyTitle(event)" onmouseout="hideFancyTitle()">
                <img src="images/cups/cup1.png" alt="<?php echo $language ? 'Réussi par':'Succeeded by'; ?>" />
                <?php echo count($playerNames); ?>
            </div>
        <?php
        if ($isCompleted) {
            $sprites = get_sprite_srcs($reward['sprites']);
            ?>
            <img class="reward-item-perso" src="<?php echo $sprites['ld']; ?>" alt="<?php echo htmlspecialchars($reward['name']); ?>" />
            <?php
        }
        else if (empty($clId)) {
            ?>
            <a class="reward-item-try" href="<?php echo $circuit['href']; ?>"><?php echo $language ? 'Take&nbsp;up':'Relever'; ?></a>
            <?php
        }
        if ($circuit['author']) {
            ?>
            <div class="reward-item-author">
                <?php echo ($language ? 'By':'Par').' <strong>'. $circuit['author'] .'</strong>'; ?>
            </div>
            <?php
        }
        ?>
        </div>
    </div>
    <?php
}
?>
</div>
<?php
if ($nbPages > 1) {
    ?>
    <div class="reward-pages"><p>
        Page : <?php
        $get = $_GET;
        for ($i=1;$i<=$nbPages;$i++) {
            $get['page'] = $i;
            if ($i == $currentPage)
                echo '<strong>'.$i.'</strong>';
            else
                echo '<a href="?'. http_build_query($get) .'">'. $i .'</a>';
            echo ' ';
        }
        ?>
    </p></div>
    <?php
}
?>
<?php
if (empty($clId)) {
    ?>
    <p><a href="mariokart.php"><?php echo $language ? "Back to Mario Kart PC":"Retour à Mario Kart PC"; ?></a></p>
    <div class="perso-bottom">
        <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
        <!-- Mario Kart PC -->
        <ins class="adsbygoogle"
                style="display:inline-block;width:468px;height:60px"
                data-ad-client="ca-pub-1340724283777764"
                data-ad-slot="6691323567"></ins>
        <script>
        (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>
    <?php
}
else {
    ?>
    <p><a href="mariokart.php" onclick="window.close();return false"><?php echo $language ? "Back to Mario Kart PC":"Retour à Mario Kart PC"; ?></a></p>
    <?php
}
?>
<script type="text/javascript" src="scripts/posticons.js"></script>
</body>
</html>
<?php
mysql_close();
?>