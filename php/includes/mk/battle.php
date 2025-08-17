<?php
require_once(__DIR__.'/../circuitImgUtils.php');
$printCircuitData = function($arene) {
	global $circuitMainData, $circuitPayload;
	$circuitPayload = json_decode(gzuncompress($arene['data']));
	if (!$circuitPayload)
		return;
	$circuitMainData = $circuitPayload->main;
	echo '{';
	printCircuitPart($arene, 0,$circuitPayload);
	if (isset($circuitPayload->lapOverrides)) {
		echo ',"lapOverrides":[';
		$v = '';
		$lapKey = 1;
		foreach ($circuitPayload->lapOverrides as $lapData) {
			echo $v.'{';
			printCircuitPart($arene, $lapKey,$lapData);
			echo '}';
			$v = ',';
			$lapKey++;
		}
		echo ']';
	}
	echo '}';
};
if (!function_exists('printCircuitPart')) {
function printCircuitPart($arene, $lapId,$circuitPayload) {
	$circuitMainData = $circuitPayload->main;
	$circuitImg = isset($arene['img_data']) ? json_decode($arene['img_data']) : null;
	if ($lapId) {
		if (isset($circuitImg->lapOverrides->$lapId))
			$circuitImg = getRefCircuitImg($circuitImg->lapOverrides->$lapId, $circuitImg);
		else
			$circuitImg = null;
	}
	if (!$lapId && isset($arene['ID'])) echo '"map": '.$arene['ID'].',';
	if ($circuitImg) {
	?>
	"ext" : "<?php echo $circuitImg->ext; ?>",
	"img" : "<?php echo getCircuitImgUrl($circuitImg); ?>",
	"w" : <?php echo $circuitImg->w; ?>,
	"h" : <?php echo $circuitImg->h; ?>,
		<?php
	}
	if ($lapId) {
		if (isset($circuitPayload->meta->time))
			echo '"time":'.$circuitPayload->meta->time.',';
		if (isset($circuitPayload->meta->endTime))
			echo '"endTime":'.$circuitPayload->meta->endTime.',';
		if (isset($circuitPayload->meta->zone))
			echo '"zone":'.json_encode($circuitPayload->meta->zone).',';
		if (isset($circuitPayload->meta->endZone))
			echo '"endZone":'.json_encode($circuitPayload->meta->endZone).',';
		if (isset($circuitPayload->meta->zoneMeta))
			echo '"zoneMeta":'.json_encode($circuitPayload->meta->zoneMeta).',';
		if (isset($circuitPayload->meta->endZoneMeta))
			echo '"endZoneMeta":'.json_encode($circuitPayload->meta->endZoneMeta).',';
		if (isset($circuitPayload->meta->endOnExit))
			echo '"endOnExit":'.json_encode($circuitPayload->meta->endOnExit).',';
		if (isset($circuitPayload->meta->endDelay))
			echo '"endDelay":'.$circuitPayload->meta->endDelay.',';
		if (isset($circuitPayload->meta->impactAll))
			echo '"impactAll":'.json_encode($circuitPayload->meta->impactAll).',';
		if (isset($circuitPayload->meta->interactions))
			echo '"lapInteractions":'.json_encode($circuitPayload->meta->interactions).',';
		if (isset($circuitPayload->meta->requiredOverrides))
			echo '"requiredOverrides":'.json_encode($circuitPayload->meta->requiredOverrides).',';
	}
	if (isset($circuitMainData->bgcolor))
		echo '"bgcolor":['.implode(',',$circuitMainData->bgcolor).'],';
	if (!$lapId && isset($arene['icon']))
		echo '"icon":'.json_encode($arene['icon']).',';
	if (isset($circuitMainData->bgcustom))
		echo '"custombg":'.$circuitMainData->bgimg.',';
	elseif (isset($circuitMainData->bgimg)) {
		echo '"custombg":undefined,';
		echo '"fond":["';
		include(__DIR__.'/../circuitEnums.php');
		$getInfos = $bgImgs[$circuitMainData->bgimg];
		echo implode('","',$getInfos);
		echo '"],';
	}
	if (!empty($circuitMainData->bgtransition))
		echo '"bgtransition":1,';
	if (!empty($circuitMainData->music)) {
		echo '"music":'.$circuitMainData->music.',';
		echo '"music_ref":{},';
		echo '"yt":undefined,';
	}
	elseif (isset($circuitMainData->youtube)) {
		?>
		"yt" : "<?php echo addslashes($circuitMainData->youtube); ?>",
		<?php
		if (isset($circuitMainData->youtube_opts)) {
			?>
			"yt_opts" : <?php echo json_encode($circuitMainData->youtube_opts); ?>,
			<?php
		}
		else
			echo '"yt_opts":undefined,';
		echo '"music_ref":{},';
		echo '"music":undefined,';
	}
	elseif (!$lapId) {
		echo '"music":9,';
		echo '"music_ref":{},';
	}
	if (isset($circuitMainData->startposition))
		echo '"startposition":'.json_encode($circuitMainData->startposition).',';
	elseif (!$lapId)
		echo '"startposition":[[-1,-1,0]],';
	if (isset($circuitPayload->aipoints)) {
		if ($lapId)
			$circuitPayload->aipoints = array($circuitPayload->aipoints);
		echo '"aipoints":'.json_encode($circuitPayload->aipoints).',';
	}
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
	if (isset($circuitPayload->collisionProps)) echo '"collisionProps":'. json_encode($circuitPayload->collisionProps) .',';
	elseif ($lapId) echo '"collisionProps":undefined,';
	}
	if (isset($circuitPayload->horspistes)) {
		?>
	"horspistes" : <?php
		echo json_encode($circuitPayload->horspistes);
	?>,
		<?php
	}
	if (isset($circuitPayload->trous)) {
		?>
	"trous" : <?php
		echo json_encode($circuitPayload->trous);
	?>,
		<?php
	}
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
		echo '"decor":'.json_encode($circuitPayload->decor).',';
	if (!empty($circuitPayload->decorparams)) {
		?>
	"decorparams" : <?php echo json_encode($circuitPayload->decorparams).',';
		}
		elseif ($lapId)
			echo '"decorparams":undefined,';
	}
	if (!empty($circuitPayload->assets)) {
		$assetTypes = array('pointers', 'flippers', 'bumpers', 'oils');
		foreach ($assetTypes as $assetType) {
			if (!empty($circuitPayload->assets->{$assetType})) {
				?>
				"<?php echo $assetType; ?>" : <?php echo json_encode($circuitPayload->assets->{$assetType}); ?>,
				<?php
			}
		}
	}
	echo '"_":0';
	if (isset($circuitPayload->cannons)) {
		?>,
	"cannons" : <?php echo json_encode($circuitPayload->cannons);
	}
	if (isset($circuitPayload->teleports)) {
		?>,
	"teleports" : <?php echo json_encode($circuitPayload->teleports);
	}
	if (isset($circuitPayload->rails)) {
		?>,
	"rails" : <?php echo json_encode($circuitPayload->rails);
	}
	if (isset($circuitPayload->flows)) {
		?>,
	"flows" : <?php echo json_encode($circuitPayload->flows);
	}
	if (isset($circuitPayload->spinners)) {
		?>,
	"spinners" : <?php echo json_encode($circuitPayload->spinners);
	}
	if (isset($circuitPayload->elevators)) {
		?>,
	"elevators" : <?php echo json_encode($circuitPayload->elevators);
	}
}
}
?>