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
            Looks like this is your first message to MKPC! Welome to the forum, if you haven't done it yet, please have a look at the <a href="topic.php?topic=2448" target="_blank">rules</a> before proceeding.
            <?php
        }
        else {
            ?>
            Il semble que ceci est votre 1er message sur MKPC ! Bienvenue sur le forum, si ce n'est pas déjà fait, pensez à consulter le <a href="topic.php?topic=2448">règlement</a> avant de continuer.
            <?php
        }
        ?>
        </div>
        <?php
    }
}