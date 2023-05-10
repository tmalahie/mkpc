<?php
header("Access-Control-Allow-Origin: *");
include('../includes/initdb.php');
$allData = json_decode(file_get_contents('../includes/mk/maps.json'), true);
$baseData = array();
for ($i=1;$i<=56;$i++)
	$baseData["map$i"] = $allData["map$i"];
unset($allData);
$getCircuitsData = mysql_query('SELECT c.img_data,d.* FROM circuits c INNER JOIN circuits_data d ON c.id=d.id WHERE d.id>=1 AND d.id<=56');
while ($circuit = mysql_fetch_array($getCircuitsData)) {
	$id = $circuit['id'];
	$circuitImg = json_decode($circuit['img_data']);
	$w = $circuitImg->w;
	$h = $circuitImg->h;
	$ext = $circuitImg->ext;
	$circuitPayload = json_decode(gzuncompress($circuit['data']));
	$circuitMainData = $circuitPayload->main;
	$mapData = &$baseData["map$id"];
	$mapData['ext'] = $ext;
	if ('png' === $mapData['ext'])
		unset($mapData['ext']);
	$mapData['bgcolor'] = $circuitMainData->bgcolor;
	$mapData['w'] = $w;
	$mapData['h'] = $h;
	if (512 === $mapData['w'])
		unset($mapData['w']);
	if (512 === $mapData['h'])
		unset($mapData['h']);
	$mapData['tours'] = $circuitMainData->tours;
	if (3 === $mapData['tours'])
		unset($mapData['tours']);
	$mapData['startposition'] = array(($circuitMainData->startposition[0]+5),($circuitMainData->startposition[1]-6));
	$mapData['startrotation'] = $circuitMainData->startrotation;
	if (180 === $mapData['startrotation'])
		unset($mapData['startrotation']);
	$mapData['startdirection'] = empty($circuitMainData->startdirection)?1:0;
	$aipoints = $mapData['aipoints'];
	$mapData['aipoints'] = $circuitPayload->aipoints;
	if (isset($circuitPayload->aishortcuts))
		$mapData['aishortcuts'] = $circuitPayload->aishortcuts;
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
	if (empty($mapData['collision']))
		unset($mapData['collision']);
	if (isset($circuitPayload->collisionProps))
		$mapData['collisionProps'] = $circuitPayload->collisionProps;
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
	if (empty((array)$mapData['horspistes']))
		unset($mapData['horspistes']);
	$allEmpty = true;
	foreach ($circuitPayload->trous as &$trousData) {
		foreach ($trousData as &$trouData) {
			$allEmpty = false;
			if (isset($trouData[0][3]) && is_numeric($trouData[0][3])) {
				$trouData[0][2]++;
				$trouData[0][3]++;
				$trouData = [$trouData[0][0],$trouData[0][1],$trouData[0][2],$trouData[0][3],$trouData[1][0],$trouData[1][1]];
			}
		}
		unset($trouData);
	}
	unset($trousData);
	$mapData['trous'] = $circuitPayload->trous;
	if ($allEmpty)
		unset($mapData['trous']);
	$mapData['checkpoint'] = $circuitPayload->checkpoint;
	if (empty($mapData['horspistes'])) unset($mapData['horspistes']);
	$mapData['arme'] = $circuitPayload->arme;
	foreach ($circuitPayload->sauts as &$sautsData) {
		$sautsData[2]++;
		$sautsData[3]++;
		if (isset($sautsData[5]) && !isset($sautsData[6]))
			unset($sautsData[5]);
	}
	unset($sautsData);
	if ($mapData['fond'] == array('eciel','enuages'))
		$mapData['fond'] = array('ciel','enuages');
	$mapData['sauts'] = $circuitPayload->sauts;
	if (empty($mapData['sauts']))
		unset($mapData['sauts']);
	$mapData['accelerateurs'] = $circuitPayload->accelerateurs;
	if (empty($mapData['accelerateurs']))
		unset($mapData['accelerateurs']);
	foreach ($circuitPayload->decor as $type => $value)
		$mapData['decor'][$type] = $circuitPayload->decor->{$type};
	switch ($id) {
	case 8:
		foreach ($mapData['sauts'] as &$sautsData)
			unset($sautsData[5]);
		$mapData['decor']['thwomp'] = [[77,391,null,10,0],[89,391,null,10,10],[101,391,null,10,20],[283,125,null,0,0],[343,493,null,0,0]];
		break;
	case 44:
		$mapData['decor']['movingtree'] = [[308,777,null,0,[[302,762],[314,792]]],[326,985,null,0,[[342,963],[310,1007]]],[521,950,null,0,[[510,971],[548,957],[527,933],[497,937]]],[675,1144,null,0,[[684,1121],[665,1166]]]];
		break;
	case 45:
		$mapData['decor']['firesnake'] = [[854,1070,null,0,385],[875,1077,null,0,370],[1102,1101,null,0,540],[1091,1126,null,0,505]];
		$mapData['decor']['pokey'] = [[607,961,null,null,[17,0],[0,0.05]],[592,717,null,null,[20,10],[1,-0.04]],[618,649,null,null,[20,10],[1,-0.04]],[619,555,null,null,[17,0],[2,0.05]]];
		break;
	case 48:
		$trucks = [[1389,933,null,0,0,0,3],[1000,803,null,0,0,0,16],[822,733,null,0,0,0,24],[682,1076,null,0,0,0,32],[737,1390,null,0,0,0,42],[874,1087,null,0,0,0,52],[690,1239,null,0,0,1,10],[564,1225,null,0,0,1,24],[599,910,null,0,0,1,30],[1033,575,null,0,0,1,33],[1242,756,null,0,0,1,48]];
		foreach ($trucks as $i=>$truck) {
			for ($j=2;$j<7;$j++)
				$mapData['decor']['truck'][$i][$j] = $truck[$j];
		}
		break;
	case 51:
		$mapData['decor']['goomba'] = [[1398,954,null,0,0],[1390,1040,null,0,0],[1346,1140,null,0,0.8]];
		$mapData['decor']['fireplant'] = [[884,1220,null,0,0.8],[804,1300,null,0,-0.8]];
		break;
	case 52:
		$mapData['decor']['firebar'] = [[959,528,null,0,[[959,506,0,0],[959,550,0,0]],0,1,0],[989,528,null,0,[[989,506,0,0],[989,550,0,0]],1,1,0]];
		foreach ($mapData['decor']['billball'] as &$decorData) {
			$decorData[] = null;
			$decorData[] = null;
			$decorData[] = 90;
		}
		unset($decorData);
		break;
	}
}
header('Content-Type: application/json');
echo json_encode($baseData);
mysql_close();
?>