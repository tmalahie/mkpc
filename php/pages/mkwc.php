<?php
include('../includes/getId.php');
include('../includes/language.php');
include('../includes/session.php');
include('../includes/initdb.php');
$console = isset($_GET['console']) ? $_GET['console'] : null;
$year = 2023;
$playInStage = $language ? 'Play-In Stage':'Tour Préliminaire';
$groupStage = $language ? 'Group Stage':'Phase de Groupe';
$lowerStage = $language ? 'Lower Group Stage':'Phase de Groupe inférieure';
$upperStage = $language ? 'Upper Group Stage':'Phase de Groupe supérieure';
$playIn = $language ? 'Play-In':'Qualifications';
$group = $language ? 'Group':'Groupe';
$isPollClosed = false;//(time() > 1657335600);
switch ($console) {
case 'mkw':
    $consoleName = 'Mario Kart Wii';
    $teams = array(
        $groupStage => array(
            "$group A" => array(
                //'header' => $language ? "Asia: all of Asia, outside of Japan.\nLatin America: all of Latin America, outside of Brazil." : "Asie: toute l'Asie, sauf le Japon.\nAmérique Latine: toute l'Amérique Latine, sauf le Brésil.",
                'url' => 'https://mariokartworldcuphistory.000webhostapp.com/world_cup/mkwii/2023.html',
                'list' => array(
                    'uss'=> $language ? 'United States South':'États-Unis du Sud',
                    'aus'=> $language ? 'Australia':'Australie',
                    'ita'=> $language ? 'Italy':'Italie',
                    'asi'=> $language ? 'Asia':'Asie'
                )
            ),
            "$group B" => array(
                'list' => array(
                    'usn'=> $language ? 'United States North':'États-Unis du Nord',
                    'lta'=> $language ? 'Latin America':'Amerique Latine',
                    'den'=> $language ? 'Denmark':'Danemark',
                    'ire'=> $language ? 'Ireland':'Irlande'
                )
            ),
            "$group C" => array(
                'list' => array(
                    'eng'=> $language ? 'England':'Angleterre',
                    'nor'=> $language ? 'Norway':'Norvège',
                    'can'=> $language ? 'Canada':'Canada',
                    'sco'=> $language ? 'Scotland':'Écosse'
                )
            ),
            "$group D" => array(
                'list' => array(
                    'jap'=> $language ? 'Japan':'Japon',
                    'ger'=> $language ? 'Germany':'Allemagne',
                    'fra'=> $language ? 'France':'France',
                    'gre'=> $language ? 'Greece':'Grèce'
                )
            )
        )
    );
    break;
case 'mkt':
    $consoleName = 'Mario Kart Tour';
    $teams = array(
        $playInStage => array(
            "$group 1" => array(
                'url' => 'https://mariokartworldcuphistory.000webhostapp.com/world_cup/mkt/2023.html',
                'list' => array(
                    'bra'=> $language ? 'Brazil':'Brésil',
                    'pan'=> $language ? 'Panama':'Panama',
                    'gua'=> $language ? 'Guatemala':'Guatemala',
                    'can'=> $language ? 'Canada':'Canada'
                ),
                'eliminated' => array('pan', 'can')
            ),
            "$group 2" => array(
                'list' => array(
                    'chi'=> $language ? 'Chile':'Chili',
                    'arg'=> $language ? 'Argentina':'Argentina',
                    'ger'=> $language ? 'Germany':'Allemagne',
                    'aus'=> $language ? 'Australia':'Australie'
                ),
                'eliminated' => array('ger', 'aus')
            ),
            "$group 3" => array(
                'list' => array(
                    'ecu'=> $language ? 'Ecuador':'Équateur',
                    'swi'=> $language ? 'Switzerland':'Suisse',
                    'ven'=> $language ? 'Venezuela':'Venezuela',
                    'bol'=> $language ? 'Bolivia':'Bolivie'
                ),
                'eliminated' => array('ven', 'bol')
            ),
            "$group 4" => array(
                'list' => array(
                    'spa'=> $language ? 'Spain':'Espagne',
                    'ukg'=> $language ? 'United Kingdom':'Royaume-Uni',
                    'nic'=> $language ? 'Nicaragua':'Nicaragua'
                ),
                'eliminated' => array('nic')
            )
        ),
        $groupStage => array(
            "$group A" => array(
                'list' => array(
                    'mex'=> $language ? 'Mexico':'Mexique',
                    'sal'=> $language ? 'El Salvador':'Salvador',
                    'spa'=> $language ? 'Spain':'Espagne',
                    'gua'=> $language ? 'Guatemala':'Guatemala'
                ),
                'eliminated' => array('spa', 'gua', 'sal'),
                'winner' => array('mex')
            ),
            "$group B" => array(
                'list' => array(
                    'per'=> $language ? 'Peru':'Pérou',
                    'col'=> $language ? 'Colombia':'Colombie',
                    'arg'=> $language ? 'Argentina':'Argentina',
                    'swi'=> $language ? 'Switzerland':'Suisse'
                ),
                'eliminated' => array('arg', 'swi', 'per', 'col')
            ),
            "$group C" => array(
                'list' => array(
                    'usa'=> $language ? 'USA':'États-Unis',
                    'fra'=> $language ? 'France':'France',
                    'ecu'=> $language ? 'Ecuador':'Équateur',
                    'chi'=> $language ? 'Chile':'Chili'
                ),
                'eliminated' => array('ecu', 'chi', 'fra', 'usa')
            ),
            "$group D" => array(
                'list' => array(
                    'jap'=> $language ? 'Japan':'Japon',
                    'hok'=> $language ? 'Hong Kong':'Hong Kong',
                    'bra'=> $language ? 'Brazil':'Brésil',
                    'ukg'=> $language ? 'United Kingdom':'Royaume-Uni'
                ),
                'eliminated' => array('bra', 'ukg', 'hok', 'jap')
            )
        )
    );
    break;
case 'mk8d':
    $consoleName = 'Mario Kart 8 Deluxe';
    $teams = array(
        $playInStage => array(
            "$group I" => array(
                'header' => $language ? "Caledonbria: Scotland and Wales\nEastern Europe: all of Eastern Europe." : "Caledonbria : Ecosse et Pays de Galles\nEurope de l'Est : toute l'Europe de l'Est.",
                'url' => 'https://mariokartworldcuphistory.000webhostapp.com/world_cup/mk8d/2023.html',
                'list' => array(
                    'cri'=> $language ? 'Costa Rica':'Costa Rica',
                    'cal'=> $language ? 'Caledonbria':'Caledonbria',
                    'pri'=> $language ? 'Puerto Rico':'Puerto Rico',
                    'lux'=> $language ? 'Luxembourg':'Luxembourg'
                )
            ),
            "$group II" => array(
                'list' => array(
                    'mag'=> $language ? 'Maghreb':'Maghreb',
                    'kor'=> $language ? 'South Korea':'Corée du Sud',
                    'eue'=> $language ? 'Eastern Europe':'Europe de l\'Est',
                    'por'=> $language ? 'Portugal':'Portugal'
                )
            ),
            "$group III" => array(
                'list' => array(
                    'col'=> $language ? 'Colombia':'Colombie',
                    'chn'=> $language ? 'China':'Chine',
                    'aru'=> $language ? 'Argentina-Uruguay':'Argentine-Uruguay',
                    'hkt'=> $language ? 'Hong Kong-Taiwan':'Hong Kong-Taiwan'
                )
            )
        ),
        $lowerStage => array(
            "$group 1" => array(
                'header' => $language ? "Nordic: all of nordic countries and territories." : "Nordique : l'ensemble des pays et territoires nordiques.",
                'list' => array(
                    'bel'=> $language ? 'Belgium':'Belgique',
                    'aus'=> $language ? 'Australia':'Australie',
                    'nor'=> $language ? 'Nordic':'Nordique',
                    'pin0'=> $language ? 'TBD':'TBD'
                )
            ),
            "$group 2" => array(
                'list' => array(
                    'swi'=> $language ? 'Switzerland':'Suisse',
                    'per'=> $language ? 'Peru':'Pérou',
                    'bra'=> $language ? 'Brazil':'Brésil',
                    'pin0'=> $language ? 'TBD':'TBD'
                )
            ),
            "$group 3" => array(
                'list' => array(
                    'net'=> $language ? 'Netherlands':'Pays-Bas',
                    'ire'=> $language ? 'Ireland':'Irlande',
                    'pin0'=> $language ? 'TBD':'TBD',
                    'pin1'=> $language ? 'TBD':'TBD'
                )
            ),
            "$group 4" => array(
                'list' => array(
                    'chi'=> $language ? 'Chile':'Chili',
                    'aut'=> $language ? 'Austria':'Autriche',
                    'pin0'=> $language ? 'TBD':'TBD',
                    'pin1'=> $language ? 'TBD':'TBD'
                )
            )
        ),
        $upperStage => array(
            "$group A" => array(
                //'header' => $language ? "Eastern Europe: all of Eastern Europe.\nNordic: all of nordic countries and territories.\nCentroamerica: Belize, El Salvador, Nicaragua and Panama." : "Europe de l'Est: toute l'Europe de l'Est.\nNordique: l'ensemble des pays et territoires nordiques.\nAmérique Centrale: Belize, Salvador, Nicaragua et Panama.",
                'list' => array(
                    'fra'=> $language ? 'France':'France',
                    'eng'=> $language ? 'England':'Angleterre',
                    'usa'=> $language ? 'United States':'États-Unis',
                    'mex'=> $language ? 'Mexico':'Mexique',
                )
            ),
            "$group B" => array(
                'list' => array(
                    'jap'=> $language ? 'Japan':'Japon',
                    'ger'=> $language ? 'Germany':'Allemagne',
                    'spa'=> $language ? 'Spain':'Espagne',
                    'can'=> $language ? 'Canada':'Canada',
                )
            )
        )
    );
    break;
default:
    $console = null;
}
function isNormalTeam($code) {
    return !preg_match('#^pin\d+$#', $code);
}
if (isset($teams)) {
    $teamsDict = array();
    foreach ($teams as $groups) {
        foreach ($groups as $group) {
            foreach ($group['list'] as $code => $country) {
                if (isNormalTeam($code))
                    $teamsDict[$code] = htmlspecialchars($country);
            }
        }
    }

}
if ($console && !$isPollClosed && isset($_POST['vote'])) {
    $vote = $_POST['vote'];
    if (isset($teamsDict[$vote])) {
        if ($id) {
            $success = $language ? 'Your bet has been saved' : 'Votre pari a été enregistré';
            $success .= '<br />';
            $success .= '<a href="#mVotesTitle" onclick="showOtherVotes()">'. ($language ? 'See other members\' bets':'Voir les paris des autres membres') .'</a>';
            $success .= '<br />';
            //$success .= '<a href="mkwc.php">'. ($language ? 'Back to tournaments list':'Retour &agrave; la liste des tournois') .'</a>';
            $success .= '<a href="news.php?id=14697">'. ($language ? 'Back to MKWC news':'Retour &agrave; la news MKWC') .'</a>';
            mysql_query('INSERT IGNORE INTO mkwcbets SET console="'. $console .'",player="'. $id .'",vote="'. $_POST['vote'] .'"');
        }
        else {
            $error = $language ? 'You must be logged in to vote' : 'Vous devez être connecté pour pouvoir voter';
        }
    }
}
$myVote = null;
if ($id) {
    if ($getMyVote = mysql_fetch_array(mysql_query('SELECT vote FROM mkwcbets WHERE console="'. $console .'" AND player="'. $id .'"')))
        $myVote = $getMyVote['vote'];
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <title><?php echo $language ? 'MKWC - Place your bets!' : 'MKWC - Faites vos paris !'; ?></title>
    <?php
    include('../includes/heads.php');
    ?>
    <link rel="stylesheet" type="text/css" href="styles/forum.css" />
    <link rel="stylesheet" type="text/css" href="styles/profil.css" />
    <style type="text/css">
        .fMessage .mBody {
            margin: 0.5em;
            padding-bottom: 0;
            max-height: none;
        }

        .mBody h2 {
            text-align: center;
            margin: 0.2em auto;
        }
        .mBody h2 strong {
            color: #f83;
        }

        .mDescriptionMain {
            font-size: 1.3em;
            text-align: justify;
        }
        .mDescriptionMain img {
            height: 1.1em;
            position: relative;
            top: 0.1em;
        }

        .mDescriptionHeader {
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            flex-wrap: wrap;
        }
        .mDescriptionHeader div {
            flex: 1;
            margin-left: 5px;
            margin-right: 5px;
        }

        .mDescriptionConsoles {
            margin-top: 1em;
            margin-bottom: 1em;
        }

        @media screen and (max-width: 599px) {
            .mDescriptionConsoles {
                margin-bottom: -0.5em;
            }
            .mDescriptionConsoles > a {
                margin: 1em 0;
            }
        }
        @media screen and (min-width: 600px) {
            .mDescriptionConsoles {
                display: grid;
                grid-auto-columns: 1fr;
                grid-auto-flow: column;
                grid-gap: 1em;
            }
        }
        @font-face {
            font-family: 'Monofonto';
            src: url('styles/monofonto.ttf');
        }

        .mDescriptionConsoles > a {
            background-color: #e5ad1b;
            color: #820;
            text-decoration: none;
            font-size: 1.5em;
            padding: 0.5em;
            border-radius: 0.5em;
            font-weight: bold;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-evenly;
        }

        .mDescriptionConsoles > a:hover {
            background-color: #c49b2e;
        }
        .mDescriptionConsoles > a:hover .mDescriptionConsoleHeader img {
            opacity: 0.8;
        }
        .mDescriptionConsoleHeader {
            display: grid;
            grid-auto-columns: 1fr;
            grid-auto-flow: column;
            grid-gap: 0.25em;
        }
        .mDescriptionConsoleHeader img {
            max-width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
        .mDescriptionConsoleHeader.mDescriptionConsoleHeader-2 img {
            max-width: 100%;
        }
        .mDescriptionConsoleLabel {
            margin-top: 0.2em;
            font-family: 'Monofonto', monospace;
            font-size: 1.4em;
        }
        @media screen and (max-width: 599px) {
            .mDescriptionConsoleLabel small {
                font-size: 0.8em;
            }
        }
        @media screen and (min-width: 600px) {
            .mDescriptionConsoleLabel small {
                font-size: 0.7em;
                display: block;
            }
        }

        .mTeamsTable {
            text-align: center;
            margin-left: auto;
            margin-right: auto;
        }
        .mTeamsCaption {
            color: #e4e4e4;
            background-color: #038;
            border: solid 1px #e4e4e4;
            max-width: 480px;
            padding: 3px 0;
            margin: 0 auto;
            font-family: 'Monofonto', monospace;
            font-size: 1.8em;
            margin-top: 0.5em;
        }
        .mTeamsHd {
            max-width: 470px;
            margin: 0.5em auto 0 auto;
            border: solid 1px rgba(142,99,15, 0.4);
            padding: 4px 6px;
            background-color: #FFE30C;
            background-color: rgba(220,204,78, 0.6);
            color: #820;
        }
        .mTeamsTr {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .mTeamsTd {
            color: #268;
            border: solid 1px #268;
            background-color: #f7f7f7;
            margin-top: 0.5em;
        }
        @media screen and (max-width: 649px) {
            .mTeamsTd {
                width: 100%;
            }
        }
        @media screen and (min-width: 650px) {
            .mTeamsTd {
                width: 230px;
            }
            .mTeamsTd:not(:first-child) {
                border-left: 0;
            }
        }
        .mTeamsTf {
            padding: 0.4em 0;
        }
        .mTeamsTh {
            background-color: #e4e4e4;
            font-size: 1.3em;
            padding: 0.1em 0;
            font-weight: bold;
            border-bottom: solid 1px #268;
        }
        .mTeamsTd label {
            display: block;
            text-align: left;
            margin-left: 10px;
            margin-right: 10px;
        }
        .mTeamsTf label.eliminated {
            text-decoration: line-through;
            opacity: 0.6;
        }
        .mTeamsTf label.winner {
            font-weight: bold;
            color : #E80;
        }
        .mTeamsTd img {
            height: 1em;
        }
        .mTeamsTd input {
            width: 1.3em;
            height: 1.3em;
            margin-right: 0.5em;
        }
        .mTeamsVote {
            margin-top: 0.5em;
        }
        .mTeamsVote button {
            width: 100%;
            max-width: 400px;
            cursor: pointer;
            background-color: #cfc;
            color: #041;
            border-color: #080;
            font-size: 1.3em;
            font-weight: bold;
            padding: 0.1em 0.5em;
            display: none;
        }
        .mTeamsVote button:hover {
            background-color: #dfc;
            border-color: #0a0;
        }
        .mVotesList {
            color: #f83;
            font-size: 1.2em;
            text-align: center;
        }
        .mBracket {
            color: #f83;
            font-size: 1.2em;
            text-align: center;
            margin-top: 0.25em;
        }
        #mVotesList {
            display: none;
            margin-top: 0.5em;
        }
        #mVotesList.mVotesListShow {
            display: block;
        }
        #mVotesList > div {
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 0.2em;
            margin-bottom: 0.2em;
        }
        #mBracket {
            display: none;
            margin-top: 0.5em;
            text-align: center;
        }
        #mBracket.mBracketShow {
            display: block;
        }
        .mVotesName {
            display: flex;
            align-items: center;
            width: 9em;
            text-overflow: ellipsis;
        }
        .mVotesName > img {
            margin-right: 0.4em;
            height: 1em;
        }
        .mVotesName > span {
            display: inline-block;
            text-align: left;
            white-space: nowrap;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .mVotesBar {
            display: flex;
            margin-left: 0.4em;
            margin-right: 0.3em;
            border-radius: 5px;
            border: solid 1px #820;
            width: 7em;
            height: 1em;
            overflow: hidden;
        }
        @media screen and (max-width: 599px) {
            .mVotesBar {
                width: 5em;
            }
        }
        .mVotesBar > div {
            display: inline-block;
            margin-left: auto;
            margin-right: auto;
            height: 100%;
        }
        .mVotesBar > div:first-child {
            background-color: #f1b341;
        }
        .mVotesBar > div:last-child {
            background-color: #ffe76f;
        }
        .vote-success {
            background-color: #dfc;
            color: #041;
            border: solid 1px #080;
            max-width: 300px;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
            padding: 5px;
        }
        .vote-success a {
            color: #066;
        }
        .vote-success a:hover {
            color: #060;
        }
        .vote-error {
            background-color: #fdc;
            color: #410;
            border: solid 1px #800;
            max-width: 300px;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
            padding: 5px;
        }
    </style>
    <script type="text/javascript">
    var teamsDict = <?php echo json_encode($teamsDict); ?>;
    function toggleOtherVotes() {
        var $mVotesList = document.getElementById("mVotesList");
        if ($mVotesList.classList.contains("mVotesListShow"))
            $mVotesList.classList.remove("mVotesListShow");
        else
            $mVotesList.classList.add("mVotesListShow");
    }
    function toggleBracket() {
        var $mVotesList = document.getElementById("mBracket");
        if ($mVotesList.classList.contains("mBracketShow"))
            $mVotesList.classList.remove("mBracketShow");
        else
            $mVotesList.classList.add("mBracketShow");
    }
    function showOtherVotes() {
        var $mVotesList = document.getElementById("mVotesList");
        $mVotesList.classList.add("mVotesListShow");
    }
    function handleTeamSelect() {
        var $submit = document.querySelector(".mTeamsVote button");
        if (!$submit.style.display) {
            $submit.style.display = "inline-block";
            setTimeout(function() {
                $submit.focus();
            }, 200);
        }
    }
    function handleSubmit(e) {
        e.preventDefault();
        var $form = e.target;
        var $input = $form.elements["vote"];
        o_confirm(o_language ? "Confirm your bet for <strong>" + teamsDict[$input.value] +"</strong> ?<br />Warning, you won't be allowed to change it later":"Confirmer la sélection de <strong>" + teamsDict[$input.value] +"</strong> ?<br />Attention, vous ne pourrez pas changer votre pari", function(valided) {
            if (valided) {
                $form.submit();
            }
        });
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
        <h1><?php echo $language ? 'MKWC - Place your bets!' : 'MKWC - Faites vos paris !'; ?></h1>
        <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
        <!-- Forum MKPC -->
        <p class="pub"><ins class="adsbygoogle" style="display:inline-block;width:728px;height:90px" data-ad-client="ca-pub-1340724283777764" data-ad-slot="4919860724"></ins></p>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
        <?php
        if (isset($success)) {
            echo '<p class="vote-success">'.$success.'</p>';
        }
        elseif (isset($error)) {
            echo '<p class="vote-error">'.$error.'</p>';
        }
        ?>
        <div id="fMessages">
            <div class="fMessage" data-msg="1">
                <div class="mContent">
                    <div class="mBody">
                        <?php
                        if ($console) {
                            ?>
                            <div class="mDescriptionMain">
                                <?php
                                if ($language) {
                                    ?>
                                    Welcome to the <?php echo $year; ?> Mario Kart World Cup's predictor page!!!<br />
                                    Here, you can predict the team you think will win the World Cup.<br />
                                    In case of a correct prediction, you will earn a unique role on the forum!!!
                                    <img src="images/forum/reactions/laugh.png" alt="laugh" />
                                    <?php
                                }
                                else {
                                    ?>
                                    Bienvenue sur la page de pronostic de la Coupe Du Monde de Mario Kart <?php echo $year; ?> !!!<br />
                                    Ici, vous pourrez-voter pour l'équipe que vous allez pronostiquer comme vainqueur de la Coupe Du Monde !<br />
                                    En cas de pronostic correct, vous gagnerez un rôle inédit sur le forum !!!
                                    <img src="images/forum/reactions/laugh.png" alt="laugh" />
                                    <?php
                                }
                                ?>
                                <br />&nbsp;
                            </div>
                            <div class="mDescriptionHeader">
                                <?php
                                $logos = array(
                                    array(
                                        "src" => "images/mkwc/logo-mkwc-$year.png",
                                        "alt" => "MKWC"
                                    ),
                                    array(
                                        "src" => "images/mkwc/header-$console.png",
                                        "alt" => $consoleName
                                    ),
                                    array(
                                        //"src" => "images/mkwc/logo-$console-$year.png",
                                        "src" => "images/mkwc/logo-mkwc-$year.png",
                                        "alt" => $console
                                    )
                                );
                                foreach ($logos as $logo) {
                                    list($w,$h) = getimagesize('../../'.$logo['src']);
                                    echo '<div style="flex: '. ($w/$h) .'"><img src="'. $logo['src'] .'" alt="'. $logo['alt'] .'" /></div>';
                                }
                                ?>
                            </div>
                            <h2 id="mVotesTitle">
                                <?php
                                if ($myVote)
                                    echo '<img src="images/mkwc/flags/'.$myVote.'.png" alt="'. $myVote .'" /> ' . ($language ? 'You have selected <strong>'. $teamsDict[$myVote] .'</strong> team' : 'Vous avez sélectionné l\'équipe de <strong>'. $teamsDict[$myVote] .'</strong>');
                                elseif ($isPollClosed)
                                    echo ($language ? 'The poll is closed. See you at the end of the tournament!' : 'Le sondage est fermé. Rendez-vous à la fin du tournoi !');
                                else
                                    echo $language ? 'Select your team within the list below' : 'Sélectionnez votre équipe dans la liste';
                                ?>
                            </h2>
                            <?php
                            if ($myVote || $isPollClosed) {
                                ?>
                                <div class="mVotesList">+ <a href="javascript:toggleOtherVotes()"><?php echo $language ? 'See other members\' bets' : 'Voir les paris des autres membres'; ?></a></div>
                                <div id="mVotesList">
                                <?php
                                $getVotesByTeam = mysql_query('SELECT vote,COUNT(*) AS nb FROM mkwcbets WHERE console="'. $console .'" GROUP BY vote ORDER BY nb DESC');
                                $votesByTeam = array();
                                $totalVotes = 0;
                                while ($teamVote = mysql_fetch_array($getVotesByTeam)) {
                                    $votesByTeam[] = $teamVote;
                                    $totalVotes += $teamVote['nb'];
                                }
                                foreach ($votesByTeam as $teamVote) {
                                    echo '<div>';
                                    echo '<div class="mVotesName">';
                                    echo '<img src="images/mkwc/flags/'.$teamVote['vote'].'.png" alt="'. $teamVote['vote'] .'" /> ';
                                    echo '<span>'. $teamsDict[$teamVote['vote']] .'</span>';
                                    echo '</div>';
                                    echo '<div class="mVotesBar">';
                                    $w = 100;
                                    echo '<div style="flex:'. (round($w*$teamVote['nb']/$totalVotes)) .'"></div>';
                                    echo '<div style="flex:'. ($w-round($w*$teamVote['nb']/$totalVotes)) .'"></div>';
                                    echo '</div>';
                                    echo '<div class="mVotesCount">'. $teamVote['nb'].' ('.(round(100*$teamVote['nb']/$totalVotes)).'%)</div>';
                                    echo '</div>';
                                }
                                ?>
                                </div>
                                <?php
                                /*<div class="mBracket">+ <a href="javascript:toggleBracket()"><?php echo $language ? 'See tournament bracket' : 'Voir le tableau des qualifications'; ?></a></div>
                                <div id="mBracket">
                                    <img src="https://cdn.discordapp.com/attachments/309729458925993985/1005467247210418205/unknown.png" alt="Bracket" />
                                </div>*/
                            }
                            ?>
                            <form method="post" class="mTeamsTable" onsubmit="handleSubmit(event)">
                                <?php
                                foreach ($teams as $title => $groups) {
                                    $groupNames = array_keys($groups);
                                    $nbGroups = count($groupNames);
                                    for ($i=0;$i<$nbGroups;$i+=2) {
                                        $name1 = $groupNames[$i];
                                        $name2 = isset($groupNames[$i+1]) ? $groupNames[$i+1] : null;
                                        $group1 = $groups[$name1];
                                        $group2 = $name2 ? $groups[$name2] : null;
                                        if (!$i) {
                                            echo '<div class="mTeamsCaption">';
                                                echo $title;
                                            echo '</div>';
                                        }
                                        $group12 = array($group1,$group2);
                                        $name12 = array($name1,$name2);
                                        $groupHeader = array();
                                        foreach ($group12 as $j=>$group) {
                                            if ($group === null) break;
                                            if (isset($group['header']))
                                                $groupHeader[] = nl2br($group['header']);
                                            if (isset($group['url']))
                                                $groupHeader[] = ($language ? 'For more information, <a href="'.$group['url'].'" target="_blank">go here</a>' : 'Pour plus d\'informations, <a href="'.$group['url'].'" target="_blank">cliquez ici</a>');
                                        }
                                        if (!empty($groupHeader)) {
                                            echo '<div class="mTeamsHd">';
                                            echo implode('<br />', $groupHeader);
                                            echo '</div>';
                                        }
                                        echo '<div class="mTeamsTr">';
                                        foreach ($group12 as $j=>$group) {
                                            if ($group === null) break;
                                            echo '<div class="mTeamsTd">';
                                            echo '<div class="mTeamsTh">'. $name12[$j] .'</div>';
                                            echo '<div class="mTeamsTf">';
                                            foreach ($group['list'] as $code => $country) {
                                                $src = $code;
                                                $isNormalTeam = isNormalTeam($code);
                                                if (!$isNormalTeam)
                                                    $src = 'pin';
                                                $eliminated = isset($group['eliminated']) && in_array($code, $group['eliminated']);
                                                $winner = !$eliminated && isset($group['winner']) && in_array($code, $group['winner']);
                                                echo '<label'. ($eliminated ? ' class="eliminated"':'') . ($winner ? ' class="winner"':'') .'>';
                                                    echo '<input type="radio"'. (($myVote || !$isNormalTeam || $isPollClosed) ? ' disabled="disabled"':'') .' name="vote"'. (($myVote===$code) ? ' checked="checked"':'') .' onclick="handleTeamSelect()" value="'.$code.'" />';
                                                    echo '<img src="images/mkwc/flags/'.$src.'.png" alt="'. $src .'" />';
                                                    if ($winner)
                                                        echo '<small> &nbsp;</small><img src="images/cups/cup1.png" alt="Winner" />';
                                                    echo ' '. htmlspecialchars($country);
                                                echo '</label>';
                                            }
                                            echo '</div>';
                                            echo '</div>';
                                        }
                                        echo '</div>';
                                    }
                                }
                                ?>
                                <?php
                                if (!$myVote) {
                                    ?>
                                    <div class="mTeamsVote">
                                        <button><?php echo $language ? 'Validate my bet' : 'Valider mon pari'; ?></button>
                                    </div>
                                    <?php
                                }
                                ?>
                            </form>
                            <?php
                        }
                        else {
                            ?>
                        <div class="mDescriptionMain">
                            <?php
                            if ($language) {
                                ?>
                                Welcome to the 2023 Mario Kart World Cup's predictor page!!!<br />
                                Here, you can predict a total of 3 teams (1 for each game), to win the World Cup.<br />
                                In case of a correct prediction, you will earn an unique role on the forum!!!
                                <img src="images/forum/reactions/laugh.png" alt="laugh" />
                                <?php
                            }
                            else {
                                ?>
                                Bienvenue sur la page de pronostic de la Coupe Du Monde de Mario Kart 2023 !!!<br />
                                Ici, vous pourrez-voter pour un total de 3 équipes (1 par jeu) que vous aller pronostiquer comme vainqueur de la Coupe Du Monde!<br />
                                En cas de pronostic correct, vous gagnerez un rôle inédit sur le forum !!!
                                <img src="images/forum/reactions/laugh.png" alt="laugh" />
                                <?php
                            }
                            ?>
                        </div>
                        <div class="mDescriptionConsoles">
                            <a href="?console=mkw">
                                <div class="mDescriptionConsoleHeader">
                                    <img src="images/mkwc/header-mkw.png" alt="Mario Kart Wii" />
                                </div>
                                <div class="mDescriptionConsoleLabel">
                                    Mario Kart Wii
                                </div>
                            </a>
                            <a href="?console=mk8d">
                                <div class="mDescriptionConsoleHeader">
                                    <img src="images/mkwc/header-mk8d.png" alt="Mario Kart 8" />
                                </div>
                                <div class="mDescriptionConsoleLabel">
                                    Mario Kart 8 Deluxe
                                </div>
                            </a>
                            <a href="?console=mkt">
                                <div class="mDescriptionConsoleHeader">
                                    <img src="images/mkwc/header-mkt.png" alt="Mario Kart Tour" />
                                </div>
                                <div class="mDescriptionConsoleLabel">
                                    Mario Kart Tour
                                </div>
                            </a>
                        </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <p class="forumButtons">
            <?php
            if (isset($console))
                echo '<a href="mkwc.php">'. ($language ? 'Back to tournaments list':'Retour à la liste des tournois') .'</a><br />';
            ?>
            <a href="news.php?id=14697"><?php echo $language ? 'Back to MKWC news':'Retour à la news MKWC'; ?></a><br />
            <a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour à Mario Kart PC'; ?></a>
        </p>
    </main>
    <?php
    include('../includes/footer.php');
    ?>
</body>
</html>
