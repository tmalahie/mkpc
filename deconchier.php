<?php
include('initdb.php');
$rep = 'images/uploads';
$dir = opendir($rep);
$inc = 0;
$pds = 0;
while ($fichier=@readdir($dir)) {
    if (($fichier != ".") && ($fichier != "..") && ($fichier != "index.php")) {
		if (is_file($rep.'/'.$fichier)) {
			if (preg_match('#overload#',$fichier))
				continue;
			if ($circuit = preg_match('#map#',$fichier))
				$id = preg_replace('#^map([0-9]+).+$#','$1',$fichier);
			else
				$id = preg_replace('#^course([0-9]+).+$#','$1',$fichier);
			if (($circuit && !mysql_numrows(mysql_query('SELECT * FROM `circuits` WHERE id="'. $id .'"'))) || (!$circuit && !mysql_numrows(mysql_query('SELECT * FROM `arenes` WHERE id="'. $id .'"')))) {
				$pds += filesize($rep.'/'.$fichier);
				$inc++;
				unlink($rep.'/'.$fichier);
			}
		}
	}
}
closedir($dir);
mysql_close();
echo $inc .' fichiers supprimés, '. $pds .' octets économisés.';
?>