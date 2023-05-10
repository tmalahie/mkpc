<?php
header('Content-Type: text/plain');
include('../includes/initdb.php');
$ip = $_SERVER['REMOTE_ADDR'];
$getCountryCode = mysql_query('SELECT country,date FROM `mkipcountry` WHERE ip="'. $ip .'"');
if ($countryCode = mysql_fetch_array($getCountryCode)) {
	if (strtotime($countryCode['date'].' Europe/Paris') < (time()-7*86400)) {
		mysql_query('DELETE FROM `mkipcountry` WHERE ip="'. $ip .'"');
		$countryCode = null;
	}
}
if ($countryCode) {
	if (!empty($countryCode['country']))
		echo '{"countryCode":"'.$countryCode['country'].'"}';
	else
		echo '{}';
}
else {
	$res = file_get_contents("http://ip-api.com/json/$ip?fields=countryCode");
	$data = json_decode($res);
	if ($data && !empty($data->countryCode)) {
		$code = $data->countryCode;
		echo $res;
	}
	else {
		$code = '';
		echo '{}';
	}
	mysql_query('INSERT IGNORE INTO `mkipcountry` VALUES("'.$ip.'","'.mysql_real_escape_string($data->countryCode).'",NULL)');
}
mysql_close();
?>