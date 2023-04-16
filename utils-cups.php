<?php
require_once('circuitPrefix.php');
function printCupCircuit(&$circuit, $options=array()) {
    global $language;
    $circuitnb = isset($options['nb']) ? $options['nb'] : '';
    $isCup = count($circuit['srcs']) > 1;
    ?>
    <tr id="circuit<?php echo $circuit['id']; ?>" data-id="<?php echo $circuit['id']; ?>" onclick="selectCircuit(this)">
        <td class="td-preview<?php if ($isCup) echo ' td-preview-cup'; ?>" <?php
        if (isset($circuit['icon'])) {
            $allMapSrcs = $circuit['icon'];
            foreach ($allMapSrcs as $j=>$jMapSrc)
                $allMapSrcs[$j] = "url('images/creation_icons/$jMapSrc')";
            echo ' style="background-image:'.implode(',',$allMapSrcs).'"';
        }
        else
            echo ' data-cicon="'.$circuit['cicon'].'"';
        ?> onclick="previewImg(event,<?php echo htmlspecialchars(json_encode($circuit['srcs'])); ?>)"></td>
        <td class="td-name"><em><?php echo $circuitnb; ?></em><?php echo ($circuit['nom'] ? escapeUtf8($circuit['nom']):($language ? 'Untitled':'Sans titre')); ?></td>
        <td class="td-access">&rarr; <a href="<?php echo $circuit['href']; ?>" target="_blank" onclick="event.stopPropagation()"><?php echo $language ? 'Access':'Accéder'; ?></a></td>
    </tr>
    <?php
}

