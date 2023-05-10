<?php
include('../includes/getId.php');
include('../includes/language.php');
include('../includes/initdb.php');
require_once('../includes/utils-challenges.php');
if (isset($_GET['ch'])) {
	$challenge = getChallenge($_GET['ch']);
	if ($challenge)
		$clRace = getClRace($challenge['clist']);
}
if (empty($clRace)) {
	mysql_close();
	exit;
}
include('../includes/challenge-cldata.php');
mysql_query('DELETE FROM `mknotifs` WHERE identifiant="'. $clRace['identifiant'] .'" AND identifiant2="'. $clRace['identifiant2'] .'" AND identifiant3="'. $clRace['identifiant3'] .'" AND identifiant4="'. $clRace['identifiant4'] .'" AND type="challenge_moderated" AND link="'. $challenge['id'] .'"');
$challengeDetails = getChallengeDetails($challenge);
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/challenges.css" />
<script type="text/javascript" src="scripts/jquery.min.js"></script>
<?php
include('../includes/o_online.php');
?>

<title><?php echo $language ? 'Challenge moderation result':'Modération défi'; ?> - Mario Kart PC</title>
</head>
<body class="challenge-moderation-details">
	<h1 class="challenge-main-title"><?php echo $language ? 'Challenge moderation result' : 'Résultat de la modération'; ?></h1>
	<div>
		<div class="challenge-moderation">
			<?php
			if ($challenge['validation']) {
				$validation = json_decode($challenge['validation']);
				if (is_array($validation)) $validation = new \stdClass();
				if ('active' === $challenge['status']) {
					?>
					<div class="challenge-moderation-success">
						<?php
						echo $language ? 'Congratulations, your challenge has been <strong>accepted</strong>!':'Félicitations, votre défi a été <strong>accepté</strong> !';
						if (isset($validation->old_difficulty)) {
							echo '<br />';
							echo '<small>';
							$difficulties = getChallengeDifficulties();
							echo $language ? 'The difficulty has been changed from <strong>'. $difficulties[$validation->old_difficulty] .'</strong> to <strong>'.$difficulties[$challenge['difficulty']].'</strong>':'Le niveau de difficulté a été changé de <strong>'. $difficulties[$validation->old_difficulty] .'</strong> à <strong>'.$difficulties[$challenge['difficulty']].'</strong>';
							if (!empty($validation->msg)) {
								echo '<br />';
								echo $language ? 'The moderator has left a comment to justify this:':'Le modérateur a laisse ce commentaire pour justifier cela :';
								echo '<br />';
								echo '<em>'. $validation->msg .'</em>';
							}
							echo '</small>';
						}
						?>
					</div>
					<?php
				}
				else {
					?>
					<div class="challenge-moderation-error">
						<?php
						echo $language ? 'Sorry, your challenge has been rejected by moderation team':'Désolé, votre défi a été refusé par l\'équipe de modération';
						if (!empty($validation->msg)) {
							echo $language ? ' for the following reason:':' pour la raison suivante :';
							echo '<br />';
							echo '<em>'. $validation->msg .'</em>';
						}
						else
							echo '...';
						echo '<br />';
						echo $language ? 'You can still edit the challenge to make it be re-evaluated.':'Vous pouvez toujours modifier le défi pour qu\'il repasse en modération.';
						echo '<small>';
						echo $language ? 'Please re-publish your challenge once you actually fixed the reported problem. In case of doubt, refer to this <a class="pretty-link" target="_blank" href="topic.php?topic=7109">forum topic</a> to see the recommendations about challenges.':'Merci de ne republier votre défi qu\'une fois que vous avez corrigé le problème remonté. En cas de doute, référez-vous à <a class="pretty-link" target="_blank" href="topic.php?topic=7109">ce topic</a> du forum pour voir les recommandations sur les défis.';
						echo '</small>';
						?>
					</div>
					<?php
				}
				if (!isset($validation->ack)) {
					$validation->ack = time();
					$validationJson = mysql_real_escape_string(json_encode($validation));
					mysql_query('UPDATE mkchallenges SET validation="'.$validationJson.'" WHERE id="'. $challenge['id'] .'"');
				}
			}
			?>
		</div>
	</div>
	<div>
		<div class="challenge-description">
		<?php
		echo '<h2>'. ($language ? 'Challenge details':'Détails du défi') .'</h2>';
		echo $challengeDetails['description']['main'];
		if (isset($challengeDetails['description']['extra']))
			echo '<div><small>'. $challengeDetails['description']['extra'] .'</small></div>';
		if ($challengeDetails['name'])
			echo '<div>'. ($language ? 'Name:':'Nom :') .' '. htmlspecialchars($challengeDetails['name']) .'</div>';
		echo '<div>'. ($language ? 'Difficulty:':'Difficulté :') .' '. htmlspecialchars($challengeDetails['difficulty']['name']) .'</div>';
		?>
		</div>
	</div>
	<div class="challenge-navigation">
		<a href="<?php echo nextPageUrl('challenges.php', array('ch'=>null,'cl'=>empty($clRace)?null:$clRace['clid'])); ?>">&lt; <u><?php echo $language ? 'Back to challenges list':'Retour à la liste des défis'; ?></u></a>
	</div>
</body>
</html>
<?php
mysql_close();
?>