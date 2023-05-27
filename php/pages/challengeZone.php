<?php
include('../includes/getId.php');
include('../includes/language.php');
include('../includes/initdb.php');
require_once('../includes/utils-challenges.php');
if (isset($_GET['moderate'])) {
	include('../includes/session.php');
	require_once('../includes/getRights.php');
	if (hasRight('clvalidator'))
		$moderate = true;
}
if (isset($_GET['ch'])) {
	$challenge = getChallenge($_GET['ch'], !empty($moderate));
	if ($challenge)
		$clRace = getClRace($challenge['clist'], !empty($moderate));
}
elseif (isset($_GET['cl']))
	$clRace = getClRace($_GET['cl']);
include('../includes/challenge-cldata.php');
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
$type = isset($_GET['type']) ? $_GET['type'] : null;
$editorContext = array();
switch ($type) {
	case 'zones':
		$submitTitle = $language ? 'Validate zones':'Valider les zones';
	break;
	case 'coins':
		$submitTitle = $language ? 'Validate coins':'Valider les pièces';
	break;
	case 'items':
		$submitTitle = $language ? 'Validate items':'Valider les objets';
	break;
	case 'decors':
		$submitTitle = $language ? 'Validate decors':'Valider les décors';
		$getDecors = mysql_query('SELECT id,name,type,sprites,img_data FROM mkdecors WHERE identifiant="'. $identifiants[0] .'" AND extra_parent_id IS NULL ORDER BY id');
		$myDecors = array();
		$customDecors = new \stdClass();
		while ($decor = mysql_fetch_array($getDecors)) {
			require_once('../includes/utils-decors.php');
			$decorSrcs = get_decor_srcs($decor);
			$myDecor = array(
				'id' => $decor['id'],
				'name' => $decor['name'],
				'type' => $decor['type'],
                'hd' => $decorSrcs['hd'],
                'ld' => $decorSrcs['ld'],
                'map' => $decorSrcs['map']
			);
			$myDecors[] = $myDecor;
			$customDecors->{'custom-'.$decor['id']} = $myDecor;
		}
		$editorContext['customDecors'] = $customDecors;
	break;
	case 'startpos':
		$submitTitle = $language ? 'Validate location':'Valider la position';
	break;
	default:
		$submitTitle = $language ? 'Validate zone':'Valider la zone';
}
?>
<html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/challenges.css" />
<link rel="stylesheet" href="styles/editor.css?reload=1" />
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
.challenge-explain {
	margin-top: 5px;
	margin-bottom: 15px;
	font-size: 0.9em;
}
#zone-editor-options > div {
	display: flex;
	flex-wrap: wrap;
	margin: 4px 0;
	align-items: center;
}
#zone-editor-options > div > * {
	margin: 0 2px;
}
#zone-editor-options > div > span {
	font-size: 1.2em;
}
#zone-editor-options input[type="button"].selected {
	border-color: #0CF;
}
#zone-editor-theme input[type="button"] {
	width: 40px;
}
.zone-editor-decor input[type="button"] {
    width: 22px;
    background-size: auto 14px;
    background-repeat: no-repeat;
    background-position: center;
}
.zone-editor-decor input[type="button"].selected {
	background-color: #7CF;
}
#zone-editor-shape input[type="button"] {
	width: 25px;
	padding: 3px 5px;
	background-size: cover;
	background-repeat: no-repeat;
	background-position: center;
}
#editor-svg {
	position: absolute;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
}
#editor-svg rect, #editor-svg polygon {
	fill: white;
}
.theme-dark #editor-svg polygon, .theme-dark #editor-svg rect {
	fill: black;
}
#editor-svg rect, #editor-svg polyline, #editor-svg polygon {
	opacity: 0.6;
}
#editor-svg circle.vertex {
	stroke-width: 2;
	r: 4;
}
#editor-svg circle {
	fill: white;
}
.theme-dark #editor-svg circle {
	fill: black;
}
#editor-svg circle.vertex, .theme-dark #editor-svg circle.vertex.first:hover {
	fill: black;
	stroke: white;
}
.theme-dark #editor-svg circle.vertex, #editor-svg circle.vertex.first:hover {
	fill: white;
	stroke: black;
}
#editor-svg polyline {
	stroke-width: 4;
	stroke: white;
	fill: transparent;
}
.theme-dark #editor-svg polyline {
	stroke: black;
}
.theme-dark #editor-svg polygon {
	background-color: black;
}
#editor-svg line {
	stroke-width: 4;
	stroke: white;
	fill: transparent;
}
.theme-dark #editor-svg line {
	stroke: black;
}
#editor-svg text {
	alignment-baseline: middle;
	text-anchor: middle;
	pointer-events: none;
	opacity: 0.8;
}
.theme-dark #editor-svg text {
	fill: white;
}
#editor-svg .shape-order {
	display: none;
	font-size: 12px;
}
.ordered-shape #editor-svg .shape-order {
	display: block;
}
#apply {
	margin-top: 8px;
}
#apply button {
	width: 100%;
	font-size: 1.3em;
	cursor: pointer;
	margin-bottom: 10px;
	margin-left: 0;
}
#decor-selector-more {
	cursor: pointer;
	background-color: #396;
	background-image: url('images/editor/plus.png');
}
#collab-popup {
	position: fixed;
	z-index: 10;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	background-color: rgba(0,0,0, 0.5);
}
.popup-content {
	position: absolute;
	left: 50%;
	top: 50%;
	padding: 10px 10px;
	width: 425px;
	max-width: 90%;
	transform: translate(-50%, -50%);
	-ms-transform: translate(-50%, -50%);
	-webkit-transform: translate(-50%, -50%);
	background-color: #AAC;
	border-radius: 5px;
	opacity: 0.8;
	color: black;
}
.popup-content:hover {
	opacity: 1;
}
.popup-content h3 {
	margin-top: 2px;
	margin-bottom: 2px;
}
.popup-content h4 {
	margin-top: 2px;
	margin-bottom: 2px;
}
.popup-content h2 {
	margin-top: 5px;
	margin-bottom: 5px;
}
#collab-popup .editor-mask-content {
	color: black;
}
#collab-popup:not([data-state]) {
	display: none;
}
#collab-popup[data-state="loading"] .editor-mask-content {
	display: none;
}
#collab-popup form {
	margin-top: 0.5em;
	margin-bottom: 0.5em;
	font-size: 0.9em;
	display: flex;
	gap: 0.1em;
}
#collab-popup form input[type="url"] {
	font-size: 1em;
	flex: 1;
}
#collab-popup form input[type="submit"] {
    background-color: #69C;
    color: white;
    border-color: #48A;
	cursor: pointer;
	border-radius: 0.25em;
}
</style>
<?php
include('../includes/o_xhr.php');
?>
<script type="text/javascript">
var language = <?php echo $language ? 1:0; ?>;
var editorType = "<?php if ($type) echo htmlspecialchars($type); ?>";
var editorContext = <?php echo json_encode($editorContext); ?>;
function getRelativePos(e,parent) {
	var rect = parent.getBoundingClientRect();
	return {
		x: Math.round(e.clientX - rect.left),
		y: Math.round(e.clientY - rect.top)
	}
}
function selectButton($btn) {
	var $btns = $btn.parentNode.parentNode.querySelectorAll('input[type="button"]');
	for (var i=0;i<$btns.length;i++)
		$btns[i].classList.remove("selected");
	$btn.classList.add("selected");
	$btn.blur();
}
function selectTheme($btn,isDark) {
	selectButton($btn);
	if (isDark)
		document.body.classList.add("theme-dark");
	else
		document.body.classList.remove("theme-dark");
}
var currentDecor;
function selectDecor($btn,type) {
	selectButton($btn);
	currentDecor = type;
}
function selectOrdered($btn,isOrdered) {
	if (isOrdered)
		document.body.classList.add("ordered-shape");
	else
		document.body.classList.remove("ordered-shape");
}
var shapeType = "rectangle";
switch (editorType) {
case "coins":
case "items":
	shapeType = "point";
	break;
case "decors":
	shapeType = "img";
	break;
case "startpos":
	shapeType = "arrow";
}
function selectShape($btn,type) {
	selectButton($btn);
	shapeType = type;
}
function validateZone() {
	undo = firstUndo;
	var $svg = document.getElementById("editor-svg");
	var oShapes = $svg.getElementsByClassName("shape");
	var data = [], meta = {};
	for (var i=0;i<oShapes.length;i++) {
		var iData = oShapes[i].dataset.data;
		if (iData)
			data.push(JSON.parse(iData));
	}
	switch (editorType) {
	case "startpos":
		data = data[0];
		break;
	case "decors":
		for (var i=0;i<data.length;i++) {
			var iData = data[i];
			var customData = editorContext.customDecors[iData.src];
			if (customData) {
				if (!meta.custom_decors) meta.custom_decors = {};
				meta.custom_decors[iData.src] = {
					id: customData.id,
					type: customData.type
				};
			}
		}
		break;
	}
	var $ordered = document.getElementById("zone-editor-ordered");
	if ($ordered)
		meta.ordered = +$ordered.checked;
	window.opener.storeZoneData(data,meta, editorType);
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
function resetOrders() {
	var $texts = document.querySelectorAll(".shape-order");
	for (var i=0;i<$texts.length;i++)
		$texts[i].innerHTML = (i+1);
}
function getShapeType(oBox) {
	if ("number" === typeof(oBox[0])) {
		if (oBox.length === 2)
			return "point";
		return "rectangle";
	}
	if (oBox.src)
		return "img";
	if (oBox.pos)
		return "arrow";
	return "polygon";
}
function showOrderHelp() {
	alert(language ? "If enabled, the player will have to pass through the zones in the right order to complete the challenge. If disabled, the player will have to pass the zones but in the order he wants." : "Si activé, le joueur devra traverser les zones dans le bon ordre pour réussir le défi. Si désactivé, il devra passer par toutes les zones mais dans l'ordre qu'il veut.")
}
var SVG = "http://www.w3.org/2000/svg";
document.addEventListener("DOMContentLoaded", function() {
	var editor = document.getElementById("editor");
	var $svg = document.getElementById("editor-svg");
	var oRect, oPoly, oArrow;
	var aX, aY, aPts;
	function applyRect(x,y,w,h,iRect) {
		var triggered = !iRect;
		if (triggered) {
			iRect = document.createElementNS(SVG, "rect");
			$svg.appendChild(iRect);
		}
		iRect.setAttribute("class", "shape");
		if (!iRect.dataset) iRect.dataset = {};
		iRect.dataset.data = JSON.stringify([x,y,w,h]);
		iRect.setAttribute("x", x);
		iRect.setAttribute("y", y);
		iRect.setAttribute("width", w);
		iRect.setAttribute("height", h);
		iRect.title = language ? "Right click to delete":"Clic droit pour supprimer";
		var iOrder = document.createElementNS(SVG, "text");
		iOrder.setAttribute("class", "shape-order");
		iOrder.setAttribute("x", Math.round(x+w/2));
		iOrder.setAttribute("y", Math.round(y+h/2));
		iOrder.innerHTML = document.getElementsByClassName("shape").length;
		$svg.appendChild(iOrder);
		var aRect = iRect;
		if (!triggered) {
			var aX2 = aX, aY2 = aY;
			var aMouseMove = document.onmousemove;
			pushUndo(function() {
				aX = aX2;
				aY = aY2;
				aRect.setAttribute("x", aX);
				aRect.setAttribute("y", aY);
				aRect.setAttribute("width", 1);
				aRect.setAttribute("height", 1);
				oRect = aRect;
				$svg.removeChild(iOrder);
				document.onmousemove = aMouseMove;
			});
		}
		iRect.oncontextmenu = function(e) {
			var lShape = iOrder.nextSibling;
			$svg.removeChild(iOrder);
			$svg.removeChild(aRect);
			resetOrders();
			if (lShape) {
				pushUndo(function() {
					$svg.insertBefore(aRect,lShape);
					$svg.insertBefore(iOrder,lShape);
					resetOrders();
				});
			}
			else {
				pushUndo(function() {
					$svg.appendChild(aRect);
					$svg.appendChild(iOrder);
					resetOrders();
				});
			}
			return false;
		};
		if (!triggered) {
			document.onmousemove = undefined;
			oRect = null;
		}
	}
	function applyPoly(pts,iPoly) {
		var triggered = !iPoly;
		if (triggered)
			iPoly = document.createElementNS(SVG, "polygon");
		$svg.appendChild(iPoly);
		iPoly.setAttribute("class", "shape");
		if (!iPoly.dataset) iPoly.dataset = {};
		iPoly.dataset.data = JSON.stringify(pts);
		setPolyPoints(iPoly, pts);
		iPoly.title = language ? "Right click to delete":"Clic droit pour supprimer";
		var iOrder = document.createElementNS(SVG, "text");
		var cX = 0, cY = 0;
		for (var i=0;i<pts.length;i++) {
			cX += pts[i][0];
			cY += pts[i][1];
		}
		cX /= pts.length;
		cY /= pts.length;
		iOrder.setAttribute("class", "shape-order");
		iOrder.setAttribute("x", Math.round(cX));
		iOrder.setAttribute("y", Math.round(cY));
		iOrder.innerHTML = document.getElementsByClassName("shape").length;
		$svg.appendChild(iOrder);
		var aPoly = iPoly;
		if (!triggered) {
			var aPts2 = aPts;
			var aMouseMove = document.onmousemove;
			var aPolyData = oPoly;
			pushUndo(function() {
				aPts = aPts2;
				var aPt = aPts[aPts.length-1];
				aPts.push([aPt[0],aPt[1]]);
				$svg.removeChild(aPoly);
				$svg.appendChild(aPolyData.lines);
				for (var i=0;i<aPolyData.pts.length;i++)
					$svg.appendChild(aPolyData.pts[i]);
				oPoly = aPolyData;
				$svg.removeChild(iOrder);
				document.onmousemove = aMouseMove;
			});
		}
		iPoly.oncontextmenu = function(e) {
			var lShape = iOrder.nextSibling;
			$svg.removeChild(iOrder);
			$svg.removeChild(aPoly);
			resetOrders();
			if (lShape) {
				pushUndo(function() {
					$svg.insertBefore(aPoly,lShape);
					$svg.insertBefore(iOrder,lShape);
					resetOrders();
				});
			}
			else {
				pushUndo(function() {
					$svg.appendChild(aPoly);
					$svg.appendChild(iOrder);
					resetOrders();
				});
			}
			return false;
		};
		if (!triggered) {
			document.onmousemove = undefined;
			oPoly = null;
		}
	}
	function applyPoint(x,y, iCircle) {
		var triggered = !iCircle;
		if (triggered)
			iCircle = document.createElementNS(SVG, "circle");
		iCircle.setAttribute("r", 4);
		iCircle.setAttribute("cx", x);
		iCircle.setAttribute("cy", y);
		iCircle.setAttribute("class", "shape");
		if (!iCircle.dataset) iCircle.dataset = {};
		iCircle.dataset.data = JSON.stringify([x,y]);
		$svg.appendChild(iCircle);

		if (!triggered) {
			pushUndo(function() {
				$svg.removeChild(iCircle);
			});
		}
		iCircle.oncontextmenu = function(e) {
			var lShape = iCircle.nextSibling;
			$svg.removeChild(iCircle);
			if (lShape) {
				pushUndo(function() {
					$svg.insertBefore(iCircle,lShape);
				});
			}
			else {
				pushUndo(function() {
					$svg.appendChild(iCircle);
				});
			}
			return false;
		};
		if (!triggered) {
			document.onmousemove = undefined;
			oRect = null;
		}
	}
	function applyImg(x,y,src, oImg) {
		var triggered = !oImg;
		if (triggered)
			oImg = document.createElementNS(SVG, "image");
		setupImg(x,y,src, oImg);
		if (!oImg.dataset) oImg.dataset = {};
		oImg.dataset.data = JSON.stringify({src:src,pos:[x,y]});
		$svg.appendChild(oImg);

		if (!triggered) {
			pushUndo(function() {
				$svg.removeChild(oImg);
			});
		}
		oImg.oncontextmenu = function(e) {
			var lShape = oImg.nextSibling;
			$svg.removeChild(oImg);
			if (lShape) {
				pushUndo(function() {
					$svg.insertBefore(oImg,lShape);
				});
			}
			else {
				pushUndo(function() {
					$svg.appendChild(oImg);
				});
			}
			return false;
		};
		if (!triggered) {
			document.onmousemove = undefined;
			oRect = null;
		}
	}
	function setupImg(x,y,src, oImg) {
		var customDecor = editorContext.customDecors[src];
		if (customDecor) {
			if (customDecor.onload) {
				oImg.setAttribute("href", "images/map_icons/"+ customDecor.type +".png");
				customDecor.onload(function(res) {
					oImg.setAttribute("href", res.map);
				});
			}
			else
				oImg.setAttribute("href", customDecor.map);
		}
		else
			oImg.setAttribute("href", "images/map_icons/"+ src +".png");
		oImg.setAttribute("height", 12);
		oImg.setAttribute("x", x-6);
		oImg.setAttribute("y", y-6);
		oImg.setAttribute("class", "shape");
	}
	function createArrow(x,y) {
		var oCircle = document.createElementNS(SVG, "circle");
		oCircle.setAttribute("r", 4);
		oCircle.setAttribute("cx", x);
		oCircle.setAttribute("cy", y);
		oCircle.setAttribute("class", "shape");
		if (!oCircle.dataset) oCircle.dataset = {};
		oCircle.dataset.data = JSON.stringify({pos:[x,y],angle:0});
		$svg.appendChild(oCircle);
		var oLines = [
			document.createElementNS(SVG, "line"),
			document.createElementNS(SVG, "line"),
			document.createElementNS(SVG, "line")
		];
		for (var i=0;i<oLines.length;i++)
			$svg.appendChild(oLines[i]);
		var res = {
			start: oCircle,
			lines: oLines
		}
		function removeArr(e) {
			removeArrow(res);
			var aArrow = oArrow;
			var aPos = [aX,aY];
			var aMouseMove = document.onmousemove;
			document.onmousemove = undefined;
			oArrow = undefined;
			pushUndo(function() {
				$svg.appendChild(oCircle);
				for (var i=0;i<oLines.length;i++)
					$svg.appendChild(oLines[i]);
				oArrow = aArrow;
				document.onmousemove = aMouseMove;
				aX = aPos[0];
				aY = aPos[1];
			});
			return false;
		};
		oCircle.oncontextmenu = removeArr;
		for (var i=0;i<oLines.length;i++)
			oLines[i].oncontextmenu = removeArr;
		return res;
	}
	function removeArrow(cArrow) {
		var oCircle = cArrow.start, oLines = cArrow.lines;
		$svg.removeChild(oCircle);
		for (var i=0;i<oLines.length;i++)
			$svg.removeChild(oLines[i]);
	}
	function applyArrow(aX,aY, x,y, cArrow) {
		if (!cArrow) {
			cArrow = createArrow(aX,aY);
			pushUndo(function() {
				$svg.removeChild(cArrow.start);
				for (var i=0;i<cArrow.lines.length;i++)
					$svg.removeChild(cArrow.lines[i]);
				oArrow = undefined;
			});
		}
		var angle = Math.atan2(y-aY,x-aX) || 0;

		var oData = JSON.parse(cArrow.start.dataset.data);
		oData.angle = angle;
		cArrow.start.dataset.data = JSON.stringify(oData);

		var arrowLength = 30;
		var nX = aX + arrowLength * Math.cos(angle);
		var nY = aY + arrowLength * Math.sin(angle);
		cArrow.lines[0].setAttribute("x1", aX);
		cArrow.lines[0].setAttribute("y1", aY);
		cArrow.lines[0].setAttribute("x2", nX);
		cArrow.lines[0].setAttribute("y2", nY);

		var tipOffset = 1, tipLength = 10, tipAngle = angle - Math.PI*3/4;
		var aX1 = nX - tipOffset * Math.cos(tipAngle);
		var aY1 = nY - tipOffset * Math.sin(tipAngle);
		var nX1 = nX + tipLength * Math.cos(tipAngle);
		var nY1 = nY + tipLength * Math.sin(tipAngle);
		cArrow.lines[1].setAttribute("x1", aX1);
		cArrow.lines[1].setAttribute("y1", aY1);
		cArrow.lines[1].setAttribute("x2", nX1);
		cArrow.lines[1].setAttribute("y2", nY1);

		tipAngle = angle + Math.PI*3/4;
		var aX2 = nX - tipOffset * Math.cos(tipAngle);
		var aY2 = nY - tipOffset * Math.sin(tipAngle);
		var nX2 = nX + tipLength * Math.cos(tipAngle);
		var nY2 = nY + tipLength * Math.sin(tipAngle);
		cArrow.lines[2].setAttribute("x1", aX2);
		cArrow.lines[2].setAttribute("y1", aY2);
		cArrow.lines[2].setAttribute("x2", nX2);
		cArrow.lines[2].setAttribute("y2", nY2);
	}
	editor.onclick = function(e) {
		var pos = getRelativePos(e,editor);
		var x = pos.x, y = pos.y;
		if (oRect)
			applyRect(Math.min(aX,x),Math.min(aY,y),Math.abs(x-aX),Math.abs(y-aY), oRect);
		else if (oPoly) {
			var aPt = aPts[aPts.length-1];
			aPt[0] = x;
			aPt[1] = y;
			aPts.push([x,y]);
			var oLines = oPoly.lines;
			setPolyPoints(oLines, aPts);
			var oPoint = document.createElementNS(SVG, "circle");
			oPoint.setAttribute("class", "vertex");
			oPoint.setAttribute("cx", x);
			oPoint.setAttribute("cy", y);
			oPoly.pts.push(oPoint);
			$svg.appendChild(oPoint);
			pushUndo(function() {
				$svg.removeChild(oPoint);
				aPts.splice(aPts.length-2,1);
				oPoly.pts.pop();
				setPolyPoints(oLines, aPts);
			});
		}
		else if (oArrow) {
			applyArrow(aX,aY,x,y, oArrow);
			var aMouseMove = document.onmousemove;
			document.onmousemove = undefined;
			var aArrow = oArrow;
			var aPos = [aX,aY];
			oArrow = undefined;
			pushUndo(function() {
				oArrow = aArrow;
				document.onmousemove = aMouseMove;
				aX = aPos[0];
				aY = aPos[1];
			});
		}
		else {
			switch (shapeType) {
			case "rectangle":
				aX = x;
				aY = y;
				oRect = document.createElementNS(SVG, "rect");
				oRect.setAttribute("x", x);
				oRect.setAttribute("y", y);
				$svg.appendChild(oRect);
				document.onmousemove = function(e2) {
					var pos2 = getRelativePos(e2,editor);
					var x2 = pos2.x, y2 = pos2.y;
					oRect.setAttribute("x", Math.min(aX,x2));
					oRect.setAttribute("y", Math.min(aY,y2));
					oRect.setAttribute("width", Math.abs(x2-aX));
					oRect.setAttribute("height", Math.abs(y2-aY));
				};
				pushUndo(function() {
					$svg.removeChild(oRect);
					oRect = null;
					document.onmousemove = undefined;
				});
				break;
			case "point":
				var oCircle = document.createElementNS(SVG, "circle");
				applyPoint(x,y, oCircle);
				break;
			case "img":
				var oImg = document.createElementNS(SVG, "image");
				if (!currentDecor) {
					alert(language ? "Please select a decor type first":"Sélectionnez un type de décor avant de commencer");
					return;
				}
				applyImg(x,y,currentDecor, oImg);
				break;
			case "arrow":
				if (document.getElementsByClassName("shape").length) break;
				aX = x;
				aY = y;
				oArrow = createArrow(x,y);
				document.onmousemove = function(e2) {
					var pos2 = getRelativePos(e2,editor);
					var x2 = pos2.x, y2 = pos2.y;
					applyArrow(aX,aY,x2,y2, oArrow);
				};
				var cArrow = oArrow;
				pushUndo(function() {
					removeArrow(cArrow);
					document.onmousemove = undefined;
					oArrow = undefined;
				});
				break;
			case "polygon":
				aPts = [[x,y],[x,y]];
				var oLines = document.createElementNS(SVG, "polyline");
				setPolyPoints(oLines, aPts);
				$svg.appendChild(oLines);
				var oPoint = document.createElementNS(SVG, "circle");
				oPoint.setAttribute("class", "vertex first");
				oPoint.setAttribute("cx", x);
				oPoint.setAttribute("cy", y);
				oPoint.onclick = function(e) {
					e.stopPropagation();
					if (aPts.length > 3) {
						aPts.pop();
						for (var i=0;i<oPoly.pts.length;i++)
							$svg.removeChild(oPoly.pts[i]);
						$svg.removeChild(oPoly.lines);
						var oGon = document.createElementNS(SVG, "polygon");
						applyPoly(aPts,oGon);
					}
				}
				$svg.appendChild(oPoint);
				oPoly = {
					pts: [oPoint],
					lines: oLines
				};
				document.onmousemove = function(e2) {
					var pos2 = getRelativePos(e2,editor);
					var x2 = pos2.x, y2 = pos2.y;
					var aPt = aPts[aPts.length-1];
					aPt[0] = x2;
					aPt[1] = y2;
					setPolyPoints(oLines, aPts);
				};
				pushUndo(function() {
					$svg.removeChild(oPoint);
					$svg.removeChild(oPoly.lines);
					oPoly = null;
					document.onmousemove = undefined;
				});
				break;
			}
		}
	};
	function setPolyPoints($poly, pts) {
		$poly.setAttribute("points", pts.map(function(xy) { return xy.toString(); }).join(" "));
	}
	document.onkeydown = function(e) {
		if (e.keyCode == 90 && e.ctrlKey)
			undo();
	}
	window.onbeforeunload = function() {
		var oShapes = document.getElementsByClassName("shape");
		if (oShapes.length && (firstUndo != undo))
			return language ? "Caution, if you want to save the zone, click on \"<?php echo $submitTitle; ?>\" before":"Attention, si vous voulez sauvegarder la zone, cliquez sur \"<?php echo $submitTitle; ?>\" avant";
	};
	var params = window.opener.loadZoneData(editorType);
	var data = params.data;
	if (!data)
		data = [];
	else if (data.length === undefined)
		data = [data];
	var meta = params.meta;
	if (meta.custom_decors) {
		var customDecors = JSON.parse(meta.custom_decors);
		for (var key in customDecors) {
			if (!editorContext.customDecors[key]) {
				var loadCbs = [];
				editorContext.customDecors[key] = {
					id: customDecors[key].id,
					type: customDecors[key].type,
					onload: function(cb) {
						loadCbs.push(cb);
					}
				};
				xhr("getDecorData.php?id="+customDecors[key].id, "", function(res) {
					res = feedCustomDecorData(res);
					for (var i=0;i<loadCbs.length;i++)
						loadCbs[i](res);
					return true;
				});
			}
		}
	}
	for (var i=0;i<data.length;i++) {
		var iData = data[i];
		switch (getShapeType(iData)) {
		case "rectangle":
			applyRect(iData[0],iData[1],iData[2],iData[3]);
			break;
		case "point":
			applyPoint(iData[0],iData[1]);
			break;
		case "img":
			applyImg(iData.pos[0],iData.pos[1], iData.src);
			break;
		case "polygon":
			applyPoly(iData);
			break;
		case "arrow":
			var x1 = iData.pos[0], y1 = iData.pos[1];
			var x2 = x1 + Math.cos(iData.angle), y2 = y1 + Math.sin(iData.angle);
			applyArrow(x1,y1,x2,y2);
		}
	}
	if (meta.ordered == 1)
		document.getElementById("zone-editor-ordered").click();
	if (meta.extra_decors && (editorType !== "decors")) {
		var decors = meta.extra_decors;
		for (var i=decors.length-1;i>=0;i--) {
			var iData = decors[i];
			var oImg = document.createElementNS(SVG, "image");
			setupImg(iData.pos[0],iData.pos[1], iData.src, oImg);
			$svg.insertBefore(oImg, $svg.firstChild);
			delete oImg.dataset.data;
		}
	}
});
function showCollabImportPopup(e) {
	var $collabPopup = document.getElementById("collab-popup");
	$collabPopup.dataset.state = "open";

	closeCollabImportPopup = function() {
		document.removeEventListener("keydown", hideOnEscape);
		delete $collabPopup.dataset.state;
	}
	function hideOnEscape(e) {
		switch (e.keyCode) {
		case 27:
			closeCollabImportPopup();
		}
	}
	document.addEventListener("keydown", hideOnEscape);
	$collabPopup.querySelector('input[name="collablink"]').focus();
}
function importCollabDecor(e) {
	e.preventDefault();
	var $form = e.target;
	var url = $form.elements["collablink"].value;
	var urlParams = new URLSearchParams(new URL(url).search);
	var creationId, creationType, creationKey, creationMode;
	try {
		creationId = urlParams.get('id');
		creationKey = urlParams.get('collab');
	}
	catch (e) {
	}
	if (!creationKey) {
		alert(language ? "Invalid URL" : "URL invalide");
		return;
	}
	var $collabPopup = document.getElementById("collab-popup");
	$collabPopup.dataset.state = "loading";
	xhr("importCollabDecor.php", "type=mkdecors&id="+creationId+"&collab="+creationKey, function(res) {
		if (!res) {
			alert(language ? "Invalid link" : "Lien invalide");
			$collabPopup.dataset.state = "open";
			return true;
		}

		res = feedCustomDecorData(res);
		var customId = "custom-"+ res.id;
		var $btn = document.querySelector("#zone-editor-custom-decors [data-custom-id='"+ customId +"']");
		if ($btn) selectDecor($btn, customId);

		closeCollabImportPopup();
		$form.reset();
		return true;
	});
}
function feedCustomDecorData(res) {
	res = JSON.parse(res);

	var customId = "custom-"+ res.id;
	var customDecor = editorContext.customDecors[customId];
	if (!customDecor || customDecor.onload) {
		if (!res.type && customDecor)
			res.type = customDecor.type;
		var $btn = document.createElement("input");
		$btn.type = "button";
		$btn.style.backgroundImage = "url('"+ res.map +"')";
		$btn.title = res.name;
		$btn.dataset.customId = customId;
		$btn.onclick = function() {
			selectDecor($btn, customId);
		};
		document.getElementById("zone-editor-custom-decors").insertBefore($btn, document.getElementById("decor-selector-more"));
		editorContext.customDecors[customId] = res;
	}

	return res;
}
window.onload = function() {
	document.getElementById("editor-ctn").style.width = Math.max(200,document.getElementById("editor").scrollWidth) +"px";
};
</script>
</head>
<body>
	<div id="editor-ctn">
		<div class="challenge-explain"><?php
		switch ($type) {
		case 'zones':
			if ($language) {
				?>
				Indicate the zones in the circuit with one or more shapes.
				You have 2 options for the shapes: rectangle or polygon.<br />
				To draw a rectangle, click on the map, move the mouse and click again.
				To draw a polygon, click on the map for each point, and click on the first point to close it.
				To delete a shape, right click on it.<br />
				<?php
			}
			else {
				?>
				Indiquez les zones du circuit avec une ou plusieurs formes.
				Vous avez 2 options pour les formes: rectangle ou polygone.<br />
				Pour dessiner un rectangle, cliquez sur la carte, déplacez la souris et cliquez de nouveau.
				Pour dessiner un polygone, cliquez sur la carte pour chaque point, puis sur le premier point pour le fermer.
				Pour supprimer une forme, faites un clic droit dessus.<br />
				<?php
			}
			break;
		case 'coins':
			if ($language) {
				?>
				Indicate coins locations by clicking where you want on the circuit image.
				To delete a coin, right click on it.<br />
				<?php
			}
			else {
				?>
				Indiquez les emplacements des pièces en cliquant où vous voulez sur l'image du circuit.<br />
				Pour supprimer une pièce, faites un clic droit dessus.<br />
				<?php
			}
			break;
		case 'items':
			if ($language) {
				?>
				Indicate items locations by clicking where you want on the circuit image.
				To delete an item, right click on it.<br />
				<?php
			}
			else {
				?>
				Indiquez les emplacements des objets en cliquant où vous voulez sur l'image du circuit.<br />
				Pour supprimer un objet, faites un clic droit dessus.<br />
				<?php
			}
			break;
		case 'decors':
			if ($language) {
				?>
				Indicate decor locations by clicking where you want on the circuit image.
				To delete a decor, right click on it.<br />
				<?php
			}
			else {
				?>
				Indiquez les emplacements des décors en cliquant où vous voulez sur l'image du circuit.<br />
				Pour supprimer un décor, faites un clic droit dessus.<br />
				<?php
			}
			break;
		case 'startpos':
			if ($language) {
				?>
				Indicate the location in the circuit where the player will start.<br />
                You have to indicate 2 information: the initial position and the orientation of the player.<br />
				<?php
			}
			else {
				?>
				Indiquez la position de départ du joueur dans le circuit.<br />
                Vous devez indiquer 2 informations: la position initiale et l'orientation du joueur.<br />
				<?php
			}
			break;
		default:
			if ($language) {
				?>
				Indicate the zone in the circuit with one or more shapes.
				You have 2 options for the shapes: rectangle or polygon.<br />
				To draw a rectangle, click on the map, move the mouse and click again.
				To draw a polygon, click on the map for each point, and click on the first point to close it.
				To delete a shape, right click on it.<br />
				<?php
			}
			else {
				?>
				Indiquez la zones du circuit avec une ou plusieurs formes.
				Vous avez 2 options pour les formes: rectangle ou polygone.<br />
				Pour dessiner un rectangle, cliquez sur la carte, déplacez la souris et cliquez de nouveau.
				Pour dessiner un polygone, cliquez sur la carte pour chaque point, puis sur le premier point pour le fermer.
				Pour supprimer une forme, faites un clic droit dessus.<br />
				<?php
			}
			break;
		}
		echo $language ? 'When you finished, click on &quot;'. $submitTitle .'&quot; below.':'Quand vous avez fini, cliquez sur &quot;'. $submitTitle .'&quot; en bas.';
		?>
		<hr />
		<div id="zone-editor-options">
			<?php
			switch ($type) {
			case 'decors':
				break;
			default:
				?>
			<div id="zone-editor-theme">
				<span><?php echo $language ? 'Theme:':'Thème :'; ?></span>
				<input type="button" style="background-color:white" class="selected" onclick="selectTheme(this,false)" />
				<input type="button" style="background-color:black" onclick="selectTheme(this,true)" />
			</div>
				<?php
			}
			?>
			<?php
			switch ($type) {
			case 'coins':
			case 'items':
			case 'startpos':
				break;
			case 'decors':
				?>
				<div class="zone-editor-decor">
					<span><?php echo $language ? 'Decor:':'Décor :'; ?></span>
					<?php
					include('../includes/circuitDecors.php');
					foreach ($decors as &$decorGroup)
						unset($decorGroup['truck']);
					unset($decorGroup);
					foreach ($decors as &$decorGroup) {
						foreach ($decorGroup as $key => $name) {
							?>
							<input type="button" style="background-image:url('images/map_icons/<?php echo $key; ?>.png')"<?php if ($name) echo ' title="'.$name.'"'; ?> onclick="selectDecor(this, '<?php echo $key; ?>')" />
							<?php
						}
					}
					?>
				</div>
				<div class="zone-editor-decor" id="zone-editor-custom-decors">
				<?php
				foreach ($myDecors as $decor) {
					$name = htmlspecialchars($decor['name']);
					?>
					<input type="button" style="background-image:url('<?php echo $decor['map']; ?>')"<?php if ($name) echo ' title="'.$name.'"'; ?> onclick="selectDecor(this, 'custom-<?php echo $decor['id']; ?>')" />
					<?php
				}
				?>
				<input type="button" id="decor-selector-more" title="<?php echo $language ? "Select decor of another member..." : "Sélectionner le décor d'un autre membre..."; ?>" onclick="showCollabImportPopup()" />
				</div>
				<?php
				break;
			default;
				?>
				<div id="zone-editor-shape">
					<span><?php echo $language ? 'Shape:':'Forme :'; ?></span>
					<input type="button" style="background-image:url('images/editor/rectangle.png')" class="selected" onclick="selectShape(this,'rectangle')" />
					<input type="button" style="background-image:url('images/editor/polygon.png')" onclick="selectShape(this,'polygon')" />
				</div>
				<?php
			break;
			}
			?>
			<?php
			switch ($type) {
			case 'zones':
				?>
				<div class="zone-editor-checkbox">
					<label><input type="checkbox" id="zone-editor-ordered" onclick="selectOrdered(this,this.checked)" /> <?php echo $language ? 'Zones have to be passed in the right order <a class="pretty-link" href="javascript:showOrderHelp()">[?]</a>':'Les zones doivent être passées dans l\'ordre <a class="pretty-link" href="javascript:showOrderHelp()">[?]</a>'; ?></label>
				</div>
				<?php
			break;
			}
			?>
		</div>
		</div>
		<br />
		<div id="editor">
			<img src="<?php echo $circuitUrl; ?>" alt="Circuit" />
			<svg id="editor-svg"></svg>
		</div>
		<div id="apply">
			<button type="button" class="main-challenge-action" onclick="validateZone()"><?php echo $submitTitle; ?></button>
		</div>
	</div>
	<?php
	switch ($type) {
	case 'decors':
		?>
		<div id="collab-popup" onclick="closeCollabImportPopup()">
			<div class="popup-content" onclick="event.stopPropagation()">
				<h2><?php echo $language ? "Import a decor of another member" : "Importer le décor d'un autre membre"; ?></h2>
				<div>
				<?php
				if ($language) {
					?>
					Enter the decors's collaboration link here.<br />
					To get this link, the decor owner will simply need
					to click on &quot;Collaborate&quot; on the decors editor page.
					<?php
				}
				else {
					?>
					Saisissez ici le lien de collaboration de décor.<br />
					Pour obtenir ce lien, le propriétaire du décor devra simplement
					cliquer sur &quot;Collaborer&quot; dans la page d'édition des décors.
					<?php
				}
				?>
				</div>
				<form onsubmit="importCollabDecor(event)">
					<input type="url" name="collablink" placeholder="<?php
					require_once('../includes/collabUtils.php');
					$placeholderType = 'mkdecors';
					$placeholderId = 1;
					$collab = array(
						'type' => $placeholderType,
						'creation_id' => $placeholderId,
						'secret' => 'y-vf-erny_2401_pbasvezrq'
					);
					echo getCollabUrl($collab);
					?>" required="required" />
					<input type="submit" value="Ok" />
				</form>
			</div>
		</div>
		<?php
	}
	?>
</body>
</html>
<?php
mysql_close();
?>