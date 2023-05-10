<?php
include('../includes/getId.php');
include('../includes/language.php');
include('../includes/session.php');
include('../includes/initdb.php');
if ($id) {
	$notifs = array(
		'forum_mention' => $language ? 'Mention on a forum message':'Mention dans un message sur le forum',
		'forum_quote' => $language ? 'Quote on a forum message':'Citation dans un message sur le forum',
		'reaction_topic' => $language ? 'Reaction to a forum message':'Réaction à un message sur le forum',
		'circuit_comment' => $language ? 'Answer on one of your circuits':'Commentaire sur un de vos circuits',
		'answer_comment' => $language ? 'Answer on circuit comment':'Réponse à un commentaire sur un circuit',
		'reaction_trackcom' => $language ? 'Reaction on a circuit comment':'Réaction à un commentaire sur un circuit',
		'news_comment' => $language ? 'Comment on your news':'Commentaire sur une de vos news',
		'reaction_news' => $language ? 'Reaction on your news':'Réaction sur une de vos news',
		'answer_newscom' => $language ? 'Answer to a comment on a news':'Réponse à un commentaire sur une news',
		'reaction_newscom' => $language ? 'Reaction to a comment on a news':'Réaction à un commentaire sur une news',
		'follower_topic' => $language ? 'Topic published by a member you follow':'Topic publié par un membre que vous suivez',
		'follower_circuit' => $language ? 'Circuit published by a member you follow':'Circuit publié par un membre que vous suivez',
		'follower_challenge' => $language ? 'Challenge published by a member you follow':'Défi publié par un membre que vous suivez',
		'follower_news' => $language ? 'News published by a member you follow':'News publiée par un membre que vous suivez',
		'follower_perso' => $language ? 'Character published by a member you follow':'Perso publié par un membre que vous suivez',
		'currently_online' => $language ? 'A member you follow is playing online':'Un membre suivi joue actuellement en ligne',
		'new_record' => $language ? 'Record broken in Time Trial':'Record battu en contre-la-montre',
		'new_followuser' => $language ? 'New profile follower':'Nouvel abonné à votre profil',
		'new_followtopic' => $language ? 'New subscriber to your topic':'Nouvel abonnement à votre topic'
	);
	require_once('../includes/getRights.php');
	if (hasRight('moderator')) {
		$notifs['admin_report'] = $language ? 'Reported messages on the forum' : 'Messages signalés sur le forum';
	}
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$getNotifMute = mysql_query('DELETE FROM `mknotifmute` WHERE user="'. $id .'"');
		foreach ($notifs as $type => $notif) {
			if (!isset($_POST[$type]))
				mysql_query('INSERT INTO `mknotifmute` VALUES('. $id .',"'. $type .'")');
		}
		$success = $language ? 'Settings updated successfully':'Paramètres mis à jour avec succès';
	}
	$disabled = array();
	$getNotifMute = mysql_query('SELECT type FROM `mknotifmute` WHERE user="'. $id .'"');
	while ($notifMute = mysql_fetch_array($getNotifMute))
		$disabled[$notifMute['type']] = true;
	?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Notification settings':'Paramètres de notifications'; ?> - Mario Kart PC</title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/profil.css" />
<link rel="stylesheet" type="text/css" href="styles/forms.css" />
<script type="text/javascript" src="scripts/topic.js"></script>
<?php
include('../includes/o_online.php');
?>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'forum';
include('../includes/menu.php');
?>
<main>
<form method="post" class="advanced-search" action="notif-settings.php">
	<h1><?php echo $language ? 'Notification settings':'Paramètres de notification'; ?></h1>
	<?php
	if (isset($success))
		echo '<div class="success">'. $success .'</div>';
	?>
	<p>
		<?php echo $language ? 'Indicate here the type of notifications you want to receive' : 'Renseignez ici les types de notifications que vous souhaitez recevoir'; ?>
	</p>
	<table>
		<?php
		foreach ($notifs as $type => $notif) {
			?>
			<tr>
				<td class="ligne">
					<input type="checkbox" name="<?php echo $type; ?>" id="<?php echo $type; ?>"<?php if (!isset($disabled[$type])) echo ' checked="checked"' ?> />
				</td>
				<td>
					<label for="<?php echo $type; ?>">
					<?php echo $notif; ?>
					</label>
				</td>
			</tr>
			<?php
		}
		?>
		<tr>
			<td colspan="2">
				<input type="submit" class="action_button" value="<?php echo $language ? 'Submit':'Valider'; ?>" />
			</td>
		</tr>
	</table>
	<p class="forumButtons">
		<a href="profil.php?id=<?php echo $id; ?>"><?php echo $language ? 'Back to your profile':'Retour à votre profil'; ?></a><br />
		<a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a>
	</p>
</form>
</main>
<?php
include('../includes/footer.php');
mysql_close();
}
?>
</body>
</html>