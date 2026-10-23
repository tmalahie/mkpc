<?php
function generatePasswordLink($memberId) {
	global $identifiants;
	do {
		$code = bin2hex(openssl_random_pseudo_bytes(16));
	} while (mysql_numrows(mysql_query('SELECT * FROM mkpassrecovery WHERE token="'. $code .'"')));
	mysql_query('INSERT INTO `mkpassrecovery` VALUES("'. $code .'",'.$memberId.','.$identifiants[0].',DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 7 DAY))');
	$link = 'https://mkpc.malahieude.net/new-password.php?code='. $code;
	return $link;
}