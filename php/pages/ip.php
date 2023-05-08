<?php
include('../includes/getId.php');
$identifiant = $identifiants[0];
//$identifiant = 1552514642;
echo (floor($identifiant/256/256/256)).'.'.(floor($identifiant/256/256)%256).'.'.(floor($identifiant/256)%256).'.'.($identifiant%256);
echo '<br />';
echo $_SERVER['REMOTE_ADDR'];
?>