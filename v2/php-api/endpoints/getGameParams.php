<?php
include('../includes/initdb.php');
include('../includes/language.php');
$language = getLanguage();
header('Content-Type: application/json');
echo '{';
include_once('../../../circuitNames.php');
echo '"circuitNames":'.json_encode($circuitNames).',';
echo '"characterNames":';
include('../../../getPersos.php');
echo ',';
echo '"unlockedCharacters":';
include('../../../getLocks.php');
echo ',';
echo '"ptsGP":';
echo '"'. $mkSaves .'",';
echo '"customCharacterDir":';
require_once('../../../persos.php');
echo '"'.PERSOS_DIR.'",';
echo '"nbVsCircuits":';
echo $nbVSCircuits;
echo '}';