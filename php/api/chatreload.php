<?php
header('Content-Type: text/plain');
include('../includes/session.php');
include('../includes/initdb.php');
$timeStamp = time();
echo '[';
echo '{';
$colon = '';
include('../includes/o_utils.php');
for ($i=0;isset($_POST['c'.$i])&&isset($_POST['m'.$i]);$i++) {
	mysql_query('UPDATE `mkchats` SET seen=1 WHERE id<="'. $_POST['m'.$i] .'" AND sender="'. $_POST['c'.$i] .'" AND receiver="'. $id .'"');
	if (isset($_POST['w'.$i]))
		mysql_query('UPDATE `mkconvs` SET writting='. ($_POST['w'.$i] ? 'CURRENT_TIMESTAMP()':'NULL') .' WHERE sender="'. $_POST['c'.$i] .'" AND receiver="'. $id .'"');
	$getMsgs = mysql_query('SELECT * FROM `mkchats` WHERE id>"'. $_POST['m'.$i] .'" AND ((sender="'. $id .'" AND receiver="'. $_POST['c'.$i] .'") OR (sender="'. $_POST['c'.$i] .'" AND receiver="'. $id .'")) ORDER BY id');
	$colon2 = '';
	echo $colon.'"c'.$_POST['c'.$i].'":[';
	while ($msg = mysql_fetch_array($getMsgs)) {
		echo $colon2;
		$colon2 = ',';
		echo '['.$msg['id'].','.$msg['sender'].',"'.parse_msg($msg['message']).'","'.to_local_tz($msg['date']).'"]';
	}
	echo ']';
	$colon = ',';
}
echo '},[';
$getWritting = mysql_query('SELECT receiver FROM `mkconvs` WHERE sender="'. $id .'" AND writting>DATE_SUB(NOW(),INTERVAL 11 SECOND)');
$colon = '';
while ($writer = mysql_fetch_array($getWritting)) {
	echo $colon;
	echo $writer['receiver'];
	$colon = ',';
}
echo ']';
echo ']';
mysql_close();
?>