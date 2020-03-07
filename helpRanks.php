<?php
include('language.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>"> 
	<head> 
		<title><?php echo $language ? 'Help: ranks':'Aide : Les rangs'; ?></title> 
		<meta charset="utf-8" /> 
		<link rel="stylesheet" type="text/css" href="styles/forum.css" />
		<style type="text/css">
		.mBody > span {
			display: inline-block;
		}
		.mBody  > span > img {
			width: 65%;
			width: calc(40% + 7px);
			transform: translateY(10%);
			-webkit-transform: translateY(10%);
			-moz-transform: translateY(10%);
			-o-transform: translateY(10%);
			-ms-transform: translateY(10%);
			transform: translate(calc(120% - 16px),10%);
			-webkit-transform: translate(calc(120% - 16px),10%);
			-moz-transform: translate(calc(120% - 16px),10%);
			-o-transform: translate(calc(120% - 16px),10%);
			-ms-transform: translate(calc(120% - 16px),10%);
		}
		</style>
	</head> 
	<body>
		<main>
			<h1><?php echo $language ? 'Help: ranks':'Aide : Les rangs'; ?></h1>
			<div class="fMessages">
			<div class="fMessage">
			<div class="mContent">
			<div class="mBody">
				<?php
				if ($language) {
					?>
				Each player is attributed a <em>rank</em> represented by a <em>character</em> according to the number of messages he posted on the <a href="forum.php" target="_blank">forum</a> of the website. Keep posting messages to get new characters!<br /><br />
				The different characters are the following:<br />
					<?php
				}
				else {
					?>
				Chaque joueur se voit attribuer un <em>rang</em> caractérisé par un personnage de Mario en fonction du nombre de messages postés sur le <a href="forum.php" target="_blank">forum</a> du site. Postez des messages pour obtenir de nouveaux persos !<br /><br />
				Les différents rangs sont les suivantes :<br />
					<?php
				}
				?>
				<?php
				include('avatars.php');
				$lastRank = 0;
				$forumRanks = $FORUM_RANKS;
				$forumRanks[] = INF;
				foreach ($forumRanks as $rank) {
					if (is_infinite($rank))
						echo '#Messages &ge; '. $lastRank;
					elseif ($lastRank)
						echo $lastRank . ' &le; #Messages &lt; '. $rank;
					else
						echo '#Messages &lt; '. $rank;
					echo ' : ';
					$rkname = get_forum_rkname($lastRank);
					$rkimg = get_forum_rkimg($lastRank);
					echo '<span><img src="images/ranks/'. $rkimg .'.gif" alt="'. $rkname .'" /></span><strong>'. $rkname .'</strong><br />';
					$lastRank = $rank;
				}
				/*Score &ge; <?php echo $lastScore; ?> : <strong>???</strong>*/
				?>
			</div>
			</div>
			</div>
			</div>
		</main>
	</body> 
</html>
