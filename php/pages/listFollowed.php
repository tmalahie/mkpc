<?php
include('../includes/session.php');
if ($id) {
	include('../includes/language.php');
	include('../includes/initdb.php');
	if ($getInfos = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $id .'"'))) {
		?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Following users':'Vos abonnements'; ?> - Mario Kart PC</title>
<?php
include('../includes/heads.php');
include('../includes/avatars.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/profil.css" />
<link rel="stylesheet" type="text/css" href="styles/followers.css?reload=1" />

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
	<?php
	function controlLength($str,$maxLength) {
		$pts = '...';
		if (mb_strlen($str) > $maxLength)
			return mb_substr($str,0,$maxLength-mb_strlen($pts)).$pts;
		return $str;
	}
	$dateFormat = $language ? '%Y-%m-%d':'le %d/%m/%y';
	$today = time();
	$getUsers = mysql_query('SELECT followed,DATE_FORMAT(date, "'. $dateFormat .'") AS infosDate FROM `mkfollowusers` WHERE follower="'. $id .'" ORDER BY date DESC');
	$users = array();
	while ($user = mysql_fetch_array($getUsers))
		$users[] = $user;
	$nbUsers = count($users);
	$page = isset($_GET['page']) ? max(intval($_GET['page']),1):1;
	$usersPerPage = 30;
	$users = array_slice($users,($page-1)*$usersPerPage,$usersPerPage);
	$nbPages = ceil($nbUsers/$usersPerPage);
	?>
	<h1><?php echo $language ? 'Following users':'Vos abonnements'; ?> (<?php echo $nbUsers; ?>)</h1>
	<div class="following-users">
	<?php
	foreach ($users as $user) {
		$pseudo = get_pseudo_text($user['followed']);
		$displayName = ($pseudo === null) ? ($language ? 'this deleted account':'ce compte supprimé'):$pseudo;
		$confirmMsg = $language ? 'Unfollow '. $displayName .'?':'Ne plus suivre '. $displayName .' ?';
		?>
		<div class="follower-item-ctn">
			<a class="follower-item" href="profil.php?id=<?php echo $user['followed']; ?>" title="<?php echo htmlspecialchars($displayName); ?>">
				<?php
				echo '<div class="avatar-ctn">';
				print_avatar($user['followed'],50);
				echo '</div>';
				echo '<div class="nick-ctn">';
				echo $pseudo;
				echo '<br />';
				echo '<span class="nick-info">'. ($language ? 'Followed since '.$user['infosDate']:'Suivi depuis '.$user['infosDate']) .'</span>';
				echo '</div>';
				?>
			</a>
			<a class="unfollow-user" href="follow-user.php?user=<?php echo urlencode($user['followed']); ?>&amp;src=follows&amp;page=<?php echo $page; ?>" title="<?php echo $language ? 'Unfollow':'Ne plus suivre'; ?>" onclick="return confirm('<?php echo htmlspecialchars(addslashes($confirmMsg), ENT_QUOTES); ?>')">&times;</a>
		</div>
		<?php
	}
	?>
	</div>
	<?php
	if ($nbPages > 1) {
		?>
		<div class="topicPages"><p>
			<?php echo $language ? 'Page: &nbsp;':'Page : ';
			$get = $_GET;
			for ($i=1;$i<=$nbPages;$i++) {
				$get['page'] = $i;
				if ($i == $page)
					echo $i;
				else
					echo '<a href="?user='. $id .'&amp;'. http_build_query($get) .'">'. $i .'</a>';
				echo ' &nbsp; ';
			}
			?>
		</p></div>
		<?php
	}
	?>
	<div class="comments-list">
		<a href="profil.php?id=<?php echo $id; ?>"><?php echo $language ? 'Back to the profile':'Retour au profil'; ?></a><br />
		<a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a>
	</div>
</main>
		<?php
		include('../includes/footer.php');
	}
	mysql_close();
}
?>
</body>
</html>