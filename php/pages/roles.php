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
require_once('../includes/tokens.php');
if (!hasRight('admin')) {
	echo "Vous n'&ecirc;tes pas administrateur";
	mysql_close();
	exit;
}
assign_token();
$roleNames = getRoleNames();
$success = null;
$error = null;
if (isset($_POST['role']) && (!isset($_POST['token']) || ($_POST['token'] != $_SESSION['csrf'])))
	$error = $language ? 'Invalid token, please try again':'Token invalide, veuillez réessayer';
elseif (isset($_POST['role']) && isset($roleNames[$_POST['role']])) {
	$role = $_POST['role'];
	$roleName = $roleNames[$role];
	if (isset($_POST['grant'])) {
		$getMember = mysql_fetch_array(mysql_query('SELECT id,nom,deleted FROM `mkjoueurs` WHERE nom="'. $_POST['grant'] .'"'));
		if (!$getMember || $getMember['deleted'])
			$error = ($language ? 'No member named <strong>':'Aucun membre nommé <strong>') . htmlspecialchars(stripslashes($_POST['grant'])) .'</strong>.';
		elseif (mysql_fetch_array(mysql_query('SELECT player FROM `mkrights` WHERE player="'. $getMember['id'] .'" AND privilege="'. $role .'"')))
			$error = '<strong>'. htmlspecialchars($getMember['nom']) .'</strong> '. ($language ? 'already has the role':'a déjà le rôle') .' <strong>'. $roleName .'</strong>.';
		else {
			mysql_query('INSERT INTO `mkrights` SET player="'. $getMember['id'] .'",privilege="'. $role .'"');
			insertLog($id, 'Role '. $getMember['id'] .' '. $role, array(
				'type' => 'role',
				'member' => snapshotMember($getMember['id']),
				'role' => $role
			));
			$success = '<strong>'. htmlspecialchars($getMember['nom']) .'</strong> '. ($language ? 'now has the role':'a maintenant le rôle') .' <strong>'. $roleName .'</strong>.';
		}
	}
	elseif (isset($_POST['revoke'])) {
		$memberId = intval($_POST['revoke']);
		if (($memberId == $id) && ($role === 'admin'))
			$error = $language ? 'You can\'t remove your own administrator role.':'Vous ne pouvez pas retirer votre propre rôle d\'administrateur.';
		else {
			$q = mysql_query('DELETE FROM `mkrights` WHERE player="'. $memberId .'" AND privilege="'. $role .'"');
			if (mysql_affected_rows()) {
				$member = snapshotMember($memberId);
				insertLog($id, 'Unrole '. $memberId .' '. $role, array(
					'type' => 'role',
					'member' => $member,
					'role' => $role
				));
				$success = '<strong>'. htmlspecialchars(isset($member['name']) ? $member['name'] : '#'.$memberId) .'</strong> '. ($language ? 'no longer has the role':'n\'a plus le rôle') .' <strong>'. $roleName .'</strong>.';
			}
		}
	}
}
$membersByRole = array();
foreach ($roleNames as $role => $roleName)
	$membersByRole[$role] = array();
$getMembers = mysql_query('SELECT r.player,r.privilege,j.nom,NULLIF(DATE(p.last_connect),0) AS last_connect FROM `mkrights` r LEFT JOIN `mkjoueurs` j ON j.id=r.player LEFT JOIN `mkprofiles` p ON p.id=r.player ORDER BY j.nom');
while ($member = mysql_fetch_array($getMembers))
	$membersByRole[$member['privilege']][] = $member;
