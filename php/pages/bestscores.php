<?php
include('../includes/language.php');
include('../includes/session.php');
include('../includes/initdb.php');

$isBattle = isset($_GET['battle']);
$pts = 'pts_'.($isBattle ? 'battle' : 'vs');

$pagenum = isset($_GET['page']) ? max(intval($_GET['page']),1) : 1;
$player = isset($_POST['player']) ? $_POST['player'] : null;
if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $id .'"')))
    $myPseudo = $getPseudo['nom'];
else
    $myPseudo = null;

?>

<!DOCTYPE html>
<html lang="<?= P_("html language", "en") ?>">
<head>
    <title><?= _('Online mode leaderboard') ?> - Mario Kart PC</title>
    <?php include('../includes/heads.php'); include('../includes/o_online.php'); ?>
    <link rel="stylesheet" type="text/css" href="styles/classement.css" />
    <link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />
</head>
<body>
<?php
include('../includes/header.php');
$page = 'game';
include('../includes/menu.php');
?>
<main>
    <h1><?= _('Mario Kart PC Leaderboard') ?></h1>
    <div class="ranking-modes">
        <?php if ($isBattle): ?>
            <a href="bestscores.php"><?= _('VS mode') ?></a><span><?= _('Battle mode') ?></span>
        <?php else: ?>
            <span><?= _('VS mode') ?></span><a href="bestscores.php?battle"><?= _('Battle mode') ?></a>
        <?php endif; ?>
    </div>
    <form method="post" action="bestscores.php<?php if ($isBattle) echo '?battle'; ?>">
        <blockquote>
            <p><label for="player"><strong><?= _('See player') ?></strong></label> : <input type="text" name="player" id="player" value="<?= ($player ? $player:$myPseudo); ?>" /> <input type="submit" value="<?= _('Validate') ?>" class="action_button" /></p>
        </blockquote>
    </form>
    <?php
        $RES_PER_PAGE = 20;
        $offset = ($pagenum-1)*$RES_PER_PAGE;
        $where = $player ? "j.nom='$player'" : "(j.$pts != 5000) AND j.deleted=0";

        $records = mysql_query(
            "SELECT j.id,j.nom,j.$pts
            AS pts,c.code FROM `mkjoueurs` j
            INNER JOIN `mkprofiles` p ON p.id=j.id
            LEFT JOIN `mkcountries` c ON c.id=p.country
            WHERE $where
            ORDER BY j.$pts DESC,j.id
            LIMIT $offset,$RES_PER_PAGE"
        );
        if ($player) {
            if ($record = mysql_fetch_array($records))
                $nb_temps = $records ? 1:0;
            else {
                $player = null;
                $nb_temps = 0;
            }
        }
        else {
            $countPlayers = mysql_fetch_array(mysql_query(
                "SELECT COUNT(*)
                AS nb
                FROM `mkjoueurs` j
                WHERE $where"
            ));
            $nb_temps = $countPlayers['nb'];
        }


        if ($nb_temps > 0) {
            require_once('../includes/utils-leaderboard.php');

            $leaderboardData = array();

			function pushLeaderboardData($record, $place) {
				global $leaderboardData;
				$leaderboardData[] = [
					'Place' => ['type' => CELLTYPE_PLACE, 'place' => print_rank($place, true)],
					_('Username') => ['type' => CELLTYPE_PROFILE, 'id' => $record['id'], 'nick' => $record['nom'], 'flag' => $record['code']],
					'Score' => $record['pts']
				];
			}
            
            if ($player) {
                $getPlaces = mysql_query(
                    "SELECT j.id,j.nom
                    FROM `mkjoueurs` j
                    WHERE (j.$pts!=5000) AND (j.$pts>{$record['pts']}
                    OR (j.$pts={$record['pts']}
                    AND j.id<{$record['id']}))
                    AND j.deleted=0"
                );
                $place = 1 + mysql_numrows($getPlaces);
                $pagenum = 0;
                pushLeaderboardData($record, $place);
            } else {
                $place = $offset;
                while ($record=mysql_fetch_array($records)) {
                    $place++;
                    pushLeaderboardData($record, $place);
                }
            }
            renderLeaderboardTable($leaderboardData, true);
		}
    ?>
	<?php if ($nb_temps): ?>
        <tr>
            <td colspan="4" id="page"><strong>Page : </strong>
                <?php
                    if ($player) {
                        $pagenum = ceil($place/$RES_PER_PAGE);
                        echo '<a href="?'. ($isBattle ? 'battle&amp;':'') .'page='.$pagenum.'">'.$pagenum.'</a>';
                    }
                    else {
                        function pageLink($pagenum, $isCurrent) {
                            global $isBattle;
                            echo ($isCurrent ? "<span>$pagenum</span>" : '<a href="?'. ($isBattle ? 'battle&amp;':'') .'page='.$pagenum.'">'.$pagenum.'</a>').'&nbsp; ';
                        }
                        $limite = ceil($nb_temps/$RES_PER_PAGE);
                        require_once('../includes/utils-paging.php');
                        $allPages = makePaging($pagenum,$limite);
                        foreach ($allPages as $i=>$block) {
                            if ($i)
                                echo '...&nbsp; ';
                            foreach ($block as $p)
                                pageLink($p, $p==$pagenum);
                        }
                    }
                ?>
            </td>
        </tr>
    </table>
    <?php else:?>
        <p><strong><?= _('No results found for this search') ?></strong></p>
    <?php endif; ?>
    <p><a href="online.php<?= $isBattle ? '?battle':'' ?>"><?= _('Back to the online mode') ?></a><br />
    <a href="index.php"><?= _('Back to Mario Kart PC') ?></a></p>
</main>

<?php include('../includes/footer.php'); ?>
<script type="text/javascript" src="scripts/auto-complete.min.js"></script>
<script type="text/javascript" src="scripts/autocomplete-player.js"></script>
<script type="text/javascript">
    autocompletePlayer('#player');
</script>
<?php mysql_close(); ?>
</body>
</html>