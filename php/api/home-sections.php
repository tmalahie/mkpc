<?php
include('../includes/language.php');
include('../includes/initdb.php');
include('../includes/session.php');
require_once('../includes/home-utils.php');

$section = isset($_POST['section']) ? $_POST['section'] : '';
$offset = isset($_POST['offset']) ? max(0, intval($_POST['offset'])) : 0;

header('Content-Type: text/html; charset=utf-8');

$validSections = array('topics', 'news', 'creations', 'challenges', 'activity');
if (!in_array($section, $validSections)) {
    http_response_code(400);
    exit;
}

$userId = isset($id) ? $id : null;

$defaultLimits = array(
    'topics' => 10,
    'news' => 8,
    'creations' => 14,
    'challenges' => 15,
    'activity' => 14
);

$limit = isset($_POST['limit']) ? max(1, min(50, intval($_POST['limit']))) : $defaultLimits[$section];

switch ($section) {
    case 'topics':
        $items = getLatestTopics($limit, $offset, $userId);
        echo renderTopicItems($items);
        break;
        
    case 'news':
        $items = getLatestNews($limit, $offset, $userId);
        echo renderNewsItems($items);
        break;
        
    case 'creations':
        $items = getLatestCreations($limit, $offset);
        echo renderCreationItems($items);
        break;
        
    case 'challenges':
        $items = getLatestChallenges($limit, $offset, $userId);
        echo renderChallengeItems($items);
        break;
        
    case 'activity':
        $items = getRecentActivity($limit, $offset);
        echo renderActivityItems($items);
        break;
}

mysql_close();

