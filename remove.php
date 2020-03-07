<?php
include('initdb.php');
$tables = Array('levels', 'blocs', 'mines', 'murs', 'objets', 'notes');
$nbTables = count($tables);
for ($i=0;isset($_GET['i'.$i]);$i++) {
	$id = $_GET['i'.$i];
	for ($j=0;$j<$nbTables;$j++)
		mysql_query('DELETE FROM `'.$tables[$j].'` WHERE id="'. $id .'"');
}
mysql_close();
?>