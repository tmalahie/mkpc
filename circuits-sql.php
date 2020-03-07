<?php
$cCups = 'SELECT *,circuit0 AS id0,circuit1 AS id1,circuit2 AS id2,circuit3 AS id3 FROM mkcups WHERE mode=1';
$sCups = 'SELECT *,circuit0 AS id0,circuit1 AS id1,circuit2 AS id2,circuit3 AS id3 FROM mkcups WHERE mode=0';
$cCircuits = 'SELECT * FROM circuits WHERE nom IS NOT NULL';
$sCircuits = 'SELECT * FROM mkcircuits WHERE !type';
$cArenes = 'SELECT * FROM arenes WHERE nom IS NOT NULL';
$sArenes = 'SELECT * FROM mkcircuits WHERE type';
$aCircuits = array($cCups,$sCups,$cCircuits,$sCircuits,$cArenes,$sArenes);
?>