<?php
require_once(__DIR__.'/../circuitImgUtils.php');
$printCircuitData = function($circuit) {
	global $circuitMainData, $circuitPayload;
	$circuitPayload = json_decode(gzuncompress($circuit['data']));
	if (!$circuitPayload)
		return;
	$circuitMainData = $circuitPayload->main;
	printCircuitPart($circuit, $circuitPayload);
	if (isset($circuitPayload->lapOverrides)) {
		echo ',"lapOverrides":{';
		$v = '';
		foreach ($circuitPayload->lapOverrides as $lapKey => $lapData) {
			echo $v.'"'.$lapKey.'":';
			printCircuitPart(array(), $lapData);
			$v = ',';
		}
		echo '}';
	}
};
function printCircuitPart($circuit, $circuitPayload) {
	$circuitMainData = $circuitPayload->main;
	$circuitImg = isset($circuit['img_data']) ? json_decode($circuit['img_data']) : null;
	echo '{';
	if (isset($circuit['ID'])) echo '"map": '.$circuit['ID'].',';
	if ($circuitImg) {
	?>
"ext" : "<?php echo $circuitImg->ext; ?>",
"img" : "<?php echo getCircuitImgUrl($circuitImg); ?>",
"w" : <?php echo $circuitImg->w; ?>,
"h" : <?php echo $circuitImg->h; ?>,
	<?php
}
?>
"smartjump": 1,
<?php
if (isset($circuitMainData->bgcolor))
	echo '"bgcolor":['.implode(',',$circuitMainData->bgcolor).'],';
if (isset($circuit['icon']))
	echo '"icon":'.json_encode($circuit['icon']).',';
if (isset($circuitMainData->bgcustom))
	echo '"custombg":'.$circuitMainData->bgimg.',';
elseif (isset($circuitMainData->bgimg)) {
	echo '"fond":["';
	include(__DIR__.'/../circuitEnums.php');
	$getInfos = $bgImgs[$circuitMainData->bgimg];
	echo implode('","',$getInfos);
	echo '"],';
}
if (isset($circuitMainData->tours))
	echo '"tours":'.$circuitMainData->tours.',';
if (isset($circuitMainData->sections)) {
	?>
"sections" : [<?php echo implode(',',$circuitMainData->sections); ?>],
	<?php
}
if (isset($circuitMainData->music))
	echo '"music":'.$circuitMainData->music.',';
elseif (isset($circuitMainData->youtube)) {
	?>
	"yt" : "<?php echo addslashes($circuitMainData->youtube); ?>",
	<?php
	if (isset($circuitMainData->youtube_opts)) {
		?>
		"yt_opts" : <?php echo json_encode($circuitMainData->youtube_opts); ?>,
		<?php
	}
}
if (isset($circuitMainData->startposition)) {
	?>
"startposition" : [<?php echo ($circuitMainData->startposition[0]+5).','.($circuitMainData->startposition[1]-6); ?>],
"startrotation" : <?php echo $circuitMainData->startrotation; ?>,
"startdirection" : <?php echo empty($circuitMainData->startdirection)?1:0; ?>,
	<?php
}
if (isset($circuitPayload->aipoints))
	echo '"aipoints":'.json_encode($circuitPayload->aipoints).',';
if (isset($circuitPayload->aishortcuts)) echo '"aishortcuts":'. json_encode($circuitPayload->aishortcuts) .',';
if (isset($circuitPayload->airoutesmeta)) echo '"airoutesmeta":'. json_encode($circuitPayload->airoutesmeta) .',';
if (isset($circuitPayload->collision)) {
	?>
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
	<?php
}
if (isset($circuitPayload->collisionProps)) echo '"collisionProps":'. json_encode($circuitPayload->collisionProps) .',';
if (isset($circuitPayload->horspistes)) {
	?>
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
	<?php
}
if (isset($circuitPayload->trous)) {
	?>
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
	<?php
}
if (isset($circuitPayload->checkpoint))
	echo '"checkpoint":'.json_encode($circuitPayload->checkpoint).',';
if (isset($circuitPayload->arme))
	echo '"arme":'.json_encode($circuitPayload->arme).',';
if (isset($circuitPayload->sauts)) {
	?>
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
	<?php
}
if (isset($circuitPayload->accelerateurs))
	echo '"accelerateurs":'.json_encode($circuitPayload->accelerateurs).',';
if (isset($circuitPayload->decor)) {
	echo '"decor":'.json_encode($circuitPayload->decor);
if (!empty($circuitPayload->decorparams)) {
	?>,
"decorparams" : <?php echo json_encode($circuitPayload->decorparams);
	}
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