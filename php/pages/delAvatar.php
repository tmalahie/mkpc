<?php
include('../includes/initdb.php');
include('../includes/session.php');
if (isset($_GET['id'])) {
	$aId = $id;
	require_once('../includes/getRights.php');
	require_once('../includes/utils-logs.php');
	if (!hasRight('moderator')) {
		mysql_close();
		exit;
	}
	$id = $_GET['id'];
}
if ($id) {
	include('../includes/avatars.php');
	$oldAvatar = get_avatar_img($id);
	if ($oldAvatar) {
		mysql_query('UPDATE `mkprofiles` SET avatar="" WHERE id="'. $id .'"');
		@unlink(AVATAR_REL_DIR.$oldAvatar['ld']);
		@unlink(AVATAR_REL_DIR.$oldAvatar['hd']);
		clear_avatar_cache($id);
		if (isset($_GET['id']))
			insertLog($aId, 'SPicture '. $id, array(
				'type' => 'avatar',
				'member' => snapshotMember($id),
				'avatar' => $oldAvatar
			));
	}
	mysql_close();
	header('location: profil.php?id='.$id);
}
else
	mysql_close();
?>