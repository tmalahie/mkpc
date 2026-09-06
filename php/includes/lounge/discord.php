<?php
// Notifications into the CT Lounge Discord. The ladder lives there, and staff want it to keep
// working as the place you find out a mogi is gathering even though queueing itself has moved
// to the site.
//
// Credentials come from php/includes/config/discord.php, which is never committed. With no
// config the whole module turns into a no-op, so a fresh checkout and CI behave as if Discord
// did not exist.
function lounge_discord_config() {
	static $config = null;
	if (!is_null($config))
		return $config;
	$config = array();
	$path = dirname(__DIR__) .'/config/discord.php';
	if (file_exists($path)) {
		include($path);
		$config = array(
			'token' => isset($loungeDiscordToken) ? $loungeDiscordToken : '',
			'mllu' => isset($loungeDiscordMlluChannel) ? $loungeDiscordMlluChannel : '',
			'tiers' => isset($loungeDiscordTierChannels) ? $loungeDiscordTierChannels : array()
		);
	}
	return $config;
}

function lounge_discord_tier_channel($tierCode) {
	$config = lounge_discord_config();
	return isset($config['tiers'][$tierCode]) ? $config['tiers'][$tierCode] : '';
}

function lounge_discord_mllu_channel() {
	$config = lounge_discord_config();
	return isset($config['mllu']) ? $config['mllu'] : '';
}

// Every outgoing message is recorded whether or not it is actually sent. It doubles as the
// audit trail staff will want when a ping did not arrive, and as the seam the tests assert
// on - they run with discord_enabled off, so nothing leaves the machine.
function lounge_discord_record($channel, $content, $messageId, $action) {
	mysql_query(
		'INSERT INTO `mklounge_discord_log` (channel, content, message_id, action)
		VALUES ("'. mysql_real_escape_string($channel) .'",
			"'. mysql_real_escape_string($content) .'",
			"'. mysql_real_escape_string((string) $messageId) .'",
			"'. mysql_real_escape_string($action) .'")'
	);
}