$selectedRole = isset($_GET['role']) && isset($roleNames[$_GET['role']]) ? $_GET['role'] : null;
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Staff roles':'Rôles du staff'; ?> - Mario Kart PC</title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css?reload=2" />
<link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />
<style type="text/css">
h1 + p {
	margin-top: 6px;
	margin-bottom: 12px;
}
main h2 {
	margin-bottom: 2px;
}
.role-desc {
	font-size: 0.9em;
	color: #666;
	margin: 0 0 6px 0;
}
.role-success {
	color: #0A0;
}
.role-error {
	color: #C00;
}
table a.profile {
	color: #820;
}
table a.profile:hover {
	color: #B50;
}
td.role-none {
	color: #888;
	font-style: italic;
}
td.role-options form {
	display: inline;
	margin: 0;
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
$roleDescs = array(
	'admin' => $language ? 'Full access, including this page. Also has all the moderator and event host rights.':'Accès complet, y compris à cette page. A aussi tous les droits de modérateur et d\'animateur.',
	'moderator' => $language ? 'Moderates the forum, comments, creations and the online mode. Can warn and ban members.':'Modère le forum, les commentaires, les créations et le mode en ligne. Peut avertir et bannir des membres.',
	'organizer' => $language ? 'Gives online points and awards after events.':'Donne des points en ligne et des récompenses après les événements.',
	'publisher' => $language ? 'Accepts or rejects the news submitted by members.':'Accepte ou refuse les news proposées par les membres.',
	'clvalidator' => $language ? 'Accepts or rejects the challenges submitted by members.':'Accepte ou refuse les défis proposés par les membres.'
);
?>
<main>
	<h1><?php echo $language ? 'Staff roles':'Rôles du staff'; ?></h1>
	<p><?php
	if ($language)
		echo 'This page lists the members having a role on the site, and lets you give a role to a member or take it back.';
	else
		echo 'Cette page liste les membres ayant un rôle sur le site, et permet de donner un rôle à un membre ou de le lui retirer.';
	?></p>
	<?php
	if ($success)
		echo '<p class="role-success">'. $success .'</p>';
	if ($error)
		echo '<p class="role-error">'. $error .'</p>';
	?>
	<form method="post" action="roles.php">
	<input type="hidden" name="token" value="<?php echo $_SESSION['csrf']; ?>" />
	<blockquote>
	<p>
		<label for="grant"><strong><?php echo $language ? 'Member':'Membre'; ?></strong></label><?php echo $language ? ':':' :'; ?>
		<input type="text" name="grant" id="grant" required="required" />
		&nbsp;<label for="grant-role"><strong><?php echo $language ? 'Role':'Rôle'; ?></strong></label><?php echo $language ? ':':' :'; ?>
		<select name="role" id="grant-role" required="required">
			<?php
			foreach ($roleNames as $role => $roleName)
				echo '<option value="'. $role .'"'. ($selectedRole === $role ? ' selected="selected"':'') .'>'. $roleName .'</option>';
			?>
		</select>
		<input type="submit" value="<?php echo $language ? 'Give role':'Donner le rôle'; ?>" class="action_button" />
	</p>
	</blockquote>
	</form>
	<?php
	foreach ($roleNames as $role => $roleName) {
		$members = $membersByRole[$role];
		?>
		<h2><?= $roleName ?> (<?= count($members) ?>)</h2>
		<p class="role-desc"><?= $roleDescs[$role] ?></p>
		<table>
		<tr id="titres">
			<td><?php echo $language ? 'Member':'Membre'; ?></td>
			<td><?php echo $language ? 'Last connection':'Dernière connexion'; ?></td>
			<td>Options</td>
		</tr>
		<?php
		if (empty($members))
			echo '<tr class="clair"><td class="role-none" colspan="3">'. ($language ? 'Nobody has this role':'Personne n\'a ce rôle') .'</td></tr>';
		foreach ($members as $i => $member) {
			?>
			<tr class="<?= $i%2 ? 'fonce':'clair' ?>">
				<?php
				if ($member['nom'] === null)
					echo '<td class="role-none">'. ($language ? 'Deleted account':'Compte supprimé') .' #'. $member['player'] .'</td>';
				else
					echo '<td><a class="profile" href="profil.php?id='. $member['player'] .'">'. htmlspecialchars($member['nom']) .'</a></td>';
				?>
				<td><?= $member['last_connect'] ? $member['last_connect'] : '-' ?></td>
				<td class="role-options">
					<?php
					if (($member['player'] == $id) && ($role === 'admin'))
						echo '<span class="role-none">'. ($language ? 'You':'Vous') .'</span>';
					else {
						?>
					<form method="post" action="roles.php" onsubmit="return confirm(this.dataset.confirm)" data-confirm="<?php
					$memberName = ($member['nom'] === null) ? '#'.$member['player'] : $member['nom'];
					echo htmlspecialchars($language ? 'Remove the '. $roleName .' role from '. $memberName .'?' : 'Retirer le rôle '. $roleName .' à '. $memberName .' ?');
					?>">
						<input type="hidden" name="token" value="<?php echo $_SESSION['csrf']; ?>" />
						<input type="hidden" name="role" value="<?= $role ?>" />
						<input type="hidden" name="revoke" value="<?= $member['player'] ?>" />
						<input type="submit" value="<?php echo $language ? 'Remove':'Retirer'; ?>" class="action_button" />
					</form>
						<?php
					}
					?>
				</td>
			</tr>
			<?php
		}
		?>
		</table>
		<?php
	}
	?>
	<p><a href="admin.php"><?php echo $language ? 'Back to the admin page':'Retour à la page admin'; ?></a><br />
	<a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('../includes/footer.php');
?>
<script type="text/javascript" src="scripts/auto-complete.min.js"></script>
<script type="text/javascript" src="scripts/autocomplete-player.js?reload=1"></script>
<script type="text/javascript">
autocompletePlayer('#grant', {
	onSelect: function(event, term, item) {
		preventSubmit(event);
	}
});
</script>
<?php
mysql_close();
?>
</body>
</html>
