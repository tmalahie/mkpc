<?php
include('getId.php');
include('language.php');
include('session.php');
include('initdb.php');
$console = isset($_GET['console']) ? $_GET['console'] : null;
switch ($console) {
case 'mkw':
    $consoleName = 'Mario Kart Wii';
    $teams = array(
        'Play-ins' => array(
            'A' => array(
                'uss' => 'United States South',
                'aus' => 'Australia',
                'ita' => 'Italy'
            ),
            'B' => array(
                'usn' => 'United States North',
                'lta' => 'Latin America',
                'den' => 'Denmark'
            ),
            'C' => array(
                'eng' => 'England',
                'nor' => 'Norway',
                'can' => 'Canada'
            ),
            'D' => array(
                'jap' => 'Japan',
                'ger' => 'Germany',
                'fra' => 'France'
            )
        ),
        'Group Stage' => array(
            'I' => array(
                'spa' => 'Spain',
                'sco' => 'Scotland',
                'gre' => 'Greece',
                'lua' => 'Luso Alliance',
                'fin' => 'Finland',
                'afr' => 'Africa'
            ),
            'II' => array(
                'ire' => 'Ireland',
                'net' => 'Netherlands',
                'asi' => 'Asia',
                'eue' => 'Eastern Europe',
                'swi' => 'Switzerland'
            )
        )
    );
    break;
case 'mkt':
    $consoleName = 'Mario Kart Tour';
    $teams = array(
        'Play-ins' => array(
            'A' => array(
                'uss' => 'United States South',
                'aus' => 'Australia',
                'ita' => 'Italy'
            ),
            'B' => array(
                'usn' => 'United States North',
                'lta' => 'Latin America',
                'den' => 'Denmark'
            ),
            'C' => array(
                'eng' => 'England',
                'nor' => 'Norway',
                'can' => 'Canada'
            ),
            'D' => array(
                'jap' => 'Japan',
                'ger' => 'Germany',
                'fra' => 'France'
            )
        ),
        'Group Stage' => array(
            'I' => array(
                'spa' => 'Spain',
                'sco' => 'Scotland',
                'gre' => 'Greece',
                'lua' => 'Luso Alliance',
                'fin' => 'Finland',
                'afr' => 'Africa'
            ),
            'II' => array(
                'ire' => 'Ireland',
                'net' => 'Netherlands',
                'asi' => 'Asia',
                'eue' => 'Eastern Europe',
                'swi' => 'Switzerland'
            )
        )
    );
    break;
case 'mk8':
    $consoleName = 'Mario Kart 8';
    $teams = array(
        'Play-ins' => array(
            'A' => array(
                'uss' => 'United States South',
                'aus' => 'Australia',
                'ita' => 'Italy'
            ),
            'B' => array(
                'usn' => 'United States North',
                'lta' => 'Latin America',
                'den' => 'Denmark'
            ),
            'C' => array(
                'eng' => 'England',
                'nor' => 'Norway',
                'can' => 'Canada'
            ),
            'D' => array(
                'jap' => 'Japan',
                'ger' => 'Germany',
                'fra' => 'France'
            )
        ),
        'Group Stage' => array(
            'I' => array(
                'spa' => 'Spain',
                'sco' => 'Scotland',
                'gre' => 'Greece',
                'lua' => 'Luso Alliance',
                'fin' => 'Finland',
                'afr' => 'Africa'
            ),
            'II' => array(
                'ire' => 'Ireland',
                'net' => 'Netherlands',
                'asi' => 'Asia',
                'eue' => 'Eastern Europe',
                'swi' => 'Switzerland'
            )
        )
    );
    break;
default:
    $console = null;
}
if ($console && isset($_POST['vote'])) {
    $vote = $_POST['vote'];
    $isVoteValid = '';
    foreach ($teams as $groups) {
        foreach ($groups as $group) {
            foreach ($group as $code => $country) {
                if ($vote === $code) {
                    $isVoteValid = true;
                    break 3;
                }
            }
        }
    }
    if ($isVoteValid) {
        if ($id) {
            $success = $language ? 'Your vote has been saved' : 'Votre vote a été enregistré';
            $success .= '<br />';
            $success .= '<a href="mkwc.php">'. ($language ? 'Back to tournaments list':'Retour &agrave; la liste des tournois') .'</a>';
            mysql_query('INSERT INTO mkwcbets SET console="'. $console .'",player="'. $id .'",vote="'. $_POST['vote'] .'" ON DUPLICATE KEY UPDATE vote=VALUES(vote)');
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
    <title>Bet for your MKWC team! - Forum MKPC</title>
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
            font-size: 1.5em;
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
    function handleTeamSelect() {
        var $submit = document.querySelector(".mTeamsVote button");
        if (!$submit.style.display) {
            $submit.style.display = "inline-block";
            setTimeout(function() {
                $submit.focus();
            }, 200);
        }
    }
    </script>
    <?php
    include('o_online.php');
    ?>
</head>

<body>
    <?php
    include('header.php');
    $page = 'forum';
    include('menu.php');
    ?>
    <main>
        <h1>Bet for your MKWC teams!</h1>
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
                            <h2>
                                <?php echo $language ? 'Select your team within the list below' : 'Sélectionnez votre équipe dans la liste'; ?>
                            </h2>
                            <form method="post" class="mTeamsTable">
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
                                        echo '<div class="mTeamsTr">';
                                        $group12 = array($group1,$group2);
                                        $name12 = array($name1,$name2);
                                        foreach ($group12 as $j=>$group) {
                                            echo '<div class="mTeamsTd">';
                                            echo '<div class="mTeamsTh">'. $name12[$j] .'</div>';
                                            echo '<div class="mTeamsTf">';
                                            foreach ($group as $code => $country) {
                                                echo '<label>';
                                                    echo '<input type="radio" name="vote"'. (($myVote===$code) ? ' checked="checked"':'') .' onclick="handleTeamSelect()" value="'.$code.'" />';
                                                    echo '<img src="images/mkwc/flags/'.$code.'.png" alt="'. $code .'" />';
                                                    echo ' '. $country;
                                                echo '</label>';
                                            }
                                            echo '</div>';
                                            echo '</div>';
                                        }
                                        echo '</div>';
                                    }
                                }
                                ?>
                                <div class="mTeamsVote">
                                    <button<?php if ($myVote) echo ' style="display:inline-block"'; ?>><?php echo $language ? 'Validate my vote' : 'Valider mon vote'; ?></button>
                                </div>
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
                            <a href="?console=mkt">
                                <div class="mDescriptionConsoleHeader">
                                    <img src="images/mkwc/header-mkt.png" alt="Mario Kart Tour" />
                                </div>
                                <div class="mDescriptionConsoleLabel">
                                    Mario Kart Tour
                                </div>
                            </a>
                            <a href="?console=mk8">
                                <div class="mDescriptionConsoleHeader mDescriptionConsoleHeader-2">
                                    <img src="images/mkwc/header-mk8.png" alt="Mario Kart 8" />
                                    <img src="images/mkwc/header-mk8d.png" alt="Mario Kart 8 Deluxe" />
                                </div>
                                <div class="mDescriptionConsoleLabel">
                                    <small>Mario Kart 8&nbsp;/</small>
                                    <small>Mario Kart 8 Deluxe</small>
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