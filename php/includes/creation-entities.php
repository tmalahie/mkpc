<?php
$CREATION_ENTITIES = array(
    array(
        'page' => 'circuit',
        'table' => 'mkcircuits',
        'get_track_from_params' => function($options) {
            global $lettres, $nbLettres;
            $infos = &$options['infos'];
            for ($i=0;$i<36;$i++)
                $infos["p$i"] = (isset($_GET["p$i"])) ? intval($_GET["p$i"]) : 11;
            $infos['map'] = (isset($_GET["map"])) ? intval($_GET["map"]) : 1;
            $infos['laps'] = (isset($_GET["nl"])) ? intval($_GET["nl"]) : 3;
            $infos['name'] = '';
            for ($i=0;$i<$nbLettres;$i++) {
                $lettre = $lettres[$i];
                $prefixes = getLetterPrefixes($lettre,$infos['map']);
                for ($k=0;$k<$prefixes;$k++) {
                    $prefix = getLetterPrefix($lettre,$k);
                    for ($j=0;isset($_GET[$prefix.$j]);$j++)
                        $infos[$prefix.$j] = $_GET[$prefix.$j];
                }
            }
        },
        'fetch_tracks' => function($options) {
            global $identifiants;
            $ids = $options['ids'];
            $requireOwner = !empty($options['require_owner']);
            $idsString = implode(',', $ids);
            return getCreationDataQuery(array(
                'table' => 'mkcircuits',
                'select' => 'c.id,c.map,c.laps,c.nom AS name0,c.auteur,c.note,c.nbnotes,c.publication_date,s.prefix,s.thumbnail',
                'where' => 'c.id IN ('. $idsString .') AND !c.type'. ($requireOwner ? (' AND c.identifiant="'. $identifiants[0] .'" AND c.identifiant2="'. $identifiants[1] .'" AND c.identifiant3="'. $identifiants[2] .'" AND c.identifiant4="'. $identifiants[3] .'"') : ''),
            ));
        },
        'fetch_track_extras' => function($options) {
            global $lettres, $nbLettres;
            $trackID = $options['id'];
            $infos = &$options['infos'];
            $base = &$options['base'];
            $infos['map'] = $base['map'];
            $infos['laps'] = $base['laps'];
            $infos['thumbnail'] = "mappreview.php?id=$trackID";
            $infos['icon'] = "trackicon.php?id=$trackID&type=0";
            $pieces = mysql_query('SELECT * FROM `mkp` WHERE circuit="'.$trackID.'"');
            while ($piece = mysql_fetch_array($pieces))
                $infos['p'.$piece['id']] = $piece['piece'];
            for ($j=0;$j<$nbLettres;$j++) {
                $lettre = $lettres[$j];
                $getInfos = mysql_query('SELECT * FROM `mk'.$lettre.'` WHERE circuit="'.$trackID.'"');
                $incs = array();
                while ($info=mysql_fetch_array($getInfos)) {
                    $prefix = getLetterPrefixD($lettre,$info);
                    if (!isset($incs[$prefix])) $incs[$prefix] = 0;
                    $infos[$prefix.$incs[$prefix]] = $info['x'].','.$info['y'];
                    $incs[$prefix]++;
                }
            }
        },
        'get_share_params' => function() {
            global $nid, $infos, $lettres, $nbLettres, $isBattle, $clId;
            $shareParams = array(
                'map' => $infos['map']
            );
            if ($isBattle) $shareParams['battle'] = '';
            for ($i=0;$i<36;$i++)
                $shareParams['p'.$i] = $infos['p'.$i];
            if ($isBattle) {
                for ($i=0;$i<8;$i++) {
                    $shareParams['r'.$i] = $infos['r'.$i];
                    $shareParams['s'.$i] = $infos['s'.$i];
                }
            }
            else
                $shareParams['nl'] = $infos['laps'];
            for ($i=0;$i<$nbLettres;$i++) {
                $l = $lettres[$i];
                $prefixes = getLetterPrefixes($l,$infos['map']);
                for ($k=0;$k<$prefixes;$k++) {
                    $prefix = getLetterPrefix($l,$k);
                    for ($j=0;isset($infos[$prefix.$j]);$j++)
                        $shareParams[$prefix.$j] = $infos[$prefix.$j];
                }
            }
            
            $res = array(
                'send' => array(
                    'endpoint' => 'saveCreation.php',
                    'params' => $shareParams
                ),
                'edit' => array(
                    'page' => $isBattle ? 'arene.php' : 'create.php'
                )
            );

            if (isset($nid)) {
                $onUnshare = 'document.location.href = "?map='.$infos['map'];
                for ($i=0;$i<36;$i++)
                    $onUnshare .= '&p'.$i.'='.$infos['p'.$i];
                if ($isBattle) {
                    for ($i=0;$i<8;$i++)
                        $onUnshare .= '&r'.$i.'='.$infos['r'.$i].'&s'.$i.'='.$infos['s'.$i];
                }
                else
                    $onUnshare .= '&nl='.$infos['laps'];
                for ($i=0;$i<$nbLettres;$i++) {
                    $l = $lettres[$i];
                    $prefixes = getLetterPrefixes($l,$infos['map']);
                    for ($k=0;$k<$prefixes;$k++) {
                        $prefix = getLetterPrefix($l,$k);
                        for ($j=0;isset($infos[$prefix.$j]);$j++)
                            $onUnshare .= '&'.$prefix.$j.'='.$infos[$prefix.$j];
                    }
                }
                if ($clId) $onUnshare .= '&cl='.$clId;
                $onUnshare .= '";';

                $res['remove'] = array(
                    'endpoint' => 'supprCreation.php',
                    'onSuccess' => $onUnshare
                );
            }

            return $res;
        }
    ),
    array(
        'page' => 'map',
        'table' => 'circuits',
        'get_track_from_params' => function($options) use (&$CREATION_ENTITIES) {
            if ($options['base'])
                $CREATION_ENTITIES[1]['fetch_track_extras']($options);
        },
        'fetch_tracks' => function($options) {
            global $identifiants;
            $ids = $options['ids'];
            $requireOwner = !empty($options['require_owner']);
            $idsString = implode(',', $ids);
            return getCreationDataQuery(array(
                'table' => 'circuits',
                'join' => 'LEFT JOIN `circuits_data` d ON c.id=d.id',
                'select' => 'c.*,c.nom AS name0,c.id,d.data,s.prefix,s.thumbnail',
                'where' => 'c.id IN ('. $idsString .')'. ($requireOwner ? (' AND c.identifiant="'. $identifiants[0] .'" AND c.identifiant2="'. $identifiants[1] .'" AND c.identifiant3="'. $identifiants[2] .'" AND c.identifiant4="'. $identifiants[3] .'"') : '')
            ));
        },
        'fetch_track_extras' => function($options) {
            foreach ($options['base'] as $key => $value)
                $options['infos'][$key] = $value;
            $isBattle = ($options['infos']['mode'] === 3);
            $trackID = $options['id'];
            $options['infos']['thumbnail'] = ($isBattle?'coursepreview':'racepreview').'.php?id='.$trackID;
            $options['infos']['icon'] = 'trackicon.php?id='.$trackID.'&type='.($isBattle?2:1);
        },
        'get_share_params' => function() {
            global $cShared, $isBattle;
            
            $res = array(
                'send' => array(
                    'endpoint' => $isBattle ? 'saveBattle.php' : 'saveDraw.php'
                ),
                'edit' => array(
                    'page' => $isBattle ? 'course.php' : 'draw.php'
                )
            );

            if ($cShared) {
                $onUnshare = 'location.reload();';

                $res['remove'] = array(
                    'endpoint' => $isBattle ? 'supprBattle.php' : 'supprDraw.php',
                    'onSuccess' => $onUnshare
                );
            }

            return $res;
        }
    ),
    array(
        'page' => 'arena',
        'table' => 'mkcircuits',
        'get_track_from_params' => function($options) {
            global $lettres, $nbLettres;
            $infos = &$options['infos'];
            for ($i=0;$i<36;$i++)
                $infos["p$i"] = (isset($_GET["p$i"])) ? intval($_GET["p$i"]) : 11;
            for ($i=0;$i<8;$i++) {
                $infos["r$i"] = isset($_GET["r$i"]) ? intval($_GET["r$i"]) : 0;
                $infos["s$i"] = isset($_GET["s$i"]) ? intval($_GET["s$i"]) : 0;
            }
            $infos['map'] = (isset($_GET["map"])) ? intval($_GET["map"]) : 1;
            $infos['name'] = '';
            for ($i=0;$i<$nbLettres;$i++) {
                $lettre = $lettres[$i];
                $prefixes = getLetterPrefixes($lettre,$infos['map']);
                for ($k=0;$k<$prefixes;$k++) {
                    $prefix = getLetterPrefix($lettre,$k);
                    for ($j=0;isset($_GET[$prefix.$j]);$j++)
                        $infos[$prefix.$j] = $_GET[$prefix.$j];
                }
            }
        },
        'fetch_tracks' => function($options) {
            global $identifiants;
            $ids = $options['ids'];
            $requireOwner = !empty($options['require_owner']);
            $idsString = implode(',', $ids);
            return getCreationDataQuery(array(
                'table' => 'mkcircuits',
                'select' => 'c.id,c.map,c.nom AS name0,c.auteur,c.note,c.nbnotes,c.publication_date,s.prefix,s.thumbnail',
                'where' => 'c.id IN ('. $idsString .') AND c.type'. ($requireOwner ? (' AND c.identifiant="'. $identifiants[0] .'" AND c.identifiant2="'. $identifiants[1] .'" AND c.identifiant3="'. $identifiants[2] .'" AND c.identifiant4="'. $identifiants[3] .'"') : '')
            ));
        },
        'fetch_track_extras' => function($options) {
            global $lettres, $nbLettres;
            $trackID = $options['id'];
            $infos = &$options['infos'];
            $base = &$options['base'];
            $infos['map'] = $base['map'];
            $infos['thumbnail'] = "mappreview.php?id=$trackID";
            $infos['icon'] = "trackicon.php?id=$trackID&type=0";
            $pieces = mysql_query('SELECT * FROM `mkp` WHERE circuit="'.$trackID.'"');
            while ($piece = mysql_fetch_array($pieces))
                $infos['p'.$piece['id']] = $piece['piece'];
            $positions = mysql_query('SELECT * FROM `mkr` WHERE circuit="'.$trackID.'"');
            while ($position = mysql_fetch_array($positions)) {
                $infos['s'.$position['id']] = $position['s'];
                $infos['r'.$position['id']] = $position['r'];
            }
            for ($j=0;$j<$nbLettres;$j++) {
                $lettre = $lettres[$j];
                $getInfos = mysql_query('SELECT * FROM `mk'.$lettre.'` WHERE circuit="'.$trackID.'"');
                $incs = array();
                while ($info=mysql_fetch_array($getInfos)) {
                    $prefix = getLetterPrefixD($lettre,$info);
                    if (!isset($incs[$prefix])) $incs[$prefix] = 0;
                    $infos[$prefix.$incs[$prefix]] = $info['x'].','.$info['y'];
                    $incs[$prefix]++;
                }
            }
        },
        'get_share_params' => function() use (&$CREATION_ENTITIES) {
            return $CREATION_ENTITIES[0]['get_share_params']();
        }
    ),
    array(
        'page' => 'battle',
        'table' => 'arenes',
        'get_track_from_params' => function($options) use (&$CREATION_ENTITIES) {
            return $CREATION_ENTITIES[1]['get_track_from_params']($options);
        },
        'fetch_tracks' => function($options) {
            global $identifiants;
            $ids = $options['ids'];
            $requireOwner = !empty($options['require_owner']);
            $idsString = implode(',', $ids);
            return getCreationDataQuery(array(
                'table' => 'arenes',
                'join' => 'LEFT JOIN `arenes_data` d ON c.id=d.id',
                'select' => 'c.*,c.nom AS name0,c.id,d.data,s.prefix,s.thumbnail',
                'where' => 'c.id IN ('. $idsString .')'. ($requireOwner ? (' AND c.identifiant="'. $identifiants[0] .'" AND c.identifiant2="'. $identifiants[1] .'" AND c.identifiant3="'. $identifiants[2] .'" AND c.identifiant4="'. $identifiants[3] .'"') : '')
            ));
        },
        'fetch_track_extras' => function($options) use (&$CREATION_ENTITIES) {
            return $CREATION_ENTITIES[1]['fetch_track_extras']($options);
        },
        'get_share_params' => function() use (&$CREATION_ENTITIES) {
            return $CREATION_ENTITIES[1]['get_share_params']();
        }
    ),
);