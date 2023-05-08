<?php
require_once('utils-date.php');
function parse_msg($msg) {
	return preg_replace("#[\r\t]#", ' ', preg_replace("#\r?\n#",'<br />',str_replace('\\','\\\\',htmlspecialchars($msg))));
}
?>