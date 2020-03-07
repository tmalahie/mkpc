<?php
header('location: https://discordapp.com/channels/308979137480097793/308979137480097793');
exit;
include('session.php');
include('initdb.php');
if ($id) {
	if ($getBanned = mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"'))) {
		if ($getBanned['banned'])
			$id = null;
		require_once('getRights.php');
		$admin = hasRight('moderator');
	}
}
if (!$id) {
	echo 'Vous devez <a href="forum.php">vous connecter</a> pour acc&eacute;der &agrave; cette page.';
	mysql_close();
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" >
<head>
 <title>Mini-chat</title>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1">
<style type="text/css">
body {
	margin-top: 0;
	overflow: hidden;
	background-color: #DEF3FF;
}
.dit {
	color: gray;
}
#testText {
	position: absolute;
	left: 0;
	top: 0;
	visibility: hidden;
}
#seeHeightScreen {
	position: absolute;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
}
#messages {
	width: 98%;
	height: 90%;
	padding: 10px;
	overflow-y: scroll;
	background-color: #EFFBFF;
}
#messages p {
	position: relative;
}
.chat {
	background-color: #EFFBFF;
	border: solid 1px #7B9EBD;
}
textarea.chat {
	width: 98.5%;
}
.chat:hover {
	background-color: white;
}
select {
	height: 22px;
}
a {
	text-decoration: none;
}
a img {
	border: none;
}
form {
	position: absolute;
	bottom: 5px;
	margin: 0;
}
form p {
	padding: 5px 0 10px 10px;
}
form a:hover, #liste_connectes a:hover {
	background-color: #BBB;
}
form a:active {
	color: navy;
}
#zoneTexte {
	background-color: #C0E6E6;
}
#smileys {
	display: none;
	position: absolute;
	left: 100px;
	bottom: 80px;
	background-color: #BDF;
	padding: 0;
	margin: 0;
}
#smileys caption {
	background-color: navy;
	font-weight: bold;
	text-align: right;
}
#smileys caption input {
	font-size: 10px;
	padding: 0 4px;
}
#smileys td {
	background-color: #CFF;
	border: solid 1px #7DE;
	padding: 4px 4px 0 4px;
	cursor: pointer;
}
#smileys td:hover {
	background-color: #E7FFFF;
	border: solid 1px #0AF;
}
form input[type=button], form input[type=submit] {
	border: outset 1px #36F;
	background-color: #9CF;
	color: navy;
}
form input[type=button] {
	width: 25px;
}
#font {
	font-weight: bold;
	text-decoration: underline;
}
#ecrire {
	width: 100%;
	display: inline;
}
#fermer, #chooseColor thead th {
	width: 99.7%;
	background-color: navy;
	padding: 2px;
}
#closeDegrade {
	display: block;
	position: absolute;
	left: 125px;
	top: 25%;
	text-align: right;
	font-size: 10px;
	width: 318px;
	background-color: navy;
	padding: 2px;
}
#closeDegrade input {
	font-size: 10px;
	font-weight: bold;
	padding: 0 4px;
	margin: 0 2px;
}
#fermer {
	display: block;
	color: white;
	font-weight: bold;
	text-align: right;
}
.fermer {
	background-color: #F30;
	color: white;
	border: solid 1px white;
	cursor: default;
}
.fermer:hover {
	background-color: #F61;
}
.fermer:active {
	background-color: #E10;
}
#chooseColor {
	display: none;
	top: 30%;
	left: 150px;
	text-align: center;
	border-color: rgb(51, 102, 255);
	background-color: #FFFFCC;
	border-color: #FFFFCC;
	border: solid 1px black;
}
#chooseColor thead th, #degrade span {
	text-align: right;
}
#plus {
	width: 80%;
	font-weight: bold;
}
#chooseColor td {
	border: solid 1px black;
}
#chooseColor tbody input {
	border-width: 1px;
	width: 20px;
}
#chooseColor, #degrade {
	position: absolute;
}
#degrade {
	margin-top: 16px;
	top: 25%;
	left: 125px;
	width: 320px;
	height: 128px;
	border-right: solid 13px silver;
	border-bottom: solid 112px silver;
}
#degrade div {
	width: 2px;
	height: 8px;
	position: absolute;
	cursor: crosshair;
}
#apercuColor {
	width: 50px;
	height: 20px;
	border: solid 1px black;
	background-color: black;
}
.choix {
	width: 10px;
	height: 1px;
	position: absolute;
	left: 1px;
	cursor: crosshair;
}
label {
	position: absolute;
	top: 110px;
}
#couleur0, #couleur1, #couleur2 {
	position: absolute;
	top: 135px;
}
#alerte {
	position: absolute;
	left: -1000px;
	height: 0;
	overflow: hidden;
}
#connected {
	position: absolute;
	right: 0px;
	top: 0px;
	text-align: right;
}
#show-connected {
	background-color: white;
	padding: 5px;
	border-left: solid 2px #338;
	border-bottom: solid 2px #338;
	border-bottom-left-radius: 10px;
	font-size: 25px;
	cursor: pointer;
	display: inline-block;
}
#show-connected:hover {
	background-color: #EEE;
}
#show-connected img {
	position: relative;
	top: 4px;
	width: 25px;
	margin-right: 3px;
}
#list-connected {
	width: 200px;
	display: none;
	background-color: white;
	border-left: solid 2px #338;
	border-top: solid 2px #338;
	border-bottom: solid 2px #338;
	border-top-left-radius: 10px;
	border-bottom-left-radius: 10px;
	padding: 5px 0px;
	margin-top: 5px;
	text-align: left;
}
#list-connected .member-ctn {
	padding: 3px 0px;
	background-color: #EEF;
	border-top: solid 1px #33F;
}
#list-connected .member-ctn:last-child {
	border-bottom: solid 1px #33F;
}
#list-connected > .blocked {
	background-color: #FDD;
}
.blocked .member {
	text-decoration: line-through;
}
#list-connected .member {
	display: inline-block;
	width: 160px;
	padding: 0px 5px;
	position: relative;
	top: -2px;
}
#list-connected .block {
	width: 20px;
	padding: 0px 5px;
	cursor: pointer;
}
#mask {
	display: none;
	position: fixed;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
}
<?php
if ($admin) {
	?>
	#messages p:hover {
		background-color: #FEF;
	}
	.delete-msg {
		display: none;
	}
	#messages p:hover .delete-msg {
		display: block;
	}
	.delete-msg {
		position: absolute;
		right: 10px;
		top: 2px;
		color: #A00;
		font-size: 0.9em;
	}
	<?php
}
?>
</style>
<script type="text/javascript">
<!--
var heightScreen = 0, using = false, vide = true, listmsgs = <?php include('print_msgs.php'); ?>, repondu = false, styles = [false,false,false], couleur = [0,0,0], infoRGB = couleur;
var ignores = [<?php
$ignores = mysql_query('SELECT nom FROM `mkignores` INNER JOIN `mkjoueurs` ON ignored=id WHERE ignorer='. $id);
$v = '';
while ($ignore = mysql_fetch_array($ignores)) {
	echo $v;
	$v = ',';
	echo '"'.$ignore['nom'].'"';
}
?>];
function xhr(page, send, onload, backoff) {
	if (!backoff)
		backoff = 1000;
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
	try {
		xhr_object.timeout = 5000;
		xhr_object.onload = function () {
			if (!onload(xhr_object.responseText)) {
				setTimeout(function() {
					xhr(page,send,onload,backoff*2);
				}, backoff);
			}
		};
		xhr_object.ontimeout = function(e) {
			setTimeout(function() {
				xhr(page,send,onload, backoff*2);
			}, backoff);
		};
	}
	catch (e) {
		xhr_object.onreadystatechange = function () {
			if ((xhr_object.readyState == 4) && !onload(xhr_object.responseText)) {
				setTimeout(function() {
					xhr(page,send,onload,backoff*2);
				}, backoff);
			}
		};
	}
	xhr_object.send(send);
}
function envoyer() {
	var msg = getValue("message");
	if (msg) {
		vide = true;
		repondu = true;
		var styleMore = "";
		for (i=0;i<styles.length;i++) {
			if (styles[i])
				styleMore += "&s"+i+"=";
		}
		var colorMore = "";
		for (i=0;i<3;i++)
			colorMore += "&c"+i+"="+couleur[i];
		xhr("send.php", "message="+msg+styleMore+colorMore+"&taille="+document.forms[0].taille.value+"&police="+document.forms[0].police.value, function(reponse){return (reponse=="1")});
		document.forms[0].message.value = "";
	}
}
function cmp_array(arr1,arr2) {
    if (!arr2)
        return false;

    if (arr1.length != arr2.length)
        return false;

    for (var i=0,l=arr1.length;i<l;i++) {
        if (arr1[i] instanceof Array && arr2[i] instanceof Array) {
            if (!cmp_array(arr1[i],arr2[i]))
                return false;       
        }
        else if (arr1[i] != arr2[i])
            return false;
    }
    return true;
}
function getMsgById(id) {
	for (var i=0;i<listmsgs.length;i++) {
		var msg = listmsgs[i];
		if (msg[0] == id)
			return i;
	}
	return -1;
}
function deleteMsg(id) {
	var msgId = getMsgById(id);
	var msg = listmsgs[msgId];
	if (msgId != -1) {
		var temp = document.createElement("pre");
		temp.innerHTML = msg[2];
		if (confirm("Supprimer le message \""+ temp.firstChild.nodeValue +"\" ?")) {
			var cMessages = document.getElementById("messages");
			xhr("deletemsg.php","msg="+id, function(res) {
				msgId = getMsgById(id);
				if (msgId != -1) {
					cMessages.removeChild(cMessages.children[msgId]);
					listmsgs.splice(msgId,1);
				}
				return (res == 1);
			});
		}
	}
}
function reload() {
	xhr("refresh.php", (document.forms[0].message.value?"writting=1":""), function(reponse) {
		var rep;
		try {
			rep = eval(reponse);
		}
		catch (e) {
			return false;
		}
		if (!rep) return false;
		if (repondu || !cmp_array(listmsgs,rep[0])) {
			listmsgs = rep[0];
			if (!repondu && !using) {
				var pEmbed = document.getElementsByTagName("embed");
				if (pEmbed.length)
					document.body.removeChild(pEmbed[0]);
				var newMsg = document.createElement("embed");
				newMsg.src = "musics/new.mp3?reload="+ new Date().getTime();
				newMsg.setAttribute("autostart", "true");
				newMsg.style.position = "absolute";
				newMsg.style.left = "-1000px";
				newMsg.style.top = "-1000px";
				document.body.appendChild(newMsg);
			}
			else
				repondu = false;
			var hAvant = document.getElementById("messages").scrollHeight;
			var oMessages = document.getElementById("messages").getElementsByTagName("p");
			while (oMessages.length)
				document.getElementById("messages").removeChild(oMessages[0]);
			for (i=0;i<rep[0].length;i++) {
				var infos = rep[0][i];
				var oPara = document.createElement("p");
				<?php
				if ($admin) {
					?>
					var del = document.createElement("a");
					del.href = "javascript:deleteMsg("+ infos[0] +")";
					del.innerHTML = "Supprimer";
					del.className = "delete-msg";
					oPara.appendChild(del);
					<?php
				}
				?>
				var oSpan = document.createElement("span");
				oSpan.className = "dit";
				oSpan.innerHTML = infos[1] +" dit :";
				oPara.appendChild(oSpan);
				oPara.appendChild(document.createElement("br"));
				var oMessage = document.createElement("span");
				if (infos[3])
					oMessage.style.fontWeight = "bold";
				if (infos[4])
					oMessage.style.textDecoration = "underline";
				if (infos[5])
					oMessage.style.fontStyle = "italic";
				oMessage.style.color = "rgb("+ infos[6] +","+ infos[7] +","+ infos[8] +")";
				oMessage.style.fontSize = infos[9] +"pt";
				oMessage.style.fontFamily = infos[10];
				oMessage.innerHTML = infos[2];
				oPara.appendChild(oMessage);
				document.getElementById("messages").appendChild(oPara);
			}
			document.getElementById("messages").scrollTop += document.getElementById("messages").scrollHeight-hAvant;
		}
		var onlines = rep[1];
		var writting = new Array();
		for (var i=0;i<onlines.length;i++) {
			if (rep[2][i])
				writting.push(onlines[i]);
		}
		onlines.sort();
		writting.sort();
		if (writting.length)
			document.getElementById("ecrire").innerHTML = writting+" "+((writting.length<2)?"est":"sont")+" en train d'&eacute;crire...";
		else
			document.getElementById("ecrire").innerHTML = "&nbsp;";
		document.getElementById("nb-connected").innerHTML = onlines.length;
		if (onlines.length) {
			document.getElementById("list-connected").innerHTML = "";
			for (var i=0;i<onlines.length;i++) {
				var online = onlines[i];
				var oDiv = document.createElement("div");
				if (ignores.indexOf(online) == -1)
					oDiv.className = "member-ctn unblocked";
				else
					oDiv.className = "member-ctn blocked";
				var oPseudo = document.createElement("div");
				oPseudo.className = "member";
				oPseudo.innerHTML = online;
				oDiv.appendChild(oPseudo);
				var oBlock = document.createElement("img");
				oBlock.src = "images/minichat/ic-block.png";
				oBlock.pseudo = online;
				oBlock.className = "block";
				oBlock.title = "Ignorer ce membre";
				oBlock.onclick = function() {
					var iID = ignores.indexOf(this.pseudo);
					if (iID == -1) {
						if (confirm("Ignorer "+ this.pseudo +" ?\nVous ne verrez plus ses messages.")) {
							ignores.push(this.pseudo);
							xhr("chatblock.php", "pseudo="+this.pseudo, function(res) {
								return (res == 1);
							});
							this.parentNode.className = "member-ctn blocked";
						}
					}
					else {
						ignores.splice(iID,1);
						xhr("chatunblock.php", "pseudo="+this.pseudo, function(res) {
							return (res == 1);
						});
						this.parentNode.className = "member-ctn unblocked";
					}
				};
				oDiv.appendChild(oBlock);
				document.getElementById("list-connected").appendChild(oDiv);
			}
		}
		else
			document.getElementById("list-connected").innerHTML = '<div style="text-align: center">Aucun joueur connect&eacute;</div>';
		setTimeout(reload, 1000);
		return true;
	});
}
function getValue(name) {
	return encodeURIComponent(document.forms[0].elements[name].value);
}
function maj(e) {
	if (e.keyCode == 13 && !e.shiftKey) {
		envoyer();
		return true;
	}
}
function resize() {
	var h = heightScreen - document.forms[0].scrollHeight;
	if (h >= 0) {
		var diff = parseInt(document.getElementById("messages").style.height)-h;
		document.getElementById("messages").style.height = h+"px";
		document.getElementById("messages").scrollTop += diff;
	}
}
function adapteHauteur() {
	var oMessage = document.forms[0].message, rows = oMessage.value.split('\n'), nRows = 0, tLength = Math.floor(oMessage.scrollWidth/Math.round(parseInt(oMessage.style.fontSize)/1.25));
	for (i=0;i<rows.length;i++)
		nRows += Math.floor(rows[i].length/tLength);
	nRows += rows.length;
	if (nRows != oMessage.rows) {
		oMessage.rows=nRows;
		oMessage.style.height='auto';
		resize()
	}
}

