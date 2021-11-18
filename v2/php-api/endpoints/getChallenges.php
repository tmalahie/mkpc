<?php
include('../includes/initdb.php');
require_once('../includes/auth.php');
$id = getUserId();
require_once('../includes/creations.php');
require_once('../includes/challenges.php');
require_once('../includes/api.php');
require_once('../includes/language.php');
$language = getLanguage();
if (isset($_GET['pending_moderation'])) {
	require_once('getRights.php');
	if (hasUserRights($id, 'clvalidator'))
		$moderate = true;
}
$completed = isset($_GET['completed']);
$chSelect = 'c.*,l.type,l.circuit';
$chJoin = array();
$chJoin[] = 'INNER JOIN mkclrace l ON c.clist=l.id';
$chWhere = array();
$chWhere[] = 'l.type!=""';
if (isset($moderate)) {
	$chWhere[] = 'c.status="pending_moderation"';
	$chOrder = 'c.date';
	$challengeTitle = $language ? 'Challenges pending moderation':'Défis en attente de validation';
}
else {
	$chWhere[] = 'c.status="active"';
	if (empty($_GET['ordering']) || ('rating' !== $_GET['ordering']))
		$chOrder = 'c.date DESC';
	else
		$chOrder = 'c.avgrating DESC, c.nbratings DESC, c.date DESC';
}
if (isset($_GET['difficulty']) && ($_GET['difficulty'] != ''))
	$chWhere[] = 'c.difficulty="'. $_GET['difficulty'] .'"';
if (!empty($_GET['hide_succeeded']) && $id) {
	$chJoin[] = 'LEFT JOIN mkclwin w ON w.challenge=c.id AND w.player='.$id;
	$chWhere[] = 'w.player IS NULL';
}
if (isset($_GET['author'])) {
	if ($getProfile = mysql_fetch_array(mysql_query('SELECT identifiant,identifiant2,identifiant3,identifiant4 FROM `mkprofiles` WHERE id="'. $_GET['author'] .'"'))) {
		$chWhere[] = 'l.identifiant='.$getProfile['identifiant'];
		$chWhere[] = 'l.identifiant2='.$getProfile['identifiant2'];
		$chWhere[] = 'l.identifiant3='.$getProfile['identifiant3'];
		$chWhere[] = 'l.identifiant4='.$getProfile['identifiant4'];
		if ($username = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $_GET['author'] .'"')))
			$challengeTitle = $language ? 'Challenges list of '.$username['nom']:'Liste des défis de '.$username['nom'];
	}
}
if ($completed || isset($_GET['winner'])) {
	$winner = $completed ? $id : $_GET['winner'];
	$chJoin[] = 'INNER JOIN mkclwin w2 ON w2.challenge=c.id AND w2.player="'.$winner.'" AND w2.creator=0';
	$chOrder = 'w2.date DESC';
	if ($completed)
		$chSelect .= ',w2.rating AS avgrating,(w2.rating>0) AS nbratings';
}
$chOrder .= ', c.id DESC';
$currentPage = isset($_GET['page']) ? $_GET['page']:1;
$challengesPerPage = 20;
$chOffset = ($currentPage-1)*$challengesPerPage;
$challengeParams = array(
	'rating' => true,
	'circuit' => true
);
if ($id) {
	$challengeParams['winners'] = true;
	$challengeParams['id'] = $id;
}

$getNbChallenges = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkchallenges c '. implode(' ', $chJoin) .' WHERE '. implode(' AND ', $chWhere)));
$nbChallenges = $getNbChallenges['nb'];
$getChallenges = mysql_query('SELECT '.$chSelect.' FROM mkchallenges c '. implode(' ', $chJoin) .' WHERE '. implode(' AND ', $chWhere) .' ORDER BY '. $chOrder .' LIMIT '.$chOffset.','.$challengesPerPage);

$challenges = array();
while ($challenge = mysql_fetch_array($getChallenges)) {
    $challengeDetails = getChallengeDetails($challenge, $challengeParams);
    $challengeData = array(
        'id' => $challengeDetails['id'],
        'name' => $challengeDetails['name'],
        'status' => $challengeDetails['status'],
        'difficulty' => $challengeDetails['difficulty'],
        'description' => $challengeDetails['description']
    );
    if (isset($challengeDetails['circuit']))
        $challengeData['circuit'] = $challengeDetails['circuit'];
    if (isset($challengeDetails['rating']))
        $challengeData['rating'] = $challengeDetails['rating'];
    if (isset($challengeDetails['succeeded']))
        $challengeData['succeeded'] = $challengeDetails['succeeded'];
    $challenges[] = $challengeData;
}
renderResponse(array(
    'data' => $challenges,
    'count' => $nbChallenges
));
