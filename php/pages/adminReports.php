<?php
include('../includes/getId.php');
include('../includes/language.php');
include('../includes/session.php');
include('../includes/initdb.php');
require_once('../includes/getRights.php');
if (!hasRight('moderator')) {
	echo t('admin_reports.you_are_not_moderator');
	mysql_close();
	exit;
}
include('../includes/tokens.php');
assign_token();
mysql_query('DELETE FROM `mknotifs` WHERE user="'. $id .'" AND type="admin_report"');
$pageTitle = t('admin_reports.reported_messages');
$archivedFilter = isset($_GET['archived']);
if ($archivedFilter)
    $pageTitle = t('admin_reports.archived_reports');
?>
<!DOCTYPE html>
<html lang="<?= $locale ?>">
<head>
<title><?= t('common.mario_kart_pc_forum') ?> - <?= $pageTitle ?></title>
<?php
include('../includes/heads.php');
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
include('../includes/o_online.php');
?>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'forum';
include('../includes/menu.php');
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
<h1><?= t('common.mario_kart_pc_forum') ?> - <?= $pageTitle ?></h1>
<div id="search-results">
<?php
include('../includes/avatars.php');
include('../includes/bbCode.php');
$maxRes = ($page+7)*$RES_PER_PAGE;
$getNbRes = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkreports WHERE type="topic" AND state="pending"'));
$nbres = $getNbRes['nb'];
$isMax = ($nbres == $maxRes);
if ($nbres) {
    require_once('../includes/reactions.php');
    printReactionUI();

    $state = 'pending';
    if ($archivedFilter)
        $state = 'archived';

    $sql = 'SELECT r.id AS reportid,m.id,t.titre,m.topic,m.message,m.auteur,t.private,m.date,r.count';
    $sql .= ' FROM `mkreports` r INNER JOIN `mkmessages` m ON m.id=SUBSTRING_INDEX(r.link, ",", -1) AND m.topic=SUBSTRING_INDEX(r.link, ",", 1) INNER JOIN `mktopics` t ON t.id=m.topic';
    $sql .= ' WHERE r.type="topic" AND r.state="'.$state.'"';
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
        echo t('admin_reports.no_new_reported_message_now');
        echo '</h4>';
    }
    else {
        populateReactionsData('topic', $searchResults);

        echo '<div id="reports-wrapper">';
        foreach ($searchResults as $result) {
            $topicName = $result['titre'];
            echo '<div id="report-wrapper-'. $result['reportid'] .'">';

            echo '<div class="report-title">';
            echo t('admin_reports.in_topic', topicName: htmlspecialchars($topicName), topicUrl: "topic.php?topic=" . $result['topic']);
            echo t('admin_reports.reported_member_count',
                count: $result['count'],
                showMembers: "javascript:showMembers(" . $result['reportid'] . ")",
            );

            echo '</div>';

            echo '<div class="report-members" id="report-member-'.$result['reportid'].'"></div>';
            echo '<div class="fMessages" data-topic="'.$result['topic'].'">';
            print_forum_msg($result, array(
                'mayEdit' => !$archivedFilter,
                'mayQuote' => false,
                'mayReact' => false,
                'mayReport' => false,
                'canModerate' => true,
                'archived' => $archivedFilter
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
            <?php echo $language ? 'Page: &nbsp;':'Page : ';
            $get = $_GET;
            foreach ($get as $k => $getk)
                $get[$k] = stripslashes($get[$k]);
            require_once('../includes/utils-paging.php');
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
    echo t('admin_reports.no_pending_reported_message');
    echo '</h4>';
}
?>
</div>
<p class="forumButtons">
    <?php
    if ($archivedFilter)
        echo '<a href="adminReports.php">'. t('admin_reports.back_pending_reports') .'</a><br />';
    else
        echo '<a href="adminReports.php?archived=1">'. t('admin_reports.see_archived_reports') .'</a><br />';
    ?>
    <a href="forum.php"><?= t('common.back_forum') ?></a>
</p>
</main>
<?php
include('../includes/footer.php');
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
function unarchiveReport(reportId) {
    if (confirm(o_language ? 'Unarchive this message?' : 'Désarchiver ce message ?')) {
        o_xhr("archiveReport.php", "id="+reportId+"&unarchive", function(res) {
            if (res != 1) return false;
            document.getElementById("reports-wrapper").removeChild(document.getElementById("report-wrapper-"+reportId));
            return true;
        });
    }
}
</script>
</body>
</html>