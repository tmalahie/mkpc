<?php
if ($isCup) {
	if ($isMCup) {
		echo 'var cupScore = 0;';
		echo 'var ptsGP = "';
		$ptsGP = array();
		if (!empty($cupIDs)) {
			$getScores = mysql_query('SELECT cup,score FROM `mkwins` WHERE cup IN ('. implode(',',$cupIDs) .') AND identifiant="'.$identifiants[0].'" AND identifiant2="'.$identifiants[1].'" AND identifiant3="'.$identifiants[2].'" AND identifiant4="'.$identifiants[3].'"');
			while ($getScore = mysql_fetch_array($getScores))
				$ptsGP[$getScore['cup']] = $getScore['score'];
			foreach ($cupIDs as $i => $cupID)
				echo (isset($ptsGP[$cupID]) ? $ptsGP[$cupID]:0);
		}
		echo '";';
	}
	else {
		$cupScore = 0;
		if ($nid) {
			if ($getScore = mysql_fetch_array(mysql_query('SELECT score FROM `mkwins` WHERE cup="'. $nid .'" AND identifiant="'.$identifiants[0].'" AND identifiant2="'.$identifiants[1].'" AND identifiant3="'.$identifiants[2].'" AND identifiant4="'.$identifiants[3].'"')))
				$cupScore = $getScore['score'];
		}
		echo 'var cupScore = '. $cupScore .';';
	}
}
?>