<?php
$getFollowers = mysql_query('SELECT follower FROM `mkfollowusers` f LEFT JOIN `mknotifs` n ON f.follower=n.user AND n.type="currently_online" AND n.link="'.$id.'" WHERE f.followed="'. $id .'" AND n.id IS NULL');
while ($follower = mysql_fetch_array($getFollowers))
	mysql_query('INSERT INTO `mknotifs` SET type="currently_online", user="'. $follower['follower'] .'", link="'.$id.'"');
?>