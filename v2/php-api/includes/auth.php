<?php
include('initdb.php');
require_once('../../../credentials.php');
include('../../../utilId.php');
function getUserId()
{
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
