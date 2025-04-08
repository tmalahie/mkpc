<?php
// --- Initial Setup ---
include('../includes/language.php');
include('../includes/session.php');
include('../includes/initdb.php');
include('../includes/o_online.php');
include('../includes/heads.php');

// --- Page Variables ---
$page = 'game';
$language = $language ?? false;
$cc = isset($_GET['cc']) ? intval($_GET['cc']) : 150;
$page = isset($_GET['page']) ? max(intval($_GET['page']), 1) : 1;
$player = $_POST['joueur'] ?? null;

$getPseudo = mysql_fetch_array(mysql_query("SELECT nom FROM mkjoueurs WHERE id='$id'"));
$myPseudo = $getPseudo['nom'] ?? null;
?>
<!DOCTYPE html>
<html lang="<?= $language ? 'en' : 'fr' ?>">
<head>
	<title><?= $language ? 'Time trial ranking' : 'Classement contre-la-montre' ?> - Mario Kart PC</title>
	<link rel="stylesheet" href="styles/classement.css">
	<link rel="stylesheet" href="styles/auto-complete.css">
	<style>
		.details { width: 20px; }
		.details:hover { opacity: 0.7; }
		.ranking-modes-ctn {
			text-align: center;
			margin-bottom: 10px;
		}
		.ranking-modes-ctn > div {
			display: inline-flex;
			align-items: center;
		}
		.ranking-modes-ctn > div > span {
			font-weight: bold;
			margin-right: 6px;
		}
	</style>
</head>

<body>
<?php
include('../includes/header.php');
include('../includes/menu.php');
?>

