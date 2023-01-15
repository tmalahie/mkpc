<?php
include('getId.php');
include('language.php');
include('initdb.php');
require_once('utils-challenges.php');
require_once('collabUtils.php');
if (isset($_GET['rw'])) {
	$reward = mysql_fetch_array(mysql_query('SELECT * FROM mkclrewards WHERE id="'. $_GET['rw'] .'"'));
	if ($reward)
        $clRace = getClRace($reward['clist']);
}
elseif (isset($_GET['cl']))
    $clRace = getClRace($_GET['cl']);
include('challenge-cldata.php');
if (isset($_POST['perso']) && isset($_POST['challenges']) && !empty($clRace)) {
    $challengeIds = array();
    foreach ($_POST['challenges'] as $challengeId)
        $challengeIds[] = intval($challengeId);
    if (!empty($challengeIds)) {
        $challengeIdsString = implode(',', $challengeIds);
        $challengeIds = array();
        $getChallenges = mysql_query('SELECT c.id FROM mkchallenges c INNER JOIN mkclrace l ON c.clist=l.id WHERE l.identifiant='.$identifiants[0].' AND l.identifiant2='.$identifiants[1].' AND l.identifiant3='.$identifiants[2].' AND l.identifiant4='.$identifiants[3].' AND c.id IN ('. $challengeIdsString .') AND c.status!="deleted"');
        while ($challenge = mysql_fetch_array($getChallenges))
            $challengeIds[] = $challenge['id'];
    }
    $clMsg = null;
    if (!empty($challengeIds)) {
        $persoId = $_POST['perso'];
        $hasCollabGrants = hasCollabGrants('mkchars', $persoId, $_POST['collab'], 'use');
        if ($perso = mysql_fetch_array(mysql_query('SELECT * FROM `mkchars` WHERE id="'. $persoId .'" AND name!="" AND author IS NULL'))) {
            if ($hasCollabGrants || (($perso['identifiant'] == $identifiants[0]) && ($perso['identifiant2'] == $identifiants[1]) && ($perso['identifiant3'] == $identifiants[2]) && ($perso['identifiant4'] == $identifiants[3]))) {
                if (isset($reward)) {
                    $rewardId = intval($reward['id']);
                    mysql_query('UPDATE mkclrewards SET charid="'. $persoId .'" WHERE id='.$rewardId);
                    mysql_query('DELETE FROM mkclrewardchs WHERE reward='. $rewardId);
                }
                else {
                    mysql_query('INSERT INTO mkclrewards SET clist="'. $clRace['id'] .'", charid="'. $persoId .'"');
                    $rewardId = mysql_insert_id();
                }
                foreach ($challengeIds as $challengeId)
                    mysql_query('INSERT INTO `mkclrewardchs` SET reward='. $rewardId .',challenge='. $challengeId);
                $getRewarded = getRewardedPlayers(array(
                    'reward' => $rewardId
                ));
                $rewardedPlayers = array();
                foreach ($getRewarded as $rewarded)
                    $rewardedPlayers[] = $rewarded['player'];
                if (empty($rewardedPlayers))
                    $rewardedPlayers = array(0);
                mysql_query('DELETE FROM `mkclrewarded` WHERE reward='. $rewardId .' AND player NOT IN('. implode(',', $rewardedPlayers) .')');
                foreach ($getRewarded as $rewarded)
                    mysql_query('INSERT IGNORE INTO `mkclrewarded` SET reward='. $rewardId .',player='.$rewarded['player']);

                $clMsg = 'reward_created';
            }
            elseif (isset($_POST['collab']))
                $clMsg = 'invalid_collab_link';
        }
        else
            $clMsg = 'character_not_found';
    }
	header('location: '. nextPageUrl('challengeRewards.php', array('rw'=>null,'cl'=>$clRace['clid'],'clmsg'=>$clMsg)));
	exit;
}
$clOptions = array('alltracks' => true, 'status' => array('active'));
if (!empty($clRace))
    $challenges = listChallenges($clRace['id'], $clOptions);
else
    $challenges = array();
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/challenges.css" />
<?php
include('o_online.php');
?>
<script type="text/javascript" src="scripts/challenges.js?reload=1"></script>
<title><?php echo $language ? 'Challenge rewards':'Défis et récompenses'; ?> - Mario Kart PC</title>
</head>
<body>
<h1 class="challenge-main-title"><?php
    if (isset($reward))
        echo $language ? 'Edit reward' : 'Modifier une récompense';
    else
        echo $language ? 'Add reward' : 'Ajouter une récompense';
