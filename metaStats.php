<?php
if (isset($_POST['stats']))
    $stats = $_POST['stats'];
include('initdb.php');
include('language.php');
include('getId.php');
$canBeAdmin = in_array($identifiants[0], array(1390635815,2963080980));
$isAdmin = $canBeAdmin && isset($_GET['common']);
if ($isAdmin)
    $identifiants[0] = 0;
require_once('utils-challenges.php');
if (isset($stats)) {
    $statsData = json_decode($stats);
    if ($statsData) {
        function toSQLStat($statData,$key) {
            global $isAdmin;
            if ($isAdmin && !isset($statData->{$key}))
                return '';
            if (property_exists($statData,$key)) {
                $res = ",$key=";
                $nb = $statData->{$key};
                if ($nb === null)
                    $res .= 'NULL';
                else
                    $res .= +$nb;
                return $res;
            }
            return '';
        }
        function toSQLValue($statData,$key) {
            global $isAdmin;
            if ($isAdmin && !isset($statData->{$key}))
                return '';
            if (property_exists($statData,$key))
                return ",$key=VALUES($key)";
            return '';
        }
        foreach ($statsData as $perso => $statData)
            mysql_query('INSERT INTO `mkteststats` SET perso="'.mysql_real_escape_string($perso).'",identifiant='.$identifiants[0].toSQLStat($statData,'acceleration').toSQLStat($statData,'speed').toSQLStat($statData,'handling').toSQLStat($statData,'mass').' ON DUPLICATE KEY UPDATE id=id'.toSQLValue($statData,'acceleration').toSQLValue($statData,'speed').toSQLValue($statData,'handling').toSQLValue($statData,'mass'));
        $statKeys = array('acceleration','speed','handling','mass');
        if ($isAdmin) {
            foreach ($statKeys as $statKey)
                mysql_query("UPDATE mkteststats m INNER JOIN mkteststats g ON m.perso=g.perso AND m.$statKey=g.$statKey SET m.$statKey=NULL WHERE m.identifiant!=0 AND g.identifiant=0");
            mysql_query('DELETE FROM mkteststats WHERE identifiant!=0 AND acceleration IS NULL AND speed IS NULL AND handling IS NULL AND mass IS NULL');
        }
        else {
            foreach ($statKeys as $statKey)
                mysql_query("UPDATE mkteststats m INNER JOIN mkteststats g ON m.perso=g.perso AND m.$statKey=g.$statKey SET m.$statKey=NULL WHERE m.identifiant=".$identifiants[0]." AND g.identifiant=0");
            mysql_query('DELETE FROM mkteststats WHERE identifiant='.$identifiants[0].' AND acceleration IS NULL AND speed IS NULL AND handling IS NULL AND mass IS NULL');
        }
    }
}
$globalStats = new \stdClass();
$myStats = new \stdClass();
$getPersos = mysql_query('SELECT * FROM mkteststats WHERE identifiant IN (0,'.$identifiants[0].') ORDER BY id');
while ($perso = mysql_fetch_array($getPersos)) {
    $stats = array($perso['acceleration'],$perso['speed'],$perso['handling'],$perso['mass']);
    if ($perso['identifiant'])
        $myStats->{$perso['perso']} = $stats;
    else
        $globalStats->{$perso['perso']} = $stats;
}
?>
<!DOCTYPE html>
<html lang="fr"> 
	<head> 
		<title><?php echo $language ? 'Metagame - Edit stats':'Metagame - Modifier les stats'; ?></title> 
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, user-scalable=no" /> 
        <style type="text/css">
        input[type="range"] {
            width: 600px;
            max-width: 80%;
            max-width: calc(98% - 100px);
        }
        .control-range label {
            display: inline-block;
            width: 100px;
        }
        .before {
            color: #F96;
            text-decoration: line-through;
        }
        .after {
            color: #080;
        }
        .diffs a {
            color: #08F;
        }
        h3, h4 {
            margin: 5px 0;
        }
        input[type="submit"] {
            margin-top: 10px;
            padding: 0.1em 1em;
            font-weight: bold;
            font-size: 1em;
            <?php if ($isAdmin) echo 'color: #F60;'; ?>
        }
        </style>
        <script type="text/javascript">
        var globalStats = <?php echo json_encode($globalStats); ?>;
        var myStats = <?php echo json_encode($myStats); ?>;
        var initialStats = JSON.parse(JSON.stringify(myStats));
        var statsKeys = ["acceleration","speed","handling","mass"];
        function selectPerso(perso) {
            for (var i=0;i<statsKeys.length;i++) {
                var key = statsKeys[i];
                if (myStats[perso] && (null!==myStats[perso][i]))
                    document.getElementById(key).value = myStats[perso][i];
                else
                    document.getElementById(key).value = globalStats[perso][i];
                document.getElementById(key+"-value").innerHTML = document.getElementById(key).value;
            }
        }
        function resetStat(elt,perso,statId) {
            myStats[perso][statId] = null;
            if (document.getElementById("perso").value == perso)
                selectPerso(perso);
            elt.parentNode.parentNode.removeChild(elt.parentNode);
        }
        function handleStat(key,value) {
            var statId = statsKeys.indexOf(key);
            var perso = document.getElementById("perso").value;
            if (!myStats[perso]) myStats[perso] = [null,null,null,null];
            if (value == globalStats[perso][statId])
                myStats[perso][statId] = null;
            else
                myStats[perso][statId] = value;
            document.getElementById(key+"-value").innerHTML = value;
        }
        function saveStats() {
            document.forms[0].elements["stats"].value = getSaves();
            document.body.onbeforeunload = undefined;
        }
        function getSaves() {
            var changes = {};
            for (var perso in myStats) {
                var myStat = myStats[perso];
                if (!initialStats[perso])
                    initialStats[perso] = [null,null,null,null];
                var change = {};
                var isChanges = false;
                for (var i=0;i<statsKeys.length;i++) {
                    if (initialStats[perso][i] != myStat[i]) {
                        change[statsKeys[i]] = myStat[i];
                        isChanges = true;
                    }
                }
                if (isChanges)
                    changes[perso] = change;
            }
            return JSON.stringify(changes);
        }
        function checkSave() {
            if (getSaves() != "{}")
                return "Unsaved changes will be lost";
        }
        function handleKey(e) {
            switch (e.keyCode) {
            case 83:
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    saveStats();
                    document.forms[0].submit();
                }
            }
        }
        </script>
	</head>
	<body onload="selectPerso(document.getElementById('perso').value)" onbeforeunload="return checkSave()" onkeydown="handleKey(event)">
    <form method="post">
        <h2><?php
            if ($isAdmin)
                echo '<span style="color:#C00">'. ($language ? 'Metagame - common stats':'Metagame - stats communes') .'</span>';
            else
                echo $language ? 'Metagame - personnal stats':'Metagame - stats persos';
        ?></h2>
        <?php echo $language ? 'Character:':'Perso :'; ?>
        <select id="perso" name="perso" onchange="selectPerso(this.value)">
            <?php
            $getPersos = mysql_query('SELECT perso FROM mkteststats WHERE identifiant=0 ORDER BY id');
            $selectedPerso = isset($_POST['perso']) ? $_POST['perso']:'';
            while ($perso = mysql_fetch_array($getPersos)) {
                echo '<option value="'.$perso['perso'].'"'.($perso['perso']==$selectedPerso ? ' selected="selected"':'').'>'. getCharacterName($perso['perso']) .'</option>';
            }
            ?>
        </select>
        <div class="control-range">
            <label><?php echo $language ? 'Acceleration:':'Accélération:'; ?></label>
            <input type="range" id="acceleration" min="0" max="32" oninput="handleStat(this.id,this.value)" />
            <span id="acceleration-value"></span><br />
            <label><?php echo $language ? 'Speed:':'Vitesse:'; ?></label>
            <input type="range" id="speed" min="0" max="32" oninput="handleStat(this.id,this.value)" />
            <span id="speed-value"></span><br />
            <label><?php echo $language ? 'Handling:':'Maniabilité:'; ?></label>
            <input type="range" id="handling" min="0" max="32" oninput="handleStat(this.id,this.value)" />
            <span id="handling-value"></span><br />
            <label><?php echo $language ? 'Weight:':'Poids:'; ?></label>
            <input type="range" id="mass" min="0" max="32" oninput="handleStat(this.id,this.value)" />
            <span id="mass-value"></span>
        </div>
        <input type="hidden" name="stats" value="{}" />
        <input type="submit" value="<?php echo $language ? 'Validate (Ctrl+S)':'Valider (Ctrl+S)'; ?>" onclick="saveStats()" />
    </form>
    <br />
    <?php
    if ($canBeAdmin) {
        if ($isAdmin)
            echo '<a href="metaStats.php">'. ($language ? 'Back to personal stats':'Retour aux stats persos') .'</a>';
        else
            echo '<a href="?common">'. ($language ? 'Admin common stats':'Admin stats communes') .'</a>';
        echo '<br />';
    }
    ?>
    <a href="mariokart.meta.php<?php echo $isAdmin ? '?common':''; ?>"><?php echo $language ? 'Back to Mario Kart PC':'Retour à Mario Kart PC'; ?></a>
    <?php
    if (!empty((array)$myStats)) {
        ?>
        <br />
        <br />
        <hr />
        <h3><?php echo $language ? 'Your changes relative to common version':'Vos changements par rapport à la version commune'; ?></h3>
        <div class="diffs">
        <?php
        function print_diff($perso,$key,$name) {
            global $language, $myStat, $globalStat;
            if (isset($myStat[$key]) && ($myStat[$key] != $globalStat[$key])) {
                echo '<div>';
                echo "$name: ";
                echo '<span class="before">'.$globalStat[$key].'</span>';
                echo '<span class="after">'.$myStat[$key].'</span>';
                echo '<a href="javascript:void(0)" onclick="resetStat(this,\''.$perso.'\','.$key.')"> ['. ($language ? 'Reset':'Rétablir') .']</a>';
                echo '</div>';
            }
        }
        foreach ((array)$myStats as $perso => $myStat) {
            $globalStat = $globalStats->{$perso};
            echo '<h4>'. getCharacterName($perso) .'</h4>';
            print_diff($perso,0, $language ? 'Acceleration':'Accélération');
            print_diff($perso,1, $language ? 'Speed':'Vitesse');
            print_diff($perso,2, $language ? 'Handling':'Maniabilité');
            print_diff($perso,3, $language ? 'Weight':'Poids');
        }
    }
    ?>
    </div>
	</body>
</html>
<?php
mysql_close();
?>