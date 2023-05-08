<?php
if (isset($_POST['id'])) {
    header('Content-Type: application/json');
	include('../includes/initdb.php');
	$getPlayer = mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $_POST['id'] .'"');
    $res = null;
	if ($player = mysql_fetch_array($getPlayer)) {
        $res = array(
            'name' => $player['nom']
        );
	}
    echo json_encode($res);
	mysql_close();
}
?>