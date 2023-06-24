<?php
require_once(__DIR__.'/../circuitImgUtils.php');
$printCircuitData = function($arene) {
	global $circuitMainData, $circuitPayload;
	$id = $arene['ID'];
	$circuitPayload = json_decode(gzuncompress($arene['data']));
	if (!$circuitPayload)
		return;
	$circuitMainData = $circuitPayload->main;
	$circuitImg = json_decode($arene['img_data']);
	echo '{';
	?>
	"map" : <?php echo $id; ?>,
	"ext" : "<?php echo $circuitImg->ext; ?>",
	"img" : "<?php echo getCircuitImgUrl($circuitImg); ?>",
	"bgcolor" : [<?php echo implode(',',$circuitMainData->bgcolor) ?>],
	"w" : <?php echo $circuitImg->w; ?>,
	"h" : <?php echo $circuitImg->h; ?>,
	<?php
	if (isset($arene['icon']))
		echo '"icon":'.json_encode($arene['icon']).',';
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
	"startposition" : <?php echo empty($circuitMainData->startposition) ? '[[-1,-1,0]]':json_encode($circuitMainData->startposition); ?>,
	"aipoints" : <?php echo json_encode($circuitPayload->aipoints); ?>,
	"collision" : <?php
		foreach ($circuitPayload->collision as &$collisionData) {
			if (isset($collisionData[3]) && is_numeric($collisionData[3])) {
				$collisionData[2]++;
				$collisionData[3]++;
			}
		}
		echo json_encode($circuitPayload->collision);
	?>,
	<?php if (isset($circuitPayload->collisionProps)) echo '"collisionProps":'. json_encode($circuitPayload->collisionProps) .','; ?>
	"horspistes" : <?php echo json_encode($circuitPayload->horspistes); ?>,
	"trous" : <?php echo json_encode($circuitPayload->trous); ?>,
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
		echo json_encode($circuitPayload->sauts);
	?>,
	"accelerateurs" : <?php echo json_encode($circuitPayload->accelerateurs); ?>,
	"decor" : <?php echo json_encode($circuitPayload->decor);
	if (!empty($circuitPayload->decorparams)) {
		?>,
	"decorparams" : <?php echo json_encode($circuitPayload->decorparams);
	}
	if (!empty($circuitPayload->assets)) {
		$assetTypes = array('pointers', 'flippers', 'bumpers', 'oils');
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
	"spinners" : <?php echo json_encode($circuitPayload->spinners);
	}
	if (!empty($circuitPayload->elevators)) {
		?>,
	"elevators" : <?php echo json_encode($circuitPayload->elevators);
	}
	echo '}';
}
?>