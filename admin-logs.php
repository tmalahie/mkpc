<?php
include('session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('language.php');
include('initdb.php');
require_once('getRights.php');
if (!hasRight('moderator')) {
	echo "Vous n'&ecirc;tes pas mod&eacute;rateur";
	mysql_close();
	exit;
}
$logMapping = array(
    'AChallenge' => array(
        'render' => 'A accepté le défi <a href="challengeTry.php?challenge=$1">{{table.mkchallenges(id=$1).name|global.ifEmpty("Sans titre")}}</a>'
    ),
    'CCircuit' => array(
        'render' => 'A supprimé le circuit complet #$1'
    ),
    'Suppr' => array(
        'render' => function($group) {
            if (isset($group[2]))
                return 'A supprimé le message #$2 dans le topic <a href="topic.php?topic=$1">{{table.mktopics(id=$1).titre}}</a>';
            return 'A supprimé le topic #$1';
        }
    ),
    'Edit' => array(
        'render' => function($group) {
            if (isset($group[2]))
                return 'A modifié le <a href="topic.php?topic=$1&amp;message=$2">message #$2</a> dans le topic <a href="topic.php?topic=$1">{{table.mktopics(id=$1).titre}}</a>';
            return 'A modifié le topic <a href="topic.php?topic=$1">{{table.mktopics(id=$1).titre}}</a>';
        }
    ),
    'DChallenge' => array(
        'render' => 'A modifié la difficulté du défi <a href="challengeTry.php?challenge=$1">{{table.mkchallenges(id=$1).name|global.ifEmpty("Sans titre")}}</a> en <strong>{{table.mkchallenges(id=$1).difficulty|local.difficulty()}}</strong>',
        'locals' => array(
            'difficulty' => function($i) {
                require_once('challenge-consts.php');
                $difficulties = getChallengeDifficulties();
                return $difficulties[$i];
            }
        )
    ),
    'RChallenge' => array(
        'render' => 'A refusé le défi <a href="challengeTry.php?challenge=$1">{{table.mkchallenges(id=$1).name|global.ifEmpty("Sans titre")}}</a>'
    ),
    'Ban' => array(
        'render' => 'A banni le membre <a href="profil.php?id=$1">{{table.mkjoueurs(id=$1).nom|global.ifNull("Compte supprimé")}}</a>'
    ),
    'Unban' => array(
        'render' => 'A débanni le membre <a href="profil.php?id=$1">{{table.mkjoueurs(id=$1).nom|global.ifNull("Compte supprimé")}}</a>'
    ),
    'SComment' => array(
        'render' => 'A supprimé le commentaire #$1 sur un circuit'
    )
);
$logGlobals = array(
    'ifEmpty' => function($res, $fallback) {
        return empty($res) ? $fallback : $res;
    },
    'ifNull' => function($res, $fallback) {
        return ($res === null) ? $fallback : $res;
    }
);
function format_log($log) {
    global $logMapping;
    $logArgs = explode(' ', $log);
    $logType = $logArgs[0];
    if (!isset($logMapping[$logType])) return $log;
    $renderFn = $logMapping[$logType]['render'];
    switch (gettype($renderFn)) {
    case 'object':
        $pattern = $renderFn($logArgs);
        break;
    default:
        $pattern = $renderFn;
        break;
    }
    return format_log_pattern($pattern, $logArgs);
}
function format_log_pattern($pattern, $logArgs) {
    $pattern = preg_replace_callback('#\$(\d)#', function($matches) use ($logArgs) {
        return $logArgs[$matches[1]];
    }, $pattern);
    $pattern = preg_replace_callback('#\{\{(.+?)\}\}#', function($matches) use ($logArgs) {
        global $logMapping, $logGlobals;
        $logType = $logArgs[0];
        $expr = $matches[1];
        $exprParts = explode('|', $expr);
        $res = '';
        foreach ($exprParts as $exprPart) {
            $exprArgs = explode('.', $exprPart);
            $exprType = $exprArgs[0];
            switch ($exprType) {
            case 'table':
                if (preg_match('#^(\w+?)\((\w+?)=(.+)\)$#', $exprArgs[1], $query)) {
                    $table = $query[1];
                    $col = $query[2];
                    $value = json_decode($query[3]);
                    $field = $exprArgs[2];
                    if ($getRow = mysql_fetch_array(mysql_query('SELECT '.$field.' FROM `'.$table.'` WHERE '. mysql_real_escape_string($col) .'="'. mysql_real_escape_string($value) .'"')))
                        $res = $getRow[$field];
                }
                break;
            case 'global':
            case 'local':
                if (preg_match('#^(\w+?)\((.*?)\)$#', $exprArgs[1], $fnParts)) {
                    $fnName = $fnParts[1];
                    $fnArgs = explode(',', $fnParts[2]);
                    foreach ($fnArgs as &$fnArg)
                        $fnArg = json_decode($fnArg);
                    unset($fnArg);
                    switch ($exprType) {
                    case 'global':
                        $fn = $logGlobals[$fnName];
                        break;
                    case 'local':
                        $fn = $logMapping[$logType]['locals'][$fnName];
                        break;
                    }
                    array_unshift($fnArgs, $res);
                    $res = call_user_func_array($fn, $fnArgs);
                }
                break;
            }
        }
        return $res;
    }, $pattern);
    // TODO keep going
    return $pattern;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Admin logs' : 'Logs admin'; ?> - Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<?php
include('o_online.php');
?>
<style type="text/css">
main table {
    font-weight: normal;
}
</style>
</head>
<body>
<?php
include('header.php');
$page = 'forum';
include('menu.php');
?>
<main>
	<h1><?php echo $language ? 'Admin logs' : 'Logs admin'; ?></h1>
    <p><?php echo $language
        ? "This page shows the history of all actions made by MKPC staff members"
        : "Cette page affiche l'historique de toutes les actions effectuées par l'équipe admin MKPC"
    ?></p>
    <table>
        <tr id="titres">
        <td><?php echo $language ? 'Nick':'Pseudo'; ?></td>
        <td>Date</td>
        <td>Log</td>
        </tr>
    <?php
    $RES_PER_PAGE = 50;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $getLogs = mysql_query('SELECT l.id,l.auteur,l.date,l.log,j.nom FROM mklogs l LEFT JOIN mkjoueurs j ON l.auteur=j.id ORDER BY l.id DESC LIMIT ' . (($page-1)*$RES_PER_PAGE) .','.$RES_PER_PAGE);
    $logCount = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mklogs'));
    while ($log = mysql_fetch_array($getLogs)) {
        ?>
        <tr>
        <td><?php
            if ($log['nom'])
                echo '<a class="profile" href="profil.php?id='.$log['auteur'].'">'.$log['nom'].'</a>';
            else
                echo '<em>'. ($language ? 'Deleted account' : 'Compte supprimé') .'</em>';
        ?></td>
        <td><?php echo $log['date']; ?></td>
        <td><?php echo format_log($log['log']); ?></td>
        </tr>
        <?php
    }
    ?>
    <tr><td colspan="4" id="page"><strong>Page : </strong> 
    <?php
    function pageLink($page, $isCurrent) {
        echo ($isCurrent ? '<span>'.$page.'</span>' : '<a href="?page='.$page.'">'.$page.'</a>').'&nbsp; ';
    }
    $limite = ceil($logCount['nb']/$RES_PER_PAGE);
    require_once('utils-paging.php');
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
	<p><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('footer.php');
mysql_close();
?>
</body>
</html>