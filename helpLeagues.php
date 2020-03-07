<?php
include('language.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>"> 
	<head> 
		<title><?php echo $language ? 'Help: Leagues':'Aide : Les Leagues'; ?></title> 
		<meta charset="utf-8" /> 
		<link rel="stylesheet" type="text/css" href="styles/forum.css" />
	</head> 
	<body>
		<main>
			<h1><?php echo $language ? 'Help: Leagues':'Aide : Les Leagues'; ?></h1>
			<div class="fMessages">
			<div class="fMessage">
			<div class="mContent">
			<div class="mBody" style="padding-top:10px">
				<?php
				if ($language) {
					?>
				Each player is attributed a <em>league</em> according to their score in the <a href="bestscores.php" target="_blank">online mode</a> of the game. Try to increase your score in order to get the best possible rank!<br /><br />
				The different leagues are the following:<br />
					<?php
				}
				else {
					?>
				Chaque joueur se voit attribuer une <em>league</em> en fonction de son score dans le <a href="bestscores.php" target="_blank">mode en ligne</a> du jeu. Tentez de monter votre score afin d'obtenir un grade le plus élevé possible !<br /><br />
				Les différentes leagues sont les suivantes :<br />
					<?php
				}
				?>
				<?php
				include('avatars.php');
				$lastScore = 0;
				$leagueScores = $LEAGUES_SCORES;
				$leagueScores[] = INF;
				foreach ($leagueScores as $score) {
					if (is_infinite($score))
						echo 'Score &ge; '. $lastScore;
					elseif ($lastScore)
						echo $lastScore . ' &le; Score &lt; '. $score;
					else
						echo 'Score &lt; '. $score;
					echo ' : ';
					echo '<strong style="color:'. get_league_color($lastScore) .'">'. get_league_name($lastScore) .'</strong><br />';
					$lastScore = $score;
				}
				//echo 'Score &ge; '. $lastScore .' : <strong>???</strong>';
				?>
			</div>
			</div>
			</div>
			</div>
		</main>
	</body> 
</html>
