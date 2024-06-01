<?php
function checkMessageContent($msg) {
    global $language, $identifiants;
    $maxCharacters = 65536;
    if (isset($identifiants)) {
        if ($getSize = mysql_fetch_array(mysql_query('SELECT message_size FROM `mkidentifiants` WHERE identifiant='.$identifiants[0].' AND message_size IS NOT NULL')))
            $maxCharacters = +$getSize['message_size'];
    }
    if (strlen($msg) >= $maxCharacters) {
        return array(
            'success' => false,
            'reason' => $language ? "Your message is too long (max $maxCharacters characters)" : "Votre message est trop long (max $maxCharacters caractères)"
        );
    }
    return array('success' => true);
}
function printCheckFailDetails($data) {
    ?>
    <div class="auto-moderation-notice">
        <?php echo htmlspecialchars($data['reason']); ?>
    </div>
    <?php
}