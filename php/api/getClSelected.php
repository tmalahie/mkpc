<?php
header('Content-Type: application/json');
session_start();
$res = array();
if (isset($_SESSION['clselected'])) {
    $challengeId = $_SESSION['clselected'];
    unset($_SESSION['clselected']);
    $res['id'] = $challengeId;
    include('../includes/initdb.php');
    if ($challenge = mysql_fetch_array(mysql_query('SELECT clist,data FROM mkchallenges WHERE id="'. $challengeId .'"'))) {
        $language = 0;
        require_once('../includes/utils-challenges.php');
        $res['autoset'] = array();
        challengeAutoSet($res['autoset'],$challenge);
    }
    mysql_close();
}
echo json_encode($res);