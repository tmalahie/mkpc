<?php
include('../includes/session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('../includes/language.php');
include('../includes/initdb.php');
require_once('../includes/getRights.php');
if (!hasRight('clvalidator')) {
	echo "Vous n'&ecirc;tes pas validateur";
	mysql_close();
	exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Ban member':'Bannir un membre'; ?> - Mario Kart PC</title>
<?php
include('../includes/heads.php');
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
#ban_explain ul {
    margin-top: 0;
    text-align: left;
    margin-left: auto;
    margin-right: auto;
    width: 450px;
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
#joueur {
    width: 400px;
}
</style>
<?php
include('../includes/o_online.php');
?>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'forum';
include('../includes/menu.php');
$ban = isset($_POST['ban']) ? $_POST['ban']:null;
$banIp = null;
$origin = (isset($_SERVER['HTTPS'])?'https':'http') . '://' . $_SERVER['HTTP_HOST'];
if ($ban) {
    $banId = null;
    if (str_starts_with($ban, $origin)) {
        $banLink = $ban;
        $profileUrl = "$origin/profil.php?id=";
        $challengeUrl = "$origin/challengeTry.php?challenge=";
        $circuitId = null;
        $circuitType = null;
        if (str_starts_with($ban, $profileUrl))
            $banId = substr($ban, strlen($profileUrl));
        elseif (str_starts_with($ban, $challengeUrl)) {
            $challengeId = intval(substr($ban, strlen($challengeUrl)));
            if ($getIp = mysql_fetch_array(mysql_query('SELECT l.identifiant,l.circuit,l.type FROM `mkchallenges` c INNER JOIN `mkclrace` l ON c.clist=l.id WHERE c.id="'. $challengeId .'"'))) {
                $banIp = $getIp['identifiant'];
                $circuitId = $getIp['circuit'];
                $circuitType = $getIp['type'];
                if ($getUser = mysql_fetch_array(mysql_query('SELECT auteur FROM `'. $circuitType .'` WHERE id="'. $circuitId .'"')))
                    $banUsername = $getUser['auteur'];
            }
        }
        else {
            include('../includes/adminUtils.php');
            $creationData = getCreationByUrl($ban);
            if ($creationData) {
                $circuitId = $creationData['id'];
                $circuitType = $creationData['type'];
                if ($getUser = mysql_fetch_array(mysql_query('SELECT auteur,identifiant FROM `'. $circuitType .'` WHERE id="'. $circuitId .'"'))) {
                    $banIp = $getUser['identifiant'];
                    $banUsername = $getUser['auteur'];
                }
            }
        }
    }
    else {
        if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM `mkjoueurs` WHERE nom="'. $ban .'"'))) {
            $banId = $getId['id'];
            $banLink = "$origin/profil.php?id=". $getId['id'];
        }
    }

    if ($banId) {
        if ($getUser = mysql_fetch_array(mysql_query('SELECT j.nom,p.identifiant FROM `mkjoueurs`j INNER JOIN `mkprofiles` p ON j.id=p.id WHERE j.id="'. $banId .'"'))) {
            $banIp = $getUser['identifiant'];
            $banUsername = $getUser['nom'];
        }
    }
    
	if ($banIp) {
        mysql_query('DELETE FROM `mkclbans` WHERE identifiant='. $banIp);
        mysql_query('INSERT INTO `mkclbans` SET identifiant='. $banIp .',link="'. $banLink .'",username="'.mysql_real_escape_string($banUsername).'",msg="'. $_POST['msg'] .'",ban_until_date='. (!empty($_POST['ban_until_date']) ? '"'. $_POST['ban_until_date'] .'"':'NULL'));
        $insertId = mysql_insert_id();
        mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "CBan '. $insertId .'")');
	}
}
$unban = isset($_GET['unban']) ? $_GET['unban']:null;
if ($unban) {
    if ($getUsername = mysql_fetch_array(mysql_query('SELECT username FROM `mkclbans` WHERE id="'. $unban .'"'))) {
        $banUsername = $getUsername['username'];
        mysql_query('DELETE FROM `mkclbans` WHERE id="'. $unban .'"');
        mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "CUnban '. $unban .'")');
    }
    else
        $unban = null;
}
?>
<main>
	<?php
	if ($banIp) {
        if ($language)
            echo '<p><strong>'. $banUsername .'</strong> has been banned</p>';
        else
            echo '<p><strong>'. $banUsername .'</strong> a été banni</p>';
    }
	elseif ($unban) {
        if (!$banUsername) $banUsername = $language ? 'Anonymous':'Anonyme';
        if ($language)
            echo '<p><strong>'. $banUsername .'</strong> has been unbanned</p>';
        else
            echo '<p><strong>'. $banUsername .'</strong> a été débanni</p>';
	}
	?>
	<h1><?php
    echo $language ? 'Ban member':'Bannir un membre';
    ?></h1>
    <p>
    <?php
    if ($language)
        echo "This page allows you to block a member from publishing challenges, for a fixed amount of time or permanently. Use it in case of repeated abuse or spam.";
    else
        echo "Cette page vous permet de bloquer un membre de la publication de challenges, pour une durée déterminée ou de manière permanente. Utilisez-le en cas de spam ou abus à répétition.";
	?>
	</p>
	<form method="post" action="challengesBan.php">
	<blockquote>
	<p>
		<label for="joueur"><strong><?php
            echo $language ? 'Ban a member':'Bannir un membre';
        ?></strong></label> : <input type="text" name="ban" id="joueur" onkeypress="handleValidate(event)" onblur="handleNameBlur()" />
		<div id="ban_msg">
			Message : <textarea name="msg" cols="30" rows="4"></textarea><br />
            <label><input type="checkbox" name="ban_until" onclick="hanleBanUntil(this.checked)" /> <?php echo $language ? "Ban until:":"Bannir jusqu'à :"; ?> <input type="date" name="ban_until_date" disabled /></label><br />
            <input type="submit" value="<?php echo $language ? 'Validate' : 'Valider'; ?>" class="action_button" />
		</div>
	</p>
	</blockquote>
	</form>
    <div id="ban_explain">
        <?php
        if ($language) {
            ?>
            To ban a member you can enter either:
            <ul>
                <li>His username (ex: <em>tendokiddo</em>)</li>
                <li>His profile URL (ex: <em><?php echo $origin; ?>/profil.php?id=73654</em>)</li>
                <li>The URL of a challenge he published (ex: <em><?php echo $origin; ?>/challengeTry.php?challenge=1234</em>)</li>
                <li>The URL of a circuit he published (ex: <em><?php echo $origin; ?>/circuit.php?id=1234</em>)</li>
            </ul>
            <?php
        }
        else {
            ?>
            Pour bannir un membre vous pouvez rentrer au choix :
            <ul>
                <li>Son pseudo (ex: <em>tendokiddo</em>)</li>
                <li>Son URL de profil (ex: <em><?php echo $origin; ?>/profil.php?id=73654</em>)</li>
                <li>L'URL d'un défi qu'il a publié (ex: <em><?php echo $origin; ?>/challengeTry.php?challenge=1234</em>)</li>
                <li>L'URL d'un circuit qu'il a publié (ex: <em><?php echo $origin; ?>/circuit.php?id=1234</em>)</li>
            </ul>
            <?php
        }
        ?>
    </div>
	<h2><?php
    echo $language ? 'Banned member list':'Liste des membres bannis';
    ?></h2>
	<table>
	<tr id="titres">
	<td><?php echo $language ? 'Username':'Pseudo'; ?></td>
	<td>Message</td>
	<?php
    echo '<td>'. ($language ? 'End date':'Date de fin') .'</td>';
    ?>
	<td><?php
    echo $language ? 'Unban':'Débannir';
    ?></td>
	</tr>
	<?php
    $bannished = mysql_query('SELECT * FROM `mkclbans` ORDER BY id DESC');
	function controlLength($str,$maxLength) {
		$pts = '...';
		if (strlen($str) > $maxLength)
			return substr($str,0,$maxLength-strlen($pts)).$pts;
		return $str;
	}
	while ($joueur = mysql_fetch_array($bannished)) {
		?>
		<tr>
		<td><a class="profile" href="<?php echo $joueur['link']; ?>"><?php echo $joueur['username'] ? $joueur['username'] : ($language ? 'Anonymous':'Anonyme'); ?></a></td>
		<td title="<?php if ($joueur['msg']) echo htmlspecialchars($joueur['msg']); ?>"><?php
			if ($joueur['msg']) echo nl2br(htmlspecialchars(controlLength($joueur['msg'],150)));
		?></td>
		<?php
        echo '<td>'. $joueur['ban_until_date'] .'</td>';
        ?>
		<td><a href="?unban=<?php echo $joueur['id']; ?>" class="action_button"><?php
        echo $language ? 'Unban':'Débannir';
        ?></a></td>
		</tr>
		<?php
	}
	?>
	</table>
	<p><a href="challengesList.php?moderate"><?php echo $language ? 'Back to moderation list':'Retour aux défis à valider'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('../includes/footer.php');
?>
<script type="text/javascript" src="scripts/jquery.min.js"></script>
<script type="text/javascript" src="scripts/auto-complete.min.js"></script>
<script type="text/javascript" src="scripts/autocomplete-player.js?reload=1"></script>
<script type="text/javascript">
function handleValidate(e) {
    if (e.keyCode == 13) {
        e.preventDefault();
        e.target.blur();
    }
}
function handleNameBlur() {
    $('#ban_msg').css('display', $('#joueur').val() ? 'block':'none');
}
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