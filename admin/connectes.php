<?php
include('auth.php');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
<title>Admin MKPC</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php
$getConnectes = mysql_query('SELECT id FROM `mkconnectes` WHERE id!="'. $id .'" AND connecte > '. (time()-30));
$colon = '';
while ($getId = mysql_fetch_array($getConnectes)) {
	$getPseudo = mysql_fetch_array(mysql_query('SELECT nom,online FROM `mkjoueurs` WHERE id='. $getId['id']));
	if ($getPseudo['online']) {
		echo $colon .'<a href="../profil.php?id='. $getId['id'] .'" style="color:'. ($getPseudo['online']==2?'#090':'#C70') .'">'. $getPseudo['nom'] .'</a>';
		$colon = ', ';
	}
}
mysql_close();
?>
</body>
</html>