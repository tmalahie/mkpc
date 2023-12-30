<?php
if (!empty($_GET['player']) && isset($_GET['map'])) {
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
    $getPlayer = mysql_fetch_array(mysql_query('SELECT nom FROM mkjoueurs WHERE id="'. $_GET['player'] .'"'));
    ?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>"> 
    <head> 
        <title><?php echo $language ? 'Time trial history of '.htmlspecialchars($getPlayer['nom']):'Historique CLM de '.htmlspecialchars($getPlayer['nom']); ?></title> 
        <meta charset="utf-8" /> 
        <link rel="stylesheet" type="text/css" href="styles/classement.css" />
        <style type="text/css">
        body {
            background-color: #E8964C;
            background: url('../images/pages/bg-mountains-320w.jpg'), linear-gradient(0deg, #12021D 0%, #12021D 40%, #E8964C 70%, #AF663D 100%);
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-position: center bottom;
            background-position: center -30vw, center;
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
        </style>
    </head> 
    <body>
        <main>
            <h1><?php echo htmlspecialchars($circuitName) ?></h1>
            <h2><?php echo $language ? 'Time trial history of '.htmlspecialchars($getPlayer['nom']):'Historique CLM de '.htmlspecialchars($getPlayer['nom']); ?></h2>
            <?php
            $getRecords = mysql_query('SELECT date,perso,time FROM mkrecords WHERE class="'.$cc.'" AND type="'. $type .'" AND circuit="'. $_GET['map'] .'" AND player="'. $_GET['player'] .'" ORDER BY date DESC');
            ?>
            <table>
                <tr id="titres">
                    <td style="width:135px">Date</td>
                    <td style="width:40px"><?php echo $language ? 'Char.':'Perso'; ?></td>
                    <td><?php echo $language ? 'Time':'Temps'; ?></td>
                </tr>
                <?php
                while ($record = mysql_fetch_array($getRecords)) {
                    ?>
                    <tr class="result">
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
                }
                ?>
            </table>
        </main>
    </body> 
</html>
    <?php
}
?>