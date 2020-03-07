<?php
$res = array();
if (isset($_POST['challenge']) && isset($_POST['rating'])) {
	$rating = $_POST['rating'];
	if ($rating >= 0 && $rating <= 5) {
		include('session.php');
		if ($id) {
			include('initdb.php');
			if ($challenge = mysql_fetch_array(mysql_query('SELECT id,clist FROM mkchallenges WHERE id="'. $_POST['challenge'] .'"'))) {
				include('challengeMyRate.php');
				$myRate = getMyRating($challenge);
				if ($myRate) {
					mysql_query('UPDATE `mkclwin` SET rating='. $rating .' WHERE challenge='.$challenge['id'].' AND player='. $myRate['player']);
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