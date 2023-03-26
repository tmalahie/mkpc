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
            return mysql_query('SELECT id,map,laps,nom,auteur,note,nbnotes,publication_date FROM `mkcircuits` WHERE id IN ('. $idsString .') AND !type'. ($requireOwner ? (' AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"') : ''));
        },
        'fetch_track_extras' => function($options) {
            global $lettres, $nbLettres;
            $trackID = $options['id'];
            $infos = &$options['infos'];
            $base = &$options['base'];
            $infos['map'] = $base['map'];
            $infos['laps'] = $base['laps'];
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
            return mysql_query('SELECT c.*,c.id,d.data FROM `circuits` c LEFT JOIN `circuits_data` d ON c.id=d.id WHERE c.id IN ('. $idsString .')'. ($requireOwner ? (' AND c.identifiant="'. $identifiants[0] .'" AND c.identifiant2="'. $identifiants[1] .'" AND c.identifiant3="'. $identifiants[2] .'" AND c.identifiant4="'. $identifiants[3] .'"') : ''));
        },
        'fetch_track_extras' => function($options) {
            foreach ($options['base'] as $key => $value)
                $options['infos'][$key] = $value;
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
            return mysql_query('SELECT id,map,nom,auteur,note,nbnotes,publication_date FROM `mkcircuits` WHERE id IN ('. $idsString .') AND type'. ($requireOwner ? (' AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"') : ''));
        },
        'fetch_track_extras' => function($options) {
            global $lettres, $nbLettres;
            $trackID = $options['id'];
            $infos = &$options['infos'];
            $base = &$options['base'];
            $infos['map'] = $base['map'];
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
        }
    ),
    array(
        'page' => 'battle',
        'table' => 'arenes',
        'get_track_from_params' => function($options) {
            // TODO auto-generated method stub
        },
        'fetch_tracks' => function($options) {
            // TODO auto-generated method stub
        },
        'fetch_track_extras' => function($options) {
            // TODO auto-generated method stub
        }
    ),
);