<?php
function makePaging($cPage,$nbPages) {
    $intervalle = 3;
	if ($nbPages <= ($intervalle*2+2)) {
        $block = array();
		for ($i=1;$i<=$nbPages;$i++)
            $block[] = $i;
        return [$block];
	}
    $res = array();
    $block = array();
    $debut = $cPage-$intervalle;
    if ($debut <= 1)
        $debut = 1;
    else {
        $block[] = 1;
        if ($debut != 2) {
            $res[] = $block;
            $block = array();
        }
    }
    $fin = $debut + $intervalle*2;
    if ($fin > $nbPages) {
        $fin = $nbPages;
        $debut = $fin-$intervalle*2;
    }
    for ($i=$debut;$i<=$fin;$i++)
        $block[] = $i;
    if ($fin < $nbPages) {
        if ($fin != ($nbPages-1)) {
            $res[] = $block;
            $block = array();
        }
        $block[] = $nbPages;
        $res[] = $block;
    }
    else
        $res[] = $block;
    return $res;
}