function ajouter(smiley) {
	document.getElementById("smileys").style.display = "none";
	var field = document.forms[0].message;
	field.focus();
	
	if (window.ActiveXObject) {
		var textRange = document.selection.createRange();            
		var currentSelection = textRange.text;
		textRange.text = currentSelection + smiley;
		textRange.moveStart('character', -2-currentSelection.length);
		textRange.moveEnd('character', 0);
		textRange.select();  
	}
	else {
		var startSelection = field.value.substring(0, field.selectionStart);
		var currentSelection = field.value.substring(field.selectionStart, field.selectionEnd);
		var endSelection = field.value.substring(field.selectionEnd);
		field.value = startSelection + smiley + currentSelection + endSelection;
		field.focus();
		field.setSelectionRange(startSelection.length, startSelection.length + currentSelection.length + 2);
	}
}
function changeResolution() {
	var oDiv = document.createElement("div");
	oDiv.id = "seeHeightScreen";
	oDiv.innerHTML = "&nbsp;";
	document.body.appendChild(oDiv);
	heightScreen = oDiv.offsetHeight - 25;
	document.body.removeChild(oDiv);
	resize();
}
function changeStyle(element, id) {
	styles[id] = !styles[id];
	element.style.backgroundColor = styles[id] ? "#CFF":"";
	element.style.border = (styles[id] ? "inset":"outset") + " 1px blue";
	document.forms[0].message.style[["fontWeight","textDecoration","fontStyle"][id]] = styles[id] ? ["bold","underline","italic"][id]:"";
}

