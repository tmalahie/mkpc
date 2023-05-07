<?php
$idsSQL = '';
if (!isset($id)) $id = null;
if ($id)
	$idsSQL .= 'user="'. $id .'"';
else
	$idsSQL .= '0';
$myIdentifiants = null;
if (isset($_COOKIE['mktoken'])) {
	include('utilId.php');
	if (fetch_mkid()) {
		$idsSQL .= ' OR (';
		for ($i=0;$i<4;$i++) {
			if ($i)
				$idsSQL .= ' AND ';
			$key = 'identifiant'. ($i ? ($i+1):'');
			$idsSQL .= $key .'="';
			$myIdentifiants[$i] = $identifiants[$i];
			$idsSQL .= $myIdentifiants[$i];
			$idsSQL .= '"';
		}
		$idsSQL .= ')';
	}
}
?>