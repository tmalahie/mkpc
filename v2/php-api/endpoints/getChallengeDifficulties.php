<?php
include('../includes/initdb.php');
require_once('../includes/auth.php');
$id = getUserId();
require_once('../includes/creations.php');
require_once('../includes/challenges.php');
require_once('../includes/api.php');
require_once('../includes/language.php');
$language = getLanguage();
$challengeDifficulties = getChallengeDifficulties();
$challengeRewards = getChallengeRewards();
$data = array();
foreach ($challengeDifficulties as $i => $challengeDifficulty) {
    $iData = getChallengeDifficulty(array(
        'difficulty' => $i
    ));
    $iData['reward'] = $challengeRewards[$i];
    $data[] = $iData;
}
renderResponse(array(
  'data' => $data,
));
