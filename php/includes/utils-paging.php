<?php
function makePaging($currentPage, $pagenum, $interval=3) {
	if ($pagenum <= ($interval * 2 + 2)) {
        $block = array();
		for ($i=1;$i<=$pagenum;$i++)
            $block[] = $i;
        return [$block];
	}
    $res = array();
    $block = array();
    $start = intval($currentPage)-$interval;
    if ($start <= 1)
        $start = 1;
    else {
        $block[] = 1;
        if ($start != 2) {
            $res[] = $block;
            $block = array();
        }
    }
    $end = $start + $interval * 2;
    if ($end > $pagenum) {
        $end = $pagenum;
        $start = $end - $interval * 2;
    }
    for ($i=$start;$i<=$end;$i++)
        $block[] = $i;
    if ($end < $pagenum) {
        if ($end != ($pagenum - 1)) {
            $res[] = $block;
            $block = array();
        }
        $block[] = $pagenum;
        $res[] = $block;
    }
    else
        $res[] = $block;
    return $res;
}