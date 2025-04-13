<?php
// --- Initial Setup ---
include('../includes/language.php');
include('../includes/session.php');
include('../includes/initdb.php');
include('../includes/heads.php');

require_once('../includes/challenge-consts.php');
require_once('../includes/utils-ads.php');

$pagenum = isset($_GET['page']) ? max(intval($_GET['page']), 1) : 1;
$player = isset($_POST['player']) ? $_POST['player'] : null;
if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $id .'"')))
	$myPseudo = $getPseudo['nom'];
else
	$myPseudo = null;
$get = $_GET;
foreach ($get as $k => $getk)
	$get[$k] = stripslashes($get[$k]);

$challengeDifficulties = getChallengeDifficulties();
$challengeRewards = getChallengeRewards();

?>
<!DOCTYPE html>
<html lang="<?= P_("html language", "en") ?>">
<head>
	<title><?= _('Challenges leaderboard') ?> - Mario Kart PC</title>
	<link rel="stylesheet" type="text/css" href="styles/classement.css" />
	<link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />
	<style type="text/css">
		#ranking_explain {
			max-width: 650px;
			margin-left: auto;
			margin-right: auto;
			text-align: justify;
		}
		#ranking_info {
			display: none;
			margin-top: 10px;
		}
		#ranking_info ul {
			margin: 5px 0;
			padding-left: 25px;
		}
	</style>
	<?php include('../includes/o_online.php'); ?>
</head>
<body>

<?php
include('../includes/header.php');
$page = 'game';
include('../includes/menu.php');
?>

