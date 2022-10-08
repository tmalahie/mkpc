<?php
if (isset($_POST['id']) && isset($_FILES['layer'])) {
	$bgId = $_POST['id'];
	include('initdb.php');
    include('getId.php');
    include('language.php');
    require_once('collabUtils.php');
    $requireOwner = !hasCollabGrants('mkbgs', $_POST['id'], $_POST['collab'], 'edit');
	if ($bg = mysql_fetch_array(mysql_query('SELECT id FROM `mkbgs` WHERE id="'. $bgId .'"'. ($requireOwner ? (' AND identifiant="'. $identifiants[0] .'"') : '')))) {
        require_once('utils-bgs.php');
        include('file-quotas.php');
        $collabSuffix = isset($_POST['collab']) ? '&collab='.$_POST['collab'] : '';
        $url = isset($_POST['url']) ? $_POST['url'] : '';
        if ($url === '')
            $layerFile = $_FILES['layer'];
        else
            $layerFile = url_to_file_payload($url);
        $upload = handle_bg_upload(array($layerFile), array(
            'bg' => $bgId
        ));
        if (isset($upload['id']))
            header('location: editBg.php?id='. $upload['id'] . $collabSuffix);
        if (isset($upload['error']))
            header('location: editBg.php?id='. $bgId .'&error='. urlencode($upload['error']) . $collabSuffix);
	}
	mysql_close();
}
?>