<?php
include('initdb.php');
include('getId.php');
if ($identifiants[0] != 1390635815)
	exit;
?>
<html>
<head>
<style type="text/css">
.star {width: 25px; cursor: pointer}
#toNote {max-height: 600px}
</style>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script type="text/javascript">
var circuits = [<?php
$circuits = mysql_query('SELECT id,note,nbnotes FROM `mkcircuits` WHERE nom!="NULL" AND (identifiant!='.$identifiants[0].' OR identifiant2!='.$identifiants[1].' OR identifiant3!='.$identifiants[2].' OR identifiant4!='.$identifiants[3].')');
$t = 0;
while ($circuit = mysql_fetch_array($circuits)) {
	if (!mysql_numrows(mysql_query('SELECT * FROM `mknotes` WHERE circuit='. $circuit['id'] .' AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]))) {
		if ($t)
			echo ',';
		$t++;
		echo '['.$circuit['id'].','.$circuit['note'].','.$circuit['nbnotes'].']';
	}
}
$comp = $t;
$circuits2 = mysql_query('SELECT id,note,nbnotes FROM `circuits` WHERE (nom!="NULL" OR exists(SELECT * FROM mkcups WHERE mode=1 AND (circuit0=circuits.id OR circuit1=circuits.id OR circuit2=circuits.id OR circuit3=circuits.id))) AND (identifiant!='.$identifiants[0].' OR identifiant2!='.$identifiants[1].' OR identifiant3!='.$identifiants[2].' OR identifiant4!='.$identifiants[3].')');
while ($circuit = mysql_fetch_array($circuits2)) {
	if (!mysql_numrows(mysql_query('SELECT * FROM `notes` WHERE circuit='. $circuit['id'] .' AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]))) {
		if ($t)
			echo ',';
		$t++;
		echo '['.$circuit['id'].','.$circuit['note'].','.$circuit['nbnotes'].']';
	}
}
$arn = $t;
$circuits3 = mysql_query('SELECT id,note,nbnotes FROM `arenes` WHERE nom!="NULL" AND (identifiant!='.$identifiants[0].' OR identifiant2!='.$identifiants[1].' OR identifiant3!='.$identifiants[2].' OR identifiant4!='.$identifiants[3].')');
while ($arene = mysql_fetch_array($circuits3)) {
	if (!mysql_numrows(mysql_query('SELECT * FROM `marks` WHERE circuit='. $arene['id'] .' AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]))) {
		if ($t)
			echo ',';
		$t++;
		echo '['.$arene['id'].','.$arene['note'].','.$arene['nbnotes'].']';
	}
}
?>];
var comp = <?php echo $comp; ?>, arn = <?php echo $arn; ?>;
var iNote = 0;
var cNote = -1;
function getType(i) {
	if (i >= arn)
		return 2;
	if (i >= comp)
		return 1;
	return 0;
}
function xhr(page, send, onload) {
	var xhr_object;
	if (window.XMLHttpRequest || window.ActiveXObject) {
		if (window.ActiveXObject) {
			try {
				xhr_object = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch(e) {
				xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
			}
		}
		else
			xhr_object = new XMLHttpRequest(); 
	}
	xhr_object.open("POST", page, true);
	xhr_object.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr_object.setRequestHeader("If-Modified-Since", "Wed, 15 Nov 1995 00:00:00 GMT");
	xhr_object.send(send);
	xhr_object.onreadystatechange = function() {
		if ((xhr_object.readyState == 4) && !onload(xhr_object.responseText))
			setTimeout(function(){xhr(page,send,onload)},1000);
	}
}
function previewMark(note) {
	for (i=0;i<=note;i++)
		document.getElementById("star"+ i).src = "images/star1.png";
	for (i=note+1;i<5;i++)
		document.getElementById("star"+ i).src = "images/star0.png";
}
function updateMark() {
	previewMark(cNote);
}
function sendMark(i) {
	cNote = i;
	updateMark();
	xhr("sendMark.php", "id="+ circuits[iNote][0] +"&note="+i+(getType(iNote) ? ("&complete="+ getType(iNote)):""), function(reponse) {
		if (reponse == 1) {
			cNote = -1;
			updateMark();
			iNote++;
			imgLoad();
			return true;
		}
		return true;
	});
}
function imgLoad() {
	document.getElementById("toNote").style.display = "none";
	document.getElementById("toNote").src = "";
	if (iNote < circuits.length) {
		var src, href;
		switch (getType(iNote)) {
		case 0 :
			src = "mappreview.php?id="+ circuits[iNote][0];
			href = "previewcreation.php?id="+ circuits[iNote][0];
			break;
		case 1 :
			src = "notepreview.php?id="+ circuits[iNote][0];
			href = "map.php?i="+ circuits[iNote][0];
			break;
		case 2 :
			src = "markpreview.php?id="+ circuits[iNote][0];
			href = "battle.php?i="+ circuits[iNote][0];
		}
		document.getElementById("toTest").href = href;
		document.getElementById("note").innerHTML = (circuits[iNote][1]+1);
		document.getElementById("nbnotes").innerHTML = circuits[iNote][2];
		setTimeout(function() {
			document.getElementById("toNote").src = src;
			document.getElementById("toNote").style.display = "";
		},1);
	}
	else if (confirm("Plus rien a noter, noter les coupes ?"))
		document.location.href = "autonote.php";
}
function prev() {
	iNote--;
	imgLoad();
}
function next() {
	iNote++;
	imgLoad();
}
</script>
</head>
<body onload="imgLoad()">
<p><a href="#null" id="toTest" target="_blank"><img src="mappreview.php" id="toNote" alt="A noter" /></a></p>
<p><input type="button" value="&lt;-" onclick="prev()" /><?php
$apreciations = Array('Très mauvais', 'Mauvais', 'Moyen', 'Bon', 'Excellent');
function addStar($i, $a) {
	echo '<img id="star'.$i.'" class="star" src="images/star'.$a.'.png" onclick="sendMark('.$i.')" onmouseover="previewMark('.$i.')" onmouseout="updateMark()" title="'.HTMLentities($apreciations[$i]).'" /> &nbsp;';
}
for ($i=0;$i<=$cNote;$i++)
	addStar($i, 1);
for ($i=$cNote+1;$i<5;$i++)
	addStar($i, 0);
?><input type="button" value="-&gt;" onclick="next()" /> &nbsp; <span id="note"></span>/5 sur <span id="nbnotes"></span> note(s)</p>
</body>
</html>
<?php
mysql_close();