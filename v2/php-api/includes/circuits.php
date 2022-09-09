<?php
include('../../../circuitEscape.php');
require_once('../includes/language.php');
function getCircuitNames() {
    $language = getLanguage();
    include('../../../circuitNames.php');
    return $circuitNames;
}