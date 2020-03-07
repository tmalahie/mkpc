<?php
include('session.php');
if ($id && isset($_POST['member'])) {
	include('initdb.php');
	mysql_query('DELETE FROM `mkinvitations` WHERE demandeur="'. $id .'" AND receveur="'. $_POST['member'] .'" AND battle='. (isset($_POST['battle']) ? 1:0) .' AND reponse!=-1');
	echo 1;
	mysql_close();
}
?>