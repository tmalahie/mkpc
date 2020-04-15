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
	document.forms[0].elements["p"+id].value = c;
	fermer();
}
function deplacer(event, E, nouveau) {
	if (e) return;
	var centerX = E.scrollWidth/2, centerY = E.scrollHeight/2;
	E.style.position = "absolute";
	E.style.left = Math.round(event.pageX-centerX) +"px";
	E.style.top = Math.round(event.pageY-centerY) +"px";
	E.style.zIndex = 20;
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
		var getAlt = this.alt;
		e.style.cursor = "pointer";
		e = undefined;
		document.onmousemove = undefined;
		if (posX > 0 && posY > 0 && posX < 590 && posY < 590) {
			this.onclick = function(evt) {deplacer(evt, this, false)};
			this.style.left = posX +"px";
			this.style.top = posY +"px";
			this.style.zIndex = 19;
			pieceplus(getAlt+getId, [posX, posY], [centerX, centerY]);
			
			if (nouveau) {
				var nToAdd = document.getElementById(getAlt+(getId+1));
				deplacer(evt, nToAdd, true);
				ajouter(nToAdd.alt, getId+2);
			}
		}
		else {
			document.getElementById(getAlt).removeChild(this);
			if (document.forms[0].elements[getAlt+getId]) {
				document.getElementById("pieces").removeChild(document.forms[0].elements[getAlt+getId]);
				for (i=getId+1;document.forms[0].elements[getAlt+i];i++) {
					document.getElementById(getAlt+i).id = getAlt+ (i-1);
					document.forms[0].elements[getAlt+i].name = getAlt+ (i-1);
				}
				document.getElementById(getAlt+i).id = getAlt+(i-1);
			}
			else
				document.getElementById(getAlt+(getId+1)).id = getAlt+getId;
		}
	}
}
function ajouter(src, Id) {
	var tSrc = src;
	if (src.match(/^[a-d]$/g)) {
		var cMap = currentMap();
		if (cMap > 30)
			tSrc = {"a":"u","b":"v","c":"w","d":"x"}[src];
		else if (cMap > 13)
			tSrc = {"a":"p","b":"q","c":"r","d":"s"}[src];
	}
	var nImg = document.createElement("img");
	nImg.src = "images/pieces/piececircuit_"+ tSrc + (src != "t" ? "" : currentMap()) +".png";
	nImg.alt = src;
	nImg.id = src+Id;
	nImg.onclick = function(event){deplacer(event, this, true);ajouter(this.alt, parseInt(this.id.match(/\d+$/g))+1)}
	document.getElementById(src).appendChild(nImg);
}
var letterRegex = /^(\w+)\d+$/;
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
		for (i=0;i<8;i++) {
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
			var j;
			for (j=0;document.getElementById(E[i]+(j+1));j++)
				document.getElementById(E[i]).removeChild(document.getElementById(E[i]+j));
			document.getElementById(E[i]+j).id = E[i]+0;
			for (j=0;document.forms[0].elements[E[i]+j];j++)
				document.getElementById("pieces").removeChild(document.forms[0].elements[E[i]+j]);
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
	for(i=0;i<E.length;i++) {
		if (!document.getElementById(E[i]).innerHTML)
			ajouter(E[i],0);
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
	if (isBox(E.id) && E.style.left && E.style.top) {
		E.onload = function() {
			centerX -= Math.round(E.scrollWidth/2), centerY -= Math.round(E.scrollHeight/2);
			E.style.left = (parseInt(E.style.left)+centerX) +"px";
			E.style.top = (parseInt(E.style.top)+centerY) +"px";
			E.onload = undefined;
		}
	}
}
function changeMap(p) {
	var cPieces = document.getElementsByClassName("cPiece");
	var snes = (p <= 13);
	var gba = (p <= 30);
	for (var i=0;i<36;i++)
		document.getElementsByTagName("img")[i].src = "images/pieces/piececircuit"+ p +"_"+ document.forms[0].elements["p"+i].value +".png";
	for (var i=0;i<cPieces.length;i++)
		cPieces[i].src = "images/pieces/piececircuit"+ p +"_"+ i +".png";
	var accSrc = "abcd";
	var accEffSrc = snes ? accSrc: (gba ? "pqrs":"uvwx");
	for (var i=0;i<accSrc.length;i++) {
		for (var j=0;document.getElementById(accSrc[i]+j);j++)
			document.getElementById(accSrc[i]+j).src = "images/pieces/piececircuit_"+accEffSrc[i]+".png";
	}
	var tPiece;
	for (i=0;tPiece=document.getElementById("t"+i);i++) {
		recenterPos(tPiece);
		tPiece.src = "images/pieces/piececircuit_t"+p+".png";
	}
	if (document.getElementById("croisement"))
		document.getElementById("croisement").style.backgroundImage = "url('mapcreate.php?p1=5&p2=4&p6=5&p7=10&p8=7&map="+p+"')";
}
function currentMap() {
	return document.forms[0].map.value;
}