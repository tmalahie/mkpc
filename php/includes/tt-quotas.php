<?php
require_once('circuitImgUtils.php');
function tt_total_quota($options=null) {
	global $identifiants;
	$ownerId = isset($options['identifiant']) ? $options['identifiant'] : $identifiants[0];
	if ($getQuota = mysql_fetch_array(mysql_query('SELECT tt_quota FROM `mkidentifiants` WHERE identifiant='.$ownerId.' AND tt_quota IS NOT NULL')))
		return +$getQuota['file_quota'];
	return 30000000;
}
define('MAX_GHOST_TIME', tt_total_quota());
function tt_used_quota($item=null) {
	global $identifiants;
    $except = isset($item['ghost']) ? $item['ghost'] : -1;
    $getTime = mysql_fetch_array(mysql_query('SELECT SUM(time) AS total FROM `mkghosts` WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3].' AND id!='.$except));
    return $getTime['total'];
}
?>