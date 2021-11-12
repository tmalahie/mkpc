<?php
require_once('auth.php');
function getNotifsSqlFilter() {
    $idsSQL = '';
    $id = getUserId();
    if (!isset($id)) $id = null;
    if ($id)
        $idsSQL .= 'user="'. $id .'"';
    else
        $idsSQL .= '0';
    if (isset($_COOKIE['mktoken'])) {
        $myIdentifiants = getMkIds();
        $idsSQL .= ' OR (';
        for ($i=0;$i<4;$i++) {
            if ($i)
                $idsSQL .= ' AND ';
            $key = 'identifiant'. ($i ? ($i+1):'');
            $idsSQL .= $key .'="';
            $idsSQL .= $myIdentifiants[$i];
            $idsSQL .= '"';
        }
        $idsSQL .= ')';
    }
    return $idsSQL;
}
?>