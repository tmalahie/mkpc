<?php
header('Content-Type: text/plain');
if (isset($_POST['tz'])) {
	function isValidTimezoneId($tzid){
		try {
			new DateTimeZone($tzid);
		}
		catch (Exception $e) {
			return false;
		}
		return true;
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