<?php
if (isset($_GET['map'])) {
    include('../includes/language.php');
    include_once('circuitNames.php');
    include('../includes/initdb.php');
    require_once('../includes/utils-date.php');
    require_once('../includes/persos.php');
    function getSpriteSrc($playerName) {
        if (substr($playerName, 0,3) == 'cp-')
            return PERSOS_DIR . $playerName . ".png";
        return "images/sprites/sprite_" . $playerName . ".png";
    }
    if (isset($_GET['type']) && in_array($_GET['type'], array('circuits', 'mkcircuits'))) {
        $type = $_GET['type'];
		require_once('../includes/utils-cups.php');
        if ($circuit = fetchCreationData($type, $_GET['map']))
            $circuitName = $circuit['name'];
        if (!$circuitName)
            $circuitName = $language ? 'Untitled' : 'Sans titre';
    }
    else {
        $type = '';
        $circuitName = $circuitNames[$_GET['map']-1];
    }
    $cc = isset($_GET['cc']) ? $_GET['cc'] : 150;
    ?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>"> 
    <head> 
        <title><?php echo $language ? 'World record history of '.htmlspecialchars($circuitName):'Historique record CLM de '.htmlspecialchars($circuitName); ?></title> 
        <meta charset="utf-8" /> 
        <link rel="stylesheet" type="text/css" href="styles/classement.css" />
        <style type="text/css">
        body {
            background-color: #55B8FF;
            background-image: url('../images/fond_mountains.jpg');
            background-size: cover;
            background-attachment: fixed;
            background-position: center bottom;
            background-repeat: no-repeat;
            margin: auto 0;
        }
        main {
            width: 88%;
            border-radius: 5px;
            padding: 10px;
            margin-left: auto;
            margin-right: auto;
            background-color: white;
            background-color: rgba(255,255,255, 0.9);
            min-height: 95vh;
        }
        h1 {
            margin: 0;
            color: #560000;
            text-decoration: underline;
        }
        h2 {
            margin-top: 5px;
            margin-bottom: 10px;
        }
        main table {
            background-color: #FC0;
        }
        main tr.result:nth-child(2n), main tr.result:nth-child(2n) a {
            color: #820;
        }
        main tr.result:nth-child(2n+1) {
            background-color: yellow;
        }
        main tr.result:nth-child(2n+1), main tr.result:nth-child(2n+1) a {
            color: #F60;
        }
        main tr.result:nth-child(2n) a:hover {
            color: #B50;
        }
        main tr.result:nth-child(2n+1) a:hover {
            color: #FA0;
        }
        main table div {
            margin-left: auto;
            margin-right: auto;
        }
        main table .result td {
            width: auto;
        }
        main .pager {
            font-size: 1.3em;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1em;
        }
        main .pager a {
            text-decoration: none;
        }
        </style>
    </head> 
    <body>
        <main>
            <h1><?php echo htmlspecialchars($circuitName) ?></h1>
            <h2><?php echo $language ? 'World record history':'Historique record circuit'; ?></h2>
            <table>
                <tr id="titres">
                    <td style="width:100px"><?php echo $language ? 'Nick':'Pseudo'; ?></td>
                    <td style="width:135px">Date</td>
                    <td style="width:40px"><?php echo $language ? 'Char.':'Perso'; ?></td>
                    <td><?php echo $language ? 'Time':'Temps'; ?></td>
                </tr>
                <?php
                $date = isset($_GET['date']) ? $_GET['date'] : '9999-12-31 23:59:59';
                for ($i=0;$i<20;$i++) {
                    $record = mysql_fetch_array(mysql_query('SELECT r.date,r.perso,r.time,r.name,r.player,c.code FROM mkrecords r LEFT JOIN `mkprofiles` p ON r.player=p.id LEFT JOIN `mkcountries` c ON p.country=c.id WHERE r.class="'.$cc.'" AND r.type="'.$type.'" AND r.circuit="'. $_GET['map'] .'" AND r.date<"'. $date .'" ORDER BY r.time LIMIT 1'));
                    if (!$record)
                        break;
                    ?>
                    <tr class="result">
                        <td><?php
                            if ($record['player']) {
                                echo '<a href="profil.php?id='. $record['player'] .'" target="_blank" class="recorder">';
                                if ($record['code'])
                                    echo '<img src="images/flags/'.$record['code'].'.png" alt="'.$record['code'].'" onerror="this.style.display=\'none\'" /> ';
                                echo htmlspecialchars($record['name']);
                                echo '</a>';
                            }
                            else
                                echo htmlspecialchars($record['name']);
                        ?></a></td>
                        <td><?php echo to_local_tz($record['date'], $language ? 'Y-m-d H:i':'d/m/Y H:i'); ?></td>
                        <td><div><img src="<?php echo getSpriteSrc($record['perso']); ?>" alt="<?php echo $record['perso']; ?>"></div></td>
                        <td><?php
                        $getTime = $record['time'];
                        $sec = floor($getTime/1000);
                        $mls = round($getTime-$sec*1000);
                        $min = floor($sec/60);
                        $sec -= $min*60;
                        if ($sec < 10)
                            $sec = '0'.$sec;
                        if ($mls < 10)
                            $mls = '00'.$mls;
                        else if ($mls < 100)
                            $mls = '0'.$mls;
                        echo $min.':'.$sec.':'.$mls;
                        ?></td>
                    </tr>
                    <?php
                    $date = $record['date'];
                }
                ?>
            </table>
                <p class="pager">
                    <?php
                    $get = $_GET;
                    if (isset($_GET['date'])) {
                        unset($get['date']);
                        ?>
                        <a href="?<?php echo http_build_query($get) ?>"><?php echo $language ? '&lt; Back':'&lt; Retour'; ?></a>
                        <?php
                    }
                    if ($record) {
                        $get['date'] = $date;
                        ?>
                        <a href="?<?php echo http_build_query($get) ?>"><?php echo $language ? 'Next results &gt;':'Page suivante &gt;'; ?></a>
                        <?php
                    }
                    ?>
                </p>
        </main>
    </body> 
</html>
    <?php
}
?>