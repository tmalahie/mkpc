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
    // getCourse.php relies on apcu_add as a lock between concurrent requests, so unlike the
    // rest of this fallback it has to hold across requests: an exclusive file create does.
    function apcu_add($key,$value,$ttl=0) {
        global $apcStore;
        $lock = sys_get_temp_dir().'/mkpc-apcu-'.md5($key);
        if ($ttl && file_exists($lock) && (filemtime($lock)+$ttl < time()))
            @unlink($lock);
        $handle = @fopen($lock, 'x');
        if (!$handle)
            return false;
        fclose($handle);
        $apcStore[$key] = $value;
        return true;
    }
}