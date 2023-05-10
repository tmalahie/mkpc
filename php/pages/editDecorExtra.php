<?php
if (isset($_GET['parent'])) {
    include('../includes/initdb.php');
    $decorId = intval($_GET['parent']);
    $collabSuffix = isset($_GET['collab']) ? '&collab='.urlencode($_GET['collab']) : '';
    if ($decor = mysql_fetch_array(mysql_query('SELECT * FROM `mkdecors` WHERE id="'. $decorId .'"'))) {
		include('../includes/getId.php');
		require_once('../includes/collabUtils.php');
		if (($decor['identifiant'] == $identifiants[0]) || hasCollabGrants('mkdecors', $decor['id'], $_GET['collab'], 'edit')) {
            include('../includes/utils-decors.php');
            $extra = get_extra_sprites_payload('extraSprites');
            if (!empty($extra)) {
                include('../includes/file-quotas.php');
                $res = handle_decor_upload($decor['type'], null, $extra, $decor);
                if (isset($res['error'])) {
                    header('location: editDecor.php?id='.$decorId.'&error='.urlencode($res['error']).$collabSuffix);
                    exit;
                }
            }
        }
    }
    mysql_close();
    header('location: editDecor.php?id='.$decorId.$collabSuffix);
}