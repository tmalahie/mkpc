<?php
include('../includes/initdb.php');
require_once('../includes/creations.php');
require_once('../includes/api.php');
$tri = isset($_GET['tri']) ? $_GET['tri']:0;
$type = isset($_GET['type']) ? $_GET['type']:'';
$nom = isset($_GET['nom']) ? stripslashes($_GET['nom']):'';
$auteur = isset($_GET['auteur']) ? stripslashes($_GET['auteur']):'';
$pids = null;
if (isset($_GET['user'])) {
	$user = $_GET['user'];
	if ($getProfile = mysql_fetch_array(mysql_query('SELECT identifiant,identifiant2,identifiant3,identifiant4 FROM `mkprofiles` WHERE id="'. $user .'"')))
		$pids = array($getProfile['identifiant'],$getProfile['identifiant2'],$getProfile['identifiant3'],$getProfile['identifiant4']);
}
else
	$user = '';
$singleType = ($type !== '');
if ($singleType) {
    $aCircuits = array($aCircuits[$type]);
    $weightsByType = array($weightsByType[$type]);
}
$nbCircuits = isset($_GET['nb']) ? +$_GET['nb'] : $MAX_CIRCUITS;
$nbByType = null;
if (isset($_GET['nbByType']) && is_array($_GET['nbByType'])) {
    $nbByType = $_GET['nbByType'];
    $nbCircuits = array_sum($nbByType);
}
if ($nbCircuits > $MAX_CIRCUITS)
    $nbCircuits = $MAX_CIRCUITS;
$aParams = array(
	'type' => $type,
	'tri' => $tri,
	'nom' => $nom,
	'auteur' => $auteur,
	'pids' => $pids,
	'max_circuits' => $nbCircuits,
);
$page = isset($_GET['page']) ? $_GET['page']:1;
if ($nbByType === null)
    $nbByType = countTracksByType($aCircuits,$aParams);
$creationsList = listCreations($page,$nbByType,$weightsByType,$aCircuits,$aParams);
$data = array();
foreach ($creationsList as &$creation) {
    $item = array(
        'id' => isset($creation['id']) ? +$creation['id'] : +$creation['ID'],
        'publicationDate' => strtotime($creation['publication_date'])*1000,
        'name' => $creation['nom'],
        'author' => $creation['auteur'],
        'rating' => +$creation['note'],
        'nbRatings' => +$creation['nbnotes'],
        'nbComments' => +$creation['nbcomments'],
        'category' => $creation['category'],
        'isCup' => (strpos($creation['cicon'], ',') !== false),
        'href' => $creation['href'],
        'cicon' => $creation['cicon'],
        'srcs' => $creation['srcs']
    );
    if (isset($creation['icon']))
        $item['icons'] = $creation['icon'];
    $data[] = $item;
}
unset($creation);
$nbCreations = array_sum($nbByType);
renderResponse(array(
    'data' => $data,
    'count' => $nbCreations,
    'countByType' => $nbByType
));
