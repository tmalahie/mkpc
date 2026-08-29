<?php
function isNickBlacklisted($nick) {
    $nick = strtolower($nick);
    $getBlacklist = mysql_query('SELECT word FROM `mkbadnicks`');
    while ($badNick = mysql_fetch_array($getBlacklist)) {
        if (strpos($nick, strtolower($badNick['word'])) !== false)
            return true;
    }
    return false;
}
function editNick($userId,$old,$new,&$message,$allowBlacklisted=false) {
    global $language;
    if (!$new)
        $message = $language ? 'Please enter a username':'Veuillez entrer un pseudo';
    elseif (!preg_match('#^[a-zA-Z0-9_\-]+$#', $new))
        $message = $language ? 'The username mustn\'t contain special chars.<br />Allowed chars are: letters, numbers, the dash - and the underscore _':'Le pseudo ne doit pas contenir de caract&egrave;res spéciaux.<br />Les caract&egrave;res autoris&eacute;s sont les lettres sans accents, les chiffres, le tiret - et le underscore _';
    elseif (!$allowBlacklisted && isNickBlacklisted($new))
        $message = $language ? 'This username is inappropriate.':'Ce pseudo est inapproprié.';
    elseif (mysql_numrows(mysql_query('SELECT * FROM `mkjoueurs` WHERE nom="'.$new.'" AND id!='. $userId)))
        $message = $language ? 'This username already exists':'Ce pseudo existe déjà';
    else {
        if ($old !== $new) {
            mysql_query('UPDATE `mkjoueurs` SET nom="'. $new .'" WHERE id='. $userId);
            mysql_query('UPDATE `mkprofiles` SET nick_color="'. $new .'" WHERE id='. $userId);
            mysql_query('INSERT IGNORE INTO `mknewnicks` VALUES("'. $old .'",'. $userId .',NULL)');
        }
        return true;
    }
}
