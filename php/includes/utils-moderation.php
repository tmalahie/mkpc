<?php
function printForumReplyNotices() {
    global $id, $language;
    $profile = mysql_fetch_array(mysql_query('SELECT nbmessages FROM mkprofiles WHERE id="'. $id .'"'));
    if (!$profile['nbmessages']) {
        ?>
        <div class="auto-moderation-notice">
        <?php
        if ($language) {
            ?>
            Looks like this is your first message to MKPC! Welome to the forum, if you haven't done it yet, please have a look at the <a href="forum-rules.php" target="_blank">rules</a> before proceeding.
            <?php
        }
        else {
            ?>
            Il semble que c'est votre 1er message sur MKPC ! Bienvenue sur le forum, si ce n'est pas déjà fait, pensez à consulter le <a href="forum-rules.php">règlement</a> avant de continuer.
            <?php
        }
        ?>
        </div>
        <?php
    }
    elseif (!apcu_fetch("forum_rules_ack_$id") && !hasRecentMessage($id, '2026-02-28')) {
        ?>
        <div class="auto-moderation-notice">
        <?php
        if ($language) {
            ?>
            The forum rules have been recently updated! Please make sure to <a href="forum-rules.php" target="_blank">read them here</a>.
            <?php
        }
        else {
            ?>
            Le règlement du forum a été modifié ! Merci de prendre connaissance des <a href="forum-rules.php">nouvelles règles ici</a>.
            <?php
        }
        ?>
        </div>
        <?php
    }
}

function hasRecentMessage($userId, $since) {
    return mysql_fetch_array(mysql_query('SELECT 1 FROM mkmessages WHERE auteur=' . $userId . ' AND date > "' . $since . '"'));
}