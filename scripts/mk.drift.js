var pause, chatting = false;
var aPlayers = new Array(), aPlaces = new Array(), aScores = new Array(), aTeams = new Array(), aPseudos = new Array();
var fInfos;
var formulaire;
var baseCp;
var nBasePersos, customPersos;
var selectedDifficulty;
if (typeof edittingCircuit === 'undefined') {
	var edittingCircuit = false;
}
var isOnline = (page=="OL");
var isMCups = (isCup && (NBCIRCUITS>4));
var clRuleVars = {};
var clGlobalVars;
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

var selectPerso;
if (typeof selectedTeams === 'undefined') {
	var selectedTeams = 0;
}
if (typeof challenges === 'undefined') {
	var challenges = {mcup:[],cup:[],track:[]};
}
if (typeof cupNames === 'undefined') {
	var cupNames = [];
}
//challenges["track"]["4749"].list = [challenges["track"]["4749"].list[1]];
var challengesForCircuit;

function MarioKart() {

var oMaps = listMaps();

var aAvailableMaps = new Array();
if (typeof Array.isArray === 'undefined') {
	Array.isArray = function(obj) {
		return Object.prototype.toString.call(obj) === '[object Array]';
	}
};
for (circuits in oMaps) {
	aAvailableMaps.push(circuits);
	var oMap = oMaps[circuits];
	if (!oMap.w)
		oMap.w = 512;
	if (!oMap.h)
		oMap.h = 512;
	if (!oMap.tours)
		oMap.tours = 3;
	oMap.ref = circuits.replace("map", "")*1;
	if (oMap.aipoints && oMap.aipoints[0] && !Array.isArray(oMap.aipoints[0][0]))
		oMap.aipoints = [oMap.aipoints];
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
var iRendering = optionOf("quality");
var iQuality, iSmooth;
resetQuality();
var bMusic = !!optionOf("music");
var iSfx = !!optionOf("sfx");
var gameMenu;

var refreshDatas = isOnline, finishing = false;
var destructions = new Array();
var nbNews = new Array();
var connecte = 1;
for (i=0;i<6;i++)
	destructions.push(new Array());
var aIDs = new Array();
var tnCourse = 0;
var identifiant;
if (typeof mId !== "undefined")
	identifiant = mId;
if (typeof cShared === 'undefined')
	cShared = (page=="AR" && (nid>0));
if (typeof shareLink !== "undefined") {
	if (shareLink.options) {
		if (shareLink.options.team)
			selectedTeams = 1;
	}
}
var myCircuit = (document.getElementById("changeRace") != null);


function setQuality(iValue) {
	if (bCounting) return;

	iRendering = iValue;
	resetQuality();
	
	if (bRunning)
		resetScreen();
		
	xhr("changeParam.php", "param=0&value="+ iValue, function(reponse) {
		return (reponse == 1);
	});
}
function resetQuality() {
	if (iRendering == 5) {
		iQuality = 1;
		iSmooth = false;
	}
	else {
		iQuality = iRendering;
		iSmooth = true;
	}
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

	for (var i=0;i<oContainers.length;i++) {
		var oScr = oContainers[i].firstChild;
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
	}

	reposKeyboard();
}

function reposKeyboard() {
	var virtualKeyboardW = virtualButtonW*4.8;
	var virtualKeyboardH = virtualButtonH*2.6;
	document.getElementById("virtualkeyboard").style.width = Math.round(virtualKeyboardW) +"px";
	document.getElementById("virtualkeyboard").style.height = Math.round(virtualKeyboardH) +"px";
	document.getElementById("virtualkeyboard").style.left = (iScreenScale*iWidth - virtualKeyboardW)/2 +"px";
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
		if (!clLocalVars.forcePause) {
			var pauseMusic = playSoundEffect("musics/events/pause.mp3");
			pauseMusic.className = "";
		}
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
		if (bMusic) {
			playMusicSmoothly("musics/menu/"+ (gameMenu ? "selection-remix":"main-remix") +".mp3", forceUpdate?0:undefined);
			if (!gameMenu)
				loopAfterIntro(oMusicEmbed, 60.15,54.9);
		}
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
if (isOnline && isBattle) {
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
var oPlayers = new Array();
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
var strPlayer = new Array();
var oMap;
var iDificulty = 5, iTeamPlay = selectedTeams;
var iTrajet;
var jTrajets;
var gPersos = new Array();
var gRecord;
var gSelectedPerso;
if (pause) {
	strPlayer = fInfos.player;
	oMap = oMaps["map"+fInfos.map];
	if (course != "CM")
		iDificulty = fInfos.difficulty;
	else {
		iTrajet = fInfos.my_route;
		gPersos = fInfos.perso;
		jTrajets = fInfos.cpu_route;
		gRecord = fInfos.record;
		gSelectedPerso = fInfos.selPerso;
	}
}

var oMapImg;

function resetGame(strMap) {
	oMap = oMaps[strMap];
	loadMap();
}

var oPlanDiv,oPlanDiv2, oPlanCtn,oPlanCtn2, oPlanImg,oPlanImg2;

var oPlanWidth, oPlanSize, oPlanRealSize, oCharWidth, oObjWidth, oExpWidth;
var oPlanWidth2, oPlanSize2, oCharWidth2, oObjWidth2, oExpWidth2;
var oTeamRatio, oCharRatio, oPlanRatio;
var oPlanCharacters = new Array(), oPlanObjects = new Array(), oPlanDecor = new Array(),
	oPlanFauxObjets = new Array(), oPlanBananes = new Array(), oPlanBobOmbs = new Array(),
	oPlanCarapaces = new Array(), oPlanCarapacesRouges = new Array(), oPlanCarapacesBleues = new Array(),
	oPlanEtoiles = new Array(), oPlanBillballs = new Array(), oPlanTeams = new Array();
var oPlanCharacters2 = new Array(), oPlanObjects2 = new Array(), oPlanDecor2 = new Array(),
	oPlanFauxObjets2 = new Array(), oPlanBananes2 = new Array(), oPlanBobOmbs2 = new Array(),
	oPlanCarapaces2 = new Array(), oPlanCarapacesRouges2 = new Array(), oPlanCarapacesBleues2 = new Array(),
	oPlanEtoiles2 = new Array(), oPlanBillballs2 = new Array(), oPlanTeams2 = new Array();

function posImg(elt, eltX,eltY,eltR, eltW, mapW) {
	var fRelX = -eltX/oPlanRealSize, fRelY = -eltY/oPlanRealSize;
	elt.style.transform = elt.style.WebkitTransform = elt.style.MozTransform = "translate("+ -Math.round(mapW*fRelX + eltW/2) +"px, "+ -Math.round(mapW*fRelY + eltW/2) +"px) rotate("+ Math.round(180-eltR) +"deg)";
	return elt;
}
function posImgRel(elt, eltX,eltY,eltR, eltW, mapW, relX,relY) {
	var fRelX = -eltX/oPlanRealSize, fRelY = -eltY/oPlanRealSize;
	elt.style.transform = elt.style.WebkitTransform = elt.style.MozTransform = "translate("+ (Math.round(relX)-Math.round(mapW*fRelX + eltW/2)) +"px, "+ (Math.round(relY)-Math.round(mapW*fRelY + eltW/2)) +"px) rotate("+ Math.round(180-eltR) +"deg)";
	return elt;
}
function setPlanPos() {
	var oPlayer = oPlayers[0];
	var fRotation = Math.round(oPlayer.rotation-180);
	var fCosR = direction(1,fRotation), fSinR = direction(0,fRotation);
	function createObject(elts, src, eltW, iPlanCtn) {
		var res = document.createElement("img");
		res.src = "images/map_icons/"+ src +".png";
		res.style.position = "absolute";
		res.style.width = eltW;
		res.className = "pixelated";
		iPlanCtn.appendChild(res);
		return res;
	}
	function setObject(elt, eltX,eltY, eltW,mapW, iTeam,hallowSize) {
		posImg(elt, eltX,eltY,oPlayer.rotation, eltW,mapW);
		if ((iTeam >= 0) && (elt.team != iTeam)) {
			var iColor = iTeam ? "red":"blue";
			elt.team = iTeam;
			elt.style.background = "radial-gradient(ellipse at center, "+ iColor +" 0%,transparent "+hallowSize+"%)";
		}
		return elt;
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
			var updatePos = true;
			if (aKarts[i].loose) {
				if (isOnline || (aKarts[i] == oPlayer))
					iPlanCharacters[i].style.opacity = 0.25;
				else {
					iPlanCharacters[i].style.display = "none";
					if (iTeamPlay && (iCharWidth==oCharWidth)) {
						oPlanTeams[i].style.display = "none";
						oPlanTeams2[i].style.display = "none";
					}
					updatePos = false;
				}
			}
			if (updatePos) {
				var iCharR = aKarts[i].billball ? 1.5:aKarts[i].size;
				var iCharW = Math.round(iCharWidth*iCharR);
				iPlanCharacters[i].style.width = iCharW +"px";
				posImg(iPlanCharacters[i], aKarts[i].x,aKarts[i].y,aKarts[i].rotation-aKarts[i].tourne*360/21, iCharW, iMapW);
				if (iTeamPlay && (iCharWidth==oCharWidth)) {
					var iCharW2 = Math.round(oCharWidth2*iCharR);
					var iTeamW = Math.round(oTeamWidth*iCharR);
					var iTeamW2 = Math.round(oTeamWidth2*iCharR);
					posImgRel(oPlanTeams[i],aKarts[i].x,aKarts[i].y, Math.round(oPlayer.rotation), iCharW,oPlanSize, (iCharW-iTeamW)/2,(iCharW-iTeamW)/2);
					oPlanTeams[i].style.width = iTeamW +"px";
					oPlanTeams[i].style.height = iTeamW +"px";
					posImgRel(oPlanTeams2[i],aKarts[i].x,aKarts[i].y, Math.round(oPlayer.rotation), iCharW2,oPlanSize2, (iCharW2-iTeamW2)/2,(iCharW2-iTeamW2)/2);
					oPlanTeams2[i].style.width = iTeamW2 +"px";
					oPlanTeams2[i].style.height = iTeamW2 +"px";
				}
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
		setObject(oPlanFauxObjets[i],fauxobjets[i][3],fauxobjets[i][4], oObjWidth,oPlanSize, fauxobjets[i][2],200);
		setObject(oPlanFauxObjets2[i],fauxobjets[i][3],fauxobjets[i][4], oObjWidth2,oPlanSize2, fauxobjets[i][2],200);
		oPlanFauxObjets[i].style.zIndex = oPlanFauxObjets2[i].style.zIndex = 2;
	}
	syncObjects(oPlanBananes,bananes,"banane", oObjWidth,oPlanCtn);
	syncObjects(oPlanBananes2,bananes,"banane", oObjWidth2,oPlanCtn2);
	for (var i=0;i<bananes.length;i++) {
		setObject(oPlanBananes[i],bananes[i][3],bananes[i][4], oObjWidth,oPlanSize, bananes[i][2],100);
		setObject(oPlanBananes2[i],bananes[i][3],bananes[i][4], oObjWidth2,oPlanSize2, bananes[i][2],100);
		oPlanBananes[i].style.zIndex = oPlanBananes2[i].style.zIndex = 2;
	}

	function getExplosionSrc(src,team) {
		switch (team) {
		case 0:
			src = "explosionB";
			break;
		case 1:
			src = "explosionR";
			break;
		}
		return "images/map_icons/"+src+".png";
	}

	function setBobombPos(iPlanBobOmbs, iObjWidth,iPlanCtn, iPlanSize, iExpWidth) {
		syncObjects(iPlanBobOmbs,bobombs,"bob-omb", iObjWidth,iPlanCtn);
		for (var i=0;i<bobombs.length;i++) {
			if (bobombs[i][8] <= 0) {
				posImg(iPlanBobOmbs[i], bobombs[i][3],bobombs[i][4],Math.round(oPlayer.rotation), iExpWidth,iPlanSize).src = getExplosionSrc("explosion",bobombs[i][2]);
				iPlanBobOmbs[i].style.width = iExpWidth +"px";
				iPlanBobOmbs[i].style.opacity = Math.max(1+bobombs[i][8]/10, 0);
				iPlanBobOmbs[i].style.background = "";
			}
			else
				setObject(iPlanBobOmbs[i],bobombs[i][3],bobombs[i][4], iObjWidth,iPlanSize, bobombs[i][2],100).style.zIndex = 2;
		}
	}
	setBobombPos(oPlanBobOmbs, oObjWidth,oPlanCtn, oPlanSize, oExpWidth);
	setBobombPos(oPlanBobOmbs2, oObjWidth2,oPlanCtn2, oPlanSize2, oExpWidth2);

	syncObjects(oPlanCarapaces,carapaces,"carapace", oObjWidth,oPlanCtn);
	syncObjects(oPlanCarapaces2,carapaces,"carapace", oObjWidth2,oPlanCtn2);
	for (var i=0;i<carapaces.length;i++) {
		var c1 = setObject(oPlanCarapaces[i],carapaces[i][3],carapaces[i][4], oObjWidth,oPlanSize, carapaces[i][2],200);
		var c2 = setObject(oPlanCarapaces2[i],carapaces[i][3],carapaces[i][4], oObjWidth2,oPlanSize2, carapaces[i][2],200);
		var red = (carapaces[i][7] < 0);
		if (red && !c1.red) {
			c1.red = 1;
			c1.src = "images/map_icons/carapace-rouge.png";
			c2.src = "images/map_icons/carapace-rouge.png";
		}
		else if (!red && c1.red) {
			c1.red = undefined;
			c1.src = "images/map_icons/carapace.png";
			c2.src = "images/map_icons/carapace.png";
		}
		c2.style.zIndex = 2;
	}

	syncObjects(oPlanCarapacesRouges,carapacesRouge,"carapace-rouge", oObjWidth,oPlanCtn);
	syncObjects(oPlanCarapacesRouges2,carapacesRouge,"carapace-rouge", oObjWidth2,oPlanCtn2);
	for (var i=0;i<carapacesRouge.length;i++) {
		setObject(oPlanCarapacesRouges[i],carapacesRouge[i][3],carapacesRouge[i][4], oObjWidth,oPlanSize, carapacesRouge[i][2],200);
		setObject(oPlanCarapacesRouges2[i],carapacesRouge[i][3],carapacesRouge[i][4], oObjWidth2,oPlanSize2, carapacesRouge[i][2],200).style.zIndex = 2;
		if (carapacesRouge[i][6])
			oPlanCarapacesRouges[i].style.zIndex = 2;
	}

	function setCarapacesBleuesPos(iPlanCarapacesBleues, iObjWidth,iPlanSize,iExpWidth,iPlanCtn) {
		syncObjects(iPlanCarapacesBleues,carapacesBleue,"carapace-bleue",iObjWidth,iPlanCtn);
		for (var i=0;i<carapacesBleue.length;i++) {
			if (carapacesBleue[i][6] <= 0) {
				posImg(iPlanCarapacesBleues[i], carapacesBleue[i][3],carapacesBleue[i][4],Math.round(oPlayer.rotation), iExpWidth,iPlanSize).src = getExplosionSrc("explosionB",carapacesBleue[i][2]);
				iPlanCarapacesBleues[i].style.width = iExpWidth +"px";
				iPlanCarapacesBleues[i].style.opacity = Math.max(1+carapacesBleue[i][6]/10, 0);
				iPlanCarapacesBleues[i].style.background = "";
			}
			else
				setObject(iPlanCarapacesBleues[i],carapacesBleue[i][3],carapacesBleue[i][4], iObjWidth,iPlanSize, carapacesBleue[i][2],200).style.zIndex = 2;
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
		setObject(oPlanEtoiles2[i],oStars[i].x,oStars[i].y, oStarWidth2,oPlanSize2).style.width = oStarWidth2+"px";
		oPlanEtoiles[i].style.zIndex = oPlanEtoiles2[i].style.zIndex = 2;
	}
	syncObjects(oPlanBillballs,oBillBalls,"billball", oObjWidth,oPlanCtn);
	syncObjects(oPlanBillballs2,oBillBalls,"billball", oObjWidth2,oPlanCtn2);
	for (var i=0;i<oBillBalls.length;i++) {
		posImg(oPlanBillballs[i],oBillBalls[i].x,oBillBalls[i].y, Math.round(oPlayer.rotation), oBBWidth,oPlanSize).style.width = oBBWidth +"px";
		posImg(oPlanBillballs2[i],oBillBalls[i].x,oBillBalls[i].y, Math.round(oPlayer.rotation), oBBWidth2,oPlanSize2).style.width = oBBWidth2 +"px";
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
	
	formulaire.screenscale.disabled = true;
	formulaire.quality.disabled = true;
	formulaire.music.disabled = true;
	formulaire.sfx.disabled = true;

	iTeamPlay = isTeamPlay();

	setSRest();
	document.body.style.cursor = "progress";
	for (var i=0;i<strPlayer.length;i++) {
		var	iScreenMore = i*(iWidth*iScreenScale+2);
		if (!pause || !fInfos.replay) {
			document.getElementById("compteur"+i).style.left = (15 + iScreenMore) + "px";
			document.getElementById("compteur"+i).style.top = iScreenScale * 36 + 8 +"px";
			document.getElementById("compteur"+i).style.fontSize = iScreenScale * 2+"pt";
			document.getElementById("compteur"+i).innerHTML = (course != "BB") ? (oMap.sections ? "Section":toLanguage("Lap","Tour")) + ' <span id="tour'+i+'">1</span>/'+ oMap.tours : '&nbsp;<img src="'+balloonSrc(aTeams[i])+'" style="width: '+(iScreenScale*2)+'" /><img src="'+balloonSrc(aTeams[i])+'" style="width: '+(iScreenScale*2)+'" /><img src="'+balloonSrc(aTeams[i])+'" style="width: '+(iScreenScale*2)+'" /><img src="'+balloonSrc(aTeams[i])+'" style="width: '+(iScreenScale*2)+'" />';
			document.getElementById("objet"+i).style.left = (iScreenMore+14) +"px";
			document.getElementById("objet"+i).style.width = iScreenScale * 9 +"px";
			document.getElementById("objet"+i).style.height = iScreenScale * 8 +"px";
			document.getElementById("objet"+i).style.visibility = "visible";
		}
		document.getElementById("temps"+i).style.left = (56*iScreenScale + iScreenMore) +"px";
		document.getElementById("temps"+i).style.fontSize = iScreenScale * 2 +"pt";
		var lakitu = document.getElementById("lakitu"+i);
		if (lakitu) {
			lakitu.style.width = iScreenScale * 9 +"px";
			lakitu.style.height = Math.round(iScreenScale*6.6) +"px";
			lakitu.style.fontSize = Math.round(iScreenScale*2.3) +"px";
		}
		getDriftImg(i).style.width = iScreenScale * 8 +"px";
		document.getElementById("drift"+i).style.left = (iScreenScale * 36 + 12 + iScreenMore) +"px";
		document.getElementById("drift"+i).style.top = Math.round(iScreenScale*32 + 10) +"px";
		getDriftImg(i).style.left = "0px";
		getDriftImg(i).style.top = "0px";
		document.getElementById("infos"+i).style.left = (10+35*iScreenScale + iScreenMore) +"px";
		document.getElementById("infos"+i).style.top = 10 + 8 * iScreenScale +"px";
		document.getElementById("infos"+i).style.fontSize = iScreenScale * 10 +"pt";
		document.getElementById("infos"+i).innerHTML = '<tr><td id="decompte'+i+'">3</td></tr>';
		document.getElementById("infoPlace"+i).style.left = (iScreenScale*58+10 + iScreenMore) +"px";
		document.getElementById("infoPlace"+i).style.top = iScreenScale * 24 + 10 +"px";
		document.getElementById("infoPlace"+i).style.width = (iScreenScale*22) +"px";
		document.getElementById("infoPlace"+i).style.fontSize = iScreenScale * 10 +"pt";
		document.getElementById("scroller"+i).style.width = iScreenScale * 8 +"px";
		document.getElementById("scroller"+i).style.height = iScreenScale * 7 +"px";
		document.getElementById("scroller"+i).style.lineHeight = iScreenScale +"px";
		document.getElementById("scroller"+i).setAttribute("width", iScreenScale * 8 +"px");
		document.getElementById("scroller"+i).setAttribute("height", iScreenScale * 7 +"px");
		document.getElementById("scroller"+i).style.top = Math.round(13+iScreenScale*0.2)+"px";
		document.getElementById("scroller"+i).style.left = Math.round(14+iScreenScale*0.5 + iScreenMore)+"px";
		document.getElementById("scroller"+i).getElementsByTagName("div")[0].style.left = Math.round(iScreenScale*0.1 + 1) +"px";
		document.getElementById("mariokartcontainer").style.top = iScreenScale * 31 + 10 +"px";

		var lObjet = iScreenScale * 8 - 3;
	}
	initMap();

	var lObjet = iScreenScale * 8 - 3;
	for (var j=0;j<document.getElementsByClassName("aObjet").length;j++)
		document.getElementsByClassName("aObjet")[j].style.width = lObjet +"px";

	removeMenuMusic();
	if (bMusic && !isOnline)
		loadMapMusic();
}
function getShapeType(oBox) {
	if ("number" === typeof(oBox[0]))
		return "rectangle";
	return "polygon";
}
function classifyByShape(shapes) {
	var res = {rectangle:[],polygon:[]};
	for (var i=0;i<shapes.length;i++)
		res[getShapeType(shapes[i])].push(shapes[i]);
	return res;
}
function initMap() {
	if (oMap.collision)
		oMap.collision = classifyByShape(oMap.collision);
	if (oMap.horspistes) {
		for (var type in oMap.horspistes)
			oMap.horspistes[type] = classifyByShape(oMap.horspistes[type]);
	}
	if (oMap.trous) {
		for (var i=0;i<4;i++) {
			var holes = {rectangle:[],polygon:[]};
			for (var j=0;j<oMap.trous[i].length;j++) {
				var hole = oMap.trous[i][j];
				if (hole.length == 6)
					hole = [[hole[0],hole[1],hole[2],hole[3]],[hole[4],hole[5]]];
				holes[getShapeType(hole[0])].push(hole);
			}
			oMap.trous[i] = holes;
		}
	}
	if (oMap.accelerateurs) {
		for (var i=0;i<oMap.accelerateurs.length;i++) {
			var oBox = oMap.accelerateurs[i];
			if (oBox[2]) {
				oBox[2]++;
				oBox[3]++;
			}
			else {
				oBox[2] = 9;
				oBox[3] = 9;
			}
		}
	}
}
var vitesse;
var time = 0;
var timer = 0;
iScreenScale = optionOf("screenscale");

var fMaxRotInc = 6;
var fTurboDriftCpt = 80, fTurboDriftCpt2 = 160;

/*function throwItem(oKart,newItem) {
	var lastItem = oKart.using[0][oKart.using[1]];
	if (lastItem) {
		lastItem.length = newItem.length;
		for (var i=2;i<lastItem.length;i++)
			lastItem[i] = newItem[i];
	}
	oKart.using = [false];
}*/
function addNewItem(kart,collection,item) {
	collection.push(item);
	if ((kart == oPlayers[0]) && clLocalVars.myItems)
		clLocalVars.myItems.push(item);
	if (item[2] != -1) {
		var hallowSize;
		switch (collection) {
		case bananes:
			hallowSize = 50;
			break;
		case carapaces:
		case carapacesRouge:
			hallowSize = 60;
			break;
		case fauxobjets:
			hallowSize = 65;
			break;
		case carapacesBleue:
			hallowSize = 60;
			break;
		case bobombs:
			hallowSize = 40;
			break;
		default:
			hallowSize = 60;
		}
		var hallowLeft = 50-hallowSize, hallowTop = 50-hallowSize;
		switch (collection) {
		case bananes:
			hallowTop += 5;
			break;
		case bobombs:
			hallowTop += 5;
			break;
		case fauxobjets:
			hallowTop -= 5;
		}
		for (var i=0;i<oPlayers.length;i++) {
			var oDiv = document.createElement("div");
			oDiv.className = "sprite-hallow";
			oDiv.style.position = "absolute";
			oDiv.style.left = hallowLeft+"%";
			oDiv.style.top = hallowTop+"%";
			oDiv.style.width = (hallowSize*2)+"%";
			oDiv.style.height = (hallowSize*2)+"%";
			oDiv.style.borderRadius = hallowSize+"%";
			oDiv.style.backgroundColor = item[2] ? "red":"blue";
			oDiv.style.opacity = 0.25;
			var oImg = item[0][i].div.firstChild;
			if (oImg)
				item[0][i].div.insertBefore(oDiv,oImg);
			else
				item[0][i].div.appendChild(oDiv);
		}
	}
}

function arme(ID, backwards) {
	var oKart = aKarts[ID];
	if (!oKart.using[0]) {
		if (oKart.roulette != 25) return;
		if (oKart == oPlayers[0])
			clLocalVars.itemsUsed = true;
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
			for (var i=0;i<strPlayer.length;i++)
				oKart.sprite[i].img.src = getStarSrc(oKart.personnage);
			if (!oKart.cpu && !oKart.etoile) {
				if (!isOnline) {
					oKart.sprite[0].img.onload = function() {
						bCounting = false;
						this.onload = undefined;
						reprendre(false);
					}
					pause = true;
					bCounting = true;
				}
				if (shouldPlayMusic(oKart) && !oPlayers[1])
					postStartMusic("musics/events/starman.mp3");
			}
			if (oKart.speedinc > 0)
				oKart.speedinc *= 5;
			oKart.protect = true;
			break;

			case "billball" :
			tpsUse = Math.max(Math.min(Math.round(distanceToFirst(oKart)/6), 120), 50);
			for (var i=0;i<strPlayer.length;i++) {
				oKart.sprite[i].img.src = "images/sprites/sprite_billball.png";
				resetSpriteHeight(oKart.sprite[i]);
			}
			if (!oKart.cpu && !isOnline) {
				oKart.sprite[0].img.onload = function() {
					bCounting = false;
					this.onload = undefined;
					reprendre(false);
				}
				pause = true;
				bCounting = true;
			}
			oKart.rotinc = 0;
			oKart.size = 2.5;
			oKart.z = 2;
			oKart.protect = true;
			oKart.champi = 0;
			resetPowerup(oKart);
			playIfShould(oKart,"musics/events/boost.mp3");
			stopDrifting(ID);
			break;

			case "megachampi" :
			tpsUse = 50;
			oKart.size = 1;
			updateDriftSize(ID);
			oKart.protect = true;
			if (!oKart.megachampi && shouldPlayMusic(oKart) && !oPlayers[1])
				postStartMusic("musics/events/megamushroom.mp3");
			break;

			case "eclair" :
			tpsUse = 100;
			for (i=0;i<aKarts.length;i++) {
				var kart = aKarts[i];
				if (!friendlyFire(kart,oKart)) {
					if (!kart.protect) {
						kart.size = 0.6;
						updateDriftSize(i);
						kart.arme = false;
						if (kart.using[0]) {
							if (kart.using[0][kart.using[1]][5])
								kart.using[0][kart.using[1]][5] = 0;
							kart.using = [false];
						}
						kart.champi = 0;
						kart.spin(20);
						kart.roulette = 0;
						stopDrifting(i);
						supprArme(i);
					}
					else
						kart.megachampi = (kart.megachampi<8 || kart.etoile ? kart.megachampi : 8);
				}
			}
			if (iSfx && !finishing && !oKart.cpu)
				playSoundEffect("musics/events/lightning.mp3");
			document.getElementById("mariokartcontainer").style.opacity = 0.7;
			break;

			case "banane" :
			oKart.using = [bananes, (bananes.length), "banane"];
			addNewItem(oKart,bananes,[new Sprite("banane"), -1, oKart.team, (oKart.x - 5 * direction(0, oKart.rotation)), (oKart.y - 5 * direction(1, oKart.rotation)), oKart.z]);
			playIfShould(oKart,"musics/events/item_store.mp3");
			break;

			case "fauxobjet" :
			oKart.using = [fauxobjets, (fauxobjets.length), "fauxobjet"];
			addNewItem(oKart,fauxobjets,[new Sprite("objet"), -1, oKart.team, (oKart.x - 5 * direction(0, oKart.rotation)), (oKart.y - 5 * direction(1, oKart.rotation)), oKart.z]);
			playIfShould(oKart,"musics/events/item_store.mp3");
			break;

			case "carapace" :
			oKart.using = [carapaces, (carapaces.length), "carapace"];
			addNewItem(oKart,carapaces,[new Sprite("carapace"), -1, oKart.team, (oKart.x - 5 * direction(0, oKart.rotation)), (oKart.y - 5 * direction(1, oKart.rotation)), oKart.z, -1, 10]);
			playIfShould(oKart,"musics/events/item_store.mp3");
			break;

			case "carapacerouge" :
			oKart.using = [carapacesRouge, (carapacesRouge.length), "carapacerouge"];
			addNewItem(oKart,carapacesRouge,[new Sprite("carapace-rouge"), -1, oKart.team, (oKart.x - 5 * direction(0, oKart.rotation)), (oKart.y - 5 * direction(1, oKart.rotation)), oKart.z, -1, -1, -1]);
			playIfShould(oKart,"musics/events/item_store.mp3");
			break;

			case "carapacebleue" :
			var cible = aKarts[aKarts.length-1].id;
			var cPlace = 1;
			for (var i=0;i<aKarts.length;i++) {
				if (aKarts[i].place == cPlace) {
					if (((aKarts[i].tours <= oMap.tours) || (course == "BB")) && !sameTeam(oKart.team,aKarts[i].team)) {
						cible = aKarts[i].id;
						i = aKarts.length;
					}
					else {
						cPlace++;
						i = -1;
					}
				}
			}
			addNewItem(oKart,carapacesBleue,[new Sprite("carapace-bleue"), -1, oKart.team, oKart.x,oKart.y, cible, 5]);
			playDistSound(oKart,"musics/events/throw.mp3",50);
			break;

			case "bobomb" :
			oKart.using = [bobombs, (bobombs.length), "bobomb"];
			addNewItem(oKart,bobombs,[new Sprite("bob-omb"), -1, oKart.team, (oKart.x - 5 * direction(0, oKart.rotation)), (oKart.y - 5 * direction(1, oKart.rotation)), oKart.z,-1,15,30]);
			playIfShould(oKart,"musics/events/item_store.mp3");
			break;
		}

		if (tpsUse)
			oKart[oKart.arme] = tpsUse;

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
			addNewItem(oKart,bananes,[new Sprite("banane"), -1, oKart.team, fPosX, fPosY, 0]);
			playIfShould(oKart,"musics/events/put.mp3");
			break;

			case "fauxobjet" :
			var decalage = 30/(oKart.speed+5);
			addNewItem(oKart,fauxobjets,[new Sprite("objet"), -1, oKart.team, posX - decalage * direction(0, oKart.rotation),posY - decalage * direction(1, oKart.rotation), 0]);
			playIfShould(oKart,"musics/events/put.mp3");
			break;

			case "carapace" :
			var oAngleView = angleShoot(oKart, backwards);
			var shiftDist = backwards?7.5:15;
			addNewItem(oKart,carapaces,[new Sprite("carapace"), -1, oKart.team, posX + shiftDist * direction(0, oAngleView),posY + shiftDist * direction(1, oAngleView),0,oAngleView,10]);
			playDistSound(oKart,"musics/events/throw.mp3",50);
			break;

			case "carapacerouge" :
			var oAngleView = angleShoot(oKart, backwards);
			if (backwards)
				addNewItem(oKart,carapaces,[new Sprite("carapace-rouge"), -1, oKart.team, posX + 7.5 * direction(0, oAngleView),posY + 7.5 * direction(1, oAngleView),0,oAngleView,-1]);
			else
				addNewItem(oKart,carapacesRouge,[new Sprite("carapace-rouge"), -1, oKart.team, posX + 15 * direction(0, oAngleView),posY + 15 * direction(1, oAngleView),0,oAngleView,oKart.id,-1]);
			playDistSound(oKart,"musics/events/throw.mp3",50);
			break;

			case "bobomb" :
			var oAngleView = angleShoot(oKart, backwards);
			if (backwards)
				addNewItem(oKart,bobombs,[new Sprite("bob-omb"), -1, oKart.team, posX+5*direction(0,oAngleView),posY+5*direction(1,oAngleView),0,oAngleView,2,42]);
			else
				addNewItem(oKart,bobombs,[new Sprite("bob-omb"), -1, oKart.team, posX,posY,0,oAngleView,15,30]);
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
		res.src = "https://www.youtube.com/embed/"+ ytId +"?"+ (autoplay ? "autoplay=1&amp;":"") +"loop=1&amp;playlist="+ ytId + "&amp;enablejsapi=1&amp;allow=autoplay";
		res.setAttribute("enablejsapi", 1);
		res.setAttribute("allow", "autoplay");
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
	return iSfx && kartIsPlayer(oKart) && !finishing && !oKart.loose;
}
function shouldPlayMusic(oKart) {
	return bMusic && kartIsPlayer(oKart) && !finishing && !oKart.loose;
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
	if (shouldPlayMusic(oKart) && !oPlayers[1])
		postResumeMusic(mapMusic, 0.9);
}
function stopMegaMusic(oKart) {
	if (shouldPlayMusic(oKart) && !oPlayers[1])
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
var mapMusic, endingMusic, endGPMusic, challengeMusic, carEngine, carEngine2, carEngine3, carDrift, carSpark;
var willPlayEndMusic = false, isEndMusicPlayed = false;
var forceStartMusic = false;
var forcePrepareEnding = false;
function loadMapMusic() {	
	startMapMusic(false);
	loadEndingMusic();
	mapMusic.blur();
	endingMusic.blur();
	if (!isMobile() && !isChatting()) {
		var oDebug = document.createElement("input");
		document.body.appendChild(oDebug);
		oDebug.focus();
		oDebug.blur();
		document.body.removeChild(oDebug);
	}
}
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
	var endingSrc = getEndingSrc(strPlayer[0]);
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
function loopAfterIntro(embed, introTime, loopTime) {
	if (embed.looper) return;
	var buffer = 0.15;
	loopTime -= buffer;
	var loopStamp = introTime+loopTime;
	embed.looper = function() {
        if (this.currentTime >= loopStamp)
            this.currentTime -= loopTime;
	};
	embed.addEventListener('timeupdate', embed.looper, false);
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
function startEndMusic() {
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
		willPlayEndMusic = true;
		setTimeout(function() {
			var oMusics = document.getElementsByClassName("gamemusic");
			var musicsToRemove = [];
			for (var i=0;i<oMusics.length;i++) {
				if (!oMusics[i].permanent)
					musicsToRemove.push(oMusics[i]);
			}
			for (var i=0;i<musicsToRemove.length;i++)
				document.body.removeChild(musicsToRemove[i]);
			if (willPlayEndMusic) {
				willPlayEndMusic = false;
				isEndMusicPlayed = true;
				unpauseMusic(endingMusic);
			}
		}, 200);
	}
	if (iSfx && course != "BB") {
		var goalSound = playSoundEffect("musics/events/goal.mp3");
		goalSound.className = "";
	}
}
function handleEndRace() {
	if (bMusic||iSfx)
		startEndMusic();
	var events = ["next_circuit"];
	challengeCheck("end_game", events);
	challengeCheck("end_gp", events);
	clGlobalVars.nbcircuits++;
}


function startGame() {

	resetScreen();
	
	if (!isOnline) {
		if (course == "BB") {
			for (var i=0;i<aPlayers.length+strPlayer.length;i++)
				aPlaces[i] = i+1;
		}
		else if (course == "CM")
			aPlaces = [1];
	}

	var cp0 = oMap.sections ? oMap.checkpoint.length-1:0;
	var rot0 = (oMap.startrotation==undefined)?180:oMap.startrotation;
	var dir0 = oMap.startdirection||0;

	for (var i=0;i<strPlayer.length;i++) {
		var oPlace = aPlaces[i];
		var oPlayer = {
			id : i,
			
			x : oMap.startposition[0],
			y : oMap.startposition[1],
			z : 0,

			personnage : strPlayer[i],

			speed : 0,
			speedinc : 0,
			heightinc : 0,

			rotation : rot0,
			rotincdir : 0,
			rotinc : 0,
			changeView : 0,

			size : 1,
			sprite : new Sprite(strPlayer[i]),
			cpu : false,
			aipoints : oMap.aipoints[0],

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
			using : [false]
		};
		if (isOnline)
			oPlayer.id = identifiant;
		if (isOnline)
			oPlayer.nick = aPseudos[i];
		if (iTeamPlay)
			oPlayer.team = aTeams[i];
		else
			oPlayer.team = -1;
		if (course != "BB") {
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
			else {
				switch (oMap.startrotation) {
				case 270 :
					oPlayer.x += oPlace * 12;
					oPlayer.y -= (oPlace%2==dir0 ? 0 : 18);
					break;
				case 0 :
					oPlayer.x -= (oPlace%2==dir0 ? 0 : 18);
					oPlayer.y -= oPlace * 12;
					break;
				case 90 :
					oPlayer.x -= oPlace * 12;
					oPlayer.y += (oPlace%2==dir0 ? 0 : 18);
					break;
				default :
					oPlayer.x += (oPlace%2==dir0 ? 0 : 18);
					oPlayer.y += oPlace * 12;
				}
			}
			oPlayer.time = 0;
			oPlayer.tours = 1;
			oPlayer.demitours = cp0;
			//oPlayer.tours = oMap.tours;
			//oPlayer.demitours = cp0 ? oMap.checkpoint.length-2:oMap.checkpoint.length-1;
			oPlayer.billball = 0;
			oPlayer.place = oPlace;
		}
		else {
			var iAI = isOnline ? oPlace-1:i;
			var iAIc = iAI%oMap.startposition.length;
			oPlayer.x = oMap.startposition[iAIc][0];
			oPlayer.y = oMap.startposition[iAIc][1];
			oPlayer.rotation = oMap.startposition[iAIc][2]*90;
			oPlayer.loose = false;
			oPlayer.ballons = [createBalloonSprite(oPlayer)];
			oPlayer.reserve = 4;
			oPlayer.place = oPlace;
		}
		oPlayer.initialPlace = oPlayer.place;
		oPlayers.push(oPlayer);
		aKarts.push(oPlayer);
	}

	for (i=0;i<aPlayers.length;i++) {
		var joueur = aPlayers[i];
		var inc = i+strPlayer.length;
		var oPlace = aPlaces[inc];
		var depart = (iDificulty-4)*2+Math.round(Math.random());
		if (course == "BB")
			depart = 2;
		var oEnemy = {
			id : inc,

			speed : 0,
			speedinc : 0.5,
			heightinc : 0,

			rotation : rot0,
			rotincdir : 0,
			rotinc : 0,

			x : oMap.startposition[0],
			y : oMap.startposition[1],
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

			cpu : !isOnline,
			aipoint : 0,
			aipoints : oMap.aipoints[0],
			maxspeed : 5.7
		};
		if (isOnline)
			oEnemy.id = aIDs[i];
		else
			oEnemy.aipoints = oMap.aipoints[inc%oMap.aipoints.length]||[];

		if (simplified) {
			switch (oMap.startdirection) {
			case -6 :
				oEnemy.x += (oPlace%2 ? 0 : 18);
				oEnemy.y += oPlace * 12;
				break;
			case -1 :
				oEnemy.x += oPlace * 12;
				oEnemy.y -= (oPlace%2 ? 0 : 18);
				break;
			case 6 :
				oEnemy.x -= (oPlace%2 ? 0 : 18);
				oEnemy.y -= oPlace * 12;
				break;
			case 1 :
				oEnemy.x -= oPlace * 12;
				oEnemy.y += (oPlace%2 ? 0 : 18);
			}
		}
		else {
			switch (oMap.startrotation) {
			case 270 :
				oEnemy.x += oPlace * 12;
				oEnemy.y -= (oPlace%2==dir0 ? 0 : 18);
				break;
			case 0 :
				oEnemy.x -= (oPlace%2==dir0 ? 0 : 18);
				oEnemy.y -= oPlace * 12;
				break;
			case 90 :
				oEnemy.x -= oPlace * 12;
				oEnemy.y += (oPlace%2==dir0 ? 0 : 18);
				break;
			default :
				oEnemy.x += (oPlace%2==dir0 ? 0 : 18);
				oEnemy.y += oPlace * 12;
			}
		}
		if (isOnline)
			oEnemy.nick = aPseudos[inc];
		if (iTeamPlay)
			oEnemy.team = aTeams[inc];
		else
			oEnemy.team = -1;
		if ((oEnemy.team != -1) || oEnemy.nick)
			oEnemy.marker = createMarker(oEnemy);
		if (course != "BB") {
			oEnemy.tours = 1;
			oEnemy.demitours = cp0;
			//oEnemy.tours = oMap.tours;
			//oEnemy.demitours = oMap.checkpoint.length-1;
			oEnemy.billball = 0;
			if (!isOnline) {
				oEnemy.speed = (depart<2) ? 0 : 5.7;
				oEnemy.tourne = depart ? 0 : 42;
			}
			oEnemy.place = oPlace;
		}
		else {
			var corDir = [-6, -1, 6, 1];
			var iAI = isOnline ? oPlace-1:inc;
			var iAIc = iAI%oMap.startposition.length;
			oEnemy.x = oMap.startposition[iAIc][0];
			oEnemy.y = oMap.startposition[iAIc][1];
			oEnemy.rotation = oMap.startposition[iAIc][2]*90;
			oEnemy.loose = false;
			oEnemy.aipoint = oMap.startposition[iAIc][3];
			if (simplified)
				oEnemy.lastAI = oMap.startposition[iAIc][3] + corDir[oMap.startposition[iAIc][2]];
			oEnemy.ballons = [createBalloonSprite(oEnemy)];
			oEnemy.reserve = 4;
			oEnemy.place = iAI+1;
			if (!simplified)
				oEnemy.speed = oEnemy.maxspeed;
		}
		oEnemy.initialPlace = oEnemy.place;

		aKarts.push(oEnemy);
	}
	if (oMap.decor) {
		for (var i=1;i<oMap.decor.length;i++)
			oMap.decor[i][2] = new Sprite(oMap.decor[0]);
	}
	function spinKart(nb) {
		if (!this.tourne) {
			if (isOnline)
				playIfShould(this,"musics/events/spin.mp3");
			else
				playDistSound(this,"musics/events/spin.mp3",(course=="BB")?80:50);
		}
		this.tourne = nb;
	}
	function fallKart() {
		return tombe(this.x+this.speed*direction(0,this.rotation),this.y+this.speed*direction(1,this.rotation));
	}
	function exitKart() {
		var fNewPosX = Math.round(this.x + this.speed * direction(0, this.rotation));
		var fNewPosY = Math.round(this.y + this.speed * direction(1, this.rotation));
		return ralenti(fNewPosX,fNewPosY);
	}
	for (var i=0;i<aKarts.length;i++) {
		aKarts[i].spin = spinKart;
		aKarts[i].falling = fallKart;
		aKarts[i].exiting = exitKart;
		for (var j=0;j<strPlayer.length;j++) {
			(function(sprite) {
				sprite.img.onload = function() {
					sprite.h = this.naturalHeight;
					delete this.onload;
				}
			})(aKarts[i].sprite[j]);
		}
	}

	if (course != "CM") {
		for (var i=0;i<oMap.arme.length;i++)
			oMap.arme[i][2] = 0;

		for (var i=0;i<oPlayers.length;i++) {
			document.getElementById("infoPlace"+i).innerHTML = toPlace(oPlayers[i].place);
			document.getElementById("infoPlace"+i).style.display = "block";
			var oColor = (oPlayers[i].team != -1) ? (oPlayers[i].team ? "#F96":"#69F"):"";
			document.getElementById("infoPlace"+i).style.color = oColor;
			if (course != "BB")
				document.getElementById("compteur"+i).style.color = oColor;
		}
	}
	else {
		oMap.arme = [];
		aKarts[0].arme = "champi";
		aKarts[0].roulette = 25;
		for (var i=0;i<gPersos.length;i++) {
			var gPerso = gPersos[i];
			aKarts.push({
				speed : (depart<2) ? 0 : 5.7,
				speedinc : 0.5,
				heightinc : 0,

				rotation : 180,
				rotincdir : 0,
				rotinc : 0,

				x : aKarts[0].x,
				y : aKarts[0].y,
				z : 0,

				size : 1,
				personnage : gPerso,
				sprite : new Sprite(gPerso),

				tourne : 0,
				tombe : 0,
				protect : false,

				roulette : 0,
				arme : false,

				champi : 0,
				etoile : 0,
				megachampi : 0,
				using : [false],

				cpu : false,
				aipoint : 0,
				aipoints : oMap.aipoints[0],
				maxspeed : 5.7,

				place : 1
			});
			aKarts[aKarts.length-1].sprite[0].div.style.opacity = 0.5;
		}
		document.getElementById("roulette0").innerHTML = '<img alt="."class="pixelated" src="images/items/champi.gif" style="width: '+ Math.round(iScreenScale * 8 - 3)+'px;" />';
	}
	gameControls = getGameControls();

	challengesForCircuit = {
		"end_game": [],
		"each_frame": [],
		"each_hit": [],
		"each_kill": [],
		"end_gp": []
	};
	if (!clGlobalVars) {
		clGlobalVars = {
			nbcircuits: 0
		};
	}
	var circuitId = getMapId(oMap);
	addCreationChallenges("track",circuitId);
	if (isCup) {
		if (isMCups) {
			var cupId = cupIDs[Math.floor((oMap.ref-1)/4)];
			addCreationChallenges("cup",cupId);
			for (var cupId in challenges["mcup"])
				addCreationChallenges("mcup",cupId);
		}
		else {
			for (var cupId in challenges["cup"])
				addCreationChallenges("cup",cupId);
		}
	}
	var challengesUsed = {};
	for (var verifType in challengesForCircuit) {
		var challengesForType = challengesForCircuit[verifType];
		for (var i=0;i<challengesForType.length;i++) {
			var challenge = challengesForType[i];
			challengesUsed[challenge.id] = true;
		}
	}
	for (var cid in clRuleVars) {
		if (!challengesUsed[cid])
			delete clRuleVars[cid];
	}
	reinitLocalVars();

	if (strPlayer.length == 1) {
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
		oPlanDiv.style.left = (15 + iScreenScale*iWidth) +"px";
		oPlanDiv.style.top = "9px";
		oPlanDiv.style.width = oPlanWidth +"px";
		oPlanDiv.style.height = oPlanWidth +"px";
		oPlanDiv.style.overflow = "hidden";

		oPlanDiv2 = document.createElement("div");
		oPlanDiv2.style.backgroundColor = "rgb("+ oMap.bgcolor +")";
		oPlanDiv2.style.position = "absolute";
		oPlanDiv2.style.left = (15 + iScreenScale*iWidth) +"px";
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
		oTeamWidth = Math.round(iScreenScale*2.4);
		oBBWidth = iScreenScale*2;
		oStarWidth2 = Math.round(iScreenScale*1.5);
		oObjWidth = Math.round(iScreenScale*1.5);
		oExpWidth = iScreenScale*7;

		oCharWidth2 = Math.round(oCharRatio*oCharWidth);
		oTeamWidth2 = Math.round(oCharRatio*oTeamWidth);
		oBBWidth2 = Math.round(oCharRatio*oBBWidth);
		oObjWidth2 = Math.round(oPlanRatio*oObjWidth);
		oExpWidth2 = Math.round(oPlanRatio*oExpWidth);
		if (iTeamPlay) {
			for (var i=0;i<aTeams.length;i++) {
				var oTeam = document.createElement("div");
				oTeam.style.position = "absolute";
				oTeam.style.zIndex = 1;
				oTeam.style.width = oTeamWidth +"px";
				oTeam.style.height = oTeamWidth +"px";
				oTeam.style.borderRadius = Math.round(oTeamWidth/2) +"px";
				oTeam.style.opacity = 0.5;
				oTeam.style.backgroundColor = aTeams[i] ? "red":"blue";
				oPlanTeams.push(oTeam);
				oPlanCtn.appendChild(oTeam);

				var oTeam2 = document.createElement("div");
				oTeam2.style.position = "absolute";
				oTeam2.style.zIndex = 1;
				oTeam2.style.width = oTeamWidth2 +"px";
				oTeam2.style.height = oTeamWidth2 +"px";
				oTeam2.style.borderRadius = Math.round(oTeamWidth2/2) +"px";
				oTeam2.style.opacity = 0.5;
				oTeam2.style.backgroundColor = aTeams[i] ? "red":"blue";
				oPlanTeams2.push(oTeam2);
				oPlanCtn2.appendChild(oTeam2);
			}
		}
		for (var i=0;i<aKarts.length;i++) {
			var oCharacter = document.createElement("img");
			oCharacter.style.position = "absolute";
			oCharacter.style.zIndex = 1;
			oCharacter.style.width = oCharWidth +"px";
			oCharacter.src = getMapIcSrc(aKarts[i].personnage);
			oCharacter.className = "pixelated";
			oPlanCharacters.push(oCharacter);

			var oCharacter2 = document.createElement("img");
			oCharacter2.style.position = "absolute";
			oCharacter2.style.zIndex = 1;
			oCharacter2.style.width = oCharWidth2 +"px";
			oCharacter2.src = getMapIcSrc(aKarts[i].personnage);
			oCharacter2.className = "pixelated";
			oPlanCharacters2.push(oCharacter2);
		}
		if ((course == "CM") && (oPlanCharacters.length > 1)) {
			for (var i=0;i<oPlanCharacters.length;i++)
				oPlanCtn.appendChild(oPlanCharacters[i]);
			for (var i=0;i<oPlanCharacters2.length;i++)
				oPlanCtn2.appendChild(oPlanCharacters2[i]);
			oPlanCharacters[1].style.opacity = 0.5;
			oPlanCharacters2[1].style.opacity = 0.5;
		}
		else {
			for (var i=oPlanCharacters.length-1;i>=0;i--)
				oPlanCtn.appendChild(oPlanCharacters[i]);
			for (var i=oPlanCharacters2.length-1;i>=0;i--)
				oPlanCtn2.appendChild(oPlanCharacters2[i]);
		}

		for (var i=0;i<oMap.arme.length;i++) {
			fSprite = oMap.arme[i];
			fSprite[2] = new Sprite("objet");

			var oObject = document.createElement("img");
			oObject.src = "images/map_icons/objet.png";
			oObject.style.position = "absolute";
			oObject.style.display = "none";
			oObject.style.width = oObjWidth;
			oObject.className = "pixelated";
			posImg(oObject, fSprite[0],fSprite[1],Math.round(oPlayer.rotation), oObjWidth, oPlanSize);
			oPlanCtn.appendChild(oObject);
			oPlanObjects.push(oObject);

			var oObject2 = document.createElement("img");
			oObject2.src = "images/map_icons/objet.png";
			oObject2.style.position = "absolute";
			oObject2.style.display = "none";
			oObject2.style.width = oObjWidth2;
			oObject2.className = "pixelated";
			posImg(oObject2, fSprite[0],fSprite[1],Math.round(oPlayer.rotation), oObjWidth2, oPlanSize2);
			oPlanCtn2.appendChild(oObject2);
			oPlanObjects2.push(oObject2);
		}
		oPlanDiv.appendChild(oPlanCtn);
		document.body.appendChild(oPlanDiv);
		oPlanDiv2.appendChild(oPlanCtn2);
		document.body.appendChild(oPlanDiv2);
		setPlanPos();
	}

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

	var oCounts = new Array();
	for (var i=0;i<strPlayer.length;i++) {
		oCounts[i] = [document.createElement("div"), new Image()];
		oCounts[i][0].style.position = "absolute";
		oCounts[i][0].style.width = (12*iScreenScale)+"px";
		oCounts[i][0].style.height = (12*iScreenScale)+"px";
		oCounts[i][0].style.overflow = "hidden";
		oCounts[i][0].style.top = (4*iScreenScale)+"px";
		oCounts[i][0].style.left = (8*iScreenScale)+"px";

		oCounts[i][1].src = "images/lakitu_depart.png";
		oCounts[i][1].style.position = "absolute";
		oCounts[i][1].style.left = "0px";
		oCounts[i][1].height = 12*iScreenScale;
		oCounts[i][1].className = "pixelated";

		oCounts[i][0].appendChild(oCounts[i][1]);
		oContainers[i].appendChild(oCounts[i][0]);

		oCounts[i].scrollLeft = 0;
	}

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
		if (!isMobile() && !isChatting()) {
			var oDebug = document.createElement("input");
			document.body.appendChild(oDebug);
			oDebug.focus();
			oDebug.blur();
			document.body.removeChild(oDebug);
		}
	}

	var willReplay = (fInfos && fInfos.replay);
	var fncCount = function() {
		if (iCntStep) {
			for (var i=0;i<strPlayer.length;i++)
				oCounts[i][0].scrollLeft = iCntStep * 12 * iScreenScale;
			if (iCntStep < 3) {
				for (var i=0;i<strPlayer.length;i++) {
					document.getElementById("decompte"+i).innerHTML--;
					oPlayers[i].speed += oPlayers[i].speedinc;
				}
				if (bMusic || iSfx) {
					countDownMusic.currentTime = 0;
					countDownMusic.play();
				}
			}
			else {
				for (var i=0;i<strPlayer.length;i++) {
					document.getElementById("infos"+i).innerHTML = '<tr><td>'+ toLanguage('&nbsp; &nbsp; GO !', 'PARTEZ !') +'</td></tr>';
					document.getElementById("infos"+i).style.left = (10+20*iScreenScale + i*(iWidth*iScreenScale+2)) + "px";
					document.getElementById("infos"+i).style.fontSize = iScreenScale * 8 + "pt";
					if (oPlayers[i].speed == 1)
						oPlayers[i].speed = 11;
					else if (oPlayers[i].speed > 1) {
						oPlayers[i].spin(42);
						oPlayers[i].speed = 0;
						oPlayers[i].speedinc = 0;
					}
				}
				if (!isOnline && course == "BB") {
					for (var i=strPlayer.length;i<aKarts.length;i++) {
						var oKart = aKarts[i];
						var f = 1+Math.round(Math.random());
						for (j=0;j<f;j++) {
							addNewBalloon(oKart);
							oKart.reserve--;
						}
					}
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
						var stillRacing = (kartIsPlayer(oPlayers[0]) && !oPlayers[0].loose && !finishing) || willReplay;
						for (var i=0;i<strPlayer.length;i++) {
							oContainers[i].removeChild(oCounts[i][0]);
							if (stillRacing||i)
								document.getElementById("infos"+i).style.visibility = "hidden";
						}
						if (stillRacing) {
							document.getElementById("infos0").style.top = iScreenScale * 7 + 10 +"px";
							document.getElementById("infos0").style.left = Math.round(iScreenScale*25+10 + (strPlayer.length-1)/2*(iWidth*iScreenScale+2)) +"px";
							document.getElementById("infos0").style.fontSize = iScreenScale * 4 +"pt";
							document.getElementById("infos0").innerHTML = '<tr><td><input type="button" style="font-size: '+ iScreenScale*3 +'pt; width: 100%;" value=" &nbsp; '+ toLanguage('  RESUME  ', 'REPRENDRE') +' &nbsp; " id="reprendre" /></td></tr><tr><td'+ (course != "CM" ? ' style="font-size: '+ iScreenScale * 10 +'px;">&nbsp;' : ' style="font-size: '+ (iScreenScale * 2) +'px">&nbsp;</td></tr><tr><td><input type="button" id="recommencer" value=" &nbsp; '+ toLanguage('RETRY', 'R&Eacute;&Eacute;SSAYER') +' &nbsp; " style="font-size: '+ iScreenScale*3 +'pt; width: 100%;" /></td></tr><tr><td style="font-size: '+ (iScreenScale * 2) +'px">&nbsp;') +'</td></tr><tr><td><input type="button" id="quitter" value=" &nbsp; '+ toLanguage('QUIT', 'QUITTER') +' &nbsp; " style="font-size: '+ iScreenScale*3 +'pt; width: 100%;" /></td></tr>';
							document.getElementById("reprendre").onclick = reprendre;
							if (course == "CM")
								document.getElementById("recommencer").onclick = function() {
									pause = true;
									removeGameMusics();
									oContainers[0].innerHTML = "";
									document.getElementById("compteur0").innerHTML = "";
									document.getElementById("temps0").innerHTML = "";
									document.getElementById("objet0").style.visibility = "hidden";
									fInfos = {
										player:strPlayer,
										map:oMap.ref,
										difficulty:iDificulty,
										perso:gPersos,
										cpu_route:jTrajets,
										record:gRecord
									};
									document.getElementById("infos0").style.visibility = "hidden";
									document.getElementById("infos0").style.opacity = 0.8;
									document.getElementById("infos0").style.color = "#FF9900";
									document.getElementById("infos0").style.fontFamily = "";
									document.getElementById("lakitu0").style.display = "none";
									document.getElementById("drift0").style.display = "none";
									if (strPlayer.length == 1)
										removePlan();
									oBgLayers.length = 0;
									document.onmousedown = undefined;
									setTimeout(MarioKart, 500);
								}
							document.getElementById("quitter").onclick = quitter;
							if (bMusic && !oMusicEmbed) {
								unpauseMusic(mapMusic);
								forceStartMusic = true;
							}
						}
						bCounting = false;
					}, 1000
				);

				if (!pause || !fInfos.replay) {
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
						if (clLocalVars.fastForward) return;
						var gameAction = gameControls[e.keyCode];
						if (!gameAction) return;
						var aElt = document.activeElement;
						if (aElt && (aElt.tagName == "INPUT") && (aElt.type != "button") && (aElt.type != "submit")) return;
						if (e.preventDefault)
							e.preventDefault();
						switch (gameAction) {
							case "up":
								oPlayers[0].speedinc = cp[oPlayers[0].personnage][0]*oPlayers[0].size;
								if (oPlayers[0].etoile) oPlayers[0].speedinc *= 5;
								break;
							case "left":
								oPlayers[0].rotincdir = cp[oPlayers[0].personnage][2];
								if (!oPlayers[0].driftinc && !oPlayers[0].tourne && !oPlayers[0].fell && oPlayers[0].ctrl) {
									if (oPlayers[0].jumped)
										oPlayers[0].driftinc = 1;
									if (oPlayers[0].driftinc)
										clLocalVars.drifted = true;
								}
								break;
							case "right":
								oPlayers[0].rotincdir = -cp[oPlayers[0].personnage][2];
								if (!oPlayers[0].driftinc && !oPlayers[0].tourne && !oPlayers[0].fell && oPlayers[0].ctrl) {
									if (oPlayers[0].jumped)
										oPlayers[0].driftinc = -1;
									if (oPlayers[0].driftinc)
										clLocalVars.drifted = true;
								}
								break;
							case "down":
								oPlayers[0].speedinc -= 0.2;
								break;
							case "jump":
								if (pause) break;
								oPlayers[0].ctrl = true;
								if (!oPlayers[0].z && !oPlayers[0].heightinc) {
									if (!oPlayers[0].driftinc && !oPlayers[0].tourne) {
										oPlayers[0].z = 1;
										oPlayers[0].heightinc = 0.5;
										oPlayers[0].jumped = true;
										if (oPlayers[0].rotincdir)
											oPlayers[0].driftinc = (oPlayers[0].rotincdir>0) ? 1:-1;
										if (oPlayers[0].driftinc)
											clLocalVars.drifted = true;
									}
								}
								else if (!oPlayers[0].jumped && !oPlayers[0].fell && !oPlayers[0].ctrled && !oPlayers[0].billball && !oPlayers[0].tourne && !oPlayers[0].figuring && !oPlayers[0].figstate)
									stuntKart(oPlayers[0]);
								break;
							case "pause":
								if (isOnline) break;
								if (!pause) {
									if (!bCounting) {
										document.getElementById("infos0").style.visibility = "visible";
										pause = true;
										pauseSounds();
									}
								}
								else
									reprendre(true);
								break;
							case "balloon" :
								if (pause) return;
								if (course == "BB") {
									if ((oPlayers[0].tourne<5) && oPlayers[0].reserve && oPlayers[0].ballons.length < 3 && !oPlayers[0].sprite[0].div.style.opacity) {
										oPlayers[0].ballons[oPlayers[0].ballons.length] = createBalloonSprite(oPlayers[0]);
										oPlayers[0].reserve--;
										document.getElementById("compteur0").innerHTML = "&nbsp;";
										for (i=0;i<oPlayers[0].reserve;i++)
											document.getElementById("compteur0").innerHTML += '<img src="'+balloonSrc(oPlayers[0].team)+'" style="width: '+(iScreenScale*2)+'" />';
										playIfShould(oPlayers[0],"musics/events/balloon.mp3");
									}
								}
								break;
							case "quit":
								if (!bCounting)
									quitter();
								break;
							case "cheat":
								if (!isOnline && (course != "GP") && (course != "CM"))
									openCheats();
								break;
							case "fastfwd":
								if (clLocalVars.delayedStart && !clLocalVars.cheated && !pause) {
									if (!clLocalVars.startedAt) {
										var tUntil = clLocalVars.delayedStart*1000/67;
										if (timer < tUntil) {
											function fastCycle() {
												if (pause) {
													if (timer < tUntil) {
														setTimeout(fastCycle,1);
														runOneFrame();
													}
													else {
														delete clLocalVars.fastForward;
														reprendre = resume;
														reprendre(false);
													}
												}
											}
											pause = true;
											clLocalVars.fastForward = true;
											var resume = reprendre;
											reprendre = function(){};
											fastCycle();
										}
									}
									else
										alert(language ? "You have already started, it's too late...":"Vous avez déjà démarré, il est trop tard...");
								}
								break;
							case "up_p2":
								if (!oPlayers[1]) return;
								oPlayers[1].speedinc = cp[oPlayers[1].personnage][0]*oPlayers[1].size;
								if (oPlayers[1].etoile) oPlayers[1].speedinc *= 5;
								break;
							case "left_p2":
								if (!oPlayers[1]) return;
								oPlayers[1].rotincdir = cp[oPlayers[1].personnage][2];
								if (!oPlayers[1].driftinc && !oPlayers[1].tourne && !oPlayers[1].fell && oPlayers[1].ctrl) {
									if (oPlayers[1].jumped)
										oPlayers[1].driftinc = 1;
								}
								break;
							case "right_p2":
								if (!oPlayers[1]) return;
								oPlayers[1].rotincdir = -cp[oPlayers[1].personnage][2];
								if (!oPlayers[1].driftinc && !oPlayers[1].tourne && !oPlayers[1].fell && oPlayers[1].ctrl) {
									if (oPlayers[1].jumped)
										oPlayers[1].driftinc = -1;
								}
								break;
							case "down_p2":
								if (!oPlayers[1]) return;
								oPlayers[1].speedinc -= 0.2;
								break;
							case "jump_p2":
								if (pause) break;
								if (!oPlayers[1]) return;
								oPlayers[1].ctrl = true;
								if (!oPlayers[1].z && !oPlayers[1].heightinc) {
									if (!oPlayers[1].driftinc && !oPlayers[1].tourne) {
										oPlayers[1].z = 1;
										oPlayers[1].heightinc = 0.5;
										oPlayers[1].jumped = true;
										if (oPlayers[1].rotincdir)
											oPlayers[1].driftinc = (oPlayers[1].rotincdir>0) ? 1:-1;
									}
								}
								else if (!oPlayers[1].jumped && !oPlayers[1].ctrled && !oPlayers[1].fell && !oPlayers[1].billball && !oPlayers[1].tourne && !oPlayers[1].figuring && !oPlayers[1].figstate)
									stuntKart(oPlayers[1]);
								return false;
							case "rear_p2":
								if (pause) return;
								if (!oPlayers[1]) return;
								if (course == "BB") {
									if ((oPlayers[0].tourne<5) && oPlayers[1].reserve && oPlayers[1].ballons.length < 3 && !oPlayers[1].sprite[0].div.style.opacity) {
										oPlayers[1].ballons[oPlayers[1].ballons.length] = createBalloonSprite(oPlayers[1]);
										oPlayers[1].reserve--;
										document.getElementById("compteur1").innerHTML = "&nbsp;";
										for (i=0;i<oPlayers[1].reserve;i++)
											document.getElementById("compteur1").innerHTML += '<img src="'+balloonSrc(oPlayers[1].team)+'" style="width: '+(iScreenScale*2)+'" />';
									}
								}
								break;
						}
					}
					document.onkeyup = function(e) {
						var gameAction = gameControls[e.keyCode];
						if (!gameAction) return;
						var aElt = document.activeElement;
						if (aElt && (aElt.tagName == "INPUT") && (aElt.type != "button") && (aElt.type != "submit")) return;
						switch (gameAction) {
							case "item":
							case "item_back":
								if (!oPlayers[0].tourne && !pause)
									arme(0, ("item_back" === gameAction));
								break;
							case "up":
								oPlayers[0].speedinc = 0;
								break;
							case "left":
								oPlayers[0].rotincdir = 0;
								break;
							case "right":
								oPlayers[0].rotincdir = 0;
								break;
							case "down":
								oPlayers[0].speedinc = 0;
								break;
							case "jump":
								if (pause) break;
								delete oPlayers[0].ctrl;
								if (oPlayers[0].driftinc) {
									oPlayers[0].driftinc = 0;
									if (oPlayers[0].driftcpt >= fTurboDriftCpt) {
										oPlayers[0].turbodrift = 15;
										if (oPlayers[0].driftcpt >= fTurboDriftCpt2)
											oPlayers[0].turbodrift += 10;
										oPlayers[0].turbodrift0 = oPlayers[0].turbodrift;
										getDriftImg(0).src = "images/drift.png";
									}
									oPlayers[0].driftcpt = 0;
									document.getElementById("drift0").style.display = "none";
									if (oPlayers[0].driftSound) {
										oPlayers[0].driftSound.pause();
										oPlayers[0].driftSound = undefined;
									}
								}
								oPlayers[0].ctrled = false;
								if (oPlayers[0].jumped) {
									if (oPlayers[0].z || oPlayers[0].heightinc)
										oPlayers[0].ctrled = true;
								}
								break;
							case "rear":
								if (!bCounting) {
									var nView = 180 - oPlayers[0].changeView;
									oPlayers[0].changeView = nView;
									oPlayers[0].sprite[0].setState(11);
								}
								break;
							case "item_p2":
							case "item_back_p2":
								if (!oPlayers[1].tourne && !pause)
									arme(1, ("item_back_p2" === gameAction));
								break;
							case "up_p2":
								if (!oPlayers[1]) return;
								oPlayers[1].speedinc = 0;
								break;
							case "left_p2":
								if (!oPlayers[1]) return;
								oPlayers[1].rotincdir = 0;
								break;
							case "right_p2":
								if (!oPlayers[1]) return;
								oPlayers[1].rotincdir = 0;
								break;
							case "down_p2":
								if (!oPlayers[1]) return;
								oPlayers[1].speedinc = 0;
								break;
							case "jump_p2":
								if (pause) break;
								if (!oPlayers[1]) return;
								delete oPlayers[1].ctrl;
								if (oPlayers[1].driftinc) {
									oPlayers[1].driftinc = 0;
									if (oPlayers[1].driftcpt >= fTurboDriftCpt) {
										oPlayers[1].turbodrift = 15;
										if (oPlayers[1].driftcpt >= fTurboDriftCpt2)
											oPlayers[1].turbodrift += 10;
										oPlayers[1].turbodrift0 = oPlayers[1].turbodrift;
										getDriftImg(1).src = "images/drift.png";
									}
									oPlayers[1].driftcpt = 0;
									document.getElementById("drift1").style.display = "none";
									if (oPlayers[1].driftSound) {
										oPlayers[1].driftSound.pause();
										oPlayers[1].driftSound = undefined;
									}
								}
								oPlayers[1].ctrled = false;
								if (oPlayers[1].jumped) {
									if (oPlayers[1].z || oPlayers[1].heightinc)
										oPlayers[1].ctrled = true;
								}
								break;
							case "rear_p2":
								if (!oPlayers[1]) return;
								if (!bCounting) {
									var nView = 180 - oPlayers[1].changeView;
									oPlayers[1].changeView = nView;
									oPlayers[1].sprite[0].setState(11);
								}
						}
					}
					window.releaseOnBlur = function() {
						for (var i=0;i<oPlayers.length;i++) {
							oPlayers[i].speedinc = 0;
							oPlayers[i].rotincdir = 0;
							stopDrifting(i);
						}
					};
					window.addEventListener("blur", window.releaseOnBlur);
					if (isMobile()) {
						document.onmousedown = function(e) {
							if (pause)
								return true;
							if (!oPlayers[0].tourne) {
								if (course == "BB") {
									document.onkeydown({"keyCode":findKeyCode("balloon")});
									return false;
								}
								if (oPlayers[0].arme || oPlayers[0].using[0]) {
									arme(0);
									return false;
								}
								return true;
							}
							return true;
						}
					}
					if (isOnline) {
						window.onbeforeunload = function() {
							return language ? "Caution, if you leave the game, you are considered loser" : "Attention, si vous quittez la partie, vous êtes considéré comme perdant";
						}
					}
					pause = false;
					if (course == "CM")
						iTrajet = new Array();
					cycle();
					bRunning = true;
				}
				else {
					pause = false;
					var iTrajets = [iTrajet];
					var aJumped = false, aHeight = 0, aDriftInc = 0, aHeightInc = null, willDrift = false, aStunted = false;
					function stopDrifting_() {
						aKarts[0].tourne = 0;
						getDriftImg(0).src = "images/drift.png";
						document.getElementById("drift0").style.display = "none";
						aDriftInc = 0;
					}
					function stopStunt() {
						if (aStunted) {
							aStunted = false;
							aKarts[0].tourne = 0;
						}
						var oSprite = aKarts[0].sprite[0];
						if (oSprite.div.ahallowed) {
							oSprite.div.ahallowed = false;
							oSprite.div.style.backgroundColor = "";
							oSprite.div.style.backgroundImage = "";
							oSprite.div.style.backgroundRepeat = "";
							oSprite.div.style.backgroundSize = "";
							oSprite.img.style.opacity = 1;
						}
					}
					function angleDiff(angle1,angle2) {
						var diffRotation = angle2-angle1;
						return diffRotation - 360*Math.round(diffRotation/360);
					}
					function isTurning(angle1,angle2, threshold) {
						return (Math.abs(angleDiff(angle1,angle2)) > threshold);
					}
					function calcHeightInc(diffHeight) {
						return Math.sqrt(Math.abs(diffHeight)/0.7)*Math.sign(diffHeight);
					}
					function turboDriftNext(timer) {
						return (turboDriftNextId(timer) > 0);
					}
					function turboDriftNextId(timer) {
						var limit = Math.min(timer+50,iTrajets[i].length);
						var iTrajet = iTrajets[0];
						for (var t=timer+1;t<limit;t++) {
							if (iTrajet[t][3]) return -1;
							var lastX = iTrajet[t-1][0], lastY = iTrajet[t-1][1];
							var currentX = iTrajet[t][0], currentY = iTrajet[t][1];
							var speed2 = (currentX-lastX)*(currentX-lastX) + (currentY-lastY)*(currentY-lastY);
							if (Math.abs(64-speed2) < 0.16)
								return t-timer;
							if (speed2 > 100)
								return -1;
						}
						return -1;
					}
					function revoir() {
						if (pause)
							return;
						for (var i=0;i<iTrajets.length;i++) {
							var oKart = aKarts[i];
							var getInfos = iTrajets[i][timer];
							if (getInfos) {
								if (oKart.tombe) {
									oKart.tombe--;
									if (!oKart.tombe)
										oKart.sprite[0].img.style.display = "block";
									if (!i)
										oContainers[0].style.opacity = Math.abs(oKart.tombe-10)/10;
								}
								var lastRotation = oKart.rotation;
								oKart.x = getInfos[0];
								oKart.y = getInfos[1];
								oKart.rotation = getInfos[2];
								if (getInfos[3]) {
									if (!i) {
										aJumped = true;
										if (!aStunted)
											stopDrifting_();
									}
									oKart.z = getInfos[3];
									if (!i) {
										if (oKart.z > 1.18)
											aJumped = false;
										if (aJumped && (oKart.z < aHeight)) {
											aJumped = false;
											var iLastRotation = oKart.rotation;
											for (var t=timer+1;t<iTrajets[i].length;t++) {
												var iInfos = iTrajets[i][t];
												if (!iInfos[3]) {
													var turnDiff = angleDiff(iLastRotation,iInfos[2]);
													if (Math.abs(turnDiff) > 0.01) {
														willDrift = true;
														oKart.tourne = (turnDiff>0) ? 19:2;
													}
													break;
												}
												iLastRotation = iInfos[2];
											}
										}
									}
									if (getInfos[4]) {
										oKart.tombe = 20;
										oKart.sprite[0].img.style.display = "none";
									}
								}
								else if (willDrift) {
									if (!i) {
										aJumped = false;
										willDrift = false;
										aDriftInc = 1;
										var nextTurboCpt = turboDriftNextId(timer);
										if (nextTurboCpt != -1) {
											if (nextTurboCpt == 1)
												aDriftInc = 5;
											else {
												var nDriftInc = 15-nextTurboCpt;
												if ((nDriftInc > 0) && (nDriftInc <= 5))
													aDriftInc = nDriftInc;
											}
										}
										document.getElementById("drift0").style.display = "block";
									}
								}
								else if (aDriftInc) {
									if (!i) {
										var diffRotation = oKart.rotation-lastRotation;
										diffRotation = diffRotation - 360*Math.round(diffRotation/360);
										if (aDriftInc == 15)
											getDriftImg(0).src = "images/turbo-drift.png";
										if (!isTurning(oKart.rotation,lastRotation, 0.01) && !turboDriftNext(timer))
											stopDrifting_();
										else
											aDriftInc++;
									}
								}
								if (!getInfos[3])
									oKart.z = 0;
								if (!i) {
									if (aHeightInc != null) {
										if (oKart.z) {
											if (aStunted) {
												oKart.tourne -= 1 + Math.round((11-Math.abs(11-oKart.tourne))*0.5);
												if (oKart.tourne < 8) {
													var oSprite = oKart.sprite[0];
													if (!oSprite.div.ahallowed) {
														oSprite.div.ahallowed = true;
														oSprite.div.style.backgroundImage = "url('images/halo.png')";
														oSprite.div.style.backgroundRepeat = "no-repeat";
														oSprite.div.style.backgroundSize = "contain";
														oSprite.img.style.opacity = 0.7;
													}
													if (oKart.tourne < 0)
														oKart.tourne = 0;
												}
											}
											else {
												aHeightInc -= 0.5;
												var cHeightInc = calcHeightInc(oKart.z-aHeight);
												if ((cHeightInc-aHeightInc) > 0.01) {
													aStunted = true;
													stopDrifting_();
													oKart.tourne = 19;
												}
												aHeightInc = cHeightInc;
											}
										}
										else {
											stopStunt();
											aHeightInc = null;
										}
									}
									else if (aHeight)
										aHeightInc = calcHeightInc(oKart.z-aHeight);
									aHeight = oKart.z;
								}
							}
							else {
								if (oKart.aipoint == undefined) {
									oKart.aipoint = 0;
									oKart.arme = false;
									oKart.maxspeed = 5.7;
									if (!i) {
										oKart.tourne = 0;
										stopDrifting_();
										stopStunt();
									}
								}
								ai(oKart);
								move(i);
							}
						}
						timer++;
						showTimer();
						if (!(timer%100))
							aKarts[0].changeView = Math.floor(Math.random()*21)*(360/21);

						setTimeout((timer != iTrajet.length) ? revoir : function(){var oKart=aKarts[0];oKart.aipoint=0;oKart.changeView=180;oKart.maxspeed=5.7;oKart.speed=5.7;oKart.tourne=0;stopDrifting_();stopStunt();document.onkeyup=undefined;document.getElementById("infos0").style.visibility="visible";if(bMusic||iSfx){startEndMusic()}cycle()},67);
						render();
					}
					for (i=0;i<aKarts.length;i++)
						aKarts[i].cpu = true;
					for (i=0;i<gPersos.length;i++)
						iTrajets.push(jTrajets[i]);
					revoir();
					pause = false;
					setTimeout(continuer,1000);
					document.onkeyup = function(e) {
						var gameAction = gameControls[e.keyCode];
						if (gameAction == "pause" && !bCounting)
							document.getElementById("infos0").style.visibility = (document.getElementById("infos0").style.visibility == "hidden") ? "visible" : "hidden";
						else if (gameAction == "quit")
							quitter();
					}
				}
				fInfos = undefined;
				return;
			}
		}
		else {
			for (var i=0;i<strPlayer.length;i++)
				document.getElementById("infos"+i).style.visibility = "visible";
			if (bMusic || iSfx)
				countDownMusic.play();
			document.body.style.cursor = "default";
		}
		iCntStep++;
		setTimeout(fncCount,1000*1.00);
	}
	function showTeam(tnCountdown) {
		var oTeam = oPlayers[0].team;
		var oDiv = document.createElement("div");
		oDiv.style.position = "absolute";
		oDiv.style.zIndex = 10000;
		oDiv.style.left = "0px";
		oDiv.style.top = (iScreenScale*12) +"px";
		oDiv.style.width = (iScreenScale*iWidth) +"px";
		oDiv.style.textAlign = "center";
		oDiv.style.fontSize = (iScreenScale*6) +"px";
		oDiv.style.fontWeight = "bold";
		oDiv.style.color = oTeam ? "#F96":"#69F";
		oDiv.innerHTML = toLanguage("You are ", "Vous êtes ") + (oTeam ? toLanguage("red","rouge") : toLanguage("blue","bleu"));
		var dispDelay = Math.min(1500,tnCountdown-1000);
		if (dispDelay > 500) {
			setTimeout(function() {
				oContainers[0].appendChild(oDiv);
				setTimeout(function() {
					oContainers[0].removeChild(oDiv);
				}, dispDelay);
			}, 500);
		}
	}

	if (iSfx)
		setTimeout(startEngineSound,bMusic ? 2600:1100);
	if (isOnline) {
		var tnCountdown = tnCourse-new Date().getTime();
		setTimeout(fncCount,tnCountdown);
		if (iTeamPlay)
			showTeam(tnCountdown);
	}
	else
		setTimeout(fncCount,bMusic?3000*1.00:1500*1.00);
	if (!isOnline)
		document.body.style.cursor = "default";
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
var oContainers = [document.createElement("div")];
oContainers[0].tabindex = 1;
oContainers[0].style.position = "absolute";
oContainers[0].style.border = "2px solid black";
oContainers[0].style.left = "10px";
oContainers[0].style.overflow = "hidden";

document.getElementById("mariokartcontainer").appendChild(oContainers[0]);
if (pause && fInfos.player[1]) {
	oContainers[1] = oContainers[0].cloneNode(false);
	oContainers[1].style.left = (10+iWidth*iScreenScale)+"px";
	document.getElementById("mariokartcontainer").appendChild(oContainers[1]);
}

// setup screen canvas for render mode 0.
var oScreens = new Array();

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

	for (var i=0;i<strPlayer.length;i++) {
		var oCtrChange = oContainers[i];
		oCtrChange.style.width = (iWidth*iScreenScale)+"px";
		oCtrChange.style.height = (iHeight*iScreenScale)+"px";
		oCtrChange.style.left = (10+iWidth*iScreenScale*i)+"px";

		var oScreenCanvas = document.createElement("canvas");
		oScreenCanvas.style.position = "absolute";
		oScreens.push(oScreenCanvas);
		oContainers[i].appendChild(oScreenCanvas);
		oScreenCanvas.width=iWidth/fLineScale;
		oScreenCanvas.height=iHeight/fLineScale;
		oScreenCanvas.style.width = (iWidth*iScreenScale+iScreenScale)+"px";
		oScreenCanvas.style.left = (-iScreenScale/2)+"px";
		oScreenCanvas.style.top = iScreenScale+"px";
		oScreenCanvas.style.height = (iHeight*iScreenScale)+"px";
	}

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
	if (isOnline) {
		document.location.href = isMCups ? ((complete ? 'map':'circuit') + '.php?mid=' + nid):(isCup ? (complete ? (isBattle ? 'battle':'map')+'.php?'+(isSingle ? 'i':'cid')+'='+nid:(isBattle ? 'arena':'circuit')+'.php?'+(isSingle ? 'id':'cid')+'='+nid):"index.php");
		return;
	}
	pause = true;
	displayCommands("&nbsp;");
	removeGameMusics();
	for (var i=0;i<strPlayer.length;i++) {
		oContainers[i].innerHTML = "";
		document.getElementById("infos"+i).style.visibility = "hidden";
		document.getElementById("infoPlace"+i).style.display = "none";
		document.getElementById("infoPlace"+i).innerHTML = "";
		document.getElementById("compteur"+i).innerHTML = "";
		document.getElementById("temps"+i).innerHTML = "";
		document.getElementById("objet"+i).style.visibility = "hidden";
		document.getElementById("roulette"+i).innerHTML = "";
		var lakitu = document.getElementById("lakitu"+i);
		if (lakitu) lakitu.style.display = "none";
		document.getElementById("drift"+i).style.display = "none";
		document.getElementById("infos"+i).style.opacity = 0.8;
		document.getElementById("infos"+i).style.color = "#FF9900";
		document.getElementById("scroller"+i).style.visibility="hidden";
	}
	document.getElementById("mariokartcontainer").style.opacity = 1;
	if (strPlayer.length == 1)
		removePlan();
	document.onmousedown = undefined;
	document.onkeydown = undefined;
	document.onkeyup = undefined;
	window.removeEventListener("blur", window.releaseOnBlur);
	window.releaseOnBlur = undefined;
	oBgLayers.length = 0;
	aPlayers = [];
	aScores = [];
	clRuleVars = {};
	clGlobalVars = undefined;
	setTimeout(function(){pause=false;MarioKart()},500);
}

function classement() {
	var nbjoueurs = aKarts.length;
	for (var i=0;i<nbjoueurs;i++) {
		var kart = aKarts[i];
		var ptsPlayer = aScores[i];
		var iPlaces = 1;
		for (var j=0;j<nbjoueurs;j++) {
			var ptsKart = aScores[j];
			if (kart != aKarts[j] && ptsPlayer <= ptsKart) {
				if (ptsPlayer < ptsKart || i > j)
				iPlaces++;
				}
			}
		kart.place = iPlaces;
	}
	var iPlacement = new Array();
	for (var i=1;i<=nbjoueurs;i++) {
		for (var j=0;j<nbjoueurs;j++) {
			var iPlaces = aKarts[j].place;
			if (iPlaces == i) {
				iPlacement.push(j);
				j = nbjoueurs;
			}
		}
	}

	document.getElementById("infos0").style.visibility="hidden";
	var nPlayer = strPlayer.length-1;
	for (var i=0;i<iPlacement.length;i++) {
		var iPlayer = iPlacement[i];
		var tPlayer = aKarts[iPlayer].personnage;
		var isRedTeam = (aKarts[iPlayer].team==1) ? 1:0;
		document.getElementById("fJ"+i).style.backgroundColor = (iPlayer != 0) ? ((iPlayer != nPlayer) ? (isRedTeam?"red":"") : (isRedTeam?"brown":"navy")) : (rankingColor(aKarts[iPlayer].team));
		document.getElementById("fJ"+i).style.opacity = (tPlayer != strPlayer) ? "" : 0.8;
		document.getElementById("j"+i).innerHTML = toPerso(tPlayer);
		document.getElementById("pts"+i).innerHTML = aScores[iPlayer];
	}
	var oTeamTable;
	if (iTeamPlay) {
		var teamsRecap = [0,0];
		for (var i=0;i<aScores.length;i++)
			teamsRecap[aTeams[i]] += aScores[i];
		var teamsRank = teamsRecap[0]>teamsRecap[1] || (teamsRecap[0]==teamsRecap[1] && teamsRecap[0]==oPlayers[0].team) ? [0,1]:[1,0];
		oTeamTable = document.createElement("table");
		oTeamTable.id = "team-table";
		var positions = '<tr style="font-size: '+ iScreenScale * 2 +'px; background-color: white; color: black;"><td>Places</td><td>'+ toLanguage('Team','Équipe') +'</td><td>Pts</td></tr>';
		for (var i=0;i<teamsRank.length;i++) {
			var isRedTeam = teamsRank[i];
			positions += '<tr id="fJ'+i+'" style="background-color:'+ (isRedTeam?'red':'blue') +'"><td>'+ toPlace(i+1) +' </td><td class="maj" id="j'+i+'">'+ (isRedTeam ? toLanguage('Red','Rouge'):toLanguage('Blue','Bleue')) +'</td><td id="pts'+i+'">'+ teamsRecap[isRedTeam] +'</td></tr>';
		}
		oTeamTable.style.visibility = "hidden";
		oTeamTable.style.position = "absolute";
		oTeamTable.style.zIndex = 20000;
		oTeamTable.style.left = (iScreenScale*3 + 10) +"px";
		oTeamTable.style.top = (iScreenScale*10) +"px";
		oTeamTable.style.backgroundColor = "blue";
		oTeamTable.style.color = "yellow";
		oTeamTable.style.opacity = 0.7;
		oTeamTable.style.textAlign = "center";
		oTeamTable.style.fontSize = Math.round(iScreenScale*1.5+4) +"pt";
		oTeamTable.style.fontFamily = "Courier";
		oTeamTable.style.fontWeight = "bold";
		oTeamTable.style.fontFamily = "arial";
		oTeamTable.innerHTML = positions;
		document.body.appendChild(oTeamTable);
	}
	document.getElementById("octn").onclick = continuer;
	setTimeout(function() {
		document.getElementById("infos0").style.visibility = "visible";
		if (oTeamTable)
			oTeamTable.style.visibility = "visible";
		var aScroll = document.body.scrollTop;
		document.getElementById("octn").focus();
		document.body.scrollTop = aScroll;
	}, 500);
}

function continuer() {
	document.getElementById("infos0").style.border = 0;
	document.getElementById("infos0").style.top = iScreenScale * 10 + 10 +"px";
	document.getElementById("infos0").style.left = Math.round(iScreenScale*20+10 + (strPlayer.length-1)/2*(iWidth*iScreenScale+2)) +"px";
	document.getElementById("infos0").style.background = "transparent";
	document.getElementById("infos0").style.fontSize = iScreenScale * 4 +"pt";
	document.getElementById("infos0").innerHTML = '<tr><td id="continuer"></td></tr><tr><td'+ ((course != "CM") ? ' style="font-size: '+ iScreenScale * 3 +'px;">&nbsp;</td></tr>' : ' id="enregistrer"></td></tr><tr><td id="revoir"></td></tr><tr><td id="classement">') +'</td></tr><tr><td><input type="button" id="quitter" value="'+ toLanguage('QUIT', 'QUITTER') +'" style="font-size: '+ iScreenScale*3 +'pt; width: 100%;" /></td></tr>';

	var oTeamTable = document.getElementById("team-table");
	if (oTeamTable)
		document.body.removeChild(oTeamTable);

	var oContinue = document.createElement("input");
	oContinue.type = "button";
	oContinue.style.fontSize = iScreenScale*3 + "pt";
	oContinue.style.width = "100%";

	if (course != "CM") {
		if (oMap.ref % 4 || course != "GP") {
			if (isSingle && !isOnline)
				oContinue.value = "        "+ toLanguage('  REPLAY', 'REJOUER') +"        ";
			else {
				if ((course == "GP") && (oMap == oMaps[3]))
					oContinue.value = toLanguage("           NEXT           ", "         SUIVANT          ");
				else if (course == "BB")
					oContinue.value = toLanguage("      NEXT BATTLE	   ", "BATAILLE SUIVANTE");
				else
					oContinue.value = toLanguage("       NEXT RACE	   ", "COURSE SUIVANTE");
			}
			function nextRace() {
				pause = true;
				removeGameMusics();

				for (var i=0;i<strPlayer.length;i++) {
					oContainers[i].innerHTML = "";
					document.getElementById("infoPlace"+i).style.display = "none";
					document.getElementById("compteur"+i).innerHTML = "";
					document.getElementById("temps"+i).innerHTML = "";
					document.getElementById("objet"+i).style.visibility = "hidden";
					var lakitu = document.getElementById("lakitu"+i);
					if (lakitu) lakitu.style.display = "none";
					fInfos = {
						player:strPlayer,
						map:oMap.ref+1,
						difficulty:iDificulty
					};
					document.getElementById("infos"+i).style.visibility = "hidden";
					document.getElementById("infos"+i).style.opacity = 0.8;
					document.getElementById("infos"+i).style.color = "#FF9900";
					document.getElementById("infos"+i).style.fontFamily = "";
				}
				document.getElementById("mariokartcontainer").style.opacity = 1;
				if (strPlayer.length == 1)
					removePlan();
				oBgLayers.length = 0;
				document.onmousedown = undefined;
				setTimeout(MarioKart, 500);
			}
			var forceClic3 = true;
			oContinue.onclick = function() {
				forceClic3 = false;
				nextRace();
			};
			if (isOnline)
				setTimeout(function(){if(forceClic3)nextRace();},5000);
		}
		else {
			oContinue.value = toLanguage("           NEXT           ", "         SUIVANT          ");
			oContinue.onclick = function () {
				pause = true;
				var posX = [29,22,36];
				var posY = [15,17,19];
				document.body.innerHTML = toLanguage('You are', 'Vous &ecirc;tes') +' <span id="position"></span> !<br /><a href="javascript:location.reload()" style="color: white;">'+ toLanguage('Back', 'Retour') +'</a><img alt="." src="images/podium.gif" style="position: absolute; left: '+ iScreenScale * 20 +'px; top: '+ iScreenScale * 20 +'px; width: '+ iScreenScale * 24 +'px;" />';
				var oPlace;
				var placement = new Array();
				for (var i=1;i<=aKarts.length;i++) {
					for (var j=0;j<aKarts.length;j++) {
						if (aKarts[j].place == i) {
							placement.push(aKarts[j].personnage);
							j = aKarts.length;
						}
					}
				}
				document.body.style.fontSize = iScreenScale * 2 +"pt";
				for (var i=0;i<placement.length;i++) {
					if (placement[i] == strPlayer)
						oPlace = i+1;
					if (i < 3)
						document.body.innerHTML += '<img alt="." src="'+ getWinnerSrc(placement[i]) +'" style="width: '+ iScreenScale*4 +'px; position: absolute; left: '+ iScreenScale * posX[i] +'px; top: '+ iScreenScale * (posY[i]+(isCustomPerso(placement[i])?6:0)) +'px;'+ (isCustomPerso(placement[i]) ? 'transform:translateY(-100%);-webkit-transform:translateY(-100%);-moz-transform:translateY(-100%);-o-transform:translateY(-100%);-ms-transform:translateY(-100%);':'') +'" />';
					else if (oPlace)
						i = placement.length;
				}
				document.getElementById("position").innerHTML = toPlace(oPlace);
				if (oPlace <= 3) {
					document.body.innerHTML += '<img alt="." src="images/cups/cup'+oPlace+'.png" style="width: '+ iScreenScale*3 +'px; position: absolute; left: '+ iScreenScale * 30 +'px; top: '+ iScreenScale * 25 +'px;" />';
					var saveUrl, saveParams = "pts="+(4-oPlace);
					if (page == "MK") {
						saveUrl = "saveGP.php";
						saveParams += "&change="+(oMap.map-4)/4;
					}
					else {
						if (nid) {
							saveUrl = "cupsave.php";
							if (isMCups)
								saveParams += "&cup="+cupIDs[oMap.ref/4-1];
							else
								saveParams += "&cup="+nid;
						}
					}
					if (saveUrl) {
						xhr(saveUrl, saveParams, function(reponse) {
							if (reponse) {
								var newPerso;
								try {
									newPerso = eval(reponse);
								}
								catch (e) {
									return false;
								}
								if (newPerso) {
									var uwPerso = toPerso(newPerso);
									uwPerso = uwPerso.charAt(0).toUpperCase() + uwPerso.substring(1);
									document.body.innerHTML += '<div style="position: absolute; left: '+ iScreenScale * 16 +'px; top: '+ iScreenScale * 30 +'px; text-align: center">' +
									toLanguage(
										'You can now play<br />with '+ uwPerso +' !',
										'Vous pouvez d&eacute;sormais<br />jouer avec '+ uwPerso +' !'
									) +
									'<br /><img src="'+ getWinnerSrc(newPerso) +'" style="width: '+ iScreenScale*4 +'px" /></div>';
								}
								return true;
							}
							return false;
						});
					}
					if (bMusic)
						endGPMusic = startMusic("musics/menu/congrats.mp3",true,700);
				}
				else {
					if (bMusic)
						endGPMusic = startMusic("musics/menu/toobad.mp3",true,700);
				}

				reinitChallengeVars();
				clLocalVars.endGP = true;
				challengeCheck("end_gp", ["finish_gp"]);
			}
		}
	}
	else {
		var oSave = oContinue.cloneNode(false);
		var oReplay = oContinue.cloneNode(false);
		var oClassement = oContinue.cloneNode(false);

		if (gSelectedPerso)
			oContinue.value = toLanguage('        FACE WITH        ', '     AFFRONTER     ');
		else
			oContinue.value = toLanguage('          RETRY          ', '     RÉESSAYER     ');
		oContinue.onclick = function() {
			pause = true;
			removeGameMusics();
			oContainers[0].innerHTML = "";
			document.getElementById("infoPlace0").style.display = "none";
			document.getElementById("compteur0").innerHTML = "";
			document.getElementById("temps0").innerHTML = "";
			document.getElementById("objet0").style.visibility = "hidden";
			fInfos = {
				player:strPlayer,
				map:oMap.ref,
				difficulty:iDificulty,
				perso:gPersos,
				cpu_route:jTrajets,
				record:gRecord
			};
			if (gSelectedPerso) {
				fInfos.player = [gSelectedPerso];
				fInfos.perso = [strPlayer[0]];
				fInfos.cpu_route = [iTrajet];
			}
			document.getElementById("infos0").style.visibility = "hidden";
			document.getElementById("infos0").style.opacity = 0.8;
			document.getElementById("infos0").style.color = "#FF9900";
			document.getElementById("infos0").style.fontFamily = "";
			if (strPlayer.length == 1)
				removePlan();
			oBgLayers.length = 0;
			document.onmousedown = undefined;
			setTimeout(MarioKart, 500);
		}

		oSave.value = "   "+ toLanguage('SAVE', 'ENREGISTRER') +"   ";
		oSave.onclick = function() {
			document.getElementById("infos0").style.visibility = "hidden";
			var oForm = document.createElement("form");
			oForm.style.color = "black";
			oForm.style.position = "absolute";
			oForm.style.left = (iScreenScale*5+10) +"px";
			oForm.style.top = (iScreenScale*5+10) +"px";
			oForm.style.fontSize = (iScreenScale*4) +"pt";
			oForm.style.backgroundColor = "#FF6";
			oForm.style.opacity = 0.8;
			oForm.style.border = "double 4px black";
			oForm.style.textAlign = "center";
			oForm.style.width = (iScreenScale*70-10) +"px";
			oForm.style.height = (iScreenScale*25-10) +"px";
			oForm.style.zIndex = 20000;

			oForm.onsubmit = function() {
				var nom = this.pseudo.value;
				
				if (nom) {
					document.body.style.cursor = "progress";
					this[1].style.visibility = "hidden";
					var params = "nom="+nom+"&perso="+strPlayer[0]+"&temps="+iTrajet.length;
					switch (page) {
					case "MK":
						params += "&circuit="+oMap.map;
						break;
					case "CI":
						params += "&creation="+ oMap.id;
						break;
					case "MA":
						params += "&map="+ oMap.map;
						break;
					}
					xhr("records_.php", params, function(reponse) {
						if (reponse) {
							document.body.style.cursor = "default";
							var enregistre;
							try {
								enregistre = eval(reponse);
							}
							catch (e) {
								return false;
							}
							oInput.disabled = true;
							aPara2.removeChild(oValide);
							aPara2.style.fontSize = Math.round(iScreenScale*2.5) + "px";
							aPara2.innerHTML = enregistre ? toLanguage("Congratulations "+ nom +", your score has been saved successfully ! You places ", "F&eacute;licitations "+ nom +", votre score a bien &eacute;t&eacute; enregistr&eacute; ! Vous &ecirc;tes ") + toPlace(enregistre[0]) + toLanguage(" out of "+ enregistre[1] +" in this race !", " sur "+ enregistre[1] +" au classement de ce circuit !") : toLanguage("You did a better score on this race before.<br />Your score has not been registered.", "Vous avez fait un meilleur score sur ce circuit.<br />Votre temps n'a donc pas &eacute;t&eacute; enregistr&eacute;.");
							if (enregistre)
								oSave.style.display = "none";
							return true;
						}
						return false;
					});
					recorder = nom;
				}
				return false;
			}
			var aPara1 = document.createElement("p");
			aPara1.innerHTML = toLanguage("Nick : ", "Pseudo : ");
			aPara1.style.margin = iScreenScale +"px";
			var oInput = document.createElement("input");
			oInput.type = "text";
			oInput.name = "pseudo";
			oInput.value = recorder;
			oInput.size = 15;
			oInput.maxlength = 18;
			oInput.style.fontSize = (iScreenScale*3) +"px";
			aPara1.appendChild(oInput);
			var aPara2 = aPara1.cloneNode(false);
			var oValide = document.createElement("input");
			oValide.type = "submit";
			oValide.value = "     "+ toLanguage("Submit", "Valider") +"     ";
			oValide.style.fontSize = (iScreenScale*5) +"px";
			oValide.onmouseover = function() {this.style.fontSize = (iScreenScale*5) +"px"; oRetour.style.fontSize = (iScreenScale*4) +"px";}
			aPara2.appendChild(oValide);
			var aPara3 = aPara1.cloneNode(false);
			var oRetour = document.createElement("input");
			oRetour.type = "button";
			oRetour.value = "     "+ toLanguage("Back", "Retour") +"     ";
			oRetour.style.fontSize = (iScreenScale*4) +"px";
			oRetour.onmouseover = function() {
				this.style.fontSize = (iScreenScale*5) +"px";
				oValide.style.fontSize = (iScreenScale*4) +"px"
			};
			oRetour.onclick = function() {
				document.body.removeChild(oForm);
				document.getElementById("infos0").style.visibility = "visible"
			};
			aPara3.appendChild(oRetour);

			oForm.appendChild(aPara1);
			oForm.appendChild(aPara2);
			oForm.appendChild(aPara3);
			document.body.appendChild(oForm);
		}
		if (gSelectedPerso) oSave.style.display = "none";
		document.getElementById("enregistrer").appendChild(oSave);

		oReplay.value = toLanguage("REPLAY", "REVOIR");
		oReplay.onclick = function() {
			pause = true;
			removeGameMusics();
			for (var i=0;i<strPlayer.length;i++) {
				oContainers[i].innerHTML = "";
				document.getElementById("infoPlace"+i).style.display = "none";
				document.getElementById("compteur"+i).innerHTML = "";
				document.getElementById("temps"+i).innerHTML = "";
				document.getElementById("objet"+i).style.visibility = "hidden";
				fInfos = {
					player:strPlayer,
					map:oMap.ref,
					my_route:iTrajet,
					replay:true,
					perso:gPersos,
					selPerso:gSelectedPerso,
					cpu_route:jTrajets,
					record:gRecord
				};
				document.getElementById("infos"+i).style.visibility = "hidden";
				document.getElementById("infos"+i).style.opacity = 0.8;
				document.getElementById("infos"+i).style.color = "#FF9900";
				document.getElementById("infos"+i).style.fontFamily = "";
			}
			if (strPlayer.length == 1)
				removePlan();
			oBgLayers.length = 0;
			document.onmousedown = undefined;
			setTimeout(MarioKart, 500);
		}
		document.getElementById("revoir").appendChild(oReplay);


		oClassement.value = "RECORDS";
		oClassement.onclick = function() {
			open(rankingsLink(oMap));
		}
		if (gSelectedPerso) oClassement.style.display = "none";
		document.getElementById("classement").appendChild(oClassement);

	}
	document.getElementById("continuer").appendChild(oContinue);
	document.getElementById("quitter").onclick = quitter;
	if (!isChatting())
		oContinue.focus();
}

function rankingColor(team) {
	switch (team) {
	case 0:
		return "#69F";
	case 1:
		return "#F96";
	default:
		return "#990";
	}
}


var iViewCanvasHeight = 240;
var iViewCanvasWidth = 600;
var iViewYOffset = 10;
var oViewCanvas;


function Sprite(strSprite) {
	var oCtSprites = new Array();
	for (var i=0;i<strPlayer.length;i++) {
		this[i] = {};
		var oImg = new Image();
		oImg.style.position = "absolute";
		oImg.style.left = "200px";
		oImg.alt = ".";
		oImg.className = "pixelated";

		oImg.src = getSpriteSrc(strSprite);

		var oSpriteCtr = document.createElement("div");
		oSpriteCtr.style.width = "32px";
		oSpriteCtr.style.height = "32px";
		oSpriteCtr.style.position = "absolute";
		oSpriteCtr.style.overflow = "hidden";
		oSpriteCtr.style.zIndex = 10000;
		oSpriteCtr.className = "pixelated";

		oSpriteCtr.style.display = "none";

		oSpriteCtr.appendChild(oImg);
		oContainers[i].appendChild(oSpriteCtr);

		this[i].i = i;
		this[i].h = 32;

		this[i].draw = function(iX, iY, fScale, iZ) {
			if (!iZ)
				iZ = 0;
			
			var i = this.i;

			if (iY > iHeight * iScreenScale || (iY+iZ*iScreenScale) < 9 * iScreenScale) {
				oCtSprites[i][0].style.display = "none";
				return;
			}


			oCtSprites[i][0].style.display = "block";

			var fSpriteSize = Math.round(32 * fSpriteScale * fScale);
			var fSpriteHeight = Math.round(this.h * fSpriteScale * fScale);

			oCtSprites[i][0].style.left = Math.round(iX - fSpriteSize/2)+"px";
			oCtSprites[i][0].style.top = Math.round(iY - fSpriteHeight/2 - (fSpriteHeight-fSpriteSize)*0.3)+"px";

			if (this.h != 32) {
				var nbSprites = Math.round(oCtSprites[i][1].naturalWidth/32);
				oCtSprites[i][1].style.width = (fSpriteSize*nbSprites)+"px";
			}
			else
				oCtSprites[i][1].style.width = "";
			oCtSprites[i][1].style.height = fSpriteHeight + "px";

			oCtSprites[i][0].style.width = fSpriteSize + "px";
			oCtSprites[i][0].style.height = fSpriteHeight + "px";

			oCtSprites[i][1].style.left = -(fSpriteSize*oCtSprites[i][2])+"px";
		}

		this[i].setState = function(iState) {
			oCtSprites[this.i][2] = iState;
		}
		this[i].getState = function() {
			return oCtSprites[this.i][2];
		}

		this[i].div = oSpriteCtr;
		this[i].img = oImg;
		oCtSprites.push([oSpriteCtr, oImg, 0]);
	}
	this[0].suppr = function() {
		for (var i=0;i<strPlayer.length;i++)
			oContainers[i].removeChild(oCtSprites[i][0]);
	}
}



function BGLayer(strImage, scaleFactor) {
	var oLayers = new Array();

	var imageDims = new Image();
	imageDims.src = "images/map_bg/fond_" + strImage + ".png";
	if (!iSmooth) imageDims.className = "pixelated";
	for (var i=0;i<oContainers.length;i++) {
		oLayers[i] = document.createElement("div");
		oLayers[i].style.height = (10 * iScreenScale)+"px";
		oLayers[i].style.width = (iWidth * iScreenScale)+"px";
		oLayers[i].style.position = "absolute";
		(function(oLayer){setTimeout(function(){oLayer.style.backgroundImage="url('"+imageDims.src+"')"},500)})(oLayers[i]);
		oLayers[i].style.backgroundSize = "auto 100%";
		if (!iSmooth) oLayers[i].className = "pixelated";

		oContainers[i].appendChild(oLayers[i]);
	}



	return {
		draw : function(fRotation, i) {
			if (!imageDims.naturalWidth) return;
			var iRot = fRotation - 360*Math.ceil(fRotation/360);
			var iActualWidth = 10*iScreenScale*imageDims.naturalWidth/imageDims.naturalHeight;

			// one degree of rotation equals x width units:
			var fRotScale = iActualWidth*scaleFactor/360;

			var iScroll = iRot*fRotScale;

			oLayers[i].style.backgroundPosition = Math.round(iScroll)+"px 0";
		},
		suppr : function() {
			for (var i=0;i<strPlayer.length;i++)
				oContainers[i].removeChild(oLayers[i]);
		}
	}
}
function createMarker(oKart) {
	var res = {
		div: new Array()
	};

	for (var i=0;i<strPlayer.length;i++) {
		var oDiv = document.createElement("div");
		oDiv.style.display = "none";
		oDiv.style.position = "absolute";
		oDiv.style.opacity = 0.7;

		var oColor = (oKart.team==-1) ? "#EEE":((oKart.team==1) ? "red":"blue");

		var lineWidth = iScreenScale*12, lineHeight = iScreenScale*3, lineAlpha = Math.PI/4, lineThick = Math.round(iScreenScale/4);
		var cosAlpha = Math.cos(lineAlpha), sinAlpha = Math.sin(lineAlpha);

		var oLine = document.createElement("div");
		oLine.style.position = "absolute";
		oLine.style.width = lineThick +"px";
		oLine.style.height = lineHeight +"px";
		oLine.style.backgroundColor = oColor;
		oLine.style.left = "0px";
		oLine.style.bottom = "0px";
		oLine.style.transform = oLine.style.WebkitTransform = oLine.style.MozTransform = "rotate("+Math.round(lineAlpha*180/Math.PI)+"deg)";
		oLine.style.transformOrigin = oLine.style.WebkitTransformOrigin = oLine.style.MozTransformOrigin = "bottom left";
		oDiv.appendChild(oLine);

		var oLine2 = document.createElement("div");
		oLine2.style.position = "absolute";
		oLine2.style.width = lineWidth +"px";
		oLine2.style.height = lineThick +"px";
		oLine2.style.backgroundColor = oColor;
		oLine2.style.left = Math.round(lineHeight*Math.sin(lineAlpha)) +"px";
		oLine2.style.bottom = Math.round(lineHeight*cosAlpha - lineThick*sinAlpha) +"px";
		oDiv.appendChild(oLine2);

		var oName = document.createElement("div");
		oName.style.color = (oKart.team==-1) ? "#555":oColor;
		oName.style.whiteSpace = "nowrap";
		var oShadow = (oKart.team==-1) ? "#EEE":((oKart.team==1) ? "#fcc":"#ccf");
		var sThickness = Math.ceil(iScreenScale/4) +"px";
		oName.style.textShadow = "-"+sThickness+" 0 "+oShadow+", 0 "+sThickness+" "+oShadow+", "+sThickness+" 0 "+oShadow+", 0 -"+sThickness+" "+oShadow;
		if (oKart.nick)
			oName.innerHTML = oKart.nick;
		else {
			oName.style.textTransform = "capitalize";
			oName.innerHTML = toPerso(oKart.personnage);
		}
		oName.style.position = "absolute";
		oName.style.left = Math.round(lineHeight*sinAlpha) +"px";
		oName.style.bottom = Math.round(lineHeight*cosAlpha) +"px";
		oName.style.width = Math.round(lineWidth - iScreenScale*0.5) +"px";
		oName.style.overflow = "hidden";
		oName.style.fontSize = Math.round(iScreenScale*1.5) +"px";
		oName.style.textAlign = "right";
		oDiv.appendChild(oName);

		res.div.push(oDiv);

		oContainers[i].appendChild(oDiv);
	}
	res.draw = function(i, iX, iY, fScale, iZ) {
		if (!iZ)
			iZ = 0;

		var oDiv = this.div[i];

		if (iY > iHeight * iScreenScale || (iY+iZ*iScreenScale) < 12 * iScreenScale) {
			oDiv.style.display = "none";
			return;
		}

		oDiv.style.display = "block";

		var fSpriteSize = Math.round(oKart.sprite[i].h * fSpriteScale * fScale);

		oDiv.style.left = Math.round(iX)+"px";
		oDiv.style.top = Math.round(iY - fSpriteSize/2)+"px";
	};
	return res;
}

function render() {

	collisionTest = COL_OBJ;
	collisionTeam = undefined;
	clLocalVars.currentKart = undefined;

	for (var i=0;i<strPlayer.length;i++) {
		if (oPlayers[i].tombe <= 10) {

			var posX = oPlayers[i].x;
			var posY = oPlayers[i].y;
			var fRotation = oPlayers[i].rotation;
		 
			if (oPlayers[i].tours == (oMap.tours+1) && oPlayers[i].changeView < 180)
				oPlayers[i].changeView += 15;
		 
			if (oPlayers[i].changeView)
				fRotation += (fRotation < 360-oPlayers[i].changeView ? oPlayers[i].changeView : oPlayers[i].changeView-360);

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

			oScreens[i].getContext("2d").imageSmoothingEnabled = iSmooth;

			for (var j=0;j<aStrips.length;j++) {

				var oStrip = aStrips[j];

				try {
					oScreens[i].getContext("2d").drawImage(
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
			var iOffsetY = (iHeight - iViewYOffset - correctZ(oPlayers[i].z))*iScreenScale;
			var fSprite;


			for (var j=0;j<aKarts.length;j++) {
				fSprite = aKarts[j];
				if (fSprite.cpu || fSprite != oPlayers[i]) {

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

					fSprite.sprite[i].setState(iAngleStep);

					fSprite.sprite[i].div.style.zIndex = Math.round(10000 - fTransY);

					var iX = ((iWidth/2) + fViewX) * iScreenScale,
						iY = (iHeight - iViewY) * iScreenScale,
						fScale = fFocal / (fFocal + fTransY) * fSprite.size,
						iZ = correctZ(fSprite.z);
					fSprite.sprite[i].draw(iX,iY, fScale, iZ);
					if (course == "BB") {
						var nbBallons = fSprite.ballons.length;
						var fTaille = fFocal / (fFocal + fTransY) * fSprite.size;
						var pTaille = fTaille*(6+(fSprite.sprite[i].h-32)/5);
						for (k=1;k<=nbBallons;k++)
							fSprite.ballons[k-1][i].draw(
								((iWidth/2) + fViewX +(k-nbBallons/2)*2.5*fTaille) * iScreenScale, 
								(iHeight - iViewY - pTaille) * iScreenScale,
								fTaille / 2,
								pTaille
							);
					}
					if (fSprite.marker && !fSprite.loose && !fSprite.tombe) {
						fSprite.marker.draw(i, iX,iY, fScale, iZ);
						fSprite.marker.div[i].style.zIndex = Math.round(10001 - fTransY);
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

					fSprite[2][i].div.style.zIndex = Math.round(10000 - fTransY);

					fSprite[2][i].draw(
						((iWidth/2) + fViewX) * iScreenScale, 
						(iHeight - iViewY) * iScreenScale,
						fFocal / (fFocal + (fTransY))
					);
				}
				else if (!i) {
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

					fSprite[2][i].div.style.zIndex = Math.round(10000 - fTransY);

					fSprite[2][i].draw(
						((iWidth/2) + fViewX) * iScreenScale, 
						(iHeight - iViewY) * iScreenScale,
						fFocal / (fFocal + (fTransY)) * 1.2
					);
				}
			}


			for (var j=0;j<bananes.length;j++){
				fSprite = bananes[j];
				var fCamX = fSprite[3] - posX;
				var fCamY = fSprite[4] - posY;

				var fRotRad = fRotation * Math.PI / 180;

				var fTransX = fCamX * Math.cos(fRotRad) - fCamY * Math.sin(fRotRad);
				var fTransY = fCamX * Math.sin(fRotRad) + fCamY * Math.cos(fRotRad);

				var iDeltaY = -iCamHeight;
				var iDeltaX = iCamDist + fTransY;

				var iViewY = ((iDeltaY / iDeltaX) * iCamDist + iCamHeight) - iViewHeight;
				var fViewX = -(fTransX / (fTransY + iCamDist)) * iCamDist;

				fSprite[0][i].div.style.zIndex = Math.round(10000 - fTransY);

				fSprite[0][i].draw(
					((iWidth/2) + fViewX) * iScreenScale, 
					(iHeight - iViewY - correctZ(fSprite[5])) * iScreenScale,
					fFocal / (fFocal + (fTransY)) / 1.5
				);
			}


			for (var j=0;j<fauxobjets.length;j++) {
				fSprite = fauxobjets[j];
				var fCamX = fSprite[3] - posX;
				var fCamY = fSprite[4] - posY;

				var fRotRad = fRotation * Math.PI / 180;

				var fTransX = fCamX * Math.cos(fRotRad) - fCamY * Math.sin(fRotRad);
				var fTransY = fCamX * Math.sin(fRotRad) + fCamY * Math.cos(fRotRad);

				var iDeltaY = -iCamHeight;
				var iDeltaX = iCamDist + fTransY;

				var iViewY = ((iDeltaY / iDeltaX) * iCamDist + iCamHeight) - iViewHeight;
				var fViewX = -(fTransX / (fTransY + iCamDist)) * iCamDist;

				fSprite[0][i].div.style.zIndex = Math.round(10000 - fTransY);

				fSprite[0][i].draw(
					((iWidth/2) + fViewX) * iScreenScale, 
					(iHeight - iViewY - correctZ(fSprite[5])) * iScreenScale,
					fFocal / (fFocal + (fTransY))
				);
			}


			for (var j=0;j<carapaces.length;j++) {
				fSprite = carapaces[j];

				var fNewPosX;
				var fNewPosY;
					
				var fMoveX = 8 * direction(0, fSprite[6]), fMoveY = 8 * direction(1, fSprite[6]);
				
				if (!i && fSprite[6] != -1) {
					fNewPosX = fSprite[3] + fMoveX;
					fNewPosY = fSprite[4] + fMoveY;

					for (var k=0;k<oPlayers.length;k++)
						fSprite[0][k].setState(1-fSprite[0][k].getState());
				}
				else {
					fNewPosX = fSprite[3];
					fNewPosY = fSprite[4];
				}

				var fCamX = fSprite[3] - posX;
				var fCamY = fSprite[4] - posY;

				var fRotRad = fRotation * Math.PI / 180;

				var fTransX = fCamX * Math.cos(fRotRad) - fCamY * Math.sin(fRotRad);
				var fTransY = fCamX * Math.sin(fRotRad) + fCamY * Math.cos(fRotRad);

				var iDeltaY = -iCamHeight;
				var iDeltaX = iCamDist + fTransY;

				var iViewY = ((iDeltaY / iDeltaX) * iCamDist + iCamHeight) - iViewHeight;
				var fViewX = -(fTransX / (fTransY + iCamDist)) * iCamDist;

				fSprite[0][i].div.style.zIndex = Math.round(10000 - fTransY);

				fSprite[0][i].draw(
					((iWidth/2) + fViewX) * iScreenScale, 
					(iHeight - iViewY - correctZ(fSprite[5])) * iScreenScale,
					fFocal / (fFocal + (fTransY)) / 1.5
				);

				if (!i) {
					var roundX1 = Math.round(fSprite[3]);
					var roundY1 = Math.round(fSprite[4]);
					var roundX2 = Math.round(fNewPosX);
					var roundY2 = Math.round(fNewPosY);

					if (((fSprite[6] != -1) && tombe(roundX1, roundY1)) || touche_banane(roundX1, roundY1) || touche_banane(roundX2, roundY2) || touche_crouge(roundX1, roundY1) || touche_crouge(roundX2, roundY2) || touche_cverte(roundX1, roundY1, j) || touche_cverte(roundX2, roundY2, j)) {
						detruit(carapaces,j,true);
						j--;
					}

					else if ((fSprite[6] == -1) || canMoveTo(fSprite[3],fSprite[4], fMoveX,fMoveY)) {
						fSprite[3] = fNewPosX;
						fSprite[4] = fNewPosY;
					}
					else {
						fSprite[7]--;
						if (fSprite[7] > 0) {
							var horizontality = getHorizontality(fSprite[3],fSprite[4], fMoveX,fMoveY);
							var normalAngle = Math.atan2(-horizontality[1],horizontality[0])*180/Math.PI;
							var angleToNormal = normalizeAngle(fSprite[6]-normalAngle, 180);
							fSprite[6] = normalizeAngle(fSprite[6]-2*angleToNormal+180, 360);
						}
						else {
							detruit(carapaces,j);
							j--;
						}
					}
				}
			}


			for (var j=0;j<carapacesRouge.length;j++) {
				fSprite = carapacesRouge[j];

				if (!fSprite[0][i].div.style.opacity) {

					var fNewPosX;
					var fNewPosY;

					for (var l=0;l<2;l++) {
						if (!i && fSprite[6] != -1) {
							var fMoveX;
							var fMoveY;

							if (!l) {
								for (var k=0;k<oPlayers.length;k++)
									fSprite[0][k].setState(1-fSprite[0][k].getState());
							}

							var iLocal = oMap.aipoints[0];
							if (fSprite[8] != -1) {
								fMoveX = iLocal[fSprite[8]][0] - fSprite[3];
								fMoveY = iLocal[fSprite[8]][1] - fSprite[4];
								var oBox = iLocal[fSprite[8]];
								if (fSprite[3] > oBox[0] - 10 && fSprite[3] < oBox[0] + 10 && fSprite[4] > oBox[1] - 10 && fSprite[4] < oBox[1] + 10) {
									if (fSprite[8] < iLocal.length - 1) fSprite[8]++;
									else fSprite[8] = 0;
								}
								var fNewMove = Math.sqrt(fMoveX*fMoveX + fMoveY*fMoveY)/5;
								fMoveX /= fNewMove;
								fMoveY /= fNewMove;
							}
							else {
								if (course != "BB") {
									for (var k=0;k<iLocal.length;k++) {
										var oBox = iLocal[k];
										if (fSprite[3] > oBox[0] - 35 && fSprite[3] < oBox[0] + 35 && fSprite[4] > oBox[1] - 35 && fSprite[4] < oBox[1] + 35) {
											fSprite[8] = k + 1;
											if (fSprite[8] == iLocal.length) fSprite[8] = 0;
											k = iLocal.length;
										}
									}
								}
								fMoveX = 5 * direction(0, fSprite[6]);
								fMoveY = 5 * direction(1, fSprite[6]);
							}

							fNewPosX = fSprite[3] + fMoveX;
							fNewPosY = fSprite[4] + fMoveY;

							var tCible;
							var maxDist = 1000;

							for (var k=0;k<aKarts.length;k++) {
								var pCible = aKarts[k];
								if (pCible.id != fSprite[7] && !sameTeam(fSprite[2],pCible.team) && !pCible.tombe && !pCible.loose) {
									var fDist = Math.pow(pCible.x-fNewPosX, 2) + Math.pow(pCible.y-fNewPosY, 2);
									if (fDist < maxDist) {
										fNewPosX = pCible.x;
										fNewPosY = pCible.y;
										maxDist = fDist;
										tCible = pCible;
									}
								}
								if (tCible && tCible.using[0] && (tCible.using[0] != fauxobjets)) {
									var rAngle = Math.atan2(fSprite[4]-fNewPosY,fSprite[3]-fNewPosX) - (90-tCible.rotation)*Math.PI/180;
									var pi2 = Math.PI*2;
									while (rAngle < 0)
										rAngle += pi2;
									while (rAngle > pi2)
										rAngle -= pi2;
									if (rAngle > Math.PI)
										rAngle = pi2-rAngle;
									if (Math.abs(rAngle) > 2) {
										if (isOnline) {
											detruit(carapacesRouge,j);
											j--;
										}
										else
											fSprite[0][i].div.style.opacity = 0.8;
										fNewPosX -= 5 * direction(0,tCible.rotation);
										fNewPosY -= 5 * direction(1,tCible.rotation);
										detruit(tCible.using[0],tCible.using[1],true);
										l = 1;
									}
									else {
										tCible.using[0][tCible.using[1]][3] -= 2 * direction(0,tCible.rotation);
										tCible.using[0][tCible.using[1]][4] -= 2 * direction(1,tCible.rotation);
									}
								}
							}
							fNewPosX = Math.round(fNewPosX);
							fNewPosY = Math.round(fNewPosY);
						}
						else {
							fNewPosX = fSprite[3];
							fNewPosY = fSprite[4];
						}


						if (i || ((fSprite[7] == -1 || (!tombe(fNewPosX, fNewPosY) && canMoveTo(fSprite[3],fSprite[4], fMoveX,fMoveY))) && !touche_banane(fNewPosX, fNewPosY) && !touche_banane(fSprite[3], fSprite[4]) && !touche_crouge(fNewPosX, fNewPosY, j) && !touche_crouge(fSprite[3], fSprite[4], j) && !touche_cverte(fNewPosX, fNewPosY) && !touche_cverte(fSprite[3], fSprite[4]))) {
							fSprite[3] = fNewPosX;
							fSprite[4] = fNewPosY;
							if (l) {
								var fCamX = fSprite[3] - posX;
								var fCamY = fSprite[4] - posY;

								var fRotRad = fRotation * Math.PI / 180;

								var fTransX = fCamX * Math.cos(fRotRad) - fCamY * Math.sin(fRotRad);
								var fTransY = fCamX * Math.sin(fRotRad) + fCamY * Math.cos(fRotRad);

								var iDeltaY = -iCamHeight;
								var iDeltaX = iCamDist + fTransY;

								var iViewY = ((iDeltaY / iDeltaX) * iCamDist + iCamHeight) - iViewHeight;
								var fViewX = -(fTransX / (fTransY + iCamDist)) * iCamDist;

								fSprite[0][i].div.style.zIndex = Math.round(10000 - fTransY);

								fSprite[0][i].draw(
									((iWidth/2) + fViewX) * iScreenScale, 
									(iHeight - iViewY - correctZ(fSprite[5])) * iScreenScale,
									fFocal / (fFocal + (fTransY)) / 1.5
								);
							}
						}
						else if (!i) {
							if (isOnline) {
								detruit(carapacesRouge,j);
								j--;
							}
							else
								fSprite[0][i].div.style.opacity = 0.8;
							l = 1;
						}
					}
				}
				else if (!i) {
					var setOpac = fSprite[0][0].div.style.opacity-0.2;
					for (var k=0;k<strPlayer.length;k++)
						fSprite[0][k].div.style.opacity = setOpac;
					if (setOpac < 0.01) {
						detruit(carapacesRouge,j);
						j--;
					}
				}
			}


			for (var j=0;j<carapacesBleue.length;j++) {
				fSprite = carapacesBleue[j];

				var fCamX = fSprite[3] - posX;
				var fCamY = fSprite[4] - posY;

				var fRotRad = fRotation * Math.PI / 180;

				var fTransX = fCamX * Math.cos(fRotRad) - fCamY * Math.sin(fRotRad);
				var fTransY = fCamX * Math.sin(fRotRad) + fCamY * Math.cos(fRotRad);

				var iDeltaY = -iCamHeight;
				var iDeltaX = iCamDist + fTransY;

				var iViewY = ((iDeltaY / iDeltaX) * iCamDist + iCamHeight) - iViewHeight;
				var fViewX = -(fTransX / (fTransY + iCamDist)) * iCamDist;
				
				var cible = -1;
				for (var k=0;k<aKarts.length;k++) {
					if (aKarts[k].id == fSprite[5]) {
						cible = k;
						break;
					}
				}
				if (cible == -1) {
					cible = aKarts.length-1;
					var cPlace = 1;
					for (k=0;k<aKarts.length;k++) {
						if (aKarts[k].place == cPlace) {
							if (((aKarts[k].tours <= oMap.tours) || (course == "BB")) && !sameTeam(fSprite[2],aKarts[k].team)) {
								fSprite[5] = aKarts[k].id;
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

				var fMoveX = fSprite[3] - aKarts[cible].x;
				var fMoveY = fSprite[4] - aKarts[cible].y;

				var size = 1;
				if (fSprite[6] > 0) {
					if (!i) {
						if (Math.abs(fMoveX*fMoveY) > 100) {
							var fNewMove = Math.sqrt(Math.pow(fMoveX,2) + Math.pow(fMoveY,2))/10;
							fMoveX /= fNewMove;
							fMoveY /= fNewMove;

							for (var k=0;k<oPlayers.length;k++)
								fSprite[0][k].setState(1-fSprite[0][k].getState());
						}
						else {
							fSprite[0][i].setState(Math.round(Math.random()));
							fSprite[6]--;
							if (fSprite[6]) {
								fViewX += fSprite[6] - 2.5;
								iViewY -= Math.abs(5-fSprite[6]);
							}
							else {
								var fLoad;
								for (var k=0;k<strPlayer.length;k++) {
									makeSpriteExplode(fSprite,"explosionB",k);
									if (fSprite[0][k].div.style.display == "block")
										fLoad = k;
								}
								if (!isOnline && (fLoad != undefined)) {
									fSprite[0][fLoad].img.onload = function() {
										bCounting = false;
										fSprite[0][fLoad].img.onload = undefined;
										reprendre(false);
										playDistSound(aKarts[cible],"musics/events/boom.mp3",200);
									}
									bCounting = true;
									pause = true;
								}
								else
									playDistSound(aKarts[cible],"musics/events/boom.mp3",200);
								fMoveX *= aKarts[cible].speed/2;
								fMoveY *= aKarts[cible].speed/2;
								for (var k=0;k<oPlayers.length;k++)
									fSprite[0][k].setState(0);
								fSprite[0][i].div.style.opacity = 1;
							}
						}

						fSprite[3] -= fMoveX;
						fSprite[4] -= fMoveY;
					}
				}
				else {
					if (!bCounting)
						size = 10;
					if (!i) {
						if (isOnline && (fSprite[5] == oPlayers[0].id) && (fSprite[6] < -10))
							fSprite[6] = 0;
						fSprite[6]--;
						for (var k=0;k<oPlayers.length;k++)
							fSprite[0][k].div.style.opacity = Math.max(1+fSprite[6]/10,0);
						var delLimit = (isOnline&&(fSprite[5]!=oPlayers[0].id)) ? -70:-10;
						if (fSprite[6] < delLimit) {
							detruit(carapacesBleue,j);
							size = false;
							j--;
						}
					}
				}

				if (size) {

					fSprite[0][i].div.style.zIndex = Math.round(10000 - fTransY);

					fSprite[0][i].draw(
						((iWidth/2) + fViewX) * iScreenScale, 
						(iHeight - iViewY - (fSprite[6] > 0 ? 15 + aKarts[cible].speed : 0)) * iScreenScale,
						fFocal / (fFocal + (fTransY)) * size
					);
				}
			}


			for (var j=0;j<bobombs.length;j++) {
				fSprite = bobombs[j];

				var fCamX = fSprite[3] - posX;
				var fCamY = fSprite[4] - posY;

				var fRotRad = fRotation * Math.PI / 180;

				var fTransX = fCamX * Math.cos(fRotRad) - fCamY * Math.sin(fRotRad);
				var fTransY = fCamX * Math.sin(fRotRad) + fCamY * Math.cos(fRotRad);

				var iDeltaY = -iCamHeight;
				var iDeltaX = iCamDist + fTransY;

				var iViewY = ((iDeltaY / iDeltaX) * iCamDist + iCamHeight) - iViewHeight;
				var fViewX = -(fTransX / (fTransY + iCamDist)) * iCamDist;

				var size = 1;
				var hauteur = 0;

				if (fSprite[6] != -1) {
					if (fSprite[7]) {
						if (!i) {
							fSprite[7]--;
							var fMoveX = 15 * direction(0, fSprite[6]);
							var fMoveY = 15 * direction(1, fSprite[6]);

							var fNewPosX = fSprite[3] + fMoveX;
							var fNewPosY = fSprite[4] + fMoveY;

							fSprite[3] = fNewPosX;
							fSprite[4] = fNewPosY;
						}
						hauteur = fSprite[7];
					}
					else {
						if (tombe(Math.round(fSprite[3]), Math.round(fSprite[4]))) {
							detruit(bobombs, j);
							size = false;
							j--;
						}
						if (!i) {
							if (--fSprite[8] == 30)
								fSprite[8] -= 12;
						}
						if (!fSprite[8]) {
							if (!i) {
								var fLoad;
								for (var k=0;k<strPlayer.length;k++) {
									makeSpriteExplode(fSprite,"explosion",k);
									if (fSprite[0][k].div.style.display == "block")
										fLoad = k;
								}
								if (!isOnline && (fLoad != undefined)) {
									fSprite[0][fLoad].img.onload = function() {
										bCounting = false;
										fSprite[0][fLoad].img.onload = undefined;
										reprendre(false);
										playDistSound({x:fSprite[3],y:fSprite[4]},"musics/events/boom.mp3",200);
									}
									bCounting = true;
									pause = true;
								}
								else
									playDistSound({x:fSprite[3],y:fSprite[4]},"musics/events/boom.mp3",200);
								fSprite[0][i].div.style.opacity = 1;
							}
						}
						if (fSprite[8] <= 0) {
							if (!bCounting)
								size = 10;
							if (!i) {
							for (var k=0;k<oPlayers.length;k++)
								fSprite[0][k].div.style.opacity = 1+fSprite[8]/10;
								if (fSprite[8] < -10) {
									detruit(bobombs,j);
									size = false;
									j--;
								}
							}
						}
					}
				}
				if (size) {
					fSprite[0][i].div.style.zIndex = Math.round(10000 - fTransY);

					var spriteZ = correctZ(fSprite[5] + (- Math.abs(hauteur-8) + 8) * 2);

					fSprite[0][i].draw(
						((iWidth/2) + fViewX) * iScreenScale, 
						(iHeight - iViewY - spriteZ) * iScreenScale,
						fFocal / (fFocal + (fTransY)) * size,
						spriteZ
					);
				}
			}


			oPlayers[i].sprite[i].div.style.zIndex = 10000;
			oPlayers[i].sprite[i].draw(iOffsetX,iOffsetY,oPlayers[i].size,correctZ(oPlayers[i].z));
			if (course == "BB") {
				var nbBallons = oPlayers[i].ballons.length;
				var pTaille = (oPlayers[i].sprite[i].h-32)*oPlayers[i].size/5;
				for (j=0;j<nbBallons;j++) {
					oPlayers[i].ballons[j][i].draw(
						(iOffsetX+(2*oPlayers[i].size+(j-nbBallons/2)*2.5*oPlayers[i].size)*iScreenScale), 
						(iOffsetY-(oPlayers[i].size*6+pTaille)*iScreenScale),
						oPlayers[i].size / 2,
						6*oPlayers[i].size+pTaille
					);
				}
			}

			for (var j=0;j<aKarts.length;j++) {
				var oKart = aKarts[j];
				var oSprite = oKart.sprite[i];
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
				oBgLayers[j].draw(fRotation, i);

			if (strPlayer.length == 1)
				setPlanPos();
		}
	}
}
function makeSpriteExplode(fSprite,src,k) {
	switch (fSprite[2]) {
	case 0:
		src = "explosionB";
		break;
	case 1:
		src = "explosionR";
		break;
	}
	fSprite[0][k].img.src = "images/sprites/sprite_"+src+".png";
	var oDivs = fSprite[0][k].div.getElementsByClassName("sprite-hallow");
	if (oDivs.length)
		fSprite[0][k].div.removeChild(oDivs[0]);
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
function possibleObjs(oKart, except) {
	var a = Math.floor((oKart.place-1)*120/aKarts.length);
	var b = Math.floor(oKart.place*120/aKarts.length);
	var res = {};
	for (var i=a;i<b;i++) {
		if (!except[objets[i]])
			res[objets[i]] = true;
	}
	return res;
}
function otherObjects(oKart, blackList) {
	var except = {};
	for (var i=0;i<blackList.length;i++)
		except[blackList[i]] = true;
	var pObjs = possibleObjs(oKart, except);
	return Object.getOwnPropertyNames(pObjs).length>0;
}

function friendlyFire(kart,oKart) {
	return (kart == oKart || (iTeamPlay && (kart.team==oKart.team)));
}
function sameTeam(team1,team2) {
	if (team1 == -1)
		return false;
	return (team1 == team2);
}

function addNewBalloon(oKart,team) {
	oKart.ballons.push(createBalloonSprite(oKart,team));
}
function createBalloonSprite(oKart,team) {
	if (team === undefined) team = oKart.team;
	return new Sprite((team==1) ? "ballonR":"ballon");
}
function balloonSrc(team) {
	return 'images/sprites/sprite_'+(team==1?'ballonR':'ballon')+'.png';
}

function detruit(cible, id, sound) {
	if (cible[id]) {
		if (isOnline) {
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
		}
		supprime(cible, id, sound);
	}
}
function supprime(cible, id, sound) {
	cible[id][0][0].suppr();
	for (var i=0;i<aKarts.length;i++) {
		var oUsing = aKarts[i].using;
		if (oUsing[0] == cible && id <= oUsing[1]) {
			if (id != oUsing[1]) oUsing[1]--;
			else {
				aKarts[i].using = [false];
				if (sound) {
					if (playIfShould(aKarts[i],"musics/events/hit.mp3"))
						sound = false;
				}
			}
		}
	}
	if (typeof(sound) == "object")
		playDistSound(sound,"musics/events/hit.mp3",80);
	if (clLocalVars.myItems) {
		var mhId = clLocalVars.myItems.indexOf(cible[id]);
		if (mhId != -1)
			clLocalVars.myItems.splice(mhId,1);
	}
	cible.splice(id,1);
}


function supprArme(i) {
	var oKart = aKarts[i];
	oKart.arme = false;
	oKart.roulette = 0;
	if (kartIsPlayer(oKart)) {
		document.getElementById("roulette"+i).innerHTML = "";
		document.getElementById("scroller"+i).style.visibility = "hidden";
		removeIfExists(oKart.rouletteSound);
	}
}

function stopDrifting(i) {
	var oKart = aKarts[i];
	if (kartIsPlayer(oKart)) {
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

function resetSpriteHeight(sprite) {
	sprite.lastH = sprite.h;
	sprite.h = 32;
}
function resumeSpriteHeight(sprite) {
	if (sprite.lastH) {
		sprite.h = sprite.lastH;
		delete sprite.lastH;
	}
}

function pCol(oKart) {
	for (var i=0;i<strPlayer.length;i++) {
		if (!friendlyFire(oKart,oPlayers[i]) && (course!="BB"||(oKart.ballons.length&&oPlayers[i].ballons.length)) && Math.pow(oKart.x-oPlayers[i].x, 2) + Math.pow(oKart.y-oPlayers[i].y, 2) < 25 && Math.max(oKart.z,oPlayers[i].z) < 2 && !oKart.tourne && Math.abs(oPlayers[i].speed - oKart.speed) < 2) {
			var oVictim = oPlayers[i].speed < oKart.speed ? oKart : oPlayers[i];
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
}

function pointInRectangle(x,y, oBox) {
	return (x > oBox[0] && x < oBox[0]+oBox[2] && y > oBox[1] && y < oBox[1]+oBox[3]);
}
function pointInPolygon(x,y, vs) {
	var inside = false;
	for (var i = 0, j = vs.length - 1; i < vs.length; j = i++) {
		var xi = vs[i][0], yi = vs[i][1];
		var xj = vs[j][0], yj = vs[j][1];
		
		var intersect = ((yi > y) != (yj > y))
			&& (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
		if (intersect) inside = !inside;
	}
	
	return inside;
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
					oMap.decor[i][2][0].suppr();
					oMap.decor.splice(i,1);
					break;
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

	var oRectangles = oMap.collision.rectangle;
	for (var i=0;i<oRectangles.length;i++) {
		if (pointInRectangle(iX,iY, oRectangles[i]))
			return true;
	}
	var oPolygons = oMap.collision.polygon;
	for (var i=0;i<oPolygons.length;i++) {
		if (pointInPolygon(iX,iY, oPolygons[i]))
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

	for (var i=0;i<oRectangles.length;i++) {
		var oBox = oRectangles[i];
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
	for (var i=0;i<oPolygons.length;i++) {
		var oPoints = oPolygons[i];
		for (var j=0;j<oPoints.length;j++) {
			var oPoint1 = oPoints[j], oPoint2 = oPoints[(j+1)%oPoints.length];
			if (secants(iX,iY,nX,nY, oPoint1[0],oPoint1[1],oPoint2[0],oPoint2[1]))
				return false;
		}
	}
	return true;
}

function getLineHorizontality(iX,iY, nX,nY, lines) {
	for (var i=0;i<lines.length;i++) {
		var line = lines[i];
		var cross = secants(iX,iY,nX,nY,line.x1,line.y1,line.x2,line.y2);
		if (cross) {
			return {
				"dir" : [line.x2-line.x1,line.y2-line.y1],
				"t" : cross[0]
			};
		}
	}
	return null;
}

function secants(x11,y11,x21,y21, x12,y12,x22,y22) {
    var den = -x21*y22 + x21*y12 + x11*y22 - x11*y12 + x22*y21 - x22*y11 - x12*y21 + x12*y11;
    var l = (-x22*y11 + x12*y11 - x11*y12 + x11*y22 + x22*y12 - x12*y22)/den;
    if ((l >= 0) && (l <= 1)) {
        var m = (x11*y21 - x21*y11 + x21*y12 - x11*y12 - x12*y21 + x12*y11)/den;
        if ((m >= 0) && (m <= 1))
            return [l, m];
    }
    return null;
}

function getHorizontality(iX,iY, iI,iJ) {
	var nearCol = {
		"t" : 1
	};
	var nX = iX+iI, nY = iY+iJ;
	if (!isCup) {
		if ((course == "BB") || (oMap.map <= 20)) {
			if (nX > (oMap.w-5) || nX < 4) nearCol.dir = [0,oMap.h];
			if (nY > (oMap.h-5) || nY < 4) nearCol.dir = [oMap.w,0];
		}
		else {
			if (nX >= oMap.w || nX < 0) nearCol.dir = [0,oMap.h];
			if (nY >= oMap.h || nY < 0) nearCol.dir = [oMap.w,0];
		}
	}
	if (oMap.decor) {
		for (var i=1;i<oMap.decor.length;i++) {
			var oBox = oMap.decor[i];
			var lines = [{
				"x1" : oBox[0]-5,
				"y1" : oBox[1]-5,
				"x2" : oBox[0]+5,
				"y2" : oBox[1]-5
			},{
				"x1" : oBox[0]-5,
				"y1" : oBox[1]-5,
				"x2" : oBox[0]-5,
				"y2" : oBox[1]+5
			},{
				"x1" : oBox[0]-5,
				"y1" : oBox[1]+5,
				"x2" : oBox[0]+5,
				"y2" : oBox[1]+5
			},{
				"x1" : oBox[0]+5,
				"y1" : oBox[1]-5,
				"x2" : oBox[0]+5,
				"y2" : oBox[1]+5
			}];
			var colLine = getLineHorizontality(iX,iY, nX,nY, lines);
			if (colLine && (colLine.t < nearCol.t))
				nearCol = colLine;
		}
	}
	if (oMap.collision) {
		var oRectangles = oMap.collision.rectangle;
		for (var i=0;i<oRectangles.length;i++) {
			var oBox = oRectangles[i];
			var lines = [{
				"x1" : oBox[0],
				"y1" : oBox[1],
				"x2" : oBox[0]+oBox[2],
				"y2" : oBox[1]
			},{
				"x1" : oBox[0],
				"y1" : oBox[1],
				"x2" : oBox[0],
				"y2" : oBox[1]+oBox[3]
			},{
				"x1" : oBox[0]+oBox[2],
				"y1" : oBox[1],
				"x2" : oBox[0]+oBox[2],
				"y2" : oBox[1]+oBox[3]
			},{
				"x1" : oBox[0],
				"y1" : oBox[1]+oBox[3],
				"x2" : oBox[0]+oBox[2],
				"y2" : oBox[1]+oBox[3]
			}];
			var colLine = getLineHorizontality(iX,iY, nX,nY, lines);
			if (colLine && (colLine.t < nearCol.t))
				nearCol = colLine;
		}
		var oPolygons = oMap.collision.polygon;
		for (var i=0;i<oPolygons.length;i++) {
			var lines = [];
			var oPoints = oPolygons[i];
			for (var j=0;j<oPoints.length;j++) {
				var oPoint1 = oPoints[j], oPoint2 = oPoints[(j+1)%oPoints.length];
				lines.push({
					"x1" : oPoint1[0],
					"y1" : oPoint1[1],
					"x2" : oPoint2[0],
					"y2" : oPoint2[1]
				})
			}
			var colLine = getLineHorizontality(iX,iY, nX,nY, lines);
			if (colLine && (colLine.t < nearCol.t))
				nearCol = colLine;
		}
	}
	if (nearCol.dir) {
		var norm = Math.hypot(nearCol.dir[0],nearCol.dir[1]);
		nearCol.dir[0] /= norm;
		nearCol.dir[1] /= norm;
	}
	else
		nearCol.dir = [0.7,0.7];
	return nearCol.dir;
}
function normalizeAngle(angle, modulo) {
	return angle-modulo*Math.round(angle/modulo);
}

function objet(iX, iY) {
	for (var i=0;i<oMap.arme.length;i++) {
		var oBox = oMap.arme[i];
		if (iX > oBox[0] - 7 && iX < oBox[0] + 7 && iY > oBox[1] - 7 && iY < oBox[1] + 7 && isNaN(oBox[2])) {
			for (var i=0;i<strPlayer.length;i++)
				oBox[2][i].div.style.display = "none";
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
		if (pointInRectangle(iX,iY, oBox))
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
		var oRectangles = hp.rectangle;
		for (var i=0;i<oRectangles.length;i++) {
			if (pointInRectangle(iX,iY, oRectangles[i]))
				return type;
		}
		var oPolygons = hp.polygon;
		for (var i=0;i<oPolygons.length;i++) {
			if (pointInPolygon(iX,iY, oPolygons[i]))
				return type;
		}
	}
	return false;
}

function accelere(iX, iY) {
	if (!oMap.accelerateurs) return false;
	for (var i=0;i<oMap.accelerateurs.length;i++) {
		var oBox = oMap.accelerateurs[i];
		if (pointInRectangle(iX,iY, oBox))
			return true;
	}
	return false;
}

function tombe(iX, iY, iC) {
	if (iX > oMap.w || iY > oMap.h || iX < 0 || iY < 0) {
		var rotation;
		if (oMap.startposition[2] != undefined)
			rotation = oMap.startposition[2];
		else if (oMap.startrotation != undefined)
			rotation = oMap.startrotation/90;
		else
			rotation = 2;
		return (course=="BB") ? true:[oMap.startposition[0],oMap.startposition[1], rotation];
	}

	if (!oMap.trous) return false;

	var fTrou;
	for (var j=0;j<4;j++) {
		var oRectangles = oMap.trous[j].rectangle;
		for (var i=0;i<oRectangles.length;i++) {
			var oHole = oRectangles[i];
			if (pointInRectangle(iX,iY, oHole[0])) {
				if (iC == undefined)
					return true;
				fTrou = [oHole[1][0],oHole[1][1],j];
				if (j%2 - iC)
					return fTrou;
			}
		}
		var oPolygons = oMap.trous[j].polygon;
		for (var i=0;i<oPolygons.length;i++) {
			var oHole = oPolygons[i];
			if (pointInPolygon(iX,iY, oHole[0])) {
				if (iC == undefined)
					return true;
				fTrou = [oHole[1][0],oHole[1][1],j];
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

var lambdaReturnsTrue = function(scope){return true};
var challengeRules = {
	"finish_circuit": {
		"verify": "end_game",
		"success": lambdaReturnsTrue
	},
	"finish_circuit_first": {
		"verify": "end_game",
		"success": function(scope) {
			return (oPlayers[0].place == 1);
		}
	},
	"finish_circuit_time": {
		"verify": "end_game",
		"success": function(scope) {
			var seconds = (timer-1)*67/1000;
			return (seconds <= scope.value);
		}
	},
	"finish_arena": {
		"verify": "end_game",
		"success": lambdaReturnsTrue
	},
	"finish_arena_first": {
		"verify": "end_game",
		"success": function(scope) {
			return (oPlayers[0].place == 1);
		}
	},
	"hit": {
		"verify": "each_hit",
		"initLocalVars": function(scope) {
			clLocalVars.myItems = [];
			clLocalVars.nbHits = 0;
		},
		"success": function(scope) {
			return (clLocalVars.nbHits >= scope.value);
		}
	},
	"eliminate": {
		"verify": "each_kill",
		"initLocalVars": function(scope) {
			clLocalVars.myItems = [];
			clLocalVars.killed = [];
			clLocalVars.nbKills = 0;
			clLocalVars.nbHits = 0;
		},
		"success": function(scope) {
			return (clLocalVars.nbKills >= scope.value);
		}
	},
	"survive": {
		"verify": "each_frame",
		"success": function(scope) {
			var seconds = (timer-1)*67/1000;
			return (seconds >= scope.value);
		}
	},
	"reach_zone": {
		"verify": "each_frame",
		"success": function(scope) {
			var zones = scope.value;
			var posX = oPlayers[0].x;
			var posY = oPlayers[0].y;
			for (var i=0;i<zones.length;i++) {
				var iZone = zones[i];
				if ((posX >= iZone[0]) && (posY >= iZone[1]) && (posX < (iZone[0]+iZone[2])) && (posY < (iZone[1]+iZone[3])))
					return true;
			}
		}
	},
	"gold_cup": {
		"verify": "end_gp",
		"initRuleVars": function() {
			return {nbcircuits: 0};
		},
		success: function(scope, ruleVars) {
			if (clLocalVars.endGP && (ruleVars.nbcircuits == 4))
				return (oPlayers[0].place == 1);
		},
		"next_circuit": function(ruleVars) {
			ruleVars.nbcircuits++;
		}
	},
	"gold_cups": {
		"verify": "end_gp",
		"initRuleVars": function(challenge) {
			return {challenge: challenge, nbcircuits: 0};
		},
		success: function(scope, ruleVars) {
			if (clLocalVars.endGP && (ruleVars.nbcircuits == 4)) {
				if (oPlayers[0].place == 1) {
					var succeededCups = sessionStorage.getItem("cl"+ruleVars.challenge.id+".gold_cups");
					if (!succeededCups) succeededCups = '{}';
					succeededCups = JSON.parse(succeededCups);
					if (!ruleVars.challenge.data.constraints.length) {
						for (var i=0;i<ptsGP.length;i++) {
							if (ptsGP.charAt(i) == 3)
								succeededCups[cupIDs[i]] = true;
						}
					}
					var iCup = cupIDs[oMap.ref/4-1];
					succeededCups[iCup] = true;
					var won = true;
					for (var i=0;i<cupIDs.length;i++) {
						var cupID = cupIDs[i];
						if (!succeededCups[cupID]) {
							won = false;
							break;
						}
					}
					if (won) {
						sessionStorage.removeItem("cl"+ruleVars.challenge.id+".gold_cups");
						return true;
					}
				}
				else
					return false;
			}
		},
		"next_circuit": function(ruleVars) {
			ruleVars.nbcircuits++;
		},
		finish_gp: function(ruleVars) {
			if (ruleVars.nbcircuits == 4) {
				var succeededCups = sessionStorage.getItem("cl"+ruleVars.challenge.id+".gold_cups");
				if (!succeededCups) succeededCups = '{}';
				succeededCups = JSON.parse(succeededCups);
				var iCup = cupIDs[oMap.ref/4-1];
				succeededCups[iCup] = true;
				sessionStorage.setItem("cl"+ruleVars.challenge.id+".gold_cups", JSON.stringify(succeededCups));
				var simpleChallenge = !ruleVars.challenge.data.constraints.length;
				if (simpleChallenge) {
					for (var i=0;i<ptsGP.length;i++) {
						if (ptsGP.charAt(i) == 3)
							succeededCups[cupIDs[i]] = true;
					}
				}
				showChallengePartialSuccess(ruleVars.challenge, {nb:Object.keys(succeededCups).length,total:cupIDs.length,warning:!simpleChallenge});
			}
		}
	},
	"finish_circuits_first": {
		"verify": "end_game",
		"initRuleVars": function() {
			return {nbcircuits: 1};
		},
		"success": function(scope, ruleVars) {
			if (oPlayers[0].place != 1) return false;
			if (ruleVars.nbcircuits >= scope.value) return true;
		},
		"next_circuit": function(ruleVars) {
			ruleVars.nbcircuits++;
		}
	},
	"pts_greater": {
		"verify": "end_game",
		"initRuleVars": function() {
			if (clGlobalVars.nbcircuits) return {};
			return {nbcircuits: 1, initialscore: 0};
		},
		"success": function(scope, ruleVars) {
			if ((ruleVars.nbcircuits == scope.value) && (aScores[0] >= scope.pts))
				return true;
		},
		"next_circuit": function(ruleVars) {
			ruleVars.nbcircuits++;
		}
	},
	"pts_equals": {
		"verify": "end_game",
		"initRuleVars": function() {
			if (clGlobalVars.nbcircuits) return {};
			return {nbcircuits: 1};
		},
		"success": function(scope, ruleVars) {
			if ((ruleVars.nbcircuits == scope.value) && (aScores[0] == scope.pts))
				return true;
		},
		"next_circuit": function(ruleVars) {
			ruleVars.nbcircuits++;
		}
	},
	"game_mode": {
		"success": function(scope) {
			var courseValues = ['VS','CM'];
			return (course == courseValues[scope.value]);
		}
	},
	"game_mode_cup": {
		"success": function(scope) {
			var courseValues = ['GP','VS'];
			return (course == courseValues[scope.value]);
		}
	},
	"difficulty": {
		"success": function(scope) {
			return (iDificulty == 4+(2-scope.value)*0.5);
		}
	},
	"no_teams": {
		"success": function(scope) {
			return !iTeamPlay;
		}
	},
	"participants": {
		"success": function(scope) {
			return (aKarts.length == scope.value);
		}
	},
	"balloons": {
		"success": function(scope) {
			if (oPlayers[0].lost && clLocalVars.gagnant != oPlayers[0])
				return false;
			var gagnant = clLocalVars.gagnant;
			return (gagnant.ballons.length+gagnant.reserve >= scope.value);
		}
	},
	"balloons_lost": {
		"success": function(scope) {
			if (oPlayers[0].loose && clLocalVars.gagnant != oPlayers[0])
				return false;
			return (clLocalVars.lostBalloons <= scope.value);
		}
	},
	"no_drift": {
		"success": function(scope) {
			return !clLocalVars.drifted;
		}
	},
	"avoid_items": {
		"success": function(scope) {
			return !clLocalVars.itemsGot;
		}
	},
	"no_item": {
		"success": function(scope) {
			return !clLocalVars.itemsUsed;
		}
	},
	"character": {
		"success": function(scope) {
			return (oPlayers[0].personnage == scope.value);
		}
	},
	"falls": {
		"initRuleVars": function() {
			return {falls: 0};
		},
		"success": function(scope, ruleVars) {
			if (ruleVars)
				return ((clLocalVars.falls+ruleVars.falls) <= scope.value);
		},
		"next_circuit": function(ruleVars) {
			if (ruleVars)
				ruleVars.falls += clLocalVars.falls;
		}
	},
	"no_stunt": {
		"success": function(scope) {
			return !clLocalVars.stunted;
		}
	},
	"time": {
		"success": function(scope) {
			var seconds = (timer-1)*67/1000;
			return (seconds <= scope.value);
		}
	},
	"time_delay": {
		"initLocalVars": function(scope) {
			if (!clLocalVars.delayedStart || (clLocalVars.delayedStart > scope.value))
				clLocalVars.delayedStart = scope.value;
		},
		"success": function(scope) {
			if (!clLocalVars.startedAt) return true;
			var seconds = (clLocalVars.startedAt-1)*67/1000;
			return (seconds >= scope.value);
		}
	},
	"position": {
		"success": function(scope) {
			return (oPlayers[0].place == scope.value);
		}
	},
	"with_pts": {
		"verify": "end_game",
		"initRuleVars": function() {
			if (clGlobalVars.nbcircuits) return {};
			return {firstAttempt:true};
		},
		"success": function(scope, ruleVars) {
			if (ruleVars.firstAttempt && (aScores[0] >= scope.value))
				return true;
		}
	},
	"different_circuits": {
		"initRuleVars": function() {
			return {played_circuits: {}};
		},
		"success": function(scope, ruleVars) {
			if (ruleVars.played_circuits[oMap.ref])
				return false;
			return true;
		},
		"next_circuit": function(ruleVars) {
			ruleVars.played_circuits[oMap.ref] = true;
		}
	}
};
function addCreationChallenges(type,cid) {
	var creationChallenges = challenges[type][cid];
	if (creationChallenges) {
		var challengesList = creationChallenges.list;
		for (var i=0;i<challengesList.length;i++) {
			var challenge = challengesList[i];
			if (challenge.succeeded)
				continue;
			var challengeData = challenge.data;
			var challengeVerifType = challengeRules[challengeData.goal.type].verify;
			challengesForCircuit[challengeVerifType].push(challenge);
			var chRules = listChallengeRules(challengeData);
			for (var j=0;j<chRules.length;j++)
				initChallengeRule(challenge, chRules[j]);
		}
	}
}
function listChallengeRules(challengeData) {
	var res = challengeData.constraints.slice(0);
	res.unshift(challengeData.goal);
	return res;
}
function initChallengeRule(challenge, rule) {
	if (challengeRules[rule.type].initRuleVars) {
		if (!clRuleVars[challenge.id])
			clRuleVars[challenge.id] = {};
		if (!clRuleVars[challenge.id][rule.type])
			clRuleVars[challenge.id][rule.type] = challengeRules[rule.type].initRuleVars(challenge);
	}
}
function reinitChallengeVars() {
	for (var verifType in challengesForCircuit) {
		var challengesForType = challengesForCircuit[verifType];
		for (var i=0;i<challengesForType.length;i++) {
			var challenge = challengesForType[i];
			var challengeData = challenge.data;
			var chRules = listChallengeRules(challengeData);
			for (var j=0;j<chRules.length;j++)
				initChallengeRule(challenge, chRules[j]);
		}
	}
	reinitLocalVars();
}
function reinitLocalVars() {
	clLocalVars = {
		drifted: false,
		stunted: false,
		itemsGot: false,
		itemsUsed: false,
		falls: 0,
		lostBalloons: 0,
		cheated: false
	};
	for (var verifType in challengesForCircuit) {
		var challengesForType = challengesForCircuit[verifType];
		for (var i=0;i<challengesForType.length;i++) {
			var challenge = challengesForType[i];
			var challengeData = challenge.data;
			var chRules = listChallengeRules(challengeData);
			for (var j=0;j<chRules.length;j++) {
				var rule = chRules[j];
				if (challengeRules[rule.type].initLocalVars)
					challengeRules[rule.type].initLocalVars(rule);
			}
		}
	}
}
function challengeCheck(verifType, events) {
	if (clLocalVars.cheated)
		return;
	if (strPlayer.length > 1)
		return;
	var challengesForType = challengesForCircuit[verifType];
	for (var i=0;i<challengesForType.length;i++) {
		var challenge = challengesForType[i];
		var status = challengeRulesSatisfied(challenge);
		if (true === status) {
			challengeSucceeded(challenge);
			challengesForType.splice(i,1);
			i--;
		}
		else if (false === status)
			delete clRuleVars[challenge.id];
		else
			challengeHandleEvents(challenge, events);
	}
}
function challengeHandleEvents(challenge, events) {
	if (events) {
		var ruleVars = clRuleVars[challenge.id];
		if (ruleVars) {
			var chRules = listChallengeRules(challenge.data);
			for (var i=0;i<events.length;i++) {
				var event = events[i];
				for (var j=0;j<chRules.length;j++) {
					var rule = chRules[j];
					if (challengeRules[rule.type][event])
						challengeRules[rule.type][event](ruleVars[rule.type]);
				}
			}
		}
	}
}
function challengeRulesSatisfied(challenge) {
	var chRules = listChallengeRules(challenge.data);
	var allOk = true;
	for (var i=0;i<chRules.length;i++) {
		var status = challengeRuleSatisfied(challenge,chRules[i]);
		if (false === status)
			return false;
		else if (true !== status)
			allOk = false;
	}
	if (allOk)
		return true;
	return null;
}
function challengeRuleSatisfied(challenge,rule) {
	var ruleVars = clRuleVars[challenge.id] ? clRuleVars[challenge.id][rule.type] : undefined;
	return challengeRules[rule.type].success(rule,ruleVars);
}
function challengeSucceeded(challenge) {
	if (challenge.succeeded) return;
	challenge.succeeded = true;
	if ("pending_completion" === challenge.status)
		challenge.status = "pending_publication";
	delete clRuleVars[challenge.id];
	xhr("challengeSucceeded.php", "id="+challenge.id, function(res) {
		if (!res)
			return false;
		var data;
		try {
			data = JSON.parse(res);
		} catch (e) {
			return false;
		}
		showChallengePopup(challenge,data);
		return true;
	});
}
function showChallengePartialSuccess(challenge, res) {
	var lastPopup = document.getElementById("challenge-popup-"+challenge.id);
	if (lastPopup) return;
	var oDiv = document.createElement("div");
	oDiv.id = "challenge-popup-"+challenge.id;
	oDiv.className = "challenge-popup challenge-popup-partial";
	oDiv.style.width = (iScreenScale*56) +"px";
	oDiv.style.left = (iScreenScale*12) +"px";
	oDiv.style.top = Math.round(iScreenScale*4.5) +"px";
	oDiv.style.padding = Math.round(iScreenScale*1.5) +"px";
	oDiv.style.paddingBottom = (iScreenScale*5) +"px";
	oDiv.style.border = "inset "+ Math.round(iScreenScale*0.5) +"px #07B";
	oDiv.style.fontSize = (iScreenScale*2) +"px";
	oDiv.style.opacity = 0;
	var challengeTitle = language ? 'Challenge being completed':'Défi en cours de réussite';
	var challengeCongrats = challenge.description.main;
	var challengeAward = language ? 'Cups completed: '+ res.nb +'/'+ res.total:'Coupes réussies: '+ res.nb +'/'+ res.total;
	var challengeDisclaimer = language ? 'Caution, progress will be lost when you close the browser':'Attention, toute progression sera perdue à la fermeture du navigateur.';
	var challengeClose = language ? 'Close':'Fermer';
	oDiv.innerHTML = 
		'<div style="font-size: '+ Math.round(iScreenScale*2) +'px">'+
			'<img src="images/cups/cup2.png" alt="star" class="pixelated" style="width:'+ Math.round(iScreenScale*2.5) +'px" /> '+
			'<h1 class="challenge-popup-title" style="margin:'+ Math.round(iScreenScale/2) +'px 0; font-size: '+ Math.round(iScreenScale*3.25) +'px">'+ challengeTitle +'</h1>'+
		'</div>'+
		'<div class="challenge-popup-header" style="font-size: '+ Math.round(iScreenScale*2.25) +'px">'+challengeCongrats+'</div>'+
		'<div class="challenge-popup-award" style="margin:'+iScreenScale+'px 0">'+challengeAward+'</div>' +
		(res.warning ? '<div class="challenge-popup-disclaimer" style="margin:'+iScreenScale+'px 0">'+challengeDisclaimer+'</div>':'') +
		'<div class="challenge-popup-close" style="font-size:'+(iScreenScale*2)+'px;bottom:'+iScreenScale+'px;right:'+Math.round(iScreenScale*1.25)+'px">'+
			'<a href="javascript:closeChallengePopup('+challenge.id+');">'+ challengeClose +'</a>'+
		'</div>';
	var oOtherPopup = document.getElementsByClassName("challenge-popup");
	if (oOtherPopup.length)
		document.body.insertBefore(oDiv, oOtherPopup[0]);
	else
		document.body.appendChild(oDiv);
	var opacity = 0;
	function fadeInPopup() {
		if (opacity < 1) {
			oDiv.style.opacity = opacity;
			opacity += 0.2;
			setTimeout(fadeInPopup,40);
		}
		else
			oDiv.style.opacity = 1;
	}
	fadeInPopup();
}
//showChallengePartialSuccess(challenges["track"][2817].list[0],{nb:3,total:4});
//showChallengePopup(challenges.track["2817"].list[0],{pts:2,pts_before:1,pts_after:3,publish:true});
window.closeChallengePopup = function(id) {
	var challengePopup = document.getElementById("challenge-popup-"+id);
	if (challengePopup) {
		var opacity = 1;
		function fadeOutPopup() {
			if (opacity > 0) {
				challengePopup.style.opacity = opacity;
				opacity -= 0.2;
				setTimeout(fadeOutPopup,40);
			}
			else
				document.body.removeChild(challengePopup);
		}
		fadeOutPopup();
	}
}
function showChallengePopup(challenge, res) {
	var lastPopup = document.getElementById("challenge-popup-"+challenge.id);
	if (lastPopup) return;
	var gain = res.pts;
	var oDiv = document.createElement("div");
	oDiv.id = "challenge-popup-"+challenge.id;
	oDiv.className = "challenge-popup";
	oDiv.style.width = (iScreenScale*56) +"px";
	oDiv.style.left = (iScreenScale*12) +"px";
	oDiv.style.top = Math.round(iScreenScale*4.5) +"px";
	oDiv.style.padding = Math.round(iScreenScale*1.5) +"px";
	oDiv.style.paddingBottom = (iScreenScale*5) +"px";
	oDiv.style.border = "inset "+ Math.round(iScreenScale*0.5) +"px #7B0";
	oDiv.style.fontSize = (iScreenScale*2) +"px";
	oDiv.style.opacity = 0;
	var challengeTitle = language ? 'Challenge completed!':'Défi réussi !';
	var challengeCongrats = challenge.description.main;
	var challengeAward = language ? 'You receive a reward of <strong>'+gain+' pt'+(gain>=2?'s':'')+'</strong>.':'Vous recevez <strong>'+gain+' pt'+(gain>=2?'s':'')+'</strong> en récompense.';
	var challengeAward2 = language ? 'Your challenge points goes from <strong>'+res.pts_before+'</strong> to <strong>'+res.pts_after+'</strong>!':'Vos points défis passent de <strong>'+res.pts_before+'</strong> à <strong>'+res.pts_after+'</strong> !';
	var challengeClose = language ? 'Close':'Fermer';
	oDiv.innerHTML = 
		'<div style="font-size: '+ Math.round(iScreenScale*2) +'px">'+
			'<img src="images/cups/cup1.png" alt="star" class="pixelated" style="width:'+ Math.round(iScreenScale*2.5) +'px" /> '+
			'<h1 class="challenge-popup-title" style="margin:'+ Math.round(iScreenScale/2) +'px 0; font-size: '+ Math.round(iScreenScale*3.25) +'px">'+ challengeTitle +'</h1>'+
		'</div>'+
		'<div class="challenge-popup-header" style="font-size: '+ Math.round(iScreenScale*2.25) +'px">'+challengeCongrats+'</div>'+
		(gain ? '<div class="challenge-popup-award" style="margin:'+iScreenScale+'px 0">'+challengeAward+'<br />'+challengeAward2+'</div>':'') +
		((res.rating>=0) ? '<div class="challenge-rating" style="margin-left:'+Math.round(iScreenScale*3.5)+'px;font-size:'+Math.round(iScreenScale*2.5)+'px">'+ toLanguage('Rate this challenge:', 'Notez ce défi :') +'<div class="challenge-rating-stars"></div><div class="challenge-rated">'+toLanguage("Thanks","Merci")+'</div></div>':'')+
		(res.publish ? '<div class="challenge-publish" style="margin:'+iScreenScale+'px 0">'+ toLanguage("You can now", "Vous pouvez maintenant") +' <a href="javascript:publishChallenge('+challenge.id+')">'+toLanguage("publish challenge","publier le défi")+'</a>.</div>':'')+
		'<div class="challenge-popup-close" style="font-size:'+(iScreenScale*2)+'px;bottom:'+iScreenScale+'px;right:'+Math.round(iScreenScale*1.25)+'px">'+
			'<a href="javascript:closeChallengePopup('+challenge.id+');">'+ challengeClose +'</a>'+
		'</div>';
	if (res.rating >= 0) {
		var $challengeRatingStars = oDiv.getElementsByClassName("challenge-rating-stars");
		$challengeRatingStars = $challengeRatingStars[0];
		$challengeRatingStars.style.position = "relative";
		$challengeRatingStars.style.marginLeft = Math.round(iScreenScale*0.4) +"px";
		$challengeRatingStars.style.marginRight = Math.round(iScreenScale*0.4) +"px";
		$challengeRatingStars.style.top = Math.round(iScreenScale*0.4) +"px";
		var $challengeRated = oDiv.getElementsByClassName("challenge-rated");
		$challengeRated = $challengeRated[0];
		var $allStars = [];
		var challengeStarOver = function() {
			var i = +this.rating;
			for (var j=0;j<i;j++)
				$allStars[j].src = "images/star1.png";
			for (var j=i;j<5;j++)
				$allStars[j].src = "images/star0.png";
		};
		var challengeStarOut = function() {
			for (var j=0;j<res.rating;j++)
				$allStars[j].src = "images/star1.png";
			for (var j=res.rating;j<5;j++)
				$allStars[j].src = "images/star0.png";
		};
		var challengeStarClick = function() {
			var i = +this.rating;
			res.rating = (res.rating==i) ? 0:i;
			challengeStarOut();
			$challengeRated.style.visibility = "hidden";
			xhr("challengeRate.php", "challenge="+challenge.id+"&rating="+res.rating, function(reponse) {
				if (reponse == 1) {
					$challengeRated.style.visibility = "visible";
					return true;
				}
				return false;
			});
		};
		for (var i=0;i<5;i++) {
			var $challengeRatingStar = document.createElement("img");
			$challengeRatingStar.alt = "S";
			$challengeRatingStar.src = "images/star0.png";
			$challengeRatingStar.style.width = (iScreenScale*3) +"px";
			$challengeRatingStar.style.marginLeft = Math.round(iScreenScale*0.4) +"px";
			$challengeRatingStar.style.marginRight = Math.round(iScreenScale*0.4) +"px";
			$challengeRatingStar.rating = i+1;
			$challengeRatingStar.onmouseover = challengeStarOver;
			$challengeRatingStar.onmouseout = challengeStarOut;
			$challengeRatingStar.onclick = challengeStarClick;
			$allStars[i] = $challengeRatingStar;
			$challengeRatingStars.appendChild($challengeRatingStar);
		}
		challengeStarOut();
	}
	var oOtherPopup = document.getElementsByClassName("challenge-popup");
	if (oOtherPopup.length)
		document.body.insertBefore(oDiv, oOtherPopup[0]);
	else
		document.body.appendChild(oDiv);
	var opacity = 0;
	function fadeInPopup() {
		if (opacity < 1) {
			oDiv.style.opacity = opacity;
			opacity += 0.2;
			setTimeout(fadeInPopup,40);
		}
		else
			oDiv.style.opacity = 1;
	}
	fadeInPopup();
	if (!pause && document.onkeydown) {
		clLocalVars.forcePause = true;
		document.onkeydown({keyCode:findKeyCode("pause")});
		delete clLocalVars.forcePause;
	}
	if (bMusic || iSfx) {
		if (!challengeMusic) {
			challengeMusic = playSoundEffect("musics/events/challenge.mp3");
			challengeMusic.className = "";
			if (endGPMusic) {
				pauseMusic(endGPMusic);
				challengeMusic.onended = function() {
					unpauseMusic(endGPMusic);
					challengeMusic = null;
				};
			}
			else if (willPlayEndMusic) {
				willPlayEndMusic = false;
				challengeMusic.onended = function() {
					unpauseMusic(endingMusic);
					isEndMusicPlayed = true;
					challengeMusic = null;
				};
			}
			else if (isEndMusicPlayed) {
				pauseMusic(endingMusic);
				challengeMusic.onended = function() {
					unpauseMusic(endingMusic);
					isEndMusicPlayed = true;
					challengeMusic = null;
				};
			}
			else {
				challengeMusic.onended = function() {
					challengeMusic = null;
				};
			}
		}
	}
}
window.closeChallengePopup = function(id) {
	var challengePopup = document.getElementById("challenge-popup-"+id);
	if (challengePopup) {
		var opacity = 1;
		function fadeOutPopup() {
			if (opacity > 0) {
				challengePopup.style.opacity = opacity;
				opacity -= 0.2;
				setTimeout(fadeOutPopup,40);
			}
			else
				document.body.removeChild(challengePopup);
		}
		fadeOutPopup();
	}
}
window.publishChallenge = function(id) {
	for (var type in challenges) {
		for (var cid in challenges[type]) {
			var creationChallenges = challenges[type][cid];
			var challengesList = creationChallenges.list;
			for (var i=0;i<challengesList.length;i++) {
				if (challengesList[i].id == id) {
					if (creationChallenges.main)
						openChallengeEditor();
					else
						document.location.href = "challenges.php?cl="+creationChallenges.id;
				}
			}
		}
	}
}

var COL_KART = 0, COL_OBJ = 1;
var collisionTest, collisionTeam;
function isHitSound(oBox) {
	if (collisionTest==COL_OBJ)
		return true;
	if (collisionTeam==oBox[2])
		return {x:oBox[3],y:oBox[4]};
	return false;
}
function handleHit(oBox) {
	if (clLocalVars.myItems && clLocalVars.currentKart && (clLocalVars.currentKart != oPlayers[0]) && !clLocalVars.currentKart.tourne && (clLocalVars.myItems.indexOf(oBox) != -1))
		incChallengeHits(clLocalVars.currentKart);
}
function handleHit2(oKart,kart) {
	if ((oKart == oPlayers[0]) && (kart != oPlayers[0]))
		incChallengeHits(kart);
}
function incChallengeHits(kart) {
	clLocalVars.nbHits++;
	if ((course == "BB") && (kart.ballons.length == 1)) {
		if (clLocalVars.killed && clLocalVars.killed.indexOf(kart) == -1) {
			clLocalVars.killed.push(kart);
			clLocalVars.nbKills++;
		}
	}
	challengeCheck("each_hit");
}
function touche_banane(iX, iY, iP) {
	for (var i=0;i<bananes.length;i++) {
		var oBox = bananes[i];
		if (i != iP && !oBox[5]) {
			if (iX > oBox[3]-4 && iX < oBox[3]+4 && iY > oBox[4]-4 && iY < oBox[4] + 4) {
				handleHit(oBox);
				detruit(bananes,i,isHitSound(oBox));
				return (collisionTeam!=oBox[2]);
			}
		}
	}
	return false;
}

function touche_fauxobjet(iX, iY, iP) {
	for (var i=0;i<fauxobjets.length;i++) {
		var oBox = fauxobjets[i];
		if (i != iP && !oBox[5]) {
			if (iX > oBox[3]-4 && iX < oBox[3]+4 && iY > oBox[4]-4 && iY < oBox[4] + 4) {
				handleHit(oBox);
				detruit(fauxobjets,i,isHitSound(oBox));
				return (collisionTeam!=oBox[2]);
			}
		}
	}
	return false;
}

function touche_cverte(iX, iY, iP) {
	for (var i=0;i<carapaces.length;i++) {
		var oBox = carapaces[i];
		if (i != iP && !oBox[5]) {
			if (iX > oBox[3]-5 && iX < oBox[3]+5 && iY > oBox[4]-5 && iY < oBox[4] + 5) {
				handleHit(oBox);
				detruit(carapaces,i,isHitSound(oBox));
				return (collisionTeam!=oBox[2]);
			}
		}
	}
	return false;
}

function touche_crouge(iX, iY, iP) {
	for (var i=0;i<carapacesRouge.length;i++) {
		var oBox = carapacesRouge[i];
		if (!oBox[0][0].div.style.opacity) {
			if (i != iP && !oBox[5]) {
				if (oBox[7] != -1 && iX == oBox[3] && iY == oBox[4]) {
					if (isOnline)
						detruit(carapacesRouge,i,isHitSound(oBox));
					else {
						handleHit(oBox);
						for (var i=0;i<strPlayer.length;i++)
							oBox[0][i].div.style.opacity = 0.8;
					}
					return (collisionTeam!=oBox[2]);
				}
				else if (oBox[7] == -1 && iX > oBox[3]-5 && iX < oBox[3]+5 && iY > oBox[4]-5 && iY < oBox[4] + 5) {
					handleHit(oBox);
					detruit(carapacesRouge,i,isHitSound(oBox));
					return (collisionTeam!=oBox[2]);
				}
			}
		}
	}
	return false;
}
function touche_bobomb(iX, iY, iP) {
	for (var i=0;i<bobombs.length;i++) {
		var oBox = bobombs[i];
		if (!oBox[5] && i != iP) {
			if (oBox[6] != -1) {
				var hitboxW = 30;
				if (oBox[8] >= 38)
					hitboxW = 0;
				else if (oBox[8] >= 30)
					hitboxW = 5;
				if (!oBox[7] && iX > oBox[3]-hitboxW && iX < oBox[3]+hitboxW && iY > oBox[4]-hitboxW && iY < oBox[4]+hitboxW) {
					if (oBox[8] <= 0) {
						var res = (collisionTeam!=oBox[2]) ? (oBox[8] < -5 ? 42 : 84):false;
						if (res) handleHit(oBox);
						return res;
					}
					else
						oBox[8] = 1;
				}
			}
			else {
				if (iX > oBox[3]-5 && iX < oBox[3]+5 && iY > oBox[4]-5 && iY < oBox[4] + 5) {
					var oKart;
					for (j=0;j<aKarts.length;j++) {
						if (aKarts[j].using[0]==bobombs&&i==aKarts[j].using[1]) {
							aKarts[j].using=[false];
							oKart = aKarts[j];
							j=aKarts.length;
						}
					}
					addNewItem(oKart,bobombs,[new Sprite("bob-omb"), -1, oBox[2], oBox[3], oBox[4], oBox[5],1,0,1]);
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
		if (oBox[6] < 0 && oBox[6] >= -10) {
			if (iX > oBox[3]-30 && iX < oBox[3]+30 && iY > oBox[4]-30 && iY < oBox[4]+30) {
				var res = (collisionTeam!=oBox[2]) ? (oBox[6] < -5 ? 42 : 84):false;
				if (res) handleHit(oBox);
				return res;
			}
		}
	}
	return false;
}

function colKart(oKart) {
	for (var i=0;i<aKarts.length;i++) {
		var kart = aKarts[i];
		if (!friendlyFire(kart,oKart) && Math.pow(kart.x-oKart.x, 2) + Math.pow(kart.y-oKart.y, 2) < 25 && !kart.tourne && !kart.loose && (!kart.protect || (kart.megachampi && !oKart.megachampi))) {
			handleHit2(oKart,kart);
			loseBall(i);
			stopDrifting(i);
			kart.spin(62);
			if (kart.using[0]) {
				if (kart.using[0][kart.using[1]][5])
					kart.using[0][kart.using[1]][5] = 0;
				kart.using = [false];
			}
			supprArme(i);
		}
	}
}
if(!Math.hypot)Math.hypot=function(x,y){return Math.sqrt(x*x+y*y)};
function distKart(obj) {
	var res = Infinity;
	for (var i=0;i<oPlayers.length;i++) {
		var oPlayer = oPlayers[i];
		if (kartIsPlayer(oPlayer) && !finishing) {
			var dist = Math.hypot(obj.x-oPlayer.x, obj.y-oPlayer.y);
			if (dist < res)
				res = dist;
		}
	}
	return res;
}
function stuntKart(oKart) {
	oKart.figstate = 21;
	oKart.z += 1;
	oKart.heightinc += 0.5;
	if (oKart == oPlayers[0])
		clLocalVars.stunted = true;
	playIfShould(oKart, "musics/events/stunt.mp3");
}

function places(j,force) {
	var oKart = aKarts[j];
	var retour = !force;
	for (var i=0;i<strPlayer.length;i++) {
		if (!oPlayers[i].cpu && !oPlayers[i].loose)
			retour = false;
	}
	if (retour) return;
	var place = 1;
	if (course != "BB") {
		if (oKart.tours > oMap.tours || !oMap.checkpoint.length) return;
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
	if (j<strPlayer.length)
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
function distanceToFirst(kart) {
	var cible = aKarts.length-1;
	var cPlace = 1;
	for (var k=0;k<aKarts.length;k++) {
		if (aKarts[k].place == cPlace) {
			if (aKarts[k].tours <= oMap.tours) {
				cible = k;
				break;
			}
			else {
				cPlace++;
				k = -1;
			}
		}
	}
	if (k == -1)
		return 0;
	var oKart = aKarts[k];
	var tours = kart.tours;
	var checkpoint = kart.demitours;
	var res = 0;
	var posX = kart.x, posY = kart.y;
	if (oMap.sections)
		tours = oKart.tours;
	while ((tours < oKart.tours) || ((tours == oKart.tours) && (checkpoint < oKart.demitours))) {
		checkpoint++;
		if (checkpoint >= oMap.checkpoint.length) {
			checkpoint = 0;
			tours++;
		}
		var oBox = oMap.checkpoint[checkpoint];
		var nPosX = oBox[0] + (oBox[3] ? Math.round(oBox[2]/2) : 8);
		var nPosY = oBox[1] + (oBox[3] ? 8 : Math.round(oBox[2]/2));
		res += Math.hypot(nPosX-posX, nPosY-posY);
		posX = nPosX;
		posY = nPosY;
	}
	res += Math.hypot(oKart.x-posX, oKart.y-posY);
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
	var oPlayer = oPlayers[0];
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
								var strSprite = strSprites[i];
								if ((strSprite == "carapace") && (aCodes[i][j][6] == -1))
									strSprite = "carapace-rouge";
								var toAdd = [new Sprite(strSprite),aID];
								for (k=1;k<aCodes[i][j].length;k++)
									toAdd.push(aCodes[i][j][k]);
								addNewItem(null,iObjets[i],toAdd);
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
									var aEtoile = aKarts[j].etoile, aBillBall = aKarts[j].billball, aEclair = aKarts[j].eclair, aTombe = aKarts[j].tombe;
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
												aKarts[j].sprite[0].div.style.opacity = 1;
												aKarts[j].sprite[0].img.style.display = "";
												oPlanCharacters[j].style.display = "block";
												oPlanCharacters2[j].style.display = "block";
												aKarts[j].loose = false;
											}
											addNewBalloon(aKarts[j]);
										}
										while (aKarts[j].ballons.length > extraParams.ballons) {
											var lg = aKarts[j].ballons.length-1;
											aKarts[j].ballons[lg][0].suppr();
											aKarts[j].ballons.pop();
										}
									}
									else
										aKarts[j].demitours = (getLastCp(aKarts[j])+extraParams.demitours)%oMap.checkpoint.length;
									if ((aKarts[j].billball >= 40) && !aBillBall) {
										aKarts[j].sprite[0].img.src = "images/sprites/sprite_billball.png";
										resetSpriteHeight(aKarts[j].sprite[0]);
										aKarts[j].aipoint = undefined;
									}
									else if ((aKarts[j].etoile >= 50) && !aEtoile)
										aKarts[j].sprite[0].img.src = getStarSrc(aKarts[j].personnage);
									else if ((aEtoile && !aKarts[j].etoile) || (aBillBall && !aKarts[j].billball)) {
										aKarts[j].sprite[0].img.src = getSpriteSrc(aKarts[j].personnage);
										resumeSpriteHeight(aKarts[j].sprite[0]);
									}
									if ((aKarts[j].eclair >= 90) && !aEclair) {
										for (k=0;k<aKarts.length;k++) {
											var kart = aKarts[k];
											if (!friendlyFire(kart,aKarts[j])) {
												if (!kart.protect) {
													kart.size = 0.6;
													updateDriftSize(k);
													kart.arme = false;
													if (kart.using[0]) {
														if (kart.using[0][kart.using[1]][5])
															kart.using[0][kart.using[1]][5] = 0;
														kart.using = [false];
													}
													kart.champi = 0;
													kart.spin(20);
													kart.roulette = 0;
													stopDrifting(k);
													supprArme(k);
												}
												else
													kart.megachampi = (kart.megachampi<8 || kart.etoile ? kart.megachampi : 8);
											}
										}
										document.getElementById("mariokartcontainer").style.opacity = 0.7;
										if (iSfx && !finishing && !oPlayers[0].cpu)
											playSoundEffect("musics/events/lightning.mp3");
									}
									else if (aEclair && !aKarts[j].eclair && (oPlayers[0].size < 1)) {
										oPlayers[0].size = 1;
										updateDriftSize(j);
										document.getElementById("mariokartcontainer").style.opacity = 1;
									}
									aKarts[j].protect = (aKarts[j].etoile || aKarts[j].megachampi || aKarts[j].billball);
									if (aTombe && !aKarts[j].tombe) {
										aKarts[j].sprite[0].img.style.display = "block";
										if (course == "BB") {
											for (var k=0;k<aKarts[j].ballons.length;k++)
												aKarts[j].ballons[k][0].img.style.display = "block";
										}
									}
									if (!aTombe && aKarts[j].tombe) {
										aKarts[j].sprite[0].img.style.display = "none";
										if (aKarts[j].tombe > 2) {
											if (course == "BB") {
												for (var k=0;k<aKarts[j].ballons.length;k++)
													aKarts[j].ballons[k][0].img.style.display = "none";
											}
											if (aKarts[j].marker)
												aKarts[j].marker.div[0].style.display = "none";
										}
									}
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
							var oPlayer = oPlayers[0];
							if (course == "BB") {
								oPlayer.arme = false;
								oPlayer.speed = 0;
								supprArme(0);
							}
							oPlayer.speedinc = 0;
							oPlayer.rotinc = 0;
							oPlayer.rotincdir = 0;
							oPlayer.sprite[0].setState(0);
							stopDrifting(0);
							var infos0 = document.getElementById("infos0");
							infos0.innerHTML = "";
							infos0.style.border = "solid 1px black";
							infos0.style.opacity = 0.7;
							infos0.style.fontSize = Math.round(iScreenScale*1.5+4) +"pt";
							infos0.style.fontFamily = "Courier";
							infos0.style.top = iScreenScale * 3 +"px";
							infos0.style.left = Math.round(iScreenScale*25+10) +"px";
							infos0.style.backgroundColor = iTeamPlay ? "blue":"#063";
							infos0.style.color = "yellow";
							var oTrs = new Array();
							var oTds = new Array();
							for (i=0;i<rCode[3].length;i++) {
								var pCode = rCode[3][i];
								var oTr = document.createElement("tr");
								oTds[i] = new Array();
								if (pCode[0] == identifiant) {
									oTr.style.backgroundColor = rankingColor(oPlayers[0].team);
									document.getElementById("infoPlace0").innerHTML = toPlace(i+1);
									document.getElementById("infoPlace0").style.visibility = "visible";
								}
								else if (pCode[4] == 1)
									oTr.style.backgroundColor = "red";
								var oTd = document.createElement("td");
								oTd.innerHTML = toPlace(i+1);
								oTds[i][0] = document.createElement("td");
								oTds[i][0].innerHTML = pCode[1];
								oTds[i][1] = document.createElement("td");
								oTds[i][1].innerHTML = pCode[2];
								var oSmall = document.createElement("small");
								oSmall.innerHTML = ((pCode[3]<0) ? "":"+") + pCode[3];
								oTr.appendChild(oTd);
								oTr.appendChild(oTds[i][0]);
								oTds[i][1].appendChild(oSmall);
								oTr.appendChild(oTds[i][1]);
								infos0.appendChild(oTr);
								oTrs[i] = oTr;
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
									var aCode = rCode[3][i];
									rCode[3][i] = rCode[3][mID];
									rCode[3][mID] = aCode;
								}
								for (i=0;i<oTds.length;i++) {
									var pCode = rCode[3][i];
									oTds[i][0].innerHTML = toPerso(pCode[1]);
									oTds[i][1].innerHTML = pCode[2];
									if (pCode[0] == identifiant)
										oTrs[i].style.backgroundColor = rankingColor(oPlayers[0].team);
									else if (pCode[4] == 1)
										oTrs[i].style.backgroundColor = "red";
									else
										oTrs[i].style.backgroundColor = "";
								}
								var forceClic2 = true;
								setTimeout(function(){infos0.style.visibility="visible";if(!isChatting())oContinue.focus()}, 500);

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
							if (!isChatting())
								oContinue.focus();
							document.onkeydown = undefined;
							document.onkeyup = undefined;
							document.onmousedown = undefined;
							window.onbeforeunload = undefined;
							window.removeEventListener("blur", window.releaseOnBlur);
							window.releaseOnBlur = undefined;
							supprArme(0);
							if (bMusic||iSfx)
								startEndMusic();
							finishing = true;
							document.getElementById("racecountdown").innerHTML = rCode[4]-(course=="BB"?6:5);
							document.getElementById("waitrace").style.visibility = "visible";
							dRest();
							document.getElementById("compteur0").innerHTML = "";
							document.getElementById("roulette0").innerHTML = "";
							document.getElementById("scroller0").style.visibility = "hidden";
							var lakitu = document.getElementById("lakitu0");
							if (lakitu) lakitu.style.display = "none";
						}
						if (course == "BB") {
							var firstID = rCode[3][0][0];
							var firstTeam = rCode[3][0][4];
							for (var i=0;i<aKarts.length;i++) {
								var oKart = aKarts[i];
								if (iTeamPlay ? (oKart.team!=firstTeam):(oKart.id != firstID)) {
									if (oKart.ballons.length && !oKart.tourne) {
										do {
											var lg = oKart.ballons.length-1;
											oKart.ballons[lg][0].suppr();
											oKart.ballons.pop();
										} while (oKart.ballons.length);
										oKart.spin(20);
										if (oKart != oPlayers[0])
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

function loseBall(i) {
	if (course == "BB") {
		var lg = aKarts[i].ballons.length-1;
		if (!aKarts[i].tourne && aKarts[i].ballons[lg]) {
			aKarts[i].ballons[lg][0].suppr();
			aKarts[i].ballons.pop();
			if (!aKarts[i].cpu)
				clLocalVars.lostBalloons++;
			if (isOnline && !i && !aKarts[i].ballons.length) {
				supprArme(i);
				document.getElementById("infoPlace0").style.visibility = "hidden";
			}
		}
	}
}

function showTimer() {
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
	for (var i=0;i<strPlayer.length;i++)
		document.getElementById("temps"+i).innerHTML = tps;
}

function move(getId) {
	var oKart = aKarts[getId];
	collisionTest = COL_KART;
	collisionTeam = (oKart.team==-1) ? undefined:oKart.team;
	clLocalVars.currentKart = oKart;
	var oKart = aKarts[getId];
	if ((getId<strPlayer.length) && !oKart.cpu && !finishing) {
		showTimer();
		if (!getId)
			timer++;

		if (oKart.time) {
			oKart.time--;
			document.getElementById("lakitu"+getId).style.left = Math.round(iScreenScale * (20-oKart.time/5) + 10 + getId * (iWidth*iScreenScale+2))+"px";
			document.getElementById("lakitu"+getId).style.top = Math.round((-(Math.abs(oKart.time - 20)) + 20) * (iScreenScale - 2)) +"px";

			if (oKart.time && !oPlayers[getId].changeView)
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
				for (var i=0;i<strPlayer.length;i++) {
					for (var j=0;j<oKart.ballons.length;j++)
						oKart.ballons[j][i].img.style.display = "block";
				}
			}
		}
		else if (!oKart.tombe) {
			loseBall(getId);
			if (course == "BB") {
				if (oKart.cpu && oKart.ballons.length == 1) {
					var f = 1+Math.round(Math.random());
					for (i=0;(i<f)&&(oKart.reserve);i++) {
						addNewBalloon(oKart);
						oKart.reserve--;
					}
				}
				if (!oKart.ballons.length && !oKart.loose) {
					for (i=0;i<strPlayer.length;i++)
						oKart.sprite[i].div.style.opacity = 1;
				}
			}
			for (var i=0;i<strPlayer.length;i++)
				oKart.sprite[i].img.style.display = "block";
		}
		if (oKart == oPlayers[getId])
			oContainers[getId].style.opacity = Math.abs(oKart.tombe-10)/10;
		return;
	}

	if (oKart.rotincdir) {
		oKart.rotinc += 2 * oKart.rotincdir;
		if (oKart.driftinc && ((oKart.driftinc>0)!=(oKart.rotincdir>0))) {
			if (oKart.driftinc > 0)
				oKart.rotinc = Math.max(oKart.rotinc,-1.6);
			else
				oKart.rotinc = Math.min(oKart.rotinc,1.6);
		}
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
				oKart.drift += oKart.driftinc;
				if (oKart.drift > 6)
					oKart.drift = 6;
				else if (oKart.drift < -6)
					oKart.drift = -6;
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
		if (oKart.driftcpt < fTurboDriftCpt2) {
			var lastDriftCpt = oKart.driftcpt;
			if (!oKart.rotincdir)
				oKart.driftcpt += 2;
			else if ((oKart.rotincdir>0) == (oKart.driftinc>0))
				oKart.driftcpt += 6*Math.max(1,Math.sqrt(Math.abs(oKart.rotincdir)/0.6));
			else
				oKart.driftcpt++;
			if (oKart.driftcpt >= fTurboDriftCpt2) {
				getDriftImg(getId).src = "images/turbo-drift-2.png";
				if (carSpark && (oKart != oPlayers[1])) {
					carSpark.currentTime = 0;
					carSpark.volume = 1;
					carSpark.play();
					oKart.sparkSound = carSpark;
				}
			}
			else if ((lastDriftCpt < fTurboDriftCpt) && (oKart.driftcpt >= fTurboDriftCpt)) {
				getDriftImg(getId).src = "images/turbo-drift.png";
				if (carSpark && (oKart != oPlayers[1])) {
					carSpark.currentTime = 0;
					carSpark.volume = 0.7;
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


	if (!oKart.tourne && oKart.speed && !oKart.billball && !oKart.figstate)
		oKart.rotation += (oKart.rotinc+(oKart.driftinc||0)*1.5)*((((oKart.speedinc<0)||(oKart.speedinc==0&&oKart.speed<0))?-1:1))*Math.abs(Math.cos(angleDrift(oKart)*Math.PI/180));

	else if (oKart.tourne) {
		oKart.figuring = false;
		oKart.figstate = 0;
		oKart.speed = oKart.speed / 1.2 - oKart.speedinc;
		oKart.tourne -= 2;
		if (course == "BB" && !oKart.tourne) {
			if (oKart.cpu && oKart.ballons.length == 1) {
				var f = 1+Math.round(Math.random());
				for (i=0;(i<f)&&(oKart.reserve);i++) {
					addNewBalloon(oKart);
					oKart.reserve--;
				}
			}
			if (!oKart.ballons.length && !oKart.loose) {
				for (i=0;i<strPlayer.length;i++)
					oKart.sprite[i].div.style.opacity = 1;
			}
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

	if (kartIsPlayer(oKart)) {
		if (!clLocalVars.startedAt && oKart.speed > 1)
			clLocalVars.startedAt = timer;
		var oSprite = oKart.sprite[getId];
		if (!oKart.changeView) {
			if (oKart.figstate)
				oSprite.setState((21-oKart.figstate) % 21);
			else if (oKart.driftinc)
				oSprite.setState((oKart.driftinc>0) ? 18:4);
			else if (oKart.rotincdir && !oKart.tourne)
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

	if (!oKart.z && !oKart.heightinc) {
		oKart.speed += oKart.speedinc;
		if (isCup && oMap.skin != 22 && oMap.skin != 30) {
			if (oKart.cpu && ((tombe(posx_arrondi, posy_arrondi) && !sauts(aPosX, aPosY, fMoveX, fMoveY)) || ((oKart.speed > 2.5) && oMap.horspistes.herbe && ralenti(posx_arrondi, posy_arrondi) && !oKart.protect && !oKart.champi))) {
				oKart.z = 1;
				oKart.heightinc = 0.5;
			}
		}
	}
	else {
		oKart.z += 0.7 * oKart.heightinc * Math.abs(oKart.heightinc);
		oKart.heightinc -= 0.5;
		if (oKart.z <= 0) {
			oKart.heightinc = 0;
			oKart.z = 0;
			if (kartIsPlayer(oKart)) {
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
			document.getElementById("drift"+ getId).style.top = Math.round(iScreenScale*(32-correctZ(oKart.z)) + (oKart.sprite[getId].h-32)*fSpriteScale*0.15 + 10) + "px";
	}
	
	if ((!getId || !isOnline || finishing) && !oKart.loose) {
		var pExplose = touche_bobomb(posx_arrondi, posy_arrondi, (oKart.using[0]==bobombs ? oKart.using[1]:-1)) + touche_cbleue(posx_arrondi, posy_arrondi);
		if (pExplose && !oKart.tourne && !oKart.protect) {
			loseBall(getId);
			oKart.spin(pExplose);
			if (oKart.using[0]) {
				if (oKart.using[0][oKart.using[1]][5])
					oKart.using[0][oKart.using[1]][5] = 0;
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
				oKart.spin(42);
				oKart.using = [false];
			}
			else if (touche_banane(posx_arrondi, posy_arrondi, (oKart.using[0]==bananes ? oKart.using[1]:-1)) && !oKart.protect) {
				loseBall(getId);
				stopDrifting(getId);
				oKart.spin(20);
				if (oKart.using[0]) {
					if (oKart.using[0][oKart.using[1]][5])
						oKart.using[0][oKart.using[1]][5] = 0;
					oKart.using = [false];
				}
			}
		}
	}

	var rScroller, rHeight, rSize;
	if (kartIsPlayer(oKart)) {
		var rScroller = document.getElementById("scroller"+getId).getElementsByTagName("div")[0];
		var rHeight = rScroller.offsetHeight;
		rSize = iScreenScale*7;
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
		if (!oKart.arme && (oKart.tours <= oMap.tours || course == "BB") && !oKart.billball && !finishing) {
			var iObj;
			if (course != "BB") {
				iObj = randObj(oKart);
				if ((oKart.tours == 1) && (getCpScore(oKart) <= (getCpDiff(oKart)/2))) {
					if (otherObjects(oKart,["carapacebleue","eclair"])) {
						while ((iObj == "carapacebleue") || (iObj == "eclair"))
							iObj = randObj(oKart);
					}
				}
				else {
					for (var i=0;i<aKarts.length;i++) {
						if (aKarts[i].arme == "carapacebleue") {
							if (otherObjects(oKart, ["carapacebleue"])) {
								while (iObj == "carapacebleue")
									iObj = randObj(oKart);
							}
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
			if (kartIsPlayer(oKart)) {
				document.getElementById("scroller"+getId).getElementsByTagName("div")[0].style.top = -Math.floor(Math.random()*rHeight) +"px";
				document.getElementById("scroller"+getId).style.visibility="visible";
				clLocalVars.itemsGot = true;
			}
		}
	}
	if (oKart.arme && oKart.roulette != 25) {
		if (kartIsPlayer(oKart)) {
			var nTop = (parseInt(rScroller.style.top) + iScreenScale*3);
			if (nTop > 0)
				nTop += rSize-rHeight;
			rScroller.style.top = nTop +"px";
		}
		oKart.roulette++;
		if (oKart.roulette >= 25) {
			oKart.roulette = 25;
			if (kartIsPlayer(oKart)) {
				document.getElementById("scroller"+getId).style.visibility="hidden";
				document.getElementById("roulette"+getId).innerHTML = '<img alt="." class="pixelated" src="images/items/'+ oKart.arme +'.gif" style="width: '+ Math.round(iScreenScale * 8 - 3)+'px;" />';
				if (oKart.rouletteSound) {
					removeIfExists(oKart.rouletteSound);
					playSoundEffect("musics/events/gotitem.mp3");
					oKart.rouletteSound = undefined;
				}
			}
		}
	}


	if ((page == "MK" && course=="BB" && oKart.cpu) || (oKart.z > 1.175) || canMoveTo(aPosX,aPosY, fMoveX,fMoveY, oKart.protect)) {
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
		var horizontality = getHorizontality(aPosX,aPosY, fMoveX,fMoveY);
		if (oKart.cpu)
			oKart.horizontality = horizontality;
		else if (oKart.speed < -0.2) {
			var s = (oKart.x-fNewPosX)*horizontality[0] + (oKart.y-fNewPosY)*horizontality[1];
			for (var i=5;i>0;i--) {
				oKart.x += horizontality[0]*s*i/oKart.speed;
				oKart.y += horizontality[1]*s*i/oKart.speed;
				if (canMoveTo(aPosX,aPosY, oKart.x-aPosX,oKart.y-aPosY, oKart.protect))
					break;
				else {
					oKart.x = aPosX;
					oKart.y = aPosY;
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
			oKart.turbodrift0 = oKart.turbodrift;
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
			var fTombe;
			if ((!isOnline || !getId) && (page!="MK" || course!="BB" || !oKart.cpu))
				fTombe = tombe(Math.round(oKart.x), Math.round(oKart.y), oMap.checkpoint&&oKart.demitours ? oMap.checkpoint[(oKart.demitours+1!=oMap.checkpoint.length) ? oKart.demitours+1 : 0][3] : 0);
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
				else if (isNaN(fTombe[0]))
					fTombe = oMap.startposition[(oKart.initialPlace-1)%oMap.startposition.length];
				oKart.x = fTombe[0];
				oKart.y = fTombe[1];
				oKart.rotation = fTombe[2]*90;
				oKart.speed = 0;
				oKart.protect = false;
				oKart.figuring = false;
				oKart.figstate = 0;
				oKart.fell = true;
				stopDrifting(getId);
				supprArme(getId);
				if (oKart.using)
					detruit(oKart.using[0],oKart.using[1]);
				oKart.champi = 0;
				if ((page == "CI") || (page == "MA")) {
					if (oKart.cpu && oKart.aipoints.length) {
						oKart.rotinc = 0;
						oKart.rotincdir = 0;
						var tAI = true;
						for (var i=oKart.aipoint+1;i>=oKart.aipoint-1;i--) {
							var iAi = (i+oKart.aipoints.length)%oKart.aipoints.length;
							var aCurPoint = oKart.aipoints[iAi];
							var iLocalX = aCurPoint[0] - oKart.x;
							var iLocalY = aCurPoint[1] - oKart.y;
							var pDist = iLocalX*iLocalX + iLocalY*iLocalY;
							if (pDist < 300) {
								oKart.aipoint = iAi;
								tAI = false;
								break;
							}
						}
						var fAI = oKart.aipoint;
						for (var mAI=1;tAI;mAI++) {
							tAI = false;
							for (var i=0;i<50;i++) {
								ai(oKart);
								if (oKart.rotincdir) {
									oKart.rotinc += 2 * oKart.rotincdir;
								}
								else {
									if (oKart.rotinc < 0) {
										oKart.rotinc = Math.min(0, oKart.rotinc + 1);
									}
									if (oKart.rotinc > 0) {
										oKart.rotinc = Math.max(0, oKart.rotinc - 1);
									}
								}

								oKart.rotinc = Math.min(oKart.rotinc, fMaxRotInc);
								oKart.rotinc = Math.max(oKart.rotinc, -fMaxRotInc);

								var iPosX = oKart.x, iPosY = oKart.y, iMoveX = oKart.speed * direction(0, oKart.rotation), iMoveY = oKart.speed * direction(1, oKart.rotation);
								if (!oKart.tourne && oKart.speed && !oKart.billball) {
									oKart.rotation += ((oKart.speedinc < 0) || (oKart.speedinc == 0 && oKart.speed < 0)) ? -oKart.rotinc : oKart.rotinc;
									if (oKart.rotation < 0)
										oKart.rotation += 360;
									if (oKart.rotation > 360)
										oKart.rotation -= 360;
									oKart.x += iMoveX;
									oKart.y += iMoveY;
								}
								if (!oKart.z && !oKart.heightinc) {
									if (oKart.speed < 5)
										oKart.speed += oKart.speedinc;
									var pJump = sauts(iPosX,iPosY, iMoveX,iMoveY);
									if (pJump && !oKart.tourne) {
										oKart.heightinc = pJump / 30 + 1.5;
										oKart.speed = 11;
									}
									else if (tombe(Math.round(oKart.x), Math.round(oKart.y))) {
										tAI = true;
										i = 50;
									}
								}
								else {
									oKart.z += 0.7 * oKart.heightinc * Math.abs(oKart.heightinc);
									oKart.heightinc -= 0.5;
									if (oKart.z <= 0) {
										oKart.heightinc = 0;
										oKart.z = 0;
									}
								}
							}
							oKart.aipoint = fAI;
							oKart.aipoint -= mAI;
							if (!tAI)
								oKart.aipoint++;
							if (oKart.aipoint < 0) {
								oKart.aipoint += oKart.aipoints.length;
								if (oKart.aipoint < 0) {
									oKart.aipoint = fAI;
									tAI = false;
								}
							}
							oKart.x = fTombe[0];
							oKart.y = fTombe[1];
							oKart.rotation = fTombe[2]*90;
							oKart.speed = 0;
							oKart.rotinc = 0;
							oKart.rotincdir = 0;
						}
					}
				}
				oKart.tombe = 20;
				oKart.ctrled = true;
				oKart.z = 10;
				oKart.tourne = 0;
				for (var i=0;i<strPlayer.length;i++) {
					oKart.sprite[i].img.style.display = "none";
					oKart.sprite[i].div.style.backgroundImage = "";
					if (oKart.etoile)
						oKart.sprite[i].img.src = getSpriteSrc(oKart.personnage);
				}
				for (var i=0;i<strPlayer.length;i++) {
					if (course == "BB") {
						for (var j=0;j<oKart.ballons.length;j++)
							oKart.ballons[j][i].img.style.display = "none";
					}
					if (oKart.marker)
						oKart.marker.div[i].style.display = "none";
				}
				resetPowerup(oKart);
				if (!oKart.cpu)
					clLocalVars.falls++;
				playIfShould(oKart, "musics/events/fall.mp3");
			}
			else if (!oKart.protect && !oKart.champi && !oKart.z && !oKart.figuring && oKart.speed > 1.5 && !(oKart.turbodrift>oKart.turbodrift0*0.8) && (hpType=ralenti(posx_arrondi, posy_arrondi))) {
				var capSpeed;
				switch (hpType) {
					case "herbe" :
						capSpeed = 2.5-oKart.speedinc;
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
		oArme[3] = (oKart.x - 5 * direction(0, oKart.rotation));
		oArme[4] = (oKart.y - 5 * direction(1, oKart.rotation));
		oArme[5] = oKart.z;
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
				if (kartIsPlayer(oKart) && !finishing) {
					if (course != "CM")
						document.getElementById("infoPlace"+getId).innerHTML = toPlace(oKart.place);
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
					if (!oPlayers[1-getId] || oPlayers[1-getId].cpu) {
						if (!isOnline) {
							if (course != "CM") {
								for (var i=0;i<nbjoueurs;i++)
									places(i,true);
								var cKarts = aKarts.slice(0);
								cKarts.sort(function(k1,k2) {
									return k1.place-k2.place;
								});
								for (var i=0;i<nbjoueurs;i++)
									cKarts[i].place = i+1;
								aPlaces = new Array();
								for (var i=0;i<nbjoueurs;i++)
									aPlaces[i] = aKarts[i].place;
								var positions = '<tr style="font-size: '+ iScreenScale * 2 +'px; background-color: white; color: black;"><td>Places</td><td>'+ toLanguage('Player','Joueur') +'</td><td>Pts</td></tr>';
								for (var i=0;i<nbjoueurs;i++) {
									for (var j=0;j<nbjoueurs;j++) {
										var joueur = aKarts[j].personnage;
										if (aKarts[j].place == i+1) {
											var isRedTeam = (aKarts[j].team==1) ? 1:0;
											var ptsInc = [10,8,6,4,3,2,1,0][Math.round(i*7/(aKarts.length-1))];
											positions += '<tr id="fJ'+i+'" style="background-color: '+ (j<strPlayer.length ? (j ? (isRedTeam?'brown':'navy') : rankingColor(aKarts[j].team)) : (isRedTeam?'red':'transparent')) +'"><td>'+ toPlace(i+1)+' </td><td class="maj" id="j'+i+'">'+ toPerso(joueur) +'</td><td id="pts'+i+'">'+ aScores[j] +'<small>+'+ ptsInc +'</small></td></tr>';
											aScores[j] += ptsInc;
											j = nbjoueurs;
										}
									}
								}


								positions += '<tr><td colspan="3" id="continuer"></td></tr>';
								document.getElementById("infos0").style.border = "solid 1px black";
								document.getElementById("infos0").style.opacity = 0.7;
								document.getElementById("infos0").style.fontSize = Math.round(iScreenScale*1.77-0.5) +"pt";
								document.getElementById("infos0").style.fontFamily = "Courier";
								document.getElementById("infos0").style.top = iScreenScale * 3 +"px";
								document.getElementById("infos0").style.left = Math.round(iScreenScale*24+10 + (strPlayer.length-1)/2*(iWidth*iScreenScale+2)) +"px";
								document.getElementById("infos0").style.backgroundColor = iTeamPlay ? "blue":"#063";
								document.getElementById("infos0").style.color = "yellow";
								document.getElementById("infos0").innerHTML = positions;
								var oContinue = document.createElement("input");
								oContinue.type = "button";
								oContinue.id = "octn";
								oContinue.value = toLanguage("CONTINUE", "CONTINUER");
								oContinue.style.width = "100%";
								oContinue.style.height = "100%";
								oContinue.style.fontSize = iScreenScale*3 +"pt";
								oContinue.onclick = classement;
								document.getElementById("continuer").appendChild(oContinue);
								document.getElementById("infos0").style.visibility = "visible";
								var aScroll = document.body.scrollTop;
								oContinue.focus();
								document.body.scrollTop = aScroll;
							}
							else {
								document.getElementById("infos0").style.fontSize = (iScreenScale * 5) +"px";
								document.getElementById("infos0").style.fontWeight = "bold";
								document.getElementById("infos0").style.color = "blue";
								document.getElementById("infos0").style.top = (iScreenScale*10 + 10) +"px";
								document.getElementById("infos0").innerHTML = '<tr><td style="text-decoration: blink;">'+ document.getElementById("temps0").innerHTML +'</td></tr><tr><td id="continuer"></td></tr>';
								document.getElementById("infos0").style.visibility = "visible";
								var oContinue = document.createElement("input");
								oContinue.type = "button";
								oContinue.id = "octn";
								oContinue.value = toLanguage("CONTINUE", "CONTINUER");
								oContinue.style.width = "100%";
								oContinue.style.height = "100%";
								oContinue.style.fontSize = iScreenScale*3 +"pt";
								oContinue.onclick = function() {
									document.getElementById("infos0").style.visibility = "hidden";
									
									var oForm = document.createElement("div");
									oForm.style.color = "black";
									oForm.style.position = "absolute";
									oForm.style.left = (iScreenScale*5+10) +"px";
									oForm.style.top = (iScreenScale*5+10) +"px";
									oForm.style.fontSize = (iScreenScale*4) +"pt";
									oForm.style.backgroundColor = "#FF6";
									oForm.style.opacity = 0.8;
									oForm.style.border = "double 4px black";
									oForm.style.textAlign = "center";
									oForm.style.width = (iScreenScale*70-10) +"px";
									oForm.style.height = (iScreenScale*25-10) +"px";
									oForm.style.zIndex = 20000;

									var aPara1 = document.createElement("p");
									aPara1.innerHTML = toLanguage("New record !<br />Save the ghost ?", "Nouveau record !<br />Enregistrer le fant&ocirc;me ?");
									aPara1.style.margin = iScreenScale +"px";
									var aPara2 = aPara1.cloneNode(false);
									var oSave = document.createElement("input");
									oSave.type = "button";
									oSave.value = "  "+ toLanguage("Yes", "Oui") +"  ";

									oSave.style.fontSize = (iScreenScale*4) +"px";
									oSave.onmouseover = function() {
										this.style.fontSize = (iScreenScale*5) +"px";
										oRetour.style.fontSize = (iScreenScale*4) +"px"
									};
									oSave.onclick = function() {
										oSave.disabled = true;
										oRetour.disabled = true;
										aPara1.innerHTML = toLanguage("Saving...", "Enregistrement en cours...") + "<br />";
										var oRequest = "map="+ oMap.map +"&perso="+ strPlayer[0];
										for (i=0;i<iTrajet.length;i++)
											oRequest += "&p"+ i +"="+ iTrajet[i].toString().replace(/\,/g, "_");
										xhr("saveghost_.php", oRequest, function(reponse) {
											if (reponse == 1) {
												gRecord = timer;
												aPara1.innerHTML = toLanguage("Ghost saved successfully...", "Fantôme enregistré avec succès.") + "<br />";
												setTimeout(function() {
													oSave.disabled = false;
													oRetour.disabled = false;
													askForRegister(true);
												}, 500);
												return true;
											}
											else
												return false;
										});
									};
									aPara2.appendChild(oSave);
									var oRetour = document.createElement("input");
									oRetour.type = "button";
									oRetour.value = "  "+ toLanguage("No", "Non") +"  ";
									oRetour.style.fontSize = (iScreenScale*4) +"px";
									oRetour.onmouseover = function() {
										this.style.fontSize = (iScreenScale*5) +"px";
										oSave.style.fontSize = (iScreenScale*4) +"px"
									};
									oRetour.onclick = function() {
										askForRegister(true);
									};
									aPara2.appendChild(oRetour);

									oForm.appendChild(aPara1);
									oForm.appendChild(aPara2);
									document.body.appendChild(oForm);
									
									function askForRegister(newScreen) {
										aPara1.innerHTML = toLanguage('Save the time to the <a href="classement.php" target="_blank" style="color: orange">record list</a> ?', 'Enregistrer le temps dans la <a href="'+ rankingsLink(oMap) +'" target="_blank" style="color: orange">liste des records</a> ?');
										oSave.onclick = function() {
											document.body.removeChild(oForm);
											continuer();
											document.getElementById("enregistrer").getElementsByTagName("input")[0].onclick();
										};
										oRetour.onclick = function() {
											document.body.removeChild(oForm);
											continuer();
											document.getElementById("infos0").style.visibility = "visible";
										};
										if (newScreen) {
											oForm.style.visibility = "hidden";
											setTimeout(function() {
												oForm.style.visibility = "visible";
											}, 500);
										}
									}
									
									if (page != "MK" || timer >= gRecord)
										askForRegister(false);
								};
								document.getElementById("continuer").appendChild(oContinue);
								document.getElementById("infos0").style.visibility = "visible";
								oContinue.focus();
							}
							handleEndRace();
						}
						else {
							var infos0 = document.getElementById("infos0");
							infos0.style.left = (iScreenScale*15) +"px";
							infos0.innerHTML = "";
							var oTr = document.createElement("tr");
							var oTd = document.createElement("td");
							oTd.style.fontSize = (iScreenScale*8) +"px";
							oTd.style.color = "#F80";
							oTd.innerHTML = toLanguage("&nbsp; &nbsp; FINISH !", "TERMIN&Eacute; !");
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
							finishing = true;
						}
						document.onkeydown = undefined;
						document.onkeyup = undefined;
						document.onmousedown = undefined;
						window.onbeforeunload = undefined;
						window.removeEventListener("blur", window.releaseOnBlur);
						window.releaseOnBlur = undefined;
					}
				}
				if (oMap.sections)
					if (oKart.billball>1) oKart.billball = 1;
			}
			else if (!(isOnline ? (getId||finishing):oKart.cpu)) {
				document.getElementById("tour"+getId).innerHTML = oKart.tours;
				document.getElementById("lakitu"+getId).getElementsByTagName("div")[0].innerHTML = (oMap.sections ? "Sec":toLanguage("Lap","Tour")) + "<small>&nbsp;</small>" + oKart.tours;
				oKart.time = 40;
				if (bMusic || iSfx) {
					if (oKart.tours == oMap.tours) {
						var firstOne = true;
						for (var i=0;i<oPlayers.length;i++) {
							if ((oPlayers[i] != oKart) && (oPlayers[i].tours >= 3)) {
								firstOne = false;
								break;
							}
						}
						if (firstOne) {
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
					}
					else if (iSfx)
						playSoundEffect("musics/events/nextlap.mp3");
				}
			}
		}
	}
	else {
		if (!isOnline) {
			var gagnant;
			if (oPlayers[0].loose && (!oPlayers[1] || oPlayers[1].loose)) {
				do {
					gagnant = aKarts[Math.floor(Math.random()*(aKarts.length-strPlayer.length))+strPlayer.length];
				} while(gagnant.loose);
				for (i=strPlayer.length;i<aKarts.length;i++)
					aKarts[i].loose = true;
			}
			else {
				if (iTeamPlay) {
					var EnVie = [false,false];
					for (i=0;i<aKarts.length;i++) {
						if (!aKarts[i].loose) {
							if (!aKarts[i].cpu)
								gagnant = aKarts[i];
							EnVie[aKarts[i].team] = true;
						}
					}
					if (EnVie[0] && EnVie[1])
						gagnant = undefined;
				}
				else {
					var EnVie = false;
					for (i=0;i<aKarts.length;i++) {
						if (!aKarts[i].loose) {
							if (!EnVie) {
								EnVie = true;
								gagnant = aKarts[i];
							}
							else {
								gagnant = undefined;
								i = aKarts[i].length;
							}
						}
					}
				}
			}
			if (gagnant) {
				clLocalVars.gagnant = gagnant;
				for (i=0;i<strPlayer.length;i++) {
					stopDrifting(i);
					supprArme(i);
				}
				var positions = '<tr style="font-size: '+ iScreenScale * 2 +'px; background-color: white; color: black;"><td>Places</td><td>'+ toLanguage('Player','Joueur') +'</td><td>Pts</td></tr>';
				for (var i=0;i<aKarts.length;i++) {
					var isRedTeam = (aKarts[i].team==1) ? 1:0;
					var ptsInc = (aKarts[i] == gagnant);
					var joueur = aKarts[i].personnage;
					positions += '<tr id="fJ'+i+'" style="background-color:'+ (i<strPlayer.length ? (i ? (isRedTeam?'brown':'navy') : (rankingColor(aKarts[i].team))) : (isRedTeam?'red':'transparent')) +'"><td>'+ toPlace(i+1) +' </td><td class="maj" id="j'+i+'">'+ toPerso(joueur) +'</td><td id="pts'+i+'">'+ aScores[i] + (!ptsInc ? "" : "<small>+1</small>")+'</td></tr>';
					aScores[i] += ptsInc;
				}

				positions += '<tr><td colspan="3" id="continuer"></td></tr>';
				document.getElementById("infos0").style.border = "solid 1px black";
				document.getElementById("infos0").style.opacity = 0.7;
				document.getElementById("infos0").style.fontSize = Math.round(iScreenScale*1.77-0.5) +"pt";
				document.getElementById("infos0").style.fontFamily = "Courier";
				document.getElementById("infos0").style.top = iScreenScale * 3 +"px";
				document.getElementById("infos0").style.left = Math.round(iScreenScale*24+10 + (strPlayer.length-1)/2*(iWidth*iScreenScale+2)) +"px";
				document.getElementById("infos0").style.backgroundColor = iTeamPlay ? "blue":"#063";
				document.getElementById("infos0").style.color = "yellow";
				document.getElementById("infos0").innerHTML = positions;
				var oContinue = document.createElement("input");
				oContinue.type = "button";
				oContinue.id = "octn";
				oContinue.value = toLanguage("CONTINUE", "CONTINUER");
				oContinue.style.width = "100%";
				oContinue.style.height = "100%";
				oContinue.style.fontSize = iScreenScale*3 +"pt";
				oContinue.onclick = classement;
				document.getElementById("continuer").appendChild(oContinue);
				document.getElementById("infos0").style.visibility = "visible";
				var aScroll = document.body.scrollTop;
				oContinue.focus();
				document.body.scrollTop = aScroll;
				for (i=0;i<aKarts.length;i++)
					aKarts[i].loose = true;
				handleEndRace();
				document.onkeydown = undefined;
				document.onkeyup = undefined;
				document.onmousedown = undefined;
				window.onbeforeunload = undefined;
				window.removeEventListener("blur", window.releaseOnBlur);
				window.releaseOnBlur = undefined;
			}
		}
	}

	if (oKart.cpu) {
		if (oKart.roulette == 25 || oKart.using[0] && !oKart.tourne) {
			if ((!oMap.lignedroite || (oKart.arme != "champi" && oKart.arme != "megachampi" && oKart.arme != "etoile")) && (course != "BB" || iTeamPlay || oKart.using[2] != "carapacerouge")) {
				if (Math.random() > 0.98) {
					var backwards = (((oKart.place<oPlayers[0].place)||(course=="BB")) && (Math.random() > 0.5));
					arme(getId, backwards);
				}
			}
			else if (oKart.using[2] != "carapacerouge") {
				for (var j=0;j<oMap.lignedroite.length;j++) {
					var oBox = oMap.lignedroite[j];
					if (oKart.x > oBox[0] && oKart.x < oBox[0] + 50 && oKart.y > oBox[1] && oKart.y < oBox[1] + 50) {
						arme(getId);
						j = oMap.lignedroite.length;
					}
				}
			}
			else {
				for (i=0;i<strPlayer.length;i++) {
					if (!aKarts[i].loose && Math.pow(aKarts[i].x-oKart.x-15*direction(0,oKart.rotation),2) + Math.pow(aKarts[i].y-oKart.y-15*direction(1,oKart.rotation),2) < 1000) {
						arme(getId);
						i = strPlayer.length;
					}
				}
			}
		}

		if (course == "BB")
			oKart.maxspeed = 5.7;
		else {
			var influence = 1, rSpeed = iDificulty;
			if (complete) {
				influence = Math.max(Math.pow(0.96, getId-oPlayers.length-2),0.8);
				rSpeed *= influence*iDificulty/5;
			}
			var rRatio = 1.25;
			if ((iDificulty >= 5) && (aKarts.length > 8))
				rRatio *= Math.log(1+100*aKarts.length/8)/4.61;
			if (oKart.maxspeed > rSpeed*rRatio) oKart.maxspeed = rSpeed*rRatio;
			else if (oKart.maxspeed < rSpeed) oKart.maxspeed = rSpeed;
			if (oKart.place <= oPlayers[0].place)
				oKart.maxspeed -= (oKart.maxspeed*influence-rSpeed*oKart.size)/100;
			else
				oKart.maxspeed += (rSpeed*rRatio*1.12*oKart.size-oKart.maxspeed*influence)/100;
		}
	}
	else
		oKart.maxspeed = 5.4 * cp[oKart.personnage][1];

	if (oKart.turbodrift) {
		if (oKart.speed > -8) {
			oKart.maxspeed = 8;
			oKart.speed = Math.max(8, oKart.speed);
		}
		oKart.turbodrift--;
	}
	if (oKart.champi) {
		oKart.maxspeed = 11;
		oKart.champi--;
		if (course == "BB") {
			var touche = false;
			for (i=0;i<aKarts.length;i++) {
				var kart = aKarts[i];
				if (!friendlyFire(oKart,kart) && oKart.ballons.length && kart.ballons.length && Math.pow(oKart.x-kart.x, 2) + Math.pow(oKart.y-kart.y, 2) < 25 && Math.abs(oKart.z - kart.z) < 2 && !oKart.tourne && !kart.tourne && !kart.protect && !kart.champi) {
					handleHit2(oKart,kart);
					loseBall(i);
					stopDrifting(i);
					if (oKart.ballons.length < 3)
						addNewBalloon(oKart,kart.team);
					kart.spin(20);
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
			iLocalX = oKart.aipoints[oKart.aipoint][0] - oKart.x;
			iLocalY = oKart.aipoints[oKart.aipoint][1] - oKart.y;

			if (iLocalX*iLocalX + iLocalY*iLocalY < 1600) {
				oKart.aipoint++;

				if (oKart.aipoint >= oKart.aipoints.length)
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
			for (var i=0;i<oKart.aipoints.length;i++) {
				var oBox = oKart.aipoints[i];
				if (oKart.x > oBox[0] - 35 && oKart.x < oBox[0] + 35 && oKart.y > oBox[1] - 35 && oKart.y < oBox[1] + 35) {
					var nextAI = i + 1;
					if (nextAI == oKart.aipoints.length) nextAI = 0;
					var nPosX = oKart.aipoints[nextAI][0], nPosY = oKart.aipoints[nextAI][1];
					var angle0 = oKart.rotation*Math.PI/180;
					var angle1 = Math.abs(normalizeAngle(Math.atan2(nPosX-oKart.x,nPosY-oKart.y)-angle0, 2*Math.PI));
					var angle2 = Math.abs(normalizeAngle(Math.atan2(iLocalX,iLocalY)-angle0, 2*Math.PI));
					if ((angle1 < Math.max(angle2,Math.PI/4))) {
						oKart.aipoint = nextAI;
						iLocalX = nPosX - oKart.x;
						iLocalY = nPosY - oKart.y;
						break;
					}
				}
			}
		}

		var iRotatedX = iLocalX * direction(1, oKart.rotation) - iLocalY * direction(0, oKart.rotation);
		var iRotatedY = iLocalX * direction(0, oKart.rotation) + iLocalY * direction(1, oKart.rotation);

		var fAngle = Math.atan2(iRotatedX,iRotatedY) / Math.PI * 180;
		if (Math.abs(fAngle) > 10) {
			if (Math.abs(fAngle) > 60) {
				oKart.speed = 1;
				fAngle = (fAngle > 0) ? 20:-20;
			}
			else
				fAngle = (fAngle > 0) ? 10:-10;
		}

		oKart.rotation += fAngle;
		oKart.rotation = oKart.rotation % 360;
		oKart.billball--;
		if (oKart.billball) {
			if (!oKart.billjump && (oKart.billball < 12)) {
				var fMoveX = oKart.speed*direction(0, oKart.rotation);
				var fMoveY = oKart.speed*direction(1, oKart.rotation);
				if (sauts(oKart.x,oKart.y, fMoveX,fMoveY)) {
					oKart.billball = 12;
					oKart.billjump = true;
				}
			}
		}
		else {
			for (var i=0;i<strPlayer.length;i++) {
				oKart.sprite[i].img.src = getSpriteSrc(oKart.personnage);
				resumeSpriteHeight(oKart.sprite[i]);
			}
			oKart.size = 1;
			oKart.z = 0;
			updateDriftSize(getId);
			oKart.jumped = false;
			oKart.protect = false;
			delete oKart.billjump;
			if (!oKart.cpu)
				delete oKart.aipoint;
		}
	}
	if (oKart.etoile) {
		oKart.maxspeed *= 1.35;
		oKart.etoile--;
		if (oKart.etoile < 15) {
			for (var i=0;i<strPlayer.length;i++)
				oKart.sprite[i].img.src = (oKart.etoile % 2 ? getStarSrc(oKart.personnage) : getSpriteSrc(oKart.personnage));
			if (!oKart.etoile) {
				oKart.protect = !!oKart.megachampi;
				var maxSpeedInc = oKart.cpu ? 1 : cp[oKart.personnage][0]*oKart.size;
				oKart.speedinc = Math.min(oKart.speedinc, maxSpeedInc);
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
		if ((isOnline || (oKart.eclair > 80)) && (oKart.eclair <= 88))
			document.getElementById("mariokartcontainer").style.opacity = 1;
		if (oKart.eclair < 1) {
			for (var i=0;i<aKarts.length;i++) {
				var kart = aKarts[i];
				if (!friendlyFire(kart,oKart) && kart.size < 1 && (!isOnline||kart==oPlayers[0])) {
					kart.size = 1;
					updateDriftSize(i);
				}
			}
		}
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
	if (iSfx && (oKart == oPlayers[0]) && !finishing && !oKart.cpu && (!oKart.loose||isOnline)) {
		if ((bMusic&&(oKart.etoile||oKart.megachampi)) || oKart.tombe || oKart.turbodrift || oKart.turboSound) {
			updateEngineSound();
			if (oKart.turbodrift == (oKart.turbodrift0-1)) {
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
		var setOpac = oKart.sprite[0].div.style.opacity-0.1;
		for (var i=0;i<strPlayer.length;i++)
			oKart.sprite[i].div.style.opacity = setOpac;
		var oPacLim = (isOnline&&oKart==oPlayers[0]) ? 0.4:0.01;
		if (setOpac < oPacLim) {
			if (!isOnline || oKart != oPlayers[0]) {
				for (var i=0;i<strPlayer.length;i++) {
					oKart.sprite[i].img.style.display = "none";
					if (oKart.marker)
						oKart.marker.div[i].style.display = "none";
				}
			}
			oKart.loose = true;
			challengeCheck("each_kill");
		}
	}
}

function kartIsPlayer(oKart) {
	if (!isOnline)
		return !oKart.cpu;
	return (oKart == oPlayers[0]);
}

function angleDrift(oKart) {
	if (!kartIsPlayer(oKart))
		return 0;
	if (oKart.sliding)
		return oKart.rotinc*oKart.sliding;
	return oKart.drift*6;
}
function angleShoot(oKart, backwards) {
	var res = oKart.rotation;
	if (backwards) res += 180;
	return res;
}
function updateDriftSize(getId) {
	if (kartIsPlayer(aKarts[getId])) {
		var k = aKarts[getId].size-1;
		getDriftImg(getId).style.left = -Math.round((iScreenScale*2)*k) + "px";
		getDriftImg(getId).style.top = Math.round((iScreenScale*2)*k) + "px";
		getDriftImg(getId).style.width = Math.round(iScreenScale * 8 + (iScreenScale*4)*k) + "px";
	}
}
function getDriftImg(getId) {
	return document.getElementById("drift"+ getId).getElementsByClassName("driftimg")[0];
}

var clLocalVars;

function openCheats() {
	var cheatCode = prompt("MKPC Console command");
	if (!cheatCode)
		return false;
	if (!processCode(cheatCode))
		alert("Invalid command");
	else;
		clLocalVars.cheated = true;
}
function processCode(cheatCode) {
	if (cheatCode.charAt(0) != "/")
		return false;
	cheatCode = cheatCode.substring(1);
	var oPlayer = oPlayers[0];
	var isObject = /^give (\w+)$/g.exec(cheatCode);
	if (isObject) {
		var wObject = isObject[1];
		if (objets.indexOf(wObject) == -1)
			return false;
		oPlayer.arme = wObject;
		oPlayer.roulette = 25;
		document.getElementById("scroller0").style.visibility="hidden";
		document.getElementById("roulette0").innerHTML = '<img alt="." class="pixelated" src="images/items/'+ wObject +'.gif" style="width: '+ Math.round(iScreenScale * 8 - 3)+'px;" />';
		return true;
	}
	var isTP = /^tp ([\d\-+\.]+) ([\d\-+\.]+)$/g.exec(cheatCode);
	if (!isTP)
		isTP = /^tp ([\d\-+\.]+) ([\d\-+\.]+) ([\d\-+\.]+)$/g.exec(cheatCode);
	if (isTP) {
		var x = parseFloat(isTP[1]), y = parseFloat(isTP[2]);
		if (isNaN(x) || isNaN(y))
			return false;
		var th = parseFloat(isTP[3]);
		oPlayer.x = x;
		oPlayer.y = y;
		if (!isNaN(th))
			oPlayer.rotation = th;
		return true;
	}
	var isLap = /^lap(?: ([1-3]|c))?(?: (\d+|c))?$/g.exec(cheatCode);
	if (isLap) {
		var t = parseInt(isLap[1]), c = parseInt(isLap[2]);
		if (isLap[1] == "c")
			t = oPlayer.tours;
		if (!isLap[1]) {
			t = oMap.tours;
			c = oMap.checkpoint.length-1;
		}
		if (isLap[2] == "c")
			c = oPlayer.demitours;
		if (!isLap[2])
			c = oMap.checkpoint.length-1;
		if (isNaN(t) || isNaN(c))
			return false;
		oPlayer.tours = t;
		oPlayer.demitours = c;
		document.getElementById("tour0").innerHTML = oPlayer.tours;
		return true;
	}
	if (course == "BB") {
		if (cheatCode == "balloon")
			cheatCode += " 1";
		var isBaloon = /^balloon (\d+)$/g.exec(cheatCode);
		if (isBaloon) {
			var toAdd = parseInt(isBaloon[1]);
			if (toAdd) {
				oPlayer.reserve += toAdd;
				document.getElementById("compteur0").innerHTML = "&nbsp;";
				for (i=0;i<oPlayer.reserve;i++)
					document.getElementById("compteur0").innerHTML += '<img src="'+balloonSrc(oPlayer.team)+'" style="width: '+(iScreenScale*2)+'" />';
				return true;
			}
		}
	}
	return false;
}

function ai(oKart) {
	var completeBattle = (isBattle && complete);
	var completeCircuit = (!isBattle && complete);
	var simpleCircuit = (!isBattle && simplified);
	var simpleBattle = (isBattle && simplified);
	var normalBattle = ((course=="BB")&&!isCup);
	var smartBattle = completeBattle||normalBattle;
	var smartAI = complete||normalBattle;

	if ((oMap.sections && oKart.tours > oMap.tours) || (oKart.ballons && !oKart.ballons.length)) {
		oKart.speedinc = 0;
		oKart.rotincdir = 0;
		return;
	}
	if (!oKart.aipoints.length) return;

	if (smartBattle) {
		if (oKart.aipoint == undefined) {
			var minDist = Infinity;
			for (var i=0;i<oKart.aipoints.length;i++) {
				if (i != oKart.lastAI) {
					var iPt = oKart.aipoints[i];
					if (iPt[0] == 0) {
						var diffX = iPt[1]-oKart.x, diffY = iPt[2]-oKart.y;
						var gDist = Math.sqrt(diffX*diffX + diffY*diffY), aDist = gDist*(Math.abs((Math.atan2(diffX,diffY)-oKart.rotation*Math.PI/180+Math.PI)%(2*Math.PI)-Math.PI));
						var pDist = gDist*2 + aDist;
						if (pDist < minDist) {
							oKart.aipoint = i;
							minDist = pDist;
						}
					}
				}
			}
		}
		if (oKart.aipoint == undefined)
			return;
	}

	var aCurPoint = oKart.aipoints[oKart.aipoint];
	if (simpleBattle)
		aCurPoint = [(oKart.aipoint%6)*100+50,Math.floor(oKart.aipoint/6)*100+50];

	var iLocalX = aCurPoint[0] - oKart.x;
	var iLocalY = aCurPoint[1] - oKart.y;
	if (smartBattle) {
		iLocalX = aCurPoint[1] - oKart.x;
		iLocalY = aCurPoint[2] - oKart.y;
	}

	iRotatedX = iLocalX * direction(1, oKart.rotation) - iLocalY * direction(0, oKart.rotation);
	iRotatedY = iLocalX * direction(0, oKart.rotation) + iLocalY * direction(1, oKart.rotation);
	var iLocalD = iLocalX*iLocalX + iLocalY*iLocalY;

	var fAngle = Math.atan2(iRotatedX,iRotatedY) / Math.PI * 180;

	oKart.speedinc = (oKart.speed >= 0 ? 1 : 0.2);

	if (!isBattle) {
		if (oKart.speed < 0) {
			oKart.movesince = 12;
			if (oKart.retablit) {
				oKart.retablit = false;
				if (oKart.movesince) {
					oKart.movesince--;
					if (oKart.movesince < (complete?6:1))
						oKart.reflexion = 0;
				}
				if (oKart.reflexion) {
					oKart.reflexion--;
					if (oKart.reflexion == 0)
						oKart.decision = -oKart.decision;
				}
				else {
					if (oKart.horizontality) {
						var xp = direction(0,oKart.rotation), yp = direction(1,oKart.rotation);
						var xc = oKart.horizontality[0], yc = oKart.horizontality[1];
						oKart.decision = (xp*xc+yp*yc>0)!=(xp*yc-yp*xc>0) ? 1:-1;
					}
					else
						oKart.decision = -1;
				}
				if (!oKart.reflexion)
					oKart.reflexion = 5;
			}
			oKart.rotincdir = oKart.decision;
		}
		else {
			oKart.retablit = true;
			if (oKart.movesince) {
				oKart.movesince--;
				if (!oKart.movesince)
					oKart.reflexion = 0;
			}
			if (Math.abs(fAngle) > 7 + Math.random()*5) {
				if (smartAI) {
					var maxAngle = 10;
					var virage = true;
					if (fAngle < -maxAngle)
						oKart.rotation -= maxAngle;
					else if (fAngle > maxAngle)
						oKart.rotation += maxAngle;
					else {
						oKart.rotation += fAngle;
						virage = false;
					}
					if (virage) {
						if (!oKart.movesince) {
							if (oKart.protect || oKart.champi || oKart.z || !ralenti(Math.round(oKart.x), Math.round(oKart.y))) {
								oKart.speed = 60*Math.pow(maxAngle/Math.abs(fAngle), 2.5);
								oKart.speedinc = 0;
							}
						}
					}
				}
				else {
					if (Math.abs(fAngle) > 10 && oKart.speed > 5)
						oKart.speedinc = 0;
					if (oMap.skin == 24) {
						var maxAngle = 25+Math.random()*10;
						if (Math.abs(fAngle) > maxAngle)
							oKart.speed = Math.min(oKart.speed,5*Math.sqrt(maxAngle/Math.abs(fAngle)));
					}
					oKart.rotincdir = fAngle > 0 ? 1 : -1;
				}
			}
			else if (!smartAI)
				oKart.rotincdir = 0;
		}
		if (completeCircuit && iLocalD > 40000 && (fAngle < 7) && (oKart.arme == "champi" || oKart.arme == "megachampi" || oKart.arme == "etoile"))
			arme(aKarts.indexOf(oKart));
	}
	else if (smartBattle || oMap.skin == 27) {
		if (oKart.speed < 0) {
			if (!oKart.retablissement) {
				if (oKart.reflexion) {
					oKart.reflexion--;
					if (oKart.reflexion == 0)
						oKart.decision = -oKart.decision;
				}
				else
					oKart.decision = -1;
				if (!oKart.reflexion)
					oKart.reflexion = 5;
			}
			oKart.rotincdir = oKart.decision;
			oKart.retablissement = 7;
		}
		if (oKart.retablissement)
			oKart.retablissement--;
		else if (Math.abs(fAngle) > 7 + Math.random()*5) {
			var maxAngle = 10;
			var virage = true;
			if (fAngle < -maxAngle)
				oKart.rotation -= maxAngle;
			else if (fAngle > maxAngle)
				oKart.rotation += maxAngle;
			else {
				oKart.rotation += fAngle;
				virage = false;
			}
			if (virage) {
				oKart.speed = 10*maxAngle/Math.abs(fAngle);
				oKart.speedinc = 0;
			}
		}
	}
	else {
		if (Math.abs(fAngle) > 7 + Math.random()*5 || oKart.speed < 0) {
			if (course == "BB" && oMap.trous) {
				if (Math.abs(fAngle) > 10 && oKart.speed > 4)
					oKart.speedinc = 0;
				var maxAngle = 89.9;
				if (Math.abs(fAngle) > maxAngle) {
					oKart.speed = Math.min(oKart.speed,4*Math.pow(maxAngle/Math.abs(fAngle),4));
					oKart.movesince = 10;
				}
				else if (oKart.movesince > 0) {
					oKart.movesince--;
					oKart.speed = Math.min(oKart.speed, 1.5);
				}
			}
			if (simpleCircuit) {
				if (oMap.skin == 24 && oKart.speed > 0) {
					var maxAngle = 25+Math.random()*10;
					if (Math.abs(fAngle) > maxAngle)
						oKart.speed = Math.min(oKart.speed,5*Math.sqrt(maxAngle/Math.abs(fAngle)));
				}
			}
			oKart.rotincdir = fAngle > 0 && oKart.speed > 0 ? 1 : -1;
		}
		else
			oKart.rotincdir = 0;
	}

	if (oMap.jumpable && (iDificulty > 4)) {
		if (oKart.z && !oKart.jumped && !oKart.billball && !oKart.figstate && !oKart.figuring && !oKart.tourne && (oKart.heightinc > 0)) {
			if ((iLocalD > 5000) && (Math.abs(fAngle) < 13))
				oKart.figstate = 21;
		}
	}

	if (smartBattle) {
		if (iLocalD < 300) {
			var chemins = new Array();
			for (var i=0;i<oKart.aipoints.length;i++) {
				var iPt = oKart.aipoints[i];
				if (iPt[0] == 1) {
					if (iPt[1] == oKart.aipoint)
						chemins.push(iPt[2]);
					else if (iPt[2] == oKart.aipoint)
						chemins.push(iPt[1]);
				}
			}
			var origine = oKart.lastAI;
			oKart.lastAI = oKart.aipoint;
			if (chemins.length) {
				do {
					oKart.aipoint = chemins[Math.floor(Math.random()*chemins.length)];
				} while ((origine == oKart.aipoint) && (chemins.length > 1));
			}
			else
				oKart.aipoint = undefined;
		}
	}
	else if (simpleBattle) {
		var nbPos = Math.floor(oKart.x/100)+Math.floor(oKart.y/100)*6;
		if (nbPos != oKart.lastAI) {
			if (oMap.skin != 27 || iLocalD < 300 || oKart.speed < 0 || oKart.tombe) {
				var chemins = oKart.aipoints[nbPos];
				var origine = oKart.lastAI;
				oKart.lastAI = nbPos;
				do {
					oKart.aipoint = chemins[Math.floor(Math.random()*chemins.length)];
				} while ((origine == oKart.aipoint) && (chemins.length > 1));
			}
		}
	}
	else {
		if (iLocalD < (completeCircuit?300:1600)) {
			oKart.aipoint++;

			if (oKart.aipoint >= oKart.aipoints.length)
				oKart.aipoint = 0;
		}
	}
}

function cycle() {
	if (!pause) {
		setTimeout(cycle,67);
		runOneFrame();
	}
}
function runOneFrame() {
	for (var i=0;i<aKarts.length;i++) {
		var oKart = aKarts[i];
		if (i && (course == "CM") && !oKart.cpu) {
			var jTrajet = jTrajets[i-1];
			if (timer <= jTrajet.length) {
				var getInfos = jTrajet[timer-1];
				if (oKart.tombe) {
					oKart.tombe--;
					if (!oKart.tombe)
						oKart.sprite[0].img.style.display = "block";
				}
				oKart.x = getInfos[0];
				oKart.y = getInfos[1];
				oKart.rotation = getInfos[2];
				if (getInfos[3]) {
					oKart.z = getInfos[3]
					if (getInfos[4]) {
						oKart.tombe = 20;
						oKart.sprite[0].img.style.display = "none";
					}
				}
				continue;
			}
			else {
				oKart.cpu = true;
				oKart.aipoint = 0;
				oKart.arme = false;
			}
		}
		if (!oKart.loose || isOnline) {
			if (oKart.cpu) {
				if (!oKart.billball)
					ai(oKart);
				pCol(oKart);
			}
			move(i);
			if (oKart.protect)
				colKart(oKart);
			if (course == "CM" && !oKart.cpu) {
				var trajetplus = [oKart.x,oKart.y,oKart.rotation];
				if (oKart.z) {
					trajetplus.push(oKart.z);
					if (oKart.tombe == 20)
						trajetplus.push(true);
				}
				iTrajet.push(trajetplus);
			}
		}
	}
	if (course != "CM") {
		for (var i=0;i<aKarts.length;i++)
			places(i);
	}
	if (oMap.infoPlus)
		oMap.infoPlus({map:oMap,players:oPlayers,karts:aKarts});
	if (!oPlayers[0].cpu)
		challengeCheck("each_frame");
	if (refreshDatas)
		resetDatas();
	render();
}

var gameControls = {};
document.onkeydown = function(e) {
	var gameAction = gameControls[e.keyCode];
	switch (gameAction) {
		case "up":
			oPlayers[0].speedinc = 1;
			if (document.getElementById("decompte0").innerHTML > 1)
				updateEngineSound(carEngine2);
			return false;
		case "left":
			oPlayers[0].rotincdir = 1;
			return false;
		case "right":
			oPlayers[0].rotincdir = -1;
			return false;
	}
	if (oPlayers[1]) {
		switch (gameAction) {
			case "up_p2":
				oPlayers[1].speedinc = 1;
				break;
			case "left_p2":
				oPlayers[1].rotincdir = 1;
				break;
			case "right_p2":
				oPlayers[1].rotincdir = -1;
		}
	}
}


document.onkeyup = function(e) {
	var gameAction = gameControls[e.keyCode];
	switch (gameAction) {
		case "up":
			oPlayers[0].speedinc = 0;
			updateEngineSound(carEngine);
			break;
		case "left":
			oPlayers[0].rotincdir = 0;
			break;
		case "right":
			oPlayers[0].rotincdir = 0;
			break;
	}
	if (oPlayers[1]) {
		switch (gameAction) {
			case "up_p2":
				oPlayers[1].speedinc = 0;
				break;
			case "left_p2":
				oPlayers[1].rotincdir = 0;
				break;
			case "right_p2":
				oPlayers[1].rotincdir = 0;
		}
	}
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

if (!String.prototype.startsWith) {
	String.prototype.startsWith = function(searchString, position) {
		position = position || 0;
    	return this.indexOf(searchString, position) === position;
	};
}
function isCustomPerso(playerName) {
	if (playerName.startsWith("cp-")) {
		if (!customPersos[playerName]) {
			cp[playerName] = [0.6,1,0.6];
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
	return "images/sprites/sprite_" + playerName +".png";
}
function getMapIcSrc(playerName) {
	if (isCustomPerso(playerName))
		return customPersos[playerName].map;
	return "images/map_icons/"+ playerName +".png";
}
function getMapSelectorSrc(i) {
	return isCup ? (complete ? "trackicon.php?id="+ oMaps[aAvailableMaps[i]].map +"&type=" + (course=="BB" ? 2:1):"trackicon.php?id="+ oMaps[aAvailableMaps[i]].id +"&type=0") : "images/selectors/select_" + aAvailableMaps[i] + ".png";
}
function getMapId(oMap) {
	var res = isBattle ? nid : (simplified ? oMap.id : oMap.map);
	if (res === undefined) res = -1;
	return res;
}

function privateGame(options) {
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
		oContainers[0].removeChild(oScr);
		privateLink(options);
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
		oContainers[0].removeChild(oScr);
		shareLink.options = null;
		selectPlayerScreen(0);
	}
	oScr.appendChild(oPInput);

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Private game options...", "Options de la partie privée...");
	oPInput.style.fontSize = (2*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (52*iScreenScale)+"px";
	oPInput.style.top = (35*iScreenScale)+"px";
	oPInput.onclick = function() {
		oScr.innerHTML = "";
		oContainers[0].removeChild(oScr);
		privateGameOptions(shareLink.options, function(newOptions) {
			if (newOptions) {
				shareLink.options = newOptions;
				options = newOptions;
			}
			privateGame(options);
		});
	}
	oScr.appendChild(oPInput);

	oContainers[0].appendChild(oScr);
}
function privateGameOptions(gameOptions, onProceed) {
	var oScr = document.createElement("div");

	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";

	var oTitle;
	if (isOnline)
		oTitle = toTitle(toLanguage("Private game options", "Options partie privée"), 0);
	else
		oTitle = toTitle(toLanguage("Online game options", "Options mode en ligne"), 0);
	oTitle.style.fontSize = (7*iScreenScale)+"px";
	oScr.appendChild(oTitle);

	var oForm = document.createElement("form");
	oForm.style.position = "absolute";
	oForm.style.left = "0px";
	oForm.style.top = (8*iScreenScale) +"px";
	oForm.style.width = (iWidth*iScreenScale) +"px";
	oForm.onsubmit = function(e) {
		e.preventDefault();
		var team = this.elements["option-teams"].checked ? 1:0;
		var manualTeams = this.elements["option-manualTeams"].checked ? 1:0;
		if (!team)
			manualTeams = 0;
		var friendly = this.elements["option-friendly"].checked ? 1:0;
		onProceed({
			team: team,
			manualTeams: manualTeams,
			friendly: friendly
		});
		oScr.innerHTML = "";
		oContainers[0].removeChild(oScr);
	};

	var oScroll = document.createElement("div");
	oScroll.style.height = (20*iScreenScale) +"px";
	oScroll.style.overflow = "auto";

	var oTable = document.createElement("table");
	oTable.style.marginLeft = "auto";
	oTable.style.marginRight = "auto";

	var oTr = document.createElement("tr");
	var oTd = document.createElement("td");
	oTd.style.textAlign = "center";
	oTd.style.width = (iScreenScale*8) +"px";
	var oCheckbox = document.createElement("input");
	oCheckbox.style.transform = oCheckbox.style.WebkitTransform = oCheckbox.style.MozTransform = "scale("+ Math.round(iScreenScale/3) +")";
	oCheckbox.id = "option-teams";
	oCheckbox.name = "option-teams";
	oCheckbox.type = "checkbox";
	if (gameOptions && gameOptions.team)
		oCheckbox.checked = true;
	//if (isOnline) {
		oCheckbox.onchange = function() {
			if (this.checked)
				document.getElementById("option-manualTeams-ctn").style.display = "";
			else
				document.getElementById("option-manualTeams-ctn").style.display = "none";
		}
	//}
	oTd.appendChild(oCheckbox);
	oTr.appendChild(oTd);

	var oTd = document.createElement("td");
	var oLabel = document.createElement("label");
	oLabel.style.cursor = "pointer";
	oLabel.setAttribute("for", "option-teams");
	var oH1 = document.createElement("h1");
	oH1.style.fontSize = (3*iScreenScale) +"px";
	oH1.style.marginBottom = "0px";
	oH1.innerHTML = toLanguage("Team games","Parties par équipe");
	oLabel.appendChild(oH1);
	var oDiv = document.createElement("div");
	oDiv.style.fontSize = (2*iScreenScale) +"px";
	oDiv.style.color = "white";
	oDiv.innerHTML = toLanguage("If enabled, 2 teams are selected in each game. You object: defeat the opposing team.", "Si activé, 2 équipes sont sélectionnées à chaque partie. Votre objectif : vaincre l'équipe adverse.");
	oLabel.appendChild(oDiv);
	oTd.appendChild(oLabel);
	oTd.style.padding = Math.round(iScreenScale*1.5) +"px 0";
	oTr.appendChild(oTd);
	oTable.appendChild(oTr);

	var oTr = document.createElement("tr");
	oTr.id = "option-manualTeams-ctn";
	if (!gameOptions || !gameOptions.team)
		oTr.style.display = "none";
	var oTd = document.createElement("td");
	oTd.style.textAlign = "center";
	oTd.style.width = (iScreenScale*8) +"px";
	var oCheckbox = document.createElement("input");
	oCheckbox.style.position = "relative";
	oCheckbox.style.left = Math.round(iScreenScale*1.5) +"px";
	oCheckbox.style.transform = oCheckbox.style.WebkitTransform = oCheckbox.style.MozTransform = "scale("+ Math.round(iScreenScale/3) +")";
	oCheckbox.id = "option-manualTeams";
	oCheckbox.name = "option-manualTeams";
	oCheckbox.type = "checkbox";
	if (gameOptions && gameOptions.team && gameOptions.manualTeams)
		oCheckbox.checked = true;
	oTd.appendChild(oCheckbox);
	oTr.appendChild(oTd);

	var oTd = document.createElement("td");
	var oLabel = document.createElement("label");
	oLabel.style.cursor = "pointer";
	oLabel.style.display = "inline-block";
	oLabel.setAttribute("for", "option-manualTeams");
	var oH1 = document.createElement("h1");
	oH1.style.marginTop = 0;
	oH1.style.marginLeft = Math.round(iScreenScale*1.5) +"px";
	oH1.style.fontSize = (3*iScreenScale) +"px";
	oH1.style.marginBottom = "0px";
	oH1.innerHTML = toLanguage("Manual selection","Sélection manuelle");
	oLabel.appendChild(oH1);
	var oDiv = document.createElement("div");
	oDiv.style.paddingLeft = Math.round(iScreenScale*1.5) +"px";
	oDiv.style.fontSize = (2*iScreenScale) +"px";
	oDiv.style.color = "white";
	oDiv.innerHTML = toLanguage("If enabled, teams are selected manually at each game.", "Si activé, les équipes sont sélectionnées manuellement à chaque partie. Sinon, les équipes sont formées automatiquement en fonction du niveau de chaque joueur.");
	oLabel.appendChild(oDiv);
	oTd.appendChild(oLabel);
	oTd.style.paddingBottom = Math.round(iScreenScale*1.5) +"px";
	oTr.appendChild(oTd);
	oTable.appendChild(oTr);

	var oTr = document.createElement("tr");
	var oTd = document.createElement("td");
	oTd.style.textAlign = "center";
	oTd.style.width = (iScreenScale*8) +"px";
	var oCheckbox = document.createElement("input");
	oCheckbox.id = "option-friendly";
	oCheckbox.name = "option-friendly";
	oCheckbox.type = "checkbox";
	if (gameOptions && gameOptions.friendly)
		oCheckbox.checked = true;
	oCheckbox.style.transform = oCheckbox.style.WebkitTransform = oCheckbox.style.MozTransform = "scale("+ Math.round(iScreenScale/3) +")";
	oTd.appendChild(oCheckbox);
	oTr.appendChild(oTd);

	var oTd = document.createElement("td");
	var oLabel = document.createElement("label");
	oLabel.style.cursor = "pointer";
	oLabel.setAttribute("for", "option-friendly");
	oTd.appendChild(oLabel);

	var oH1 = document.createElement("h1");
	oH1.style.fontSize = (3*iScreenScale) +"px";
	oH1.innerHTML = toLanguage("Friendly game", "Matchs amicaux");
	oH1.style.marginBottom = "0px";
	oLabel.appendChild(oH1);
	var oDiv = document.createElement("div");
	oDiv.style.fontSize = (2*iScreenScale) +"px";
	oDiv.style.color = "white";
	oDiv.innerHTML = toLanguage("If enabled, games won't make you win or lose points in the online mode.", "Si activé, les parties ne vous feront pas gagner ou perdre de points dans le mode en ligne.");
	oLabel.appendChild(oDiv);
	oTd.appendChild(oLabel);
	oTr.appendChild(oTd);
	oTable.appendChild(oTr);

	oScroll.appendChild(oTable);

	oForm.appendChild(oScroll);

	var oDiv = document.createElement("div");
	oDiv.style.textAlign = "center";
	oDiv.style.marginTop = (2*iScreenScale)+"px";
	var oSubmit = document.createElement("input");
	oSubmit.type = "submit";
	oSubmit.value = toLanguage("Validate", "Valider");
	oSubmit.style.fontSize = (3*iScreenScale)+"px";
	oSubmit.style.width = (18*iScreenScale)+"px";
	oDiv.appendChild(oSubmit);
	oForm.appendChild(oDiv);

	oScr.appendChild(oForm);

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Cancel", "Annuler");
	oPInput.style.fontSize = (2*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (2*iScreenScale)+"px";
	oPInput.style.top = (35*iScreenScale)+"px";
	oPInput.onclick = function() {
		oScr.innerHTML = "";
		oContainers[0].removeChild(oScr);
		onProceed(null);
	}
	oScr.appendChild(oPInput);

	oContainers[0].appendChild(oScr);
}
function privateLink(options) {
	var oScr = document.createElement("div");

	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";

	oScr.appendChild(toTitle(toLanguage("Private game", "Partie privée"), 0));

	xhr("privateGame.php", isCustomOptions(options) ? ("options="+encodeURIComponent(JSON.stringify(options))):null, function(res) {
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
	
	oContainers[0].appendChild(oScr);
}

function selectTypeScreen() {
	var FBRoot;
	
	var oScr = document.createElement("div");

	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";

	var oTitle = new Image();
	oTitle.src = "images/mariokart.gif";
	oTitle.style.position = "absolute";
	oTitle.style.width = (39*iScreenScale)+"px";
	oTitle.style.height = (10*iScreenScale)+"px";
	oTitle.style.left = ((iWidth-39)/2*iScreenScale)+"px";
	oTitle.style.top = iScreenScale+"px";
	oScr.appendChild(oTitle);

	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";
	oContainers[0].appendChild(oScr);

	if (page == "MK") {
		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = "Grand Prix";
		oPInput.style.fontSize = (3*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (10*iScreenScale)+"px";
		oPInput.style.top = (14*iScreenScale)+"px";
		oPInput.style.width = (29*iScreenScale)+"px";

		oPInput.onclick = function() {
			course = "GP";
			FBRoot.style.display = "none";
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			selectPlayerScreen(0);
		};
		oScr.appendChild(oPInput);

		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = toLanguage("Time trial", "Contre-la-montre");
		oPInput.style.fontSize = (3*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (40*iScreenScale)+"px";
		oPInput.style.top = (14*iScreenScale)+"px";
		oPInput.style.width = (29*iScreenScale)+"px";
		oPInput.onclick = function() {
			course = "CM";
			FBRoot.style.display = "none";
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			selectPlayerScreen(0);
		};

		oScr.appendChild(oPInput);

		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = toLanguage("VS", "Course VS");
		oPInput.style.fontSize = (3*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = "0px";
		oPInput.style.top = (21*iScreenScale)+"px";
		oPInput.style.width = (29*iScreenScale)+"px";

		oPInput.onclick = function() {
			course = "VS";
			FBRoot.style.display = "none";
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			selectNbJoueurs();
		};
		oScr.appendChild(oPInput);

		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = toLanguage("Battle", "Bataille");
		oPInput.style.fontSize = (3*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (50*iScreenScale)+"px";
		oPInput.style.top = (21*iScreenScale)+"px";
		oPInput.style.width = (29*iScreenScale)+"px";
		oPInput.onclick = function() {
			course = "BB";
			FBRoot.style.display = "none";
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			selectNbJoueurs();
		};
		oScr.appendChild(oPInput);

		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = toLanguage("Track builder", "Éditeur de circuit");
		oPInput.style.fontSize = (3*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (10*iScreenScale)+"px";
		oPInput.style.top = (29*iScreenScale)+"px";
		oPInput.style.width = (29*iScreenScale)+"px";
		oPInput.onclick = function() {
			FBRoot.style.display = "none";
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			selectTypeCreate();
		}
		oScr.appendChild(oPInput);

		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = toLanguage("Online race", "Course en ligne");
		oPInput.style.fontSize = (3*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (40*iScreenScale)+"px";
		oPInput.style.top = (29*iScreenScale)+"px";
		oPInput.style.width = (29*iScreenScale)+"px";
		oPInput.onclick = function() {
			course = "VS";
			FBRoot.style.display = "none";
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			selectOnlineScreen();
		}
		oScr.appendChild(oPInput);

		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = toLanguage("Home", "Accueil");
		oPInput.style.fontSize = (2*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (2*iScreenScale)+"px";
		oPInput.style.top = (35*iScreenScale)+"px";
		oPInput.onclick = function() {
			document.location.href = "index.php";
		}
		oScr.appendChild(oPInput);
	}
	else {
		var oModes = [toLanguage("VS", "Course VS")];
		var oModeIds = ["VS"];
		if (!isSingle) {
			oModes.unshift("Grand Prix");
			oModeIds.unshift("GP");
		}
		if (hasChallenges() || myCircuit) {
			oModes.push(toLanguage("Challenges", "Défis"));
			oModeIds.push("CH");
		}
		if (nid || !isSingle) {
			oModes.push(toLanguage("Time Trial", "Contre-la-montre"));
			oModeIds.push("CM");
		}
		if (nid && (!isSingle||!complete||cShared)) {
			oModes.push(toLanguage("Online race", "Course en ligne"));
			oModeIds.push("CL");
		}
		if (!isSingle && cupScore) {
			var oCup = new Image();
			oCup.src = "images/cups/cup"+ (4-cupScore) +".png";
			oCup.style.width = Math.round(4 * iScreenScale) + "px";
			oCup.style.height = Math.round(4 * iScreenScale) + "px";
			oCup.style.position = "absolute"
			if (oModes.length < 5) {
				oCup.style.left = (3*iScreenScale)+"px";
				oCup.style.top = Math.round(18*iScreenScale)+"px";
			}
			else {
				oCup.style.left = (5*iScreenScale)+"px";
				oCup.style.top = Math.round(14*iScreenScale)+"px";
			}
			oCup.className = "pixelated";
			oScr.appendChild(oCup);
		}

		for (i=0;i<oModes.length;i++) {
			var oPInput = document.createElement("input");
			oPInput.type = "button";
			oPInput.value = oModes[i];
			oPInput.style.position = "absolute";
			if (oModes.length < 4) {
				oPInput.style.left = (20*iScreenScale)+"px";
				oPInput.style.top = Math.round((14+i*6.5)*iScreenScale)+"px";
				oPInput.style.width = (38*iScreenScale)+"px";
				oPInput.style.fontSize = Math.round(3.5*iScreenScale)+"px";
			}
			else if (oModes.length == 4) {
				oPInput.style.left = ((8+(i%2)*36)*iScreenScale)+"px";
				oPInput.style.top = ((18+Math.floor(i/2)*8)*iScreenScale)+"px";
				oPInput.style.width = (28*iScreenScale)+"px";
				oPInput.style.fontSize = (3*iScreenScale)+"px";
			}
			else {
				var buttonsPos = [[10,14],[40,14],[25,21],[10,29],[40,29]];
				var buttonPos = buttonsPos[i];
				oPInput.style.left = (buttonPos[0]*iScreenScale)+"px";
				oPInput.style.top = (buttonPos[1]*iScreenScale)+"px";
				oPInput.style.fontSize = (3*iScreenScale)+"px";
				oPInput.style.width = (29*iScreenScale)+"px";
			}
			if (!oPInput.dataset)
				oPInput.dataset = {};
			oPInput.dataset.course = oModeIds[i];

			oPInput.onclick = function() {
				course = this.dataset.course;
				if (course == "CL")
					document.location.href = "online.php?"+(isMCups?"mid="+nid:(isSingle?(complete?"i":"id"):(complete?"cid":"sid"))+"="+nid);
				else {
					FBRoot.style.display = "none";
					oScr.innerHTML = "";

					oContainers[0].removeChild(oScr);
					if (course == "VS")
						selectNbJoueurs();
					else if (course == "CH")
						selectChallengesScreen();
					else
						selectPlayerScreen(0);
				}
			};
			oScr.appendChild(oPInput);
		}

		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = toLanguage("Back", "Retour");
		oPInput.style.fontSize = (2*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (2*iScreenScale)+"px";
		oPInput.style.top = (35*iScreenScale)+"px";
		oPInput.onclick = function() {
			exitCircuit();
		}
		oScr.appendChild(oPInput);
	}

	FBRoot = document.getElementById("fb-root");
	var FBToLoad = !FBRoot;
	if (FBToLoad) {
		FBRoot = document.createElement("div");
		FBRoot.id = "fb-root";
		FBRoot.style.position = "absolute";
	}
	else
		FBRoot.style.display = "";
	FBRoot.style.left = (16*iScreenScale - 12)+"px";
	FBRoot.style.top = (36*iScreenScale + 2)+"px";
	FBRoot.style.transform = FBRoot.style.WebkitTransform = FBRoot.style.MozTransform = "scale("+ (iScreenScale/7) +")";
	if (FBToLoad) {
		var FBshare = document.createElement("div");
		FBshare.className = "fb-share-button";
		FBshare.dataset.href = document.location.href;
		FBshare.dataset.layout = "button";
		FBRoot.appendChild(FBshare);
		document.body.appendChild(FBRoot);
		
		(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/"+ (language ? "en_EN":"fr_FR") +"/sdk.js#xfbml=1&version=v2.4";
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));
	}

	var oEnglish = document.createElement("img");
	oEnglish.src = "images/english.png";
	oEnglish.alt = "En";
	oEnglish.style.position = "absolute";
	oEnglish.style.left = (68*iScreenScale)+"px";
	oEnglish.style.top = (35*iScreenScale)+"px";
	oEnglish.style.width = (4*iScreenScale)+"px";
	oEnglish.style.height = Math.round(8*iScreenScale/3)+"px";
	
	var oFrench = document.createElement("img");
	oFrench.src = "images/french.png";
	oEnglish.alt = "Fr";
	oFrench.style.position = "absolute";
	oFrench.style.left = (74*iScreenScale)+"px";
	oFrench.style.top = (35*iScreenScale)+"px";
	oFrench.style.width = (4*iScreenScale)+"px";
	oFrench.style.height = Math.round(8*iScreenScale/3)+"px";
	
	var oSelected = language ? oEnglish:oFrench, oButton = language ? oFrench:oEnglish;
	oSelected.style.border = "solid 1px yellow";
	oButton.style.border = "solid 1px transparent";
	oButton.style.cursor = "pointer";
	oButton.onmouseover = function() {
		oButton.style.border = "solid 1px yellow";
	}
	oButton.onmouseout = function() {
		oButton.style.border = "solid 1px transparent";
	}
	oButton.onclick = function() {
		language = !language;
		xhr("setLanguage.php", "nLanguage="+ language*1, function(reponse) {
			if (reponse == 1) {
				location.reload();
				return true;
			}
			else
				return false;
		});
	}
	
	oScr.appendChild(oEnglish);
	oScr.appendChild(oFrench);

	updateMenuMusic(0);
}
function selectMainPage() {
	switch (page) {
		case "OL":
			if (mId)
				selectPlayerScreen(0);
			else
				connexion();
			break;
		case "MK":
			selectTypeScreen();
			break;
		case "CI":
		case "MA":
			if (nid)
				selectTypeScreen();
			else {
				course = "VS";
				selectNbJoueurs();
			}
			break;
		case "BA":
		case "AR":
			course = "BB";
			selectNbJoueurs();
	}
}

function selectNbJoueurs() {
	var oScr = document.createElement("div");
	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";

	oScr.appendChild(toTitle(toLanguage("Number of players", "Nombre de joueurs"), 0.5));

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Back", "Retour");
	oPInput.style.fontSize = (2*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (2*iScreenScale)+"px";
	oPInput.style.top = (35*iScreenScale)+"px";
	oPInput.onclick = function() {
		if (!isBattle && (!isCup||nid)) {
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			selectTypeScreen();
		}
		else
			exitCircuit();
	}
	oScr.appendChild(oPInput);

	var sShared = (isBattle&&cShared);

	for (i=1;i<=2;i++) {
		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.id = "select-nbj-"+i;
		oPInput.value = i + (i<2 ? "  " : " ") + toLanguage("player","joueur") + (i<2 ? " " : "s");
		oPInput.style.fontSize = (4*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = ((sShared?26:27)*iScreenScale)+"px";
		oPInput.style.top = (((sShared?7:10)+i*(sShared?7:8))*iScreenScale)+"px";
		if (sShared) {
			oPInput.style.paddingLeft = (iScreenScale*2) +"px";
			oPInput.style.paddingRight = (iScreenScale*2) +"px";
		}

		oPInput.onclick = function() {
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			if (this.value.charAt(0) == "2") {
				var oContainer2 = oContainers[0].cloneNode(false);
				oContainer2.style.left = (10+iWidth*iScreenScale)+"px";
				oContainers.push(oContainer2);
				var fElements = ["temps", "compteur", "infos", "infoPlace", "lakitu", "drift", "scroller"];
				for (var i=0;i<fElements.length;i++) {
					var bElement = document.getElementById(fElements[i]+0);
					if (bElement) {
						var fElement = bElement.cloneNode(true);
						fElement.id = fElements[i]+1;
						document.body.appendChild(fElement);
					}
				}
			}
			selectPlayerScreen(0);
		};
		oScr.appendChild(oPInput);
	}
	if (sShared) {
		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = toLanguage("Online mode", "Mode en ligne");
		oPInput.style.fontSize = (3*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (26*iScreenScale)+"px";
		oPInput.style.top = (30*iScreenScale)+"px";
		oPInput.style.paddingTop = Math.round(iScreenScale*0.5) +"px";
		oPInput.style.paddingBottom = Math.round(iScreenScale*0.5) +"px";
		oPInput.onclick = function() {
			document.location.href = "online.php?"+ (complete ? "i":"id") +"="+ nid +"&battle";
		}
		oScr.appendChild(oPInput);
	}
	oContainers[0].appendChild(oScr);

	if ((myCircuit || hasChallenges()) && (!nid || isBattle)) {
		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = toLanguage("Challenges...", "Défis...");
		oPInput.style.fontSize = (2*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.right = (2*iScreenScale)+"px";
		oPInput.style.top = (35*iScreenScale)+"px";
		oPInput.onclick = function() {
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			selectChallengesScreen();
		}
		oScr.appendChild(oPInput);
	}

	updateMenuMusic(0);
}

function selectOnlineScreen(options) {
	var oScr = document.createElement("div");
	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";

	oScr.appendChild(toTitle(toLanguage("Online mode", "Mode en ligne"), 2));

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Back", "Retour");
	oPInput.style.fontSize = (2*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (2*iScreenScale)+"px";
	oPInput.style.top = (35*iScreenScale)+"px";
	oPInput.onclick = function() {
		oScr.innerHTML = "";
		oContainers[0].removeChild(oScr);
		selectTypeScreen();
	}
	oScr.appendChild(oPInput);

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("More options...", "Plus d'options...");
	oPInput.style.fontSize = (2*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (60*iScreenScale)+"px";
	oPInput.style.top = (35*iScreenScale)+"px";
	oPInput.onclick = function() {
		oScr.innerHTML = "";
		oContainers[0].removeChild(oScr);
		privateGameOptions(options, function(newOptions) {
			if (newOptions) {
				options = newOptions;
				if (!isCustomOptions(options))
					options = null;
			}
			selectOnlineScreen(options);
		});
	}
	oScr.appendChild(oPInput);

	if (options) {
		var oDiv = document.createElement("div");
		oDiv.style.position = "absolute";
		oDiv.style.left = "0px";
		oDiv.style.width = (iWidth*iScreenScale) +"px";
		oDiv.style.textAlign = "center";
		oDiv.style.top = (13*iScreenScale) +"px";
		oDiv.style.color = "white";
		oDiv.style.fontSize = (iScreenScale*2) +"px";
		oDiv.innerHTML = "\u26A0 " + toLanguage("By choosing specific rules, you might encounter less opponents.", "En choisissant des options spécifiques, vous risquez de trouver moins d'adversaires.");
		oScr.appendChild(oDiv);
	}

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = language ? "VS mode":"Course VS";
	oPInput.style.fontSize = Math.round(3.5*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (22*iScreenScale)+"px";
	oPInput.style.top = ((options ? 18:17)*iScreenScale)+"px";
	oPInput.style.width = (36*iScreenScale)+"px";
	oPInput.onclick = function() {
		oScr.innerHTML = "";
		oContainers[0].removeChild(oScr);
		openOnlineMode(false, options);
	};
	oScr.appendChild(oPInput);

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = language ? "Battle mode":"Bataille de ballons";
	oPInput.style.fontSize = Math.round(3.5*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (22*iScreenScale)+"px";
	oPInput.style.top = ((options ? 26:25)*iScreenScale)+"px";
	oPInput.style.width = (36*iScreenScale)+"px";
	oPInput.onclick = function() {
		oScr.innerHTML = "";
		oContainers[0].removeChild(oScr);
		openOnlineMode(true, options);
	};
	oScr.appendChild(oPInput);

	oContainers[0].appendChild(oScr);

	updateMenuMusic(0);
}

function openOnlineMode(isBattle, options) {
	if (options) {
		xhr("onlineOptions.php", "options="+encodeURIComponent(JSON.stringify(options)), function(res) {
			if (res) {
				document.location.href = "online.php?"+ (isBattle ? "battle&":"")+("key="+ res);
				return true;
			}
			return false;
		});
	}
	else
		document.location.href = "online.php"+ (isBattle ? "?battle":"");
}
function openChallengeEditor() {
	if (clId && !edittingCircuit)
		document.location.href = "challenges.php?cl="+clId;
	else
		document.location.href = document.location.href.replace(/\/(\w+)\.php\?(.+)$/g, '/challenges.php?page=$1&$2');
}

function selectTypeCreate() {
	var oScr = document.createElement("div");
	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";

	oScr.appendChild(toTitle(toLanguage("Track builder", "Éditeur de circuit"), 0.5));
	
	var oDiv = document.createElement("div");
	oDiv.style.color = "white";
	oDiv.style.fontWeight = "normal";
	oDiv.style.position = "absolute";
	oDiv.style.fontSize = Math.round(2.5*iScreenScale)+"px";
	oDiv.style.left = (2*iScreenScale)+"px";
	oDiv.style.top = Math.round(14.5*iScreenScale)+"px";
	oDiv.style.width = (20*iScreenScale)+"px";
	oDiv.style.textAlign = "right";
	oDiv.innerHTML = toLanguage("Quick mode :", "Mode simplifié :");
	oScr.appendChild(oDiv);

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = "Circuit";
	oPInput.style.fontSize = Math.round(2.5*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (24*iScreenScale)+"px";
	oPInput.style.top = (14*iScreenScale)+"px";
	oPInput.style.width = (10*iScreenScale)+"px";

	oPInput.onclick = function() {
		document.location.href = "create.php";
	};
	oScr.appendChild(oPInput);

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Arena", "Arène");
	oPInput.style.fontSize = Math.round(2.5*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (36*iScreenScale)+"px";
	oPInput.style.top = (14*iScreenScale)+"px";
	oPInput.style.width = (10*iScreenScale)+"px";

	oPInput.onclick = function() {
		document.location.href = "arene.php";
	};
	oScr.appendChild(oPInput);

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Cup", "Coupe");
	oPInput.style.fontSize = Math.round(2.5*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (48*iScreenScale)+"px";
	oPInput.style.top = (14*iScreenScale)+"px";
	oPInput.style.width = (10*iScreenScale)+"px";

	oPInput.onclick = function() {
		document.location.href = "simplecup.php";
	};
	oScr.appendChild(oPInput);

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Multi Cup", "Multicoupe");
	oPInput.style.fontSize = Math.round(2.5*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (60*iScreenScale)+"px";
	oPInput.style.top = (14*iScreenScale)+"px";
	oPInput.style.width = (16*iScreenScale)+"px";

	oPInput.onclick = function() {
		document.location.href = "simplecups.php";
	};
	oScr.appendChild(oPInput);
	
	var oDiv = document.createElement("div");
	oDiv.style.position = "absolute";
	oDiv.style.color = "white";
	oDiv.style.fontWeight = "normal";
	oDiv.style.fontSize = Math.round(2.5*iScreenScale)+"px";
	oDiv.style.left = (2*iScreenScale)+"px";
	oDiv.style.top = Math.round(21.5*iScreenScale)+"px";
	oDiv.style.width = (20*iScreenScale)+"px";
	oDiv.style.textAlign = "right";
	oDiv.innerHTML = toLanguage("Complete mode :", "Mode complet :");
	oScr.appendChild(oDiv);

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = "Circuit";
	oPInput.style.fontSize = Math.round(2.5*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (24*iScreenScale)+"px";
	oPInput.style.top = Math.round(21*iScreenScale)+"px";
	oPInput.style.width = (10*iScreenScale)+"px";

	oPInput.onclick = function() {
		document.location.href = "draw.php";
	};
	oScr.appendChild(oPInput);

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Arena", "Arène");
	oPInput.style.fontSize = Math.round(2.5*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (36*iScreenScale)+"px";
	oPInput.style.top = Math.round(21*iScreenScale)+"px";
	oPInput.style.width = (10*iScreenScale)+"px";

	oPInput.onclick = function() {
		document.location.href = "course.php";
	};
	oScr.appendChild(oPInput);

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Cup", "Coupe");
	oPInput.style.fontSize = Math.round(2.5*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (48*iScreenScale)+"px";
	oPInput.style.top = Math.round(21*iScreenScale)+"px";
	oPInput.style.width = (10*iScreenScale)+"px";

	oPInput.onclick = function() {
		document.location.href = "completecup.php";
	};
	oScr.appendChild(oPInput);

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Multi Cup", "Multicoupe");
	oPInput.style.fontSize = Math.round(2.5*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (60*iScreenScale)+"px";
	oPInput.style.top = Math.round(21*iScreenScale)+"px";
	oPInput.style.width = (16*iScreenScale)+"px";

	oPInput.onclick = function() {
		document.location.href = "completecups.php";
	};
	oScr.appendChild(oPInput);
	
	var oLink = document.createElement("a");
	oLink.style.color = "#CCF";
	oLink.style.fontSize = Math.round(2.5*iScreenScale)+"px";
	oLink.style.position = "absolute";
	oLink.style.left = (24*iScreenScale)+"px";
	oLink.style.top = Math.round(29.5*iScreenScale)+"px";
	oLink.href = "#null";
	oLink.innerHTML = toLanguage("Help", "Aide");
	oLink.onclick = function() {
		var oDivHelp = document.createElement("div");
		oDivHelp.style.position = "absolute";
		oDivHelp.style.left = "0px";
		oDivHelp.style.top = "0px";
		oDivHelp.style.width = (iScreenScale*iWidth) +"px";
		oDivHelp.style.height = (iScreenScale*iHeight) +"px";
		oDivHelp.style.backgroundColor = "rgba(0,0,0,0.7)";
		oDivHelp.style.fontWeight = "normal";

		var oTable = document.createElement("table");
		oTable.style.position = "absolute";
		oTable.style.left = (iScreenScale*30) +"px";
		oTable.style.width = (iScreenScale*50) +"px";
		oTable.style.top = (-iScreenScale*2) +"px";
		oTable.style.color = "#333";
		oTable.style.opacity = 0.93;
		oTable.style.textAlign = "center";
		oTable.style.borderSpacing = "0 "+ (iScreenScale*5) +"px";
		oTable.style.fontSize = (iScreenScale*2) +"px";
		var oTr = document.createElement("tr");
		oTr.style.backgroundColor = "#CCC";
		var oTd = document.createElement("td");
		if (language)
			oTd.innerHTML = '<strong>Quick mode:</strong> create a track in a few clics thanks to ready-made pieces';
		else
			oTd.innerHTML = '<strong>Mode simplifié :</strong> créez un circuit en quelques clics grâce à des pièces toutes faites';
		oTd.style.padding = (iScreenScale*2) +"px " + (iScreenScale*3) +"px";
		oTr.appendChild(oTd);
		var oTd = document.createElement("td");
		oTd.style.width = (iScreenScale*10) +"px";
		oTd.style.height = (iScreenScale*14) +"px";
		oTd.innerHTML = '<img src="images/help/mode-simple.png" style="height: 100%" alt="Simplify" />';
		oTr.appendChild(oTd);
		oTable.appendChild(oTr);
		var oTr = document.createElement("tr");
		oTr.style.backgroundColor = "#CCC";
		var oTd = document.createElement("td");
		if (language)
			oTd.innerHTML = '<strong>Complete mode:</strong> create entierely the track from an image you draw yourself';
		else
			oTd.innerHTML = '<strong>Mode complet :</strong> créez entièrement le circuit à partir d\'une image dessinée par vous-même';
		oTd.style.padding = (iScreenScale*2) +"px " + (iScreenScale*3) +"px";
		oTr.appendChild(oTd);
		var oTd = document.createElement("td");
		oTd.style.width = (iScreenScale*10) +"px";
		oTd.style.height = (iScreenScale*14) +"px";
		oTd.innerHTML = '<img src="images/help/mode-complete.png" style="height: 100%" alt="Complify" />';
		oTr.appendChild(oTd);
		oTable.appendChild(oTr);

		oDivHelp.appendChild(oTable);

		var simpleFrame = document.createElement("div");
		simpleFrame.style.color = "white";
		simpleFrame.style.position = "absolute";
		simpleFrame.style.fontSize = Math.round(2.5*iScreenScale)+"px";
		simpleFrame.style.right = ((iWidth-22.9)*iScreenScale)+"px";
		simpleFrame.style.top = Math.round(14*iScreenScale)+"px";
		simpleFrame.style.textAlign = "right";
		simpleFrame.style.border = "solid "+ Math.round(0.4*iScreenScale) +"px #99C";
		simpleFrame.style.padding = Math.round(0.1*iScreenScale)+"px "+Math.round(0.5*iScreenScale)+"px";
		simpleFrame.innerHTML = toLanguage("Quick mode :", "Mode simplifié :");
		oDivHelp.appendChild(simpleFrame);

		var oLineAngle = Math.PI*0.3;
		var oLineHeight = 3;
		var oLine1 = document.createElement("div");
		oLine1.style.position = "absolute";
		oLine1.style.left = (21*iScreenScale) +"px";
		oLine1.style.top = Math.round((14.15-oLineHeight)*iScreenScale) +"px";
		oLine1.style.width = Math.round(0.5*iScreenScale) +"px";
		oLine1.style.height = Math.round(oLineHeight/Math.cos(oLineAngle)*iScreenScale) +"px";
		oLine1.style.backgroundColor = "#99C";
		oLine1.style.transform = oLine1.style.WebkitTransform = oLine1.style.MozTransform = "rotate("+Math.round(oLineAngle*180/Math.PI)+"deg)";
		oLine1.style.transformOrigin = oLine1.style.WebkitTransformOrigin = oLine1.style.MozTransformOrigin = "top center";
		oDivHelp.appendChild(oLine1);

		var oLine2 = document.createElement("div");
		oLine2.style.position = "absolute";
		oLine2.style.left = (21*iScreenScale) +"px";
		oLine2.style.top = Math.round((14.05-oLineHeight)*iScreenScale) +"px";
		oLine2.style.width = Math.round(9*iScreenScale) +"px";
		oLine2.style.height = Math.round(0.4*iScreenScale) +"px";
		oLine2.style.backgroundColor = "#99C";
		oDivHelp.appendChild(oLine2);

		var completeFrame = document.createElement("div");
		completeFrame.style.color = "white";
		completeFrame.style.position = "absolute";
		completeFrame.style.fontSize = Math.round(2.5*iScreenScale)+"px";
		completeFrame.style.right = ((iWidth-22.9)*iScreenScale)+"px";
		completeFrame.style.top = Math.round(21*iScreenScale)+"px";
		completeFrame.style.textAlign = "right";
		completeFrame.style.border = "solid "+ Math.round(0.4*iScreenScale) +"px #99C";
		completeFrame.style.padding = Math.round(0.1*iScreenScale)+"px "+Math.round(0.5*iScreenScale)+"px";
		completeFrame.innerHTML = toLanguage("Complete mode :", "Mode complet :");
		oDivHelp.appendChild(completeFrame);
		
		var oLineAngle = Math.PI*0.3;
		var oLineHeight = 3;
		var oLine1 = document.createElement("div");
		oLine1.style.position = "absolute";
		oLine1.style.left = (21*iScreenScale) +"px";
		oLine1.style.top = Math.round((25.35-oLineHeight)*iScreenScale) +"px";
		oLine1.style.width = Math.round(0.5*iScreenScale) +"px";
		oLine1.style.height = Math.round(oLineHeight/Math.cos(oLineAngle)*iScreenScale) +"px";
		oLine1.style.backgroundColor = "#99C";
		oLine1.style.transform = oLine1.style.WebkitTransform = oLine1.style.MozTransform = "rotate("+Math.round(-oLineAngle*180/Math.PI)+"deg)";
		oLine1.style.transformOrigin = oLine1.style.WebkitTransformOrigin = oLine1.style.MozTransformOrigin = "bottom center";
		oDivHelp.appendChild(oLine1);

		var oLine2 = document.createElement("div");
		oLine2.style.position = "absolute";
		oLine2.style.left = (21*iScreenScale) +"px";
		oLine2.style.top = Math.round((24.3+oLineHeight)*iScreenScale) +"px";
		oLine2.style.width = Math.round(9*iScreenScale) +"px";
		oLine2.style.height = Math.round(0.4*iScreenScale) +"px";
		oLine2.style.backgroundColor = "#99C";
		oDivHelp.appendChild(oLine2);

		var oNext = document.createElement("input");
		oNext.type = "button";
		oNext.style.position = "absolute";
		oNext.style.left = (iScreenScale*8) +"px";
		oNext.style.bottom = (iScreenScale*5) +"px";
		oNext.style.fontSize = (iScreenScale*3) +"px";
		oNext.value = "Ok \u2192";
		oNext.onclick = function() {
			while (oDivHelp.childNodes.length)
				oDivHelp.removeChild(oDivHelp.firstChild);

			var oLine1 = document.createElement("div");
			oLine1.style.position = "absolute";
			oLine1.style.left = (29*iScreenScale) +"px";
			oLine1.style.top = (12*iScreenScale) +"px";
			oLine1.style.width = Math.round(0.5*iScreenScale) +"px";
			oLine1.style.height = (3*iScreenScale) +"px";
			oLine1.style.backgroundColor = "#99C";
			oDivHelp.appendChild(oLine1);

			var oLine2 = document.createElement("div");
			oLine2.style.position = "absolute";
			oLine2.style.left = (41*iScreenScale) +"px";
			oLine2.style.top = (17*iScreenScale) +"px";
			oLine2.style.width = Math.round(0.5*iScreenScale) +"px";
			oLine2.style.height = (3*iScreenScale) +"px";
			oLine2.style.backgroundColor = "#99C";
			oDivHelp.appendChild(oLine2);

			var oLine3 = document.createElement("div");
			oLine3.style.position = "absolute";
			oLine3.style.left = (53*iScreenScale) +"px";
			oLine3.style.top = (12*iScreenScale) +"px";
			oLine3.style.width = Math.round(0.5*iScreenScale) +"px";
			oLine3.style.height = (3*iScreenScale) +"px";
			oLine3.style.backgroundColor = "#99C";
			oDivHelp.appendChild(oLine3);

			var oLine4 = document.createElement("div");
			oLine4.style.position = "absolute";
			oLine4.style.left = (68*iScreenScale) +"px";
			oLine4.style.top = (17*iScreenScale) +"px";
			oLine4.style.width = Math.round(0.5*iScreenScale) +"px";
			oLine4.style.height = (3*iScreenScale) +"px";
			oLine4.style.backgroundColor = "#99C";
			oDivHelp.appendChild(oLine4);

			var oPInput = document.createElement("input");
			oPInput.type = "button";
			oPInput.value = "Circuit";
			oPInput.style.fontSize = Math.round(2.5*iScreenScale)+"px";
			oPInput.style.position = "absolute";
			oPInput.style.left = (24*iScreenScale)+"px";
			oPInput.style.top = (14*iScreenScale)+"px";
			oPInput.style.width = (10*iScreenScale)+"px";
			oPInput.style.backgroundColor = "#372F1A";
			oPInput.style.color = "#F6DA14";
			oDivHelp.appendChild(oPInput);

			var oPInput = document.createElement("input");
			oPInput.type = "button";
			oPInput.value = toLanguage("Arena", "Arène");
			oPInput.style.fontSize = Math.round(2.5*iScreenScale)+"px";
			oPInput.style.position = "absolute";
			oPInput.style.left = (36*iScreenScale)+"px";
			oPInput.style.top = (14*iScreenScale)+"px";
			oPInput.style.width = (10*iScreenScale)+"px";
			oPInput.style.backgroundColor = "#372F1A";
			oPInput.style.color = "#F6DA14";
			oDivHelp.appendChild(oPInput);

			var oPInput = document.createElement("input");
			oPInput.type = "button";
			oPInput.value = toLanguage("Cup", "Coupe");
			oPInput.style.fontSize = Math.round(2.5*iScreenScale)+"px";
			oPInput.style.position = "absolute";
			oPInput.style.left = (48*iScreenScale)+"px";
			oPInput.style.top = (14*iScreenScale)+"px";
			oPInput.style.width = (10*iScreenScale)+"px";
			oPInput.style.backgroundColor = "#372F1A";
			oPInput.style.color = "#F6DA14";
			oDivHelp.appendChild(oPInput);

			var oPInput = document.createElement("input");
			oPInput.type = "button";
			oPInput.value = toLanguage("Multi Cup", "Multicoupe");
			oPInput.style.fontSize = Math.round(2.5*iScreenScale)+"px";
			oPInput.style.position = "absolute";
			oPInput.style.left = (60*iScreenScale)+"px";
			oPInput.style.top = (14*iScreenScale)+"px";
			oPInput.style.width = (16*iScreenScale)+"px";
			oPInput.style.backgroundColor = "#372F1A";
			oPInput.style.color = "#F6DA14";
			oDivHelp.appendChild(oPInput);

			var oMask = document.createElement("div");
			oMask.style.position = "absolute";
			oMask.style.left = (24*iScreenScale)+"px";
			oMask.style.top = (14*iScreenScale)+"px";
			oMask.style.width = (52*iScreenScale)+"px";
			oMask.style.height = (4*iScreenScale)+"px";
			oDivHelp.appendChild(oMask);

			var oDiv1 = document.createElement("div");
			oDiv1.style.position = "absolute";
			oDiv1.style.left = (12*iScreenScale)+"px";
			oDiv1.style.bottom = ((iHeight-12)*iScreenScale)+"px";
			oDiv1.style.width = (20*iScreenScale)+"px";
			oDiv1.style.padding = Math.round(0.5*iScreenScale) +"px " + iScreenScale +"px";
			oDiv1.style.backgroundColor = "#CCC";
			oDiv1.style.color = "#333";
			oDiv1.style.fontSize = (iScreenScale*2) +"px";
			if (language)
				oDiv1.innerHTML = '<strong>Circuit:</strong> Create a track and play against your opponents in <strong style="color:#393">VS mode</strong>';
			else
				oDiv1.innerHTML = '<strong>Circuit :</strong> Créez une piste et affrontez vos adversaires en <strong style="color:#393">course VS</strong>';
			oDivHelp.appendChild(oDiv1);

			var oDiv2 = document.createElement("div");
			oDiv2.style.position = "absolute";
			oDiv2.style.left = (28*iScreenScale)+"px";
			oDiv2.style.top = (20*iScreenScale)+"px";
			oDiv2.style.width = (20*iScreenScale)+"px";
			oDiv2.style.padding = Math.round(0.5*iScreenScale) +"px " + iScreenScale +"px";
			oDiv2.style.backgroundColor = "#CCC";
			oDiv2.style.color = "#333";
			oDiv2.style.fontSize = (iScreenScale*2) +"px";
			if (language)
				oDiv2.innerHTML = '<strong>Arena:</strong> Create a battle course and play in mode <strong style="color:#393">Balloon battle</strong>';
			else
				oDiv2.innerHTML = '<strong>Arène :</strong> Créez une zone de combat et jouez en mode <strong style="color:#393">bataille de ballons</strong>';
			oDivHelp.appendChild(oDiv2);

			var oDiv3 = document.createElement("div");
			oDiv3.style.position = "absolute";
			oDiv3.style.left = (44*iScreenScale)+"px";
			oDiv3.style.bottom = ((iHeight-12)*iScreenScale)+"px";
			oDiv3.style.width = (20*iScreenScale)+"px";
			oDiv3.style.padding = Math.round(0.5*iScreenScale) +"px " + iScreenScale +"px";
			oDiv3.style.backgroundColor = "#CCC";
			oDiv3.style.color = "#333";
			oDiv3.style.fontSize = (iScreenScale*2) +"px";
			if (language)
				oDiv3.innerHTML = '<strong>Cup:</strong> Create a <strong style="color:#393">Grand Prix</strong> cup from 4 of your circuits';
			else
				oDiv3.innerHTML = '<strong>Coupe :</strong> Créer une coupe <strong style="color:#393">Grand Prix</strong> à partir de 4 de vos circuits';
			oDivHelp.appendChild(oDiv3);

			var oDiv4 = document.createElement("div");
			oDiv4.style.position = "absolute";
			oDiv4.style.left = (58*iScreenScale)+"px";
			oDiv4.style.top = (20*iScreenScale)+"px";
			oDiv4.style.width = (20*iScreenScale)+"px";
			oDiv4.style.padding = Math.round(0.5*iScreenScale) +"px " + iScreenScale +"px";
			oDiv4.style.backgroundColor = "#CCC";
			oDiv4.style.color = "#333";
			oDiv4.style.fontSize = (iScreenScale*2) +"px";
			if (language)
				oDiv4.innerHTML = '<strong>Multicup:</strong> Merge <strong style="color:#393">several cups</strong> in a same page to form a series!';
			else
				oDiv4.innerHTML = '<strong>Multicoupe :</strong> Réunissez <strong style="color:#393">plusieurs coupes</strong> sur une seule page pour former une série !';
			oDivHelp.appendChild(oDiv4);

			var oNext = document.createElement("input");
			oNext.type = "button";
			oNext.style.position = "absolute";
			oNext.style.left = (iScreenScale*8) +"px";
			oNext.style.bottom = (iScreenScale*5) +"px";
			oNext.style.fontSize = (iScreenScale*3) +"px";
			oNext.value = "Ok \u2713";
			oNext.onclick = function() {
				oScr.removeChild(oDivHelp);
			};

			oDivHelp.appendChild(oNext);
		};
		oDivHelp.appendChild(oNext);

		oScr.appendChild(oDivHelp);

		return false;
	}
	oScr.appendChild(oLink);
	
	var oLink = document.createElement("a");
	oLink.style.color = "#CCF";
	oLink.style.fontSize = Math.round(2.5*iScreenScale)+"px";
	oLink.style.position = "absolute";
	oLink.style.left = (34*iScreenScale)+"px";
	oLink.style.top = Math.round(29.5*iScreenScale)+"px";
	oLink.href = "creations.php";
	oLink.innerHTML = toLanguage("List of creations", "Liste des créations");
	oScr.appendChild(oLink);

	oContainers[0].appendChild(oScr);
	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Back", "Retour");
	oPInput.style.fontSize = (2*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (2*iScreenScale)+"px";
	oPInput.style.top = (35*iScreenScale)+"px";
	oPInput.onclick = function() {
		oScr.innerHTML = "";
		oContainers[0].removeChild(oScr);
		selectTypeScreen();
	}

	oScr.appendChild(oPInput);

	updateMenuMusic(0);
}

var myPersosCache;
function selectPlayerScreen(IdJ,newP,nbSels) {
	var isCustomSel = (nbSels !== undefined);
	if (!IdJ) {
		strPlayer = [];
		aPlayers = [];
		for (joueurs in cp)
			aPlayers.push(joueurs);
		updateCommandSheet();
	}
	if (!fInfos)
		fInfos = {};

	var oScr = document.createElement("div");
	if (newP)
		oScr.style.visibility = "hidden";

	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";

	var oTitle;
	if (isCustomSel) {
		var oMsg;
		if (IdJ >= oContainers.length)
			oMsg = toLanguage("Choose CPU", "Choisissez ordi") + " "+ (IdJ+1-oContainers.length);
		else if (oContainers.length == 1)
			oMsg = toLanguage("Choose player", "Choisissez joueur");
		else
			oMsg = toLanguage("Choose player ", "Choisissez joueur ") + (IdJ+1);
		oTitle = toTitle(oMsg, 0);
		oTitle.style.color = "#F90";
	}
	else {
		oTitle = toTitle(toLanguage("Choose a player", "Choisissez un joueur"), 0);
	}
	oScr.appendChild(oTitle);

	vitesse = 15*iScreenScale;
	
	var cTable = document.createElement("table");
	cTable.style.display = "none";
	cTable.style.position = "absolute";
	cTable.style.top = (36*iScreenScale+16)+"px";
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

	var rotateHandler;
	function tourner(kart) {
		console.log(0);
		var rotation = parseFloat(kart.style.left);
		var size = iScreenScale*5;
		if (rotation > -21*size)
			kart.style.left = rotation - size +"px";
		else
			kart.style.left = "0px";
		rotateHandler = setTimeout(function() {
			tourner(kart);
		}, 100);
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

		var cDiv = document.createElement("div");
		cDiv.style.position = "absolute";
		cDiv.style.display = "inline-block";
		cDiv.style.width = (5 * iScreenScale) + "px";
		cDiv.style.height = (5 * iScreenScale) + "px";
		cDiv.style.overflow = "hidden";

		var oPImg = new Image();
		oPImg.style.height = (5 * iScreenScale) +"px";
		oPImg.style.position = "absolute";
		oPImg.className = "pixelated";
		if (pUnlocked[i]) {
			var libre = true;
			var oJoueur = aPlayers[i];
			for (var j=0;j<strPlayer.length;j++) {
				if (strPlayer[j] == oJoueur) {
					libre = false;
					j = strPlayer.length;
				}
			}
			oPImg.src = (libre ? getSpriteSrc(oJoueur):getStarSrc(oJoueur));
			oPImg.alt = aPlayers[i];
			oPImg.nb = i;
			oPImg.style.left = -(30 * iScreenScale) +"px";
			oPImg.style.cursor = "pointer";
			oPImg.id = "perso-selector-"+oJoueur;
			oPImg.j = IdJ;
			oPImg.onload = function() {
				var cHeight = Math.min(5*this.naturalHeight/32,6);
				cDiv.style.height = Math.round(cHeight*iScreenScale)+"px";
				oPImg.style.height = Math.round(5*this.naturalHeight/32*iScreenScale)+"px";
				oPImg.style.top = Math.round((cHeight-5*this.naturalHeight/32)*iScreenScale/2)+"px";
				cDiv.style.top = Math.round(1+(5-cHeight)/2*iScreenScale)+"px";
			}
			oPImg.onmouseover = function() {
				cTable.style.display = "block";
				hTd2.innerHTML = toPerso(this.alt);
				var coeffs = [2.5,5,2.5], consts = [0.2,0.8,0.2];
				for (var i=0;i<dCaracteristiques.length;i++)
					dCaracteristiques[i].style.width = vitesse*((cp[this.alt][i]-consts[i])*coeffs[i]+1)+"px";
				clearTimeout(rotateHandler);
				tourner(this);
			}
			oPImg.onmouseout = function() {
				cTable.style.display = "none";
				this.style.left = -(30 * iScreenScale) +"px";
				clearTimeout(rotateHandler);
			}
			oPImg.onclick = function() {
				clearTimeout(rotateHandler);
				strPlayer[this.j] = this.alt;
				var newOptions = ""; // will be used later
				if (!isOnline) {
					if (course == "VS") {
						iDificulty = 4+selectedDifficulty*0.5;
						newOptions = "difficulty="+ selectedDifficulty +"&players="+ fInfos.nbPlayers +"&team="+ fInfos.teams;
					}
					else
						newOptions = "players="+ fInfos.nbPlayers +"&team="+ fInfos.teams;
				}
				oScr.innerHTML = "";
				this.j++;
				oContainers[0].removeChild(oScr);
				document.body.removeChild(cTable);
				if (this.j == (isCustomSel ? nbSels:oContainers.length)) {
					if (isOnline)
						aPlayers = [strPlayer[0]];
					else if (course == "CM")
						aPlayers = [];
					else {
						aPlayers = [];
						if (isCustomSel) {
							for (var i=strPlayer.length-1;i>=oContainers.length;i--)
								aPlayers.push(strPlayer[i]);
							strPlayer.splice(oContainers.length);
						}
						else {
							var i = 0;
							for (joueurs in cp) {
								if (pUnlocked[i]) {
									aPlayers.push(joueurs);
									i++;
								}
							}
							aPlayers.sort(function(){return 0.5-Math.random()});
							if (aPlayers.length < fInfos.nbPlayers) {
								var aLength = aPlayers.length;
								aPlayers.length = aPlayers.length*Math.ceil(fInfos.nbPlayers/aPlayers.length);
								for (var i=aLength;i<aPlayers.length;i++)
									aPlayers[i] = aPlayers[i%aLength];
							}
							else {
								for (var i=0;i<aPlayers.length;i++) {
									var joueur = aPlayers[i];
									for (var j=0;j<strPlayer.length;j++) {
										if (strPlayer[j] == joueur) {
											aPlayers.splice(i,1);
											i--;
											j = strPlayer.length;
										}
									}
								}
							}
							var oSuppr = (course!="GP") ? aPlayers.length-fInfos.nbPlayers+strPlayer.length:aPlayers.length-7;
							aPlayers.splice(0,oSuppr);
						}
						aPlaces = [];
						for (var i=0;i<strPlayer.length;i++)
							aPlaces[i] = aPlayers.length+i+1;
						for (var i=0;i<aPlayers.length;i++)
							aPlaces[i+strPlayer.length] = i+1;
						for (var i=0;i<aPlaces.length;i++)
							aScores[i] = 0;
						clRuleVars = {};
						clGlobalVars = undefined;
						if (course != "GP") {
							selectedPlayers = fInfos.nbPlayers;
							selectedTeams = fInfos.teams;
							xhr("updateCourseOptions.php", newOptions, function(reponse) {
								return (reponse == 1);
							});
						}
					}
					if (isOnline) {
						if (isCustomOptions(shareLink.options) && !shareLink.accepted && (shareLink.player != identifiant))
							acceptRulesScreen();
						else
							searchCourse();
					}
					else {
						if (isTeamPlay())
							selectTeamScreen(0);
						else
							selectTrackScreen();
					}
				}
				else
					selectPlayerScreen(this.j,undefined,nbSels);
				var cpId = /^cp-\w+-(\d+)$/g.exec(this.alt);
				if (cpId)
					xhr("selectPerso.php", "id="+cpId[1], function(){return true});
			}
		}
		else
			oPImg.src = "images/kart_locked.png";
		cDiv.appendChild(oPImg);
		oDiv.appendChild(cDiv);
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
	pPImg.className = "pixelated";
	pPImg.onclick = function() {
		window.open('choosePerso.php','chose','scrollbars=1, resizable=1, width=500, height=500');
	}

	pDiv.appendChild(pPImg);
	oScr.appendChild(pDiv);

	oContainers[0].appendChild(oScr);
	
	if (isOnline) {
		if (shareLink.key) {
			if (shareLink.player == identifiant) {
				var oPInput = document.createElement("input");
				oPInput.type = "button";
				oPInput.value = toLanguage("Private game options...", "Options de la partie privée...");
				oPInput.style.fontSize = (2*iScreenScale)+"px";
				oPInput.style.position = "absolute";
				oPInput.style.left = (52*iScreenScale)+"px";
				oPInput.style.top = (35*iScreenScale)+"px";
				oPInput.onclick = function() {
					oScr.innerHTML = "";
					oContainers[0].removeChild(oScr);
					privateGameOptions(shareLink.options, function(options) {
						if (options && (isCustomOptions(options)||isCustomOptions(shareLink.options))) {
							xhr("privateGameOptions.php", "key="+shareLink.key+"&options="+encodeURIComponent(JSON.stringify(options)), function(res) {
								if (res != 1) return false;
								if (!shareLink.options) shareLink.options = {};
								shareLink.options.team = options.team;
								shareLink.options.manualTeams = options.manualTeams;
								shareLink.options.friendly = options.friendly;
								selectedTeams = options.team;
								selectPlayerScreen(0);
								return true;
							});
						}
						else
							selectPlayerScreen(0);
					});
				}
				oScr.appendChild(oPInput);
			}
		}
		else {
			var oPInput = document.createElement("input");
			oPInput.type = "button";
			oPInput.value = toLanguage("Private game...", "Partie privée...");
			oPInput.style.fontSize = (2*iScreenScale)+"px";
			oPInput.style.position = "absolute";
			oPInput.style.left = (62*iScreenScale)+"px";
			oPInput.style.top = (35*iScreenScale)+"px";
			oPInput.onclick = function() {
				oScr.innerHTML = "";
				oContainers[0].removeChild(oScr);
				privateGame();
			}
			oScr.appendChild(oPInput);
		}
	}
	if (!isOnline && (course == "VS" || course == "BB")) {
		var oForm = document.createElement("form");
		oForm.onsubmit = function(){return false};
		oForm.style.position = "absolute";
		oForm.style.top = (32*iScreenScale-5) +"px";
		oForm.style.left = (18*iScreenScale) +"px";
		oForm.style.fontSize = (2*iScreenScale) +"px";
		oForm.style.zIndex = 2;
		if (!IdJ && !newP) {
			iDificulty = selectedDifficulty;
			fInfos.nbPlayers = selectedPlayers;
			fInfos.teams = selectedTeams;
		}
		if (course == "VS") {
			oForm.appendChild(document.createTextNode(toLanguage("Difficulty: ", "Difficulté : ")));
			var iDifficulties = [toLanguage("Easy", "Facile"), toLanguage("Medium", "Moyen"), toLanguage("Difficult", "Difficile")];
			var oSelect = document.createElement("select");
			oSelect.name = "difficulty";
			oSelect.style.width = "auto";
			oSelect.style.fontSize = iScreenScale*2 +"px";
			oSelect.style.marginRight = "10px";
			oSelect.onchange = function() {
				selectedDifficulty = this.selectedIndex;
			};
			for (var i=0;i<iDifficulties.length;i++) {
				var oOption = document.createElement("option");
				oOption.value = i;
				oOption.innerHTML = iDifficulties[i];
				if (selectedDifficulty == i)
					oOption.selected = "selected";
				oSelect.appendChild(oOption);
			}
			oForm.appendChild(oSelect);
		}
		else
			iDificulty = 4.5;
		if ((course == "VS") || (course == "BB")) {
			oForm.appendChild(document.createTextNode(toLanguage("Teams: ", "Équipes : ")));
			var oSelect = document.createElement("select");
			oSelect.name = "difficulty";
			oSelect.style.width = (iScreenScale*15+15) +"px";
			oSelect.style.fontSize = iScreenScale*2 +"px";
			oSelect.onchange = function() {
				fInfos.teams = this.selectedIndex;
			};
			var iTeams = [toLanguage("No teams", "Chacun pour soi"), toLanguage("Team Game", "Match par équipes")];
			for (var i=0;i<iTeams.length;i++) {
				var oOption = document.createElement("option");
				oOption.value = i;
				oOption.innerHTML = iTeams[i];
				if (selectedTeams == i)
					oOption.selected = "selected";
				oSelect.appendChild(oOption);
			}
			oForm.appendChild(oSelect);
		}
		oForm.appendChild(document.createElement("br"));
		oForm.appendChild(document.createTextNode(toLanguage("Number of participants", "Nombre de participants ")+ ": "));
		var oSelect = document.createElement("select");
		oSelect.name = "nbj";
		oSelect.style.width = (iScreenScale*3+20) +"px";
		oSelect.style.fontSize = iScreenScale*2 +"px";
		function setCustomValue(oSelect, customNb) {
			var oOptions = oSelect.getElementsByTagName("option");
			for (var i=0;i<oOptions.length;i++) {
				var oValue = +oOptions[i].value;
				if (oValue == customNb) {
					oSelect.selectedIndex = i;
					break;
				}
				else if ((oValue == -1) || (oValue > customNb)) {
					var oOption = document.createElement("option");
					oOption.value = customNb;
					oOption.innerHTML = customNb;
					oSelect.insertBefore(oOption, oOptions[i]);
					oSelect.selectedIndex = i;
					break;
				}
			}
			oSelect.value = customNb;
		}
		for (var i=2;i<=8;i++) {
			var oOption = document.createElement("option");
			oOption.value = i;
			oOption.innerHTML = i;
			oSelect.appendChild(oOption);
		}
		var oOption = document.createElement("option");
		oOption.value = -1;
		oOption.innerHTML = toLanguage("More...","Plus...");
		oSelect.appendChild(oOption);
		setCustomValue(oSelect,fInfos.nbPlayers);
		oSelect.onchange = function() {
			if (this.value == -1) {
				var customNb = parseInt(prompt(toLanguage("Enter number", "Nombre de joueurs :")));
				if (!isNaN(customNb) && (customNb > 1))
					setCustomValue(this,Math.min(customNb,999));
				else {
					if (!isNaN(customNb))
						alert(toLanguage("Invalid value", "Valeur invalide"));
					setCustomValue(this,fInfos.nbPlayers);
				}
			}
			fInfos.nbPlayers = parseInt(this.value);
		};
		oForm.appendChild(oSelect);
		var oChoosePerso = document.createElement("a");
		oChoosePerso.innerHTML = toLanguage("Choose characters...", "Choix des persos...");
		oChoosePerso.href = "#null";
		oChoosePerso.style.display = "inline-block";
		oChoosePerso.style.marginLeft = (iScreenScale*2) +"px";
		oChoosePerso.style.color = "white";
		oChoosePerso.onclick = function() {
			selectedDifficulty = iDificulty;
			selectedPlayers = fInfos.nbPlayers;
			selectedTeams = fInfos.teams;
			clearTimeout(rotateHandler);
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			selectPlayerScreen(IdJ,false,fInfos.nbPlayers);
			return false;
		};
		oForm.appendChild(oChoosePerso);
		oScr.appendChild(oForm);
		if (isCustomSel) {
			oForm.style.display = "none";
			var oStepCtn = document.createElement("div");
			oStepCtn.style.position = "absolute";
			oStepCtn.style.left = "0px";
			oStepCtn.style.width = (iWidth*iScreenScale) +"px";
			oStepCtn.style.textAlign = "center";
			oStepCtn.style.top = (32*iScreenScale) +"px";
			var oStepBack = document.createElement("input");
			oStepBack.type = "button";
			oStepBack.style.fontSize = (3*iScreenScale) + "px";
			if (IdJ) {
				oStepBack.value = "\u25C4";
				oStepBack.style.marginRight = (3*iScreenScale) + "px";
			}
			else {
				oStepBack.style.width = "0px";
				oStepBack.value = "\xA0";
				oStepBack.style.visibility = "hidden";
			}
			oStepBack.onclick = function() {
				clearTimeout(rotateHandler);
				oScr.innerHTML = "";
				oContainers[0].removeChild(oScr);
				strPlayer.pop();
				selectPlayerScreen(IdJ-1,false,fInfos.nbPlayers);
			};
			oStepCtn.appendChild(oStepBack);
			var oStepValue = document.createElement("span");
			oStepValue.style.fontSize = (3*iScreenScale) + "px";
			oStepValue.innerHTML = toLanguage("Character", "Perso") +" "+ (IdJ+1) +"/"+ nbSels;
			oStepCtn.appendChild(oStepValue);
			oStepBack.style.color = oStepValue.style.color = "#F90";
			oScr.appendChild(oStepCtn);
		}
	}
	else if (course == "CM" && isSingle) {
		var oClassement = document.createElement("input");
		oClassement.type = "button";
		oClassement.value = toLanguage("Rankings", "Classement");
		oClassement.style.position = "absolute";
		oClassement.style.fontSize = (3*iScreenScale)+"px";
		oClassement.style.position = "absolute";
		oClassement.style.left = (30*iScreenScale-10)+"px";
		oClassement.style.top = (32*iScreenScale)+"px";
		oClassement.style.width = (20*iScreenScale)+"px";
		oClassement.onclick = openRankings;
		oScr.appendChild(oClassement);
	}
	
	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Back", "Retour");
	oPInput.style.fontSize = (2*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (2*iScreenScale)+"px";
	oPInput.style.top = (35*iScreenScale)+"px";
	oPInput.onclick = function() {
		oScr.innerHTML = "";
		oContainers[0].removeChild(oScr);
		document.body.removeChild(cTable);
		if (isCustomSel)
			selectPlayerScreen(0);
		else {
			displayCommands("&nbsp;");
			if (isOnline)
				connexion();
			else if (course == "VS" || course == "BB") {
				for (var i=1;i<oContainers.length;i++)
					oContainers.splice(i,1);
				selectNbJoueurs();
			}
			else
				selectTypeScreen();
		}
	}
	if (isCustomSel)
		oPInput.style.color = "#F90";
	oScr.appendChild(oPInput);

	function addMyPersos(newPersos) {
		cp = {};
		for (var joueurs in baseCp)
			cp[joueurs] = baseCp[joueurs];
		customPersos = {};
		for (var i=0;i<newPersos.length;i++) {
			var newPerso = newPersos[i];
			cp[newPerso["sprites"]] = [newPerso["acceleration"],newPerso["speed"],newPerso["handling"]];
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
				return;
			}
			oDiv.style.left = 67*iScreenScale +"px";
			oDiv.style.top = ((10+i*7)*iScreenScale)+"px";
			oScr.insertBefore(oDiv,pDiv);
		}
		oScr.style.visibility = "visible";
	}

	if (newP)
		myPersosCache = undefined;
	if (myPersosCache)
		addMyPersos(myPersosCache);
	else {
		xhr("myPersos.php", null, function(res) {
			if (oScr.dataset && oScr.dataset.bypass)
				return true;
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
			addMyPersos(newPersos);
			myPersosCache = newPersos;
			return true;
		});
	}

	selectPerso = function(persoId) {
		clearTimeout(rotateHandler);
		oScr.innerHTML = "";
		oContainers[0].removeChild(oScr);
		xhr("selectPerso.php", "id="+persoId, function(res) {
			selectPlayerScreen(IdJ,true,nbSels);
			return true;
		});
	};

	updateMenuMusic(isOnline ? 0:1);
}
function isCustomOptions(linkOptions) {
	return linkOptions && (linkOptions.team||linkOptions.manualTeams||linkOptions.friendly);
}
function hasChallenges() {
	for (var type in challenges) {
		for (var cid in challenges[type])
			return true;
	}
}
function isTeamPlay() {
	switch (course) {
		case "BB":
		case "VS":
			return selectedTeams;
	}
	return 0;
}

function selectTeamScreen(IdJ) {
	if (!IdJ)
		aTeams.length = 0;

	var oScr = document.createElement("div");
	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";

	var sTitle = toLanguage("Select team", "Sélectionner équipe");
	if (strPlayer.length > 1)
		sTitle += " ("+ toLanguage("P","J") + (IdJ+1) +")";
	var oTitle = toTitle(sTitle, 0.5);
	if (strPlayer.length > 1)
		oTitle.style.fontSize = Math.round(7*iScreenScale)+"px";
	oScr.appendChild(oTitle);

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Back", "Retour");
	oPInput.style.fontSize = (2*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (2*iScreenScale)+"px";
	oPInput.style.top = (35*iScreenScale)+"px";
	oPInput.onclick = function() {
		oScr.innerHTML = "";
		oContainers[0].removeChild(oScr);
		selectPlayerScreen(0);
	}
	oScr.appendChild(oPInput);

	for (i=0;i<2;i++) {
		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = i ? toLanguage("Red team","Équipe rouge"):toLanguage("Blue team","Équipe bleue");
		oPInput.i = i;
		oPInput.style.fontSize = (4*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (25*iScreenScale)+"px";
		oPInput.style.top = ((16+i*9)*iScreenScale)+"px";
		oPInput.style.width = (30*iScreenScale)+"px";

		oPInput.onclick = function() {
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			var i = +this.i;
			aTeams.push(i);
			if (aTeams.length >= strPlayer.length) {
				for (var k=0;k<strPlayer.length;k++)
					aTeams.push(1-aTeams[k]);
				for (var k=strPlayer.length;k<aPlayers.length;k++)
					aTeams.push((k+i+aPlayers.length)%2);
				selectTrackScreen();
			}
			else
				selectTeamScreen(IdJ+1);
		};
		oScr.appendChild(oPInput);
	}
	oContainers[0].appendChild(oScr);

	updateMenuMusic(1);
}
function selectTrackScreen() {
	if (course != "BB")
		selectMapScreen();
	else {
		if (page == "MK")
			selectMapScreen();
		else
			selectRaceScreen(0);
	}
}
function selectGamersScreen() {	
	if (!isOnline && isTeamPlay())
		selectTeamScreen(0);
	else
		selectPlayerScreen(0);
}

function acceptRulesScreen() {
	var oScr = document.createElement("div");

	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";

	var oTitle;
	if (shareLink.options["public"])
		oTitle = toTitle(toLanguage("Game rules", "Règles parties"), 0);
	else
		oTitle = toTitle(toLanguage("Private game rules", "Règles partie privée"), 0);
	oTitle.style.fontSize = (7*iScreenScale)+"px";
	oScr.appendChild(oTitle);

	var oForm = document.createElement("div");
	oForm.style.position = "absolute";
	oForm.style.left = "0px";
	oForm.style.top = (9*iScreenScale) +"px";
	oForm.style.width = (iWidth*iScreenScale) +"px";

	var oScroll = document.createElement("div");
	oScroll.style.maxHeight = (24*iScreenScale) +"px";
	oScroll.style.overflow = "auto";

	var oDiv = document.createElement("div");
	oDiv.style.textAlign = "center";
	oDiv.style.color = "#F90";
	oDiv.style.fontSize = (iScreenScale*2) +"px";
	oDiv.style.lineHeight = (iScreenScale*3) +"px";
	if (shareLink.options["public"])
		oDiv.innerHTML = "\u26A0 " + toLanguage("Games from this mode have special rules", "Les parties de ce mode utilisent des règles spécifiques");
	else
		oDiv.innerHTML = "\u26A0 " + toLanguage("Games from this private link have special rules", "Les parties de ce lien privé utilisent des règles spécifiques");
	oScroll.appendChild(oDiv);

	var oTable = document.createElement("table");
	oTable.style.marginLeft = "auto";
	oTable.style.marginRight = "auto";

	if (shareLink.options.team) {
		var oTr = document.createElement("tr");
		var oTd = document.createElement("td");
		var oLabel = document.createElement("label");
		oLabel.setAttribute("for", "option-teams");
		var oH1 = document.createElement("h1");
		oH1.style.fontSize = (3*iScreenScale) +"px";
		oH1.style.marginTop = "0px";
		oH1.style.marginBottom = "0px";
		oH1.innerHTML = toLanguage("Team games","Parties par équipe");
		oLabel.appendChild(oH1);
		var oDiv = document.createElement("div");
		oDiv.style.fontSize = (2*iScreenScale) +"px";
		oDiv.style.color = "white";
		oDiv.innerHTML = toLanguage("2 teams are selected in each game. You object: defeat the opposing team.", "2 équipes sont sélectionnées à chaque partie. Votre objectif : vaincre l'équipe adverse.");
		oLabel.appendChild(oDiv);
		oTd.appendChild(oLabel);
		oTr.appendChild(oTd);
		oTable.appendChild(oTr);
	}

	if (shareLink.options.manualTeams) {
		var oTr = document.createElement("tr");
		var oTd = document.createElement("td");
		var oLabel = document.createElement("label");
		oLabel.setAttribute("for", "option-friendly");
		oTd.appendChild(oLabel);

		var oH1 = document.createElement("h1");
		oH1.style.fontSize = (3*iScreenScale) +"px";
		oH1.innerHTML = toLanguage("Manual selection", "Sélection manuelle");
		oH1.style.marginBottom = "0px";
		oLabel.appendChild(oH1);
		var oDiv = document.createElement("div");
		oDiv.style.fontSize = (2*iScreenScale) +"px";
		oDiv.style.color = "white";
		oDiv.innerHTML = toLanguage("Teams are selected manually by one of the players.", "Les équipes sont sélectionnées manuellement par l'un des joueurs.");
		oLabel.appendChild(oDiv);
		oTd.appendChild(oLabel);
		oTr.appendChild(oTd);
		oTable.appendChild(oTr);
	}

	if (shareLink.options.friendly) {
		var oTr = document.createElement("tr");
		var oTd = document.createElement("td");
		var oLabel = document.createElement("label");
		oLabel.setAttribute("for", "option-friendly");
		oTd.appendChild(oLabel);

		var oH1 = document.createElement("h1");
		oH1.style.fontSize = (3*iScreenScale) +"px";
		oH1.innerHTML = toLanguage("Friendly game", "Matchs amicaux");
		oH1.style.marginBottom = "0px";
		oLabel.appendChild(oH1);
		var oDiv = document.createElement("div");
		oDiv.style.fontSize = (2*iScreenScale) +"px";
		oDiv.style.color = "white";
		oDiv.innerHTML = toLanguage("Games won't make you win or lose points in the online mode.", "Les parties ne vous feront pas gagner ou perdre de points dans le mode en ligne.");
		oLabel.appendChild(oDiv);
		oTd.appendChild(oLabel);
		oTr.appendChild(oTd);
		oTable.appendChild(oTr);
	}

	oScroll.appendChild(oTable);

	oForm.appendChild(oScroll);

	var oDiv = document.createElement("div");
	oDiv.style.textAlign = "center";
	oDiv.style.marginTop = (2*iScreenScale)+"px";
	var oSubmit = document.createElement("input");
	oSubmit.type = "button";
	oSubmit.value = toLanguage("Accept and play", "Accepter et jouer");
	oSubmit.style.fontSize = (3*iScreenScale)+"px";
	oSubmit.onclick = function() {
		oScr.innerHTML = "";
		oContainers[0].removeChild(oScr);
		shareLink.accepted = true;
		searchCourse();
	}
	oDiv.appendChild(oSubmit);
	oForm.appendChild(oDiv);

	oScr.appendChild(oForm);

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Back", "Retour");
	oPInput.style.fontSize = (2*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (2*iScreenScale)+"px";
	oPInput.style.top = (35*iScreenScale)+"px";
	oPInput.onclick = function() {
		oScr.innerHTML = "";
		oContainers[0].removeChild(oScr);
		selectPlayerScreen(0);
	}
	oScr.appendChild(oPInput);

	oContainers[0].appendChild(oScr);
}

function selectChallengesScreen() {
	var oScr = document.createElement("div");

	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";

	var oTitle = toTitle(toLanguage("Challenges", "Défis"), 0);
	oTitle.style.fontSize = (7*iScreenScale)+"px";
	oScr.appendChild(oTitle);

	var oForm = document.createElement("div");
	oForm.style.position = "absolute";
	oForm.style.left = "0px";
	oForm.style.top = (9*iScreenScale) +"px";
	oForm.style.width = (iWidth*iScreenScale) +"px";

	var oScroll = document.createElement("div");
	oScroll.style.maxHeight = (24*iScreenScale) +"px";
	oScroll.style.overflowX = "hidden";
	oScroll.style.overflowY = "auto";

	var aChallenge = hasChallenges();
	if (aChallenge) {
		if (document.getElementById("comment-connect")) {
			var oNoConnect = document.createElement("div");
			oNoConnect.style.width = ((iWidth-5)*iScreenScale) +"px";
			oNoConnect.style.marginLeft = "auto";
			oNoConnect.style.marginRight = "auto";
			oNoConnect.style.marginBottom = Math.round(iScreenScale*1.5) +"px";
			oNoConnect.style.textAlign = "center";
			oNoConnect.innerHTML = language ? 'You are not connected. The challenges you complete will not be saved. <a href="forum.php" target="_blank" style="color:white">Click here</a> to log in or register.' : 'Vous n\'êtes pas connecté. Les défis réussis ne seront pas sauvegardés. <a href="forum.php" target="_blank" style="color:white">Cliquez ici</a> pour vous connecter ou vous inscrire.';
			oNoConnect.style.fontSize = Math.round(iScreenScale*1.8) +"px";
			oScroll.appendChild(oNoConnect);
		}

		var oTable = document.createElement("table");
		oTable.style.width = ((iWidth-3)*iScreenScale) +"px";
		oTable.style.marginLeft = "auto";
		oTable.style.marginRight = "auto";
		oTable.style.borderCollapse = "collapse";

		for (var type in challenges) {
			for (var cid in challenges[type]) {
				var creationChallenges = challenges[type][cid];
				if (!creationChallenges.main) {
					var oTr = document.createElement("tr");
					oTr.style.border = "solid 1px white";
					var oTd = document.createElement("td");
					oTd.setAttribute("colspan", 2);
					var oH1 = document.createElement("h1");
					var trackType = "";
					switch (type) {
						case "mcup":
							trackType = toLanguage("Multicup", "Multicoupe");
							break;
						case "cup":
							trackType = toLanguage("Cup", "Coupe");
							break;
						case "track":
							trackType = toLanguage("Track", "Circuit");
							break;
					}
					oH1.innerHTML = trackType + ' <span style="color:#FDB">'+ creationChallenges.name +'</span>';
					oH1.style.textAlign = "center";
					oH1.style.margin = "0px";
					oH1.style.fontSize = Math.round(iScreenScale*4)+"px";
					oH1.style.paddingTop = Math.round(iScreenScale*0.5)+"px";
					oH1.style.paddingBottom = Math.round(iScreenScale*0.5)+"px";
					oH1.style.backgroundColor = "#fa7c1b";
					oH1.style.color = "white";
					oTd.appendChild(oH1);
					oTr.appendChild(oTd);
					oTable.appendChild(oTr);
				}
				var challengesList = creationChallenges.list;
				for (var i=0;i<challengesList.length;i++) {
					var challenge = challengesList[i];
					var challengeComplete = (challenge.status == "active" && challenge.succeeded);
					var challengeColor = challengeComplete ? "#9E9":"white";
					var oTr = document.createElement("tr");
					oTr.style.border = "solid 1px "+challengeColor;
					if (challengeComplete)
						oTr.style.backgroundColor = "#031";
					var oTd = document.createElement("td");
					oTd.style.padding = (iScreenScale)+" "+(iScreenScale) +"px";
					if (challenge.name) {
						var oH1 = document.createElement("h1");
						oH1.style.fontSize = (3*iScreenScale) +"px";
						oH1.style.marginTop = "0px";
						oH1.style.marginBottom = "0px";
						oH1.innerHTML = challenge.name;
						oTd.appendChild(oH1);
					}
					var oDiv = document.createElement("div");
					if (challenge.name || challenge.description.extra)
						oDiv.style.fontSize = (2*iScreenScale) +"px";
					else
						oDiv.style.fontSize = Math.round(2.5*iScreenScale) +"px";
					oDiv.style.color = challengeColor;
					oDiv.style.fontWeight = "bold";
					oDiv.innerHTML = challenge.description.main;
					oTd.appendChild(oDiv);
					if (challenge.description.extra) {
						var oDiv = document.createElement("div");
						oDiv.style.fontSize = Math.round(1.6*iScreenScale) +"px";
						oDiv.style.color = challengeColor;
						oDiv.innerHTML = challenge.description.extra;
						oTd.appendChild(oDiv);
					}
					if (challenge.status != "active") {
						var oDiv = document.createElement("div");
						oDiv.style.fontSize = Math.round(1.6*iScreenScale) +"px";
						oDiv.style.color = "#FC0";
						switch (challenge.status) {
						case 'pending_completion':
							oDiv.innerHTML = toLanguage('This challenge is pending completion. Succeed it to publish it.', 'Ce défi est en attente de réussite. Réussissez-le pour le publier.');
							break;
						case 'pending_publication':
							oDiv.innerHTML = toLanguage('This challenge is pending publication. Click on &quot;Manage challenges&quot; to publish it.', 'Ce défi est en attente de publication. Cliquez sur &quot;Gérer les défis&quot; pour le publier.');
							break;
						case 'pending_moderation':
							oDiv.innerHTML = toLanguage('This challenge is pending moderation. It will be published once a validator validates it.', 'Ce défi est en attente de modération. Il sera publié dès qu\'un modérateur l\'aura validé.');
							break;
						}
						oDiv.style.fontWeight = "bold";
						oTd.appendChild(oDiv);
					}
					oTr.appendChild(oTd);
					var oTd = document.createElement("td");
					oTd.style.padding = (iScreenScale)+" "+(iScreenScale) +"px";
					oTd.style.width = (iScreenScale*12) +"px";
					oTd.style.textAlign = "center";

					if (challenge.succeeded) {
						var oSuccess = document.createElement("div");
						oSuccess.innerHTML = '<span style="color:#CFC;display:inline-block;margin-right:2px">\u2714</span>'+ toLanguage("Completed","Réussi");
						oSuccess.style.whiteSpace = "nowrap";
						oSuccess.style.fontSize = Math.round(iScreenScale*(language ? 2:2.2)) +"px";
						oSuccess.style.backgroundColor = "#33A033";
						oSuccess.style.display = "inline-block";
						oSuccess.style.padding = "0px "+Math.round(iScreenScale*0.8)+"px";
						oSuccess.style.borderRadius = Math.round(iScreenScale*0.6)+"px";
						oSuccess.style.color = "white";
						oSuccess.style.marginBottom = Math.round(iScreenScale*0.5) +"px";
						oSuccess.style.marginTop = Math.round(iScreenScale*0.5) +"px";
						oTd.appendChild(oSuccess);
					}

					var oIcons = document.createElement("div");
					var oIconDifficulty = document.createElement("img");
					oIconDifficulty.src = "images/challenges/difficulty"+ challenge.difficulty.level +".png";
					oIconDifficulty.alt = "D";
					oIconDifficulty.style.width = (iScreenScale*2) +"px";
					oIcons.appendChild(oIconDifficulty);
					var oSpan = document.createElement("span");
					oSpan.style.color = challenge.difficulty.color;
					oSpan.style.fontSize = Math.round(iScreenScale*1.7) +"px";
					oSpan.style.position = "relative";
					oSpan.style.top = "-1px";
					oSpan.innerHTML = " "+ challenge.difficulty.name;
					oIcons.appendChild(oSpan);
					oTd.appendChild(oIcons);

					if (challenge.winners.length) {
						var oIcons = document.createElement("div");
						oIcons.style.cursor = "help";
						var oIconWinners = document.createElement("img");
						if (!challenge.succeeded)
							oIcons.style.marginBottom = Math.round(iScreenScale*0.5) +"px";
						oIconWinners.src = "images/cups/cup1.png";
						oIconWinners.alt = "W";
						oIconWinners.style.width = (iScreenScale*2) +"px";
						oIcons.appendChild(oIconWinners);
						var oSpan = document.createElement("span");
						oSpan.style.color = "white";
						oSpan.style.fontSize = Math.round(iScreenScale*1.7) +"px";
						oSpan.style.position = "relative";
						oSpan.style.top = "-2px";
						oSpan.innerHTML = " "+ challenge.winners.length;
						var iconTitle = '<small style="color:#CFC">'+toLanguage("Succeeded by:", "Réussi par :")+'</small>';
						for (var j=0;j<challenge.winners.length;j++)
							iconTitle += '<br /><span style="color:#CFC;display:inline-block;margin-right:2px">\u2714</span>'+challenge.winners[j].nick;
						if (!oIcons.dataset) oIcons.dataset = {};
						oIcons.dataset.title = iconTitle;
						oIcons.appendChild(oSpan);
						var $fancyTitle;
						oIcons.onmouseover = function(e) {
							if ($fancyTitle) return;
							$fancyTitle = document.createElement("div");
							$fancyTitle.className = "ranking_activeplayertitle";
							$fancyTitle.innerHTML = this.dataset.title;
							$fancyTitle.style.position = "absolute";
							$fancyTitle.style.padding = Math.round(iScreenScale/2)+"px "+iScreenScale+"px";
							$fancyTitle.style.borderRadius = iScreenScale+"px";
							$fancyTitle.style.zIndex = 10;
							$fancyTitle.style.backgroundColor = "rgba(51,160,51, 0.95)";
							$fancyTitle.style.color = "white";
							$fancyTitle.style.fontSize = Math.round(iScreenScale*1.8) +"px";
							$fancyTitle.style.lineHeight = Math.round(iScreenScale*2) +"px";
							$fancyTitle.style.visibility = "hidden";
							document.body.appendChild($fancyTitle);
							var rect = this.getBoundingClientRect();
							$fancyTitle.style.left = Math.round(rect.left + (this.scrollWidth-$fancyTitle.scrollWidth)/2)+"px";
							$fancyTitle.style.top = (rect.top + this.scrollHeight + 5)+"px";
							$fancyTitle.style.visibility = "visible";
						};
						oIcons.onmouseout = function(e) {
							if (!$fancyTitle) return;
							document.body.removeChild($fancyTitle);
							$fancyTitle = undefined;
						};
						oTd.appendChild(oIcons);
					}

					if (!challenge.succeeded) {
						var oInput = document.createElement("input");
						oInput.type = "button";
						oInput.value = toLanguage("Take up", "Relever");
						oInput.style.width = (iScreenScale*11) +"px";
						oInput.style.fontSize = Math.round(iScreenScale*2.4) +"px";
						if (!oInput.dataset) oInput.dataset = {};
						oInput.dataset.id = challenge.id;
						oInput.onclick = function() {
							oScr.innerHTML = "";
							oContainers[0].removeChild(oScr);
							xhr("challengeTry.php", "challenge="+this.dataset.id, function(res) {
								if (!res)
									return false;
								try {
									res = JSON.parse(res);
								}
								catch (e) {
									return false;
								}
								course = "";
								delete window.selectedPerso;
								for (var k in res)
									window[k] = res[k];
								if (course)
									selectPlayerScreen(0);
								else {
									selectMainPage();
									var nbPselector = document.getElementById("select-nbj-1");
									if (nbPselector && nbPselector.onclick)
										nbPselector.onclick();
								}
								if (window.selectedPerso) {
									var persoSelector = document.getElementById("perso-selector-"+window.selectedPerso);
									if (persoSelector && persoSelector.onclick) {
										var oScr = oContainers[0].childNodes[0];
										if (!oScr.dataset)
											oScr.dataset = {};
										oScr.dataset.bypass = true;
										persoSelector.onclick();
									}
								}
								return true;
							});
						};
						oTd.appendChild(oInput);
					}
					oTr.appendChild(oTd);
					oTable.appendChild(oTr);
				}
			}
		}

		oScroll.appendChild(oTable);
	}
	else {
		oScroll.style.textAlign = "center";
		var oDiv = document.createElement("div");
		oDiv.style.fontSize = (iScreenScale*3) +"px";
		oDiv.style.marginTop = (iScreenScale*3) +"px";
		oDiv.style.marginBottom = (iScreenScale*2) +"px";
		oDiv.style.marginLeft = "auto";
		oDiv.style.marginRight = "auto";
		oDiv.style.width = (iScreenScale*60);
		oDiv.style.color = "white";
		oDiv.innerHTML = toLanguage("This circuit has no challenges. Create some right now, it's fast and easy!", "Ce circuit ne comporte aucun défi. Créez-en dès maintenant, c'est facile et rapide !")
		oScroll.appendChild(oDiv);

		var oChallengeCreate = document.createElement("input");
		oChallengeCreate.type = "button";
		oChallengeCreate.style.fontSize = (iScreenScale*3) +"px";
		oChallengeCreate.style.paddingLeft = (iScreenScale*2) +"px";
		oChallengeCreate.style.paddingRight = (iScreenScale*2) +"px";
		oChallengeCreate.value = toLanguage("Go to challenge editor", "Accéder à l'éditeur de défis");
		oChallengeCreate.onclick = function() {
			openChallengeEditor();
		}
		oScroll.appendChild(oChallengeCreate);
	}

	oForm.appendChild(oScroll);

	oScr.appendChild(oForm);

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Back", "Retour");
	oPInput.style.fontSize = (2*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (2*iScreenScale)+"px";
	oPInput.style.top = (35*iScreenScale)+"px";
	oPInput.onclick = function() {
		oScr.innerHTML = "";
		oContainers[0].removeChild(oScr);
		selectMainPage();
	}
	oScr.appendChild(oPInput);

	if (myCircuit && aChallenge) {
		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = toLanguage("Edit challenges...", "Gérer les défis...");
		oPInput.style.fontSize = (2*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.right = (2*iScreenScale)+"px";
		oPInput.style.top = (35*iScreenScale)+"px";
		oPInput.onclick = function() {
			openChallengeEditor();
		}
		oScr.appendChild(oPInput);
	}

	oContainers[0].appendChild(oScr);
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
	oDiv.style.left = "0px";
	oDiv.style.top = (iScreenScale*4) +"px";
	oDiv.style.width = (iScreenScale*iWidth) +"px";
	oDiv.style.fontSize = (iScreenScale*4) +"";
	oDiv.style.textAlign = "center";
	oDiv.innerHTML = toLanguage("Searching for other players<br />Please wait...", "Recherche d'autres joueurs<br />Veuillez patienter...");
	
	oScr.appendChild(oDiv);

	var oAlertCtn = document.createElement("label");
	oAlertCtn.style.position = "absolute";
	oAlertCtn.style.left = "0px";
	oAlertCtn.style.top = (iScreenScale*15-9) +"px";
	oAlertCtn.style.width = (iScreenScale*iWidth) +"px";
	oAlertCtn.style.textAlign = "center";
	
	var oAlert = document.createElement("input");
	oAlert.type = "checkbox";
	oAlert.id = "iAlert";
	oAlert.style.transform = oAlert.style.WebkitTransform = oAlert.style.MozTransform = "scale("+ (iScreenScale/6) +") translateY(8%)";
	oAlert.style.transformOrigin = oAlert.style.WebkitTransformOrigin = oAlert.style.MozTransformOrigin = "bottom right";
	oAlertCtn.appendChild(oAlert);
	
	var oLabel = document.createElement("span");
	oLabel.setAttribute("for", "iAlert");
	oLabel.style.fontSize = (iScreenScale*2) +"pt";
	oLabel.style.marginLeft = "5px";
	oLabel.innerHTML = toLanguage("Notify me when opponents have been found", "M'alerter lorsque des adversaires ont été trouvés");
	oAlertCtn.appendChild(oLabel);

	oScr.appendChild(oAlertCtn);
	
	var ratio = 41;
	var mLeft = 0;
	
	var oLoadBar = document.createElement("div");
	oLoadBar.style.position = "absolute";
	oLoadBar.style.left = "0px";
	oLoadBar.style.top = (iScreenScale*21) +"px";
	oLoadBar.style.width = (iScreenScale*ratio*2) +"px";
	oLoadBar.style.height = Math.round(iScreenScale*8.5) +"px";
	oLoadBar.style.overflow = "hidden";
	for (var i=0;i<ratio;i++) {
		var oImg = document.createElement("img");
		oImg.src = "images/cLoading.png";
		oImg.className = "pixelated";
		oImg.style.width = (iScreenScale*2) +"px";
		oImg.style.position = "absolute";
		oImg.style.left = (i*iScreenScale*2) +"px";
		oImg.style.top = "0px";
		oLoadBar.appendChild(oImg);
	}
	oScr.appendChild(oLoadBar);
	
	var oActivePlayers = document.createElement("div");
	oActivePlayers.style.position = "absolute";
	oActivePlayers.style.left = "0px";
	oActivePlayers.style.top = Math.round(iScreenScale*17.5) +"px";
	oActivePlayers.style.width = (iScreenScale*iWidth) +"px";
	oActivePlayers.style.textAlign = "center";
	oActivePlayers.style.fontSize = (iScreenScale*2) +"px";
	oActivePlayers.style.color = "#0B0";
	oActivePlayers.style.display = "none";
	oActivePlayers.style.backgroundColor = "rgba(0,0,0, 0.7)";
	oActivePlayers.innerHTML = toLanguage('<span id="nb-active-players" style="color:#0E0"></span> currently online. You\'ll join them as soon as they finish their '+ (isBattle ? 'battle':'race'), '<span id="nb-active-players" style="color:#0E0"></span> actuellement en ligne. Vous les rejoindrez une fois leur partie terminée');
	
	oScr.appendChild(oActivePlayers);
	
	var rCount = 1;
	var mLoadX = iScreenScale/2;

	var courseParams = "";
	if (isCup) {
		if (isMCups)
			courseParams += "mid=" + nid;
		else if (isSingle)
			courseParams += (complete ? 'i':'id') + '='+ nid + (isBattle ? '&battle':'');
		else
			courseParams += (complete ? 'c':'s') + 'id='+ nid;
	}
	else if (isBattle)
		courseParams += 'battle';
	if (shareLink.key)
		courseParams += (courseParams ? '&':'') + 'key='+ shareLink.key;
	function rSearchCourse() {
		if (rCount) {
			rCount--;
			if (!rCount) {
				xhr("getCourse.php", courseParams, function(reponse) {
					if (!reponse)
						return false;
					try {
						reponse = JSON.parse(reponse);
					}
					catch (e) {
						return false;
					}
					if (!reponse.found) {
						rCount = 10;
						if (reponse.nb_players) {
							if (reponse.nb_players < 2)
								reponse.nb_players = 2;
							document.getElementById("nb-active-players").innerHTML = reponse.nb_players +" "+ toLanguage("players","joueurs");
							oActivePlayers.style.display = "block";
						}
						else
							oActivePlayers.style.display = "none";
					}
					else {
						var isAlert = oAlert.checked;
						oScr.innerHTML = "";
						oContainers[0].removeChild(oScr);
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
							reponse.time -= Math.round((new Date().getTime()-sTime)/1000);
							document.body.removeChild(oMusicAlert);
						}
						if (reponse.time < 1)
							reponse.time = 1;
						document.getElementById("racecountdown").innerHTML = reponse.time-5;
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

	if (!shareLink.key) {
		xhr("sendCourseNotifs.php", null, function(reponse) {
			return true;
		});
	}

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
		oContainers[0].removeChild(oScr);
		selectPlayerScreen(0);
	}
	oScr.appendChild(oPInput);
	
	oContainers[0].appendChild(oScr);

	updateMenuMusic(0);
}

function chooseRandMap() {
	if (page == "MK") {
		if (course != "BB")
			choose(Math.ceil(Math.random()*NBCIRCUITS));
		else
			choose(NBCIRCUITS + Math.ceil(Math.random()*8));
	}
	else if (isSingle)
		choose(1);
	else if (isBattle)
		choose(NBCIRCUITS+Math.ceil(Math.random()*8));
	else
		choose(Math.ceil(Math.random()*NBCIRCUITS));
}

function selectMapScreen() {
	if ((isCup&&!isMCups) || (isBattle&&isCup))
		selectRaceScreen(0);
	else {
		var oScr = document.createElement("div");
		var oStyle = oScr.style;
		
		var forceClic4 = true;

		oStyle.width = (iWidth*iScreenScale)+"px";
		oStyle.height = (iHeight*iScreenScale)+"px";
		oStyle.border = "solid 1px black";
		oStyle.backgroundColor = "black";

		if (course != "BB")
			oScr.appendChild(toTitle(toLanguage("Choose cup", "Choisissez la coupe"), 0.5));
		else
			oScr.appendChild(toTitle(toLanguage("Choose stage", "Choisissez une arène"), 0.5));

		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = toLanguage("Back", "Retour");
		oPInput.style.fontSize = (2*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (2*iScreenScale)+"px";
		if (!isOnline)
			oPInput.style.top = (35*iScreenScale)+"px";
		else
			oPInput.style.top = (30*iScreenScale)+"px";
		oPInput.onclick = function() {
			if (pause && !isOnline) {
				removeMenuMusic(false);
				quitter();
			}
			else {
				forceClic4 = false;
				oScr.innerHTML = "";
				oContainers[0].removeChild(oScr);
				if (isOnline)
					document.getElementById("waitrace").style.visibility = "hidden";
				chatting = false;
				selectGamersScreen();
			}
		}

		var oCupName = document.createElement("div");
		oCupName.style.position = "absolute";
		oCupName.style.zIndex = 10;
		oCupName.style.top = Math.round(35.5 * iScreenScale - 6) +"px";
		oCupName.style.left = (25 * iScreenScale - 5) +"px";
		oCupName.style.width = (25 * iScreenScale + 6) +"px";
		oCupName.style.height = Math.round(3.9 * iScreenScale) +"px";
		oCupName.style.border = "solid 1px white";
		oCupName.style.color = "white";
		oCupName.style.backgroundColor = "black";
		oCupName.style.borderBottom = "none";
		oCupName.style.textAlign = "center";
		oCupName.style.display = "none";
		oCupName.style.flexDirection = "column";
		oCupName.style.justifyContent = "center";
		var oCupNameDiv = document.createElement("div");
		oCupNameDiv.style.maxHeight = Math.round(3.9 * iScreenScale) +"px";
		oCupNameDiv.style.overflow = "hidden";
		oCupName.appendChild(oCupNameDiv);
		oScr.appendChild(oCupName);

		oScr.appendChild(oPInput);
		if (course != "GP")
			oContainers[0].appendChild(oScr);

		document.getElementById("dMaps").style.top = 40 * iScreenScale +"px";
		document.getElementById("dMaps").style.left = (7 + 25*iScreenScale) +"px";
		document.getElementById("dMaps").style.width = (25 * iScreenScale) +"px";
		document.getElementById("dMaps").style.height = (10 * iScreenScale) +"px";

		function defileMaps(fMap) {
			if (document.getElementById("maps") && document.getElementById("maps").alt == fMap) {
				if (fMap % 4 != 0)
					fMap++;
				else
					fMap -= 3;
				document.getElementById("oMapName").innerHTML = lCircuits[fMap-1];
				document.getElementById("maps").alt = fMap;
				document.getElementById("maps").src = getMapSelectorSrc(fMap-1);
				setTimeout(function() {
					defileMaps(fMap);
				}, 1000);
			}
		}

		var coupes = ["champi", "etoile", "carapace", "carapacebleue", "speciale", "carapacerouge", "banane", "feuille", "megachampi", "eclair", "upchampi", "fireflower", "bobomb", "minichampi", "egg", "iceflower", "plume", "cloudchampi"];
		var nbcoupes = NBCIRCUITS/4;
		var cups_per_line = 6;
		if (course == "BB") {
			coupes = ["snes","gba"];
			nbcoupes = 2;
		}
		var nb_lines = Math.ceil(nbcoupes/cups_per_line);
		cups_per_line = Math.ceil(nbcoupes/nb_lines);
		var cup_width = Math.round(10.5/Math.pow(Math.max(nbcoupes/5,nb_lines,0.5),0.6));
		var cup_margin_x = 4, cup_margin_y = (4/nb_lines);
		var cup_offset_x = 1, cup_offset_y = 38;
		if (course == "BB") {
			cup_width = Math.round(cup_width*1.5);
			cup_margin_x = Math.round(cup_margin_x*2);
			cup_offset_y -= 3;
		}
		for (var i=0;i<nbcoupes;i++) {
			var cups_in_line = Math.min(cups_per_line, nbcoupes-(i-i%cups_per_line));
			var oPImg = document.createElement("img");
			oPImg.className = "pixelated";
			oPImg.src = "images/cups/"+ coupes[i] +".gif";

			oPImg.style.width = (cup_width * iScreenScale) + "px";
			oPImg.style.height = (cup_width * iScreenScale) + "px";
			oPImg.style.cursor = "pointer";
			oPImg.style.position = "absolute";
			var cup_x = ((iWidth+cup_margin_x+cup_offset_x)/2+(((i%cups_per_line)-cups_in_line/2)*(cup_width+cup_margin_x)));
			var cup_y = ((cup_margin_y+cup_offset_y)/2+((Math.floor(i/cups_per_line)-nb_lines/2)*(cup_width+cup_margin_y)));
			oPImg.style.left = Math.round(cup_x*iScreenScale)+"px";
			oPImg.style.top = Math.round(cup_y*iScreenScale)+"px";

			if (course == "BB")
				oPImg.alt = i+(NBCIRCUITS/4);
			else
				oPImg.alt = i;
			
			var mScreenScale = iScreenScale;

			oPImg.onmouseover = function() {
				var oDefMap = new Image();
				oDefMap.src = getMapSelectorSrc(i);
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
				if (cupNames[this.alt]) {
					oCupNameDiv.innerHTML = cupNames[this.alt];
					var cupFS = Math.min(Math.max(8/Math.sqrt(stripSpecialChars(cupNames[this.alt]).length), 1.45), 3);
					oCupName.style.fontSize = Math.round(cupFS*iScreenScale) +"px";
					oCupName.style.display = "flex";
				}
			}

			oPImg.onmouseout = function() {
				document.getElementById("dMaps").style.display = "none";
				document.getElementById("dMaps").innerHTML = "";
				oCupName.style.display = "none";
			}

			oPImg.onclick = function() {
				document.getElementById("dMaps").style.display = "none";

				document.getElementById("dMaps").innerHTML = "";
				oScr.innerHTML = "";
				oContainers[0].removeChild(oScr);

				selectRaceScreen(this.alt*4);
			}

			if (course == "BB") {
				var labels = [toLanguage("SNES Stages", "Arènes SNES"), toLanguage("GBA Stages", "Arènes GBA")];
				var oLabel = document.createElement("div");
				oLabel.style.position = "absolute"
				oLabel.style.left = Math.round((cup_x-cup_margin_x/2)*iScreenScale)+"px";
				oLabel.style.top = Math.round((cup_y+cup_width*0.9)*iScreenScale)+"px";
				oLabel.style.width = ((cup_width+cup_margin_x) * iScreenScale) + "px";
				oLabel.style.fontSize = (iScreenScale*3) +"px";
				oLabel.style.color = "white";
				oLabel.style.textAlign = "center";
				oLabel.innerHTML = labels[i];
				oScr.appendChild(oLabel);
			}

			oScr.appendChild(oPImg);

			if (course == "GP") {
				var iPtsGP = ptsGP.charAt(i)*1;
				if (iPtsGP) {
					var oCup = new Image();
					oCup.src = "images/cups/cup"+ (4-iPtsGP) +".png";
					oCup.style.width = Math.round(4*iScreenScale*cup_width/7) + "px";
					oCup.style.height = Math.round(4*iScreenScale*cup_width/7) + "px";
					oCup.style.position = "absolute"
					oCup.style.left = Math.round((cup_x+cup_width*4/7)*iScreenScale)+"px";
					oCup.style.top = Math.round((cup_y+cup_width*4/7)*iScreenScale)+"px";
					oCup.className = "pixelated";
					oScr.appendChild(oCup);
				}
			}
			else if (isMCups && !isOnline) {
				var mLink = document.createElement("a");
				mLink.style.position = "absolute";
				mLink.style.left = Math.round((cup_x+cup_width*5/7)*iScreenScale)+"px";
				mLink.style.top = Math.round((cup_y+cup_width*5/7)*iScreenScale)+"px";
				mLink.style.backgroundColor = "rgba(0,50,128, 0.5)";
				mLink.style.padding = "4px";
				mLink.style.borderRadius = "50%";
				var iMap = oMaps[aAvailableMaps[i]];
				mLink.href = "?cid="+cupIDs[i];
				mLink.title = toLanguage("Link to this cup", "Lien vers cette coupe");
				mLink.onmouseover = function() {
					this.style.backgroundColor = "rgba(0,102,153, 0.8)";
				}
				mLink.onmouseout = function() {
					this.style.backgroundColor = "rgba(0,50,128, 0.5)";
				}
				var iLink = document.createElement("img");
				iLink.src = "images/clink.png";
				iLink.style.width = Math.round(cup_width*iScreenScale*2/7) +"px";
				mLink.appendChild(iLink);
				oScr.appendChild(mLink);
			}
		}

		if (course == "VS" || course == "BB") {
			var oPInput = document.createElement("input");
			oPInput.type = "button";
			oPInput.value = toLanguage("Random", "Aléatoire");
			oPInput.style.fontSize = (3*iScreenScale)+"px";
			oPInput.style.position = "absolute";
			oPInput.style.left = (34*iScreenScale-10)+"px";
			oPInput.style.top = (30*iScreenScale)+"px";
			
			oPInput.onclick = function() {
				forceClic4 = false;
				oScr.innerHTML = "";
				oContainers[0].removeChild(oScr);
				chooseRandMap();
			};
			oScr.appendChild(oPInput);
		}
		else if (course == "GP")
			oContainers[0].appendChild(oScr);
		else if (course == "CM") {
			var oPInput = document.createElement("input");
			oPInput.type = "button";
			oPInput.value = toLanguage("Ranking", "Classement");
			oPInput.style.fontSize = (3*iScreenScale)+"px";
			oPInput.style.position = "absolute";
			oPInput.style.left = (33*iScreenScale-10)+"px";
			oPInput.style.top = (30*iScreenScale)+"px";
			oPInput.onclick = openRankings;
			oScr.appendChild(oPInput);
		}
		
		if (isOnline) {
			setTimeout(function() {
				if (forceClic4) {
					document.getElementById("dMaps").style.display = "none";
					document.getElementById("dMaps").innerHTML = "";
					oScr.innerHTML = "";
					oContainers[0].removeChild(oScr);
					chooseRandMap();
				}
			}, document.getElementById("racecountdown").innerHTML*1000);
		}
	}
	if (isOnline) {
		setSRest();
		document.getElementById("waitrace").style.visibility = "visible";
	}

	updateMenuMusic(1);
}

function setMapSrc(oPImg,cup,i,src) {
	if (isCup) {
		setTimeout(function() {
			oPImg.src = src;
		}, 100*(i-cup));
	}
	else
		oPImg.src = src;
}

function rankingsLink(oMap) {
	switch (page) {
	case "MK":
		return "classement.php?map="+encodeURIComponent(sCircuits[oMap.map-1]);
	case "CI":
		return "classement.php?circuit="+ oMap.id;
	case "MA":
		return "classement.php?draw="+ oMap.map;
	}
}
function openRankings() {
	if (isMCups)
		open("classement.php?mcup="+ nid);
	else {
		switch (page) {
		case "MK":
			open("classement.php");
			break;
		case "CI":
			open("classement.php"+ (isSingle ? "?circuit="+nid : "?scup="+nid));
			break;
		case "MA":
			open("classement.php"+ (isSingle ? "?draw="+nid : "?ccup="+nid));
		}
	}
}
function exitCircuit() {
	var changeRace = document.getElementById("changeRace");
	var supprRace = document.getElementById("supprRace");
	if (changeRace && !supprRace)
		changeRace.click();
	else
		document.location.href = "index.php";
}

function appendContainers() {
	for (var i=1;i<oContainers.length;i++)
		document.getElementById("mariokartcontainer").appendChild(oContainers[i]);
}

function selectRaceScreen(cup) {
	if (isOnline || (!isSingle && course != "GP")) {
		var oScr = document.createElement("div");
		var oStyle = oScr.style;
		
		var forceClic4 = true;

		oStyle.width = (iWidth*iScreenScale)+"px";
		oStyle.height = (iHeight*iScreenScale)+"px";
		oStyle.border = "solid 1px black";
		oStyle.backgroundColor = "black";

		oContainers[0].appendChild(oScr);

		if (course != "BB")
			oScr.appendChild(toTitle(toLanguage("Choose track", "Choisissez un circuit"), (isSingle?2.5:0.5)));
		else
			oScr.appendChild(toTitle(toLanguage("Choose stage", "Choisissez une arène"), (isSingle?2.5:0.5)));

		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = toLanguage("Back", "Retour");
		oPInput.style.fontSize = (2*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (2*iScreenScale)+"px";
		if (!isOnline)
			oPInput.style.top = (35*iScreenScale)+"px";
		else
			oPInput.style.top = (30*iScreenScale)+"px";
		oPInput.onclick = function() {
			forceClic4 = false;
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			if (isOnline && isCup && !isMCups) {
				document.getElementById("waitrace").style.visibility = "hidden";
				chatting = false;
				selectPlayerScreen(0);
			}
			else {
				if (course != "BB") {
					if (isCup && !isMCups) {
						if (!pause)
							selectGamersScreen();
						else {
							removeMenuMusic(false);
							quitter();
						}
					}
					else
						selectMapScreen();
				}
				else if (!isCup)
					selectMapScreen();
				else if (!pause) selectGamersScreen();
				else {removeMenuMusic(false);quitter();}
			}
		}
		oScr.appendChild(oPInput);
			
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
				mDiv.style.top = (10*iScreenScale)+11*iScreenScale*Math.floor(j/2)+"px";
			}
			mDiv.map = aAvailableMaps[i];
			mDiv.ref = i+1;
			
			var oPImg = new Image();
			setMapSrc(oPImg, cup,i, getMapSelectorSrc(i));
			oPImg.style.width = "100%";
			oPImg.style.height = "100%";
			oPImg.style.border = "double 4px silver";
			oPImg.className = "pixelated";
			mDiv.appendChild(oPImg);
			
			mDiv.appendChild(mapNameOf(mScreenScale, i));
			if (isCup && !isOnline) {
				var mLink = document.createElement("a");
				mLink.style.position = "absolute";
				mLink.style.right = "-3px";
				mLink.style.top = "5px";
				mLink.style.backgroundColor = "rgba(0,50,128, 0.5)";
				mLink.style.padding = "4px";
				mLink.style.borderRadius = "50%";
				var iMap = oMaps[aAvailableMaps[i]];
				mLink.href = (page=="MA") ? "?i="+iMap.map : "?id="+iMap.id;
				mLink.title = toLanguage("Link to this circuit", "Lien vers ce circuit");
				mLink.onclick = function(e) {
					e.stopPropagation();
				}
				mLink.onmouseover = function() {
					this.style.backgroundColor = "rgba(0,102,153, 0.8)";
				}
				mLink.onmouseout = function() {
					this.style.backgroundColor = "rgba(0,50,128, 0.5)";
				}
				var iLink = document.createElement("img");
				iLink.src = "images/clink.png";
				iLink.style.width = (2*iScreenScale) +"px";
				mLink.appendChild(iLink);
				mDiv.appendChild(mLink);
			}

			mDiv.onclick = function() {
				forceClic4 = false;
				oScr.innerHTML = "";
				oContainers[0].removeChild(oScr);
				if (!isOnline) {
					if (course != "CM") {
						appendContainers();
						resetGame(this.map);
					}
					else if (page != "MK") {
						gPersos.length = 0;
						resetGame(this.map);
					}
					else {
						document.body.style.cursor = "progress";
						var tMap = this.map;
						var iMap = tMap.replace(/^[a-zA-Z]+([0-9]+)$/, "$1");
						xhr("ghostsave.php", "map="+ iMap, function(reponse) {
							var ghostSaves;
							try {
								ghostSaves = eval(reponse);
							}
							catch (e) {
								return false;
							}
							if (ghostSaves)
								selectFantomeScreen(ghostSaves, iMap-1);
							else
								selectFantomeScreen(undefined, iMap-1);
							return true;
						});
					}
				}
				else
					choose(this.ref);
			}
			oScr.appendChild(mDiv);
		}

		if (isCup && !isSingle && !isMCups) {
			var oPInput = document.createElement("input");
			oPInput.type = "button";
			if (course != "CM")
				oPInput.value = toLanguage("Random", "Aléatoire");
			else
				oPInput.value = toLanguage("Rankings", "Classement");
			oPInput.style.position = "absolute";
			if (!isOnline) {
				oPInput.style.fontSize = (3*iScreenScale)+"px";
				oPInput.style.left = (34*iScreenScale-10)+"px";
				oPInput.style.top = (34*iScreenScale)+"px";
			}
			else {
				oPInput.style.fontSize = (2*iScreenScale)+"px";
				oPInput.style.left = (67*iScreenScale)+"px";
				oPInput.style.top = (30*iScreenScale)+"px";
			}
			oPInput.onclick = function() {
				if (course != "CM") {
					forceClic4 = false;
					oScr.innerHTML = "";
					oContainers[0].removeChild(oScr);
					chooseRandMap();
				}
				else
					openRankings();
			}
			oScr.appendChild(oPInput);
		}

		if (isOnline) {
			setSRest();
			setTimeout(function() {
				if (forceClic4) {
					oScr.innerHTML = "";
					oContainers[0].removeChild(oScr);
					chooseRandMap();
				}
			}, document.getElementById("racecountdown").innerHTML*1000);
		}
	}
	else {
		if (course == "GP") {
			if (page != "MK" || cup)
				iDificulty = 5;
			else
				iDificulty = 4.5;
		}
		cup++;
		strMap = "map"+ cup;
		appendContainers();
		resetGame(strMap);
	}

	updateMenuMusic(1);
}

var startMusicHandler;
function choose(map) {
	if (!isOnline) {
		appendContainers();
		resetGame("map"+map);
		return;
	}
	var choixJoueurs = [];
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
						aPseudos = new Array();
						aTeams = new Array();
						for (i=0;i<choixJoueurs.length;i++) {
							var aID = choixJoueurs[i][0];
							if (aID != identifiant) {
								aIDs.push(aID);
								aPlayers.push(choixJoueurs[i][1]);
								isCustomPerso(choixJoueurs[i][1]);
								aPlaces.push(choixJoueurs[i][3]);
								aPseudos.push(choixJoueurs[i][4]);
								aTeams.push(choixJoueurs[i][5]);
							}
							else {
								aPlaces.unshift(choixJoueurs[i][3]);
								aPseudos.unshift(choixJoueurs[i][4]);
								aTeams.unshift(choixJoueurs[i][5]);
							}
						}
						selectedTeams = (aTeams.indexOf(-1) == -1);
						if (!selectedTeams)
							aTeams.length = 0;
						tnCourse = new Date().getTime()+rCode[2];
						if (isSingle)
							rCode[2] = 0;
						else {
							if (rCode[4].manualTeams) {
								if (playerIsSelecter())
									rCode[2] = 0;
								else
									rCode[2] -= 12000;
							}
							else
								tnCourse += 5000;
						}
						connecte = rCode[3]+1;
						var cCursor = 0;
						var cTime = 50;
						function moveCursor() {
							var isInFuckingLoop = true;
							if (cCursor == rCode[1]) {
								var pTime = 0, iTime = cTime;
								for (var i=0;i<choixJoueurs.length;i++) {
									iTime = Math.round(iTime*1.05);
									pTime += iTime;
								}
								if (pTime >= rCode[2])
									isInFuckingLoop = false;
							}
							if (isInFuckingLoop) {
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
								setTimeout(function(){document.body.removeChild(oTable);proceedOnlineRaceSelection(rCode)}, 500);
						}
						moveCursor();
						oMap = oMaps[aAvailableMaps[choixJoueurs[rCode[1]][2]-1]];
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
	function playerIsSelecter() {
		var poster;
		for (var i=0;i<choixJoueurs.length;i++) {
			if (choixJoueurs[i][0] == shareLink.player) {
				poster = choixJoueurs[i];
				break;
			}
		}
		if (!poster)
			poster = choixJoueurs[0];
		return (poster[0] == identifiant);
	}
	function proceedOnlineRaceSelection(rCode) {
		var options = rCode[4];
		var strMap = aAvailableMaps[choixJoueurs[rCode[1]][2]-1];
		if (options.manualTeams)
			selectOnlineTeams(strMap,choixJoueurs,playerIsSelecter());
		else
			resetGame(strMap);
	}
	xhr("chooseMap.php", "joueur="+strPlayer+"&map="+map+(course=="BB"?"&battle":""), refreshTab);
	function waitForChoice() {
		xhr("getMap.php", (course=="BB"?"battle":""), refreshTab);
	}
	oTable.appendChild(oTBody);
	document.body.appendChild(oTable);
	document.getElementById("waitrace").style.visibility = "hidden";

	updateMenuMusic(1);

	formulaire.screenscale.disabled = true;
	formulaire.quality.disabled = true;
	formulaire.music.disabled = true;
	formulaire.sfx.disabled = true;

	if (bMusic) {
		startMusicHandler = setInterval(function() {
			if (oMapImg) {
				loadMapMusic();
				clearInterval(startMusicHandler);
			}
		}, 500);
	}
}

function selectOnlineTeams(strMap,choixJoueurs,selecter) {
	var oScr = document.createElement("div");

	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";

	var oTitle = toTitle(toLanguage("Team selection", "Sélection des équipes"), 0.5);
	oTitle.style.fontSize = Math.round(7*iScreenScale)+"px";
	oScr.appendChild(oTitle);

	var oTableCtn = document.createElement("div");
	oTableCtn.style.display = "none";
	oTableCtn.style.position = "absolute";
	oTableCtn.style.zIndex = 50000;
	oTableCtn.style.left = iScreenScale +"px";
	oTableCtn.style.top = (iScreenScale*10) +"px";
	oTableCtn.style.width = ((iWidth-2)*iScreenScale) +"px";
	oTableCtn.style.textAlign = "center";

	var teamsTable = document.createElement("table");
	teamsTable.style.marginLeft = "auto";
	teamsTable.style.marginRight = "auto";
	teamsTable.style.fontSize = Math.round(iScreenScale*2.4) +"px";

	var oMoreOptions = document.createElement("div");
	oMoreOptions.style.zIndex = 50002;
	oMoreOptions.style.display = "none";
	oMoreOptions.style.position = "absolute";
	oMoreOptions.style.right = (3*iScreenScale)+"px";
	oMoreOptions.style.bottom = (5*iScreenScale)+"px";
	
	var oNoTeams = document.createElement("input");
	oNoTeams.type = "button";
	oNoTeams.style.display = "block";
	oNoTeams.style.marginTop = Math.round(iScreenScale/2)+"px";
	oNoTeams.value = toLanguage("No teams", "Chacun pour soi");
	oNoTeams.style.fontSize = (2*iScreenScale)+"px";
	oNoTeams.style.width = (18*iScreenScale)+"px";
	oNoTeams.style.textAlign = "center";
	oNoTeams.onclick = function() {
		confirmCustomOption(toLanguage("No teams?","Jouer sans équipes ?"),
			toLanguage("Please confirm that you want to play without teams", "Confirmez que vous souhaitez jouer en mode <em>chacun pour soi</em>."),
			function() {
				clearTimeout(forceTeamHandler);
				var teamsPayload = "noteams";
				if (isBattle) teamsPayload += "&battle";
				if (isSingle) teamsPayload += "&single";
				removeTeamSelectionUI();
				xhr("chooseTeams.php", teamsPayload, function(res) {
					if (!res) return false;
					var gTeams;
					try {
						gTeams = JSON.parse(res);
					}
					catch (e) {
						return false;
					}
					onTeamsSelected(gTeams);
					return true;
				});
			}
		);
	}
	oMoreOptions.appendChild(oNoTeams);

	var oNoGame = document.createElement("input");
	oNoGame.type = "button";
	oNoGame.style.display = "block";
	oNoGame.style.marginTop = Math.round(iScreenScale/2)+"px";
	oNoGame.value = toLanguage("Cancel game", "Annuler la partie");
	oNoGame.style.fontSize = (2*iScreenScale)+"px";
	oNoGame.style.width = (18*iScreenScale)+"px";
	oNoGame.style.color = "#F60";
	oNoGame.style.textAlign = "center";
	oNoGame.onclick = function() {
		confirmCustomOption(toLanguage("Cancel game?","Annuler la partie ?"),
			toLanguage("Caution, you're about to cancel the game <strong style=\"color:#FEB\">for all players</strong>. Use this option if you're waiting for more players for example.", "Attention, vous êtes sur le point d'annuler la partie <strong style=\"color:#FEB\">pour tous les joueurs</strong>. Utilisez cette option si vous attendez plus de joueurs par exemple."),
			function() {
				clearTimeout(forceTeamHandler);
				var teamsPayload = "cancel";
				if (isBattle) teamsPayload += "&battle";
				removeTeamSelectionUI();
				xhr("chooseTeams.php", teamsPayload, function(res) {
					if (!res) return false;
					var gTeams;
					try {
						gTeams = JSON.parse(res);
					}
					catch (e) {
						return false;
					}
					onTeamsCanceled();
					return true;
				});
			}
		);
	}
	oMoreOptions.appendChild(oNoGame);
	oScr.appendChild(oMoreOptions);

	function confirmCustomOption(title,msg, onConfirm) {
		var oMask = document.createElement("div");
		oMask.id = "online-teams-confirm";
		oMask.style.position = "absolute";
		oMask.style.left = 0;
		oMask.style.top = 0;
		oMask.style.width = (iWidth*iScreenScale)+"px";
		oMask.style.height = (iHeight*iScreenScale)+"px";
		oMask.style.backgroundColor = "rgba(0,0,0, 0.5)";
		oMask.style.zIndex = 60000;
		var oDialog = document.createElement("div");
		oDialog.style.position = "absolute";
		oDialog.style.zIndex = 60000;
		oDialog.style.left = Math.round(iScreenScale*iWidth/2) +"px";
		oDialog.style.top = Math.round(iScreenScale*iHeight/2) +"px";
		oDialog.style.width = (iScreenScale*40) +"px";
		oDialog.style.transform = oDialog.style.WebkitTransform = oDialog.style.MozTransform = "translate(-50%, -50%)";
		oDialog.style.backgroundColor = "gray";
		oDialog.style.border = "solid 1px silver";
		oDialog.onclick = function(event) {
			event.stopPropagation();
		}
		var oTitle = document.createElement("div");
		oTitle.style.marginTop = iScreenScale +"px";
		oTitle.style.marginBottom = Math.round(iScreenScale/2) +"px";
		oTitle.style.fontSize = Math.round(iScreenScale*2.5) +"px";
		oTitle.style.textAlign = "center";
		oTitle.style.marginLeft = (iScreenScale*2) +"px";
		oTitle.style.marginRight = (iScreenScale*2) +"px";
		oTitle.style.color = "#FE9";
		oTitle.innerHTML = title;
		oDialog.appendChild(oTitle);
		var oMessage = document.createElement("div");
		oMessage.style.marginBottom = iScreenScale +"px";
		oMessage.style.textAlign = "center";
		oMessage.style.marginLeft = Math.round(iScreenScale*1.5) +"px";
		oMessage.style.marginRight = Math.round(iScreenScale*1.5) +"px";
		oMessage.style.fontSize = Math.round(iScreenScale*1.8) +"px";
		oMessage.style.color = "white";
		oMessage.innerHTML = msg;
		oDialog.appendChild(oMessage);
		var oButtonCtn = document.createElement("div");
		oButtonCtn.style.textAlign = "center";
		oButtonCtn.style.marginBottom = iScreenScale +"px";
		var oValid = document.createElement("input");
		oValid.type = "button";
		oValid.style.marginRight = Math.round(iScreenScale/2) +"px";
		oValid.value = "Ok";
		oValid.style.fontSize = Math.round(iScreenScale*2) +"px";
		oValid.onclick = function() {
			oScr.removeChild(oMask);
			onConfirm();
			return false;
		};
		oButtonCtn.appendChild(oValid);
		var oCancel = document.createElement("input");
		oCancel.type = "button";
		oCancel.value = toLanguage("Cancel", "Annuler");
		oCancel.style.marginLeft = Math.round(iScreenScale/2) +"px";
		oCancel.style.fontSize = Math.round(iScreenScale*2) +"px";
		oCancel.onclick = oMask.onclick = function() {
			oScr.removeChild(oMask);
			return false;
		};
		oButtonCtn.appendChild(oCancel);
		oDialog.appendChild(oButtonCtn);
		oMask.appendChild(oDialog);
		oScr.appendChild(oMask);
		setTimeout(function() {
			oValid.focus();
		}, 1);
		return oMask;
	}

	function updateTeamsTable(editable) {
		teamsTable.innerHTML = "";
		var nbRows = Math.max(teams[0].length,teams[1].length);
		for (var i=0;i<nbRows;i++) {
			var oTr = document.createElement("tr");
			for (var j=0;j<2;j++) {
				var oTd = document.createElement("td");
				var player = teams[j][i];
				if (player) {
					if (selectedTeams) {
						oTd.style.backgroundColor = j ? "#fba":"#abf";
						oTd.style.color = j ? "red":"blue";
					}
					else {
						oTd.style.backgroundColor = "#ccc";
						oTd.style.color = "#222";
					}
					oTd.style.position = "relative";
					oTd.style.textAlign = "center";
					oTd.style.userSelect = "none";
					var oPlayerName = document.createElement("span");
					oPlayerName.style.display = "block";
					oPlayerName.style.top = "1px";
					oPlayerName.style.position = "relative";
					oPlayerName.innerHTML = player[4];
					oPlayerName.style.whiteSpace = "nowrap";
					oPlayerName.style.textOverflow = "ellipsis";
					oPlayerName.style.overflow = "hidden";
					oPlayerName.style.width = (iScreenScale*16) +"px";
					oTd.appendChild(oPlayerName);
					if (editable) {
						var oArrow = document.createElement("span");
						oArrow.innerHTML = j ? "\u25C0":"\u25B6";
						oArrow.style.position = "absolute";
						if (j) {
							oArrow.style.left = "0px";
							oTd.style.paddingLeft = (iScreenScale*3) +"px";
							oTd.style.paddingRight = iScreenScale +"px";
						}
						else {
							oArrow.style.right = "0px";
							oTd.style.paddingRight = (iScreenScale*3) +"px";
							oTd.style.paddingLeft = iScreenScale +"px";
						}
						oArrow.style.top = "48%";
						oArrow.style.color = "#F80";
						oArrow.style.padding = Math.round(iScreenScale/2) +"px";
						oArrow.style.transform = oArrow.style.WebkitTransform = oArrow.style.MozTransform = "translateY(-50%)";
						oArrow.style.cursor = "pointer";
						oArrow.style.opacity = 0.9;
						oArrow.onmouseover = function() {
							this.style.opacity = 0.45;
						}
						oArrow.onmouseout = function() {
							this.style.opacity = 0.9;
						}
						if (!oArrow.dataset) oArrow.dataset = {};
						oArrow.dataset.i = i;
						oArrow.dataset.j = j;
						oArrow.onclick = moveTeamPlayer;
						oTd.appendChild(oArrow);
					}
				}
				else {
					oTd.innerHTML = "&nbsp;";
					oTd.style.width = (iScreenScale*20) +"px";
				}
				oTr.appendChild(oTd);
			}
			teamsTable.appendChild(oTr);
		}
		if (teams[0].length && teams[1].length) {
			oSubmit.style.opacity = 1;
			oSubmit.disabled = false;
			oSubmit.style.cursor = "";
		}
		else {
			oSubmit.style.opacity = 0.4;
			oSubmit.disabled = true;
			oSubmit.style.cursor = "not-allowed";
		}
	}
	function moveTeamPlayer() {
		var oMask = document.createElement("div");
		oMask.style.position = "absolute";
		oMask.style.left = "0px";
		oMask.style.top = "0px";
		oMask.style.width = (iScreenScale*iWidth) +"px";
		oMask.style.height = (iScreenScale*iWidth) +"px";
		oMask.style.zIndex = 50000;
		oScr.appendChild(oMask);
		var i = +this.dataset.i, j = +this.dataset.j;
		var player = teams[j][i];
		teams[j].splice(i,1);
		teams[1-j].splice(Math.min(i,teams[1-j].length),0,player);
		var oTd = this.parentNode;
		oTd.style.backgroundColor = "#ccc";
		function smoothTeamMove(t,T,dt) {
			var aT = t;
			t += dt;
			if (t > T) {
				oScr.removeChild(oMask);
				updateTeamsTable(true);
			}
			else {
				oTd.style.left = Math.round(iScreenScale*20*t/T*(j?-1:1)) +"px";
				var T_2 = T/2;
				if ((aT < T_2) && (t >= T_2)) {
					oTd.style.backgroundColor = j ? "#abf":"#fba";
					oTd.style.color = j ? "blue":"red";
				}
				var oTrs = teamsTable.getElementsByTagName("tr");
				for (var j_=0;j_<2;j_++) {
					var down = (j_==j);
					for (var i_=down?i+1:i;i_<oTrs.length;i_++) {
						var oTds = oTrs[i_].getElementsByTagName("td");
						oTds[j_].style.top = Math.round(iScreenScale*3*t/T*(down?-1:1)) +"px";
					}
				}
				changeTeamHandler = setTimeout(function() {
					smoothTeamMove(t,T,dt);
				}, dt);
			}
		}
		smoothTeamMove(0,150,20);
	}
	function onTeamsSelected(res) {
		tnCourse = new Date().getTime()+res.time;

		var choosedTeams = res.teams;
		var playersTeams = {};
		for (var i=0;i<2;i++) {
			for (var j=0;j<teams[i].length;j++)
				playersTeams[teams[i][j][0]] = teams[i][j];
		}
		for (var i=0;i<choosedTeams.length;i++)
			playersTeams[choosedTeams[i].id][5] = choosedTeams[i].team;
		for (var i=0;i<strPlayer.length;i++)
			aTeams[i] = playersTeams[identifiant][5];
		for (var i=0;i<aPlayers.length;i++) {
			var id = aIDs[i];
			var inc = i+strPlayer.length;
			aTeams[inc] = playersTeams[id][5];
		}
		selectedTeams = (aTeams.indexOf(-1) == -1);
		teams[0].length = 0;
		teams[1].length = 0;
		if (selectedTeams) {
			for (var i=0;i<choosedTeams.length;i++)
				teams[choosedTeams[i].team].push(playersTeams[choosedTeams[i].id]);
		}
		else {
			for (var i=0;i<choosedTeams.length;i++)
				teams[i%2].push(playersTeams[choosedTeams[i].id]);
		}

		updateTeamsTable(false);
		oTableCtn.removeChild(oSubmit);
		oScr.removeChild(oMoreOptions);
		var oTeamsSelected = document.createElement("div");
		oTeamsSelected.style.textAlign = "center";
		oTeamsSelected.style.fontSize = (iScreenScale*3) +"px";
		oTeamsSelected.style.marginTop = iScreenScale +"px";
		oTeamsSelected.style.color = "#DFC";
		if (selectedTeams)
			oTeamsSelected.innerHTML = toLanguage("Teams have been selected !", "Les équipes ont été sélectionnées !");
		else
			oTeamsSelected.innerHTML = toLanguage("Mode &quot;no teams&quot; selected. In this game, you're playing for yourself!", "Mode &quot;Chacun pour soi&quot; sélectionné. Cette partie se déroulera sans équipes");
		oTableCtn.appendChild(oTeamsSelected);
		oTableCtn.style.display = "block";

		var tnCountdown = tnCourse-new Date().getTime();
		setTimeout(function() {
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			resetGame(strMap);
		}, Math.min(2000,tnCountdown-1000));
	}
	function onTeamsCanceled() {
		oContainers[0].removeChild(oScr);
		oScr.innerHTML = "";

		var oDiv = document.createElement("div");
		oDiv.style.position = "absolute";
		oDiv.style.left = (iScreenScale*10+10) +"px";
		oDiv.style.top = (iScreenScale*15+10) +"px";
		oDiv.style.fontSize = (iScreenScale*2) +"pt";
		oDiv.innerHTML = toLanguage("The game has been cancelled by the teams selector.", "Partie annulée par le sélectionneur des équipes.");
		
		oDiv.appendChild(document.createElement("br"));
		
		var nSearch = document.createElement("a");
		nSearch.style.color = "white";
		nSearch.innerHTML = toLanguage("Search for new players", "Rechercher de nouveaux joueurs");
		nSearch.setAttribute("href", "#null");
		nSearch.onclick = function() {
			document.body.removeChild(oDiv);
			removeMenuMusic();
			removeGameMusics();
			formulaire.screenscale.disabled = false;
			formulaire.quality.disabled = false;
			formulaire.music.disabled = false;
			formulaire.sfx.disabled = false;
			chatting = false;
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

		clearInterval(startMusicHandler);
	}
	function removeTeamSelectionUI() {
		oTableCtn.style.display = "none";
		oMoreOptions.style.display = "none";
		var oMask = document.getElementById("online-teams-confirm");
		if (oMask) oScr.removeChild(oMask);
		document.getElementById("waitteam").style.visibility = "hidden";
	}

	var teams = [[],[]];
	for (var i=0;i<choixJoueurs.length;i++)
		teams[choixJoueurs[i][5]].push(choixJoueurs[i]);

	var oSubmit = document.createElement("input");
	oSubmit.type = "button";
	oSubmit.style.fontSize = (iScreenScale*3) +"px";
	oSubmit.style.marginTop = iScreenScale +"px";
	oSubmit.value = toLanguage("Validate","Valider");
	oSubmit.onclick = function() {
		clearTimeout(forceTeamHandler);
		var teamsPayload = "";
		var inc = 0;
		for (var i=0;i<2;i++) {
			for (var j=0;j<teams[i].length;j++) {
				if (inc) teamsPayload += "&";
				teamsPayload += "j"+(teams[i][j][0])+"="+i;
				inc++;
			}
		}
		if (isBattle) teamsPayload += "&battle";
		if (isSingle) teamsPayload += "&single";
		removeTeamSelectionUI();
		xhr("chooseTeams.php", teamsPayload, function(res) {
			if (!res) return false;
			var gTeams;
			try {
				gTeams = JSON.parse(res);
			}
			catch (e) {
				return false;
			}
			onTeamsSelected(gTeams);
			return true;
		});
	};
	oTableCtn.appendChild(teamsTable);
	oTableCtn.appendChild(oSubmit);
	oScr.appendChild(oTableCtn);

	var forceTeamHandler, changeTeamHandler;

	var curTime = new Date().getTime();
	var tnCountdown = tnCourse-curTime-2000;
	if (selecter) {
		document.getElementById("teamcountdown").innerHTML = Math.round(tnCountdown/1000);
		setSRest("team");
		document.getElementById("waitteam").style.visibility = "visible";
		dRest("team");
		forceTeamHandler = setTimeout(function() {
			clearTimeout(changeTeamHandler);
			var teamsPayload = "";
			if (isBattle) teamsPayload = "battle";
			removeTeamSelectionUI();
			xhr("chooseTeams.php", teamsPayload, function(res) {
				if (!res) return false;
				var gTeams;
				try {
					gTeams = JSON.parse(res);
				}
				catch (e) {
					return false;
				}
				onTeamsSelected(gTeams);
				return true;
			});
		}, document.getElementById("teamcountdown").innerHTML*1000);

		updateTeamsTable(true);
		oTableCtn.style.display = "block";
		oMoreOptions.style.display = "block";
	}
	else {
		oScr.style.visibility = "hidden";

		var oDiv = document.createElement("div");
		oDiv.style.position = "absolute";
		oDiv.style.left = (iScreenScale*6) + "px";
		oDiv.style.top = (iScreenScale*12) + "px";
		oDiv.style.fontSize = Math.round(iScreenScale*2.5) + "px";
		oDiv.style.color = "#DFC";
		oDiv.innerHTML = language ? "Teams are being selected... Please don't exit game":"Les équipes sont cours de sélection... Ne pas quitter la partie.";
		oScr.appendChild(oDiv);

		var ratio = 41;
		var mLeft = 0;
		
		var oLoadBar = document.createElement("div");
		oLoadBar.style.position = "absolute";
		oLoadBar.style.left = "0px";
		oLoadBar.style.top = (iScreenScale*19) +"px";
		oLoadBar.style.width = (iScreenScale*ratio*2) +"px";
		oLoadBar.style.height = Math.round(iScreenScale*8.5) +"px";
		oLoadBar.style.overflow = "hidden";
		for (var i=0;i<ratio;i++) {
			var oImg = document.createElement("img");
			oImg.src = "images/cLoading.png";
			oImg.className = "pixelated";
			oImg.style.width = (iScreenScale*2) +"px";
			oImg.style.position = "absolute";
			oImg.style.left = (i*iScreenScale*2) +"px";
			oImg.style.top = "0px";
			oImg.style.opacity = 0.5;
			oLoadBar.appendChild(oImg);
		}
		oScr.appendChild(oLoadBar);

		var loadBarInc = 0;
		function fillLoadBar() {
			var oImgs = oLoadBar.getElementsByTagName("img");
			var t1 = new Date().getTime();
			var i1 = Math.round(ratio*Math.min((t1-curTime)/tnCountdown,1));
			while (loadBarInc < i1) {
				oImgs[loadBarInc].style.opacity = 1;
				loadBarInc++;
			}
		}

		function waitForSelection() {
			xhr("getTeams.php", "", function(res) {
				oScr.style.visibility = "";
				if (!res) return false;
				var gTeams;
				try {
					gTeams = JSON.parse(res);
				}
				catch (e) {
					return false;
				}
				switch (gTeams.state) {
				case "selecting_teams":
					fillLoadBar();
					setTimeout(waitForSelection, 1000);
					break;
				case "teams_selected":
					oScr.removeChild(oDiv);
					oScr.removeChild(oLoadBar);
					onTeamsSelected(gTeams);
					break;
				default:
					oScr.removeChild(oDiv);
					oScr.removeChild(oLoadBar);
					onTeamsCanceled();
				}
				return true;
			});
		}
		waitForSelection();
	}

	oContainers[0].appendChild(oScr);
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
	oContainers[0].appendChild(oDiv);
	chatting = false;
}

function dRest(type) {
	if (!type) type = "race";
	if (isOnline) {
		var tRest = document.getElementById(type+"countdown").innerHTML - 1;
		document.getElementById(type+"countdown").innerHTML = tRest;
		if (tRest && (document.getElementById("wait"+type).style.visibility == "visible"))
			setTimeout(function(){dRest(type)}, 1000);
	}
}
function setSRest(type) {
	if (!type) type = "race";
	if (isOnline) {
		document.getElementById("wait"+type).style.left = (iScreenScale*2+10) +"px";
		document.getElementById("wait"+type).style.top = (iScreenScale*35+10) +"px";
		document.getElementById("wait"+type).style.minWidth = (iScreenScale*(iWidth-4)) +"px";
		document.getElementById("wait"+type).style.fontSize = (iScreenScale*3) +"px";
	}
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
				mId = rep;
				mPseudo = iPseudo.value;
				mCode = iCode.value;
				oScr.innerHTML = "";
				oContainers[0].removeChild(oScr);
				selectPlayerScreen(0);
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
	
	oContainers[0].appendChild(oScr);

	updateMenuMusic(0);
}
function selectFantomeScreen(ghostsData, map, otherGhostsData) {
	var oScr = document.createElement("div");
	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";

	oScr.appendChild(toTitle(lCircuits[map], 0.4));
	
	var oTable = document.createElement("table");
	oTable.setAttribute("border", "4px");
	oTable.style.borderStyle = "double";
	oTable.style.borderColor = "gray";
	oTable.style.height = (iScreenScale*8) +"px";
	oTable.style.position = "absolute";
	oTable.style.left = (iScreenScale*22) +"px";
	oTable.style.top = (iScreenScale*10) +"px";
	
	var oGhost = document.createElement("tr");
	var oPersoImage = document.createElement("td");
	oPersoImage.style.width = (5 * iScreenScale) + "px";
	oPersoImage.style.paddingRight = "6px";
	
	var oDiv = document.createElement("div");
	oDiv.style.position = "relative";
	oDiv.style.width = (5 * iScreenScale) + "px";
	oDiv.style.height = (5 * iScreenScale) + "px";
	oDiv.style.overflow = "hidden";

	var oPImg = new Image();
	oPImg.style.height = (5 * iScreenScale) +"px";
	oPImg.style.position = "absolute";
	oPImg.className = "pixelated";
	
	if (ghostsData)
		oPImg.alt = ghostsData[0];
	oPImg.nb = i;
	oPImg.style.left = -(30 * iScreenScale) +"px";
	oPImg.style.top = "0px";
	oDiv.appendChild(oPImg);
	oPersoImage.appendChild(oDiv);
	oGhost.appendChild(oPersoImage);
	
	var oPersoTime = document.createElement("td");
	oPersoTime.style.textAlign = "center";
	oPersoTime.style.width = (iScreenScale*30) +"px";
	oPersoTime.style.fontSize = Math.round(iScreenScale*5.5) + "px";
	oPersoTime.style.color = "white";
	if (ghostsData)
		gRecord = ghostsData[1].length;
	else
		gRecord = undefined;
	function writeTime(perso,time,oImg,oDiv) {
		if (!oImg) oImg = oPImg;
		if (!oDiv) oDiv = oPersoTime;
		oImg.src = getSpriteSrc(perso);
		var timeMillis = time * 67;
		var timeMins = Math.floor(timeMillis/60000);
		timeMillis -= timeMins*60000;
		timeMins += "";
		var timeSecs = Math.floor(timeMillis/1000);
		timeMillis -= timeSecs*1000;
		timeSecs += "";
		if (timeSecs.length < 2)
			timeSecs = "0"+ timeSecs;
		timeMillis += "";
		while (timeMillis.length < 3)
			timeMillis = "0"+ timeMillis;
		oDiv.innerHTML = timeMins +":"+ timeSecs +":"+ timeMillis;
	}
	
	function multiGhosts(gTimes) {
		oScr.innerHTML = "";
		var a = gID-3, b = gID+4;
		if (a < 0)
			b -= a;
		else if (b > gTimes.length)
			a -= (b-gTimes.length);
		a = Math.max(a,0);
		b = Math.min(b,gTimes.length);
		gIDs = new Array();
		gIDs.length = b-a;
		var inc = 0;
		for (var i=a;i<b;i++) {
			gIDs[inc] = i;

			var iScr = document.createElement("div");
			iScr.style.position = "absolute";
			if (i == (b-1))
				iScr.style.left = (iScreenScale*20) +"px";
			else
				iScr.style.left = ((inc%2)*iScreenScale*40) +"px";
			iScr.style.top = ((1 + Math.floor(inc/2)*8)*iScreenScale) +"px";
			iScr.style.width = (iScreenScale*40) +"px";

			var iTable = document.createElement("table");
			iTable.setAttribute("border", "2px");
			iTable.style.marginLeft = "auto";
			iTable.style.marginRight = "auto";
			iTable.style.borderStyle = "double";
			iTable.style.borderColor = "gray";
			iTable.style.width = (iScreenScale*24) +"px";
			iTable.style.height = (iScreenScale*4) +"px";
			
			var iGhost = document.createElement("tr");
			var iPersoImage = document.createElement("td");
			iPersoImage.style.width = (3 * iScreenScale) + "px";
			iPersoImage.style.paddingRight = "5px";
			
			var iDiv = document.createElement("div");
			iDiv.style.position = "relative";
			iDiv.style.width = (3 * iScreenScale) + "px";
			iDiv.style.height = (3 * iScreenScale) + "px";
			iDiv.style.overflow = "hidden";

			var iPImg = new Image();
			iPImg.style.height = (3 * iScreenScale) +"px";
			iPImg.style.position = "absolute";
			iPImg.className = "pixelated";
			
			if (ghostsData)
				iPImg.alt = ghostsData[0];
			iPImg.nb = i;
			iPImg.style.left = -(18 * iScreenScale) +"px";
			iPImg.style.top = "0px";
			iDiv.appendChild(iPImg);
			iPersoImage.appendChild(iDiv);
			iGhost.appendChild(iPersoImage);
			
			var iPersoTime = document.createElement("td");
			iPersoTime.style.textAlign = "center";
			iPersoTime.style.fontSize = Math.round(iScreenScale*3.5) + "px";
			iPersoTime.style.color = "white";
			writeTime(gTimes[gIDs[inc]][1],gTimes[gIDs[inc]][2],iPImg,iPersoTime);

			var fGauche = document.createElement("input");
			fGauche.type = "button";
			fGauche.value = "\u2190";
			fGauche.style.fontSize = (4*iScreenScale)+"px";
			fGauche.style.position = "absolute";
			fGauche.style.left = iScreenScale+"px";
			fGauche.style.top = Math.round(0.5*iScreenScale)+"px";
			(function(inc,iPImg,iPersoTime) {
				fGauche.onclick = function() {
					gIDs[inc]--;
					if (gIDs[inc] < 0)
						gIDs[inc] = gTimes.length-1;
					writeTime(gTimes[gIDs[inc]][1],gTimes[gIDs[inc]][2],iPImg,iPersoTime);
				}
			})(inc,iPImg,iPersoTime);
			iScr.appendChild(fGauche);
			
			var fDroite = document.createElement("input");
			fDroite.type = "button";
			fDroite.value = "\u2192";
			fDroite.style.fontSize = (4*iScreenScale)+"px";
			fDroite.style.position = "absolute";
			fDroite.style.left = (32.5*iScreenScale)+"px";
			fDroite.style.top = Math.round(0.5*iScreenScale)+"px";
			(function(inc,iPImg,iPersoTime) {
				fDroite.onclick = function() {
					gIDs[inc]++;
					if (gIDs[inc] >= gTimes.length)
						gIDs[inc] = 0;
					writeTime(gTimes[gIDs[inc]][1],gTimes[gIDs[inc]][2],iPImg,iPersoTime);
				}
			})(inc,iPImg,iPersoTime);
			iScr.appendChild(fDroite);
	
			iGhost.appendChild(iPersoTime);
			iTable.appendChild(iGhost);
			iScr.appendChild(iTable);
			oScr.appendChild(iScr);

			inc++;
		}

		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = toLanguage("Back", "Retour");
		oPInput.style.fontSize = (2*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (2*iScreenScale)+"px";
		oPInput.style.top = (36*iScreenScale)+"px";
		oPInput.onclick = function() {
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			selectFantomeScreen(ghostsData, map, {"times":gTimes,"id":gID});
		};
		oScr.appendChild(oPInput);

		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = toLanguage("Let's go", "Commencer");
		oPInput.style.fontSize = (3*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (52*iScreenScale-10)+"px";
		oPInput.style.top = (34*iScreenScale)+"px";
		
		oPInput.onclick = function() {
			seeGhost(false);
		};
		oScr.appendChild(oPInput);
	}
	function showGhosts() {
		if (gTimes.length) {
			for (i=0;i<gTimes.length-1;i++) {
				var m = i;
				for (j=i+1;j<gTimes.length;j++) {
					if (gTimes[j][2] < gTimes[m][2])
						m = j;
				}
				var c = gTimes[m];
				gTimes[m] = gTimes[i];
				gTimes[i] = c;
			}
			if (gID == -1) {
				if (ghostsData) {
					gID = gTimes.length-1;
					while (gID && (gTimes[gID][2] >= ghostsData[1].length))
						gID--;
				}
				else
					gID = 0;
			}
			var fGauche = document.createElement("input");
			fGauche.id = "fGauche";
			fGauche.type = "button";
			fGauche.value = "\u2190";
			fGauche.style.fontSize = (6*iScreenScale)+"px";
			fGauche.style.position = "absolute";
			fGauche.style.left = (12*iScreenScale)+"px";
			fGauche.style.top = Math.round(10.5*iScreenScale)+"px";
			fGauche.onclick = function() {
				gID--;
				if (gID < 0)
					gID = gTimes.length-1;
				writeTime(gTimes[gID][1],gTimes[gID][2]);
			}
			oScr.appendChild(fGauche);
			
			var fDroite = document.createElement("input");
			fDroite.id = "fDroite";
			fDroite.type = "button";
			fDroite.value = "\u2192";
			fDroite.style.fontSize = (6*iScreenScale)+"px";
			fDroite.style.position = "absolute";
			fDroite.style.left = (63*iScreenScale)+"px";
			fDroite.style.top = Math.round(10.5*iScreenScale)+"px";
			fDroite.onclick = function() {
				gID++;
				if (gID >= gTimes.length)
					gID = 0;
				writeTime(gTimes[gID][1],gTimes[gID][2]);
			}
			oScr.appendChild(fDroite);
			writeTime(gTimes[gID][1],gTimes[gID][2]);
			if (ghostsData)
				oScr.style.visibility = "visible";
			else
				oContainers[0].appendChild(oScr);
			if (OPFace)
				OPFace.style.display = "none";
			document.body.style.cursor = "default";
			OPFace7 = document.createElement("input");
			OPFace7.type = "button";
			OPFace7.value = toLanguage("7 ghosts...", "7 fantômes...");
			OPFace7.style.fontSize = (2*iScreenScale)+"px";
			OPFace7.style.position = "absolute";
			OPFace7.style.right = (2*iScreenScale)+"px";
			OPFace7.style.top = (36*iScreenScale)+"px";
			var oHint;
			OPFace7.onmouseover = function() {
				if (oHint) return;
				oHint = document.createElement("div");
				oHint.style.position = "absolute";
				oHint.style.textAlign = "center";
				oHint.style.fontSize = (2*iScreenScale)+"px";
				oHint.style.width = (30*iScreenScale)+"px";
				oHint.style.right = (2*iScreenScale)+"px";
				oHint.style.bottom = (4*iScreenScale)+"px";
				oHint.style.backgroundColor = "rgba(204,192,178,0.95)";
				oHint.style.padding = Math.round(iScreenScale/2) +" "+ iScreenScale +"px";
				oHint.style.color = "#363330";
				oHint.innerHTML = toLanguage("Play with 7 ghosts with the same level as the ghost above", "Affronter 7 fantômes du même niveau que le fantôme ci-dessus");
				oHint.style.borderBottomLeftRadius = iScreenScale +"px";
				oHint.style.borderTopRightRadius = iScreenScale +"px";
				oScr.appendChild(oHint);
			}
			OPFace7.onmouseout = function() {
				if (oHint) {oScr.removeChild(oHint);oHint=null;}
			}
			OPFace7.onclick = function() {
				if (oHint) {oScr.removeChild(oHint);oHint=null;}
				multiGhosts(gTimes);
			}
			oScr.appendChild(OPFace7);
		}
		else {
			if (ghostsData) {
				try {
					alert(language ? 'No other record for this circuit yet':'Aucun autre record pour ce circuit');
				}
				catch (e) {
				}
				oScr.style.visibility = "visible";
				gID = -1;
				document.body.style.cursor = "default";
			}
			else {
				oScr.innerHTML = "";
				try {
					oContainers[0].removeChild(oScr);
				}
				catch (e) {
				}
				document.body.style.cursor = "default";
				gPersos.length = 0;
				resetGame(aAvailableMaps[map]);
			}
		}
	}
	function otherGhosts() {
		document.body.style.cursor = "progress";
		if (ghostsData)
			oScr.style.visibility = "hidden";
		xhr("otherghosts.php", "map="+ (map+1), function(reponse) {
			if (reponse) {
				try {
					gTimes = eval(reponse);
				}
				catch (e) {
					return false;
				}
				showGhosts();
				return true;
			}
			return false;
		});
	}

	function seeGhost(replay) {
		if (replay) {
			pause = true;
			fInfos.replay = true;
			gSelectedPerso = strPlayer[0];
		}
		if (gID == -1) {
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			if (replay) {
				strPlayer[0] = ghostsData[0];
				iTrajet = ghostsData[1];
			}
			else {
				gPersos = [ghostsData[0]];
				jTrajets = [ghostsData[1]];
			}
			resetGame(aAvailableMaps[map]);
		}
		else {
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			document.body.style.cursor = "progress";
			var xhrUrl, xhrData;
			if (gIDs) {
				xhrUrl = "ghostsrace.php";
				xhrData = "";
				for (var i=0;i<gIDs.length;i++) {
					if (i)
						xhrData += "&";
					xhrData += "id"+i+"="+gTimes[gIDs[i]][0];
				}
			}
			else {
				xhrUrl = "ghostrace.php";
				xhrData = "id="+ gTimes[gID][0];
			}
			xhr(xhrUrl, xhrData, function(reponse) {
				if (reponse) {
					var gCourse;
					try {
						gCourse = eval(reponse);
					}
					catch (e) {
						return false;
					}
					if (replay) {
						strPlayer[0] = gTimes[gID][1];
						iTrajet = gCourse;
					}
					else {
						if (gIDs) {
							gPersos = [];
							for (var i=0;i<gIDs.length;i++)
								gPersos.push(gTimes[gIDs[i]][1]);
							jTrajets = gCourse;
						}
						else {
							gPersos = [gTimes[gID][1]];
							jTrajets = [gCourse];
						}
					}
					resetGame(aAvailableMaps[map]);
					return true;
				}
				return false;
			});
		}
	}

	if (ghostsData)
		writeTime(ghostsData[0],ghostsData[1].length);
	
	var gTimes;
	var gID = -1;
	var gIDs;
	
	oGhost.appendChild(oPersoTime);
	oTable.appendChild(oGhost);
	
	oScr.appendChild(oTable);
	
	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Face with this ghost", "Affronter ce fantôme");
	oPInput.style.fontSize = (3*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (22*iScreenScale-10)+"px";
	oPInput.style.top = (20*iScreenScale)+"px";
	oPInput.style.width = (37*iScreenScale+31)+"px";
	oPInput.onclick = function() {
		seeGhost(false);
	}
	oScr.appendChild(oPInput);

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("See race", "Voir la course");
	oPInput.style.fontSize = (3*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (22*iScreenScale-10)+"px";
	oPInput.style.top = (25*iScreenScale)+"px";
	oPInput.style.width = (37*iScreenScale+31)+"px";
	oPInput.onclick = function() {
		seeGhost(true);
	};
	oScr.appendChild(oPInput);
	
	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Play alone", "Jouer seul");
	oPInput.style.fontSize = (3*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (22*iScreenScale-10)+"px";
	oPInput.style.top = (30*iScreenScale)+"px";
	oPInput.style.width = (37*iScreenScale+31)+"px";
	oPInput.onclick = function() {
		oScr.innerHTML = "";
		oContainers[0].removeChild(oScr);
		gPersos.length = 0;
		resetGame(aAvailableMaps[map]);
	}
	oScr.appendChild(oPInput);

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Back", "Retour");
	oPInput.style.fontSize = (2*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (2*iScreenScale)+"px";
	oPInput.style.top = (36*iScreenScale)+"px";
	oPInput.onclick = function() {
		if ((gID == -1) || !ghostsData) {
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			selectRaceScreen(map-map%4);
		}
		else {
			writeTime(ghostsData[0],ghostsData[1].length);
			oScr.removeChild(document.getElementById("fGauche"));
			oScr.removeChild(document.getElementById("fDroite"));
			oScr.removeChild(OPFace7);
			OPFace.style.display = "";
			gID = -1;
		}
	}
	oScr.appendChild(oPInput);

	var OPFace, OPFace7;
	if (ghostsData) {
		var OPFace = document.createElement("input");
		OPFace.type = "button";
		OPFace.value = toLanguage("Face with another player...", "Affronter un autre joueur...");
		OPFace.style.fontSize = (2*iScreenScale)+"px";
		OPFace.style.position = "absolute";
		OPFace.style.right = (2*iScreenScale)+"px";
		OPFace.style.top = (36*iScreenScale)+"px";
		OPFace.onclick = otherGhosts;
		oScr.appendChild(OPFace);
	}
	
	if (ghostsData) {
		oContainers[0].appendChild(oScr);
		document.body.style.cursor = "default";
		if (otherGhostsData) {
			gID = otherGhostsData.id;
			gTimes = otherGhostsData.times;
			showGhosts();
		}
	}
	else
		otherGhosts();

	updateMenuMusic(1);
}
function stripSpecialChars(s) {
	return s.replace(/&[#\w]+;/g, "_");
}
function mapNameOf(mScreenScale, mID) {
	var sMapName = lCircuits[mID];
	var oMapName = document.createElement("div");
	var mapFS = isCup ? Math.min(Math.max(9/Math.sqrt(stripSpecialChars(sMapName).length), 1.4), 4) : 2.1;
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
function displayCommands(html, showSettings) {
	var $commandes = document.getElementById("commandes");
	if ($commandes) {
		if (showSettings) {
			$commandes.innerHTML = html+'<img src="images/edit-controls.png" alt="Edit" id="commandes-edit" title="'+toLanguage("Edit controls","Modifier les contrôles")+'" />';
			document.getElementById("commandes-edit").onclick = function() {
				editCommands();
			};
		}
		else
			$commandes.innerHTML = html;
	}
}
function updateCommandSheet() {
	var gameCommands = getCommands();
	var isMac = navigator.platform.toUpperCase().indexOf('MAC')>=0;
	function aTouches(T1, T2) {
		return (oContainers.length == 1) ? T1 : "J1 : "+ T1 +"; J2 : "+ T2 +"";
	}
	function aKeyName(keyKey) {
		var keyCodes = gameCommands[keyKey];
		var keyCode = keyCodes[0];
		if (keyCodes[1] && isMac)
			keyCode = keyCodes[1];
		return getKeyName(keyCode);
	}
	displayCommands('<strong>'+ toLanguage('Move', 'Se diriger') +'</strong> : '+ aTouches(aKeyName("up")+aKeyName("left")+aKeyName("down")+aKeyName("right"), "ESDF") +'<br /><strong>'+ toLanguage('Use item', 'Utiliser un objet') +'</strong> : '+ aTouches(aKeyName("item"), toLanguage("A","Q")) +'<br /><strong>'+ toLanguage("Item backwards", "Objet en arrière") +'</strong> : '+ aTouches(aKeyName("item_back"), toLanguage("W", "A")) +'<br /><strong>'+ toLanguage('Jump/drift', 'Sauter/déraper') +'</strong> : '+ aTouches(aKeyName("jump"), "G") + ((course=="BB") ? ('<br /><strong>'+ toLanguage('Inflate a balloon', 'Gonfler un ballon') +'</strong> : '+ aTouches(aKeyName("balloon"), "R")):'') +'<br /><strong>'+ toLanguage('Rear/Front view', 'Vue arri&egrave;re/avant') +'</strong> : '+ aTouches(aKeyName("rear"), toLanguage("W","Z")) +'<br /><strong>'+ toLanguage('Pause', 'Mettre en pause') +'</strong> : '+ aKeyName("pause") +'<br /><strong>'+ toLanguage('Quit', 'Quitter') +'</strong> : '+ aKeyName("quit"),true);
}
function editCommands(reload) {
	var $controlEditorMask = document.getElementById("control-editor-mask");
	if ($controlEditorMask) {
		document.body.removeChild($controlEditorMask);
		if (!reload) {
			updateCommandSheet();
			return;
		}
	}
	$controlEditorMask = document.createElement("div");
	$controlEditorMask.id = "control-editor-mask";
	$controlEditorMask.onclick = function() {
		editCommands();
	};
	var $controlEditor = document.createElement("div");
	$controlEditor.className = "control-editor";
	$controlEditor.onclick = function(e) {
		e.stopPropagation();
	};
	var $controlHeader = document.createElement("div");
	$controlHeader.className = "control-header";
	var $controlTitle = document.createElement("div");
	$controlTitle.innerHTML = toLanguage("Edit controls", "Modifier les contrôles");
	$controlHeader.appendChild($controlTitle);
	var $controlClose = document.createElement("button");
	$controlClose.className = "control-close";
	$controlClose.innerHTML = "&times;";
	$controlClose.onclick = function() {
		editCommands();
	};
	$controlHeader.appendChild($controlClose);
	$controlEditor.appendChild($controlHeader);
	var commands = [{
		name: toLanguage("Move forward", "Avancer"),
		key: "up"
	}, {
		name: toLanguage("Move back", "Reculer"),
		key: "down"
	}, {
		name: toLanguage("Turn left", "Tourner à gauche"),
		key: "left"
	}, {
		name: toLanguage("Turn right", "Tourner à droite"),
		key: "right"
	}, {
		name: toLanguage("Use item", "Utiliser un objet"),
		key: "item"
	}, {
		name: toLanguage("Item backwards", "Objet en arrière"),
		key: "item_back"
	}, {
		name: toLanguage("Jump/drift", "Sauter/déraper"),
		key: "jump"
	}, {
		name: toLanguage("Inflate balloon", "Gonfler un ballon"),
		key: "balloon"
	}, {
		name: toLanguage("Rear view", "Vue arrière"),
		key: "rear"
	}, {
		name: toLanguage("Pause", "Pause"),
		key: "pause"
	}, {
		name: toLanguage("Quit", "Quitter"),
		key: "quit"
	}];
	var gameCommands = getCommands();
	var localControls = JSON.parse(localStorage.getItem("controls")||"{}");
	var isMac = (navigator.platform.toUpperCase().indexOf('MAC')>=0);
	var $controlEditorGrid = document.createElement("div");
	$controlEditorGrid.className = "control-editor-grid";
	for (var i=0;i<commands.length;i++) {
		(function(command) {
			var localControl = gameCommands[command.key];
			var keyCode = localControl[0];
			if (localControl[1] && isMac)
				keyCode = localControl[1];
			var $controlKey = document.createElement("div");
			var $controlLabel = document.createElement("div");
			$controlLabel.className = "control-label";
			$controlLabel.innerHTML = command.name;
			$controlKey.appendChild($controlLabel);
			var $controlInput = document.createElement("button");
			$controlInput.className = "control-input";
			$controlInput.innerHTML = getKeyName(keyCode);
			$controlInput.onfocus = function() {
				this.innerHTML = "...";
			};
			$controlInput.onblur = function() {
				this.innerHTML = getKeyName(keyCode);
			};
			$controlInput.onclick = function() {
				this.focus();
			};
			$controlInput.onkeydown = function(e) {
				e.preventDefault();
				e.stopPropagation();
				keyCode = e.keyCode;
				localControls[command.key] = keyCode;
				localStorage.setItem("controls", JSON.stringify(localControls));
				if (gameControls)
					gameControls = getGameControls();
				this.blur();
			};
			$controlKey.appendChild($controlInput);
			$controlEditorGrid.appendChild($controlKey);
		})(commands[i]);
	}
	$controlEditor.appendChild($controlEditorGrid);
	var $controlReset = document.createElement("div");
	$controlReset.className = "control-reset";
	var $controlResetBtn = document.createElement("a");
	$controlResetBtn.href = "#null";
	$controlResetBtn.innerHTML = toLanguage("Reset controls", "Rétablir les contrôles par défaut");
	$controlResetBtn.onclick = function() {
		if (confirm(toLanguage("Reset to default controls?","Confirmer la réinitialisation des contrôles ?"))) {
			localStorage.removeItem("controls");
			if (gameControls)
				gameControls = getGameControls();
			editCommands(true);
		}
		return false;
	};
	$controlReset.appendChild($controlResetBtn);
	$controlEditor.appendChild($controlReset);
	$controlEditorMask.appendChild($controlEditor);
	document.body.appendChild($controlEditorMask);
}
function getKeyName(keyCode) {
	if (!this.keyMatching)
		this.keyMatching = ["","","","Break","","","","","Backspace","Tab","","","Clear","Enter","","","Shift","Ctrl","Alt","Pause","CapsLock","Hangul","","","","Hanja","",toLanguage("Escape","Échap"),"Conversion","Non-conversion","","",toLanguage("Spacebar","Espace"),"PageUp","PageDown","End","Home","&larr;","&uarr;","&rarr;","&darr;","Select","Print","Execute",toLanguage("Print Screen","ImpEcr"),"Inser",toLanguage("Delete","Suppr"),"Help","0","1","2","3","4","5","6","7","8","9",":","=","&lt;","=","","SS","@","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","Meta","Meta","Meta","","Sleep","0","1","2","3","4","5","6","7","8","9","&times;","+",".","-",".","/","F1","F2","F3","F4","F5","F6","F7","F8","F9","F10","F11","F12","F13","F14","F15","F16","F17","F18","F19","F20","F21","F22","F23","F24","","","","","","","","","NumLock","ScrollLock","","","","","","","","","","","","","","","^","!","؛","#","$","Ù","PageDown","PageUp","Refresh",")","*","~","Home","-","Vol. down","Vol. up","Next","Previous","Stop","Play/pause","@","Mute","Vol. down","Vol. up","","","Ñ","=",",","#",".","/","%","°",",","","","","","","","","","","","","","","","","","","","","","","","","","{","\\","}","'","`","Meta","AltGr","&lt;","","","","Compose","Ç","","Forward","Back","Non-conversion","","","","","Alphanumeric","","Hiragana","Half-width","Kanji","","","","","","","Unlock Trackpad","","","","Toggle Touchpad"];
	if (this.keyMatching[keyCode])
		return this.keyMatching[keyCode];
	return "#"+keyCode;
}
function getCommands() {
	var defaultControls = {
		up:[38],
		down:[40],
		left:[37],
		right:[39],
		item:[32],
		item_back:[67],
		jump:[17,18],
		balloon:[16],
		rear:[88],
		pause:[80],
		quit:[27],
		cheat:[120,33,57,105],
		fastfwd:[118,36,55,103]
	};
	if (strPlayer.length > 1) {
		defaultControls["up_p2"] = [69];
		defaultControls["down_p2"] = [68];
		defaultControls["left_p2"] = [83];
		defaultControls["right_p2"] = [70];
		defaultControls["item_p2"] = [toLanguage(65,81)];
		defaultControls["item_back_p2"] = [toLanguage(87,65)];
		defaultControls["jump_p2"] = [71];
		defaultControls["balloon_p2"] = [82];
		defaultControls["rear_p2"] = [toLanguage(87,90)];
	}
	var res = defaultControls;
	var localControls = localStorage.getItem("controls");
	if (localControls) {
		localControls = JSON.parse(localControls);
		for (var key in localControls)
			res[key] = [localControls[key]];
	}
	return res;
}
function getGameControls() {
	var res = {};
	var commands = getCommands();
	for (var key in commands) {
		for (var i=0;i<commands[key].length;i++) {
			var iCommand = commands[key][i];
			if (!res[iCommand])
				res[iCommand] = key;
		}
	}
	return res;
}
function findKeyCode(action) {
	for (var key in gameControls) {
		if (gameControls[key] == action)
			return key;
	}
	return "";
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
	if (isSingle && !isOnline)
		choose(1);
	else if (course == "VS")
		selectMapScreen();
	else if (course == "BB") {
		if (isCup)
			selectRaceScreen(NBCIRCUITS);
		else
			selectMapScreen();
	}
	else
		loadMap(fInfos.map);
}
else {
	addOption("pQuality", toLanguage("Quality","Qualit&eacute;"),
	"vQuality", "quality", [
		[5, toLanguage("Pixelated","Pixelisé")],
		[4, toLanguage("Low","Inf&eacute;rieure")],
		[2, toLanguage("Medium","Moyenne")],
		[1, toLanguage("High","Sup&eacute;rieure")]
	], iRendering);
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
	selectMainPage();
	
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
			var $commandes = document.getElementById("commandes");
			if ($commandes) $commandes.style.display = "none";
		}
		window.turnEvents = true;
	}
	
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
function isChatting() {
	return isOnline && (document.activeElement == document.forms[1].elements["rMessage"]);
}
function applyButtonCode(action,keyData) {
	var keycodes = keyData.split(",");
	for (var i=0;i<keycodes.length;i++)
		document[action]({"keyCode":parseInt(keycodes[i])});
}
function onButtonTouch(e) {
	e.preventDefault();
	this.style.backgroundColor = "#603";
	navigator.vibrate(30);
	var keycode = this.dataset.key;
	applyButtonCode("onkeydown", keycode);
	return false;
}
function onButtonPress(e) {
	this.style.backgroundColor = "";
	applyButtonCode("onkeyup", this.dataset.key);
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
	var bConnectes = document.createElement("a");
	bConnectes.href = "#null";
	bConnectes.style.textDecoration = "none";
	bConnectes.title = language ? "Ignore player" : "Ignorer un joueur";
	bConnectes.style.marginLeft = "10px";
	bConnectes.style.opacity = 0.7;
	oConnectes.onmouseover = function() {
		bConnectes.style.opacity = 1;
	};
	oConnectes.onmouseout = function() {
		bConnectes.style.opacity = 0.7;
	};
	bConnectes.style.position = "relative";
	bConnectes.style.top = "2px";
	bConnectes.onmouseover = function() {
		biConnectes.src = "images/ic_block_h.png";
	};
	bConnectes.onmouseout = function() {
		biConnectes.src = "images/ic_block.png";
	};
	var oBlockDialog;
	function removeBlockDialog() {
		if (oBlockDialog) {
			oChat.removeChild(oBlockDialog);
			oBlockDialog = null;
			return true;
		}
		return false;
	}
	bConnectes.onclick = function() {
		if (removeBlockDialog()) return false;
		oBlockDialog = document.createElement("div");
		oBlockDialog.style.position = "absolute";
		oBlockDialog.style.left = "85px";
		oBlockDialog.style.top = "8%";
		oBlockDialog.style.width = "200px";
		oBlockDialog.style.border = "double 4px silver";
		oBlockDialog.style.backgroundColor = "#222";

		var oBlockTitle = document.createElement("h1");
		oBlockTitle.style.fontSize = "1.1em";
		oBlockTitle.style.marginTop = "24px";
		oBlockTitle.style.marginBottom = "2px";
		oBlockTitle.style.textAlign = "center";
		oBlockTitle.innerHTML = language ? "Ignore member":"Ignorer un membre";
		oBlockTitle.style.color = "white";
		oBlockTitle.style.textDecoration = "underline";
		oBlockDialog.appendChild(oBlockTitle);

		var oBlockClose = document.createElement("input");
		oBlockClose.type = "button";
		oBlockClose.onclick = function() {
			removeBlockDialog();
		};
		oBlockClose.style.position = "absolute";
		oBlockClose.style.right = "5px";
		oBlockClose.style.top = "5px";
		oBlockClose.value = "\xD7";
		oBlockDialog.appendChild(oBlockClose);

		var oBlockMembers = document.createElement("div");
		oBlockMembers.style.margin = "3px 4px";
		xhr("listCoursePlayers.php", "", function(reponse) {
			if (reponse) {
				try {
					var rCode = eval(reponse);
				}
				catch(e) {
					return false;
				}
				function stylishMember(oBlockMember) {
					if (oBlockMember.dataset.blocked) {
						oBlockMember.style.color = "red";
						oBlockMember.style.textDecoration = "line-through";
						oBlockMember.style.opacity = 0.8;
					}
					else {
						oBlockMember.style.color = "#F90";
						oBlockMember.style.textDecoration = "";
						oBlockMember.style.opacity = 1;
					}
				}
				for (var i=0;i<rCode.length;i++) {
					var memberId = rCode[i][0];
					var memberPseudo = rCode[i][1];
					var memberBlocked = rCode[i][2];
					var oBlockMember = document.createElement("div");
					if (!oBlockMember.dataset)
						oBlockMember.dataset = {};
					oBlockMember.dataset.id = memberId;
					oBlockMember.dataset.blocked = memberBlocked ? "1":"";
					oBlockMember.innerHTML = memberPseudo;
					oBlockMember.style.padding = "2px 5px";
					oBlockMember.style.cursor = "pointer";
					oBlockMember.style.margin = "1px";
					oBlockMember.style.backgroundColor = "#666";
					oBlockMember.style.color = oBlockMember.dataset.blocked ? "red":"#F90";
					oBlockMember.onmouseover = function() {
						this.style.backgroundColor = "#777";
						this.style.color = "#FC0";
					};
					oBlockMember.onmouseout = function() {
						this.style.backgroundColor = "#666";
						this.style.color = this.dataset.blocked ? "red":"#F90";
					};
					stylishMember(oBlockMember);
					oBlockMember.onclick = function() {
						this.dataset.blocked = this.dataset.blocked ? "":"1";
						var that = this;
						xhr(this.dataset.blocked ? "ignore.php":"unignore.php", "member="+ this.dataset.id, function(reponse) {
							if (reponse == 1) {
								stylishMember(that);
								return true;
							}
							return false;
						});
					}
					oBlockMembers.appendChild(oBlockMember);
				}
				return true;
			}
			return false;
		});
		oBlockDialog.appendChild(oBlockMembers);
		oChat.appendChild(oBlockDialog);

		return false;
	};
	var biConnectes = document.createElement("img");
	biConnectes.alt = "Block";
	biConnectes.src = "images/ic_block.png";
	biConnectes.style.height = "16px";
	bConnectes.appendChild(biConnectes);
	oConnectes.appendChild(bConnectes);
	
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
	rMessage.onkeydown = function(e) {
		e.stopPropagation();
	};
	rMessage.onkeyup = function(e) {
		e.stopPropagation();
	};
	rMessage.style.backgroundColor = "#FE7";
	var rEnvoi = document.createElement("input");
	rEnvoi.type = "submit";
	rEnvoi.value = toLanguage("Send", "Envoyer");
	rP.appendChild(rMessage);
	rP.appendChild(rEnvoi);
	oRepondre.onsubmit = function() {
		if (rMessage.value) {
			xhr("parler.php", "msg="+encodeURIComponent(rMessage.value).replace(/\+/g, "%2B"), function(reponse){return (reponse=="1")});
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
			xhr("chat.php", "", function(reponse) {
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