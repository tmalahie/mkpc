var id;
var e;
var c;
var s;
var xy = new Array();
function change() {
	if (document.getElementById("circuit").onclick) return;
	document.getElementsByTagName("img")[s].style.width = "";
	document.getElementsByTagName("img")[s].style.border = "";
	if (id != undefined) {
		document.getElementsByTagName("img")[id].style.width = "";
		document.getElementsByTagName("img")[id].style.border = "";
		document.getElementsByClassName("cPiece")[c].style.border = "";
		document.getElementsByTagName("img")[id].src = "images/pieces/piececircuit"+ currentMap() +"_"+ document.forms[0].elements["p"+id].value +".png";
	}

	document.getElementById("choose").style.display = "block";
	document.getElementsByTagName("img")[s].style.width = "92px";
	document.getElementsByTagName("img")[s].style.border = "solid 4px #8bf";
	
	c = parseInt(document.forms[0].elements["p"+s].value);
	document.getElementsByClassName("cPiece")[c].style.border = "solid 2px #9CF";
	id = s;
}
function survol(ID) {
	if (s != undefined && id != s) {
		document.getElementsByTagName("img")[s].style.width = "";
		document.getElementsByTagName("img")[s].style.border = "";
	}
	if (ID != id) {
		document.getElementsByTagName("img")[ID].style.width = "92px";
		document.getElementsByTagName("img")[ID].style.border = "solid 4px #18B";
	}
	s = ID;
}
function pos(event) {
	xy[0] = Math.round(event.clientX);
	xy[1] = Math.round(event.clientY);
	document.body.onmousemove = function(evt) {
		var x = parseInt(document.getElementById("choose").style.left) + Math.round(evt.clientX)-xy[0];
		var y = parseInt(document.getElementById("choose").style.top) + Math.round(evt.clientY)-xy[1]
		if (x >= 0) {
			document.getElementById("choose").style.left = x +"px";
			xy[0] = Math.round(evt.clientX);
		}
		else
			document.getElementById("choose").style.left = "0px";
		if (y >= 0) {
			document.getElementById("choose").style.top = y +"px";
			xy[1] = Math.round(evt.clientY);
		}
		else
			document.getElementById("choose").style.top = "0px";
	}
}
function fermer() {
	document.getElementsByTagName("img")[id].src = "images/pieces/piececircuit"+ currentMap() +"_"+ document.forms[0].elements["p"+id].value +".png";
	document.getElementById("choose").style.display="none";
	document.getElementsByTagName("img")[id].style.width = (id == s) ? "92px" : "";
	document.getElementsByTagName("img")[id].style.border = (id == s) ? "solid 4px #18B" : "";
	document.getElementsByClassName("cPiece")[c].style.border = "";

	c = undefined;
	id = undefined;
}
function apercu(changeur) {
	document.getElementsByTagName("img")[id].src = "images/pieces/piececircuit"+ currentMap() +"_"+ changeur +".png";
	document.getElementsByClassName("cPiece")[c].style.border = "";
	c = changeur;
	document.getElementsByClassName("cPiece")[c].style.border = "solid 2px #9CF";
}
function disappear() {
	if (id==undefined) return;
	document.getElementsByTagName("img")[id].src = "images/pieces/piececircuit"+ currentMap() +"_"+ document.forms[0].elements["p"+id].value +".png";
}
function appliquer() {
	showWarningIfNeeded(c);
	document.forms[0].elements["p"+id].value = c;
	fermer();
}
function showWarningIfNeeded(c) {
	if (c == 10) {
		var $crossWarning = document.getElementById("crossing-warning");
		if ($crossWarning) $crossWarning.style.display = "block";
		return true;
	}
	return false;
}
function deplacer(event, E, nouveau) {
	if (e) return;
	var centerX = E.scrollWidth/2, centerY = E.scrollHeight/2;
	E.style.position = "absolute";
	E.style.left = Math.round(event.pageX-centerX) +"px";
	E.style.top = Math.round(event.pageY-centerY) +"px";
	E.style.zIndex = 21;
	E.style.cursor = "none";
	e = E;
	document.onmousemove = function(evt) {
		e.style.left = Math.round(evt.pageX-centerX) +"px";
		e.style.top = Math.round(evt.pageY-centerY) +"px";
	}
	E.onclick = function(evt) {
		var posX = Math.round(evt.pageX-centerX);
		var posY = Math.round(evt.pageY-centerY);
		var getId = parseInt(this.id.match(/\d+$/g));
		var prefix = getSrcPrefix(this.dataset);
		e.style.cursor = "pointer";
		e = undefined;
		document.onmousemove = undefined;
		if (posX > 0 && posY > 0 && posX < 590 && posY < 590) {
			this.onclick = function(evt) {deplacer(evt, this, false)};
			this.style.left = posX +"px";
			this.style.top = posY +"px";
			this.style.zIndex = (prefix==="o") ? 20:19;
			pieceplus(prefix+getId, [posX, posY], [centerX, centerY]);
			
			if (nouveau) {
				var nToAdd = document.getElementById(prefix+(getId+1));
				deplacer(evt, nToAdd, true);
				ajouter(nToAdd.dataset, getId+2);
			}
		}
		else {
			document.getElementById(prefix).removeChild(this);
			if (document.forms[0].elements[prefix+getId]) {
				document.getElementById("pieces").removeChild(document.forms[0].elements[prefix+getId]);
				for (var i=getId+1;document.forms[0].elements[prefix+i];i++) {
					document.getElementById(prefix+i).id = prefix+ (i-1);
					document.forms[0].elements[prefix+i].name = prefix+ (i-1);
				}
				document.getElementById(prefix+i).id = prefix+(i-1);
			}
			else
				document.getElementById(prefix+(getId+1)).id = prefix+getId;
		}
	}
}
function getSrcPrefix(params) {
	if (params.n > 0)
		return params.t+params.n+"_";
	return params.t;
}
function ajouter(params, Id) {
	var src = params.t, tSrc = src;
	if (src.match(/^[a-d]$/g)) {
		var cMap = currentMap();
		var console = getMapConsole(cMap);
		switch (console) {
		case "gba":
			tSrc = {"a":"p","b":"q","c":"r","d":"s"}[src];
			break;
		case "ds":
			tSrc = {"a":"u","b":"v","c":"w","d":"x"}[src];
		}
	}
	var nImg = document.createElement("img");
	if ("t" !== src)
		nImg.src = "images/pieces/piececircuit_"+ tSrc +".png";
	else {
		var decorType = decorTypes[currentMap()][params.n||0];
		if (!decorType) {
			decorType = decorTypes[currentMap()][0];
			nImg.style.display = "none";
		}
		nImg.src = "images/map_icons/"+decorType+".png";
		nImg.className = "decor";
		if (decorType.startsWith("assets/"))
			nImg.className += " decor-asset";
	}
	var prefix = getSrcPrefix(params);
	nImg.id = prefix+Id;
	nImg.alt = src;
	for (var key in params)
		nImg.dataset[key] = params[key];
	nImg.onclick = function(event){deplacer(event, this, true);ajouter(this.dataset, parseInt(this.id.match(/\d+$/g))+1)}
	document.getElementById(prefix).appendChild(nImg);
}
var letterRegex = /^(\w+?)(\d+_)?\d+$/;
function isBox(piece) {
	var letterMatch = letterRegex.exec(piece);
	if (letterMatch) {
		var letter = letterMatch[1];
		if (["o","t"].indexOf(letter) !== -1)
			return true;
	}
	return false;
}
function pieceplus(piece, coordonnees, center) {
	if (isBox(piece)) {
		coordonnees[0] += Math.round(center[0]);
		coordonnees[1] += Math.round(center[1]);
	}
	var nPiece = document.forms[0].elements[piece];
	if (!nPiece) {
		nPiece = document.createElement("input");
		nPiece.type = "hidden";
		nPiece.name = piece;
		document.getElementById("pieces").appendChild(nPiece);
	}
	nPiece.value = coordonnees;
}


