<?php
include('../includes/language.php');
include('../includes/initdb.php');
include('../includes/getId.php');
$deletedGhosts = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deletedIds = array();
    foreach ($_POST as $key => $value) {
        if (preg_match('#^d\d+$#', $key) && $_POST[$key])
            $deletedIds[] = substr($key, 1);
    }
    if (!empty($deletedIds)) {
        mysql_query('DELETE g,d FROM mkghosts g LEFT JOIN mkghostsdata d ON d.id=g.id WHERE g.id IN ('. implode(',', $deletedIds) .') AND g.identifiant='.$identifiants[0].' AND g.identifiant2='.$identifiants[1].' AND g.identifiant3='.$identifiants[2].' AND g.identifiant4='.$identifiants[3]);
        $deletedGhosts = true;
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Manage time trial ghosts':'Gérer les fantômes en contre-la-montre'; ?> - Mario Kart PC</title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/classement.css?reload=1" />
<style type="text/css">
.clm-records td:first-child {
    width: 20px;
}
.clm-records td:nth-child(2) {
    width: 100px;
}
.clm-records td:nth-child(3) {
    width: 100px;
}
.clm-records td:nth-child(4) {
    width: 200px;
}
@media screen and (max-width: 600px) {
    .clm-records td:nth-child(2) {
        width: 60px;
    }
    .clm-records td:nth-child(3) {
        display: none;
    }
    .clm-records td:nth-child(5) {
        display: none;
    }
}
.clm-records tr {
    cursor: default;
}
.clm-records tr.clair:hover {
    background-color: #FFD71E;
}
.clm-records tr.fonce:hover {
    background-color: #FFB840;
}
.clm-records td em {
    font-weight: normal;
}
</style>
<?php
include('../includes/o_online.php');
?>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'game';
include('../includes/menu.php');
?>
<main>
    <?php
    if ($deletedGhosts)
        echo '<p class="success">'. ($language ? 'The selected ghosts have been successfully deleted' : 'Les fantômes sélectionnés ont été supprimés avec succès') .'</p>';
    ?>
	<h1><?php echo $language ? 'Manage your time trial ghosts':'Gérer vos fantômes en contre-la-montre'; ?></h1>
	<p>
        <?php
        require_once('../includes/tt-quotas.php');
        require_once('../includes/utils-tt.php');
        $totalQuota = tt_total_quota();
        $usedQuota = tt_used_quota();
        $usedQuotaPercent = round($usedQuota*100/$totalQuota, 1);
        if ($language) {
            ?>
            You currently have <strong><?php echo round($usedQuota/60000); ?> minutes</strong> of ghosts saved, out of <?php echo round($totalQuota/60000); ?> minutes you can save in total (<strong><?php echo $usedQuotaPercent; ?>%</strong> quota usage).
            <?php
        }
        else {
            ?>
            Vous avez actuellement <strong><?php echo round($usedQuota/60000); ?> minutes</strong> de fantômes enregistrés sur <?php echo round($totalQuota/60000); ?> minutes que vous pouvez sauvegarder au total (<strong><?php echo $usedQuotaPercent; ?>%</strong> du quota).
            <?php
        }
        ?>
    </p>
    <h2><?php echo $language ? 'Delete ghosts':'Supprimer des fantômes'; ?></h2>
    <form name="manage-ghosts" method="post" action="manageGhosts.php" onsubmit="return confirmDelete()">
        <table class="clm-records">
            <tr id="titres">
                <td style="width:20px">&nbsp;</td>
                <td><?php echo $language ? 'Rank':'Place'; ?></td>
                <td><?php echo $language ? 'Class':'Cylindrée'; ?></td>
                <td>Circuit</td>
                <td><?php echo $language ? 'Character':'Perso'; ?></td>
                <td><?php echo $language ? 'Time':'Temps'; ?></td>
            </tr>
        <?php
        include_once('circuitNames.php');
        $nameCol = $language ? 'name_en' : 'name_fr';
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $resPerPage = 50;
        $offset = ($page-1)*$resPerPage;
        $myGhosts = mysql_query(
            'SELECT g.id,g.date,g.perso,g.time,g.class,g.type,g.circuit,COUNT(r.id) AS rank,IFNULL(m.id,c.id) AS track,IFNULL(s.'.$nameCol.',IFNULL(c.nom,m.nom)) AS name
            FROM mkghosts g
            LEFT JOIN mkcircuits m ON g.circuit=m.id AND g.type="mkcircuits"
            LEFT JOIN circuits c ON g.circuit=c.id AND g.type="circuits"
            LEFT JOIN mktracksettings s ON s.type=g.type AND s.circuit=g.circuit
            LEFT JOIN mkrecords r ON r.circuit=g.circuit AND r.type=g.type AND r.class=g.class AND r.best=1 AND r.time<g.time
            WHERE g.identifiant='.$identifiants[0].' AND g.identifiant2='.$identifiants[1].' AND g.identifiant3='.$identifiants[2].' AND g.identifiant4='.$identifiants[3].'
            GROUP BY g.id
            ORDER BY g.time DESC
            LIMIT '.$offset.','.$resPerPage
        );
        $getNbGhosts = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkghosts WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]));
        $nbRes = $getNbGhosts['nb'];
        require_once('../includes/persos.php');
        require_once('../includes/utils-leaderboard.php');
        function getSpriteSrc($playerName) {
            if (substr($playerName, 0,3) == 'cp-')
                return PERSOS_DIR . $playerName . ".png";
            return "images/sprites/sprite_" . $playerName . ".png";
        }
        $i = 0;
        while ($record = mysql_fetch_array($myGhosts)) {
            ?>
            <tr onclick="selectRow(<?php echo $record['id']; ?>)" class="<?php echo (($i%2) ? 'clair':'fonce') ?>">
                <td><input type="checkbox" name="d<?php echo $record['id']; ?>" onclick="event.stopPropagation()" /></td>
                <td><?php print_rank(1+$record['rank']); ?></td>
                <td><?php echo $record['class']; ?>cc</td>
                <td><?php
                if ($record['type'] === '')
                    echo $circuitNames[$record['circuit']-1];
                elseif ($record['track'] === null)
                    echo '<em>'. ($language ? 'Deleted track':'Circuit supprimé') .'</em>';
                else {
                    $trackUrl = '';
                    if ($record['type'] === 'circuits')
                        $trackUrl = 'map.php?i='. $record['track'];
                    elseif ($record['type'] === 'mkcircuits')
                        $trackUrl = 'circuit.php?id='. $record['track'];
                    $trackName = $record['name'];
                    if (!$trackName) $trackName = $language ? 'Untitled' : 'Sans titre';
                    echo '<a target="_blank" href="'. $trackUrl .'" onclick="event.stopPropagation()">'. $trackName .'</a>';
                }
                ?></td>
                <td>
                    <div>
                        <img src="<?php echo getSpriteSrc($record['perso']); ?>" alt="mario" onload="spriteLoad(this)">
                    </div>
                </td>
                <td>
                    <?php
                    print_time_ms($record['time']);
                    ?>
                </td>
            </tr>
            <?php
            $i++;
        }
        ?>
        <tr><td colspan="6" id="page"><strong>Page : </strong> 
        <?php
        function pageLink($page, $isCurrent) {
            echo ($isCurrent ? '<span>'.$page.'</span>' : '<a href="?page='.$page.'">'.$page.'</a>').'&nbsp; ';
        }
        $limite = ceil($nbRes/$resPerPage);
        require_once('../includes/utils-paging.php');
        $allPages = makePaging($page,$limite);
        foreach ($allPages as $i=>$block) {
            if ($i)
                echo '...&nbsp; ';
            foreach ($block as $p)
                pageLink($p, $p==$page);
        }
        ?>
        </td></tr>
        </table>
        <p>
            <input type="submit" value="<?php echo $language ? 'Delete selected ghosts':'Supprimer les fantômes sélectionnés'; ?>" class="action_button action_delete" />
        </p>
    </form>
	<p><a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('../includes/footer.php');
?>
<script type="text/javascript">
function spriteLoad(img) {
	var w = img.naturalWidth, h = img.naturalHeight;
	if (w != 768 || h != 32) {
		var div = img.parentNode;
		// TODO: this works because 768 = 24*32, but it's a coincidence
		div.style.width = Math.round(w/h)+"px";
		img.style.left = -Math.round(6*w/h)+"px";
	}
}
function selectRow(id) {
    var $manageGhosts = document.forms['manage-ghosts'].elements['d'+id];
    $manageGhosts.checked = !$manageGhosts.checked;
}
function confirmDelete() {
    return confirm("<?php echo $language ? 'Delete the ghosts? This action cannot be undone':'Supprimer les fantômes ? Cette action est irréversible'; ?>");
}
</script>
<?php
mysql_close();
?>
</body>
</html>