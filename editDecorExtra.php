<?php
if (isset($_GET['parent'])) {
    include('initdb.php');
    $decorId = $_GET['parent'];
    if ($decor = mysql_fetch_array(mysql_query('SELECT * FROM `mkdecors` WHERE id="'. $decorId .'"'))) {
		include('getId.php');
		if ($decor['identifiant'] == $identifiants[0]) {
            include('utils-decors.php');
            $extra = get_extra_sprites_payload('extraSprites');
            if (!empty($extra)) {
                include('file-quotas.php');
                $res = handle_decor_upload($decor['type'], null, $extra, $decor);
                if (isset($res['error'])) {
                    header('location: editDecor.php?id='.$decorId.'&error='.urlencode($res['error']));
                    exit;
                }
            }
        }
    }
    mysql_close();
    header('location: editDecor.php?id='.$decorId);
}