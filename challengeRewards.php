<?php
include('getId.php');
include('language.php');
include('initdb.php');
require_once('utils-challenges.php');
if (isset($_GET['cl']))
    $clRace = getClRace($_GET['cl']);
include('challenge-cldata.php');
if (!empty($clRace)) {
    $getRewards = mysql_query('SELECT r.id,r.charid,c.name,c.sprites FROM mkclrewards r INNER JOIN mkchars c ON r.charid=c.id WHERE clist='. $clRace['id']);
    $rewards = array();
    while ($reward = mysql_fetch_array($getRewards))
        $rewards[] = $reward;
}
if (isset($_GET['clmsg'])) {
    switch ($_GET['clmsg']) {
    case 'reward_created':
        $clMsg = $language ? 'The reward has been created':'La récompense a été créée';
        break;
    }
    unset($_GET['clmsg']);
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/challenges.css?reload=1" />
<?php
include('o_online.php');
?>
<title><?php echo $language ? 'Challenge rewards':'Défis et récompenses'; ?> - Mario Kart PC</title>
</head>
<body>
<h1 class="challenge-main-title"><?php echo $language ? 'Challenge rewards' : 'Défis et récompenses'; ?></h1>
<?php
if (isset($clMsg))
    echo '<div class="challenge-msg-success">'. $clMsg .'</div><br />';
if (empty($rewards)) {
    ?>
    <div class="challenge-explain">
    <?php
    if ($language) {
        ?>
        This section allows you to give an extra reward to your challenges in addition to the challenge points:
        <strong>character unlocking</strong>!<br />
        The principle is simple: you select one or more challenges to achieve,
        and a character to unlock from those you have created in the <a class="pretty-link" href="persoEditor.php">character editor</a>.<br />
        Your character then becomes a <em>secret character</em> that only members who have successfully completed the challenges will have access to!
        <?php
    }
    else {
        ?>
        Cette section vous permet de donner une récompense à vos défis en plus des points défis :
        le <strong>déblocage de persos</strong> !<br />
        Le principe est simple : vous sélectionnez un ou plusieurs défis à réaliser, et un perso à débloquer
        parmi ceux que vous avez créé dans l'<a class="pretty-link" href="persoEditor.php">éditeur de persos</a>.<br />
        Votre perso devient ainsi un <em>perso secret</em> dont seuls les membres ayant réussi les défis auront accès !
        <?php
    }
    ?>
    </div>
    <?php
}
else {
    ?>
    <table class="challenges-table challenges-table-reward">
        <tr>
            <th><?php echo $language ? 'Character':'Perso'; ?></th>
            <th><?php echo $language ? 'Challenges to complete':'Défis à réaliser'; ?></th>
            <th><?php echo $language ? 'Action':'Action'; ?></th>
        </tr>
    <?php
    include('persos.php');
    foreach ($rewards as $reward) {
        $spriteSrcs = get_sprite_srcs($reward['sprites']);
        ?>
        <tr>
            <td class="challenges-td-center" style="padding-top:5px">
            <img src="<?php echo $spriteSrcs['ld']; ?>" alt="<?php echo $reward['name']; ?>" />
            <br /><?php
            echo $reward['name'];
            ?></td>
            <td>
            <div class="challenge-description challenge-description-main">
            <ul>
            <?php
            $challenges = mysql_query('SELECT c.* FROM mkchallenges c INNER JOIN mkclrewardchs r ON c.id=r.challenge WHERE r.reward='. $reward['id']);
            while ($challenge = mysql_fetch_array($challenges)) {
                $challengeDetails = getChallengeDetails($challenge);
                echo '<li>';
                if ($challengeDetails['name'])
                    echo htmlspecialchars($challengeDetails['name']);
                else
                    echo $challengeDetails['description']['main'];
                echo '</li>';
            }
            ?>
            </ul>
            </div>
            <?php
            /*$challengeDesc = $challenge['description'];
            if ($challenge['name'])
                echo '<h3>'.htmlspecialchars($challenge['name']).'</h3>';
            echo '<div class="challenge-description challenge-description-main">'. $challengeDesc['main'] .'</div>';
            if (isset($challengeDesc['extra']))
                echo '<div class="challenge-description challenge-description-extra">'. $challengeDesc['extra'] .'</div>';*/
            ?></td>
            <td class="challenges-td-center"><a class="challenge-action-edit" href="<?php echo nextPageUrl('challengeReward.php', array('cl' => null, 'rw' => $reward['id'])); ?>"><?php echo $language ? 'Edit':'Modifier'; ?></a><br />
            <a class="challenge-action-del" href="<?php echo nextPageUrl('challengeRewardDel.php', array('cl' => null, 'rw' => $reward['id'])); ?>" onclick="return confirm('<?php echo $language ? 'Delete this reward?':'Supprimer cette récompense ?'; ?>')"><?php echo $language ? 'Delete':'Supprimer'; ?></a></td>
        </tr>
        <?php
    }
    ?>
    </table>
    <?php
}
?>
<div class="main-challenge-actions">
    <a class="main-challenge-action" href="<?php echo nextPageUrl('challengeReward.php'); ?>">+ &nbsp;<?php echo $language ? 'Create a reward':'Créer une récompense'; ?></a>
</div>
<div class="challenge-navigation">
    <a href="<?php echo nextPageUrl('challenges.php', array('ch'=>null,'cl'=>empty($clRace)?null:$clRace['clid'])); ?>">&lt; <u><?php echo $language ? 'Back to challenges list':'Retour à la liste des défis'; ?></u></a>
</div>
</body>
</html>
<?php
mysql_close();
?>