<main>
	<h1><?= $language ? 'Time Trial - Global ranking' : 'Contre-la-montre - Classement global' ?></h1>
	<p>
		<?= $language
			? 'This page shows a leaderboard of top players in time trial.<br />This leaderboard is based on a score calculation which depends on your rank on each circuit. See <a href="topic.php?topic=5318">this topic</a> for further info.'
			: 'Cette page affiche le classement des meilleurs joueurs en contre la montre.<br />Ce classement se base sur un calcul de score dépendant de votre place sur chaque circuit. Voir <a href="topic.php?topic=5318">ce topic</a> pour en savoir plus.' ?>
	</p>

	<div class="pub"><?php require_once('../includes/utils-ads.php'); showSmallAd(); ?></div>

	<!-- Mode Selection -->
	<div class="ranking-modes-ctn">
		<div>
			<span><?= $language ? 'Class:' : 'Cylindrée :' ?></span>
			<div class="ranking-modes">
				<?= $cc == 150
					? '<span>150cc</span><a href="classement.global.php?cc=200">200cc</a>'
					: '<a href="classement.global.php?cc=150">150cc</a><span>200cc</span>' ?>
			</div>
		</div>
	</div>

	<!-- Player Search -->
	<form method="post" action="classement.global.php?cc=<?= $cc ?>">
		<blockquote>
			<p>
				<label for="joueur"><strong><?= $language ? 'See player' : 'Voir joueur' ?></strong></label> :
				<input type="text" name="joueur" id="joueur" value="<?= htmlspecialchars($player ?: $myPseudo) ?>" />
				<input type="submit" value="<?= $language ? 'Validate' : 'Valider' ?>" class="action_button" />
			</p>
		</blockquote>
	</form>

	<?php
	// --- Leaderboard Query ---
	$query = "
		SELECT t.player, j.nom, t.score, c.code 
		FROM mkttranking t 
		INNER JOIN mkjoueurs j ON t.player = j.id 
		INNER JOIN mkprofiles p ON p.id = j.id 
		LEFT JOIN mkcountries c ON c.id = p.country 
		WHERE class = '$cc' 
			AND " . ($player ? "j.nom = '".mysql_real_escape_string($player)."'" : "j.deleted = 0") . "
		ORDER BY t.score DESC, t.player
	";
	$records = mysql_query($query);

	if ($player) {
		if ($record = mysql_fetch_array($records)) {
			$nb_temps = 1;
		} else {
			$player = null;
			$nb_temps = 0;
		}
	} else {
		$nb_temps = mysql_numrows($records);
	}

	if ($nb_temps):
		require_once('../includes/utils-leaderboard.php');
	?>
		<table>
			<tr id="titres">
				<td>Place</td>
				<td><?= $language ? 'Username' : 'Pseudo' ?></td>
				<td>Score</td>
				<td><?= $language ? 'Details' : 'Détails' ?></td>
			</tr>

			<?php
			if ($player) {
				$getPlaces = mysql_query("
					SELECT t.player FROM mkttranking t 
					INNER JOIN mkjoueurs j ON t.player = j.id 
					WHERE class = '$cc' 
						AND (t.score > '{$record['score']}' OR (t.score = '{$record['score']}' AND t.player < '{$record['player']}')) 
						AND j.deleted = 0
				");
				$place = 1 + mysql_numrows($getPlaces);
				?>
				<tr class="clair">
					<td><?= print_rank($place) ?></td>
					<td><a href="profil.php?id=<?= $record['player'] ?>" class="recorder">
						<?= $record['code'] ? '<img src="images/flags/'.$record['code'].'.png" alt="'.$record['code'].'" /> ' : '' ?>
						<?= htmlspecialchars($player) ?>
					</a></td>
					<td><?= $record['score'] ?></td>
					<td><a href="classement.php?user=<?= $record['player'] ?>&cc=<?= $cc ?>&pts">
						<img src="images/details.png" class="details" alt="Preview" />
					</a></td>
				</tr>
				<?php
			} else {
				$place = ($page - 1) * 20;
				$end = $place + 20;
				$i = 0;
				while ($record = mysql_fetch_array($records)) {
					$i++;
					if ($i > $place) {
						$place++;
						?>
						<tr class="<?= ($i % 2) ? 'clair' : 'fonce' ?>">
							<td><?= print_rank($place) ?></td>
							<td><a href="profil.php?id=<?= $record['player'] ?>" class="recorder">
								<?= $record['code'] ? '<img src="images/flags/'.$record['code'].'.png" alt="'.$record['code'].'" onerror="this.style.display=\'none\'" /> ' : '' ?>
								<?= $record['nom'] ?>
							</a></td>
							<td><?= $record['score'] ?></td>
							<td><a href="classement.php?user=<?= $record['player'] ?>&cc=<?= $cc ?>&pts">
								<img src="images/details.png" class="details" alt="Preview" />
							</a></td>
						</tr>
						<?php
						if ($i == $end) break;
					}
				}
			}
			?>

			<!-- Pagination -->
			<tr><td colspan="4" id="page"><strong>Page : </strong>
				<?php
				function pageLink($page, $isCurrent) {
					global $cc;
					echo $isCurrent ? "<span>$page</span>" : "<a href=\"?cc=$cc&amp;page=$page\">$page</a>";
					echo '&nbsp; ';
				}

				if ($player) {
					$page = ceil($place / 20);
					pageLink($page, true);
				} else {
					require_once('../includes/utils-paging.php');
					$limit = ceil($nb_temps / 20);
					$allPages = makePaging($page, $limit);
					foreach ($allPages as $i => $block) {
						if ($i) echo '...&nbsp;';
						foreach ($block as $p) pageLink($p, $p == $page);
					}
				}
				?>
			</td></tr>
		</table>
	<?php else: ?>
		<p><strong><?= $language ? 'No results found for this search' : 'Aucun résultat trouvé pour cette recherche' ?></strong></p>
	<?php endif; ?>

	<p>
		<a href="classement.php?cc=<?= $cc ?>"><?= $language ? 'Ranking circuit by circuit' : 'Classement circuit par circuit' ?></a><br />
		<a href="index.php"><?= $language ? 'Back to Mario Kart PC' : 'Retour à Mario Kart PC' ?></a>
	</p>
</main>

<?php
include('../includes/footer.php');
mysql_close();
?>

<!-- Scripts -->
<script src="scripts/auto-complete.min.js"></script>
<script src="scripts/autocomplete-player.js"></script>
<script>
	autocompletePlayer('#joueur');
</script>
</body>
</html>
