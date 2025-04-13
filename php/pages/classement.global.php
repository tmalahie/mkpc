<?php
// --- Constants ---
define('SCOREMODE_SCORE', 1);
define('SCOREMODE_TOTALTIME', 2);
define('SCOREMODE_AF', 3);

// --- Initial Setup ---
include('../includes/language.php');
include('../includes/session.php');
include('../includes/initdb.php');
include('../includes/heads.php');

// --- Helpers --- 

function formatTotalTime($ms) {
	$totalSeconds = floor($ms / 1000);
	$minutes = floor(($totalSeconds % 3600) / 60);
	$seconds = $totalSeconds % 60;
	$milliseconds = $ms % 1000;

	return sprintf('%d:%02d.%03d', $minutes, $seconds, $milliseconds);
}

function getAverageRank($userId) {
    $rankQuery = 
	"SELECT * 
    FROM mkrecords 
    WHERE player = $userId
    ORDER BY time ASC";
    
    $rankResult = mysql_query($rankQuery);
    
    $ranks = [];
    
    $currentRank = 1;
    while ($row = mysql_fetch_array($rankResult)) {
        $ranks[] = $currentRank;
        $currentRank++;
    }

    if (count($ranks) == 0) {
        return null;
    }

    $averageRank = array_sum($ranks) / count($ranks);
    
	// truncate to 3 decimal places
    return floor($averageRank * 1000) / 1000;
}

function getRecordCount($userId) {
	$recordCountQuery = 
	"SELECT COUNT(*) AS record_count 
	FROM mkrecords 
	WHERE player = $userId;";
	$recordCountResult = mysql_fetch_array(mysql_query($recordCountQuery));

	return $recordCountResult['record_count'];
}


function getTotalTime($userId) {
	$timesQuery = 
	"SELECT 
		SUM(time) AS total_time
	FROM 
		mkrecords
	WHERE 
		player = $userId;
	";
	$timesResult = mysql_fetch_array(mysql_query($timesQuery));

	return formatTotalTime($timesResult['total_time']);
}

function pushLeaderboardData($place, $record) {
	global $cc, $scoreMode;

	switch ($scoreMode) {
		case SCOREMODE_TOTALTIME:
			$measureHeader = _('Total Time');
			$measureValue = getTotalTime($record['player']);
			break;
		case SCOREMODE_AF:
			$measureHeader = _('Average Rank');
			$measureValue = getAverageRank($record['player']);
			break;
		default:
			$measureHeader = _('Score');
			$measureValue = $record['score'];
			break;
	}

	if ($scoreMode != SCOREMODE_SCORE) {
		$recordCount = getRecordCount($record['player']);
		if ($recordCount < 56) {
			$measureValue = "<span style=\"color: red !important;\">$measureValue&nbsp($recordCount/56)</span>";
		} elseif ($scoreMode == SCOREMODE_TOTALTIME) {
			$measureValue = formatTotalTime($measureValue);
		}
	}

	return [
		'Place' => ['type' => CELLTYPE_PLACE, 'place' => print_rank($place, true)],
		_('Nick') => ['type' => CELLTYPE_PROFILE, 'id' => $record['player'], 'nick' => $record['nom'], 'flag' => $record['code']],
		$measureHeader => $measureValue, // changes depending on scoreMode
		'Details' => '<a href="classement.php?user='.$record['player'].'&cc='.$cc.'&pts"><img src="images/details.png" class="details" alt="Preview" /></a>'
	];
}

// --- Page Variables ---

$scoreMode = isset($_GET['mode']) ? intval($_GET['mode']) : SCOREMODE_SCORE;
$cc = isset($_GET['cc']) ? intval($_GET['cc']) : 150;
$pagenum = isset($_GET['page']) ? max(intval($_GET['page']), 1) : 1;
$player = $_POST['joueur'] ?? null;

$getPseudo = mysql_fetch_array(mysql_query("SELECT nom FROM mkjoueurs WHERE id='$id'"));
$myPseudo = $getPseudo['nom'] ?? null;
?>
<!DOCTYPE html>
<html lang="<?= P_("html language", "en") ?>">
<head>
	<title><?= _('Time trial ranking')?> - Mario Kart PC</title>
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
	<?php include('../includes/o_online.php'); ?>
</head>

<body>
<?php
$page = 'game';
include('../includes/header.php');
include('../includes/menu.php');
?>

