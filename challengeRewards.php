<?php
include('getId.php');
include('language.php');
include('initdb.php');
require_once('utils-challenges.php');
if (isset($_GET['cl']))
    $clRace = getClRace($_GET['cl']);
include('challenge-cldata.php');
if (!empty($clRace))
    $awards = mysql_query('SELECT * FROM awards WHERE clist='. $clRace['id']);
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
if (empty($awards)) {
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
    <div class="main-challenge-actions">
        <a class="main-challenge-action" href="<?php echo nextPageUrl('challengeReward.php'); ?>">+ &nbsp;<?php echo $language ? 'Create an award':'Créer une récompense'; ?></a>
    </div>
	<div class="challenge-navigation">
		<a href="<?php echo nextPageUrl('challenges.php', array('ch'=>null,'cl'=>empty($clRace)?null:$clRace['clid'])); ?>">&lt; <u><?php echo $language ? 'Back to challenges list':'Retour à la liste des défis'; ?></u></a>
	</div>
    <?php
}
?>
</body>
</html>
<?php
mysql_close();
?>