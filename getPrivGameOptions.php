<?php
if (isset($_POST['key'])) {
	include('initdb.php');
	if ($privateLinkData = mysql_fetch_array(mysql_query('SELECT * FROM `mkgameoptions` WHERE id="'.$_POST['key'].'"')))
		echo $privateLinkData['rules'];
	mysql_close();
}
?>