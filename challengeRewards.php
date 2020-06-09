<?php
include('getId.php');
include('language.php');
include('initdb.php');
require_once('utils-challenges.php');
if (isset($_GET['cl']))
    $clRace = getClRace($_GET['cl']);
include('challenge-cldata.php');
if (!empty($clRace)) {
    $getRewards = mysql_query('SELECT r.id,r.charid,c.name FROM mkclrewards r INNER JOIN mkchars c ON r.charid=c.id WHERE clist='. $clRace['id']);
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
<h1 class="challenge-main-title"><?php echo $language ? 'Challenge Awards' : 'Défis et récompenses'; ?></h1>
<?php
if (isset($clMsg))
    echo '<div class="challenge-msg-success">'. $clMsg .'</div><br />';
if (empty($rewards)) {
    ?>
    <div class="challenge-explain">
    <?php
    if ($language) {
        ?>
        Welcome to the challenge editor! A challenge is an action to perform in the circuit, than can be tried out by other players.<br />
        Example of challenge: &quot;Complete the track in less than 30s&quot;, &quot;Finish 1<sup>st</sup> with 100 participants&quot;, &quot;Complete the track without falling&quot;...
        The editor offers you a variety of combinations, which leaves a lot of freedom for the creation.<br />
        It's up to you to find the right combo that makes the challenge fun. Happy creating!
        <?php
    }
    else {
        ?>
        Sur cette page, vous pouvez donner une récompense de type &quot;débloquage de perso&quot; lorsqu'un on ou plusieurs de vos défis est réussi.
        TODO : remplir cette page, je suis pas inspiré là...
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
    foreach ($rewards as $reward) {
        ?>
        <tr>
            <td class="challenges-td-center"><?php
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
    <a class="main-challenge-action" href="<?php echo nextPageUrl('challengeReward.php'); ?>">+ &nbsp;<?php echo $language ? 'Create an award':'Créer une récompense'; ?></a>
</div>
<div class="challenge-navigation">
    <a href="<?php echo nextPageUrl('challenges.php', array('ch'=>null,'cl'=>empty($clRace)?null:$clRace['clid'])); ?>">&lt; <u><?php echo $language ? 'Back to challenges list':'Retour à la liste des défis'; ?></u></a>
</div>
</body>
</html>
<?php
mysql_close();
?>