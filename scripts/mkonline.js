var pause, chatting = false;
var aPlayers = new Array();
var fInfos;
var formulaire;
var baseCp;
var nBasePersos, customPersos;
function tourner(kart) {
	if (kart == undefined)
		kart = tourne;
	if (kart == tourne) {
		var rotation = parseFloat(document.getElementsByTagName("img")[kart].style.left);
		var size = parseFloat(document.getElementsByTagName("img")[kart].style.height);
		if (rotation > -21*size)
			document.getElementsByTagName("img")[kart].style.left = rotation - size +"px";
		else
			document.getElementsByTagName("img")[kart].style.left = "0px";
		setTimeout("tourner("+ kart +")", 100);
	}
}

function defileMaps(fMap) {
	if (document.getElementById("maps") && document.getElementById("maps").alt == fMap) {
		if (fMap % 4 != 0)
			fMap++;
		else
			fMap -= 3;
		document.getElementById("oMapName").innerHTML = lCircuits[fMap-1];
		document.getElementById("maps").alt = fMap;
		document.getElementById("maps").src = "images/selectors/select_map"+ fMap +".png";
		setTimeout("defileMaps("+ fMap +")", 1000);
	}
}

var selectPerso;

function MarioKart() {

var oMaps = listMaps();

var aAvailableMaps = new Array();

for (circuits in oMaps) {
	aAvailableMaps.push(circuits);
	var oMap = oMaps[circuits];
	if (!oMap.w)
		oMap.w = 512;
	if (!oMap.h)
		oMap.h = 512;
	if (!oMap.tours)
		oMap.tours = 3;
	if (!oMap.horspistes) {
		oMap.horspistes = {};
		if (oMap.horspiste) {
			oMap.horspistes["herbe"] = oMap.horspiste;
			delete oMap.horspiste;
		}
	}
}

var iWidth = 80;
var iHeight = 39;
var iQuality = optionOf("quality");
var bMusic = !!optionOf("music");
var iSfx = !!optionOf("sfx");
var gameMenu;

var refreshDatas = true, finishing = false;
var destructions = new Array();
var nbNews = new Array();
var connecte = 1;
for (i=0;i<6;i++)
	destructions.push(new Array());
var aIDs = new Array(), aPlaces = new Array();
var tnCourse = 0;
var identifiant = fID;
var keyDowned = true, keyUped = true;


function setQuality(iValue) {
	if (bCounting) return;

	iQuality = iValue;
	
	if (bRunning)
		resetScreen();
		
	xhr("changeParam.php", "param=0&value="+ iValue, function(reponse) {
		return (reponse == 1);
	});
}

function setScreenScale(iValue) {
	if (bCounting) return;

	var aScreenScale = iScreenScale;
	iScreenScale = iValue;
	if (bRunning)
		resetScreen();
		
	xhr("changeParam.php", "param=1&value="+ iValue, function(reponse) {
		return (reponse == 1);
	});

	var oScr = oContainer.firstChild;
	if (oScr) {
		if (!oScr.aScreenScale)
			oScr.aScreenScale = aScreenScale;
		oScr.style.width = (iWidth*iScreenScale)+"px";
		oScr.style.height = (iHeight*iScreenScale)+"px";
		oScr.style.transformOrigin = oScr.style.WebkitTransformOrigin = oScr.style.MozTransformOrigin = "top left";
		oScr.style.transform = oScr.style.WebkitTransform = oScr.style.MozTransform = "scale("+ (iScreenScale/oScr.aScreenScale) +")";
		var FBRoot = document.getElementById("fb-root");
		if (FBRoot)
			FBRoot.style.display = "none";
	}

	reposKeyboard();
}

function reposKeyboard() {
	var virtualKeyboardW = virtualButtonW*4.8;
	var virtualKeyboardH = virtualButtonH*2.6;
	document.getElementById("virtualkeyboard").style.width = Math.round(virtualKeyboardW) +"px";
	document.getElementById("virtualkeyboard").style.height = Math.round(virtualKeyboardH) +"px";
	document.getElementById("virtualkeyboard").style.left = (iScreenScale*80 - virtualKeyboardW)/2 +"px";
	document.getElementById("virtualkeyboard").style.top = (iScreenScale*40) +"px";
}

function setMusic(iValue) {
	bMusic = !!iValue;
	if (gameMenu != -1)
		updateMenuMusic(gameMenu, true);
	xhr("changeParam.php", "param=2&value="+ iValue, function(reponse) {
		return (reponse == 1);
	});
}
function setSfx(iValue) {
	iSfx = !!iValue;
	xhr("changeParam.php", "param=3&value="+ iValue, function(reponse) {
		return (reponse == 1);
	});
}

function removeMenuMusic(forceRemove) {
	clearTimeout(oMusicHandler);
	if (oMusicEmbed && document.body.contains(oMusicEmbed)) {
		if (forceRemove)
			document.body.removeChild(oMusicEmbed);
		else
			fadeOutMusic(oMusicEmbed, 1, 0.8);
		oMusicEmbed = undefined;
	}
}
function removeIfExists(elt) {
	if (document.body.contains(elt))
		document.body.removeChild(elt);
	if (oMusicEmbed == elt)
		oMusicEmbed = undefined;
}
function removeGameMusics() {
	if (bMusic || iSfx) {
		var oMusics = document.getElementsByClassName("gamemusic");
		while (oMusics.length)
			document.body.removeChild(oMusics[0]);
		oMusicEmbed = undefined;
	}
}
function pauseSounds() {
	if (bMusic || iSfx) {
		var pauseMusic = playSoundEffect("musics/events/pause.mp3");
		pauseMusic.className = "";
		var oMusics = document.getElementsByClassName("gamemusic");
		for (var i=0;i<oMusics.length;i++)
			muteSound(oMusics[i]);
	}
}
function unpauseSounds() {
	if (bMusic || iSfx) {
		var pauseMusic = playSoundEffect("musics/events/pause.mp3");
		pauseMusic.className = "";
		setTimeout(function() {
			if (!pause) {
				var oMusics = document.getElementsByClassName("gamemusic");
				for (var i=0;i<oMusics.length;i++)
					unmuteSound(oMusics[i]);
			}
		}, 300);
	}
}

function setMusicVolume(embed, volume) {
	if (isOriginalEmbed(embed))
		embed.volume = volume;
	else {
		onPlayerReady(embed, function(player) {
			player.setVolume(Math.round(volume*100));
		});
	}
}
function fadeInMusic(embed, volume, ratio) {
	if (embed.fadingOut)
		return;
	embed.fadingIn = true;
	volume /= ratio;
	if (volume < 1) {
		setMusicVolume(embed,volume);
		embed.fadingIn = false;
		setTimeout(function(){fadeInMusic(embed,volume,ratio)},100);
	}
	else
		setMusicVolume(embed,1);
}
function fadeOutMusic(embed, volume, ratio, remove) {
	if (embed.fadingIn)
		return;
	embed.fadingOut = true;
	volume *= ratio;
	if (volume > 0.2) {
		setMusicVolume(embed,volume);
		setTimeout(function(){fadeOutMusic(embed,volume,ratio,remove)},100);
	}
	else {
		embed.fadingOut = false;
		if (remove === false) {
			pauseMusic(embed);
			setMusicVolume(embed,1);
		}
		else if (remove !== -1)
			stopMusic(embed);
	}
}

var oMusicHandler;
function updateMenuMusic(menu, forceUpdate) {
	if ((menu != gameMenu) || forceUpdate) {
		gameMenu = menu;
		removeMenuMusic(!bMusic);
		if (bMusic)
			playMusicSmoothly("musics/menu/"+ (gameMenu ? "selection":"main") +".mp3", forceUpdate?0:undefined);
	}
}

function playMusicSmoothly(src,delay) {
	if (undefined === delay)
		delay = 1000;
	oMusicEmbed = document.createElement("audio");
	oMusicEmbed.setAttribute("loop", true);
	oMusicEmbed.style.position = "absolute";
	oMusicEmbed.style.left = "-1000px";
	oMusicEmbed.style.top = "-1000px";
	var oMusicSource = document.createElement("source");
	oMusicSource.type = "audio/mpeg";
	oMusicSource.src = src;
	oMusicEmbed.appendChild(oMusicSource);
	clearTimeout(oMusicHandler);
	if (delay) {
		oMusicHandler = setTimeout(function() {
			oMusicEmbed.play();
		}, delay);
	}
	else
		oMusicEmbed.setAttribute("autoplay", true);
	document.body.appendChild(oMusicEmbed);
}
function playMusicRoughly(src) {
	playMusicSmoothly(src,0);
}

var objets;
if (isBattle) {
	objets = [
		"fauxobjet", "fauxobjet",  "fauxobjet", "fauxobjet", "banane", "banane", "banane", "banane", "banane", "banane", "banane", "carapace", "carapace", "carapace", "carapace",	// 1er
		"carapace", "carapace", "carapace", "carapace", "carapace", "bobomb", "bobomb", "bobomb", "bobomb", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge",	// 2e
		"carapace", "bobomb", "bobomb", "banane", "carapace", "carapace", "carapace", "carapace", "carapace", "banane", "banane", "fauxobjet", "carapacerouge", "carapacerouge", "carapacerouge",	// 3e
		"banane", "banane", "banane", "banane", "banane", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge",	// 4e
		"carapacebleue", "carapacebleue", "carapacerouge", "megachampi", "megachampi", "megachampi", "megachampi", "etoile", "etoile", "etoile", "etoile", "champi", "champi", "champi", "champi" // 5e
	];
}
else {
	objets = [
		"fauxobjet", "fauxobjet",  "fauxobjet", "fauxobjet", "banane", "banane", "banane", "banane", "banane", "banane", "banane", "carapace", "carapace", "carapace", "carapace",	// 1er
		"carapace", "carapace", "carapace", "carapace", "carapace", "bobomb", "bobomb", "bobomb", "bobomb", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge",	// 2e
		"carapace", "bobomb", "bobomb", "bobomb", "bobomb", "bobomb", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge",	// 3e
		"bobomb", "bobomb", "bobomb", "bobomb", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge",	// 4e
		"carapacebleue", "carapacebleue", "carapacebleue", "carapacerouge", "carapacerouge", "megachampi", "megachampi", "megachampi", "megachampi", "champi", "champi", "champi", "champi", "champi", "champi",	// 5e

		"carapacebleue", "megachampi", "megachampi", "megachampi", "megachampi", "megachampi", "megachampi", "megachampi", "megachampi", "megachampi", "etoile", "etoile", "etoile", "etoile", "etoile",	// 6e
		"megachampi", "megachampi", "megachampi", "megachampi", "megachampi", "megachampi", "megachampi", "etoile", "etoile", "etoile", "etoile", "etoile", "etoile", "billball", "billball", // 7e
		"megachampi", "megachampi", "etoile", "etoile", "etoile", "etoile", "etoile", "billball", "billball", "billball", "billball", "billball", "eclair", "eclair", "eclair" // 8e
	];
}

var oBgLayers = new Array();
var oPlayer;
if (!pause) {
	if (baseCp)
		cp = baseCp;
	baseCp = {};
	customPersos = {};
	nBasePersos = 0;
	for (joueurs in cp) {
		aPlayers.push(joueurs);
		baseCp[joueurs] = cp[joueurs];
		nBasePersos++;
	}
}

var bananes = new Array();
var fauxobjets = new Array();
var carapaces = new Array();
var bobombs = new Array();
var carapacesRouge = new Array();
var carapacesBleue = new Array();
var strPlayer = "";
var oMap;
if (pause)
	strPlayer = fInfos;

var oMapImg;

function resetGame(strMap) {
	oMap = oMaps[strMap];
	loadMap();
}

var oPlanDiv,oPlanDiv2, oPlanCtn,oPlanCtn2, oPlanImg,oPlanImg2;

var oPlanWidth, oPlanSize, oPlanRealSize, oCharWidth, oObjWidth, oExpWidth;
var oPlanWidth2, oPlanSize2, oCharWidth2, oObjWidth2, oExpWidth2;
var oCharRatio, oPlanRatio;
var oPlanCharacters = new Array(), oPlanObjects = new Array(), oPlanDecor = new Array(),
	oPlanFauxObjets = new Array(), oPlanBananes = new Array(), oPlanBobOmbs = new Array(),
	oPlanCarapaces = new Array(), oPlanCarapacesRouges = new Array(), oPlanCarapacesBleues = new Array(),
	oPlanEtoiles = new Array(), oPlanBillballs = new Array();
var oPlanCharacters2 = new Array(), oPlanObjects2 = new Array(), oPlanDecor2 = new Array(),
	oPlanFauxObjets2 = new Array(), oPlanBananes2 = new Array(), oPlanBobOmbs2 = new Array(),
	oPlanCarapaces2 = new Array(), oPlanCarapacesRouges2 = new Array(), oPlanCarapacesBleues2 = new Array(),
	oPlanEtoiles2 = new Array(), oPlanBillballs2 = new Array();

function posImg(elt, eltX,eltY,eltR, eltW, mapW) {
	var fRelX = -eltX/oPlanRealSize, fRelY = -eltY/oPlanRealSize;
	elt.style.transform = elt.style.WebkitTransform = elt.style.MozTransform = "translate("+ -Math.round(mapW*fRelX + eltW/2) +"px, "+ -Math.round(mapW*fRelY + eltW/2) +"px) rotate("+ Math.round(180-eltR) +"deg)";
	return elt;
}
function setPlanPos() {
	var fRotation = Math.round(oPlayer.rotation-180);
	var fCosR = direction(1,fRotation), fSinR = direction(0,fRotation);
	function createObject(elts, src, eltW, iPlanCtn) {
		var res = document.createElement("img");
		res.src = "images/map_icons/"+ src +".png";
		res.style.position = "absolute";
		res.style.width = eltW;
		iPlanCtn.appendChild(res);
		return res;
	}
	function setObject(elt, eltX,eltY, eltW,mapW) {
		return posImg(elt, eltX,eltY,oPlayer.rotation, eltW,mapW);
	}
	function syncObjects(elts,objs,src, eltW,iPlanCtn) {
		if (elts.length != objs.length) {
			while (elts.length < objs.length)
				elts.push(createObject(elts,src, eltW,iPlanCtn));
			while (elts.length > objs.length) {
				iPlanCtn.removeChild(elts[0]);
				elts.shift();
			}
		}
	}
	var fRelX = oPlayer.x/oPlanRealSize, fRelY = oPlayer.y/oPlanRealSize;
	oPlanCtn.style.transform = oPlanCtn.style.WebkitTransform = oPlanCtn.style.MozTransform = "translate("+ -Math.round(oPlanSize*(fRelX*fCosR - fRelY*fSinR) - oPlanWidth/2) +"px, "+ -Math.round(oPlanSize*(fRelX*fSinR + fRelY*fCosR) - oPlanWidth/2) +"px) rotate("+ fRotation +"deg)";

	function setKartsPos(iPlanCharacters, iCharWidth, iMapW) {
		for (var i=0;i<aKarts.length;i++) {
			var iCharW = Math.round(iCharWidth * (aKarts[i].billball ? 1.5:aKarts[i].size));
			var updatePos = true;
			if (aKarts[i].loose) {
				if (aKarts[i] == oPlayer)
					iPlanCharacters[i].style.opacity = 0.4;
				else {
					iPlanCharacters[i].style.display = "none";
					updatePos = false;
				}
			}
			if (updatePos) {
				var iCharW = Math.round(iCharWidth * (aKarts[i].billball ? 1.5:aKarts[i].size));
				iPlanCharacters[i].style.width = iCharW +"px";
				posImg(iPlanCharacters[i], aKarts[i].x,aKarts[i].y,aKarts[i].rotation-aKarts[i].tourne*360/21, iCharW, iMapW);
			}
		}
	}
	setKartsPos(oPlanCharacters, oCharWidth, oPlanSize);
	setKartsPos(oPlanCharacters2, oCharWidth2, oPlanSize2);

	function setObjPos(iPlanObjects) {
		for (var i=0;i<oMap.arme.length;i++) {
			if (isNaN(oMap.arme[i][2]))
				iPlanObjects[i].style.display = "block";
			else
				iPlanObjects[i].style.display = "none";
		}
	}
	setObjPos(oPlanObjects);
	setObjPos(oPlanObjects2);

	function setDecorPos(iPlanDecor,iObjWidth,iPlanCtn,iPlanSize) {
		if (oMap.decor && ((oMap.decor.length-1) != iPlanDecor.length)) {
			syncObjects(iPlanDecor,oMap.decor.slice(1),oMap.decor[0], iObjWidth,iPlanCtn);
			for (var i=1;i<oMap.decor.length;i++)
				setObject(iPlanDecor[i-1],oMap.decor[i][0],oMap.decor[i][1], iObjWidth,iPlanSize);
		}
	}
	setDecorPos(oPlanDecor, oObjWidth, oPlanCtn, oPlanSize);
	setDecorPos(oPlanDecor2, oObjWidth2, oPlanCtn2, oPlanSize2);

	syncObjects(oPlanFauxObjets,fauxobjets,"objet", oObjWidth,oPlanCtn);
	syncObjects(oPlanFauxObjets2,fauxobjets,"objet", oObjWidth2,oPlanCtn2);
	for (var i=0;i<fauxobjets.length;i++) {
		setObject(oPlanFauxObjets[i],fauxobjets[i][2],fauxobjets[i][3], oObjWidth,oPlanSize);
		setObject(oPlanFauxObjets2[i],fauxobjets[i][2],fauxobjets[i][3], oObjWidth2,oPlanSize2);
		oPlanFauxObjets[i].style.zIndex = oPlanFauxObjets2[i].style.zIndex = 2;
	}
	syncObjects(oPlanBananes,bananes,"banane", oObjWidth,oPlanCtn);
	syncObjects(oPlanBananes2,bananes,"banane", oObjWidth2,oPlanCtn2);
	for (var i=0;i<bananes.length;i++) {
		setObject(oPlanBananes[i],bananes[i][2],bananes[i][3], oObjWidth,oPlanSize);
		setObject(oPlanBananes2[i],bananes[i][2],bananes[i][3], oObjWidth2,oPlanSize2);
		oPlanBananes[i].style.zIndex = oPlanBananes2[i].style.zIndex = 2;
	}

	function setBobombPos(iPlanBobOmbs, iObjWidth,iPlanCtn, iPlanSize, iExpWidth) {
		syncObjects(iPlanBobOmbs,bobombs,"bob-omb", iObjWidth,iPlanCtn);
		for (var i=0;i<bobombs.length;i++) {
			if (bobombs[i][7] <= 0) {
				posImg(iPlanBobOmbs[i], bobombs[i][2],bobombs[i][3],Math.round(oPlayer.rotation), iExpWidth,iPlanSize).src = "images/map_icons/explosion.png";
				iPlanBobOmbs[i].style.width = iExpWidth +"px";
				iPlanBobOmbs[i].style.opacity = Math.max(1+bobombs[i][7]/10, 0);
			}
			else
				setObject(iPlanBobOmbs[i],bobombs[i][2],bobombs[i][3], iObjWidth,iPlanSize).style.zIndex = 2;
		}
	}
	setBobombPos(oPlanBobOmbs, oObjWidth,oPlanCtn, oPlanSize, oExpWidth);
	setBobombPos(oPlanBobOmbs2, oObjWidth2,oPlanCtn2, oPlanSize2, oExpWidth2);

	syncObjects(oPlanCarapaces,carapaces,"carapace", oObjWidth,oPlanCtn);
	syncObjects(oPlanCarapaces2,carapaces,"carapace", oObjWidth2,oPlanCtn2);
	for (var i=0;i<carapaces.length;i++) {
		setObject(oPlanCarapaces[i],carapaces[i][2],carapaces[i][3], oObjWidth,oPlanSize);
		setObject(oPlanCarapaces2[i],carapaces[i][2],carapaces[i][3], oObjWidth2,oPlanSize2).style.zIndex = 2;
	}

	syncObjects(oPlanCarapacesRouges,carapacesRouge,"carapace-rouge", oObjWidth,oPlanCtn);
	syncObjects(oPlanCarapacesRouges2,carapacesRouge,"carapace-rouge", oObjWidth2,oPlanCtn2);
	for (var i=0;i<carapacesRouge.length;i++) {
		setObject(oPlanCarapacesRouges[i],carapacesRouge[i][2],carapacesRouge[i][3], oObjWidth,oPlanSize);
		setObject(oPlanCarapacesRouges2[i],carapacesRouge[i][2],carapacesRouge[i][3], oObjWidth2,oPlanSize2).style.zIndex = 2;
		if (carapacesRouge[i][5])
			oPlanCarapacesRouges[i].style.zIndex = 2;
	}

	function setCarapacesBleuesPos(iPlanCarapacesBleues, iObjWidth,iPlanSize,iExpWidth,iPlanCtn) {
		syncObjects(iPlanCarapacesBleues,carapacesBleue,"carapace-bleue",iObjWidth,iPlanCtn);
		for (var i=0;i<carapacesBleue.length;i++) {
			if (carapacesBleue[i][5] <= 0) {
				posImg(iPlanCarapacesBleues[i], carapacesBleue[i][2],carapacesBleue[i][3],Math.round(oPlayer.rotation), iExpWidth,iPlanSize).src = "images/map_icons/explosionB.png";
				iPlanCarapacesBleues[i].style.width = iExpWidth +"px";
				iPlanCarapacesBleues[i].style.opacity = Math.max(1+carapacesBleue[i][5]/10, 0);
			}
			else
				setObject(iPlanCarapacesBleues[i],carapacesBleue[i][2],carapacesBleue[i][3], iObjWidth,iPlanSize).style.zIndex = 2;
		}
	}
	setCarapacesBleuesPos(oPlanCarapacesBleues, oObjWidth,oPlanSize,oExpWidth,oPlanCtn);
	setCarapacesBleuesPos(oPlanCarapacesBleues2, oObjWidth2,oPlanSize2,oExpWidth2,oPlanCtn2);

	var oStars = new Array(), oBillBalls = new Array();
	for (var i=0;i<aKarts.length;i++) {
		if (aKarts[i].etoile)
			oStars.push(aKarts[i]);
		else if (aKarts[i].billball)
			oBillBalls.push(aKarts[i]);
	}
	syncObjects(oPlanEtoiles,oStars,"etoile", oObjWidth,oPlanCtn);
	syncObjects(oPlanEtoiles2,oStars,"etoile", oObjWidth2,oPlanCtn2);
	for (var i=0;i<oStars.length;i++) {
		setObject(oPlanEtoiles[i],oStars[i].x,oStars[i].y, oObjWidth,oPlanSize);
		setObject(oPlanEtoiles2[i],oStars[i].x,oStars[i].y, oStarWidth2,oPlanSize2).style.width = oStarWidth2;
		oPlanEtoiles[i].style.zIndex = oPlanEtoiles2[i].style.zIndex = 2;
	}
	syncObjects(oPlanBillballs,oBillBalls,"billball", oObjWidth,oPlanCtn);
	syncObjects(oPlanBillballs2,oBillBalls,"billball", oObjWidth2,oPlanCtn2);
	for (var i=0;i<oBillBalls.length;i++) {
		posImg(oPlanBillballs[i],oBillBalls[i].x,oBillBalls[i].y, Math.round(oPlayer.rotation), oBBWidth,oPlanSize).style.width = oBBWidth;
		posImg(oPlanBillballs2[i],oBillBalls[i].x,oBillBalls[i].y, Math.round(oPlayer.rotation), oBBWidth2,oPlanSize2).style.width = oBBWidth2;
		oPlanBillballs[i].style.zIndex = oPlanBillballs2[i].style.zIndex = 2;
	}
}
function removePlan() {
	try {
		document.body.removeChild(oPlanDiv);
	}
	catch (e) {
	}
	try {
		document.body.removeChild(oPlanDiv2);
	}
	catch (e) {
	}
}

function loadMap() {
	oMapImg = new Image();

	oMapImg.onload = startGame;

	oMapImg.src = isCup ? (complete ? "images/uploads/"+ (course=="BB"?"course":"map") + oMap.map +"."+ oMap.ext:"mapcreate.php"+ oMap.map):"images/tracks/map"+ oMap.map +".png";

	setSRest();
	document.body.style.cursor = "progress";
	document.getElementById("compteur0").style.left = "12px";
	document.getElementById("compteur0").style.top = iScreenScale * 35 + 10 +"px";
	document.getElementById("compteur0").style.fontSize = iScreenScale * 2+"pt";
	document.getElementById("compteur0").innerHTML = (course != "BB") ? (oMap.sections ? "Section":toLanguage("Lap","Tour")) + ' <span id="tour0">1</span>/'+ oMap.tours : '&nbsp;<img src="images/sprites/sprite_ballon_smooth.png" style="width: '+(iScreenScale*2)+'" /><img src="images/sprites/sprite_ballon_smooth.png" style="width: '+(iScreenScale*2)+'" /><img src="images/sprites/sprite_ballon_smooth.png" style="width: '+(iScreenScale*2)+'" /><img src="images/sprites/sprite_ballon_smooth.png" style="width: '+(iScreenScale*2)+'" />';
	document.getElementById("objet0").style.left = "12px";
	document.getElementById("objet0").style.width = iScreenScale * 10 +"px";
	document.getElementById("objet0").style.height = iScreenScale * 10 +"px";
	document.getElementById("objet0").style.visibility = "visible";
	document.getElementById("temps0").style.left = (56*iScreenScale) +"px";
	document.getElementById("temps0").style.fontSize = iScreenScale * 2 +"pt";
	document.getElementById("lakitu0").style.width = iScreenScale * 9 +"px";
	document.getElementById("lakitu0").style.height = Math.round(iScreenScale*6.6) +"px";
	document.getElementById("lakitu0").style.fontSize = Math.round(iScreenScale*2.3) +"px";
	getDriftImg(0).style.width = iScreenScale * 8 +"px";
	document.getElementById("drift0").style.left = (iScreenScale * 36 + 12) +"px";
	document.getElementById("drift0").style.top = Math.round(iScreenScale*32 + 10) +"px";
	getDriftImg(0).style.left = "0px";
	getDriftImg(0).style.top = "0px";
	document.getElementById("infos0").style.left = (10+35*iScreenScale) +"px";
	document.getElementById("infos0").style.top = 10 + 8 * iScreenScale +"px";
	document.getElementById("infos0").style.fontSize = iScreenScale * 10 +"pt";
	document.getElementById("infos0").innerHTML = '<tr><td id="decompte0">3</td></tr>';
	document.getElementById("infoPlace0").style.left = (iScreenScale*58+10) +"px";
	document.getElementById("infoPlace0").style.top = iScreenScale * 24 + 10 +"px";
	document.getElementById("infoPlace0").style.width = (iScreenScale*22) +"px";
	document.getElementById("infoPlace0").style.fontSize = iScreenScale * 10 +"pt";
	document.getElementById("scroller0").style.width = iScreenScale * 8 +"px";
	document.getElementById("scroller0").style.height = iScreenScale * 8 +"px";
	document.getElementById("scroller0").width = iScreenScale * 8 +"px";
	document.getElementById("scroller0").height = iScreenScale * 8 +"px";
	document.getElementById("scroller0").style.top = (20-(8-iScreenScale)*1.25)+"px";
	document.getElementById("scroller0").style.left = (21-(8-iScreenScale)*1.25)+"px";
	document.getElementById("scroller0").getElementsByTagName("div")[0].style.left = Math.round(iScreenScale*0.1 + 1) +"px";
	document.getElementById("mariokartcontainer").style.top = iScreenScale * 31 + 10 +"px";

	var lObjet = iScreenScale * 8 - 3;
	for (var j=0;j<document.getElementsByClassName("aObjet").length;j++)
		document.getElementsByClassName("aObjet")[j].style.width = lObjet +"px";

	removeMenuMusic();
}
var vitesse;
var time = 0;
var timer = 0;
iScreenScale = optionOf("screenscale");

var fMaxRotInc = 6;

function arme(ID) {
	var oKart = aKarts[ID];
	if (!oKart.using[0]) {
		if (oKart.roulette != 25) return;
		var tpsUse;
		switch(oKart.arme) {
			case "champi" :
			tpsUse = 20;
			oKart.maxspeed = 11;
			oKart.speed = 11;
			playIfShould(oKart,"musics/events/boost.mp3");
			break;

			case "etoile" :
			tpsUse = 60;
			oKart.sprite.img.src = getStarSrc(oKart.personnage);
			oKart.speedinc *= 5;
			oKart.protect = true;
			if (shouldPlayMusic(oKart))
				postStartMusic("musics/events/starman.mp3");
			break;

			case "billball" :
			tpsUse = 50;
			oKart.sprite.img.src = "images/sprites/sprite_billball_smooth.png";
			oKart.rotinc = 0;
			oKart.size = 2.5;
			oKart.z = 2;
			oKart.protect = true;
			oKart.champi = 0;
			resetPowerup(oKart);
			playIfShould(oKart,"musics/events/boost.mp3");
			break;

			case "megachampi" :
			tpsUse = 50;
			oKart.size = 1;
			updateDriftSize(ID);
			oKart.protect = true;
			if (shouldPlayMusic(oKart))
				postStartMusic("musics/events/megamushroom.mp3");
			break;

			case "eclair" :
			tpsUse = 100;
			for (i=0;i<aKarts.length;i++) {
				var kart = aKarts[i];
				if (kart != oKart) {
					if (!kart.protect) {
						kart.size = 0.6;
						updateDriftSize(i);
						kart.arme = false;
						if (kart.using[0]) {
							if (kart.using[0][kart.using[1]][4])
								kart.using[0][kart.using[1]][4] = 0;
							kart.using = [false];
						}
						kart.champi = 0;
						spinKart(kart,20);
						kart.roulette = 0;
						supprArme(i);
					}
					else
						kart.megachampi = (kart.megachampi<8 || kart.etoile ? kart.megachampi : 8);
				}
			}
			if (iSfx && !finishing && !oPlayer.cpu)
				playSoundEffect("musics/events/lightning.mp3");
			document.getElementById("mariokartcontainer").style.opacity = 0.7;
			break;

			case "banane" :
			oKart.using = [bananes, (bananes.length), "banane"];
			bananes.push([new Sprite("banane"), -1, (oKart.x - 5 * direction(0, oKart.rotation)), (oKart.y - 5 * direction(1, oKart.rotation)), oKart.z]);
			playIfShould(oKart,"musics/events/item_store.mp3");
			break;

			case "fauxobjet" :
			oKart.using = [fauxobjets, (fauxobjets.length), "fauxobjet"];
			fauxobjets.push([new Sprite("objet"), -1, (oKart.x - 5 * direction(0, oKart.rotation)), (oKart.y - 5 * direction(1, oKart.rotation)), oKart.z]);
			playIfShould(oKart,"musics/events/item_store.mp3");
			break;

			case "carapace" :
			oKart.using = [carapaces, (carapaces.length), "carapace"];
			carapaces.push([new Sprite("carapace"), -1, (oKart.x - 5 * direction(0, oKart.rotation)), (oKart.y - 5 * direction(1, oKart.rotation)), oKart.z, -1, 10]);
			playIfShould(oKart,"musics/events/item_store.mp3");
			break;

			case "carapacerouge" :
			oKart.using = [carapacesRouge, (carapacesRouge.length), "carapacerouge"];
			carapacesRouge.push([new Sprite("carapace-rouge"), -1, (oKart.x - 5 * direction(0, oKart.rotation)), (oKart.y - 5 * direction(1, oKart.rotation)), oKart.z, -1, -1, -1]);
			playIfShould(oKart,"musics/events/item_store.mp3");
			break;

			case "carapacebleue" :
			var cible = aKarts.length-1;
			var cPlace = 1;
			for (var i=0;i<aKarts.length;i++) {
				if (aKarts[i].place == cPlace) {
					if (aKarts[i].tours <= oMap.tours || course == "BB") {
						cible = aKarts[i].id;
						i = aKarts.length;
					}
					else {
						cPlace++;
						i = -1;
					}
				}
			}
			carapacesBleue.push([new Sprite("carapace-bleue"), -1, oKart.x,oKart.y, cible, 5]);
			playDistSound(oKart,"musics/events/throw.mp3",50);
			break;

			case "bobomb" :
			oKart.using = [bobombs, (bobombs.length), "bobomb"];
			bobombs.push([new Sprite("bob-omb"), -1, (oKart.x - 5 * direction(0, oKart.rotation)), (oKart.y - 5 * direction(1, oKart.rotation)), oKart.z,-1,15,30]);
			playIfShould(oKart,"musics/events/item_store.mp3");
			break;
		}

		if (tpsUse)
			oKart[oKart.arme] = tpsUse;
		
		stopDrifting(ID);

		supprArme(ID);
	}
	else {
		var posX = oKart.x;
		var posY = oKart.y;

		switch(oKart.using[2]) {
			case "banane" :
			var decalage = 30/(oKart.speed+5);
			var fPosX = posX - decalage * direction(0, oKart.rotation);
			var fPosY = posY - decalage * direction(1, oKart.rotation);
			if (!tombe(Math.round(fPosX),Math.round(fPosY)))
			bananes.push([new Sprite("banane"), -1, fPosX, fPosY, 0]);
			playIfShould(oKart,"musics/events/put.mp3");
			break;

			case "fauxobjet" :
			var decalage = 30/(oKart.speed+5);
			fauxobjets.push([new Sprite("objet"), -1, posX - decalage * direction(0, oKart.rotation),posY - decalage * direction(1, oKart.rotation), 0]);
			playIfShould(oKart,"musics/events/put.mp3");
			break;

			case "carapace" :
			carapaces.push([new Sprite("carapace"), -1, posX + 15 * direction(0, oKart.rotation),posY + 15 * direction(1, oKart.rotation),0,oKart.rotation,10]);
			playDistSound(oKart,"musics/events/throw.mp3",50);
			break;

			case "carapacerouge" :
			carapacesRouge.push([new Sprite("carapace-rouge"), -1, posX + 15 * direction(0, oKart.rotation),posY + 15 * direction(1, oKart.rotation),0,oKart.rotation,oKart.id,-1]);
			playDistSound(oKart,"musics/events/throw.mp3",50);
			break;

			case "bobomb" :
			bobombs.push([new Sprite("bob-omb"), -1, posX,posY,0,oKart.rotation,15,30]);
			playDistSound(oKart,"musics/events/throw.mp3",50);
			break;

			default :
			return;
		}

		detruit(oKart.using[0], oKart.using[1]);
	}
}

var aKarts = new Array();
var bRunning = false;
var bCounting = false;

var musicIdInc = 0;
function loadMusic(src, autoplay) {
	var res;
	var isOriginal = isOriginalMusic(src);
	if (isOriginal) {
		res = document.createElement("audio");
		res.setAttribute("loop", true);
	}
	else {
		var ytId = youtube_parser(src);
		res = document.createElement("iframe");
		res.id = "youtube-video-"+(musicIdInc++);
		res.src = "https://www.youtube.com/embed/"+ ytId +"?"+ (autoplay ? "autoplay=1&amp;":"") +"loop=1&amp;playlist="+ ytId + "&amp;&amp;enablejsapi=1";
		res.setAttribute("enablejsapi", 1);
	}
	res.className = "gamemusic";
	if (isOriginal) {
		var oMusicSource = document.createElement("source");
		oMusicSource.type = "audio/mpeg";
		res.src = src;
		res.appendChild(oMusicSource);
		if (autoplay)
			res.setAttribute("autoplay", true);
	}
	return res;
}
function pauseMusic(elt) {
	var isOriginal = isOriginalEmbed(elt);
	if (isOriginal)
		elt.pause();
	else {
		onPlayerReady(elt, function(player) {
			player.pauseVideo();
		});
	}
	if (oMusicEmbed == elt)
		oMusicEmbed = undefined;
}
function bufferMusic(elt) {
	var isOriginal = isOriginalEmbed(elt);
	if (!isOriginal) {
		onPlayerReady(elt, function(player) {
			player.setVolume(0);
			player.playVideo();
			setTimeout(function() {
				player.seekTo(0,true);
				player.setVolume(100);
				player.pauseVideo();
			}, 1000);
		});
	}
}
function stopMusic(elt) {
	if (!elt)
		return;
	if (elt.permanent)
		pauseMusic(elt);
	else
		removeIfExists(elt);
}
function unpauseMusic(elt) {
	if (document.body.contains(elt)) {
		var isOriginal = isOriginalEmbed(elt);
		if (isOriginal)
			elt.play();
		else {
			onPlayerReady(elt, function(player) {
				player.playVideo();
			});
		}
		oMusicEmbed = elt;
	}
}
function muteSound(elt) {
	var isOriginal = isOriginalEmbed(elt);
	if (isOriginal)
		elt.muted = true;
	else {
		onPlayerReady(elt, function(player) {
			player.mute();
		});
	}
}
function unmuteSound(elt) {
	var isOriginal = isOriginalEmbed(elt);
	if (isOriginal)
		elt.muted = false;
	else {
		onPlayerReady(elt, function(player) {
			player.unMute();
		});
	}
}
function onPlayerReady(elt, onReady) {
	try {
		if (elt.yt) {
			if (elt.tasks)
				elt.tasks.push(onReady);
			else
				onReady(elt.yt);
		}
		else {
			elt.tasks = [onReady];
			elt.yt = new YT.Player(elt.id, {
				events: {
					'onReady': function() {
						for (var i=0;i<elt.tasks.length;i++)
							elt.tasks[i](elt.yt);
						elt.tasks = undefined;
					}
				}
			});
		}
	}
	catch (e) {
	}
}
function updateMusic(elt,fast) {
	if (elt != oMusicEmbed)
		removeIfExists(oMusicEmbed);
	if (document.body.contains(elt)) {
		var isOriginal = isOriginalEmbed(elt);
		if (isOriginal) {
			if (fast) {
				elt.volume = 1;
				elt.currentTime = 0;
				elt.play();
				elt.playbackRate = 1.2;
			}
		}
		else {
			onPlayerReady(elt, function(player) {
				if (fast) {
					player.setPlaybackRate(1.25);
					player.seekTo(0,true);
					player.setVolume(100);
					player.playVideo();
				}
			});
		}
		oMusicEmbed = elt;
	}
}
function fastenMusic(elt) {
	var isOriginal = isOriginalEmbed(elt);
	if (isOriginal)
		elt.playbackRate = 1.2;
	else {
		onPlayerReady(elt, function(player) {
			player.setPlaybackRate(1.25);
		});
	}
}
function shouldPlaySound(oKart) {
	return iSfx && (oKart == oPlayer) && !finishing && !oKart.cpu && !oKart.loose;
}
function shouldPlayMusic(oKart) {
	return bMusic && (oKart == oPlayer) && !finishing && !oKart.cpu && !oKart.loose;
}
function playIfShould(oKart, src) {
	if (shouldPlaySound(oKart))
		return playSoundEffect(src);
}
function playSoundEffect(src) {
	var elt = loadMusic(src, true);
	elt.removeAttribute("loop");
	elt.onended = function() {
		document.body.removeChild(this);
	};
	document.body.appendChild(elt);
	return elt;
}
function playDistSound(obj, src, maxDist) {
	if (iSfx) {
		var pow0 = maxDist/distKart(obj);
		if (pow0 >= 1) {
			var res = playSoundEffect(src);
			res.volume = Math.min(0.05*pow0*pow0, 1);
			return res;
		}
	}
}
function startMusic(src, autoplay, delay) {
	var res = loadMusic(src, autoplay);
	document.body.appendChild(res);
	if (delay) {
		pauseMusic(res);
		var lMusicEmbed = oMusicEmbed;
		setTimeout(function() {
			if (oMusicEmbed == res) {
				stopMusic(lMusicEmbed);
				unpauseMusic(res);
			}
		}, delay);
		oMusicEmbed = res;
	}
	else if (autoplay) {
		stopMusic(oMusicEmbed);
		oMusicEmbed = res;
	}
	return res;
}
function postStartMusic(src) {
	if (oMusicEmbed)
		fadeOutMusic(oMusicEmbed,1,0.6,false);
	return startMusic(src,true,200);
}
function postResumeMusic(elt, ratio) {
	if (oMusicEmbed == elt)
		return;
	var cMusicEmbed = oMusicEmbed;
	fadeOutMusic(cMusicEmbed,1,ratio,true);
	setTimeout(function() {
		if ((oMusicEmbed == cMusicEmbed) || !oMusicEmbed) {
			fadeInMusic(elt,0.2,ratio);
			unpauseMusic(elt);
		}
	}, 500);
}
function stopStarMusic(oKart) {
	if (shouldPlayMusic(oKart))
		postResumeMusic(mapMusic, 0.9);
}
function stopMegaMusic(oKart) {
	if (shouldPlayMusic(oKart))
		postResumeMusic(mapMusic, 0.92);
}
function resetPowerup(oKart) {
	if (oKart.etoile) {
		oKart.etoile = 0;
		stopStarMusic(oKart);
	}
	if (oKart.megachampi) {
		oKart.megachampi = 0;
		stopMegaMusic(oKart);
	}
}
function isOriginalMusic(src) {
	return (src.indexOf("mp3") != -1);
}
function isOriginalEmbed(elt) {
	return (elt.tagName.toLowerCase() == "audio");
}
var mapMusic, endingMusic, carEngine, carEngine2, carEngine3, carDrift, carSpark;
var forceStartMusic = false;
var forcePrepareEnding = false;
function startMapMusic(lastlap) {
	if (lastlap)
		updateMusic(mapMusic,true);
	else {
		mapMusic = startMusic(oMap.music ? "musics/maps/map"+ oMap.music +".mp3":oMap.yt);
		mapMusic.permanent = 1;
		bufferMusic(mapMusic);
	}
}
function loadEndingMusic() {
	var endingSrc = getEndingSrc(strPlayer);
	endingMusic = startMusic(endingSrc);
	endingMusic.permanent = 1;
	bufferMusic(endingMusic);
}
function loopWithoutGap() {
	if (playingCarEngine == this) {
        var buffer = 0.44;
        if (this.currentTime > this.duration - buffer) {
            this.currentTime = 0;
            this.play();
        }
    }
}
function startEngineSound() {
	carEngine = loadMusic("musics/events/engine.mp3", true);
	carEngine2 = loadMusic("musics/events/engine2.mp3", false);
	carEngine3 = loadMusic("musics/events/engine3.mp3", false);
	carDrift = loadMusic("musics/events/drift.mp3", false);
	carSpark = loadMusic("musics/events/spark.mp3", false);
	playingCarEngine = carEngine;
	carEngine.addEventListener('timeupdate', loopWithoutGap, false);
	carEngine2.addEventListener('timeupdate', loopWithoutGap, false);
	carEngine.permanent = 1;
	carEngine2.permanent = 1;
	carEngine3.permanent = 1;
	carDrift.permanent = 1;
	carSpark.permanent = 1;
	document.body.appendChild(carEngine);
	document.body.appendChild(carEngine2);
	document.body.appendChild(carEngine3);
	document.body.appendChild(carDrift);
	document.body.appendChild(carSpark);
}
var playingCarEngine;
function updateEngineSound(elt) {
	if (iSfx && (elt != playingCarEngine)) {
		if (playingCarEngine)
			playingCarEngine.pause();
		playingCarEngine = elt;
		if (playingCarEngine)
			playingCarEngine.play();
	}
}
function startEndMusic(getId) {
	if (bMusic) {
		removeMenuMusic(true);
		removeIfExists(mapMusic);
	}
	if (iSfx) {
		playingCarEngine = undefined;
		removeIfExists(carEngine);
		removeIfExists(carEngine2);
		removeIfExists(carEngine3);
		removeIfExists(carDrift);
		removeIfExists(carSpark);
	}
	if (bMusic) {
		setTimeout(function() {
			var oMusics = document.getElementsByClassName("gamemusic");
			var musicsToRemove = [];
			for (var i=0;i<oMusics.length;i++) {
				if (!oMusics[i].permanent)
					musicsToRemove.push(oMusics[i]);
			}
			for (var i=0;i<musicsToRemove.length;i++)
				document.body.removeChild(musicsToRemove[i]);
			unpauseMusic(endingMusic);
		}, 200);
	}
	if (iSfx && course != "BB") {
		var goalSound = playSoundEffect("musics/events/goal.mp3");
		goalSound.className = "";
	}
}

function startGame() {

	resetScreen();
	
	var oPlace;
	
	for (var i=1;true;i++) {
		var continuer = true;
		for (var j=0;j<aPlaces.length;j++) {
			if (aPlaces[j] == i) {
				continuer = false;
				break;
			}
		}
		if (continuer) {
			oPlace = i;
			break;
		}
	}

	var cp0 = oMap.sections ? oMap.checkpoint.length-1:0;

	oPlayer = {
		id : identifiant,
		
		x : oMap.startposition[0] + (simplified ? 0 : (((oPlace%2)==oMap.startdirection) ? 0:18)),
		y : oMap.startposition[1] + (simplified ? 0 : oPlace*12),
		z : 0,

		personnage : strPlayer,

		speed : 0,
		speedinc : 0,
		heightinc : 0,

		rotation : simplified ? oMap.startrotation:180,
		rotincdir : 0,
		rotinc : 0,
		changeView : 0,

		size : 1,
		sprite : new Sprite(strPlayer),
		cpu : false,

		tourne : 0,
		tombe : 0,
		protect : false,

		roulette : 0,
		arme : false,
		maxspeed : vitesse,
		
		driftinc : 0,
		driftcpt : 0,
		drift : 0,
		turbodrift : 0,
		jumped : false,

		champi : 0,
		etoile : 0,
		megachampi : 0,
		eclair : 0,
		using : [false],

		place : oPlace
	};
	if (simplified) {
	switch (oMap.startdirection) {
		case -6 :
			oPlayer.x += (oPlace%2 ? 0 : 18);
			oPlayer.y += oPlace * 12;
			break;
		case -1 :
			oPlayer.x += oPlace * 12;
			oPlayer.y -= (oPlace%2 ? 0 : 18);
			break;
		case 6 :
			oPlayer.x -= (oPlace%2 ? 0 : 18);
			oPlayer.y -= oPlace * 12;
			break;
		case 1 :
			oPlayer.x -= oPlace * 12;
			oPlayer.y += (oPlace%2 ? 0 : 18);
		}
	}
	if (course != "BB") {
		oPlayer.time = 0;
		oPlayer.tours = 1;
		oPlayer.demitours = cp0;
		oPlayer.billball = 0;
	}
	else {
		oPlayer.x = oMap.startposition[oPlace-1][0];
		oPlayer.y = oMap.startposition[oPlace-1][1];
		oPlayer.rotation = oMap.startposition[oPlace-1][2]*90;
		oPlayer.loose = false;
		oPlayer.ballons = [new Sprite("ballon")];
		oPlayer.reserve = 4;
	}
	oPlayer.initialPlace = oPlayer.place;
	aKarts.push(oPlayer);


	for (i=0;i<aPlayers.length;i++) {
		var joueur = aPlayers[i];
		var oEnemy = {
			id : aIDs[i],

			speed : 0,
			speedinc : 0,
			heightinc : 0,

			rotation : simplified ? oMap.startrotation:180,
			rotincdir : 0,
			rotinc : 0,

			x : oMap.startposition[0] + (simplified ? 0 : (((aPlaces[i]%2)==oMap.startdirection) ? 0 : 18)),
			y : oMap.startposition[1] + (simplified ? 0 : aPlaces[i]*12),
			z : 0,

			size : 1,
			personnage : joueur,
			sprite : new Sprite(joueur),

			tourne : 0,
			tombe : 0,
			protect : false,

			roulette : 0,
			arme : false,

			champi : 0,
			etoile : 0,
			megachampi : 0,
			eclair : 0,
			using : [false],

			cpu : false,
			aipoint : 0,
			maxspeed : 5.7,

			place : aPlaces[i]
		};

		if (simplified) {
			switch (oMap.startdirection) {
			case -6 :
				oEnemy.x += (aPlaces[i]%2 ? 0 : 18);
				oEnemy.y += aPlaces[i] * 12;
				break;
			case -1 :
				oEnemy.x += aPlaces[i] * 12;
				oEnemy.y -= (aPlaces[i]%2 ? 0 : 18);
				break;
			case 6 :
				oEnemy.x -= (aPlaces[i]%2 ? 0 : 18);
				oEnemy.y -= aPlaces[i] * 12;
				break;
			case 1 :
				oEnemy.x -= aPlaces[i] * 12;
				oEnemy.y += (aPlaces[i]%2 ? 0 : 18);
			}
		}
		if (course != "BB") {
			oEnemy.tours = 1;
			oEnemy.demitours = cp0;
			oEnemy.billball = 0;
			oEnemy.speedinc = 0.5;
		}
		else {
			oEnemy.x = oMap.startposition[aPlaces[i]-1][0];
			oEnemy.y = oMap.startposition[aPlaces[i]-1][1];
			oEnemy.rotation = oMap.startposition[aPlaces[i]-1][2]*90;
			oEnemy.loose = false;
			oEnemy.ballons = [new Sprite("ballon")];
			oEnemy.reserve = 4;
		}
		oEnemy.initialPlace = oEnemy.place;

		aKarts.push(oEnemy);
	}
	if (oMap.decor) {
		for (var i=1;i<oMap.decor.length;i++)
			oMap.decor[i][2] = new Sprite(oMap.decor[0]);
	}

	for (var i=0;i<oMap.arme.length;i++)
		oMap.arme[i][2] = 0;

	document.getElementById("infoPlace0").innerHTML = toPlace(oPlayer.place);
	document.getElementById("infoPlace0").style.display = "block";

	oPlanWidth = Math.round(iScreenScale*19.4);
	oPlanWidth2 = (oMap.w>=oMap.h) ? oPlanWidth : Math.round(oPlanWidth*(oMap.w/oMap.h));
	var oPlanHeight2 = (oMap.w<=oMap.h) ? oPlanWidth : Math.round(oPlanWidth*(oMap.h/oMap.w));
	oPlanSize = iScreenScale*59;
	oPlanSize2 = oPlanWidth2;
	oPlanRealSize = oMap.w;
	oCharRatio = 0.8;
	oPlanRatio = 0.5;

	oPlanDiv = document.createElement("div");
	oPlanDiv.style.backgroundColor = "rgb("+ oMap.bgcolor +")";
	oPlanDiv.style.position = "absolute";
	oPlanDiv.style.left = (15 + iScreenScale*80) +"px";
	oPlanDiv.style.top = "9px";
	oPlanDiv.style.width = oPlanWidth +"px";
	oPlanDiv.style.height = oPlanWidth +"px";
	oPlanDiv.style.overflow = "hidden";

	oPlanDiv2 = document.createElement("div");
	oPlanDiv2.style.backgroundColor = "rgb("+ oMap.bgcolor +")";
	oPlanDiv2.style.position = "absolute";
	oPlanDiv2.style.left = (15 + iScreenScale*80) +"px";
	oPlanDiv2.style.top = (10 + Math.round(iScreenScale/4) + oPlanWidth) +"px";
	oPlanDiv2.style.width = oPlanWidth +"px";
	oPlanDiv2.style.height = oPlanWidth +"px";
	oPlanDiv2.style.overflow = "hidden";

	oPlanCtn = document.createElement("div");
	oPlanCtn.style.position = "absolute";
	oPlanCtn.style.transformOrigin = oPlanCtn.style.WebkitTransformOrigin = oPlanCtn.style.MozTransformOrigin = "left";

	oPlanCtn2 = document.createElement("div");
	oPlanCtn2.style.position = "absolute";
	oPlanCtn2.style.left = Math.round((oPlanWidth-oPlanWidth2)/2) +"px";
	oPlanCtn2.style.top = Math.round((oPlanWidth-oPlanHeight2)/2) +"px";
	oPlanCtn2.style.width = oPlanWidth2 +"px";
	oPlanCtn2.style.height = oPlanHeight2 +"px";

	oPlanImg = document.createElement("img");
	oPlanImg.src = oMapImg.src;
	oPlanImg.style.position = "absolute";
	oPlanImg.style.left = "0px";
	oPlanImg.style.top = "0px";
	oPlanImg.style.width = oPlanSize +"px";
	oPlanCtn.appendChild(oPlanImg);

	oPlanImg2 = document.createElement("img");
	oPlanImg2.src = oMapImg.src;
	oPlanImg2.style.position = "absolute";
	oPlanImg2.style.left = "0px";
	oPlanImg2.style.top = "0px";
	oPlanImg2.style.width = oPlanWidth2 +"px";
	oPlanCtn2.appendChild(oPlanImg2);

	oCharWidth = iScreenScale*2;
	oBBWidth = iScreenScale*2;
	oStarWidth2 = Math.round(iScreenScale*1.5);
	oObjWidth = Math.round(iScreenScale*1.5);
	oExpWidth = iScreenScale*7;

	oCharWidth2 = Math.round(oCharRatio*oCharWidth);
	oBBWidth2 = Math.round(oCharRatio*oBBWidth);
	oObjWidth2 = Math.round(oPlanRatio*oObjWidth);
	oExpWidth2 = Math.round(oPlanRatio*oExpWidth);
	for (var i=0;i<aKarts.length;i++) {
		var oCharacter = document.createElement("img");
		oCharacter.style.position = "absolute";
		oCharacter.style.zIndex = 1;
		oCharacter.style.width = oCharWidth +"px";
		oCharacter.src = getMapIcSrc(aKarts[i].personnage);
		oPlanCharacters.push(oCharacter);

		var oCharacter2 = document.createElement("img");
		oCharacter2.style.position = "absolute";
		oCharacter2.style.zIndex = 1;
		oCharacter2.style.width = oCharWidth2 +"px";
		oCharacter2.src = getMapIcSrc(aKarts[i].personnage);
		oPlanCharacters2.push(oCharacter2);
	}
	for (var i=oPlanCharacters.length-1;i>=0;i--)
		oPlanCtn.appendChild(oPlanCharacters[i]);
	for (var i=oPlanCharacters2.length-1;i>=0;i--)
		oPlanCtn2.appendChild(oPlanCharacters2[i]);
			
	for (var i=0;i<oMap.arme.length;i++) {
		fSprite = oMap.arme[i];
		fSprite[2] = new Sprite("objet");

		var oObject = document.createElement("img");
		oObject.src = "images/map_icons/objet.png";
		oObject.style.position = "absolute";
		oObject.style.display = "none";
		oObject.style.width = oObjWidth;
		posImg(oObject, fSprite[0],fSprite[1],Math.round(oPlayer.rotation), oObjWidth, oPlanSize);
		oPlanCtn.appendChild(oObject);
		oPlanObjects.push(oObject);

		var oObject2 = document.createElement("img");
		oObject2.src = "images/map_icons/objet.png";
		oObject2.style.position = "absolute";
		oObject2.style.display = "none";
		oObject2.style.width = oObjWidth2;
		posImg(oObject2, fSprite[0],fSprite[1],Math.round(oPlayer.rotation), oObjWidth2, oPlanSize2);
		oPlanCtn2.appendChild(oObject2);
		oPlanObjects2.push(oObject2);
	}
	oPlanDiv.appendChild(oPlanCtn);
	document.body.appendChild(oPlanDiv);
	oPlanDiv2.appendChild(oPlanCtn2);
	document.body.appendChild(oPlanDiv2);
	setPlanPos();

	setTimeout(render, 500);

	if (bMusic) {
		var startingMusic = playSoundEffect("musics/events/"+ (course!="BB"?"start":"startbb") +".mp3");
		startingMusic.pause();
		setTimeout(function() {
			startingMusic.play();
		}, 300);
		startingMusic.blur();
	}

	bCounting = true;

	var oCount = [document.createElement("div"), new Image()];
	oCount = [document.createElement("div"), new Image()];
	oCount[0].style.position = "absolute";
	oCount[0].style.width = (12*iScreenScale)+"px";
	oCount[0].style.height = (12*iScreenScale)+"px";
	oCount[0].style.overflow = "hidden";
	oCount[0].style.top = (4*iScreenScale)+"px";
	oCount[0].style.left = (8*iScreenScale)+"px";

	oCount[1].src = "images/lakitu_depart.png";
	oCount[1].style.position = "absolute";
	oCount[1].style.left = "0px";
	oCount[1].height = 12*iScreenScale;

	oCount[0].appendChild(oCount[1]);
	oContainer.appendChild(oCount[0]);

	oCount.scrollLeft = 0;

	var iCntStep = 0;

	var countDownMusic, goMusic;
	if (bMusic || iSfx) {
		countDownMusic = loadMusic("musics/events/countdown.mp3");
		countDownMusic.removeAttribute("loop");
		document.body.appendChild(countDownMusic);
		goMusic = loadMusic("musics/events/go.mp3");
		goMusic.removeAttribute("loop");
		document.body.appendChild(goMusic);
		goMusic.blur();
		if (!isMobile()) {
			var oDebug = document.createElement("input");
			document.body.appendChild(oDebug);
			oDebug.focus();
			oDebug.blur();
			document.body.removeChild(oDebug);
		}
	}

	var fncCount = function() {
		if (iCntStep) {
			oCount[0].scrollLeft = iCntStep * 12 * iScreenScale;
			if (iCntStep < 3) {
				document.getElementById("decompte0").innerHTML--;
				oPlayer.speed += oPlayer.speedinc;
				if (bMusic || iSfx) {
					countDownMusic.currentTime = 0;
					countDownMusic.play();
				}
			}
			else {
				document.getElementById("infos0").innerHTML = '<tr><td>'+ toLanguage('&nbsp; &nbsp; GO !', 'PARTEZ !') +'</td></tr>';
				document.getElementById("infos0").style.left = (10+20*iScreenScale) + "px";
				document.getElementById("infos0").style.fontSize = iScreenScale * 8 + "pt";
				if (oPlayer.speed == 1)
					oPlayer.speed = 11;
				else if (oPlayer.speed > 1) {
					spinKart(oPlayer,42);
					oPlayer.speed = 0;
					oPlayer.speedinc = 0;
				}
				if (bMusic || iSfx) {
					goMusic.play();
					goMusic.onended = function() {
						document.body.removeChild(countDownMusic);
						document.body.removeChild(goMusic);
					};
				};
				forcePrepareEnding = true;
				setTimeout(
					function() {
						oContainer.removeChild(oCount[0]);
						document.getElementById("infos0").style.visibility = "hidden";
						document.getElementById("infos0").style.top = iScreenScale * 7 + 10 +"px";
						document.getElementById("infos0").style.left = Math.round(iScreenScale*26+10) +"px";
						document.getElementById("infos0").style.fontSize = Math.round(iScreenScale*1.77-0.5) +"pt";
						document.getElementById("infos0").innerHTML = '<tr><td><input type="button" style="font-size: '+ iScreenScale*3 +'pt; width: 100%;" value=" &nbsp; '+ toLanguage('  RESUME  ', 'REPRENDRE') +' &nbsp; " id="reprendre" /></td></tr><tr><td style="font-size: '+ iScreenScale * 10 +'px;">&nbsp;</td></tr><tr><td><input type="button" id="quitter" value=" &nbsp; '+ toLanguage('RETRY', 'R&Eacute;&Eacute;SSAYER') +' &nbsp; " style="font-size: '+ iScreenScale*3 +'pt; width: 100%;" /></td></tr>';
						document.getElementById("reprendre").onclick = reprendre;
						document.getElementById("quitter").onclick = quitter;
						if (bMusic && !oMusicEmbed) {
							unpauseMusic(mapMusic);
							forceStartMusic = true;
						}
						bCounting = false;
					}, 1000
				);

				document.onkeydown = function(e) {
					if (forceStartMusic) {
						try {
							if (mapMusic.yt)
								mapMusic.yt.playVideo();
							forceStartMusic = false;
						}
						catch (e) {
						}
					}
					else if (forcePrepareEnding) {
						try {
							if (endingMusic.yt) {
								endingMusic.yt.setVolume(0);
								endingMusic.yt.playVideo();
								setTimeout(function() {
									endingMusic.yt.seekTo(0,true);
									endingMusic.yt.setVolume(100);
									endingMusic.yt.pauseVideo();
								}, 1000);
							}
							forcePrepareEnding = false;
						}
						catch (e) {
						}
					}
					if (keyDowned) {
						switch (e.keyCode) {
							case 38: // up
								oPlayer.speedinc = cp[oPlayer.personnage][0]*oPlayer.size;
								return false;
							case 37: // left
								oPlayer.rotincdir = cp[oPlayer.personnage][2];
								if (!oPlayer.driftinc && !oPlayer.tourne && !oPlayer.fell && (e.ctrlKey!==false||e.altKey!==false)) {
									if (oPlayer.jumped)
										oPlayer.driftinc = 1;
								}
								return false;
							case 39: // right
								oPlayer.rotincdir = -cp[oPlayer.personnage][2];
								if (!oPlayer.driftinc && !oPlayer.tourne && !oPlayer.fell && (e.ctrlKey!==false||e.altKey!==false)) {
									if (oPlayer.jumped)
										oPlayer.driftinc = -1;
								}
								return false;
							case 40: // down
								oPlayer.speedinc -= 0.2;
								return false;
							case 17: // ctrl
							case 18: // alt
								if (pause) break;
								if (!oPlayer.z && !oPlayer.heightinc) {
									if (!oPlayer.driftinc && !oPlayer.tourne) {
										oPlayer.z = 1;
										oPlayer.heightinc = 0.5;
										oPlayer.jumped = true;
										if (oPlayer.rotincdir)
											oPlayer.driftinc = (oPlayer.rotincdir>0) ? 1:-1;
									}
								}
								else if (!oPlayer.jumped && !oPlayer.fell && !oPlayer.ctrled && !oPlayer.billball && !oPlayer.tourne && !oPlayer.figuring && !oPlayer.figstate)
									stuntKart(oPlayer);
								break;
							case 16 : // shift
								if (pause) return;
								if (course == "BB") {
									if (!oPlayer.tourne && oPlayer.reserve && oPlayer.ballons.length < 3 && !oPlayer.sprite.div.style.opacity) {
										oPlayer.ballons[oPlayer.ballons.length] = new Sprite("ballon");
										oPlayer.reserve--;
										document.getElementById("compteur0").innerHTML = "&nbsp;";
										for (i=0;i<oPlayer.reserve;i++)
											document.getElementById("compteur0").innerHTML += '<img src="images/sprites/sprite_ballon_smooth.png" style="width: '+(iScreenScale*2)+'" />';
										playIfShould(oPlayer,"musics/events/balloon.mp3");
									}
								}
								return false;
							case 27: // escape
								quitter();
								break;
							default:
								return true;
						}
					}
					else
						keyDowned = true;
				}
				document.onkeyup = function(e) {
					if (keyUped) {
						switch (e.keyCode) {
							case 32: // space
								if (!oPlayer.tourne && !pause)
								arme(0);
								break;
							case 38: // up
								oPlayer.speedinc = 0;
								break;
							case 37: // left
								oPlayer.rotincdir = 0;
								break;
							case 39: // right
								oPlayer.rotincdir = 0;
								break;
							case 40: // down
								oPlayer.speedinc = 0;
								break;
							case 17: // ctrl
							case 18: // alt
								if (pause) break;
								if (oPlayer.driftinc) {
									oPlayer.driftinc = 0;
									if (oPlayer.driftcpt >= 15) {
										oPlayer.turbodrift = 15;
										getDriftImg(0).src = "images/drift.png";
									}
									oPlayer.driftcpt = 0;
									document.getElementById("drift0").style.display = "none";
									if (oPlayer.driftSound) {
										oPlayer.driftSound.pause();
										oPlayer.driftSound = undefined;
									}
								}
								oPlayer.ctrled = false;
								if (oPlayer.jumped) {
									if (oPlayer.z || oPlayer.heightinc)
										oPlayer.ctrled = true;
								}
								break;
							case 88: // X
								if (!bCounting) {
									var nView = 180 - oPlayer.changeView;
									oPlayer.changeView = nView;
									oPlayer.sprite.setState(11);
								}
						}
					}
					else
						keyUped = true;
				}
				if (isMobile()) {
					document.onmousedown = function(e) {
						if (pause)
							return true;
						if (!oPlayer.tourne) {
							if (course == "BB") {
								document.onkeydown({"keyCode":16});
								return false;
							}
							if (oPlayer.arme || oPlayer.using[0]) {
								arme(0);
								return false;
							}
							return true;
						}
						return true;
					}
				}
				window.onbeforeunload = function() {
					return language ? "Caution, if you leave the game, you are considered loser" : "Attention, si vous quittez la partie, vous êtes considéré comme perdant";
				}
				keyDowned = true;
				keyUped = true;
				pause = false;
				cycle();
				bRunning = true;
				return;
			}
		}
		else {
			document.getElementById("infos0").style.visibility = "visible";
			if (bMusic || iSfx)
				countDownMusic.play();
			document.body.style.cursor = "default";
		}
		iCntStep++;
		setTimeout(fncCount,1000);
	}

	if (iSfx)
		setTimeout(startEngineSound,bMusic ? 2600:1100);
	setTimeout(fncCount,tnCourse-new Date().getTime());
}

function youtube_parser(url) {
	var regExp = /.*(?:youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=)([^#\&\?]*).*/;
	var match = url.match(regExp);
	return (match&&match[1].length==11)? match[1] : false;
}

var oMusicEmbed;


var fSpriteScale = 0;
var fLineScale = 0;

// setup main container
var oContainer = document.createElement("div");
oContainer.tabindex = 1;
oContainer.style.position = "absolute";
oContainer.style.border = "2px solid black";
oContainer.style.left = "10px";
oContainer.style.overflow = "hidden";

document.getElementById("mariokartcontainer").appendChild(oContainer);

// setup screen canvas for render mode 0.
var oScreenCanvas = new Array();

// array for screen strip descriptions
var aStrips = new Array();

var iCamHeight = 24;
var iCamDist = 32;
var iViewHeight = -10;
var iViewDist = 0;
var fFocal = 1 / Math.tan(Math.PI*Math.PI / 360);

function resetScreen() {
	fSpriteScale = iScreenScale / 4;
	fLineScale = 1/iScreenScale * iQuality;

	aStrips = [];

	// change dimensions of main container

		oContainer.style.width = (iWidth*iScreenScale)+"px";
		oContainer.style.height = (iHeight*iScreenScale)+"px";

		oScreenCanvas = document.createElement("canvas");
		oScreenCanvas.style.position = "absolute";
		oContainer.appendChild(oScreenCanvas);
		oScreenCanvas.width=iWidth/fLineScale;
		oScreenCanvas.height=iHeight/fLineScale;
		oScreenCanvas.style.width = (iWidth*iScreenScale+iScreenScale)+"px";
		oScreenCanvas.style.left = (-iScreenScale/2)+"px";
		oScreenCanvas.style.top = iScreenScale+"px";
		oScreenCanvas.style.height = (iHeight*iScreenScale)+"px";

	for (var i=0;i<oBgLayers.length;i++)
		oBgLayers[i].suppr();

	var fLastZ = 0;
		// create horizontal strip descriptions
	for (var iViewY=0;iViewY<iHeight;iViewY+=fLineScale) {
		var iTotalY = iViewY + iViewHeight; // total height of point (on view) from the ground up
		var iDeltaY = iCamHeight - iTotalY; // height of point relative to camera
		var iPointZ = (iTotalY/(iDeltaY / iCamDist)); // distance to point on the map
		var fScaleRatio = fFocal / (fFocal + iPointZ);
		var iStripWidth = Math.floor(iWidth/fScaleRatio);
		if (fScaleRatio > 0 && iStripWidth < iViewCanvasWidth) {
			if (iViewY == 0)
				fLastZ = iPointZ - 1;
			var oCanvas;
			aStrips.push(
				{
					viewy : iViewY,
					mapz : iPointZ,
					scale : fScaleRatio,
					stripwidth : iStripWidth,
					mapzspan : iPointZ - fLastZ
				}
			)
			fLastZ = iPointZ;
		}
	}
	for (var i=0;i<oMap.fond.length;i++)
		oBgLayers[i] = new BGLayer(oMap.fond[i], (oMap.fond.length==2)?1:i+1);
	oViewCanvas = document.createElement("canvas");
	oViewCanvas.width=iViewCanvasWidth;
	oViewCanvas.height=iViewCanvasHeight;
}

function reprendre(debug) {
	setTimeout(function(){if(pause){pause=false;cycle()}}, 67);
	if (debug) {
		unpauseSounds();
		document.getElementById("infos0").style.visibility = "hidden";
	}
}

function quitter() {
	document.location.href = isCup ? (complete ? (isBattle ? 'battle':'map')+'.php?'+(isSingle ? 'i':'cid')+'='+nid:(isBattle ? 'arena':'circuit')+'.php?'+(isSingle ? 'id':'cid')+'='+nid):"index.php";
}


function continuer() {
	document.getElementById("infos0").style.border = 0;
	document.getElementById("infos0").style.top = iScreenScale * 10 + 10 +"px";
	document.getElementById("infos0").style.left = iScreenScale*20+10 +"px";
	document.getElementById("infos0").style.background = "transparent";
	document.getElementById("infos0").style.fontSize = iScreenScale * 4 +"pt";
	document.getElementById("infos0").innerHTML = '<tr><td id="continuer"></td></tr><tr><td style="font-size: '+ iScreenScale * 3 +'px;">&nbsp;</td></tr><tr><td><input type="button" id="quitter" value="'+ toLanguage('QUIT', 'QUITTER') +'" style="font-size: '+ iScreenScale*3 +'pt; width: 100%;" /></td></tr>';

	var oContinue = document.createElement("input");
	oContinue.type = "button";
	oContinue.style.fontSize = iScreenScale*3 + "pt";
	oContinue.style.width = "100%";

	oContinue.value = toLanguage("       NEXT RACE       ", "COURSE SUIVANTE");
	function nextRace() {
		pause = true;
		removeGameMusics();
		oContainer.innerHTML = "";
		document.getElementById("infoPlace0").style.display = "none";
		document.getElementById("compteur0").innerHTML = "";

		document.getElementById("temps0").innerHTML = "";
		document.getElementById("objet0").style.visibility = "hidden";
		fInfos = strPlayer;
		fID = identifiant;
		document.getElementById("infos0").style.visibility = "hidden";
		document.getElementById("infos0").style.opacity = 0.8;
		document.getElementById("infos0").style.color = "#FF9900";
		document.getElementById("mariokartcontainer").style.opacity = 1;
		removePlan();
		oBgLayers.length = 0;
		supprArme(0);
		document.onmousedown = undefined;
		setTimeout(MarioKart, 500);
	}
	var forceClic3 = true;
	oContinue.onclick = function() {
		forceClic3 = false;
		nextRace();
	};
	setTimeout(function(){if(forceClic3)nextRace();},5000);
	document.getElementById("continuer").appendChild(oContinue);
	if (document.activeElement != document.forms[1].elements["rMessage"])
		oContinue.focus();
	document.getElementById("quitter").onclick = quitter;
}


var iViewCanvasHeight = 240;
var iViewCanvasWidth = 600;
var iViewYOffset = 10;
var oViewCanvas;


function Sprite(strSprite) {
	var oCtSprites = new Array();
	var oImg = new Image();
	oImg.style.position = "absolute";
	oImg.style.left = "200px";
	oImg.alt = ".";

	oImg.src = getSpriteSrc(strSprite);

	var oSpriteCtr = document.createElement("div");
	oSpriteCtr.style.width = "32px";
	oSpriteCtr.style.height = "32px";
	oSpriteCtr.style.position = "absolute";
	oSpriteCtr.style.overflow = "hidden";
	oSpriteCtr.style.zIndex = 10000;

	oSpriteCtr.style.display = "none";

	oSpriteCtr.appendChild(oImg);
	oContainer.appendChild(oSpriteCtr);

	this.draw = function(iX, iY, fScale, iZ) {
		if (!iZ)
			iZ = 0;

		if (iY > iHeight * iScreenScale || (iY+iZ*iScreenScale) < 9 * iScreenScale) {
			oCtSprites[0].style.display = "none";
			return;
		}

		oCtSprites[0].style.display = "block";

		var fSpriteSize = Math.round(32 * fSpriteScale * fScale);

		oCtSprites[0].style.left = Math.round(iX - fSpriteSize/2)+"px";
		oCtSprites[0].style.top = Math.round(iY - fSpriteSize/2)+"px";

		oCtSprites[1].style.height = fSpriteSize + "px";

		oCtSprites[0].style.width = fSpriteSize + "px";
		oCtSprites[0].style.height = fSpriteSize + "px";

		oCtSprites[1].style.left = -(fSpriteSize*oCtSprites[2])+"px";
	}

	this.setState = function(iState) {
		oCtSprites[2] = iState;
	}
	this.getState = function() {
		return oCtSprites[2];
	}

	this.div = oSpriteCtr;
	this.img = oImg;
	oCtSprites = [oSpriteCtr, oImg, 0];
	this.suppr = function() {
		oContainer.removeChild(oCtSprites[0]);
	}
}



function BGLayer(strImage, scaleFactor) {
	var imageDims = new Image();
	imageDims.src = "images/map_bg/fond_" + strImage + ".png";
	var oLayer = document.createElement("div");
	oLayer.style.height = (10 * iScreenScale)+"px";
	oLayer.style.width = (iWidth * iScreenScale)+"px";
	oLayer.style.position = "absolute";
	setTimeout(function(){oLayer.style.backgroundImage="url('"+imageDims.src+"')"},500);
	oLayer.style.backgroundSize = "auto 100%";

	oContainer.appendChild(oLayer);

	return {
		draw : function(fRotation, i) {
			if (!imageDims.naturalWidth) return;
			var iRot = fRotation - 360*Math.ceil(fRotation/360);
			var iActualWidth = 10*iScreenScale*imageDims.naturalWidth/imageDims.naturalHeight;

			// one degree of rotation equals x width units:
			var fRotScale = iActualWidth*scaleFactor/360;

			var iScroll = iRot*fRotScale;

			oLayer.style.backgroundPosition = Math.round(iScroll)+"px 0";
		},
		suppr : function() {
			oContainer.removeChild(oLayer);
		}
	}
}

function render() {

	collisionTest = COL_OBJ;

	if (oPlayer.tombe <= 10) {

		var posX = oPlayer.x;
		var posY = oPlayer.y;
		var fRotation = oPlayer.rotation;
		var oViewCtx = oViewCanvas.getContext("2d");
	 
		if (oPlayer.tours == (oMap.tours+1) && oPlayer.changeView < 180)
			oPlayer.changeView += 15;
	 
		if (oPlayer.changeView)
			fRotation += (fRotation < 360-oPlayer.changeView ? oPlayer.changeView : oPlayer.changeView-360);

		oViewCanvas.getContext("2d").fillStyle = "rgb("+ oMap.bgcolor +")";
		oViewCanvas.getContext("2d").fillRect(0,0,oViewCanvas.width,oViewCanvas.height);

		oViewCanvas.getContext("2d").save();
		oViewCanvas.getContext("2d").translate(iViewCanvasWidth/2,iViewCanvasHeight-iViewYOffset);
		oViewCanvas.getContext("2d").rotate((180 + fRotation) * Math.PI / 180);

		oViewCanvas.getContext("2d").drawImage(
			oMapImg,
			-posX,-posY
		);

		oViewCanvas.getContext("2d").restore();

		for (var j=0;j<aStrips.length;j++) {

			var oStrip = aStrips[j];

			try {
				oScreenCanvas.getContext("2d").drawImage(
					oViewCanvas,
					iViewCanvasWidth/2 - (oStrip.stripwidth/2),
					((iViewCanvasHeight-iViewYOffset) - oStrip.mapz)-1,
					oStrip.stripwidth,
					oStrip.mapzspan,

					0,(iHeight-oStrip.viewy)/fLineScale,iWidth/fLineScale,1
				);
			} catch(e) {};

		}

		var iOffsetX = (iWidth/2)*iScreenScale;
		var iOffsetY = (iHeight - iViewYOffset - correctZ(oPlayer.z))*iScreenScale;
		var fSprite;


		for (var j=0;j<aKarts.length;j++) {
			fSprite = aKarts[j];
			if (fSprite.cpu || fSprite != oPlayer) {

				var fCamX = fSprite.x - posX;
				var fCamY = fSprite.y - posY;

				var fRotRad = fRotation * Math.PI / 180;

				var fTransX = fCamX * Math.cos(fRotRad) - fCamY * Math.sin(fRotRad);
				var fTransY = fCamX * Math.sin(fRotRad) + fCamY * Math.cos(fRotRad);

				var iDeltaY = -iCamHeight;
				var iDeltaX = iCamDist + fTransY;

				var iViewY = ((iDeltaY / iDeltaX) * iCamDist + iCamHeight) - iViewHeight + correctZ(fSprite.z);
				var fViewX = -(fTransX / (fTransY + iCamDist)) * iCamDist;

				var fAngle = fRotation - fSprite.rotation;
				while (fAngle < 0)
					fAngle += 360;
				while (fAngle > 360)
					fAngle -= 360;

				var iAngleStep = Math.round(fAngle*11 / 180) + fSprite.tourne % 21;
				if (iAngleStep > 21) iAngleStep -= 22;

				if (fSprite.figstate)
					iAngleStep = (iAngleStep + 21-fSprite.figstate) % 21;

				fSprite.sprite.setState(iAngleStep);

				fSprite.sprite.div.style.zIndex = Math.round(10000 - fTransY);

				fSprite.sprite.draw(
					((iWidth/2) + fViewX) * iScreenScale, 
					(iHeight - iViewY) * iScreenScale,
					fFocal / (fFocal + fTransY) * fSprite.size,
					correctZ(fSprite.z)
				);
				if (course == "BB") {
					var nbBallons = fSprite.ballons.length;
					var fTaille = fFocal / (fFocal + fTransY) * fSprite.size;
					for (k=1;k<=nbBallons;k++)
						fSprite.ballons[k-1].draw(
							((iWidth/2) + fViewX +(k-nbBallons/2)*2.5*fTaille) * iScreenScale, 
							(iHeight - iViewY - 6*fTaille) * iScreenScale,
							fTaille / 2,
							6*fTaille
						);
				}
			}
		}


		for (var j=0;j<oMap.arme.length;j++) {
			fSprite = oMap.arme[j];
			if (isNaN(fSprite[2])) {
				var fCamX = fSprite[0] - posX;
				var fCamY = fSprite[1] - posY;

				var fRotRad = fRotation * Math.PI / 180;

				var fTransX = fCamX * Math.cos(fRotRad) - fCamY * Math.sin(fRotRad);
				var fTransY = fCamX * Math.sin(fRotRad) + fCamY * Math.cos(fRotRad);

				var iDeltaY = -iCamHeight;
				var iDeltaX = iCamDist + fTransY;

				var iViewY = ((iDeltaY / iDeltaX) * iCamDist + iCamHeight) - iViewHeight;
				var fViewX = -(fTransX / (fTransY + iCamDist)) * iCamDist;

				fSprite[2].div.style.zIndex = Math.round(10000 - fTransY);

				fSprite[2].draw(
					((iWidth/2) + fViewX) * iScreenScale, 
					(iHeight - iViewY) * iScreenScale,
					fFocal / (fFocal + (fTransY))
				);
			}
			else {
				if (fSprite[2])fSprite[2]--;
				else fSprite[2] = new Sprite("objet");
			}
		}

		if (oMap.decor) {
			for (var j=1;j<oMap.decor.length;j++) {
				fSprite = oMap.decor[j];
				var fCamX = fSprite[0] - posX;
				var fCamY = fSprite[1] - posY;
				var fCamZ = correctZ(fSprite[3] ? fSprite[3] : 0);

				var fRotRad = fRotation * Math.PI / 180;

				var fTransX = fCamX * Math.cos(fRotRad) - fCamY * Math.sin(fRotRad);
				var fTransY = fCamX * Math.sin(fRotRad) + fCamY * Math.cos(fRotRad);

				var iDeltaY = -iCamHeight;
				var iDeltaX = iCamDist + fTransY;

				var iViewY = ((iDeltaY / iDeltaX) * iCamDist + iCamHeight) - iViewHeight + fCamZ;
				if (fCamZ && (fCamZ*fTransY) > 180 && iViewY > 0 && iViewY < iHeight)
					iViewY = iHeight - 9;
				var fViewX = -(fTransX / (fTransY + iCamDist)) * iCamDist;

				fSprite[2].div.style.zIndex = Math.round(10000 - fTransY);

				fSprite[2].draw(
					((iWidth/2) + fViewX) * iScreenScale, 
					(iHeight - iViewY) * iScreenScale,
					fFocal / (fFocal + (fTransY)) * 1.2
				);
			}
		}


		for (var j=0;j<bananes.length;j++){
			fSprite = bananes[j];
			var fCamX = fSprite[2] - posX;
			var fCamY = fSprite[3] - posY;

			var fRotRad = fRotation * Math.PI / 180;

			var fTransX = fCamX * Math.cos(fRotRad) - fCamY * Math.sin(fRotRad);
			var fTransY = fCamX * Math.sin(fRotRad) + fCamY * Math.cos(fRotRad);

			var iDeltaY = -iCamHeight;
			var iDeltaX = iCamDist + fTransY;

			var iViewY = ((iDeltaY / iDeltaX) * iCamDist + iCamHeight) - iViewHeight;
			var fViewX = -(fTransX / (fTransY + iCamDist)) * iCamDist;

			fSprite[0].div.style.zIndex = Math.round(10000 - fTransY);

			fSprite[0].draw(
				((iWidth/2) + fViewX) * iScreenScale, 
				(iHeight - iViewY - correctZ(fSprite[4])) * iScreenScale,
				fFocal / (fFocal + (fTransY)) / 1.5
			);
		}


		for (var j=0;j<fauxobjets.length;j++) {
			fSprite = fauxobjets[j];
			var fCamX = fSprite[2] - posX;
			var fCamY = fSprite[3] - posY;

			var fRotRad = fRotation * Math.PI / 180;

			var fTransX = fCamX * Math.cos(fRotRad) - fCamY * Math.sin(fRotRad);
			var fTransY = fCamX * Math.sin(fRotRad) + fCamY * Math.cos(fRotRad);

			var iDeltaY = -iCamHeight;
			var iDeltaX = iCamDist + fTransY;

			var iViewY = ((iDeltaY / iDeltaX) * iCamDist + iCamHeight) - iViewHeight;
			var fViewX = -(fTransX / (fTransY + iCamDist)) * iCamDist;

			fSprite[0].div.style.zIndex = Math.round(10000 - fTransY);

			fSprite[0].draw(
				((iWidth/2) + fViewX) * iScreenScale, 
				(iHeight - iViewY - correctZ(fSprite[4])) * iScreenScale,
				fFocal / (fFocal + (fTransY))
			);
		}


		for (var j=0;j<carapaces.length;j++) {
			fSprite = carapaces[j];

			var fNewPosX;
			var fNewPosY;
				
			var fMoveX = 8 * direction(0, fSprite[5]), fMoveY = 8 * direction(1, fSprite[5]);
			
			if (fSprite[5] != -1) {
				fNewPosX = fSprite[2] + fMoveX;
				fNewPosY = fSprite[3] + fMoveY;

				fSprite[0].setState(1-fSprite[0].getState());
			}
			else {
				fNewPosX = fSprite[2];
				fNewPosY = fSprite[3];
			}

			var fCamX = fSprite[2] - posX;
			var fCamY = fSprite[3] - posY;

			var fRotRad = fRotation * Math.PI / 180;

			var fTransX = fCamX * Math.cos(fRotRad) - fCamY * Math.sin(fRotRad);
			var fTransY = fCamX * Math.sin(fRotRad) + fCamY * Math.cos(fRotRad);

			var iDeltaY = -iCamHeight;
			var iDeltaX = iCamDist + fTransY;

			var iViewY = ((iDeltaY / iDeltaX) * iCamDist + iCamHeight) - iViewHeight;
			var fViewX = -(fTransX / (fTransY + iCamDist)) * iCamDist;

			fSprite[0].div.style.zIndex = Math.round(10000 - fTransY);

			fSprite[0].draw(
				((iWidth/2) + fViewX) * iScreenScale, 
				(iHeight - iViewY - correctZ(fSprite[4])) * iScreenScale,
				fFocal / (fFocal + (fTransY)) / 1.5
			);

			var roundX1 = Math.round(fSprite[2]);
			var roundY1 = Math.round(fSprite[3]);
			var roundX2 = Math.round(fNewPosX);
			var roundY2 = Math.round(fNewPosY);

			window.quarantedeux = true;
			if (((fSprite[5] != -1) && tombe(roundX1, roundY1)) || touche_banane(roundX1, roundY1) || touche_banane(roundX2, roundY2) || touche_crouge(roundX1, roundY1) || touche_crouge(roundX2, roundY2) || touche_cverte(roundX1, roundY1, j) || touche_cverte(roundX2, roundY2, j)) {
				detruit(carapaces,j,true);
				j--;
			}

			else if ((fSprite[5] == -1) || canMoveTo(fSprite[2],fSprite[3], fMoveX,fMoveY)) {
				fSprite[2] = fNewPosX;
				fSprite[3] = fNewPosY;
			}
			else {
				fSprite[6]--;
				if (fSprite[6]) {
					var fNewTrajectoire = fSprite[5] - (fSprite[5] - 180)*2;
					fMoveX = 8 * direction(0, fNewTrajectoire);
					fMoveY = 8 * direction(1, fNewTrajectoire);
					if (canMoveTo(fSprite[2],fSprite[3], fMoveX,fMoveY))
						fSprite[5] = fNewTrajectoire;
					else {
						fSprite[5] -= (fSprite[5] - 90)*2;
						if (fSprite[5] < 0) fSprite[5] += 360;
					}
				}
				else {
					detruit(carapaces,j);
					j--;
				}
			}
		}


		for (var j=0;j<carapacesRouge.length;j++) {
			fSprite = carapacesRouge[j];

			var fNewPosX;
			var fNewPosY;

			if (fSprite[5] != -1) {
				var fMoveX;
				var fMoveY;

				fSprite[0].setState(1-fSprite[0].getState());

				var iLocal = oMap.aipoints;
				if (fSprite[7] != -1) {
					fMoveX = iLocal[fSprite[7]][0] - fSprite[2];
					fMoveY = iLocal[fSprite[7]][1] - fSprite[3];
					var oBox = iLocal[fSprite[7]];
					if (fSprite[2] > oBox[0] - 10 && fSprite[2] < oBox[0] + 10 && fSprite[3] > oBox[1] - 10 && fSprite[3] < oBox[1] + 10) {
						if (fSprite[7] < iLocal.length - 1) fSprite[7]++;
						else fSprite[7] = 0;
					}
					var fNewMove = Math.sqrt(fMoveX*fMoveX + fMoveY*fMoveY)/8;
					fMoveX /= fNewMove;
					fMoveY /= fNewMove;
				}
				else {
					if (course != "BB") {
						for (var k=0;k<iLocal.length;k++) {
							var oBox = iLocal[k];
							if (fSprite[2] > oBox[0] - 35 && fSprite[2] < oBox[0] + 35 && fSprite[3] > oBox[1] - 35 && fSprite[3] < oBox[1] + 35) {
								fSprite[7] = k + 1;
								if (fSprite[7] == iLocal.length) fSprite[7] = 0;
								k = iLocal.length;
							}
						}
					}
					fMoveX = 8 * direction(0, fSprite[5]);
					fMoveY = 8 * direction(1, fSprite[5]);
				}

				fNewPosX = fSprite[2] + fMoveX;
				fNewPosY = fSprite[3] + fMoveY;

				var tCible;
				var maxDist = 1000;

				for (var k=0;k<aKarts.length;k++) {
					var pCible = aKarts[k];
					if (pCible.id != fSprite[6] && !pCible.tombe) {
						var fDist = Math.pow(pCible.x-fNewPosX, 2) + Math.pow(pCible.y-fNewPosY, 2);
						if (fDist < maxDist) {
							fNewPosX = pCible.x;
							fNewPosY = pCible.y;
							maxDist = fDist;
							tCible = pCible;
						}
					}
					if (tCible && tCible.using[0] && (tCible.using[0] != fauxobjets)) {
						var rAngle = Math.atan2(fSprite[3]-fNewPosY,fSprite[2]-fNewPosX) - (90-tCible.rotation)*Math.PI/180;
						var pi2 = Math.PI*2;
						while (rAngle < 0)
							rAngle += pi2;
						while (rAngle > pi2)
							rAngle -= pi2;
						if (rAngle > Math.PI)
							rAngle = pi2-rAngle;
						if (Math.abs(rAngle) > 2) {
							detruit(carapacesRouge,j);
							j--;
							fNewPosX -= 5 * direction(0,tCible.rotation);
							fNewPosY -= 5 * direction(1,tCible.rotation);
							detruit(tCible.using[0],tCible.using[1],true);
						}
						else {
							tCible.using[0][tCible.using[1]][2] -= 2 * direction(0,tCible.rotation);
							tCible.using[0][tCible.using[1]][3] -= 2 * direction(1,tCible.rotation);
						}
					}
				}
				fNewPosX = Math.round(fNewPosX);
				fNewPosY = Math.round(fNewPosY);
			}
			else {
				fNewPosX = fSprite[2];
				fNewPosY = fSprite[3];
			}


			if ((fSprite[6] == -1 || (!tombe(fNewPosX, fNewPosY) && canMoveTo(fSprite[2],fSprite[3], fMoveX,fMoveY))) && !touche_banane(fNewPosX, fNewPosY) && !touche_banane(fSprite[2], fSprite[3]) && !touche_crouge(fNewPosX, fNewPosY, j) && !touche_crouge(fSprite[2], fSprite[3], j) && !touche_cverte(fNewPosX, fNewPosY) && !touche_cverte(fSprite[2], fSprite[3])) {
				fSprite[2] = fNewPosX;
				fSprite[3] = fNewPosY;

				var fCamX = fSprite[2] - posX;
				var fCamY = fSprite[3] - posY;

				var fRotRad = fRotation * Math.PI / 180;

				var fTransX = fCamX * Math.cos(fRotRad) - fCamY * Math.sin(fRotRad);
				var fTransY = fCamX * Math.sin(fRotRad) + fCamY * Math.cos(fRotRad);

				var iDeltaY = -iCamHeight;
				var iDeltaX = iCamDist + fTransY;

				var iViewY = ((iDeltaY / iDeltaX) * iCamDist + iCamHeight) - iViewHeight;
				var fViewX = -(fTransX / (fTransY + iCamDist)) * iCamDist;

				fSprite[0].div.style.zIndex = Math.round(10000 - fTransY);

				fSprite[0].draw(
					((iWidth/2) + fViewX) * iScreenScale, 
					(iHeight - iViewY - correctZ(fSprite[4])) * iScreenScale,
					fFocal / (fFocal + (fTransY)) / 1.5
				);
			}
			else {
				detruit(carapacesRouge,j);
				j--;
			}
		}


		for (var j=0;j<carapacesBleue.length;j++) {
			fSprite = carapacesBleue[j];

			var fCamX = fSprite[2] - posX;
			var fCamY = fSprite[3] - posY;

			var fRotRad = fRotation * Math.PI / 180;

			var fTransX = fCamX * Math.cos(fRotRad) - fCamY * Math.sin(fRotRad);
			var fTransY = fCamX * Math.sin(fRotRad) + fCamY * Math.cos(fRotRad);

			var iDeltaY = -iCamHeight;
			var iDeltaX = iCamDist + fTransY;

			var iViewY = ((iDeltaY / iDeltaX) * iCamDist + iCamHeight) - iViewHeight;
			var fViewX = -(fTransX / (fTransY + iCamDist)) * iCamDist;
			
			var cible = -1;
			for (var k=0;k<aKarts.length;k++) {
				if (aKarts[k].id == fSprite[4]) {
					cible = k;
					break;
				}
			}
			if (cible == -1) {
				var cPlace = 1;
				for (k=0;k<aKarts.length;k++) {
					if (aKarts[k].place == cPlace) {
						if (aKarts[k].tours <= oMap.tours) {
							fSprite[4] = aKarts[k].id;
							cible = k;
							k = aKarts.length;
						}
						else {

							cPlace++;
							k = -1;
						}
					}
				}
			}

			var fMoveX = fSprite[2] - aKarts[cible].x;
			var fMoveY = fSprite[3] - aKarts[cible].y;

			var size = 1;
			if (fSprite[5] > 0) {
				if (Math.abs(fMoveX*fMoveY) > 100) {
					var fNewMove = Math.sqrt(Math.pow(fMoveX,2) + Math.pow(fMoveY,2))/10;
					fMoveX /= fNewMove;
					fMoveY /= fNewMove;

					fSprite[0].setState(1-fSprite[0].getState());
				}
				else {
					fSprite[0].setState(Math.round(Math.random()));
					fSprite[5]--;
					if (fSprite[5]) {
						fViewX += fSprite[5] - 2.5;
						iViewY -= Math.abs(5-fSprite[5]);
					}
					else {
						fSprite[0].img.src = "images/sprites/sprite_explosionB_smooth.png";
						playDistSound(aKarts[cible],"musics/events/boom.mp3",200);
						fMoveX *= aKarts[cible].speed/2;
						fMoveY *= aKarts[cible].speed/2;
						fSprite[0].setState(0);
						fSprite[0].div.style.opacity = 1;
					}
				}

				fSprite[2] -= fMoveX;
				fSprite[3] -= fMoveY;
			}
			else {
				if (!bCounting)
					size = 10;
				fSprite[5]--;
				fSprite[0].div.style.opacity = 1+fSprite[5]/10;
				if (fSprite[5] < -10) {
					detruit(carapacesBleue,j);
					size = false;
					j--;
				}
			}

			if (size) {

				fSprite[0].div.style.zIndex = Math.round(10000 - fTransY);

				fSprite[0].draw(
					((iWidth/2) + fViewX) * iScreenScale, 
					(iHeight - iViewY - (fSprite[5] > 0 ? 15 + aKarts[cible].speed : 0)) * iScreenScale,
					fFocal / (fFocal + (fTransY)) * size
				);
			}
		}


		for (var j=0;j<bobombs.length;j++) {
			fSprite = bobombs[j];

			var fCamX = fSprite[2] - posX;
			var fCamY = fSprite[3] - posY;

			var fRotRad = fRotation * Math.PI / 180;

			var fTransX = fCamX * Math.cos(fRotRad) - fCamY * Math.sin(fRotRad);
			var fTransY = fCamX * Math.sin(fRotRad) + fCamY * Math.cos(fRotRad);

			var iDeltaY = -iCamHeight;
			var iDeltaX = iCamDist + fTransY;

			var iViewY = ((iDeltaY / iDeltaX) * iCamDist + iCamHeight) - iViewHeight;
			var fViewX = -(fTransX / (fTransY + iCamDist)) * iCamDist;

			var size = 1;
			var hauteur = 0;

			if (fSprite[5] != -1) {
				if (fSprite[6]) {
					fSprite[6]--;
					var fMoveX = 15 * direction(0, fSprite[5]);
					var fMoveY = 15 * direction(1, fSprite[5]);

					var fNewPosX = fSprite[2] + fMoveX;
					var fNewPosY = fSprite[3] + fMoveY;

					fSprite[2] = fNewPosX;
					fSprite[3] = fNewPosY;
					hauteur = fSprite[6];
				}
				else {
					if (tombe(Math.round(fSprite[2]), Math.round(fSprite[3]))) {
						detruit(bobombs, j);
						size = false;
						j--;
					}
					fSprite[7]--;
					if (!fSprite[7]) {
						fSprite[0].img.src = "images/sprites/sprite_explosion_smooth.png";
						playDistSound({x:fSprite[2],y:fSprite[3]},"musics/events/boom.mp3",200);
						fSprite[0].div.style.opacity = 1;
					}
					if (fSprite[7] <= 0) {
						if (!bCounting)
							size = 10;
						fSprite[0].div.style.opacity = 1+fSprite[7]/10;
						if (fSprite[7] < -10) {
							detruit(bobombs,j);
							size = false;
							j--;
						}
					}
				}
			}
			if (size) {
				fSprite[0].div.style.zIndex = Math.round(10000 - fTransY);

				var spriteZ = correctZ(fSprite[4] + (- Math.abs(hauteur-8) + 8) * 2);

				fSprite[0].draw(
					((iWidth/2) + fViewX) * iScreenScale, 
					(iHeight - iViewY - spriteZ) * iScreenScale,
					fFocal / (fFocal + (fTransY)) * size,
					spriteZ
				);
			}
		}


		oPlayer.sprite.div.style.zIndex = 10000;
		oPlayer.sprite.draw(iOffsetX,iOffsetY,oPlayer.size,correctZ(oPlayer.z));
		if (course == "BB") {
			var nbBallons = oPlayer.ballons.length;
			for (j=0;j<nbBallons;j++)
				oPlayer.ballons[j].draw(
					(iOffsetX+(2*oPlayer.size+(j-nbBallons/2)*2.5*oPlayer.size)*iScreenScale), 
					(iOffsetY-(2+oPlayer.size*4)*iScreenScale),
					oPlayer.size / 2,
					6*oPlayer.size
				);
		}

		for (var j=0;j<aKarts.length;j++) {
			var oKart = aKarts[j];
			var oSprite = oKart.sprite;
			if (oKart.figstate > 0 && oKart.figuring) {
				if (!oSprite.div.hallowed) {
					oSprite.div.hallowed = true;
					oSprite.div.style.backgroundImage = "url('images/halo.png')";
					oSprite.div.style.backgroundRepeat = "no-repeat";
					oSprite.div.style.backgroundSize = "contain";
					oSprite.img.style.opacity = 0.7;
				}
			}
			else if (oSprite.div.hallowed) {
				oSprite.div.hallowed = false;
				oSprite.div.style.backgroundImage = "";
				oSprite.div.style.backgroundRepeat = "";
				oSprite.div.style.backgroundSize = "";
				oSprite.img.style.opacity = 1;
			}
		}

		for (var j=0;j<oBgLayers.length;j++)
			oBgLayers[j].draw(fRotation);

		setPlanPos();
	}
}

function correctZ(z) {
	return 2*Math.pow(z/2,0.7);
}

function direction(fDir, rotation) {
	return Math[["sin","cos"][fDir]](rotation * Math.PI / 180)
}
function randObj(oKart) {
	return objets[Math.floor(Math.random()*120/aKarts.length+(oKart.place-1)*120/aKarts.length)];
}

function detruit(cible, id, sound) {
	if (cible[id]) {
		var gIndex = [bananes,fauxobjets,carapaces,carapacesRouge,carapacesBleue,bobombs].indexOf(cible);
		for (var i=0;i<nbNews[gIndex].length;i++) {
			if (nbNews[gIndex][i] > id)
				nbNews[gIndex][i]--;
			else if (nbNews[gIndex][i] == id) {
				nbNews[gIndex].splice(i, 1);
				i--;
			}
		}
		destructions[gIndex].push(cible[id][1]);
		supprime(cible, id, sound);
	}
}
function supprime(cible, id, sound) {
	cible[id][0].suppr();
	for (i=0;i<aKarts.length;i++) {
		var oUsing = aKarts[i].using;
		if (oUsing[0] == cible && id <= oUsing[1]) {
			if (id != oUsing[1]) oUsing[1]--;
			else {
				aKarts[i].using = [false];
				if (sound) playIfShould(aKarts[i],"musics/events/hit.mp3");
			}
		}
	}
	cible.splice(id,1);
}


function supprArme(i) {
	var oKart = aKarts[i];
	oKart.arme = false;
	oKart.roulette = 0;
	if (!i) {
		document.getElementById("roulette0").innerHTML = "";
		document.getElementById("scroller0").style.visibility = "hidden";
		removeIfExists(oKart.rouletteSound);
	}
}

function stopDrifting(i) {
	if (!i) {
		var oKart = aKarts[i];
		aKarts[i].driftinc = 0;
		aKarts[i].driftcpt = 0;
		aKarts[i].turbodrift = 0;
		getDriftImg(i).src = "images/drift.png";
		document.getElementById("drift"+ i).style.display = "none";
		if (oKart.driftSound) {
			oKart.driftSound.pause();
			oKart.driftSound = undefined;
		}
		if (oKart.sparkSound) {
			oKart.sparkSound.pause();
			oKart.sparkSound = undefined;
		}
	}
}

function pCol(oKart) {
	if (oKart != oPlayer && (course!="BB"||(oKart.ballons.length&&oPlayer.ballons.length)) && Math.pow(oKart.x-oPlayer.x, 2) + Math.pow(oKart.y-oPlayer.y, 2) < 25 && Math.abs(oKart.z - oPlayer.z) < 2 && !oKart.tourne && Math.abs(oPlayer.speed - oKart.speed) < 2) {
		var oVictim = oPlayer.speed < oKart.speed ? oKart : oPlayer;
		oVictim.speed = 2;
		if (!oVictim.colSound) {
			oVictim.colSound = playIfShould(oVictim, "musics/events/colkart.mp3");
			if (oVictim.colSound) {
				oVictim.colSound.onended = function() {
					oVictim.colSound = undefined;
					document.body.removeChild(this);
				}
			}
		}
	}
}




function canMoveTo(iX,iY, iI,iJ, iP) {

	var nX = iX+iI, nY = iY+iJ;

	if (oMap.decor) {
		for (var i=1;i<oMap.decor.length;i++) {
			var oBox = oMap.decor[i];
			if (nX > oBox[0]-5 && nX < oBox[0]+5 && nY > oBox[1]-5 && nY < oBox[1]+5 && (!oBox[3]||oBox[3]<4)) {
				if (!iP)
					return false;
				else {
					oMap.decor[i][2].suppr();
					oMap.decor.splice(i,1);
					return true;
				}
			}
		}
	}

	if (!oMap.collision) return true;

	if (!isCup) {
		if ((course == "BB") || (oMap.map <= 20)) {
			if (iX > (oMap.w-5) || iY > (oMap.h-5) || iX < 4 || iY < 4) return true;
		}
		else {
			if (iX >= oMap.w || iY >= oMap.h || iX < 0 || iY < 0) return true;
		}
	}

	for (var i=0;i<oMap.collision.length;i++) {
		var oBox = oMap.collision[i];
		if (iX > oBox[0] && iX < oBox[0]+oBox[2] && iY > oBox[1] && iY < oBox[1]+oBox[3])
			return true;
	}
	
	if (!isCup) {
		if ((course == "BB") || (oMap.map <= 20)) {
			if (nX > (oMap.w-5) || nY > (oMap.h-5) || nX < 4 || nY < 4) return false;
		}
		else {
			if (nX >= oMap.w || nY >= oMap.h || nX < 0 || nY < 0) return false;
		}
	}
	
	var aPos = [iX, iY], aMove = [iI, iJ];
	var dir = [(iI>0), (iJ>0)];

	for (var i=0;i<oMap.collision.length;i++) {
		var oBox = oMap.collision[i];
		for (var j=0;j<2;j++) {
			var l = dir[j];
			if ((l ? ((aPos[j] <= oBox[j])&&((aPos[j]+aMove[j]) >= oBox[j])):((aPos[j] >= (oBox[j]+oBox[j+2]))&&((aPos[j]+aMove[j]) <= (oBox[j]+oBox[j+2]))))) {
				var dim = 1-j;
				var croiseJ = aPos[dim] + ((l?oBox[j]:oBox[j]+oBox[j+2])-aPos[j])*aMove[dim]/aMove[j];
				if ((croiseJ >= oBox[dim]) && (croiseJ <= (oBox[dim]+oBox[dim+2])))
					return false;
			}
		}
	}
	return true;
}

function objet(iX, iY) {
	for (var i=0;i<oMap.arme.length;i++) {
		var oBox = oMap.arme[i];
		if (iX > oBox[0] - 7 && iX < oBox[0] + 7 && iY > oBox[1] - 7 && iY < oBox[1] + 7 && isNaN(oBox[2])) {
			oBox[2].div.style.display = "none";
			oBox[2] = 20;
			return true;
		}
	}
	return false;
}

function res_sauts(oBox) {
	return isCup ? (oBox[2]+oBox[3])/45+1 : oMap.sauts[0];
}
function sauts(iX, iY, iI, iJ) {
	if (!oMap.sauts)
		return false;
	var aPos = [iX, iY], aMove = [iI, iJ];
	var dir = [(iI>0), (iJ>0)];
	for (var i=isCup?0:1;i<oMap.sauts.length;i++) {
		var oBox = oMap.sauts[i];
		if (iX > oBox[0] && iX < oBox[0]+oBox[2] && iY > oBox[1] && iY < oBox[1]+oBox[3])
			return res_sauts(oBox);
		for (var j=0;j<2;j++) {
			var l = dir[j];
			if ((l ? ((aPos[j] <= oBox[j])&&((aPos[j]+aMove[j]) >= oBox[j])):((aPos[j] >= (oBox[j]+oBox[j+2]))&&((aPos[j]+aMove[j]) <= (oBox[j]+oBox[j+2]))))) {
				var dim = 1-j;
				var croiseJ = aPos[dim] + ((l?oBox[j]:oBox[j]+oBox[j+2])-aPos[j])*aMove[dim]/aMove[j];
				if ((croiseJ >= oBox[dim]) && (croiseJ <= (oBox[dim]+oBox[dim+2])))
					return res_sauts(oBox);
			}
		}
	}
	return false;
}

function ralenti(iX, iY) {
	for (var type in oMap.horspistes) {
		var hp = oMap.horspistes[type];
		for (var i=0;i<hp.length;i++) {
			var oBox = hp[i];
			if (iX > oBox[0] && iX < oBox[0] + oBox[2] && iY > oBox[1] && iY < oBox[1] + oBox[3])
				return type;
		}
	}
	return false;
}

function accelere(iX, iY) {
	if (!oMap.accelerateurs) return false;
	for (var i=0;i<oMap.accelerateurs.length;i++) {
		var oBox = oMap.accelerateurs[i];
		if (iX > oBox[0] && iX < oBox[0] + 9 && iY > oBox[1] && iY < oBox[1] + 9)
			return true;
		}
	return false;
}

function tombe(iX, iY, iC) {
	if (iX > oMap.w || iY > oMap.h || iX < 0 || iY < 0)
		return (course=="BB") ? true:[oMap.startposition[0],oMap.startposition[1], simplified?oMap.startposition[2]:2];

	if (!oMap.trous) return false;

	var fTrou;
	for (var j=0;j<4;j++) {
		for (var i=0;i<oMap.trous[j].length;i++) {
			var oBox = oMap.trous[j][i];
			if (iX > oBox[0] && iX < oBox[0] + oBox[2] && iY > oBox[1] && iY < oBox[1] + oBox[3]) {
				if (iC == undefined)
					return true;
				fTrou = [oBox[4],oBox[5],j];
				if (j%2 - iC)
					return fTrou;
			}
		}
	}
	if (!fTrou)
		return false;
	else
		return fTrou;
}

var COL_KART = 0, COL_OBJ = 1;
var collisionTest;
function touche_banane(iX, iY, iP) {
	for (var i=0;i<bananes.length;i++) {
		var oBox = bananes[i];
		if (i != iP && !oBox[4]) {
			if (iX > oBox[2]-4 && iX < oBox[2]+4 && iY > oBox[3]-4 && iY < oBox[3] + 4) {
				detruit(bananes,i,(collisionTest==COL_OBJ));
				return true;
			}
		}
	}
	return false;
}

function touche_fauxobjet(iX, iY, iP) {
	for (var i=0;i<fauxobjets.length;i++) {
		var oBox = fauxobjets[i];
		if (i != iP && !oBox[4]) {
			if (iX > oBox[2]-4 && iX < oBox[2]+4 && iY > oBox[3]-4 && iY < oBox[3] + 4) {
				detruit(fauxobjets,i,(collisionTest==COL_OBJ));
				return true;
			}
		}
	}
	return false;
}

function touche_cverte(iX, iY, iP) {
	for (var i=0;i<carapaces.length;i++) {
		var oBox = carapaces[i];
		if (i != iP && !oBox[4]) {
			if (iX > oBox[2]-5 && iX < oBox[2]+5 && iY > oBox[3]-5 && iY < oBox[3] + 5) {
				detruit(carapaces,i,(collisionTest==COL_OBJ));
				return true;
			}
		}
	}
	return false;
}

function touche_crouge(iX, iY, iP) {
	for (var i=0;i<carapacesRouge.length;i++) {
		var oBox = carapacesRouge[i];
		if (i != iP && !oBox[4]) {
			if (oBox[6] != -1 && iX == oBox[2] && iY == oBox[3]) {
				detruit(carapacesRouge,i,(collisionTest==COL_OBJ));
				return true;
			}
			else if (oBox[6] == -1 && iX > oBox[2]-5 && iX < oBox[2]+5 && iY > oBox[3]-5 && iY < oBox[3] + 5) {
				detruit(carapacesRouge,i,(collisionTest==COL_OBJ));
				return true;
			}
		}
	}
	return false;
}
function touche_bobomb(iX, iY, iP) {
	for (var i=0;i<bobombs.length;i++) {
		var oBox = bobombs[i];
		if (!oBox[4] && i != iP) {
			if (oBox[5] != -1) {
				if (!oBox[6] && iX > oBox[2]-30 && iX < oBox[2]+30 && iY > oBox[3]-30 && iY < oBox[3]+30) {
					if (oBox[7] <= 0)
						return (oBox[7] < -5 ? 42 : 84);
					else
						oBox[7] = 1;
				}
			}
			else {
				if (iX > oBox[2]-5 && iX < oBox[2]+5 && iY > oBox[3]-5 && iY < oBox[3] + 5) {
					for (j=0;j<aKarts.length;j++) {
						if (i==aKarts[j].using[1]) {
							aKarts[j].using=[false];
							j=aKarts.length;
						}
					}
					bobombs.push([new Sprite("bob-omb"), -1, oBox[2], oBox[3], oBox[4],1,0,1]);
					detruit(bobombs, i);
					i--;
				}
			}
		}
	}
	return false;
}

function touche_cbleue(iX, iY) {
	for (var i=0;i<carapacesBleue.length;i++) {
		var oBox = carapacesBleue[i];
		if (oBox[5] < 0) {
			if (iX > oBox[2]-30 && iX < oBox[2]+30 && iY > oBox[3]-30 && iY < oBox[3]+30)
				return (oBox[5] < -5 ? 42 : 84);
		}
	}
	return false;
}

function colKart(oKart) {
	for (var i=0;i<aKarts.length;i++) {
		var kart = aKarts[i];
		if (kart != oKart && Math.pow(kart.x-oKart.x, 2) + Math.pow(kart.y-oKart.y, 2) < 25 && !kart.tourne && !kart.loose && (!kart.protect || (kart.megachampi && !oKart.megachampi))) {
			loseBall(i);
			stopDrifting(i);
			spinKart(kart,62);
			if (kart.using[0]) {
				if (kart.using[0][kart.using[1]][4])
					kart.using[0][kart.using[1]][4] = 0;
				kart.using = [false];
			}
			supprArme(i);
		}
	}
}
function spinKart(oKart,nb) {
	if (!oKart.tourne)
		playIfShould(oKart,"musics/events/spin.mp3");
	oKart.tourne = nb;
}
if (!Math.hypot) Math.hypot = function(x,y){return Math.sqrt(x*x+y*y)};
function distKart(obj) {
	if (finishing || oPlayer.cpu)
		return Infinity;
	return Math.hypot(obj.x-oPlayer.x, obj.y-oPlayer.y);
}
function stuntKart(oKart) {
	oKart.figstate = 21;
	oKart.z += 1;
	oKart.heightinc += 0.5;
	playIfShould(oKart, "musics/events/stunt.mp3");
}

function places(j) {
	var oKart = aKarts[j];
	if (oPlayer.cpu) return;
	var place = 1;
	if (course != "BB") {
		if (oKart.tours > oMap.tours) return;
		var dest = oKart.demitours+1;
		if (dest >= oMap.checkpoint.length) dest = 0;
		var iLine = oMap.checkpoint[dest][3];
		var score = oKart.tours*oMap.checkpoint.length + getCpScore(oKart) - Math.abs(oKart[(iLine ? "y" : "x")]-oMap.checkpoint[dest][iLine]) / 1000;
		for (var i=0;i<aKarts.length;i++) {
			var kart = aKarts[i];
			dest = kart.demitours+1;
			if (dest >= oMap.checkpoint.length) dest = 0;
			iLine = oMap.checkpoint[dest][3];
			if (kart != oKart && kart.tours*oMap.checkpoint.length + getCpScore(kart) - Math.abs(kart[(iLine ? "y" : "x")]-oMap.checkpoint[dest][iLine]) / 1000 > score)
			place++;
		}
	}
	else {
		for (i=0;i<aKarts.length;i++) {
			var score1 = oKart.ballons.length ? oKart.ballons.length+oKart.reserve : 0;
			var score2 = aKarts[i].ballons.length ? aKarts[i].ballons.length+aKarts[i].reserve : 0;
			if ((aKarts[i] != oKart) && (score1 < score2) || ((score1 == score2) && (oKart.initialPlace > aKarts[i].initialPlace)))
				place++;
		}
	}
	if (!oKart.loose)
		oKart.place = place;
	if (!j)
		document.getElementById("infoPlace"+j).innerHTML = toPlace(place);
}

function getLastCp(kart) {
	if (oMap.sections) {
		if ((kart.tours > 1) && (kart.tours <= oMap.tours))
			return oMap.sections[kart.tours-2];
		return oMap.checkpoint.length-1;
	}
	return 0;
}
function getNextCp(kart) {
	if (oMap.sections) {
		if (kart.tours <= oMap.sections.length)
			return oMap.sections[kart.tours-1];
		return oMap.checkpoint.length-1;
	}
	return 0;
}
function getCpDiff(kart) {
	var lastCp = getLastCp(kart), nextCp = getNextCp(kart);
	var res = nextCp-lastCp;
	if (res <= 0)
		res += oMap.checkpoint.length;
	return res;
}
function getCpScore(kart) {
	var lastCp = getLastCp(kart), currentCp = kart.demitours;
	var res = currentCp-lastCp;
	if (res < 0)
		res += oMap.checkpoint.length;
	return res;
}
function checkpoint(kart) {
	var demitour = kart.demitours;
	if (!simplified) {
		var iCP = getNextCp(kart);
		var jCP = (iCP?iCP:oMap.checkpoint.length)-1;
	}
	for (var i=0;i<oMap.checkpoint.length;i++) {
		var oBox = oMap.checkpoint[i];
		if (kart.x > oBox[0] && kart.x < oBox[0] + (oBox[3] ? oBox[2] : 15) && kart.y > oBox[1] && kart.y < oBox[1] + (oBox[3] ? 15 : oBox[2])) {
			if (simplified) {
				if (i==0 && (oMap.checkpoint.length-demitour) < 5)
					return true;
				else if (demitour == i-1 || (demitour && Math.abs(demitour-i) < 5)) {
					kart.demitours = i;
					return false;
				}
			}
			else {
				if (i==iCP && demitour==jCP)
					return true;
				else if (demitour == i-1 || demitour == i+1) {
					kart.demitours = i;
					return false;
				}
				else if (i==0 && demitour == oMap.checkpoint.length-1) {
					kart.demitours = i;
					return false;
				}
			}
		}
	}
	return false;
}

function resetDatas() {
	var params = (course != "BB")
	 ? ["x","y","z","speed","speedinc","heightinc","rotation","rotincdir","rotinc","size","tourne","tombe","tours","demitours","champi","etoile","megachampi","billball","eclair","place"]
	 : ["x","y","z","speed","speedinc","heightinc","rotation","rotincdir","rotinc","size","tourne","tombe","ballons","reserve","champi","etoile","megachampi"];
	var paramsExcept = ["demitours","ballons"];
	var eParams = {};
	for (var i=0;i<paramsExcept.length;i++)
		eParams[paramsExcept[i]] = true;
	var uSend = "";
	for (var i=0;i<params.length;i++) {
		if (!eParams[params[i]])
			uSend += params[i]+"="+oPlayer[params[i]]+"&";
	}
	if (course == "BB")
		uSend += "ballons="+oPlayer.ballons.length +"&battle=1&";
	else {
		uSend += "demitours="+getCpScore(oPlayer)+"&";
		if (oMap.tours != 3)
			uSend += "laps="+oMap.tours+"&";
	}
	var iObjets = [bananes, fauxobjets, carapaces, carapacesRouge, carapacesBleue, bobombs];
	if (oPlayer.using[0])
		uSend += "i="+ iObjets.indexOf(oPlayer.using[0]) +"&j="+ oPlayer.using[0][oPlayer.using[1]][1] +"&";
	var alpha = "abcdef";
	var idObjets = new Array();
	for (i=0;i<iObjets.length;i++) {
		idObjets[i] = new Array();
		for (var j=0;j<iObjets[i].length;j++)
			idObjets[i][j] = iObjets[i][j][1];
	}
	nbNews = new Array();
	for (i=0;i<iObjets.length;i++) {
		var iObjet = iObjets[i];

		var lettre = alpha.charAt(i);
		nbNews[i] = new Array();
		for (j=0;j<iObjet.length;j++) {
			var cObjet = iObjet[j];
			for (var k=1;k<cObjet.length;k++)
				uSend += lettre+j+"_"+(k-1)+"="+cObjet[k]+"&";
			if (cObjet[1] == -1)
				nbNews[i].push(j);
		}
	}
	for (i=0;i<destructions.length;i++) {
		var lettre = alpha.charAt(i);
		for (j=0;j<destructions[i].length;j++)
			uSend += lettre+j+"="+destructions[i][j]+"&";
		destructions[i] = new Array();
	}
	xhr("reload.php", uSend, function(reponse) {
		refreshDatas = true;
		if (reponse) {
			if (reponse != -1) {
				try {
					var rCode = eval(reponse);
					var aCodes = rCode[1];
					for (i=0;i<aCodes.length;i++) {
						for (j=0;j<nbNews[i].length;j++)
							iObjets[i][nbNews[i]][1] = idObjets[i][nbNews[i]] = aCodes[i][aCodes[i].length-nbNews[i].length+j][0];
					}
					var strSprites = ["banane", "objet", "carapace", "carapace-rouge", "carapace-bleue", "bob-omb"];
					for (i=0;i<aCodes.length;i++) {
						for (j=0;j<aCodes[i].length;j++) {
							var aID = aCodes[i][j][0];
							var inArray = true;
							for (var k=0;k<idObjets[i].length;k++) {
								if (idObjets[i][k] == aID) {
									inArray = false;
									break;
								}
							}
							if (inArray) {
								var toAdd = [new Sprite(strSprites[i]),aID];
								for (k=1;k<aCodes[i][j].length;k++)
									toAdd.push(aCodes[i][j][k]);
								iObjets[i].push(toAdd);
							}
						}
					}
					for (i=0;i<iObjets.length;i++) {
						for (j=0;j<iObjets[i].length;j++) {
							var oID = iObjets[i][j][1];
							if (oID != -1) {
								var inArray = true;
								for (k=0;k<aCodes[i].length;k++) {
									if (aCodes[i][k][0] == oID) {
										inArray = false;
										break;
									}
								}
								if (inArray) {
									supprime(iObjets[i], j);
									j--;
								}
							}
						}
					}
					var jCodes = rCode[0];
					for (i=0;i<jCodes.length;i++) {
						var jCode = jCodes[i];
						if (jCode[0][1] >= connecte) {
							var pID = jCode[0][0];
							for (j=0;j<aKarts.length;j++) {
								if (aKarts[j].id == pID) {
									var pCode = jCode[1];
									var aEtoile = aKarts[j].etoile, aBillBall = aKarts[j].billball, aEclair = aKarts[j].eclair, aTombe = aKarts[j].tombe, aTourne = aKarts[j].tourne;
									var extraParams = {};
									for (k=0;k<params.length;k++) {
										if (!eParams[params[k]])
											aKarts[j][params[k]] = pCode[k];
										else
											extraParams[params[k]] = pCode[k];
									}
									if (course == "BB") {
										while (aKarts[j].ballons.length < extraParams.ballons) {
											if (!aKarts[j].ballons.length) {
												aKarts[j].sprite.div.style.opacity = 1;
												aKarts[j].sprite.img.style.display = "";
												oPlanCharacters[j].style.display = "block";
												oPlanCharacters2[j].style.display = "block";
												aKarts[j].loose = false;
											}
											aKarts[j].ballons.push(new Sprite("ballon"));
										}
										while (aKarts[j].ballons.length > extraParams.ballons) {
											var lg = aKarts[j].ballons.length-1;
											aKarts[j].ballons[lg].suppr();
											aKarts[j].ballons.pop();
										}
									}
									else
										aKarts[j].demitours = (getLastCp(aKarts[j])+extraParams.demitours)%oMap.checkpoint.length;
									if ((aKarts[j].billball >= 40) && !aBillBall) {
										aKarts[j].sprite.img.src = "images/sprites/sprite_billball_smooth.png";
										aKarts[j].aipoint = undefined;
									}
									else if ((aKarts[j].etoile >= 50) && !aEtoile)
										aKarts[j].sprite.img.src = getStarSrc(aKarts[j].personnage);
									else if ((aEtoile && !aKarts[j].etoile) || (aBillBall && !aKarts[j].billball))
										aKarts[j].sprite.img.src = getSpriteSrc(aKarts[j].personnage);
									if ((aKarts[j].eclair >= 90) && !aEclair) {
										for (k=0;k<aKarts.length;k++) {
											var kart = aKarts[k];
											if (kart != aKarts[j]) {
												if (!kart.protect) {
													kart.size = 0.6;
													updateDriftSize(k);
													kart.arme = false;
													if (kart.using[0]) {
														if (kart.using[0][kart.using[1]][4])
															kart.using[0][kart.using[1]][4] = 0;
														kart.using = [false];
													}
													kart.champi = 0;
													spinKart(kart,20);
													kart.roulette = 0;
													stopDrifting(k);
													supprArme(k);
												}
												else
													kart.megachampi = (kart.megachampi<8 || kart.etoile ? kart.megachampi : 8);
											}
										}
										document.getElementById("mariokartcontainer").style.opacity = 0.7;
										if (iSfx && !finishing && !oPlayer.cpu)
											playSoundEffect("musics/events/lightning.mp3");
									}
									else if (aEclair && !aKarts[j].eclair && (oPlayer.size < 1)) {
										oPlayer.size = 1;
										updateDriftSize(j);
									}
									aKarts[j].protect = (aKarts[j].etoile || aKarts[j].megachampi || aKarts[j].billball);
									if (aTombe && !aKarts[j].tombe)
										aKarts[j].sprite.img.style.display = "block";
									if (!aKarts[j].turnSound && aKarts[j].tourne)
										aKarts[j].turnSound = playDistSound(aKarts[j],"musics/events/spin.mp3",(course=="BB")?80:50);
									if (aKarts[j].turnSound && !aKarts[j].tourne)
										aKarts[j].turnSound = undefined;
									var uID = jCode[0][3];
									if (uID == -1)
										aKarts[j].using = [false];
									else {
										aKarts[j].using = [false];
										var iObjet = iObjets[jCode[0][2]];
										for (k=0;k<iObjet.length;k++) {
											if (uID == iObjet[k][1]) {
												aKarts[j].using = [iObjets[jCode[0][2]], k];
												break;
											}
										}
									}
									for (k=jCode[0][1];k<rCode[2];k++)
										move(j);
									break;
								}
							}
						}
					}
					connecte = rCode[2];
					if (rCode[3]) {
						refreshDatas = false;
						function displayRankings() {
							if (course == "BB") {
								oPlayer.arme = false;
								oPlayer.speed = 0;
								oPlayer.speedinc = 0;
								oPlayer.rotinc = 0;
								oPlayer.sprite.setState(0);
								stopDrifting(0);
								supprArme(0);
							}
							var infos0 = document.getElementById("infos0");
							infos0.innerHTML = "";
							infos0.style.border = "solid 1px black";
							infos0.style.opacity = 0.6;
							infos0.style.fontSize = Math.round(iScreenScale*1.5+4) +"pt";
							infos0.style.top = iScreenScale * 3 +"px";
							infos0.style.left = Math.round(iScreenScale*25+10) +"px";
							infos0.style.backgroundColor = "blue";
							infos0.style.color = "yellow";
							var oTrs = new Array();
							var oTds = new Array();
							for (i=0;i<rCode[3].length;i++) {
								var pCode = rCode[3][i];
								var oTr = document.createElement("tr");
								oTds[i] = new Array();
								if (pCode[0] == identifiant) {
									oTr.style.backgroundColor = "#69F";
									document.getElementById("infoPlace0").innerHTML = toPlace(i+1);
									document.getElementById("infoPlace0").style.visibility = "visible";
								}
								var oTd = document.createElement("td");
								oTd.innerHTML = i+1;
								var oSup = document.createElement("sup");
								oSup.innerHTML = "e"+ (i ? "":"r");
								oTds[i][0] = document.createElement("td");
								oTds[i][0].innerHTML = pCode[1];
								oTds[i][1] = document.createElement("td");
								oTds[i][1].innerHTML = pCode[2];
								var oSmall = document.createElement("small");
								oSmall.innerHTML = ((pCode[3]<0) ? "":"+") + pCode[3];
								oTd.appendChild(oSup);
								oTr.appendChild(oTd);
								oTr.appendChild(oTds[i][0]);
								oTds[i][1].appendChild(oSmall);
								oTr.appendChild(oTds[i][1]);
								infos0.appendChild(oTr);
							}
							var oTr = document.createElement("tr");
							var oTd = document.createElement("td");
							oTd.setAttribute("colspan", 3);
							var oContinue = document.createElement("input");
							oContinue.type = "button";
							oContinue.value = toLanguage("CONTINUE", "CONTINUER");
							oContinue.style.width = "100%";
							oContinue.style.height = "100%";
							oContinue.style.fontSize = iScreenScale*3 +"pt";
							var forceClic = true;
							function updateTable() {
								infos0.style.visibility = "hidden";
								for (var i=0;i<rCode[3].length;i++)
									rCode[3][i][2] += rCode[3][i][3];
								var fin = rCode[3].length-1;
								for (i=0;i<fin;i++) {
									var max = 0;
									var mID = 0;
									for (var j=i;j<rCode[3].length;j++) {
										if (rCode[3][j][2] >= max) {
											max = rCode[3][j][2];
											mID = j;
										}
									}
									var aNom = rCode[3][i][1], aPts = rCode[3][i][2];
									rCode[3][i][1] = rCode[3][mID][1];
									rCode[3][i][2] = rCode[3][mID][2];
									rCode[3][mID][1] = aNom;
									rCode[3][mID][2] = aPts;
								}
								for (i=0;i<oTds.length;i++) {
									oTds[i][0].innerHTML = toPerso(rCode[3][i][1]);
									oTds[i][1].innerHTML = rCode[3][i][2];
								}
								var forceClic2 = true;
								setTimeout(function(){infos0.style.visibility="visible";if(document.activeElement!=document.forms[1].elements["rMessage"])oContinue.focus()}, 500);

								setTimeout(function(){if(forceClic2)continuer();}, 5000);
								oContinue.onclick = function() {
									forceClic2 = false;
									continuer();
								};
							}
							oContinue.onclick = function() {
								forceClic = false;
								updateTable();
							};
							setTimeout(function(){if(forceClic)updateTable();}, 5000);
							
							oTd.appendChild(oContinue);
							oTr.appendChild(oTd);
							infos0.appendChild(oTr);
							infos0.style.visibility = "visible";
							if (document.activeElement != document.forms[1].elements["rMessage"])
								oContinue.focus();
							document.onkeydown = undefined;
							document.onkeyup = undefined;
							document.onmousedown = undefined;
							window.onbeforeunload = undefined;
							supprArme(0);
							if (bMusic||iSfx)
								startEndMusic(0);
							finishing = true;
							document.getElementById("sRest").innerHTML = rCode[4]-(course=="BB"?6:5);
							document.getElementById("wait").style.visibility = "visible";
							dRest();
							document.getElementById("compteur0").innerHTML = "";
							document.getElementById("roulette0").innerHTML = "";
							document.getElementById("scroller0").style.visibility = "hidden";
						}
						if (course == "BB") {
							var firstID = rCode[3][0][0];
							for (var i=0;i<aKarts.length;i++) {
								var oKart = aKarts[i];
								if (oKart.id != firstID) {
									if (oKart.ballons.length && !oKart.tourne) {
										do {
											var lg = oKart.ballons.length-1;
											oKart.ballons[lg].suppr();
											oKart.ballons.pop();
										} while (oKart.ballons.length);
										spinKart(oKart,20);
										if (oKart != oPlayer)
											playDistSound(oKart,"musics/events/spin.mp3",(course=="BB")?80:50);
									}
								}
							}
							setTimeout(displayRankings, 1000);
						}
						else
							displayRankings();
					}
				}
				catch (e) {
					return true;
				}
			}
			else {
				iDeco();
				pause = true;
			}
		}
		return true;
	});
	refreshDatas = false;
}

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
		xhr_object.onload = function () {
			if (xhr_object.status == 200) {
				if (!onload(xhr_object.responseText)) {
					setTimeout(function() {
						xhr(page,send,onload,backoff*2);
					}, backoff);
				}
			}
			else
				xhr_object.onerror();
		};
		xhr_object.onerror = function () {
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

function loseBall(i) {
	if (course == "BB") {
		var lg = aKarts[i].ballons.length-1;
		if (!aKarts[i].tourne && aKarts[i].ballons[lg]) {
			aKarts[i].ballons[lg].suppr();
			aKarts[i].ballons.pop();
			if (!i && !aKarts[i].ballons.length) {
				supprArme(i);
				document.getElementById("infoPlace0").style.visibility = "hidden";
			}
		}
	}
}

function move(getId) {
	collisionTest = COL_KART;
	var oKart = aKarts[getId];
	if (!getId && !oKart.cpu && !finishing) {
		var getTime = timer/1000*67;
		var sec = Math.floor(getTime);
		var mls = Math.round((getTime-sec)*1000);
		var min = Math.floor(sec/60);
		sec -= min*60;
		if (sec < 10)
			sec = "0"+sec;
		if (mls < 10)
			mls = "00"+mls;

		else if (mls < 100)
			mls = "0"+mls;
		var tps = toLanguage("&nbsp;Time", "Temps") +": "+ min +":"+ sec +"."+ mls;
		document.getElementById("temps0").innerHTML = tps;
		timer++;

		if (oKart.time) {
			oKart.time--;
			document.getElementById("lakitu"+getId).style.left = Math.round(iScreenScale * (20-oKart.time/5) + 10 + getId * (iWidth*iScreenScale+2))+"px";
			document.getElementById("lakitu"+getId).style.top = Math.round((-(Math.abs(oKart.time - 20)) + 20) * (iScreenScale - 2)) +"px";

			if (oKart.time && !oPlayer.changeView)
				document.getElementById("lakitu"+getId).style.display = "block";
			else
				document.getElementById("lakitu"+getId).style.display = "none";
		}
	}

	if (oKart.tombe) {
		oKart.tombe--;
		updateDriftSize(getId);
		oKart.size = 1;
		if (oKart.tombe == 19)
			playIfShould(oKart, "musics/events/rescue.mp3");
		if (oKart.tombe == 2) {
			if (course == "BB") {
				for (j=0;j<oKart.ballons.length;j++)
					oKart.ballons[j].img.style.display = "block";
			}
		}
		else if (!oKart.tombe) {
			loseBall(getId);
			if (course == "BB") {
				if (!oKart.ballons.length && !oKart.loose)
					oKart.sprite.div.style.opacity = 1;
			}
			oKart.sprite.img.style.display = "block";
		}
		if (oKart == oPlayer)
			oContainer.style.opacity = Math.abs(oKart.tombe-10)/10;
		return;
	}

	if (oKart.rotincdir) {
		oKart.rotinc += 2 * oKart.rotincdir;
	}
	else {
		if (oKart.rotinc < 0)
			oKart.rotinc = Math.min(0, oKart.rotinc + 1);
		else if (oKart.rotinc > 0)
			oKart.rotinc = Math.max(0, oKart.rotinc - 1);
	}
	if (oKart.driftinc) {
		if (oKart.rotincdir) {
			if ((oKart.rotincdir>0) == (oKart.driftinc>0)) {
				oKart.drift += (oKart.rotincdir>0) ? 1:-1;
				if (oKart.drift > 4)
					oKart.drift = 4;
				else if (oKart.drift < -4)
					oKart.drift = -4;
			}
			else {
				if (oKart.rotincdir > 0) {
					oKart.drift++;
					if (oKart.drift > 0)
						oKart.drift = 0;
				}
				else {
					oKart.drift--;
					if (oKart.drift < 0)
						oKart.drift = 0;
				}
			}
		}
		if (oKart.driftcpt < 15) {
			oKart.driftcpt++;
			if (oKart.driftcpt >= 15) {
				getDriftImg(getId).src = "images/turbo-drift.png";
				if (carSpark) {
					carSpark.currentTime = 0;
					carSpark.play();
					oKart.sparkSound = carSpark;
				}
			}
		}
	}
	else {
		if (oKart.drift) {
			if (oKart.drift < 0)
				oKart.drift = Math.min(0, oKart.drift + 0.75);
			else if (oKart.drift > 0)
				oKart.drift = Math.max(0, oKart.drift - 0.75);
		}
	}

	oKart.rotinc = Math.min(oKart.rotinc, fMaxRotInc);
	oKart.rotinc = Math.max(oKart.rotinc, -fMaxRotInc);


	if (!oKart.tourne && oKart.speed && !oKart.billball && !oKart.figstate && (!oKart.driftinc || !oKart.rotincdir || (oKart.rotincdir>0)==(oKart.driftinc>0)))
		oKart.rotation += (((oKart.speedinc < 0) || (oKart.speedinc == 0 && oKart.speed < 0)) ? -oKart.rotinc : oKart.rotinc)*Math.cos(angleDrift(oKart)*Math.PI/180);

	else if (oKart.tourne) {
		oKart.figuring = false;
		oKart.figstate = 0;
		oKart.speed = oKart.speed / 1.2 - oKart.speedinc;
		oKart.rotincdir = 0;
		oKart.tourne -= 2;
		if (course == "BB" && !oKart.tourne) {
			if (oKart.cpu && oKart.ballons.length == 1) {
				var f = 1+Math.round(Math.random());
				for (i=0;(i<f)&&(oKart.reserve);i++) {
					oKart.ballons.push(new Sprite("ballon"));
					oKart.reserve--;
				}
			}
			if (!oKart.ballons.length && !oKart.loose)
				oKart.sprite.div.style.opacity = 1;
		}
	}
	else if (oKart.figstate) {
		oKart.figstate -= 1 + Math.round((11-Math.abs(11-oKart.figstate))*0.5);
		if (oKart.figstate < 0)
			oKart.figstate = 0;
		if (oKart.figstate < 8)
			oKart.figuring = true;
	}
	if (oKart.rotation < 0)
		oKart.rotation += 360;
	if (oKart.rotation > 360)
		oKart.rotation -= 360;

	if (!getId && !oKart.cpu) {
		var oSprite = oKart.sprite;
		if (!oKart.changeView) {
			if (oKart.figstate)
				oSprite.setState((21-oKart.figstate) % 21);
			else if (oKart.driftinc)
				oSprite.setState((oKart.driftinc>0) ? 18:4);
			else if (oKart.rotincdir)
				oSprite.setState((oKart.rotincdir > 0) ? 23:22);
			else
				oSprite.setState(oKart.tourne % 21);
		}
		else if (!oKart.tourne)
			oSprite.setState(11);
		else {
			var fTourne = oKart.tourne % 21;
			oSprite.setState(fTourne + (fTourne < 11 ? 11 : -11));
		}
	}


	if (!oKart.z && !oKart.heightinc)
		oKart.speed += oKart.speedinc;
	else {
		oKart.z += 0.7 * oKart.heightinc * Math.abs(oKart.heightinc);
		oKart.heightinc -= 0.5;
		if (oKart.z <= 0) {
			oKart.heightinc = 0;
			oKart.z = 0;
			if (oKart == oPlayer) {
				oKart.jumped = false;
				if (oKart.driftinc) {
					document.getElementById("drift"+ getId).style.display = "block";
					if (carDrift && !oKart.driftSound) {
						carDrift.currentTime = 0;
						carDrift.play();
						oKart.driftSound = carDrift;
					}
				}
			}
		}
		if (oKart.driftinc)
			document.getElementById("drift"+ getId).style.top = Math.round(iScreenScale*(32-correctZ(oKart.z)) + 10) + "px";
	}

	var fMaxKartSpeed = oKart.maxspeed * oKart.size;

	if (oKart.speed > fMaxKartSpeed)
		oKart.speed = fMaxKartSpeed;
	if (oKart.speed < -fMaxKartSpeed/4)
		oKart.speed = -fMaxKartSpeed/4;

	var effRotation = oKart.rotation-angleDrift(oKart);
	var fMoveX = oKart.speed * direction(0, effRotation);
	var fMoveY = oKart.speed * direction(1, effRotation);

	var fNewPosX = oKart.x + fMoveX;
	var fNewPosY = oKart.y + fMoveY;
	
	var aPosX = oKart.x, aPosY = oKart.y;

	var posx_arrondi = Math.round(fNewPosX);
	var posy_arrondi = Math.round(fNewPosY);
	
	if ((!getId || finishing) && !oKart.loose) {
		var pExplose = touche_bobomb(posx_arrondi, posy_arrondi, (oKart.using[0]==bobombs ? oKart.using[1]:-1)) + touche_cbleue(posx_arrondi, posy_arrondi);
		if (pExplose && !oKart.tourne && !oKart.protect) {
			loseBall(getId);
			spinKart(oKart,pExplose);
			if (oKart.using[0]) {
				if (oKart.using[0][oKart.using[1]][4])
					oKart.using[0][oKart.using[1]][4] = 0;
				oKart.using = [false];
			}
			stopDrifting(getId);
			if (pExplose == 84) {
				oKart.speed = 0;
				oKart.heightinc = 3;
				supprArme(getId)
			}
		}
		else if (oKart.z < 5) {
			if ((touche_fauxobjet(posx_arrondi, posy_arrondi, (oKart.using[0]==fauxobjets ? oKart.using[1]:-1)) || (touche_cverte(posx_arrondi, posy_arrondi, (oKart.using[0]==carapaces ? oKart.using[1]:-1)) || touche_cverte(Math.round(oKart.x), Math.round(oKart.y), (oKart.using[0]==carapaces ? oKart.using[1]:-1))) || touche_crouge(Math.round(oKart.x), Math.round(oKart.y), (oKart.using[0]==carapacesRouge ? oKart.using[1]:-1))) && !oKart.protect) {
				loseBall(getId);
				stopDrifting(getId);
				spinKart(oKart,42);
				oKart.using = [false];
			}
			else if (touche_banane(posx_arrondi, posy_arrondi, (oKart.using[0]==bananes ? oKart.using[1]:-1)) && !oKart.protect) {
				loseBall(getId);
				stopDrifting(getId);
				spinKart(oKart,20);
				if (oKart.using[0]) {
					if (oKart.using[0][oKart.using[1]][4])
						oKart.using[0][oKart.using[1]][4] = 0;
					oKart.using = [false];
				}
			}
		}
	}

	var rScroller, rHeight, rSize;
	if (!getId) {
		var rScroller = document.getElementById("scroller"+getId).getElementsByTagName("div")[0];
		var rHeight = rScroller.offsetHeight;
		rSize = iScreenScale*8;
	}
	if (objet(posx_arrondi, posy_arrondi)) {
		if (!oKart.destroySound) {
			oKart.destroySound = playDistSound(oKart, "musics/events/item_destroy.mp3", 80);
			if (oKart.destroySound) {
				oKart.destroySound.onended = function() {
					oKart.destroySound = undefined;
					document.body.removeChild(this);
				}
			}
		}
		if (!oKart.arme && (oKart.tours <= oMap.tours || course == "BB") && !oKart.billball) {
			var iObj;
			if (course != "BB") {
				iObj = randObj(oKart);
				if ((oKart.tours == 1) && (getCpScore(oKart) <= (getCpDiff(oKart)/2))) {
					while ((iObj == "carapacebleue") || (iObj == "eclair"))
						iObj = randObj(oKart);
				}
				else {
					for (var i=0;i<aKarts.length;i++) {
						if (aKarts[i].arme == "carapacebleue") {
							while (iObj == "carapacebleue")
								iObj = randObj(oKart);
							break;
						}
					}
				}
			}
			else {
				if (oKart.ballons.length) {
					do {
						iObj = objets[Math.floor(Math.random()*75)];
					} while ((iObj == "billball") || ((iObj == "carapacebleue") && ((oKart.place == 1) || (timer < 500))));
				}
				else {
					var ghostItems = ["fauxobjet", "banane", "carapace", "bobomb"];
					iObj = ghostItems[Math.floor(Math.random()*ghostItems.length)];
				}
			}
			oKart.arme = iObj;
			if (shouldPlaySound(oKart))
				oKart.rouletteSound = playSoundEffect("musics/events/roulette.mp3");
			if (!getId) {
				document.getElementById("scroller"+getId).getElementsByTagName("div")[0].style.top = -Math.floor(Math.random()*rHeight) +"px";
				document.getElementById("scroller"+getId).style.visibility="visible";
			}
		}
	}
	if (oKart.arme && oKart.roulette != 25) {
		if (!getId) {
			var nTop = (parseInt(rScroller.style.top) + Math.round(iScreenScale*3.5));
			if (nTop > 0)
				nTop += rSize-rHeight;
			rScroller.style.top = nTop +"px";
		}
		oKart.roulette++;
		if (oKart.roulette >= 25) {
			oKart.roulette = 25;
			if (!getId) {
				document.getElementById("scroller0").style.visibility="hidden";
				document.getElementById("roulette0").innerHTML = '<img alt="." src="images/objects/'+ oKart.arme +'.gif" style="width: '+ Math.round(iScreenScale * 8 - 3)+'px;" />';
				if (oKart.rouletteSound) {
					removeIfExists(oKart.rouletteSound);
					playSoundEffect("musics/events/gotitem.mp3");
					oKart.rouletteSound = undefined;
				}
			}
		}
	}


	if ((oKart.z > 1.175) || canMoveTo(aPosX,aPosY, fMoveX,fMoveY, oKart.protect)) {
		oKart.x = fNewPosX;
		oKart.y = fNewPosY;
	}
	else {
		oKart.speed *= -1;
		if (!oKart.collideSound) {
			oKart.collideSound = playIfShould(oKart, "musics/events/collide.mp3");
			if (oKart.collideSound) {
				oKart.collideSound.onended = function() {
					oKart.collideSound = undefined;
					document.body.removeChild(this);
				}
			}
		}
	}

	if (!oKart.speedinc)
		oKart.speed *= oKart.sliding ? 0.95:0.9;

	oKart.sliding = undefined;
	if (!oKart.z) {
		if (!oKart.heightinc) {
			oKart.ctrled = false;
			oKart.fell = false;
		}
		if (oKart.figuring) {
			oKart.turbodrift = 15;
			oKart.driftcpt = 0;
		}
		var pJump = sauts(aPosX, aPosY, fMoveX, fMoveY);
		if (pJump && !oKart.tourne) {
			oKart.heightinc = pJump * 1.5;
			oKart.speed = 11;
			oKart.figuring = false;
			oKart.figstate = 0;
			if (!oKart.bounceSound) {
				oKart.bounceSound = playIfShould(oKart, "musics/events/jump.mp3");
				if (oKart.bounceSound) {
					oKart.bounceSound.onended = function() {
						oKart.bounceSound = undefined;
						document.body.removeChild(this);
					}
				}
			}
		}
		else {
			var hpType;
			var fTombe = tombe(Math.round(oKart.x), Math.round(oKart.y), oMap.checkpoint ? oMap.checkpoint[(oKart.demitours+1!=oMap.checkpoint.length) ? oKart.demitours+1 : 0][3] : 0);
			if (fTombe) {
				if (fTombe == true) {
					if (isBattle && simplified) {
						fTombe = [oKart.x,oKart.y,oKart.rotation];
						if (oKart.x > oMap.w-1) {
							fTombe[0] = oMap.w-50;
							if (oKart.y > oMap.h-1) {
								fTombe[1] = oMap.h-50;
								fTombe[2] = 225;
							}
							else if (oKart.y < 0) {
								fTombe[1] = 50;
								fTombe[2] = 315;
							}
							else {
								fTombe[1] = oKart.y-oKart.y%100+50;
								fTombe[2] = 270;
							}
						}
						else if (oKart.y > oMap.h-1) {
							fTombe[1] = oMap.h-50;
							if (oKart.x < 0) {
								fTombe[0] = 50;
								fTombe[2] = 135;
							}
							else {
								fTombe[0] = oKart.x-oKart.x%100+50;
								fTombe[2] = 180;
							}
						}
						else if (oKart.x < 0) {
							fTombe[0] = 50;
							if (oKart.y < 0) {
								fTombe[1] = 50;
								fTombe[2] = 45;
							}
							else {
								fTombe[1] = oKart.y-oKart.y%100+50;
								fTombe[2] = 90;
							}
						}
						else {
							fTombe[1] = 50;
							fTombe[0] = oKart.x-oKart.x%100+50;
							fTombe[2] = 0;
						}
					}
					else
						fTombe = oMap.startposition[0];
				}
				oKart.tombe = 20;
				oKart.ctrled = true;
				oKart.x = fTombe[0];
				oKart.y = fTombe[1];
				oKart.speed = 0;
				oKart.z = 10;
				oKart.tourne = 0;
				oKart.rotation = fTombe[2]*90;
				oKart.protect = false;
				oKart.figuring = false;
				oKart.figstate = 0;
				oKart.fell = true;
				stopDrifting(getId);
				supprArme(getId);
				if (oKart.using)
					detruit(oKart.using[0],oKart.using[1]);
				oKart.sprite.img.style.display = "none";
				oKart.sprite.div.style.backgroundImage = "";
				if (oKart.etoile)
					oKart.sprite.img.src = getSpriteSrc(oKart.personnage);
				if (course == "BB") {
					for (j=0;j<oKart.ballons.length;j++)
						oKart.ballons[j].img.style.display = "none";
				}
				oKart.champi = 0;
				resetPowerup(oKart);
				playIfShould(oKart, "musics/events/fall.mp3");
			}
			else if (!oKart.protect && !oKart.champi && !oKart.z && !oKart.figuring && oKart.speed > 1 && !(oKart.turbodrift>12) && (hpType=ralenti(posx_arrondi, posy_arrondi))) {
				var capSpeed;
				switch (hpType) {
					case "herbe" :
						capSpeed = 1;
						break;
					case "glace" :
						capSpeed = 3;
						oKart.sliding = 8;
						break;
					case "eau" :
						capSpeed = 3-oKart.speedinc;
						oKart.sliding = 5;
						break;
					case "choco" :
						capSpeed = 3-oKart.speedinc;
						oKart.sliding = 4;
						break;
				}
				if (oKart.speed > capSpeed)
					oKart.speed = capSpeed;
				stopDrifting(getId);
			}
			oKart.figuring = false;
			oKart.figstate = 0;
		}
	}


	if (oKart.using[0]) {
		var oArme = oKart.using[0][oKart.using[1]];
		oArme[2] = (oKart.x - 5 * direction(0, oKart.rotation));
		oArme[3] = (oKart.y - 5 * direction(1, oKart.rotation));
		oArme[4] = oKart.z;
	}
	if (course != "BB") {
		if (checkpoint(oKart)) {
			var nbjoueurs = aKarts.length;
			oKart.demitours = getNextCp(oKart);
			oKart.tours++;
			if (oKart.tours == (oMap.tours+1)) {
				oKart.place = 0;
				for (var i=0;i<nbjoueurs;i++) {
					if (aKarts[i].tours > oMap.tours)
						oKart.place++;
				}
				if (!getId && !finishing) {
					oKart.checkpoint = 0;
					if (oKart.using[0]) {
						var aMusic = bMusic, aSfx = iSfx;
						bMusic = false;
						iSfx = false;
						arme(getId);
						bMusic = aMusic;
						iSfx = aSfx;
					}
					oKart.arme = false;
					stopDrifting(getId);
					supprArme(getId);
					oKart.cpu = true;
					oKart.aipoint = 0;
					oKart.maxspeed = 5.7;
					document.getElementById("lakitu0").style.display = "none";
					var infos0 = document.getElementById("infos0");
					infos0.style.left = (iScreenScale*15) +"px";
					infos0.innerHTML = "";
					var oTr = document.createElement("tr");
					var oTd = document.createElement("td");
					oTd.style.fontSize = (iScreenScale*8) +"px";
					oTd.style.color = "#F80";
					oTd.innerHTML = toLanguage("&nbsp; &nbsp; FINISH !", "TERMINE !");
					oTr.appendChild(oTd);
					var oTr2 = document.createElement("tr");
					var oTd2 = document.createElement("td");
					oTd2.style.fontSize = Math.round(iScreenScale*4.5+10) +"px";
					oTd2.style.color = "#FF0";
					oTd2.innerHTML = toLanguage("&nbsp; &nbsp; &nbsp; Please wait...", "Veuillez patienter...");
					oTr2.appendChild(oTd2);
					infos0.appendChild(oTr);
					infos0.appendChild(oTr2);
					infos0.style.visibility = "visible";
					document.getElementById("infoPlace0").style.visibility = "hidden";
					document.onkeydown = undefined;
					document.onkeyup = undefined;
					document.onmousedown = undefined;
					window.onbeforeunload = undefined;
				}
				if (oMap.sections)
					if (oKart.billball>1) oKart.billball = 1;
			}
			else if (!getId) {
				document.getElementById("tour0").innerHTML = oKart.tours;
				document.getElementById("lakitu0").getElementsByTagName("div")[0].innerHTML = (oMap.sections ? "Sec":toLanguage("Lap","Tour")) + "<small>&nbsp;</small>" + oKart.tours;
				oKart.time = 40;
				if (bMusic || iSfx) {
					if (oKart.tours == oMap.tours) {
						var cMusicEmbed = postStartMusic("musics/events/lastlap.mp3");
						if (iSfx) {
							fadeOutMusic(carEngine,1,0.6,-1);
							fadeOutMusic(carEngine2,1,0.6,-1);
						}
						cMusicEmbed.removeAttribute("loop");
						setTimeout(function() {
							if (bMusic) {
								if (document.body.contains(cMusicEmbed)) {
									document.body.removeChild(cMusicEmbed);
									startMapMusic(true);
								}
								else
									fastenMusic(mapMusic);
							}
							if (iSfx) {
								carEngine.volume = 1;
								carEngine2.volume = 1;
							}
						}, 2700);
					}
					else if (iSfx)
						playSoundEffect("musics/events/nextlap.mp3");
				}
			}
		}
	}
	
	oKart.maxspeed = 5.4 * cp[oKart.personnage][1];
	if (oKart.turbodrift) {
		oKart.maxspeed = 8;
		oKart.speed = 8;
		oKart.turbodrift--;
	}
	if (oKart.champi) {
		oKart.maxspeed = 11;
		oKart.champi--;
		if (course == "BB" && oKart.ballons.length < 3) {
			var touche = false;
			for (i=0;i<aKarts.length;i++) {
				var kart = aKarts[i];
				if (oKart != kart && oKart.ballons.length && kart.ballons.length && Math.pow(oKart.x-kart.x, 2) + Math.pow(oKart.y-kart.y, 2) < 25 && Math.abs(oKart.z - kart.z) < 2 && !oKart.tourne && !kart.tourne && !kart.protect) {
					loseBall(i);
					stopDrifting(i);
					oKart.ballons.push(new Sprite("ballon"));
					spinKart(kart,20);
				}
			}
		}
	}
	if (oKart.billball) {
		oKart.z = 2;
		oKart.heightinc = 0;
		oKart.speed = 9;

		var iLocalX, iLocalY;
		if (oKart.aipoint != undefined) {
			iLocalX = oMap.aipoints[oKart.aipoint][0] - oKart.x;
			iLocalY = oMap.aipoints[oKart.aipoint][1] - oKart.y;

			if (iLocalX*iLocalX + iLocalY*iLocalY < 1600) {
				oKart.aipoint++;

				if (oKart.aipoint >= oMap.aipoints.length)
					oKart.aipoint = 0;
			}
		}
		else {
			var demitour = oKart.demitours+1;
			if (demitour >= oMap.checkpoint.length)
				demitour = 0;
			var oBox = oMap.checkpoint[demitour];
			iLocalX = oBox[0] + (oBox[3] ? Math.round(oBox[2]/2) : 8) - oKart.x;
			iLocalY = oBox[1] + (oBox[3] ? 8 : Math.round(oBox[2]/2)) - oKart.y;
			for (var i=0;i<oMap.aipoints.length;i++) {
				var oBox = oMap.aipoints[i];
				if (oKart.x > oBox[0] - 35 && oKart.x < oBox[0] + 35 && oKart.y > oBox[1] - 35 && oKart.y < oBox[1] + 35) {
					oKart.aipoint = i + 1;
					if (oKart.aipoint == oMap.aipoints.length) oKart.aipoint = 0;
					iLocalX = oMap.aipoints[oKart.aipoint][0] - oKart.x;
					iLocalY = oMap.aipoints[oKart.aipoint][1] - oKart.y;
					i = oMap.aipoints.length;
				}
			}
		}

		var iRotatedX = iLocalX * direction(1, oKart.rotation) - iLocalY * direction(0, oKart.rotation);
		var iRotatedY = iLocalX * direction(0, oKart.rotation) + iLocalY * direction(1, oKart.rotation);

		var fAngle = Math.atan2(iRotatedX,iRotatedY) / Math.PI * 180;
		if (Math.abs(fAngle) > 10) {
			if (Math.abs(fAngle) > 60) oKart.speed = 1;
			fAngle = (fAngle > 0) ? 10:-10;
		}

		oKart.rotation += fAngle;
		oKart.rotation = oKart.rotation % 360;
		oKart.billball--;
		if (!oKart.billball) {
			oKart.sprite.img.src = getSpriteSrc(oKart.personnage);
			oKart.size = 1;
			oKart.z = 0;
			updateDriftSize(getId);
			oKart.jumped = false;
			oKart.protect = false;
			if (!oKart.cpu)
				oKart.aipoint = undefined;
		}
	}
	if (oKart.etoile) {
		oKart.maxspeed *= 1.35;
		oKart.etoile--;
		if (oKart.etoile < 15) {
			oKart.sprite.img.src = (oKart.etoile % 2 ? getStarSrc(oKart.personnage) : getSpriteSrc(oKart.personnage));
			if (!oKart.etoile) {
				oKart.protect = !!oKart.megachampi;
				oKart.speedinc /= 5;
				stopStarMusic(oKart);
			}
		}
	}
	if (oKart.megachampi) {
		oKart.megachampi--;
		if (oKart.megachampi > 41)
			oKart.size *= 1.05;
		else if (oKart.megachampi < 8) {
			oKart.size /= 1.05;
			if (!oKart.megachampi) {
				oKart.protect = !!oKart.etoile;
				stopMegaMusic(oKart);
			}
		}
		updateDriftSize(getId);
	}
	if (oKart.eclair) {
		oKart.eclair--;
		if ((oKart.eclair > 80) && (oKart.eclair <= 88))
			document.getElementById("mariokartcontainer").style.opacity = 1;
		else if ((oKart.eclair < 1) && (oPlayer.size < 1))
			oPlayer.size = 1;
	}

	if (!oKart.z && accelere(posx_arrondi, posy_arrondi)) {
		oKart.champi = 20;
		oKart.maxspeed = 11;
		oKart.speed = 11;
		if (!oKart.boostSound) {
			oKart.boostSound = playIfShould(oKart, "musics/events/boost.mp3");
			if (oKart.boostSound) {
				oKart.boostSound.onended = function() {
					oKart.boostSound = undefined;
					document.body.removeChild(this);
				}
			}
		}
	}
	if (iSfx && (oKart == oPlayer) && !finishing && !oKart.cpu) {
		if ((bMusic&&(oKart.etoile||oKart.megachampi)) || oKart.tombe || oKart.turbodrift || oKart.turboSound) {
			updateEngineSound();
			if (oKart.turbodrift == 14) {
				carEngine3.currentTime = 0;
				carEngine3.volume = 1;
				carEngine3.play();
				oKart.turboSound = carEngine3;
				clearTimeout(oKart.turboHandler);
				oKart.turboHandler = setTimeout(function() {
					if (oKart.turboSound) {
						oKart.turboSound.pause();
						oKart.turboSound = undefined;
					}
				}, 2000);
				if (oKart.sparkSound) {
					fadeOutMusic(oKart.sparkSound, 1,0.8, false);
					oKart.sparkSound = undefined;
				}
			}
			if (bMusic && oKart.protect && oKart.turboSound)
				oKart.turboSound.volume = 0;
		}
		else
			updateEngineSound(oKart.speed>3 ? carEngine2:carEngine);
	}
	if (course == "BB" && !oKart.ballons.length && !oKart.tourne && !oKart.loose) {
		var setOpac = oKart.sprite.div.style.opacity-0.1;
		oKart.sprite.div.style.opacity = setOpac;
		var oPacLim = (oKart==oPlayer) ? 0.4:0.01;
		if (setOpac < oPacLim) {
			if (oKart != oPlayer)
				oKart.sprite.img.style.display = "none";
			oKart.loose = true;
		}
	}
}

function angleDrift(oKart) {
	if (oKart.sliding)
		return oKart.rotinc*oKart.sliding;
	if (oKart != oPlayer)
		return 0;
	return oKart.drift*6;
}
function updateDriftSize(getId) {
	if (!getId) {
		var k = aKarts[getId].size-1;
		getDriftImg(getId).style.left = -Math.round((iScreenScale*2)*k) + "px";
		getDriftImg(getId).style.top = Math.round((iScreenScale*2)*k) + "px";
		getDriftImg(getId).style.width = Math.round(iScreenScale * 8 + (iScreenScale*4)*k) + "px";
	}
}
function getDriftImg(getId) {
	return document.getElementById("drift"+ getId).getElementsByClassName("driftimg")[0];
}

function ai(oKart) {
	if (oMap.sections && oKart.tours > oMap.tours) {
		oKart.speedinc = 0;
		oKart.rotincdir = 0;
		return;
	}

	var aCurPoint = oMap.aipoints[oKart.aipoint];

	var iLocalX = aCurPoint[0] - oKart.x;
	var iLocalY = aCurPoint[1] - oKart.y;

	iRotatedX = iLocalX * direction(1, oKart.rotation) - iLocalY * direction(0, oKart.rotation);
	iRotatedY = iLocalX * direction(0, oKart.rotation) + iLocalY * direction(1, oKart.rotation);

	var fAngle = Math.atan2(iRotatedX,iRotatedY) / Math.PI * 180;

	oKart.speedinc = (oKart.speed >= 0 ? 1 : 0.2);

	if (Math.abs(fAngle) > 7 + Math.random()*5 || oKart.speed < 0)
		oKart.rotincdir = fAngle > 0 && oKart.speed > 0 ? 1 : -1;
	else
		oKart.rotincdir = 0;

	if (iLocalX*iLocalX + iLocalY*iLocalY < 1600) {
		oKart.aipoint++;

		if (oKart.aipoint >= oMap.aipoints.length)
			oKart.aipoint = 0;
	}
}

function cycle() {

	if (!pause) {
		setTimeout(cycle,67);
		for (var i=0;i<aKarts.length;i++) {
			var oKart = aKarts[i];
			if (oKart.cpu) {
				if (!oKart.billball)
					ai(oKart);
				pCol(oKart);
			}
			move(i);
			if (oKart.protect)
				colKart(oKart);
			places(i);
		}
		if (oMap.infoPlus)
			oMap.infoPlus({map:oMap});
		if (refreshDatas)
			resetDatas();
		render();
	}
}

document.onkeydown = function(e) {
	switch (e.keyCode) {
		case 38: // up
			oPlayer.speedinc = 1;
			if (document.getElementById("decompte0").innerHTML > 1)
				updateEngineSound(carEngine2);
			return false;
		case 37: // left
			oPlayer.rotincdir = 1;
			return false;
		case 39: // right
			oPlayer.rotincdir = -1;
			return false;
	}
}

document.onkeyup = function(e) {
	switch (e.keyCode) {
		case 38: // up
			oPlayer.speedinc = 0;
			updateEngineSound(carEngine);
			break;
		case 37: // left
			oPlayer.rotincdir = 0;
			break;
		case 39: // right
			oPlayer.rotincdir = 0;
	}
}

if (!String.prototype.startsWith) {
	String.prototype.startsWith = function(searchString, position) {
		position = position || 0;
    	return this.indexOf(searchString, position) === position;
	};
}
function isCustomPerso(playerName) {
	if (playerName.startsWith("cp-")) {
		if (!customPersos[playerName]) {
			cp[playerName] = [0.6,1,0.6,0];
			var defaultIc = PERSOS_DIR + playerName + "-ld.png";
			customPersos[playerName] = {
				"name": language ? "Deleted character":"Perso supprimé",
				"map": defaultIc,
				"podium": defaultIc,
				"music" : "mario"
			};
			xhr("getCP.php", "perso="+playerName, function(res) {
				if (res == -1)
					return true;
				if (!res)
					return false;
				var perso;
				try {
					perso = JSON.parse(res);
				}
				catch (e) {
					return false;
				}
				cp[playerName][0] = perso.acceleration;
				cp[playerName][1] = perso.speed;
				cp[playerName][2] = perso.handling;
				customPersos[playerName] = perso;
				return true;
			});
		}
		return true;
	}
	return false;
}
function getWinnerSrc(playerName) {
	if (isCustomPerso(playerName))
		return customPersos[playerName].podium;
	return "images/winners/w_"+ playerName +".png";
}
function getEndingSrc(playerName) {
	if (isCustomPerso(playerName))
		playerName = customPersos[playerName].music;
	if (baseCp[playerName])
		return "musics/endings/ending_"+playerName+".mp3";
	return playerName;
}
function getStarSrc(playerName) {
	if (isCustomPerso(playerName))
		return PERSOS_DIR + playerName + "-star.png";
	return "images/star/star_" + playerName +".png";
}
function getSpriteSrc(playerName) {
	if (isCustomPerso(playerName))
		return PERSOS_DIR + playerName + ".png";
	return "images/sprites/sprite_" + playerName +"_smooth.png";
}
function getMapIcSrc(playerName) {
	if (isCustomPerso(playerName))
		return customPersos[playerName].map;
	return "images/map_icons/"+ playerName +".png";
}

function privateGame() {
	var oScr = document.createElement("div");

	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";

	oScr.appendChild(toTitle(toLanguage("Private game", "Partie privée"), 0));

	var oDiv = document.createElement("div");
	oDiv.style.position = "absolute";
	oDiv.style.left = (iScreenScale*6) + "px";
	oDiv.style.top = (iScreenScale*12) + "px";
	oDiv.style.fontSize = Math.round(iScreenScale*2.5) + "px";
	oDiv.style.color = "#DFC";
	oDiv.innerHTML = language ? "The &quot;private game&quot; option allows you to play only with people you want.<br />The principle is simple: a private link is generated, you send this link to the concerned members, and you can start playing.":"L'option &quot;partie privée&quot; vous permet de jouer uniquement avec les personnes de votre choix.<br />Le principe est simple : un lien privé est généré, vous envoyez ce lien aux membres concernés, et vous pouvez commencer à jouer.";
	oScr.appendChild(oDiv);
	
	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Generate private link", "Générer un lien privé");
	oPInput.style.fontSize = (3*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.width = (35*iScreenScale)+"px";
	oPInput.style.left = (23*iScreenScale)+"px";
	oPInput.style.top = (28*iScreenScale)+"px";
	oPInput.onclick = function() {
		oScr.innerHTML = "";
		oContainer.removeChild(oScr);
		privateLink();
	}
	oScr.appendChild(oPInput);
	
	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Back", "Retour");
	oPInput.style.fontSize = (2*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (2*iScreenScale)+"px";
	oPInput.style.top = (35*iScreenScale)+"px";
	oPInput.onclick = function() {
		oScr.innerHTML = "";
		oContainer.removeChild(oScr);
		selectPlayerScreen();
	}
	oScr.appendChild(oPInput);

	oContainer.appendChild(oScr);
}
function privateLink() {
	var oScr = document.createElement("div");

	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";

	oScr.appendChild(toTitle(toLanguage("Private game", "Partie privée"), 0));

	xhr("privateGame.php", null, function(res) {
		if (res) {
			var baseUrl = shareLink.url;
			var params = shareLink.params.slice(0);
			params.push("key="+res);
			var url = baseUrl + "?" + params.join("&");
			var oDiv = document.createElement("div");
			oDiv.style.position = "absolute";
			oDiv.style.left = "0px";
			oDiv.style.top = (iScreenScale*13) + "px";
			oDiv.style.width = (iWidth*iScreenScale) +"px";
			oDiv.style.textAlign = "center";
			oDiv.style.fontSize = (iScreenScale*3) + "px";
			oDiv.style.color = "#CFC";
			oDiv.innerHTML = language ? 'The following private link has been generated:<br /><a href="'+url+'" style="color:AAF">'+url+'</a><br /><br />Enjoy game :)' : 'Le lien privé suivant a été généré :<br /><a href="'+url+'" style="color:AAF">'+url+'</a><br /><br />Bonne partie :)';
			oScr.appendChild(oDiv);
			return true;
		}
		return false;
	});
	
	oContainer.appendChild(oScr);
}

function selectPlayerScreen(newP) {
	strPlayer = "";
	aPlayers = [];
	for (joueurs in cp)
		aPlayers.push(joueurs);

	var oScr = document.createElement("div");
	if (newP)
		oScr.style.visibility = "hidden";

	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";

	oScr.appendChild(toTitle(toLanguage("Choose a player", "Choisissez un joueur"), 0));

	vitesse = 15*iScreenScale;
	
	var cTable = document.createElement("table");
	cTable.style.display = "none";
	cTable.style.position = "absolute";
	cTable.style.top = (37*iScreenScale+20)+"px";
	cTable.style.left = (25*iScreenScale-60)+"px";
	cTable.style.textAlign = "left";
	cTable.style.fontSize = 2*iScreenScale+"pt";
	cTable.style.color = "white";
	cTable.setAttribute("cellpadding", 2);
	cTable.setAttribute("cellspacing", 2);
	document.body.appendChild(cTable);
	
	var hTr = document.createElement("tr");
	var hTd1 = document.createElement("td");
	hTd1.innerHTML = "&nbsp;";
	hTr.appendChild(hTd1);
	var hTd2 = document.createElement("td");
	hTd2.className = "maj";
	hTd2.innerHTML = "&nbsp;";
	hTd2.style.fontWeight = "bold";
	hTr.appendChild(hTd2);
	cTable.appendChild(hTr);
	
	var sCaracteristiques = [toLanguage("Acceleration", "Accélération"), toLanguage("Max speed", "Vitesse max"), toLanguage("Handling", "Maniabilité")];
	var dCaracteristiques = new Array();
	
	for (var i=0;i<sCaracteristiques.length;i++) {
		var oTr = document.createElement("tr");
		var oTd1 = document.createElement("td");
		oTd1.className = "rgt";
		oTd1.innerHTML = sCaracteristiques[i] +" :";
		oTr.appendChild(oTd1);
		var oTd2 = document.createElement("td");
		dCaracteristiques[i] = document.createElement("div");
		dCaracteristiques[i].style.backgroundColor = "#838057";
		dCaracteristiques[i].style.border = "solid 1px silver";
		dCaracteristiques[i].style.height = 2*iScreenScale+"px";
		dCaracteristiques[i].innerHTML = "&nbsp;";
		oTd2.appendChild(dCaracteristiques[i]);
		oTr.appendChild(oTd2);
		cTable.appendChild(oTr);
	}

	function createPersoSelector(i) {
		var oDiv = document.createElement("div");
		oDiv.style.backgroundColor = "#78D0F8";
		oDiv.style.position = "absolute";
		oDiv.style.width = (5 * iScreenScale) + "px";
		oDiv.style.height = (5 * iScreenScale) + "px";
		oDiv.style.borderTop = "double 4px black"; 
		oDiv.style.borderLeft = "double 4px #F8F8F8"; 
		oDiv.style.borderRight = "double 4px #F8F8F8"; 
		oDiv.style.borderBottom = "solid 5px #00B800"; 
		oDiv.style.overflow = "hidden";

		var oPImg = new Image();
		oPImg.style.height = (5 * iScreenScale) +"px";
		oPImg.style.position = "absolute";
		if (pUnlocked[i]) {
			oPImg.src = getSpriteSrc(aPlayers[i]);
			oPImg.style.cursor = "pointer";
			oPImg.alt = aPlayers[i];
			oPImg.nb = i;
			oPImg.style.left = -(30 * iScreenScale) +"px";
			oPImg.onmouseover = function() {
				cTable.style.display = "block";
				hTd2.innerHTML = toPerso(this.alt);
				var coeffs = [2.5,5,2.5], consts = [0.2,0.8,0.2];
				for (var i=0;i<dCaracteristiques.length;i++)
					dCaracteristiques[i].style.width = vitesse*((cp[this.alt][i]-consts[i])*coeffs[i]+1)+"px";
				tourne=this.nb;tourner();
			}
			oPImg.onmouseout = function() {
				cTable.style.display = "none";
				this.style.left = (parseFloat(this.style.height) * -6) +"px";
				tourne = -1;
			}
			oPImg.onclick = function() {
				tourne = -1;
				strPlayer = this.alt;
				oScr.innerHTML = "";
				oContainer.removeChild(oScr);
				document.body.removeChild(cTable);
				searchCourse();
				var cpId = /^cp-\w+-(\d+)$/g.exec(this.alt);
				if (cpId)
					xhr("selectPerso.php", "id="+cpId[1], function(){return true});
			}
		}
		else
			oPImg.src = "images/kart_locked.png";
		oDiv.appendChild(oPImg);
		return oDiv;
	}

	for (var i=0;i<nBasePersos;i++) {
		var oDiv = createPersoSelector(i);
		oDiv.style.left = (8*(i%7)+9) * iScreenScale +"px";
		oDiv.style.top = ((10+Math.floor(i/7)*7)*iScreenScale)+"px";
		oScr.appendChild(oDiv);
	}
	var pDiv = document.createElement("div");
	pDiv.style.backgroundColor = "#78D0F8";
	pDiv.style.position = "absolute";
	pDiv.style.width = (5 * iScreenScale) + "px";
	pDiv.style.height = (5 * iScreenScale) + "px";
	pDiv.style.left = (67 * iScreenScale) +"px";
	pDiv.style.top = (24 * iScreenScale)+"px";
	pDiv.style.borderTop = "double 4px black"; 
	pDiv.style.borderLeft = "double 4px #F8F8F8"; 
	pDiv.style.borderRight = "double 4px #F8F8F8"; 
	pDiv.style.borderBottom = "solid 5px #00B800"; 
	pDiv.style.overflow = "hidden";

	var pPImg = new Image();
	pPImg.style.height = (5 * iScreenScale) +"px";
	pPImg.style.position = "absolute";
	pPImg.src = "images/kart_persos.png";
	pPImg.style.cursor = "pointer";
	pPImg.title = language ? "Character editor" : "Éditeur de personnages";
	pPImg.onclick = function() {
		window.open('choosePerso.php','chose','scrollbars=1, resizable=1, width=500, height=500');
	}

	pDiv.appendChild(pPImg);
	oScr.appendChild(pDiv);

	oContainer.appendChild(oScr);
	
	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Back", "Retour");
	oPInput.style.fontSize = (2*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (2*iScreenScale)+"px";
	oPInput.style.top = (35*iScreenScale)+"px";
	oPInput.onclick = function() {
		oScr.innerHTML = "";
		oContainer.removeChild(oScr);
		document.body.removeChild(cTable);
		connexion();
	}
	oScr.appendChild(oPInput);
	
	if (!shareLink.key) {
		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = toLanguage("Private game...", "Partie privée...");
		oPInput.style.fontSize = (2*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (62*iScreenScale)+"px";
		oPInput.style.top = (35*iScreenScale)+"px";
		oPInput.onclick = function() {
			oScr.innerHTML = "";
			oContainer.removeChild(oScr);
			privateGame();
		}
		oScr.appendChild(oPInput);
	}

	xhr("myPersos.php", null, function(res) {
		var newPersos = [];
		try {
			newPersos = eval(res);
		}
		catch (e) {
		}
		if (!newPersos.length) {
			oScr.style.visibility = "visible";
			return true;
		}
		cp = {};
		for (var joueurs in baseCp)
			cp[joueurs] = baseCp[joueurs];
		customPersos = {};
		for (var i=0;i<newPersos.length;i++) {
			var newPerso = newPersos[i];
			cp[newPerso["sprites"]] = [newPerso["acceleration"],newPerso["speed"],newPerso["handling"],0];
			customPersos[newPerso["sprites"]] = newPerso;
		}
		aPlayers = [];
		for (joueurs in cp)
			aPlayers.push(joueurs);
		for (var i=0;i<newPersos.length;i++) {
			var inc = nBasePersos+i;
			pUnlocked[inc] = 1;
			var oDiv = createPersoSelector(inc);
			if (newP && !i && oDiv.firstChild.onclick) {
				oDiv.firstChild.onclick();
				return true;
			}
			oDiv.style.left = 67*iScreenScale +"px";
			oDiv.style.top = ((10+i*7)*iScreenScale)+"px";
			oScr.insertBefore(oDiv,pDiv);
		}
		oScr.style.visibility = "visible";
		return true;
	});

	selectPerso = function(persoId) {
		tourne = -1;
		oScr.innerHTML = "";
		oContainer.removeChild(oScr);
		xhr("selectPerso.php", "id="+persoId, function(res) {
			selectPlayerScreen(true);
			return true;
		});
	};

	updateMenuMusic(0);
}

function searchCourse() {

	var oScr = document.createElement("div");

	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";
	
	var oDiv = document.createElement("div");
	oDiv.style.position = "absolute";
	oDiv.style.left = (iScreenScale*15) +"px";
	oDiv.style.top = (iScreenScale*4) +"px";
	oDiv.style.fontSize = (iScreenScale*4) +"";
	oDiv.style.textAlign = "center";
	oDiv.innerHTML = toLanguage("Searching for other players<br />Please wait...", "Recherche d'autres joueurs<br />Veuillez patienter...");
	
	oScr.appendChild(oDiv);
	
	var oAlert = document.createElement("input");
	oAlert.type = "checkbox";
	oAlert.id = "iAlert";
	oAlert.style.position = "absolute";
	oAlert.style.left = (iScreenScale*10-16) +"px";
	oAlert.style.top = (iScreenScale*14) +"px";
	
	oScr.appendChild(oAlert);
	
	var oLabel = document.createElement("label");
	oLabel.setAttribute("for", "iAlert");
	oLabel.style.position = "absolute";
	oLabel.style.left = Math.round(iScreenScale*12-3.5) +"px";
	oLabel.style.top = Math.round(iScreenScale*12.5+9) +"px";
	oLabel.style.fontSize = (iScreenScale*2) +"pt";
	oLabel.innerHTML = toLanguage("Notify me when opponents have been found", "M'alerter lorsque des adversaires ont été trouvés");
	
	oScr.appendChild(oLabel);
	
	var ratio = 41;
	var mLeft = 0;
	
	var oLoadBar = document.createElement("div");
	oLoadBar.style.position = "absolute";
	oLoadBar.style.left = "0px";
	oLoadBar.style.top = (iScreenScale*20) +"px";
	oLoadBar.style.width = (iScreenScale*ratio*2) +"px";
	oLoadBar.style.height = Math.round(iScreenScale*8.5) +"px";
	oLoadBar.style.overflow = "hidden";
	for (var i=0;i<ratio;i++) {
		var oImg = document.createElement("img");
		oImg.src = "images/cLoading.png";
		oImg.style.width = (iScreenScale*2) +"px";
		oImg.style.position = "absolute";
		oImg.style.left = (i*iScreenScale*2) +"px";
		oImg.style.top = "0px";
		oLoadBar.appendChild(oImg);
	}
	oScr.appendChild(oLoadBar);
	
	var rCount = 1;
	var mLoadX = iScreenScale/2;
	function rSearchCourse() {
		if (rCount) {
			rCount--;
			if (!rCount) {
				var courseParams = "";
				if (isCup) {
					if (isSingle)
						courseParams += (complete ? 'i':'id') + '='+ nid + (isBattle ? '&battle':'');
					else
						courseParams += (complete ? 'c':'s') + 'id='+ nid;
				}
				else if (isBattle)
					courseParams += 'battle';
				if (shareLink.key)
					courseParams += (courseParams ? '&':'') + 'key='+ shareLink.key;
				xhr("getCourse.php", courseParams, function(reponse) {
					if (!reponse || isNaN(reponse))
						return false;
					if (reponse == -1)
						rCount = 10;
					else {
						var isAlert = oAlert.checked;
						oScr.innerHTML = "";
						oContainer.removeChild(oScr);
						if (isAlert) {
							var oMusicAlert = document.createElement("embed");
							oMusicAlert.src = "musics/mkalert.wav";
							oMusicAlert.setAttribute("loop", false);
							oMusicAlert.setAttribute("autostart", true);
							oMusicAlert.style.position = "absolute";
							oMusicAlert.style.left = "-1000px";
							oMusicAlert.style.top = "-1000px";
							document.body.appendChild(oMusicAlert);
							var sTime = new Date().getTime();
							alert(toLanguage("Opponents have been found !\nGood luck !", "Des adversaires ont \xE9t\xE9 trouv\xE9s !\nBonne chance !"));
							reponse -= Math.round((new Date().getTime()-sTime)/1000);
							document.body.removeChild(oMusicAlert);
						}
						if (reponse < 1)
							reponse = 1;
						document.getElementById("sRest").innerHTML = reponse-5;
						selectMapScreen();
						dRest();
						setTimeout(setChat, 1000);
					}
					return true;
				});
			}
		}
		mLeft--;
		if (mLeft <= -4)
			mLeft = 0;
		oLoadBar.style.left = Math.round(mLeft*mLoadX) +"px";
		setTimeout(rSearchCourse, 100);
		
	}
	rSearchCourse();

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Back", "Retour");
	oPInput.style.fontSize = (2*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (2*iScreenScale)+"px";
	oPInput.style.top = (35*iScreenScale)+"px";
	oPInput.onclick = function() {
		rSearchCourse = function(){};
		oScr.innerHTML = "";
		oContainer.removeChild(oScr);
		selectPlayerScreen();
	}
	oScr.appendChild(oPInput);
	
	oContainer.appendChild(oScr);

	updateMenuMusic(0);
}

function chooseRandMap() {
	if (isSingle)
		choose(1);
	else if (isBattle)
		choose(NBCIRCUITS+Math.ceil(Math.random()*4));
	else
		choose(Math.ceil(Math.random()*NBCIRCUITS));
}

function selectMapScreen() {
	if (isCup || isBattle)
		selectRaceScreen(0);
	else {
		var oScr = document.createElement("div");
		var oStyle = oScr.style;
		
		var forceClic4 = true;

		oStyle.width = (iWidth*iScreenScale)+"px";
		oStyle.height = (iHeight*iScreenScale)+"px";
		oStyle.border = "solid 1px black";
		oStyle.backgroundColor = "black";

		oScr.appendChild(toTitle(toLanguage("Choose cup", "Choisissez la coupe"), 0.5));

		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = toLanguage("Back", "Retour");
		oPInput.style.fontSize = (2*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (2*iScreenScale)+"px";
		oPInput.style.top = (30*iScreenScale)+"px";
		oPInput.onclick = function() {
			forceClic4 = false;
			oScr.innerHTML = "";
			oContainer.removeChild(oScr);
			document.getElementById("wait").style.visibility = "hidden";
			chatting = false;
			selectPlayerScreen();
		}

		oScr.appendChild(oPInput);
		oContainer.appendChild(oScr);

		document.getElementById("dMaps").style.top = 40 * iScreenScale +"px";
		document.getElementById("dMaps").style.left = 25 * iScreenScale +"px";
		document.getElementById("dMaps").style.width = (25 * iScreenScale) +"px";
		document.getElementById("dMaps").style.height = (10 * iScreenScale) +"px";

		var coupes = ["champi", "etoile", "carapace", "carapacebleue", "speciale", "carapacerouge", "banane", "feuille", "megachampi", "eclair"];
		var cups_per_line = 5;
		for (var i=0;i<coupes.length;i++) {
			var oPImg = document.createElement("img");
			oPImg.src = "images/objects/"+ coupes[i] +".gif";

			oPImg.style.width = (7 * iScreenScale) + "px";
			oPImg.style.height = (7 * iScreenScale) + "px";
			oPImg.style.cursor = "pointer";
			oPImg.style.position = "absolute"
			oPImg.style.left = (((iWidth-10*cups_per_line)/2+(i%cups_per_line)*11)*iScreenScale)+"px";
			oPImg.style.top = ((11+Math.floor(i/cups_per_line)*9)*iScreenScale)+"px";

			oPImg.alt = i;
			
			var mScreenScale = iScreenScale;

			oPImg.onmouseover = function() {
				var oDefMap = new Image();
				oDefMap.src = "images/selectors/select_map"+ (this.alt*4+1) +".png";
				oDefMap.alt = this.alt*4+4;
				oDefMap.style.border = "double 4px white";
				oDefMap.style.width = "100%";
				oDefMap.style.height = "100%";
				oDefMap.id = "maps";
				document.getElementById("dMaps").appendChild(oDefMap);
				
				var oMapName = mapNameOf(mScreenScale, this.alt*4);
				oMapName.id = "oMapName";
				document.getElementById("dMaps").appendChild(oMapName);

				document.getElementById("dMaps").style.display = "block";
				defileMaps(this.alt*4+4);
			}

			oPImg.onmouseout = function() {
				document.getElementById("dMaps").style.display = "none";
				document.getElementById("dMaps").innerHTML = "";
			}

			oPImg.onclick = function() {
				document.getElementById("dMaps").style.display = "none";

				document.getElementById("dMaps").innerHTML = "";
				oScr.innerHTML = "";
				oContainer.removeChild(oScr);

				selectRaceScreen(this.alt*4);
			}

			oScr.appendChild(oPImg);

		}

		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = "Aléatoire";
		oPInput.style.fontSize = (3*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (34*iScreenScale-10)+"px";
		oPInput.style.top = (30*iScreenScale)+"px";
		
		oPInput.onclick = function() {
			forceClic4 = false;
			oScr.innerHTML = "";
			oContainer.removeChild(oScr);
			chooseRandMap();
		};
		oScr.appendChild(oPInput);
		
		setTimeout(function() {
			if (forceClic4) {
				document.getElementById("dMaps").style.display = "none";
				document.getElementById("dMaps").innerHTML = "";
				oScr.innerHTML = "";
				oContainer.removeChild(oScr);
				chooseRandMap();
			}
		}, document.getElementById("sRest").innerHTML*1000);
	}
	setSRest();
	document.getElementById("wait").style.visibility = "visible";

	updateMenuMusic(1);
}

function setMapSrc(oPImg,i,src) {
	if (isCup) {
		setTimeout(function() {
			oPImg.src = src;
		}, 100*i);
	}
	else
		oPImg.src = src;
}

function selectRaceScreen(cup) {
	var oScr = document.createElement("div");
	var oStyle = oScr.style;
	
	var forceClic4 = true;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";

	oContainer.appendChild(oScr);

	oScr.appendChild(toTitle(toLanguage("Choose race", "Choisissez un circuit"), (isSingle?2.5:0.5)));

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Back", "Retour");
	oPInput.style.fontSize = (2*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (2*iScreenScale)+"px";
	oPInput.style.top = (30*iScreenScale)+"px";
	oPInput.onclick = function() {
		forceClic4 = false;
		oScr.innerHTML = "";
		oContainer.removeChild(oScr);
		if (isCup || isBattle) {
			document.getElementById("wait").style.visibility = "hidden";
			chatting = false;
			selectPlayerScreen();
		}
		else
			selectMapScreen();
	}
	oScr.appendChild(oPInput);

	if ((isCup || isBattle) && !isSingle) {
		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = toLanguage("Random", "Aléatoire");
		oPInput.style.fontSize = (2*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (67*iScreenScale)+"px";
		oPInput.style.top = (30*iScreenScale)+"px";
		oPInput.onclick = function() {
			forceClic4 = false;
			oScr.innerHTML = "";
			oContainer.removeChild(oScr);
			chooseRandMap();
		}
		oScr.appendChild(oPInput);
	}
		
	var mScreenScale = iScreenScale;

	var lCup = isSingle ? cup+1:cup+4;
	for (var i=cup;i<lCup;i++) {
		var mDiv = document.createElement("div");
		mDiv.style.width = (25 * iScreenScale) + "px";
		mDiv.style.height = (10 * iScreenScale) + "px";
		mDiv.style.cursor = "pointer";
		mDiv.style.position = "absolute"
		var j = i - cup;
		if (isSingle) {
			mDiv.style.left = (iScreenScale*27)+"px";
			mDiv.style.top = (iScreenScale*15)+"px";
		}
		else {
			mDiv.style.left = (((iWidth-113)/2+(j-Math.floor(j/2)*2)*25+(j-Math.floor(j/2)*2))*iScreenScale)+iScreenScale*30+"px";
			mDiv.style.top = (12*iScreenScale)+11*iScreenScale*Math.floor(j/2)+"px";
		}
		mDiv.map = aAvailableMaps[i];
		
		var oPImg = new Image();
		setMapSrc(oPImg, i, isCup ? (complete ? "images/uploads/"+ (course=="BB" ? "course":"map") + oMaps[aAvailableMaps[i]].map +"."+ oMaps[aAvailableMaps[i]].ext:"trackicon.php?id="+ oMaps[aAvailableMaps[i]].id +"&type=0") : "images/selectors/select_" + aAvailableMaps[i] + ".png");
		oPImg.style.width = "100%";
		oPImg.style.height = "100%";
		oPImg.style.border = "double 4px silver";
		mDiv.appendChild(oPImg);
		
		mDiv.appendChild(mapNameOf(mScreenScale, i + (isBattle&&!isCup ? 40:0)));

		mDiv.onclick = function() {
			forceClic4 = false;
			oScr.innerHTML = "";
			oContainer.removeChild(oScr);
			choose(this.map.substr(3));
		}
		oScr.appendChild(mDiv);
	}
	setSRest();
	
	setTimeout(function() {
		if (forceClic4) {
			oScr.innerHTML = "";
			oContainer.removeChild(oScr);
			chooseRandMap();
		}
	}, document.getElementById("sRest").innerHTML*1000);

	updateMenuMusic(1);
}

function choose(map) {
	var choixJoueurs = [];
	var startMusicHandler;
	var oTable = document.createElement("table");
	oTable.border = 1;
	oTable.setAttribute("cellspacing", 2);
	oTable.setAttribute("cellpadding", 2);
	oTable.style.position = "absolute";
	oTable.style.fontSize = (iScreenScale*2) +"pt";
	oTable.style.textAlign = "center";
	oTable.style.left = (iScreenScale*25) +"px";
	oTable.style.top = (iScreenScale*2) +"px";
	oTable.style.width = (iScreenScale*30) +"px";
	var oTBody = document.createElement("tbody");
	function refreshTab(reponse) {
		if (reponse) {
			if (reponse != -1) {
				var rCode;
				try {
					rCode = eval(reponse);
				}
				catch(e) {
					return false;
				}
				choixJoueurs = rCode[0];
				var trs = oTBody.getElementsByTagName("tr");
				while (trs.length)
					oTBody.removeChild(trs[0]);
				for (i=0;i<choixJoueurs.length;i++) {
					var oTr = document.createElement("tr");
					var oTd = document.createElement("td");
					var isChoix = choixJoueurs[i][2];
					oTd.innerHTML = isChoix ? lCircuits[isChoix-1] : toLanguage("Not choosen","Non choisi");
					oTr.appendChild(oTd);
					oTBody.appendChild(oTr);
				}
				if (rCode[1] == -1)
					setTimeout(waitForChoice, 1000);
				else {
					if (choixJoueurs.length > 1) {
						aPlayers = new Array();
						aIDs = new Array();
						aPlaces = new Array();
						for (i=0;i<choixJoueurs.length;i++) {
							var aID = choixJoueurs[i][0];
							if (aID != identifiant) {
								aIDs.push(aID);
								aPlayers.push(choixJoueurs[i][1]);
								isCustomPerso(choixJoueurs[i][1]);
								aPlaces.push(choixJoueurs[i][3]);
							}
						}
						tnCourse = new Date().getTime()+rCode[2];
						if (isSingle)
							rCode[2] = 0;
						else
							tnCourse += 5000;
						connecte = rCode[3]+1;
						var cCursor = 0;
						var cTime = 50;
						function moveCursor() {
							var continuer = true;
							if (cCursor == rCode[1]) {
								var pTime = 0, iTime = cTime;
								for (var i=0;i<choixJoueurs.length;i++) {
									iTime = Math.round(iTime*1.05);
									pTime += iTime;
								}
								if (pTime >= rCode[2])
									continuer = false;
							}
							if (continuer) {
								trs[cCursor].style.backgroundColor = "";
								trs[cCursor].style.color = "";
								cCursor++;
								if (cCursor == choixJoueurs.length)
									cCursor = 0;
								trs[cCursor].style.backgroundColor = "#F80";
								trs[cCursor].style.color = "white";
								cTime = Math.round(cTime*1.05);
								rCode[2] -= cTime;
								setTimeout(moveCursor, cTime);
							}
							else
								clignote(0);
						}
						function clignote(cID) {
							trs[cCursor].style.backgroundColor = (cID%2) ? "":"#F80";
							trs[cCursor].style.color = (cID%2) ? "":"white";
							if (cID < 4)
								setTimeout(function(){clignote(cID+1)}, 100);
							else
								setTimeout(function(){document.body.removeChild(oTable);resetGame("map"+ choixJoueurs[rCode[1]][2])}, 500);
						}
						moveCursor();
						oMap = oMaps["map"+choixJoueurs[rCode[1]][2]];
					}
					else {
						var oDiv = document.createElement("div");
						oDiv.style.position = "absolute";
						oDiv.style.left = (iScreenScale*10+10) +"px";
						oDiv.style.top = (iScreenScale*20+10) +"px";
						oDiv.style.fontSize = (iScreenScale*2) +"pt";
						oDiv.innerHTML = toLanguage("Sorry, all your opponents have left the race...", "D&eacute;sol&eacute;, tous vos adversaires ont quitt&eacute; la course...");
						
						oDiv.appendChild(document.createElement("br"));
						
						var nSearch = document.createElement("a");
						nSearch.style.color = "white";
						nSearch.innerHTML = toLanguage("Search for new players", "Rechercher de nouveaux joueurs");
						nSearch.setAttribute("href", "#null");
						nSearch.onclick = function() {
							document.body.removeChild(oTable);
							document.body.removeChild(oDiv);
							removeMenuMusic();
							removeGameMusics();
							formulaire.screenscale.disabled = false;
							formulaire.quality.disabled = false;
							formulaire.music.disabled = false;
							formulaire.sfx.disabled = false;
							searchCourse();
							return false;
						};
						oDiv.appendChild(nSearch);
						
						oDiv.appendChild(document.createElement("br"));
						
						var nSearch = document.createElement("a");
						nSearch.style.color = "white";
						nSearch.innerHTML = toLanguage("Back to Mario Kart PC", "Retour \xE0 Mario Kart PC");
						nSearch.setAttribute("href", "index.php");
						oDiv.appendChild(nSearch);
						
						document.body.appendChild(oDiv);
						
						chatting = false;

						clearInterval(startMusicHandler);
					}
				}
			}
			else
				iDeco();
			return true;
		}
		return false;
	}
	xhr("chooseMap.php", "joueur="+strPlayer+"&map="+map+(course=="BB"?"&battle":""), refreshTab);
	function waitForChoice() {
		xhr("getMap.php", (course=="BB"?"battle":""), refreshTab);
	}
	oTable.appendChild(oTBody);
	document.body.appendChild(oTable);
	document.getElementById("wait").style.visibility = "hidden";

	updateMenuMusic(1);

	formulaire.screenscale.disabled = true;
	formulaire.quality.disabled = true;
	formulaire.music.disabled = true;
	formulaire.sfx.disabled = true;

	if (bMusic) {
		oMap = undefined;
		startMusicHandler = setInterval(function() {
			if (oMap) {
				startMapMusic(false);
				loadEndingMusic();
				mapMusic.blur();
				endingMusic.blur();
				if (!isMobile()) {
					var oDebug = document.createElement("input");
					document.body.appendChild(oDebug);
					oDebug.focus();
					oDebug.blur();
					document.body.removeChild(oDebug);
				}
				clearInterval(startMusicHandler);
			}
		}, 1000);
	}
}

function iDeco() {
	var oDiv = document.createElement("div");
	oDiv.style.position = "absolute";
	oDiv.style.left = (iScreenScale*15) +"px";
	oDiv.style.top = (iScreenScale*8) +"px";
	oDiv.style.width = (iScreenScale*50) +"px";
	oDiv.style.height = (iScreenScale*15) +"px";
	oDiv.style.fontSize = (iScreenScale*3) +"px";
	oDiv.style.backgroundColor = "gray";
	oDiv.style.color = "white";
	oDiv.style.border = "solid 1px silver";
	oDiv.style.fontWeight = "bold";
	oDiv.style.textAlign = "center";
	oDiv.style.paddingTop = (iScreenScale*5) +"px";
	oDiv.style.zIndex = 20000;
	oDiv.innerHTML = toLanguage("You have been disconnected", "Vous avez &eacute;t&eacute; d&eacute;connect&eacute;");
	for (var i=0;i<2;i++)
		oDiv.appendChild(document.createElement("br"));
	var oQuit = document.createElement("input");
	oQuit.type = "button";
	oQuit.value = toLanguage("Back", "Retour");
	oQuit.style.fontSize = (iScreenScale*3) +"px";
	oQuit.onclick = function() {
		location.reload();
	}
	oDiv.appendChild(oQuit);
	oContainer.appendChild(oDiv);
	chatting = false;
}

function dRest() {
	var tRest = document.getElementById("sRest").innerHTML - 1;
	document.getElementById("sRest").innerHTML = tRest;
	if (tRest && (document.getElementById("wait").style.visibility == "visible"))
		setTimeout(dRest, 1000);
}
function setSRest() {
	document.getElementById("wait").style.left = (iScreenScale*2+10) +"px";
	document.getElementById("wait").style.top = (iScreenScale*35+10) +"px";
	document.getElementById("wait").style.fontSize = (iScreenScale*3) +"px";
}

function connexion() {
	var oScr = document.createElement("div");

	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";

	oScr.appendChild(toTitle(toLanguage("Connection", "Connexion"), -0.5));
	
	var iPseudo, iCode, oConnect;
	
	var oForm = document.createElement("form");
	oForm.style.position = "absolute";
	oForm.style.left = (iScreenScale*16) +"px";
	oForm.style.top = (iScreenScale*10) +"px";
	oForm.onsubmit = function() {
		aError.style.visibility = "hidden";
		oConnect.disabled = true;
		xhr("testcode.php", "pseudo="+ iPseudo.value +"&code="+ iCode.value,  function(reponse) {
			if (!reponse || isNaN(reponse))
				return false;
			var rep = reponse*1;
			if (rep) {
				identifiant = rep;
				mPseudo = iPseudo.value;
				mCode = iCode.value;
				oScr.innerHTML = "";
				oContainer.removeChild(oScr);
				selectPlayerScreen();
			}
			else {
				aError.style.visibility = "visible";
				oConnect.disabled = false;
			}
			return true;
		});
		return false;
	}
	var oTable = document.createElement("table");
	oTable.border = 2;
	oTable.setAttribute("cellpadding", 1);
	oTable.setAttribute("cellspacing", 2);
	var oTr1 = document.createElement("tr");
	var oTd11 = document.createElement("td");
	oTd11.style.textAlign = "right";
	var oPseudo = document.createElement("label");
	oPseudo.style.fontSize = (iScreenScale*3) +"px";
	oPseudo.setAttribute("for", "iPseudo");
	oPseudo.innerHTML = toLanguage(" &nbsp; &nbsp; Nick :", "Pseudo :");
	oTd11.appendChild(oPseudo);
	var oTd12 = document.createElement("td");
	iPseudo = document.createElement("input");
	iPseudo.type = "text";
	iPseudo.name = "iPseudo";
	iPseudo.id = "iPseudo";
	iPseudo.value = mPseudo;
	iPseudo.style.fontSize = (iScreenScale*3) +"px";
	iPseudo.style.backgroundColor = "#FE7";
	oTd12.appendChild(iPseudo);
	oTr1.appendChild(oTd11);
	oTr1.appendChild(oTd12);
	var oTr2 = document.createElement("tr");
	var oTd21 = document.createElement("td");
	oTd21.style.textAlign = "right";
	var oCode = document.createElement("label");
	oCode.style.fontSize = (iScreenScale*3) +"px";
	oCode.setAttribute("for", "iCode");
	oCode.innerHTML = "Code :";
	oTd21.appendChild(oCode);
	var oTd22 = document.createElement("td");
	iCode = document.createElement("input");
	iCode.type = "password";
	iCode.name = "iCode";
	iCode.id = "iCode";
	iCode.value = mCode;
	iCode.style.fontSize = (iScreenScale*3) +"px";
	iCode.style.backgroundColor = "#FE7";
	oTd22.appendChild(iCode);
	oTr2.appendChild(oTd21);
	oTr2.appendChild(oTd22);
	var oTr3 = document.createElement("tr");
	var oTd3 = document.createElement("td");
	oTd3.setAttribute("colspan", 2);
	oTd3.style.textAlign = "center";
	oConnect = document.createElement("input");
	oConnect.type = "submit";
	oConnect.style.fontSize = (iScreenScale*4) +"px";
	oConnect.value = toLanguage("Submit", "Valider");
	oTd3.appendChild(oConnect);
	oTr3.appendChild(oTd3);
	oTable.appendChild(oTr1);
	oTable.appendChild(oTr2);
	oTable.appendChild(oTr3);
	oForm.appendChild(oTable);
	oScr.appendChild(oForm);
	
	var aError = document.createElement("div");
	aError.style.color = "red";
	aError.style.fontSize = (iScreenScale*2) +"pt";
	aError.style.position = "absolute";
	aError.style.left = (iScreenScale*21) +"px";
	aError.style.top = (iScreenScale*31) +"px";
	aError.innerHTML = toLanguage("Incorrect nick or password", "Pseudo ou mot de passe incorrect");
	aError.style.visibility = "hidden";
	oScr.appendChild(aError);
	
	var aInscription = document.createElement("a");
	aInscription.style.color = "white";
	aInscription.style.fontSize = (iScreenScale*2) +"pt";
	aInscription.style.position = "absolute";
	aInscription.style.left = (iScreenScale*20) +"px";
	aInscription.style.top = (iScreenScale*35) +"px";
	aInscription.innerHTML = toLanguage("Register", "Inscription");
	aInscription.setAttribute("href", "inscription.php" + ((course=="BB")?"?battle":""));
	oScr.appendChild(aInscription);
	
	var eClassement = document.createElement("a");
	eClassement.style.color = "white";
	eClassement.style.fontSize = (iScreenScale*2) +"pt";
	eClassement.style.position = "absolute";
	eClassement.style.left = (iScreenScale*45) +"px";
	eClassement.style.top = (iScreenScale*35) +"px";
	eClassement.innerHTML = toLanguage("Ranking", "Classement");
	eClassement.setAttribute("href", "bestscores.php" + ((course=="BB")?"?battle":""));
	oScr.appendChild(eClassement);
	
	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Back", "Retour");
	oPInput.style.fontSize = (2*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (2*iScreenScale)+"px";
	oPInput.style.top = (35*iScreenScale)+"px";
	oPInput.onclick = quitter;
	oScr.appendChild(oPInput);
	
	oContainer.appendChild(oScr);

	updateMenuMusic(0);
}
function mapNameOf(mScreenScale, mID) {
	var sMapName = lCircuits[mID];
	var oMapName = document.createElement("div");
	var mapFS = isCup ? Math.min(Math.max(9/Math.sqrt(sMapName.length), 1.4), 4) : 2.1;
	oMapName.style.fontSize = Math.round(mScreenScale*mapFS) +"px";
	oMapName.style.width = (26*mScreenScale) +"px";
	oMapName.style.bottom = -Math.round(mScreenScale/2) + "px";
	oMapName.className = "mapname";
	oMapName.style.textAlign = "center";
	oMapName.innerHTML = sMapName;
	return oMapName;
}
function addOption(pID, pValue, vID, vName, vValue, vDefaut) {
	document.getElementById(pID).innerHTML = pValue.replace(/ /g,"&nbsp;");
	document.getElementById(vID).innerHTML = "";
	var oSelect = document.createElement("select");
	oSelect.name = vName;
	var optionIndex;
	for (var i=0;i<vValue.length;i++) {
		var oOption = document.createElement("option");
		var nValue = vValue[i][0];
		oOption.value = nValue;
		oOption.innerHTML = vValue[i][1];
		if (nValue == vDefaut)
			optionIndex = i;
		oSelect.appendChild(oOption);
	}
	oSelect.selectedIndex = optionIndex;
	document.getElementById(vID).appendChild(oSelect);
}
function optionOf(vName) {
	return formulaire ? formulaire.elements[vName].value*1 : baseOptions[vName];
}

function toLanguage(english, french) {
	return language ? english:french;
}
function toPlace(place) {
	var term;
	if (language) {
		switch (place) {
		case 1 :
			term = "st";
			break;
		case 2 :
			term = "nd";
			break;
		case 3 :
			term = "rd";
			break;
		default :
			term = "th";
		}
	}
	else
		term = (place!=1) ? "e":"er";
	return place +"<sup>"+ term +"</sup>";
}
function toTitle(text, top) {
	var oTitle = document.createElement("div");
	oTitle.style.width = (iWidth*iScreenScale)+"px";
	oTitle.style.fontSize = Math.round(8*iScreenScale)+"px";
	oTitle.style.fontWeight = "normal";
	oTitle.style.position = "absolute";
	oTitle.style.left = "0px";
	oTitle.style.top = Math.round(top*iScreenScale)+"px";
	oTitle.style.textAlign = "center";
	oTitle.style.color = "yellow";
	oTitle.innerHTML = text;
	oTitle.style.fontFamily = "Tahoma";
	return oTitle;
}
function toPerso(sPerso) {
	if (isCustomPerso(sPerso))
		return customPersos[sPerso].name;
	if (language) {
		if (sPerso == "maskass")
			return "shy guy";
		if (sPerso == "skelerex")
			return "dry bones";
		if (sPerso == "harmonie")
			return "rosalina";
		if (sPerso == "roi_boo")
			return "king boo";
		if (sPerso == "frere_marto")
			return "hammer bro";
	}
	else {
		if (sPerso == "frere_marto")
			return "frère marto";
	}
	sPerso = sPerso.replace(/_/g, " ");
	return sPerso;
}

if (pause) {
	formulaire.screenscale.disabled = false;
	formulaire.quality.disabled = false;
	formulaire.music.disabled = false;
	formulaire.sfx.disabled = false;
	selectMapScreen();
}
else {
	addOption("pQuality", toLanguage("Quality","Qualit&eacute;"),
	"vQuality", "quality", [
		[4, toLanguage("Low","Inf&eacute;rieure")],
		[2, toLanguage("Medium","Moyenne")],
		[1, toLanguage("High","Sup&eacute;rieure")]
	], iQuality);
	addOption("pSize", toLanguage("Screen Size","Taille de l'&eacute;cran"),
	"vSize", "screenscale", [
		[4, toLanguage("Very small","Tr&egrave;s petite")],
		[6, toLanguage("Small","Petite")],
		[8, toLanguage("Medium","Moyenne")],
		[10, toLanguage("Large","Large")],
		[12, toLanguage("Very large","Tr&egrave;s large")]
	], iScreenScale);
	addOption("pMusic", toLanguage("Music","Musique"),
	"vMusic", "music", [
		[0, toLanguage("Off","D&eacute;sactiv&eacute;e")],
		[1, toLanguage("On","Activ&eacute;e")]
	], bMusic);
	addOption("pSfx", toLanguage("Sound effects","Bruitages"),
	"vSfx", "sfx", [
		[0, toLanguage("Off","D&eacute;sactiv&eacute;s")],
		[1, toLanguage("On","Activ&eacute;s")]
	], iSfx);
	if (mId)
		selectPlayerScreen();
	else
		connexion();
	formulaire = document.forms.modes;
	formulaire.quality.onchange = function() {
		var iValue = parseInt(this.item(this.selectedIndex).value);
		MarioKartControl.setQuality(iValue);
	}
	formulaire.screenscale.onchange = function() {
		var iValue = parseInt(this.item(this.selectedIndex).value);
		MarioKartControl.setScreenScale(iValue);
	}
	formulaire.music.onchange = function() {
		var iValue = parseInt(this.item(this.selectedIndex).value);
		MarioKartControl.setMusic(iValue);
	}
	formulaire.sfx.onchange = function() {
		var iValue = parseInt(this.item(this.selectedIndex).value);
		MarioKartControl.setSfx(iValue);
	}
}
function isMobile() {
	return navigator.userAgent.match(/Android/i) || navigator.userAgent.match(/webOS/i) || navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPad/i) || navigator.userAgent.match(/iPod/i) || navigator.userAgent.match(/BlackBerry/i);
}
function applyButtonCode(action,keyData) {
	var keycodes = keyData.split(",");
	for (var i=0;i<keycodes.length;i++) {
		if (keycodes[i] == 17)
			oButtonCtrled = (action == "onkeydown");
		document[action]({"keyCode":parseInt(keycodes[i]),"ctrlKey":oButtonCtrled,"altKey":false});
	}
}
var oButtonCtrled = false;
function onButtonTouch(e) {
	e.preventDefault();
	this.style.backgroundColor = "#603";
	navigator.vibrate(30);
	var keycode = this.dataset.key;
	applyButtonCode("onkeydown", keycode);
	var that = this;
	clearInterval(this.handler);
	this.handler = setTimeout(function() {
		that.handler = setInterval(function(){document.onkeydown({"keyCode":parseInt(keycode),"ctrlKey":oButtonCtrled,"altKey":false})},100);
	}, 500);
	return false;
}
function onButtonPress(e) {
	this.style.backgroundColor = "";
	clearInterval(this.handler);
	applyButtonCode("onkeyup", this.dataset.key);
}
var virtualButtonW = 60, virtualButtonH = 50;
function addButton(lettre, key, x, y, w, h, fs) {
	w = (w || 1)*virtualButtonW;
	h = (h || 1)*virtualButtonH;
	var oButton = document.createElement("button");
	oButton.style.position = "absolute";
	oButton.style.left = Math.round(x*virtualButtonW*1.2) + "px";
	oButton.style.top = Math.round(y*virtualButtonH*1.3) + "px";
	oButton.style.width = w + "px";
	oButton.style.height = h + "px";
	oButton.style.textAlign = "center";
	oButton.style.padding = "0px";
	if (fs)
		oButton.style.fontSize = fs+"px";
	oButton.innerHTML = lettre;
	oButton.dataset.key = key;
	oButton.ontouchstart = onButtonTouch;
	oButton.ontouchend = onButtonPress;
	document.getElementById("virtualkeyboard").appendChild(oButton);
	return oButton;
}
if (!window.turnEvents) {
	if (isMobile()) {
		navigator.vibrate = navigator.vibrate || navigator.webkitVibrate || navigator.mozVibrate || navigator.msVibrate || function(){};
		addButton(' <span style="position:absolute;left:8px;top:-5px">\u2191</span><span style="position:absolute;right:6px;bottom:8px;font-size:10px;text-align:right">'+(language?'+ Jump<br/>Drift':'+ Saut<br/>Dérapage')+'</span>',[38,17], 0,0);
		addButton(" \u2191 ",38, 1,0);
		addButton("Obj",32, 2,0, null,null, 25);
		addButton("\u275A\u275A",80, 3,0, null,null, 25);
		document.getElementById("virtualkeyboard").appendChild(document.createElement("br"));
		document.getElementById("virtualkeyboard").appendChild(document.createElement("br"));
		var driftButton = addButton(language ? "Jump<br/>Drift":"Saut<br/>Dérapage", 17, 0,1,null,null, 11);
		addButton(" \u2193 ",40, 1,1);
		addButton(" \u2190 ",37, 2,1);
		addButton(" \u2192 ",39, 3,1);
		reposKeyboard();
		document.getElementById("virtualkeyboard").ontouchstart = function(e) {
			e.preventDefault();
			return false;
		};
		document.getElementById("virtualkeyboard").style.display = "block";
		document.getElementById("commandes").style.display = "none";
	}
	window.turnEvents = true;
}
	
function setChat() {
	chatting = true;
	var oChat = document.createElement("div");
	oChat.className = "online-chat";
	oChat.style.position = "absolute";
	oChat.style.zIndex = 3;
	oChat.style.backgroundColor = "black";
	oChat.style.right = "10px";
	oChat.style.top = "5%";
	oChat.style.width = "350px";
	oChat.style.height = "90%";
	oChat.style.border = "double 4px silver";
	
	var oConnectes = document.createElement("p");
	oConnectes.style.paddingBottom = "2px";
	oConnectes.style.borderBottom = "solid 1px silver";
	var iConnectes = document.createElement("span");
	iConnectes.innerHTML = toLanguage("Online opponent(s) : ", "Adversaire(s) en ligne : ");
	oConnectes.appendChild(iConnectes);
	var jConnectes = document.createElement("span");
	jConnectes.style.color = "white";
	oConnectes.appendChild(jConnectes);
	
	var oMessages = document.createElement("div");
	oMessages.style.paddingTop = "2px";
	
	var oRepondre = document.createElement("form");
	oRepondre.style.position = "absolute";
	oRepondre.style.bottom = "0";
	oRepondre.style.left = "10px";
	var rP = document.createElement("p");
	rP.style.textAlign = "center";
	var rMessage = document.createElement("input");
	rMessage.setAttribute("size", 35);
	rMessage.type = "text";
	rMessage.name = "rMessage";
	rMessage.onkeydown = function() {
		keyDowned = false;
	};
	rMessage.onkeyup = function() {
		keyUped = false;
	};
	rMessage.style.backgroundColor = "#FE7";
	var rEnvoi = document.createElement("input");
	rEnvoi.type = "submit";
	rEnvoi.value = toLanguage("Send", "Envoyer");
	rP.appendChild(rMessage);
	rP.appendChild(rEnvoi);
	oRepondre.onsubmit = function() {
		if (rMessage.value) {
			xhr("parler.php", "msg="+encodeURIComponent(rMessage.value).replace(/\+/g, "%2B")+(course=="BB"?"&battle":""), function(reponse){return (reponse=="1")});
			rMessage.value = "";
		}
		return false;
	}
	oRepondre.appendChild(rP);
	
	oChat.appendChild(oConnectes);
	oChat.appendChild(oMessages);
	oChat.appendChild(oRepondre);

	function refreshChat() {
		if (chatting) {
			xhr("chat.php", (course=="BB"?"battle":""), function(reponse) {
				if (reponse) {
					try {
						var rCode = eval(reponse);
					}
					catch(e) {
						return false;
					}
					if (rCode != -1) {
						var noms = rCode[0];
						var sNoms = "";
						for (var i=0;i<noms.length;i++)
							sNoms += (i ? ", ":"")+noms[i];
						jConnectes.innerHTML = sNoms;
						var messages = rCode[1];
						var pMessages = oMessages.getElementsByTagName("p");
						while (pMessages.length)
							oMessages.removeChild(pMessages[0]);
						for (var i=0;i<messages.length;i++) {
							var oP = document.createElement("p");
							var sPseudo = document.createElement("span");
							sPseudo.innerHTML = messages[i][0] +" : ";
							oP.appendChild(sPseudo);
							var sMessage = document.createElement("span");
							sMessage.style.color = "white";
							sMessage.style.fontWeight = "normal";
							sMessage.innerHTML = messages[i][1];
							oP.appendChild(sMessage);
							oMessages.appendChild(oP);
						}
					}
					else
						chatting = false;
					return true;
				}
				return false;
			});
			setTimeout(refreshChat, 1000);
		}
		else
			document.body.removeChild(oChat);
	}
	refreshChat();
	
	document.body.appendChild(oChat);
}

window.MarioKartControl = {
	setQuality : function(iValue) {
		 setQuality(iValue);
	},
	setScreenScale : function(iValue) {
		 setScreenScale(iValue);
	},
	setMusic : function(iValue) {
		setMusic(iValue);
	},
	setSfx : function(iValue) {
		setSfx(iValue);
	}
};

}
var tag = document.createElement('script');
tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);