<?php
include('auth.php');
mysql_close();
if (isset($_GET['clear'])) {
    apc_clear_cache();
    header('location: apc.php');
}
$info = apc_cache_info();
if (isset($_GET['full']))
    $res = $info;
else {
    $res = array();
    foreach ($info['cache_list'] as $data)
        $res[$data['info']] = apc_fetch($data['info']);
}
echo '<pre>';
print_r($res);
echo '</pre>';
echo '<br />';
echo '<a href="?clear">Clear cache</a>';
?>