?></h1>
<form method="post" action="" class="challenge-edit-form">
    <fieldset class="challenge-reward">
        <?php echo $language ? 'Unlocked character':'Perso à débloquer'; ?> :<br />
        <input type="text" name="perso" required="required" style="display:none" />
        <input type="hidden" name="collab" />
        <?php
        $getEligiblePersos = mysql_query('SELECT * FROM `mkchars` WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3].' AND name!="" AND author IS NULL ORDER BY id DESC');
        $areEligiblePersos = mysql_numrows($getEligiblePersos);
        if ($areEligiblePersos) {
            ?>
            <div class="challenge-character-selector">
            <?php
            $selectedPerso = isset($reward) ? $reward['charid'] : -1;
            require_once('persos.php');
            while ($perso = mysql_fetch_array($getEligiblePersos)) {
                $spriteSrcs = get_sprite_srcs($perso['sprites']);
                ?>
                <div data-cid="<?php echo $perso['id'] ?>" data-cname="<?php echo $perso['name'] ?>" onclick="selectPerso(this)"><img src="<?php echo $spriteSrcs['ld']; ?>" alt="<?php echo $perso['name'] ?>" /></div>
                <?php
            }
            ?>
            </div>
            <?php
        }
        ?>
        <div id="challenge-reward-selection"></div>
        <?php
        if ($areEligiblePersos)
            echo '<div id="challenge-reward-note">'. ($language ? 'Note that only unpublished characters appear in this list' : 'Notez que seuls les persos non publiés apparaissent dans cette liste') .'</div>';
        else {
            echo '<div class="challenge-reward-empty">';
            $getPersos = mysql_query('SELECT * FROM `mkchars` WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3].' AND name!="" ORDER BY id DESC');
            $arePersos = mysql_numrows($getPersos);
            if ($arePersos)
                echo $language ? 'You don\'t have any elligible character yet. To be elligible, your character must not have been shared. Go to the <a class="pretty-link" href="persoEditor.php">character editor</a> to manage your existing characters or create a new one.':'Vous n\'avez aucun perso elligible pour l\'instant. Pour être elligible, votre personnage ne doit pas avoir été partagé. Rendez-vous dans l\'<a class="pretty-link" href="persoEditor.php">éditeur de persos</a> pour gérer vos persos ou en créer de nouveaux.';
            else
                echo $language ? 'You haven\'t created any character yet. Go to the <a class="pretty-link" href="persoEditor.php">character editor</a> to create one.':'Vous n\'avez créé aucun perso pour l\'instant. Rendez-vous dans l\'<a class="pretty-link" href="persoEditor.php">éditeur de persos</a> pour en créer.';
            echo '</div>';
        }
        ?>
        <div class="challenge-perso-collab">
            <?php
            echo '<em>'. ($language ? 'OR' : 'OU') .'</em> <a class="pretty-link" href="javascript:toggleCollabChar()">'. ($language ? "Select character from another member..." : "Sélectionner le perso d'un autre membre...") .'</a>';
            ?>
            <label id="challenge-perso-collab">
                <span>
                    <?php
                    echo $language ? 'Collaboration link' : 'Lien de collaboration';
                    ?><a href="javascript:void(0)" class="pretty-link pretty-title" title="<?php
                    echo $language ? "Enter the characters's collaboration link here.<br />To get this link, the character owner will simply need to select the character in the editor and click on &quot;Collaborate&quot;" : "Saisissez ici le lien de collaboration du perso.<br />Pour obtenir ce lien, le propriétaire du perso devra simplement sélectionner le perso dans l'éditeur et cliquer sur &quot;Collaborer&quot;";
                    ?>">[?]</a>:
                </span>
                <input type="url" id="collab-link" placeholder="<?php
                $collab = array(
                    'type' => 'mkchars',
                    'creation_id' => 42,
                    'secret' => 'y-vf-erny_2401_pbasvezrq'
                );
                echo getCollabUrl($collab);
                ?>" onchange="handleCollabCharChange(this)" />
            </label>
        </div>
        <br />
        <?php echo $language ? 'Challenge(s) to succeed':'Défi(s) à réaliser'; ?> :<br />
        <?php
        if (empty($challenges)) {
            echo '<div class="challenge-reward-empty">';
            echo $language ? 'You don\'t have any active challenges yet. Go back to the <a class="pretty-link" href="'. nextPageUrl('challenges.php', array('rw'=>null,'cl'=>empty($clRace)?null:$clRace['clid'])) .'">challenges list</a> to complete your existing challenges or create a new one.':'Vous n\'avez aucun défi actif pour l\'instant. Retournez à la <a class="pretty-link" href="'. nextPageUrl('challenges.php', array('rw'=>null,'cl'=>empty($clRace)?null:$clRace['clid'])) .'">liste des défis</a> pour compléter vos défis existants ou en créer de nouveaux.';
            echo '</div>';
        }
        ?>
        <select name="challenges[]" required="required" multiple="multiple"<?php if (empty($challenges)) echo ' style="display:none"'; ?>>
            <?php
            $selectedChallenges = array();
            if (isset($reward)) {
                $getChallenges = mysql_query('SELECT challenge FROM mkclrewardchs WHERE reward="'. $reward['id'] .'"');
                while ($challenge = mysql_fetch_array($getChallenges))
                    $selectedChallenges[$challenge['challenge']] = 1;
            }
    		foreach ($challenges as $challenge) {
                echo '<option value="'. $challenge['id'] .'"'. (isset($selectedChallenges[$challenge['id']]) ? ' selected="selected"':'') .'>';
				if ($challenge['name'])
                    echo htmlspecialchars($challenge['name']);
                else
                    echo $challenge['description']['main'];
                echo '</option>';
            }
            ?>
        </select>
        <button type="submit" class="challenge-edit-submit"><?php echo $language ? 'Validate!':'Valider !'; ?></button>
    </fieldset>
</form>
<div class="challenge-navigation">
    <a href="<?php echo nextPageUrl('challengeRewards.php', array('rw'=>null,'cl'=>empty($clRace)?null:$clRace['clid'])); ?>">&lt; <u><?php echo $language ? 'Back to rewards list':'Retour à la liste des récompenses'; ?></u></a>
</div>
<script type="text/javascript">
var $form = document.querySelector('.challenge-edit-form');
function resetSelectedPerso() {
    var $selectedPerso = document.querySelector(".challenge-character-selector > div.character-selected");
    if ($selectedPerso)
        $selectedPerso.className = "";
}
function selectPerso($elt) {
    resetSelectedPerso();
    $elt.className = "character-selected";
    $form.elements["perso"].value = $elt.dataset.cid;
    $form.elements["collab"].value = "";
    document.getElementById("collab-link").value = "";
    document.getElementById("challenge-reward-selection").innerHTML = $elt.dataset.cname;
    onFormChange();
}
<?php
if (isset($reward)) {
    ?>
    var $currentlySelectedPerso = document.querySelector('.challenge-character-selector > div[data-cid="<?php echo $reward['charid']; ?>"]');
    if ($currentlySelectedPerso)
        selectPerso($currentlySelectedPerso);
    else
        toggleCollabChar();
    <?php
}
?>
function onFormChange() {
    $form.querySelector('.challenge-edit-submit').disabled = !$form.checkValidity()
}

function handleCollabCharChange($elt) {
    var $form = $elt.form;
    var $perso = $form.elements["perso"];
    var $collab = $form.elements["collab"];
    var url = $elt.value;
    if (url) {
        $perso.value = "";
        $collab.value = "";
        resetSelectedPerso();
        document.getElementById("challenge-reward-selection").innerHTML = "";
		try {
			var urlParams = new URLSearchParams(new URL(url).search);
			$perso.value = urlParams.get('id');
			$collab.value = urlParams.get('collab');
		}
		catch (e) {
		}
    }
    else if ($collab.value) {
        $perso.value = "";
        $collab.value = "";
    }
    onFormChange();
}
function toggleCollabChar() {
    var $collabCharForm = document.getElementById("challenge-perso-collab");
    $collabCharForm.className = $collabCharForm.className ? "" : "shown";
    if ($collabCharForm.className)
        document.getElementById("collab-link").focus();
}
$form.addEventListener("change", onFormChange);
onFormChange();
document.addEventListener("DOMContentLoaded", initPrettyTitles);
</script>
</body>
</html>
<?php
mysql_close();
?>