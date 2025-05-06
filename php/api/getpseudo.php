<?php
header('Content-Type: application/json');
include('../includes/language.php');
include('../includes/session.php');
if (!$id) {
    echo json_encode([]);
    exit;
}

include('../includes/initdb.php');

// iterate over all currently online users
if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom,online FROM `mkjoueurs` WHERE id="'. $id .'" AND !banned'))) {
    $onlineStatusCode = $getPseudo['online'];

    $conversations = [];
    include('../includes/o_consts.php');
    include('../includes/o_utils.php');

    $getConvs = mysql_query('SELECT c.receiver, j.nom, c.reduced FROM `mkconvs` c INNER JOIN `mkjoueurs` j ON c.receiver=j.id WHERE c.sender="'. $id .'" ORDER BY c.id');
    while ($conv = mysql_fetch_array($getConvs)) {
        $receiverId = $conv['receiver'];
        $receiverName = $conv['nom'];

        $messages = [];
		if ($conv['reduced']) {
			$getMsgs = mysql_query(<<<SQL
				SELECT * FROM `mkchats` 
				WHERE (sender="$id" AND receiver="$receiverId") 
				OR (sender="$receiverId" AND receiver="$id" AND seen=1) 
				ORDER BY id DESC 
				LIMIT $MSGS_PACKET_SIZE
			SQL);
		} else {
			$getMsgs = mysql_query(<<<SQL
				SELECT * FROM `mkchats` 
				WHERE (sender="$id" AND receiver="$receiverId") 
				OR (sender="$receiverId" AND receiver="$id") 
				ORDER BY id DESC 
				LIMIT $MSGS_PACKET_SIZE
			SQL);
        }

        while ($msg = mysql_fetch_array($getMsgs)) {
            $messages[] = [
				$msg['id'],
				$msg['sender'],
				parse_msg($msg['message']),
				to_local_tz($msg['date'])
			];
        }

        $conversationEntry = [$receiverId, $receiverName, $messages];
        if ($isReduced) {
            $conversationEntry[] = 1;
        }

        $conversations[] = $conversationEntry;
    }

    // ignored users
    $ignoredUsers = [];
    $getIgn = mysql_query('SELECT ignored FROM `mkignores` WHERE ignorer="'. $id .'"');
    while ($ignored = mysql_fetch_array($getIgn)) {
        $ignoredUsers[] = (int)$ignored['ignored'];
    }

    // update online state
    mysql_query('DELETE FROM `mkconnectes` WHERE id="'. $id .'"');
    if ($onlineStatusCode > 0) {
        mysql_query('INSERT INTO `mkconnectes` VALUES('. $id .','. time() .')');
        mysql_query('UPDATE `mkprofiles` SET last_connect=NULL WHERE id='. $id);
    }

    echo json_encode([
		$id,				// user id
		$getPseudo['nom'],  // user nick
		$language,		    // 1 if english, 0 if french
		$onlineStatusCode,  // 0 - offline, 1 - dnd, 2 - online
		$conversations,     // array of conversations -> [[id, nick, msgs], ...]
		$ignoredUsers       // array of ignored users -> [id, id, ...]
	]);
} else {
    echo json_encode([]);
}

mysql_close();
?>
