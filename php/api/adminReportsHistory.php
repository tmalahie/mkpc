<?php
if (!isset($_POST['id'])) exit;
header('Content-Type: application/json');
include('../includes/session.php');
include('../includes/initdb.php');
require_once('../includes/getRights.php');
$res = array();
if (hasRight('moderator')) {
    if ($report = mysql_fetch_array(mysql_query('SELECT type,link FROM mkreports WHERE id="'. $_POST['id'] .'"'))) {
        $getHistory = mysql_query('SELECT h.id,h.reporter,h.date,j.nom FROM mkreportshist h LEFT JOIN mkjoueurs j ON h.reporter=j.id WHERE h.type="'. $report['type'] .'" AND h.link="'. $report['link'] .'" ORDER BY h.id');
        while ($history = mysql_fetch_array($getHistory)) {
            $res[] = array(
                'id' => $history['id'],
                'reporter' => array(
                    'id' => $history['reporter'],
                    'name' => $history['nom']
                ),
                'date' => $history['date']
            );
        }
    }
}
echo json_encode($res);