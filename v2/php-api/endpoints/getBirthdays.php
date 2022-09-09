<?php
include('../includes/initdb.php');
include('../includes/language.php');
include('../includes/api.php');
$today = time();
$cYear = date('Y', $today);
$cMonth = date('m', $today);
$cDay = date('d', $today);
$curDate = $cYear . '-' . $cMonth . '-' . $cDay;
$getBirthdays = mysql_query('SELECT j.id,j.nom,p.identifiant,p.identifiant2,p.identifiant3,p.identifiant4,p.nbmessages FROM `mkprofiles` p INNER JOIN `mkjoueurs` j ON p.id=j.id WHERE birthdate IS NOT NULL AND DAY(birthdate)=' . $cDay . ' AND MONTH(birthdate)=' . $cMonth . ' AND j.banned=0 AND j.deleted=0 AND last_connect>=DATE_SUB("' . $curDate . '",INTERVAL 1 YEAR) AND TIMESTAMPDIFF(SECOND,last_connect,"' . $curDate . '")<=TIMESTAMPDIFF(SECOND,IFNULL(sub_date,"2016-01-01"),last_connect)+7*24*3600 ORDER BY p.nbmessages DESC, p.id ASC');
$dc = array();
$birthdaysList = array();
while ($getBirthday = mysql_fetch_array($getBirthdays)) {
  $dId = $getBirthday['identifiant'] . '_' . $getBirthday['identifiant2'] . '_' . $getBirthday['identifiant3'] . '_' . $getBirthday['identifiant4'];
  if (!isset($dc[$dId])) {
    $dc[$dId] = $getBirthday;
    $birthdaysList[] = array(
      'id' => +$getBirthday['id'],
      'name' => $getBirthday['nom']
    );
  }
}
renderResponse(array(
  'data' => $birthdaysList
));