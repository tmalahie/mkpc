<?php
include('../includes/session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('../includes/language.php');
include('../includes/initdb.php');
require_once('../includes/getRights.php');
require_once('../includes/utils-logs.php');
if (!hasRight('moderator')) {
	echo "Vous n'&ecirc;tes pas mod&eacute;rateur";
	mysql_close();
	exit;
}
$member = isset($_GET['member']) ? $_GET['member']:'';
$memberId = null;
if ($member !== '') {
	if ($getMember = mysql_fetch_array(mysql_query('SELECT id FROM `mkjoueurs` WHERE nom="'. $member .'"')))
		$memberId = $getMember['id'];
}
if ($memberId && isset($_GET['del'])) {
	$sanctionSnapshot = snapshotQuery('SELECT id,player,moderator,type AS sanction_type,reason,sanction_date,end_date FROM `mksanctions` WHERE id="'. $_GET['del'] .'" AND player="'. $memberId .'"');
	$q = mysql_query('DELETE FROM `mksanctions` WHERE id="'. $_GET['del'] .'" AND player="'. $memberId .'"');
	if (mysql_affected_rows())
		insertLog($id, 'SSanction '. $_GET['del'], array_merge(
			array('type' => 'sanction', 'member' => snapshotMember($memberId)),
			$sanctionSnapshot ? $sanctionSnapshot : array()
		));
}
$sanctionNames = array(
	'warn' => $language ? 'Warning':'Avertissement',
	'tempban' => $language ? 'Temporary ban':'Ban temporaire',
	'ban' => $language ? 'Permanent ban':'Ban définitif'
);
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Infraction log':'Historique des sanctions'; ?> - Mario Kart PC</title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css?reload=2" />
<link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />
<style type="text/css">
h1 {
    margin-bottom: 0;
}
h1 + p {
    margin-top: 6px;
    margin-bottom: 12px;
}
main tr.clair a.action_button, main tr.fonce a.action_button {
    color: white;
}
table a.profile {
	color: #820;
}
table a.profile:hover {
	color: #B50;
}
td.sanction-reason {
	max-width: 350px;
}
td.sanction-none {
	color: #888;
	font-style: italic;
}
</style>
<?php
include('../includes/o_online.php');
?>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'forum';
include('../includes/menu.php');
?>
<main>
	<h1><?php echo $language ? 'Infraction log':'Historique des sanctions'; ?></h1>
	<p><?php
	if ($language)
		echo "This page lists every warning and ban a member has received, including those that have since been lifted or expired. Removing an entry only erases it from this history, it does not undo the sanction itself.";
	else
		echo "Cette page liste tous les avertissements et bans reçus par un membre, y compris ceux qui ont depuis été retirés ou ont expiré. Supprimer une ligne l'efface uniquement de cet historique, cela n'annule pas la sanction elle-même.";
	?></p>
	<div class="ranking-modes">
		<a href="warn-player.php"><?php echo $language ? 'Warned members':'Membres avertis'; ?></a>
		<a href="ban-player.php"><?php echo $language ? 'Banned members':'Membres bannis'; ?></a>
		<a href="ban-ip.php"><?php echo $language ? 'Banned IPs':'IP bannies'; ?></a>
		<span><?php echo $language ? 'Infraction log':'Historique des sanctions'; ?></span>
	</div>
	<form method="get" action="sanction-logs.php">
	<blockquote>
	<p>
		<label for="member"><strong><?php echo $language ? 'Member:':'Membre :'; ?></strong></label> &nbsp;<input type="text" name="member" id="member" value="<?php echo htmlspecialchars($member); ?>" required="required" />
		<input type="submit" value="<?php echo $language ? 'Search':'Rechercher'; ?>" class="action_button" />
	</p>
	</blockquote>
	</form>
	<?php
	if ($member !== '') {
		if (!$memberId)
			echo '<p>'. ($language ? 'No member named <strong>':'Aucun membre nommé <strong>') . htmlspecialchars($member) .'</strong>.</p>';
		else {
			$dateFormat = $language ? '%Y-%m-%d %H:%i':'%d/%m/%Y à %Hh%i';
			$getSanctions = mysql_query('SELECT s.id,s.type,s.reason,s.end_date,DATE_FORMAT(s.sanction_date, "'. $dateFormat .'") AS infosDate,j.id AS modId,j.nom AS modName FROM `mksanctions` s LEFT JOIN `mkjoueurs` j ON j.id=s.moderator WHERE s.player="'. $memberId .'" ORDER BY s.sanction_date DESC,s.id DESC');
			if (!mysql_numrows($getSanctions))
				echo '<p>'. ($language ? '<strong>'. htmlspecialchars($member) .'</strong> has never been sanctioned.':'<strong>'. htmlspecialchars($member) .'</strong> n\'a jamais été sanctionné.') .'</p>';
			else {
				?>
				<h2><?php echo $language ? 'Sanctions of ':'Sanctions de '; ?><a class="profile" href="profil.php?id=<?php echo $memberId; ?>"><?php echo htmlspecialchars($member); ?></a></h2>
				<table>
				<tr id="titres">
					<td><?php echo $language ? 'Date':'Date'; ?></td>
					<td><?php echo $language ? 'Sanction':'Sanction'; ?></td>
					<td><?php echo $language ? 'Reason':'Raison'; ?></td>
					<td><?php echo $language ? 'Moderator':'Modérateur'; ?></td>
					<td>Options</td>
				</tr>
				<?php
				$i = 0;
				while ($sanction = mysql_fetch_array($getSanctions)) {
					$sanctionName = $sanctionNames[$sanction['type']];
					if (($sanction['type'] === 'tempban') && $sanction['end_date'])
						$sanctionName .= ' ('. ($language ? 'until ':'jusqu\'au ') . $sanction['end_date'] .')';
					?>
					<tr class="<?php echo $i%2 ? 'fonce':'clair'; ?>">
						<td><?php echo $sanction['infosDate']; ?></td>
						<td><?php echo $sanctionName; ?></td>
						<?php
						if ($sanction['reason'] === null || $sanction['reason'] === '')
							echo '<td class="sanction-reason sanction-none">'. ($language ? 'Not recorded':'Non enregistrée') .'</td>';
						else
							echo '<td class="sanction-reason">'. nl2br(htmlspecialchars($sanction['reason'])) .'</td>';
						if ($sanction['modName'] === null)
							echo '<td class="sanction-none">'. ($language ? 'Automatic':'Automatique') .'</td>';
						else
							echo '<td><a class="profile" href="profil.php?id='. $sanction['modId'] .'">'. htmlspecialchars($sanction['modName']) .'</a></td>';
						?>
						<td><a class="action_button" href="?member=<?php echo urlencode($member); ?>&amp;del=<?php echo $sanction['id']; ?>" onclick="return confirmDelete()"><?php echo $language ? 'Delete':'Supprimer'; ?></a></td>
					</tr>
					<?php
					$i++;
				}
				?>
				</table>
				<?php
			}
		}
	}
	?>
	<p><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('../includes/footer.php');
?>
<script type="text/javascript" src="scripts/jquery.min.js"></script>
<script type="text/javascript" src="scripts/auto-complete.min.js"></script>
<script type="text/javascript" src="scripts/autocomplete-player.js?reload=1"></script>
<script type="text/javascript">
autocompletePlayer('#member');
function confirmDelete() {
    return confirm("<?php echo $language ? 'Remove this sanction from the member\'s history?':'Retirer cette sanction de l\'historique du membre ?'; ?>");
}
</script>
<?php
mysql_close();
?>
</body>
</html>
