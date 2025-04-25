<?php
include('../includes/getId.php');
include('../includes/initdb.php');
include('../includes/session.php');
require_once('../includes/getRights.php');
require_once('../includes/utils-date.php');
if (!hasRight('clvalidator')) {
    echo 'Access denied';
    exit;
}
$challengeId = isset($_GET['id']) ? intval($_GET['id']) : 0;
include('../includes/language.php');
require_once('../includes/utils-challenges.php');
$challenge = mysql_fetch_array(mysql_query('SELECT c.*,l.* FROM mkchallenges c INNER JOIN mkclrace l ON l.id=c.clist WHERE c.id="'. $challengeId .'"'));
if (!$challenge) {
	echo 'Unknown challenge';
	exit;
}
$challengeParams = array(
	'circuit' => true
);
$challengeDetails = getChallengeDetails($challenge, $challengeParams);
$getRejections = mysql_query('SELECT v.*,j.nom AS validator_name FROM mkclvalidations v LEFT JOIN mkjoueurs j ON v.validator=j.id WHERE v.challenge="'. $challengeId .'" AND v.accepted=0 ORDER BY v.id DESC');
$rejections = array();
while ($rejection = mysql_fetch_array($getRejections))
	$rejections[] = $rejection;
$countRejections = count($rejections);
?>
<html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/challenges.css?reload=1" />
<style type="text/css">
body {
	text-align: center;
}
h1 {
    margin: 5px auto;
}
table {
	margin-left: auto;
	margin-right: auto;
    background-color: rgb(216,216,230);
    background-color: rgba(230,230,240,0.9);
	border: outset 2px gray;
	border-radius: 5px;
}
table thead tr {
	background-color: #047;
	color: #EEF;
}
table td, table th {
	border: outset 1px gray;
	margin: 1px;
	padding: 5px;
	text-align: center;
}
table tbody tr td {
	padding: 5px;
}
.challenge-explain {
	max-width: 750px;
}
.challenge-metadata {
	margin: 10px auto;
}
</style>
</head>
<body>
	<div class="challenge-explain">
		<h1><?php
			echo $language ? 'Challenge rejections history' : 'Historique des refus du défi';
		?></h1>
		<div class="challenge-metadata">
            <?php
			$metadata = false;
			if ($challenge['name']) {
				$metadata = true;
				echo '<small><strong>'. ($language ? 'Challenge:':'Défi :') .'</strong> '. $challenge['name'] .'</small>';
			}
			if ($challengeDetails['circuit']['author']) {
				if ($metadata)
					echo ' &nbsp; ';
				$metadata = true;
				echo '<small><strong>'. ($language ? 'By:':'Par :') .'</strong> '. $challengeDetails['circuit']['author'] .'</small>';
			}
			if ($metadata)
				echo '<br />';
			echo $language ? 'This challenge has been rejected <strong>'. $countRejections .' time'. ($countRejections > 1 ? 's' : '') .'</strong>.' : 'Ce défi a été refusé <strong>'. $countRejections .' fois</strong>.';
            ?>
		</div>
		<table>
			<thead>
				<tr>
					<th><?php echo $language ? 'Date' : 'Date'; ?></th>
					<th><?php echo $language ? 'Rejected by' : 'Refusé par'; ?></th>
					<th><?php echo $language ? 'Reason' : 'Raison'; ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($rejections as $rejection) {
					$validation = json_decode($rejection['validation']);
					$reasonStr = isset($validation->msg) ? htmlspecialchars($validation->msg) : '<em>'. ($language ? 'No reason provided' : 'Aucune raison fournie') .'</em>';
					echo '<tr>';
					echo '<td>'. pretty_dates($rejection['date']) .'</td>';
					echo '<td><a class="pretty-link" href="profil.php?id='. $rejection['validator'] .'" target="_blank">'. $rejection['validator_name'] .'</a></td>';
					echo '<td>'. $reasonStr .'</td>';
					echo '</tr>';
				}
				?>
			</tbody>
		</table>
	</div>
</body>
</html>