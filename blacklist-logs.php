<?php
include('session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('language.php');
include('initdb.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	mysql_close();
	exit;
}
require_once('getRights.php');
if (!hasRight('moderator')) {
	echo "Vous n'&ecirc;tes pas mod&eacute;rateur";
	mysql_close();
	exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Blacklist logs':'Logs blacklist'; ?> - Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />

<?php
include('o_online.php');
?>
<style type="text/css">
#log {
    width: 400px;
}

main table {
    font-weight: normal;
}

main table a {
    font-weight: bold;
    color: #f60;
}
main table a:hover {
    color: #f80;
}
table a.profile {
	color: #820;
}
table a.profile:hover {
	color: #B50;
}
table a.details {
    display: inline-block;
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
	<h1><?php echo $language ? 'Censored messages logs':'Logs des messages censurés'; ?></h1>
    <p>
        <?php
        if ($language) {
            ?>
            This page shows the list of posts that have been blocked by the moderation bot.<br />
            This can be used to take actions on the concerned members,<br />
            or on the contrary to soften the blocking rules in case of a false positive.
            <?php
        }
        else {
            ?>
            Cette page affiche la liste des messages qui ont été bloqués par le bot de modération.<br />
            Cela peut servir pour effectuer des actions sur les membres concernés,<br />
            ou au contraire pour assouplir les règles de bloquage en cas de faux positif.
            <?php
        }
        ?>
    </p>
    <table>
        <tr id="titres">
        <td><?php echo $language ? 'Member' : 'Membre'; ?></td>
        <td>Date</td>
        <td id="log">Message</td>
        <td><?php echo $language ? 'Block<br />Reason' : 'Raison du<br />bloquage'; ?></td>
        </tr>
        <?php
        $RES_PER_PAGE = 20;
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $getLogs = mysql_query('SELECT l.id,l.player,l.date,l.message,l.code,j.nom FROM mkbadmsglog l LEFT JOIN mkjoueurs j ON l.player=j.id ORDER BY l.id DESC LIMIT '. (($page-1)*$RES_PER_PAGE) .','.$RES_PER_PAGE);
        $logCount = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkbadmsglog'));
        include('utils-date.php');
        $blockReasons = array(
            '-1' => 'Blacklist',
            '-2' => 'Spam',
            '-3' => $language ? 'Message<br />too long' : 'Message<br />trop long'
        );
        while ($log = mysql_fetch_array($getLogs)) {
            ?>
        <tr>
        <td><a class="profile" href="profil.php?id=<?php echo $log['player']; ?>"><?php
        if ($log['nom'])
            echo $log['nom'];
        else
            echo '<em>'. ($language ? 'Deleted account' : 'Compte supprimé') .'</em>';
        ?></a></td>
        <td><?php echo to_local_tz($log['date']); ?></td>
        <td><?php
            echo $log['message'];
        ?>
        <a class="details" href="chat-logs.php?blacklist=<?php echo $log['id']; ?>#context">[<?php
            echo $language ? 'See logs in context' : 'Voir les logs en contexte';
        ?>]</a></td>
        <td><?php echo $blockReasons[$log['code']]; ?></td>
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