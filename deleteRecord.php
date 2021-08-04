<?php
if (isset($_POST["id"])) {
    include('initdb.php');
    if ($record = mysql_fetch_array(mysql_query('SELECT id,identifiant,identifiant2,identifiant3,identifiant4,class,circuit,type FROM mkrecords WHERE id="'. $_POST['id'] .'"'))) {
        include('getId.php');
        if (($record['identifiant'] != $identifiants[0]) || ($record['identifiant2'] != $identifiants[1]) || ($record['identifiant3'] != $identifiants[2]) || ($record['identifiant4'] != $identifiants[3])) {
            echo -2;
            mysql_close();
            exit;
        }
        mysql_query('DELETE r,g,d FROM mkrecords r LEFT JOIN mkghosts g ON g.identifiant=r.identifiant AND g.identifiant2=r.identifiant2 AND g.identifiant3=r.identifiant3 AND g.identifiant4=r.identifiant4 AND g.perso=r.perso AND g.time=r.time AND r.class=g.class AND r.circuit=g.circuit AND r.type="" LEFT JOIN mkghostdata d ON d.ghost=g.id WHERE r.id='. $record['id']);
        mysql_query('UPDATE mkrecords r1 LEFT JOIN mkrecords r2 ON r1.player=r2.player AND r1.identifiant=r2.identifiant AND r1.identifiant2=r2.identifiant2 AND r1.identifiant3=r2.identifiant3 AND r1.identifiant4=r2.identifiant4 AND r1.circuit=r2.circuit AND r1.type=r2.type AND r1.class=r2.class AND r2.time<r1.time SET r1.best=(r2.id IS NULL) WHERE r1.class="'. $record['class'] .'" AND r1.circuit="'. $record['circuit'] .'" AND r1.type="'. $record['type'] .'"');
    }
    echo 1;
	mysql_close();
}
?>