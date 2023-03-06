<?php
header('Content-Type: text/plain');
if (isset($_POST["id"]) && isset($_POST["name"])) {
    include('initdb.php');
    if ($record = mysql_fetch_array(mysql_query('SELECT id,identifiant,identifiant2,identifiant3,identifiant4,class,circuit,type FROM mkrecords WHERE id="'. $_POST['id'] .'"'))) {
        include('getId.php');
        if (($record['identifiant'] != $identifiants[0]) || ($record['identifiant2'] != $identifiants[1]) || ($record['identifiant3'] != $identifiants[2]) || ($record['identifiant4'] != $identifiants[3])) {
            echo -2;
            mysql_close();
            exit;
        }
        include('session.php');
		$name = ucwords($_POST["name"]);
        $existingNick = mysql_query('SELECT j.id FROM `mkjoueurs` j INNER JOIN `mkprofiles` p ON j.id=p.id INNER JOIN `mkrecords` r ON r.name=j.nom AND p.identifiant=r.identifiant AND p.identifiant2=r.identifiant2 AND p.identifiant3=r.identifiant3 AND p.identifiant4=r.identifiant4 WHERE j.nom="'.$name.'" AND j.id!="'.$id.'" AND (p.identifiant!='.$identifiants[0].' OR p.identifiant2!='.$identifiants[1].' OR p.identifiant3!='.$identifiants[2].' OR p.identifiant4!='.$identifiants[3].') LIMIT 1');
		if (mysql_fetch_array($existingNick)) {
			echo -1;
            mysql_close();
            exit;
        }
        mysql_query('UPDATE mkrecords SET name="'. $name .'" WHERE id="'. $_POST["id"] .'"');
    }
    echo 1;
	mysql_close();
}
?>