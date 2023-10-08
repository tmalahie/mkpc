<?php
function pretty_dates($str, $options=array()) {
	global $language;
	$str = humanize_date(to_local_tz($str),$options);
	$tz = new \DateTimeZone(get_client_tz());
	$now = new \Datetime('now', $tz);
	$onedayago = new \Datetime('-1 day', $tz);
	if ($language) {
		$today = $now->format('Y-m-d');
		$yesterday = $onedayago->format('Y-m-d');
		$str = str_replace("On $today",'Today',$str);
		$str = str_replace("on $today",'today',$str);
		$str = str_replace("On $yesterday",'Yesterday',$str);
		$str = str_replace("on $yesterday",'yesterday',$str);
	}
	else {
		$today = $now->format('d/m/Y');
		$yesterday = $onedayago->format('d/m/Y');
		$str = str_replace("Le $today",'Aujourd\'hui',$str);
		$str = str_replace("le $today",'aujourd\'hui',$str);
		$str = str_replace("Le $yesterday",'Hier',$str);
		$str = str_replace("le $yesterday",'hier',$str);
	}
	return $str;
}
function pretty_dates_short($str, $options=array()) {
	global $language;
	$dt = new \DateTime($str, new \DateTimeZone('Europe/Paris'));
	$new = isset($options['new']) ? $options['new'] : (time()-$dt->getTimestamp() < 86400);
	if (isset($_COOKIE['tz']))
		$dt->setTimezone(new \DateTimeZone($_COOKIE['tz']));
	if (isset($options['shorter'])) {
		if ($new)
			$format = 'G:i';
		else {
			if (isset($options['year']))
				$format = $language ? 'm-d-y':'d/m/y';
			else
				$format = $language ? 'm-d':'d/m';
		}
	}
	elseif (isset($options['lower'])) {
		if ($new)
			$format = $language ? '\a\t G:i':'&\a\g\r\a\v\e; G:i';
		else
			$format = $language ? '\o\n m-d':'\l\e d/m';
	}
	else {
		if ($new)
			$format = $language ? '\A\t G:i':'&\A\g\r\a\v\e; G:i';
		else
			$format = $language ? '\O\n m-d':'\L\e d/m';
	}
	return $dt->format($format);
}
function humanize_date($strDate, &$options=array()) {
	global $language;
	if (isset($options['lower']))
		$format = $language ? 'on $1-$2-$3 at $4:$5:$6':'le $3/$2/$1 &agrave; $4:$5:$6';
	else
		$format = $language ? 'On $1-$2-$3 at $4:$5:$6':'Le $3/$2/$1 &agrave; $4:$5:$6';
	return preg_replace('#^(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)$#', $format, $strDate);
}
function get_client_tz() {
	if (isset($_COOKIE['tz']))
		return $_COOKIE['tz'];
	return 'Europe/Paris';
}
function to_local_tz($strDate, $format='Y-m-d H:i:s') {
	if ($strDate && isset($_COOKIE['tz'])) {
		$tz = $_COOKIE['tz'];
		$date = new \Datetime($strDate, new \DateTimeZone('Europe/Paris'));
		$date->setTimezone(new \DateTimeZone($tz));
		return $date->format($format);
	}
	return $strDate;
}
?>