// Discord is on the far side of the internet and this runs inside a player's request, so it
// gets a short leash and every failure is swallowed: a lounge that stops working because
// Discord is down would be a worse outcome than a missing notification.
function lounge_discord_call($method, $path, $payload = null) {
	$config = lounge_discord_config();
	if (empty($config['token']) || !lounge_setting('discord_enabled'))
		return null;
	$ch = curl_init('https://discord.com/api/v10'. $path);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
	$headers = array('Authorization: Bot '. $config['token']);
	if (!is_null($payload)) {
		$headers[] = 'Content-Type: application/json';
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$body = curl_exec($ch);
	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	if (($status < 200) || ($status >= 300))
		return null;
	return json_decode($body, true);
}

// $ping is the "@here +3" line: passing it is what makes the message notify, and leaving it
// out is what keeps the other updates quiet.
function lounge_discord_post($channel, $content, $ping = false) {
	if (!$channel)
		return null;
	$payload = array(
		'content' => $content,
		'allowed_mentions' => array('parse' => $ping ? array('everyone') : array())
	);
	$res = lounge_discord_call('POST', '/channels/'. $channel .'/messages', $payload);
	$messageId = ($res && isset($res['id'])) ? $res['id'] : '';
	lounge_discord_record($channel, $content, $messageId, 'post');
	return $messageId;
}

function lounge_discord_edit($channel, $messageId, $content) {
	if (!$channel || !$messageId)
		return false;
	$res = lounge_discord_call(
		'PATCH', '/channels/'. $channel .'/messages/'. $messageId,
		array('content' => $content)
	);
	lounge_discord_record($channel, $content, $messageId, 'edit');
	return (bool) $res;
}

function lounge_state_get($name) {
	$row = mysql_fetch_array(mysql_query(
		'SELECT value FROM `mklounge_state` WHERE name="'. mysql_real_escape_string($name) .'"'
	));
	return $row ? $row['value'] : null;
}

function lounge_state_set($name, $value) {
	mysql_query(
		'INSERT INTO `mklounge_state` (name, value)
		VALUES ("'. mysql_real_escape_string($name) .'", "'. mysql_real_escape_string($value) .'")
		ON DUPLICATE KEY UPDATE value="'. mysql_real_escape_string($value) .'"'
	);
}

// "@here +3" means "three more needed": to reach the minimum while the lineup is still
// gathering, and to fill it once the mogi is already going to happen. That is the reading of
// Fways' worked examples, where 1/2/3 players ask for +3/+2/+1 and 4 players asks for +4.
function lounge_discord_ping_line($playerCount, $minPlayers, $maxPlayers) {
	$needed = ($playerCount < $minPlayers)
		? ($minPlayers - $playerCount)
		: ($maxPlayers - $playerCount);
	if ($needed < 1)
		return '';
	return '@here +'. $needed;
}

function lounge_discord_countdown($seconds) {
	$seconds = max(0, intval($seconds));
	return floor($seconds / 60) .':'. str_pad($seconds % 60, 2, '0', STR_PAD_LEFT);
}

// The list Fways specified: a header, the lineup with everyone's rating, then whichever of the
// ping and the countdown apply.
function lounge_discord_mogi_list($queue, $event = '', $ping = '') {
	$lines = array();
	if ($event)
		$lines[] = $event;
	$lines[] = '`Mogi List`';
	foreach ($queue['members'] as $i => $member)
		$lines[] = '`'. ($i + 1) .'.` '. $member['name'] .' (MMR: '. $member['mmr'] .')';

	$count = count($queue['members']);
	if ($count >= $queue['ready_threshold'])
		$lines[] = '*Queue is full*';
	if ($ping)
		$lines[] = $ping;
	if (!is_null($queue['lock_seconds_left']) && ($queue['status'] === 'locked'))
		$lines[] = '**Décompte automatique: '. lounge_discord_countdown($queue['lock_seconds_left']) .'**';
	return implode("\n", $lines);
}

function lounge_discord_should_ping($queueId, $playerCount, $minPlayers, $wasBelowMin) {
	// the first player to gather a lineup, and the moment it becomes a real mogi
	if ($playerCount === 1)
		return true;
	if ($wasBelowMin && ($playerCount >= $minPlayers))
		return true;
	return false;
}

function lounge_discord_mark_ping($queueId) {
	mysql_query(
		'UPDATE `mklounge_queues` SET discord_here_at=NOW() WHERE id="'. intval($queueId) .'"'
	);
}

function lounge_discord_announce($queueId, $event, $forcePing = false) {
	if (!lounge_setting('discord_enabled'))
		return;
	$queue = lounge_queue_state($queueId);
	if (!$queue)
		return;
	$channel = lounge_discord_tier_channel($queue['tier_code']);
	if (!$channel)
		return;

	$count = count($queue['members']);
	$ping = '';
	if ($forcePing && $count) {
		$ping = lounge_discord_ping_line($count, $queue['lock_threshold'], $queue['ready_threshold']);
		lounge_discord_mark_ping($queueId);
	}
	lounge_discord_post($channel, lounge_discord_mogi_list($queue, $event, $ping), (bool) $ping);
	lounge_discord_sync_mllu();
}

// #mllu carries one message for the whole channel, edited in place, so the channel stays a
// dashboard rather than a feed. Only the tier channels get a message per event.
function lounge_discord_mllu_text() {
	$active = array();
	$full = 0;
	$res = mysql_query(
		'SELECT q.id, t.code AS tier_code FROM `mklounge_queues` q
		INNER JOIN `mklounge_tiers` t ON t.id=q.tier
		WHERE q.status IN ("open","locked","voting")
		ORDER BY q.id'
	);
	while ($row = mysql_fetch_array($res)) {
		$queue = lounge_queue_state(intval($row['id']));
		if (!$queue || !count($queue['members']))
			continue;
		$active[] = $queue;
		if (count($queue['members']) >= $queue['ready_threshold'])
			$full++;
	}

	$lines = array();
	$lines[] = 'There are '. count($active) .' active mogi and '. $full .' full mogi.';
	foreach ($active as $queue) {
		$lines[] = '';
		$header = '⁠CT Lounge⁠'. $queue['tier_code'] .' ('. $queue['tier_code'] .') - '
			. count($queue['members']) .'/'. $queue['ready_threshold'];
		if (!is_null($queue['lock_seconds_left']) && ($queue['status'] === 'locked'))
			$header .= ' - '. lounge_discord_countdown($queue['lock_seconds_left']);
		$lines[] = $header;
		$names = array();
		foreach ($queue['members'] as $member)
			$names[] = $member['name'];
		$lines[] = implode(', ', $names);
	}
	$lines[] = '';
	$lines[] = 'Last updated: <t:'. time() .':R>.';
	return implode("\n", $lines);
}

// Rewritten on every queue change, and otherwise at most twice a minute - a gathering lineup
// with a countdown running has to keep moving even when nobody joins or leaves.
function lounge_discord_sync_mllu($force = true) {
	if (!lounge_setting('discord_enabled'))
		return;
	$channel = lounge_discord_mllu_channel();
	if (!$channel)
		return;
	if (!$force) {
		$last = intval(lounge_state_get('mllu_synced_at'));
		if ($last && ((time() - $last) < lounge_setting('discord_mllu_seconds')))
			return;
	}
	lounge_state_set('mllu_synced_at', time());

	$content = lounge_discord_mllu_text();
	$messageId = lounge_state_get('mllu_message');
	if ($messageId && lounge_discord_edit($channel, $messageId, $content))
		return;
	$messageId = lounge_discord_post($channel, $content);
	if ($messageId)
		lounge_state_set('mllu_message', $messageId);
}

// Called from the tick, which is the only clock this system has: there is no cron, so the
// 30-minute reminder rides on whatever polling is happening. That is sound as long as somebody
// is queued, which is exactly when the reminder is wanted.
function lounge_discord_tick() {
	if (!lounge_setting('discord_enabled'))
		return;
	$stale = mysql_query(
		'SELECT q.id FROM `mklounge_queues` q
		WHERE q.status IN ("open","locked")
		AND IFNULL(q.discord_here_at, q.opened_at) < (NOW() - INTERVAL '. intval(lounge_setting('discord_here_minutes')) .' MINUTE)
		AND EXISTS(SELECT 1 FROM `mklounge_queue_members` m WHERE m.queue=q.id AND m.dropped_at IS NULL)'
	);
	$queues = array();
	while ($row = mysql_fetch_array($stale))
		$queues[] = intval($row['id']);
	foreach ($queues as $queueId)
		lounge_discord_announce($queueId, '', true);

	lounge_discord_sync_mllu(false);
}
