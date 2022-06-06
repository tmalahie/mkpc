<?php
if ('cli' !== php_sapi_name()) exit;
$circuit = array();
include('initdb.php');
include('postCircuitUpdate.php');
require_once('circuitPrefix.php');
$getCircuits = mysql_query('SELECT id FROM mkcircuits WHERE !type ORDER BY id');
while ($circuitData = mysql_fetch_array($getCircuits)) {
    $circuitId = $circuitData['id'];
    $circuit = array();
    $pieces = mysql_query('SELECT * FROM `mkp` WHERE circuit="'.$circuitId.'"');
    while ($piece = mysql_fetch_array($pieces))
        $circuit['p'.$piece['id']] = $piece['piece'];
    for ($j=0;$j<$nbLettres;$j++) {
        $lettre = $lettres[$j];
        $getInfos = mysql_query('SELECT * FROM `mk'.$lettre.'` WHERE circuit="'.$circuitId.'"');
        $incs = array();
        while ($info=mysql_fetch_array($getInfos)) {
            $prefix = getLetterPrefixD($lettre,$info);
            if (!isset($incs[$prefix])) $incs[$prefix] = 0;
            $circuit[$prefix.$incs[$prefix]] = $info['x'].','.$info['y'];
            $incs[$prefix]++;
        }
    }
    if (postCircuitUpdate('mkcircuits', $circuitId, $circuit))
        echo "Flagged circuit $circuitId\n";
}
mysql_close();