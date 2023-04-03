<?php
include('getId.php');
include('language.php');
include('session.php');
include('initdb.php');
require_once('getRights.php');
if (!hasRight('moderator')) {
	echo _("You are not moderator");
	mysql_close();
	exit;
}
include('tokens.php');
assign_token();
mysql_query('DELETE FROM `mknotifs` WHERE user="'. $id .'" AND type="admin_report"');
?>
<!DOCTYPE html>
<html lang="<?= P_("html language", "en") ?>">
<head>
<title><?= _('Mario Kart PC Forum') ?> - <?= _('Reported messages') ?></title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css?reload=2" />
<link rel="stylesheet" type="text/css" href="styles/profil.css" />
<link rel="stylesheet" type="text/css" href="styles/forms.css" />
<style type="text/css">
    .report-title {
        margin-top: 1em;
    }
    .report-member {
        margin-top: 0.2em;
    }
</style>
<script type="text/javascript" src="scripts/topic.js"></script>
<script type="text/javascript" src="scripts/forum-search.js?reload=1"></script>
<?php
include('o_online.php');
?>
</head>
<body>
<?php
include('header.php');
$page = 'forum';
include('menu.php');
$RES_PER_PAGE = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page']:1;

function zerofill($s,$l) {
	while (strlen($s) < $l)
		$s = '0'.$s;
	return $s;
}
?>
<main>
<template id="report-member">
  <div class="report-member">
    - <a href="#null" target="_blank"></a>
  </div>
</template>
<h1><?= _('Mario Kart PC Forum') ?> - <?= _('Reported messages') ?></h1>
<div id="search-results">
<?php
include('avatars.php');
include('bbCode.php');
$maxRes = ($page+7)*$RES_PER_PAGE;
$getNbRes = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkreports WHERE type="topic" AND state="pending"'));
$nbres = $getNbRes['nb'];
$isMax = ($nbres == $maxRes);
if ($nbres) {
    require_once('reactions.php');
    printReactionUI();

    $sql = 'SELECT r.id AS reportid,m.id,t.titre,m.topic,m.message,m.auteur,t.private,m.date,r.count';
    $sql .= ' FROM `mkreports` r INNER JOIN `mkmessages` m ON m.id=SUBSTRING_INDEX(r.link, ",", -1) AND m.topic=SUBSTRING_INDEX(r.link, ",", 1) INNER JOIN `mktopics` t ON t.id=m.topic';
    $sql .= ' WHERE r.type="topic" AND r.state="pending"';
    $sql .= ' ORDER BY r.count DESC LIMIT '.(($page-1)*$RES_PER_PAGE).','.$RES_PER_PAGE;
    $search = mysql_query($sql);
    ?>
    <?php
    $topicName = '';
    $searchResults = array();
    while ($result = mysql_fetch_array($search))
        $searchResults[] = $result;
    if (empty($searchResults)) {
        echo '<h4>';
        echo _("No new reported message for now");
        echo '</h4>';
    }
    else {
        populateReactionsData('topic', $searchResults);

        echo '<div id="reports-wrapper">';
        foreach ($searchResults as $result) {
            $topicName = $result['titre'];
            echo '<div id="report-wrapper-'. $result['reportid'] .'">';

            echo '<div class="report-title">';
            echo F_('In <a href="{topicUrl}">{topicName}</a>, ', topicName: htmlspecialchars($topicName), topicUrl: "topic.php?topic=" . $result['topic']);
            echo FN_(
                'reported by <a href="{showMembers}">{count} member</a>',
                'reported by <a href="{showMembers}">{count} members</a>',
                count: $result['count'],
                showMembers: "javascript:showMembers(" . $result['reportid'] . ")",
            );

            echo '</div>';

            echo '<div class="report-members" id="report-member-'.$result['reportid'].'"></div>';
            echo '<div class="fMessages" data-topic="'.$result['topic'].'">';
            print_forum_msg($result, array(
                'mayEdit' => true,
                'mayQuote' => false,
                'mayReact' => false,
                'mayReport' => false,
                'canModerate' => true
            ));
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }
    ?>
    <?php
    $nbPages = ceil($nbres/$RES_PER_PAGE);
    if ($nbPages > 1) {
        ?>
        <div class="topicPages"><p>
            Page : <?php
            $get = $_GET;
            foreach ($get as $k => $getk)
                $get[$k] = stripslashes($get[$k]);
            require_once('utils-paging.php');
            $allPages = makePaging($page,$nbPages);
            foreach ($allPages as $i=>$block) {
                if ($i)
                    echo '...&nbsp; &nbsp;';
                foreach ($block as $p) {
                    $get['page'] = $p;
                    if ($p == $page)
                        echo $p;
                    else
                        echo '<a href="?'. http_build_query($get) .'#search-results">'. $p .'</a>';
                    echo ' &nbsp; ';
                }
            }
            if ($isMax)
                echo '...';
            ?>
        </p></div>
        <?php
    }
}
else {
    echo '<h4>';
    echo _('No pending reported message');
    echo '</h4>';
}
?>
</div>
<p class="forumButtons"><a href="forum.php"><?= _('Back to the forum') ?></a></p>
</main>
<?php
include('footer.php');
mysql_close();
?>
<script type="text/javascript">
function showMembers(reportId) {
    var $reportMembers = document.getElementById("report-member-"+reportId);
    if ($reportMembers.dataset.shown) {
        $reportMembers.dataset.shown = "";
        $reportMembers.innerHTML = "";
        return;
    }
    $reportMembers.dataset.shown = 1;
    $reportMembers.dataset.query = (+$reportMembers.dataset.query||0) + 1;
    var currentQuery = $reportMembers.dataset.query;
    o_xhr("adminReportsHistory.php", "id="+reportId, function(res) {
        if ($reportMembers.dataset.query !== currentQuery)
            return;
        $reportMembers.dataset.shown = 1;
        var reporters = JSON.parse(res);
        for (var i=0;i<reporters.length;i++) {
            var payload = reporters[i];
            var $reportMemberTemplate = document.getElementById("report-member");
            var $reportMember = $reportMemberTemplate.content.cloneNode(true);
            var $reportMemberLink = $reportMember.querySelector("a");
            $reportMemberLink.href = "profil.php?id="+ payload.reporter.id;
            $reportMemberLink.innerHTML = payload.reporter.name;
            $reportMembers.appendChild($reportMember);
        }
        return true;
    });
}
function archiveReport(reportId) {
    if (confirm(o_language ? 'Archive this message report?' : 'Archiver ce message signalé ?')) {
        o_xhr("archiveReport.php", "id="+reportId, function(res) {
            if (res != 1) return false;
            document.getElementById("reports-wrapper").removeChild(document.getElementById("report-wrapper-"+reportId));
            return true;
        });
    }
}
</script>
</body>
</html>