<?php
header('Content-Type: text/plain');
if (isset($_POST['prefix'])) {
	function toSQLSearch($search) {
	    $search = str_replace('"', '""', $search);
	    $search = str_replace('\\', '\\\\\\\\', $search);
	    $search = str_replace('%', '\\%', $search);
	    $search = $search .'%';
	    return $search;
	}
	include('../includes/initdb.php');
	$getPlayers = mysql_query('SELECT nom FROM `mkjoueurs` WHERE nom LIKE "'. toSQLSearch($_POST['prefix']) .'" AND deleted=0 ORDER BY CHAR_LENGTH(nom),id LIMIT 10');
	$v = '';
	echo '[';
	while ($player = mysql_fetch_array($getPlayers)) {
		echo $v.'"'.$player['nom'].'"';
		$v = ',';
	}
	echo ']';
	mysql_close();
}
?>