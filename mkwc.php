<?php
include('getId.php');
include('language.php');
include('session.php');
include('initdb.php');
$console = isset($_GET['console']) ? $_GET['console'] : null;
$playInStage = $language ? 'Play-In Stage':'Tour Préliminaire';
$groupStage = $language ? 'Group Stage':'Phase de Groupe';
$playIn = $language ? 'Play-In':'Qualifications';
$group = $language ? 'Group':'Groupe';
switch ($console) {
case 'mkw':
    $consoleName = 'Mario Kart Wii';
    $teams = array(
        $playInStage => array(
            "$group I" => array(
                'header' => $language ? "Luso Alliance: Brazil + Portugal + other lusophonic countries.\nAfrica: all of Africa.":"Luso Alliance: Brésil + Portugal + autres pays lusophones.\nAfrique: toute l'Afrique.",
                'list' => array(
                    'spa'=> $language ? 'Spain':'Espagne',
                    'sco'=> $language ? 'Scotland':'Écosse',
                    'gre'=> $language ? 'Greece':'Grèce',
                    'lua'=> $language ? 'Luso Alliance':'Luso Alliance',
                    'fin'=> $language ? 'Finland':'Finlande',
                    'afr'=> $language ? 'Africa':'Afrique'
                )
            ),
            "$group II" => array(
                'header' => $language ? "Asia: all of Asia, outside of Japan.\nEastern Europe: all of eastern Europe, outside of Greece.":"Asie: toute l'Asie, sauf le Japon.\nEurope de l'Est: toute l'Europe de l'est, sauf la Grèce.",
                'list' => array(
                    'ire'=> $language ? 'Ireland':'Irelande',
                    'net'=> $language ? 'Netherlands':'Pays-Bas',
                    'asi'=> $language ? 'Asia':'Asie',
                    'eue'=> $language ? 'Eastern Europe':'Europe de l\'Est',
                    'swi'=> $language ? 'Switzerland':'Suisse'
                )
            )
        ),
        $groupStage => array(
            "$group A" => array(
                'list' => array(
                    'uss'=> $language ? 'United States South':'États-Unis du Sud',
                    'aus'=> $language ? 'Australia':'Australie',
                    'ita'=> $language ? 'Italy':'Italie',
                    'pin' => $playIn
                )
            ),
            "$group B" => array(
                'header' => $language ? "Latin America: all of Latin America":"Amérique Latine: toute l'Amérique Latine",
                'list' => array(
                    'usn'=> $language ? 'United States North':'États-Unis du Nord',
                    'lta'=> $language ? 'Latin America':'Amerique Latine',
                    'den'=> $language ? 'Denmark':'Danemark',
                    'pin' => $playIn
                )
            ),
            "$group C" => array(
                'list' => array(
                    'eng'=> $language ? 'England':'Angleterre',
                    'nor'=> $language ? 'Norway':'Norvège',
                    'can'=> $language ? 'Canada':'Canada',
                    'pin' => $playIn
                )
            ),
            "$group D" => array(
                'list' => array(
                    'jap'=> $language ? 'Japan':'Japon',
                    'ger'=> $language ? 'Germany':'Allemagne',
                    'fra'=> $language ? 'France':'France',
                    'pin' => $playIn
                )
            )
        )
    );
    break;
case 'mkt':
    $consoleName = 'Mario Kart Tour';
    $teams = array(
        $playInStage => array(
            "$group VII" => array(
                'list' => array(
                    'cri'=> $language ? 'Costa Rica':'Costa Rica',
                    'hon'=> $language ? 'Honduras':'Honduras',
                    'pan'=> $language ? 'Panama':'Panama',
                    'gua'=> $language ? 'Guatemala':'Guatemala'
                )
            ),
            "$group VIII" => array(
                'list' => array(
                    'ecu'=> $language ? 'Ecuador':'Équateur',
                    'ita'=> $language ? 'Italy':'Italie',
                    'hok'=> $language ? 'Hong Kong':'Hong Kong',
                    'aus'=> $language ? 'Australia':'Australia'
                )
            ),
            "$group IX" => array(
                'list' => array(
                    'spa'=> $language ? 'Spain':'Espane',
                    'bol'=> $language ? 'Bolivia':'Bolivie',
                    'ger'=> $language ? 'Germany':'Allemagne',
                    'sal'=> $language ? 'El Salvador':'Salvador'
                )
            ),
            "$group X" => array(
                'list' => array(
                    'bra'=> $language ? 'Brazil':'Brésil',
                    'swi'=> $language ? 'Switzerland':'Suisse',
                    'nic'=> $language ? 'Nicaragua':'Nicaragua',
                    'ven'=> $language ? 'Venezuela':'Venezuela'
                )
            )
        ),
        $groupStage => array(
            "$group J" => array(
                'list' => array(
                    'jap'=> $language ? 'Japan':'Japon',
                    'per'=> $language ? 'Peru':'Perou',
                    'pin' => $playIn,
                    'pin' => $playIn
                )
            ),
            "$group K" => array(
                'list' => array(
                    'mex'=> $language ? 'Mexico':'Mexique',
                    'chi'=> $language ? 'Chile':'Chili',
                    'pin' => $playIn,
                    'pin' => $playIn
                )
            ),
            "$group L" => array(
                'list' => array(
                    'fra'=> $language ? 'France':'France',
                    'col'=> $language ? 'Colombia':'Colombie',
                    'pin' => $playIn,
                    'pin' => $playIn
                )
            ),
            "$group M" => array(
                'list' => array(
                    'usa'=> $language ? 'United States':'États-Unis',
                    'ukg'=> $language ? 'United Kingdom':'Royaume-Uni',
                    'pin' => $playIn,
                    'pin' => $playIn
                )
            )
        )
    );
    break;
case 'mk8d':
    $consoleName = 'Mario Kart 8 Deluxe';
    $teams = array(
        $playInStage => array(
            "$group III" => array(
                'header' => $language ? "Nordic: Greenland, Iceland, Denmark, Norway, Sweden, Finland and nordic possessions.":"Nordique: Groenland, Islande, Danemark, Norvège, Suède, Finlande et territoires nordiques.",
                'list' => array(
                    'ita'=> $language ? 'Italy':'Italie',
                    'col'=> $language ? 'Colombia':'Colombie',
                    'ire'=> $language ? 'Ireland':'Irelande',
                    'nrd'=> $language ? 'Nordic':'Nordique',
                    'ecu'=> $language ? 'Ecuador':'Équateur'
                )
            ),
            "$group IV" => array(
                'header' => $language ? "Centroamerica: Belize, Salvador, Nicaragua and Panama.\nHong Kong & Taïwan: Hong Kong & Taiwan.":"Amérique Centrale: Belize, Salvador, Nicaragua et Panama.\nHong Kong & Taïwan: Hong Kong & Taïwan.",
                'list' => array(
                    'swi'=> $language ? 'Switzerland':'Suisse',
                    'cta'=> $language ? 'Centroamerica':'Amérique centrale',
                    'hkt'=> $language ? 'Hong Kong & Taiwan':'Hong Kong & Taiwan',
                    'per'=> $language ? 'Peru':'Perou',
                    'lux'=> $language ? 'Luxembourg':'Luxembourg',
                    'sco'=> $language ? 'Scotland':'Écosse'
                )
            ),
            "$group V" => array(
                'header' => $language ? "Rio de la Plata: Argentina & Uruguay.":"Rio de la Plata: Argentine & Uruguay.",
                'list' => array(
                    'aus'=> $language ? 'Australia':'Australie',
                    'bra'=> $language ? 'Brazil':'Brésil',
                    'sko'=> $language ? 'South Korea':'Corée du Sud',
                    'cri'=> $language ? 'Costa Rica':'Costa Rica',
                    'rip'=> $language ? 'Rio de la Plata':'Rio de la Plata'
                )
            ),
            "$group VI" => array(
                'header' => $language ? "Eastern Europe: all of eastern Europe.":"Europe de l'Est: toute l'Europe de l'est.",
                'list' => array(
                    'aut'=> $language ? 'Austria':'Autriche',
                    'hon'=> $language ? 'Honduras':'Honduras',
                    'eue'=> $language ? 'Eastern Europe':'Europe de l\'Est',
                    'gua'=> $language ? 'Guatemala':'Guatemala',
                    'bol'=> $language ? 'Bolivia':'Bolivie',
                    'wal'=> $language ? 'Wales':'Pays de Galles'
                )
            )
        ),
        $groupStage => array(
            "$group E" => array(
                'list' => array(
                    'jap'=> $language ? 'Japan':'Japon',
                    'ger'=> $language ? 'Germany':'Allemagne',
                    'chn'=> $language ? 'China':'Chine',
                    'pin' => $playIn,
                    'pin' => $playIn
                )
            ),
            "$group F" => array(
                'list' => array(
                    'fra'=> $language ? 'France':'France',
                    'mex'=> $language ? 'Mexico':'Mexique',
                    'bel'=> $language ? 'Belgium':'Belgique',
                    'pin' => $playIn,
                    'pin' => $playIn
                )
            ),
            "$group G" => array(
                'list' => array(
                    'usa'=> $language ? 'United States':'États-Unis',
                    'can'=> $language ? 'Canada':'Canada',
                    'net'=> $language ? 'Netherlands':'Pays-Bas',
                    'pin' => $playIn,
                    'pin' => $playIn
                )
            ),
            "$group M" => array(
                'list' => array(
                    'eng'=> $language ? 'England':'Angleterre',
                    'spa'=> $language ? 'Spain':'Espagne',
                    'chi'=> $language ? 'Chile':'Chili',
                    'pin' => $playIn,
                    'pin' => $playIn
                )
            )
        )
    );
    break;
default:
    $console = null;
}
if (isset($teams)) {
    $teamsDict = array();
    foreach ($teams as $groups) {
        foreach ($groups as $group) {
            foreach ($group['list'] as $code => $country) {
                if ($code !== 'pin')
                    $teamsDict[$code] = htmlspecialchars($country);
            }
        }
    }

}
if ($console && isset($_POST['vote'])) {
    $vote = $_POST['vote'];
    if (isset($teamsDict[$vote])) {
        if ($id) {
            $success = $language ? 'Your bet has been saved' : 'Votre pari a été enregistré';
            $success .= '<br />';
            $success .= '<a href="#mVotesTitle" onclick="showOtherVotes()">'. ($language ? 'See other members\' bets':'Voir les paris des autres membres') .'</a>';
            $success .= '<br />';
            $success .= '<a href="mkwc.php">'. ($language ? 'Back to tournaments list':'Retour &agrave; la liste des tournois') .'</a>';
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
    <title>Bet for your MKWC team! - MKPC</title>
    <?php
    include('heads.php');
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
        o_confirm(o_language ? "Confirm your bet for <strong>" + teamsDict[$input.value] +"</strong> ?<br />Warning, you won't be allowed to change it later":"Confirmer le pari de <strong>" + teamsDict[$input.value] +"</strong> ?<br />Attention, vous ne pourrez pas le changer", function(valided) {
            if (valided) {
                $form.submit();
            }
        });
    }
    </script>
    <?php
    include('o_online.php');
    ?>
</head>

<body>
    <?php
    include('header.php');
    $page = 'home';
    include('menu.php');
    ?>
    <main>
        <h1><?php echo $language ? 'MKWC - Place your bets!' : 'MKWC - faites vos paris !'; ?></h1>
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
                            <div class="mDescriptionHeader">
                                <?php
                                $logos = array(
                                    array(
                                        "src" => "images/mkwc/logo-mkwc.png",
                                        "alt" => "MKWC"
                                    ),
                                    array(
                                        "src" => "images/mkwc/header-$console.png",
                                        "alt" => $consoleName
                                    ),
                                    array(
                                        "src" => "images/mkwc/logo-$console.png",
                                        "alt" => $console
                                    )
                                );
                                foreach ($logos as $logo) {
                                    list($w,$h) = getimagesize($logo['src']);
                                    echo '<div style="flex: '. ($w/$h) .'"><img src="'. $logo['src'] .'" alt="'. $logo['alt'] .'" /></div>';
                                }
                                ?>
                            </div>
                            <h2 id="mVotesTitle">
                                <?php
                                if ($myVote)
                                    echo '<img src="images/mkwc/flags/'.$myVote.'.png" alt="'. $myVote .'" /> ' . ($language ? 'You have selected <strong>'. $teamsDict[$myVote] .'</strong> team' : 'Vous avez sélectionné l\'équipe de <strong>'. $teamsDict[$myVote] .'</strong>');
                                else
                                    echo $language ? 'Select your team within the list below' : 'Sélectionnez votre équipe dans la liste';
                                ?>
                            </h2>
                            <?php
                            if ($myVote) {
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
                            }
                            ?>
                            <form method="post" class="mTeamsTable" onsubmit="handleSubmit(event)">
                                <?php
                                foreach ($teams as $title => $groups) {
                                    $groupNames = array_keys($groups);
                                    $nbGroups = count($groupNames);
                                    for ($i=0;$i<$nbGroups;$i+=2) {
                                        $name1 = $groupNames[$i];
                                        $name2 = $groupNames[$i+1];
                                        $group1 = $groups[$name1];
                                        $group2 = $groups[$name2];
                                        if (!$i) {
                                            echo '<div class="mTeamsCaption">';
                                                echo $title;
                                            echo '</div>';
                                        }
                                        $group12 = array($group1,$group2);
                                        $name12 = array($name1,$name2);
                                        $groupHeader = array();
                                        foreach ($group12 as $j=>$group) {
                                            if (isset($group['header']))
                                                $groupHeader[] = nl2br(htmlspecialchars($group['header']));
                                        }
                                        if (!empty($groupHeader)) {
                                            echo '<div class="mTeamsHd">';
                                            echo implode('<br />', $groupHeader);
                                            echo '</div>';
                                        }
                                        echo '<div class="mTeamsTr">';
                                        foreach ($group12 as $j=>$group) {
                                            echo '<div class="mTeamsTd">';
                                            echo '<div class="mTeamsTh">'. $name12[$j] .'</div>';
                                            echo '<div class="mTeamsTf">';
                                            foreach ($group['list'] as $code => $country) {
                                                echo '<label>';
                                                    echo '<input type="radio"'. (($myVote || $code === 'pin') ? ' disabled="disabled"':'') .' name="vote"'. (($myVote===$code) ? ' checked="checked"':'') .' onclick="handleTeamSelect()" value="'.$code.'" />';
                                                    echo '<img src="images/mkwc/flags/'.$code.'.png" alt="'. $code .'" />';
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
                            Welcome to the MKWC bet page!
                            Here you can vote for the teams that you think will win the 2021
                            <a href="https://mariokartworldcup.000webhostapp.com/world_cup.html" target="_blank">Mario Kart world cup</a>.
                            Just select your team for each tournament!<br />(Texte à retravailler, je te laisserai voir ça <img src="images/smileys/smiley4.png" alt=":p" />)<br />
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
                echo '<a href="mkwc.php">'. ($language ? 'Back to tournaments list':'Retour &agrave; la liste des tournois') .'</a><br />';
            ?>
            <a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a>
        </p>
    </main>
    <?php
    include('footer.php');
    ?>
</body>
</html>