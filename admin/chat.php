<?php
include('auth.php');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<base href=".." />
	<style type="text/css">
	img {
		width: auto !important;
		height: auto !important;
	}
	</style>
<title>Admin MKPC</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php
if (isset($_GET['all']))
	$msgs = mysql_query("SELECT * FROM `minichat`");
else {
	$nbMessages = mysql_fetch_array(mysql_query("SELECT COUNT(*) AS nb FROM `minichat`"));
	$nbKept = 200;
	$first = max($nbMessages['nb']-$nbKept,0);
	$msgs = mysql_query("SELECT * FROM `minichat` LIMIT $first,$nbKept");
}
while ($getMsg = mysql_fetch_array($msgs)) {
	$html = '<p><strong>';
	$html .= $getMsg['pseudo'];
	$html .= '</strong> : ';
	$html .= nl2br($getMsg['message']);
	$html .= '</p>';
	$toWrite = $html.$toWrite;
}
echo $toWrite;
mysql_close();
?>
</body>
</html>