<?php
if ('cli' !== php_sapi_name()) exit;
$circuitId = -1;
$circuit = array();
include('initdb.php');
include('postCircuitUpdate.php');
$getCircuitPieces = mysql_query('SELECT p.* FROM mkp p INNER JOIN mkcircuits c ON p.circuit=c.id WHERE !c.type ORDER BY p.circuit,p.id');
function handleCircuit($circuitId, &$circuit) {
    if ($circuitId === -1) return;
    if (postCircuitUpdate('mkcircuits', $circuitId, $circuit))
        echo "Flagged circuit $circuitId\n";
}
while ($circuitPiece = mysql_fetch_array($getCircuitPieces)) {
    if ($circuitPiece['circuit'] !== $circuitId) {
        handleCircuit($circuitId, $circuit);
        $circuit = array();
        $circuitId = $circuitPiece['circuit'];
    }
    $circuit['p'.$circuitPiece['id']] = $circuitPiece['piece'];
}
handleCircuit($circuitId, $circuit);
mysql_close();