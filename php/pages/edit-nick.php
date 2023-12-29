<?php
include('../includes/session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('../includes/language.php');
include('../includes/initdb.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Change your username':'Modifier mon pseudo'; ?> - Mario Kart PC</title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<link rel="stylesheet" type="text/css" href="styles/forms.css" />
<style type="text/css">
.warning-msg {
    font-size: 0.8em;
    max-width: 550px;
    margin: 0.2em auto;
}
.success {
    font-weight: normal;
	margin-bottom: 10px;
}
#newpseudo {
    font-size: 1em;
    width: 10em;
}
</style>
<script type="text/javascript">
var confirmed = false;
function confirmNick() {
    if (confirmed)
        return true;
    var nick = document.getElementById("newpseudo").value;
    o_confirm(o_language ? "Do you confirm the username <strong>"+ nick +"</strong>? Warning, you won't be able to rechange it for 24h." : "Confirmer le pseudo <strong>"+ nick +"</strong> ? Attention, vous ne pourrez pas le rechanger avant 24h.", function(valided) {
        if (valided) {
            confirmed = true;
            document.forms[0].submit();
        }
    });
    return false;
}
</script>

<?php
include('../includes/o_online.php');
?>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'forum';
include('../includes/menu.php');
$getPseudo = mysql_fetch_array(mysql_query('SELECT nom,banned FROM `mkjoueurs` WHERE id="'. $id .'"'));
$old = $getPseudo['nom'];
if (isset($_POST['newpseudo'])) {
    $new = $_POST['newpseudo'];
	if (!$getPseudo['banned']) {
        include('../includes/utils-nicks.php');
        $nextNickChange = 0;
        if ($lastNickChange = mysql_fetch_array(mysql_query('SELECT date FROM mknewnicks WHERE id="'. $id .'" ORDER BY date DESC LIMIT 1')))
            $nextNickChange = strtotime($lastNickChange['date'])+86400;
        if ($nextNickChange > time())
            $message = $language ? 'You have changed your username recently, therefore you are not allowed to rechange it for now. Please come back later.':'Vous avez changé votre pseudo récemment, vous ne pouvez donc pas le rechanger pour l\'instant. Revenez plus tard.';
        else
            $success = editNick($id,$getPseudo['nom'],$new,$message);
	}
	else
        $message = $language ? 'You have been banned, you cannot edit your username.':'Vous avez été banni, vous ne pouvez pas modifier votre pseudo.';
}
else
	mysql_query('DELETE FROM `mknewnicks` WHERE date<DATE_SUB(NOW(), INTERVAL 1 MONTH)');
?>
<main>
	<h1><?php echo $language ? 'Change username':'Modification de pseudo'; ?></h1>
	<?php
	if (isset($success))
		echo $language ? '<p class="success">You have been renamed into <strong>'. htmlspecialchars($new) .'</strong>.</p>' : '<p class="success">Vous avez été renommé en <strong>'. htmlspecialchars($new) .'</strong>.</p>';
	if (isset($message))
		echo '<p style="color: red">'. $message .'</p>';
    if ($language) {
        ?>
        This page allows you to change your current username.<br />
        Simply enter your new username in the field below:
        <?php
    }
    else {
        ?>
        Cette page vous permet de changer votre pseudo actuel.<br />
        Entrez simplement votre nouveau pseudo dans le champ ci-dessous :
        <?php
    }
    ?>
	<form method="post" action="edit-nick.php" onsubmit="return confirmNick()">
        <p>
            <input type="text" name="newpseudo" id="newpseudo" value="<?php if (isset($new)) echo htmlspecialchars($new); else echo $old; ?>" maxlength="30" required="required" />
            <input type="submit" value="Ok" class="action_button" />
        </p>
    </form>
    <?php
    if ($language) {
        ?>
        <div class="warning-msg">
            <strong>Warning, to avoid abuses, username changes are limited to 1 per day.</strong>
        </div>
        <?php
    }
    else {
        ?>
        <div class="warning-msg">
            <strong>Attention, pour éviter les abus, les changements de pseudos sont limités à 1 par jour.</strong>
        </div>
        <?php
    }
    ?>
    <p class="forumButtons">
        <a href="profil.php?id=<?php echo $id; ?>"><?php echo $language ? 'Back to your profile':'Retour à votre profil'; ?></a><br />
        <a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a>
    </p>
</main>
<?php
include('../includes/footer.php');
mysql_close();
?>
</body>
</html>