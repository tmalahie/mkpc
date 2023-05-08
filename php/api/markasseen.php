<?php
header('Content-Type: text/plain');
include('../includes/session.php');
if ($id && isset($_POST['member'])) {
	include('../includes/initdb.php');
	mysql_query('DELETE FROM `mkinvitations` WHERE demandeur="'. $id .'" AND receveur="'. $_POST['member'] .'" AND battle='. (isset($_POST['battle']) ? 1:0) .' AND reponse!=-1');
	echo 1;
	mysql_close();
}
?>