function changeColor(bg) {
	infoRGB = bg;
	document.getElementById("apercuColor").style.backgroundColor = "rgb("+bg+")";
}
function appliquer() {
	document.forms[0].message.style.color = document.getElementById("font").style.color=document.getElementById('apercuColor').style.backgroundColor;
	couleur = infoRGB;
	document.getElementById("chooseColor").style.display = "none";
	document.getElementById("font").style.backgroundColor = "";
	document.getElementById("font").style.border = "outset 1px #36F";
}
function more() {
	document.getElementById("plus").disabled = true;
	var inChoose = false, inClick = false;
	var p = document.createElement("p");
	p.style.position = "absolute";
	p.id = "degrade";
	var aff = document.createElement("div");
	aff.style.width = "12px";
	aff.style.height = "256px";
	aff.style.position = "absolute";
	aff.style.left = "445px";
	aff.style.top = "25%";
	aff.style.backgroundColor = "silver";
	var oForm = document.createElement("form");
	oForm.method="post";
	oForm.style.position = "absolute";
	oForm.style.left = "50px";
	oForm.style.top = "50px";
	oForm.onsubmit = function() {
		var rgb = new Array();
		for (i=0;i<3;i++) {
			var nb = this.elements[i].value;
			if ((nb % 1) || (nb < 0) || isNaN(nb))
				return false;
			if (nb > 255) {
				this.elements[i].value = 255;
				nb = 255;
			}
			rgb[i] = nb;
		}
		afficher([rgb[0]*1,rgb[1]*1,rgb[2]*1]);
		return false;
	}
	var colors = new Array();
	for (i=0;i<3;i++) {
		var oLabel = document.createElement("label");
		oLabel.style.left = (17+i*80-(i?0:5))+"px";
		oLabel.innerHTML = ["Rouge","Vert","Bleu"][i]+":";
		oLabel.setAttribute("for","couleur"+i);
		oForm.appendChild(oLabel);
		colors[i] = document.createElement("input");
		colors[i].type="text";
		colors[i].name = i;
		colors[i].id = "couleur"+i;
		colors[i].size = 1;
		colors[i].setAttribute("maxlength",3);
		colors[i].style.left = (15+i*80)+"px";
		colors[i].onchange = function() {
			var nb = this.value;
			if ((nb % 1) || (nb < 0) || isNaN(nb)) {
				this.value = infoRGB[this.name];
				return;
			}
			if (nb > 255) {
				this.value = 255;
				nb = 255;
			}
			afficher([colors[0].value*1,colors[1].value*1,colors[2].value*1]);
		}
		oForm.appendChild(colors[i]);
	}
	var oSubmit = document.createElement("input");
	oSubmit.type="submit";
	oSubmit.style.position = "absolute";
	oSubmit.style.width = 0;
	oSubmit.style.height = 0;
	oSubmit.style.border = 0;
	oForm.appendChild(oSubmit);
	p.appendChild(oForm);
	function bgToRGB(bg) {
		return eval(bg.replace(/rgb\(([0-9]+), ?([0-9]+), ?([0-9]+)\)/g,"[$1,$2,$3]"));
	}
	function voir(bg) {
		document.getElementById("apercuColor").style.backgroundColor = "rgb("+bg+")";
		for (i=0;i<3;i++)
			colors[i].value = bg[i];
	}
	function afficher(bg) {
		voir(bg);
		changeColor(bg);
		for (i=0;i<256;i++) {
			var BG = [bg[0],bg[1],bg[2]];
			for (j=0;j<3;j++)
				BG[j] += (i<128) ? Math.round(-BG[j]/255*(256-i*2)) : Math.round((255-BG[j])/255*(i*2-256));
			cDivs[i].style.backgroundColor = "rgb("+BG+")";
		}
	}
	function closeWindows() {
		document.body.removeChild(p);
		document.body.removeChild(aff);
		document.body.removeChild(oWindow);
		document.getElementById("oValid").onclick = appliquer;
		document.getElementById("plus").disabled = false;
	}
	var cDivs = new Array();
	for (i=0;i<256;i++) {
		cDivs[i] = document.createElement("div");
		cDivs[i].className = "choix";
		cDivs[i].style.top = i+"px";
		cDivs[i].onmousedown = function(){voir(bgToRGB(this.style.backgroundColor));inChoose=true};
		cDivs[i].onmouseover = function(){if(inChoose)voir(bgToRGB(this.style.backgroundColor))};
		aff.appendChild(cDivs[i]);
	}
	for (j=0;j<128;j+=8) {
		for (i=0;i<1280;i+=8) {
			var oDiv = document.createElement("div");
			oDiv.style.left = Math.round(i/4)+"px";
			oDiv.style.top = j+"px";
			var bg = new Array();
			switch (true) {
				case i<255:
				bg = [255,i,0];
				break;
				case i<384:
				bg = [767-i*2,511-i,0];
				break;
				case i<512:
				bg = [0,i-256,i*2-767];
				break;
				case i<768:
				bg = [0,768-i,255];
				break;
				case i<896:
				bg = [i-768,0,255];
				break;
				case i<1024:
				bg = [i-767,0,255];
				break;
				case i<1280:
				bg = [255,0,1279-i];
			}
			for (k=0;k<3;k++)
				bg[k] += Math.round((128-bg[k])/128*j);
			oDiv.style.backgroundColor = "rgb("+bg+")";
			oDiv.onmousedown = function(){afficher(bgToRGB(this.style.backgroundColor));inClick=true};
			oDiv.onmouseover = function(){if(inClick)afficher(bgToRGB(this.style.backgroundColor))};
			p.appendChild(oDiv);
		}
	}
	var oWindow = document.createElement("div");
	oWindow.id = "closeDegrade";
	var oClose = document.createElement("input");
	oClose.type = "button";
	oClose.className = "fermer";
	oClose.onclick = closeWindows;
	oClose.value = "\xD7";
	oWindow.appendChild(oClose);
	document.onmouseup = function(){inClick=false;inChoose=false};
	document.getElementById("oValid").onclick = function() {
		appliquer();
		closeWindows();
	}
	afficher(infoRGB);
	document.body.appendChild(oWindow);
	document.body.appendChild(p);
	document.body.appendChild(aff);
}
var showList = false;
function toggleConnected() {
	showList = !showList;
	document.getElementById("list-connected").style.display = (showList ? "block":"none");
	document.getElementById("mask").style.display = (showList ? "block":"none");
}
function hideConnected() {
	if (showList)
		toggleConnected();
}
window.onresize = changeResolution;
window.onload = function() {
	changeResolution();
	document.getElementById("messages").scrollTop = document.getElementById("messages").scrollHeight;
	reload();
}
-->
</script>
</head>
<body>
<div id="messages">
<?php
$smileys = Array(':)', ':D', ';)', ':o', ':p', ':s', ':(', '8)', ':$', ':}', '|)', '*[');
mysql_data_seek($messages,0);
while ($donnees = mysql_fetch_array($messages)) {
	$styles = Array();
	list($styles[0], $styles[1], $styles[2]) = split(",", $donnees["style"]);
	echo '<p>'. ($admin ? '<a class="delete-msg" href="javascript:deleteMsg('. $donnees["id"] .')">Supprimer</a>':'') .'<span class="dit">'.$donnees["pseudo"].' dit :</span><br /><span style="'. (in_array('gras', $styles) ? 'font-weight: bold;':null) . (in_array('souligne', $styles) ? 'text-decoration: underline;':null) . (in_array('italique', $styles) ? 'font-style: italic;':null) .'color: rgb('. $donnees["r"].','.$donnees["g"].','.$donnees["b"] .');font-size: '.$donnees["taille"].'pt;font-family: '.$donnees["police"].'">'.$donnees['message'].'</span></p>';
}
mysql_close();
?>
</div>
<form method="post" action="parler.php" onsubmit="envoyer();return false">
<p id="zoneTexte">
<textarea name="message" class="chat" rows="1" cols="135" style="font-size: 10pt" onkeydown="if(maj(event))return false;" oninput="adapteHauteur()" onchange="adapteHauteur()" onfocus="using=true" onblur="using=false"></textarea><br />
<input type="submit" value="Envoyer" /> &nbsp; <img src="images/smileys/smiley0.png" alt=":)" style="cursor: pointer" onclick="document.getElementById('smileys').style.display='block';document.forms[0].message.focus()" /> &nbsp; <input type="button" value="G" style="font-weight: bold" onclick="changeStyle(this,0,'fontWeight','bold')" /> <input type="button" value="S" style="text-decoration: underline" onclick="changeStyle(this,1)" /> <input type="button" value="I" style="font-style: italic" onclick="changeStyle(this,2)" /> <input type="button" value="A" id="font" style="color: black" onclick="this.style.backgroundColor='#CFF';this.style.border='inset 1px blue';document.getElementById('chooseColor').style.display='block'" /> &nbsp;<select name="taille" onchange="if(this.value!='Autre'){document.forms[0].message.style.fontSize=this.value+'pt';resize()}else{this.style.display='none';this.form.changeSize.value=parseInt(this.form.message.style.fontSize);this.form.changeSize.style.display='inline';function addSize(){var oSelect=document.forms[0].taille;if(document.getElementById('otherSize'))oSelect.removeChild(document.getElementById('otherSize'));document.forms[0].onsubmit=function(){envoyer();return false};document.forms[0].changeSize.style.display='none';document.forms[0].changeSize.onblur=undefined;oSelect.style.display='inline';oSelect.focus();var nb=document.forms[0].changeSize.value;if (!nb||isNaN(nb))nb=parseInt(document.forms[0].message.style.fontSize);else nb*=1;if(nb<7)nb=7;else if(nb>50)nb=50;document.forms[0].message.style.fontSize=nb+'pt';resize();for(i=0;i<7;i++){if(nb==oSelect[i].value){oSelect.selectedIndex=i;return}}var tailles = [oSelect[7].cloneNode(true)];oSelect.removeChild(oSelect[7]);for(var si=6;(si>=0)&&(nb<oSelect[si].value*1);si--){tailles.push(oSelect[si].cloneNode(true));oSelect.removeChild(oSelect[si])}var oNewOption = document.createElement('option');oNewOption.value=nb;oNewOption.innerHTML=nb;oNewOption.style.fontSize=nb+'pt';oNewOption.id='otherSize';oSelect.appendChild(oNewOption);for(i=tailles.length-1;i>=0;i--)oSelect.appendChild(tailles[i]);oSelect.selectedIndex=si+1}this.form.onsubmit=function(){addSize();return false};this.form.changeSize.onblur=addSize;this.form.changeSize.focus()}">
<?php
		$tailles = Array(8, 10, 12, 14, 18, 24, 36);
		for ($i=0;$i<7;$i++) {
			$taille = $tailles[$i];
			echo '<option'.($taille!=10 ? null:' selected="selected"').' value="'.$taille.'" style="font-size: '.$taille.'pt">'.$taille.'</option>';
		}
