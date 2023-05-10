<?php
if (isset($_POST['id']) && isset($_FILES['layer'])) {
	$bgId = intval($_POST['id']);
	include('../includes/initdb.php');
    include('../includes/getId.php');
    include('../includes/language.php');
    require_once('../includes/collabUtils.php');
    $requireOwner = !hasCollabGrants('mkbgs', $_POST['id'], $_POST['collab'], 'edit');
	if ($bg = mysql_fetch_array(mysql_query('SELECT id,identifiant FROM `mkbgs` WHERE id="'. $bgId .'"'. ($requireOwner ? (' AND identifiant="'. $identifiants[0] .'"') : '')))) {
        require_once('../includes/utils-bgs.php');
        include('../includes/file-quotas.php');
        $collabSuffix = isset($_POST['collab']) ? '&collab='.$_POST['collab'] : '';
        $url = isset($_POST['url']) ? $_POST['url'] : '';
        if ($url === '')
            $layerFile = $_FILES['layer'];
        else
            $layerFile = url_to_file_payload($url);
        $upload = handle_bg_upload(array($layerFile), array(
            'bg' => $bgId,
            'identifiant' => $bg['identifiant']
        ));
        if (isset($upload['id']))
            header('location: editBg.php?id='. $upload['id'] . $collabSuffix);
        if (isset($upload['error']))
            header('location: editBg.php?id='. $bgId .'&error='. urlencode($upload['error']) . $collabSuffix);
	}
	mysql_close();
}
?>