<?php
include('getId.php');
include('language.php');
include('initdb.php');
require_once('utils-challenges.php');
if (isset($_GET['moderate'])) {
	include('session.php');
	require_once('getRights.php');
	if (hasRight('clvalidator'))
		$moderate = true;
}
if (isset($_GET['ch'])) {
	$challenge = getChallenge($_GET['ch']);
	if ($challenge)
		$clRace = getClRace($challenge['clist'], !empty($moderate));
}
elseif (isset($_GET['cl']))
	$clRace = getClRace($_GET['cl']);
include('challenge-cldata.php');
$circuitUrl = '';
if (isset($clCircuit) && isset($clTable)) {
	$circuitId = $clCircuit['id'];
	switch ($clTable) {
	case 'circuits':
		$circuitUrl = 'racepreview.php?id='.$circuitId;
		break;
	case 'arenes':
		$circuitUrl = 'coursepreview.php?id='.$circuitId;
		break;
	case 'mkcircuits':
		$circuitUrl = 'mappreview.php?id='.$circuitId;
		break;
	}
}
else
	$circuitUrl = 'mapcreate.php?'.$_SERVER["QUERY_STRING"];
?>
<html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/challenges.css" />
<style type="text/css">
body {
	text-align: center;
}
#editor-ctn {
	display: inline-block;
	max-width: 100%;
}
#editor {
	position: relative;
	display: inline-block;
	image-rendering: optimizeSpeed;
	image-rendering: -moz-crisp-edges;
	image-rendering: -o-crisp-edges;
	image-rendering: -webkit-optimize-contrast;
	image-rendering: pixelated;
	image-rendering: optimize-contrast;
	-ms-interpolation-mode: nearest-neighbor;
}
#params {
	margin-top: 5px;
	margin-bottom: 15px;
}
#params input[type="button"] {
	width: 40px;
}
#theme-titme {
	font-size: 1.2em;
}
.rect {
	position: absolute;
	background-color: white;
	opacity: 0.6;
}
.theme-dark .rect {
	background-color: black;
}
#apply {
	margin-top: 8px;
}
#apply button {
	width: 100%;
	font-size: 1.3em;
	cursor: pointer;
	margin-bottom: 10px;
}
</style>
<script type="text/javascript">
var language = <?php echo $language ? 1:0; ?>;
function getRelativePos(e,parent) {
	var rect = parent.getBoundingClientRect();
	return {
		x: Math.round(e.clientX - rect.left),
		y: Math.round(e.clientY - rect.top)
	}
}
function selectTheme(themeClass) {
	document.body.className = themeClass;
}
function validateZone() {
	undo = firstUndo;
	var oRects = document.getElementsByClassName("rect");
	var data = [];
	for (var i=0;i<oRects.length;i++) {
		var dataset = oRects[i].dataset;
		data.push([+dataset.x,+dataset.y,+dataset.w,+dataset.h]);
	}
	window.opener.storeZoneData(data);
	window.close();
};
var undo = function(){};
var firstUndo = undo;
function pushUndo(callback) {
	var lastUndo = undo;
	undo = function() {
		callback();
		undo = lastUndo;
	};
};
document.addEventListener("DOMContentLoaded", function() {
	var editor = document.getElementById("editor");
	var oRect;
	var aX, aY;
	function applyRect(x,y,w,h,iRect) {
		var triggered = !iRect;
		if (triggered) {
			iRect = document.createElement("div");
			iRect.className = "rect";
			editor.appendChild(iRect);
		}
		if (!iRect.dataset) iRect.dataset = {};
		iRect.dataset.x = x;
		iRect.dataset.y = y;
		iRect.dataset.w = w;
		iRect.dataset.h = h;
		iRect.style.left = iRect.dataset.x +"px";
		iRect.style.top = iRect.dataset.y +"px";
		iRect.style.width = iRect.dataset.w +"px";
		iRect.style.height = iRect.dataset.h +"px";
		iRect.title = language ? "Right click to delete":"Clic droit pour supprimer";
		var aRect = iRect;
		if (!triggered) {
			var aX2 = aX, aY2 = aY;
			var aMouseMove = document.onmousemove;
			pushUndo(function() {
				aX = aX2;
				aY = aY2;
				aRect.style.left = aX +"px";
				aRect.style.top = aY +"px";
				aRect.style.width = "1px";
				aRect.style.height = "1px";
				oRect = aRect;
				document.onmousemove = aMouseMove;
			});
		}
		iRect.onmousedown = function(e) {
			if (e.button == 2) {
				var lRect = aRect.nextSibling;
				editor.removeChild(aRect);
				if (lRect) {
					pushUndo(function() {
						editor.insertBefore(aRect,lRect);
					});
				}
				else {
					pushUndo(function() {
						editor.appendChild(aRect);
					});
				}
				document.oncontextmenu = function() {
					document.oncontextmenu = null;
					return false;
				}
			}
		};
		if (!triggered) {
			document.onmousemove = undefined;
			oRect = null;
		}
	}
	editor.onclick = function(e) {
		var pos = getRelativePos(e,editor);
		var x = pos.x, y = pos.y;
		if (oRect)
			applyRect(Math.min(aX,x),Math.min(aY,y),Math.abs(x-aX),Math.abs(y-aY), oRect);
		else {
			aX = x;
			aY = y;
			oRect = document.createElement("div");
			oRect.style.left = x +"px";
			oRect.style.top = y +"px";
			oRect.className = "rect";
			editor.appendChild(oRect);
			document.onmousemove = function(e2) {
				var pos2 = getRelativePos(e2,editor);
				var x2 = pos2.x, y2 = pos2.y;
				oRect.style.left = Math.min(aX,x2) +"px";
				oRect.style.top = Math.min(aY,y2) +"px";
				oRect.style.width = Math.abs(x2-aX) +"px";
				oRect.style.height = Math.abs(y2-aY) +"px";
			};
			pushUndo(function() {
				editor.removeChild(oRect);
				oRect = null;
				document.onmousemove = undefined;
			});
		}
	};
	document.onkeydown = function(e) {
		if (e.keyCode == 90 && e.ctrlKey)
			undo();
	}
	window.onbeforeunload = function() {
		var oRects = document.getElementsByClassName("rect");
		if (oRects.length && (firstUndo != undo))
			return language ? "Caution, if you want to save the zone, click on \"Validate zone before\"":"Attention, si vous voulez sauvegarder la zone, cliquez sur \"Validez la zone\" avant";
	};
	var data = window.opener.loadZoneData();
	for (var i=0;i<data.length;i++) {
		var iData = data[i];
		applyRect(iData[0],iData[1],iData[2],iData[3]);
	}
});
window.onload = function() {
	document.getElementById("editor-ctn").style.width = Math.max(200,document.getElementById("editor").scrollWidth) +"px";
};
</script>
</head>
<body>
	<div id="editor-ctn">
		<div id="params" class="challenge-explain"><?php
		if ($language) {
			?>
			Indicate the zone in the circuit with one or more rectangles.
			To draw a rectangle, click on the map, move the mouse and click again.
			To delete a rectangle, right click on it.
			When you finished, click on &quot;Validate zone&quot; below.
			<?php
		}
		else {
			?>
			Indiquez la zone avec un ou plusieurs rectangles.
			Pour dessiner un rectangle, cliquez sur la carte, déplacez la souris et cliquez de nouveau.
			Pour supprimer un rectangle, faites un clic droit dessus.<br />
			Quand vous avez fini, cliquez sur &quot;Valider la zone&quot; en bas.
			<?php
		}
		?>
		<hr />
		<span id="theme-titme">Thème :</span>
		<input type="button" style="background-color:white" onclick="selectTheme('')" />
		<input type="button" style="background-color:black" onclick="selectTheme('theme-dark')" />
		</div>
		<br />
		<div id="editor">
			<img src="<?php echo $circuitUrl; ?>" alt="Circuit" />
		</div>
		<div id="apply">
			<button type="button" class="main-challenge-action" onclick="validateZone()"><?php echo $language ? 'Validate zone':'Valider la zone'; ?></button>
		</div>
	</div>
</body>
</html>
<?php
mysql_close();
?>