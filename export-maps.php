<?php
include('initdb.php');
$baseData = json_decode(file_get_contents('mk/maps.json'), true);
$getCircuitsData = mysql_query('SELECT * FROM circuits_data WHERE id>=1 AND id<=40');
while ($circuit = mysql_fetch_array($getCircuitsData)) {
	$id = $circuit['id'];
	include('getExt.php');
	list($w,$h) = getimagesize('images/uploads/map'.$id.'.'.$ext);
	$circuitPayload = json_decode(gzuncompress($circuit['data']));
	$circuitMainData = $circuitPayload->main;
	$mapData = &$baseData["map$id"];
	/*$mapData['ext'] = $ext;
	if ('png' === $mapData['ext'])
		unset($mapData['ext']);
	$mapData['bgcolor'] = $circuitMainData->bgcolor;
	$mapData['w'] = $w;
	$mapData['h'] = $h;
	$mapData['tours'] = $circuitMainData->tours;
	$mapData['startposition'] = array(($circuitMainData->startposition[0]+5),($circuitMainData->startposition[1]-6));
	$mapData['startrotation'] = $circuitMainData->startrotation;
	$mapData['startdirection'] = $circuitMainData->startdirection?0:1;
	$aipoints = $mapData['aipoints'];
	$mapData['aipoints'] = $circuitPayload->aipoints;
	if (count($mapData['aipoints']) == 1)
		$mapData['aipoints'] = $mapData['aipoints'][0];
	if (empty($mapData['aipoints']))
		$mapData['aipoints'] = $aipoints;
	foreach ($circuitPayload->collision as &$collisionData) {
		if (isset($collisionData[3]) && is_numeric($collisionData[3])) {
			$collisionData[2]++;
			$collisionData[3]++;
		}
	}
	unset($collisionData);
	$mapData['collision'] = $circuitPayload->collision;
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
	$mapData['horspistes'] = $circuitPayload->horspistes;
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
	$mapData['trous'] = $circuitPayload->trous;*/
	$mapData['checkpoint'] = $circuitPayload->checkpoint;
	if (empty($mapData['horspistes'])) unset($mapData['horspistes']);
	/*$mapData['arme'] = $circuitPayload->arme;
	foreach ($circuitPayload->sauts as &$sautsData) {
		$sautsData[2]++;
		$sautsData[3]++;
	}
	unset($sautsData);
	$mapData['sauts'] = $circuitPayload->sauts;
	$mapData['accelerateurs'] = $circuitPayload->accelerateurs;
	foreach ($circuitPayload->decor as $type => $value)
		$mapData['decor'][$type] = $circuitPayload->decor->{$type};
	switch ($id) {
	case 41:
		foreach ($mapData['sauts'] as &$saut)
			$saut[4] = 4.2;
		unset($saut);
		break;
	case 44:
		$mapData['decor']['movingtree'] = [[308,777,null,0,[[302,762],[314,792]]],[326,985,null,0,[[342,963],[310,1007]]],[521,950,null,0,[[510,971],[548,957],[527,933],[497,937]]],[675,1144,null,0,[[684,1121],[665,1166]]]];
		break;
	case 45:
		$mapData['decor']['firesnake'] = [[679,970,null,0,465],[685,1031,null,0,450],[493,327,null,0,585],[559,390,null,0,600]];
		break;
	case 55:
		$mapData['sauts'] = [[258,254,26,8,2.2],[248,218,8,26,1.6]];
		break;
	case 56:
		$mapData['startposition'] = [225,642];
		$mapData['startrotation'] = 45;
		break;
	}*/
}
header('Content-Type: application/json');
echo json_encode($baseData);
mysql_close();
?>