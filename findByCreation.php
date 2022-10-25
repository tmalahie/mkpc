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
include('getId.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Manage circuit ratings':'Gérer les notes des circuits'; ?> - Mario Kart PC</title>
<?php
include('heads.php');
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
</style>
<?php
include('o_online.php');
?>
</head>
<body>
<?php
include('header.php');
$page = 'game';
include('menu.php');
if (isset($_GET['del'])) {
    if ($rating = mysql_fetch_array(mysql_query('SELECT type,circuit,identifiant FROM mkratings WHERE id="'. $_GET['del'] .'"'))) {
        if ($circuit = mysql_fetch_array(mysql_query('SELECT nom,identifiant FROM `'. $rating['type'] .'` WHERE id='. $rating['circuit']))) {
            if ($circuit['identifiant'] != $identifiants[0]) {
                mysql_query('DELETE FROM mkratings WHERE id="'. $_GET['del'] .'"');
                require_once('utils-ratings.php');
                recomputeRating($rating['type'], $rating['circuit']);
                mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "DRating '. $rating['type'] .' '. $rating['circuit'] .' '. $rating['identifiant'] .'")');
            }
        }
    }
}
if (isset($_GET['url'])) {
    include('adminUtils.php');
    $creationData = getCreationByUrl($_GET['url']);
    if ($creationData) {
        $creationId = $creationData['id'];
        $creationType = $creationData['type'];
        $creationPk = 'id';
    }
    if (!$creationData) {
        if (preg_match('#/images/sprites/uploads/(cp-\w+-\d+)(?:-\w+)?\.png$#', $_GET['url'], $matches)) {
            $creationId = $matches[1];
            $creationType = 'mkchars';
            $creationPk = 'sprites';
        }
    }
}
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
    if (isset($_GET['url'])) {
        if (isset($creationType)) {
            if ($getCreation = mysql_fetch_array(mysql_query('SELECT identifiant FROM `'. $creationType .'` WHERE '. $creationPk .'="'. $creationId .'"'))) {
                echo '<p>';
                $getPlayers = mysql_query('SELECT j.id,j.nom FROM mkprofiles p INNER JOIN mkjoueurs j ON p.id=j.id WHERE p.identifiant="'. $getCreation['identifiant'] .'"');
                $nbPlayers = 0;//mysql_numrows($getPlayers);
                if ($nbPlayers) {
                    $v = '';
                    $s = ($nbPlayers >= 2) ? 's' : '';
                    $have = $language ? (($nbPlayers >= 2) ? 'have':'has') : (($nbPlayers >= 2) ? 'ont':'a');
                    echo $language ? "The following account$s $have been found: " : "Le$s compte$s suivant$s $have été trouvé$s : ";
                    while ($player = mysql_fetch_array($getPlayers)) {
                        echo $v.'<a href="profil.php?id='. $player['id'] .'"><strong>'.$player['nom'].'</strong></a>';
                        $v = ', ';
                    }
                }
                else {
                    echo '<em>';
                    echo $language ? "No account has been found" : "Aucun compte n'a été trouvé";
                    echo '</em>';
                    echo '. ';
                    $get = $_GET;
                    $get['create'] = 1;
                    echo '<a href="findByCreation.php?'. http_build_query($get) .'" style="color: #080">';
                    echo $language ? "Create account from user" : "Créer un compte à partir de l'utilisateur";
                    echo '</a>';
                }
                echo '</p>';
            }
            else {
                echo '<p class="error">'. ($language ? 'This circuit/character does not exist' : 'Ce circuit/perso n\'existe pas') .'</p>';
            }
        }
        else {
            echo '<p class="error">'. ($language ? 'This URL does not correspond to a circuit/character URL' : 'Cette URL n\'est pas celle d\'un circuit ou d\'un perso') .'</p>';
        }
    }
    ?>
	<p><a href="admin.php"><?php echo $language ? 'Back to the admin page':'Retour à la page admin'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('footer.php');
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