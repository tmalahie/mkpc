<?php
function getMyRating($type, $circuitId) {
    global $identifiants;
    $getRatings = mysql_query('SELECT rating FROM `mkratings` WHERE type="'. $type .'" AND circuit="'. $circuitId .'" AND identifiant='.$identifiants[0]);
	if ($getRating = mysql_fetch_array($getRatings))
		return $getRating['rating'];
	else
		return 0;
}
function printRatingView($title) {
	global $language, $cNote;
	?>
	<p id="markMsg"><?php echo $title; ?></p>
	<?php
	function addStar($i, $a, $apreciation) {
		echo '&nbsp;<img id="star'.$i.'" class="star" src="images/star'.$a.'.png" onclick="setMark('.$i.')" onmouseover="previewMark('.$i.')" onmouseout="updateMark()" title="'.HTMLentities($apreciation).'" /> ';
	}
	$apreciations = $language ? Array(null, 'Very bad', 'Bad', 'Average', 'Good', 'Excellent'):Array(null, 'Très mauvais', 'Mauvais', 'Moyen', 'Bon', 'Excellent');
	for ($i=1;$i<=$cNote;$i++)
		addStar($i, 1, $apreciations[$i]);
	for ($i=$cNote+1;$i<=5;$i++)
		addStar($i, 0, $apreciations[$i]);
	?><br />
	<input type="button" id="submitMark" value="<?php echo $language ? 'Submit':'Valider'; ?>" disabled="disabled" class="cannotChange" onclick="sendMark();" /></td>
	<?php
}
function recomputeRating($type, $circuitId) {
	include('circuitTables.php');
	if (!in_array($type, $circuitTables))
		return;
	
	$K = 5;
	$za_2 = 1.65;

	mysql_query('DROP TEMPORARY TABLE IF EXISTS tmp_rating');
	mysql_query(
		'CREATE TEMPORARY TABLE tmp_rating
		SELECT p.type,p.circuit,p.tscore,p.nb_of_rating AS nb_of_rating,p.nb_of_rating*1/(1+POW(TIMESTAMPDIFF(SECOND,IFNULL(c1.publication_date,"2018-01-01"),NOW())/(76*3600),2)) AS weight FROM
		(SELECT t.type,t.circuit,t.nb,o.rating,o.tscore,COUNT(r.rating) AS nb_of_rating FROM
		((SELECT type,circuit,COUNT(id) AS nb FROM mkratings WHERE type="'. $type .'" AND circuit="'. $circuitId .'" GROUP BY type,circuit) t
		INNER JOIN
		(SELECT rating,tscore FROM mkratingoptions) o
		LEFT JOIN
		(SELECT type,circuit,rating,date FROM mkratings WHERE type="'. $type .'" AND circuit="'. $circuitId .'") r ON t.type=r.type AND t.circuit=r.circuit AND r.rating=o.rating) GROUP BY t.type,t.circuit,o.rating) p
		LEFT JOIN `'. $type .'` c1 ON p.type="'. $type .'" AND p.circuit=c1.id'
	);

	mysql_query('DROP TEMPORARY TABLE IF EXISTS tmp_tscore');
	mysql_query(
		'CREATE TEMPORARY TABLE tmp_tscore
		SELECT s.type,s.circuit, s.sigma2 - '.$za_2.'*SQRT((s.sigma1-s.sigma2*s.sigma2)/(s.sum+'.$K.'+1)) AS tscore
		FROM
		(SELECT p.type,p.circuit,p2.sum,
		SUM((p.tscore*p.tscore)*(p.weight+1)/(p2.sum+'.$K.')) AS sigma1,
		SUM(p.tscore*(p.weight+1)/(p2.sum+'.$K.')) AS sigma2
		FROM tmp_rating p INNER JOIN (SELECT type,circuit,SUM(weight) AS sum FROM tmp_rating GROUP BY type,circuit) p2 ON p.type=p2.type AND p.circuit=p2.circuit
		GROUP BY p.type,p.circuit) s;'
	);

	mysql_query(
		'UPDATE `'.$type.'` c1
		LEFT JOIN tmp_tscore p ON p.type="'.$type.'" AND p.circuit=c1.id
		SET c1.tscore=IFNULL(p.tscore,0)
		WHERE c1.id="'. $circuitId .'";'
	);
	
	$getNotes = mysql_query("SELECT rating FROM `mkratings` WHERE type='$type' AND circuit='$circuitId'");
	$total = 0;
	$nbNotes = 0;
	$nbByRating = array();
	for ($i=1;$i<=$K;$i++)
		$nbByRating[$i] = 0;
	while ($ratings = mysql_fetch_array($getNotes)) {
		$total += $ratings['rating'];
		$nbByRating[$ratings['rating']]++;
		$nbNotes++;
	}
	if ($nbNotes) {
		$nNote = ($total/$nbNotes);
		$sigma1 = 0;
		$sigma2 = 0;
		for ($i=1;$i<=$K;$i++) {
			$sigma1 += ($i*$i)*($nbByRating[$i]+1)/($nbNotes+$K);
			$sigma2 += $i*($nbByRating[$i]+1)/($nbNotes+$K);
		}
		$pScore = $sigma2 - $za_2*sqrt(($sigma1-$sigma2*$sigma2)/($nbNotes+$K+1));
	}
	else {
		$nNote = 0;
		$pScore = 0;
	}

	mysql_query('UPDATE `'.$type.'` SET note='.$nNote.',nbnotes='.$nbNotes.',pscore='.$pScore.' WHERE id="'.$circuitId.'"');
}