?>
<option value="Autre">Autre</option>
</select><input type="text" name="changeSize" size="1" maxlength="2" style="display: none" /> &nbsp;<select name="police" onchange="document.forms[0].message.style.fontFamily=this.value">
<?php
		$fonts = Array('Arial', 'Arial Black', 'Book Antiqua', 'Calibri', 'Comic Sans MS', 'Courier New', 'Decorative', 'Frosty', 'Garamond', 'Georgia', 'Impact', 'Monospace', 'Terminal', 'Times New Roman', 'Trebuchet MS', 'Verdana');
		for ($i=0;$i<16;$i++) {
			$font = $fonts[$i];
			echo '<option value="'.$font.'" style="font-family: '.$font.'">'.$font.'</option>';
		}
?>
</select></p>
<p id="ecrire">&nbsp;</p>
<div id="alerte"></div>
</form>
<table id="smileys">
<caption><input type="button" class="fermer" value="&times;" onclick="document.getElementById('smileys').style.display='none';document.forms[0].message.focus()" /><span style="font-size: 10px">&nbsp;</span></caption>
		<?php
		$emoticones = Array("Visage", "Rire", "Clin d'&oelig;il", "Surpris", "Tire la langue", "Confus", "Triste", "Intello", "Embarrass&eacute;", "Agressif", "Pensif", "Endormi");
		for ($i=0;$i<12;$i++) {
			$smiley = $smileys[$i];
			echo ($i%4 ? null:'<tr>') .'<td title="'.$emoticones[$i].' &nbsp;'.$smiley.'" onclick="ajouter(\''.$smiley.'\')"><img src="images/smileys/smiley'.$i.'.png" alt="'.$smiley.'" /></td>'. (($i+1)%4 ? null:'</tr>');
		}
		?>
