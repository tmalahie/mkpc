<?php
include('../includes/session.php');
include('../includes/language.php');
include('../includes/initdb.php');
include('../includes/lounge/common.php');

if (!$id) {
	mysql_close();
	header('Location: forum.php');
	exit;
}

$multicup = lounge_get_season_multicup();
mysql_close();

if (!$multicup) {
	echo $language
		? 'The ranked lounge is not available at the moment.'
		: 'Le lounge class&eacute; n\'est pas disponible pour le moment.';
	exit;
}

header('Location: online.php?mid='. $multicup .'&ranked');
