<?php
include('../includes/language.php');
?>
<!DOCTYPE html>
<html lang="<?= P_("html language", "en") ?>">
	<head> 
		<title><?= _('Help: forum ranks') ?></title> 
		<meta charset="utf-8" /> 
		<link rel="stylesheet" type="text/css" href="styles/forum.css" />
		<link rel="stylesheet" type="text/css" href="styles/main.css" />
		<style type="text/css">
			.mBody > span {
				display: inline-block;
			}

			table {
				margin: 0 auto;
				border-collapse: collapse;
				width: 100%;
			}
		</style>
	</head> 
	<body>
		<main>
			<h1><?= _('Help: forum ranks') ?></h1>
			<div class="fMessages">
			<div class="fMessage">
			<div class="mContent">
			<div class="mBody">
				<?php if ($language): ?>
					Each player is assigned a <em>rank</em>, represented by a <em>character</em>, based on the number of messages they post on the website's <a href="forum.php" target="_blank">forum</a>.Keep posting to unlock reach the next rank!<br /><br />
					List of forum ranks:<br /><br />
				<?php else: ?>
					Chaque joueur se voit attribuer un <em>rang</em> caractérisé par un personnage de Mario en fonction du nombre de messages postés sur le <a href="forum.php" target="_blank">forum</a> du site. Postez des messages pour obtenir de nouveaux persos !<br /><br />
					Liste des rangs du forum :<br />
				<?php endif; ?>
				
				<table border="1" cellpadding="5" cellspacing="0">
					<thead>
						<?php if ($language): ?>
							<tr>
								<th>Message Count</th>
								<th>Name</th>
								<th>Icon</th>
							</tr>
						<?php else: ?>
							<tr>
								<th>Nombre de messages</th>
								<th>Nom</th>
								<th>Icône</th>
							</tr>
						<?php endif; ?>
					</thead>
					<tbody>
						<?php
						include('../includes/avatars.php');
						$lastmsgs = 0;
						$keys = array_keys($FORUM_RANKS);
						for ($i = 0; $i < count($keys); $i++) {
							$msgs = $keys[$i];
							$nextmsgs = $keys[$i + 1] ?? null;
						
							if ($msgs == 0) {
								$msgsRange = "Score &lt; $nextmsgs";
							} elseif (!$nextmsgs) {
								$msgsRange = "Score &ge; $msgs";
							} else {
								$msgsRange = "$msgs &le; Score &lt; $nextmsgs";
							}

							$rkinfo = get_forum_rank($msgs);

							echo "<tr>";
							echo "<td>{$msgsRange}</td>"; // message count
							echo "<td><strong>{$rkinfo['name']}</strong></td>"; // name
							echo "<td><img src=\"images/ranks/{$rkinfo['img']}.gif\" alt=\"{$rkinfo['name']}\" /></td>"; // icon
							echo "</tr>";
						}
						?>
					</tbody>
				</table>
			</div>
			</div>
			</div>
			</div>
		</main>
	</body> 
</html>
