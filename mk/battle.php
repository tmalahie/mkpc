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
		switch ($circuitMainData->bgimg) {
		case 0 :
		$getInfos = Array('hills', 'trees');
		break;
		case 1 :
		$getInfos = Array('plains', 'pine');
		break;
		case 2 :
		$getInfos = Array('eciel', 'enuages');
		break;
		case 3 :
		$getInfos = Array('desert', 'roc');
		break;
		case 4 :
		$getInfos = Array('ciel', 'nuages');
		break;
		case 5 :
		$getInfos = Array('nuit', 'boos');
		break;
		case 6 :
		$getInfos = Array('volcans', 'pilliers');
		break;
		case 7 :
		$getInfos = Array('space', 'etoiles');
		break;
		case 8:
		$getInfos = Array('clouds','castle','bush');
		break;
		case 9:
		$getInfos = Array('palms','boat','waves');
		break;
		case 10:
		$getInfos = Array('sunset','grass','baobabs');
		break;
		case 11:
		$getInfos = Array('darkness','bowser','pillars');
		break;
		case 12:
		$getInfos = Array('fhills','shills','oaks');
		break;
		case 13:
		$getInfos = Array('spectrum','mansion','dtrees');
		break;
		case 14:
		$getInfos = Array('earth','dunes','sandcastle');
		break;
		case 15:
		$getInfos = Array('shield','throne','ark');
		break;
		case 16:
		$getInfos = Array('factory','airship','rain');
		break;
		case 17:
		$getInfos = Array('bean','yairship','yclouds');
		break;
		case 18:
		$getInfos = Array('sun','sand','lighthouse');
		break;
		case 19:
		$getInfos = Array('sunrise','canyon','valley');
		break;
		case 20:
		$getInfos = Array('pclouds','cristals','diamonds');
		break;
		case 21:
		$getInfos = Array('sclouds','garland','gifts');
		break;
		case 22:
		$getInfos = Array('dclouds','pyramids','mound');
		break;
		case 23:
		$getInfos = Array('storm','dongeons','towers');
		break;
		case 24:
		$getInfos = Array('scree','volcanos','willows');
		break;
		case 25:
		$getInfos = Array('cave','hallow','stalagmites');
		break;
		case 26:
		$getInfos = Array('hose','duct','grilling');
		break;
		case 27:
		$getInfos = Array('night','ship','nclouds');
		break;
	}
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
	"decor" : <?php echo json_encode($circuitPayload->decor); ?>
	}
	<?php
}
?>