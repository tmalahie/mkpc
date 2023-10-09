<?php
header('Content-Type: text/plain');
if (isset($_POST['prefix']) && isset($_POST['cc']) && isset($_POST['type'])) {
	function toSQLSearch($search) {
	    $search = str_replace('"', '""', $search);
	    $search = str_replace('\\', '\\\\\\\\', $search);
	    $search = str_replace('%', '\\%', $search);
	    $search = $search .'%';
	    return $search;
	}
	include('../includes/initdb.php');
    $cc = $_POST['cc'];
    $type = $_POST['type'];
    $cIDs = isset($_POST['cIDs']) ? explode(',', $_POST['cIDs']) : null;
	if ($cIDs) {
		foreach ($cIDs as &$cID)
			$cID = intval($cID);
		unset($cID);
	}
	if ($type && ($cIDs === null))
		$cIDs = array();
	if (($cIDs !== null) && empty($cIDs))
		$cIDs = array(0);
	$cIDsFilter = $cIDs ? ' AND circuit IN ('.implode(',',$cIDs).')' : '';
	$getPlayers = mysql_query('SELECT name FROM `mkrecords` WHERE name LIKE "'. toSQLSearch($_POST['prefix']) .'" AND best=1 AND type="'. $type .'" AND class="'. $cc .'"'.$cIDsFilter.' GROUP BY name ORDER BY CHAR_LENGTH(name),MIN(id) LIMIT 10');
	$v = '';
	echo '[';
	while ($player = mysql_fetch_array($getPlayers)) {
		echo $v.'"'.$player['name'].'"';
		$v = ',';
	}
	echo ']';
	mysql_close();
}
?>