<?php
require_once('getRights.php');

function getProfileIdsString() {
    global $id, $identifiants;
    $getProfiles = mysql_query('SELECT id FROM mkprofiles WHERE identifiant="'. $identifiants[0] .'"');
    $profileIds = array();
    $profileIds[$id] = true;
    while ($getProfile = mysql_fetch_array($getProfiles))
        $profileIds[$getProfile['id']] = true;
    $profileIdsString = implode(',', array_keys($profileIds));
    return $profileIdsString;
}
function isMsgCooldowned($context = array()) {
    // Admins are exempted from cooldown
    if (hasRight('manager'))
        return false;

    $profileIdsString = getProfileIdsString();
    $getRecentMsgs = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkmessages WHERE auteur IN ('. $profileIdsString .') AND date>=DATE_SUB(NOW(),INTERVAL 60 SECOND)'));
    $recentMsgs = $getRecentMsgs['nb'];
    if ($recentMsgs >= 2)
        return true;
    $getRecentMsgs = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkmessages WHERE auteur IN ('. $profileIdsString .') AND date>=DATE_SUB(NOW(),INTERVAL 600 SECOND)'));
    $recentMsgs = $getRecentMsgs['nb'];
    if ($recentMsgs > 5)
        return true;
    if (!empty($context['newtopic'])) {
        $getRecentTopics = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkmessages WHERE auteur IN ('. $profileIdsString .') AND id=1 AND date>=DATE_SUB(NOW(),INTERVAL 120 SECOND)'));
        $recentTopics = $getRecentTopics['nb'];
        if ($recentTopics >= 1)
            return true;
        $getRecentTopics = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkmessages WHERE auteur IN ('. $profileIdsString .') AND id=1 AND date>=DATE_SUB(NOW(),INTERVAL 1200 SECOND)'));
        $recentTopics = $getRecentTopics['nb'];
        if ($recentTopics > 5)
            return true;
    }
    return false;
}
function printMsgCooldowned() {
    global $language;
    ?>
    <p style="text-align: center">
    <?php
    include('utils-date.php');
    if ($language) {
        ?>
        It looks like you've sent a lot of messages in the last minutes.<br />
        Our system has blocked you temporarilly from sending new messages to prevent spam.<br />
        Sending lot of messages is generally considered a bad behaviour, to learn more, please read the <a href="topic.php?topic=2448">forum's rules</a> topic.<br />
        Don't worry, this block is temporary, you should be able to post again in a few minutes
        <?php
    }
    else {
        ?>
        Il semble que vous avez envoyé beaucoup de messages dans les dernières minutes.<br />
        Le système a bloqué temporairement l'envoi de messages sur votre compte pour éviter les spams.<br />
        Le spam de façon générale est interdit sur le forum du site, pour en savoir plus, lisez le <a href="topic.php?topic=2448">règlement</a> du forum.<br />
        Notez que le bloquage est temporaire, vous devriez pouvoir reposter des messages dans quelques minutes.
        <?php
    }
    ?>
    </p>
    <?php
}
function isNewsCooldowned() {
    $profileIdsString = getProfileIdsString();
    $getRecentMsgs = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mknews WHERE author IN ('. $profileIdsString .') AND creation_date>=DATE_SUB(NOW(),INTERVAL 60 SECOND)'));
    $recentMsgs = $getRecentMsgs['nb'];
    if ($recentMsgs >= 1)
        return true;
    $getRecentMsgs = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mknews WHERE author IN ('. $profileIdsString .') AND creation_date>=DATE_SUB(NOW(),INTERVAL 600 SECOND)'));
    $recentMsgs = $getRecentMsgs['nb'];
    if ($recentMsgs > 2)
        return true;
    return false;
}
function isNewsComCooldowned() {
    $profileIdsString = getProfileIdsString();
    $getRecentMsgs = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mknewscoms WHERE author IN ('. $profileIdsString .') AND date>=DATE_SUB(NOW(),INTERVAL 60 SECOND)'));
    $recentMsgs = $getRecentMsgs['nb'];
    if ($recentMsgs >= 2)
        return true;
    $getRecentMsgs = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mknewscoms WHERE author IN ('. $profileIdsString .') AND date>=DATE_SUB(NOW(),INTERVAL 300 SECOND)'));
    $recentMsgs = $getRecentMsgs['nb'];
    if ($recentMsgs > 5)
        return true;
    return false;
}
function printNewsCooldowned() {
    global $language;
    ?>
    <p style="text-align: center">
    <?php
    include('utils-date.php');
    if ($language) {
        ?>
        It looks like you've sent a lot of news in the last minutes.<br />
        Our system has blocked you temporarilly from sending news to prevent spam.<br />
        Don't worry, this block is temporary, you should be able to post again in a few minutes
        <?php
    }
    else {
        ?>
        Il semble que vous avez envoyé beaucoup de news dans les dernières minutes.<br />
        Le système a bloqué temporairement l'envoi de news sur votre compte pour éviter les spams.<br />
        Ce bloquage est temporaire, vous devriez pouvoir reposter des news dans quelques minutes.
        <?php
    }
    ?>
    </p>
    <?php
}
function isTrackCooldowned($context) {
    global $identifiants;
    $table = $context['type'];
    $getRecentMsgs = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `'.$table.'` WHERE identifiant='.$identifiants[0].' AND publication_date>=DATE_SUB(NOW(),INTERVAL 60 SECOND)'));
    $recentMsgs = $getRecentMsgs['nb'];
    if ($recentMsgs >= 2)
        return true;
    $getRecentMsgs = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `'.$table.'` WHERE identifiant='.$identifiants[0].' AND publication_date>=DATE_SUB(NOW(),INTERVAL 300 SECOND)'));
    $recentMsgs = $getRecentMsgs['nb'];
    if ($recentMsgs > 5)
        return true;
    return false;
}
function isTrackComCooldowned() {
    $profileIdsString = getProfileIdsString();
    $getRecentMsgs = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkcomments WHERE auteur IN ('. $profileIdsString .') AND date>=DATE_SUB(NOW(),INTERVAL 60 SECOND)'));
    $recentMsgs = $getRecentMsgs['nb'];
    if ($recentMsgs >= 4)
        return true;
    $getRecentMsgs = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkcomments WHERE auteur IN ('. $profileIdsString .') AND date>=DATE_SUB(NOW(),INTERVAL 300 SECOND)'));
    $recentMsgs = $getRecentMsgs['nb'];
    if ($recentMsgs >= 15)
        return true;
    return false;
}
function isRatingCooldowned() {
    global $identifiants;
    $getRecentMsgs = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkratings` WHERE identifiant='.$identifiants[0].' AND date>=DATE_SUB(NOW(),INTERVAL 60 SECOND)'));
    $recentMsgs = $getRecentMsgs['nb'];
    if ($recentMsgs >= 5)
        return true;
    $getRecentMsgs = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkratings` WHERE identifiant='.$identifiants[0].' AND date>=DATE_SUB(NOW(),INTERVAL 300 SECOND)'));
    $recentMsgs = $getRecentMsgs['nb'];
    if ($recentMsgs >= 20)
        return true;
    return false;
}
function isAccountCooldowned() {
    global $identifiants;
    $getRecentMsgs = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkprofiles` WHERE identifiant='.$identifiants[0].' AND sub_date=CURDATE()'));
    $recentMsgs = $getRecentMsgs['nb'];
    if ($recentMsgs >= 25)
        return true;
    return false;
}
function logCooldownEvent($type) {
    global $id, $identifiants;
    $identifiant = isset($identifiants) ? $identifiants[0] : 0;
    $playerId = isset($id) ? $id : 0;
    mysql_query('INSERT INTO `mkcooldownhist` SET player="'.$playerId.'",identifiant="'.$identifiant.'",type="'.$type.'"');
}