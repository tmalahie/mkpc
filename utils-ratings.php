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
	global $language, $id, $cNote;
	if (isset($id)) $aId = $id;
	include('session.php');
	if ($id) {
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
	if (isset($aId)) $id = $aId;
}
function recomputeRating($type, $circuitId) {
	$K = 5;
	$za_2 = 1.65;
	include('circuitTables.php');
	if (!in_array($type, $circuitTables))
		return;

	mysql_query('DROP TEMPORARY TABLE IF EXISTS tmp_rating');
	mysql_query(
		'CREATE TEMPORARY TABLE tmp_rating
		SELECT p.type,p.circuit,p.rating,p.pscore,p.nb_of_rating AS nb_of_rating,p.nb_of_rating*15/(15+DATEDIFF(CURDATE(),IFNULL(c1.publication_date,"2018-01-01"))) AS weight FROM
		(SELECT t.type,t.circuit,t.nb,o.rating,o.pscore,COUNT(r.rating) AS nb_of_rating FROM
		((SELECT type,circuit,COUNT(id) AS nb FROM mkratings WHERE type="'. $type .'" AND circuit="'. $circuitId .'" GROUP BY type,circuit) t
		INNER JOIN
		(SELECT rating,pscore FROM mkratingoptions) o
		LEFT JOIN
		(SELECT type,circuit,rating,date FROM mkratings WHERE type="'. $type .'" AND circuit="'. $circuitId .'") r ON t.type=r.type AND t.circuit=r.circuit AND r.rating=o.rating) GROUP BY t.type,t.circuit,o.rating) p
		LEFT JOIN `'. $type .'` c1 ON p.type="'. $type .'" AND p.circuit=c1.id'
	);

	mysql_query('DROP TEMPORARY TABLE IF EXISTS tmp_pscore');
	mysql_query(
		'CREATE TEMPORARY TABLE tmp_pscore
		SELECT s.type,s.circuit, s.note,s.nbnotes, s.sigma2 - '.$za_2.'*SQRT((s.sigma1-s.sigma2*s.sigma2)/(s.sum+'.$K.'+1)) AS pscore
		FROM
		(SELECT p.type,p.circuit,p2.sum,
		SUM((p.pscore*p.pscore)*(p.weight+1)/(p2.sum+'.$K.')) AS sigma1,
		SUM(p.pscore*(p.weight+1)/(p2.sum+'.$K.')) AS sigma2,
		SUM(p.rating*p.nb_of_rating)/SUM(p.nb_of_rating) AS note,
		SUM(p.nb_of_rating) AS nbnotes
		FROM tmp_rating p INNER JOIN (SELECT type,circuit,SUM(weight) AS sum FROM tmp_rating GROUP BY type,circuit) p2 ON p.type=p2.type AND p.circuit=p2.circuit
		GROUP BY p.type,p.circuit) s;'
	);

	mysql_query(
		'UPDATE `'.$type.'` c1
		LEFT JOIN tmp_pscore p ON p.type="'.$type.'" AND p.circuit=c1.id
		SET c1.pscore=IFNULL(p.pscore,0),c1.note=IFNULL(p.note,0),c1.nbnotes=IFNULL(p.nbnotes,0)
		WHERE c1.id="'. $circuitId .'";'
	);
}