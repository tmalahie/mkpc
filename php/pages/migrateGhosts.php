<?php
if (isset($_SERVER['REMOTE_ADDR'])) exit;
include('../includes/initdb.php');
$getGhosts = mysql_query('SELECT id FROM `mkghosts` ORDER BY id DESC');
while ($ghost = mysql_fetch_array($getGhosts)) {
    $ptsData = array();
    $getTemps = mysql_query('SELECT posX,posY,posZ,rotation,reverse(export_set(flags,"1","0","",4)) AS flags_raw FROM `mkghostdata` WHERE ghost='.$ghost['id'].' ORDER BY frame');
    while ($temps = mysql_fetch_array($getTemps)) {
        $ptData = array($temps['posX'],$temps['posY'],round($temps['posZ'],3),$temps['rotation']);
        $eInfos = $temps['flags_raw'];
        $extra = array();
        if (!empty($eInfos[0]))
            $extra['f'] = 1; // fall
        if (!empty($eInfos[1])) {
            if (!empty($eInfos[2]))
                $extra['d'] = 1; // drift right
            elseif (!empty($eInfos[3]))
                $extra['d'] = -1; // drift left
            else
                $extra['d'] = 0; // drift straight
        }
        if (!empty($extra))
            $ptData[] = $extra;
        $ptsData[] = $ptData;
    }
    mysql_query('INSERT IGNORE INTO `mkghostsdata` SET id="'. $ghost['id'] .'",data="'. mysql_real_escape_string(gzcompress(json_encode($ptsData))) .'"');
    echo $ghost['id']."\n";
}
mysql_close();
?>