</table>
<table id="chooseColor">
<thead>
<tr><th colspan="10"><input type="button" class="fermer" value="&times;" onclick="document.getElementById('chooseColor').style.display='none';document.getElementById('font').style.backgroundColor='';document.getElementById('font').style.border='outset 1px #36F'" /></th></tr>
</thead>
<tfoot>
<tr><th colspan="10"><input type="button" id="plus" value="Plus de couleurs" onclick="more()" />
<input type="text" disabled="disabled" id="apercuColor" /> <input type="submit" id="oValid" value="Valider" onclick="appliquer()" /></th></tr>
</tfoot>
<tbody>
		<?php
		$couleurs = Array(Array(255,255,255),Array(255,204,204),Array(255,204,153),Array(255,255,153),Array(255,255,204),Array(153,255,153),Array(153,255,255),Array(204,255,255),Array(204,204,255),Array(255,204,255),Array(204,204,204),Array(255,102,102),Array(255,153,102),Array(255,255,102),Array(255,255,51),Array(102,255,153),Array(51,255,255),Array(102,255,255),Array(153,153,255),Array(255,153,255),Array(192,192,192),Array(255,0,0),Array(255,153,0),Array(255,204,102),Array(255,255,0),Array(51,255,51),Array(102,204,204),Array(51,204,255),Array(102,102,204),Array(204,102,204),Array(153,153,153),Array(204,0,0),Array(255,102,0),Array(255,204,51),Array(255,204,0),Array(51,204,0),Array(0,204,204),Array(51,102,255),Array(102,51,255),Array(204,51,204),Array(102,102,102),Array(153,0,0),Array(204,102,0),Array(204,153,51),Array(153,153,0),Array(0,153,0),Array(51,153,153),Array(51,51,255),Array(102,0,204),Array(153,51,153),Array(51,51,51),Array(102,0,0),Array(153,51,0),Array(153,102,51),Array(102,102,0),Array(0,102,0),Array(51,102,102),Array(0,0,153),Array(51,51,153),Array(102,51,102),Array(0,0,0),Array(51,0,0),Array(102,51,0),Array(102,51,51),Array(51,51,0),Array(0,51,0),Array(0,51,51),Array(0,0,102),Array(51,0,153),Array(51,0,51));
		$l = count($couleurs);
		for ($i=0;$i<$l;$i++) {
			$rgb = $couleurs[$i];
			echo (($i%10) ? null:'<tr>') .'<td><input type="button" class="choose" style="background-color: rgb('. $rgb[0].','.$rgb[1].','.$rgb[2] .')" onclick="changeColor(['. $rgb[0].','.$rgb[1].','.$rgb[2] .'])" /></td>'. ((($i+1)%10) ? null:'</tr>');
		}
		?>
</tbody>
</table>
<div id="mask" onclick="hideConnected()"></div>
<div id="connected">
	<div id="show-connected" onclick="toggleConnected()">
		<img src="images/minichat/ic-connected.png" alt="Connectés" /> <span id="nb-connected">...</span>
	</div>
	<div id="list-connected">
	</div>
</div>
</body>
</html>