function printCollabImportPopup($type, $mode) {
    global $language;
    switch ($type) {
    case 'cup':
        $Circuit = $language ? 'cup' : 'coupe';
        $aCircuit = $language ? 'a cup' : 'une coupe';
        $ofCircuit = $language ? 'of the cup' : 'de la coupe';
        $placeholderType = 'mkcups';
        $placeholderId = 1+$mode;
        break;
    case 'arena':
        $Circuit = $language ? "arena" : "arène";
        $aCircuit = $language ? "an arena" : "une arène";
        $ofCircuit = $language ? "of the arena" : "de l'arène";
        $placeholderType = $mode ? 'arenes' : 'mkcircuits';
        $placeholderId = 226;
        break;
    default:
        $Circuit = $language ? 'circuit' : 'circuit';
        $aCircuit = $language ? 'a circuit' : 'un circuit';
        $ofCircuit = $language ? 'of the circuit' : 'du circuit';
        $placeholderType = $mode ? 'circuits' : 'mkcircuits';
        $placeholderId = 42;
    }
    ?>
    <div id="collab-popup" class="editor-mask editor-mask-dark" onclick="closeCollabImportPopup()">
        <div class="editor-mask-content" onclick="event.stopPropagation()">
            <h2><?php echo $language ? "Import $aCircuit of another member" : "Importer $aCircuit d'un autre membre"; ?></h2>
            <div>
            <?php
            if ($language) {
                ?>
                Enter the <?php echo $Circuit ?>'s collaboration link here.<br />
                To get this link, the <?php echo $Circuit ?> owner will simply need
                to click on &quot;Collaborate&quot; at the bottom-left of the <?php echo $Circuit ?> page.
                <?php
            }
            else {
                ?>
                Saisissez ici le lien de collaboration <?php echo $ofCircuit; ?>.<br />
                Pour obtenir ce lien, le propriétaire <?php echo $ofCircuit; ?> devra simplement
                cliquer sur &quot;Collaborer&quot; en bas à gauche de la page <?php echo $ofCircuit; ?>.
                <?php
            }
            ?>
            </div>
            <form onsubmit="importCollabTrack(event)">
                <input type="url" name="collablink" placeholder="<?php
                $collab = array(
                    'type' => $placeholderType,
                    'creation_id' => $placeholderId,
                    'secret' => 'y-vf-erny_2401_pbasvezrq'
                );
                echo getCollabUrl($collab);
                ?>" required="required" />
                <input type="submit" value="Ok" />
            </form>
        </div>
    </div>
    <?php
}
function getTrackPayloads($options) {
    global $isCup, $isMCup, $id, $nid, $edittingCircuit, $cName, $cPseudo, $cAuteur, $cDate, $cOptions, $cupIDs, $cupPayloads, $pNote, $pNotes, $clPayloadParams, $hthumbnail, $cShared, $cEditting, $infos, $NBCIRCUITS, $trackIDs, $circuitsData, $creationData;
    include('creation-entities.php');
    $sid = $options['sid'];
    $mode = $options['mode'];
    $creationEntities = &$CREATION_ENTITIES[$mode];
    $table = $creationEntities['table'];
    if (isset($_GET['mid'])) { // Existing multicup
        $id = intval($_GET['mid']);
        $nid = $id;
        $isCup = true;
        $isMCup = true;
        if ($getMCup = fetchCreationData('mkmcups', $id)) {
            $cName = $getMCup['name'];
            $infos['name'] = $cName;
            $cPseudo = $getMCup['auteur'];
            $cAuteur = $cPseudo;
            $pNote = $getMCup['note'];
            $pNotes = $getMCup['nbnotes'];
            $cDate = $getMCup['publication_date'];
            $cOptions = $getMCup['options'];
            $creationData = $getMCup;
            $cShared = true;
            $getCups = mysql_query('SELECT cup FROM `mkmcups_tracks` WHERE mcup="'. $id .'" ORDER BY ordering');
            $cupIDs = array();
            while ($getCup = mysql_fetch_array($getCups))
                $cupIDs[] = $getCup['cup'];
            addCircuitChallenges('mkmcups', $nid,$cName, $clPayloadParams);
        }
    }
    elseif (isset($_GET['cid'])) { // Existing cup
        $id = intval($_GET['cid']);
        $nid = $id;
        $isCup = true;
        if ($getCup = fetchCreationData('mkcups', $id)) {
            $cName = $getCup['name'];
            $infos['name'] = $cName;
            $cPseudo = $getCup['auteur'];
            $cAuteur = $cPseudo;
            $pNote = $getCup['note'];
            $pNotes = $getCup['nbnotes'];
            $cDate = $getCup['publication_date'];
            $creationData = $getCup;
            $cShared = true;
            for ($i=0;$i<4;$i++) {
                $tracksToFetch[$i] = array(
                    'id' => $getCup['circuit'. $i],
                    'mode' => $getCup['mode'],
                );
                $cupIDs[$i] = $getCup['circuit'. $i];
            }
            addCircuitChallenges('mkcups', $nid,$cName, $clPayloadParams);
        }
    }
    elseif (isset($_GET[$sid])) { // Existing track
        $id = intval($_GET[$sid]);
        $nid = $id;
        $tracksToFetch = array(array(
            'id' => $id,
            'mode' => $mode
        ));
        $hthumbnail = 'https://mkpc.malahieude.net/mappreview.php?id='.$id;
        $cShared = true;
    }
    elseif (isset($_GET['cid0']) && isset($_GET['cid1']) && isset($_GET['cid2']) && isset($_GET['cid3'])) { // Cup being created
        $isCup = true;
        if (isset($_GET['nid'])) { // Cup being edited
            $nid = intval($_GET['nid']);
            if ($getMain = fetchCreationData('mkcups', $nid, array('select' => 'c.auteur,c.note,c.nbnotes,c.publication_date'))) {
                $cName = $getMain['name'];
                $cPseudo = $getMain['auteur'];
                $cAuteur = $cPseudo;
                $pNote = $getMain['note'];
                $pNotes = $getMain['nbnotes'];
                $cDate = $getMain['publication_date'];
                $creationData = $getMain;
                $cShared = true;
                $cEditting = true;
                addCircuitChallenges('mkcups', $nid,$cName, $clPayloadParams);
            }
        }
        else
            $cPseudo = isset($_COOKIE['mkauteur']) ? $_COOKIE['mkauteur']:null;
        for ($i=0;$i<4;$i++)
            $cupIDs[$i] = intval($_GET['cid'. $i]);
        $edittingCircuit = true;
    }
    elseif (isset($_GET['mid0'])) { // Multicups being created
        $isCup = true;
        $isMCup = true;
        if (isset($_GET['nid'])) { // Multicups being edited
            $nid = intval($_GET['nid']);
            if ($getMain = fetchCreationData('mkmcups', $nid, array('select' => 'c.auteur,c.note,c.nbnotes,c.publication_date'))) {
                $cName = $getMain['name'];
                $cPseudo = $getMain['auteur'];
                $cAuteur = $cPseudo;
                $pNote = $getMain['note'];
                $pNotes = $getMain['nbnotes'];
                $cDate = $getMain['publication_date'];
                $creationData = $getMain;
                $cShared = true;
                $cEditting = true;
                addCircuitChallenges('mkmcups', $nid,$cName, $clPayloadParams);
            }
        }
        else {
            $cPseudo = isset($_COOKIE['mkauteur']) ? $_COOKIE['mkauteur']:null;
            $cShared = false;
        }
        for ($i=0;isset($_GET['mid'.$i])&&is_numeric($_GET['mid'.$i]);$i++)
            $cupIDs[$i] = intval($_GET['mid'.$i]);
        $cOptions = isset($_GET['opt']) ? json_decode(stripslashes($_GET['opt'])) : null;
        if ($cOptions) $cOptions = json_encode($cOptions);
        $edittingCircuit = true;
    }
    else { // Track being created
        $getMain = null;
        if (isset($_GET['nid'])) { // Track being edited
            $nid = intval($_GET['nid']);
            require_once('collabUtils.php');
            $requireOwner = !hasCollabGrants($table, $nid, $_GET['collab'], 'view');
            if ($getMain = mysql_fetch_array($creationEntities['fetch_tracks'](array('ids' => array($nid), 'mode' => $mode, 'require_owner' => $requireOwner)))) {
                $infos['id'] = $nid;
                $cName = $getMain['name'];
                $cPseudo = $getMain['auteur'];
                $cAuteur = $cPseudo;
                $pNote = $getMain['note'];
                $pNotes = $getMain['nbnotes'];
                $cDate = $getMain['publication_date'];
                addCircuitChallenges($table, $nid,$cName, $clPayloadParams);
            }
            else {
                mysql_close();
                exit;
            }
        }
        else
            $cPseudo = isset($_COOKIE['mkauteur']) ? $_COOKIE['mkauteur']:null;
        
        $creationEntities['get_track_from_params'](array(
            'infos' => &$infos,
            'mode' => $mode,
            'base' => $getMain
        ));
        $edittingCircuit = true;
    }
    $cupPayloads = array();
    if ($isMCup && !isset($tracksToFetch)) {
        $tracksToFetch = array();
        if (!empty($cupIDs)) {
            $cupsTracks = array();
            $cupById = array();
            $getAllCups = getCreationDataQuery(array(
                'table' => 'mkcups',
                'select' => 'c.id,c.mode,c.circuit0,c.circuit1,c.circuit2,c.circuit3',
                'where' => 'c.id IN ('. implode(',',$cupIDs) .')'
            ));
            while ($getCup = mysql_fetch_array($getAllCups)) {
                $cupTracks = array();
                for ($i=0;$i<4;$i++)
                    $cupTracks[] = $getCup['circuit'.$i];
                $cupsTracks[$getCup['id']] = $cupTracks;
                $cupById[$getCup['id']] = $getCup;
                addCircuitChallenges('mkcups', $getCup['id'],$getCup['name'], $clPayloadParams, false);
            }
            foreach ($cupIDs as $cupID) {
                if (isset($cupsTracks[$cupID])) {
                    foreach ($cupsTracks[$cupID] as $cupTrack) {
                        $tracksToFetch[] = array(
                            'id' => $cupTrack,
                            'mode' => $cupById[$cupID]['mode']
                        );
                    }
                }
                if (isset($cupById[$cupID])) {
                    $cupObj = $cupById[$cupID];
                    $cupPayloads[] = array(
                        'id' => $cupObj['id'],
                        'name' => $cupObj['name'],
                        'mode' => $cupObj['mode'],
                        'complete' => ($cupObj['mode'] % 2 > 0),
                        'battle' => ($cupObj['mode'] > 1),
                    );
                }
            }
        }
    }
    if (isset($tracksToFetch)) {
        $circuitsData = array();
        if (!empty($tracksToFetch)) {
            $trackIDsByMode = array();
            foreach ($tracksToFetch as $i=>$trackToFetch) {
                $trackMode = $trackToFetch['mode'];
                if (is_numeric($trackToFetch['id']))
                    $trackIDsByMode[$trackMode][] = $trackToFetch['id'];
            }
            $allTracks = array();
            foreach ($trackIDsByMode as $trackMode=>$trackIDs) {
                $getAllTracks = $CREATION_ENTITIES[$trackMode]['fetch_tracks'](array(   
                    'ids' => $trackIDs
                ));
                $allTracksForMode = array();
                while ($getMain = mysql_fetch_array($getAllTracks))
                    $allTracksForMode[$getMain['id']] = $getMain;
                $allTracks[$trackMode] = $allTracksForMode;
            }
            foreach ($tracksToFetch as $trackToFetch) {
                $trackID = $trackToFetch['id'];
                $trackMode = $trackToFetch['mode'];
                if (isset($allTracks[$trackMode][$trackID])) {
                    $getMain = $allTracks[$trackMode][$trackID];
                    $infos = array();
                    $infos['id'] = $trackID;
                    $infos['name'] = $getMain['name'];
                    $infos['note'] = $getMain['note'];
                    $infos['nbnotes'] = $getMain['nbnotes'];
                    $infos['auteur'] = $getMain['auteur'];
                    $infos['publication_date'] = $getMain['publication_date'];
                    $infos['mode'] = $trackMode;
                    $CREATION_ENTITIES[$trackMode]['fetch_track_extras'](array(
                        'id' => $trackID,
                        'base' => $getMain,
                        'infos' => &$infos
                    ));
                    $circuitsData[] = $infos;
                    addCircuitChallenges($table, $trackID,$infos['name'], $clPayloadParams, !$isCup);
                }
            }
        }
        if (!$isCup && isset($circuitsData[0])) {
            $infos = $circuitsData[0];
            $cName = $infos['name'];
            $cAuteur = $infos['auteur'];
            $pNote = $infos['note'];
            $pNotes = $infos['nbnotes'];
            $cDate = $infos['publication_date'];
            $creationData = $circuitsData[0];
            $cShared = (null !== $cName);
            if ($cShared)
                $cPseudo = $cAuteur;
            else
                $cPseudo = isset($_COOKIE['mkauteur']) ? $_COOKIE['mkauteur']:null;
        }
    }
    elseif (!empty($infos))
        $circuitsData = Array($infos);
    else {
        mysql_close();
        exit;
    }
    if ($isCup)
        $infos = Array();
    if (empty($circuitsData)) {
        mysql_close();
        exit;
    }
    $NBCIRCUITS = 0;
    foreach ($circuitsData as $circuitData) {
        if ($circuitData['mode'] < 2)
            $NBCIRCUITS++;
    }
    addClChallenges($nid, $clPayloadParams);
}
function printCircuitsData() {
    global $circuitsData;
    include('creation-entities.php');
    foreach ($circuitsData as $c => $circuitData) {
        if ($c) echo ',';
        echo '"map'. ($c+1) .'":';
        $page = $CREATION_ENTITIES[$circuitData['mode']]['page'];
        include("mk/$page.php");
        $printCircuitData($circuitData);
        echo '';
    }
}
function getCupPage(&$mode) {
	switch ($mode) {
	case 1:
		return 'map';
	case 2:
		return 'arena';
	case 3:
		return 'battle';
	default:
		return 'circuit';
	}
}
function fetchCreationData($table,$id, $options=array()) {
    return mysql_fetch_array(getCreationDataQuery(array_merge(
        array('table' => $table, 'where' => 'c.id="'. $id .'"'),
        $options
    )));
}
function getCreationDataQuery($options) {
    global $language;
    $table = $options['table'];
    $columns = isset($options['select']) ? $options['select'] : 'c.*';
    $join = isset($options['join']) ? $options['join'] : '';
    $where = $options['where'];
    $orderBy = isset($options['order']) ? (' ORDER BY '. $options['order']) : '';
    $nameCol = $language ? 'name_en' : 'name_fr';
    return mysql_query('SELECT '.$columns.',IFNULL(s.'.$nameCol.',c.nom) AS name FROM `'. $table .'` c LEFT JOIN `mktracksettings` s ON s.type="'. $table .'" AND s.circuit=c.id '.$join.' WHERE '.$where . $orderBy);
}