<main>
	<h1><?= _('Challenge points - Leaderboard') ?></h1>
	<p>
		<?php if ($language): ?>
			<div id="ranking_explain">
				This page displays the ranking of the players with the most points in the MKPC challenge mode
				<a href="#null" onclick="document.getElementById('ranking_info').style.display = document.getElementById('ranking_info').style.display ? '' : 'block'; return false" style="position:relative;top:-1px">[Read more]</a>.
				<div id="ranking_info">
					<a href="challengesList.php">Challenges</a> are tasks to complete in the game (e.g: &quot;Complete a track in less than 1:30&quot;) for points!
					They are created by members using the <strong>challenge editor</strong>. Anyone can create challenges, including you!<br />
					When you complete a challenge, you win a certain amount of <strong>challenge points</strong> depending on the difficulty of the challenge. Your position in the ranking is determined by your number of challenge points.
					<ul>
						<?php
						foreach ($challengeDifficulties as $index => $difficulty) {
							$end = $challengeRewards[$index] >= 2 ? 's' : '';
							echo "<li><strong>$difficulty</strong> challenges give you <strong>{$challengeRewards[$index]} pt$end</strong>.</li>";
						}
						?>
					</ul>
				</div>
			</div>
		<?php else: ?>
			<div id="ranking_explain">
				Cette page affiche le classement des players ayant le plus de points dans le mode défis de MKPC
				<a href="#null" onclick="document.getElementById('ranking_info').style.display = document.getElementById('ranking_info').style.display ? '' : 'block'; return false" style="position:relative;top:-1px">[En savoir plus]</a>.
				<div id="ranking_info">
					Les <a href="challengesList.php">défis</a> sont des actions à réaliser sur le jeu (Ex : &quot;Finir un circuit en moins de 1:30&quot;).
					Ils sont créés par les membres via l'<strong>éditeur de défis</strong>. N'importe qui peut créer des défis, vous aussi !<br />
					Lorsque vous réussissez un défi, vous gagnez un certain nombre de <strong>points défis</strong> en fonction de la difficulté. Ce sont ces points défis qui déterminent votre place dans le classement.
					<ul>
						<?php
						$challengeDifficulties = getChallengeDifficulties();
						$challengeRewards = getChallengeRewards();
						foreach ($challengeDifficulties as $index => $difficulty) {
							$fin = $challengeRewards[$index] >= 2 ? 's' : '';
							echo "<li>Un défi <strong>$difficulty</strong> rapporte <strong>{$challengeRewards[$index]} pt$fin</strong>.</li>";
						}
						?>
					</ul>
				</div>
			</div>
		<?php endif; ?>
	</p>
	<?php showSmallAdSection(); ?>

	<!-- Player Search -->
	<form method="post" action="challengeRanking.php">
		<p>
			<label for="player"><strong><?= _('See player') ?></strong></label> :
			<input type="text" name="player" id="player" value="<?= htmlspecialchars($player ?: $myPseudo) ?>" />
			<input type="submit" value="<?= _('Validate') ?>" class="action_button" />
		</p>
	</form>
	<?php
	$where = ($player ? "j.nom=\"$player\"" : 'j.pts_challenge > 0 AND j.deleted = 0');
	$recordsResult = mysql_query(
		"SELECT j.id, j.nom
		AS nickname, j.pts_challenge
		AS points, c.code
		AS country_code
		FROM `mkprofiles` p
		INNER JOIN `mkjoueurs` j
		ON p.id = j.id
		LEFT JOIN `mkcountries` c
		ON c.id = p.country
		WHERE $where
		ORDER BY j.pts_challenge DESC, j.id
	");

	if ($player) {
		if ($record = mysql_fetch_array($recordsResult)) {
			$recordCount = $recordsResult ? 1 : 0;
		} else {
			$player = null;
			$recordCount = 0;
		}
	} else {
		$recordCount = mysql_numrows($recordsResult);
	}
	

	if ($recordCount > 0) {
		require_once('../includes/utils-leaderboard.php');

		$leaderboardData = [];

		// helper function to construct table rows
		function pushLeaderboardData($place, $record) {
			global $leaderboardData;
			$leaderboardData[] = [
				'Place' => ['type' => CELLTYPE_PLACE, 'place' => print_rank($place, true)],
				_('Nick') => ['type' => CELLTYPE_PROFILE, 'id' => $record['id'], 'nick' => $record['nickname'], 'flag' => $record['country_code']],
				'Score' => $record['points']
			];
		}


		if ($player) {
			$placeResult = mysql_query(
				"SELECT id 
				FROM `mkjoueurs` 
				WHERE (pts_challenge > 0) 
				AND (pts_challenge > {$record['points']}
				OR (pts_challenge = {$record['points']}
				AND id < {$record['id']}))
				AND deleted = 0
			");
			$place = 1 + mysql_numrows($placeResult);
			$pagenum = 0;

			pushLeaderboardData($place, $record);
		} else {
			$place = ($pagenum - 1) * 20;
			$i = 0;
			$end = $place + 20;

			while ($record = mysql_fetch_array($recordsResult)) {
				$i++;
				if ($i > $place) {
					$place++;
					pushLeaderboardData($place, $record);
					if ($i == $end) break;
				}
			}
		}

		// --- Display the leaderboard table ---
		renderLeaderboardTable($leaderboardData, true);
		?>
		
		<tr>
			<td colspan="4" id="page">
				<strong>Page : </strong>
				<?php
				if ($player) {
					$pagenum = ceil($place / 20);
					$getParams['page'] = $pagenum;
					echo '<a href="?' . http_build_query($getParams) . '">' . $pagenum . '</a>';
				} else {
					function renderPageLink($pagenum, $isCurrent) {
						global $getParams;
						$getParams['page'] = $pagenum;
						echo ($isCurrent ? "<span>$pagenum</span>" : '<a href="?' . http_build_query($getParams) . '">' . $pagenum . '</a>') . '&nbsp; ';
					}

					$limit = ceil($recordCount / 20);
					require_once('../includes/utils-paging.php');
					$allPages = makePaging($pagenum, $limit);
					foreach ($allPages as $index => $block) {
						if ($index > 0) {
							echo '...&nbsp; ';
						}
						foreach ($block as $p) {
							renderPageLink($p, $p == $pagenum);
						}
					}
				}
				?>
			</td>
		</tr>
		</table>
	<?php /* else: */ } else { ?>
		<p><strong><?= _('No results found for this search') ?></strong></p>
	<?php /* endif; */ } ?>
	<p>
		<a href="challengesList.php"><?= _('Back to challenge list') ?></a><br />
		<a href="index.php"><?= _('Back to Mario Kart PC') ?></a>
	</p>
</main>

<?php include('../includes/footer.php') ?>
<script type="text/javascript" src="scripts/auto-complete.min.js"></script>
<script type="text/javascript" src="scripts/autocomplete-dummy.js"></script>
<script type="text/javascript">
	<?php
		$playerdata = array();

		$players = mysql_query(
			"SELECT nom
			FROM `mkjoueurs`
			WHERE pts_challenge>0
			AND deleted=0
			ORDER BY nom"
		);

		while ($player = mysql_fetch_array($players)) {
			$playerdata[] = $player['nom'];
		}

		$players_json = json_encode($playerdata);

		echo "window.players = $players_json;\n";
	?>
	autocompleteDummy("#player", players);
</script>
<?php mysql_close(); ?>
</body>
</html>