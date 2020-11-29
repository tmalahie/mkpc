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