<?php
define('LOG_SNAPSHOT_MAX_TEXT', 20000);

function insertLog($author, $log, $snapshot = null) {
	if (!mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. intval($author) .', "'. $log .'")'))
		return null;
	$logId = mysql_insert_id();
	if ($logId && ($snapshot !== null))
		insertLogSnapshot($logId, $snapshot);
	return $logId;
}

function insertLogSnapshot($logId, $snapshot) {
	$json = json_encode(truncateSnapshot($snapshot), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE);
	if ($json === false) return false;
	return mysql_query('INSERT INTO `mklogsnapshots` SET log='. intval($logId) .',data="'. mysql_real_escape_string($json) .'" ON DUPLICATE KEY UPDATE data=VALUES(data)');
}

function truncateSnapshot($value) {
	if (is_string($value)) {
		if (mb_strlen($value) > LOG_SNAPSHOT_MAX_TEXT)
			return mb_substr($value, 0, LOG_SNAPSHOT_MAX_TEXT) .'…';
		return $value;
	}
	if (is_array($value)) {
		foreach ($value as $key => $item)
			$value[$key] = truncateSnapshot($item);
	}
	return $value;
}

function snapshotRow($row) {
	if (!$row) return null;
	$res = array();
	foreach ($row as $key => $value) {
		if (!is_int($key))
			$res[$key] = $value;
	}
	return $res;
}

function snapshotQuery($query) {
	return snapshotRow(mysql_fetch_array(mysql_query($query)));
}

function snapshotMember($memberId) {
	if (!$memberId) return null;
	$res = array('id' => intval($memberId));
	if ($member = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. intval($memberId) .'"')))
		$res['name'] = $member['nom'];
	return $res;
}

function countMemberContent($memberId) {
	$memberId = intval($memberId);
	$queries = array(
		'forum_messages' => 'SELECT COUNT(*) AS nb FROM `mkmessages` WHERE auteur="'. $memberId .'"',
		'forum_topics' => 'SELECT COUNT(*) AS nb FROM `mkmessages` WHERE auteur="'. $memberId .'" AND id=1',
		'news' => 'SELECT COUNT(*) AS nb FROM `mknews` WHERE author="'. $memberId .'"',
		'news_comments' => 'SELECT COUNT(*) AS nb FROM `mknewscoms` WHERE author="'. $memberId .'"',
		'track_comments' => 'SELECT COUNT(*) AS nb FROM `mkcomments` WHERE auteur="'. $memberId .'"'
	);
	$res = array();
	foreach ($queries as $key => $query) {
		$count = mysql_fetch_array(mysql_query($query));
		$res[$key] = intval($count['nb']);
	}
	return $res;
}

function snapshotTrack($type, $id) {
	$res = array('type' => $type, 'id' => intval($id));
	if (empty($type)) return $res;
	$track = mysql_fetch_array(mysql_query(
		'SELECT c.nom,c.auteur,s.name_en,s.name_fr,s.prefix,s.description
		FROM `'. $type .'` c LEFT JOIN `mktracksettings` s ON s.type="'. $type .'" AND s.circuit=c.id
		WHERE c.id="'. intval($id) .'"'
	));
	if ($track) {
		$res['name'] = $track['nom'];
		$res['author'] = $track['auteur'];
		foreach (array('name_en','name_fr','prefix','description') as $field) {
			if ($track[$field] !== null)
				$res[$field] = $track[$field];
		}
	}
	return $res;
}

function decodeSnapshotJson($json) {
	if (empty($json)) return null;
	$res = json_decode($json, true);
	return ($res === null) ? $json : $res;
}

function snapshotWord($table, $wordId, $fields) {
	$word = snapshotQuery('SELECT '. $fields .' FROM `'. $table .'` WHERE id="'. intval($wordId) .'"');
	return $word ? $word : array();
}

function snapshotChallenge($challenge) {
	if (!$challenge) return null;
	$res = array(
		'type' => 'challenge',
		'id' => intval($challenge['id']),
		'name' => $challenge['name'],
		'status' => $challenge['status'],
		'difficulty' => intval($challenge['difficulty']),
		'data' => decodeSnapshotJson($challenge['data']),
		'validation' => decodeSnapshotJson($challenge['validation'])
	);
	$clRace = mysql_fetch_array(mysql_query('SELECT type,circuit FROM `mkclrace` WHERE id="'. intval($challenge['clist']) .'"'));
	if ($clRace && !empty($clRace['type']))
		$res['track'] = snapshotTrack($clRace['type'], $clRace['circuit']);
	return $res;
}

function snapshotRecord($record) {
	if (!$record) return null;
	return array(
		'type' => 'time_trial_record',
		'id' => intval($record['id']),
		'track' => snapshotTrack($record['type'], $record['circuit']),
		'class' => intval($record['class']),
		'name' => $record['name'],
		'player' => snapshotMember($record['player']),
		'character' => $record['perso'],
		'time' => intval($record['time'])
	);
}

function snapshotNews($news) {
	if (!$news) return null;
	return array(
		'type' => 'news',
		'id' => intval($news['id']),
		'title' => $news['title'],
		'category' => intval($news['category']),
		'author' => snapshotMember($news['author']),
		'status' => $news['status'],
		'content' => $news['content']
	);
}

function snapshotForumMessage($topicId, $messageId) {
	$res = array(
		'type' => 'forum_message',
		'topic' => intval($topicId),
		'message_id' => intval($messageId)
	);
	$message = mysql_fetch_array(mysql_query(
		'SELECT m.auteur,m.date,m.message,t.titre
		FROM `mkmessages` m LEFT JOIN `mktopics` t ON t.id=m.topic
		WHERE m.topic="'. intval($topicId) .'" AND m.id="'. intval($messageId) .'"'
	));
	if ($message) {
		$res['topic_title'] = $message['titre'];
		$res['author'] = snapshotMember($message['auteur']);
		$res['date'] = $message['date'];
		$res['content'] = $message['message'];
	}
	return $res;
}

function snapshotReportedContent($type, $link) {
	switch ($type) {
	case 'topic':
		$ids = explode(',', $link);
		return snapshotForumMessage($ids[0], isset($ids[1]) ? $ids[1] : 1);
	case 'circuits':
	case 'arenes':
	case 'mkcircuits':
	case 'mkcups':
	case 'mkmcups':
		return snapshotTrack($type, $link);
	}
	return array('type' => $type, 'link' => $link);
}
?>
