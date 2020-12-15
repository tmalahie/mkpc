<?php
include('initdb.php');
$getNotes = mysql_query("SELECT type,circuit,rating FROM `mkratings` ORDER BY type,circuit");
function handleRating($type,$circuit,$ratings) {
    $nbNotes = 0;
    $nbByRating = array();
    $K = 5;
    for ($i=1;$i<=$K;$i++)
        $nbByRating[$i] = 0;
    foreach ($ratings as $rating) {
        $nbByRating[$rating]++;
        $nbNotes++;
    }
    $za_2 = 1.65;
    $sigma1 = 0;
    $sigma2 = 0;
    for ($i=1;$i<=$K;$i++) {
        $sigma1 += ($i*$i)*($nbByRating[$i]+1)/($nbNotes+$K);
        $sigma2 += $i*($nbByRating[$i]+1)/($nbNotes+$K);
    }
    $pScore = $sigma2 - $za_2*sqrt(($sigma1-$sigma2*$sigma2)/($nbNotes+$K+1));
    mysql_query("UPDATE `$type` SET pscore=$pScore WHERE id=$circuit");
}
$type = null;
$circuit = null;
$ratings = array();
while ($note = mysql_fetch_array($getNotes)) {
    if ($note['type'] !== $type || $note['circuit'] !== $circuit) {
        if (!empty($ratings)) {
            handleRating($type,$circuit,$ratings);
            $ratings = array();
        }
        $type = $note['type'];
        $circuit = $note['circuit'];
    }
    $ratings[] = $note['rating'];
}
if (!empty($ratings)) {
    handleRating($type,$circuit,$ratings);
    $ratings = array();
}
mysql_close();