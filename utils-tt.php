<?php
define('GHOST_MYSQL_FIELDS', 'posX,posY,posZ,rotation,flags,reverse(export_set(flags,"1","0","",4)) AS flags_raw');
function print_ghost_frame($time) {
	echo '[';
	echo $time['posX'].','.$time['posY'].','.$time['posZ'].','.$time['rotation'];
	if ($time['flags'])
		echo ',"'.$time['flags_raw'].'"';
	echo ']';
}
?>