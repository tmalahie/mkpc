<?php
header('Content-Type: text/plain');
if (!isset($_POST['id'])) exit;
include('../includes/session.php');
include('../includes/initdb.php');
require_once('../includes/getRights.php');
require_once('../includes/utils-logs.php');
if (hasRight('moderator')) {
    $newState = isset($_POST['unarchive']) ? 'pending' : 'archived';
    $logKey = isset($_POST['unarchive']) ? 'UAReport' : 'AReport';
    $report = mysql_fetch_array(mysql_query('SELECT type,link,count,first_reported,last_reported,state FROM mkreports WHERE id="'. $_POST['id'] .'"'));
    mysql_query('UPDATE mkreports SET state="'. $newState .'" WHERE id="'. $_POST['id'] .'"');
    $reportSnapshot = array(
        'type' => 'report',
        'id' => intval($_POST['id'])
    );
    if ($report) {
        $reportSnapshot['reported_type'] = $report['type'];
        $reportSnapshot['link'] = $report['link'];
        $reportSnapshot['count'] = intval($report['count']);
        $reportSnapshot['first_reported'] = $report['first_reported'];
        $reportSnapshot['last_reported'] = $report['last_reported'];
        $reportSnapshot['before'] = array('state' => $report['state']);
        $reportSnapshot['after'] = array('state' => $newState);
        $reportSnapshot['reported_content'] = snapshotReportedContent($report['type'], $report['link']);
    }
    insertLog($id, $logKey .' '. $_POST['id'], $reportSnapshot);
}
echo 1;