<?php
if (isset($_POST['id']) && isset($_FILES['layer'])) {
	$bgId = $_POST['id'];
	include('initdb.php');
    include('getId.php');
    include('language.php');
	if ($bg = mysql_fetch_array(mysql_query('SELECT id FROM `mkbgs` WHERE id="'. $bgId .'" AND identifiant="'. $identifiants[0] .'"'))) {
        require_once('utils-bgs.php');
        include('file-quotas.php');
        $url = isset($_POST['url']) ? $_POST['url'] : '';
        if ($url === '')
            $layerFile = $_FILES['layer'];
        else
            $layerFile = url_to_file_payload($url);
        $upload = handle_bg_upload(array($layerFile), array(
            'bg' => $bgId
        ));
        if (isset($upload['id']))
            header('location: editBg.php?id='. $upload['id']);
        if (isset($upload['error']))
            header('location: editBg.php?id='. $bgId .'&error='. urlencode($upload['error']));
	}
	mysql_close();
}
?>