<?php
$getPublicLinks = mysql_query('SELECT id,rules FROM `mkgameoptions` WHERE public');
$publicLinkIds = array(0);
$publicLinksData = array(
	array(
		'team' => 0,
		'friendly' => 0
	)
);
while ($getPublicLink = mysql_fetch_array($getPublicLinks)) {
	$publicLinksData[] = json_decode($getPublicLink['rules']);
	$publicLinkIds[] = $getPublicLink['id'];
}
$publicLinksString = implode(',',$publicLinkIds);
?>