<main>
	<h1><?= _('Time Trial - Global ranking') ?></h1>
	<p>
		<?= _('This page shows a leaderboard of top players in time trial.<br />This leaderboard is based on a score calculation which depends on your rank on each circuit. See <a href="topic.php?topic=5318">this topic</a> for further info.') ?>
	</p>

	<div class="pub"><?php require_once('../includes/utils-ads.php'); showSmallAd(); ?></div>

	<!-- Mode Selection -->
	<div class="ranking-modes-ctn">
		<div>
			<span><?= _('Class:') ?></span>
			<div class="ranking-modes">
				<?= $cc == 150
					? '<span>150cc</span><a href="classement.global.php?cc=200">200cc</a>'
					: '<a href="classement.global.php?cc=150">150cc</a><span>200cc</span>' ?>
			</div>
		</div>
	</div>

	<!-- Player Search -->
	<form method="post" action="classement.global.php?cc=<?= $cc ?>&mode=<?= $scoreMode ?>">
		<blockquote>
			<p>
				<label for="joueur"><strong><?= _('See player') ?></strong></label> :
				<input type="text" name="joueur" id="joueur" value="<?= $player ?: $myPseudo ?>" />
				<input type="submit" value="<?= _('Validate') ?>" class="action_button" />
			</p>
		</blockquote>
	</form>

	<!-- Mode Toggle Buttons -->
	<div id="modeselect">
		<a href="classement.global.php?cc=<?= $cc ?>&mode=1">Score</a> <span>|</span>
		<a href="classement.global.php?cc=<?= $cc ?>&mode=2"><?= _('Total Time') ?></a> <span>|</span>
		<a href="classement.global.php?cc=<?= $cc ?>&mode=3"><?= _('Average Rank') ?></a>
	</div>

	<?php
	// --- Leaderboard Query ---
	$query = "
		SELECT t.player, j.nom, t.score, c.code 
		FROM mkttranking t 
		INNER JOIN mkjoueurs j ON t.player = j.id 
		INNER JOIN mkprofiles p ON p.id = j.id 
		LEFT JOIN mkcountries c ON c.id = p.country 
		WHERE class = '$cc' 
			AND " . ($player ? "j.nom = '$player'" : "j.deleted = 0") . "
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
		$leaderboardData = [];

		if ($player) {
			$getPlaces = mysql_query("
				SELECT t.player FROM mkttranking t 
				INNER JOIN mkjoueurs j ON t.player = j.id 
				WHERE class = '$cc' 
					AND (t.score > '{$record['score']}' OR (t.score = '{$record['score']}' AND t.player < '{$record['player']}')) 
					AND j.deleted = 0
			");
			$place = 1 + mysql_numrows($getPlaces);
			
			$leaderboardData[] = pushLeaderboardData($place, $record);
		} else {
			$place = ($pagenum - 1) * 20;
			$end = $place + 20;
			$i = 0;
			while ($record = mysql_fetch_array($records)) {
				$i++;
				if ($i > $place) {
					$place++;

					$leaderboardData[] = pushLeaderboardData($place, $record);

					if ($i == $end) break;
				}
			}
		}

		renderLeaderboardTable($leaderboardData, true);
		?>

		<!-- Pagination -->
		<tr><td colspan="4" id="page"><strong>Page : </strong>
			<?php
			function pageLink($pagenum, $isCurrent) {
				global $cc;
				echo $isCurrent 
					? "<span>$pagenum</span>"
					: "<a href=\"?cc=$cc&amp;page=$pagenum\">$pagenum</a>";
				echo '&nbsp; ';
			}

			if ($player) {
				$pagenum = ceil($place / 20);
				pageLink($pagenum, true);
			} else {
				require_once('../includes/utils-paging.php');
				$limit = ceil($nb_temps / 20);
				$allPages = makePaging($pagenum, $limit);
				foreach ($allPages as $i => $block) {
					if ($i) echo '...&nbsp;';
					foreach ($block as $p) pageLink($p, $p == $pagenum);
				}
			}
			?>
		</td></tr>
		</table>
	<?php else: ?>
		<p><strong><?= _('No results found for this search') ?></strong></p>
	<?php endif; ?>

	<p>
		<a href="classement.php?cc=<?= $cc ?>"><?= _('Ranking circuit by circuit') ?></a><br />
		<a href="index.php"><?= _('Back to Mario Kart PC') ?></a>
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