function inClick(elmt, nb) {
	if (document.getElementById("circuit").onclick) return;
	elmt.style.opacity = 1;
	elmt.style.zIndex = 11;
	document.getElementById("circuit").onmousemove = function(e) {
		elmt.style.left = (Math.floor((e.pageX)/100)*100+25)+"px";
		elmt.style.top = (Math.floor((e.pageY)/100)*100+25)+"px";
	}
	elmt.onclick = function() {
		var newPos = (parseInt(elmt.style.left)-25)/100+Math.floor((parseInt(elmt.style.top)-25)/100)*6;
		for (var i=0;i<8;i++) {
			if (i != nb) {
				var iPos = document.forms[0].elements["s"+i];
				if (iPos.value == newPos) {
					var nPos = document.forms[0].elements["s"+nb].value;
					document.getElementsByClassName("startposition")[i].style.left = ((nPos%6)*100+25) +"px";
					document.getElementsByClassName("startposition")[i].style.top = (Math.floor(nPos/6)*100+25) +"px";
					iPos.value = nPos;
					i = 8;
				}
			}
		}
		document.forms[0].elements["s"+nb].value = newPos;
		document.getElementById("circuit").onmousemove = null;
		document.getElementById("circuit").onclick = null;
		elmt.onclick = function(){inClick(this, nb)};
		elmt.style.opacity = "";
		elmt.style.zIndex = "";
	}
	document.getElementById("circuit").onclick = elmt.onclick;
}
function deleteAll(msg) {
	if (confirm(msg)) {
		for (var i=0;i<36;i++) {
			id = i;
			c = 11;
			appliquer();
		}
		var E=['a','b','c','d','e','f','g','h','i','j','o','t'];
		for (var i=0;i<E.length;i++) {
			var prefix = E[i];
			for (var k=0;document.getElementById(prefix);k++) {
				var j;
				for (j=0;document.getElementById(prefix+(j+1));j++)
					document.getElementById(prefix).removeChild(document.getElementById(prefix+j));
				document.getElementById(prefix+j).id = prefix+0;
				for (j=0;document.forms[0].elements[prefix+j];j++)
					document.getElementById("pieces").removeChild(document.forms[0].elements[prefix+j]);
				prefix = E[i]+(k+1)+"_";
			}
		}
	}
}
document.onkeydown = function(t) {
	if (c == undefined) {
		var i = s;
		if (i != undefined) {
			switch(t.keyCode) {
				case 37 :
				i--;
				break;
				case 38 :
				i -= 6
				break;
				case 39 :
				i++;
				break;
				case 40 :
				i += 6;
				break;
				case 13 :
				change(s);
				return;
				case 46 :
				id = s;
				c = 11;
				appliquer();
				return;
				default :
				return;
			}
		if (i > 35)
			i -= 36;
		else if (i < 0)
			i += 36;
		}
		else {
			var touche = t.keyCode;
			if (touche == 37 || touche == 38)
				i = 35;
			else if (touche == 39 || touche == 40)
				i = 0;
			else
				return;
		}
	survol(i);
	}
	else {
		var i = c;
		switch (t.keyCode) {
			case 37 :

			i--;
			break;
			case 38 :
			i -= 4
			break;
			case 39 :
			i++;
			break;
			case 40 :
			i += 4;
			break;
			case 13 :
			appliquer(c);
			return;
			case 27 :
			fermer();
			return;
			break;
			default :
			return;
		}
	var nbPieces = document.getElementsByClassName("cPiece").length;
	if (i > (nbPieces-1))
		i -= nbPieces;
	else if (i < 0)
		i += nbPieces;
	apercu(i);
	}
}
window.onload = function() {
	var E=['a','b','c','d','e','f','g','h','i','j','o','t'];
	for (var i=0;i<E.length;i++) {
		var prefix = E[i];
		for (var k=0;document.getElementById(prefix);k++) {
			if (!document.getElementById(prefix).innerHTML) {
				var params = {t:E[i]};
				if (k) params["n"] = k;
				ajouter(params,0);
			}
			prefix = E[i]+(+k+1)+"_";
		}
	}
	for (var i=0;i<36;i++) {
		if (showWarningIfNeeded(document.forms[0].elements["p"+i].value))
			break;
	}
}
function centerPos(E) {
	if (isBox(E.id)) {
		var centerX = Math.round(E.scrollWidth/2), centerY = Math.round(E.scrollHeight/2);
		E.style.left = (parseInt(E.style.left)-centerX) +"px";
		E.style.top = (parseInt(E.style.top)-centerY) +"px";
	}
	E.onload = undefined;
}
function recenterPos(E) {
	var centerX = Math.round(E.scrollWidth/2), centerY = Math.round(E.scrollHeight/2);
	if (isBox(E.id) && E.style.left && E.style.top && E.style.display !== "none") {
		E.onload = function() {
			centerX -= Math.round(E.scrollWidth/2), centerY -= Math.round(E.scrollHeight/2);
			E.style.left = (parseInt(E.style.left)+centerX) +"px";
			E.style.top = (parseInt(E.style.top)+centerY) +"px";
			E.onload = undefined;
		}
	}
}
function getMapConsole(p) {
	if ((p <= 13) || (p >= 56))
		return "snes";
	if ((p <= 30) || (p >= 52))
		return "gba";
	return "ds";
}
function changeMap(p) {
	var cPieces = document.getElementsByClassName("cPiece");
	var console = getMapConsole(p);
	for (var i=0;i<36;i++)
		document.getElementsByTagName("img")[i].src = "images/pieces/piececircuit"+ p +"_"+ document.forms[0].elements["p"+i].value +".png";
	for (var i=0;i<cPieces.length;i++)
		cPieces[i].src = "images/pieces/piececircuit"+ p +"_"+ i +".png";
	var accSrc = "abcd";
	var accEffSrc;
	switch (console) {
	case "gba":
		accEffSrc = "pqrs";
		break;
	case "ds":
		accEffSrc = "uvwx";
		break;
	default:
		accEffSrc = accSrc;
	}
	for (var i=0;i<accSrc.length;i++) {
		for (var j=0;document.getElementById(accSrc[i]+j);j++)
			document.getElementById(accSrc[i]+j).src = "images/pieces/piececircuit_"+accEffSrc[i]+".png";
	}
	var tPiece;
	var prefix = "t";
	for (var k=0;document.getElementById(prefix);k++) {
		var decorType = decorTypes[p][k];
		for (var i=0;tPiece=document.getElementById(prefix+i);i++) {
			if (decorType) {
				recenterPos(tPiece);
				tPiece.src = "images/map_icons/"+decorType+".png";
				tPiece.className = "decor";
				if (decorType.startsWith("assets/"))
					tPiece.className += " decor-asset";
				tPiece.style.display = "";
			}
			else
				tPiece.style.display = "none";
		}
		prefix = "t"+(k+1)+"_";
	}
	if (document.getElementById("croisement"))
		document.getElementById("croisement").style.backgroundImage = "url('mapcreate.php?p0=11&p1=5&p2=4&p6=5&p7=10&p8=7&map="+p+"')";
}
function currentMap() {
	return document.forms[0].map.value;
}