<?php
require_once('circuitEnumsQuick.php');
$lettres = Array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'o', 't');
$nbLettres = count($lettres);
function getLetterPrefix($l,$k) {
    if ($k)
        return $l.$k.'_';
    return $l;
}
function getLetterPrefixD($l,&$info) {
    return getLetterPrefix($l,isset($info['t']) ? $info['t']:null);
}
function getLetterPrefixes($l,$map) {
    global $decorTypes;
    if ('t' === $l)
        return count($decorTypes[$map]);
    return 1;
}
?>