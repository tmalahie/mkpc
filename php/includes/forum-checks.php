<?php
function checkMessageContent($msg) {
    global $language;
    $maxCharacters = 65536;
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