<?php
header('Content-Type: text/plain');
$res = array();
if (isset($_POST['challenge']) && isset($_POST['rating'])) {
	$rating = intval($_POST['rating']);
	if ($rating >= 0 && $rating <= 5) {
		include('../includes/session.php');
		if ($id) {
			include('../includes/initdb.php');
			if ($challenge = mysql_fetch_array(mysql_query('SELECT id,clist FROM mkchallenges WHERE id="'. $_POST['challenge'] .'"'))) {
				include('../includes/challengeMyRate.php');
				$myRate = getMyRating($challenge);
				if ($myRate) {
					$q = mysql_query('UPDATE `mkclwin` SET rating='. $rating .' WHERE challenge='.$challenge['id'].' AND player='. $myRate['player']);
					if (mysql_affected_rows()) {
						if ($getChallengeStats = mysql_fetch_array(mysql_query('SELECT IFNULL(AVG(rating),0) AS avgrating, COUNT(*) AS nbratings FROM mkclwin WHERE challenge='. $challenge['id'] .' AND rating>0')))
							mysql_query('UPDATE mkchallenges SET avgrating='.$getChallengeStats['avgrating'].',nbratings='.$getChallengeStats['nbratings'].' WHERE id='.$challenge['id']);
					}
				}
			}
			mysql_close();
		}
		echo 1;
	}
}
?>