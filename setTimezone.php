<?php
if (isset($_POST['tz'])) {
	function isValidTimezoneId($tzid){
		$valid = array();
		$tza = timezone_abbreviations_list();
		foreach ($tza as $zone) {
			foreach ($zone as $item)
				$valid[$item['timezone_id']] = true;
		}
		unset($valid['']);
		return isset($valid[$tzid]);
	}
	if (isValidTimezoneId($_POST['tz']))
		setcookie('tz', $_POST['tz'], 4294967295,'/');
	else {
		include('initdb.php');
		mysql_query('INSERT INTO mkwrongtz VALUES(NULL,"'.$_POST['tz'].'")');
		mysql_close();
	}
}
?>