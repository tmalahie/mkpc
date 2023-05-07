<?php
include('initdb.php');
include('session.php');
if (isset($_GET['id'])) {
	$aId = $id;
	require_once('getRights.php');
	if (!hasRight('moderator')) {
		mysql_close();
		exit;
	}
	$id = $_GET['id'];
}
if ($id) {
	include('avatars.php');
	$oldAvatar = get_avatar_img($id);
	if ($oldAvatar) {
		mysql_query('UPDATE `mkprofiles` SET avatar="" WHERE id="'. $id .'"');
		@unlink(AVATAR_REL_DIR.$oldAvatar['ld']);
		@unlink(AVATAR_REL_DIR.$oldAvatar['hd']);
		clear_avatar_cache($id);
		if (isset($_GET['id']))
			mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $aId .', "SPicture '. $id .'")');
	}
	mysql_close();
	header('location: profil.php?id='.$id);
}
else
	mysql_close();
?>