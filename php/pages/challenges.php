<?php
include('../includes/getId.php');
include('../includes/language.php');
include('../includes/initdb.php');
require_once('../includes/utils-challenges.php');
if (isset($_GET['cl']))
	$clRace = getClRace($_GET['cl']);
include('../includes/challenge-cldata.php');
if (isset($_GET['clmsg'])) {
	switch ($_GET['clmsg']) {
	case 'challenge_edited':
	case 'challenge_updated':
		$clMsg = $language ? 'Your challenge has been edited':'Votre défi a été modifié';
		break;
	case 'challenge_created':
		$clMsg = $language ? 'Your challenge has been created! In order to check it\'s possible, you now have to succeed it yourself before being allowed to publish it.':'Votre défi a été créé ! Afin de vérifier que celui-ci est faisable, vous devez le réussir vous-même avant de pouvoir le publier.';
		break;
	case 'challenge_published':
		$clMsg = $language ? 'Your publication request has been taken into account. The challenge will be verified by the validation team, you will be notified once it\'s validated.':'Votre demande de publication a été prise en compte. Le défi va être vérifié par l\'équipe de validation, vous serez notifié une fois celui-ci validé.';
		break;
	case 'challenge_actived':
		$clMsg = $language ? 'The challenge has been published.':'Le défi a été publié';
		break;
	case 'challenge_unpublished':
		$clMsg = $language ? 'The challenge has been unpublished':'La publication du défi a été annulée';
		break;
	case 'circuit_required':
		$clError = $language ? 'You must share the circuit before publishing the challenge':'Vous devez partager le circuit avant de publier le défi';
		break;
	}
	unset($_GET['clmsg']);
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/challenges.css" />
<script type="text/javascript" src="scripts/challenges.js?reload=1"></script>
<?php
include('../includes/o_online.php');
?>
<script type="text/javascript">
var publishingChallenge;
var language = <?php echo $language ? 1:0; ?>;
function showClMsg(newMsg) {
	document.location.href = document.location.href.replace(/&clmsg=\w+/g, '')+"&clmsg="+newMsg;
}
function publishChallenge(id, skip_confirm) {
	if (publishingChallenge) return;
	publishingChallenge = true;
	o_xhr("challengeUpdateStatus.php", "challenge="+id+"&status=pending_moderation" + (skip_confirm ? "":"&require_confirmation"), function(res) {
		if (res) {
			if (res == "pending_moderation")
				showClMsg("challenge_published");
			else if (res == "pending_circuit")
				showClMsg("circuit_required");
			else if (res == "active")
				showClMsg("challenge_actived");
			else if (res == "confirmation_required") {
				publishingChallenge = false;
				o_language = language;
				o_confirm(language ?
					"Your challenge will be sent to the validation team.<br />" +
					"Please check that it matches the <a href=\"javascript:challengeReco()\" class=\"pretty-link\">recommendations</a> before confirming.<br />" +
					"In perticular, pointless or too easy challenges will be immediately rejected."
					:
					"Votre défi va être envoyé à l'équipe de validation.<br />" +
					"Veuillez vérifier qu'il suit les <a href=\"javascript:challengeReco()\" class=\"pretty-link\">recommandations</a> avant de confirmer.<br />" +
					"En particulier, les défis sans intérêt ou trop faciles seront immédiatement rejetés."
				, function(ok) {
					if (ok)
						publishChallenge(id, true);
				});
				var $dialog = document.querySelector(".o_dialog_box");
				$dialog.style.width = "500px";
				$dialog.style.maxWidth = "90%";
			}
			else
				document.location.reload();
			return true;
		}
		return false;
	});
}
function unpublishChallenge(id) {
	if (publishingChallenge) return;
	publishingChallenge = true;
	o_xhr("challengeUpdateStatus.php", "challenge="+id+"&status=pending_publication", function(res) {
		if (res) {
			if (res == "pending_publication")
				showClMsg("challenge_unpublished");
			else
				document.location.reload();
			return true;
		}
		return false;
	});
}
function challengeReco() {
	window.open('challengeReco.php','gerer','scrollbars=1, resizable=1, width=500, height=500');
}
document.addEventListener("DOMContentLoaded", initPrettyTitles);
</script>

<title><?php echo $language ? 'Challenge editor':'Éditeur de défis'; ?> - Mario Kart PC</title>
</head>
<body class="challenge-edit-list">
	<h1 class="challenge-main-title"><?php echo $language ? 'Challenge Editor' : 'Éditeur de défis'; ?></h1>
	<?php
	if (!empty($clRace))
		$challenges = listChallenges($clRace['id']);
	if (isset($clError))
		echo '<div class="challenge-msg-error">'. $clError .'</div>';
	if (isset($clMsg))
		echo '<div class="challenge-msg-success">'. $clMsg .'</div>';
	if (empty($challenges)) {
		?>
		<div class="challenge-explain">
		<?php
		if ($language) {
			?>
			Welcome to the challenge editor! A challenge is an action to perform in the circuit, than can be tried out by other players.<br />
			Example of challenge: &quot;Complete the track in less than 30s&quot;, &quot;Finish 1<sup>st</sup> with 100 participants&quot;, &quot;Complete the track without falling&quot;...
			The editor offers you a variety of combinations, which leaves a lot of freedom for the creation.
			It's up to you to find the right combo that makes the challenge fun!<br />
			If you're just starting out, we recommend reading <a href="https://mkpc.malahieude.net/topic.php?topic=7109" class="pretty-link" target="_blank">this topic</a> to make sure you post appropriate challenges.
			<br />
			Happy creating!
			<?php
		}
		else {
			?>
			Bienvenue dans l'éditeur de défis ! Les défis sont des actions à réaliser dans le circuit et qui peuvent être tentés par les autres joueurs.<br />
			Exemple de défi : &quot;Finir le circuit en moins de 30s&quot;, &quot;Finir 1<sup>er</sup> avec 100 participants&quot;, &quot;Finir le circuit sans tomber&quot;...
			L'éditeur offre un grand nombre de combinaisons différentes, ce qui vous laisse un large choix dans la création. À vous de trouver le combo qui rend le défi amusant&nbsp;!<br />
			Si vous débutez, nous vous recommandons de lire <a href="https://mkpc.malahieude.net/topic.php?topic=7109" class="pretty-link" target="_blank">ce topic</a> pour vous assurer de publier des défis appropriés.
			<br />Bonne création !
			<?php
		}
		?>
		</div>
		<div class="main-challenge-actions">
			<?php
			if (!empty($clRace) && in_array($clRace['type'], array('mkcups', 'mkmcups')))
				echo '<a href="'. nextPageUrl('challengeRewards.php') .'" class="other-challenge-action">'. ($language ? 'More rewards...':'Plus de récompenses...') .'</a>';
			?>
			<a class="main-challenge-action" href="<?php echo nextPageUrl('challengeEdit.php'); ?>"><?php echo $language ? 'Create my first challenge':'Créer mon premier défi'; ?> &nbsp; &gt;</a>
		</div>
		<?php
	}
	else {
		?>
		<table class="challenges-table">
			<tr>
				<th class="challenges-td-left"><?php echo $language ? 'Challenge':'Défi'; ?></th>
				<th><?php echo $language ? 'Difficulty':'Difficulté'; ?></th>
				<th><?php echo $language ? 'Status':'Statut'; ?></th>
				<th><?php echo $language ? 'Action':'Action'; ?></th>
			</tr>
			<?php
		foreach ($challenges as $challenge) {
			?>
			<tr>
				<td><?php
				$challengeDesc = $challenge['description'];
				if ($challenge['name'])
					echo '<h3>'.htmlspecialchars($challenge['name']).'</h3>';
				echo '<div class="challenge-description challenge-description-main">'. $challengeDesc['main'] .'</div>';
				if (isset($challengeDesc['extra']))
					echo '<div class="challenge-description challenge-description-extra">'. $challengeDesc['extra'] .'</div>';
				?></td>
				<td class="challenges-td-center challenge-difficulty challenge-difficulty-<?php echo $challenge['difficulty']['level']; ?>">
					<img src="images/challenges/difficulty<?php echo $challenge['difficulty']['level']; ?>.png" alt="<?php echo $challenge['difficulty']['level']; ?>" />
					<?php echo $challenge['difficulty']['name']; ?>
				</td>
				<td class="challenges-td-center"><?php
					$needsAck = false;
					switch ($challenge['status']) {
					case 'pending_completion':
						if (!$challenge['validation']) {
							echo $language ? 'Pending completion':'En attente de réussite';
							echo ' <a href="javascript:void(0)" class="pretty-title" title="'. ($language ? 'You have to succeed the challenge to prove it\'s possible.':'Vous devez réussir le défi pour valider qu\'il n\'est pas impossible.') .'">[?]</a>';
						}
						else {
							$validationData = json_decode($challenge['validation']);
							if (!isset($validationData->ack))
								$needsAck = true;
							echo '<span class="challenge-status-rejected">';
							echo $language ? 'Publication rejected':'Publication refusée';
							echo '</span>';
							echo '<br />';
							echo '<a href="'. nextPageUrl('challengeDetails.php', array('cl' => null, 'ch' => $challenge['id'])) .'">'. ($language ? 'Details':'Détails') .'</a>';
						}
						break;
					case 'pending_publication':
						echo $language ? 'Waiting for publication':'En attente de publication';
						echo '<br />';
						echo '<a href="javascript:publishChallenge('. $challenge['id'] .')">'. ($language ? 'Publish now':'Publier maintenant') .'</a>';
						break;
					case 'pending_moderation':
						echo $language ? 'Waiting for moderation':'En attente de modération';
						echo ' <a href="javascript:void(0)" class="pretty-title" title="'. ($language ? 'The challenge will be verified by a moderator. You will be notified as soon as it\'s validated.':'Le défi va être vérifié par un modérateur. Vous serez notifié dès qu\'il sera validé.') .'">[?]</a>';
						echo '<br />';
						echo '<a href="javascript:unpublishChallenge('. $challenge['id'] .')">'. ($language ? 'Cancel publication request':'Annuler la demande de publication') .'</a>';
						break;
					case 'active':
						echo '<span class="challenge-status-active">';
						echo $language ? 'Active':'Actif';
						echo '</span>';
						if ($challenge['validation']) {
							$validationData = json_decode($challenge['validation']);
							if (isset($validationData->old_difficulty) && !isset($validationData->ack)) {
								echo '<br />';
								echo '<span class="challenge-status-warning">';
								echo '⚠️ ';
								echo $language ? 'Difficulty change':'Difficulté modifiée';
								echo '</span>';
								echo ' ';
								echo '<a href="'. nextPageUrl('challengeDetails.php', array('cl' => null, 'ch' => $challenge['id'])) .'">['. ($language ? 'Details':'Détails') .']</a>';
							}
						}
						break;
					case 'deleted':
						echo $language ? 'Deleted':'Supprimé';
						break;
					}
				?></td>
				<td class="challenges-td-center">
					<?php
					if ($needsAck)
						echo '<a href="'. nextPageUrl('challengeDetails.php', array('cl' => null, 'ch' => $challenge['id'])) .'">'. ($language ? 'Details':'Détails') .'</a>';
					else {
						?>
						<a class="challenge-action-edit" href="<?php echo nextPageUrl('challengeEdit.php', array('cl' => null, 'ch' => $challenge['id'])); ?>"><?php echo $language ? 'Edit':'Modifier'; ?></a><br />
						<a class="challenge-action-del" href="<?php echo nextPageUrl('challengeDel.php', array('cl' => null, 'ch' => $challenge['id'])); ?>" onclick="return confirm('<?php echo $language ? 'Delete this challenge?':'Supprimer ce défi ?'; ?>')"><?php echo $language ? 'Delete':'Supprimer'; ?></a>
						<?php
					}
					?>
				</td>
			</tr>
			<?php
		}
		?>
		</table>
		<div class="main-challenge-actions">
			<a class="main-challenge-action" href="<?php echo nextPageUrl('challengeEdit.php'); ?>">+ &nbsp;<?php echo $language ? 'Create another challenge':'Créer un autre défi'; ?> &nbsp;</a>
			<a href="<?php echo nextPageUrl('challengeRewards.php'); ?>" class="other-challenge-action"><?php echo $language ? 'More rewards...':'Plus de récompenses...'; ?></a>
		</div>
		<div class="pub">
			<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
			<!-- Mario Kart PC -->
			<ins class="adsbygoogle"
			     style="display:inline-block;width:468px;height:60px"
			     data-ad-client="ca-pub-1340724283777764"
			     data-ad-slot="6691323567"></ins>
			<script>
			(adsbygoogle = window.adsbygoogle || []).push({});
			</script>
		</div>
		<?php
	}
	?>
	<div class="challenge-navigation">
		<a href="<?php echo backCircuitUrl(); ?>"">&lt; <u><?php echo backCircuitText(); ?></u></a>
	</div>
</body>
</html>
<?php
mysql_close();
?>