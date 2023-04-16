<?php
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

function printCollabImportPopup($type, $mode, $isBattle) {
    global $language;
    switch ($type) {
    case 'cup':
        $Circuit = $language ? 'cup' : 'coupe';
        $aCircuit = $language ? 'a cup' : 'une coupe';
        $ofCircuit = $language ? 'of the cup' : 'de la coupe';
        $placeholderType = 'mkcups';
        $placeholderId = 1+$mode + $isBattle*2;
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