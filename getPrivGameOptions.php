<?php
if (isset($_POST['key'])) {
	include('initdb.php');
	if ($privateLinkData = mysql_fetch_array(mysql_query('SELECT IFNULL(o.rules,"{}") AS rules FROM `mkprivgame` p LEFT JOIN `mkgameoptions` o ON p.id=o.id WHERE p.id="'.$_POST['key'].'"')))
		echo $privateLinkData['rules'];
	mysql_close();
}
?>