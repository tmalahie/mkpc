<?php
include('initdb.php');
require_once('../../../credentials.php');
include('../../../utilId.php');
function getUserId() {
    global $_sessionUserId;
    if (isset($_sessionUserId))
        return $_sessionUserId;
    include('../../../session.php');
    $_sessionUserId = +$id;
    return $_sessionUserId;
}
function getMkIds()
{
    global $identifiants, $mkSalt;
    if (isset($identifiants))
        return $identifiants;
    include('../../../getId.php');
    return $identifiants;
}
$aID = isset($id) ? $id : null;
$id = null;
$id = $aID;
require_once('../../../getRights.php');
function hasUserRights($id,$key) {
    static $userRightsCache = array();
    if (!isset($userRightsCache[$id]))
        $userRightsCache[$id] = getUserRights($id);
    return isset($userRightsCache[$id][$key]);
}