<?php
require_once('../includes/api.php');
$data = getJsonBody();
if (isset($data->name) && isset($data->password)) {
    include('../includes/initdb.php');
    require_once('../includes/auth.php');
	if (($getId = mysql_fetch_array(mysql_query('SELECT id,code,banned,deleted FROM `mkjoueurs` WHERE nom="'.$data->name.'"'))) && password_verify($data->password,$getId['code'])) {
		if ($getId['deleted'] && empty($data->restoreDeleted)) {
			$warningDeleted = true;
            renderResponse(array(
                'error' => 'account_deleted'
            ), 400);
		}
		else {
            session_start();
			session_regenerate_id();
			$id = $getId['id'];
			$_SESSION['mkid'] = $id;
			require_once('../../../credentials.php');
			setcookie('mkp', credentials_encrypt($id,$data->password), 4294967295, '/');
			if ($getId['deleted'])
				mysql_query('UPDATE `mkjoueurs` SET deleted=0 WHERE id="'. $id .'"');
			function banIfBlackIp() {
				global $id, $getId;
                $identifiants = getMkIds();
				if (!$getId['banned'] && mysql_numrows(mysql_query('SELECT * FROM `ip_bans` WHERE ip1="'.$identifiants[0].'" AND ip2="'.$identifiants[1].'" AND ip3="'.$identifiants[2].'" AND ip4="'.$identifiants[3].'"'))) {
					mysql_query('UPDATE `mkjoueurs` SET banned=2 WHERE id="'.$id.'"');
					mysql_query('INSERT IGNORE INTO `ip_bans` VALUES('.$id.',"'.$identifiants[0].'","'.$identifiants[1].'","'.$identifiants[2].'","'.$identifiants[3].'")');
					mysql_query('INSERT IGNORE INTO `mkbans` VALUES('.$id.',"Auto-ban par IP")');
				}
			}
			banIfBlackIp();
			include('../../../setId.php');
			banIfBlackIp();
            renderResponse(array(
                'id' => +$id
            ));
		}
	}
    else {
        renderResponse(array(
            'error' => 'wrong_credentials'
        ), 400);
    }
}
else {
    renderResponse(array(
        'error' => 'invalid_parameters'
    ), 400);
}