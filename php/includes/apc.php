<?php
if (!function_exists('apcu_fetch')) {
    $apcStore = array();
    function apcu_fetch($key) {
        global $apcStore;
        return isset($apcStore[$key]) ? $apcStore[$key]:false;
    }
    function apcu_store($key,$value,$ttl=0) {
        global $apcStore;
        $apcStore[$key] = $value;
    }
    function apcu_inc($key, $inc=1, &$success=null, $ttl=0) {
        global $apcStore;
        if (isset($apcStore[$key]))
            $apcStore[$key] += $inc;
        else
            $apcStore[$key] = $inc;
        $success = true;
        return $apcStore[$key];
    }
    function apcu_delete($key) {
        global $apcStore;
        unset($apcStore[$key]);
    }
    function apcu_add($key,$value,$ttl=0) {
        global $apcStore;
        $apcStore[$key] = $value;
        return true;
    }
}