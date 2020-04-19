<?php
if (!function_exists('apc_fetch')) {
    $apcStore = array();
    function apc_fetch($key) {
        global $apcStore;
        return isset($apcStore[$key]) ? $apcStore[$key]:null;
    }
    function apc_store($key,$value,$ttl=0) {
        global $apcStore;
        $apcStore[$key] = $value;
    }
    function apc_delete($key) {
        global $apcStore;
        unset($apcStore[$key]);
    }
}