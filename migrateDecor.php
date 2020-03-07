<?php
if ($_SERVER['REMOTE_ADDR'] != '90.90.118.235') die($_SERVER['REMOTE_ADDR']);
include('initdb.php');
$tables = array('circuits_data','arenes_data');
$decors = Array('tuyau', 'taupe', 'poisson', 'plante', 'boo', 'thwomp', 'spectre');
foreach ($tables as $table) {
	$getCircuits = mysql_query('SELECT * FROM '.$table.' ORDER BY id');
	while ($circuit = mysql_fetch_array($getCircuits)) {
		$payload = json_decode(gzuncompress($circuit['data']));
		$decor = $decors[$payload->main->decor];
		unset($payload->main->decor);
		if ($decor && !empty($payload->decor))
			$payload->decor = array($decor=>$payload->decor);
		else
			$payload->decor = new stdClass();
		mysql_query('UPDATE '.$table.' SET data="'.mysql_real_escape_string(gzcompress(json_encode($payload))).'" WHERE id='.$circuit['id']);
	}
}
mysql_close();
?>