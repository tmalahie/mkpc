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
        if ($circuit = mysql_fetch_array(mysql_query('SELECT identifiant FROM `'. $rating['type'] .'` WHERE id='. $rating['circuit']))) {
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
        $circuitId = $creationData['id'];
        $circuitType = $creationData['type'];
    }
}
?>
<main>
	<h1><?php echo $language ? 'Manage circuit ratings':'Gérer les notes des circuits'; ?></h1>
	<p>
        <?php
        if ($language) {
            ?>
            This page allows you to view and delete the ratings given by members on a track.<br />
            Please only use this feature to remove troll or unjustified ratings.<br />
            To avoid abuses, it will be impossible for you to remove ratings on your own track.
            <?php
        }
        else {
            ?>
            Cette page vous permet de voir et supprimer les notes données par les membres sur un circuit.<br />
            Merci d'utiliser cette fonctionnalité uniquement pour supprimer les trolls ou autres notes injustifiées.<br />
            Pour éviter les abus, il vous est impossible de supprimer les notes de votre propre circuit.
            <?php
        }
        ?>
    </p>
    <form method="get">
        <strong><?php echo $language ? 'Circuit URL:':'URL du circuit :'; ?></strong>
        <input type="url" name="url" placeholder="https://mkpc.malahieude.net/map.php?mid=2432" value="<?php if (isset($_GET['url'])) echo htmlspecialchars($_GET['url']); ?>" />
        <input type="submit" class="action_button" value="Ok" />
    </form>
    <?php
    if (isset($_GET['url'])) {
        if (isset($circuitType)) {
            require_once('utils-cups.php');
            if ($getCircuit = fetchCreationData($circuitType, $circuitId, array('select' => 'c.identifiant'))) {
                $myCircuit = ($getCircuit['identifiant'] == $identifiants[0]);
                ?>
                <h2><?php echo ($language ? 'Ratings of' : 'Notes de') . ' '. htmlspecialchars($getCircuit['name']); ?> :</h2>
                <table>
                	<tr id="titres">
                        <td style="min-width: 120px"><?php echo $language ? 'Nick':'Pseudo'; ?></td>
                        <td><?php echo $language ? 'Rating':'Note'; ?></td>
                        <td>Date</td>
                        <?php if (!$myCircuit) echo '<td>Action</td>'; ?>
                    </tr>
                    <?php
                    $getRatings = mysql_query('SELECT r.id,r.player,r.date,j.nom,r.rating FROM mkratings r LEFT JOIN mkjoueurs j ON r.player=j.id WHERE r.type="'. $circuitType .'" AND r.circuit="'. $circuitId .'" ORDER BY r.date DESC');
                    $i = 0;
                    while ($rating = mysql_fetch_array($getRatings)) {
                        echo '<tr class="'. ($i%2 ? 'fonce':'clair') .'">
                            <td>'. ($rating['player'] ? '<a href="profil.php?id='. $rating['player'] .'">'.$rating['nom'].'</a>' : '<em>'.($language ? 'No account':'Aucun compte').'</em>') .'</td>
                            <td>'.$rating['rating'].'/5</td>
                            <td>'.$rating['date'].'</td>
                            '. ($myCircuit ? '': '<td><a class="action_button" href="?url='. urlencode($_GET['url']) .'&del='. $rating['id'] .'" onclick="return confirmDelete()">'. ($language ? 'Delete':'Supprimer') .'</a></td>') .'
                        </tr>';
                        $i++;
                    }
                    if (!$i) {
                        echo '<tr class="fonce">
                            <td colspan="'. ($myCircuit ? 3:4) .'"><em>'. ($language ? 'No rating for this circuit':'Aucune note sur ce circuit') .'</em></td>
                        </tr>';
                    }
                    ?>
                </table>
                <?php
            }
            else {
                echo '<p class="error">'. ($language ? 'This circuit does not exist' : 'Ce circuit n\'existe pas') .'</p>';
            }
        }
        else {
            echo '<p class="error">'. ($language ? 'This URL does not correspond to a circuit URL' : 'Cette URL n\'est pas celle d\'un circuit') .'</p>';
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