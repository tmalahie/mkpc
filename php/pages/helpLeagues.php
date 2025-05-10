<?php
include('../includes/language.php');
?>
<!DOCTYPE html>
<html lang="<?= P_("html language", "en") ?>">
	<head> 
		<title><?= _('Help: Leagues') ?></title> 
		<meta charset="utf-8" /> 
		<link rel="stylesheet" type="text/css" href="styles/forum.css" />
		<link rel="stylesheet" type="text/css" href="styles/main.css" />
		<style>
			table {
				margin: 0 auto;
				border-collapse: collapse;
				width: 100%;
			}
		</style>
	</head> 
	<body>
		<main>
			<h1><?= _('Help: Leagues') ?></h1>
			<div class="fMessages">
			<div class="fMessage">
			<div class="mContent">
			<div class="mBody">
				<?php if ($language): ?>
					Each player is assigned a <em>league</em> based on their score in the game's <a href="bestscores.php" target="_blank">online mode</a>. Try to improve your score to reach the highest possible rank!<br /><br />
					List of leagues:<br /><br />
				<?php else:?>
					Chaque joueur se voit attribuer une <em>league</em> en fonction de son score dans le <a href="bestscores.php" target="_blank">mode en ligne</a> du jeu. Tentez de monter votre score afin d'obtenir un grade le plus élevé possible !<br /><br />
					Liste des ligues :<br /><br />
				<?php endif;?>
				<table border="1" cellpadding="5" cellspacing="0">
					<thead>
						<tr>
							<th><?=_('Points')?></th>
							<th>League</th>
						</tr>
					</thead>
					<tbody>
						<?php
						include('../includes/avatars.php');
						$lastPts = 0;
						$keys = array_keys($LEAGUES);
						for ($i = 0; $i < count($keys); $i++) {
							$pts = $keys[$i];
							$nextPts = $keys[$i + 1] ?? null;
						
							if ($pts == 0) {
								$ptsRange = "Score &lt; $nextPts";
							} elseif (!$nextPts) {
								$ptsRange = "Score &ge; $pts";
							} else {
								$ptsRange = "$pts &le; Score &lt; $nextPts";
							}
						
							$rkinfo = get_league_rank($pts);
							echo "<tr>";
							echo "<td>{$ptsRange}</td>";
							if (!strlen($rkinfo['color'])) {
								echo "<td>{$rkinfo['name']}</td>";
							} else {
								echo "<td><span style=\"color: {$rkinfo['color']};\">{$rkinfo['name']}</span></td>";
							}
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
