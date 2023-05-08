<?php
include('../includes/session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('../includes/language.php');
include('../includes/initdb.php');
require_once('../includes/getRights.php');
if (!hasRight('moderator')) {
	echo "Vous n'&ecirc;tes pas mod&eacute;rateur";
	mysql_close();
	exit;
}
include('../includes/getId.php');
$resMessage = '';
if (!empty($_GET['url'])) {
    include('../includes/adminUtils.php');
    $creationData = getCreationByUrl($_GET['url']);
    if ($creationData) {
        $creationId = $creationData['id'];
        $creationType = $creationData['type'];
        $creationPk = 'id';
        $authorCol = 'auteur';
    }
    if (!$creationData) {
        if (preg_match('#/images/sprites/uploads/(cp-\w+-\d+)(?:-\w+)?\.png$#', $_GET['url'], $matches)) {
            $creationId = $matches[1];
            $creationType = 'mkchars';
            $creationPk = 'sprites';
            $authorCol = 'author';
        }
    }
    if (isset($creationType)) {
        if ($getCreation = mysql_fetch_array(mysql_query('SELECT identifiant,'.$authorCol.' FROM `'. $creationType .'` WHERE '. $creationPk .'="'. $creationId .'"'))) {
            $resMessage .= '<p>';
            if (isset($_GET['created']))
                $getPlayers = mysql_query('SELECT id,nom FROM mkjoueurs WHERE id="'. $_GET['created'] .'"');
            else
                $getPlayers = mysql_query('SELECT j.id,j.nom FROM mkprofiles p INNER JOIN mkjoueurs j ON p.id=j.id WHERE p.identifiant="'. $getCreation['identifiant'] .'"');
            $nbPlayers = mysql_numrows($getPlayers);
            if ($nbPlayers) {
                $v = '';
                $s = ($nbPlayers >= 2) ? 's' : '';
                $have = $language ? (($nbPlayers >= 2) ? 'have':'has') : (($nbPlayers >= 2) ? 'ont':'a');
                $resMessage .= '<div class="success-message">';
                if (isset($_GET['created']))
                    $resMessage .= $language ? "The following account has been created: " : "Le compte suivant a été créé : ";
                else
                    $resMessage .= $language ? "The following account$s $have been found: " : "Le$s compte$s suivant$s $have été trouvé$s : ";
                while ($player = mysql_fetch_array($getPlayers)) {
                    $resMessage .= $v.'<a href="profil.php?id='. $player['id'] .'"><strong>'.$player['nom'].'</strong></a>';
                    $v = ', ';
                }
                $resMessage .= '</div>';
                $resMessage .= '<br /><em>'. ($language ? "Note: if you want to prevent the user from posting new creations, you'll have to IP-ban the account" : "Note : si vous souhaitez empêcher l'utilisateur de poster d'autres créations, il vous faudra ban-IP son compte") .'</em>';
            }
            elseif (isset($_GET['create'])) {
                $pseudo = $getCreation[$authorCol];
                $pseudo = preg_replace('#[^a-zA-Z0-9_\-]#', '', $pseudo);
                if (!$pseudo)
                    $pseudo = 'fake-account';
                $pseudo0 = $pseudo;
                $i = 0;
                $userId = 0;
                for ($i=-1;$i<100;$i++) {
                    if ($i >= 0) {
                        $pseudo = $pseudo0 . '_';
                        if ($i)
                            $pseudo .= $i;
                    }
                    mysql_query('INSERT IGNORE INTO `mkjoueurs` SET nom="'.$pseudo.'", code="********", joueur=0, choice_map=0, choice_rand=0, pts_vs=5000, pts_battle=5000, pts_challenge=0, online=2, banned=0, deleted=0');
                    $userId = mysql_insert_id();
                    if ($userId)
                        break;
                }
                if ($userId) {
                    mysql_query('INSERT INTO `mkprofiles` SET id='. $userId .', identifiant='.$getCreation['identifiant'].',identifiant2=0,identifiant3=0,identifiant4=0,avatar="",nick_color="'.$pseudo.'",nbmessages=0,email="",country=0,sub_date=CURDATE(),description=""');
                    $get = $_GET;
                    unset($get['create']);
                    $get['created'] = $userId;
                    header('location: findByCreation.php?'. http_build_query($get));
                }
            }
            else {
                $resMessage .= '<em>';
                $resMessage .= $language ? "No account has been found" : "Aucun compte n'a été trouvé";
                $resMessage .= '</em>';
                $resMessage .= '. ';
                $get = $_GET;
                $get['create'] = 1;
                $resMessage .= '<a href="findByCreation.php?'. http_build_query($get) .'" style="color: #080">';
                $resMessage .= $language ? "Create account from user" : "Créer un compte à partir de l'utilisateur";
                $resMessage .= '</a>';
            }
            $resMessage .= '</p>';
        }
        else {
            $resMessage .= '<p class="error">'. ($language ? 'This circuit/character does not exist' : 'Ce circuit/perso n\'existe pas') .'</p>';
        }
    }
    else {
        $resMessage .= '<p class="error">'. ($language ? 'This URL does not correspond to a circuit/character URL' : 'Cette URL n\'est pas celle d\'un circuit ou d\'un perso') .'</p>';
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Manage circuit ratings':'Gérer les notes des circuits'; ?> - Mario Kart PC</title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />
<style type="text/css">
form {
    font-size: 1.3em;
}
form input {
    font-size: 1em;
}
form input.action_button {
    font-size: 0.8em;
    position: relative;
    top: -0.1em;
}
.error {
	text-align: center;
	font-weight: bold;
	color: #A00;
}
h2 {
    font-size: 1.2em;
    text-decoration: underline;
}
table em {
    font-weight: normal;
}
main tr.clair a.action_button, main tr.fonce a.action_button {
    color: white;
}
.success-message {
    display: inline-block;
    margin-left: auto;
    margin-right: auto;
    background-color: #9F9;
    color: #031;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 0.5em;
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
	<h1><?php echo $language ? "Find author of creation":"Trouver l'auteur d'une création"; ?></h1>
	<p>
        <?php
        if ($language) {
            ?>
            This page allows you to find a user from his creation.<br />
            This can be useful if you find an inappropriate track or character and want to punish his author,<br />
            but you don't know his account.<br />
            If the user does not have an account, you will have the option to create it.
            <?php
        }
        else {
            ?>
            Cette page vous permet de retrouver un utilisateur à partir de sa création.<br />
            Cela peut être utile si vous trouvez un circuit ou un perso inapproprié et que vous voulez sanctionner son auteur,<br />
            mais que vous ne connaissez pas son compte.<br />
            Si l'utilisateur n'a pas de compte, on vous proposera de le créer.
            <?php
        }
        ?>
    </p>
    <form method="get">
        <strong><?php echo $language ? 'Circuit/Character URL:':'URL du circuit/perso :'; ?></strong>
        <input type="url" name="url" placeholder="https://mkpc.malahieude.net/map.php?mid=2432" value="<?php if (isset($_GET['url'])) echo htmlspecialchars($_GET['url']); ?>" />
        <input type="submit" class="action_button" value="Ok" />
    </form>
    <?php
    echo $resMessage;
    ?>
	<p><a href="admin.php"><?php echo $language ? 'Back to the admin page':'Retour à la page admin'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('../includes/footer.php');
?>
<script type="text/javascript">
function confirmDelete() {
    return confirm("<?php echo $language ? 'Delete this rating?':'Supprimer cette note ?'; ?>");
}
</script>
<?php
mysql_close();
?>
</body>
</html>