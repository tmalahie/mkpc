<?php
foreach ($circuitsData as $c => $circuit) {
	if ($c)
		echo ',';
	$id = $circuit['ID'];
	$circuitPayload = json_decode(gzuncompress($circuit['data']));
	$circuitMainData = $circuitPayload->main;
	include('getExt.php');
	list($w,$h) = getimagesize('images/uploads/map'.$id.'.'.$ext);
	?>
"map<?php echo ($c+1); ?>" : {
"map" : <?php echo $id; ?>,
"ext" : "<?php echo $ext; ?>",
"bgcolor" : [<?php echo implode(',',$circuitMainData->bgcolor) ?>],
"smartjump": 1,
"w" : <?php echo $w; ?>,
"h" : <?php echo $h; ?>,
"fond" : ["<?php
	require_once('circuitEnums.php');
	$getInfos = $bgImages[$circuitMainData->bgimg];
	echo implode('","',$getInfos);
?>"],
"tours" : <?php echo $circuitMainData->tours; ?>,
<?php
if (isset($circuitMainData->sections)) {
	?>
"sections" : [<?php echo implode(',',$circuitMainData->sections); ?>],
	<?php
}
?>
"music" : <?php echo $circuitMainData->music; ?>,
<?php
if (!$circuitMainData->music) {
	?>
	"yt" : "<?php echo addslashes($circuitMainData->youtube); ?>",
	<?php
}
?>
"startposition" : [<?php echo ($circuitMainData->startposition[0]+5).','.($circuitMainData->startposition[1]-6); ?>],
"startrotation" : <?php echo $circuitMainData->startrotation; ?>,
"startdirection" : <?php echo $circuitMainData->startdirection?0:1; ?>,
"aipoints" : <?php echo json_encode($circuitPayload->aipoints); ?>,
"collision" : <?php
	foreach ($circuitPayload->collision as &$collisionData) {
		if (isset($collisionData[3]) && is_numeric($collisionData[3])) {
			$collisionData[2]++;
			$collisionData[3]++;
		}
	}
	unset($collisionData);
	echo json_encode($circuitPayload->collision);
?>,
"horspistes" : <?php
	foreach ($circuitPayload->horspistes as &$hpsData) {
		foreach ($hpsData as &$hpData) {
			if (isset($hpData[3]) && is_numeric($hpData[3])) {
				$hpData[2]++;
				$hpData[3]++;
			}
		}
		unset($hpData);
	}
	unset($hpsData);
	echo json_encode($circuitPayload->horspistes);
?>,
"trous" : <?php
	foreach ($circuitPayload->trous as &$trousData) {
		foreach ($trousData as &$trouData) {
			if (isset($trouData[0][3]) && is_numeric($trouData[0][3])) {
				$trouData[0][2]++;
				$trouData[0][3]++;
			}
		}
		unset($trouData);
	}
	unset($trousData);
	echo json_encode($circuitPayload->trous);
?>,
"checkpoint" : <?php echo json_encode($circuitPayload->checkpoint); ?>,
"arme" : <?php echo json_encode($circuitPayload->arme); ?>,
"sauts" : <?php
	foreach ($circuitPayload->sauts as &$sautsData) {
		$sautsData[2]++;
		$sautsData[3]++;
	}
	unset($sautsData);
	echo json_encode($circuitPayload->sauts);
?>,
"accelerateurs" : <?php echo json_encode($circuitPayload->accelerateurs); ?>,
"decor" : <?php echo json_encode($circuitPayload->decor);
if (!empty($circuitPayload->cannons)) {
	?>,
"cannons" : <?php echo json_encode($circuitPayload->cannons);
}
if (!empty($circuitPayload->flows)) {
	?>,
"flows" : <?php echo json_encode($circuitPayload->flows);
}
if (!empty($circuitPayload->spinners)) {
	?>,
"spinners" : <?php echo json_encode($circuitPayload->spinners); ?>
	<?php
}
?>
	}
	<?php
}
?>