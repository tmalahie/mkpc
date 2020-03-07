<?php
include('session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('language.php');
include('initdb.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	mysql_close();
	exit;
}
require_once('getRights.php');
if (!hasRight('moderator')) {
	echo "Vous n'&ecirc;tes pas mod&eacute;rateur";
	mysql_close();
	exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<style type="text/css">
#ban_msg {
	display: none;
}
#titres td:nth-child(2) {
	width: 300px;
}
</style>
<?php
include('o_online.php');
?>
</head>
<body>
<?php
include('header.php');
$page = 'forum';
include('menu.php');
function toIp($joueur) {
	return floor($joueur['ip1']/256/256/256).'.'.(floor($joueur['ip1']/256/256)%256).'.'.(floor($joueur['ip1']/256)%256).'.'.($joueur['ip1']%256);
}
if (isset($_GET['ip1'])) {
	$ip1 = $_GET['ip1'];
	for ($i=2;$i<=4;$i++)
		${'ip'.$i} = isset($_GET['ip'.$i]) ? $_GET['ip'.$i]:0;
	mysql_query('DELETE FROM `ip_bans` WHERE ip1="'.$ip1.'" AND ip2="'.$ip2.'" AND ip3="'.$ip3.'" AND ip4="'.$ip4.'"');
	$unban = toIp($_GET);
}
?>
<main>
	<?php
	if ($ban)
		echo '<p><strong>'. $ban .'</strong> a été banni</p>';
	elseif ($unban)
		echo '<p>L\'adresse <strong>'. $unban .'</strong> a été débannie</p>';
	?>
	<br />
	<div class="ranking-modes">
		<a href="ban-player.php"><?php echo $language ? 'Banned members':'Membres bannis'; ?></a>
		<span><?php echo $language ? 'Banned IPs':'IP bannies'; ?></span>
	</div>
	<h2><?php echo $language ? 'Banned IPs':'Liste des IP bannies'; ?></h2>
	<p>
		<?php
		if ($language)
			echo "To ban an IP, ban the associated player and check &quot;ban IP address&quot;";
		else
			echo "Pour bannir une IP, bannir le joueur associé et cocher &quot;bannir l'adresse IP&quot;";
		?>
	</p>
	<table>
	<tr id="titres">
	<td><?php echo $language ? 'IP address':'Adresse IP'; ?></td>
	<td><?php echo $language ? 'Members':'Membres'; ?></td>
	<td><?php echo $language ? 'Unban':'Débannir'; ?></td>
	</tr>
	<?php
	function controlLength($str,$maxLength) {
		$pts = '...';
		if (strlen($str) > $maxLength)
			return substr($str,0,$maxLength-strlen($pts)).$pts;
		return $str;
	}
	$bannished = mysql_query('SELECT i.*,GROUP_CONCAT(j.nom SEPARATOR ", ") AS members FROM `ip_bans` i LEFT JOIN `mkjoueurs` j ON id=player GROUP BY i.ip1,i.ip2,i.ip3,i.ip4');
	while ($joueur = mysql_fetch_array($bannished)) {
		?>
		<tr>
		<td><?php echo toIp($joueur); ?></td>
		<td><?php echo $joueur['members'] ?></td>
		<td><a href="?<?php
		for ($i=1;$i<=4;$i++) {
			if ($i>1) {
				if (!$joueur['ip'.$i])
					continue;
				echo '&amp;';
			}
			echo 'ip'.$i.'='.$joueur['ip'.$i];
		}
		?>" class="action_button"><?php echo $language ? 'Unban':'Débannir'; ?></a></td>
		</tr>
		<?php
	}
	?>
	</table>
	<p><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('footer.php');
mysql_close();
?>
</body>
</html>