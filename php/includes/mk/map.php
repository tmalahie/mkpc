<?php
require_once(__DIR__.'/../circuitImgUtils.php');
$printCircuitData = function($circuit) {
	global $circuitMainData, $circuitPayload;
	$id = $circuit['ID'];
	$circuitPayload = json_decode(gzuncompress($circuit['data']));
	if (!$circuitPayload)
		return;
	$circuitMainData = $circuitPayload->main;
	$circuitImg = json_decode($circuit['img_data']);
	echo '{';
	?>
"map" : <?php echo $id; ?>,
"ext" : "<?php echo $circuitImg->ext; ?>",
"img" : "<?php echo getCircuitImgUrl($circuitImg); ?>",
"bgcolor" : [<?php echo implode(',',$circuitMainData->bgcolor) ?>],
"smartjump": 1,
"w" : <?php echo $circuitImg->w; ?>,
"h" : <?php echo $circuitImg->h; ?>,
<?php
if (isset($circuit['icon']))
	echo '"icon":'.json_encode($circuit['icon']).',';
if (isset($circuitMainData->bgcustom))
	echo '"custombg":'.$circuitMainData->bgimg.',';
else {
	echo '"fond":["';
	include(__DIR__.'/../circuitEnums.php');
	$getInfos = $bgImgs[$circuitMainData->bgimg];
	echo implode('","',$getInfos);
	echo '"],';
}
?>
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
	"yt" : "<?php if (isset($circuitMainData->youtube)) echo addslashes($circuitMainData->youtube); ?>",
	<?php
	if (isset($circuitMainData->youtube_opts)) {
		?>
		"yt_opts" : <?php echo json_encode($circuitMainData->youtube_opts); ?>,
		<?php
	}
}
?>
"startposition" : [<?php echo ($circuitMainData->startposition[0]+5).','.($circuitMainData->startposition[1]-6); ?>],
"startrotation" : <?php echo $circuitMainData->startrotation; ?>,
"startdirection" : <?php echo empty($circuitMainData->startdirection)?1:0; ?>,
"aipoints" : <?php echo json_encode($circuitPayload->aipoints); ?>,
<?php if (isset($circuitPayload->aishortcuts)) echo '"aishortcuts":'. json_encode($circuitPayload->aishortcuts) .','; ?>
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
<?php if (isset($circuitPayload->collisionProps)) echo '"collisionProps":'. json_encode($circuitPayload->collisionProps) .','; ?>
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
		if (count($sautsData) > 3) {
			if (is_numeric($sautsData[2]))
				$sautsData[2]++;
			if (is_numeric($sautsData[3]))
				$sautsData[3]++;
		}
	}
	unset($sautsData);
	echo json_encode($circuitPayload->sauts);
?>,
"accelerateurs" : <?php echo json_encode($circuitPayload->accelerateurs); ?>,
"decor" : <?php echo json_encode($circuitPayload->decor);
if (!empty($circuitPayload->decorparams)) {
	?>,
"decorparams" : <?php echo json_encode($circuitPayload->decorparams);
}
if (!empty($circuitPayload->assets)) {
	$assetTypes = array('pointers', 'flippers', 'bumpers','oils');
	foreach ($assetTypes as $assetType) {
		if (!empty($circuitPayload->assets->{$assetType})) {
			?>,
			"<?php echo $assetType; ?>" : <?php echo json_encode($circuitPayload->assets->{$assetType});
		}
	}
}
if (!empty($circuitPayload->cannons)) {
	?>,
"cannons" : <?php echo json_encode($circuitPayload->cannons);
}
if (!empty($circuitPayload->teleports)) {
	?>,
"teleports" : <?php echo json_encode($circuitPayload->teleports);
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
if (!empty($circuitPayload->elevators)) {
	?>,
"elevators" : <?php echo json_encode($circuitPayload->elevators); ?>
	<?php
}
?>
	}
	<?php
}
?>