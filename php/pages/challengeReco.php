<?php
include('../includes/language.php');
?>
<html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/challenges.css" />
<style type="text/css">
body {
	text-align: center;
}
.challenge-explain h1 {
	margin: 6px 2px;
}
.challenge-explain p {
	margin: 4px 2px;
}
.challenge-explain ul {
	margin: 4px 0;
	padding-left: 20px;
}
</style>
</head>
<body>
	<div class="challenge-explain">
        <?php
        if ($language) {
            ?>
            <h1>Challenges: recommendations</h1>
            <p>
                All the challenges you create will be checked by the validation team before being published.<br />
                To facilitate their work, please check that your challenge follow some basic rules.<br />
                Here are some reasons a challenge can be rejected:
            </p>
            <ul>
                <li>Challenge pointless or with no difficulty (For example: &quot;Complete track&quot; without constraint, on an easy track)</li>
                <li>Spam (12 times the same challenge, or simillar challenges on a single track)</li>
                <li>Challenge name with insults or inappropriate words.</li>
            </ul>
            <p>
                To see a full list of rules your challenge should respect, you can read this <a class="pretty-link" target="_blank" href="topic.php?topic=7109">forum topic</a>.<br />
                Also, please check that the difficulty you assigned to your challenge is relevant. Click <a class="pretty-link" href="helpDifficulty.html">here</a> to see recommendations about difficulty.
            </p>
            <?php
        }
        else {
            ?>
            <h1>Défis : recommandations</h1>
            <p>
                Tous les défis que vous créez seront vérifiés par l'équipe de validation avant d'être publiés.<br />
                Pour faciliter leur travail, veuillez vérifier que votre défi respecte certaines règles de base.<br />
                Voici quelques raisons pour lesquelles un défi peut être rejeté :
            </p>
            <ul>
                <li>Défi sans intérêt ou avec aucune difficulté (&quot;Finir le circuit&quot; sans contraintes, sur un circuit facile)</li>
                <li>Spam (12 fois le même défi, ou des défis simillaires sur un même circuit)</li>
                <li>Nom de défi avec des insultes ou des mots obscènes</li>
            </ul>
            <p>
                Pour voir la liste complète des règles que votre défi doit respecter, vous pouvez lire <a class="pretty-link" target="_blank" href="topic.php?topic=7109">ce topic</a> sur le forum.<br />
                Vérifiez également que la difficulté qur vous avez attribuée à votre défi est pertinente. Cliquez <a class="pretty-link" href="aideDifficulty.html">ici</a> pour voir les recommandations sur la difficulté.
            </p>
            <?php
        }
        ?>
	</div>
</body>
</html>