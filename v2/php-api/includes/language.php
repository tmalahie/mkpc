<?php
function getLanguage() {
    global $_myLanguage;
    if (isset($_myLanguage))
        return $_myLanguage;
    include('../../../language.php');
    $_myLanguage = $language;
    return $language;
}