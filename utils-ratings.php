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
	$getNotes = mysql_query("SELECT rating FROM `mkratings` WHERE type='$type' AND circuit='$circuitId'");
	$total = 0;
	$nbNotes = 0;
	$nbByRating = array();
	$K = 5;
	for ($i=1;$i<=$K;$i++)
		$nbByRating[$i] = 0;
	while ($ratings = mysql_fetch_array($getNotes)) {
		$total += $ratings['rating'];
		$nbByRating[$ratings['rating']]++;
		$nbNotes++;
	}
	if ($nbNotes) {
		$nNote = ($total/$nbNotes);
		$za_2 = 1.65;
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