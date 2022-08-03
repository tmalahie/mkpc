var offsetX = 0, offsetY = 0;
var $editor, $editorCtn, $toolbox;
var SVG = "http://www.w3.org/2000/svg";
var initFancyTitle;
document.addEventListener("DOMContentLoaded", function() {
	var $radioSelectors = document.querySelectorAll(".radio-selector");
	for (var i=0;i<$radioSelectors.length;i++) {
		var $radioSelector = $radioSelectors[i];
		$radioSelector.getValue = function() {
			return this._value;
		};
		$radioSelector.setValue = function(value) {
			var $radioButtons = this.querySelectorAll(".radio-button");
			for (var j=0;j<$radioButtons.length;j++) {
				var $radioButton = $radioButtons[j];
				if ($radioButton.value == value)
					$radioButton.classList.add("radio-selected");
				else
					$radioButton.classList.remove("radio-selected");
			}
			this._value = value;
		};
		var $radioButtons = $radioSelector.querySelectorAll(".radio-button");
		for (var j=0;j<$radioButtons.length;j++)
			initRadioButton($radioButtons[j],$radioSelector);
	}
	var $fancyTitles = document.querySelectorAll(".fancy-title");
	var $titleElt;
	initFancyTitle = function($fancyTitle) {
		var aTitle = $fancyTitle.title;
		$fancyTitle.title = "";
		$fancyTitle.onmouseover = function() {
			if ($titleElt)
				document.body.removeChild($titleElt);
			$titleElt = document.createElement("div");
			$titleElt.className = "fancy-title-text";
			$titleElt.innerHTML = aTitle;
			document.body.appendChild($titleElt);
			var eltBounds = $fancyTitle.getBoundingClientRect();
			var titleBounds = $titleElt.getBoundingClientRect();
			if ($fancyTitle.classList.contains("fancy-title-center"))
				$titleElt.style.left = Math.round(eltBounds.left+(eltBounds.width-titleBounds.width)/2) +"px";
			else
				$titleElt.style.left = Math.round(eltBounds.left+eltBounds.width-titleBounds.width) +"px";
			$titleElt.style.top = Math.round(eltBounds.top-titleBounds.height-2) +"px";
		};
		$fancyTitle.onmouseout = function() {
			if ($titleElt) {
				document.body.removeChild($titleElt);
				$titleElt = undefined;
			}
		};
	}
	for (var i=0;i<$fancyTitles.length;i++)
		initFancyTitle($fancyTitles[i]);
	var $circuitCreated = document.getElementById("circuit-created");
	if ($circuitCreated) {
		$circuitCreated.close = function() {
			document.body.removeChild($circuitCreated);
		};
	}
	$editor = document.getElementById("editor");
	$editorCtn = document.getElementById("editor-ctn");
	$toolbox = document.getElementById("toolbox");
	offsetX = $editorCtn.offsetLeft;
	offsetY = $editorCtn.offsetTop;
	for (var key in editorTools) {
		var editorTool = editorTools[key];
		editorTool.data = [];
		editorTool.state = {};
		if (editorTool.init)
			editorTool.init(editorTool);
	}
	if (circuitData)
		restoreData(circuitData);
	//document.getElementById('mode').value = "decor";
	selectMode(document.getElementById('mode').value);
	//document.getElementById("decor-selector").setValue("truck");
	//decorChange();
});
function initRadioButton($radioButton,$radioSelector) {
	$radioButton.selector = $radioSelector;
	$radioButton.onclick = function() {
		var $selectedButton = this.selector.querySelector(".radio-selected");
		if ($selectedButton == this) return;
		if ($selectedButton) $selectedButton.classList.remove("radio-selected");
		this.classList.add("radio-selected");
		var changeCallback = this.selector.getAttribute("data-change");
		this.selector._value = this.value;
		if (changeCallback && window[changeCallback])
			window[changeCallback]({target:this,value:this.value});
	};
	if ($radioButton.classList.contains("radio-selected"))
		$radioSelector._value = $radioButton.value;
}
var zoomLevel = 1;
var zoomLevels = [0.25,0.3,0.4,0.5,0.6,0.75,0.9,1,1.1,1.25,1.5,2,3,4,6,8,10,15];
function zoomMore(focusOnMouse) {
	var zoomId = zoomLevels.indexOf(zoomLevel);
	if (zoomId < zoomLevels.length-1)
		updateZoom(zoomLevels[zoomId+1],focusOnMouse);
}
function zoomLess(focusOnMouse) {
	var zoomId = zoomLevels.indexOf(zoomLevel);
	if (zoomId > 0)
		updateZoom(zoomLevels[zoomId-1],focusOnMouse);
}
var changes = false;
var historyData = [], historyUndo = [];
var MAX_HISTORY_SIZE = 10;
function undo() {
	var editorTool = editorTools[currentMode];
	if (editorTool.onundo)
		editorTool.onundo();
	else {
		$toolbox.classList.remove("hiddenbox");
		var lastData = historyData.pop();
		if (lastData) {
			var nextHistory = historyData;
			var nextUndo = historyUndo;
			nextUndo.push(editorTools[currentMode].data);
			if (nextUndo.length > MAX_HISTORY_SIZE)
				nextUndo.shift();
			editorTools[currentMode].data = lastData;
			selectMode(currentMode);
			if (nextHistory.length)
				historyData = nextHistory;
			historyUndo = nextUndo;
		}
		changes = true;
	}
}
function redo() {
	var editorTool = editorTools[currentMode];
	if (editorTool.onredo)
		editorTool.onredo();
	else {
		var lastData = historyUndo.pop();
		if (lastData) {
			var nextHistory = historyData;
			var nextUndo = historyUndo;
			nextHistory.push(editorTools[currentMode].data);
			if (nextHistory.length > MAX_HISTORY_SIZE)
				nextHistory.shift();
			editorTools[currentMode].data = lastData;
			selectMode(currentMode);
			if (nextUndo.length)
				historyUndo = nextUndo;
			historyData = nextHistory;
		}
	}
}
if (typeof Array.isArray === 'undefined') {
	Array.isArray = function(obj) {
		return Object.prototype.toString.call(obj) === '[object Array]';
	}
};
function deepCopy(obj) {
	if (Array.isArray(obj)) {
		var clone = [];
		for (var i=0;i<obj.length;i++)
			clone.push(deepCopy(obj[i]));
		return clone;
	}
	else if (("object" === typeof(obj)) && (obj != null)) {
		var clone = {};
		for (var i in obj)
			clone[i] = deepCopy(obj[i]);
		return clone;
	}
	else
		return obj;
}
function storeHistoryData(data) {
	changes = true;
	historyData.push(deepCopy(data));
	if (historyData.length > MAX_HISTORY_SIZE)
		historyData.shift();
	historyUndo.length = 0;
}
function resizeRectangle(rectangle,data,options) {
	options = options||{};
	var mask = createMask();
	mask.classList.add("mask-dark");
	var screenCoords = getScreenCoords(data);
	var bubbleR = 10;
	var centerX = screenCoords.x + screenCoords.w/2;
	var centerY = screenCoords.y + screenCoords.h/2;
	var bubbles = new Array();
	var l = (data.h!=15);
	for (var x=-1;x<=1;x++) {
		for (var y=-1;y<=1;y++) {
			if (options.cp ? (l?y:x) : (x||y)) {
				(function(x,y) {
					var bubble = createBubble(
						centerX + x*screenCoords.w/2 + (1+x)*(zoomLevel-1)/2,
						centerY + y*screenCoords.h/2 + (1+y)*(zoomLevel-1)/2,
						bubbleR
					);
					bubble.onclick = function(e) {
						e.stopPropagation();
						for (var i=0;i<bubbles.length;i++)
							bubbles[i].style.display = "none";
						var aX = e.pageX, aY = e.pageY;
						var nData = deepCopy(data);
						mask.classList.remove("mask-dark");
						function resizeRect(e) {
							var nX = e.pageX, nY = e.pageY;
							var diffX = Math.round((nX-aX)/zoomLevel);
							var diffY = Math.round((nY-aY)/zoomLevel);
							if (x == -1) {
								nData.x = data.x + diffX;
								nData.w = data.w - diffX;
							}
							else if (x == 1) {
								nData.x = data.x;
								nData.w = data.w + diffX;
							}
							if (y == -1) {
								nData.y = data.y + diffY;
								nData.h = data.h  - diffY;
							}
							else if (y == 1) {
								nData.y = data.y;
								nData.h = data.h + diffY;
							}
							if (options.cp && x && y) {
								var point1 = {x:nData.x,y:nData.y}, point2 = {x:nData.x+nData.w,y:nData.y+nData.h};
								if (x == -1) {
									var pX = point1.x;
									point1.x = point2.x;
									point2.x = pX;
								}
								if (y == -1) {
									var pY = point1.y;
									point1.y = point2.y;
									point2.y = pY;
								}
								nData = getRectDataCp(point1,point2);
								if (data.theta)
									nData.theta = data.theta;
							}
							absolutizeData(nData, options);
							setRectangleBounds(rectangle,nData);
							if (options.cp)
								rectangle.reposition(nData);
						}
						function stopResizeRect(e) {
							e.stopPropagation();
							$toolbox.classList.remove("hiddenbox");
							resizeRect(e);
							storeHistoryData(editorTools[currentMode].data);
							var apply;
							if (options.on_end_move)
								options.on_end_move();
							if (options.on_apply)
								apply = options.on_apply(nData);
							if (options.on_exit)
								options.on_exit(nData);
							if (false !== apply)
								applyObject(data,nData);
							mask.removeEventListener("mousemove", resizeRect);
							mask.removeEventListener("mouseup", stopResizeRect);
							mask.defaultClose();
						}
						mask.addEventListener("mousemove", resizeRect);
						mask.addEventListener("mouseup", stopResizeRect);
						mask.close = function(){};
						$toolbox.classList.add("hiddenbox");
						if (options.on_start_move)
							options.on_start_move();
					};
					mask.appendChild(bubble);
					bubbles.push(bubble);
				})(x,y);
			}
		}
	}
	if (options.on_exit) {
		mask.close = function(){
			options.on_exit(data);
			mask.defaultClose();
		};
	}
}
function createBubble(bubbleX,bubbleY, bubbleR) {
	var bubble = document.createElement("div");
	bubble.style.left = Math.round(bubbleX - bubbleR/2) +"px";
	bubble.style.top = Math.round(bubbleY - bubbleR/2) +"px";
	bubble.style.width = (bubbleR-2) +"px";
	bubble.style.height = (bubbleR-2) +"px";
	bubble.className = "bubble";
	bubble.style.borderRadius = bubbleR +"px";
	bubble.style.cursor = "pointer";
	bubble.className = "bubble hover-toggle";
	return bubble;
}
function moveRectangle(rectangle,data,options) {
	options = options||{};
	var mask = createMask();
	mask.classList.add("mask-dark");
	var screenCoords = getScreenCoords(data);
	var fakeRectangle = document.createElement("div");
	fakeRectangle.style.left = screenCoords.x +"px";
	fakeRectangle.style.top = screenCoords.y +"px";
	fakeRectangle.style.width = (screenCoords.w+zoomLevel) +"px";
	fakeRectangle.style.height = (screenCoords.h+zoomLevel) +"px";
	if (options.cp && data.theta) {
		fakeRectangle.style.transform = "rotate("+data.theta+"rad)";
		fakeRectangle.style.transformOrigin = "center center";
	}
	fakeRectangle.style.cursor = "move";
	fakeRectangle.onclick = function(e) {
		e.stopPropagation();
		var aX = e.pageX, aY = e.pageY;
		var nData = deepCopy(data);
		mask.classList.remove("mask-dark");
		function moveRect(e) {
			var nX = e.pageX, nY = e.pageY;
			var diffX = Math.round((nX-aX)/zoomLevel);
			var diffY = Math.round((nY-aY)/zoomLevel);
			nData.x = data.x + diffX;
			nData.y = data.y + diffY;
			capRectangle(nData, options);
			setRectangleBounds(rectangle,nData);
			if (options.cp)
				rectangle.reposition(nData);
		}
		function stopMoveRect(e) {
			e.stopPropagation();
			$toolbox.classList.remove("hiddenbox");
			moveRect(e);
			storeHistoryData(editorTools[currentMode].data);
			if (options.on_end_move)
				options.on_end_move();
			var apply;
			if (options.on_apply)
				apply = options.on_apply(nData);
			if (false !== apply)
				applyObject(data,nData);
			mask.removeEventListener("mousemove", moveRect);
			mask.removeEventListener("mouseup", stopMoveRect);
			mask.defaultClose();
		}
		mask.addEventListener("mousemove", moveRect);
		mask.addEventListener("mouseup", stopMoveRect);
		mask.close = function(){};
		$toolbox.classList.add("hiddenbox");
		if (options.on_start_move)
			options.on_start_move();
	};
	mask.appendChild(fakeRectangle);
}
function moveCircle(circle,data,options) {
	options = options||{};
	var mask = createMask();
	mask.classList.add("mask-dark");
	var screenCoords = getScreenCoords({x:data.x-data.r,y:data.y-data.r,w:data.r*2,h:data.r*2});
	var fakeRectangle = document.createElement("div");
	fakeRectangle.style.left = screenCoords.x +"px";
	fakeRectangle.style.top = screenCoords.y +"px";
	fakeRectangle.style.width = screenCoords.w +"px";
	fakeRectangle.style.height = screenCoords.h +"px";
	fakeRectangle.style.cursor = "move";
	fakeRectangle.onclick = function(e) {
		e.stopPropagation();
		var aX = e.pageX, aY = e.pageY;
		var nData = deepCopy(data);
		mask.classList.remove("mask-dark");
		function moveCirc(e) {
			var nX = e.pageX, nY = e.pageY;
			var diffX = Math.round((nX-aX)/zoomLevel);
			var diffY = Math.round((nY-aY)/zoomLevel);
			nData.x = data.x + diffX;
			nData.y = data.y + diffY;
			capCircle(nData);
			setCircleBounds(circle,nData);
		}
		function stopMoveCirc(e) {
			e.stopPropagation();
			$toolbox.classList.remove("hiddenbox");
			moveCirc(e);
			storeHistoryData(editorTools[currentMode].data);
			if (options.on_end_move)
				options.on_end_move();
			var apply;
			if (options.on_apply)
				apply = options.on_apply(nData);
			if (false !== apply)
				applyObject(data,nData);
			mask.removeEventListener("mousemove", moveCirc);
			mask.removeEventListener("mouseup", stopMoveCirc);
			mask.defaultClose();
		}
		mask.addEventListener("mousemove", moveCirc);
		mask.addEventListener("mouseup", stopMoveCirc);
		mask.close = function(){};
		$toolbox.classList.add("hiddenbox");
		if (options.on_start_move)
			options.on_start_move();
	};
	mask.appendChild(fakeRectangle);
}
function editCircle(circle,data,options) {
	options = options||{};
	var mask = createMask();
	mask.classList.add("mask-dark");
	var nData = deepCopy(data);
	mask.classList.remove("mask-dark");
	function moveCirc(e) {
		var center = getEditorCoords({x:e.pageX,y:e.pageY});
		var diffX = center.x-data.x;
		var diffY = center.y-data.y;
		nData.r = Math.round(Math.hypot(diffX,diffY));
		circle.setAttribute("r", nData.r);
	}
	function stopMoveCirc(e) {
		e.stopPropagation();
		$toolbox.classList.remove("hiddenbox");
		moveCirc(e);
		storeHistoryData(editorTools[currentMode].data);
		var apply;
		if (options.on_apply)
			apply = options.on_apply(nData);
		if (false !== apply)
			applyObject(data,nData);
		mask.removeEventListener("mousemove", moveCirc);
		mask.removeEventListener("mouseup", stopMoveCirc);
		mask.defaultClose();
	}
	mask.addEventListener("mousemove", moveCirc);
	mask.addEventListener("mouseup", stopMoveCirc);
	mask.close = function(){};
	$toolbox.classList.add("hiddenbox");
}
function moveBox(box,data,size,options) {
	options = options||{};
	var mask = createMask();
	mask.classList.add("mask-dark");
	var rectData = {x:data.x-size.w/2,y:data.y-size.h/2,w:size.w,h:size.h};
	var screenCoords = getScreenCoords(rectData);
	var fakeRectangle = document.createElement("div");
	fakeRectangle.style.left = screenCoords.x +"px";
	fakeRectangle.style.top = screenCoords.y +"px";
	fakeRectangle.style.width = screenCoords.w +"px";
	fakeRectangle.style.height = screenCoords.h +"px";
	fakeRectangle.style.cursor = "move";
	fakeRectangle.onclick = function(e) {
		e.stopPropagation();
		var aX = e.pageX, aY = e.pageY;
		var nData = deepCopy(data);
		mask.classList.remove("mask-dark");
		function moveRect(e) {
			var nX = e.pageX, nY = e.pageY;
			var diffX = Math.round((nX-aX)/zoomLevel);
			var diffY = Math.round((nY-aY)/zoomLevel);
			nData.x = data.x + diffX;
			nData.y = data.y + diffY;
			capBox(nData);
			setBoxPos(box,nData,size);
		}
		function stopMoveRect(e) {
			e.stopPropagation();
			$toolbox.classList.remove("hiddenbox");
			moveRect(e);
			storeHistoryData(editorTools[currentMode].data);
			if (options.on_end_move)
				options.on_end_move();
			var apply;
			if (options.on_apply)
				apply = options.on_apply(nData);
			if (false !== apply)
				applyObject(data,nData);
			mask.removeEventListener("mousemove", moveRect);
			mask.removeEventListener("mouseup", stopMoveRect);
			mask.defaultClose();
		}
		mask.addEventListener("mousemove", moveRect);
		mask.addEventListener("mouseup", stopMoveRect);
		mask.close = function(){};
		$toolbox.classList.add("hiddenbox");
		if (options.on_start_move)
			options.on_start_move();
	};
	mask.appendChild(fakeRectangle);
}
function moveNode(node,data,lines) {
	var mask = createMask();
	mask.classList.add("mask-dark");
	function movePoint(e) {
		var nX = e.pageX, nY = e.pageY;
		var nPoint = getEditorCoordsRounded({x:nX,y:nY});
		setPointPos(node.center,nPoint);
		setCirclePos(node.circle,nPoint);
		if (lines) {
			for (var i=0;i<lines[0].length;i++)
				moveLine(lines[0][i],nPoint,null);
			for (var i=0;i<lines[1].length;i++)
				moveLine(lines[1][i],null,nPoint);
		}
	}
	function stopMovePoint(e) {
		e.stopPropagation();
		$toolbox.classList.remove("hiddenbox");
		movePoint(e);
		var nX = e.pageX, nY = e.pageY;
		var nPoint = getEditorCoordsRounded({x:nX,y:nY});
		var editorTool = editorTools[currentMode];
		storeHistoryData(editorTool.data);
		applyObject(data,nPoint);
		mask.removeEventListener("mousemove", movePoint);
		mask.removeEventListener("mouseup", stopMovePoint);
		mask.defaultClose();
	}
	mask.addEventListener("mousemove", movePoint);
	mask.addEventListener("mouseup", stopMovePoint);
	$toolbox.classList.add("hiddenbox");
}
function editPolygon(polygon,data,options) {
	options = options||{};
	var mask = createMask();
	mask.classList.add("mask-dark");
	var fakeSvg = document.createElementNS(SVG, "svg");
	fakeSvg.setAttribute("class", "editor");
	for (var i=0;i<data.points.length;i++) {
		(function(i) {
			var fakeLine = document.createElementNS(SVG, "line");
			fakeLine.classList.add("bordered");
			fakeLine.classList.add("hover-toggle");
			fakeLine.style.strokeOpacity = 1;
			fakeLine.style.cursor = "pointer";
			fakeLine.setAttribute("stroke-width", 6);
			var fakePoint1 = getScreenCoords(data.points[i]), fakePoint2 = getScreenCoords(data.points[(i+1)%data.points.length]);
			fakeLine.setAttribute("x1", fakePoint1.x);
			fakeLine.setAttribute("y1", fakePoint1.y);
			fakeLine.setAttribute("x2", fakePoint2.x);
			fakeLine.setAttribute("y2", fakePoint2.y);
			fakeLine.onclick = function(e) {
				fakeSvg.style.display = "none";
				var editorTool = editorTools[currentMode];
				editorTool.state.point.classList.add("dark");
				mask.classList.remove("mask-dark");
				var aX = e.pageX, aY = e.pageY;
				var aPoint = getEditorCoordsRounded({x:aX,y:aY});
				var nPoints = deepCopy(data.points);
				nPoints.splice(i+1,0, aPoint);
				setPolygonPoints(polygon,nPoints,true);
				setNodePos(editorTool.state.point,aPoint);
				function movePoint(e) {
					var nX = e.pageX, nY = e.pageY;
					var nPoint = getEditorCoordsRounded({x:nX,y:nY});
					applyObject(aPoint,nPoint);
					setPolygonPoints(polygon,nPoints,true);
					setNodePos(editorTool.state.point,nPoint);
				}
				function stopMovePoint(e) {
					e.stopPropagation();
					$toolbox.classList.remove("hiddenbox");
					movePoint(e);
					storeHistoryData(editorTool.data);
					var apply;
					if (options.on_apply)
						apply = options.on_apply(Object.assign({}, data, { points: nPoints }));
					if (false !== apply)
						data.points = nPoints;
					mask.removeEventListener("mousemove", movePoint);
					mask.removeEventListener("mouseup", stopMovePoint);
					mask.defaultClose();
					editorTool.state.point.classList.remove("dark");
				}
				mask.addEventListener("mousemove", movePoint);
				mask.addEventListener("mouseup", stopMovePoint);
				mask.close = function(){};
				$toolbox.classList.add("hiddenbox");
			};
			fakeSvg.appendChild(fakeLine);
		})(i);
	}
	for (var i=0;i<data.points.length;i++) {
		(function(i) {
			var fakeCenter = getScreenCoords({x:data.points[i].x,y:data.points[i].y,r:5,l:1});
			var fakePoint = createCircle(fakeCenter,false);
			fakePoint.classList.add("hover-toggle");
			fakePoint.style.cursor = "pointer";
			fakePoint.onclick = function(e) {
				fakeSvg.style.display = "none";
				var editorTool = editorTools[currentMode];
				editorTool.state.point.classList.add("dark");
				mask.classList.remove("mask-dark");
				var aX = e.pageX, aY = e.pageY;
				var aPoint = getEditorCoordsRounded({x:aX,y:aY});
				var nPoints = deepCopy(data.points);
				nPoints[i] = aPoint;
				setPolygonPoints(polygon,nPoints,true);
				setNodePos(editorTool.state.point,aPoint);
				function movePoint(e) {
					var nX = e.pageX, nY = e.pageY;
					var nPoint = getEditorCoordsRounded({x:nX,y:nY});
					applyObject(aPoint,nPoint);
					setPolygonPoints(polygon,nPoints,true);
					setNodePos(editorTool.state.point,nPoint);
				}
				function stopMovePoint(e) {
					e.stopPropagation();
					$toolbox.classList.remove("hiddenbox");
					movePoint(e);
					storeHistoryData(editorTool.data);
					var apply;
					if (options.on_apply)
						apply = options.on_apply(Object.assign({}, data, { points: nPoints }));
					if (false !== apply)
						data.points = nPoints;
					mask.removeEventListener("mousemove", movePoint);
					mask.removeEventListener("mouseup", stopMovePoint);
					mask.defaultClose();
					editorTool.state.point.classList.remove("dark");
				}
				mask.addEventListener("mousemove", movePoint);
				mask.addEventListener("mouseup", stopMovePoint);
				mask.close = function(){};
				$toolbox.classList.add("hiddenbox");
			};
			fakePoint.oncontextmenu = function(e) {
				e.stopPropagation();
				showContextMenu(e,[{
					text: (language ? "Move":"Déplacer"),
					click: function() {
						fakePoint.onclick(e);
					}
				}, {
					text: (language ? "Delete":"Supprimer"),
					click: function() {
						var editorTool = editorTools[currentMode];
						storeHistoryData(editorTool.data);
						var nPoints = deepCopy(data.points);
						nPoints.splice(i,1);
						var apply;
						if (options.on_apply)
							apply = options.on_apply(Object.assign({}, data, { points: nPoints }));
						if (false !== apply)
							data.points = nPoints;
						setPolygonPoints(polygon,nPoints,true);
						mask.defaultClose();
					},
					disabled: (data.points.length <= 3)
				}]);
				return false;
			};
			fakeSvg.appendChild(fakePoint);
		})(i);
	}
	mask.appendChild(fakeSvg);
}
function movePolygon(polygon,data,options) {
	options = options||{};
	var mask = createMask();
	mask.classList.add("mask-dark");
	var screenCoords = getScreenCoordsExact(data);
	var minX = data.points[0].x, maxX = minX, minY = data.points[0].y, maxY = minY;
	for (var i=1;i<data.points.length;i++) {
		var iData = data.points[i];
		if (iData.x < minX)
			minX = iData.x;
		else if (iData.x > maxX)
			maxX = iData.x;
		if (iData.y < minY)
			minY = iData.y;
		else if (iData.y > maxY)
			maxY = iData.y;
	}
	var rData = {x:minX,y:minY, w:(maxX-minX), h:(maxY-minY)};
	var screenCoords = getScreenCoords(rData);
	var fakeRectangle = document.createElement("div");
	fakeRectangle.style.left = screenCoords.x +"px";
	fakeRectangle.style.top = screenCoords.y +"px";
	fakeRectangle.style.width = screenCoords.w +"px";
	fakeRectangle.style.height = screenCoords.h +"px";
	fakeRectangle.style.cursor = "move";
	fakeRectangle.onclick = function(e) {
		e.stopPropagation();
		var aX = e.pageX, aY = e.pageY;
		var nData = deepCopy(rData);
		nData.w--;
		nData.h--;
		var nPoints = deepCopy(data.points);
		mask.classList.remove("mask-dark");
		function movePoly(e) {
			var nX = e.pageX, nY = e.pageY;
			var diffX = Math.round((nX-aX)/zoomLevel);
			var diffY = Math.round((nY-aY)/zoomLevel);
			nData.x = rData.x + diffX;
			nData.y = rData.y + diffY;
			capRectangle(nData);
			diffX = nData.x-rData.x;
			diffY = nData.y-rData.y;
			for (var i=0;i<data.points.length;i++) {
				nPoints[i].x = data.points[i].x + diffX;
				nPoints[i].y = data.points[i].y + diffY;
			}
			setPolygonPoints(polygon,nPoints,true);
		}
		function stopMovePoly(e) {
			e.stopPropagation();
			$toolbox.classList.remove("hiddenbox");
			movePoly(e);
			storeHistoryData(editorTools[currentMode].data);
			if (options.on_end_move)
				options.on_end_move();
			var apply;
			if (options.on_apply)
				apply = options.on_apply(Object.assign({}, data, { points: nPoints }));
			if (false !== apply)
				data.points = nPoints;
			mask.removeEventListener("mousemove", movePoly);
			mask.removeEventListener("mouseup", stopMovePoly);
			mask.defaultClose();
		}
		mask.addEventListener("mousemove", movePoly);
		mask.addEventListener("mouseup", stopMovePoly);
		mask.close = function(){};
		$toolbox.classList.add("hiddenbox");
		if (options.on_start_move)
			options.on_start_move();
	};
	mask.appendChild(fakeRectangle);
}
function moveArrow(arrow,origin,dir,options) {
	options = options||{};
	var mask = createMask();
	var point2 = {x:origin.x+dir.x,y:origin.y+dir.y};
	function moveArr(e) {
		point2 = getEditorCoordsRounded(getPointerPos(e));
		if (options.fixed_length)
			changeArrowDir(arrow,origin,{x:point2.x-origin.x,y:point2.y-origin.y},options.fixed_length,options.from_center);
		else
			arrow.move(null,point2);
	}
	function stopMoveArr(e) {
		e.stopPropagation();
		$toolbox.classList.remove("hiddenbox");
		moveArr(e);
		storeHistoryData(editorTools[currentMode].data);
		var nDir = {x:point2.x-origin.x,y:point2.y-origin.y};
		var apply;
		if (options.on_apply)
			apply = options.on_apply(nDir);
		if (false !== apply)
			applyObject(dir,nDir);
		mask.removeEventListener("mousemove", moveArr);
		mask.removeEventListener("mouseup", stopMoveArr);
		mask.defaultClose();
	}
	mask.addEventListener("mousemove", moveArr);
	mask.addEventListener("mouseup", stopMoveArr);
	mask.close = function(){};
	$toolbox.classList.add("hiddenbox");
}
function moveCircularArrow(arrow,center,dir,options) {
	options = options||{};
	var mask = createMask();
	var nAngle = dir.dtheta;
	function moveArr(e) {
		var point = getEditorCoordsRounded(getPointerPos(e));
		var newAngle = Math.atan2(point.y-center.y,point.x-center.x);
		nAngle = newAngle-center.theta0;
		nAngle -= 2*Math.PI*Math.round(nAngle/(2*Math.PI));
		arrow.move(null,null,nAngle);
	}
	function stopMoveArr(e) {
		e.stopPropagation();
		$toolbox.classList.remove("hiddenbox");
		moveArr(e);
		storeHistoryData(editorTools[currentMode].data);
		var apply;
		if (options.on_apply)
			apply = options.on_apply(nAngle);
		if (false !== apply)
			dir.dtheta = nAngle;
		mask.removeEventListener("mousemove", moveArr);
		mask.removeEventListener("mouseup", stopMoveArr);
		mask.defaultClose();
	}
	mask.addEventListener("mousemove", moveArr);
	mask.addEventListener("mouseup", stopMoveArr);
	mask.close = function(){};
	$toolbox.classList.add("hiddenbox");
}
function moveArrowNode(arrow,data) {
	var mask = createMask();
	var nData = data;
	function moveArr(e) {
		nData = getEditorCoordsRounded(getPointerPos(e));
		arrow.move(nData);
	}
	function stopMoveArr(e) {
		e.stopPropagation();
		$toolbox.classList.remove("hiddenbox");
		moveArr(e);
		storeHistoryData(editorTools[currentMode].data);
		applyObject(data,nData);
		mask.removeEventListener("mousemove", moveArr);
		mask.removeEventListener("mouseup", stopMoveArr);
		mask.defaultClose();
	}
	mask.addEventListener("mousemove", moveArr);
	mask.addEventListener("mouseup", stopMoveArr);
	mask.close = function(){};
	$toolbox.classList.add("hiddenbox");
}
function updateBoxSize(box,boxSize) {
	box.setAttribute("x", +box.getAttribute("x")+Math.round((box.getAttribute("width")-boxSize.w)/2));
	box.setAttribute("y", +box.getAttribute("y")+Math.round((box.getAttribute("height")-boxSize.h)/2));
	box.setAttribute("width", boxSize.w);
	box.setAttribute("height", boxSize.h);
}
function updateZoom(nLevel, focusOnMouse) {
	var lastZoom = zoomLevel;
	var relPos;
	if (focusOnMouse) {
		var windowPos = getEditorCoordsExact(getWindowPos());
		relPos = {x:mouseCoords.x-windowPos.x,y:mouseCoords.y-windowPos.y};
	}
	zoomLevel = nLevel;
	$editorCtn.style.transform = $editorCtn.style.WebkitTransform = $editorCtn.style.MozTransform = "scale("+ zoomLevel +")";
	var $zoomEventElts = document.getElementsByClassName("zoom-event");
	for (var i=0;i<$zoomEventElts.length;i++)
		$zoomEventElts[i].onzoom();
	document.getElementById("editor-wrapper").style.width = Math.round(zoomLevel*imgSize.w) +"px";
	document.getElementById("editor-wrapper").style.height = Math.round(zoomLevel*imgSize.h) +"px";
	document.getElementById("zoom-value").innerHTML = Math.round(zoomLevel*100);
	if (focusOnMouse) {
		var screenPos = getScreenCoordsExact(mouseCoords);
		var nPos = {x:screenPos.x-relPos.x*lastZoom,y:screenPos.y-relPos.y*lastZoom};
		window.scrollTo(Math.round(nPos.x),Math.round(nPos.y));
	}
}
function helpChange(e) {
	var $shownText = document.querySelector(".help-text-shown");
	if ($shownText)
		$shownText.classList.remove("help-text-shown");
	document.getElementById("help-text-"+e.value).classList.add("help-text-shown");
	document.getElementById("help-img").src = "images/editor/help-"+(isBattle ? "course":"draw")+"/help-"+e.value+".png";
}
function shapeChange(e) {
	var editorTool = editorTools[currentMode];
	editorTool.state.shape = e.value;
	replaceNodeType(editorTool);
}
function replaceNodeType(editorTool) {
	if (editorTool.state.point) {
		var lastPoint = editorTool.state.point;
		delete editorTool.state.point;
		switch (editorTool.state.shape) {
		case "polygon":
			editorTool.state.point = createCircle({x:-1,y:-1,r:0.5});
			break;
		default:
			editorTool.state.point = createRectangle({x:-1,y:-1});
			break;
		}
		if (editorTool.state.point) {
			editorTool.state.point.classList.add("noclick");
			$editor.insertBefore(editorTool.state.point,lastPoint);
			$editor.removeChild(lastPoint);
		}
	}
}
function decorChange(e) {
	selectMode(currentMode);
}
function trajectChange(value,key) {
	if (value != -1)
		selectMode(currentMode);
	else
		showTrajectOptions(key);
}
function currentTrajectChange(value) {
	editorTools[currentMode].state.currentTraject = +value;
	document.getElementById("decor-bus-traject").selectedIndex = +value;
}
function manageBusTrajects() {
	document.getElementById("decor-option-truck").classList.remove("decor-option-bus-decors");
	document.getElementById("decor-option-truck").classList.add("decor-option-bus-trajects");
	selectMode(currentMode);
}
function manageBusDecor() {
	document.getElementById("decor-option-truck").classList.remove("decor-option-bus-trajects");
	document.getElementById("decor-option-truck").classList.add("decor-option-bus-decors");
	selectMode(currentMode);
}
function offroadChange(value) {
	if (value != -1)
		selectMode(currentMode);
	else
		showOffroadTransfer();
}
function themeChange(e) {
	if (e.value == "dark")
		document.body.classList.add("theme-dark");
	else
		document.body.classList.remove("theme-dark");
}
var currentMode = "";
function selectMode(mode) {
	var editorTool = editorTools[currentMode];
	if (editorTool) {
		editorTool.state = {};
		editorTool.onundo = null;
		editorTool.onredo = null;
		if (editorTool.exit)
			editorTool.exit();
		removeAllChildren($editor);
	}
	historyData = [];
	historyUndo = [];
	var lastOption = document.getElementById("mode-option-"+currentMode);
	var nextOption = document.getElementById("mode-option-"+mode);
	currentMode = mode;
	if (lastOption) lastOption.className = "";
	if (nextOption) nextOption.className = "mode-option-selected";
	var editorTool = editorTools[currentMode];
	if (editorTool.resume) {
		var aChanges = changes;
		editorTool.resume(editorTool);
		changes = aChanges;
	}
	var $mode = document.getElementById('mode');
	var $modeOptions = $mode.getElementsByTagName("option");
	var modeId = +$mode.selectedIndex;
	document.getElementById("mode-decr").disabled = !$modeOptions[modeId-1];
	document.getElementById("mode-incr").disabled = !$modeOptions[modeId+1];
}
function navigateMode(inc) {
	var $mode = document.getElementById('mode');
	var $modeOptions = $mode.getElementsByTagName("option");
	var nextMode = +$mode.selectedIndex+inc;
	if ($modeOptions[nextMode]) {
		$mode.selectedIndex = nextMode;
		selectMode($mode.value);
	}
}
var mouseCoords = {x:0,y:0};
function handleClick(e) {
	var editorTool = editorTools[currentMode];
	if (editorTool.click) {
		var pointerCoords = getPointerPos(e);
		mouseCoords = getEditorCoordsExact(pointerCoords);
		var roundedCoords = getRoundedCoords(mouseCoords,editorTool);
		editorTool.click(editorTool,roundedCoords,{oob:outOfBounds(mouseCoords)});
	}
}
function handleMove(e) {
	var editorTool = editorTools[currentMode];
	if (editorTool.move) {
		var pointerCoords = getPointerPos(e);
		mouseCoords = getEditorCoordsExact(pointerCoords);
		var roundedCoords = getRoundedCoords(mouseCoords,editorTool);
		editorTool.move(editorTool,roundedCoords,{});
	}
}
var imgSize = {w:0,h:0};
function getWindowPos() {
	return {x:parseInt(document.documentElement.scrollLeft),y:parseInt(document.documentElement.scrollTop)};
}
function getPointerPos(e) {
	return {x:e.pageX,y:e.pageY};
}
function outOfBounds(point) {
	if (point.x < 0) return true;
	else if (point.x >= imgSize.w) return true;
	if (point.y < 0) return true;
	else if (point.y >= imgSize.h) return true;
	return false;
}
function capPoint(point) {
	if (point.x < 0) point.x = 0;
	else if (point.x >= imgSize.w) point.x = imgSize.w;
	if (point.y < 0) point.y = 0;
	else if (point.y >= imgSize.h) point.y = imgSize.h;
	return point;
}
function capPointExact(point) {
	if (point.x < 0) point.x = 0;
	else if (point.x >= imgSize.w) point.x = imgSize.w-0.25;
	if (point.y < 0) point.y = 0;
	else if (point.y >= imgSize.h) point.y = imgSize.h-0.25;
	return point;
}
function capRectangle(rectangle, options) {
	options = options || {};
	switch (options.cap) {
	case "bounds":
		if (rectangle.x <= -rectangle.w) rectangle.x = -rectangle.w;
		else if (rectangle.x >= imgSize.w) rectangle.x = imgSize.w-1;
		if (rectangle.y <= -rectangle.h) rectangle.y = -rectangle.h;
		else if (rectangle.y >= imgSize.h) rectangle.y = imgSize.h-1;
		break;
	default:
		if (rectangle.x < 0) rectangle.x = 0;
		else if (rectangle.x > (imgSize.w-rectangle.w)) rectangle.x = imgSize.w-rectangle.w-1;
		if (rectangle.y < 0) rectangle.y = 0;
		else if (rectangle.y > (imgSize.h-rectangle.h)) rectangle.y = imgSize.h-rectangle.h-1;
	}
	return rectangle;
}
function capCircle(circle) {
	if (circle.x < circle.r) circle.x = circle.r;
	else if (circle.x >= (imgSize.w-circle.r)) circle.x = imgSize.w-circle.r-1;
	if (circle.y < 0) circle.y = 0;
	else if (circle.y > (imgSize.h-circle.r)) circle.y = imgSize.h-circle.r-1;
	return circle;
}
function capBox(box) {
	return capPoint(box);
}
function getRoundedCoords(point,editorTool) {
	var res = capPointExact({x:point.x,y:point.y});
	if (editorTool.round_on_pixel && editorTool.round_on_pixel(editorTool)) {
		res.x = Math.round(res.x);
		res.y = Math.round(res.y);
	}
	else {
		res.x = Math.floor(res.x);
		res.y = Math.floor(res.y);
	}
	return res;
}
function getEditorCoords(point) {
	var x = Math.floor((point.x - offsetX)/zoomLevel), y = Math.floor((point.y - offsetY)/zoomLevel);
	return capPoint({x:x,y:y});
}
function getEditorCoordsRounded(point) {
	var x = Math.round((point.x - offsetX)/zoomLevel), y = Math.round((point.y - offsetY)/zoomLevel);
	return capPoint({x:x,y:y});
}
function getEditorCoordsExact(point) {
	return {x:(point.x-offsetX)/zoomLevel,y:(point.y-offsetY)/zoomLevel};
}
function getScreenCoords(point) {
	var res = {
		"x": Math.round(point.x*zoomLevel + offsetX),
		"y": Math.round(point.y*zoomLevel + offsetY)
	};
	if (point.w != null)
		res.w = Math.round(point.w*zoomLevel);
	if (point.h != null)
		res.h = Math.round(point.h*zoomLevel);
	if (point.r != null)
		res.r = point.r;
	if (point.l)
		res.l = point.l;
	return res;
}
function getScreenCoordsExact(point) {
	var res = {
		"x": point.x*zoomLevel + offsetX,
		"y": point.y*zoomLevel + offsetY
	};
	if (point.w != null)
		res.w = point.w*zoomLevel;
	if (point.h != null)
		res.h = point.h*zoomLevel;
	return res;
}
function getDeltaAngle(theta0,origin,point) {
	var newAngle = Math.atan2(point.y-origin.y,point.x-origin.x);
	var dAngle = newAngle-theta0;
	dAngle -= 2*Math.PI*Math.round(dAngle/(2*Math.PI));
	return dAngle;
}
function createCircle(point,append) {
	var res = document.createElementNS(SVG, "circle");
	setCircleBounds(res,point);
	if (point.l) {
		res.setAttribute("class", "stroke");
		res.setAttribute("stroke-width", point.l);
	}
	if (append !== false)
		$editor.appendChild(res);
	return res;
}
function setCircleBounds(circle,data) {
	setCirclePos(circle,data);
	circle.setAttribute("r", data.r);
}
function createRectangle(point,append) {
	var res = document.createElementNS(SVG, "rect");
	res.setAttribute("x", point.x);
	res.setAttribute("y", point.y);
	res.setAttribute("width", point.w||1);
	res.setAttribute("height", point.h||1);
	if (append !== false)
		$editor.appendChild(res);
	return res;
}
function createBox(boxSize,append) {
	var res = document.createElementNS(SVG, "rect");
	res.setAttribute("x", (boxSize.x||0)-boxSize.w);
	res.setAttribute("y", (boxSize.y||0)-boxSize.h);
	res.setAttribute("width", boxSize.w);
	res.setAttribute("height", boxSize.h);
	if (append !== false)
		$editor.appendChild(res);
	return res;
}
function createLine(point1,point2,append) {
	var res = document.createElementNS(SVG, "line");
	moveLine(res,point1,point2);
	if (append !== false)
		$editor.appendChild(res);
	return res;
}
function editLine(line,origin,dir,options) {
	options = options||{};
	var mask = createMask();
	var point2 = {x:origin.x+dir.x,y:origin.y+dir.y};
	function editLine_(e) {
		point2 = getEditorCoordsRounded(getPointerPos(e));
		moveLine(line,null,point2);
	}
	function stopEditLine(e) {
		e.stopPropagation();
		$toolbox.classList.remove("hiddenbox");
		editLine_(e);
		storeHistoryData(editorTools[currentMode].data);
		var nDir = {x:point2.x-origin.x,y:point2.y-origin.y};
		var apply;
		if (options.on_apply)
			apply = options.on_apply(nDir);
		if (false !== apply)
			applyObject(dir,nDir);
		mask.removeEventListener("mousemove", editLine_);
		mask.removeEventListener("mouseup", stopEditLine);
		mask.defaultClose();
	}
	mask.addEventListener("mousemove", editLine_);
	mask.addEventListener("mouseup", stopEditLine);
	mask.close = function(){};
	$toolbox.classList.add("hiddenbox");
}
function moveLine(line,point1,point2) {
	if (point1) {
		line.setAttribute("x1", point1.x);
		line.setAttribute("y1", point1.y);
	}
	if (point2) {
		line.setAttribute("x2", point2.x);
		line.setAttribute("y2", point2.y);
	}
}
function createArc(data,append) {
	var res = document.createElementNS(SVG, "circle");
	res.setAttribute("class", "arc");
	res.setAttribute("fill", "transparent");
	moveArc(res,data);
	if (append !== false)
		$editor.appendChild(res);
	return res;
}
function moveArc(arc,data) {
	if (data.x && data.y) {
		if (data.x) arc.setAttribute("cx", data.x);
		if (data.y) arc.setAttribute("cy", data.y);
		arc.style.transformOrigin = data.x+"px "+ data.y+"px";
	}
	if (data.r) {
		arc.setAttribute("r", data.r);
		arc.setAttribute("stroke-dashoffset", (2*Math.PI-data.dtheta)*data.r);
		arc.setAttribute("stroke-dasharray", 2*Math.PI*data.r);
		arc.style.transform = "rotate("+Math.round(data.theta0*180/Math.PI)+"deg)";
	}
}
function createArrowNode(point, orientation, append) {
	var l = 18;
	var d = 6;
	var screenData = {w:0,h:0};
	function getArrowLines(orientation) {
		orientation = (4-orientation)%4*90;
		var arrowPoints = [
			{x:0,y:0},
			rotatePoint({x:0,y:l},screenData, orientation),
			rotatePoint({x:-d,y:l-d},screenData, orientation),
			rotatePoint({x:d,y:l-d},screenData, orientation)
		];
		return [
			[arrowPoints[0],arrowPoints[1]],
			[arrowPoints[1],arrowPoints[2]],
			[arrowPoints[1],arrowPoints[3]]
		];
	}
	var arrowLines = getArrowLines(orientation);
	var arrow = [];
	for (var i=0;i<2;i++) {
		var dark = !i;
		var stroke = 5-i*2;
		for (var j=0;j<arrowLines.length;j++) {
			(function(arrowLine,dark,stroke) {
				var line = createLine(point,point,append);
				line.classList.add("bordered");
				if (dark)
					line.classList.add("dark");
				addZoomListener(line, function() {
					this.setAttribute("x1",point.x+arrowLine[0].x/zoomLevel);
					this.setAttribute("y1",point.y+arrowLine[0].y/zoomLevel);
					this.setAttribute("x2",point.x+arrowLine[1].x/zoomLevel);
					this.setAttribute("y2",point.y+arrowLine[1].y/zoomLevel);
					this.setAttribute("stroke-width", stroke/zoomLevel);
				});
				arrow.push(line);
			})(arrowLines[j],dark,stroke);
		}
	}
	var origin = createCircle({x:point.x,y:point.y,r:3,l:1},append);
	addZoomListener(origin, function() {
		this.setAttribute("r", 4/zoomLevel);
		this.setAttribute("stroke-width", 1/zoomLevel);
	});
	var res = {origin:origin,lines:arrow};
	res.move = function(point2) {
		applyObject(point,point2);
		origin.setAttribute("cx", point.x);
		origin.setAttribute("cy", point.y);
		for (var i=0;i<arrow.length;i++)
			arrow[i].onzoom();
	};
	res.rotate = function(orientation) {
		var nLines = getArrowLines(orientation);
		for (var i=0;i<nLines.length;i++) {
			for (var j=0;j<arrowLines[i].length;j++)
				arrowLines[i][j] = nLines[i][j];
		}
		for (var i=0;i<arrow.length;i++)
			arrow[i].onzoom();
	};
	return res;
}
function createArrow(point1,point2,append,options) {
	options = options||{};
	options.thickness = options.thickness||2;
	var lines = [null,null];
	for (var j=1;j>=0;j--) {
		(function(j) {
			lines[j] = [createLine(point2,point2,append),createLine(point2,point2,append),createLine(point1,point2,append)].reverse();
			for (var i=0;i<lines[j].length;i++) {
				var line = lines[j][i];
				line.classList.add("bordered");
				if (j)
					line.classList.add("dark");
				addZoomListener(line, function() {
					this.setAttribute("stroke-width", (options.thickness+j*2)/zoomLevel);
				});
			}
		})(j);
	}
	var allLines = lines[0].concat(lines[1]);
	var arrowTheta = Math.PI/4;
	var cosTheta = Math.cos(arrowTheta), sinTheta = Math.sin(arrowTheta);
	function setArrowPoint(line,j,l) {
		var point3 = deepCopy(point2);
		var arrowL = 10/zoomLevel;
		var dir = {x:point2.x-point1.x, y:point2.y-point1.y};
		var dirL = Math.hypot(dir.x,dir.y);
		var point4 = deepCopy(point2);
		if (dirL) {
			dir.x /= dirL;
			dir.y /= dirL;
			var pointDir = {x:dir.x*arrowL*cosTheta - l*dir.y*arrowL*sinTheta,y:l*dir.x*arrowL*sinTheta + dir.y*arrowL*cosTheta}
			point3.x -= pointDir.x;
			point3.y -= pointDir.y;
			if (j) {
				var extendFactor = 0.15;
				point4.x += extendFactor*pointDir.x;
				point4.y += extendFactor*pointDir.y;
				point3.x -= extendFactor*pointDir.x;
				point3.y -= extendFactor*pointDir.y;
			}
		}
		moveLine(line,point4,point3);
	}
	function moveArrow(p1,p2) {
		if (p1) point1 = p1;
		if (p2) point2 = p2;
		for (var j=0;j<2;j++) {
			(function(j) {
				moveLine(lines[j][0],point1,point2);
				addZoomListener(lines[j][1], function() {
					setArrowPoint(lines[j][1],j,1);
					this.setAttribute("stroke-width", (options.thickness+j*2)/zoomLevel);
				});
				addZoomListener(lines[j][2], function() {
					setArrowPoint(lines[j][2],j,-1);
					this.setAttribute("stroke-width", (options.thickness+j*2)/zoomLevel);
				});
			})(j);
		}
	}
	function removeArrow() {
		for (var i=0;i<allLines.length;i++)
			$editor.removeChild(allLines[i]);
	}
	function hideArrow() {
		for (var i=0;i<allLines.length;i++)
			allLines[i].style.display = "none";
	}
	function showArrow() {
		for (var i=0;i<allLines.length;i++)
			allLines[i].style.display = "";
	}
	return {
		lines: allLines,
		move: moveArrow,
		remove: removeArrow,
		hide: hideArrow,
		show: showArrow
	}
}
function changeArrowDir(arrow,origin,dir,length,fromCenter) {
	var dirL = Math.hypot(dir.x,dir.y);
	if (dirL) {
		if (length == null)
			length = dirL;
		if (fromCenter) {
			dirL *= 2;
			arrow.move({x:origin.x-dir.x*length/dirL,y:origin.y-dir.y*length/dirL},{x:origin.x+dir.x*length/dirL,y:origin.y+dir.y*length/dirL});
		}
		else
			arrow.move(origin,{x:origin.x+dir.x*length/dirL,y:origin.y+dir.y*length/dirL});
	}
}
function createCircularArrow(center,angle1,dAngle,append,options) {
	options = options||{};
	options.thickness = options.thickness||2;
	var lines = [null,null];
	for (var j=1;j>=0;j--) {
		(function(j) {
			lines[j] = [createLine(center,center,append),createLine(center,center,append),createArc({x:center.x,y:center.y,r:center.r,theta0:angle1,dtheta:dAngle},append)].reverse();
			for (var i=0;i<lines[j].length;i++) {
				var line = lines[j][i];
				line.classList.add("bordered");
				if (j)
					line.classList.add("dark");
				addZoomListener(line, function() {
					this.setAttribute("stroke-width", (options.thickness+j*2)/zoomLevel);
				});
			}
		})(j);
	}
	var allLines = lines[0].concat(lines[1]);
	var arrowTheta = Math.PI/4;
	var cosTheta = Math.cos(arrowTheta), sinTheta = Math.sin(arrowTheta);
	function setArrowPoint(line,j,l) {
		var angle2 = angle1 + dAngle;
		var cosAngle = Math.cos(angle2), sinAngle = Math.sin(angle2);
		var point2 = {x:center.x+center.r*cosAngle, y:center.y+center.r*sinAngle};
		var point3 = deepCopy(point2);
		var arrowL = 10/zoomLevel;
		var dir = {x:-sinAngle, y:cosAngle};
		if (dAngle < 0) {
			dir.x = -dir.x;
			dir.y = -dir.y;
		}
		var pointDir = {x:dir.x*arrowL*cosTheta - l*dir.y*arrowL*sinTheta,y:l*dir.x*arrowL*sinTheta + dir.y*arrowL*cosTheta}
		point3.x -= pointDir.x;
		point3.y -= pointDir.y;
		if (j) {
			var extendFactor = 0.15;
			point2.x += extendFactor*pointDir.x;
			point2.y += extendFactor*pointDir.y;
			point3.x -= extendFactor*pointDir.x;
			point3.y -= extendFactor*pointDir.y;
		}
		moveLine(line,point2,point3);
	}
	function moveArrow(point,a1,dA) {
		if (point!=null)center=point;
		if (a1!=null) angle1 = a1;
		if (dA!=null) dAngle = dA;
		for (var j=0;j<2;j++) {
			(function(j) {
				moveArc(lines[j][0],{x:center.x,y:center.y,r:center.r,theta0:angle1,dtheta:dAngle});
				addZoomListener(lines[j][1], function() {
					setArrowPoint(lines[j][1],j,1);
					this.setAttribute("stroke-width", (options.thickness+j*2)/zoomLevel);
				});
				addZoomListener(lines[j][2], function() {
					setArrowPoint(lines[j][2],j,-1);
					this.setAttribute("stroke-width", (options.thickness+j*2)/zoomLevel);
				});
			})(j);
		}
	}
	function removeArrow() {
		for (var i=0;i<allLines.length;i++)
			$editor.removeChild(allLines[i]);
	}
	function hideArrow() {
		for (var i=0;i<allLines.length;i++)
			allLines[i].style.display = "none";
	}
	function showArrow() {
		for (var i=0;i<allLines.length;i++)
			allLines[i].style.display = "";
	}
	return {
		lines: allLines,
		move: moveArrow,
		remove: removeArrow,
		hide: hideArrow,
		show: showArrow
	}
}
function createPolygonNode(point,append) {
	var center = createRectangle({x:point.x-0.25,y:point.y-0.25,w:0.5,h:0.5},append);
	center.setAttribute("class", "transparent");
	var r = point.r||4, l = point.l||1;
	var circle = createCircle({x:point.x,y:point.y,r:r,l:l},append);
	addZoomListener(circle, function() {
		this.setAttribute("r", r/zoomLevel);
		this.setAttribute("stroke-width", l/zoomLevel);
	});
	var res = {center:center,circle:circle};
	res.move = function(point) {
		circle.setAttribute("cx", point.x);
		circle.setAttribute("cy", point.y);
		center.setAttribute("x", point.x-0.5);
		center.setAttribute("y", point.y-0.5);
	};
	return res;
}
function addZoomListener(elt, callback) {
	elt.classList.add("zoom-event");
	elt.onzoom = callback;
	elt.onzoom();
}
function startPolygonBuilder(self,point, options) {
	options = options || {};
	options.min_points = options.min_points || 3;
	var polygon = document.createElementNS(SVG, "polyline");
	polygon.setAttribute("class", "path");
	addZoomListener(polygon, function() {
		this.setAttribute("stroke-width", 4/zoomLevel);
	});
	$editor.appendChild(polygon);
	self.state.nodes = [];
	if (options.closed !== false) {
		self.state.nodes.push(createPolygonNode(point));
		self.state.nodes[0].circle.classList.add("hover-toggle");
		self.state.nodes[0].circle.onmouseover = function(e) {
			polygon.classList.add("dark");
		};
		self.state.nodes[0].circle.onmouseout = function(e) {
			polygon.classList.remove("dark");
		};
		self.state.nodes[0].circle.onclick = function(e) {
			if (e) e.stopPropagation();
			var points = self.state.points;
			if (points.length > options.min_points) {
				$toolbox.classList.remove("hiddenbox");
				storeHistoryData(self.data);
				points.pop();
				setPolygonPoints(polygon,points,true);
				if (!options.hollow)
					polygon.classList.remove("path");
				this.onmouseout();
				if (options.keep_nodes) {
					self.state.nodes[0].circle.onmouseover = undefined;
					self.state.nodes[0].circle.onmouseout = undefined;
					self.state.nodes[0].circle.onclick = undefined;
					self.state.nodes[0].circle.classList.remove("hover-toggle");
					self.state.nodes[0].center.onmouseover = undefined;
					self.state.nodes[0].center.onmouseout = undefined;
					self.state.nodes[0].center.onclick = undefined;
					self.state.nodes[0].center.classList.remove("hover-toggle");
				}
				else {
					for (var i=0;i<self.state.nodes.length;i++) {
						$editor.removeChild(self.state.nodes[i].center);
						$editor.removeChild(self.state.nodes[i].circle);
					}
					self.state.nodes = null;
				}
				if (!options.custom_undos) {
					self.onundo = null;
					self.onredo = null;
				}
				self.state.points = null;
				self.state.polygon = null;
				self.state.point.classList.remove("dark");
				setNodePos(self.state.point,{x:-1,y:-1});
				if (options.on_apply)
					options.on_apply(polygon,points,points[0]);
			}
		};
		self.state.nodes[0].center.onmouseover = self.state.nodes[0].circle.onmouseover;
		self.state.nodes[0].center.onmouseout = self.state.nodes[0].circle.onmouseout;
		self.state.nodes[0].center.onclick = self.state.nodes[0].circle.onclick;
	}
	self.state.points = [point,deepCopy(point)];
	setPolygonPoints(polygon,self.state.points);
	self.state.point.classList.add("dark");
	self.state.polygon = polygon;
	if (!options.custom_undos) {
		self.onundo = function() {
			if (self.state.points.length > 2) {
				self.state.points.pop();
				self.state.points[self.state.points.length-1] = self.state.points[self.state.points.length-2];
				$editor.removeChild(self.state.nodes[self.state.nodes.length-1].center);
				$editor.removeChild(self.state.nodes[self.state.nodes.length-1].circle);
				self.state.nodes.pop();
				setPolygonPoints(polygon,self.state.points);
			}
			else {
				$toolbox.classList.remove("hiddenbox");
				for (var i=0;i<self.state.nodes.length;i++) {
					$editor.removeChild(self.state.nodes[i].center);
					$editor.removeChild(self.state.nodes[i].circle);
				}
				$editor.removeChild(polygon);
				self.state.points = null;
				self.state.polygon = null;
				self.state.nodes = null;
				self.onundo = null;
				self.onredo = null;
				self.state.point.classList.remove("dark");
			}
		};
		self.onredo = function() {
		};
	}
	if (!options.keep_box)
		$toolbox.classList.add("hiddenbox");
}
function appendPolygonBuilder(self,point) {
	var points = self.state.points;
	points[points.length-1] = point;
	self.state.nodes.push(createPolygonNode(point));
	points.push(deepCopy(point));
	setPolygonPoints(self.state.polygon,points);
}
function movePolygonBuilder(self,point) {
	var polygon = self.state.polygon;
	if (polygon) {
		var points = self.state.points;
		points[points.length-1] = point;
		setPolygonPoints(polygon,points);
	}
	setNodePos(self.state.point,point);
}
function startRectangleBuilder(self,point,options) {
	self.state.origin = point;
	self.state.rectangle = createRectangle(point);
	self.state.point.style.display = "none";
	self.state.options = options;
	storeHistoryData(self.data);
	$toolbox.classList.add("hiddenbox");
}
function moveRectangleBuilder(self,point) {
	if (self.state.rectangle)
		setRectangleBounds(self.state.rectangle,getRectDataOptions(self.state.origin,point,self.state.options));
	else
		setPointPos(self.state.point,point);
}
function appendRectangleBuilder(self,point) {
	$toolbox.classList.remove("hiddenbox");
	var rectangle = self.state.rectangle;
	var data = getRectDataOptions(self.state.origin,point,self.state.options);
	data.type = "rectangle";
	setRectangleBounds(rectangle,data);
	self.state.rectangle = null;
	self.state.point.style.display = "";
	setNodePos(self.state.point,{x:-1,y:-1});
	var options = self.state.options;
	delete self.state.options;
	options.on_apply(rectangle,data,point);
}
function initRouteSelector($traject,nbRoutes) {
	for (var i=0;i<nbRoutes;i++) {
		var $trajectOption = document.createElement("option");
		$trajectOption.value = i;
		$trajectOption.innerHTML = (language ? "Route":"Trajet")+" "+(i+1);
		$traject.appendChild($trajectOption);
	}
	var $trajectOption = document.createElement("option");
	$trajectOption.value = -1;
	$trajectOption.className = "special-option";
	$trajectOption.innerHTML = (language ? "More...":"Plus...");
	$traject.appendChild($trajectOption);
	$traject.selectedIndex = 0;
}
function initRouteBuilder(self,data,traject) {
	self.state.point = createCircle({x:-1,y:-1,r:0.5});
	self.state.point.classList.add("noclick");
	self.state.traject = traject;
	var oldData = data[traject];
	data[traject] = {points:[],closed:false};
	self.state.data = data[traject];
	if (oldData) {
		for (var i=0;i<oldData.points.length;i++)
			self.click(self,oldData.points[i],{});
		if (oldData.closed)
			self.state.nodes[0].circle.onclick();
	}
}
function appendRouteBuilder(self,point,extra,options) {
	options = options || {};
	var polygon = self.state.polygon;
	var selfData = self.state.data;
	var selfPoints = selfData.points;
	if (polygon) {
		if (options.allow_undos !== false)
			storeHistoryData(self.data);
		selfPoints.push(point);
		appendPolygonBuilder(self,point);
	}
	else if (selfData.closed) {
		var i = self.state.movingNode;
		if (i !== undefined) {
			$toolbox.classList.remove("hiddenbox");
			self.move(self,point,extra);
			selfData.points[i] = Object.assign({}, selfData.points[i], point);
			$editor.removeChild(self.state.mask);
			delete self.state.movingNode;
			if (self.state.onPostMove) {
				self.state.onPostMove(self);
				delete self.state.onPostMove;
			}
		}
	}
	else {
		if (!extra.oob) {
			storeHistoryData(self.data);
			selfPoints.push(point);
			startPolygonBuilder(self,point, {
				min_points: 1,
				hollow: true,
				custom_undos: true,
				keep_nodes: true,
				keep_box: true,
				on_apply: function(polygon,points) {
					$editor.removeChild(polygon);
					self.state.polygon = null;
					selfData.closed = true;
					createPolyline(self,points,options);
				}
			});
		}
	}
}
function moveRouteBuilder(self,point,extra) {
	if (self.state.data.closed) {
		var i = self.state.movingNode;
		if (i !== undefined) {
			self.state.nodes[i].move(point);
			var lines = self.state.lines;
			var line1 = lines[(i+lines.length-1)%lines.length], line2 = lines[i];
			line1.setAttribute("x2", point.x);
			line1.setAttribute("y2", point.y);
			line2.setAttribute("x1", point.x);
			line2.setAttribute("y1", point.y);
		}
	}
	else
		movePolygonBuilder(self,point);
}
function startCircleBuilder(self,point,options) {
	self.state.origin = point;
	self.state.circle = createCircle({x:point.x,y:point.y,r:0});
	self.state.point.style.display = "none";
	self.state.options = options;
	storeHistoryData(self.data);
	$toolbox.classList.add("hiddenbox");
}
function moveCircleBuilder(self,point) {
	if (self.state.circle)
		setCircleBounds(self.state.circle,getCircleDataOptions(self.state.origin,point,self.state.options));
	else
		setPointPos(self.state.point,point);
}
function appendCircleBuilder(self,point) {
	$toolbox.classList.remove("hiddenbox");
	var circle = self.state.circle;
	var data = getCircleDataOptions(self.state.origin,point,self.state.options);
	data.type = "circle";
	setCircleBounds(circle,data);
	self.state.circle = null;
	self.state.point.style.display = "";
	setNodePos(self.state.point,{x:-1,y:-1});
	var options = self.state.options;
	delete self.state.options;
	options.on_apply(circle,data,point);
}
function getRectData(origin,point) {
	return {
		x:Math.min(point.x,origin.x),
		y:Math.min(point.y,origin.y),
		w:Math.abs(point.x-origin.x),
		h:Math.abs(point.y-origin.y)
	};
}
function getRectDataCp(origin,point) {
	var point2 = deepCopy(point);
	if (Math.abs(point.x-origin.x) < Math.abs(point.y-origin.y))
		point2.x = origin.x + (point.x>=origin.x ? 15:-15);
	else
		point2.y = origin.y + (point.y>=origin.y ? 15:-15);
	return getRectData(origin,point2);
}
function getRectDataOptions(origin,point,options) {
	if (options.cp)
		return getRectDataCp(origin,point);
	else
		return getRectData(origin,point);
}
function getCircleDataOptions(origin,point,options) {
	return {
		x: Math.round((origin.x+point.x)/2),
		y: Math.round((origin.y+point.y)/2),
		r: Math.round(Math.hypot(point.x-origin.x,point.y-origin.y)/2)
	};
}
function absolutizeData(data, options) {
	var origin = {x:data.x,y:data.y};
	var point = {x:data.x+data.w,y:data.y+data.h};
	var nData;
	switch (options.cap) {
	case "bounds":
		nData = getRectData(origin,point);
		capRectangle(nData,options);
		break;
	default:
		origin = capPoint(origin);
		point = capPoint(point);
		nData = getRectData(origin,point);
		break;
	}
	applyObject(data,nData);
};
function applyObject(data, nData) {
	if ("x" in nData)
		data.x = nData.x;
	if ("y" in nData)
		data.y = nData.y;
	if ("w" in nData)
		data.w = nData.w;
	if ("h" in nData)
		data.h = nData.h;
	if ("r" in nData)
		data.r = nData.r;
}
function setRectanglePos(rectangle,data) {
	rectangle.setAttribute("x", data.x);
	rectangle.setAttribute("y", data.y);
}
function setRectangleSize(rectangle,data) {
	rectangle.setAttribute("width", 1+data.w);
	rectangle.setAttribute("height", 1+data.h);
}
function setRectangleBounds(rectangle,data) {
	setRectanglePos(rectangle,data);
	setRectangleSize(rectangle,data);
}
function createPolygon(data,append) {
	var res = document.createElementNS(SVG, "polyline");
	setPolygonPoints(res,data,true);
	if (append !== false)
		$editor.appendChild(res);
	return res;
}
function getPolygonCenter(points) {
	var res = {x:0,y:0};
	for (var i=0;i<points.length;i++) {
		res.x += points[i].x;
		res.y += points[i].y;
	}
	res.x = Math.round(res.x/points.length);
	res.y = Math.round(res.y/points.length);
	return res;
}
function getPolygonRelativeCenter(points) {
	var res = getPolygonCenter(points);
	res.x -= points[0].x;
	res.y -= points[0].y;
	return res;
}
function movePolygonRelativeCenter(polygon,points,point,center) {
	for (var i=points.length-1;i>=0;i--) {
		points[i].x += point.x-center.x-points[0].x;
		points[i].y += point.y-center.y-points[0].y;
	}
	setPolygonPoints(polygon,points,true);
}
function setPolygonPoints(polygon,data,closed) {
	var res = "";
	for (var i=0;i<data.length;i++)
		res += (i?",":"") + data[i].x+","+data[i].y;
	if (closed)
		res += ","+data[0].x+","+data[0].y;
	polygon.setAttribute("points", res);
}
function createPolyline(self,points, options) {
	options = options || {};
	self.state.lines = [];
	function addPointInLine(e,i) {
		var nPoint = getEditorCoordsRounded(getPointerPos(e));
		self.state.nodes.splice(i+1,0,createPolygonNode(nPoint));
		var lines = self.state.lines;
		for (var j=0;j<lines.length;j++)
			$editor.removeChild(lines[j]);
		storeHistoryData(self.data);
		self.state.data.points.splice(i+1,0,nPoint);
		createPolyline(self,self.state.data.points, options);
		self.state.nodes[i+1].circle.onclick();
	}
	for (var i=0;i<points.length;i++) {
		(function(i) {
			var point1 = points[i], point2 = points[(i+1)%points.length];
			var line = createLine(point1,point2,false);
			line.classList.add("bordered");
			line.classList.add("hover-toggle");
			line.style.cursor = "pointer";
			addZoomListener(line, function() {
				this.setAttribute("stroke-width", 4/zoomLevel);
			});
			line.onclick = function(e) {
				e.stopPropagation();
				addPointInLine(e,i);
			};
			line.oncontextmenu = function(e) {
				addPointInLine(e,i);
				return false;
			};
			$editor.insertBefore(line, self.state.nodes[0].circle);
			self.state.lines.push(line);
		})(i);
	}
	for (var i=0;i<self.state.nodes.length;i++) {
		(function(i) {
			self.state.nodes[i].circle.classList.add("hover-toggle");
			self.state.nodes[i].circle.onclick = function(e) {
				if (e) e.stopPropagation();
				var mask = createRectangle({x:0,y:0,w:imgSize.w,h:imgSize.h});
				mask.setAttribute("class", "transparent");
				self.state.mask = mask;
				self.state.movingNode = i;
				self.state.onPostMove = options.on_post_edit;
				if (e) storeHistoryData(self.data);
				$toolbox.classList.add("hiddenbox");
			};
			self.state.nodes[i].circle.oncontextmenu = function(e) {
				//alert(i);
				//return false;
				e.stopPropagation();
				var ctxMenuItems = [{
					text: (language ? "Move":"Déplacer"),
					click: function() {
						self.state.nodes[i].circle.onclick(e);
					}
				}, {
					text: (language ? "Delete":"Supprimer"),
					click: function() {
						$editor.removeChild(self.state.nodes[i].center);
						$editor.removeChild(self.state.nodes[i].circle);
						self.state.nodes.splice(i,1);
						var lines = self.state.lines;
						for (var j=0;j<lines.length;j++)
							$editor.removeChild(lines[j]);
						storeHistoryData(self.data);
						self.state.data.points.splice(i,1);
						createPolyline(self,self.state.data.points, options);
						if (options.on_post_edit)
							options.on_post_edit(self);
					}
				}];
				if (options.ctxmenu) {
					var extraItems = options.ctxmenu(i, e);
					for (var j=0;j<extraItems.length;j++)
						ctxMenuItems.push(extraItems[j]);
				}
				showContextMenu(e,ctxMenuItems);
				return false;
			};
		})(i);
	}
}
function setPointPos(point,data) {
	point.setAttribute("x", data.x);
	point.setAttribute("y", data.y);
}
function setCirclePos(circle,data) {
	circle.setAttribute("cx", data.x);
	circle.setAttribute("cy", data.y);
}
function setNodePos(point,data) {
	if (point.tagName == "circle")
		setCirclePos(point,data);
	else
		setPointPos(point,data);
}
function setBoxPos(point,data,size) {
	point.setAttribute("x", data.x-size.w/2);
	point.setAttribute("y", data.y-size.h/2);
}
function setBoxPosRound(point,data,size) {
	point.setAttribute("x", Math.round(data.x-size.w/2));
	point.setAttribute("y", Math.round(data.y-size.h/2));
}
function hideBox(box,boxSize) {
	setBoxPos(box,{x:-boxSize.w,y:-boxSize.h},boxSize);
}
function handleKeySortcuts(e) {
	var currentMasks = document.getElementsByClassName("editor-mask");
	if (currentMasks.length) {
		switch (e.keyCode) {
		case 27:
			var currentMasks = document.getElementsByClassName("editor-mask");
			if (currentMasks.length)
				currentMasks[currentMasks.length-1].close();
			break;
		case 83:
			if (e.ctrlKey || e.metaKey)
				e.preventDefault();
		}
	}
	else {
		switch (e.keyCode) {
		case 38:
			if (e.ctrlKey || e.metaKey) {
				e.preventDefault();
				zoomMore(true);
			}
			break;
		case 40:
			if (e.ctrlKey || e.metaKey) {
				e.preventDefault();
				zoomLess(true);
			}
			break;
		case 90:
			if (e.ctrlKey || e.metaKey) {
				e.preventDefault();
				if (e.shiftKey)
					redo();
				else
					undo();
			}
			break;
		case 89:
			if (e.ctrlKey || e.metaKey) {
				e.preventDefault();
				redo();
			}
			break;
		case 83:
			if (e.ctrlKey || e.metaKey) {
				e.preventDefault();
				saveData();
			}
		}
	}
}
function handlePageExit() {
	if (changes)
		return language ? "Warning: unsaved data will be lost.":"Attention, Les données non sauvegardées seront perdues.";
}
function removeAllChildren(elt) {
	while (elt.lastChild)
		elt.removeChild(elt.lastChild);
}
function showContextMenu(e,elts,onHide) {
	var oContextmenu = document.getElementById("contextmenu");
	if (oContextmenu)
		oContextmenu.close();
	oContextmenu = createMask();
	if (onHide) {
		oContextmenu.close = function() {
			oContextmenu.defaultClose();
			onHide();
		};
	}
	oContextmenu.oncontextmenu = function() {
		oContextmenu.close();
		return false;
	};
	oContextmenu.id = "contextmenu";
	var oContextItems = document.createElement("div");
	oContextItems.style.left = (e.pageX+1)+"px";
	oContextItems.style.top = (e.pageY+1)+"px";
	oContextItems.onclick = function(e) {
		e.stopPropagation();
	}
	for (var i=0;i<elts.length;i++) {
		(function(elt) {
			var oContextItem = document.createElement("div");
			oContextItem.innerHTML = elt.text;
			if (elt.title) oContextItem.title = elt.title;
			if (elt.disabled) {
				oContextItem.style.cursor = "default";
				oContextItem.style.backgroundColor = (document.getElementById("theme-selector").getValue() === "dark") ? "#888" : "#EEE";
				oContextItem.style.opacity = 0.6;
			}
			else {
				oContextItem.onclick = function() {
					oContextmenu.close();
					elt.click();
				}
			}
			oContextItems.appendChild(oContextItem);
		})(elts[i]);
	}
	oContextmenu.appendChild(oContextItems);
}
function addContextMenuEvent(elt,elts) {
	elt.oncontextmenu = function(e) {
		return showContextOnElt(e,elt,elts);
	}
}
function showContextOnElt(e,elt,elts) {
	e.stopPropagation();
	elt.classList.add("highlight");
	showContextMenu(e,elts,function() {
		elt.classList.remove("highlight");
	});
	return false;
}
function createMask() {
	var res = document.createElement("div");
	res.className = "editor-mask";
	res.style.width = document.body.scrollWidth +"px";
	res.style.height = document.body.scrollHeight +"px";
	res.defaultClose = function() {
		document.body.removeChild(res);
		res.defaultClose = function(){};
		res.close = res.defaultClose;
	}
	res.close = res.defaultClose;
	res.clickable = true;
	res.onclick = function() {
		if (res.clickable)
			res.close();
	};
	res.oncontextmenu = function() {
		return false;
	};
	document.body.appendChild(res);
	if ("activeElement" in document)
	    document.activeElement.blur();
	return res;
}
function showToast(msg) {
	if (document.getElementById("editor-toast"))
		document.body.removeChild(document.getElementById("editor-toast"));
	var res = document.createElement("div");
	res.id = "editor-toast";
	res.innerHTML = msg;
	document.body.appendChild(res);
	setTimeout(function() {
		if (res.parentNode)
			document.body.removeChild(res);
	}, 4000);
	return res;
}
function removeFromArray(arr,elt) {
	var id = arr.indexOf(elt);
	if (-1 !== id) {
		arr.splice(id,1);
		return true;
	}
	return false;
}
function rotateDir(data,orientation) {
	for (var i=0;i<orientation;i+=90) {
		var nX = -data.y, nY = data.x;
		data.x = nX;
		data.y = nY;
	}
}
function rotateNullableDir(data,orientation) {
	if (data)
		return rotateDir(data,orientation);
}
function rotatePoint(data,screenData,orientation) {
	var sW = screenData.w, sH = screenData.h;
	for (var i=0;i<orientation;i+=90) {
		var nX = sH-data.y, nY = data.x;
		data.x = nX;
		data.y = nY;
		var aW = sW;
		sW = sH;
		sH = aW;
	}
	return data;
}
function rotateNullablePoint(data,screenData,orientation) {
	if (data)
		return rotatePoint(data,screenData,orientation);
}
function rotateRectangle(data,screenData,orientation) {
	var sW = screenData.w, sH = screenData.h;
	for (var i=0;i<orientation;i+=90) {
		var nX = sH-data.h-data.y, nY = data.x, nW = data.h, nH = data.w;
		data.x = nX;
		data.y = nY;
		data.w = nW;
		data.h = nH;
		var aW = sW;
		sW = sH;
		sH = aW;
	}
	return data;
}
function rotateRect(data,screenData,orientation) {
	return rotateRectangle(data,{w:screenData.w-1,h:screenData.h-1},orientation);
}
function rotatePoly(data,screenData,orientation) {
	for (var i=0;i<data.length;i++)
		rotatePoint(data[i],screenData,orientation);
	return data;
}
function rotateCircle(data,screenData,orientation) {
	return rotatePoint(data,screenData,orientation);
}
function rotateShape(data,screenData,orientation) {
	switch (data.type) {
	case "rectangle":
		return rotateRect(data, screenData,orientation);
	case "polygon":
		return rotatePoly(data.points, screenData,orientation);
	case "circle":
		return rotateCircle(data, screenData,orientation);
	}
}
function rotateBox(data,screenData,orientation) {
	rotatePoint(data,screenData,orientation);
	if (data.w && data.h && (orientation%90)) {
		var w = data.w;
		data.w = data.h;
		data.h = w;
	}
	return data;
}
function flipDir(data,axis) {
	data[axis.coord] = -data[axis.coord];
	return data;
}
function flipNullableDir(data,axis) {
	if (data)
		return flipDir(data,axis);
}
function flipPoint(data,screenData,axis) {
	data[axis.coord] = screenData[axis.size]-data[axis.coord];
	return data;
}
function flipNullablePoint(data,screenData,axis) {
	if (data)
		return flipPoint(data,screenData,axis);
}
function flipRectangle(data,screenData,axis) {
	data[axis.coord] = screenData[axis.size]-data[axis.size]-data[axis.coord];
	return data;
}
function flipRect(data,screenData,axis) {
	return flipRectangle(data,{w:screenData.w-1,h:screenData.h-1},axis);
}
function flipPoly(data,screenData,axis) {
	for (var i=0;i<data.length;i++)
		flipPoint(data[i],screenData,axis);
	return data;
}
function flipCircle(data,screenData,axis) {
	return flipPoint(data,screenData,axis);
}
function flipShape(data,screenData,axis) {
	switch (data.type) {
	case "rectangle":
		return flipRect(data, screenData,axis);
	case "polygon":
		return flipPoly(data.points, screenData,axis);
	case "circle":
		return flipCircle(data, screenData,axis);
	}
}
function flipBox(data,screenData,axis) {
	return flipPoint(data,screenData,axis);
}
function pointToData(point) {
	return [point.x,point.y];
}
function dataToPoint(data) {
	return {x:data[0],y:data[1]};
}
function nullablePointToData(point) {
	if (point)
		return pointToData(point);
	return [-1,-1];
}
function dataToNullablePoint(data) {
	if (data[0] == -1 && data[1] == -1)
		return undefined;
	return dataToPoint(data);
}
function rectToData(rect) {
	return [rect.x,rect.y,rect.w,rect.h];
}
function dataToRect(data) {
	return {x:data[0],y:data[1],w:data[2],h:data[3]};
}
function polyToData(poly) {
	var res = [];
	for (var i=0;i<poly.length;i++)
		res.push(pointToData(poly[i]));
	return res;
}
function dataToPoly(data) {
	var res = [];
	for (var i=0;i<data.length;i++)
		res.push(dataToPoint(data[i]));
	return res;
}
function circleToData(circle) {
	return [circle.x,circle.y,circle.r];
}
function dataToCirc(data) {
	return {x:data[0],y:data[1],r:data[2]};
}
function shapeToData(data) {
	switch (data.type) {
	case "rectangle":
		return rectToData(data);
	case "polygon":
		return polyToData(data.points);
	case "circle":
		return circleToData(data);
	}
}
function dataToShape(data) {
	var res;
	if ("number" === typeof(data[0])) {
		if (data.length < 4) {
			res = dataToCirc(data);
			res.type = "circle";
		}
		else {
			res = dataToRect(data);
			res.type = "rectangle";
		}
	}
	else {
		res = {
			type: "polygon",
			points: dataToPoly(data)
		};
	}
	return res;
}
function rescaleDir(data, scale) {
	rescalePoint(data, scale);
}
function rescaleNullableDir(data, scale) {
	if (data)
		rescaleDir(data, scale);
}
function rescalePoint(data, scale) {
	data.x = Math.round(data.x*scale.x);
	data.y = Math.round(data.y*scale.y);
}
function rescaleNullablePoint(data, scale) {
	if (data)
		rescalePoint(data, scale);
}
function rescaleRect(data, scale) {
	data.w = Math.round((data.x+data.w+1)*scale.x);
	data.h = Math.round((data.y+data.h+1)*scale.y);
	data.x = Math.round(data.x*scale.x);
	data.y = Math.round(data.y*scale.y);
	data.w -= data.x+1;
	data.h -= data.y+1;
}
function rescalePoly(data, scale) {
	for (var i=0;i<data.length;i++) {
		var iData = data[i];
		iData.x = Math.round(iData.x*scale.x);
		iData.y = Math.round(iData.y*scale.y);
	}
}
function rescaleCircle(data, scale) {
	data.x = Math.round(data.x*scale.x);
	data.y = Math.round(data.y*scale.y);
	data.r = Math.round(data.r*Math.sqrt(scale.x*scale.y));
}
function rescaleShape(data, scale) {
	switch (data.type) {
	case "rectangle":
		rescaleRect(data, scale);
		break;
	case "polygon":
		rescalePoly(data.points, scale);
		break;
	case "circle":
		rescaleCircle(data, scale);
	}
}
function rescaleBox(data, scale) {
	rescalePoint(data, scale);
	if (data.w != null)
		data.w = Math.round(data.w*scale.x);
	if (data.h != null)
		data.h = Math.round(data.h*scale.y);
}
var hpTypes = ["herbe","eau","glace","choco"];
function boostSizeChanged() {
	var editorTool = editorTools[currentMode];
	var boxSize = {w:+document.getElementById("boost-w").value,h:+document.getElementById("boost-h").value};
	if (!(boxSize.w > 0)) {
		boxSize.w = editorTool.state.boxSize.w;
		document.getElementById("boost-w").value = boxSize.w;
	}
	if (!(boxSize.h > 0)) {
		boxSize.h = editorTool.state.boxSize.h;
		document.getElementById("boost-h").value = boxSize.h;
	}
	updateBoxSize(editorTool.state.point,boxSize);
	editorTool.state.boxSize = boxSize;
}
function initTrajectOptions() {
	document.getElementById("traject-menu").style.display = "block";
	document.getElementById("traject-more").style.display = "none";
	document.getElementById("traject-copy").style.display = "none";
	document.getElementById("traject-less").style.display = "none";
}
function getTrajectData(editorTool,key) {
	var editorTool = editorTools[currentMode];
	switch (key) {
		case "aipoints":
			return editorTool.data;
		case "bus":
			return editorTool.data.extra.truck.route;
	}
}
function getTrajectSelector(key) {
	switch (key) {
		case "aipoints":
			return document.getElementById("traject");
		case "bus":
			return document.getElementById("decor-bus-traject");
	}
}
function showTrajectOptions(key) {
	var $trajectOptions = document.getElementById("traject-options");
	document.body.removeChild($trajectOptions);
	var $mask = createMask();
	$mask.id = "mask-traject";
	$mask.classList.add("mask-dark");
	$mask.appendChild($trajectOptions);
	$trajectOptions.classList.add("fs-shown");
	var $trajectSpecific = $trajectOptions.querySelectorAll(".traject-specific");
	for (var i=0;i<$trajectSpecific.length;i++)
		$trajectSpecific[i].style.display = "none";
	$trajectSpecific = $trajectOptions.querySelectorAll(".traject-specific-"+key);
	for (var i=0;i<$trajectSpecific.length;i++)
		$trajectSpecific[i].style.display = "block";
	$mask.close = function() {
		$mask.removeChild($trajectOptions);
		$trajectOptions.classList.remove("fs-shown");
		document.body.appendChild($trajectOptions);
		this.defaultClose();
	};
	$trajectOptions.dataset.key = key;
	var editorTool = editorTools[currentMode];
	var trajectData = getTrajectData(editorTool,key);
	var maxCp = trajectData.length;
	initTrajectOptions();
	var $links = $trajectOptions.querySelectorAll("#traject-menu a");
	$links[0].style.display = (maxCp<7) ? "block":"none";
	$links[1].style.display = (maxCp>1) ? "block":"none";
	$links[2].style.display = (maxCp>1) ? "block":"none";
	getTrajectSelector(key).selectedIndex = editorTool.state.traject;
}
function closeTrajectOptions() {
	var $mask = document.getElementById('mask-traject');
	if ($mask)
		$mask.close();
}
function showTrajectAdd() {
	var key = document.getElementById("traject-options").dataset.key;
	document.getElementById("traject-menu").style.display = "none";
	document.getElementById("traject-more").style.display = "block";
	var $trajectList = document.getElementById("traject-more-list");
	var editorTool = editorTools[currentMode];
	var maxCp = getTrajectData(editorTool,key).length;
	$trajectList.innerHTML = "";
	var $noTraject = document.createElement("option");
	$noTraject.value = -1;
	$noTraject.innerHTML = language ? 'Empty route':'Trajet vide';
	$trajectList.appendChild($noTraject);
	for (var i=0;i<maxCp;i++) {
		var $iTraject = document.createElement("option");
		$iTraject.value = i;
		$iTraject.innerHTML = (language ? 'Route':'Trajet') +" "+ (i+1);
		$trajectList.appendChild($iTraject);
	}
	switch (key) {
	case "aipoints":
		$trajectList.selectedIndex = editorTool.state.traject+1;
	}
}
function addTraject() {
	var key = document.getElementById("traject-options").dataset.key;
	var $trajectList = document.getElementById("traject-more-list");
	var trajectVal = +$trajectList.value;
	var editorTool = editorTools[currentMode];
	var trajectData = getTrajectData(editorTool,key);
	var maxCp = trajectData.length;
	if (trajectVal != -1)
		trajectData.push(deepCopy(trajectData[trajectVal]));
	else
		trajectData.push({points:[],closed:false});
	var $trajectSelector = getTrajectSelector(key);
	var $trajectOption = document.createElement("option");
	$trajectOption.value = maxCp;
	$trajectOption.innerHTML = (language ? 'Route':'Trajet') +' '+ (maxCp+1);
	$trajectSelector.insertBefore($trajectOption, $trajectSelector.childNodes[maxCp+1]);
	$trajectSelector.selectedIndex = maxCp;
	selectMode(currentMode);
	closeTrajectOptions();
}
function showTrajectCopy() {
	var key = document.getElementById("traject-options").dataset.key;
	document.getElementById("traject-menu").style.display = "none";
	document.getElementById("traject-copy").style.display = "block";
	var $trajectFrom = document.getElementById("copyFrom");
	var $trajectTo = document.getElementById("copyTo");
	var editorTool = editorTools[currentMode];
	var maxCp = getTrajectData(editorTool,key).length;
	$trajectFrom.innerHTML = "";
	$trajectTo.innerHTML = "";
	for (var i=0;i<maxCp;i++) {
		var $iTraject = document.createElement("option");
		$iTraject.value = i;
		$iTraject.innerHTML = (language ? 'Route':'Trajet') +' '+ (i+1);
		$trajectFrom.appendChild($iTraject);
		var $iTraject = document.createElement("option");
		$iTraject.value = i;
		$iTraject.innerHTML = (language ? 'Route':'Trajet') +' '+ (i+1);
		$trajectTo.appendChild($iTraject);
	}
	$trajectFrom.selectedIndex = 0;
	$trajectTo.selectedIndex = 1;
}
function copyTraject() {
	var $trajectFrom = document.getElementById("copyFrom");
	var $trajectTo = document.getElementById("copyTo");
	var m1 = +$trajectFrom.value;
	var m2 = +$trajectTo.value;
	if (m1 != m2) {
		var key = document.getElementById("traject-options").dataset.key;
		var editorTool = editorTools[currentMode];
		var trajectData = getTrajectData(editorTool,key);
		trajectData[m2] = deepCopy(trajectData[m1]);
		selectMode(currentMode);
	}
	closeTrajectOptions();
}
function showTrajectRemove() {
	var key = document.getElementById("traject-options").dataset.key;
	document.getElementById("traject-menu").style.display = "none";
	document.getElementById("traject-less").style.display = "block";
	var $trajectList = document.getElementById("traject-less-list");
	var editorTool = editorTools[currentMode];
	var maxCp = getTrajectData(editorTool,key).length;
	$trajectList.innerHTML = "";
	for (var i=0;i<maxCp;i++) {
		var $iTraject = document.createElement("option");
		$iTraject.value = i;
		$iTraject.innerHTML = (language ? 'Route':'Trajet') +" "+ (i+1);
		$trajectList.appendChild($iTraject);
	}
	$trajectList.selectedIndex = editorTool.state.traject;
}
function removeTraject() {
	var key = document.getElementById("traject-options").dataset.key;
	var $trajectList = document.getElementById("traject-less-list");
	var trajectVal = +$trajectList.value;
	var editorTool = editorTools[currentMode];
	var trajectData = getTrajectData(editorTool,key);
	var maxCp = trajectData.length;
	trajectData.splice(trajectVal,1);
	switch (key) {
	case "bus":
		for (var type in editorTool.data.decors) {
			if (getActualDecorType(type) === "truck") {
				var selfData = editorTool.data.decors[type];
				for (var i=0;i<selfData.length;i++) {
					var decor = selfData[i];
					if (decor.traject >= trajectVal) {
						decor.traject--;
						if (decor.traject < 0)
							decor.traject = 0;
					}
				}
			}
		}
		break;
	}
	var $trajectSelector = getTrajectSelector(key);
	$trajectSelector.removeChild($trajectSelector.childNodes[maxCp]);
	if (trajectVal <= editorTool.state.traject) {
		editorTool.state.traject--;
		$trajectSelector.selectedIndex = Math.max(0,editorTool.state.traject);
	}
	selectMode(currentMode);
	closeTrajectOptions();
}
function showOffroadTransfer() {
	var $offroadOptions = document.getElementById("offroad-transfer");
	document.body.removeChild($offroadOptions);
	var $mask = createMask();
	$mask.id = "mask-transfer";
	$mask.classList.add("mask-dark");
	$mask.appendChild($offroadOptions);
	$offroadOptions.classList.add("fs-shown");
	$mask.close = function() {
		$mask.removeChild($offroadOptions);
		$offroadOptions.classList.remove("fs-shown");
		document.body.appendChild($offroadOptions);
		this.defaultClose();
	};
	var editorTool = editorTools[currentMode];
	document.getElementById("offroad-type").selectedIndex = editorTool.state.type;
}
function transferOffroad() {
	var transferFrom = +document.getElementById('transferFrom').value,
	transferTo = +document.getElementById('transferTo').value;
	if (transferFrom != transferTo) {
		var editorTool = editorTools[currentMode];
		var hpFrom = editorTool.data[transferFrom];
		var hpTo = editorTool.data[transferTo];
		for (var i=0;i<hpFrom.length;i++)
			hpTo.push(deepCopy(hpFrom[i]));
		hpFrom.length = 0;
		selectMode(currentMode);
	}
	closeTransferOffroad();
}
function closeTransferOffroad() {
	document.getElementById('mask-transfer').close();
}
function updateLapsCounter() {
	var editorTool = editorTools[currentMode];
	document.getElementById("checkpoints-nblaps").innerHTML = (editorTool.data.type?(language?"Sections:":"Sections :"):(language?"Laps:":"Tours :")) +" "+ editorTool.data.nb;
}
function showLapsOptions() {
	var $choptions = document.getElementById("choptions");
	document.body.removeChild($choptions);
	var $mask = createMask();
	$mask.id = "mask-laps";
	$mask.classList.add("mask-dark");
	$mask.appendChild($choptions);
	$choptions.classList.add("fs-shown");
	$mask.close = function() {
		$mask.removeChild($choptions);
		$choptions.classList.remove("fs-shown");
		document.body.appendChild($choptions);
		this.defaultClose();
	};
	var editorTool = editorTools[currentMode];
	var lVal = editorTool.data.nb;
	var pos = editorTool.data.type;
	if (pos)
		document.getElementById("choptions-nbsections").selectedIndex = lVal-1;
	else
		document.getElementById("choptions-nblaps").selectedIndex = lVal-1;
	selectChTab(pos);
	updateSectionSelects(true);
}
function selectChTab(pos) {
	var selectedTabs = document.getElementsByClassName("tab-ch-selected");
	while (selectedTabs.length)
		selectedTabs[0].className = "tab-ch";
	document.getElementsByClassName("tab-ch")[pos].className = "tab-ch tab-ch-selected";
	var chSettings = document.getElementsByClassName("choptions-settings");
	for (var i=0;i<chSettings.length;i++)
		chSettings[i].style.display = (i==pos) ? "block":"none";
}
function updateSectionSelects(resetPos) {
	var nb = document.getElementById("choptions-nbsections").value*1;
	var sDiv;
	var editorTool = editorTools[currentMode];
	var nbCp = editorTool.data.checkpoints.length;
	document.getElementById("section-error").style.display = nbCp ? "none":"block";
	var chOptions = editorTool.data.sections;
	if (!chOptions) chOptions = [];
	for (var i=2;sDiv=document.getElementById("section-checkpoint-"+i);i++) {
		sDiv.style.display = (i<=nb) ? "block":"none";
		if (resetPos) {
			var sSelect = sDiv.getElementsByTagName("select")[0];
			sSelect.innerHTML = "";
			for (var j=1;j<=nbCp;j++) {
				var sOption = document.createElement("option");
				sOption.value = j-1;
				sOption.innerHTML = "Checkpoint "+ j;
				sSelect.appendChild(sOption);
			}
			var sSelectedIndex = chOptions[i-2]*1;
			if (sSelectedIndex >= 0)
				sSelect.selectedIndex = Math.min(nbCp-1,sSelectedIndex);
		}
	}
}
function submitLapsOptions() {
	var editorTool = editorTools[currentMode];
	storeHistoryData(editorTool.data);
	var selectedTab = document.getElementsByClassName("tab-ch-selected")[0];
	var pos;
	for (pos=-1;(selectedTab=selectedTab.previousSibling);pos++);
	editorTool.data.type = pos;
	if (pos) {
		var lVal = +document.getElementById("choptions-nbsections").value;
		editorTool.data.nb = lVal;
		var oVal = [];
		for (var i=2;i<=lVal;i++)
			oVal.push(+document.getElementById("section-checkpoint-"+i).getElementsByTagName("select")[0].value);
		editorTool.data.sections = oVal;
	}
	else {
		var lVal = +document.getElementById("choptions-nblaps").value;
		editorTool.data.nb = lVal;
	}
	var selfRectangles = editorTool.state.rectangles;
	for (var i=0;i<selfRectangles.length;i++)
		selfRectangles[i].reorder();
	updateLapsCounter();
	closeLapsOptions();
}
function closeLapsOptions() {
	document.getElementById('mask-laps').close();
}
function showBgTab(id) {
	var $bgGroups = document.querySelectorAll(".bg-selector-optgroup");
	for (var i=0;i<$bgGroups.length;i++)
		$bgGroups[i].style.display = "";
	document.getElementById("bg-selector-optgroup-"+id).style.display = "block";
	var $bgTabs = document.querySelectorAll("#bg-selector-tabs > a");
	for (var i=0;i<$bgTabs.length;i++)
		$bgTabs[i].className = "";
	document.getElementById("bg-selector-tab-"+id).className = "bg-selector-tab-selected";
}
function showBgSelector() {
	var $background = document.getElementById("bg-selector");
	document.body.removeChild($background);
	var $mask = createMask();
	$mask.id = "mask-bg";
	$mask.classList.add("mask-dark");
	$mask.appendChild($background);
	$background.classList.add("fs-shown");
	$mask.close = function() {
		$mask.removeChild($background);
		$background.classList.remove("fs-shown");
		document.body.appendChild($background);
		this.defaultClose();
	};
	var bChoices = document.getElementsByClassName("bg-selected");
	while (bChoices.length)
		bChoices[0].className = "";
	var editorTool = editorTools[currentMode];
	var $selectedBg = document.getElementById("bgchoice-"+editorTool.data.bg_img);
	$selectedBg.className = "bg-selected";
	showBgTab($selectedBg.parentNode.getAttribute("data-value"));
}
function changeBg($elt) {
	var editorTool = editorTools[currentMode];
	storeHistoryData(editorTool.data);
	editorTool.data.bg_img = +$elt.getAttribute("data-value");
	applyBgSelector();
	var $mask = document.getElementById("mask-bg");
	$mask.close();
}
function applyBgSelector() {
	var editorTool = editorTools[currentMode];
	var selectedBg = bgImgs[editorTool.data.bg_img];
	var selectedBgArr = [];
	for (var i=0;i<selectedBg.length;i++)
		selectedBgArr.unshift("url('images/map_bg/"+selectedBg[i]+".png')");
	document.getElementById("button-bgimg").style.backgroundImage = selectedBgArr.join(",");
}
function applyColorSelector() {
	var editorTool = editorTools[currentMode];
	var bgColor = editorTool.data.out_color;
	document.getElementById("button-bgcolor").style.backgroundColor = "rgb("+bgColor.r+","+bgColor.g+","+bgColor.b+")";
	document.body.style.backgroundColor = "rgb("+bgColor.r+","+bgColor.g+","+bgColor.b+")";
}
function showMusicSelector() {
	var $music = document.getElementById("music-selector");
	document.body.removeChild($music);
	var $mask = createMask();
	$mask.id = "mask-music";
	$mask.classList.add("mask-dark");
	$mask.appendChild($music);
	$music.classList.add("fs-shown");
	$mask.close = function() {
		stopMusic();
		$mask.removeChild($music);
		$music.classList.remove("fs-shown");
		document.body.appendChild($music);
		this.defaultClose();
	};
	var mChoices = document.getElementsByClassName("music-selected");
	while (mChoices.length)
		mChoices[0].className = "";
	var editorTool = editorTools[currentMode];
	var editorData = editorTool.data;
	musicSelected = editorData.music;
	var ytSpeed, ytSpeedLast;
	document.getElementById("musicchoice-"+musicSelected).className = "music-selected";
	if (!musicSelected) {
		document.getElementById("youtube-url").value = editorData.youtube;
		if (editorData.youtube_opts) {
			var youtubeOpts = editorData.youtube_opts;
			if (youtubeOpts.start != null)
				document.getElementById("youtube-start").value = timeToStr(youtubeOpts.start);
			if (youtubeOpts.end != null)
				document.getElementById("youtube-end").value = timeToStr(youtubeOpts.end);
			ytSpeed = youtubeOpts.speed;
			var youtubeOptsLast = youtubeOpts.last;
			if (youtubeOptsLast) {
				if (youtubeOptsLast.url != null)
					document.getElementById("youtube-last-url").value = youtubeOptsLast.url;
				if (youtubeOptsLast.start != null)
					document.getElementById("youtube-last-start").value = timeToStr(youtubeOptsLast.start);
				if (youtubeOptsLast.end != null)
					document.getElementById("youtube-last-end").value = timeToStr(youtubeOptsLast.end);
				ytSpeedLast = youtubeOptsLast.speed;
			}
		}
	}
	function initYtSelect($select, defaultVal) {
		defaultVal = defaultVal || 1;
		$select.onchange = function() {
			var newVal = this.value;
			if (newVal === "") {
				var enteredVal = +prompt(language ? "Enter value...":"Entrer une valeur...", this.lastValue);
				if (enteredVal > 0) {
					addOptionIfNotExist($select, enteredVal);
					this.value = enteredVal;
				}
				else {
					this.value = this.lastValue;
					return;
				}
			}
			this.lastValue = this.value;
		}
		addOptionIfNotExist($select, defaultVal);
		$select.value = defaultVal;
		$select.lastValue = $select.value;
	}
	initYtSelect(document.getElementById("youtube-speed"), ytSpeed);
	initYtSelect(document.getElementById("youtube-last-speed"), ytSpeedLast);
}
function addOptionIfNotExist($select, value) {
	var options = $select.options;
	for (var i=0;i<options.length;i++) {
		if (options[i].value == value)
			return;
	}
	var option = document.createElement("option");
	option.value = value;
	option.innerHTML = "&times;"+value;
	$select.insertBefore(option, options[options.length-1]);
}
function applyMusicSelector() {
	var editorTool = editorTools[currentMode];
	if (editorTool.data.music)
		document.getElementById("button-music").innerHTML = musicOptions[editorTool.data.music];
	else
		document.getElementById("button-music").innerHTML = "Youtube";
}
var oMusic;
var musicSelected;
function selectMusic(m) {
	if (oMusic) {
		document.body.removeChild(oMusic);
		oMusic = null;
		if (m == musicSelected)
			return;
	}
	musicSelected = m;
	if (musicSelected) {
		oMusic = document.createElement("embed");
		oMusic.src = "musics/maps/map"+ musicSelected +".mp3";
	}
	if (musicSelected) {
		oMusic.setAttribute("loop", true);
		document.body.appendChild(oMusic);
	}
	var mChoices = document.getElementsByClassName("music-selected");
	while (mChoices.length)
		mChoices[0].className = "";
	document.getElementById("musicchoice-"+m).className = "music-selected";
	if (!musicSelected) {
		var $youtubeUrl = document.getElementById("youtube-url");
		$youtubeUrl.select();
		playYt($youtubeUrl);
	}
}
function youtube_parser(url) {
	var regExp = /.*(?:youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=)([^#\&\?]*).*/;
	var match = url.match(regExp);
	return (match&&match[1].length==11)? match[1] : false;
}
function playYt(elt) {
	if (oMusic) {
		document.body.removeChild(oMusic);
		oMusic = null;
	}
	var ytId = youtube_parser(elt.value);
	if (ytId) {
		oMusic = document.createElement("iframe");
		oMusic.id = "ytplayer";
		oMusic.src = "https://www.youtube.com/embed/"+ ytId +"?allow=autoplay&autoplay=1";
		oMusic.setAttribute("allow", "autoplay");
		document.body.appendChild(oMusic);
	}
}
function ytOptions(show) {
	if (show)
		document.querySelector(".youtube").classList.add("youtube-show-advanced");
	else
		document.querySelector(".youtube").classList.remove("youtube-show-advanced");
}
function preSubmitMusic($btn) {
	if (musicSelected) {
		var $elts = $btn.form.elements;
		for (var i=0;i<$elts.length;i++)
			$elts[i].value = "";
	}
}
function zerofill(nb,l) {
	nb += "";
	while (nb.length < l)
		nb = "0"+nb;
	return nb;
}
function timeToStr(time) {
	return Math.floor(time/60)+":"+zerofill(time%60,2);
}
function strToTime(str) {
	var timeCmpts = str.split(":");
	if (timeCmpts.length > 1)
		return +timeCmpts[1] + timeCmpts[0]*60;
	return +timeCmpts[0];
}
function submitMusic(e) {
	e.preventDefault();
	var editorTool = editorTools[currentMode];
	storeHistoryData(editorTool.data);
	editorTool.data.music = musicSelected;
	if (editorTool.data.music) {
		delete editorTool.data.youtube;
		delete editorTool.data.youtube_opts;
	}
	else
		editorTool.data.youtube = document.getElementById("youtube-url").value;
	var ytOptions = {};
	if (document.getElementById("youtube-start").value)
		ytOptions.start = strToTime(document.getElementById("youtube-start").value);
	if (document.getElementById("youtube-end").value)
		ytOptions.end = strToTime(document.getElementById("youtube-end").value);
	if (document.getElementById("youtube-speed").value != 1)
		ytOptions.speed = +document.getElementById("youtube-speed").value;
	if (document.getElementById("youtube-last-url").value) {
		ytOptions.last = {url:document.getElementById("youtube-last-url").value};
		if (document.getElementById("youtube-last-start").value)
			ytOptions.last.start = strToTime(document.getElementById("youtube-last-start").value);
		if (document.getElementById("youtube-last-end").value)
			ytOptions.last.end = strToTime(document.getElementById("youtube-last-end").value);
		if (document.getElementById("youtube-last-speed").value != 1)
			ytOptions.last.speed = +document.getElementById("youtube-last-speed").value;
	}
	if (Object.keys(ytOptions).length)
		editorTool.data.youtube_opts = ytOptions;
	else
		delete editorTool.data.youtube_opts;
	applyMusicSelector();
	hideMusicSelector();
}
function undoMusic() {
	hideMusicSelector();
}
function hideMusicSelector() {
	var $mask = document.getElementById("mask-music");
	$mask.close();
}
function stopMusic() {
	if (oMusic) {
		document.body.removeChild(oMusic);
		oMusic = null;
	}
}
function showHelp() {
	var $help = document.getElementById("help");
	document.body.removeChild($help);
	var $mask = createMask();
	$mask.id = "mask-help";
	$mask.classList.add("mask-dark");
	$mask.appendChild($help);
	$help.classList.add("fs-shown");
	$mask.close = function() {
		$mask.removeChild($help);
		$help.classList.remove("fs-shown");
		document.body.appendChild($help);
		this.defaultClose();
	};
	selectHelpTab(currentMode);
}
function selectHelpTab(mode) {
	document.getElementById("help-buttons").setValue(mode);
	helpChange({value:mode});
}
function closeHelp() {
	document.getElementById("mask-help").close();
}
function showColorSelector() {
	var $mask = createMask();
	var editorTool = editorTools[currentMode];
	var bgColor = editorTool.data.out_color;
	var picker = new Picker({
		parent:$mask,
		popup:false,
		alpha: false,
		color: [bgColor.r,bgColor.g,bgColor.b,1],
		onChange: function(res) {
			var rgb = res.rgba;
			document.body.style.backgroundColor = "rgb("+rgb[0]+","+rgb[1]+","+rgb[2]+")";
		},
		onDone:function(res) {
			storeHistoryData(editorTool.data);
			var bgColor = editorTool.data.out_color;
			var rgb = res.rgba;
			bgColor.r = rgb[0];
			bgColor.g = rgb[1];
			bgColor.b = rgb[2];
			$mask.close();
		},
		onClose: function() {
			$mask.close();
		}
	});
	picker.domElement.onmousedown = function(e) {
		var lastClickEvent = $mask.onclick;
		$mask.onclick = function() {
			$mask.onclick = lastClickEvent;
		};
	}
	picker.domElement.oncontextmenu = function(e) {
		e.stopPropagation();
	}
	$mask.close = function(){
		$mask.defaultClose();
		applyColorSelector();
	};
	picker.openHandler();
}
function resetImageOptions() {
	var $editorImg = document.getElementById("editor-img");
	$editorImg.style.width = "";
	$editorImg.style.height = "";
}
function showImageOptions() {
	var $imageOptions = document.getElementById("image-options");
	document.body.removeChild($imageOptions);
	var $mask = createMask();
	$mask.id = "mask-image";
	$mask.classList.add("mask-dark");
	$mask.appendChild($imageOptions);
	$imageOptions.classList.add("fs-shown");
	$mask.close = function() {
		resetImageOptions();
		$mask.removeChild($imageOptions);
		$imageOptions.classList.remove("fs-shown");
		document.body.appendChild($imageOptions);
		this.defaultClose();
	};
}
function rotateImg(option) {
	if (option < 4) {
		var orientation = option*90;
		for (var key in editorTools) {
			var editorTool = editorTools[key];
			if (editorTool.rotate)
				editorTool.rotate(editorTool,orientation);
		}
		if (option%2) {
			var w = imgSize.w;
			imgSize.w = imgSize.h;
			imgSize.h = w;
		}
	}
	else {
		var axis = {};
		if (option == 4) {
			axis.coord = "x";
			axis.size = "w";
		}
		else {
			axis.coord = "y";
			axis.size = "h";
		}
		for (var key in editorTools) {
			var editorTool = editorTools[key];
			if (editorTool.flip)
				editorTool.flip(editorTool,axis);
		}
	}
	changes = true;
}
function resizeImg(scaleX,scaleY) {
	var scale = {x:scaleX,y:scaleY};
	imgSize.w = Math.round(imgSize.w*scale.x);
	imgSize.h = Math.round(imgSize.h*scale.y);
	for (var key in editorTools) {
		var editorTool = editorTools[key];
		if (editorTool.rescale)
			editorTool.rescale(editorTool,scale);
	}
	changes = true;
}
function saveData() {
	var payload = {main:{theme:document.getElementById("theme-selector").getValue()}};
	for (var key in editorTools) {
		var editorTool = editorTools[key];
		editorTool.save(editorTool,payload);
	}
	var $mask = createMask();
	$mask.classList.add("mask-save");
	$mask.close = function(){};
	var $loading = document.createElement("div");
	$loading.className = "save-popup save-loading";
	$loading.innerHTML = language ? "Saving...":"Sauvegarde...";
	$mask.appendChild($loading);
	var xhr = new XMLHttpRequest();
	xhr.open("POST", isBattle ? "saveCourse.php" : "saveMap.php");
	xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
	xhr.send(JSON.stringify({id:circuitId,payload:payload}));
	xhr.onload = function() {
		if (xhr.responseText == 1) {
			$mask.removeChild($loading);
			$mask.close = $mask.defaultClose;
			changes = false;
			var $success = document.createElement("div");
			$success.onclick = function(e) {
				e.stopPropagation();
			};
			$success.className = "save-popup save-status save-success";
			var $popupMsg = document.createElement("div");
			$popupMsg.className = "save-msg";
			if (isBattle)
				$popupMsg.innerHTML = '<span class="save-icon">✔</span> '+ (language ? "Arena saved":"Arène sauvegardée") +'</span>';
			else
				$popupMsg.innerHTML = '<span class="save-icon">✔</span> '+ (language ? "Circuit saved":"Circuit sauvegardé") +'</span>';
			$success.appendChild($popupMsg);
			var $popupActions = document.createElement("div");
			$popupActions.className = "save-actions";
			var $popupOk = document.createElement("button");
			$popupOk.innerHTML = "Ok";
			$popupOk.onclick = function() {
				$mask.defaultClose();
			};
			$popupActions.appendChild($popupOk);
			var $popupAccess = document.createElement("button");
			$popupAccess.className = "save-access";
			if (isBattle)
				$popupAccess.innerHTML = language ? "Test arena":"Tester arène";
			else
				$popupAccess.innerHTML = language ? "Test circuit":"Tester circuit";
			$popupAccess.onclick = function() {
				document.location.href = (isBattle?"battle":"map")+".php?i="+circuitId;
			};
			$popupActions.appendChild($popupAccess);
			$success.appendChild($popupActions);
			$mask.appendChild($success);
			$popupOk.focus();
		}
		else
			this.onerror();
	};
	xhr.onerror = function() {
		$mask.removeChild($loading);
		$mask.close = $mask.defaultClose;
		var $error = document.createElement("div");
		$error.onclick = function(e) {
			e.stopPropagation();
		};
		$error.className = "save-popup save-status save-failure";
		var $popupMsg = document.createElement("div");
		$popupMsg.className = "save-msg";
		$popupMsg.innerHTML = '<span class="save-icon">×</span> '+ (language ? "Oops, An error occured... Check your connection":"Oups, une erreur est survenue... Vérifiez votre connexion") +'</span>';
		$error.appendChild($popupMsg);
		var $popupActions = document.createElement("div");
		$popupActions.className = "save-actions";
		var $popupRetry = document.createElement("button");
		$popupRetry.innerHTML = language ? "Retry":"Réessayer";
		$popupRetry.onclick = function() {
			$mask.defaultClose();
			saveData();
		};
		$popupActions.appendChild($popupRetry);
		var $popupUndo = document.createElement("button");
		$popupUndo.innerHTML = language ? "Undo":"Annuler";
		$popupUndo.onclick = function() {
			$mask.defaultClose();
		};
		$popupActions.appendChild($popupUndo);
		$error.appendChild($popupActions);
		$mask.appendChild($error);
		$popupRetry.focus();
	};
}
function restoreData(payload) {
	for (var key in editorTools) {
		var editorTool = editorTools[key];
		try {
			editorTool.restore(editorTool,payload);
		}
		catch (e) {
			console.log(e.stack);
		}
	}
	if ("dark" === payload.main.theme) {
		document.getElementById("theme-selector").setValue(payload.main.theme);
		themeChange({value:payload.main.theme});
	}
}
var customDecors = {};
function showDecorEditor() {
	window.open('chooseDecor.php','chose','scrollbars=1, resizable=1, width=500, height=500');
}
function getDecorKey(decor) {
	return "custom-"+decor.id;
}
function getActualDecorType(type) {
	if (customDecors[type])
		return customDecors[type].type;
	return type;
}
function selectCustomDecor(decor) {
	var decorCopy = {};
	for (var key in decor)
		decorCopy[key] = decor[key];
	decor = decorCopy;
	var decorKey = getDecorKey(decor);
	if (customDecors[decorKey]) return;
	customDecors[decorKey] = decor;
	var $btnDecor = document.createElement("button");
	$btnDecor.setAttribute("value", decorKey);
	$btnDecor.className = "radio-button radio-button-25 radio-button-decor button-img fancy-title";
	var $btnAdd = document.getElementById("decor-selector-more");
	$btnAdd.blur();
	var $decorSelector = document.getElementById("decor-selector");
	$decorSelector.insertBefore($btnDecor, $btnAdd);
	initRadioButton($btnDecor, $decorSelector);
	if (decor.ld) {
		feedCustomDecorData($btnDecor,decor);
		if (currentMode === "decor") {
			$decorSelector.setValue(decorKey);
			decorChange();
		}
	}
	return $btnDecor;
}
function feedCustomDecorData($btnDecor,decor) {
	$btnDecor.title = decor.name;
	$btnDecor.style.backgroundImage = "url('"+ decor.ld +"')";
	if ($btnDecor.title)
		initFancyTitle($btnDecor);
}
function fetchCustomDecorData($btnDecor,decor) {
	var xhr = new XMLHttpRequest();
	xhr.open("POST", "getDecorData.php?id="+decor.id);
	xhr.onload = function() {
		var res = JSON.parse(xhr.responseText);
		if (res)
			feedCustomDecorData($btnDecor,res);
	};
	xhr.send(null);
}
var commonTools = {
	"walls": {
		"resume" : function(self) {
			self.state.point = createRectangle({x:-1,y:-1});
			self.state.shape = "rectangle";
			var data = self.data;
			self.data = [];
			for (var i=0;i<data.length;i++) {
				var iData = data[i];
				self.state.shape = iData.type;
				var createdShape;
				switch (iData.type) {
				case "rectangle":
					self.click(self,iData,{});
					createdShape = self.state.rectangle;
					self.click(self,{x:iData.x+iData.w,y:iData.y+iData.h},{});
					break;
				case "polygon":
					for (var j=0;j<iData.points.length;j++)
						self.click(self,iData.points[j],{});
					createdShape = self.state.polygon;
					self.state.nodes[0].circle.onclick();
					break;
				}
				if (createdShape && iData.z !== undefined)
					createdShape.reheight(iData.z);
			}
			replaceNodeType(self);
			document.getElementById("walls-shape").setValue(self.state.shape);
		},
		"click" : function(self,point,extra) {
			var selectedShape = self.state.shape;
			switch (selectedShape) {
			case "rectangle":
				if (self.state.rectangle)
					appendRectangleBuilder(self,point);
				else {
					if (!extra.oob) {
						startRectangleBuilder(self,point, {
							on_apply: function(rectangle,data) {
								self.data.push(data);
								setupShapeHeight(rectangle, data);
								var moveOptions = {on_apply:rectangle.reposition,on_start_move:rectangle.text.hide,on_end_move:rectangle.text.show};
								addContextMenuEvent(rectangle,[{
									text: (language ? "Resize":"Redimensionner"),
									click: function() {
										resizeRectangle(rectangle,data, moveOptions);
									}
								}, {
									text: (language ? "Move":"Déplacer"),
									click: function() {
										moveRectangle(rectangle,data, moveOptions);
									}
								}, {
									text: (language ? "Wall height...":"Hauteur mur..."),
									click: function() {
										rectangle.promptHeight();
									}
								}, {
									text:(language ? "Delete":"Supprimer"),
									click:function() {
										$editor.removeChild(rectangle);
										rectangle.text.remove();
										storeHistoryData(self.data);
										removeFromArray(self.data,data);
									}
								}]);
							}
						});
					}
				}
				break;
			case "polygon":
				if (self.state.polygon)
					appendPolygonBuilder(self,point);
				else {
					if (!extra.oob) {
						startPolygonBuilder(self,point, {
							on_apply: function(polygon,points) {
								var data = {type:"polygon",points:points};
								self.data.push(data);
								polygon.setAttribute("stroke-width", 1);
								setupShapeHeight(polygon, data);
								function repositionPoly(nData) {
									polygon.reposition(Object.assign({}, data, nData));
								}
								var moveOptions = {on_apply:repositionPoly,on_start_move:polygon.text.hide,on_end_move:polygon.text.show};
								addContextMenuEvent(polygon, [{
									text: (language ? "Edit":"Modifier"),
									click: function() {
										editPolygon(polygon,data, moveOptions);
									}
								}, {
									text: (language ? "Move":"Déplacer"),
									click: function() {
										movePolygon(polygon,data, moveOptions);
									}
								}, {
									text: (language ? "Wall height...":"Hauteur mur..."),
									click: function() {
										polygon.promptHeight();
									}
								}, {
									text:(language ? "Delete":"Supprimer"),
									click:function() {
										$editor.removeChild(polygon);
										polygon.text.remove();
										storeHistoryData(self.data);
										removeFromArray(self.data,data);
									}
								}]);
							}
						});
					}
				}
				break;
			}
			function setupShapeHeight(shape, data) {
				var hText = document.createElementNS(SVG, "text");
				hText.setAttribute("class", "dark noclick");
				hText.setAttribute("font-size", 15);
				hText.setAttribute("text-anchor", "middle");
				hText.setAttribute("dominant-baseline", "middle");
				hText.style.display = "none";
				shape.onmouseover = function() {
					hText.style.display = "";
				};
				shape.onmouseout = function() {
					hText.style.display = "none";
				};
				$editor.appendChild(hText);
				hText.remove = function() {
					$editor.removeChild(hText);
				};
				hText.show = function() {
					hText.style.visibility = "";
				};
				hText.hide = function() {
					hText.style.visibility = "hidden";
				};
				shape.text = hText;
				shape.reheight = function(z) {
					storeHistoryData(self.data);
					if (z === "") {
						delete data.z;
						shape.reposition(data);
					}
					else {
						data.z = z;
						shape.reposition(data);
					}
				};
				shape.reposition = function(data) {
					switch (data.type) {
					case "rectangle":
						hText.setAttribute("x", data.x+data.w/2);
						hText.setAttribute("y", data.y+data.h/2+2);
						break;
					case "polygon":
						var polygonCenter = getPolygonCenter(data.points);
						hText.setAttribute("x", polygonCenter.x);
						hText.setAttribute("y", polygonCenter.y+2);
						break;
					}
					hText.innerHTML = data.z == null ? "" : data.z;
				};
				shape.promptHeight = function(prop) {
					var defaultVal = 1;
					var enteredVal = prompt(language ? "Enter value...":"Entrer une valeur...", data.z || defaultVal);
					if (enteredVal == null) return;
					if (enteredVal !== "") {
						enteredVal = +enteredVal;
						if (enteredVal === defaultVal)
							enteredVal = "";
					}
					if (enteredVal >= 0)
						shape.reheight(enteredVal);
				}
			}
		},
		"move" : function(self,point,extra) {
			var selectedShape = self.state.shape;
			switch (selectedShape) {
			case "rectangle":
				moveRectangleBuilder(self,point);
				break;
			case "polygon":
				movePolygonBuilder(self,point);
				break;
			}
		},
		"round_on_pixel" : function(self) {
			return self.state.shape == "polygon";
		},
		"save" : function(self,payload) {
			payload.collision = [];
			var collisionProps = {}, hasCollisionProps = false;
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				payload.collision.push(shapeToData(iData));
				if (iData.z != null) {
					collisionProps[i] = {z:iData.z};
					hasCollisionProps = true;
				}
			}
			if (hasCollisionProps)
				payload.collisionProps = collisionProps;
		},
		"restore" : function(self,payload) {
			var collisionProps = payload.collisionProps || {};
			for (var i=0;i<payload.collision.length;i++) {
				var iData = dataToShape(payload.collision[i]);
				if (collisionProps[i])
					iData.z = collisionProps[i].z;
				self.data.push(iData);
			}
		},
		"rescale" : function(self, scale) {
			for (var i=0;i<self.data.length;i++)
				rescaleShape(self.data[i], scale);
		},
		"rotate" : function(self, orientation) {
			for (var i=0;i<self.data.length;i++)
				rotateShape(self.data[i], imgSize,orientation);
		},
		"flip" : function(self, axis) {
			for (var i=0;i<self.data.length;i++)
				flipShape(self.data[i], imgSize,axis);
		}
	},
	"offroad": {
		"init" : function(self) {
			self.data = [];
			for (var i=0;i<hpTypes.length;i++)
				self.data.push([]);
		},
		"resume" : function(self) {
			self.state.point = createRectangle({x:-1,y:-1});
			var offroadType = +document.getElementById("offroad-type").value;
			var oldData = self.data[offroadType];
			self.data[offroadType] = [];
			self.state.type = offroadType;
			self.state.data = self.data[offroadType];
			self.state.shape = "rectangle";
			for (var i=0;i<oldData.length;i++) {
				var iData = oldData[i];
				self.state.shape = iData.type;
				switch (iData.type) {
				case "rectangle":
					self.click(self,iData,{});
					self.click(self,{x:iData.x+iData.w,y:iData.y+iData.h},{});
					break;
				case "polygon":
					for (var j=0;j<iData.points.length;j++)
						self.click(self,iData.points[j],{});
					self.state.nodes[0].circle.onclick();
					break;
				}
			}
			replaceNodeType(self);
			document.getElementById("offroad-shape").setValue(self.state.shape);
		},
		"click" : function(self,point,extra) {
			var selectedShape = self.state.shape;
			switch (selectedShape) {
			case "rectangle":
				if (self.state.rectangle)
					appendRectangleBuilder(self,point);
				else {
					if (!extra.oob) {
						startRectangleBuilder(self,point, {
							on_apply: function(rectangle,data) {
								self.state.data.push(data);
								addContextMenuEvent(rectangle,[{
									text: (language ? "Resize":"Redimensionner"),
									click: function() {
										resizeRectangle(rectangle,data);
									}
								}, {
									text: (language ? "Move":"Déplacer"),
									click: function() {
										moveRectangle(rectangle,data);
									}
								}, {
									text:(language ? "Delete":"Supprimer"),
									click:function() {
										$editor.removeChild(rectangle);
										storeHistoryData(self.data);
										removeFromArray(self.state.data,data);
									}
								}]);
							}
						});
					}
				}
				break;
			case "polygon":
				if (self.state.polygon)
					appendPolygonBuilder(self,point);
				else {
					if (!extra.oob) {
						startPolygonBuilder(self,point, {
							on_apply: function(polygon,points) {
								var data = {type:"polygon",points:points};
								self.state.data.push(data);
								polygon.setAttribute("stroke-width", 1);
								addContextMenuEvent(polygon, [{
									text: (language ? "Edit":"Modifier"),
									click: function() {
										editPolygon(polygon,data);
									}
								}, {
									text: (language ? "Move":"Déplacer"),
									click: function() {
										movePolygon(polygon,data);
									}
								}, {
									text:(language ? "Delete":"Supprimer"),
									click:function() {
										$editor.removeChild(polygon);
										storeHistoryData(self.data);
										removeFromArray(self.state.data,data);
									}
								}]);
							}
						});
					}
				}
				break;
			}
		},
		"move" : function(self,point,extra) {
			var selectedShape = self.state.shape;
			switch (selectedShape) {
			case "rectangle":
				moveRectangleBuilder(self,point);
				break;
			case "polygon":
				movePolygonBuilder(self,point);
				break;
			}
		},
		"round_on_pixel" : function(self) {
			return self.state.shape == "polygon";
		},
		"save" : function(self,payload) {
			payload.horspistes = {};
			for (var i=0;i<hpTypes.length;i++) {
				var iData = self.data[i];
				if (iData.length) {
					var iPayload = [];
					for (var j=0;j<iData.length;j++)
						iPayload.push(shapeToData(iData[j]));
					payload.horspistes[hpTypes[i]] = iPayload;
				}
			}
		},
		"restore" : function(self,payload) {
			for (var i=0;i<hpTypes.length;i++) {
				var iPayload = payload.horspistes[hpTypes[i]];
				var iData = [];
				if (iPayload) {
					for (var j=0;j<iPayload.length;j++)
						iData.push(dataToShape(iPayload[j]));
				}
				self.data[i] = iData;
			}
		},
		"rescale" : function(self, scale) {
			for (var i=0;i<hpTypes.length;i++) {
				var iData = self.data[i];
				for (var j=0;j<iData.length;j++)
					rescaleShape(iData[j], scale);
			}
		},
		"rotate" : function(self, orientation) {
			for (var i=0;i<hpTypes.length;i++) {
				var iData = self.data[i];
				for (var j=0;j<iData.length;j++)
					rotateShape(iData[j], imgSize,orientation);
			}
		},
		"flip" : function(self, axis) {
			for (var i=0;i<hpTypes.length;i++) {
				var iData = self.data[i];
				for (var j=0;j<iData.length;j++)
					flipShape(iData[j], imgSize,axis);
			}
		}
	},
	"holes": {
		"_shape_selector_id": "holes-shape",
		"resume" : function(self) {
			self.state.point = createRectangle({x:-1,y:-1});
			self.state.orientation = 2;
			self.state.shape = "rectangle";
			var data = self.data;
			self.data = [];
			for (var i=0;i<data.length;i++) {
				var iData = data[i];
				self.state.shape = iData.type;
				if (undefined !== iData.orientation)
					self.state.orientation = iData.orientation;
				switch (iData.type) {
				case "rectangle":
					self.click(self,iData,{});
					self.click(self,{x:iData.x+iData.w,y:iData.y+iData.h},{});
					break;
				case "polygon":
					for (var j=0;j<iData.points.length;j++)
						self.click(self,iData.points[j],{});
					self.state.nodes[0].circle.onclick();
					break;
				}
				if (iData.respawn)
					self.click(self,iData.respawn,{});
			}
			replaceNodeType(self);
			document.getElementById(self._shape_selector_id).setValue(self.state.shape);
		},
		"click" : function(self,point,extra) {
			var respawnNode = self.state.respawnNode;
			if (respawnNode) {
				if (extra.oob)
					return;
				storeHistoryData(self.data);
				var data = self.data[self.data.length-1];
				data.respawn = point;
				respawnNode.move(point);
				var lastRotateTime = 0;
				respawnNode.origin.classList.add("hover-toggle");
				respawnNode.origin.onclick = function(e) {
					if (e) e.stopPropagation();
					var nRotateTime = new Date().getTime();
					if (nRotateTime > lastRotateTime+2500)
						storeHistoryData(self.data);
					lastRotateTime = nRotateTime;
					data.orientation = (data.orientation+1)%4;
					self.state.orientation = data.orientation;
					respawnNode.rotate(data.orientation);
				};
				var shape = self.state.currentshape;
				delete self.state.currentshape;
				function deleteItem() {
					$editor.removeChild(shape);
					$editor.removeChild(respawnNode.origin);
					var lines = respawnNode.lines;
					for (var i=0;i<lines.length;i++)
						$editor.removeChild(lines[i]);
					storeHistoryData(self.data);
					removeFromArray(self.data,data);
				}
				respawnNode.origin.oncontextmenu = function(e) {
					return showContextOnElt(e,shape, [{
						text: (language ? "Rotate":"Pivoter"),
						click: function() {
							respawnNode.origin.onclick();
						}
					}, {
						text: (language ? "Move":"Déplacer"),
						click: function() {
							moveArrowNode(respawnNode,data.respawn);
						}
					}, {
						text:(language ? "Delete":"Supprimer"),
						click:function() {
							deleteItem();
						}
					}]);
				};
				switch (data.type) {
				case "rectangle":
					addContextMenuEvent(shape,[{
						text: (language ? "Resize":"Redimensionner"),
						click: function() {
							resizeRectangle(shape,data);
						}
					}, {
						text: (language ? "Move":"Déplacer"),
						click: function() {
							moveRectangle(shape,data);
						}
					}, {
						text:(language ? "Delete":"Supprimer"),
						click:function() {
							deleteItem();
						}
					}]);
					break;
				case "polygon":
					addContextMenuEvent(shape, [{
						text: (language ? "Edit":"Modifier"),
						click: function() {
							editPolygon(shape,data);
						}
					}, {
						text: (language ? "Move":"Déplacer"),
						click: function() {
							movePolygon(shape,data);
						}
					}, {
						text:(language ? "Delete":"Supprimer"),
						click:function() {
							deleteItem();
						}
					}]);
					break;
				}
				delete self.state.respawnNode;
			}
			else {
				var selectedShape = self.state.shape;
				switch (selectedShape) {
				case "rectangle":
					if (self.state.rectangle)
						appendRectangleBuilder(self,point);
					else {
						if (!extra.oob) {
							startRectangleBuilder(self,point, {
								on_apply: function(rectangle,data) {
									self.state.currentshape = rectangle;
									data.orientation = self.state.orientation;
									self.data.push(data);
									self.state.respawnNode = createArrowNode({x:-10,y:-10},self.state.orientation);
								}
							});
						}
					}
					break;
				case "polygon":
					if (self.state.polygon)
						appendPolygonBuilder(self,point);
					else {
						if (!extra.oob) {
							startPolygonBuilder(self,point, {
								on_apply: function(polygon,points) {
									self.state.currentshape = polygon;
									var data = {type:"polygon",points:points};
									data.orientation = self.state.orientation;
									self.data.push(data);
									polygon.setAttribute("stroke-width", 1);
									self.state.respawnNode = createArrowNode(deepCopy(point),self.state.orientation);
								}
							});
						}
					}
					break;
				}
			}
		},
		"move" : function(self,point,extra) {
			if (self.state.respawnNode)
				self.state.respawnNode.move(point);
			else {
				var selectedShape = self.state.shape;
				switch (selectedShape) {
				case "rectangle":
					moveRectangleBuilder(self,point);
					break;
				case "polygon":
					movePolygonBuilder(self,point);
					break;
				}
			}
		},
		"round_on_pixel" : function(self) {
			return self.state.respawnNode || (self.state.shape == "polygon");
		},
		"save" : function(self,payload) {
			payload.trous = [[],[],[],[]];
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				var shape = shapeToData(iData);
				var respawn = nullablePointToData(iData.respawn);
				payload.trous[iData.orientation||0].push([shape,respawn]);
			}
		},
		"restore" : function(self,payload) {
			for (var k=0;k<2;k++) {
				for (var j=0;j<4;j++) {
					for (var i=0;i<payload.trous[j].length;i++) {
						var iPayload = payload.trous[j][i];
						var iData = dataToShape(iPayload[0]);
						iData.respawn = dataToNullablePoint(iPayload[1]);
						if (iData.respawn)
							iData.orientation = j;
						if (k == !iData.respawn)
							self.data.push(iData);
					}
				}
			}
		},
		"rescale" : function(self, scale) {
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				rescaleShape(iData, scale);
				rescaleNullablePoint(iData.respawn, scale);
			}
		},
		"rotate" : function(self, orientation) {
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				rotateShape(iData, imgSize,orientation);
				rotateNullablePoint(iData.respawn, imgSize,orientation);
				if (undefined !== iData.orientation)
					iData.orientation = (iData.orientation + 4-orientation/90)%4;
			}
		},
		"flip" : function(self, axis) {
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				flipShape(iData, imgSize,axis);
				flipNullablePoint(iData.respawn, imgSize,axis);
				if ((iData.orientation%2) == (axis.coord=="x" ? 1:0))
					iData.orientation = (iData.orientation+2)%4;
			}
		}
	},
	"items": {
		"resume" : function(self) {
			self.state.boxSize = {w:8,h:8};
			self.state.point = createBox(self.state.boxSize);
			self.state.point.classList.add("noclick");
			var data = self.data;
			self.data = [];
			for (var i=0;i<data.length;i++) {
				var iData = data[i];
				self.click(self,iData,{});
			}
		},
		"click" : function(self,point,extra) {
			if (extra.oob)
				return;
			self.move(self,point,extra);
			storeHistoryData(self.data);
			self.data.push(point);

			var boxCntText = document.createElementNS(SVG, "text");
			boxCntText.setAttribute("class", "dark noclick");
			boxCntText.setAttribute("font-size", 8);
			boxCntText.setAttribute("text-anchor", "middle");
			boxCntText.setAttribute("dominant-baseline", "middle");

			var box = self.state.point;
			box.retext = function() {
				boxCntText.innerHTML = point.nb || "";
			};
			box.reposition = function(nData) {
				boxCntText.setAttribute("x", nData.x);
				boxCntText.setAttribute("y", nData.y+1);
			};
			box.retext();
			box.reposition(point);
			$editor.appendChild(boxCntText);

			box.classList.remove("noclick");
			box.oncontextmenu = function(e) {
				hideBox(self.state.point,self.state.boxSize);
				var doubleCheck = (point.nb > 1) ? "✔ ":"";
				var moveOptions = {
					on_apply: function(nData) {
						box.reposition(nData);
					},
					on_start_move: function() {
						boxCntText.style.display = "none";
					},
					on_end_move: function() {
						boxCntText.style.display = "";
					}
				};
				return showContextOnElt(e,box,[{
					text: (language ? "Move":"Déplacer"),
					click: function() {
						moveBox(box,point,self.state.boxSize, moveOptions);
					}
				}, {
					text:(language ? "Delete":"Supprimer"),
					click:function() {
						$editor.removeChild(box);
						$editor.removeChild(boxCntText);
						storeHistoryData(self.data);
						removeFromArray(self.data,point);
					}
				}, {
					text: doubleCheck + (language ? "Double item":"Double objet"),
					click:function() {
						storeHistoryData(self.data);
						if (point.nb > 1)
							delete point.nb;
						else
							point.nb = 2;
						box.retext();
					}
				}]);
			};
			self.state.point = createBox(self.state.boxSize);
			self.state.point.classList.add("noclick");
		},
		"move" : function(self,point,extra) {
			setBoxPos(self.state.point,point,self.state.boxSize);
		},
		"save" : function(self,payload) {
			payload.arme = [];
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				var iPayload = pointToData(iData);
				if (iData.nb)
					iPayload.push(iData.nb);
				payload.arme.push(iPayload);
			}
		},
		"restore" : function(self,payload) {
			for (var i=0;i<payload.arme.length;i++) {
				var iPayload = payload.arme[i];
				var iData = dataToPoint(iPayload);
				if (iPayload.length > 2)
					iData.nb = iPayload[2];
				self.data.push(iData);
			}
		},
		"rescale" : function(self, scale) {
			for (var i=0;i<self.data.length;i++)
				rescaleBox(self.data[i], scale);
		},
		"rotate" : function(self, orientation) {
			for (var i=0;i<self.data.length;i++)
				rotateBox(self.data[i], imgSize,orientation);
		},
		"flip" : function(self, axis) {
			for (var i=0;i<self.data.length;i++)
				flipBox(self.data[i], imgSize,axis);
		}
	},
	"jumps": {
		"resume" : function(self) {
			self.state.point = createRectangle({x:-1,y:-1});
			self.state.point.classList.add("noclick");
			self.state.shape = "rectangle";
			var data = self.data;
			self.data = [];
			for (var i=0;i<data.length;i++) {
				var iData = data[i];
				self.state.shape = iData.type;
				var shape;
				switch (iData.type) {
				case "rectangle":
					self.click(self,iData,{});
					shape = self.state.rectangle;
					self.click(self,{x:iData.x+iData.w,y:iData.y+iData.h},{});
					break;
				case "polygon":
					for (var j=0;j<iData.points.length;j++)
						self.click(self,iData.points[j],{});
					shape = self.state.polygon;
					self.state.nodes[0].circle.onclick();
					break;
				}
				if (shape && data[i].dir != null)
					shape.createArrow(data[i].dir);
			}
			document.getElementById("jumps-shape").setValue(self.state.shape);
		},
		"click" : function(self,point,extra) {
			var selectedShape = self.state.shape;
			function onApply(data, shape) {
				self.data.push(data);
				var arrow;
				shape.createArrow = function(dir) {
					if (arrow) return arrow;
					dir = dir || {x:0,y:0};
					data.dir = dir;
					var center = shape.getCenter(data);
					arrow = createArrow(center,center);
					arrow.move(center,{x:center.x+dir.x,y:center.y+dir.y});
					var arrowCtxMenu = [{
						text: (language ? "Move":"Déplacer"),
						click: function() {
							var center = {x:data.x+data.w/2,y:data.y+data.h/2};
							moveArrow(arrow,center,data.dir);
						}
					}, {
						text: (language ? "Delete":"Supprimer"),
						click: function() {
							storeHistoryData(self.data);
							arrow.remove();
							delete data.dir;
							arrow = undefined;
						}
					}];
					for (var i=0;i<arrow.lines.length;i++)
						addContextMenuEvent(arrow.lines[i], arrowCtxMenu);
					return arrow;
				}
				shape.hideArrow = function() {
					if (arrow)
						arrow.hide();
				}
				shape.showArrow = function() {
					if (arrow)
						arrow.show();
				}
				shape.reshapeArrow = function(nData) {
					if (arrow) {
						var center = shape.getCenter(nData);
						arrow.move(center,{x:center.x+nData.dir.x,y:center.y+nData.dir.y});
					}
				}
				shape.remove = function() {
					$editor.removeChild(shape);
					if (arrow)
						arrow.remove();
					storeHistoryData(self.data);
					removeFromArray(self.data,data);
				}
			}
			function getRectCenter(nData) {
				return {x:nData.x+nData.w/2,y:nData.y+nData.h/2};
			}
			function getPolyCenter(nData) {
				return getPolygonCenter(nData.points);
			}
			switch (selectedShape) {
			case "rectangle":
				if (self.state.rectangle)
					appendRectangleBuilder(self,point);
				else {
					if (!extra.oob) {
						startRectangleBuilder(self,point, {
							on_apply: function(rectangle,data) {
								onApply(data, rectangle);
								rectangle.getCenter = getRectCenter;
								addContextMenuEvent(rectangle, [{
									text: (language ? "Resize":"Redimensionner"),
									click: function() {
										resizeRectangle(rectangle,data, {on_apply:rectangle.reshapeArrow});
									}
								}, {
									text: (language ? "Move":"Déplacer"),
									click: function() {
										moveRectangle(rectangle,data, {on_apply:rectangle.reshapeArrow,on_start_move:rectangle.hideArrow,on_end_move:rectangle.showArrow});
									}
								}, {
									text: (language ? "Jump height...":"Hauteur saut..."),
									click: function() {
										var arrow = rectangle.createArrow();
										moveArrow(arrow,rectangle.getCenter(data),data.dir);
									}
								}, {
									text:(language ? "Delete":"Supprimer"),
									click:function() {
										rectangle.remove();
									}
								}]);
							}
						});
					}
				}
				break;
			case "polygon":
				if (self.state.polygon)
					appendPolygonBuilder(self,point);
				else {
					if (!extra.oob) {
						startPolygonBuilder(self,point, {
							on_apply: function(polygon,points) {
								var data = {type:"polygon",points:points};
								onApply(data, polygon);
								polygon.getCenter = getPolyCenter;
								addContextMenuEvent(polygon, [{
									text: (language ? "Edit":"Modifier"),
									click: function() {
										editPolygon(polygon,data, {on_apply:polygon.reshapeArrow});
									}
								}, {
									text: (language ? "Move":"Déplacer"),
									click: function() {
										movePolygon(polygon,data, {on_apply:polygon.reshapeArrow,on_start_move:polygon.hideArrow,on_end_move:polygon.showArrow});
									}
								}, {
									text: (language ? "Jump height...":"Hauteur saut..."),
									click: function() {
										var arrow = polygon.createArrow();
										moveArrow(arrow,polygon.getCenter(data),data.dir);
									}
								}, {
									text:(language ? "Delete":"Supprimer"),
									click:function() {
										polygon.remove();
									}
								}]);
							}
						});
					}
				}
			}
		},
		"move" : function(self,point,extra) {
			var selectedShape = self.state.shape;
			switch (selectedShape) {
			case "rectangle":
				moveRectangleBuilder(self,point);
				break;
			case "polygon":
				movePolygonBuilder(self,point);
				break;
			}
		},
		"_arrowFactor": 32.4,
		"save" : function(self,payload) {
			payload.sauts = [];
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				var data = shapeToData(iData);
				if (iData.type !== "rectangle")
					data = [data];
				if (iData.dir) {
					var length = Math.hypot(iData.dir.x,iData.dir.y);
					var angle = Math.atan2(iData.dir.y,iData.dir.x) || 0;
					data.push(length/self._arrowFactor,angle);
				}
				payload.sauts.push(data);
			}
		},
		"restore" : function(self,payload) {
			for (var i=0;i<payload.sauts.length;i++) {
				var shape, dir, angle;
				var iData = payload.sauts[i];
				if (typeof iData[0] === "number") {
					shape = dataToRect(iData);
					shape.type = "rectangle";
					dir = iData[4];
					angle = iData[5];
				}
				else {
					shape = dataToShape(iData[0]);
					dir = iData[1];
					angle = iData[2];
				}
				if (dir != null) {
					var length = dir*self._arrowFactor;
					angle = angle || 0;
					shape.dir = {
						x: length*Math.cos(angle),
						y: length*Math.sin(angle)
					};
				}
				self.data.push(shape);
			}
		},
		"rescale" : function(self, scale) {
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				rescaleShape(iData, scale);
				if (iData.dir)
					rescaleDir(iData.dir, scale);
			}
		},
		"rotate" : function(self, orientation) {
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				rotateShape(iData, imgSize,orientation);
				if (iData.dir)
					rotateDir(iData.dir, orientation);
			}
		},
		"flip" : function(self, axis) {
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				flipShape(iData, imgSize,axis);
				if (iData.dir)
					flipDir(iData.dir, axis);
			}
		}
	},
	"boosts": {
		"resume" : function(self) {
			self.state.boxSize = {w:8,h:8};
			self.state.point = createBox(self.state.boxSize);
			self.state.point.classList.add("noclick");
			var data = self.data;
			self.data = [];
			for (var i=0;i<data.length;i++) {
				var iData = data[i];
				var boxSize = {w:iData.w,h:iData.h};
				updateBoxSize(self.state.point,boxSize);
				self.state.boxSize = boxSize;
				self.click(self,iData,{});
			}
			boostSizeChanged();
		},
		"click" : function(self,data,extra) {
			if (extra.oob)
				return;
			self.move(self,data,extra);
			data.w = self.state.boxSize.w;
			data.h = self.state.boxSize.h;
			storeHistoryData(self.data);
			self.data.push(data);
			var box = self.state.point;
			box.classList.remove("noclick");
			box.oncontextmenu = function(e) {
				hideBox(self.state.point,self.state.boxSize);
				return showContextOnElt(e,box,[{
					text: (language ? "Move":"Déplacer"),
					click: function() {
						moveBox(box,data,data);
					}
				}, {
					text:(language ? "Delete":"Supprimer"),
					click:function() {
						$editor.removeChild(box);
						storeHistoryData(self.data);
						removeFromArray(self.data,data);
					}
				}]);
			};
			self.state.point = createBox(self.state.boxSize);
			self.state.point.classList.add("noclick");
		},
		"move" : function(self,point,extra) {
			setBoxPosRound(self.state.point,point,self.state.boxSize);
		},
		"save" : function(self,payload) {
			payload.accelerateurs = [];
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				var iPayload = pointToData(iData);
				iPayload[0] = Math.round(iPayload[0]-iData.w/2);
				iPayload[1] = Math.round(iPayload[1]-iData.h/2);
				if (iData.w != 8 || iData.h != 8) {
					iPayload[2] = iData.w;
					iPayload[3] = iData.h;
				}
				payload.accelerateurs.push(iPayload);
			}
		},
		"restore" : function(self,payload) {
			for (var i=0;i<payload.accelerateurs.length;i++) {
				var iPayload = payload.accelerateurs[i];
				var iData = dataToPoint(iPayload);
				if (iPayload[2] && iPayload[3]) {
					iData.w = iPayload[2];
					iData.h = iPayload[3];
				}
				else {
					iData.w = 8;
					iData.h = 8;
				}
				iData.x += Math.floor(iData.w/2);
				iData.y += Math.floor(iData.h/2);
				self.data.push(iData);
			}
		},
		"rescale" : function(self, scale) {
			for (var i=0;i<self.data.length;i++)
				rescaleBox(self.data[i], scale);
		},
		"rotate" : function(self, orientation) {
			for (var i=0;i<self.data.length;i++)
				rotateBox(self.data[i], imgSize,orientation);
		},
		"flip" : function(self, axis) {
			for (var i=0;i<self.data.length;i++)
				flipBox(self.data[i], imgSize,axis);
		}
	},
	"decor": {
		"init" : function(self) {
			self.data = {decors:{},extra:{}};
		},
		"resume" : function(self) {
			self.state.boxSize = {w:8,h:8};
			self.state.point = createBox(self.state.boxSize);
			self.state.point.classList.add("noclick");
			var type = document.getElementById("decor-selector").getValue();
			var autoSelectType;
			if (!type) {
				autoSelectType = true;
				for (type in self.data.decors)
					;
			}
			else
				autoSelectType = false;
			var $decorOptionSelected = document.querySelector("#decor-options > .decor-option-selected");
			if ($decorOptionSelected) $decorOptionSelected.classList.remove("decor-option-selected");
			if (type) {
				var actualType = getActualDecorType(type);
				self.state.type = type;
				switch (actualType) {
				case "truck":
					if (!self.data.extra.truck) {
						self.data.extra.truck = {
							"route": [{points:[],closed:false}]
						};
						initRouteSelector(document.getElementById("decor-bus-traject"),1);
					}
					self.state.selectedTool = 0;
					if (document.getElementById("decor-option-truck").className === "decor-option-bus-trajects") {
						self.data.extra.truck.helped = true;
						self.state.selectedTool = 1;
					}
					self.state.helped = self.data.extra.truck.helped;
					self.state.route = [];
					var routeData = self.data.extra.truck.route;
					if (self.state.selectedTool == 0) {
						self.state.currentTraject = 0;
						var $currentTrajectSelector = document.getElementById("decor-bus-currenttraject");
						$currentTrajectSelector.innerHTML = "";
						for (var i=0;i<routeData.length;i++) {
							var $currentTrajectOption = document.createElement("option");
							$currentTrajectOption.value = i;
							$currentTrajectOption.innerHTML = (i+1);
							$currentTrajectSelector.appendChild($currentTrajectOption);
						}
						$currentTrajectSelector.selectedIndex = document.getElementById("decor-bus-traject").selectedIndex;
					}
					if ((routeData.length == 1) && !routeData[0].length && !self.state.helped) {
						setTimeout(function() {
							var oHelp = document.createElement("div");
							oHelp.className = "help-balloon";
							var oHelpClose = document.createElement("a");
							oHelpClose.href = "#null";
							oHelpClose.innerHTML = "&times;";
							oHelp.close = function() {
								document.body.removeChild(oHelp);
								document.getElementById("mode-option-decor").removeEventListener("click", oHelp.close);
							};
							oHelpClose.onclick = function() {
								oHelp.close();
								self.state.helped = true;
								self.data.extra.truck.helped = true;
								return false;
							};
							oHelp.appendChild(oHelpClose);
							var oHelpMsg = document.createElement("div");
							oHelpMsg.innerHTML = language ? "You can manage bus routes here":"Vous pouvez gérer les trajets des bus ici";
							var $manageRouteLink = document.querySelector("#decor-bus-decors");
							var routeLinkRect = $manageRouteLink.getBoundingClientRect();
							oHelp.style.left = Math.round(routeLinkRect.left + (routeLinkRect.width-150)/2) +"px";
							oHelp.style.top = (routeLinkRect.bottom+5) +"px";
							oHelp.appendChild(oHelpMsg);
							document.body.appendChild(oHelp);
							document.getElementById("mode-option-decor").addEventListener("click", oHelp.close);
						}, 1);
					}
					if (self.state.selectedTool == 1) {
						var traject = +document.getElementById("decor-bus-traject").value;
						initRouteBuilder(self,routeData,traject);
					}
					break;
				}
				if (self.state.traject === undefined) {
					var decorsData = self.data.decors[type]||[];
					self.data.decors[type] = [];
					for (var i=0;i<decorsData.length;i++) {
						var decorData = decorsData[i];
						switch (actualType) {
						case "truck":
							self.state.currentTraject = decorData.traject;
							break;
						}
						self.click(self,decorData.pos,{});
						switch (actualType) {
						case "cannonball":
						case "snowball":
						case "billball":
						case "movingthwomp":
						case "assets/pivothand":
						case "firering":
						case "fire3star":
						case "pendulum":
							if (decorData.dir) {
								self.click(self,{x:decorData.pos.x+decorData.dir.x,y:decorData.pos.y+decorData.dir.y},{});
								if (decorData.dtheta) {
									var angle0 = Math.atan2(decorData.dir.y,decorData.dir.x);
									var angle1 = angle0 + decorData.dtheta;
									self.click(self,{x:decorData.pos.x+Math.cos(angle1),y:decorData.pos.y+Math.sin(angle1)},{});
								}
							}
							break;
						case "assets/bumper":
							if (decorData.r)
								self.click(self,{x:decorData.pos.x+decorData.r,y:decorData.pos.y},{});
							break;
						}
					}
					switch (actualType) {
					case "truck":
						self.state.currentTraject = +document.getElementById("decor-bus-currenttraject").value;
						break;
					}
				}
				if (autoSelectType)
					document.getElementById("decor-selector").setValue(type);
				var $decorOption = document.getElementById("decor-option-"+actualType);
				if ($decorOption) $decorOption.classList.add("decor-option-selected");
			}
		},
		"click" : function(self,point,extra) {
			if (extra.oob)
				return;
			if (!self.state.type) {
				alert(language ? "Please select a decor type first":"Sélectionnez un type de décor avant de commencer");
				return;
			}
			var actualType = getActualDecorType(self.state.type);
			self.move(self,point,extra);
			if (self.state.traject === undefined)
				storeHistoryData(self.data);
			var over = true;
			var typeData = self.data.decors[self.state.type];
			var decorData;
			if (self.state.carrow) {
				decorData = typeData[typeData.length-1];
				var theta0 = Math.atan2(decorData.dir.y,decorData.dir.x);
				var decorPos = decorData.pos;
				decorData.dtheta = getDeltaAngle(theta0,decorPos,point);
			}
			else if (self.state.arrow || self.state.line) {
				decorData = typeData[typeData.length-1];
				decorData.dir = {x:point.x-decorData.pos.x,y:point.y-decorData.pos.y};
				switch (actualType) {
					case "assets/pivothand":
						over = false;
						var arrowData = {x:decorData.pos.x,y:decorData.pos.y,r:self._arrowCircularRadius(decorData.dir)};
						var carrow = createCircularArrow(arrowData,Math.atan2(decorData.dir.y,decorData.dir.x),0,true,{thickness:4});
						for (var i=0;i<carrow.lines.length;i++)
							carrow.lines[i].classList.add("noclick");
						self.state.carrow = carrow;
						break;
				}
			}
			else if (self.state.circle) {
				decorData = typeData[typeData.length-1];
				decorData.r = Math.hypot(point.x-decorData.pos.x, point.y-decorData.pos.y);
			}
			else if (self.state.traject !== undefined) {
				appendRouteBuilder(self,point,extra);
				over = false;
			}
			else {
				decorData = {pos:point};
				typeData.push(decorData);
				switch (actualType) {
				case "cannonball":
				case "snowball":
				case "billball":
				case "movingthwomp":
				case "firering":
				case "fire3star":
				case "pendulum":
					over = false;
					var arrow = createArrow({x:point.x,y:point.y},{x:point.x,y:point.y});
					for (var i=0;i<arrow.lines.length;i++)
						arrow.lines[i].classList.add("noclick");
					self.state.arrow = arrow;
					break;
				case "assets/bumper":
					over = false;
					var circle = createCircle({x:point.x,y:point.y,r:0});
					self.state.circle = circle;
					break;
				case "assets/pivothand":
					over = false;
					var line = createLine({x:point.x,y:point.y},{x:point.x,y:point.y});
					line.classList.add("noclick");
					var stroke = 3;
					addZoomListener(line, function() {
						this.setAttribute("stroke-width", stroke/zoomLevel);
					});
					self.state.line = line;
					break;
				case "truck":
					decorData.traject = self.state.currentTraject;
				}
			}
			if (over) {
				var box = self.state.point;
				var arrow = self.state.arrow;
				delete self.state.arrow;
				var circle = self.state.circle;
				delete self.state.circle;
				var line = self.state.line;
				delete self.state.line;
				var carrow = self.state.carrow;
				delete self.state.carrow;
				var moveOptions = {};
				if (arrow) {
					moveOptions.on_apply = function(nData) {
						changeArrowDir(arrow,{x:nData.x,y:nData.y},decorData.dir,self._arrowLength(actualType),self._arrowOriginCenter(actualType));
					}
					moveOptions.on_start_move = arrow.hide;
					moveOptions.on_end_move = arrow.show;
				}
				else if (circle) {
					moveOptions.on_apply = function(nData) {
						setCirclePos(circle,nData);
					};
					moveOptions.on_start_move = function() {
						circle.style.display = "none";
					};
					moveOptions.on_end_move = function() {
						circle.style.display = "";
					};
				}
				else if (line) {
					moveOptions.on_apply = function(nData) {
						moveLine(line,{x:nData.x,y:nData.y},{x:nData.x+decorData.dir.x,y:nData.y+decorData.dir.y});
						if (carrow)
							carrow.move({x:nData.x,y:nData.y,r:self._arrowCircularRadius(decorData.dir)});
					}
					moveOptions.on_start_move = function() {
						line.style.display = "none";
						if (carrow)
							carrow.hide();
					};
					moveOptions.on_end_move = function() {
						line.style.display = "";
						if (carrow)
							carrow.show();
					};
				}
				box.classList.remove("noclick");
				box.oncontextmenu = function(e) {
					hideBox(self.state.point,self.state.boxSize);
					var menuOptions = [{
						text: (language ? "Move":"Déplacer"),
						click: function() {
							moveBox(box,decorData.pos,self.state.boxSize,moveOptions);
						}
					}, {
						text:(language ? "Delete":"Supprimer"),
						click:function() {
							$editor.removeChild(box);
							if (arrow) arrow.remove();
							else if (line) $editor.removeChild(line);
							if (carrow) carrow.remove();
							if (circle) $editor.removeChild(circle);
							storeHistoryData(self.data);
							removeFromArray(typeData,decorData);
						}
					}];
					if (arrow) {
						menuOptions.splice(1,0, {
							text: (language ? "Edit ↗":"Modifier ↗"),
							click: function() {
								moveArrow(arrow,decorData.pos,decorData.dir,{fixed_length:self._arrowLength(actualType),from_center:self._arrowOriginCenter(actualType)});
							}
						});
					}
					else if (circle) {
						menuOptions.splice(1,0, {
							text: (language ? "Resize":"Redimensionner"),
							click: function() {
								editCircle(circle,decorData.pos,{
									on_apply: function(nData) {
										decorData.r = nData.r;
									}
								});
							}
						});
					}
					else if (line) {
						menuOptions.splice(1,0, {
							text: (language ? "Edit /":"Modifier /"),
							click: function() {
								if (carrow)
									carrow.hide();
								editLine(line,decorData.pos,decorData.dir, {
									on_apply: function(nData) {
										if (carrow) {
											carrow.move({x:decorData.pos.x,y:decorData.pos.y,r:self._arrowCircularRadius(nData)},Math.atan2(nData.y,nData.x));
											carrow.show();
										}
									}
								});
							}
						});
						if (carrow) {
							menuOptions.splice(2,0, {
								text: (language ? "Edit ↗":"Modifier ↗"),
								click: function() {
									var center = {x:decorData.pos.x,y:decorData.pos.y,theta0:Math.atan2(decorData.dir.y,decorData.dir.x)};
									moveCircularArrow(carrow,center,decorData);
								}
							});
						}
					}
					else if (decorData.traject !== undefined) {
						menuOptions.splice(1,0, {
							text: (language ? "Route...":"Trajet..."),
							click: function() {
								var newTraject = prompt(language ? "Edit route number:":"Modifier n° trajet :", 1+decorData.traject)-1;
								if ((newTraject != decorData.traject) && (newTraject >= 0) && (newTraject < self.data.extra.truck.route.length)) {
									storeHistoryData(self.data);
									decorData.traject = newTraject;
								}
							}
						});
					}
					return showContextOnElt(e,box,menuOptions);
				};
				if (circle)
					circle.oncontextmenu = box.oncontextmenu;
				self.state.point = createBox(self.state.boxSize);
				self.state.point.classList.add("noclick");
			}
		},
		"_arrowLength": function(type) {
			switch (type) {
			case "billball":
			case "movingthwomp":
			case "assets/pivothand":
				return null;
			default:
				return 25;
			}
		},
		"_arrowCircularRadius": function(dir) {
			return Math.hypot(dir.x,dir.y)*0.8;
		},
		"_arrowOriginCenter": function(type) {
			return (["firering","fire3star","pendulum"].indexOf(type) !== -1);
		},
		"_rotFactor" : 15,
		"_rotScale" : 1.5,
		"move" : function(self,point,extra) {
			var actualType = getActualDecorType(self.state.type);
			if (self.state.carrow) {
				var typeData = self.data.decors[self.state.type];
				var decorData = typeData[typeData.length-1];
				var theta0 = Math.atan2(decorData.dir.y,decorData.dir.x);
				var decorPos = decorData.pos;
				var dAngle = getDeltaAngle(theta0,decorPos,point);
				self.state.carrow.move(null,null,dAngle);
			}
			else if (self.state.arrow) {
				var typeData = self.data.decors[self.state.type];
				var decorPos = typeData[typeData.length-1].pos;
				var dir = {x:point.x-decorPos.x,y:point.y-decorPos.y};
				changeArrowDir(self.state.arrow,decorPos,dir,self._arrowLength(actualType),self._arrowOriginCenter(actualType));
			}
			else if (self.state.circle) {
				var typeData = self.data.decors[self.state.type];
				var decorPos = typeData[typeData.length-1].pos;
				var r = Math.hypot(point.x-decorPos.x,point.y-decorPos.y);
				self.state.circle.setAttribute("r", r);
			}
			else if (self.state.line) {
				moveLine(self.state.line,null,point);
			}
			else if (self.state.traject !== undefined) {
				moveRouteBuilder(self,point,extra);
			}
			else
				setBoxPos(self.state.point,point,self.state.boxSize);
		},
		"round_on_pixel" : function(self) {
			if (self.state.traject !== undefined)
				return true;
			return false;
		},
		"save" : function(self,payload) {
			var selfData = self.data.decors;
			payload.decor = {};
			payload.decorparams = {extra:{}};
			var isDecorData = false, isDecorExtra = false;
			for (var type in selfData) {
				var actualType = getActualDecorType(type);
				var isAsset = (actualType.substring(0,7) === "assets/");
				var decorsData = selfData[type];
				if (decorsData.length) {
					payload.decorparams[type] = [];
					if (isAsset) {
						if (!payload.assets)
							payload.assets = {};
						switch (actualType) {
						case "assets/pivothand":
							payload.assets["pointers"] = [];
							break;
						case "assets/bumper":
							payload.assets["bumpers"] = [];
							break;
						case "assets/oil1":
						case "assets/oil2":
							if (!payload.assets["oils"])
								payload.assets["oils"] = [];
							break;
						}
					}
					else
						payload.decor[type] = [];
					for (var i=0;i<decorsData.length;i++) {
						if (isAsset) {
							switch (actualType) {
							case "assets/pivothand":
								var dir = decorsData[i].dir ? Math.atan2(decorsData[i].dir.y,decorsData[i].dir.x) : null;
								var length = decorsData[i].dir ? Math.hypot(decorsData[i].dir.x,decorsData[i].dir.y):100;
								var dtheta = (decorsData[i].dtheta!=null) ? Math.pow(Math.abs(decorsData[i].dtheta),self._rotScale)*Math.sign(decorsData[i].dtheta)/self._rotFactor : 0.015;
								var assetParams = ["hand",[decorsData[i].pos.x,decorsData[i].pos.y,length,8,0.5,0.5],[0,0.5,dir,dtheta]];
								payload.assets["pointers"].push(assetParams);
								break;
							case "assets/oil1":
							case "assets/oil2":
								var typeSrc = actualType.substring(7);
								var assetParams = [typeSrc,[decorsData[i].pos.x,decorsData[i].pos.y,7,7,0.5,0.5],[0,0.5,0.5]];
								payload.assets["oils"].push(assetParams);
								break;
							case "assets/bumper":
								var typeSrc = actualType.substring(7);
								var assetParams = [typeSrc,[decorsData[i].pos.x,decorsData[i].pos.y,Math.round(decorsData[i].r*2),Math.round(decorsData[i].r*2)],[0.5,0.5,0]];
								payload.assets["bumpers"].push(assetParams);
								break;
							}
						}
						else {
							payload.decor[type].push(pointToData(decorsData[i].pos));
							switch (actualType) {
							case "cannonball":
							case "snowball":
							case "billball":
							case "movingthwomp":
							case "firering":
							case "fire3star":
							case "pendulum":
								var dir = decorsData[i].dir ? Math.atan2(decorsData[i].dir.x,decorsData[i].dir.y) : null;
								var decorParams = {dir:isNaN(dir)?0:dir};
								if (actualType === "billball")
									decorParams.length = decorsData[i].dir ? Math.hypot(decorsData[i].dir.x,decorsData[i].dir.y):460;
								else if (actualType === "movingthwomp")
									decorParams.length = decorsData[i].dir ? Math.hypot(decorsData[i].dir.x,decorsData[i].dir.y):0;
								payload.decorparams[type].push(decorParams);
								break;
							case "truck":
								payload.decorparams[type].push({traject:decorsData[i].traject||0});
							}
						}
					}
					if (payload.decorparams[type].length) {
						isDecorData = true;
						if (actualType === "billball") {
							isDecorExtra = true;
							payload.decorparams.extra[type] = {
								nb: Math.round(payload.decorparams[type].length*5/4)
							};
						}
					}
					else
						delete payload.decorparams[type];
					if (customDecors[type]) {
						isDecorExtra = true;
						if (!payload.decorparams.extra[type]) payload.decorparams.extra[type] = {};
						payload.decorparams.extra[type].custom = {
							id: customDecors[type].id,
							type: customDecors[type].type
						};
					}
				}
			}
			if (self.data.extra.truck) {
				var busData = self.data.extra.truck.route;
				if ((busData.length != 1) || busData[0].points.length) {
					isDecorExtra = true;
					payload.decorparams.extra.truck = {
						path: [],
						closed: []
					};
					var busPayload = payload.decorparams.extra.truck;
					for (var i=0;i<busData.length;i++) {
						busPayload.path.push(polyToData(busData[i].points));
						busPayload.closed.push(busData[i].closed ? 1:0);
					}
				}
			}
			if (!isDecorExtra) {
				delete payload.decorparams.extra;
				if (!isDecorData)
					delete payload.decorparams;
			}
		},
		"restore" : function(self,payload) {
			var selfData = self.data.decors;
			for (var type in payload.decor) {
				selfData[type] = [];
				var decorsPayload = payload.decor[type];
				var decorsParams = payload.decorparams ? payload.decorparams[type]:null;
				decorsParams = decorsParams||[];
				var decorsExtra = payload.decorparams && payload.decorparams.extra;
				decorsExtra = decorsExtra||{};
				for (var i=0;i<decorsPayload.length;i++) {
					var decorParams = decorsParams[i] || {};
					var decorExtra = decorsExtra[type] || {};
					var customDecor = decorExtra.custom;
					var actualType = customDecor ? customDecor.type:type;
					var decorData = {pos:dataToPoint(decorsPayload[i])};
					if (customDecor) {
						var $btnDecor = selectCustomDecor(customDecor);
						if ($btnDecor) fetchCustomDecorData($btnDecor,customDecor);
					}
					switch (actualType) {
					case "cannonball":
					case "snowball":
					case "billball":
					case "movingthwomp":
					case "firering":
					case "fire3star":
					case "pendulum":
						var dir = decorParams.dir || 0;
						var length = decorParams.length || 1;
						decorData.dir = {x:length*Math.sin(dir),y:length*Math.cos(dir)};
						break;
					case "truck":
						decorData.traject = decorParams.traject || 0;
					}
					selfData[type].push(decorData);
				}
			}
			if (payload.decorparams && payload.decorparams.extra) {
				var payloadExtra = payload.decorparams.extra;
				var selfExtra = self.data.extra;
				if (payloadExtra.truck) {
					selfExtra.truck = {route:[]};
					for (var i=0;i<payloadExtra.truck.path.length;i++)
						selfExtra.truck.route.push({points:dataToPoly(payloadExtra.truck.path[i]),closed:payloadExtra.truck.closed[i]});
					initRouteSelector(document.getElementById("decor-bus-traject"),payloadExtra.truck.path.length);
				}
			}
			if (payload.assets) {
				for (var type in payload.assets) {
					switch (type) {
					case "pointers":
						selfData["assets/pivothand"] = [];
					}
					for (var i=0;i<payload.assets[type].length;i++) {
						var assetPayload = payload.assets[type][i];
						switch (type) {
						case "pointers":
							var assetData = {pos:dataToPoint(assetPayload[1])};
							var dir = assetPayload[2][2] || 0;
							var length = assetPayload[1][2] || 1;
							var dtheta = Math.pow(Math.abs(assetPayload[2][3]*self._rotFactor),1/self._rotScale)*Math.sign(assetPayload[2][3]*self._rotFactor);
							assetData.dir = {x:length*Math.cos(dir),y:length*Math.sin(dir)};
							assetData.dtheta = dtheta;
							selfData["assets/pivothand"].push(assetData);
							break;
						case "oils":
							var assetKey = "assets/"+assetPayload[0];
							var assetData = {pos:dataToPoint(assetPayload[1])};
							if (!selfData[assetKey]) selfData[assetKey] = [];
							selfData[assetKey].push(assetData);
							break;
						case "bumpers":
							var assetKey = "assets/"+assetPayload[0];
							var assetData = {pos:dataToPoint(assetPayload[1]),r:assetPayload[1][2]/2};
							if (!selfData[assetKey]) selfData[assetKey] = [];
							selfData[assetKey].push(assetData);
						}
					}
				}
			}
		},
		"rescale" : function(self, scale) {
			var selfData = self.data.decors;
			for (var type in selfData) {
				var decorsData = selfData[type];
				for (var i=0;i<decorsData.length;i++) {
					rescaleBox(decorsData[i].pos, scale);
					rescaleNullableDir(decorsData[i].dir, scale);
					if (decorsData[i].r)
						decorsData[i].r *= Math.sqrt(scale.x*scale.y);
				}
			}
			if (self.data.extra.truck) {
				var busData = self.data.extra.truck.route;
				for (var i=0;i<busData.length;i++)
					rescalePoly(busData[i].points, scale);
			}
		},
		"rotate" : function(self, orientation) {
			var selfData = self.data.decors;
			for (var type in selfData) {
				var decorsData = selfData[type];
				for (var i=0;i<decorsData.length;i++) {
					rotateBox(decorsData[i].pos, imgSize,orientation);
					rotateNullableDir(decorsData[i].dir, orientation);
				}
			}
			if (self.data.extra.truck) {
				var busData = self.data.extra.truck.route;
				for (var i=0;i<busData.length;i++)
					rotatePoly(busData[i].points, imgSize,orientation);
			}
		},
		"flip" : function(self, axis) {
			var selfData = self.data.decors;
			for (var type in selfData) {
				var decorsData = selfData[type];
				for (var i=0;i<decorsData.length;i++) {
					flipBox(decorsData[i].pos, imgSize,axis);
					flipNullableDir(decorsData[i].dir, axis);
					if (decorsData[i].dtheta)
						decorsData[i].dtheta = -decorsData[i].dtheta;
				}
			}
			if (self.data.extra.truck) {
				var busData = self.data.extra.truck.route;
				for (var i=0;i<busData.length;i++)
					flipPoly(busData[i].points, imgSize,axis);
			}
		},
		"exit": function(self) {
			var $helpBalloons = document.querySelectorAll(".help-balloon");
			for (var i=0;i<$helpBalloons.length;i++)
				$helpBalloons[i].close();
		}
	},
	"options": {
		"init" : function(self) {
			self.data = {
				bg_img: 0,
				music: isBattle ? 9:1,
				out_color: {r:255,g:255,b:255}
			};
		},
		"resume" : function(self) {
			applyBgSelector();
			applyMusicSelector();
			document.body.classList.add("setting-preview");
			applyColorSelector();
		},
		"click" : function() {
		},
		"move": function() {
		},
		"exit": function(self) {
			document.body.classList.remove("setting-preview");
			document.body.style.backgroundColor = "";
		},
		"save" : function(self,payload) {
			payload.main.bgimg = self.data.bg_img;
			payload.main.music = self.data.music;
			if (self.data.youtube) {
				payload.main.youtube = self.data.youtube;
				if (self.data.youtube_opts)
					payload.main.youtube_opts = self.data.youtube_opts;
			}
			payload.main.bgcolor = [self.data.out_color.r,self.data.out_color.g,self.data.out_color.b];
		},
		"restore" : function(self,payload) {
			self.data.bg_img = payload.main.bgimg;
			self.data.music = payload.main.music;
			if (payload.main.youtube)
				self.data.youtube = payload.main.youtube;
			if (payload.main.youtube_opts)
				self.data.youtube_opts = payload.main.youtube_opts;
			self.data.out_color = {r:payload.main.bgcolor[0],g:payload.main.bgcolor[1],b:payload.main.bgcolor[2]};
		}
	},
	"cannons": {
		"resume" : function(self) {
			self.state.point = createRectangle({x:-1,y:-1});
			self.state.shape = "rectangle";
			var data = self.data;
			self.data = [];
			for (var i=0;i<data.length;i++) {
				var iData = data[i];
				self.state.shape = iData.type;
				switch (iData.type) {
				case "rectangle":
					self.click(self,iData,{});
					self.click(self,{x:iData.x+iData.w,y:iData.y+iData.h},{});
					if (iData.respawn)
						self.click(self,{x:iData.respawn.x+Math.round(iData.w/2),y:iData.respawn.y+Math.round(iData.h/2)},{});
					break;
				case "polygon":
					for (var j=0;j<iData.points.length;j++)
						self.click(self,iData.points[j],{});
					self.state.nodes[0].circle.onclick();
					if (iData.respawn) {
						var polygonCenter = getPolygonRelativeCenter(iData.points);
						self.click(self,{x:iData.respawn.points[0].x+polygonCenter.x,y:iData.respawn.points[0].y+polygonCenter.y},{});
					}
					break;
				}
			}
			replaceNodeType(self);
			document.getElementById("cannons-shape").setValue(self.state.shape);
		},
		"click" : function(self,point,extra) {
			var respawnShape = self.state.respawnShape;
			if (respawnShape) {
				if (extra.oob)
					return;
				storeHistoryData(self.data);
				var data = self.data[self.data.length-1];
				self.move(self,point,extra);
				data.respawn = self.state.respawnShape.data;
				var shape = self.state.currentshape;
				delete self.state.currentshape;
				function reshapeItem(nData) {
					switch (data.type) {
					case "rectangle":
						data.respawn.x += nData.x-data.x;
						data.respawn.y += nData.y-data.y;
						data.respawn.w = nData.w;
						data.respawn.h = nData.h;
						setRectangleBounds(respawnShape.shape,data.respawn);
						break;
					case "polygon":
						var nPoints = deepCopy(nData.points);
						for (var i=0;i<nData.length;i++) {
							nPoints[i].x += data.respawn.points[0].x-data.points[0].x;
							nPoints[i].y += data.respawn.points[0].y-data.points[0].y;
						}
						data.respawn.points = nPoints;
						setPolygonPoints(respawnShape.shape,nPoints);
						break;
					}
					reshapeArrow(nData);
				}
				function reshapeArrow(nData) {
					switch (data.type) {
					case "rectangle":
						var newCenter = {x:Math.round(nData.w/2),y:Math.round(nData.h/2)};
						respawnShape.arrow.move({x:nData.x+newCenter.x,y:nData.y+newCenter.y},{x:data.respawn.x+newCenter.x,y:data.respawn.y+newCenter.y});
						break;
					case "polygon":
						var newCenter = getPolygonRelativeCenter(nData.points);
						respawnShape.arrow.move({x:nData[0].x+newCenter.x,y:nData[0].y+newCenter.y},{x:data.respawn.points[0].x+newCenter.x,y:data.respawn.points[0].y+newCenter.y});
						break;
					}
				}
				function reshapeArrow2(nData) {
					switch (data.type) {
					case "rectangle":
						var newCenter = {x:Math.round(nData.w/2),y:Math.round(nData.h/2)};
						respawnShape.arrow.move(null,{x:nData.x+newCenter.x,y:nData.y+newCenter.y});
						break;
					case "polygon":
						var newCenter = getPolygonRelativeCenter(nData.points);
						respawnShape.arrow.move(null,{x:nData[0].x+newCenter.x,y:nData[0].y+newCenter.y});
						break;
					}
				}
				function deleteItem() {
					$editor.removeChild(shape);
					$editor.removeChild(respawnShape.shape);
					respawnShape.arrow.remove();
					storeHistoryData(self.data);
					removeFromArray(self.data,data);
				}
				var moveOptions = {on_apply:reshapeArrow,on_start_move:respawnShape.arrow.hide,on_end_move:respawnShape.arrow.show};
				var moveOptions2 = {on_apply:reshapeArrow2,on_start_move:respawnShape.arrow.hide,on_end_move:respawnShape.arrow.show};
				addContextMenuEvent(respawnShape.shape,[{
					text: (language ? "Move":"Déplacer"),
					click: function() {
						switch (data.type) {
						case "rectangle":
							moveRectangle(respawnShape.shape,data.respawn,moveOptions2);
							break;
						case "polygon":
							movePolygon(respawnShape.shape,data.respawn,moveOptions2);
							break;
						}
					}
				}, {
					text:(language ? "Delete":"Supprimer"),
					click:function() {
						deleteItem();
					}
				}]);
				switch (data.type) {
				case "rectangle":
					addContextMenuEvent(shape,[{
						text: (language ? "Resize":"Redimensionner"),
						click: function() {
							resizeRectangle(shape,data,{on_apply:reshapeItem});
						}
					}, {
						text: (language ? "Move":"Déplacer"),
						click: function() {
							moveRectangle(shape,data,moveOptions);
						}
					}, {
						text:(language ? "Delete":"Supprimer"),
						click:function() {
							deleteItem();
						}
					}]);
					break;
				case "polygon":
					addContextMenuEvent(shape, [{
						text: (language ? "Edit":"Modifier"),
						click: function() {
							editPolygon(shape,data,{on_apply:reshapeItem});
						}
					}, {
						text: (language ? "Move":"Déplacer"),
						click: function() {
							movePolygon(shape,data,moveOptions);
						}
					}, {
						text:(language ? "Delete":"Supprimer"),
						click:function() {
							deleteItem();
						}
					}]);
					break;
				}
				delete self.state.respawnShape;
			}
			else {
				var selectedShape = self.state.shape;
				switch (selectedShape) {
				case "rectangle":
					if (self.state.rectangle)
						appendRectangleBuilder(self,point);
					else {
						if (!extra.oob) {
							startRectangleBuilder(self,point, {
								on_apply: function(rectangle,data,lastPoint) {
									self.state.currentshape = rectangle;
									self.data.push(data);
									var rectCenter = {x:Math.round(data.w/2),y:Math.round(data.h/2)};
									self.state.respawnShape = {data:deepCopy(data),shape:createRectangle(point),center:rectCenter,arrow:createArrow({x:data.x+rectCenter.x,y:data.y+rectCenter.y})};
									setRectangleBounds(self.state.respawnShape.shape,data);
									self.state.respawnShape.shape.setAttribute("opacity", 0.5);
									for (var i=self.state.respawnShape.arrow.lines.length-1;i>=0;i--)
										self.state.respawnShape.arrow.lines[i].classList.add("noclick");
									if (lastPoint) self.move(self,lastPoint,extra);
								}
							});
						}
					}
					break;
				case "polygon":
					if (self.state.polygon)
						appendPolygonBuilder(self,point);
					else {
						if (!extra.oob) {
							startPolygonBuilder(self,point, {
								on_apply: function(polygon,points,lastPoint) {
									self.state.currentshape = polygon;
									var data = {type:"polygon",points:points};
									self.data.push(data);
									polygon.setAttribute("stroke-width", 1);
									var polygonCenter = getPolygonRelativeCenter(points);
									self.state.respawnShape = {data:deepCopy(data),shape:createPolygon(points),center:polygonCenter,arrow:createArrow({x:point.x+polygonCenter.x,y:point.y+polygonCenter.y})};
									self.state.respawnShape.shape.setAttribute("opacity", 0.5);
									for (var i=self.state.respawnShape.arrow.lines.length-1;i>=0;i--)
										self.state.respawnShape.arrow.lines[i].classList.add("noclick");
									if (lastPoint) self.move(self,lastPoint,extra);
								}
							});
						}
					}
					break;
				}
			}
		},
		"move" : function(self,point,extra) {
			var respawnShape = self.state.respawnShape;
			if (respawnShape) {
				switch (respawnShape.data.type) {
				case "rectangle":
					respawnShape.data.x = point.x-respawnShape.center.x;
					respawnShape.data.y = point.y-respawnShape.center.y;
					setRectanglePos(respawnShape.shape,respawnShape.data);
					break;
				case "polygon":
					movePolygonRelativeCenter(respawnShape.shape,respawnShape.data.points,point,respawnShape.center);
					break;
				}
				self.state.respawnShape.arrow.move(null,point);
			}
			else {
				var selectedShape = self.state.shape;
				switch (selectedShape) {
				case "rectangle":
					moveRectangleBuilder(self,point);
					break;
				case "polygon":
					movePolygonBuilder(self,point);
					break;
				}
			}
		},
		"round_on_pixel" : function(self) {
			var selectedType = self.state.respawnShape ? self.state.respawnShape.data.type : self.state.shape;
			return (selectedType == "polygon");
		},
		"save" : function(self,payload) {
			if (self.data.length) {
				payload.cannons = [];
				for (var i=0;i<self.data.length;i++) {
					var iData = self.data[i];
					var shape = shapeToData(iData);
					var respawn = [0,0];
					if (iData.respawn) {
						switch (iData.respawn.type) {
						case "rectangle":
							respawn[0] = iData.respawn.x-iData.x;
							respawn[1] = iData.respawn.y-iData.y;
							break;
						case "polygon":
							respawn[0] = iData.respawn.points[0].x-iData.points[0].x;
							respawn[1] = iData.respawn.points[0].y-iData.points[0].y;
							break;
						}
					}
					payload.cannons.push([shape,respawn]);
				}
			}
		},
		"restore" : function(self,payload) {
			if (payload.cannons) {
				for (var i=0;i<payload.cannons.length;i++) {
					var iPayload = payload.cannons[i];
					var iData = dataToShape(iPayload[0]);
					var respawnDir = iPayload[1];
					if (respawnDir[0] || respawnDir[1] || (i < payload.cannons.length-1)) {
						iData.respawn = deepCopy(iData);
						switch (iData.type) {
						case "rectangle":
							iData.respawn.x += respawnDir[0];
							iData.respawn.y += respawnDir[1];
							break;
						case "polygon":
							for (var j=0;j<iData.respawn.points.length;j++) {
								iData.respawn.points[j].x += respawnDir[0];
								iData.respawn.points[j].y += respawnDir[1];
							}
							break;
						}
					}
					self.data.push(iData);
				}
			}
		},
		"rescale" : function(self, scale) {
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				rescaleShape(iData, scale);
				if (iData.respawn) rescaleShape(iData.respawn, scale);
			}
		},
		"rotate" : function(self, orientation) {
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				rotateShape(iData, imgSize,orientation);
				if (iData.respawn) rotateShape(iData.respawn, imgSize,orientation);
			}
		},
		"flip" : function(self, axis) {
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				flipShape(iData, imgSize,axis);
				if (iData.respawn) flipShape(iData.respawn, imgSize,axis);
			}
		}
	},
	"teleports": {
		"_shape_selector_id": "teleports-shape",
		"save" : function(self,payload) {
			if (!self.data.length)
				return;
			payload.teleports = [];
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				var shape = shapeToData(iData);
				var respawn = nullablePointToData(iData.respawn);
				if (respawn)
					respawn.push(iData.orientation);
				payload.teleports.push([shape,respawn]);
			}
		},
		"restore" : function(self,payload) {
			if (payload.teleports) {
				for (var i=0;i<payload.teleports.length;i++) {
					var iPayload = payload.teleports[i];
					var iData = dataToShape(iPayload[0]);
					iData.respawn = dataToNullablePoint(iPayload[1]);
					if (iData.respawn)
						iData.orientation = iPayload[1][2];
					self.data.push(iData);
				}
			}
		}
	},
	"mobiles": {
		"resume" : function(self) {
			self.state.point = createRectangle({x:-1,y:-1});
			self.state.shape = "rectangle";
			var data = self.data;
			self.data = [];
			for (var i=0;i<data.length;i++) {
				var iData = data[i];
				self.state.shape = iData.type;
				switch (iData.type) {
				case "rectangle":
					self.click(self,iData,{});
					self.click(self,{x:iData.x+iData.w,y:iData.y+iData.h},{});
					if (iData.dir)
						self.click(self,{x:Math.round(iData.x+iData.w/2+iData.dir.x),y:Math.round(iData.y+iData.h/2+iData.dir.y)},{});
					break;
				case "polygon":
					for (var j=0;j<iData.points.length;j++)
						self.click(self,iData.points[j],{});
					self.state.nodes[0].circle.onclick();
					if (iData.dir) {
						var polygonCenter = getPolygonCenter(iData.points);
						self.click(self,{x:polygonCenter.x+iData.dir.x,y:polygonCenter.y+iData.dir.y},{});
					}
					break;
				case "circle":
					self.click(self,{x:iData.x-iData.r,y:iData.y},{});
					self.click(self,{x:iData.x+iData.r,y:iData.y},{});
					if (iData.dir) {
						var angle2 = self.state.dirVect.center.theta0 + iData.dir.dtheta;
						self.click(self,{x:iData.x+iData.r*Math.cos(angle2), y:iData.y+iData.r*Math.sin(angle2)},{});
					}
					break;
				}
			}
			replaceNodeType(self);
			document.getElementById("mobiles-shape").setValue(self.state.shape);
		},
		"click" : function(self,point,extra) {
			var dirVect = self.state.dirVect;
			if (dirVect) {
				if (extra.oob)
					return;
				storeHistoryData(self.data);
				var data = self.data[self.data.length-1];
				self.move(self,point,extra);
				if ("circle" !== data.type)
					data.dir = {x:point.x-dirVect.center.x,y:point.y-dirVect.center.y};
				else
					data.dir = {dtheta: dirVect.center.dtheta};
				var shape = self.state.currentshape;
				delete self.state.currentshape;
				function reshapeItem(nData) {
					reshapeArrow(nData);
				}
				function reshapeArrow(nData) {
					switch (data.type) {
					case "rectangle":
						dirVect.center = {x:Math.round(nData.x+nData.w/2),y:Math.round(nData.y+nData.h/2)};
						break;
					case "polygon":
						dirVect.center = getPolygonCenter(nData.points);
						break;
					case "circle":
						dirVect.center.x = nData.x;
						dirVect.center.y = nData.y;
						dirVect.center.r = nData.r*dirVect.center.r0;
						dirVect.arrow.move(dirVect.center);
						return;
					}
					var newCenter = dirVect.center;
					dirVect.arrow.move({x:newCenter.x,y:newCenter.y},{x:newCenter.x+data.dir.x,y:newCenter.y+data.dir.y});
				}
				function deleteItem() {
					$editor.removeChild(shape);
					dirVect.arrow.remove();
					storeHistoryData(self.data);
					removeFromArray(self.data,data);
				}
				dirVect.arrow.lines[0].oncontextmenu = function(e) {
					var rect = e.target.getBoundingClientRect();
					var r = rect.width/2;
					var x = e.clientX - rect.left - r;
					var y = e.clientY - rect.top - r;
					r -= 4;
					if (x*x + y*y < r*r) {
						shape.oncontextmenu(e);
						return false;
					}
					return showContextOnElt(e,this,[{
						text: (language ? "Edit":"Modifier"),
						click: function() {
							if ("circle" !== data.type)
								moveArrow(dirVect.arrow,dirVect.center,data.dir);
							else
								moveCircularArrow(dirVect.arrow,dirVect.center,data.dir);
						}
					}, {
						text:(language ? "Delete":"Supprimer"),
						click:function() {
							deleteItem();
						}
					}]);
				};
				var moveOptions = {on_apply:reshapeArrow,on_start_move:dirVect.arrow.hide,on_end_move:dirVect.arrow.show};
				switch (data.type) {
				case "rectangle":
					addContextMenuEvent(shape,[{
						text: (language ? "Resize":"Redimensionner"),
						click: function() {
							resizeRectangle(shape,data,{on_apply:reshapeItem});
						}
					}, {
						text: (language ? "Move":"Déplacer"),
						click: function() {
							moveRectangle(shape,data,moveOptions);
						}
					}, {
						text: (language ? "Edit ↗":"Modifier ↗"),
						click: function() {
							moveArrow(dirVect.arrow,dirVect.center,data.dir);
						}
					}, {
						text:(language ? "Delete":"Supprimer"),
						click:function() {
							deleteItem();
						}
					}]);
					break;
				case "polygon":
					addContextMenuEvent(shape, [{
						text: (language ? "Edit":"Modifier"),
						click: function() {
							editPolygon(shape,data,{on_apply:reshapeItem});
						}
					}, {
						text: (language ? "Move":"Déplacer"),
						click: function() {
							movePolygon(shape,data,moveOptions);
						}
					}, {
						text: (language ? "Edit ↗":"Modifier ↗"),
						click: function() {
							moveArrow(dirVect.arrow,dirVect.center,data.dir);
						}
					}, {
						text:(language ? "Delete":"Supprimer"),
						click:function() {
							deleteItem();
						}
					}]);
					break;
				case "circle":
					addContextMenuEvent(shape, [{
						text: (language ? "Resize":"Redimensionner"),
						click: function() {
							editCircle(shape,data,{on_apply:reshapeItem});
						}
					}, {
						text: (language ? "Move":"Déplacer"),
						click: function() {
							moveCircle(shape,data,moveOptions);
						}
					}, {
						text: (language ? "Edit ↗":"Modifier ↗"),
						click: function() {
							moveCircularArrow(dirVect.arrow,dirVect.center,data.dir);
						}
					}, {
						text:(language ? "Delete":"Supprimer"),
						click:function() {
							deleteItem();
						}
					}]);
					break;
				}
				delete self.state.dirVect;
			}
			else {
				var selectedShape = self.state.shape;
				switch (selectedShape) {
				case "rectangle":
					if (self.state.rectangle)
						appendRectangleBuilder(self,point);
					else {
						if (!extra.oob) {
							startRectangleBuilder(self,point, {
								on_apply: function(rectangle,data,lastPoint) {
									self.state.currentshape = rectangle;
									self.data.push(data);
									var rectCenter = {x:Math.round(data.x+data.w/2),y:Math.round(data.y+data.h/2)};
									self.state.dirVect = {center:rectCenter,arrow:createArrow(rectCenter,null,true,{thickness:4})};
									if (lastPoint) self.move(self,lastPoint,extra);
								}
							});
						}
					}
					break;
				case "polygon":
					if (self.state.polygon)
						appendPolygonBuilder(self,point);
					else {
						if (!extra.oob) {
							startPolygonBuilder(self,point, {
								on_apply: function(polygon,points,lastPoint) {
									self.state.currentshape = polygon;
									var data = {type:"polygon",points:points};
									self.data.push(data);
									polygon.setAttribute("stroke-width", 1);
									var polygonCenter = getPolygonCenter(points);
									self.state.dirVect = {center:polygonCenter,arrow:createArrow(polygonCenter,null,true,{thickness:4})};
									if (lastPoint) self.move(self,lastPoint,extra);
								}
							});
						}
					}
					break;
				case "circle":
					if (self.state.circle)
						appendCircleBuilder(self,point);
					else {
						if (!extra.oob) {
							startCircleBuilder(self,point, {
								on_apply: function(circle,data,lastPoint) {
									self.state.currentshape = circle;
									self.data.push(data);
									var arrowData = {x:data.x,y:data.y,r0:0.8,r:data.r,theta0:-Math.PI/2};
									arrowData.r *= arrowData.r0;
									self.state.dirVect = {center:arrowData,arrow:createCircularArrow(arrowData,arrowData.theta0,0,true,{thickness:4})};
									if (lastPoint) self.move(self,lastPoint,extra);
								}
							});
						}
					}
					break;
				}
			}
		},
		"move" : function(self,point,extra) {
			var dirVect = self.state.dirVect;
			if (dirVect) {
				if ("circle" !== self.data[self.data.length-1].type)
					self.state.dirVect.arrow.move(null,point);
				else {
					var arrowData = dirVect.center;
					var dAngle = getDeltaAngle(arrowData.theta0,arrowData,point);
					self.state.dirVect.arrow.move(null,null,dAngle);
				}
			}
			else {
				var selectedShape = self.state.shape;
				switch (selectedShape) {
				case "rectangle":
					moveRectangleBuilder(self,point);
					break;
				case "polygon":
					movePolygonBuilder(self,point);
					break;
				case "circle":
					moveCircleBuilder(self,point);
					break;
				}
			}
		},
		"round_on_pixel" : function(self) {
			var selectedType = self.state.dirVect ? self.data[self.data.length-1].type : self.state.shape;
			return (selectedType == "polygon");
		},
		"_dirFactor" : 12,
		"_rotFactor" : -36,
		"_rotScale" : 1.5,
		"save" : function(self,payload) {
			if (self.data.length) {
				for (var i=0;i<self.data.length;i++) {
					var iData = self.data[i];
					var shape = shapeToData(iData);
					if ("circle" !== iData.type) {
						if (!payload.flows) payload.flows = [];
						var dir = [0,0];
						if (iData.dir) {
							dir = pointToData(iData.dir);
							dir[0] /= self._dirFactor;
							dir[1] /= self._dirFactor;
						}
						payload.flows.push([shape,dir]);
					}
					else {
						if (!payload.spinners) payload.spinners = [];
						shape.push(0);
						if (iData.dir)
							shape[3] = Math.pow(Math.abs(iData.dir.dtheta),self._rotScale)*Math.sign(iData.dir.dtheta)/self._rotFactor;
						payload.spinners.push(shape);
					}
				}
			}
		},
		"restore" : function(self,payload) {
			var sortedData = {"complete":[],"incomplete":[]};
			if (payload.flows) {
				for (var i=0;i<payload.flows.length;i++) {
					var iPayload = payload.flows[i];
					var dirVect = iPayload[1];
					var iData = {type:"flows",data:iPayload,status:(dirVect[0]||dirVect[1])?"complete":"incomplete"};
					sortedData[iData.status].push(iData);
				}
			}
			if (payload.spinners) {
				for (var i=0;i<payload.spinners.length;i++) {
					var iPayload = payload.spinners[i];
					var iData = {type:"spinners",data:iPayload,status:iPayload[3]?"complete":"incomplete"}
					sortedData[iData.status].push(iData);
				}
			}
			for (var i=0;i<sortedData["incomplete"].length-1;i++)
				sortedData["incomplete"].status = "complete";
			var allData = sortedData["complete"].concat(sortedData["incomplete"]);
			for (var i=0;i<allData.length;i++) {
				var aData = allData[i];
				var iPayload = aData.data;
				var iData;
				if ("flows" === aData.type) {
					iData = dataToShape(iPayload[0]);
					var dirVect = iPayload[1];
					if ("complete" === aData.status) {
						iData.dir = dataToPoint(dirVect);
						iData.dir.x = Math.round(iData.dir.x*self._dirFactor);
						iData.dir.y = Math.round(iData.dir.y*self._dirFactor);
					}
				}
				else {
					iData = dataToCirc(iPayload);
					iData.type = "circle";
					if ("complete" === aData.status)
						iData.dir = {dtheta:Math.pow(Math.abs(iPayload[3]*self._rotFactor),1/self._rotScale)*Math.sign(iPayload[3]*self._rotFactor)};
				}
				self.data.push(iData);
			}
		},
		"rescale" : function(self, scale) {
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				rescaleShape(iData, scale);
				if (iData.dir) {
					if ("circle" !== iData.type)
						rescaleDir(iData.dir, scale);
				}
			}
		},
		"rotate" : function(self, orientation) {
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				rotateShape(iData, imgSize,orientation);
				if (iData.dir) {
					if ("circle" !== iData.type)
						rotateDir(iData.dir,orientation);
				}
			}
		},
		"flip" : function(self, axis) {
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				flipShape(iData, imgSize,axis);
				if (iData.dir) {
					if ("circle" !== iData.type)
						flipDir(iData.dir,axis);
					else
						iData.dir.dtheta = -iData.dir.dtheta;
				}
			}
		}
	},
	"elevators": {
		"resume" : function(self) {
			self.state.point = createRectangle({x:-1,y:-1});
			self.state.shape = "rectangle";
			var data = self.data;
			self.data = [];
			for (var i=0;i<data.length;i++) {
				var iData = data[i];
				self.state.shape = iData.type;
				var createdShape;
				switch (iData.type) {
				case "rectangle":
					self.click(self,iData,{});
					createdShape = self.state.rectangle;
					self.click(self,{x:iData.x+iData.w,y:iData.y+iData.h},{});
					break;
				case "polygon":
					for (var j=0;j<iData.points.length;j++)
						self.click(self,iData.points[j],{});
					createdShape = self.state.polygon;
					self.state.nodes[0].circle.onclick();
					break;
				}
				if (createdShape) {
					createdShape.reheight("z", iData.z);
					createdShape.reheight("z0", iData.z0);
				}
			}
			replaceNodeType(self);
			document.getElementById("elevators-shape").setValue(self.state.shape);
		},
		"click" : function(self,point,extra) {
			var selectedShape = self.state.shape;
			switch (selectedShape) {
			case "rectangle":
				if (self.state.rectangle)
					appendRectangleBuilder(self,point);
				else {
					if (!extra.oob) {
						startRectangleBuilder(self,point, {
							on_apply: function(rectangle,data) {
								self.data.push(data);
								setupShapeHeight(self,rectangle,data);
								var moveOptions = {on_apply:rectangle.reposition,on_start_move:rectangle.text.hide,on_end_move:rectangle.text.show};
								addContextMenuEvent(rectangle,[{
									text: (language ? "Resize":"Redimensionner"),
									click: function() {
										resizeRectangle(rectangle,data, moveOptions);
									}
								}, {
									text: (language ? "Move":"Déplacer"),
									click: function() {
										moveRectangle(rectangle,data, moveOptions);
									}
								}, {
									text: (language ? "Target height...":"Hauteur cible..."),
									click: function() {
										rectangle.promptHeight("z");
									}
								}, {
									text: (language ? "Min height...":"Hauteur min..."),
									click: function() {
										rectangle.promptHeight("z0");
									}
								}, {
									text:(language ? "Delete":"Supprimer"),
									click:function() {
										$editor.removeChild(rectangle);
										rectangle.text.remove();
										storeHistoryData(self.data);
										removeFromArray(self.data,data);
									}
								}]);
							}
						});
					}
				}
				break;
			case "polygon":
				if (self.state.polygon)
					appendPolygonBuilder(self,point);
				else {
					if (!extra.oob) {
						startPolygonBuilder(self,point, {
							on_apply: function(polygon,points) {
								var data = {type:"polygon",points:points};
								self.data.push(data);
								polygon.setAttribute("stroke-width", 1);
								setupShapeHeight(self,polygon,data);
								function repositionPoly(nData) {
									polygon.reposition(Object.assign({}, data, nData));
								}
								var moveOptions = {on_apply:repositionPoly,on_start_move:polygon.text.hide,on_end_move:polygon.text.show};
								addContextMenuEvent(polygon, [{
									text: (language ? "Edit":"Modifier"),
									click: function() {
										editPolygon(polygon,data, moveOptions);
									}
								}, {
									text: (language ? "Move":"Déplacer"),
									click: function() {
										movePolygon(polygon,data, moveOptions);
									}
								}, {
									text: (language ? "Target height...":"Hauteur cible..."),
									click: function() {
										polygon.promptHeight("z");
									}
								}, {
									text: (language ? "Min height...":"Hauteur min..."),
									click: function() {
										polygon.promptHeight("z0");
									}
								}, {
									text:(language ? "Delete":"Supprimer"),
									click:function() {
										$editor.removeChild(polygon);
										polygon.text.remove();
										storeHistoryData(self.data);
										removeFromArray(self.data,data);
									}
								}]);
							}
						});
					}
				}
				break;
			}
			function setupShapeHeight(self, shape, data) {
				var prevData = self.data[self.data.length-2] || { z0: 0, z: 1 };
				var hText = document.createElementNS(SVG, "text");
				hText.setAttribute("class", "dark noclick");
				hText.setAttribute("font-size", 15);
				hText.setAttribute("text-anchor", "middle");
				hText.setAttribute("dominant-baseline", "middle");
				$editor.appendChild(hText);
				hText.remove = function() {
					$editor.removeChild(hText);
				};
				hText.show = function() {
					hText.style.visibility = "";
				};
				hText.hide = function() {
					hText.style.visibility = "hidden";
				};
				shape.text = hText;
				shape.reheight = function(prop, z) {
					storeHistoryData(self.data);
					data[prop] = z;
					shape.reposition(data);
				};
				shape.reposition = function(data) {
					switch (data.type) {
					case "rectangle":
						hText.setAttribute("x", data.x+data.w/2);
						hText.setAttribute("y", data.y+data.h/2+2);
						break;
					case "polygon":
						var polygonCenter = getPolygonCenter(data.points);
						hText.setAttribute("x", polygonCenter.x);
						hText.setAttribute("y", polygonCenter.y+2);
						break;
					}
					if (data.z0)
						hText.innerHTML = data.z0 + "→" + data.z;
					else
						hText.innerHTML = data.z;
				};
				shape.promptHeight = function(prop) {
					var enteredVal = +prompt(language ? "Enter value...":"Entrer une valeur...", data[prop]);
					if (isNaN(enteredVal)) return;
					if (enteredVal >= 0)
						shape.reheight(prop, enteredVal);
				}

				if (data.z === undefined)
					data.z = prevData.z;
				if (data.z0 === undefined)
					data.z0 = prevData.z0;
				shape.reposition(data);
			}
		},
		"move" : function(self,point,extra) {
			var selectedShape = self.state.shape;
			switch (selectedShape) {
			case "rectangle":
				moveRectangleBuilder(self,point);
				break;
			case "polygon":
				movePolygonBuilder(self,point);
				break;
			}
		},
		"round_on_pixel" : function(self) {
			return self.state.shape == "polygon";
		},
		"save" : function(self,payload) {
			payload.elevators = [];
			for (var i=0;i<self.data.length;i++) {
				var iData = self.data[i];
				payload.elevators.push([shapeToData(iData),[iData.z0,iData.z]]);
			}
		},
		"restore" : function(self,payload) {
			if (!payload.elevators) return;
			for (var i=0;i<payload.elevators.length;i++) {
				var iData = payload.elevators[i];
				var nData = dataToShape(iData[0]);
				nData.z0 = iData[1][0];
				nData.z = iData[1][1];
				self.data.push(nData);
			}
		},
		"rescale" : function(self, scale) {
			for (var i=0;i<self.data.length;i++)
				rescaleShape(self.data[i], scale);
		},
		"rotate" : function(self, orientation) {
			for (var i=0;i<self.data.length;i++)
				rotateShape(self.data[i], imgSize,orientation);
		},
		"flip" : function(self, axis) {
			for (var i=0;i<self.data.length;i++)
				flipShape(self.data[i], imgSize,axis);
		}
	}
};
for (var key in commonTools["holes"]) {
	if (!commonTools["teleports"][key]) {
		var v = commonTools["holes"][key];
		if (typeof v === "function")
			v = v.bind(commonTools["teleports"])
		commonTools["teleports"][key] = v;
	}
}