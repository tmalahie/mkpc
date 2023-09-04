<?php
function print_ghost_frame($time) {
	echo '[';
	echo $time[0].','.$time[1].','.$time[2].','.$time[3];
	if (!empty($time[4])) {
		$extra = $time[4];
		$flags = '0000';
		if ($extra->f)
			$flags[0] = '1'; // fall
		if (isset($extra->d)) {
			$flags[1] = '1';
			if ($extra->d > 0)
				$flags[2] = '1';
			elseif ($extra->d < 0)
				$flags[3] = '1';
		}
		echo ',"'.$flags[3].'"';
	}
	echo ']';
}
function print_ghost_frames($ghostId) {
	echo '[';
	if ($ghost = mysql_fetch_array(mysql_query('SELECT data FROM `mkghostdata` WHERE id="'. $ghostId.'"')))
		print_ghost_data($ghost);
	echo ']';
}
function print_ghost_data($ghost) {
	$colon = '';
	$ptsData = json_decode(gzuncompress($ghost['data']));
	foreach ($ptsData as $ptData) {
		echo $colon;
		print_ghost_frame($ptData);
		$colon = ',';
	}
}
?>