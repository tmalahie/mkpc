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
$data = array();
foreach ($challengeDifficulties as $i => $challengeDifficulty) {
    $data[] = getChallengeDifficulty(array(
        'difficulty' => $i
    ));
}
renderResponse(array(
  'data' => $data,
));
