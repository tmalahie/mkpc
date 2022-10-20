<?php
if (!isset($action)) exit;
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
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php
    switch ($action) {
    case 'ban':
        echo $language ? 'Ban member':'Bannir un membre';
        break;
    case 'warn':
        echo $language ? 'Warn member':'Avertir un membre';
    }
?> - Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />
<style type="text/css">
h1 {
    margin-bottom: 0;
}
h1 + p {
    margin-top: 6px;
    margin-bottom: 12px;
}
#ban_msg {
	display: none;
}
#titres td:nth-child(2) {
	width: 300px;
}
table a.profile {
	color: #820;
}
table a.profile:hover {
	color: #B50;
}
</style>
<?php
include('o_online.php');
?>
</head>
<body>
<?php
include('header.php');
$page = 'forum';
include('menu.php');
$ban = isset($_POST['joueur']) ? $_POST['joueur']:null;
if ($ban) {
	if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM `mkjoueurs` WHERE nom="'. $ban .'"'))) {
        switch ($action) {
        case 'ban':
            mysql_query('UPDATE `mkjoueurs` SET banned=2 WHERE id='. $getId['id']);
            mysql_query('DELETE FROM `mkbans` WHERE player="'. $getId['id'] .'"');
            mysql_query('DELETE FROM `mkwarns` WHERE player="'. $getId['id'] .'"');
            mysql_query('INSERT INTO `mkbans` VALUES('. $getId['id'] .',"'. $_POST['msg'] .'",'. (!empty($_POST['ban_until_date']) ? '"'. $_POST['ban_until_date'] .'"':'NULL') .')');
            mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Ban '. $getId['id'] .'")');
            if (isset($_POST['ip'])) {
                $getIp = mysql_fetch_array(mysql_query('SELECT identifiant,identifiant2,identifiant3,identifiant4 FROM `mkprofiles` WHERE id="'.$getId['id'].'"'));
                mysql_query('INSERT IGNORE INTO `ip_bans` VALUES('.$getId['id'].','.$getIp['identifiant'].','.$getIp['identifiant2'].','.$getIp['identifiant3'].','.$getIp['identifiant4'].')');
            }
            break;
        case 'warn':
            mysql_query('DELETE FROM `mkwarns` WHERE player="'. $getId['id'] .'"');
            mysql_query('INSERT INTO `mkwarns` VALUES('. $getId['id'] .',"'. $_POST['msg'] .'",0)');
            mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Warn '. $getId['id'] .'")');
            break;
        }
	}
}
$unban = isset($_GET['unban']) ? $_GET['unban']:null;
if ($unban) {
    switch ($action) {
    case 'ban':
        mysql_query('UPDATE `mkjoueurs` SET banned=0 WHERE id="'. $unban .'"');
        mysql_query('DELETE FROM `ip_bans` WHERE player="'. $unban .'"');
        mysql_query('DELETE FROM `mkbans` WHERE player="'. $unban .'"');
        mysql_query('DELETE FROM `mkwarns` WHERE player="'. $unban .'"');
        mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Unban '. $unban .'")');
        break;
    case 'warn':
        mysql_query('DELETE FROM `mkwarns` WHERE player="'. $unban .'"');
        mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Unwarn '. $unban .'")');
        break;
    }
}
?>
<main>
	<?php
	if ($ban) {
        switch ($action) {
        case 'ban':
            if ($language)
                echo '<p><strong>'. $ban .'</strong> has been banned</p>';
            else
                echo '<p><strong>'. $ban .'</strong> a été banni</p>';
            break;
        case 'warn':
            if ($language)
                echo '<p><strong>'. $ban .'</strong> has been warned</p>';
            else
                echo '<p>Un avertissement a été envoyé à <strong>'. $ban .'</strong></p>';
        }
    }
	elseif ($unban) {
		if ($getNom = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $unban .'"'))) {
            switch ($action) {
            case 'ban':
                if ($language)
                    echo '<p><strong>'. $getNom['nom'] .'</strong> has been unbanned</p>';
                else
                    echo '<p><strong>'. $getNom['nom'] .'</strong> a été débanni</p>';
                break;
            case 'warn':
                if ($language)
                    echo '<p>Warning removed for <strong>'. $getNom['nom'] .'</strong></p>';
                else
                    echo '<p>Avertissement retiré pour <strong>'. $getNom['nom'] .'</strong></p>';
            }
        }
	}
	?>
	<h1><?php
    switch ($action) {
    case 'ban':
        echo $language ? 'Ban member':'Bannir un membre';
        break;
    case 'warn':
        echo $language ? 'Warn member':'Avertir un membre';
    }
    ?></h1>
    <p>
    <?php
    switch ($action) {
    case 'ban':
		if ($language)
			echo "When you ban a member, they can no longer post anything on the site. This action should therefore be used as a last resort.";
		else
            echo "Lorsque vous bannissez un membre, celui-ci ne peut plus rien poster sur le site. Cette action est donc à utiliser en dernier recours";
        break;
    case 'warn':
		if ($language)
			echo "This page allows you to warn a member for inappropriate behavior. The member will see the message next time they go to the site";
		else
			echo "Cette page vous permet de donner un avertissement à un membre pour comportement inapproprié. Le membre verra le message à sa prochaine connexion sur le site";
        break;
    }
	?>
	</p>
	<div class="ranking-modes">
        <?php
        switch ($action) {
        case 'ban':
            echo '<a href="warn-player.php">'. ($language ? 'Warned members':'Membres avertis') .'</a>';
            echo '<span>'. ($language ? 'Banned members':'Membres bannis') .'</span>';
            break;
        case 'warn':
            echo '<span>'. ($language ? 'Warned members':'Membres avertis') .'</span>';
            echo '<a href="ban-player.php">'. ($language ? 'Banned members':'Membres bannis') .'</a>';
            break;
        }
        ?>
		<a href="ban-ip.php"><?php echo $language ? 'Banned IPs':'IP bannies'; ?></a>
	</div>
	<form method="post" action="<?php echo $action; ?>-player.php">
	<blockquote>
	<p>
		<label for="joueur"><strong><?php
        switch ($action) {
        case 'ban':
            echo $language ? 'Ban a player':'Bannir un joueur';
            break;
        case 'warn':
            echo $language ? 'Warn a player':'Avertir un joueur';
            break;
        }
        ?></strong></label> : <input type="text" name="joueur" id="joueur" />
		<div id="ban_msg">
			Message : <textarea name="msg" cols="30" rows="4"<?php if ($action === 'warn') echo ' required="required"'; ?>></textarea><br />
            <?php
            if ($action === 'ban') {
                ?>
                <label><input type="checkbox" name="ban_until" onclick="hanleBanUntil(this.checked)" /> <?php echo $language ? "Ban until:":"Bannir jusqu'à :"; ?> <input type="date" name="ban_until_date" disabled /></label><br />
                <label><input type="checkbox" name="ip" /> <?php echo $language ? 'Also ban IP address':'Bannir également l\'adresse IP'; ?></label><br />
                <?php
            }
            ?>
            <input type="submit" value="Valider" class="action_button" />
		</div>
	</p>
	</blockquote>
	</form>
	<h2><?php
    switch ($action) {
    case 'ban':
        echo $language ? 'Banned people list':'Liste des membres bannis';
        break;
    case 'warn':
        echo $language ? 'Warned people list':'Liste des membres avertis';
        break;
    }
    ?></h2>
	<table>
	<tr id="titres">
	<td><?php echo $language ? 'Nick':'Pseudo'; ?></td>
	<td>Message</td>
	<?php
    if ($action === 'ban')
        echo '<td>'. ($language ? 'End date':'Date de fin') .'</td>';
    ?>
	<td><?php
    switch ($action) {
    case 'ban':
        echo $language ? 'Unban':'Débannir';
        break;
    case 'warn':
        echo $language ? 'Unwarn':'Retirer';
    }
    ?></td>
	</tr>
	<?php
    switch ($action) {
    case 'ban':
        $bannished = mysql_query('SELECT j.id,j.nom,b.msg,b.end_date FROM `mkjoueurs` j LEFT JOIN `mkbans` b ON j.id=b.player WHERE j.banned');	
        break;
    case 'warn':
        $bannished = mysql_query('SELECT j.id,j.nom,w.msg FROM `mkwarns` w INNER JOIN `mkjoueurs` j ON j.id=w.player');
        break;
    }
	function controlLength($str,$maxLength) {
		$pts = '...';
		if (strlen($str) > $maxLength)
			return substr($str,0,$maxLength-strlen($pts)).$pts;
		return $str;
	}
	while ($joueur = mysql_fetch_array($bannished)) {
		?>
		<tr>
		<td><a class="profile" href="profil.php?id=<?php echo $joueur['id']; ?>"><?php echo $joueur['nom']; ?></a></td>
		<td title="<?php if ($joueur['msg']) echo htmlspecialchars($joueur['msg']); ?>"><?php
			if ($joueur['msg']) echo nl2br(htmlspecialchars(controlLength($joueur['msg'],150)));
		?></td>
		<?php
        if ($action === 'ban')
            echo '<td>'. $joueur['end_date'] .'</td>';
        ?>
		<td><a href="?unban=<?php echo $joueur['id']; ?>" class="action_button"><?php
        switch ($action) {
        case 'ban':
            echo $language ? 'Unban':'Débannir';
            break;
        case 'warn':
            echo $language ? 'Unwarn':'Retirer';
        }
        ?></a></td>
		</tr>
		<?php
	}
	?>
	</table>
	<p><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('footer.php');
?>
<script type="text/javascript" src="scripts/jquery.min.js"></script>
<script type="text/javascript" src="scripts/auto-complete.min.js"></script>
<script type="text/javascript" src="scripts/autocomplete-player.js"></script>
<script type="text/javascript">
autocompletePlayer('#joueur', {
	onSelect: function(event, term, item) {
		preventSubmit(event);
		$("#ban_msg").show("fast");
	}
});
function hanleBanUntil(checked) {
	var $banUntilDate = $('input[name="ban_until_date"]');
	$banUntilDate.prop("disabled", !checked);
	$banUntilDate.prop("required", checked);
	if (checked)
		$banUntilDate.focus();
}
</script>
<?php
mysql_close();
?>
</body>
</html>