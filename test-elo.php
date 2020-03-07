<?php
include('initdb.php');
$scores = array(17000,45000);
$nbScores = count($scores);
$total = 0;
foreach ($scores as $score)
	$total += $score;
$i = 0;
foreach ($scores as $score) {
	$coeff = (($nbScores-$i-1)/($nbScores-1))-($score/$total);
	$coeff *= pow(2,$coeff);
	$inc = round($coeff*(($coeff<0)?$score:max(20000-$score,5000))/80);
	echo($score+$inc).' ';
	$i++;
}
mysql_close();
?>