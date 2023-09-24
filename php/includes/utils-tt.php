<?php
function print_ghost_frame($time) {
	echo '[';
	echo $time[0].','.$time[1].','.$time[2].','.$time[3];
	if (!empty($time[4])) {
		$extra = $time[4];
		$flags = '0000';
		if (isset($extra->f))
			$flags[0] = '1'; // fall
		if (isset($extra->d)) {
			$flags[1] = '1';
			if ($extra->d > 0)
				$flags[2] = '1';
			elseif ($extra->d < 0)
				$flags[3] = '1';
		}
		echo ',"'.$flags.'"';
	}
	echo ']';
}
function print_ghost_frames($ghostId) {
	echo '[';
	if ($ghost = mysql_fetch_array(mysql_query('SELECT data FROM `mkghostsdata` WHERE id="'. $ghostId.'"')))
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
function print_time_ms($ms) {
	$sec = floor($ms/1000);
	$mls = round($ms-$sec*1000);
	$min = floor($sec/60);
	$sec -= $min*60;
	if ($sec < 10)
		$sec = '0'.$sec;
	if ($mls < 10)
		$mls = '00'.$mls;
	else if ($mls < 100)
		$mls = '0'.$mls;
	echo $min.':'.$sec.':'.$mls;
}
?>