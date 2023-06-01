<?php
require_once('touch.php');
require_once('cache_creations.php');
$cMCups = 'SELECT *,0 AS category FROM mkmcups WHERE mode=1';
$sMCups = 'SELECT *,1 AS category FROM mkmcups WHERE mode=0';
$cCups = 'SELECT *,2 AS category FROM mkcups WHERE mode=1';
$sCups = 'SELECT *,3 AS category FROM mkcups WHERE mode=0';
$cCircuits = 'SELECT *,ID AS id,4 AS category FROM circuits WHERE nom IS NOT NULL';
$sCircuits = 'SELECT *,5 AS category FROM mkcircuits WHERE !type';
$cArenes = 'SELECT *,ID AS id,6 AS category FROM arenes WHERE nom IS NOT NULL';
$sArenes = 'SELECT *,7 AS category FROM mkcircuits WHERE type';
$caCups = 'SELECT *,8 AS category FROM mkcups WHERE mode=3';
$saCups = 'SELECT *,9 AS category FROM mkcups WHERE mode=2';
$caMCups = 'SELECT *,10 AS category FROM mkmcups WHERE mode=3';
$saMCups = 'SELECT *,11 AS category FROM mkmcups WHERE mode=2';
$aCircuits = array($cMCups,$sMCups,$cCups,$sCups,$cCircuits,$sCircuits,$cArenes,$sArenes,$caCups,$saCups,$caMCups,$saMCups);
function listRaces($sql) {
	$liste = Array();
	$i = 0;
	$getCircuits = mysql_query($sql);
	while ($circuit = mysql_fetch_array($getCircuits)) {
		$liste[$i] = $circuit;
		$i++;
	}
	return $liste;
}
function toSQLSearch($search) {
	$search = str_replace('"', '""', $search);
	$search = str_replace('\\', '\\\\\\\\', $search);
	$search = str_replace('_', '\\_', $search);
	$search = str_replace('%', '\\%', $search);
	$search = preg_replace('#\?+#','%', $search);
	$search = '%'. $search .'%';
	return $search;
}
function toSQLFilter($sql, $params) {
	if (!empty($params['id']))
		$sql .= ' AND id='. intval($params['id']);
	if (!empty($params['ids'])) {
		$idsArray = array();
		foreach ($params['ids'] as $pId)
			$idsArray[] = intval($pId);
		$sql .= ' AND id IN ('. implode(',', $idsArray) .')';
	}
	if (!empty($params['nom']))
		$sql .= ' AND nom LIKE "'. toSQLSearch($params['nom']) .'"';
	if (!empty($params['auteur']))
		$sql .= ' AND auteur LIKE "'. toSQLSearch($params['auteur']) .'"';
	if (!empty($params['prefix']))
		$sql .= ' AND id IN (SELECT circuit FROM mktracksettings WHERE type="'. getCircuitTable($params['type']) .'" AND prefix="'. $params['prefix'] .'")';
	if (!empty($params['pids'])) {
		$pids = $params['pids'];
		$sql .= ' AND identifiant="'.$pids[0].'" AND identifiant2="'.$pids[1].'" AND identifiant3="'.$pids[2].'" AND identifiant4="'.$pids[3].'"';
	}
	return $sql;
}
function toSQLSort($sql, $params) {
	if (!isset($params['tri'])) $params['tri'] = 0;
	$desc = isset($params['reverse']) ? 'ASC':'DESC';
	$asc = isset($params['reverse']) ? 'DESC':'ASC';
	switch ($params['tri']) {
	case 0 :
		$sort = "publication_date $desc, id $desc";
		break;
	case 1 :
		$sort = "pscore $desc, id $desc";
		break;
	case 2 :
		$sort = "tscore $desc, id $desc";
		break;
	case 3 :
		$sort = "nbcomments $desc, id $desc";
		break;
	default :
		$sort = "id $desc";
	}
	return toSQLFilter($sql, $params) .' ORDER BY '. $sort;
}
function floatCmp($a,$b) {
    return ($a > $b) - ($a < $b);
}
function sortCmp0($res1,$res2) {
	if ($res1['publication_date'] === null) $res1['publication_date'] = '2000-01-01';
	if ($res2['publication_date'] === null) $res2['publication_date'] = '2000-01-01';
	$res = strtotime($res2['publication_date'])-strtotime($res1['publication_date']);
	if ($res) return $res;
	return $res2['id']-$res1['id'];
}
function sortCmp1($res1,$res2) {
	$res = floatCmp($res2['pscore'],$res1['pscore']);
	if ($res) return $res;
	$res = $res1['category']-$res2['category'];
	if ($res) return $res;
	return $res2['id']-$res1['id'];
}
function sortCmp2($res1,$res2) {
	$res = floatCmp($res2['tscore'],$res1['tscore']);
	if ($res) return $res;
	$res = $res1['category']-$res2['category'];
	if ($res) return $res;
	return $res2['id']-$res1['id'];
}
function sortCmp3($res1,$res2) {
	$res = $res2['nbcomments']-$res1['nbcomments'];
	if ($res) return $res;
	$res = $res1['category']-$res2['category'];
	if ($res) return $res;
	return $res2['id']-$res1['id'];
}
function nextRaces($sql,$begin,$end,$params) {
	return listRaces(toSQLSort($sql,$params) .' LIMIT '. $begin.','.($end-$begin));
}
function scoreCmp($res1,$res2) {
	return floatCmp($res1['score'],$res2['score']);
}
function getTracksToLoad($page,$nbByType,$weightsByType,$maxCircuits) {
	$tracksToLoad = array();
	$nbLoaded = 0;
	$nbTracksTotal = array_sum($nbByType);
	$x = $page*$maxCircuits/$nbTracksTotal;
	$weightedNbByType = array();
	$weightedNbTotal = 0;
	foreach ($nbByType as $i=>$nb) {
		$weightedNbByType[$i] = $nb*pow($x,1/$weightsByType[$i]);
		$weightedNbTotal += $weightedNbByType[$i];
	}
	if (!$weightedNbTotal) $weightedNbTotal = 1;
	foreach ($weightedNbByType as $i=>$weightedNb) {
		$tracksToLoad[$i] = array(
			'i' => $i,
			'nb' => $maxCircuits*$page*$weightedNb/$weightedNbTotal,
			'max' => $nbByType[$i]
		);
		$tracksToLoad[$i]['nb_int'] = ceil($tracksToLoad[$i]['nb']);
		$nbLoaded += $tracksToLoad[$i]['nb_int'];
	}
	$nbToLoad = $page*$maxCircuits;
	if ($nbLoaded > $nbToLoad) {
		foreach ($tracksToLoad as &$track)
			$track['score'] = $track['nb']-$track['nb_int'];
		unset($track);
		usort($tracksToLoad,'scoreCmp');
		foreach ($tracksToLoad as &$track) {
			$track['nb_int']--;
			$nbLoaded--;
			if ($nbLoaded <= $nbToLoad)
				break;
		}
		unset($track);
	}
	$res = array_fill(0, count($nbByType), null);
	foreach ($tracksToLoad as &$track)
		$res[$track['i']] = $track['nb_int'];
	return $res;
}
function countTracksByType($aCircuits,&$params) {
	$pType = $params['type'];
	foreach ($aCircuits as $i=>$aCircuit) {
		if (!$pType)
			$params['type'] = $i;
		$nb = countRows($aCircuit,$params);
		$nbByType[] = $nb;
	}
	$params['type'] = $pType;
	return $nbByType;
}
function listCreations($page,$nbByType,$weightsByType,$aCircuits,$params=array()) {
	if ($nbByType == null)
		$nbByType = countTracksByType($aCircuits,$params);
	if ($weightsByType === null)
		$weightsByType = array_fill(0, count($nbByType), 1);
	$nbTracksTotal = array_sum($nbByType);
	$creationsList = array(
		'tracks' => array(),
		'cups' => array(),
		'mcups' => array(),
		'nb' => $nbTracksTotal
	);
	if ($nbTracksTotal) {
		if (!isset($params['tri'])) $params['tri'] = 0;
		if (!isset($params['max_circuits'])) $params['max_circuits'] = $nbTracksTotal;
		$nbsToLoadBegin = getTracksToLoad($page-1,$nbByType,$weightsByType,$params['max_circuits']);
		$nbsToLoadEnd = getTracksToLoad($page,$nbByType,$weightsByType,$params['max_circuits']);
		$tri = $params['tri'];
		$pType = $params['type'];
		foreach ($aCircuits as $i=>$aCircuit) {
			if (!$pType)
				$params['type'] = $i;
			$aList = nextRaces($aCircuit,$nbsToLoadBegin[$i],$nbsToLoadEnd[$i],$params);
			$creationsList['tracks'] = array_merge($creationsList['tracks'], $aList);
			if (isset($aList[0])) {
				$aType = $aList[0]['category'];
				if (in_array($aType, array(0,1,10,11)))
					$creationsList['mcups'] = array_merge($creationsList['mcups'], $aList);
				elseif (in_array($aType, array(2,3,8,9)))
					$creationsList['cups'] = array_merge($creationsList['cups'], $aList);
			}
		}
		$params['type'] = $pType;
		usort($creationsList['tracks'],"sortCmp$tri");
		if (isset($params['reverse']))
			$creationsList['tracks'] = array_reverse($creationsList['tracks']);
	}
	addCircuitsData($creationsList, $params);
	return $creationsList['tracks'];
}
function countRows($sql,&$params) {
	$query = toSQLSort($sql,$params);
	$countQuery = preg_replace('#^SELECT.+? FROM #', 'SELECT COUNT(*) AS nb FROM ', $query);
	$count = mysql_fetch_array(mysql_query($countQuery));
	return $count['nb'];
}
function escape($str) {
	return str_replace('%u', '\\u', json_encode($str));
}
function addCircuitsData(&$creationsList, &$params) {
	global $language;
	$lCups = array();
	foreach ($creationsList['cups'] as $cup) {
		$lCup = array();
		for ($i=0;$i<4;$i++)
			$lCup[] = array('id' => $cup['circuit'.$i]);
		$lCups[$cup['id']] = array(
			'tracks' => $lCup,
			'mode' => $cup['mode'],
		);
	}
	$lmCups = $creationsList['mcups'];
	$mCupIds = array_fill(0, count($lmCups), 0);
	foreach ($lmCups as $i => $mCup)
		$mCupIds[$i] = $mCup['id'];
	$mCupIdsStr = implode(',',$mCupIds);
	$mCups = array();
	if ($mCupIdsStr) {
		$getCups = mysql_query('SELECT c.*,t.mcup FROM `mkmcups_tracks` t INNER JOIN mkcups c ON t.cup=c.id WHERE t.mcup IN ('.$mCupIdsStr.') ORDER BY t.mcup,t.ordering');
		while ($cup = mysql_fetch_array($getCups)) {
			$mCupId = $cup['mcup'];
			if (!isset($mCups[$mCupId])) {
				$mCups[$mCupId] = array(
					'tracks' => array(),
					'mode' => $cup['mode'],
				);
			}
			for ($i=0;$i<4;$i++)
				$mCups[$mCupId]['tracks'][] = array('id' => $cup['circuit'.$i]);
		}
	}
	$circuitTypes = array();
	$circuitByType = array();
	foreach ($creationsList['tracks'] as &$track) {
		$cType = $track['category'];
		$cTable = getCircuitTable($cType);
		$cId = intval($track['id']);
		$circuitTypes[] = "('$cTable',$cId)";
		$circuitByType[$cTable][$cId] = &$track;
	}
	unset($track);
	if (!empty($lCups)) {
		include('creation-entities.php');
		foreach ($lCups as $cupId => &$cup) {
			$cTable = $CREATION_ENTITIES[$cup['mode']]['table'];
			foreach ($cup['tracks'] as $i => &$track) {
				$trackId = $track['id'];
				if (isset($circuitByType[$cTable][$trackId]))
					$cup['tracks'][$i] = &$circuitByType[$cTable][$trackId];
				else {
					$circuitTypes[] = "('$cTable',".$trackId.")";
					$circuitByType[$cTable][$trackId] = &$track;
				}
			}
			unset($track);
		}
		unset($cup);
	}
	$circuitTypesStr = implode(',',$circuitTypes);
	if ($circuitTypesStr) {
		$nameCol = $language ? 'name_en' : 'name_fr';
		$getTrackSettings = mysql_query('SELECT type,circuit,'.$nameCol.' AS name,thumbnail,prefix FROM `mktracksettings` WHERE (type,circuit) IN ('.$circuitTypesStr.')');
		while ($trackSettings = mysql_fetch_array($getTrackSettings)) {
			$cTable = $trackSettings['type'];
			$cId = $trackSettings['circuit'];
			if ($trackSettings['name'])
				$circuitByType[$cTable][$cId]['nom'] = $trackSettings['name'];
			if ($trackSettings['thumbnail'] && !isset($params['no_thumbnail']))
				$circuitByType[$cTable][$cId]['thumbnail'] = $trackSettings['thumbnail'];
			if ($trackSettings['prefix'])
				$circuitByType[$cTable][$cId]['prefix'] = $trackSettings['prefix'];
		}
	}
	foreach ($creationsList['tracks'] as &$track)
		addCircuitData($track,$lCups,$mCups);
}
function addCircuitData(&$circuit,&$lCups,&$mCups) {
	$linkBg = '';
	$linkPreview = array();
	$linksCached = array();
	$linkUrl = '';
	$cId = $circuit['id'];
	$cType = $circuit['category'];
	switch ($cType) {
		case 0 :
			$linkUrl = 'map.php?mid='. $cId;
			break;
		case 1 :
			$linkUrl = 'circuit.php?mid='. $cId;
			break;
		case 2 :
			$linkUrl = 'map.php?cid='. $cId;
			break;
		case 3 :
			$linkUrl = 'circuit.php?cid='. $cId;
			break;
		case 4 :
			$linkUrl = 'map.php?i='. $cId;
			break;
		case 5 :
			$linkUrl = 'circuit.php?id='. $cId;
			break;
		case 6 :
			$linkUrl = 'battle.php?i='. $cId;
			break;
		case 7 :
			$linkUrl = 'arena.php?id='. $cId;
			break;
		case 8 :
			$linkUrl = 'battle.php?cid='. $cId;
			break;
		case 9 :
			$linkUrl = 'arena.php?cid='. $cId;
			break;
		case 10 :
			$linkUrl = 'battle.php?mid='. $cId;
			break;
		case 11 :
			$linkUrl = 'arena.php?mid='. $cId;
			break;
	}
	switch ($cType) {
	case 0 :
	case 1 :
		if ($cType == 0)
			$baseUrl = 'racepreview.php?id=';
		else
			$baseUrl = 'mappreview.php?id=';
		$linkBg .= 'trackicon.php?id='. $cId .'&type=4';
		$linksCached[] = 'mcuppreview'. $cId .'.png';
		foreach ($mCups[$cId]['tracks'] as $lTrack)
			$linkPreview[] = $baseUrl . $lTrack['id'];
		break;
	case 2 :
	case 3 :
		if ($cType == 2) {
			$baseUrl = 'racepreview.php?id=';
			$baseCache = 'racepreview';
		}
		else {
			$baseUrl = 'mappreview.php?id=';
			$baseCache = 'mappreview';
		}
		$lTracks = $lCups[$cId]['tracks'];
		foreach ($lTracks as $i=>$lTrack) {
			$lId = $lTrack['id'];
			$linkPreview[] = $baseUrl . $lId;
			if (isset($lTrack['thumbnail'])) {
				$linkCached = 'uploads/'. $lTrack['thumbnail'];
				$linkIcon = cachePathRelative($linkCached);
			}
			else {
				$linkCached = $baseCache . $lId .'.png';
				$linkIcon = 'trackicon.php?id='. $lId .'&type='. (3-$cType);
			}
			$linkBg .= ($i?',':'') . $linkIcon;
			$linksCached[] = $linkCached;
		}
		break;
	case 4 :
		$linkBg = 'trackicon.php?id='. $cId .'&type=1';
		$linkPreview[] = 'racepreview.php?id='. $cId;
		$linksCached[] = 'racepreview' . $cId .'.png';
		break;
	case 6 :
		$linkBg = 'trackicon.php?id='. $cId .'&type=2';
		$linkPreview[] = 'coursepreview.php?id='. $cId;
		$linksCached[] = 'coursepreview' . $cId .'.png';
		break;
	case 5 :
	case 7 :
		$linkBg = 'trackicon.php?id='. $cId .'&type=0';
		$linkPreview[] = 'mappreview.php?id='. $cId;
		$linksCached[] = 'mappreview' . $cId .'.png';
		break;
	case 8 :
	case 9 :
		if ($cType == 8) {
			$baseUrl = 'coursepreview.php?id=';
			$baseCache = 'coursepreview';
		}
		else {
			$baseUrl = 'mappreview.php?id=';
			$baseCache = 'mappreview';
		}
		$lTracks = $lCups[$cId]['tracks'];
		foreach ($lTracks as $i=>$lTrack) {
			$lId = $lTrack['id'];
			$linkPreview[] = $baseUrl . $lId;
			if (isset($lTrack['thumbnail'])) {
				$linkCached = 'uploads/'. $lTrack['thumbnail'];
				$linkIcon = cachePathRelative($linkCached);
			}
			else {
				$linkCached = $baseCache . $lId .'.png';
				$lType = ($cType == 8) ? 2 : 0;
				$linkIcon = 'trackicon.php?id='. $lId .'&type='.$lType;
			}
			$linkBg .= ($i?',':'') . $linkIcon;
			$linksCached[] = $linkCached;
		}
		break;
	case 10 :
	case 11 :
		if ($cType == 10)
			$baseUrl = 'coursepreview.php?id=';
		else
			$baseUrl = 'mappreview.php?id=';
		$linkBg .= 'trackicon.php?id='. $cId .'&type=4';
		$linksCached[] = 'mcuppreview'. $cId .'.png';
		foreach ($mCups[$cId]['tracks'] as $lTrack)
			$linkPreview[] = $baseUrl . $lTrack['id'];
		break;
	}
	$allCached = true;
	if (isset($circuit['thumbnail'])) {
		$linkBg = 'uploads/'.$circuit['thumbnail'];
		$linksCached = array($linkBg);
	}
	else {
		foreach ($linksCached as $link) {
			$filename = cachePath($link);
			if (file_exists($filename))
				touch_async($filename);
			else {
				$allCached = false;
				break;
			}
		}
	}
	$circuit['srcs'] = $linkPreview;
	$circuit['href'] = $linkUrl;
	if ($allCached) $circuit['icon'] = $linksCached;
	$circuit['cicon'] = $linkBg;
	return $circuit;
}
function circuitPayload(&$circuit) {
	return '{'.
		'"id":'.$circuit['id'].','.
		'"category":'.$circuit['category'].','.
		'"name":'.escape($circuit['nom']).','.
		'"author":'.escape($circuit['auteur']).','.
		(isset($circuit['prefix']) ? '"prefix":'.escape($circuit['prefix']).',':'').
		'"note":'.$circuit['note'].','.
		'"nbnotes":'.$circuit['nbnotes'].','.
		'"nbcomments":'.$circuit['nbcomments'].','.
		'"srcs":["'.implode('","',$circuit['srcs']).'"],'.
		'"href":"'.$circuit['href'].'",'.
		(isset($circuit['icon']) ? '"icon":["'.implode('","',$circuit['icon']).'"],':'').
		'"cicon":'.escape($circuit['cicon']).
	'}';
}
function getCircuitTable($cType) {
	switch ($cType) {
		case 0 :
		case 1 :
		case 10 :
		case 11 :
			return 'mkmcups';
		case 2 :
		case 3 :
		case 8 :
		case 9 :
			return 'mkcups';
		case 4 :
			return 'circuits';
		case 5 :
		case 7 :
			return 'mkcircuits';
		case 6 :
			return 'arenes';
	}
}
function printCircuits(&$creationsList) {
	foreach ($creationsList as $i=>$circuit) {
		if ($i)
			echo ',';
		echo circuitPayload($circuit);
	}
}
?>
