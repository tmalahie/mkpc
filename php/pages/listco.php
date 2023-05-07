<?php
include('session.php');
include('initdb.php');
$getConnectes = mysql_query('SELECT id FROM `mkconnectes` WHERE id!="'. $id .'" AND connecte > '. (time()-30));
echo '[';
$colon = '';
while ($getId = mysql_fetch_array($getConnectes)) {
	$getPseudo = mysql_fetch_array(mysql_query('SELECT nom,pts FROM `mkjoueurs` WHERE id='. $getId['id']));
	echo $colon .'['. $getId['id'].',"'. $getPseudo['nom'].'",'.$getPseudo['pts'].']';
	$colon = ',';
}
echo ']';
mysql_close();
?>