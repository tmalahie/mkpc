<?php
header('Content-Type: application/json');
include('../includes/session.php');
include('../includes/initdb.php');

if (!$id) {
    echo json_encode([]);
    mysql_close();
    exit;
}

$now = time();

// update user activity timestamp
mysql_query('INSERT INTO `mkconnectes` SET id="'. $id .'",connecte='. $now .' ON DUPLICATE KEY UPDATE connecte='. $now);
mysql_query('DELETE FROM `mkconnectes` WHERE connecte <= '. ($now - 30));

// get connected players
$players = [];
$getConnectes = mysql_query(
    'SELECT c.id,j.nom,j.online,f.code FROM `mkconnectes` c
    INNER JOIN `mkjoueurs` j ON c.id=j.id AND j.online>0
    INNER JOIN `mkprofiles` p ON p.id=j.id
    LEFT JOIN `mkcountries` f ON p.country=f.id
    WHERE c.id!='.$id
);

while ($player = mysql_fetch_array($getConnectes)) {
    $players[] = [
        (int) $player['id'],
        $player['nom'],
        (int) $player['online'],
        $player['code']
    ];
}

// update invitations
mysql_query('UPDATE `mkinvitations` SET connecte='. $now .' WHERE demandeur="'. $id .'" AND reponse=-1');
mysql_query('DELETE FROM `mkinvitations` WHERE connecte <= '. ($now - 30));

// received invitations
$receivedInvitations = [];
$getInvitations = mysql_query('SELECT demandeur,battle FROM `mkinvitations` WHERE receveur="'. $id .'" AND reponse=-1');
while ($getDemande = mysql_fetch_array($getInvitations)) {
    $pts_ = $getDemande['battle'] ? 'pts_battle' : 'pts_vs';
    if ($getPseudo = mysql_fetch_array(mysql_query('SELECT j.nom,j.'.$pts_.' AS pts FROM `mkjoueurs` j WHERE j.id='. $getDemande['demandeur']))) {
        $receivedInvitations[] = [
            (int) $getDemande['demandeur'],
            $getPseudo['nom'],
            (int) $getPseudo['pts'],
            (int) $getDemande['battle']
        ];
    }
}

// invitation responses
$sentInvitationResponses = [];
$getInvitationResponses = mysql_query('SELECT receveur,reponse,message,battle FROM `mkinvitations` WHERE demandeur="'. $id .'" AND reponse!=-1');
while ($getDemande = mysql_fetch_array($getInvitationResponses)) {
    if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id='. $getDemande['receveur']))) {
        $sentInvitationResponses[] = [
            (int) $getDemande['receveur'],
            $getPseudo['nom'],
            (int) $getDemande['reponse'],
            htmlspecialchars($getDemande['message']),
            (int) $getDemande['battle']
        ];
    }
}

// get messages
include('../includes/o_utils.php');
$messages = [];
for ($i = 0; isset($_POST['c'.$i]) && isset($_POST['m'.$i]); $i++) {
    mysql_query('UPDATE `mkchats` SET seen=1 WHERE id<="'. $_POST['m'.$i] .'" AND sender="'. $_POST['c'.$i] .'" AND receiver="'. $id .'"');
    if (isset($_POST['w'.$i])) {
        mysql_query('UPDATE `mkconvs` SET writting='. ($_POST['w'.$i] ? 'CURRENT_TIMESTAMP()':'NULL') .' WHERE sender="'. $_POST['c'.$i] .'" AND receiver="'. $id .'"');
    }

    $chatKey = 'c' . $_POST['c'.$i];
    $messages[$chatKey] = [];
    $getMsgs = mysql_query('SELECT * FROM `mkchats` WHERE id>"'. $_POST['m'.$i] .'" AND ((sender="'. $id .'" AND receiver="'. $_POST['c'.$i] .'") OR (sender="'. $_POST['c'.$i] .'" AND receiver="'. $id .'")) ORDER BY id');
    while ($msg = mysql_fetch_array($getMsgs)) {
        $messages[$chatKey][] = [
            (int) $msg['id'],
            (int) $msg['sender'],
            parse_msg($msg['message']),
            to_local_tz($msg['date'])
        ];
    }
}

// unread messages
$unread = [];
$unreadQuery = 'SELECT c.id,c.sender,c.message,j.nom FROM `mkchats` c 
    INNER JOIN(SELECT MAX(id) AS maxID, sender FROM `mkchats` WHERE receiver="'. $id .'" AND seen=0 GROUP BY sender) m 
    ON c.id=m.maxID 
    INNER JOIN `mkjoueurs` j ON c.sender=j.id 
    WHERE c.receiver="'. $id .'" AND c.seen=0';
for ($i = 0; isset($_POST['c'.$i]) && isset($_POST['m'.$i]); $i++)
    $unreadQuery .= ' AND c.sender!="'. $_POST['c'.$i] .'"';
$unseenMsgs = mysql_query($unreadQuery);
while ($msg = mysql_fetch_array($unseenMsgs)) {
    $unread[] = [
        (int) $msg['id'],
        (int) $msg['sender'],
        $msg['nom'],
        parse_msg($msg['message'])
    ];
}

// who is currently typing
$typing = [];
$getTyping = mysql_query('SELECT receiver FROM `mkconvs` WHERE sender="'. $id .'" AND writting>DATE_SUB(NOW(),INTERVAL 10 SECOND)');
while ($writer = mysql_fetch_array($getTyping)) {
    $typing[] = (int) $writer['receiver'];
}

echo json_encode([
	$players, // [id, nick, status, country]
	$receivedInvitations, // [sender, nick, pts, battle]
	$sentInvitationResponses, // [receiver, nick, response, message, battle]
	$messages, // [chat_id => [msg_id, sender, msg, date]]
	$unread, // [unread_id, sender, nick, msg]
	$typing // [id, sender, nick, msg]
]);

mysql_close();
?>
