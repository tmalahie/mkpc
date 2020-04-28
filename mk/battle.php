<?php
$src = 'course';
foreach ($circuitsData as $c => $arene) {
	$arene = $circuitsData[$c];
	if ($c)
		echo ',';
	$id = $arene['ID'];
	$circuitPayload = json_decode(gzuncompress($circuit['data']));
	$circuitMainData = $circuitPayload->main;
	include('getExt.php');
	list($w,$h) = getimagesize('images/uploads/course'.$id.'.'.$ext);
	?>
"map<?php echo ($c+1); ?>" : {
	"map" : <?php echo $id; ?>,
	"ext" : "<?php echo $ext; ?>",
	"bgcolor" : [<?php echo implode(',',$circuitMainData->bgcolor) ?>],
	"w" : <?php echo $w; ?>,
	"h" : <?php echo $h; ?>,
	"music" : <?php echo $circuitMainData->music; ?>,
	<?php
	if (!$circuitMainData->music) {
		?>
		"yt" : "<?php echo addslashes($circuitMainData->youtube); ?>",
		<?php
	}
	?>
	"fond" : ["<?php
		require_once('circuitEnums.php');
		$getInfos = $bgImages[$circuitMainData->bgimg];
		echo implode('","',$getInfos);
	?>"],
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
	"horspistes" : <?php echo json_encode($circuitPayload->horspistes); ?>,
	"trous" : <?php echo json_encode($circuitPayload->trous); ?>,
	"arme" : <?php echo json_encode($circuitPayload->arme); ?>,
	"sauts" : <?php
		foreach ($circuitPayload->sauts as &$sautsData) {
			$sautsData[2]++;
			$sautsData[3]++;
		}
		echo json_encode($circuitPayload->sauts);
	?>,
	"accelerateurs" : <?php echo json_encode($circuitPayload->accelerateurs); ?>,
	"decor" : <?php echo json_encode($circuitPayload->decor);
	if (!empty($circuitPayload->decorparams)) {
		?>,
	"decorparams" : <?php echo json_encode($circuitPayload->decorparams);
	}
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
	"spinners" : <?php echo json_encode($circuitPayload->spinners);
	}
	echo '}';
}
?>