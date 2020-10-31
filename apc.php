<?php
if (!function_exists('apcu_fetch')) {
    $apcStore = array();
    function apcu_fetch($key) {
        global $apcStore;
        return isset($apcStore[$key]) ? $apcStore[$key]:null;
    }
    function apcu_store($key,$value,$ttl=0) {
        global $apcStore;
        $apcStore[$key] = $value;
    }
    function apcu_delete($key) {
        global $apcStore;
        unset($apcStore[$key]);
    }
}