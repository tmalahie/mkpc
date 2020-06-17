<?php
include('fetchSaves.php');
$isCommon = isset($_GET['common']);
$ids = $isCommon ? '0' : '0,'.$identifiants[0];
$getPersos = mysql_query('SELECT * FROM mkteststats WHERE identifiant IN ('.$ids.') ORDER BY id');
$cp = array();
$statKeys = array('acceleration','speed','handling','mass','offroad');
while ($perso = mysql_fetch_array($getPersos)) {
    if (!$cp[$perso['perso']])
        $cp[$perso['perso']] = array(null,null,null,null,null);
    foreach ($statKeys as $i => $statKey) {
        if ($perso[$statKey] !== null)
            $cp[$perso['perso']][$i] = $perso[$statKey]/24;
    }
}
echo json_encode($cp);
?>