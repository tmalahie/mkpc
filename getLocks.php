<?php
if (!isset($mkSaves))
	include('fetchSaves.php');
$unlocked = Array();
for ($i=0;$i<24;$i++)
	$unlocked[$i] = 0;
for ($i=0;$i<9;$i++)
	$unlocked[$i] = 1;
if ($total1 >= 3) {
	$unlocked[9] = 1;
	if ($total1 >= 7) {
		$unlocked[10] = 1;
		if ($total1 >= 12) {
			$unlocked[11] = 1;
			if ($total1 >= 18) {
				$unlocked[12] = 1;
				if ($total1 >= 24) {
					$unlocked[13] = 1;
					if ($total1 >= 30) {
						$unlocked[14] = 1;
						if ($total1 >= 33) {
							$unlocked[15] = 1;
							if ($total1 >= 39) {
								$unlocked[16] = 1;
								if ($total1 >= 42)
									$unlocked[17] = 1;
							}
						}
					}
				}
			}
		}
	}
}
if ($total2 >= 3) {
	$unlocked[18] = 1;
	if ($total2 >= 7) {
		$unlocked[19] = 1;
		if ($total2 >= 12) {
			$unlocked[20] = 1;
			if ($total2 >= 18) {
				$unlocked[21] = 1;
				if ($total2 >= 24) {
					$unlocked[22] = 1;
					if ($total2 >= 30)
						$unlocked[23] = 1;
				}
			}
		}
	}
}
require_once('isDS.php');
if (!IS_DS)
	array_splice($unlocked,15,3);
echo json_encode($unlocked);
?>