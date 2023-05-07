<?php
if (isset($_POST['options'])) {
	$options = json_decode($_POST['options']);
	if ($options) {
		include('onlineRulesUtils.php');
		$rulesString = mysql_real_escape_string(getRulesAsString($options));
		mysql_query(
			'INSERT INTO `mkgameoptions` SET id="'. $key .'",
			rules="'.$rulesString.'"
			ON DUPLICATE KEY UPDATE rules="'.$rulesString.'"'
		);
	}
}
?>