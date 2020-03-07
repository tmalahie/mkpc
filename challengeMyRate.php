<?php
function getMyRating($challenge) {
	global $id;
	if (!$id)
		return null;
	if ($getWin = mysql_fetch_array(mysql_query('SELECT player,rating FROM `mkclwin` WHERE challenge='. $challenge['id'] .' AND player='. $id .' AND creator=0'))) {
		if ($getWin['rating'])
			return $getWin;
	}
	if ($getIdentifiants = mysql_fetch_array(mysql_query('SELECT identifiant,identifiant2,identifiant3,identifiant4 FROM `mkprofiles` WHERE id='. $id))) {
		if ($cList = mysql_fetch_array(mysql_query('SELECT identifiant,identifiant2,identifiant3,identifiant4 FROM `mkclrace` WHERE id='. $challenge['clist']))) {
			if (($cList['identifiant'] == $getIdentifiants['identifiant']) && ($cList['identifiant2'] == $getIdentifiants['identifiant2']) && ($cList['identifiant3'] == $getIdentifiants['identifiant3']) && ($cList['identifiant4'] == $getIdentifiants['identifiant4'])) {
				return null;
			}
		}
		if ($getWin = mysql_fetch_array(mysql_query('SELECT w.player,w.rating FROM mkclwin w INNER JOIN mkprofiles p ON w.player=p.id AND w.player!="'.$id.'" AND w.rating>0 WHERE p.identifiant='. $getIdentifiants['identifiant'] .' AND p.identifiant2='. $getIdentifiants['identifiant2'] .' AND p.identifiant3='. $getIdentifiants['identifiant3'] .' AND p.identifiant4='. $getIdentifiants['identifiant4'])))
			return $getWin;
		return array(
			'player' => $id,
			'rating' => 0
		);
	}
}
?>