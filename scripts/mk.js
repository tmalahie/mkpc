var pause, chatting = false;
var aPlayers = new Array(), aPlaces = new Array(), aScores = new Array(), aTeams = new Array(), aPseudos = new Array();
var fInfos;
var formulaire;
var baseCp;
var customDecorData = {};
var nBasePersos, customPersos;
var selectedDifficulty;
var updateCtnFullScreen;
var isFirstLoad = true;
if (typeof edittingCircuit === 'undefined') {
	var edittingCircuit = false;
}
if (typeof noDS === 'undefined') {
	var noDS = false;
}
if (typeof cupOpts === 'undefined') {
	var cupOpts = {};
}
if (noDS) {
	var bListMaps = listMaps;
	listMaps = function() {
		NBCIRCUITS = 40;
		var aMaps = bListMaps();
		var res = {};
		for (var i=1;i<=NBCIRCUITS;i++)
			res["map"+i] = aMaps["map"+i];
		return res;
	};
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
	var clRewards = [];
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
	if (!isCup && !oMap.ext)
		oMap.ext = "png";
	oMap.ref = circuits.replace("map", "")*1;
	if (oMap.aipoints && oMap.aipoints[0] && oMap.aipoints[0].length && !Array.isArray(oMap.aipoints[0][0]))
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
var SPF = 67;
var iRendering = optionOf("quality");
var iQuality, iSmooth;
resetQuality();
var bMusic = !!optionOf("music");
var iSfx = !!optionOf("sfx");
var gameMenu;
var primaryColor = "#FEFF3F";

var refreshDatas = isOnline, finishing = false;
var connecte = 1;
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

function openFullscreen(elem) {
	if (elem.requestFullscreen) {
		return elem.requestFullscreen();
	} else if (elem.mozRequestFullScreen) { /* Firefox */
		return elem.mozRequestFullScreen();
	} else if (elem.webkitRequestFullscreen) { /* Chrome, Safari and Opera */
		return elem.webkitRequestFullscreen();
	} else if (elem.msRequestFullscreen) { /* IE/Edge */
		return elem.msRequestFullscreen();
	}
}

var $mkScreen = document.getElementById("mariokartcontainer");
if (!$mkScreen.dataset) $mkScreen.dataset = {};
updateCtnFullScreen = function(isFullScreen) {
	$mkScreen.dataset.fs = isFullScreen ? 1:"";
	if (isFullScreen) {
		var eRatio = Math.min(screen.width/(iWidth*iScreenScale),screen.height/(iHeight*iScreenScale));
		var eRatioP = Math.round(eRatio*100);
		var sWidth = $mkScreen.scrollWidth;
		var sZoom = $mkScreen.style.zoom;
		$mkScreen.style.zoom = eRatioP +"%";
		if ((sWidth != $mkScreen.scrollWidth) || (sZoom == $mkScreen.style.zoom) || (eRatioP == 100)) {
			for (var i=0;i<oContainers.length;i++) {
				oContainers[i].style.left = Math.round((screen.width/eRatio-iScreenScale*iWidth)/2 + i*(iWidth*iScreenScale+2)) +"px";
				oContainers[i].style.top = Math.round((screen.height/eRatio-iScreenScale*iHeight)/2) +"px";
			}
			updateHudFullScreen();
		}
		else  {
			$mkScreen.style.zoom = "";
			if (!formulaire || !formulaire.screenscale.disabled) {
				var eWidth = window.innerWidth-20, eHeight = window.innerHeight-20;
				var eRatio = Math.round(Math.min(eWidth/iWidth,eHeight/iHeight));
				$mkScreen.dataset.fakefs = 1;
				for (var i=0;i<oContainers.length;i++) {
					oContainers[i].style.left = Math.round((window.innerWidth-eRatio*iWidth)/2 + i*(iWidth*iScreenScale+2)) +"px";
					oContainers[i].style.top = Math.round((window.innerHeight-eRatio*iHeight)/2) +"px";
				}
				updateHudFullScreen();
				if (!$mkScreen.dataset.lastsc)
					$mkScreen.dataset.lastsc = iScreenScale;
				setScreenScale(eRatio, true);
				if (!window.nsevent) {
					window.nsevent = function(e) {
						if (e.keyCode == 27) {
							e.preventDefault();
							updateCtnFullScreen(false);
						}
					}
					window.addEventListener("keydown", window.nsevent);
				}
			}
		}
		$mkScreen.className = "fullscreen";
	}
	else {
		if ($mkScreen.dataset.fakefs && formulaire && formulaire.screenscale.disabled)
			return;
		$mkScreen.style.zoom = "";
		$mkScreen.className = "";
		if ($mkScreen.dataset.fakefs) {
			setScreenScale(+$mkScreen.dataset.lastsc, true);
			$mkScreen.dataset.lastsc = "";
			$mkScreen.dataset.fakefs = "";
			if (window.nsevent) {
				window.removeEventListener("keydown", window.nsevent);
				delete window.nsevent;
			}
		}
		for (var i=0;i<oContainers.length;i++) {
			oContainers[i].style.left = (10 + i*(iWidth*iScreenScale+2)) +"px";
			oContainers[i].style.top = "";
		}
		updateHudFullScreen();
	}
}
var hudScreens = new Array();
function updateHudFullScreen() {
	for (var i=0;i<hudScreens.length;i++) {
		hudScreens[i].style.left = oContainers[i].style.left;
		hudScreens[i].style.top = oContainers[i].style.top;
	}
}
function removeHUD() {
	for (var i=0;i<hudScreens.length;i++)
		$mkScreen.removeChild(hudScreens[i]);
	hudScreens.length = 0;
}

function setScreenScale(iValue, triggered) {
	if (iValue === iScreenScale) return;

	var aScreenScale = iScreenScale;

	if (iValue == -1) {
		formulaire.screenscale.value = aScreenScale;
		var p = openFullscreen($mkScreen);
		if (p.then && p.catch) {
			p.then(function() {
				updateCtnFullScreen(true);
				document.onfullscreenchange = function() {
					updateCtnFullScreen(document.fullscreenElement === $mkScreen);
				}
			}).catch(function() {
				updateCtnFullScreen(true);
			});
		}
		else
			updateCtnFullScreen(true);
		return;
	}
	else {
		iScreenScale = iValue;
		if (!triggered) {
			xhr("changeParam.php", "param=1&value="+ iValue, function(reponse) {
				return (reponse == 1);
			});
		}
	}
	if (bRunning)
		resetScreen();

	for (var i=0;i<oContainers.length;i++) {
		var oScr = oContainers[i].firstChild;
		if (oScr) {
			if (!oScr.aScreenScale)
				oScr.aScreenScale = aScreenScale;
			oScr.style.width = (iWidth*iScreenScale)+"px";
			oScr.style.height = (iHeight*iScreenScale)+"px";
			oScr.style.transformOrigin = oScr.style.WebkitTransformOrigin = oScr.style.MozTransformOrigin = "top left";
			oScr.style.transform = oScr.style.WebkitTransform = oScr.style.MozTransform = "scale("+ (iScreenScale/oScr.aScreenScale) +")";
		}
	}

	reposKeyboard();
	setSRest();
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
	if (muteOnBlur) {
		window.removeEventListener("blur", muteOnBlur);
		muteOnBlur = undefined;
	}
	if (unmuteOnResume) {
		window.removeEventListener("focus", unmuteOnResume);
		unmuteOnResume = undefined;
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
function clearResources() {
	if (oMapImg && oMapImg.clear)
		oMapImg.clear();
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
var muteOnBlur, unmuteOnResume;
function updateMenuMusic(menu, forceUpdate) {
	if ((menu != gameMenu) || forceUpdate) {
		gameMenu = menu;
		removeMenuMusic(!bMusic);
		if (bMusic) {
			playMusicSmoothly("musics/menu/"+ (gameMenu ? "selection-remix":"main-remix") +".mp3", forceUpdate?0:undefined);
			if (!gameMenu)
				loopAfterIntro(oMusicEmbed, 60.15,54.9);
			var cMusicEmbed = oMusicEmbed;
			muteOnBlur = function() {
				if (oMusicEmbed == cMusicEmbed) {
					clearTimeout(oMusicHandler);
					cMusicEmbed.pause();
				}
			};
			window.addEventListener("blur", muteOnBlur);
			unmuteOnResume = function() {
				if (oMusicEmbed == cMusicEmbed)
					cMusicEmbed.play();
			};
			window.addEventListener("focus", unmuteOnResume);
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

var itemDistribution;

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

var strPlayer = new Array();
var oMap;
var iDificulty = 5, iTeamPlay = selectedTeams;
var iRecord;
var iTrajet;
var jTrajets;
var iLapTimes;
var gPersos = new Array();
var gRecord;
var gSelectedPerso;
var gOverwriteRecord;
var selectedItemDistrib;
if (pause) {
	strPlayer = fInfos.player;
	selectedItemDistrib = fInfos.distribution;
	oMap = oMaps["map"+fInfos.map];
	clSelected = fInfos.cl;
	if (course != "CM")
		iDificulty = fInfos.difficulty;
	else {
		iTrajet = fInfos.my_route;
		gPersos = fInfos.perso;
		jTrajets = fInfos.cpu_route;
		gRecord = fInfos.my_record;
		gOverwriteRecord = fInfos.ow_record;
		if (gOverwriteRecord == 2) gOverwriteRecord = 1;
		iRecord = fInfos.record;
		iLapTimes = fInfos.lap_times;
		gSelectedPerso = fInfos.selPerso;
	}
}

var oMapImg;

function resetGame(strMap) {
	oMap = oMaps[strMap];
	loadMap();
}

var oPlanDiv,oPlanDiv2, oPlanCtn,oPlanCtn2, oPlanImg,oPlanImg2;

var oPlanWidth, oPlanSize, oPlanRealSize, oCharWidth, oObjWidth, oCoinWidth, oExpWidth, oExpBWidth;
var oPlanWidth2, oPlanSize2, oCharWidth2, oObjWidth2, oCoinWidth2, oExpWidth2, oExpBWidth2;
var oCharRatio, oPlanRatio;
var oPlanCharacters = new Array(), oPlanObjects = new Array(), oPlanCoins = new Array(), oPlanPoisons = new Array(), oPlanDecor = {}, oPlanAssets = {}, oPlanSea,
	oPlanFauxObjets = new Array(), oPlanBananes = new Array(), oPlanBobOmbs = new Array(), oPlanChampis = new Array(),
	oPlanCarapaces = new Array(), oPlanCarapacesRouges = new Array(), oPlanCarapacesBleues = new Array(),
	oPlanEtoiles = new Array(), oPlanBillballs = new Array(), oPlanTeams = new Array();
var oPlanCharacters2 = new Array(), oPlanObjects2 = new Array(), oPlanCoins2 = new Array(), oPlanDecor2 = {}, oPlanAssets2 = {}, oPlanSea2,
	oPlanFauxObjets2 = new Array(), oPlanBananes2 = new Array(), oPlanBobOmbs2 = new Array(), oPlanPoisons2 = new Array(), oPlanChampis2 = new Array(),
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
	function createObject(src, eltW, iPlanCtn) {
		var res = document.createElement("img");
		res.src = "images/map_icons/"+ src +".png";
		res.style.position = "absolute";
		res.style.width = eltW+"px";
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
				elts.push(createObject(src, eltW,iPlanCtn));
			while (elts.length > objs.length) {
				iPlanCtn.removeChild(elts[0]);
				elts.shift();
			}
		}
	}
	var fRelX = oPlayer.x/oPlanRealSize, fRelY = oPlayer.y/oPlanRealSize;
	oPlanCtn.style.transform = oPlanCtn.style.WebkitTransform = oPlanCtn.style.MozTransform = "translate("+ -Math.round(oPlanSize*(fRelX*fCosR - fRelY*fSinR) - oPlanWidth/2) +"px, "+ -Math.round(oPlanSize*(fRelX*fSinR + fRelY*fCosR) - oPlanWidth/2) +"px) rotate("+ fRotation +"deg)";

	function setAssetPos(iPlanAssets,iPlanCtn,iPlanSize) {
		for (var type in iPlanAssets) {
			if (oMap[type]) {
				if (iPlanAssets[type].length < oMap[type].length) {
					for (var i=iPlanAssets[type].length;i<oMap[type].length;i++) {
						var pointer = oMap[type][i];
						var iAssetWidth = pointer[1][2]*iPlanSize/oMap.w, iAssetHeight = pointer[1][3]*iPlanSize/oMap.w;
						var img = createObject(pointer[0].src, iAssetWidth,iPlanCtn);
						img.style.height = iAssetHeight+"px";
						img.style.transformOrigin = img.style.WebkitTransformOrigin = img.style.MozTransformOrigin = Math.round(pointer[2][0]*100)+"% "+Math.round(pointer[2][1]*100)+"%";
						iPlanAssets[type].push(img);
					}
				}
				for (var i=0;i<oMap[type].length;i++) {
					var pointer = oMap[type][i];
					var iAssetWidth = pointer[1][2]*iPlanSize/oMap.w;
					posImg(iPlanAssets[type][i], pointer[1][0]+pointer[1][2]*(0.5-pointer[2][0]),pointer[1][1]+pointer[1][2]/2-pointer[1][3]*pointer[2][1],Math.round((Math.PI-pointer[2][2])*180/Math.PI), iAssetWidth,iPlanSize);
				}
			}
		}
	}
	setAssetPos(oPlanAssets,oPlanCtn,oPlanSize);
	setAssetPos(oPlanAssets2,oPlanCtn2,oPlanSize2);

	function setSeaPos(iPlanSea,iPlanSize) {
		var oViewContext = iPlanSea.getContext("2d");
		oViewContext.clearRect(0, 0, iPlanSea.width, iPlanSea.height);
		oMap.sea.render(oViewContext,[0,0],iPlanSize/oMap.w);
	}
	if (oMap.sea) {
		setSeaPos(oPlanSea,oPlanSize);
		setSeaPos(oPlanSea2,oPlanSize2);
	}

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

	function setCoinPos(iPlanCoins,iObjWidth,iPlanCtn,iPlanSize) {
		if (iPlanCoins.length != oMap.coins.length) {
			syncObjects(iPlanCoins,oMap.coins,"coin", iObjWidth,iPlanCtn);
			for (var i=0;i<iPlanCoins.length;i++)
				posImg(iPlanCoins[i], oMap.coins[i].x,oMap.coins[i].y,Math.round(oPlayer.rotation), iObjWidth, iPlanSize);
		}
	}
	if (oMap.coins) {
		setCoinPos(oPlanCoins,oCoinWidth,oPlanCtn,oPlanSize);
		setCoinPos(oPlanCoins2,oCoinWidth2,oPlanCtn2,oPlanSize2);
	}

	function setDecorPos(iPlanDecor,iObjWidth,iPlanCtn,iPlanSize) {
		if (oMap.decor) {
			for (var type in oMap.decor) {
				var firstRun = !iPlanDecor[type].length;
				var decorBehavior = decorBehaviors[type];
				var decorExtra = getDecorExtra(decorBehavior);
				var customDecor = decorExtra.custom;
				if (!decorBehavior.hidden) {
					if ((oMap.decor[type].length!=iPlanDecor[type].length) || decorBehavior.movable) {
						var tObjWidth = iObjWidth;
						if (decorBehavior.size_ratio) tObjWidth *= decorBehavior.size_ratio.w;
						syncObjects(iPlanDecor[type],oMap.decor[type],type, tObjWidth,iPlanCtn);
						if (firstRun && customDecor) {
							for (var i=0;i<iPlanDecor[type].length;i++) {
								var iDecor = iPlanDecor[type][i];
								iDecor.src = "images/map_icons/empty.png";
							}
							(function(type,decorBehavior) {
								getCustomDecorData(customDecor, function(res) {
									tObjWidth = iObjWidth*decorBehavior.size_ratio.w;
									for (var i=0;i<iPlanDecor[type].length;i++) {
										var iDecor = iPlanDecor[type][i];
										iDecor.src = res.map;
										iDecor.style.width = tObjWidth +"px";
									}
									syncObjects(iPlanDecor[type],oMap.decor[type],type, tObjWidth,iPlanCtn);
								});
							})(type,decorBehavior);
						}
						var rotatable = decorBehavior.rotatable;
						var relY;
						if (rotatable) {
							var iPlanDecor0 = iPlanDecor[type][0];
							if (iPlanDecor0 && iPlanDecor0.naturalWidth)
								relY = Math.round(tObjWidth*(iPlanDecor0.naturalWidth-iPlanDecor0.naturalHeight)/(2*iPlanDecor0.naturalWidth));
						}
						for (var i=0;i<oMap.decor[type].length;i++) {
							if (rotatable)
								posImgRel(iPlanDecor[type][i], oMap.decor[type][i][0],oMap.decor[type][i][1],Math.round(oMap.decor[type][i][4]), tObjWidth,iPlanSize, 0,relY);
							else
								setObject(iPlanDecor[type][i],oMap.decor[type][i][0],oMap.decor[type][i][1], tObjWidth,iPlanSize);
						}
					}
				}
			}
		}
	}
	setDecorPos(oPlanDecor, oObjWidth, oPlanCtn, oPlanSize);
	setDecorPos(oPlanDecor2, oObjWidth2, oPlanCtn2, oPlanSize2);

	syncObjects(oPlanFauxObjets,items["fauxobjet"],"objet", oObjWidth,oPlanCtn);
	syncObjects(oPlanFauxObjets2,items["fauxobjet"],"objet", oObjWidth2,oPlanCtn2);
	for (var i=0;i<items["fauxobjet"].length;i++) {
		var fauxobjet = items["fauxobjet"][i];
		setObject(oPlanFauxObjets[i],fauxobjet.x,fauxobjet.y, oObjWidth,oPlanSize, fauxobjet.team,200);
		setObject(oPlanFauxObjets2[i],fauxobjet.x,fauxobjet.y, oObjWidth2,oPlanSize2, fauxobjet.team,200);
		oPlanFauxObjets[i].style.zIndex = oPlanFauxObjets2[i].style.zIndex = 2;
	}
	syncObjects(oPlanBananes,items["banane"],"banane", oObjWidth,oPlanCtn);
	syncObjects(oPlanBananes2,items["banane"],"banane", oObjWidth2,oPlanCtn2);
	for (var i=0;i<items["banane"].length;i++) {
		var banane = items["banane"][i];
		setObject(oPlanBananes[i],banane.x,banane.y, oObjWidth,oPlanSize, banane.team,100);
		setObject(oPlanBananes2[i],banane.x,banane.y, oObjWidth2,oPlanSize2, banane.team,100);
		oPlanBananes[i].style.zIndex = oPlanBananes2[i].style.zIndex = 2;
	}
	syncObjects(oPlanPoisons,items["poison"],"poison", oObjWidth,oPlanCtn);
	syncObjects(oPlanPoisons2,items["poison"],"poison", oObjWidth2,oPlanCtn2);
	for (var i=0;i<items["poison"].length;i++) {
		var poison = items["poison"][i];
		setObject(oPlanPoisons[i],poison.x,poison.y, oObjWidth,oPlanSize, poison.team,100);
		setObject(oPlanPoisons2[i],poison.x,poison.y, oObjWidth2,oPlanSize2, poison.team,100);
		oPlanPoisons[i].style.zIndex = oPlanPoisons2[i].style.zIndex = 2;
	}
	syncObjects(oPlanChampis,items["champi"],"champi", oObjWidth,oPlanCtn);
	syncObjects(oPlanChampis2,items["champi"],"champi", oObjWidth2,oPlanCtn2);
	for (var i=0;i<items["champi"].length;i++) {
		var champi = items["champi"][i];
		setObject(oPlanChampis[i],champi.x,champi.y, oObjWidth,oPlanSize, -1,100);
		setObject(oPlanChampis2[i],champi.x,champi.y, oObjWidth2,oPlanSize2, -1,100);
		oPlanChampis[i].style.zIndex = oPlanChampis2[i].style.zIndex = 2;
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
		syncObjects(iPlanBobOmbs,items["bobomb"],"bob-omb", iObjWidth,iPlanCtn);
		for (var i=0;i<items["bobomb"].length;i++) {
			var bobomb = items["bobomb"][i];
			if (bobomb.cooldown <= 0) {
				posImg(iPlanBobOmbs[i], bobomb.x,bobomb.y,Math.round(oPlayer.rotation), iExpWidth,iPlanSize).src = getExplosionSrc("explosion",bobomb.team);
				iPlanBobOmbs[i].style.width = iExpWidth +"px";
				iPlanBobOmbs[i].style.opacity = Math.max(1+bobomb.cooldown/10, 0);
				iPlanBobOmbs[i].style.background = "";
			}
			else
				setObject(iPlanBobOmbs[i],bobomb.x,bobomb.y, iObjWidth,iPlanSize, bobomb.team,100).style.zIndex = 2;
		}
	}
	setBobombPos(oPlanBobOmbs, oObjWidth,oPlanCtn, oPlanSize, oExpWidth);
	setBobombPos(oPlanBobOmbs2, oObjWidth2,oPlanCtn2, oPlanSize2, oExpWidth2);

	syncObjects(oPlanCarapaces,items["carapace"],"carapace", oObjWidth,oPlanCtn);
	syncObjects(oPlanCarapaces2,items["carapace"],"carapace", oObjWidth2,oPlanCtn2);
	for (var i=0;i<items["carapace"].length;i++) {
		var carapace = items["carapace"][i];
		setObject(oPlanCarapaces[i],carapace.x,carapace.y, oObjWidth,oPlanSize, carapace.team,200);
		setObject(oPlanCarapaces2[i],carapace.x,carapace.y, oObjWidth2,oPlanSize2, carapace.team,200).style.zIndex = 2;
	}

	syncObjects(oPlanCarapacesRouges,items["carapace-rouge"],"carapace-rouge", oObjWidth,oPlanCtn);
	syncObjects(oPlanCarapacesRouges2,items["carapace-rouge"],"carapace-rouge", oObjWidth2,oPlanCtn2);
	for (var i=0;i<items["carapace-rouge"].length;i++) {
		var carapaceRouge = items["carapace-rouge"][i];
		setObject(oPlanCarapacesRouges[i],carapaceRouge.x,carapaceRouge.y, oObjWidth,oPlanSize, carapaceRouge.team,200);
		setObject(oPlanCarapacesRouges2[i],carapaceRouge.x,carapaceRouge.y, oObjWidth2,oPlanSize2, carapaceRouge.team,200).style.zIndex = 2;
		if (carapaceRouge.owner)
			oPlanCarapacesRouges[i].style.zIndex = 2;
	}

	function setCarapacesBleuesPos(iPlanCarapacesBleues, iObjWidth,iPlanSize,iExpWidth,iPlanCtn) {
		syncObjects(iPlanCarapacesBleues,items["carapace-bleue"],"carapace-bleue",iObjWidth,iPlanCtn);
		for (var i=0;i<items["carapace-bleue"].length;i++) {
			var carapaceBleue = items["carapace-bleue"][i];
			if (carapaceBleue.cooldown <= 0) {
				posImg(iPlanCarapacesBleues[i], carapaceBleue.x,carapaceBleue.y,Math.round(oPlayer.rotation), iExpWidth,iPlanSize).src = getExplosionSrc("explosionB",carapaceBleue.team);
				iPlanCarapacesBleues[i].style.width = iExpWidth +"px";
				iPlanCarapacesBleues[i].style.opacity = Math.max(1+carapaceBleue.cooldown/10, 0);
				iPlanCarapacesBleues[i].style.background = "";
			}
			else
				setObject(iPlanCarapacesBleues[i],carapaceBleue.x,carapaceBleue.y, iObjWidth,iPlanSize, carapaceBleue.team,200).style.zIndex = 2;
		}
	}
	setCarapacesBleuesPos(oPlanCarapacesBleues, oObjWidth,oPlanSize,oExpBWidth,oPlanCtn);
	setCarapacesBleuesPos(oPlanCarapacesBleues2, oObjWidth2,oPlanSize2,oExpBWidth2,oPlanCtn2);

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

var gameSettings;
var oChallengeCpts;
function loadMap() {
	var mapSrc = isCup ? (complete ? oMap.img:"mapcreate.php"+ oMap.map):"images/maps/map"+oMap.map+"."+oMap.ext;
	gameSettings = localStorage.getItem("settings");
	gameSettings = gameSettings ? JSON.parse(gameSettings) : {};

	if (gameSettings.rtime) {
		cycle = function() {
			cycleHandler = setTimeout(cycle,SPF);
			runOneFrame();
		};
	}

	if (oMap.ext ? ("gif" === oMap.ext) : mapSrc.match(/\.gif$/g)) {
		if (gameSettings.nogif) {
			var oGif = new Image();
			oGif.onload = function() {
				oMapImg = document.createElement("canvas");
				oMapImg.width = oGif.naturalWidth;
				oMapImg.height = oGif.naturalHeight;
				var oMapCtx = oMapImg.getContext("2d");
				oMapCtx.drawImage(oGif, 0,0);
				startGame();
			};
			oGif.src = mapSrc;
		}
		else {
			oMapImg = GIF();
			oMapImg.onloadone = startGame;
			oMapImg.onloadall = function() {
				if (oPlanImg) oPlanImg.src = mapSrc;
				if (oPlanImg2) oPlanImg2.src = mapSrc;
			}
			oMapImg.load(mapSrc);
		}
	}
	else {
		oMapImg = new Image();
		oMapImg.onload = startGame;
		oMapImg.src = mapSrc;
	}
	oMap.assets = [];
	var assetKeys = ["oils","pivots","pointers", "flippers","bumpers","flowers"];
	for (var i=0;i<assetKeys.length;i++) {
		var key = assetKeys[i];
		if (oMap[key]) {
			function redrawAsset(asset) {
				var ctx = this.canvas.getContext("2d");
				if(ctx.resetTransform)
					ctx.resetTransform();
				else
					ctx.setTransform(1, 0, 0, 1, 0, 0);
				ctx.clearRect(0,0, this.canvas.width,this.canvas.height);
				var iW = asset[1][2], iH = asset[1][3];
				var cW = Math.max(iW,iH);
				var theta = asset[2][2];
				ctx.translate(cW/2,cW/2);
				ctx.rotate(theta);
				ctx.translate(-cW/2,-cW/2);
				try {
					ctx.drawImage(this.img, (cW-iW)/2,(cW-iH)/2, iW,iH);
				}
				catch (e) {
				}
				var cosTheta = Math.cos(theta), sinTheta = Math.sin(theta);
				var u = 0.5-asset[2][0], v = 0.5-asset[2][1];
				this.x = asset[1][0]-cW/2+u*iW*cosTheta-v*iH*sinTheta;
				this.y = asset[1][1]-cW/2+v*iH*cosTheta+u*iW*sinTheta;
			}
			function setupAsset(key,asset) {
				var canvas = document.createElement("canvas");
				canvas.width = canvas.height = Math.max(asset[1][2],asset[1][3]);
				var img = new Image();
				var src = "assets/"+asset[0];
				img.src = "images/map_icons/"+src+".png";
				var asset0 = {img:img,canvas:canvas,src:src,redraw:redrawAsset,x:asset[1][0],y:asset[1][1],w:asset[1][2],h:asset[1][3]};
				img.onload = function() {
					asset0.redraw(asset);
				};
				asset[0] = asset0;
				switch (key) {
				case "flippers":
					asset[3] = [0,16+Math.floor(Math.random()*16)];
				}
				oMap.assets.push(asset0);
			}
			for (var j=0;j<oMap[key].length;j++)
				setupAsset(key,oMap[key][j]);
		}
	}
	
	if (strPlayer.length > 1)
		updateCtnFullScreen(false);
	formulaire.screenscale.disabled = true;
	formulaire.quality.disabled = true;
	formulaire.music.disabled = true;
	formulaire.sfx.disabled = true;

	iTeamPlay = isTeamPlay();

	setSRest();
	document.body.style.cursor = "progress";
	for (var i=0;i<strPlayer.length;i++) {
		var	iScreenMore = i*(iWidth*iScreenScale+2);

		var hudScreen = document.createElement("div");
		hudScreen.style.position = "absolute";
		hudScreen.style.left = oContainers[i].style.left;
		hudScreen.style.top = oContainers[i].style.top;
		hudScreen.style.width = iWidth*iScreenScale +"px";
		hudScreen.style.height = iHeight*iScreenScale +"px";

		var oTemps = document.createElement("div");
		oTemps.id = "temps"+i;
		oTemps.style.right = Math.round(iScreenScale*0.6+1) +"px";
		oTemps.style.top = Math.round(iScreenScale*0.4+3) +"px";
		oTemps.style.fontSize = Math.round(iScreenScale*2.4) +"px";
		var shadowShift = Math.round(iScreenScale/8) +"px";
		var shadowShift2 = Math.round(iScreenScale/4) +"px";
		oTemps.style.textShadow = "-"+shadowShift2+" 0 black, 0 "+shadowShift2+" black, "+shadowShift2+" 0 black, 0 -"+shadowShift2+" black, -"+shadowShift+" -"+shadowShift+" black, -"+shadowShift+" "+shadowShift+" black, "+shadowShift+" -"+shadowShift+" black, "+shadowShift+" "+shadowShift+" black";
		hudScreen.appendChild(oTemps);

		var oCompteur = document.createElement("div");
		oCompteur.id = "compteur"+i;
		oCompteur.style.left = Math.round(iScreenScale/2) +"px";
		oCompteur.style.bottom = ((course != "BB") ? Math.round(iScreenScale/4) : Math.round(iScreenScale/4)) +"px";
		oCompteur.style.fontSize = Math.round(iScreenScale*2.4) +"px";
		if (!pause || !fInfos.replay) {
			if (course != "BB") {
				oCompteur.innerHTML = '<div></div><div class="glow"></div>';
				var oCompteurDivs = oCompteur.querySelectorAll("div");
				for (var j=0;j<oCompteurDivs.length;j++) {
					oCompteurDivs[j].style.height = Math.round(iScreenScale*2.4) +"px";
					oCompteurDivs[j].innerHTML = "<div>"+ (oMap.sections ? "SECTION":toLanguage("LAP","TOUR")) + ' <span class="tour">1</span>/'+ oMap.tours +"</div>";
					if (!j) {
						var shadowShift = Math.round(iScreenScale/8) +"px";
						var shadowShift2 = Math.round(iScreenScale/4) +"px";
						oCompteurDivs[j].style.textShadow = "-"+shadowShift2+" 0 black, 0 "+shadowShift2+" black, "+shadowShift2+" 0 black, 0 -"+shadowShift2+" black, -"+shadowShift+" -"+shadowShift+" black, -"+shadowShift+" "+shadowShift+" black, "+shadowShift+" -"+shadowShift+" black, "+shadowShift+" "+shadowShift+" black";
					}
				}
			}
			else
				updateBalloonHud(oCompteur,{reserve:4,team:aTeams[i]});
		}
		hudScreen.appendChild(oCompteur);

		var oDrift = document.createElement("div");
		oDrift.id = "drift"+i;
		var oDriftImg = document.createElement("img");
		oDriftImg.alt = ".";
		oDriftImg.src = "images/drift.png";
		oDriftImg.className = "driftimg pixelated";
		oDrift.appendChild(oDriftImg);
		hudScreen.appendChild(oDrift);

		var oObjet = document.createElement("div");
		oObjet.id = "objet"+i;
		if (!pause || !fInfos.replay) {
			oObjet.style.left = Math.round(iScreenScale) +"px";
			oObjet.style.top = Math.round(iScreenScale) +"px";
			oObjet.style.width = Math.round(iScreenScale * 26/3) +"px";
			oObjet.style.height = Math.round(iScreenScale * 18/3) +"px";
			oObjet.style.visibility = "visible";
		}
		var oRoulette = document.createElement("div");
		oRoulette.id = "roulette"+i;
		oObjet.appendChild(oRoulette);
		hudScreen.appendChild(oObjet);

		var lakitu = document.createElement("div");
		lakitu.id = "lakitu"+i;
		lakitu.className = "pixelated";
		lakitu.innerHTML = "<div></div>";
		lakitu.style.width = iScreenScale * 9 +"px";
		lakitu.style.height = Math.round(iScreenScale*6.6) +"px";
		lakitu.style.fontSize = Math.round(iScreenScale*2.3) +"px";
		hudScreen.appendChild(lakitu);
		oDriftImg.style.width = iScreenScale * 8 +"px";
		oDrift.style.left = (iScreenScale * 36) +"px";
		oDrift.style.top = Math.round(iScreenScale*32) +"px";
		oDriftImg.style.left = "0px";
		oDriftImg.style.top = "0px";
		var infoPlace = document.createElement("div");
		infoPlace.id = "infoPlace"+i;
		infoPlace.style.right = Math.round(iScreenScale/2) +"px";
		infoPlace.style.bottom = 0 +"px";
		infoPlace.style.fontSize = iScreenScale * 8 +"px";
		hudScreen.appendChild(infoPlace);
		var oInfos = document.getElementById("infos"+i);
		if (!oInfos) {
			oInfos = document.getElementById("infos0").cloneNode(true);
			oInfos.id = "infos"+i;
			$mkScreen.appendChild(oInfos);
		}
		oInfos.style.left = (10+iScreenMore) +"px";
		oInfos.style.top = (10*iScreenScale) +"px";
		oInfos.style.width = (iWidth*iScreenScale) +"px";
		oInfos.style.fontSize = iScreenScale*16 +"px";
		oInfos.style.fontFamily = '"NSMBU", Impact';
		oInfos.style.textAlign = "center";
		oInfos.style.textStroke = oInfos.style.WebkitTextStroke = oInfos.style.MozTextStroke = Math.round(iScreenScale/4) +"px "+ primaryColor;
		oInfos.style.visibility = "hidden";
		oInfos.style.display = "";
		oInfos.innerHTML = '<tr><td id="decompte'+i+'">3</td></tr>';
		var oScroller = document.getElementById("scroller").cloneNode(true);
		oScroller.id = "scroller"+i;
		var oScrollPadding = 1;
		oScroller.style.left = Math.round(iScreenScale) +"px";
		oScroller.style.top = Math.round(iScreenScale + iScreenScale*oScrollPadding) +"px";
		oScroller.style.width = Math.round(iScreenScale * 26/3) +"px";
		oScroller.style.height = Math.round(iScreenScale * 18/3 - iScreenScale*2*oScrollPadding) +"px";
		oScroller.style.lineHeight = iScreenScale +"px";
		hudScreen.appendChild(oScroller);

		$mkScreen.appendChild(hudScreen);

		hudScreens[i] = hudScreen;
	}

	oChallengeCpts = document.createElement("div");
	oChallengeCpts.id = "challenge-cpts";
	oChallengeCpts.style.right = Math.round(iScreenScale*0.85) +"px";
	oChallengeCpts.style.top = Math.round(iScreenScale*3.2) +"px";
	oChallengeCpts.style.fontSize = Math.round(iScreenScale*1.9) +"px";
	oChallengeCpts.style.visibility = "hidden";
	hudScreen.appendChild(oChallengeCpts);

	initMap();

	var lObjet = Math.round(iScreenScale*4);
	for (var j=0;j<document.getElementsByClassName("aObjet").length;j++)
		document.getElementsByClassName("aObjet")[j].style.height = lObjet +"px";

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
	if (oMap.pivots) {
		for (var i=0;i<oMap.pivots.length;i++) {
			var pivot = oMap.pivots[i][1];
			var x = pivot[0], y = pivot[1], w = Math.round(pivot[2]/2), h = Math.round(pivot[3]/2);
			oMap.collision.polygon.push([[x-w,y],[x,y-h],[x+w,y],[x,y+h]]);
		}
	}
	if (oMap.horspistes) {
		for (var type in oMap.horspistes)
			oMap.horspistes[type] = classifyByShape(oMap.horspistes[type]);
	}
	if (oMap.flowers) {
		for (var i=0;i<oMap.flowers.length;i++) {
			var flower = oMap.flowers[i][1];
			var x = flower[0], y = flower[1], w = Math.round(flower[2]/2), h = Math.round(flower[3]/2);
			oMap.horspistes.herbe.rectangle.push([x-w,y-w,2*w,2*h]);
		}
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
	if (oMap.flows) {
		var flows = {rectangle:[],polygon:[]};
		for (var i=0;i<oMap.flows.length;i++) {
			var flow = oMap.flows[i];
			flows[getShapeType(flow[0])].push(flow);
		}
		oMap.flows = flows;
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
	if (oMap.sauts) {
		for (var i=0;i<oMap.sauts.length;i++) {
			var oBox = oMap.sauts[i];
			if (oBox.length < 5)
				oBox[4] = (oBox[2]+oBox[3])/45+1;
		}
	}
	if (oMap.cannons) {
		var cannons = {rectangle:[],polygon:[]};
		for (var i=0;i<oMap.cannons.length;i++) {
			var cannon = oMap.cannons[i];
			cannons[getShapeType(cannon[0])].push(cannon);
		}
		oMap.cannons = cannons;
	}
	if (oMap.sea) {
		var oWaves = oMap.sea.waves;
		oMap.sea.projections = [];
		for (var i=0;i<oWaves.length;i++) {
			var oWave1 = oWaves[i][0], oWave2 = oWaves[i][1];
			var oProjections = [];
			var lastInc = 0;
			for (var j=0;j<oWave2.length;j++) {
				var ptX = oWave2[j][0], ptY = oWave2[j][1];
				var l;
				var minX, minY, minInc, minDist = Infinity;
				for (var k=0;k<oWave1.length;k++) {
					var inc = k, inc2 = (k+1)%oWave1.length;
					l = projete(ptX,ptY, oWave1[inc][0],oWave1[inc][1],oWave1[inc2][0],oWave1[inc2][1]);
					if (l > 1) l = 1;
					if (l < 0) l = 0;
					var hX = oWave1[inc][0] + l*(oWave1[inc2][0] - oWave1[inc][0]), hY = oWave1[inc][1] + l*(oWave1[inc2][1]-oWave1[inc][1]);
					var d = (hX-ptX)*(hX-ptX) + (hY-ptY)*(hY-ptY);
					if (d < minDist) {
						minDist = d;
						minX = hX;
						minY = hY;
						minInc = inc;
					}
				}
				if (minInc < lastInc)
					minInc += oWave1.length;
				for (var k=lastInc;k<minInc;k++) {
					var inc2 = (k+1)%oWave1.length;
					oProjections.push([ptX,ptY,oWave1[inc2][0],oWave1[inc2][1]]);
				}
				lastInc = minInc%oWave1.length;
				oProjections.push([ptX,ptY,minX,minY]);
			}
			oMap.sea.projections.push(oProjections);
		}
		function getWavePtProjected(oProjection, l) {
			return [oProjection[2] + (oProjection[0]-oProjection[2])*l, oProjection[3] + (oProjection[1]-oProjection[3])*l];
		}
		oMap.sea.progress = 0.9999;
		oMap.sea.offroad0 = oMap.horspistes.eau.polygon;
		oMap.sea.drawPolygon = function(oViewContext, oPolygon, center,scale) {
			oViewContext.beginPath();
			for (var j=0;j<oPolygon.length;j++) {
				var pt = oPolygon[j];
				var proj = [(pt[0]-center[0])*scale, (pt[1]-center[1])*scale];
				if (j)
					oViewContext.lineTo(proj[0],proj[1]);
				else
					oViewContext.moveTo(proj[0],proj[1]);
			}
			oViewContext.closePath();
			oViewContext.fill();
			oViewContext.stroke();
		}
		oMap.sea.render = function(oViewContext, center,scale) {
			var waveProgress = this.progress;
			var oWaves = this.waves;
			var waterL = waveProgress*0.99;
			var waveL = waveProgress-0.25*(1-waveProgress/2);
			var foamL = waveProgress-0.04*(1-waveProgress/3);
			oViewContext.lineWidth = 1;
			if (waveL > 0) {
				oViewContext.fillStyle = oViewContext.strokeStyle = this.colors.water;
				for (var i=0;i<oWaves.length;i++) {
					var oPolygon = this.polygon(i,0,waterL);
					this.drawPolygon(oViewContext, oPolygon, center,scale);
				}
			}
			else
				waveL = 0;
			if (foamL > 0) {
				oViewContext.fillStyle = oViewContext.strokeStyle = this.colors.wave;
				for (var i=0;i<oWaves.length;i++) {
					var oPolygon = this.polygon(i,waveL,waterL);
					this.drawPolygon(oViewContext, oPolygon, center,scale);
				}
			}
			else
				foamL = 0;
			if (waveProgress > 0.005) {
				oViewContext.fillStyle = oViewContext.strokeStyle = this.colors.foam;
				for (var i=0;i<oWaves.length;i++) {
					var oPolygon = this.polygon(i,foamL,waveProgress*1.04);
					this.drawPolygon(oViewContext, oPolygon, center,scale);
				}
			}
		}
		oMap.sea.polygon = function(i, l1,l2) {
			var oWave = this.waves[i][0];
			var res = [], pt0;
			var oProjections = this.projections[i];
			if (l1) {
				pt0 = getWavePtProjected(oProjections[0], l1);
				res.push(pt0);
				for (var k=0;k<oProjections.length;k++) {
					var ptK = getWavePtProjected(oProjections[k], l1);
					res.push(ptK);
				}
				res.push(pt0);
			}
			else {
				pt0 = oWave[0];
				res.push(pt0);
				for (var k=1;k<oWave.length;k++) {
					var ptK = oWave[k];
					res.push(ptK);
				}
				res.push(pt0);
			}
			var kLast = oProjections.length-1;
			pt0 = getWavePtProjected(oProjections[kLast], l2);
			res.push(pt0);
			for (var k=kLast-1;k>=0;k--) {
				var ptK = getWavePtProjected(oProjections[k], l2);
				res.push(ptK);
			}
			res.push(pt0);
			return res;
		}
	}
}
var timer = 0;
var timerMS;
var lapTimers = new Array();
iScreenScale = optionOf("screenscale");

var oLapTimeDiv;

var fMaxRotInc = 6;
var fTurboDriftCpt = 80, fTurboDriftCpt2 = 160;

function throwItem(oKart,newItem,k) {
	var lastItem = oKart.using[k||0];
	if (lastItem) {
		for (var key in newItem)
			lastItem[key] = newItem[key];
		if (isOnline)
			syncItems.push(lastItem);
		oKart.using.shift();
	}
}
function loadNewItem(kart,item) {
	addNewItem(kart,item);
	kart.using.push(item);
}
function addNewItem(kart,item) {
	var collection = item.type;
	var itemBehavior = itemBehaviors[collection];
	if (itemBehavior.sprite !== false) {
		item.sprite = new Sprite(collection);
		item.size = itemBehavior.size;
	}
	items[collection].push(item);
	if (kart == oPlayers[0]) {
		if (isOnline)
			syncItems.push(item);
		else if (clLocalVars.myItems)
			clLocalVars.myItems.push(item);
	}
	if (item.team != -1) {
		var hallowSize;
		switch (collection) {
		case "champi":
			break;
		case "banane":
			hallowSize = 50;
			break;
		case "poison":
			hallowSize = 60;
			break;
		case "carapace":
		case "carapace-rouge":
			hallowSize = 60;
			break;
		case "fauxobjet":
			hallowSize = 65;
			break;
		case "carapace-bleue":
			hallowSize = 60;
			break;
		case "bobomb":
			hallowSize = 40;
			break;
		default:
			hallowSize = 60;
		}
		if (hallowSize) {
			var hallowLeft = 50-hallowSize, hallowTop = 50-hallowSize;
			switch (collection) {
			case "banane":
				hallowTop += 5;
				break;
			case "poison":
				hallowTop += 5;
				break;
			case "bobomb":
				hallowTop += 5;
				break;
			case "fauxobjet":
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
				oDiv.style.backgroundColor = item.team ? "red":"blue";
				oDiv.style.opacity = 0.25;
				if (item.sprite) {
					var oImg = item.sprite[i].div.firstChild;
					if (oImg)
						item.sprite[i].div.insertBefore(oDiv,oImg);
					else
						item.sprite[i].div.appendChild(oDiv);
				}
			}
		}
	}
}

function arme(ID, backwards) {
	var oKart = aKarts[ID];
	if (!oKart.using.length) {
		if (oKart.roulette != 25) return;
		if (oKart == oPlayers[0])
			clLocalVars.itemsUsed = true;
		var tpsUse, itemKey = oKart.arme;
		switch(oKart.arme) {
			case "champi" :
			case "champiX2" :
			case "champiX3" :
			itemKey = "champi";
			tpsUse = 20;
			oKart.maxspeed = 11;
			oKart.speed = 11;
			playIfShould(oKart,"musics/events/boost.mp3");
			break;

			case "champior" :
			itemKey = "champi";
			if (oKart.champi < 12) {
				tpsUse = 20;
				oKart.maxspeed = 11;
				oKart.speed = 11;
				playIfShould(oKart,"musics/events/boost.mp3");
				if (!oKart.champior)
					oKart.champior = 70;
			}
			break;

			case "etoile" :
			tpsUse = 80;
			for (var i=0;i<strPlayer.length;i++)
				oKart.sprite[i].img.src = getStarSrc(oKart.personnage);
			if (!oKart.cpu && !oKart.etoile) {
				if (!isOnline) {
					oKart.sprite[0].img.onload = function() {
						bCounting = false;
						this.onload = undefined;
						reprendre(false);
					}
					interruptGame();
					bCounting = true;
				}
				if (shouldPlaySound(oKart) && !oPlayers[1])
					postStartMusic("musics/events/starman.mp3");
			}
			if (oKart.speedinc > 0)
				oKart.speedinc *= 5;
			delete oKart.shift;
			oKart.protect = true;
			break;

			case "billball" :
			tpsUse = Math.max(Math.min(Math.round(distanceToFirst(oKart)/9), 120), 40);
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
				interruptGame();
				bCounting = true;
			}
			oKart.rotinc = 0;
			oKart.size = 2.5;
			oKart.mini = 0;
			oKart.z = 2;
			oKart.protect = true;
			oKart.champi = 0;
			delete oKart.shift;
			resetPowerup(oKart);
			playIfShould(oKart,"musics/events/boost.mp3");
			stopDrifting(ID);
			break;

			case "megachampi" :
			tpsUse = 80;
			oKart.size = 1;
			oKart.mini = 0;
			updateDriftSize(ID);
			oKart.protect = true;
			if (!oKart.megachampi && shouldPlaySound(oKart) && !oPlayers[1])
				postStartMusic("musics/events/megamushroom.mp3");
			break;

			case "banane" :
			loadNewItem(oKart, {type: "banane", team:oKart.team, x:(oKart.x-5*direction(0,oKart.rotation)), y:(oKart.y-5*direction(1,oKart.rotation)), z:oKart.z});
			playIfShould(oKart,"musics/events/item_store.mp3");
			break;

			case "bananeX3" :
			for (var i=0;i<3;i++)
				loadNewItem(oKart, {type: "banane", team:oKart.team, x:(oKart.x-5*direction(0,oKart.rotation)), y:(oKart.y-5*direction(1,oKart.rotation)), z:oKart.z});
			oKart.rotitem = 0;
			playIfShould(oKart,"musics/events/item_store.mp3");
			break;

			case "fauxobjet" :
			loadNewItem(oKart, {type: "fauxobjet", team:oKart.team, x:(oKart.x-5*direction(0, oKart.rotation)), y:(oKart.y-5*direction(1, oKart.rotation)), z:oKart.z});
			playIfShould(oKart,"musics/events/item_store.mp3");
			break;

			case "poison" :
			loadNewItem(oKart, {type: "poison", team:oKart.team, x:(oKart.x-5*direction(0,oKart.rotation)), y:(oKart.y-5*direction(1,oKart.rotation)), z:oKart.z});
			playIfShould(oKart,"musics/events/item_store.mp3");
			break;

			case "carapace" :
			loadNewItem(oKart, {type: "carapace", team:oKart.team, x:(oKart.x-5*direction(0, oKart.rotation)), y:(oKart.y-5*direction(1, oKart.rotation)), z:oKart.z, vx:0, vy:0, owner: -1, lives:10});
			playIfShould(oKart,"musics/events/item_store.mp3");
			break;

			case "carapaceX3" :
			for (var i=0;i<3;i++)
				loadNewItem(oKart, {type: "carapace", team:oKart.team, x:(oKart.x-5*direction(0, oKart.rotation)), y:(oKart.y-5*direction(1, oKart.rotation)), z:oKart.z, vx:0, vy:0, owner: -1, lives:10});
			oKart.rotitem = 0;
			playIfShould(oKart,"musics/events/item_store.mp3");
			break;

			case "carapacerouge" :
			loadNewItem(oKart, {type: "carapace-rouge", team:oKart.team, x:(oKart.x-5*direction(0, oKart.rotation)), y:(oKart.y-5*direction(1, oKart.rotation)), z:oKart.z, theta:-1, owner:-1, aipoint:-1, aimap:-1, target:-1});
			playIfShould(oKart,"musics/events/item_store.mp3");
			break;

			case "carapacerougeX3" :
			for (var i=0;i<3;i++)
				loadNewItem(oKart, {type: "carapace-rouge", team:oKart.team, x:(oKart.x-5*direction(0, oKart.rotation)), y:(oKart.y-5*direction(1, oKart.rotation)), z:oKart.z, theta:-1, owner:-1, aipoint:-1, aimap:-1, target:-1});
			oKart.rotitem = 0;
			playIfShould(oKart,"musics/events/item_store.mp3");
			break;

			case "carapacebleue" :
			var minDist = Infinity, minAiPt = 0, minAiMap = 0;
			if (course != "BB") {
				for (var i=0;i<oMap.aipoints.length;i++) {
					var aipoints = oMap.aipoints[i];
					for (var j=0;j<aipoints.length;j++) {
						var aipoint = aipoints[j];
						var lastAipoint = aipoints[(j?j:aipoints.length)-1];
						var dist = Math.hypot(aipoint[0]-oKart.x,aipoint[1]-oKart.y);
						var isFront = ((aipoint[0]-oKart.x)*(aipoint[0]-lastAipoint[0]) + (aipoint[1]-oKart.y)*(aipoint[1]-lastAipoint[1]) > 0);
						if (!isFront)
							dist += oMap.w+oMap.h;
						var demitour = oKart.demitours+1;
						if (demitour >= oMap.checkpoint.length)
							demitour = 0;
						var nextCp = oMap.checkpoint[demitour];
						if (nextCp) {
							var cpX = nextCp[0] + (nextCp[3] ? Math.round(nextCp[2]/2) : 8);
							var cpY = nextCp[1] + (nextCp[3] ? 8 : Math.round(nextCp[2]/2));
							var ddist = Math.hypot(cpX-oKart.x,cpY-oKart.y)*Math.hypot(aipoint[0]-lastAipoint[0],aipoint[1]-lastAipoint[1]);
							if (ddist)
								dist -= 150*((cpX-oKart.x)*(aipoint[0]-lastAipoint[0]) + (cpY-oKart.y)*(aipoint[1]-lastAipoint[1]))/ddist;
						}
						if (dist < minDist) {
							minAiMap = i;
							minAiPt = j;
							minDist = dist;
						}
					}
				}
			}
			addNewItem(oKart, {type: "carapace-bleue", team:oKart.team, x:oKart.x,y:oKart.y,z:15, target:-1, aipoint:minAiPt, aimap:minAiMap, cooldown:itemBehaviors["carapace-bleue"].cooldown0});
			playDistSound(oKart,"musics/events/throw.mp3",50);
			break;

			case "bobomb" :
			loadNewItem(oKart, {type: "bobomb", team:oKart.team, x:(oKart.x-5*direction(0,oKart.rotation)), y:(oKart.y-5*direction(1,oKart.rotation)), z:oKart.z,theta:-1,countdown:15,cooldown:30});
			playIfShould(oKart,"musics/events/item_store.mp3");
			break;

			case "eclair" :
			addNewItem(oKart, {type:"eclair", owner:oKart.id});
			break;

			case "bloops" :
			addNewItem(oKart, {type:"bloops", owner:oKart.id});
			break;
		}

		if (tpsUse)
			oKart[itemKey] = tpsUse;

		var newItem;
		switch(oKart.arme) {
		case "champiX2":
			newItem = "champi";
			break;
		case "champiX3":
			newItem = "champiX2";
			break;
		case "champior":
			newItem = "champior";
		}
		if (newItem) {
			if (oKart.arme !== newItem) {
				oKart.arme = newItem;
				if (kartIsPlayer(oKart))
					updateObjHud(ID);
			}
			else {
				if (kartIsPlayer(oKart)) {
					if (newItem === "champior") {
						var $img = document.getElementById("roulette"+ID).getElementsByTagName("img")[0];
						var t = 0;
						function rescale() {
							t++;
							var tx = (t-3)/3;
							var scale = 0.8 + 0.2*tx*tx;
							if (scale >= 1) {
								$img.style.transform = "";
								return;
							}
							$img.style.transform = "scale("+scale+")";
							setTimeout(rescale, SPF);
						}
						rescale();
					}
				}
			}
		}
		else
			supprArme(ID);
	}
	else {
		var posX = oKart.x;
		var posY = oKart.y;

		switch(oKart.using[0].type) {
			case "banane" :
			var decalage = 30/(oKart.speed+5);
			var fPosX = posX - decalage * direction(0, oKart.rotation);
			var fPosY = posY - decalage * direction(1, oKart.rotation);
			if (!tombe(fPosX,fPosY))
			throwItem(oKart, {x:fPosX, y:fPosY, z:0});
			playIfShould(oKart,"musics/events/put.mp3");
			break;

			case "fauxobjet" :
			var decalage = 30/(oKart.speed+5);
			throwItem(oKart, {x:posX-decalage*direction(0, oKart.rotation), y:posY-decalage*direction(1, oKart.rotation), z:0});
			playIfShould(oKart,"musics/events/put.mp3");
			break;

			case "poison" :
			var decalage = 30/(oKart.speed+5);
			var fPosX = posX - decalage * direction(0, oKart.rotation);
			var fPosY = posY - decalage * direction(1, oKart.rotation);
			if (!tombe(fPosX,fPosY))
			throwItem(oKart, {x:fPosX, y:fPosY, z:0});
			playIfShould(oKart,"musics/events/put.mp3");
			break;

			case "carapace" :
			var uDir = dirShoot(oKart, backwards, 3.2);
			var oAngleView = angleShoot(oKart, backwards);
			var uX = uDir[0], uY = uDir[1], uL = Math.hypot(uX,uY);
			if (!uL) uL = 1;
			var shiftDist = backwards?7.5:(6+uL);
			if (oKart.using.length > 1)
				shiftDist += 5;
			throwItem(oKart, {x:posX+shiftDist*uX/uL,y:posY+shiftDist*uY/uL,z:0,vx:uX,vy:uY,owner:oKart.id,lives:10});
			playDistSound(oKart,"musics/events/throw.mp3",50);
			break;

			case "carapace-rouge" :
			var oAngleView = angleShoot(oKart, backwards);
			var shiftDist = 7.5;
			if (backwards) {
				if (oKart.using.length > 1)
					shiftDist *= 1.5;
			}
			else {
				shiftDist *= 1.5;
				shiftDist += oKart.speed;
				if (oKart.using.length > 1)
					shiftDist *= 4/3;
			}
			if (backwards)
				throwItem(oKart, {x:posX+shiftDist*direction(0,oAngleView),y:posY+shiftDist*direction(1,oAngleView),z:0,theta:oAngleView,owner:oKart.id,aipoint:-2,aimap:-1,target:-1});
			else
				throwItem(oKart, {x:posX+shiftDist*direction(0, oAngleView), y:posY+shiftDist*direction(1,oAngleView),z:0,theta:oAngleView,owner:oKart.id,aipoint:-1,aimap:-1,target:-1});
			playDistSound(oKart,"musics/events/throw.mp3",50);
			break;

			case "bobomb" :
			var oAngleView = angleShoot(oKart, backwards);
			if (backwards)
				throwItem(oKart, {x:posX+5*direction(0,oAngleView),y:posY+5*direction(1,oAngleView),z:0,theta:oAngleView,countdown:2,cooldown:42});
			else
				throwItem(oKart, {x:posX,y:posY,z:0,theta:oAngleView,countdown:15,cooldown:30});
			playDistSound(oKart,"musics/events/throw.mp3",50);
			break;

			default :
			return;
		}
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
	if (elt) {
		setTimeout(function() {
			if ((oMusicEmbed == cMusicEmbed) || !oMusicEmbed) {
				fadeInMusic(elt,0.2,ratio);
				unpauseMusic(elt);
			}
		}, 500);
	}
}
function stopStarMusic(oKart) {
	if (shouldPlaySound(oKart) && !oPlayers[1])
		postResumeMusic(mapMusic, 0.9);
}
function stopMegaMusic(oKart) {
	if (shouldPlaySound(oKart) && !oPlayers[1])
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
	
	var nbKarts = strPlayer.length+aPlayers.length;
	if (!isOnline) {
		if (course == "BB") {
			for (var i=0;i<nbKarts;i++)
				aPlaces[i] = i+1;
		}
		else if (course == "CM")
			aPlaces = [1];
	}

	var cp0 = oMap.sections ? oMap.checkpoint.length-1:0;
	var rot0 = (oMap.startrotation==undefined)?180:oMap.startrotation;
	var dir0 = oMap.startdirection||0;

	var startRotation = rot0*Math.PI/180;
	var cosStart = Math.cos(startRotation), sinStart = Math.sin(startRotation);
	var wKarts = 27, hKarts = 130;
	var nbKartsPerLine = Math.max(2,Math.round((1+Math.sqrt((hKarts+4*(nbKarts-1)*wKarts)/hKarts))/2));
	function posKart(oKart, oPlace) {
		var shiftX = (((oPlace+1)%nbKartsPerLine)-(nbKartsPerLine-1)/2)*wKarts/(nbKartsPerLine-0.5), shiftY = oPlace*Math.min(12,hKarts/nbKarts);
		if (!dir0)
			shiftX = -shiftX;
		shiftX += 9;
		oKart.x -= shiftX*cosStart + shiftY*sinStart;
		oKart.y += shiftX*sinStart - shiftY*cosStart;
	}
	function realKartStats(normalizedStats) {
		return {
			acceleration: 0.2+Math.pow(normalizedStats[0],2)*0.8,
			speed: 0.875+normalizedStats[1]*0.25,
			handling: 0.2+normalizedStats[2]*0.8,
			mass: 0.5+normalizedStats[3]
		}
	}
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
			stats : realKartStats(cp[strPlayer[i]]),

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
			maxspeed : 5.7,
			
			driftinc : 0,
			driftcpt : 0,
			drift : 0,
			turbodrift : 0,
			jumped : false,

			champi : 0,
			etoile : 0,
			megachampi : 0,
			mini : 0,
			using : []
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
			posKart(oPlayer, oPlace);
			oPlayer.time = 0;
			oPlayer.tours = 1;
			oPlayer.demitours = cp0;
			//oPlayer.tours = oMap.tours;
			//oPlayer.demitours = cp0 ? oMap.checkpoint.length-2:oMap.checkpoint.length-1;
			//oPlayer.arme = "bloops";
			//oPlayer.roulette = 24;
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
			stats : realKartStats([0.5,0.5,0.5,cp[joueur][3]]),

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
			mini : 0,
			using : [],

			cpu : !isOnline,
			aipoint : 0,
			lastAItime : 0,
			aipoints : oMap.aipoints[0],
			maxspeed : 5.7
		};
		if (isOnline)
			oEnemy.id = aIDs[i];
		else
			oEnemy.aipoints = oMap.aipoints[inc%oMap.aipoints.length]||[];

		if (isOnline)
			oEnemy.nick = aPseudos[inc];
		if (iTeamPlay)
			oEnemy.team = aTeams[inc];
		else
			oEnemy.team = -1;
		if ((oEnemy.team != -1) || oEnemy.nick)
			oEnemy.marker = createMarker(oEnemy);
		if (course != "BB") {
			posKart(oEnemy, oPlace);
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
		for (var type in oMap.decor) {
			if (!decorBehaviors[type])
				decorBehaviors[type] = {type:type};
			var decorBehavior = decorBehaviors[type];
			var decorExtra = getDecorExtra(decorBehavior);
			var customDecor = decorExtra.custom;
			if (customDecor) {
				if (decorBehaviors[customDecor.type]) {
					Object.assign(decorBehavior, decorBehaviors[customDecor.type]);
					decorBehavior.type = type;
				}
				(function(decorBehavior) {
					getCustomDecorData(customDecor, function(res) {
						var sizeRatio = {
							w: res.size.hd.w/res.original_size.hd.w,
							h: res.size.hd.h/res.original_size.hd.h
						}
						decorBehavior.size_ratio = sizeRatio;
						if (sizeRatio.w !== 1) {
							var hitboxSize = decorBehavior.hitbox||DEFAULT_DECOR_HITBOX;
							var hitboxConst = 1;
							decorBehavior.hitbox = hitboxConst + (hitboxSize-hitboxConst)*sizeRatio.w;
						}
						if (sizeRatio.h !== 1) {
							var hitboxHeight = decorBehavior.hitboxH||DEFAULT_DECOR_HITBOX_H;
							var hitboxConst = 0.8;
							decorBehavior.hitboxH = hitboxConst + (hitboxHeight-hitboxConst)*sizeRatio.h;
						}
					});
				})(decorBehavior);
			}
			if (decorBehavior.preinit)
				decorBehavior.preinit(oMap.decor[type]);
		}
		var decorIncs = {};
		for (var type in oMap.decor) {
			var decorBehavior = decorBehaviors[type];
			var decorExtra = getDecorExtra(decorBehavior);
			var customDecor = decorExtra.custom;
			var actualType = customDecor ? customDecor.type:type;

			var inc = 0;
			if (decorIncs[actualType])
				inc = decorIncs[actualType];
			else
				decorIncs[actualType] = 0;
			
			var decorsData = oMap.decor[type];
			for (var i=0;i<decorsData.length;i++) {
				var decorData = decorsData[i];
				decorData[2] = new Sprite(type);
				if (decorBehavior.init)
					decorBehavior.init(decorData,i,i+inc);
				if (gameSettings.ld && decorBehavior.hidable)
					decorData[2][0].unshow();
				else {
					if (customDecor) {
						(function(decorData,decorBehavior) {
							for (var j=0;j<oPlayers.length;j++)
								decorData[2][j].img.src = "images/map_icons/empty.png";
							getCustomDecorData(customDecor, function(res) {
								for (var j=0;j<oPlayers.length;j++) {
									decorData[2][j].img.src = res.hd;
									if (res.size.nb_sprites)
										decorData[2][j].nbSprites = res.size.nb_sprites;
									decorData[2][j].w = Math.round(decorData[2][j].w*decorBehavior.size_ratio.w);
									decorData[2][j].h = Math.round(decorData[2][j].h*decorBehavior.size_ratio.h);
									var z = (decorBehavior.size_ratio.w-decorBehavior.size_ratio.h)/(decorBehavior.size_ratio.w+decorBehavior.size_ratio.h);
									z *= decorData[2][j].w/(2*decorData[2][j].h);
									decorData[2][j].z += z;
								}
							});
						})(decorData,decorBehavior);
					}
				}
			}
			decorIncs[actualType] += decorsData.length;
		}
	}
	function spinKart(nb) {
		if (!this.tourne) {
			if (isOnline)
				playIfShould(this,"musics/events/spin.mp3");
			else
				playDistSound(this,"musics/events/spin.mp3",(course=="BB")?80:50);
		}
		this.frminv = 10;
		this.tourne = nb;
	}
	function fallKart() {
		return tombe(this.x+this.speed*direction(0,this.rotation),this.y+this.speed*direction(1,this.rotation));
	}
	function exitKart() {
		var fNewPosX = this.x + this.speed * direction(0, this.rotation);
		var fNewPosY = this.y + this.speed * direction(1, this.rotation);
		return ralenti(fNewPosX,fNewPosY);
	}
	for (var i=0;i<aKarts.length;i++) {
		aKarts[i].spin = spinKart;
		aKarts[i].falling = fallKart;
		aKarts[i].exiting = exitKart;
		for (var j=0;j<strPlayer.length;j++) {
			(function(sprite) {
				sprite.nbSprites = 24;
				sprite.img.onload = function() {
					sprite.w = this.naturalWidth/sprite.nbSprites;
					sprite.h = this.naturalHeight;
					delete this.onload;
				}
			})(aKarts[i].sprite[j]);
		}
	}

	itemDistribution = selectedItemDistrib;
	if (!itemDistribution)
		itemDistribution = itemDistributions[getItemMode()][0].value;

	if (course != "CM") {
		if (itemDistribution.length) {
			for (var i=0;i<oMap.arme.length;i++)
				oMap.arme[i][2] = 0;
		}
		else
			oMap.arme = [];

		for (var i=0;i<oPlayers.length;i++) {
			document.getElementById("infoPlace"+i).innerHTML = oPlayers[i].place;
			document.getElementById("infoPlace"+i).style.display = "block";
			var oColor = (oPlayers[i].team != -1) ? (oPlayers[i].team ? "#F96":"#69F"):"";
			document.getElementById("infoPlace"+i).style.color = oColor;
			if (course != "BB")
				document.getElementById("compteur"+i).style.color = oColor;
		}
	}
	else {
		oMap.arme = [];
		aKarts[0].arme = "champiX3";
		aKarts[0].roulette = 25;
		for (var i=0;i<gPersos.length;i++) {
			var gPerso = gPersos[i];
			aKarts.push({
				speed : (depart<2) ? 0 : 5.7,
				speedinc : 0.5,
				heightinc : 0,
				stats : realKartStats(cp[gPerso]),

				rotation : rot0,
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
				using : [],

				cpu : false,
				aipoint : 0,
				aipoints : oMap.aipoints[0],
				maxspeed : 5.7,

				place : 1
			});
			aKarts[aKarts.length-1].sprite[0].div.style.opacity = 0.5;
		}
		updateObjHud(0);
	}
	gameControls = getGameControls();

	challengesForCircuit = {
		"end_game": [],
		"each_frame": [],
		"each_hit": [],
		"each_kill": [],
		"each_item": [],
		"each_coin": [],
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
	if (clSelected && !challengesUsed[clSelected.id])
		clSelected = undefined;
	reinitLocalVars();

	if ((strPlayer.length == 1) && !gameSettings.nomap) {
		oPlanWidth = Math.round(iScreenScale*19.4);
		oPlanWidth2 = (oMap.w>=oMap.h) ? oPlanWidth : oPlanWidth*(oMap.w/oMap.h);
		var oPlanHeight2 = (oMap.w<=oMap.h) ? oPlanWidth : oPlanWidth*(oMap.h/oMap.w);
		if (oMap.iW && oMap.iH) {
			var mapRatio = Math.min(oMap.w/oMap.iW,oMap.h/oMap.iH);
			oPlanWidth2 *= mapRatio;
			oPlanHeight2 *= mapRatio;
		}
		oPlanWidth2 = Math.round(oPlanWidth2);
		oPlanHeight2 = Math.round(oPlanHeight2);
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

		if (oMapImg.src) {
			oPlanImg = document.createElement("img");
			oPlanImg.src = oMapImg.src;
			oPlanImg.style.width = oPlanSize +"px";
		}
		else {
			var oPlanHeight = Math.round(oPlanSize*oMap.h/oMap.w);
			oPlanImg = document.createElement("canvas");
			oPlanImg.width = oPlanSize;
			oPlanImg.height = oPlanHeight;
			oPlanImg.getContext("2d").drawImage(oMapImg, 0,0, oPlanSize,oPlanHeight);
		}
		oPlanImg.style.position = "absolute";
		oPlanImg.style.left = "0px";
		oPlanImg.style.top = "0px";
		oPlanCtn.appendChild(oPlanImg);

		if (oMapImg.src) {
			oPlanImg2 = document.createElement("img");
			oPlanImg2.src = oMapImg.src;
		}
		else {
			oPlanImg2 = document.createElement("canvas");
			oPlanImg2.width = oPlanSize;
			oPlanImg2.height = oPlanHeight;
			oPlanImg2.getContext("2d").drawImage(oMapImg, 0,0, oPlanSize,oPlanHeight);
		}
		oPlanImg2.style.position = "absolute";
		oPlanImg2.style.left = "0px";
		oPlanImg2.style.top = "0px";
		oPlanImg2.style.width = oPlanWidth2 +"px";
		oPlanCtn2.appendChild(oPlanImg2);

		if (oMap.decor) {
			for (var type in oMap.decor) {
				oPlanDecor[type] = new Array();
				oPlanDecor2[type] = new Array();
			}
		}

		if (oMap.sea) {
			oPlanSea = document.createElement("canvas");
			oPlanSea.style.position = "absolute";
			oPlanSea.style.left = "0px";
			oPlanSea.style.top = "0px";
			oPlanSea.setAttribute("width", oPlanSize +"px");
			oPlanSea.setAttribute("height", oPlanSize +"px");
			oPlanCtn.appendChild(oPlanSea);
			
			oPlanSea2 = document.createElement("canvas");
			oPlanSea2.style.position = "absolute";
			oPlanSea2.style.left = "0px";
			oPlanSea2.style.top = "0px";
			oPlanSea2.setAttribute("width", oPlanWidth2 +"px");
			oPlanSea2.setAttribute("height", oPlanWidth2 +"px");
			oPlanCtn2.appendChild(oPlanSea2);
		}
		var assetKeys = ["oils","pivots","pointers", "flippers","bumpers","flowers"];
		for (var i=0;i<assetKeys.length;i++) {
			var key = assetKeys[i];
			if (oMap[key]) {
				oPlanAssets[key] = new Array();
				oPlanAssets2[key] = new Array();
			}
		}

		oPlanImg.onload = function() {
			if (oMapImg.seekFrame)
				oMapImg.seekFrame(2);
		};

		oCharWidth = iScreenScale*2;
		oTeamWidth = Math.round(iScreenScale*2.4);
		oBBWidth = iScreenScale*2;
		oStarWidth2 = Math.round(iScreenScale*1.5);
		oObjWidth = Math.round(iScreenScale*1.5);
		oCoinWidth = Math.round(iScreenScale*1.2);
		oExpWidth = Math.round(iScreenScale*4.2);
		oExpBWidth = Math.round(iScreenScale*5.6);

		oCharWidth2 = Math.round(oCharRatio*oCharWidth);
		oTeamWidth2 = Math.round(oCharRatio*oTeamWidth);
		oBBWidth2 = Math.round(oCharRatio*oBBWidth);
		oObjWidth2 = Math.round(oPlanRatio*oObjWidth);
		oCoinWidth2 = Math.round(oPlanRatio*oCoinWidth);
		oExpWidth2 = Math.round(oPlanRatio*oExpWidth);
		oExpBWidth2 = Math.round(oPlanRatio*oExpBWidth);
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
			for (var i=0;i<oPlanCharacters.length;i++) {
				if (i) oPlanCharacters[i].style.opacity = 0.5;
				oPlanCtn.appendChild(oPlanCharacters[i]);
			}
			for (var i=0;i<oPlanCharacters2.length;i++) {
				if (i) oPlanCharacters2[i].style.opacity = 0.5;
				oPlanCtn2.appendChild(oPlanCharacters2[i]);
			}
		}
		else {
			for (var i=oPlanCharacters.length-1;i>=0;i--)
				oPlanCtn.appendChild(oPlanCharacters[i]);
			for (var i=oPlanCharacters2.length-1;i>=0;i--)
				oPlanCtn2.appendChild(oPlanCharacters2[i]);
		}

		for (var i=0;i<oMap.arme.length;i++) {
			fSprite = oMap.arme[i];
			fSprite[2] = new Sprite("item");

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
		oCounts[i][0].id = "oCounts"+i;
		oCounts[i][0].style.position = "absolute";
		oCounts[i][0].style.width = (12*iScreenScale)+"px";
		oCounts[i][0].style.height = (12*iScreenScale)+"px";
		oCounts[i][0].style.overflow = "hidden";
		oCounts[i][0].style.top = (4*iScreenScale)+"px";
		oCounts[i][0].style.left = (8*iScreenScale)+"px";
		oCounts[i][0].style.zIndex = 10000;

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
	var redrawCanvasHandler;

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
					document.getElementById("infos"+i).innerHTML = '<tr><td>'+ toLanguage('GO!', 'PARTEZ !') +'</td></tr>';
					document.getElementById("infos"+i).style.fontSize = iScreenScale * 12 + "px";
					document.getElementById("infos"+i).style.top = Math.round(12.5*iScreenScale) + "px";
					if (oPlayers[i].speed == 1)
						oPlayers[i].speed = 11;
					else if (oPlayers[i].speed > 1) {
						oPlayers[i].spin(42);
						oPlayers[i].speed = 0;
						oPlayers[i].speedinc = 0;
					}
				}
				oChallengeCpts.style.visibility = "visible";
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
								document.getElementById("infos"+i).style.display = "none";
						}
						if (stillRacing) {
							var oInfos = document.getElementById("infos0");
							oInfos.style.fontFamily = "";
							oInfos.style.textStroke = oInfos.style.WebkitTextStroke = oInfos.style.MozTextStroke = "";
							oInfos.style.width = "";
							oInfos.style.top = iScreenScale * 7 + 10 +"px";
							oInfos.style.left = Math.round(iScreenScale*25+10 + (strPlayer.length-1)/2*(iWidth*iScreenScale+2)) +"px";
							oInfos.style.fontSize = iScreenScale * 4 +"pt";
							var btnFontSize = (course != "CM") ? (iScreenScale*3):Math.round(iScreenScale*2.5);
							oInfos.innerHTML =
								'<tr><td><input type="button" style="font-size: '+ btnFontSize +'pt; width: 100%;" value=" &nbsp; '+ toLanguage('  RESUME  ', 'REPRENDRE') +' &nbsp; " id="reprendre" /></td></tr>'+
								'<tr><td style="font-size:'+ (iScreenScale*2) +'px">&nbsp;</td></tr>'+
								'<tr><td><input type="button" style="font-size: '+ btnFontSize +'pt; width: 100%;" value=" &nbsp; '+ toLanguage('  RETRY  ', 'RÉESSAYER') +' &nbsp; " id="recommencer" /></td></tr>'+
								((course != "CM") ? '':
									'<tr><td style="font-size:'+ (iScreenScale*2) +'px">&nbsp;</td></tr>'+
									'<tr><td><input type="button" style="font-size: '+ btnFontSize +'pt; width: 100%;" value="'+ toLanguage('  CHANGE RACE  ', 'CHANGER CIRCUIT') +'" id="changecircuit" /></td></tr>'
								)+
								'<tr><td style="font-size:'+ (iScreenScale*2) +'px">&nbsp;</td></tr>'+
								'<tr><td><input type="button" id="quitter" value=" &nbsp; '+ toLanguage('QUIT', 'QUITTER') +' &nbsp; " style="font-size: '+ btnFontSize +'pt; width: 100%;" /></td></tr>';
							oInfos.onkeydown = function(e) {
								var btnDir;
								switch (e.keyCode) {
								case 38:
									btnDir = -1;
									break;
								case 40:
									btnDir = 1;
									break;
								}
								if (btnDir) {
									var focusingElt = document.activeElement;
									if (focusingElt) {
										var inputs = Array.prototype.slice.call(document.querySelectorAll("#infos0 input, #infos0 button"));
										var nextElt = focusingElt;
										var currentId = inputs.indexOf(focusingElt);
										if (currentId != -1) {
											var i = currentId;
											do {
												i += btnDir;
												if (i < 0) i += inputs.length;
												if (i >= inputs.length) i = 0;
												var nextElt = inputs[i];
												if (nextElt && (nextElt.style.display != "none") && (nextElt.style.visibility != "hidden")) {
													nextElt.focus();
													break;
												}
											} while (i != currentId);
										}
									}
								}
							};
							document.getElementById("reprendre").onclick = reprendre;
							document.getElementById("recommencer").onclick = function() {
								interruptGame();
								removeGameMusics();
								removeHUD();
								clearResources();
								$mkScreen.removeChild(oContainers[0]);
								fInfos = {
									player:strPlayer,
									distribution:itemDistribution,
									map:oMap.ref,
									difficulty:iDificulty,
									cl:clSelected
								};
								if (course == "CM") {
									fInfos.perso = gPersos;
									fInfos.cpu_route = jTrajets;
									fInfos.my_record = gRecord;
									fInfos.ow_record = gOverwriteRecord;
									fInfos.lap_times = iLapTimes;
								}
								document.getElementById("infos0").style.display = "none";
								if (strPlayer.length == 1)
									removePlan();
								oBgLayers.length = 0;
								if (resetScores()) {
									if (course == "GP")
										fInfos.map -= (oMap.ref+3)%4;
									else if (course != "CM")
										delete fInfos.map;
								}
								document.onmousedown = undefined;
								document.onkeydown = undefined;
								document.onkeyup = undefined;
								window.removeEventListener("blur", window.releaseOnBlur);
								window.releaseOnBlur = undefined;
								setTimeout(MarioKart, 500);
							};
							if (course == "CM") {
								document.getElementById("changecircuit").onclick = function() {
									interruptGame();
									removeGameMusics();
									removeHUD();
									clearResources();
									$mkScreen.removeChild(oContainers[0]);
									fInfos = {
										player:strPlayer,
										distribution:itemDistribution,
										perso:new Array(),
										cl:clSelected
									};
									document.getElementById("infos0").style.display = "none";
									if (strPlayer.length == 1)
										removePlan();
									oBgLayers.length = 0;
									document.onmousedown = undefined;
									document.onkeydown = undefined;
									document.onkeyup = undefined;
									window.removeEventListener("blur", window.releaseOnBlur);
									window.releaseOnBlur = undefined;
									setTimeout(MarioKart, 500);
								};
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
								oPlayers[0].speedinc = oPlayers[0].stats.acceleration*oPlayers[0].size;
								if (oPlayers[0].etoile) oPlayers[0].speedinc *= 5;
								break;
							case "left":
								oPlayers[0].rotincdir = oPlayers[0].stats.handling;
								if (!oPlayers[0].driftinc && !oPlayers[0].tourne && !oPlayers[0].fell && oPlayers[0].ctrl && !oPlayers[0].cannon) {
									if (oPlayers[0].jumped)
										oPlayers[0].driftinc = 1;
									if (oPlayers[0].driftinc)
										clLocalVars.drifted = true;
								}
								break;
							case "right":
								oPlayers[0].rotincdir = -oPlayers[0].stats.handling;
								if (!oPlayers[0].driftinc && !oPlayers[0].tourne && !oPlayers[0].fell && oPlayers[0].ctrl && !oPlayers[0].cannon) {
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
								else if (!oPlayers[0].jumped && !oPlayers[0].fell && !oPlayers[0].ctrled && !oPlayers[0].billball && !oPlayers[0].tourne && !oPlayers[0].figuring && !oPlayers[0].figstate && !oPlayers[0].driftinc)
									stuntKart(oPlayers[0]);
								break;
							case "pause":
								if (isOnline) break;
								if (!pause) {
									if (!bCounting) {
										document.getElementById("infos0").style.display = "";
										interruptGame();
										pauseSounds();
										var retryButton = document.getElementById("recommencer");
										if (retryButton && ((course == "CM") || clSelected))
											retryButton.focus();
										else {
											var resumeButton = document.getElementById("reprendre");
											if (resumeButton)
												resumeButton.focus();
										}
									}
								}
								else
									reprendre(true);
								break;
							case "balloon" :
								if (pause) return;
								if (course == "BB") {
									if ((oPlayers[0].tourne<5) && oPlayers[0].reserve && oPlayers[0].ballons.length && oPlayers[0].ballons.length < 3 && !oPlayers[0].sprite[0].div.style.opacity) {
										oPlayers[0].ballons[oPlayers[0].ballons.length] = createBalloonSprite(oPlayers[0]);
										oPlayers[0].reserve--;
										updateBalloonHud(document.getElementById("compteur0"),oPlayers[0]);
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
							case "up_p2":
								if (!oPlayers[1]) return;
								oPlayers[1].speedinc = oPlayers[1].stats.acceleration*oPlayers[1].size;
								if (oPlayers[1].etoile) oPlayers[1].speedinc *= 5;
								break;
							case "left_p2":
								if (!oPlayers[1]) return;
								oPlayers[1].rotincdir = oPlayers[1].stats.handling;
								if (!oPlayers[1].driftinc && !oPlayers[1].tourne && !oPlayers[1].fell && oPlayers[1].ctrl && !oPlayers[1].cannon) {
									if (oPlayers[1].jumped)
										oPlayers[1].driftinc = 1;
								}
								break;
							case "right_p2":
								if (!oPlayers[1]) return;
								oPlayers[1].rotincdir = -oPlayers[1].stats.handling;
								if (!oPlayers[1].driftinc && !oPlayers[1].tourne && !oPlayers[1].fell && oPlayers[1].ctrl && !oPlayers[1].cannon) {
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
								else if (!oPlayers[1].jumped && !oPlayers[1].ctrled && !oPlayers[1].fell && !oPlayers[1].billball && !oPlayers[1].tourne && !oPlayers[1].figuring && !oPlayers[1].figstate && !oPlayers[1].driftinc)
									stuntKart(oPlayers[1]);
								return false;
							case "balloon_p2":
								if (pause) return;
								if (!oPlayers[1]) return;
								if (course == "BB") {
									if ((oPlayers[0].tourne<5) && oPlayers[1].reserve && oPlayers[1].ballons.length && oPlayers[1].ballons.length < 3 && !oPlayers[1].sprite[0].div.style.opacity) {
										oPlayers[1].ballons[oPlayers[1].ballons.length] = createBalloonSprite(oPlayers[1]);
										oPlayers[1].reserve--;
										updateBalloonHud(document.getElementById("compteur1"),oPlayers[1]);
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
								if (!oPlayers[0].tourne && !oPlayers[0].cannon && !pause)
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
										clLocalVars.miniTurbo++;
										var ruleVars;
										if (clSelected && clRuleVars[clSelected.id] && (ruleVars = clRuleVars[clSelected.id].mini_turbo))
											updateChallengeHud("miniTurbo", clLocalVars.miniTurbo+ruleVars.miniTurbo);
										if (oPlayers[0].driftcpt >= fTurboDriftCpt2) {
											oPlayers[0].turbodrift += 15;
											clLocalVars.superTurbo++;
											if (clSelected && clRuleVars[clSelected.id] && (ruleVars = clRuleVars[clSelected.id].super_turbo))
												updateChallengeHud("superTurbo", clLocalVars.superTurbo+ruleVars.superTurbo);
										}
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
								showRearView(0);
								break;
							case "item_p2":
							case "item_back_p2":
								if (!oPlayers[1].tourne && !oPlayers[1].cannon && !pause)
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
											oPlayers[1].turbodrift += 15;
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
								showRearView(1);
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
							if (!oPlayers[0].tourne && !oPlayers[0].cannon) {
								if (course == "BB") {
									document.onkeydown({"keyCode":findKeyCode("balloon")});
									return false;
								}
								if (oPlayers[0].arme || oPlayers[0].using.length) {
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
					clearInterval(redrawCanvasHandler);
					if (clLocalVars.delayedStart) {
						oPlayers[0].speed = 0;
						var aSpeedInc = oPlayers[0].speedinc;
						oPlayers[0].speedinc = 0;
						var tUntil = clLocalVars.delayedStart*1000/67;
						function fastCycle() {
							if (pause) {
								if (timer < tUntil) {
									setTimeout(fastCycle,1);
									runOneFrame();
								}
								else {
									delete clLocalVars.fastForward;
									oPlayers[0].speedinc = aSpeedInc;
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
					else
						cycle();
					bRunning = true;
				}
				else {
					pause = false;
					var aStunted = false, aJumped = false, aSmallJump = false, iTrajets = [iTrajet];
					function stopDrifting_() {
						var oKart = aKarts[0];
						oKart.tourne = 0;
						oKart.driftcpt = 0;
						oKart.driftinc = 0;
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
										resetFall(oKart);
									if (!i)
										oContainers[0].style.opacity = Math.abs(oKart.tombe-10)/10;
								}
								var aX = oKart.x;
								var aY = oKart.y;
								var aRotation = oKart.rotation;
								oKart.x = getInfos[0];
								oKart.y = getInfos[1];
								oKart.z = getInfos[2];
								oKart.rotation = getInfos[3];
								var getFlags = (getInfos[4]||"0000").split("");
								for (var j=0;j<4;j++)
									getFlags[j] = +getFlags[j];
								var rotincdir = 0;
								if (getFlags[2])
									rotincdir = 1;
								else if (getFlags[3])
									rotincdir = -1;
								oKart.rotincdir = oKart.stats.handling*rotincdir;
								if (oKart.z) {
									if (!i) {
										if (!aJumped) {
											aJumped = true;
											aSmallJump = true;
											stopDrifting_();
										}
										if (oKart.z > 1.18)
											aSmallJump = false;
										if (aSmallJump) {
											if (getFlags[1]) {
												if (!oKart.driftinc && rotincdir) {
													oKart.driftinc = rotincdir;
													oKart.tourne = (rotincdir>0) ? 18:4;
												}
											}
										}
										else {
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
											else if (getFlags[1] && !oKart.driftinc && !oKart.tombe) {
												aStunted = true;
												stopDrifting_();
												oKart.tourne = 19;
											}
										}
									}
									if (getFlags[0]) {
										oKart.tombe = 20;
										oKart.aX = aX;
										oKart.aY = aY;
										oKart.aRotation = aRotation;
										oKart.sprite[0].img.style.display = "none";
										stopStunt();
									}
								}
								else {
									if (!i) {
										if (aJumped) {
											aJumped = false;
											if (oKart.driftinc)
												document.getElementById("drift"+i).style.display = "block";
											stopStunt();
										}
									}
								}
								if (oKart.driftinc) {
									if (!i) {
										if (getFlags[1])
											document.getElementById("drift"+i).style.top = Math.round(iScreenScale*(32-correctZ(oKart.z)) + (oKart.sprite[i].h-32)*fSpriteScale*0.15) + "px";
										else
											stopDrifting_();
									}
								}
								if (!i)
									handleDriftCpt(i);
							}
							else {
								if (oKart.aipoint == undefined) {
									oKart.aipoint = 0;
									oKart.lastAItime = 0;
									oKart.arme = false;
									oKart.maxspeed = 5.7;
									if (!i) {
										oKart.tourne = 0;
										stopDrifting_();
										stopStunt();
									}
								}
								ai(oKart);
								var aSfx = iSfx;
								iSfx = false;
								move(i);
								iSfx = aSfx;
							}
						}
						timer++;
						showTimer(timer*SPF);
						//if (!(timer%100))
						//	aKarts[0].changeView = Math.floor(Math.random()*21)*(360/21);

						oPlayers[0].cpu = false;
						moveDecor();
						oPlayers[0].cpu = true;
						setTimeout((timer != iTrajet.length) ? revoir : function(){var oKart=aKarts[0];oKart.tours=oMap.tours+1;oKart.demitours=0;oKart.aipoint=0;oKart.changeView=180;oKart.maxspeed=5.7;oKart.speed=5.7;oKart.tourne=0;stopDrifting_();stopStunt();document.onkeyup=undefined;document.getElementById("infos0").style.display="";var firstButton = document.getElementById("infos0").getElementsByTagName("input")[0];if (firstButton)firstButton.focus();timerMS=iRecord;showTimer(timerMS);if(bMusic||iSfx){startEndMusic()}cycle()},SPF);
						render();
					}
					for (i=0;i<aKarts.length;i++)
						aKarts[i].cpu = true;
					for (i=0;i<gPersos.length;i++)
						iTrajets.push(jTrajets[i]);
					clearInterval(redrawCanvasHandler);
					revoir();
					pause = false;
					setTimeout(function() {
						continuer();
						document.querySelector("#enregistrer input").style.display = "none";
					},1000);
					document.onkeyup = function(e) {
						var gameAction = gameControls[e.keyCode];
						if (gameAction == "pause" && !bCounting) {
							document.getElementById("infos0").style.display = (document.getElementById("infos0").style.display == "none") ? "" : "none";
							var firstButton = document.getElementById("infos0").getElementsByTagName("input")[0];
							if (firstButton)
								firstButton.focus();
						}
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
				document.getElementById("infos"+i).style.visibility = "";
			if (bMusic || iSfx)
				countDownMusic.play();
			document.body.style.cursor = "default";
		}
		iCntStep++;
		//* gogogo
		setTimeout(fncCount,1000);
		//*/setTimeout(fncCount,1);
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

	if (iSfx && !fInfos.replay)
		setTimeout(startEngineSound,bMusic ? 2600:1100);
	if (isOnline) {
		var tnCountdown = tnCourse-new Date().getTime();
		//*
		setTimeout(fncCount,tnCountdown);
		//*/setTimeout(fncCount,5);
		if (iTeamPlay)
			showTeam(tnCountdown);
	}
	else {
		//* gogogo
		setTimeout(fncCount,bMusic?3000:1500);
		//*/setTimeout(fncCount,bMusic?3:1.5);
	}
	if (oMapImg.image) {
		redrawCanvasHandler = setTimeout(function() {
			redrawCanvasHandler = setInterval(function() {
				for (var i=0;i<oPlayers.length;i++) {
					var oPlayer = oPlayers[i];
					redrawCanvas(i, oPlayer);
				}
			}, 100);
		}, 100);
	}
	if (clLocalVars.backwardsStart)
		showRearView(0);
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
oContainers[0].className = "game-container";
oContainers[0].tabindex = 1;
formulaire = null;
updateCtnFullScreen($mkScreen.dataset.fs==1);

(function() {
	for (var i=0;i<2;i++) {
		var oInfos = document.getElementById("infos"+i);
		if (oInfos)
			$mkScreen.removeChild(oInfos);
	}
	var oInfos = document.createElement("table");
	oInfos.id = "infos0";
	oInfos.setAttribute("cellspacing", 1);
	oInfos.setAttribute("cellpadding", 0);
	oInfos.style.display = "none";
	$mkScreen.appendChild(oInfos);
})();


$mkScreen.appendChild(oContainers[0]);
if (pause && fInfos.player[1]) {
	oContainers[1] = oContainers[0].cloneNode(false);
	oContainers[1].style.left = (12+iWidth*iScreenScale)+"px";
	$mkScreen.appendChild(oContainers[1]);
}

// setup screen canvas for render mode 0.
var oScreens = new Array();

// array for screen strip descriptions
var aStrips = new Array();

var iCamHeight = 24;
var iCamDist = 32;
var iViewHeight = -10;
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

function interruptGame() {
	pause = true;
	clearInterval(cycleHandler);
	cycleHandler = null;
}
function reprendre(debug) {
	if(pause) {
		pause = false;
		cycle();
	}
	if (debug) {
		unpauseSounds();
		document.getElementById("infos0").style.display = "none";
	}
}

function quitter() {
	if (isOnline) {
		document.location.href = isMCups ? ((complete ? 'map':'circuit') + '.php?mid=' + nid):(isCup ? (complete ? (isBattle ? 'battle':'map')+'.php?'+(isSingle ? 'i':'cid')+'='+nid:(isBattle ? 'arena':'circuit')+'.php?'+(isSingle ? 'id':'cid')+'='+nid):"index.php");
		return;
	}
	interruptGame();
	displayCommands();
	removeGameMusics();
	removeHUD();
	clearResources();
	for (var i=0;i<strPlayer.length;i++) {
		$mkScreen.removeChild(oContainers[i]);
		var oInfos = document.getElementById("infos"+i);
		if (oInfos) oInfos.style.display = "none";
	}
	$mkScreen.style.opacity = 1;
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

	document.getElementById("infos0").style.display="none";
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
		oTeamTable = createTeamTable(teamsRecap);
	}
	document.getElementById("octn").onclick = continuer;
	setTimeout(function() {
		document.getElementById("infos0").style.display = "";
		if (oTeamTable)
			oTeamTable.style.visibility = "visible";
		var aScroll = document.body.scrollTop;
		document.getElementById("octn").focus();
		document.body.scrollTop = aScroll;
	}, 500);
}

function createTeamTable(teamsRecap) {
	var teamsRank = teamsRecap[0]>teamsRecap[1] || (teamsRecap[0]==teamsRecap[1] && teamsRecap[0]==oPlayers[0].team) ? [0,1]:[1,0];
	var oTeamTable = document.createElement("table");
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
	oTeamTable.style.color = primaryColor;
	oTeamTable.style.opacity = 0.7;
	oTeamTable.style.textAlign = "center";
	oTeamTable.style.fontSize = Math.round(iScreenScale*1.5+4) +"pt";
	oTeamTable.style.fontFamily = "Courier";
	oTeamTable.style.fontWeight = "bold";
	oTeamTable.style.fontFamily = "arial";
	oTeamTable.innerHTML = positions;
	$mkScreen.appendChild(oTeamTable);
	return oTeamTable;
}

function resetScores() {
	for (var i=0;i<strPlayer.length;i++)
		aPlaces[i] = aPlayers.length+i+1;
	for (var i=0;i<aPlayers.length;i++)
		aPlaces[i+strPlayer.length] = i+1;
	clRuleVars = {};
	clGlobalVars = undefined;
	var res = false;
	aScores.length = aPlaces.length;
	for (var i=0;i<aScores.length;i++) {
		if (aScores[i] !== 0) {
			res = true;
			aScores[i] = 0;
		}
	}
	return res;
}

function continuer() {
	document.getElementById("infos0").style.border = 0;
	document.getElementById("infos0").style.top = iScreenScale * 10 + 10 +"px";
	document.getElementById("infos0").style.left = Math.round(iScreenScale*20+10 + (strPlayer.length-1)/2*(iWidth*iScreenScale+2)) +"px";
	document.getElementById("infos0").style.background = "transparent";
	document.getElementById("infos0").style.fontSize = iScreenScale * 4 +"pt";
	document.getElementById("infos0").innerHTML = '<tr><td id="continuer"></td></tr><tr><td'+ ((course != "CM") ? ' style="font-size: '+ iScreenScale * 3 +'px;">&nbsp;</td></tr>' : ' id="enregistrer"></td></tr><tr><td id="revoir"></td></tr><tr><td id="classement"></td></tr><tr><td id="changercircuit">') +'</td></tr><tr><td><input type="button" id="quitter" value="'+ toLanguage('QUIT', 'QUITTER') +'" style="font-size: '+ iScreenScale*3 +'pt; width: 100%;" /></td></tr>';

	var oTeamTable = document.getElementById("team-table");
	if (oTeamTable)
		$mkScreen.removeChild(oTeamTable);

	var oContinue = document.createElement("input");
	oContinue.type = "button";
	oContinue.style.fontSize = iScreenScale*3 + "pt";
	oContinue.style.width = "100%";

	if (course != "CM") {
		if (oMap.ref % 4 || course != "GP") {
			if (isSingle && !isOnline)
				oContinue.value = "        "+ toLanguage('  REPLAY', 'REJOUER') +"        ";
			else {
				if (course == "BB")
					oContinue.value = toLanguage("      NEXT BATTLE	   ", "BATAILLE SUIVANTE");
				else
					oContinue.value = toLanguage("       NEXT RACE	   ", "COURSE SUIVANTE");
			}
			function nextRace() {
				interruptGame();
				removeGameMusics();
				removeHUD();
				clearResources();
				for (var i=0;i<strPlayer.length;i++) {
					$mkScreen.removeChild(oContainers[i]);
					document.getElementById("infos"+i).style.display = "none";
				}
				fInfos = {
					player:strPlayer,
					distribution:itemDistribution,
					difficulty:iDificulty,
					cl:clSelected
				};
				if (course == "GP")
					fInfos.map = oMap.ref+1;
				$mkScreen.style.opacity = 1;
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
				$mkScreen = document.body;
				interruptGame();
				var posX = [29,22,36];
				var posY = [15,17,19];
				document.body.innerHTML = toLanguage('You are', 'Vous &ecirc;tes') +' <span id="position"></span> !<br /><a href="javascript:location.reload()" style="color: white;">'+ toLanguage('Back', 'Retour') +'</a><img alt="." src="images/podium.gif" style="position: absolute; left: '+ iScreenScale * 20 +'px; top: '+ iScreenScale * 20 +'px; width: '+ iScreenScale * 24 +'px;" />';
				var oPlace;
				var placement = new Array();
				for (var i=1;i<=aKarts.length;i++) {
					for (var j=0;j<aKarts.length;j++) {
						if (aKarts[j].place == i) {
							placement.push(aKarts[j].personnage);
							break;
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
						break;
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
		var oQuit = document.getElementById("quitter");
		oContinue.style.fontSize = oQuit.style.fontSize = (iScreenScale*3) +"px";
		var oSave = oContinue.cloneNode(false);
		var oReplay = oContinue.cloneNode(false);
		var oChangeRace = oContinue.cloneNode(false);
		var oClassement = oContinue.cloneNode(false);

		if (gSelectedPerso)
			oContinue.value = toLanguage('        FACE WITH        ', '     AFFRONTER     ');
		else
			oContinue.value = toLanguage('          RETRY          ', '     RÉESSAYER     ');
		oContinue.onclick = function() {
			interruptGame();
			removeGameMusics();
			removeHUD();
			clearResources();
			$mkScreen.removeChild(oContainers[0]);
			fInfos = {
				player:strPlayer,
				distribution:itemDistribution,
				map:oMap.ref,
				difficulty:iDificulty,
				perso:gPersos,
				cpu_route:jTrajets,
				my_record:gRecord,
				ow_record:gOverwriteRecord,
				lap_times: iLapTimes,
				cl:clSelected
			};
			if (gSelectedPerso) {
				fInfos.player = [gSelectedPerso];
				fInfos.perso = [strPlayer[0]];
				fInfos.cpu_route = [iTrajet];
			}
			else {
				if (gOverwriteRecord == 2) {
					if (lapTimers.length == oMap.tours) {
						fInfos.cpu_route = [iTrajet];
						fInfos.perso = strPlayer;
						fInfos.lap_times = lapTimers;
					}
				}
			}
			document.getElementById("infos0").style.display = "none";
			if (strPlayer.length == 1)
				removePlan();
			oBgLayers.length = 0;
			document.onmousedown = undefined;
			setTimeout(MarioKart, 500);
		}

		oSave.value = "   "+ toLanguage('SAVE', 'ENREGISTRER') +"   ";
		oSave.onclick = function() {
			document.getElementById("infos0").style.display = "none";
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
			oForm.style.zIndex = 20000;

			oForm.onsubmit = function() {
				var nom = this.pseudo.value;
				
				if (nom) {
					document.body.style.cursor = "progress";
					oValide.style.visibility = "hidden";
					aPara2.style.visibility = "hidden";
					var params = "name="+nom+"&perso="+strPlayer[0]+"&time="+getActualGameTimeMS();
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
					xhr("records.php", params, function(reponse) {
						if (reponse) {
							document.body.style.cursor = "default";
							var enregistre;
							try {
								enregistre = eval(reponse);
							}
							catch (e) {
								return false;
							}
							function showBackUi(success) {
								oInput.disabled = true;
								oCheckbox.disabled = true;
								oValide.parentNode.removeChild(oValide);
								aPara2.style.fontSize = Math.round(iScreenScale*2.5) + "px";
								if (success) {
									aPara2.innerHTML = toLanguage("Congratulations "+ nom +", your score has been saved successfully ! You places ", "F&eacute;licitations "+ nom +", votre score a bien &eacute;t&eacute; enregistr&eacute; ! Vous &ecirc;tes ") + toPlace(enregistre[0]) + toLanguage(" out of "+ enregistre[1] +" in this race !", " sur "+ enregistre[1] +" au classement de ce circuit !");
									oSave.style.display = "none";
								}
								aPara2.style.visibility = "";
								oRetour.focus();
							}
							if (Array.isArray(enregistre)) {
								if (oCheckbox.checked) {
									oSave.style.display = "none";
									oValide.style.display = "none";
									var aSmall = document.createElement("span");
									aSmall.style.fontSize = Math.round(iScreenScale*2.5) + "px";
									aSmall.innerHTML = toLanguage("Saving ghost...","Enregistrement du fantôme...");
									aPara2.appendChild(aSmall);
									aPara2.style.visibility = "";
									var oRequest = "map="+ oMap.map +"&perso="+ strPlayer[0] +"&time="+ getActualGameTimeMS()+"&times="+JSON.stringify(lapTimers);
									for (i=0;i<iTrajet.length;i++)
										oRequest += "&p"+ i +"="+ iTrajet[i].toString().replace(/\,/g, "_");
									xhr("saveghost.php", oRequest, function(reponse) {
										if (reponse == 1) {
											gRecord = getActualGameTimeMS();
											if (gOverwriteRecord)
												gOverwriteRecord = 2;
											showBackUi(true);
											return true;
										}
										else
											return false;
									});
								}
								else
									showBackUi(true);
							}
							else {
								showBackUi(false);
								switch (enregistre) {
								case 0:
									aPara2.innerHTML = toLanguage("You did a better score on this race before.<br />Your score has not been registered.", "Vous avez fait un meilleur score sur ce circuit.<br />Votre temps n'a donc pas &eacute;t&eacute; enregistr&eacute;.");
									break;
								case 1:
									aPara2.innerHTML = toLanguage("This nick is already used, please choose another one. If it's you, <a href=\"forum.php\" target=\"_blank\" style=\"color: orange\">log-in</a> to your account and try again.", "Ce pseudo est déjà utilisé, veuillez en choisir un autre. S'il s'agit de vous, <a href=\"forum.php\" target=\"_blank\" style=\"color: orange\">connectez-vous</a> et réessayez.");
									break;
								default:
									aPara2.innerHTML = toLanguage("An unknown error occured, please try again later", "Une erreur inconnue est survenue, veuillez réessayer ultérieurement");
									break;
								}
								if (enregistre != 0) {
									oValide.style.visibility = "";
									oValide.style.marginRight = (iScreenScale*2) +"px";
									aPara3.insertBefore(oValide,oRetour);
									oCheckbox.disabled = false;
									oInput.disabled = false;
									oInput.select();
								}
							}
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
			oInput.onkeydown = function(e) {
				e.stopPropagation();
			};
			oInput.onkeyup = function(e) {
				e.stopPropagation();
			};
			aPara1.appendChild(oInput);
			var aPara12 = aPara1.cloneNode(false);
			var oCheckLabel = document.createElement("label");
			oCheckLabel.style.display = "inline-block";
			var oCheckbox = document.createElement("input");
			oCheckbox.type = "checkbox";
			oCheckbox.name = "saveghost";
			oCheckbox.checked = true;
			oCheckbox.style.transform = oCheckbox.style.WebkitTransform = oCheckbox.style.MozTransform = "scale("+ (iScreenScale/6).toFixed(1) +")";
			oCheckLabel.appendChild(oCheckbox);
			var oCheckSpan = document.createElement("span");
			oCheckSpan.style.fontSize = (iScreenScale*2) +"px";
			oCheckSpan.innerHTML = " "+toLanguage("Save ghost","Enregistrer le fantôme");
			oCheckLabel.appendChild(oCheckSpan);
			aPara12.appendChild(oCheckLabel);
			if ((page != "MK") || (timerMS >= gRecord)) {
				oCheckbox.checked = false;
				aPara12.style.display = "none";
			}
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
				$mkScreen.removeChild(oForm);
				document.getElementById("infos0").style.display = ""
				oContinue.focus();
			};
			aPara3.appendChild(oRetour);

			oForm.appendChild(aPara1);
			oForm.appendChild(aPara12);
			oForm.appendChild(aPara2);
			oForm.appendChild(aPara3);
			$mkScreen.appendChild(oForm);

			oForm.style.height = oForm.scrollHeight +"px";

			oInput.select();
		}
		if (gSelectedPerso) oSave.style.display = "none";
		document.getElementById("enregistrer").appendChild(oSave);

		oReplay.value = toLanguage("REPLAY", "REVOIR");
		oReplay.onclick = function() {
			interruptGame();
			removeGameMusics();
			removeHUD();
			clearResources();
			for (var i=0;i<strPlayer.length;i++) {
				$mkScreen.removeChild(oContainers[i]);
				fInfos = {
					player:strPlayer,
					distribution:itemDistribution,
					map:oMap.ref,
					my_route:iTrajet,
					replay:true,
					perso:gPersos,
					selPerso:gSelectedPerso,
					cpu_route:jTrajets,
					my_record:gRecord,
					ow_record:gOverwriteRecord,
					record:timerMS,
					lap_times:iLapTimes,
					cl:clSelected
				};
				if (fInfos.record == undefined)
					fInfos.record = iRecord;
				if (fInfos.lap_times == undefined)
					fInfos.lap_times = lapTimers;
				document.getElementById("infos"+i).style.display = "none";
			}
			if (strPlayer.length == 1)
				removePlan();
			oBgLayers.length = 0;
			document.onmousedown = undefined;
			setTimeout(MarioKart, 500);
		}
		document.getElementById("revoir").appendChild(oReplay);

		oChangeRace.value = toLanguage("     CHANGE RACE     ", "   CHANGER CIRCUIT   ");
		oChangeRace.onclick = function() {
			interruptGame();
			removeGameMusics();
			removeHUD();
			clearResources();
			for (var i=0;i<strPlayer.length;i++) {
				$mkScreen.removeChild(oContainers[i]);
				document.getElementById("infos"+i).style.display = "none";
			}
			fInfos = {
				player:strPlayer,
				distribution:itemDistribution,
				perso:new Array(),
				cl:clSelected
			};
			if (gSelectedPerso)
				fInfos.player = [gSelectedPerso];
			if (strPlayer.length == 1)
				removePlan();
			oBgLayers.length = 0;
			document.onmousedown = undefined;
			setTimeout(MarioKart, 500);
		}
		document.getElementById("changercircuit").appendChild(oChangeRace);

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
		this[i].w = 32;
		this[i].h = 32;
		this[i].z = 0;

		this[i].draw = function(iX, iY, fScale, iZ) {
			if (!iZ)
				iZ = 0;
			
			var i = this.i;

			var fSpriteSize = (this.w * fSpriteScale * fScale);
			var fSpriteHeight = (this.h * fSpriteScale * fScale);
			var oY = iY-fSpriteHeight*(this.z+1) + fSpriteSize/2;
			//var oY = iY-fSpriteHeight*((this.z?window.aaa:0)+1) + fSpriteSize/2;

			if (isNaN(oY) || oY > iHeight * iScreenScale || (iY+iZ*iScreenScale) < 9 * iScreenScale) {
				oCtSprites[i][0].style.display = "none";
				return;
			}

			oCtSprites[i][0].style.display = "block";

			oCtSprites[i][0].style.left = Math.round(iX - fSpriteSize/2)+"px";
			oCtSprites[i][0].style.top = Math.round(oY)+"px";

			if (this.h != this.w)
				oCtSprites[i][1].style.width = (Math.round(fSpriteSize)*this.nbSprites)+"px";
			else
				oCtSprites[i][1].style.width = "";
			oCtSprites[i][1].style.height = Math.round(fSpriteHeight) + "px";

			oCtSprites[i][0].style.width = Math.round(fSpriteSize) + "px";
			oCtSprites[i][0].style.height = Math.round(fSpriteHeight) + "px";

			oCtSprites[i][1].style.left = -(Math.round(fSpriteSize)*oCtSprites[i][2])+"px";
		}

		this[i].render = function(fCamera, fSprite) {
			var fSize = (fSprite.size || 1);
			
			var fCamX = fSprite.x - fCamera.x;
			var fCamY = fSprite.y - fCamera.y;

			var fRotRad = fCamera.rotation * Math.PI / 180;

			var fTransX = fCamX * Math.cos(fRotRad) - fCamY * Math.sin(fRotRad);
			var fTransY = fCamX * Math.sin(fRotRad) + fCamY * Math.cos(fRotRad);

			var iDeltaY = -iCamHeight;
			var iDeltaX = iCamDist + fTransY;

			var iZ = fSprite.z ? correctCamZ(fSprite.z,iDeltaX) : 0;

			var iViewY = ((iDeltaY / iDeltaX) * iCamDist + iCamHeight) - iViewHeight + iZ;
			var fViewX = -(fTransX / (fTransY + iCamDist)) * iCamDist;

			this.div.style.zIndex = Math.round(10000 - fTransY);

			var iX = ((iWidth/2) + fViewX) * iScreenScale,
				iY = (iHeight - iViewY) * iScreenScale,
				fScale = fFocal / (fFocal + fTransY) * fSize;
			this.draw(iX,iY, fScale, iZ);
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
	var that = this;
	this[0].unshow = function() {
		that[0].suppr();
		that[0].unshown = true;
	}
	this[0].suppr = function() {
		if (!that[0].unshown) {
			for (var i=0;i<strPlayer.length;i++)
				oContainers[i].removeChild(oCtSprites[i][0]);
		}
	}
	this[0].fadein = function(fadedelay) {
		if (!that[0].unshown) {
			var x = 0;
			var dx = SPF/fadedelay;
			function showProgressively() {
				if (x >= 1)
					x = "";
				for (var i=0;i<strPlayer.length;i++)
					oCtSprites[i][0].style.opacity = x;
				if (x === "") {
					delete that[0].fadeinhandler;
					return;
				}
				x += dx;
				that[0].fadeinhandler = setTimeout(showProgressively, SPF);
			}
			showProgressively();
		}
	}
	this[0].fadeout = function(fadedelay) {
		if (!that[0].unshown) {
			var x = 1;
			if (that[0].fadeinhandler) {
				clearTimeout(that[0].fadeinhandler);
				x = 0;
			}
			var dx = SPF/fadedelay;
			function removeProgressively() {
				x -= dx;
				if (x <= 0) {
					that[0].suppr();
					return;
				}
				for (var i=0;i<strPlayer.length;i++)
					oCtSprites[i][0].style.opacity = x;
				setTimeout(removeProgressively, SPF);
			}
			removeProgressively();
		}
	}
}



function BGLayer(strImage, scaleFactor) {
	var oLayers = new Array();

	var imageDims = new Image();
	imageDims.src = "images/map_bg/" + strImage + ".png";
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

function GIF() {
	var timerID;                          // timer handle for set time out usage
	var parseBlockID;
    var st;                               // holds the stream object when loading.
    var interlaceOffsets  = [0, 4, 2, 1]; // used in de-interlacing.
    var interlaceSteps    = [8, 8, 4, 2];
    var interlacedBufSize;  // this holds a buffer to de interlace. Created on the first frame and when size changed
    var deinterlaceBuf;
    var pixelBufSize;    // this holds a buffer for pixels. Created on the first frame and when size changed
    var pixelBuf;
    var GIF_FILE = { // gif file data headers
        GCExt   : 0xF9,
        COMMENT : 0xFE,
        APPExt  : 0xFF,
        UNKNOWN : 0x01, // not sure what this is but need to skip it in parser
        IMAGE   : 0x2C,
        EOF     : 59,   // This is entered as decimal
        EXT     : 0x21,
    };      
    // simple buffered stream used to read from the file 
    var Stream = function (data) { 
        this.data = new Uint8ClampedArray(data);
        this.pos  = 0;
        var len   = this.data.length;
        this.getString = function (count) { // returns a string from current pos of len count
            var s = "";
            while (count--) { s += String.fromCharCode(this.data[this.pos++]) }
            return s;
        };
        this.readSubBlocks = function () { // reads a set of blocks as a string
            var size, count, data  = "";
            do {
                count = size = this.data[this.pos++];
                while (count--) { data += String.fromCharCode(this.data[this.pos++]) }
            } while (size !== 0 && this.pos < len);
            return data;
        }
        this.readSubBlocksB = function () { // reads a set of blocks as binary
            var size, count, data = [];
            do {
                count = size = this.data[this.pos++];
                while (count--) { data.push(this.data[this.pos++]);}
            } while (size !== 0 && this.pos < len);
            return data;
        }
    };
    // LZW decoder uncompressed each frames pixels
    // this needs to be optimised.
    // minSize is the min dictionary as powers of two
    // size and data is the compressed pixels
    function lzwDecode(minSize, data) {
        var i, pixelPos, pos, clear, eod, size, done, dic, code, last, d, len;
        pos = pixelPos = 0;
        dic      = [];
        clear    = 1 << minSize;
        eod      = clear + 1;
        size     = minSize + 1;
        done     = false;
        while (!done) { // JavaScript optimisers like a clear exit though I never use 'done' apart from fooling the optimiser
            last = code;
            code = 0;
            for (i = 0; i < size; i++) {
                if (data[pos >> 3] & (1 << (pos & 7))) { code |= 1 << i }
                pos++;
            }
            if (code === clear) { // clear and reset the dictionary
                dic = [];
                size = minSize + 1;
                for (i = 0; i < clear; i++) { dic[i] = [i] }
                dic[clear] = [];
                dic[eod] = null;
            } else {
                if (code === eod) {  done = true; return }
                if (code >= dic.length) { dic.push(dic[last].concat(dic[last][0])) }
                else if (last !== clear) { dic.push(dic[last].concat(dic[code][0])) }
                d = dic[code];
                len = d.length;
                for (i = 0; i < len; i++) { pixelBuf[pixelPos++] = d[i] }
                if (dic.length === (1 << size) && size < 12) { size++ }
            }
        }
    };
    function parseColourTable(count) { // get a colour table of length count  Each entry is 3 bytes, for RGB.
        var colours = [];
        for (var i = 0; i < count; i++) { colours.push([st.data[st.pos++], st.data[st.pos++], st.data[st.pos++]]) }
        return colours;
    }
    function parse (){        // read the header. This is the starting point of the decode and async calls parseBlock
        var bitField;
        st.pos                += 6;  
        gif.width             = (st.data[st.pos++]) + ((st.data[st.pos++]) << 8);
        gif.height            = (st.data[st.pos++]) + ((st.data[st.pos++]) << 8);
        bitField              = st.data[st.pos++];
        gif.colorRes          = (bitField & 112) >> 4;
        gif.globalColourCount = 1 << ((bitField & 7) + 1);
        gif.bgColourIndex     = st.data[st.pos++];
        st.pos++;                    // ignoring pixel aspect ratio. if not 0, aspectRatio = (pixelAspectRatio + 15) / 64
        if (bitField & 128) { gif.globalColourTable = parseColourTable(gif.globalColourCount) } // global colour flag
        parseBlockID = setTimeout(parseBlock, 0);
    }
    function parseAppExt() { // get application specific data. Netscape added iterations and terminator. Ignoring that
        st.pos += 1;
        if ('NETSCAPE' === st.getString(8)) { st.pos += 8 }  // ignoring this data. iterations (word) and terminator (byte)
        else {
            st.pos += 3;            // 3 bytes of string usually "2.0" when identifier is NETSCAPE
            st.readSubBlocks();     // unknown app extension
        }
    };
    function parseGCExt() { // get GC data
        var bitField;
        st.pos++;
        bitField              = st.data[st.pos++];
        gif.disposalMethod    = (bitField & 28) >> 2;
        gif.transparencyGiven = bitField & 1 ? true : false; // ignoring bit two that is marked as  userInput???
        gif.delayTime         = (st.data[st.pos++]) + ((st.data[st.pos++]) << 8);
        gif.transparencyIndex = st.data[st.pos++];
        st.pos++;
    };
    function parseImg() {                           // decodes image data to create the indexed pixel image
        var deinterlace, frame, bitField;
        deinterlace = function (width) {                   // de interlace pixel data if needed
            var lines, fromLine, pass, toline;
            lines = pixelBufSize / width;
            fromLine = 0;
            if (interlacedBufSize !== pixelBufSize) {      // create the buffer if size changed or undefined.
                deinterlaceBuf = new Uint8Array(pixelBufSize);
                interlacedBufSize = pixelBufSize;
            }
            for (pass = 0; pass < 4; pass++) {
                for (toLine = interlaceOffsets[pass]; toLine < lines; toLine += interlaceSteps[pass]) {
                    deinterlaceBuf.set(pixelBuf.subArray(fromLine, fromLine + width), toLine * width);
                    fromLine += width;
                }
            }
        };
        frame                = {}
        gif.frames.push(frame);
        frame.disposalMethod = gif.disposalMethod;
        frame.time           = gif.length;
        frame.delay          = gif.delayTime * 10;
        gif.length          += frame.delay;
        if (gif.transparencyGiven) { frame.transparencyIndex = gif.transparencyIndex }
        else { frame.transparencyIndex = undefined }
        frame.leftPos = (st.data[st.pos++]) + ((st.data[st.pos++]) << 8);
        frame.topPos  = (st.data[st.pos++]) + ((st.data[st.pos++]) << 8);
        frame.width   = (st.data[st.pos++]) + ((st.data[st.pos++]) << 8);
        frame.height  = (st.data[st.pos++]) + ((st.data[st.pos++]) << 8);
        bitField      = st.data[st.pos++];
        frame.localColourTableFlag = bitField & 128 ? true : false; 
        if (frame.localColourTableFlag) { frame.localColourTable = parseColourTable(1 << ((bitField & 7) + 1)) }
        if (pixelBufSize !== frame.width * frame.height) { // create a pixel buffer if not yet created or if current frame size is different from previous
            pixelBuf     = new Uint8Array(frame.width * frame.height);
            pixelBufSize = frame.width * frame.height;
        }
        lzwDecode(st.data[st.pos++], st.readSubBlocksB()); // decode the pixels
        if (bitField & 64) {                        // de interlace if needed
            frame.interlaced = true;
            deinterlace(frame.width);
        } else { frame.interlaced = false }
        processFrame(frame);                               // convert to canvas image
    };
    function processFrame(frame) { // creates a RGBA canvas image from the indexed pixel data.
        var ct, cData, dat, pixCount, ind, useT, i, pixel, pDat, col, frame, ti;
        frame.image        = document.createElement('canvas');
        frame.image.width  = gif.width;
        frame.image.height = gif.height;
        frame.image.ctx    = frame.image.getContext("2d");
        ct = frame.localColourTableFlag ? frame.localColourTable : gif.globalColourTable;
        if (gif.lastFrame === null) { gif.lastFrame = frame }
        useT = (gif.lastFrame.disposalMethod === 2 || gif.lastFrame.disposalMethod === 3) ? true : false;
        if (!useT) { frame.image.ctx.drawImage(gif.lastFrame.image, 0, 0, gif.width, gif.height) }
        cData = frame.image.ctx.getImageData(frame.leftPos, frame.topPos, frame.width, frame.height);
        ti  = frame.transparencyIndex;
        dat = cData.data;
        if (frame.interlaced) { pDat = deinterlaceBuf }
        else { pDat = pixelBuf }
        pixCount = pDat.length;
        ind = 0;
        for (i = 0; i < pixCount; i++) {
            pixel = pDat[i];
            col   = ct[pixel];
            if (ti !== pixel) {
                dat[ind++] = col[0];
                dat[ind++] = col[1];
                dat[ind++] = col[2];
                dat[ind++] = 255;      // Opaque.
            } else
                if (useT) {
                    dat[ind + 3] = 0; // Transparent.
                    ind += 4;
                } else { ind += 4 }
        }
        frame.image.ctx.putImageData(cData, frame.leftPos, frame.topPos);
        gif.lastFrame = frame;
        if (!gif.waitTillDone && typeof gif.onload === "function") { doOnloadEvent() }// if !waitTillDone the call onload now after first frame is loaded
    };
    function finnished() { // called when the load has completed
        gif.loading           = false;
        gif.frameCount        = gif.frames.length;
        gif.lastFrame         = null;
        st                    = undefined;
        gif.complete          = true;
        gif.disposalMethod    = undefined;
        gif.transparencyGiven = undefined;
        gif.delayTime         = undefined;
        gif.transparencyIndex = undefined;
        gif.waitTillDone      = undefined;
        pixelBuf              = undefined; // dereference pixel buffer
        deinterlaceBuf        = undefined; // dereference interlace buff (may or may not be used);
        pixelBufSize          = undefined;
        deinterlaceBuf        = undefined;
        gif.currentFrame      = 0;
        if (gif.frames.length > 0) {
			gif.image = gif.frames[0].image
			if (typeof gif.onloadone === "function") {
				(gif.onloadone.bind(gif))({ type : 'load', path : [gif] })
				delete gif.onloadone;
			}
		}
        doOnloadEvent();
        if (typeof gif.onloadall === "function") {
            (gif.onloadall.bind(gif))({   type : 'loadall', path : [gif] });
        }
        if (gif.playOnLoad) { gif.play() }
    }
    function canceled () { // called if the load has been cancelled
        finnished();
        if (typeof gif.cancelCallback === "function") { (gif.cancelCallback.bind(gif))({ type : 'canceled', path : [gif] }) }
    }
    function parseExt() {              // parse extended blocks
        var blockID = st.data[st.pos++];
        if(blockID === GIF_FILE.GCExt) { parseGCExt() }
        else if(blockID === GIF_FILE.COMMENT) { gif.comment += st.readSubBlocks() }
        else if(blockID === GIF_FILE.APPExt) { parseAppExt() }
        else {
            if(blockID === GIF_FILE.UNKNOWN) { st.pos += 13; } // skip unknow block
            st.readSubBlocks();
        }

    }
    function parseBlock() { // parsing the blocks
        if (gif.cancel !== undefined && gif.cancel === true) { canceled(); return }

        var blockId = st.data[st.pos++];
        if(blockId === GIF_FILE.IMAGE ){ // image block
            parseImg();
            if (gif.firstFrameOnly) { finnished(); return }
        }else if(blockId === GIF_FILE.EOF) { finnished(); return }
		else { parseExt() }
		if (gif.frames.length) {
			if (typeof gif.onloadone === "function") {
				gif.image = gif.frames[0].image;
				(gif.onloadone.bind(gif))({ type : 'load', path : [gif] })
				delete gif.onloadone;
			}
		}
        if (typeof gif.onprogress === "function") {
            gif.onprogress({ bytesRead  : st.pos, totalBytes : st.data.length, frame : gif.frames.length });
        }
        parseBlockID = setTimeout(parseBlock, 0); // parsing frame async so processes can get some time in.
    };
    function cancelLoad(callback) { // cancels the loading. This will cancel the load before the next frame is decoded
        if (gif.complete) { return false }
        gif.cancelCallback = callback;
        gif.cancel         = true;
        return true;
    }
    function error(type) {
        if (typeof gif.onerror === "function") { (gif.onerror.bind(this))({ type : type, path : [this] }) }
        gif.onload  = gif.onerror = undefined;
        gif.loading = false;
    }
    function doOnloadEvent() { // fire onload event if set
        gif.currentFrame = 0;
        gif.nextFrameAt  = gif.lastFrameAt  = new Date().valueOf(); // just sets the time now
        if (typeof gif.onload === "function") { (gif.onload.bind(gif))({ type : 'load', path : [gif] }) }
        gif.onerror = gif.onload  = undefined;
    }
    function dataLoaded(data) { // Data loaded create stream and parse
        st = new Stream(data);
        parse();
    }
    function loadGif(filename) { // starts the load
        var ajax = new XMLHttpRequest();
        ajax.responseType = "arraybuffer";
        ajax.onload = function (e) {
            if (e.target.status === 404) { error("File not found") }
            else if(e.target.status >= 200 && e.target.status < 300 ) { dataLoaded(ajax.response) }
            else { error("Loading error : " + e.target.status) }
        };
        ajax.open('GET', filename, true);
        ajax.send();
        ajax.onerror = function (e) { error("File error") };
        this.src = filename;
        this.loading = true;
    }
    function play() { // starts play if paused
        if (!gif.playing) {
            gif.paused  = false;
            gif.playing = true;
            playing();
        }
    }
    function pause() { // stops play
        gif.paused  = true;
        gif.playing = false;
        clearTimeout(timerID);
    }
    function togglePlay(){
        if(gif.paused || !gif.playing){ gif.play() }
        else{ gif.pause() }
    }
    function seekFrame(frame) { // seeks to frame number.
        clearTimeout(timerID);
        gif.currentFrame = frame % gif.frames.length;
        if (gif.playing) { playing() }
        else { gif.image = gif.frames[gif.currentFrame].image }
    }
    function seek(time) { // time in Seconds  // seek to frame that would be displayed at time
        clearTimeout(timerID);
        if (time < 0) { time = 0 }
        time *= 1000; // in ms
        time %= gif.length;
        var frame = 0;
        while (time > gif.frames[frame].time + gif.frames[frame].delay && frame < gif.frames.length) {  frame += 1 }
        gif.currentFrame = frame;
        if (gif.playing) { playing() }
        else { gif.image = gif.frames[gif.currentFrame].image}
    }
    function playing() {
        var delay;
        var frame;
        if (gif.playSpeed === 0) {
            gif.pause();
            return;
        } else {
            if (gif.playSpeed < 0) {
                gif.currentFrame -= 1;
                if (gif.currentFrame < 0) {gif.currentFrame = gif.frames.length - 1 }
                frame = gif.currentFrame;
                frame -= 1;
                if (frame < 0) {  frame = gif.frames.length - 1 }
                delay = -gif.frames[frame].delay * 1 / gif.playSpeed;
            } else {
                gif.currentFrame += 1;
                gif.currentFrame %= gif.frames.length;
                delay = gif.frames[gif.currentFrame].delay * 1 / gif.playSpeed;
            }
            gif.image = gif.frames[gif.currentFrame].image;
            timerID = setTimeout(playing, delay);
        }
	}
	function clearFrames() {
		clearTimeout(timerID);
		clearTimeout(parseBlockID);
		gif.frames.length = 0;
	}
    var gif = {                      // the gif image object
        onload         : null,       // fire on load. Use waitTillDone = true to have load fire at end or false to fire on first frame
        onerror        : null,       // fires on error
        onprogress     : null,       // fires a load progress event
        onloadall      : null,       // event fires when all frames have loaded and gif is ready
        paused         : false,      // true if paused
        playing        : false,      // true if playing
        waitTillDone   : true,       // If true onload will fire when all frames loaded, if false, onload will fire when first frame has loaded
        loading        : false,      // true if still loading
        firstFrameOnly : false,      // if true only load the first frame
        width          : null,       // width in pixels
        height         : null,       // height in pixels
        frames         : [],         // array of frames
        comment        : "",         // comments if found in file. Note I remember that some gifs have comments per frame if so this will be all comment concatenated
        length         : 0,          // gif length in ms (1/1000 second)
        currentFrame   : 0,          // current frame. 
        frameCount     : 0,          // number of frames
        playSpeed      : 1,          // play speed 1 normal, 2 twice 0.5 half, -1 reverse etc...
        lastFrame      : null,       // temp hold last frame loaded so you can display the gif as it loads
        image          : null,       // the current image at the currentFrame
        playOnLoad     : true,       // if true starts playback when loaded
        // functions
        load           : loadGif,    // call this to load a file
        cancel         : cancelLoad, // call to stop loading
        play           : play,       // call to start play
        pause          : pause,      // call to pause
        seek           : seek,       // call to seek to time
        seekFrame      : seekFrame,  // call to seek to frame
		togglePlay     : togglePlay, // call to toggle play and pause state
		clear          : clearFrames
    };
    return gif;
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
	res.render = function(i, fCamera, fSprite) {
		var fSize = (fSprite.size || 1);
		
		var fCamX = fSprite.x - fCamera.x;
		var fCamY = fSprite.y - fCamera.y;

		var fRotRad = fCamera.rotation * Math.PI / 180;

		var fTransX = fCamX * Math.cos(fRotRad) - fCamY * Math.sin(fRotRad);
		var fTransY = fCamX * Math.sin(fRotRad) + fCamY * Math.cos(fRotRad);

		var iDeltaY = -iCamHeight;
		var iDeltaX = iCamDist + fTransY;

		var iZ = correctCamZ(fSprite.z,iDeltaX);

		var iViewY = ((iDeltaY / iDeltaX) * iCamDist + iCamHeight) - iViewHeight + iZ;
		var fViewX = -(fTransX / (fTransY + iCamDist)) * iCamDist;

		this.div[i].style.zIndex = Math.round(10000 - fTransY);

		var iX = ((iWidth/2) + fViewX) * iScreenScale,
			iY = (iHeight - iViewY) * iScreenScale,
			fScale = fFocal / (fFocal + fTransY) * fSize;
		
		this.draw(i, iX,iY, fScale, iZ);
	}
	return res;
}

function redrawCanvas(i, fCamera) {
	var oViewContext = oViewCanvas.getContext("2d");
	oViewContext.fillStyle = "rgb("+ oMap.bgcolor +")";
	oViewContext.fillRect(0,0,oViewCanvas.width,oViewCanvas.height);

	oViewContext.save();
	oViewContext.translate(iViewCanvasWidth/2,iViewCanvasHeight-iViewYOffset);
	oViewContext.rotate((180 + fCamera.rotation) * Math.PI / 180);

	var posX = fCamera.x, posY = fCamera.y;
	if (oMapImg.image) {
		oViewContext.drawImage(
			oMapImg.image,
			-posX,-posY
		);
	}
	else {
		oViewContext.drawImage(
			oMapImg,
			-posX,-posY
		);
	}
	for (var j=0;j<oMap.assets.length;j++) {
		var asset = oMap.assets[j];
		oViewContext.drawImage(
			asset.canvas,
			asset.x-posX,asset.y-posY
		);
	}
	if (oMap.sea)
		oMap.sea.render(oViewContext,[posX,posY],1);

	oViewContext.restore();

	oScreens[i].getContext("2d").imageSmoothingEnabled = iSmooth;

	var oScreenContext = oScreens[i].getContext("2d");

	var vLineScale = 1/fLineScale, iViewCanvasYOffset = iViewCanvasHeight-iViewYOffset-1, iWidthScale = iWidth*vLineScale;

	for (var j=0;j<aStrips.length;j++) {

		var oStrip = aStrips[j];

		try {
			oScreenContext.drawImage(
				oViewCanvas,
				(iViewCanvasWidth-oStrip.stripwidth)/2,
				iViewCanvasYOffset - oStrip.mapz,
				oStrip.stripwidth,
				oStrip.mapzspan,

				0,(iHeight-oStrip.viewy)*vLineScale,iWidthScale,1
			);
		}
		catch (e) {}
	}
}

function byteType(key) {
	return {key:key,type:"byte"};
}
function shortType(key) {
	return {key:key,type:"short"};
}
function floatType(key) {
	return {key:key,type:"float"};
}
function intType(key) {
	return {key:key,type:"int"};
}
var itemBehaviors = {
	"banane": {
		size: 0.67,
		sync: [byteType("team"),floatType("x"),floatType("y"),floatType("z")],
		fadedelay: 100
	},
	"fauxobjet": {
		size: 1,
		sync: [byteType("team"),floatType("x"),floatType("y"),floatType("z")],
		fadedelay: 100
	},
	"poison": {
		size: 0.54,
		sync: [byteType("team"),floatType("x"),floatType("y"),floatType("z")],
		fadedelay: 100
	},
	"champi": {
		size: 0.54,
		sync: [floatType("x"),floatType("y"),floatType("z")],
		fadedelay: 100
	},
	"eclair": {
		size: 1,
		sync: [intType("owner")],
		fadedelay: 0,
		sprite: false,
		onlineResync: false,
		move: function(fSprite) {
			if (fSprite.countdown === undefined) {
				fSprite.countdown = 20;
				var oKart = aKarts.find(function(oKart) {
					return oKart.id == fSprite.owner;
				});
				if (oKart) {
					if (iSfx && !finishing && !oPlayers[0].cpu)
						playSoundEffect("musics/events/lightning.mp3");
					$mkScreen.style.opacity = 0.7;
					for (var i=0;i<aKarts.length;i++) {
						var kart = aKarts[i];
						if (!friendlyFire(kart,oKart)) {
							if (!kart.protect) {
								if (!isOnline || !i)
									kart.size = 0.6;
								kart.mini = Math.round(Math.max(kart.mini, 120-(kart.place-1)*40/(aKarts.length-1)));
								updateDriftSize(i);
								loseUsingItems(kart);
								kart.champi = 0;
								kart.spin(20);
								stopDrifting(i);
								dropCurrentItem(kart);
							}
							else
								kart.megachampi = (kart.megachampi<8 || kart.etoile ? kart.megachampi : 8);
						}
					}
				}
			}
			if (!isOnline || fSprite.id) {
				fSprite.countdown--;
				if (fSprite.countdown <= 0) {
					if (fSprite.countdown < -50)
						detruit(fSprite);
					else if (!fSprite.disabled) {
						fSprite.disabled = true;
						$mkScreen.style.opacity = 1;
					}
				}
			}
		},
		"del": function(item) {
			if (!item.disabled)
				$mkScreen.style.opacity = 1;
		}
	},
	"bloops": {
		size: 1,
		sync: [intType("owner")],
		fadedelay: 0,
		sprite: false,
		countdowns: [5,5,15,3,2,3,3,2,2,13,7,5,5,100,15],
		onlineResync: false,
		move: function(fSprite) {
			if (!fSprite.sprites) {
				if (items.bloops.length > 1) {
					detruit(fSprite);
					return;
				}
				fSprite.countdown = 0;
				fSprite.countstate = 0;
				var oKart = aKarts.find(function(oKart) {
					return oKart.id == fSprite.owner;
				});
				if (!oKart) return false;
				fSprite.sprites = new Array(oPlayers.length);
				for (var i=0;i<aKarts.length;i++) {
					var kart = aKarts[i];
					if ((kart === oKart) || (!friendlyFire(kart,oKart) && !kart.protect)) {
						if (i < oPlayers.length) {
							var oSprites = {
								"bloops": {
									elt: document.createElement("img"),
									mine: (kart === oKart)
								}
							};
							var oBloops = oSprites.bloops.elt;
							oBloops.src = "images/sprites/sprite_blooper.png";
							oBloops.className = "pixelated";
							oBloops.style.position = "absolute";
							oBloops.style.width = (iScreenScale*7) +"px";
							oSprites.bloops.x = 36;
							oSprites.bloops.y = oSprites.bloops.mine ? 18:12;
							oBloops.style.zIndex = 19001;
							oBloops.style.opacity = oSprites.bloops.mine ? 1:0;
							oContainers[i].appendChild(oBloops);
							fSprite.sprites[i] = oSprites;
						}
						kart.bloops = fSprite;
					}
				}
				fSprite.effective = function(oKart) {
					return (this.countstate == 13) && !oKart.unbloop && this.owner !== oKart.id;
				}
			}
			var itemBehavior = itemBehaviors["bloops"];
			var sTime = itemBehavior.countdowns[fSprite.countstate];
			var iTime = fSprite.countdown/sTime;
			fSprite.countdown++;
			var jTime = fSprite.countdown/sTime;
			for (var i=0;i<fSprite.sprites.length;i++) {
				var iSprite = fSprite.sprites[i];
				var oKart = aKarts[i];
				if (iSprite) {
					var oBloops = iSprite.bloops;
					if (!oBloops) continue;
					switch (fSprite.countstate) {
					case 0:
						if (oBloops.mine) {
							oBloops.y -= 2;
							if (!iTime && iSfx && !finishing)
								playSoundEffect("musics/events/bloops0.mp3");
						}
						break;
					case 1:
						if (oBloops.mine) {
							oBloops.y -= 0.5*Math.cos(Math.PI*iTime/2);
							oBloops.elt.style.opacity = 1-jTime;
							if (fSprite.countdown == sTime) {
								oContainers[i].removeChild(oBloops.elt);
								delete iSprite.bloops;
							}
						}
						break;
					case 3:
						oBloops.elt.style.opacity = jTime;
						break;
					case 4:
						if (!iTime && iSfx && !finishing && !i)
							playSoundEffect("musics/events/bloops.mp3");
						oBloops.y += 2;
						break;
					case 5:
						oBloops.y -= 2*Math.cos(Math.PI*iTime/2);
						break;
					case 6:
						var iTime0 = 0.2;
						oBloops.x += 2;
						oBloops.y -= 2*Math.cos(Math.PI*(iTime+iTime0)/(1+iTime0));
						break;
					case 7:
						oBloops.x += 2;
						oBloops.y += 2;
						break;
					case 8:
						oBloops.x += 2;
						oBloops.y += 2;
						break;
					case 9:
						var oBackoff = Math.pow(Math.cos(Math.PI*iTime*0.45),1.2);
						oBloops.x -= 6*Math.sin(Math.PI*1.5*iTime)*oBackoff;
						oBloops.y -= 4*Math.cos(Math.PI*1.5*iTime)*oBackoff;
						break;
					case 10:
						oBloops.x += 2;
						oBloops.y -= 2.5*Math.cos(Math.PI*iTime);
						break;
					case 11:
						oBloops.x -= Math.sin(Math.PI*jTime/2);
						oBloops.y -= 1;
						break;
					case 12:
						oBloops.elt.style.opacity = 1-jTime;
						if (!iSprite.ink) {
							var inkSizes = [];
							var totalInkSize = 3000;
							var minInkSize = 100, maxInkSize = 500;
							while (totalInkSize > 0) {
								var inkSize = minInkSize + Math.random()*(maxInkSize-minInkSize);
								if (inkSize > totalInkSize) {
									inkSize = totalInkSize;
									if (inkSize < minInkSize)
										inkSize = minInkSize;
								}
								inkSizes.push(inkSize);
								totalInkSize -= inkSize;
							}
							iSprite.ink = [];
							var r = iWidth/iHeight, a = Math.sqrt(inkSizes.length*r), b = a/r;
							var b0 = Math.ceil(b);
							var aStep = iWidth/a;
							var bStep = iHeight/b0;
							var jStep = (a*b0)/inkSizes.length;
							function centeredRandom(bias) {
								return Math.random()-bias;
							}
							for (var j=0;j<inkSizes.length;j++) {
								var jnc = (j+0.5)*jStep;
								var jnc0 = jnc/a;
								var x0 = (jnc%a)*aStep;
								var y0 = (Math.floor(jnc0)+0.5)*bStep;
								var xs = jnc0%1, ys = (Math.floor(jnc0)+0.5)/b0;
								xs = 0.5 + (xs-0.5)/2;
								ys = 0.5 + (ys-0.5)/2;
								var r0 = Math.sqrt(inkSizes[j]);
								var oInk = {
									x: x0 + 0.6*r0*centeredRandom(xs),
									y: y0 + 0.5*r0*centeredRandom(ys),
									theta: 360*Math.random(),
									w: r0*(1 + centeredRandom(0.5)*0.1),
									h: r0*(1 + centeredRandom(0.5)*0.1)
								};
								oInk.y -= 1075/(oInk.w*oInk.h);
								iSprite.ink.push(oInk);
							}
							for (var j=0;j<iSprite.ink.length;j++) {
								var oInk = iSprite.ink[j];
								var oImg = document.createElement("img");
								oImg.src = "images/sprites/sprite_ink.png";
								oImg.style.position = "absolute";
								oImg.style.transform = oImg.style.WebkitTransform = oImg.style.MozTransform = "rotate("+Math.round(oInk.theta)+"deg)";
								oImg.className = "pixelated";
								oImg.style.zIndex = 19000;
								oImg.style.opacity = 0.95;
								oContainers[i].appendChild(oImg);
								oInk.elt = oImg;
							}
						}
						for (var j=0;j<iSprite.ink.length;j++) {
							var oInk = iSprite.ink[j];
							var oImg = oInk.elt;
							oImg.style.left = Math.round((oInk.x-oInk.w*jTime/2)*iScreenScale) +"px";
							oImg.style.top = Math.round((oInk.y-oInk.h*jTime/2)*iScreenScale) +"px";
							oImg.style.width = Math.round(oInk.w*jTime*iScreenScale) +"px";
							oImg.style.height = Math.round(oInk.h*jTime*iScreenScale) +"px";
						}
						break;
					case 13:
						for (var j=0;j<iSprite.ink.length;j++) {
							var oInk = iSprite.ink[j];
							var oImg = oInk.elt;
							oInk.y += 20/(oInk.w*oInk.h);
							oImg.style.top = Math.round((oInk.y-oInk.h/2)*iScreenScale) +"px";
						}
						break;
					case 14:
						for (var j=0;j<iSprite.ink.length;j++) {
							var oInk = iSprite.ink[j];
							var oImg = oInk.elt;
							oInk.y += 10/(oInk.w*oInk.h);
							oImg.style.top = Math.round((oInk.y-oInk.h/2)*iScreenScale) +"px";
							if (!oKart.unbloop)
								oImg.style.opacity = 0.95*(1-iTime);
						}
					}
					oBloops.elt.style.left = (iScreenScale*oBloops.x) +"px";
					oBloops.elt.style.top = (iScreenScale*oBloops.y) +"px";
				}
			}
			if (fSprite.countstate >= 12) {
				for (var i=0;i<aKarts.length;i++) {
					var oKart = aKarts[i];
					if (!oKart.unbloop && (oKart.protect || oKart.champi || oKart.tombe))
						oKart.unbloop = ((fSprite.countstate == 12) && oKart.protect) ? 5:1;
					if (oKart.unbloop) {
						if (oKart.unbloop <= 5) {
							var uTime = oKart.unbloop/5;
							if ((oKart.bloops === fSprite) && (fSprite.owner !== oKart.id)) {
								for (var j=0;j<oPlayers.length;j++)
									oKart.sprite[j].img.style.filter = "brightness("+ (0.15+0.85*uTime) +")";
							}
							var iSprite = fSprite.sprites[i];
							if (iSprite && iSprite.ink) {
								for (var j=0;j<iSprite.ink.length;j++) {
									var oInk = iSprite.ink[j];
									var oImg = oInk.elt;
									oImg.style.opacity = 0.95*(1-uTime);
								}
							}
							oKart.unbloop++;
						}
					}
				}
			}
			switch (fSprite.countstate) {
			case 12:
				for (var i=0;i<aKarts.length;i++) {
					var oKart = aKarts[i];
					if (!oKart.unbloop && (oKart.bloops === fSprite) && (fSprite.owner !== oKart.id)) {
						for (var j=0;j<oPlayers.length;j++)
							oKart.sprite[j].img.style.filter = "brightness("+ (1-0.85*jTime) +")";
					}
				}
				break;
			case 14:
				for (var i=0;i<aKarts.length;i++) {
					var oKart = aKarts[i];
					if (!oKart.unbloop && (oKart.bloops === fSprite) && (fSprite.owner !== oKart.id)) {
						for (var j=0;j<oPlayers.length;j++)
							oKart.sprite[j].img.style.filter = "brightness("+ (0.15+0.85*jTime) +")";
					}
				}
				break;
			}
			if (fSprite.countdown >= sTime) {
				fSprite.countstate++;
				fSprite.countdown = 0;
				if (fSprite.countstate >= itemBehavior.countdowns.length)
					detruit(fSprite);
			}
		},
		"del": function(fSprite) {
			if (!fSprite.sprites) return;
			for (var i=0;i<fSprite.sprites.length;i++) {
				var iSprite = fSprite.sprites[i];
				if (iSprite) {
					if (iSprite.bloops)
						oContainers[i].removeChild(iSprite.bloops.elt);
					var oInk = iSprite.ink;
					if (oInk) {
						for (var j=0;j<oInk.length;j++)
							oContainers[i].removeChild(oInk[j].elt);
					}
				}
			}
			for (var i=0;i<aKarts.length;i++) {
				if (aKarts[i].bloops === fSprite) {
					for (var j=0;j<oPlayers.length;j++)
						aKarts[i].sprite[j].img.style.filter = "";
					delete aKarts[i].bloops;
					delete aKarts[i].unbloop;
				}
			}
		}
	},
	"carapace": {
		size: 0.67,
		sync: [byteType("team"),floatType("x"),floatType("y"),floatType("z"),floatType("vx"),floatType("vy"),intType("owner"),byteType("lives")],
		fadedelay: 300,
		move: function(fSprite) {
			var fNewPosX;
			var fNewPosY;
				
			var fMoveX = fSprite.vx, fMoveY = fSprite.vy;
			
			if (fMoveX || fMoveY) {
				fNewPosX = fSprite.x + fMoveX;
				fNewPosY = fSprite.y + fMoveY;
	
				for (var k=0;k<oPlayers.length;k++)
					fSprite.sprite[k].setState(1-fSprite.sprite[k].getState());
			}
			else {
				fNewPosX = fSprite.x;
				fNewPosY = fSprite.y;
			}
	
			var roundX1 = fSprite.x;
			var roundY1 = fSprite.y;
			var roundX2 = fNewPosX;
			var roundY2 = fNewPosY;
	
			if (((fSprite.owner != -1) && tombe(roundX1, roundY1)) || touche_banane(roundX1, roundY1) || touche_banane(roundX2, roundY2) || touche_crouge(roundX1, roundY1) || touche_crouge(roundX2, roundY2) || touche_cverte(roundX1, roundY1, [fSprite]) || touche_cverte(roundX2, roundY2, [fSprite]))
				detruit(fSprite,true);
			else if ((fSprite.owner == -1) || canMoveTo(fSprite.x,fSprite.y,0, fMoveX,fMoveY)) {
				fSprite.x = fNewPosX;
				fSprite.y = fNewPosY;
				tendsToSpeed(fSprite, 8, 0.1);
			}
			else {
				fSprite.lives--;
				if (fSprite.lives > 0) {
					var horizontality = getHorizontality(fSprite.x,fSprite.y, fMoveX,fMoveY);
					var u = Math.hypot(horizontality[0],horizontality[1]);
					var ux = horizontality[0]/u, uy = horizontality[1]/u;
					var m_u = fMoveX*ux + fMoveY*uy;
					fSprite.vx = 2*m_u*ux-fSprite.vx;
					fSprite.vy = 2*m_u*uy-fSprite.vy;
				}
				else
					detruit(fSprite);
			}
		}
	},
	"bobomb": {
		size: 1,
		sync: [byteType("team"),floatType("x"),floatType("y"),floatType("z"),floatType("theta"),byteType("countdown"),byteType("cooldown")],
		fadedelay: 0,
		move: function(fSprite) {	
			if (fSprite.theta != -1) {
				var hauteur = 0;
				if (fSprite.countdown) {
					fSprite.countdown--;
					var fMoveX = 15 * direction(0, fSprite.theta);
					var fMoveY = 15 * direction(1, fSprite.theta);
	
					var fNewPosX = fSprite.x + fMoveX;
					var fNewPosY = fSprite.y + fMoveY;
	
					fSprite.x = fNewPosX;
					fSprite.y = fNewPosY;
					hauteur = fSprite.countdown;
				}
				else {
					if (tombe(Math.round(fSprite.x), Math.round(fSprite.y)))
						detruit(fSprite);
					if (--fSprite.cooldown == 30)
						fSprite.cooldown -= 12;
					if (fSprite.cooldown < -10)
						detruit(fSprite);
				}
				fSprite.z = (-Math.abs(hauteur-8) + 8) * 2;
			}
		},
		render: function(fSprite,i) {
			if (fSprite.cooldown <= 0) {
				if (!i && (fSprite.size == 1)) {
					var fLoad;
					for (var k=0;k<strPlayer.length;k++) {
						makeSpriteExplode(fSprite,"explosion",k);
						if (fSprite.sprite[k].div.style.display == "block")
							fLoad = k;
					}
					if (!isOnline && (fLoad != undefined)) {
						fSprite.sprite[fLoad].img.onload = function() {
							bCounting = false;
							fSprite.sprite[fLoad].img.onload = undefined;
							fSprite.size = 6;
							reprendre(false);
							playDistSound({x:fSprite.x,y:fSprite.y},"musics/events/boom.mp3",200);
						}
						bCounting = true;
						interruptGame();
					}
					else {
						fSprite.size = 6;
						playDistSound({x:fSprite.x,y:fSprite.y},"musics/events/boom.mp3",200);
					}
				}
				fSprite.sprite[i].div.style.opacity = 1+fSprite.cooldown/10;
			}
		},
		drop: function(fSprite, oKart) {
			fSprite.theta = oKart.rotation+180;
			fSprite.cooldown = 60;
			fSprite.countdown = 1;
		}
	},
	"carapace-rouge": {
		size: 0.67,
		sync: [byteType("team"),floatType("x"),floatType("y"),floatType("z"),floatType("theta"),intType("owner"),shortType("aipoint"),byteType("aimap"),intType("target")],
		fadedelay: 300,
		move: function(fSprite) {
			var fNewPosX;
			var fNewPosY;

			for (var l=0;l<2;l++) {
				if (fSprite.owner != -1) {

					if (!l) {
						for (var k=0;k<oPlayers.length;k++)
							fSprite.sprite[k].setState(1-fSprite.sprite[k].getState());
					}

					if (fSprite.target >= 0) {
						var tCible = aKarts.find(function(oKart) {
							return oKart.id == fSprite.target;
						});
						var fDist = Math.pow(tCible.x-fSprite.x, 2) + Math.pow(tCible.y-fSprite.y, 2);
						if (fDist < 500) {
							fNewPosX = tCible.x;
							fNewPosY = tCible.y;
							if (tCible.using.length && (tCible.using[0].type != "fauxobjet")) {
								var rAngle = Math.atan2(fSprite.y-fNewPosY,fSprite.x-fNewPosX) - (90-tCible.rotation)*Math.PI/180;
								var pi2 = Math.PI*2;
								while (rAngle < 0)
									rAngle += pi2;
								while (rAngle > pi2)
									rAngle -= pi2;
								if (rAngle > Math.PI)
									rAngle = pi2-rAngle;
								if (Math.abs(rAngle) > 2.5) {
									fNewPosX = tCible.using[0].x;
									fNewPosY = tCible.using[0].y;
									fSprite.x = fNewPosX;
									fSprite.y = fNewPosY;
									detruit(fSprite);
									detruit(tCible.using[0],true);
									return;
								}
								else {
									tCible.using[0].x -= 2 * direction(0,tCible.rotation);
									tCible.using[0].y -= 2 * direction(1,tCible.rotation);
								}
							}
							l = 1;
						}
						else {
							fDist = Math.sqrt(fDist);
							var fMoveX = (tCible.x-fSprite.x)*5/fDist;
							var fMoveY = (tCible.y-fSprite.y)*5/fDist;
							fNewPosX = fSprite.x + fMoveX;
							fNewPosY = fSprite.y + fMoveY;
						}
					}
					else {
						var fMoveX, fMoveY;
						if (fSprite.aipoint >= 0) {
							var iLocal = oMap.aipoints[fSprite.aimap];
							var oBox = iLocal[fSprite.aipoint];
							fMoveX = oBox[0] - fSprite.x;
							fMoveY = oBox[1] - fSprite.y;
							var fDist2 = fMoveX*fMoveX + fMoveY*fMoveY;
							if (fDist2 < 100) {
								if (fSprite.aipoint < iLocal.length - 1) fSprite.aipoint++;
								else fSprite.aipoint = 0;
							}
							var fNewMove = Math.sqrt(fMoveX*fMoveX + fMoveY*fMoveY)/5;
							fMoveX /= fNewMove;
							fMoveY /= fNewMove;
						}
						else {
							if (fSprite.aipoint == -1) {
								if (course != "BB") {
									var minDist = 1250;
									for (var j=0;j<oMap.aipoints.length;j++) {
										var iLocal = oMap.aipoints[j];
										for (var k=0;k<iLocal.length;k++) {
											var oBox = iLocal[k];
											var fDist2 = (oBox[0]-fSprite.x)*(oBox[0]-fSprite.x) + (oBox[1]-fSprite.y)*(oBox[1]-fSprite.y);
											if (fDist2 < minDist) {
												fSprite.aimap = j;
												fSprite.aipoint = k + 1;
												if (fSprite.aipoint == iLocal.length)
													fSprite.aipoint = 0;
												minDist = fDist2;
											}
										}
									}
								}
								fMoveX = 5 * direction(0, fSprite.theta);
								fMoveY = 5 * direction(1, fSprite.theta);
							}
							else {
								fMoveX = 2.5 * direction(0, fSprite.theta);
								fMoveY = 2.5 * direction(1, fSprite.theta);
							}
						}

						fNewPosX = fSprite.x + fMoveX;
						fNewPosY = fSprite.y + fMoveY;

						if (fSprite.aipoint != -2) {
							var maxDist = 4000;
							var tCible;
		
							for (var k=0;k<aKarts.length;k++) {
								var pCible = aKarts[k];
								if (pCible.id != fSprite.owner && !sameTeam(fSprite.team,pCible.team) && !pCible.tombe && !pCible.loose) {
									var fDirX = pCible.x-fNewPosX, fDirY = pCible.y-fNewPosY;
									var fDist = Math.pow(fDirX, 2) + Math.pow(fDirY, 2);
									if (fDist < maxDist) {
										var dAngle = fMoveX*fDirX + fMoveY*fDirY;
										dAngle /= Math.sqrt(fDist*(fMoveX*fMoveX + fMoveY*fMoveY));
										if (dAngle > 0.4) {
											maxDist = fDist;
											tCible = pCible;
										}
									}
								}
							}
							if (tCible) {
								fSprite.target = tCible.id;
								var oPlayer = oPlayers[0];
								if (isOnline && ((tCible == oPlayer) || (fSprite.owner == oPlayer.id)))
									syncItems.push(fSprite);
							}
						}
					}
				}
				else {
					fNewPosX = fSprite.x;
					fNewPosY = fSprite.y;
				}

				var fMoveX = fNewPosX-fSprite.x, fMoveY = fNewPosY-fSprite.y;
				if ((fSprite.owner == -1 || (!tombe(fNewPosX, fNewPosY) && canMoveTo(fSprite.x,fSprite.y,0, fMoveX,fMoveY))) && !touche_banane(fNewPosX, fNewPosY) && !touche_banane(fSprite.x, fSprite.y) && !touche_crouge(fNewPosX, fNewPosY, [fSprite]) && !touche_crouge(fSprite.x, fSprite.y, [fSprite]) && !touche_cverte(fNewPosX, fNewPosY) && !touche_cverte(fSprite.x, fSprite.y)) {
					fSprite.x = fNewPosX;
					fSprite.y = fNewPosY;
				}
				else {
					detruit(fSprite);
					return;
				}
			}
		}
	},
	"carapace-bleue": {
		size: 1,
		sync: [byteType("team"),floatType("x"),floatType("y"),floatType("z"),intType("target"),byteType("cooldown"),shortType("aipoint"),byteType("aimap")],
		fadedelay: 0,
		cooldown0: 15,
		cooldown1: 2,
		move: function(fSprite) {
			var cible = -1;
			if (fSprite.target != -1) {
				var cible = -1;
				for (var k=0;k<aKarts.length;k++) {
					if (aKarts[k].id == fSprite.target) {
						cible = k;
						break;
					}
				}
			}
			if (fSprite.aipoint == -1) {
				if (fSprite.cooldown > 0) {
					var oKart = aKarts[cible];
					var fMoveX = fSprite.x - oKart.x;
					var fMoveY = fSprite.y - oKart.y;
					var fMove2 = fMoveX*fMoveX + fMoveY*fMoveY;
					var itemBehavior = itemBehaviors["carapace-bleue"];
					if (fSprite.cooldown == itemBehavior.cooldown0) {
						if (fMove2 > 100) {
							var fNewMove = Math.sqrt(fMove2)/10;
							fMoveX /= fNewMove;
							fMoveY /= fNewMove;
			
							for (var k=0;k<oPlayers.length;k++)
								fSprite.sprite[k].setState(1-fSprite.sprite[k].getState());
						}
						else
							fSprite.cooldown--;
					}
					else {
						if (fSprite.cooldown < 5) {
							var maxSpeed2 = 32;
							if (oKart.champi > 0) {
								if (oKart.champi < (oKart.champior ? 8:16))
									maxSpeed2 = 200;
							}
							else if (oKart.turbodrift)
								maxSpeed2 = 64;
							if (fMove2 > maxSpeed2) {
								var fNewMove = Math.sqrt(fMove2/maxSpeed2);
								fMoveX /= fNewMove;
								fMoveY /= fNewMove;
							}
						}
						var r = (fSprite.cooldown-itemBehavior.cooldown1)/(itemBehavior.cooldown0-itemBehavior.cooldown1);
						if (r < 0) r = 0;
						var rX0 = 8, rX = rX0*r, rZ0 = 8, rZ = rZ0*r;
						var theta = 2*Math.PI*r;
						var pTheta = oKart.rotation*Math.PI/180;
						var z0 = (15 + rZ0);
						fSprite.z = z0 - rZ*Math.cos(theta);
						fMoveX -= rX*Math.sin(theta)*Math.cos(pTheta);
						fMoveY += rX*Math.sin(theta)*Math.sin(pTheta);
						for (var k=0;k<oPlayers.length;k++)
							fSprite.sprite[k].setState(Math.round(Math.random()));
						fSprite.cooldown--;
						if (!fSprite.cooldown) {
							for (var k=0;k<oPlayers.length;k++)
								fSprite.sprite[k].setState(0);
						}
					}
		
					fSprite.x -= fMoveX;
					fSprite.y -= fMoveY;
				}
				else {
					fSprite.z = 0;
					if (isOnline && (fSprite.target == oPlayers[0].id) && (fSprite.cooldown < -10))
						fSprite.cooldown = 0;
					fSprite.cooldown--;
					var delLimit = (isOnline&&(fSprite.target!=oPlayers[0].id)) ? -70:-10;
					if (fSprite.cooldown < delLimit)
						detruit(fSprite);
				}
			}
			else {
				var isBB = (course == "BB");
				if (!isBB) {
					var aipoints = oMap.aipoints[fSprite.aimap];
					var dSpeed = 12;
					var aX = fSprite.x, aY = fSprite.y;
					while (dSpeed > 0) {
						var target = aipoints[fSprite.aipoint];
						var dist = Math.hypot(target[0]-fSprite.x, target[1]-fSprite.y);
						if (dist > dSpeed) {
							fSprite.x += (target[0]-fSprite.x)*dSpeed/dist;
							fSprite.y += (target[1]-fSprite.y)*dSpeed/dist;
							dSpeed = 0;
						}
						else {
							dSpeed -= dist;
							fSprite.x = target[0];
							fSprite.y = target[1];
							fSprite.aipoint++;
							if (fSprite.aipoint === aipoints.length)
								fSprite.aipoint = 0;
						}
					}
				}
				if (cible == -1) {
					cible = aKarts.length-1;
					for (var cPlace=1;cPlace<=aKarts.length;cPlace++) {
						for (var k=0;k<aKarts.length;k++) {
							if (aKarts[k].place == cPlace) {
								if (((aKarts[k].tours <= oMap.tours) || (course == "BB")) && !sameTeam(fSprite.team,aKarts[k].team)) {
									cible = k;
									cPlace = aKarts.length;
								}
								break;
							}
						}
					}
				}
				var oKart = aKarts[cible];
				var fDist2 = (oKart.x-fSprite.x)*(oKart.x-fSprite.x) + (oKart.y-fSprite.y)*(oKart.y-fSprite.y);
				if ((fDist2 < 20000) || isBB) {
					if ((fSprite.target == -1) && (!isOnline || !cible) || isBB) {
						fSprite.target = oKart.id;
						if (isOnline)
							syncItems.push(fSprite);
					}
					if (fSprite.target != -1) {
						var aDist2 = (oKart.x-aX)*(oKart.x-aX) + (oKart.y-aY)*(oKart.y-aY);
						if ((fDist2 < 5000) || (fDist2 > aDist2) || isBB)
							fSprite.aipoint = -1;
					}
				}
				for (var k=0;k<oPlayers.length;k++)
					fSprite.sprite[k].setState(1-fSprite.sprite[k].getState());
			}
		},
		render: function(fSprite,i) {
			if (fSprite.cooldown <= 0) {
				if (!i && fSprite.size == 1) {
					var fLoad;
					for (var k=0;k<strPlayer.length;k++) {
						makeSpriteExplode(fSprite,"explosionB",k);
						if (fSprite.sprite[k].div.style.display == "block")
							fLoad = k;
					}
					var cible = -1;
					for (var k=0;k<aKarts.length;k++) {
						if (aKarts[k].id == fSprite.target) {
							cible = k;
							break;
						}
					}
					if (!isOnline && (fLoad != undefined)) {
						fSprite.sprite[fLoad].img.onload = function() {
							bCounting = false;
							fSprite.sprite[fLoad].img.onload = undefined;
							fSprite.size = 8;
							reprendre(false);
							playDistSound(aKarts[cible],"musics/events/boom.mp3",200);
						}
						bCounting = true;
						interruptGame();
					}
					else {
						fSprite.size = 8;
						playDistSound(aKarts[cible],"musics/events/boom.mp3",200);
					}
				}
				fSprite.sprite[i].div.style.opacity = Math.max(1+fSprite.cooldown/10,0);
			}
		}
	}
}
var itemTypes = ["banane","fauxobjet","carapace","bobomb","poison","carapace-rouge","carapace-bleue","eclair","bloops","champi"];
var items = {};
for (var i=0;i<itemTypes.length;i++)
	items[itemTypes[i]] = [];
var decorBehaviors = {
	taupe:{
		spin: 20,
		init:function(decorData,i,iG) {
			if (decorData.length > 3) return;
			decorData[3] = 0;
			decorData[4] = (iG%2) ? 9:0;
		},
		move:function(decorData) {
			decorData[4]++;
			if (decorData[4] >= 0) {
				if (decorData[4]) {
					decorData[3] += (decorData[4]<4) ? 2:-1;
					if (decorData[4] == 10) {
						decorData[4] = -20;
						decorData[3] = 10;
						for (var j=0;j<oPlayers.length;j++)
							decorData[2][j].img.style.display = "none";
					}
				}
				else {
					for (var j=0;j<oPlayers.length;j++)
						decorData[2][j].img.style.display = "block";
					decorData[3] = 0;
				}
			}
		}
	},
	poisson:{
		spin: 20,
		movable:true,
		preinit:function(decorsData) {
			this.scope = {
				limite: new Array()
			};
			for (var i=0;i<decorsData.length;i++)
				this.scope.limite[i] = [0,0];
		},
		init:function(decorData,i,iG) {
			if (decorData.length > 3) return;
			var pos1 = [3,0];
			var pos2 = [-1,1];
			decorData[3] = pos1[iG%2];
			decorData[4] = pos2[iG%2];
		},
		move:function(decorData,i) {
			decorData[3] += decorData[4];
			if (decorData[3]) {
				if (decorData[3] == 3) {
					decorData[4] = -1;
					for (var j=0;j<2;j++) {
						var o = Math.floor(9*Math.random())-4;
						if (Math.abs(this.scope.limite[i][j]+o) > 10)
							o = -o;
						this.scope.limite[i][j] += o;
						decorData[j] += o;
					}
				}
			}
			else
				decorData[4] = 1;
		}
	},
	cheepcheep:{
		spin: 20,
		movable:true,
		preinit:function(decorsData) {
			this.preinit = decorBehaviors.poisson.preinit.bind(this);
			this.init = decorBehaviors.poisson.init.bind(this);
			this.move_ = decorBehaviors.poisson.move.bind(this);
			this.preinit(decorsData);
		},
		move: function(decorData,i,iG) {
			this.move_(decorData,i,iG);
			if (decorData[3]%3 == 0) {
				for (var j=0;j<oPlayers.length;j++)
					decorData[2][j].setState(decorData[3] ? 1:0);
			}
		}
	},
	plante:{
		spin: 20,
		init:function(decorData,i,iG) {
			if (decorData.length > 3) return;
			decorData[3] = undefined;
			decorData[4] = (1+iG*2)%8;
		},
		move:function(decorData) {
			decorData[4]++;
			if (decorData[4] == 4) {
				for (var j=0;j<oPlayers.length;j++)
					decorData[2][j].setState(1);
			}
			else if (decorData[4] == 8) {
				for (var j=0;j<oPlayers.length;j++)
					decorData[2][j].setState(0);
				decorData[4] = 0;
			}
		}
	},
	thwomp:{
		spin: 20,
		init: function(decorData,i,iG) {
			if (decorData.length > 3) return;
			var pos1 = [20,0];
			var pos2 = [0,10];
			decorData[3] = pos1[iG%2];
			decorData[4] = pos2[iG%2];
		},
		move: function(decorData) {
			if (decorData[4] < 0) {
				decorData[4]++;
				if (!decorData[4]) {
					decorData[4] = -1;
					if (decorData[3] < 20)
						decorData[3] += 2;
					else
						decorData[4] = 20;
				}
			}
			else if (decorData[4])
				decorData[4]--;
			else {
				decorData[3] -= 8;
				if (decorData[3] < 0) {
					decorData[3] = 0;
					decorData[4] = -15;
				}
			}
		}
	},
	spectre:{
		spin: 20,
		init:function(decorData,i,iG) {
			decorBehaviors.thwomp.init(decorData,i,iG);
		},
		move:function(decorData) {
			decorBehaviors.thwomp.move(decorData);
		}
	},
	tree:{
		hidable:true,
		hitbox:4,
		unbreaking:true,
		init:function(decorData) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 1;
				decorData[2][j].w = 50;
				decorData[2][j].h = 100;
				decorData[2][j].z = 0.12;
			}
		}
	},
	crabe:{
		spin: 20,
		movable:true,
		transparent:true,
		preinit:function(decorsData) {
			if (course == "BB")
				this.dodgable = true;
		},
		init:function(decorData,i,iG) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 3;
				decorData[2][j].h = 30;
				decorData[2][j].w = decorData[2][j].h*16/17;
			}
			if (decorData[4] == undefined)
				decorData[4] = (10000*Math.sin(iG+1))%Math.PI;
			var initPos = decorData[5];
			if (initPos == undefined)
				initPos = (iG*137)%400;
			decorData[5] = 0;
			while (decorData[5] != initPos)
				this.move(decorData);
		},
		hSpeed: 0.7,
		handleState: function(decorData,x) {
			x %= 16;
			if (x == 0) {
				for (var j=0;j<strPlayer.length;j++)
					decorData[2][j].setState(0);
			}
			else if ((x == 4) || (x == 12)) {
				for (var j=0;j<strPlayer.length;j++)
					decorData[2][j].setState(1);
			}
			else if (x == 8) {
				for (var j=0;j<strPlayer.length;j++)
					decorData[2][j].setState(2);
			}
		},
		move:function(decorData) {
			var x = decorData[5]+50;
			var e = Math.min(x%100,99-(x%100));
			if (e >= 5) {
				var dirX = this.hSpeed*Math.cos(decorData[4]), dirY = this.hSpeed*Math.sin(decorData[4]);
				if (e < 8) {
					dirX *= 0.5;
					dirY *= 0.5;
				}
				this.handleState(decorData,x);
				if ((x%200) < 100) {
					decorData[0] += dirX;
					decorData[1] += dirY;
				}
				else {
					decorData[0] -= dirX;
					decorData[1] -= dirY;
				}
			}
			decorData[5]++;
			if (decorData[5] >= 400)
				decorData[5] = 0;
		}
	},
	goomba:{
		spin: 20,
		movable:true,
		transparent:true,
		dodgable:true,
		preinit:function(decorsData) {
			this.init_ = decorBehaviors.crabe.init.bind(this);
			this.move = decorBehaviors.crabe.move.bind(this);
		},
		init:function(decorData,i,iG) {
			this.init_(decorData,i,iG);
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 2;
				decorData[2][j].w = 32;
				decorData[2][j].h = 36;
			}
		},
		handleState: function(decorData,x) {
			x %= 8;
			if (x == 0) {
				for (var j=0;j<strPlayer.length;j++)
					decorData[2][j].setState(0);
			}
			else if (x == 4) {
				for (var j=0;j<strPlayer.length;j++)
					decorData[2][j].setState(1);
			}
		},
		hSpeed:0.3
	},
	firesnake:{
		spin: 42,
		minSpeedToSpin:3.2,
		unbreaking:true,
		movable:true,
		transparent:true,
		dodgable:true,
		hitbox:6,
		hitboxH:7,
		init:function(decorData,i,iG) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 3;
				decorData[2][j].img.style.display = "none";
			}
			decorData[3] = 10;
			if (!decorData[4])
				decorData[4] = (1+50*iG)%280;
			if (!decorData[5])
				decorData[5] = 0;
			if (!decorData[6])
				decorData[6] = 0;
			decorData[7] = [decorData[0],decorData[1]];
			decorData[8] = [decorData[0],decorData[1]];
			decorData[9] = [decorData[0],decorData[1]];
			decorData[0] = -10;
			decorData[1] = -10;
		},
		move:function(decorData,i,iG) {
			var rSeed = timer+iG;
			if (oPlayers[0].cpu)
				rSeed = 10000*Math.random();
			switch (decorData[5]) {
			case 0:
				decorData[4]--;
				if (decorData[4] <= 0) {
					for (var j=0;j<strPlayer.length;j++)
						decorData[2][j].img.style.display = "block";
					decorData[0] = decorData[7][0] + 10*Math.sin(rSeed);
					decorData[1] = decorData[7][1] + 10*Math.sin(rSeed+1);
					decorData[8][0] = decorData[0];
					decorData[8][1] = decorData[1];
					decorData[9][0] = decorData[0] + 10*Math.sin(rSeed+2);
					decorData[9][1] = decorData[1] + 10*Math.sin(rSeed+3);
					decorData[3] = 50;
					decorData[5] = 1;
				}
				break;
			case 1:
				decorData[3] -= 4;
				if (decorData[3] <= 0) {
					decorData[3] = 0;
					decorData[4] = 20;
					decorData[5] = 2;
					decorData[6] = 5;
					for (var j=0;j<strPlayer.length;j++)
						decorData[2][j].setState(1);
				}
				var l = decorData[3]/40;
				decorData[9][0] += l*(decorData[8][0]-decorData[9][0]);
				decorData[9][1] += l*(decorData[8][1]-decorData[9][1]);
				decorData[0] = decorData[9][0];
				decorData[1] = decorData[9][1];
				break;
			case 2:
				var x = (18-decorData[4])/18;
				if (x >= 0) {
					var e = (iG%2)?1:-1;
					decorData[0] = decorData[9][0] - 4*x*e*Math.cos(4*x) + x*Math.sin(rSeed);
					decorData[1] = decorData[9][1] - 4*x*e*Math.sin(4*x) + x*Math.sin(rSeed+1);
					decorData[3] = Math.max(1.2*Math.sin(4*x) + x*0.3*Math.sin(rSeed+1), 0);
					if (decorData[4]%6 == 3) {
						for (var j=0;j<strPlayer.length;j++)
							decorData[2][j].setState(3-decorData[2][j].getState());
					}
				}
				decorData[4]--;
				if (decorData[4] <= 0) {
					decorData[5] = 3;
					decorData[8][0] = decorData[0];
					decorData[8][1] = decorData[1];
					decorData[4] = 0;
					decorData[9][0] = decorData[7][0] + 10*Math.sin(rSeed);
					decorData[9][1] = decorData[7][1] + 10*Math.sin(rSeed+1);
				}
				break;
			case 3:
				var tf = 20;
				decorData[4]++;
				var l = Math.min(decorData[4]/tf, 1);
				decorData[0] = decorData[8][0] + l*(decorData[9][0]-decorData[8][0]);
				decorData[1] = decorData[8][1] + l*(decorData[9][1]-decorData[8][1]);
				decorData[3] = 30*l*(1-l);
				if (l == 1) {
					decorData[6]--;
					if (decorData[6] <= 0) {
						decorData[5] = 4;
						decorData[4] = 1;
					}
					else {
						decorData[5] = 2;
						decorData[4] = 20;
						decorData[5] = 2;
					}
				}
				break;
			case 4:
				decorData[4] -= 0.2;
				if (decorData[4] < 0) {
					for (var j=0;j<strPlayer.length;j++) {
						decorData[2][j].img.style.display = "none";
						decorData[2][j].img.style.opacity = 1;
						decorData[2][j].setState(0);
					}
					decorData[0] = -10;
					decorData[1] = -10;
					decorData[4] = 50;
					decorData[5] = 0;
					decorData[6] = 0;
				}
				else {
					for (var j=0;j<strPlayer.length;j++)
						decorData[2][j].img.style.opacity = decorData[4];
				}
				break;
			}
		}
	},
	fireplant:{
		hitbox:7,
		unbreaking:true,
		preinit:function() {
			if (oMap.decor.fireball)
				this.fireball0 = oMap.decor.fireball.length;
			else {
				this.fireball0 = 0;
				oMap.decor.fireball = new Array();
			}
			for (var i=0;i<oMap.decor[this.type].length;i++)
				oMap.decor.fireball.push([-10,-10]);
		},
		init:function(decorData,i,iG) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 4;
				decorData[2][j].w = 54;
				decorData[2][j].h = decorData[2][j].w*40/24;
				decorData[2][j].z = 0.16;
			}
			if (decorData[4] == undefined)
				decorData[4] = (10000*Math.sin(iG+1))%(2*Math.PI);
			if (decorData[5] == undefined)
				decorData[5] = iG*17%65;
			decorData[6] = decorData[4];
			decorData[7] = 0;
		},
		move:function(decorData,i) {
			decorData[5]--;
			decorData[4] = decorData[6] + 0.5*Math.sin(decorData[7]);
			decorData[7] += 0.05;
			decorData[7] %= (2*Math.PI);
			if (decorData[5] == -1) {
				for (var j=0;j<strPlayer.length;j++)
					decorData[2][j].setState(decorData[2][j].getState()+1);
			}
			else if (decorData[5] == -7) {
				var oFireball = oMap.decor.fireball[this.fireball0+i];
				oFireball[0] = decorData[0];
				oFireball[1] = decorData[1];
				for (var j=0;j<strPlayer.length;j++) {
					oFireball[2][j].img.style.display = "block";
					oFireball[2][j].setState(0);
				}
				oFireball[3] = 10;
				oFireball[4] = decorData[4];
				oFireball[5] = 0;
				oFireball[6] = 35;
			}
			else if (decorData[5] == -9) {
				for (var j=0;j<strPlayer.length;j++)
				decorData[2][j].setState((decorData[2][j].getState()+1)%4);
				decorData[5] = Math.round(60 + 10*Math.sin(decorData[7]));
			}
		}
	},
	fireball:{
		spin: 42,
		unbreaking:true,
		movable:true,
		transparent:true,
		hitboxH:6,
		init:function(decorData,i) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 4;
				decorData[2][j].img.style.display = "none";
			}
		},
		move:function(decorData) {
			if (decorData[6] >= 0) {
				var speed = 7;
				decorData[0] += speed*Math.cos(decorData[4]);
				decorData[1] += speed*Math.sin(decorData[4]);
				decorData[3] += decorData[5];
				if (decorData[3] < 0) {
					decorData[3] = 0;
					decorData[5] = 5;
				}
				decorData[5] -= 3;
				decorData[6]--;
				if (decorData[6] < 0) {
					decorData[0] = -10;
					decorData[1] = -10;
					for (var j=0;j<strPlayer.length;j++)
						decorData[2][j].img.style.display = "none";
				}
				else if ((decorData[6]%2) == 0) {
					for (var j=0;j<strPlayer.length;j++)
						decorData[2][j].setState((decorData[2][j].getState()+3)%4);
				}
			}
		}
	},
	piranhaplant: {
		spin: 20,
		preinit:function(decorsData) {
			this.init_ = decorBehaviors.plante.init.bind(this);
			this.move = decorBehaviors.plante.move.bind(this);
		},
		init:function(decorData,i,iG) {
			this.init_(decorData,i,iG);
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 2;
				decorData[2][j].w = 32;
				decorData[2][j].h = 48;
				decorData[2][j].z = 0.1;
			}
		}
	},
	firebar:{
		spin:42,
		transparent:true,
		unbreaking:true,
		movable:true,
		init:function(decorData,i,iG) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].w = 32;
				decorData[2][j].h = 192;
				decorData[2][j].z = 0.035;
			}
			if (decorData[4] == undefined)
				decorData[4] = [[decorData[0],decorData[1],0,40],[decorData[0],decorData[1],20,40]];
			if (decorData[5] == undefined)
				decorData[5] = Math.round(10000*Math.abs(Math.sin(iG+3)))%decorData[4].length;
			if (decorData[6] == undefined)
				decorData[6] = 2*Math.round(10000*Math.abs(Math.sin(iG+2))%1)-1;
			if (decorData[7] == undefined)
				decorData[7] = Math.round(10000*Math.abs(Math.sin(iG+1)))%40;
		},
		move: function(decorData,i) {
			if (decorData[7])
				decorData[7]--;
			else {
				var target = decorData[4][decorData[5]];
				var diffX = target[0]-decorData[0], diffY = target[1]-decorData[1], diffZ = target[2]-decorData[3];
				var speedL = 22/25, speedZ = (diffZ > 0) ? 4:8;
				var diffL = Math.hypot(diffX,diffY);
				var arrived = true;
				if (speedL < diffL) {
					decorData[0] += diffX*speedL/diffL;
					decorData[1] += diffY*speedL/diffL;
					arrived = false;
				}
				else {
					decorData[0] = target[0];
					decorData[1] = target[1];
				}
				var lastZ = decorData[3];
				if (speedZ < Math.abs(diffZ)) {
					decorData[3] += Math.sign(diffZ)*speedZ;
					arrived = false;
				}
				else
					decorData[3] = target[2];
				if (lastZ != decorData[3]) {
					var oScale = Math.max(0,1-decorData[3]/20);
					for (var j=0;j<strPlayer.length;j++) {
						var oImg = decorData[2][j].img;
						oImg.style.transform = oImg.style.WebkitTransform = oImg.style.MozTransform = "scale("+ oScale +")";
					}
				}
				if (arrived) {
					decorData[7] = target[3];
					decorData[5] += decorData[6];
					if (decorData[5] < 0) {
						decorData[5] += 2;
						decorData[6] = 1;
					}
					else if (decorData[5] >= decorData[4].length) {
						decorData[5] -= 2;
						decorData[6] = -1;
					}
				}
			}
		}
	},
	fire3star:{
		unbreaking:true,
		transparent:true,
		hidden:true,
		preinit:function(decorsData) {
			if (!oMap.decor.fireballs)
				oMap.decor.fireballs = new Array();
			for (var i=0;i<decorsData.length;i++) {
				var decorData = decorsData[i];
				if (decorData[3] == undefined)
					decorData[3] = 18;
				if (decorData[4] == undefined) {
					var decorParams = getDecorParams(this,i);
					decorData[4] = Math.PI/2+decorParams.dir;
					if (isNaN(decorData[4])) decorData[4] = ((10000*Math.sin(i+2))%Math.PI);
					decorData[5] = 0.1;
				}
				else {
					if (decorData[5] == undefined)
						decorData[5] = (i%2 ? 1:-1)*0.1;
				}
				if (decorData[6] == undefined)
					decorData[6] = (10000*Math.sin(i+1))%Math.PI;
				var fireGroups = [];
				for (var j=0;j<3;j++) {
					var fireGroup = [];
					for (var k=0;k<2;k++) {
						var fireBall = [decorData[0],decorData[1],undefined,decorData[3]];
						oMap.decor.fireballs.push(fireBall);
						fireGroup.push(fireBall);
					}
					fireGroups.push(fireGroup);
				}
				var fireBall = [decorData[0],decorData[1],undefined,correctZInv(decorData[3])];
				oMap.decor.fireballs.push(fireBall);
				fireGroups.push([fireBall]);
				decorData[7] = fireGroups;
			}
		},
		init: function(decorData,i,iG) {
			this.move(decorData,i,iG);
		},
		move: function(decorData,i) {
			var x = decorData[0], y = decorData[1], z = decorData[3], phi = decorData[4], omega = decorData[5], theta = decorData[6];
			var cosPhi = Math.cos(phi), sinPhi = Math.sin(phi);
			var fireGroups = decorData[7];
			for (var j=0;j<3;j++) {
				var jTheta = theta + j*2*Math.PI/3;
				var cosTheta = Math.cos(jTheta), sinTheta = Math.sin(jTheta);
				var fireGroup = fireGroups[j];
				for (var k=0;k<2;k++) {
					var fireBall = fireGroup[k];
					var r = 7.5*(k+1);
					fireBall[0] = x + r*cosPhi*cosTheta;
					fireBall[1] = y - r*sinPhi*cosTheta;
					fireBall[3] = correctZInv(z + r*sinTheta);
				}
			}
			decorData[6] += omega;
			decorData[6] %= 2*Math.PI;
		}
	},
	firering:{
		unbreaking:true,
		transparent:true,
		hidden:true,
		preinit:function(decorsData) {
			if (!oMap.decor.fireballs)
				oMap.decor.fireballs = new Array();
			for (var i=0;i<decorsData.length;i++) {
				var decorData = decorsData[i];
				if (decorData[3] == undefined)
					decorData[3] = 18;
				if (decorData[4] == undefined) {
					var decorParams = getDecorParams(this,i);
					decorData[4] = Math.PI/2+decorParams.dir;
					if (isNaN(decorData[4])) decorData[4] = ((10000*Math.sin(i+2))%Math.PI);
					decorData[5] = 0.1;
				}
				else {
					if (decorData[5] == undefined)
						decorData[5] = (i%2 ? 1:-1)*0.1;
				}
				if (decorData[6] == undefined)
					decorData[6] = (10000*Math.sin(i+1))%Math.PI;
				var fireGroup = [];
				for (var j=0;j<5;j++) {
					var fireBall = [decorData[0],decorData[1],undefined,decorData[3]];
					oMap.decor.fireballs.push(fireBall);
					fireGroup.push(fireBall);
				}
				decorData[7] = fireGroup;
			}
		},
		init: function(decorData,i,iG) {
			this.move(decorData,i,iG);
		},
		move: function(decorData,i) {
			var x = decorData[0], y = decorData[1], z = decorData[3], phi = decorData[4], omega = decorData[5], theta = decorData[6];
			var r = 15;
			var cosPhi = Math.cos(phi), sinPhi = Math.sin(phi);
			var fireGroup = decorData[7];
			for (var j=0;j<fireGroup.length;j++) {
				var jTheta = theta + j*2*Math.PI/5;
				var cosTheta = Math.cos(jTheta), sinTheta = Math.sin(jTheta);
				var fireBall = fireGroup[j];
				fireBall[0] = x + r*cosPhi*cosTheta;
				fireBall[1] = y - r*sinPhi*cosTheta;
				fireBall[3] = correctZInv(z + r*sinTheta);
			}
			decorData[6] += omega;
			decorData[6] %= 2*Math.PI;
		}
	},
	fireballs:{
		unbreaking:true,
		transparent:true,
		spin:42,
		movable:true,
		hitboxH:6,
		init: function(decorData,i) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 1;
				decorData[2][j].w = 24;
				decorData[2][j].h = decorData[2][j].w;
			}
		}
	},
	billball:{
		hitbox:10,
		unbreaking:true,
		transparent:true,
		spin:42,
		movable:true,
		rotatable:true,
		dodgable:true,
		preinit:function(decorsData) {
			for (var i=0;i<decorsData.length;i++) {
				var iExtra = getDecorParams(this,i);
				var decorData = decorsData[i];
				if (decorData[3] == undefined)
					decorData[3] = 3;
				if (decorData[4] == undefined) {
					decorData[4] = 90+iExtra.dir*180/Math.PI;
					if (isNaN(decorData[4]))
						decorData[4] = 180;
				}
			}
			for (var i=1;i<4;i++) {
				var decorsData2 = oMap.decor["billball"+i];
				if (decorsData2) {
					for (var j=0;j<decorsData2.length;j++) {
						var decorData = decorsData2[j];
						decorsData.push([decorData[0],decorData[1],null,null,180-90*i]);
					}
					decorsData2.length = 0;
				}
			}
			var extraParams = getDecorExtra(this,true);
			var nbPos = 0;
			if (extraParams.nb) {
				nbPos = decorsData.length;
				for (var i=nbPos;i<extraParams.nb;i++)
					decorsData.push(decorsData[i%nbPos].slice(0));
			}
			else
				nbPos = Math.round(decorsData.length*4/5);
			for (var i=0;i<decorsData.length;i++) {
				var iExtra = getDecorParams(this,i);
				var decorData = decorsData[i];
				decorData[6] = [decorData[0],decorData[1],decorData[3],decorData[4],iExtra.length||460];
			}
			this.initPos = [];
			for (var i=0;i<nbPos;i++) {
				var iPos = decorsData[i][6].slice();
				iPos.push(0);
				this.initPos.push(iPos);
			}
		},
		init:function(decorData,i) {
			this.easeInOut = decorBehaviors.truck.easeInOut;
			if (decorData[5] == undefined)
				decorData[5] = 1+i*20;
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].w = 80;
				decorData[2][j].h = 80;
				if (decorData[5]) {
					decorData[0] = -10;
					decorData[1] = -10;
					decorData[2][j].img.style.display = "none";
				}
			}
		},
		move:function(decorData,i) {
			if (!i) {
				for (var j=0;j<this.initPos.length;j++) {
					var jPos = this.initPos[j];
					if (jPos[jPos.length-1])
						jPos[jPos.length-1]--;
				}
			}
			if (decorData[5] > 0) {
				decorData[5]--;
				if (decorData[5] <= 0) {
					decorData[5] = 0;
					decorData[0] = decorData[6][0];
					decorData[1] = decorData[6][1];
					for (var j=0;j<strPlayer.length;j++)
						decorData[2][j].img.style.display = "block";
				}
			}
			else if (decorData[5] < 0) {
				var minState = -10;
				var opacity;
				if (decorData[5] <= minState) {
					opacity = 1;
					decorData[5] = 0;
					var randPos;
					for (var j=0;j<100;j++) {
						if (oPlayers[0].cpu)
							randPos = Math.floor(Math.random()*this.initPos.length);
						else
							randPos = Math.round((10000*Math.abs(Math.sin(timer+j))))%this.initPos.length;
						var jPos = this.initPos[randPos];
						if (!jPos[jPos.length-1])
							break;
					}
					var nPos = this.initPos[randPos];
					nPos[nPos.length-1] = 20;
					for (var j=0;j<5;j++)
						decorData[6][j] = this.initPos[randPos][j];
					decorData[0] = decorData[6][0];
					decorData[1] = decorData[6][1];
					decorData[3] = decorData[6][2];
					decorData[4] = decorData[6][3];
				}
				else {
					opacity = 1-decorData[5]/minState;
					decorData[3] = decorData[6][2]*Math.sqrt(opacity);
					decorData[5]--;
				}
				for (var j=0;j<oPlayers.length;j++)
					decorData[2][j].img.style.opacity = opacity;
			}
			else {
				var bSpeed = 5, bDir = (180-decorData[4])*Math.PI/180, bDist = decorData[6][4];
				decorData[0] += bSpeed*Math.cos(bDir);
				decorData[1] += bSpeed*Math.sin(bDir);
				if (((decorData[0]-decorData[6][0])*(decorData[0]-decorData[6][0]) + (decorData[1]-decorData[6][1])*(decorData[1]-decorData[6][1])) > bDist*bDist)
					decorData[5] = -1;
			}
			for (var j=0;j<oPlayers.length;j++) {
				var fAngle = nearestAngle(getApparentRotation(oPlayers[j])+90-decorData[4], 180,360);
				var x = (fAngle%180)/180;
				x = this.easeInOut(x);
				fAngle = 180*Math.floor(fAngle/180) + 180*x;
				var iAngleStep = Math.round(fAngle*11 / 180) % 22;
				if (iAngleStep > 21) iAngleStep -= 22;
				decorData[2][j].setState(iAngleStep);
			}
		}
	},
	tortitaupe:{
		spin: 20,
		preinit:function(decorsData) {
			this.init_ = decorBehaviors.taupe.init.bind(this);
			this.move = decorBehaviors.taupe.move.bind(this);
		},
		init:function(decorData,i,iG) {
			this.init_(decorData,i,iG);
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 1;
				decorData[2][j].w = 24;
				decorData[2][j].h = 36;
			}
		}
	},
	topitaupe:{
		spin: 20,
		preinit:function(decorsData) {
			this.init = decorBehaviors.taupe.init.bind(this);
			this.move = decorBehaviors.taupe.move.bind(this);
		}
	},
	coconut:{
		hidable:true,
		hitbox:4,
		unbreaking:true,
		init:function(decorData) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 1;
				decorData[2][j].w = 100;
				decorData[2][j].h = 100;
				decorData[2][j].z = 0.36;
			}
		}
	},
	palm:{
		hidable:true,
		hitbox:4,
		unbreaking:true,
		init:function(decorData) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 1;
				decorData[2][j].w = 100;
				decorData[2][j].h = 100;
				decorData[2][j].z = 0.38;
			}
		}
	},
	mountaintree:{
		hidable:true,
		hitbox:4,
		unbreaking:true,
		init:function(decorData) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 1;
				decorData[2][j].h = 112;
				decorData[2][j].w = decorData[2][j].h*64/126;
				decorData[2][j].z = 0.15;
			}
		}
	},
	mariotree:{
		hidable:true,
		hitbox:3,
		unbreaking:true,
		init:function(decorData) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 1;
				decorData[2][j].h = 104;
				decorData[2][j].w = decorData[2][j].h/2;
				decorData[2][j].z = 0.12;
			}
		}
	},
	fir:{
		hidable:true,
		hitbox:4,
		unbreaking:true,
		init:function(decorData) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 1;
				decorData[2][j].h = 112;
				decorData[2][j].w = decorData[2][j].h;
				decorData[2][j].z = 0.38;
			}
		}
	},
	movingtree:{
		hitbox:11.5,
		movable:true,
		unbreaking:true,
		init:function(decorData,i,iG) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 1;
				decorData[2][j].w = 85;
				decorData[2][j].h = decorData[2][j].w*139/115;
				decorData[2][j].z = 0.12;
				decorData[3] = 0;
				if (!decorData[4]) {
					var sg = (iG%2) ? 1:-1;
					decorData[4] = [
						[decorData[0]+25*sg,decorData[1]],
						[decorData[0],decorData[1]-25*sg],
						[decorData[0]-25*sg,decorData[1]],
						[decorData[0],decorData[1]+25*sg]
					];
				}
				if (decorData[5] == undefined)
					decorData[5] = iG%decorData[4].length;
				if (decorData[6] == undefined)
					decorData[6] = 0;
			}
		},
		move:function(decorData) {
			if (decorData[6])
				decorData[6]--;
			else {
				var aim = decorData[4][decorData[5]];
				var diffX = aim[0]-decorData[0], diffY = aim[1]-decorData[1];
				if (diffX*diffX+diffY*diffY < 1) {
					decorData[5] = (decorData[5]+1)%decorData[4].length;
					decorData[6] = 10;
				}
				else {
					var diffL = Math.hypot(diffX,diffY);
					decorData[0] += diffX*1.1/diffL;
					decorData[1] += diffY*1.1/diffL;
				}
			}
		}
	},
	sinistertree:{
		hidable:true,
		hitbox:9,
		unbreaking:true,
		init:function(decorData) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 1;
				decorData[2][j].w = 117;
				decorData[2][j].h = 126;
				decorData[2][j].z = 0.37;
			}
		}
	},
	pokey:{
		spin: 42,
		movable:true,
		dodgable:true,
		init:function(decorData,i,iG) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 5;
				decorData[2][j].h = 82;
				decorData[2][j].w = decorData[2][j].h*23/45;
				decorData[2][j].z = 0.1;
			}
			decorData[3] = 0;
			if (decorData[4] == undefined)
				decorData[4] = [15,15];
			if (decorData[4].length == 2)
				decorData[4].unshift(decorData[0],decorData[1]);
			if (decorData[5] == undefined)
				decorData[5] = [(10000*Math.sin(iG+1))%Math.PI,Math.sign(Math.sin(1+100*iG))*0.025];
			if (decorData[6] == undefined)
				decorData[6] = Math.floor(10000*Math.pow(Math.sin(iG+1),2))%16;
			this.repos(decorData);
		},
		repos:function(decorData) {
			var angle = decorData[5][0];
			var newPos = decorData[4];
			decorData[0] = newPos[0] + newPos[2]*Math.cos(angle);
			decorData[1] = newPos[1] + newPos[3]*Math.sin(angle);
		},
		move:function(decorData) {
			decorData[5][0] = (decorData[5][0]+decorData[5][1])%(2*Math.PI);
			decorData[6]++;
			var statePeriod = 2;
			if (decorData[6]%statePeriod == 0) {
				var flag = decorData[6]/statePeriod;
				var flagStates = [0,1,2,1,0,3,4,3];
				if (flag >= flagStates.length) {
					flag = 0;
					decorData[6] = 0;
				}
				for (var i=0;i<strPlayer.length;i++)
					decorData[2][i].setState(flagStates[flag]);
			}
			this.repos(decorData);
		}
	},
	falltree:{
		hidable:true,
		hitbox:5,
		unbreaking:true,
		init:function(decorData) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 1;
				decorData[2][j].w = 72;
				decorData[2][j].h = decorData[2][j].w*150/100;
				decorData[2][j].z = 0.24;
			}
		}
	},
	peachtree:{
		hidable:true,
		hitbox:5,
		unbreaking:true,
		init:function(decorData) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 1;
				decorData[2][j].w = 27;
				decorData[2][j].h = decorData[2][j].w*4;
				decorData[2][j].z = 0.01;
			}
		}
	},
	box:{
		breaking:true,
		bonus:true
	},
	snowman:{
		breaking:true,
		spin: 42
	},
	snowball:{
		hitbox:7,
		spin: 42,
		unbreaking:true,
		movable:true,
		defaultspeed: 3.5,
		boostspeed: 8,
		jumpspeed: function(pJump) {
			return 4+1*pJump;
		},
		jumpheight: 32,
		boostspeed:8,
		spritesize: 56,
		ondie: function(x) {
			if (x > -100)
				return "wait";
			return "respawn";
		},
		preinit:function(decorsData) {
			if (decorsData.length && (decorsData[0][4] == undefined)) {
				this.init = decorBehaviors.cannonball.init.bind(this);
				this.move_ = decorBehaviors.cannonball.move.bind(this);
				this.move = function(decorData,i,iG) {
					for (var j=0;j<oPlayers.length;j++)
						decorData[2][j].setState((decorData[2][j].getState()+1)%3);
					this.move_(decorData,i,iG);
				}
				this.setdir = decorBehaviors.cannonball.setdir.bind(this);
				this.autojump = decorBehaviors.cannonball.autojump.bind(this);
			}
		},
		init:function(decorData) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 3;
				decorData[2][j].w = 56;
				decorData[2][j].h = 56;
				decorData[2][j].z = 0.28;
			}
			decorData[3] = 0;
			decorData[4].unshift([decorData[0],decorData[1]]);
			decorData[5] = [1,0];
		},
		move:function(decorData) {
			var dSpeed0 = 3.5, dSpeed = dSpeed0;
			for (var i=0;i<oPlayers.length;i++)
				decorData[2][i].setState((decorData[2][i].getState()+1)%3);
			while (dSpeed > 0) {
				var aipoint = decorData[5][0];
				if (aipoint < decorData[4].length) {
					var target = decorData[4][decorData[5][0]];
					if (target[2] && (dSpeed == dSpeed0))
						dSpeed = target[2];
					var targetX = target[0], targetY = target[1];
					var diffX = targetX-decorData[0], diffY = targetY-decorData[1];
					var diffL = Math.hypot(diffX,diffY);
					decorData[3] = 0;
					if (diffL < dSpeed) {
						decorData[0] = targetX;
						decorData[1] = targetY;
						decorData[5][0]++;
						dSpeed -= diffL;
					}
					else {
						decorData[0] += diffX*dSpeed/diffL;
						decorData[1] += diffY*dSpeed/diffL;
						dSpeed = 0;
						if (target[3]) {
							var lastTarget = decorData[4][decorData[5][0]-1];
							var diffR = (1-diffL/Math.hypot(targetX-lastTarget[0],targetY-lastTarget[1]))/target[3][1];
							if (diffR < 1)
								decorData[3] = 4*target[3][0]*diffR*(1-diffR);
						}
					}
				}
				else {
					dSpeed = 0;
					decorData[5][1]++;
					if (decorData[5][1] < 10) {
						for (var j=0;j<oPlayers.length;j++)
							decorData[2][j].img.style.opacity = 1-decorData[5][1]/10;
					}
					else if (decorData[5][1] == 10) {
						decorData[3] = 10;
						decorData[0] = -100;
						decorData[1] = -100;
						for (var j=0;j<oPlayers.length;j++) {
							decorData[2][j].img.style.opacity = "";
							decorData[2][j].img.style.display = "none";
						}
					}
					else if (decorData[5][1] >= 104) {
						decorData[5][0] = 1;
						decorData[5][1] = 0;
						decorData[0] = decorData[4][0][0];
						decorData[1] = decorData[4][0][1];
						for (var j=0;j<oPlayers.length;j++)
							decorData[2][j].img.style.display = "block";
						decorData[3] = 0;
					}
				}
			}
		}
	},
	cannonball:{
		hitbox:8,
		spin: 42,
		unbreaking:true,
		movable:true,
		defaultspeed: 5,
		jumpspeed: function(pJump) {
			return 6+1.5*pJump;
		},
		jumpheight: 48,
		boostspeed:11,
		spritesize: 60,
		ondie: function() {
			return "suppr";
		},
		setdir:function(decorData,ux,uy,pos) {
			pos = pos||decorData;
			var r = oMap.w + oMap.h;
			decorData[4][0][0] = pos[0] + r*ux;
			decorData[4][0][1] = pos[1] + r*uy;
			decorData[4][1][0] = pos[0] - r*ux;
			decorData[4][1][1] = pos[1] - r*uy;
		},
		autojump:function(decorData,nMoveX,nMoveY,nSpeed) {
			if (decorData[6][4] && nSpeed < this.boostspeed) {
				nMoveX *= this.boostspeed/nSpeed;
				nMoveY *= this.boostspeed/nSpeed;
				nSpeed = this.boostspeed;
			}
			var nMove = Math.hypot(nMoveX,nMoveY);
			decorData[4][2] = [decorData[0]+nMoveX,decorData[1]+nMoveY];
			decorData[5]["2"] = {jump:1,loop:[0]};
			decorData[6][0] = 2;
			decorData[6][2] = nSpeed;
			decorData[6][3] = nMove;
			this.setdir(decorData,nMoveX/nMove,nMoveY/nMove,decorData[4][2]);
		},
		init:function(decorData,i,iG) {
			for (var j=0;j<strPlayer.length;j++) {
				if (!decorData[2][j].nbSprites) {
					decorData[2][j].nbSprites = 1;
					decorData[2][j].w = this.spritesize;
					decorData[2][j].h = this.spritesize;
					decorData[2][j].z = 0.33;
				}
			}
			decorData[3] = 0;
			if (!decorData[4]) {
				decorData[4] = [[],[]];
				var decorParams = getDecorParams(this,i);
				var th = decorParams.dir;
				if (isNaN(th)) th = ((10000*Math.sin(iG+2))%Math.PI);
				this.setdir(decorData,Math.sin(th),Math.cos(th));
				if (!decorData[5])
					decorData[5] = {0:{autoDir:true,pos0:[decorData[0],decorData[1]]},1:{autoDir:true,loop:[0]}};
			}
			if (!decorData[5])
				decorData[5] = {};
			decorData[6] = [0,0,5];
		},
		move:function(decorData,i,iG) {
			var dSpeed = decorData[6][2];
			if (decorData[6][4]) {
				dSpeed = Math.max(this.boostspeed,dSpeed);
				decorData[6][4]--;
			}
			while (dSpeed > 0) {
				var target = decorData[4][decorData[6][0]];
				var targetX = target[0], targetY = target[1];
				var posX = decorData[0], posY = decorData[1];
				var diffX = targetX-posX, diffY = targetY-posY;
				var diffL = Math.hypot(diffX,diffY);
				var customBehaviour = decorData[5][decorData[6][0]];
				if (diffL < dSpeed) {
					decorData[0] = targetX;
					decorData[1] = targetY;
					if (customBehaviour && customBehaviour.loop) {
						var nextTarget = customBehaviour.loop[0];
						if (decorData[6][1]) {
							decorData[6][1]--;
							if (!decorData[6][1])
								nextTarget = decorData[6][0]+1;
						}
						else if (customBehaviour.loop[1])
							decorData[6][1] = customBehaviour.loop[1];
						decorData[6][0] = nextTarget;
					}
					else
						decorData[6][0]++;
					if (customBehaviour && customBehaviour.speed)
						decorData[6][2] = customBehaviour.speed;
					else
						decorData[6][2] = this.defaultspeed;
					if (customBehaviour && customBehaviour.jump) {
						var nextTarget = decorData[4][decorData[6][0]];
						decorData[6][3] = Math.hypot(nextTarget[0]-targetX,nextTarget[1]-targetY);
					}
					else
						decorData[6][3] = 0;
					decorData[3] = 0;
					dSpeed -= diffL;
				}
				else {
					var fMoveX = diffX*dSpeed/diffL, fMoveY = diffY*dSpeed/diffL;
					if (customBehaviour) {
						if (!isNaN(customBehaviour.flipper)) {
							var flipper = oMap.flippers[customBehaviour.flipper];
							if (!flipper[3][0])
								flipper[3][1] = Math.max(0,Math.floor(diffL/dSpeed)-2);
						}
						if (customBehaviour.autoDir) {
							if (decorData[6][1] < 0) {
								var nOpacity = Math.max(0,1+decorData[6][1]/10);
								for (var j=0;j<oPlayers.length;j++)
									decorData[2][j].img.style.opacity = nOpacity;
								if (nOpacity)
									decorData[6][1]--;
								else {
									switch (this.ondie(decorData[6][1])) {
									case "wait":
										decorData[0] = oMap.w*3;
										decorData[1] = oMap.h*3;
										decorData[6][1]--;
										break;
									case "suppr":
										oMap.decor[this.type][i][2][0].suppr();
										oMap.decor[this.type].splice(i,1);
										break;
									case "respawn":
										for (var j=0;j<oPlayers.length;j++)
											decorData[2][j].img.style.opacity = "";
										decorData[0] = decorData[5][0].pos0[0];
										decorData[1] = decorData[5][0].pos0[1];
										decorData[4] = null;
										decorData[5] = null;
										this.init(decorData,i,iG);
									}
								}
								fMoveX = 0;
								fMoveY = 0;
							}
							else {
								var cannon = inCannon(decorData[0],decorData[1]);
								if (cannon) {
									var nSpeed = 17;
									this.autojump(decorData,cannon[0],cannon[1],nSpeed);
								}
								if (!customBehaviour.jump) {
									decorData[3] = 0;
									decorData[6][3] = 0;
								}
								if (!decorData[3]) {
									var cannons = oMap.decor[this.type];
									oMap.decor[this.type] = [];
									var pJump = sauts(decorData[0],decorData[1], fMoveX,fMoveY);
									var pAsset;
									if (pJump) {
										var nSpeed = this.jumpspeed(pJump), nMove = 32*pJump;
										var nMoveX = diffX*nMove/diffL, nMoveY = diffY*nMove/diffL;
										this.autojump(decorData,nMoveX,nMoveY,nSpeed);
									}
									else if (!canMoveTo(decorData[0],decorData[1],0, fMoveX,fMoveY)) {
										var horizontality = getHorizontality(decorData[0],decorData[1], fMoveX,fMoveY);
										var u = Math.hypot(horizontality[0],horizontality[1]);
										var ux = horizontality[0]/u, uy = horizontality[1]/u;
										var m_u = fMoveX*ux + fMoveY*uy;
										var nMoveX = 2*m_u*ux-fMoveX, nMoveY = 2*m_u*uy-fMoveY;
										var nMoveL = Math.hypot(nMoveX,nMoveY);
										this.setdir(decorData,nMoveX/nMoveL,nMoveY/nMoveL);
										fMoveX = 0;
										fMoveY = 0;
									}
									else if (pAsset = touche_asset(decorData[0],decorData[1], decorData[0]+fMoveX,decorData[1]+fMoveY)) {
										switch (pAsset[0]) {
										case "bumpers":
											var ux = fMoveX, uy = fMoveY;
											var bumper = pAsset[1];
											var nPosX = decorData[0]+ux, nPosY = decorData[1]+uy;
											var cx = bumper[1][0], cy = bumper[1][1];
											var rx = (decorData[0]-cx), ry = (decorData[1]-cy);
											var rr = Math.hypot(rx,ry);
											var nx = rx/rr, ny = ry/rr;
											var un = ux*nx + uy*ny;
											var ax = nPosX-un*nx, ay = nPosY-un*ny;
											ux = decorData[0]+2*(ax-decorData[0])-nPosX;
											uy = decorData[1]+2*(ay-decorData[1])-nPosY;
											var uu = Math.hypot(ux,uy);
											this.setdir(decorData,ux/uu,uy/uu);
											fMoveX = 0;
											fMoveY = 0;
											break;
										}
									}
									else if (tombe(decorData[0]+fMoveX,decorData[1]+fMoveY))
										decorData[6][1] = -1;
									else if (accelere(decorData[0],decorData[1], fMoveX,fMoveY))
										decorData[6][4] = 20;
									oMap.decor[this.type] = cannons;
								}
							}
						}
					}
					decorData[0] += fMoveX;
					decorData[1] += fMoveY;
					if (decorData[6][3]) {
						var l = (diffL-dSpeed)/decorData[6][3];
						l = Math.max(Math.min(l,1),0);
						decorData[3] = this.jumpheight*l*(1-l);
					}
					dSpeed = 0;
				}
			}
		}
	},
	truck:{
		hitbox:8,
		spin: 42,
		unbreaking:true,
		movable:true,
		rotatable:true,
		dodgable:true,
		preinit:function(decorsData) {
			for (var i=0;i<decorsData.length;i++) {
				var decorData = decorsData[i];
				if (decorData[5] == undefined) {
					var decorParams = getDecorParams(this,i);
					decorData[5] = decorParams.traject;
				}
			}
		},
		init:function(decorData) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 22;
				decorData[2][j].w = 118;
				decorData[2][j].h = decorData[2][j].w*56/111;
				decorData[2][j].z = 0.72;
			}
			var extraParams = getDecorExtra(this,true);
			if (!extraParams.path)
				extraParams.path = oMap.aipoints;
			if (!decorData[6]) {
				var minDist = Infinity;
				var initialK = (decorData[5] != undefined);
				if (!initialK)
					decorData[5] = 0;
				decorData[6] = 0;
				for (var k=0;k<extraParams.path.length;k++) {
					if (initialK && (k != decorData[5]))
						continue;
					var aipoints = extraParams.path[k];
					for (var j=0;j<aipoints.length;j++) {
						var aipoint = aipoints[j];
						var dist = (aipoint[0]-decorData[0])*(aipoint[0]-decorData[0]) + (aipoint[1]-decorData[1])*(aipoint[1]-decorData[1]);
						if (dist < minDist) {
							minDist = dist;
							decorData[5] = k;
							decorData[6] = j;
						}
					}
				}
			}
			var aipoints = extraParams.path[decorData[5]];
			if (aipoints.length < 2) {
				if (!aipoints.length)
					aipoints = [[decorData[0],decorData[1]]];
				aipoints.push([aipoints[0][0]+0.001,aipoints[0][1]+0.001]);
				extraParams.path[decorData[5]] = aipoints;
			}
			var aipoint = aipoints[decorData[6]];
			var jInc = (decorData[6]+1)%aipoints.length;
			var nAipoint = aipoints[jInc];
			if ((aipoint[0]-decorData[0])*(nAipoint[0]-aipoint[0]) + (aipoint[1]-decorData[1])*(nAipoint[1]-aipoint[1]) < 0) {
				decorData[6] = jInc;
				aipoint = nAipoint;
			}
			var aimX = aipoint[0]-decorData[0]; aimY = aipoint[1]-decorData[1];
			decorData[4] = Math.atan2(aimX,aimY)*180/Math.PI;
		},
		move:function(decorData) {
			var speed = 4;
			var x = decorData[0], y = decorData[1], aimX, aimY;
			var extraParams = getDecorExtra(this,true);
			if (!extraParams.path)
				extraParams.path = oMap.aipoints;
			var aipoints = extraParams.path[decorData[5]];
			do {
				var aipoint = aipoints[decorData[6]];
				aimX = aipoint[0]-x; aimY = aipoint[1]-y;
				var dist = Math.hypot(aimX,aimY);
				if (dist < speed) {
					x += aimX;
					y += aimY;
					speed -= dist;
					if (++decorData[6] >= aipoints.length)
						decorData[6] = 0;
				}
				else {
					x += aimX*speed/dist;
					y += aimY*speed/dist;
					speed = 0;
				}
			} while (speed > 0);
			decorData[0] = x;
			decorData[1] = y;
			if (aimX || aimY) {
				var aimAngle = nearestAngle(Math.atan2(aimX,aimY)*180/Math.PI, decorData[4],360);
				var maxOmega = 8;
				if (decorData[4] < (aimAngle-maxOmega))
					decorData[4] += maxOmega;
				else if (decorData[4] > (aimAngle+maxOmega))
					decorData[4] -= maxOmega;
				else
					decorData[4] = aimAngle;
			}
			for (var i=0;i<oPlayers.length;i++) {
				var fAngle = nearestAngle(getApparentRotation(oPlayers[i])-decorData[4], 180,360);
				var x = (fAngle%180)/180;
				x = this.easeInOut(x);
				fAngle = 180*Math.floor(fAngle/180) + 180*x;
				var iAngleStep = Math.round(fAngle*11 / 180) % 22;
				if (iAngleStep > 21) iAngleStep -= 22;
				decorData[2][i].setState(iAngleStep);
			}
		},
		easeInOut:function(t) {
            t *= 2;
            if (t < 1)
                return t*t/2;
            return -((--t)*(t-2) - 1)/2;
		}
	},
	movingthwomp:{
		spin: 20,
		hitbox: 8,
		movable: true,
		init: function(decorData,i) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 3;
				decorData[2][j].w = 48;
				decorData[2][j].h = 64;
			}
			if (decorData[4] == undefined) {
				decorData[4] = [[decorData[0],decorData[1],0,20,20],[decorData[0],decorData[1],20,5,30],[decorData[0],decorData[1],20,30,5],[decorData[0],decorData[1],0,20,20]];
				var iExtra = getDecorParams(this,i);
				if (!isNaN(iExtra.dir)) {
					var dirX = iExtra.length*Math.sin(iExtra.dir);
					var dirY = iExtra.length*Math.cos(iExtra.dir);
					for (var i=2;i<4;i++) {
						decorData[4][i][0] += dirX;
						decorData[4][i][1] += dirY;
					}
				}
			}
			if (decorData[5] == undefined)
				decorData[5] = 0;
			if (decorData[6] == undefined)
				decorData[6] = 1;
			if (decorData[7] == undefined)
				decorData[7] = 0;
		},
		move: function(decorData,i) {
			if (decorData[7]) {
				decorData[7]--;
				if (!decorData[7]) {
					var target = decorData[4][decorData[5]];
					var newState;
					if (target[2] != decorData[3])
						newState = 1;
					if (newState != undefined) {
						for (var j=0;j<strPlayer.length;j++)
							decorData[2][j].setState(newState);
					}
				}
			}
			else {
				var target = decorData[4][decorData[5]];
				var diffX = target[0]-decorData[0], diffY = target[1]-decorData[1], diffZ = target[2]-decorData[3];
				var speedL = 2, speedZ = (diffZ > 0) ? 2:8;
				var diffL = Math.hypot(diffX,diffY);
				var arrived = true;
				if (speedL < diffL) {
					decorData[0] += diffX*speedL/diffL;
					decorData[1] += diffY*speedL/diffL;
					arrived = false;
				}
				else {
					decorData[0] = target[0];
					decorData[1] = target[1];
				}
				if (speedZ < Math.abs(diffZ)) {
					decorData[3] += Math.sign(diffZ)*speedZ;
					arrived = false;
				}
				else
					decorData[3] = target[2];
				if (arrived) {
					decorData[7] = target[(decorData[6]>0) ? 3:4];
					decorData[5] += decorData[6];
					if (decorData[5] < 0) {
						decorData[5] += 2;
						decorData[6] = 1;
					}
					else if (decorData[5] >= decorData[4].length) {
						decorData[5] -= 2;
						decorData[6] = -1;
					}
					var target = decorData[4][decorData[5]];
					var newState;
					if (diffZ) {
						if (decorData[3] > 0)
							newState = 0;
						else
							newState = 2;
					}
					else
						newState = 0;
					for (var j=0;j<strPlayer.length;j++)
						decorData[2][j].setState(newState);
				}
			}
		}
	},
	chomp:{
		hitbox:9,
		spin: 42,
		unbreaking:true,
		movable:true,
		init: function(decorData,i,iG) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 8;
				decorData[2][j].w = 92;
				decorData[2][j].h = decorData[2][j].w*60/70;
				decorData[2][j].z = 0.33;
			}
			if (decorData[3] == undefined)
				decorData[3] = (10000*Math.abs(Math.sin(iG+1)))%3;
			if (decorData[4] == undefined) {
				decorData[4] = [];
				var r = 40, n = 6;
				var th0 = (10000*Math.sin(iG+2))%Math.PI;
				var dth = 2*Math.PI/n * Math.sign(Math.sin(1+80*iG));
				for (var j=0;j<n;j++) {
					var th = th0 + j*dth;
					decorData[4].push([decorData[0]+r*Math.cos(th),decorData[1]+r*Math.sin(th)]);
				}
			}
			if (decorData[5] == undefined)
				decorData[5] = 0;
			if (decorData[6] == undefined)
				decorData[6] = (iG%2) ? 1:-1;
		},
		move:function(decorData,i) {
			decorData[3] += decorData[6];
			if (decorData[3] < 0) {
				decorData[3] = 0;
				decorData[6] = 1.5;
			}
			decorData[6] -= 0.5;
			var dSpeed = 2.5;
			while (dSpeed > 0) {
				var target = decorData[4][decorData[5]];
				var targetX = target[0], targetY = target[1];
				var diffX = targetX-decorData[0], diffY = targetY-decorData[1];
				var diffL = Math.hypot(diffX,diffY);
				if (diffL < dSpeed) {
					decorData[0] = targetX;
					decorData[1] = targetY;
					decorData[5]++;
					if (decorData[5] >= decorData[4].length)
						decorData[5] = 0;
					dSpeed -= diffL;
				}
				else {
					decorData[0] += diffX*dSpeed/diffL;
					decorData[1] += diffY*dSpeed/diffL;
					dSpeed = 0;
				}
			}
			var target = decorData[4][decorData[5]];
			var origin = decorData[4][(decorData[5]?decorData[5]:decorData[4].length)-1];
			var cAngle = Math.atan2(target[0]-origin[0],target[1]-origin[1])*180/Math.PI;
			if (!isNaN(cAngle)) {
				for (var j=0;j<oPlayers.length;j++) {
					var fAngle = nearestAngle(getApparentRotation(oPlayers[j])-cAngle, 180,360);
					var iAngleStep = Math.round(fAngle*8/360)%8;
					decorData[2][j].setState(iAngleStep);
				}
			}
		},
		easeInOut:function(t) {
            t *= 2;
            if (t < 1)
                return t*t/2;
            return -((--t)*(t-2) - 1)/2;
		}
	},
	pendulum:{
		hitbox:8,
		spin: 42,
		unbreaking: true,
		movable: true,
		init: function(decorData,i,iG) {
			for (var j=0;j<strPlayer.length;j++) {
				decorData[2][j].nbSprites = 1;
				decorData[2][j].w = 60;
				decorData[2][j].h = decorData[2][j].w*3;
				decorData[2][j].z = 0.1;
				decorData[2][j].div.style.transformOrigin = decorData[2][j].div.style.WebkitTransformOrigin = decorData[2][j].div.style.MozTransformOrigin = "50% 83%";
			}
			decorData[5] = [decorData[0],decorData[1],0,-1];
			if (decorData[4] == undefined) {
				decorData[4] = ((10000*Math.sin(iG+1))%(Math.PI/3));
				var decorParams = getDecorParams(this,i);
				var th = decorParams.dir;
				if (isNaN(th)) {
					decorData[5][2] = 10000*Math.sin(iG+1)%Math.PI;
					decorData[5][3] = Math.sin(1200*(iG+1)+1)>0 ? 1:-1;
				}
				else {
					decorData[5][2] = th;
					decorData[5][3] = 1;
				}
			}
		},
		move: function(decorData) {
			var theta0 = Math.PI/3, theta = decorData[4], phi = decorData[5][2];
			var maxTheta = theta0*0.99;
			if (Math.abs(theta) > maxTheta) {
				theta = maxTheta*Math.sign(theta);
				decorData[5][3] = -decorData[5][3];
			}
			var dtheta = Math.sqrt(2*(Math.cos(theta)-Math.cos(theta0)));
			theta += dtheta/16*decorData[5][3];
			decorData[4] = theta;
			var r = 30, l = 6;
			decorData[0] = decorData[5][0] + r*Math.sin(theta)*Math.sin(phi);
			decorData[1] = decorData[5][1] + r*Math.sin(theta)*Math.cos(phi);
			var z = -l*(1-Math.cos(theta));
			for (var j=0;j<strPlayer.length;j++) {
				var pTheta = getApparentRotation(oPlayers[j]);
				var thetaApp = -theta/5*Math.sin(pTheta*Math.PI/180-phi);
				decorData[2][j].div.style.transform = decorData[2][j].div.style.WebkitTransform = decorData[2][j].div.style.MozTransform = "translateY("+z+"%) rotate("+Math.round(thetaApp*180/Math.PI)+"deg)";
			}
		}
	}
};
var DEFAULT_DECOR_HITBOX = 5;
var DEFAULT_DECOR_HITBOX_H = 4;
for (var type in decorBehaviors)
	decorBehaviors[type].type = type;

function getDecorParams(self,i) {
	var type = self.type;
	if (oMap.decorparams && oMap.decorparams[type] && oMap.decorparams[type][i])
		return oMap.decorparams[type][i];
	return {};
}
function getDecorExtra(self,actualType) {
	var type = self.type;
	var res = {};
	if (oMap.decorparams && oMap.decorparams.extra && oMap.decorparams.extra[type]) {
		res = oMap.decorparams.extra[type];
		if (actualType && res.custom)
			res = getDecorExtra(decorBehaviors[res.custom.type]);
	}
	return res;
}

function getDecorActualType(self) {
	var extra = getDecorExtra(decorBehaviors[self.type]);
	if (extra.custom)
		return extra.custom.type;
	return self.type;
}

function getApparentRotation(oPlayer) {
	var res = oPlayer.rotation;
	var changeView = oPlayer.changeView;
 
	if (changeView)
		res += (res < 360-changeView ? changeView : changeView-360);
	return res;
}

var lastState;
function getLastObj(lastObjs,i,currentObj) {
	if (lastObjs[i] && lastObjs[i].ref === currentObj.ref)
		return lastObjs[i];
	for (var j=0;j<lastObjs.length;j++) {
		if (lastObjs[j].ref === currentObj.ref)
			return lastObjs[j];
	}
	return currentObj;
}
function interpolateState(x1,x2,tFrame) {
	return x1*(1-tFrame) + x2*tFrame;
}
function interpolateStateAngle(x1,x2,tFrame) {
	x1 = nearestAngle(x1,x2, 360);
	return interpolateState(x1,x2,tFrame);
}
function interpolateStateRound(x1,x2,tFrame) {
	return Math.round(interpolateState(x1,x2,tFrame));
}
function render() {
	var currentState = {
		karts: [],
		decor: {},
		items: {}
	}
	for (var i=0;i<aKarts.length;i++) {
		var oKart = aKarts[i];
		currentState.karts.push({
			ref: oKart,
			x: oKart.x,
			y: oKart.y,
			z: oKart.z,
			rotation: oKart.rotation,
			changeView: oKart.changeView||0,
			size: oKart.size,
			tourne: oKart.tourne,
			figstate: oKart.figstate||0,
			time: oKart.time,
			roulette: oKart.roulette,
			tombe: oKart.tombe
		});
	}
	if (oMap.decor) {
		for (var type in oMap.decor) {
			currentState.decor[type] = [];
			for (var i=0;i<oMap.decor[type].length;i++) {
				var decor = oMap.decor[type][i];
				currentState.decor[type].push({
					ref: decor,
					x: decor[0],
					y: decor[1],
					z: decor[3],
				});
			}
		}
	}
	for (var key in items) {
		currentState.items[key] = [];
		for (var i=0;i<items[key].length;i++) {
			var item = items[key][i];
			currentState.items[key].push({
				ref: item,
				x: item.x,
				y: item.y,
				z: item.z,
				size: item.size
			});
		}
	}
	if (!lastState) lastState = currentState;

	var nbFrames = 1;

	function renderFrame(frame) {
		var tFrame = frame/nbFrames;
		var lastFrame = (tFrame == 1);
		var frameState;
		if (nbFrames == 1) {
			frameState = currentState;
			frameState.players = [];
		}
		else {
			frameState = {
				karts: [],
				players: [],
				decor: {},
				items: {}
			};
			for (var i=0;i<currentState.karts.length;i++) {
				var currentObj = currentState.karts[i];
				var lastObj = getLastObj(lastState.karts,i,currentObj);
				if (!currentObj.ref.progressiveView)
					lastObj.changeView = currentObj.changeView;
				if (currentObj.tombe && !lastObj.tombe)
					lastObj.tombe = currentObj.tombe;
				if (currentObj.figstate && !lastObj.figstate)
					lastObj.figstate = currentObj.figstate;
				if (currentObj.tourne && !lastObj.tourne)
					lastObj.tourne = currentObj.tourne;
				frameState.karts.push({
					ref: currentObj.ref,
					x: interpolateState(lastObj.x,currentObj.x,tFrame),
					y: interpolateState(lastObj.y,currentObj.y,tFrame),
					z: interpolateState(lastObj.z,currentObj.z,tFrame),
					rotation: interpolateStateAngle(lastObj.rotation,currentObj.rotation,tFrame),
					changeView: interpolateStateAngle(lastObj.changeView,currentObj.changeView,tFrame),
					size: interpolateState(lastObj.size,currentObj.size,tFrame),
					tourne: interpolateStateRound(lastObj.tourne,currentObj.tourne,tFrame),
					figstate: interpolateStateRound(lastObj.figstate,currentObj.figstate,tFrame),
					time: interpolateState(lastObj.time,currentObj.time,tFrame),
					roulette: interpolateState(lastObj.roulette,currentObj.roulette,tFrame),
					tombe: interpolateState(lastObj.tombe,currentObj.tombe,tFrame)
				});
			}
			for (var type in currentState.decor) {
				frameState.decor[type] = [];
				for (var i=0;i<currentState.decor[type].length;i++) {
					var currentObj = currentState.decor[type][i];
					var lastObj = getLastObj(lastState.decor[type],i,currentObj);
					frameState.decor[type].push({
						ref: currentObj.ref,
						x: interpolateState(lastObj.x,currentObj.x,tFrame),
						y: interpolateState(lastObj.y,currentObj.y,tFrame),
						z: interpolateState(lastObj.z,currentObj.z,tFrame),
					});
				}
			}
			for (var type in currentState.items) {
				frameState.items[type] = [];
				for (var i=0;i<currentState.items[type].length;i++) {
					var currentObj = currentState.items[type][i];
					var lastObj = getLastObj(lastState.items[type],i,currentObj);
					frameState.items[type].push({
						ref: currentObj.ref,
						x: interpolateState(lastObj.x,currentObj.x,tFrame),
						y: interpolateState(lastObj.y,currentObj.y,tFrame),
						z: interpolateState(lastObj.z,currentObj.z,tFrame),
						size: interpolateState(lastObj.size,currentObj.size,tFrame)
					});
				}
			}
		}
		if (lastFrame)
			lastState = currentState;
		for (var i=0;i<oPlayers.length;i++)
			frameState.players.push(frameState.karts[i]);
		for (var i=0;i<frameState.players.length;i++) {
			var oPlayer = frameState.players[i];

			var posX = oPlayer.x;
			var posY = oPlayer.y;
			var fRotation = getApparentRotation(oPlayer);
			if (oPlayer.tombe) {
				if (oPlayer.tombe > 10) {
					if (oPlayer.tombe == 20 && !lastFrame) {
						posX = interpolateState(lastState.karts[i].x,oPlayer.ref.aX, tFrame);
						posY = interpolateState(lastState.karts[i].y,oPlayer.ref.aY, tFrame);
						oPlayer.x = posX;
						oPlayer.y = posY;
						oPlayer.z = 0;
						oPlayer.rotation = oPlayer.ref.aRotation;
						oPlayer.ref.sprite[i].img.style.opacity = 1-tFrame;
						fRotation = getApparentRotation(oPlayer);
					}
					else {
						posX = oPlayer.ref.aX;
						posY = oPlayer.ref.aY;
						oPlayer.rotation = oPlayer.ref.aRotation;
						fRotation = getApparentRotation(oPlayer);
					}
				}
				oContainers[i].style.opacity = Math.abs(oPlayer.tombe-10)/10;
			}
			//posX = aKarts[1].x;
			//posY = aKarts[1].y;
			//fRotation = aKarts[1].rotation;
			var fCamera = {
				x: posX,
				y: posY,
				rotation: fRotation
			};

			redrawCanvas(i, fCamera);

			if (oPlayer.time) {
				document.getElementById("lakitu"+i).style.left = Math.round(iScreenScale * (20-oPlayer.time/5))+"px";
				document.getElementById("lakitu"+i).style.top = Math.round((-(Math.abs(oPlayer.time - 20)) + 18) * (iScreenScale - 2)) +"px";
			}
			if (oPlayer.roulette && oPlayer.roulette < 25) {
				var rTurner = document.getElementById("scroller"+i).getElementsByTagName("div")[0];
				var rHeight = +rTurner.dataset.h;
				var rSize = +rTurner.dataset.s;
				var nTop = (parseInt(rTurner.style.top) + Math.round(iScreenScale*2/nbFrames));
				if (nTop > 0)
					nTop += rSize-rHeight;
				rTurner.style.top = nTop +"px";
			}

			var fSprite;

			for (var j=0;j<frameState.karts.length;j++) {
				fSprite = frameState.karts[j];
				var fAngle = fRotation - fSprite.rotation;
				while (fAngle < 0)
					fAngle += 360;
				while (fAngle > 360)
					fAngle -= 360;

				var iAngleStep = Math.round(fAngle*11 / 180) + fSprite.tourne % 21;
				if (iAngleStep > 21) iAngleStep -= 22;

				if (!fSprite.changeView) {
					if (fSprite.figstate)
						iAngleStep = (iAngleStep + 21-fSprite.figstate) % 21;
					else if (fSprite.ref.driftinc)
						iAngleStep = (fSprite.ref.driftinc>0) ? 18:4;
					else if (fSprite == frameState.players[i]) {
						if (fSprite.ref.rotincdir && !fSprite.tourne)
							iAngleStep = (fSprite.ref.rotincdir > 0) ? 23:22;
						else if (!fSprite.tourne)
							iAngleStep = 0;
					}
				}

				fSprite.ref.sprite[i].setState(iAngleStep);
				fSprite.ref.sprite[i].render(fCamera, fSprite);

				if (course == "BB") {
					var nbBallons = fSprite.ref.ballons.length;
					var fTaille = fSprite.size/2, fHauteur = correctZInv(correctZ(fSprite.z) + 2*fTaille*(6+(fSprite.ref.sprite[i].h-32)/5));
					var fShift = 2.5;
					for (k=0;k<nbBallons;k++) {
						fSprite.ref.ballons[k][i].render(fCamera, {
							x: fSprite.x-(k+0.75-nbBallons/2)*fShift*fSprite.size*direction(1,fRotation),
							y: fSprite.y+(k+0.75-nbBallons/2)*fShift*fSprite.size*direction(0,fRotation),
							z: fHauteur,
							size: fTaille
						});
					}
				}

				if (fSprite != frameState.players[i]) {
					if (fSprite.ref.marker && !fSprite.ref.loose && !fSprite.ref.tombe)
						fSprite.ref.marker.render(i, fCamera, fSprite);
				}
			}


			for (var j=0;j<oMap.arme.length;j++) {
				fSprite = oMap.arme[j];
				if (isNaN(fSprite[2])) {
					fSprite[2][i].render(fCamera, {
						x: fSprite[0],
						y: fSprite[1]
					});
				}
				else if (!i && lastFrame) {
					if (fSprite[2])fSprite[2]--;
					else fSprite[2] = new Sprite("item");
				}
			}

			if (oMap.coins) {
				for (var j=0;j<oMap.coins.length;j++) {
					fSprite = oMap.coins[j];
					var fRotRad = fCamera.rotation * Math.PI / 180;
					var cosTheta = Math.abs(Math.cos(fSprite.theta-fRotRad));
					fSprite.sprite[i].w = 24*cosTheta;
					fSprite.sprite[i].z = (cosTheta-1)*0.5;
					fSprite.sprite[i].render(fCamera, {
						x: fSprite.x,
						y: fSprite.y
					});
				}
			}

			for (var type in frameState.decor) {
				for (var j=0;j<frameState.decor[type].length;j++) {
					fSprite = frameState.decor[type][j];
					if (fSprite.ref[2][0].unshown) continue;
					fSprite.ref[2][i].render(fCamera, {
						x: fSprite.x,
						y: fSprite.y,
						z: fSprite.z,
						size: 1.2
					});
				}
			}

			for (var key in frameState.items) {
				for (var j=0;j<frameState.items[key].length;j++) {
					fSprite = frameState.items[key][j];
					if (lastFrame) {
						var itemBehavior = itemBehaviors[key];
						if (itemBehavior.render)
							itemBehavior.render(fSprite.ref,i);
					}
					if (fSprite.ref.sprite)
						fSprite.ref.sprite[i].render(fCamera, fSprite);
				}
			}

			if (lastFrame) {
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
			}

			for (var j=0;j<oBgLayers.length;j++)
				oBgLayers[j].draw(fRotation, i);

			if ((strPlayer.length == 1) && !gameSettings.nomap)
				setPlanPos();
		}
	}
	renderFrame(1);
	for (var i=1;i<nbFrames;i++) {
		(function(i) {
			setTimeout(function(){renderFrame(i+1)}, SPF*i/nbFrames);
		})(i);
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
	fSprite.sprite[k].img.src = "images/sprites/sprite_"+src+".png";
	var oDivs = fSprite.sprite[k].div.getElementsByClassName("sprite-hallow");
	if (oDivs.length)
		fSprite.sprite[k].div.removeChild(oDivs[0]);
}

function correctZ(z) {
	return 7*Math.pow(z/7,0.7);
}
function correctZInv(z0) {
	return 7*Math.pow(z0/7, 1/0.7);
}
function correctCamZ(z,iDeltaX) {
	return correctZ(z)*iCamDist/iDeltaX;
}

function direction(fDir, rotation) {
	return Math[["sin","cos"][fDir]](rotation * Math.PI / 180)
}
function getItemDistributionRange(oKart) {
	var a = (oKart.place-1)/aKarts.length, b = oKart.place/aKarts.length;
	if (course != "BB") {
		var x, d;
		if (oKart.place == 1) {
			var distToSecond = -distanceToSecond(oKart);
			x = 0;
			d = 0.18*Math.exp(distToSecond/150);
		}
		else {
			var distToFirst = distanceToFirst(oKart);
			x = Math.pow(distToFirst/metaItemRange,0.75);
			d = 0.07;
		}
		var a2 = Math.min(1,x), b2 = Math.min(1,x+d);
		a = getItemAvgRange(a2,a);
		b = getItemAvgRange(b2,b);
	}
	else {
		var maxBalloons = 0;
		for (var i=0;i<aKarts.length;i++)
			maxBalloons = Math.max(maxBalloons, aKarts[i].ballons.length+aKarts[i].reserve);
		var myBalloons = oKart.ballons.length+oKart.reserve;
		var x = (maxBalloons-myBalloons)/2.5;
		var d = 0.07;
		var a2 = x, b2 = x+d;
		a = getItemAvgRange(a2,a);
		b = getItemAvgRange(b2,b);
	}
	return [a*itemDistribution.length,b*itemDistribution.length];
}
function getItemAvgRange(x1,x2) {
	return Math.min(0.99999, Math.pow(x1,1-metaItemPosition)*Math.pow(x2,metaItemPosition));
}
function randObj(oKart) {
	var distrib = getItemDistributionRange(oKart);
	var a = distrib[0], b = distrib[1];
	/*if (oKart == oPlayers[0]) { // Uncomment to test item distribution
		var pdf = {};
		var a_ = Math.floor(a), b_ = Math.ceil(b);
		for (var i=a_;i<b_;i++) {
			var x = 1;
			if (i == a_)
				x -= (a-a_);
			if (i == b_-1)
				x -= (b_-b);
			var distribution = itemDistribution[i];
			for (var key in distribution) {
				if (!pdf[key]) pdf[key] = 0;
				pdf[key] += x*distribution[key];
			}
		}
		var totalProb = 0;
		for (var key in pdf)
			totalProb += pdf[key];
		for (var key in pdf)
			pdf[key] = Math.round(pdf[key]*100/totalProb);
		alert(JSON.stringify(pdf,null,2));
	}*/
	var i = Math.floor(a + (b-a)*Math.random());
	var distribution = itemDistribution[i];
	var nbObjs = 0;
	for (var key in distribution)
		nbObjs += distribution[key];
	var randId = Math.floor(Math.random()*nbObjs);
	nbObjs = 0;
	for (var key in distribution) {
		nbObjs += distribution[key];
		if (nbObjs > randId)
			return key;
	}
}
function possibleObjs(oKart, except) {
	var distrib = getItemDistributionRange(oKart);
	var d = 0.005; // Safety belt to avoid infinite loops
	var a = Math.floor(distrib[0]+d), b = Math.ceil(distrib[1]-d);
	var res = {};
	if (a >= itemDistribution.length) {
		a = itemDistribution.length-1;
		b = itemDistribution.length;
	}
	for (var i=a;i<b;i++) {
		var distribution = itemDistribution[i];
		for (var key in distribution) {
			if (!except[key])
				res[key] = true;
		}
	}
	return res;
}
function otherObjects(oKart, blackList) {
	var pObjs = possibleObjs(oKart, blackList);
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
function updateBalloonHud(oCompteur,oPlayer) {
	var oSrc = balloonSrc(oPlayer.team);
	oCompteur.innerHTML = "";
	for (var i=0;i<oPlayer.reserve;i++)
		oCompteur.innerHTML += '<img src="'+oSrc+'" style="width:'+(iScreenScale*3)+'px;margin:0 '+Math.round(iScreenScale*0.5)+'px 0 '+Math.round(iScreenScale*0.2)+'px" />';
}

var syncItems = [];
function detruit(item, sound) {
	if (isOnline) {
		item = getItemToDestroy(item);
		item.deleted = 1;
		syncItems.push(item);
	}
	supprime(item, sound);
}
function supprime(item, sound) {
	var key = item.type;
	var id = items[key].indexOf(item);
	if (id != -1) {
		var itemBehavior = itemBehaviors[key];
		if (item.sprite) {
			for (var i=0;i<oPlayers.length;i++) {
				var oPlayer = oPlayers[i];
				if (!oPlayer.tombe) {
					var fCamera = {
						x: oPlayer.x,
						y: oPlayer.y,
						rotation: getApparentRotation(oPlayer)
					};
					item.sprite[i].render(fCamera, item);
				}
			}
			item.sprite[0].fadeout(itemBehavior.fadedelay);
		}
		if (itemBehavior.del)
			itemBehavior.del(item);
		for (var i=0;i<aKarts.length;i++) {
			var j = aKarts[i].using.indexOf(item);
			if (j != -1) {
				aKarts[i].using.splice(j,1);
				if (sound) {
					if (playIfShould(aKarts[i],"musics/events/hit.mp3"))
						sound = false;
				}
				break;
			}
		}
		if (typeof(sound) == "object")
			playDistSound(sound,"musics/events/hit.mp3",80);
		if (clLocalVars.myItems) {
			var mhId = clLocalVars.myItems.indexOf(item);
			if (mhId != -1)
				clLocalVars.myItems.splice(mhId,1);
		}
		items[key].splice(id,1);
	}
}
function getItemToDestroy(item) {
	for (var i=0;i<aKarts.length;i++) {
		var j = aKarts[i].using.indexOf(item);
		if (j != -1) {
			var res = aKarts[i].using[0];
			if (res.type === item.type)
				return res;
			return item;
		}
	}
	return item;
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
function loseUsingItems(oKart) {
	if (oKart.using.length) {
		for (var i=0;i<oKart.using.length;i++) {
			var oItem = oKart.using[i];
			if (oItem.z)
				oItem.z = 0;
			var itemBehavior = itemBehaviors[oItem.type];
			if (itemBehavior.drop)
				itemBehavior.drop(oItem,oKart);
			if (isOnline)
				syncItems.push(oItem);
		}
		oKart.using.length = 0;
	}
}
function loseUsingItem(oKart) {
	if (oKart.rotitem === undefined)
		loseUsingItems(oKart);
}
function dropCurrentItem(oKart) {
	var sArme = oKart.arme;
	if (!sArme) return;
	var sRoulette = oKart.roulette;
	supprArme(aKarts.indexOf(oKart));
	if (sRoulette < 25) return;
	if (isOnline && !kartIsPlayer(oKart)) return;
	var itemCount = 1;
	var sArmeCountRegex = sArme.match(/^(.+)X(\d+)$/);
	if (sArmeCountRegex) {
		sArme = sArmeCountRegex[1];
		itemCount = +sArmeCountRegex[2];
	}
	var itemType;
	switch (sArme) {
	case "champi":
	case "banane":
	case "carapace":
	case "poison":
		itemType = sArme;
		break;
	case "carapacerouge":
		itemType = "carapace-rouge";
		break;
	}
	if (itemType) {
		for (var i=0;i<itemCount;i++) {
			var rAngle = oKart.rotation*Math.PI/180 + (Math.random()-0.5)*0.9*Math.PI, rDist = 9 + Math.random()*6;
			var item = {type: itemType, team:oKart.team, x:oKart.x - rDist*Math.sin(rAngle), y:oKart.y - rDist*Math.cos(rAngle), z:0};
			switch (sArme) {
			case "carapace":
				item.vx = 0; item.vy = 0; item.owner = -1; item.lives = 10;
				break;
			case "carapacerouge":
				item.theta = -1; item.owner = -1; item.aipoint = -2; item.aimap = -1; item.target = -1;
				break;
			}
			addNewItem(oKart, item);
			item.sprite[0].fadein(200);
		}
	}
}
function deleteUsingItems(oKart) {
	for (var i=oKart.using.length-1;i>=0;i--)
		detruit(oKart.using[i]);
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

function showRearView(getId) {
	var oPlayer = oPlayers[getId];
	var nView = 180 - oPlayer.changeView;
	oPlayer.changeView = nView;
	oPlayer.sprite[0].setState(11);
	if (bCounting) {
		var oCount = document.getElementById("oCounts"+getId);
		if (oCount)
			oCount.style.visibility = oPlayer.changeView ? "hidden":"visible";
	}
}

function resetSpriteHeight(sprite) {
	sprite.lastW = sprite.w;
	sprite.lastH = sprite.h;
	sprite.w = 32;
	sprite.h = 32;
}
function resumeSpriteSize(sprite) {
	if (sprite.lastW) {
		sprite.w = sprite.lastW;
		delete sprite.lastW;
	}
	if (sprite.lastH) {
		sprite.h = sprite.lastH;
		delete sprite.lastH;
	}
}

function updateProtectFlag(oKart) {
	oKart.protect = (oKart.etoile || oKart.megachampi || oKart.billball || oKart.cannon);
}

function colKart(getId) {
	var oKart = aKarts[getId];
	for (var i=0;i<getId;i++) {
		var kart = aKarts[i];
		var protect1 = oKart.protect ? ((oKart.etoile||oKart.billball)?2:1) : 0;
		var protect2 = kart.protect ? ((kart.etoile||kart.billball)?2:1) : 0;
		var isChampiCol = (course == "BB") && (!oKart.champi != !kart.champi);
		if (oKart.cpu && kart.cpu && (protect1 == protect2) && !isChampiCol)
			continue;
		if (!friendlyFire(oKart,kart) && (course!="BB"||(oKart.ballons.length&&kart.ballons.length)) && Math.pow(oKart.x-kart.x, 2) + Math.pow(oKart.y-kart.y, 2) < 1000 && (oKart.z<=1.175||oKart.billball) && (kart.z<=1.175||kart.billball) && !oKart.tourne && !kart.tourne) {
			var dir1 = kartInstantSpeed(oKart), dir2 = kartInstantSpeed(kart);
			var relDir = [dir2[0]-dir1[0],dir2[1]-dir1[1]];
			var nDir1 = [oKart.x-kart.x,oKart.y-kart.y];
			var hitboxSize = 5*(kart.size+oKart.size)*(kart.size+oKart.size);
			var l = projete(0,0,nDir1[0],nDir1[1],nDir1[0]-relDir[0],nDir1[1]-relDir[1]);
			if (l < 0) l = 0;
			if (l > 1) l = 1;
			var nearestPoint = [nDir1[0]-l*relDir[0], nDir1[1]-l*relDir[1]];
			var d2 = nearestPoint[0]*nearestPoint[0] + nearestPoint[1]*nearestPoint[1];
			if (d2 <= hitboxSize) {
				if (!protect1 && !protect2) {
					if (isChampiCol) {
						var qKart = oKart.champi ? kart:oKart;
						if (!qKart.loose && !qKart.cannon && !qKart.frminv) {
							var pKart = oKart.champi ? oKart:kart;
							var iKart = aKarts.indexOf(qKart);
							handleHit2(pKart,qKart);
							loseBall(iKart);
							stopDrifting(iKart);
							if (pKart.ballons.length < 3)
								addNewBalloon(pKart,qKart.team);
							qKart.spin(62);
						}
					}
					var d20 = (nDir1[0]*nDir1[0] + nDir1[1]*nDir1[1]);
					if (d20 > hitboxSize) {
						if (!oKart.cpu || !kart.cpu) {
							var nDir2 = [nDir1[1],-nDir1[0]];
							var del = nDir2[0]*nDir1[1]-nDir1[0]*nDir2[1];
							if (del) {
								var l1 = (nDir2[0]*relDir[1]-nDir2[1]*relDir[0])/del, l2 = (nDir1[1]*relDir[0]-nDir1[0]*relDir[1])/del;
								nDir1[0] *= l1;
								nDir1[1] *= l1;
								nDir2[0] *= l2;
								nDir2[1] *= l2;
								var massRatio = (kart.stats.mass*kart.size)/(oKart.stats.mass*oKart.size);
								var pushVector = [nDir1[0]*massRatio,nDir1[1]*massRatio];
								//l = projete(pushVector[0],pushVector[1],0,0,dir1[1],-dir1[0]);
								//pushVector[0] = l*dir1[1];
								//pushVector[1] = -l*dir1[0];
								if (!oKart.pushVector || (oKart.pushVector[0]*oKart.pushVector[0]+oKart.pushVector[1]*oKart.pushVector[1] < pushVector[0]*pushVector[0]+pushVector[1]*pushVector[1]))
									oKart.pushVector = pushVector;
								pushVector = [(nDir2[0]+dir1[0]-dir2[0])/massRatio,(nDir2[1]+dir1[1]-dir2[1])/massRatio];
								//l = projete(pushVector[0],pushVector[1],0,0,dir2[1],-dir2[0]);
								//pushVector[0] = l*dir2[1];
								//pushVector[1] = -l*dir2[0];
								if (!kart.pushVector || (kart.pushVector[0]*kart.pushVector[0]+kart.pushVector[1]*kart.pushVector[1] < pushVector[0]*pushVector[0]+pushVector[1]*pushVector[1]))
									kart.pushVector = pushVector;
								if (!kart.cpu && !kart.colSound) {
									kart.colSound = playIfShould(kart, "musics/events/colkart.mp3");
									if (kart.colSound) {
										(function(iKart) {
											iKart.colSound.onended = function() {
												iKart.colSound = undefined;
												document.body.removeChild(this);
											}
										})(kart);
									}
								}
							}
						}
					}
				}
				else if (protect1 != protect2) {
					if (d2 < hitboxSize/2) {
						var qKart = (protect1<protect2) ? oKart:kart;
						if (!qKart.loose && !qKart.cannon && !qKart.frminv) {
							var pKart = (protect1<protect2) ? kart:oKart;
							var iKart = aKarts.indexOf(qKart);
							handleHit2(pKart,qKart);
							loseBall(iKart);
							stopDrifting(iKart);
							qKart.spin(62);
							loseUsingItems(qKart);
							dropCurrentItem(qKart);
						}
					}
				}
			}
		}
	}
}

function pointInRectangle(x,y, oBox) {
	return (x > oBox[0] && x <= oBox[0]+oBox[2] && y > oBox[1] && y <= oBox[1]+oBox[3]);
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

function canMoveTo(iX,iY,iZ, iI,iJ, iP) {

	var nX = iX+iI, nY = iY+iJ;

	if (oMap.decor) {
		for (var type in oMap.decor) {
			var decorBehavior = decorBehaviors[type];
			var hitboxSize = decorBehavior.hitbox||DEFAULT_DECOR_HITBOX;
			var hitboxHeight = decorBehavior.hitboxH||DEFAULT_DECOR_HITBOX_H;
			for (var i=0;i<oMap.decor[type].length;i++) {
				var oBox = oMap.decor[type][i];
				if (nX > oBox[0]-hitboxSize && nX < oBox[0]+hitboxSize && nY > oBox[1]-hitboxSize && nY < oBox[1]+hitboxSize && (Math.abs((oBox[3]?oBox[3]:0)-iZ)<hitboxHeight)) {
					if ((oBox[3] == undefined) && (iX > oBox[0]-hitboxSize) && (iX < oBox[0]+hitboxSize) && (iY > oBox[1]-hitboxSize) && (iY < oBox[1]+hitboxSize))
						continue;
					if (!iP || decorBehavior.unbreaking) {
						collisionDecor = type;
						if (collisionTest == COL_KART) {
							if (decorBehavior.breaking && (collisionPlayer.speed > 4)) {
								oMap.decor[type][i][2][0].suppr();
								oMap.decor[type].splice(i,1);
								if (collisionPlayer.turbodrift)
									collisionPlayer.turbodrift = 0;
								if (decorBehavior.bonus) {
									if (!isOnline || (collisionPlayer == oPlayers[0]))
										addNewItem(collisionPlayer, {type: (Math.random()<0.5) ? "banane":"champi", team:collisionPlayer.team, x:(nX+iI*2.5),y:(nY+iJ*2.5), z:0});
								}
							}
							if (decorBehavior.transparent)
								break;
						}
						return false;
					}
					else {
						oMap.decor[type][i][2][0].suppr();
						oMap.decor[type].splice(i,1);
						break;
					}
				}
			}
		}
	}

	if (iZ > 1.175) return true;

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
	var res = null, minT = 1;
	for (var i=0;i<lines.length;i++) {
		var line = lines[i];
		var cross = secants(iX,iY,nX,nY,line.x1,line.y1,line.x2,line.y2);
		if (cross && (cross[0] < minT)) {
			minT = cross[0];
			res = {
				"dir" : [line.x2-line.x1,line.y2-line.y1],
				"t" : minT
			};
		}
	}
	return res;
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
function intersectionLineLine(x11,y11,x21,y21, x12,y12,x22,y22) {
	var den = -x21*y22 + x21*y12 + x11*y22 - x11*y12 + x22*y21 - x22*y11 - x12*y21 + x12*y11;
	var l = (-x22*y11 + x12*y11 - x11*y12 + x11*y22 + x22*y12 - x12*y22)/den;
	var m = (x11*y21 - x21*y11 + x21*y12 - x11*y12 - x12*y21 + x12*y11)/den;
	return [l,m];
}
function projete(x,y, x1,y1, x2,y2) {
	var d = (x2-x1)*(x2-x1) + (y2-y1)*(y2-y1);
	if (d)
		return (-x1*x2+x1*x1+x*x2-x*x1-y1*y2+y1*y1+y*y2-y*y1)/d;
	return 1;
}
function intersectionLineCircle(x0,y0,r, x1,y1,u,v) {
	var r2 = r*r, u2 = u*u, v2 = v*v, x02 = x0*x0, y02 = y0*y0, x12 = x1*x1, y12 = y1*y1;
	var delta = (-u2*y12)+2*u2*y0*y1+2*u*v*x1*y1-2*u*v*x0*y1-u2*y02-2*u*v*x1*y0+2*u*v*x0*y0-v2*x12+2*v2*x0*x1-v2*x02+r2*v2+r2*u2;
	if (delta >= 0) {
		var a = u2+v2, b = v*y1-v*y0+u*x1-u*x0;
		return (Math.sqrt(delta)-b)/a;
	}
	return NaN;
}
function followCircleWithAngle(x1,y1,x2,y2,u,v) {
	var w = Math.hypot(u,v);
	var l = Math.hypot(x2-x1,y2-y1);
	var theta = Math.atan2(v,u)-Math.atan2(y2-y1,x2-x1);
	var sinTheta = Math.sin(theta);
	if (w && sinTheta) {
		var r = l/(2*sinTheta);
		var x0 = x2 - v*r/w, y0 = y2 + u*r/w;
		return [x0,y0,r];
	}
	return [x2,y2,l];
}
function nextAiStop(x1,y1,u1,v1,x2,y2,u2,v2,r) {
	var w1 = Math.hypot(u1,v1), w2 = Math.hypot(u2,v2), w0 = u2*v1-u1*v2;
	if (w1 && w2 && w0) {
		for (var e1=-1;e1<2;e1+=2) {
			for (var e2=-1;e2<2;e2+=2) {
				var x0 = (u1*(-r*e2*w2+u2*(y2-y1)-v2*x2)+u2*r*e1*w1+v1*u2*x1)/w0;
				var y0 = (v1*(-r*e2*w2+u2*y2+v2*(x1-x2))+v2*r*e1*w1-u1*v2*y1)/w0;
				var xT = -e1*(v1*r)/w1, yT = e1*(u1*r)/w1;
				var xN = -e2*(v2*r)/w2, yN = e2*(u2*r)/w2;
				var l1 = projete(x0+xT,y0+yT, x1,y1,x1+u1,y1+v1), l2 = projete(x0+xN,y0+yN, x2,y2,x2+u2,y2+v2);
				if ((l1 <= 1) && (l2 >= 0))
					return l1;
			}
		}
	}
	if (u1*u2 + v1*v2 > 0)
		return 1;
	else
		return 0;
}
function aiMarginLimitSpeed(x1,y1,u1,v1,x2,y2,u2,v2,h,o) {
	var w2 = Math.hypot(u2,v2);
	var minR = Infinity;
	for (var e=-1;e<2;e+=2) {
		var xH = x2 + e*v2*h/w2, yH = y2 - e*u2*h/w2;
		var r = aiMarginLimitRadius(x1,y1,u1,v1,xH,yH,u2,v2);
		if (r < minR)
			minR = r;
	}
	return 2*minR*o;
}
function aiMarginLimitRadius(x1,y1,u1,v1,xH,yH,u2,v2) {
	var uH = xH-x1, vH = yH-y1, d1 = u1*u1*v2*v2-2*u1*v1*u2*v2+v1*v1*u2*u2, d2 = u1*u1*(v2*v2*v2*v2+u2*u2*v2*v2)+u1*v1*u2*v2*(-2*v2*v2-2*u2*u2)+v1*v1*u2*u2*(v2*v2+u2*u2);
	if (d1 && d2) {
		for (var e=-1;e<2;e+=2) {
			var w1 = Math.hypot(u1,v1), w2 = Math.hypot(u2,v2);
			var X1 = (v1*(-e*uH*w1*v2*w2+e*vH*w1*u2*w2+u1*(uH*u2*v2-vH*u2*u2))+v1*v1*(uH*v2*v2-vH*u2*v2))/d1;
			var Y1 = -(u1*(e*vH*w1*u2*w2-e*uH*w1*v2*w2)+u1*v1*(uH*v2*v2-vH*u2*v2)+u1*u1*(uH*u2*v2-vH*u2*u2))/d1;
			var X2 = (v1*v2*v2*(e*vH*w1*u2*w2-e*uH*w1*v2*w2)+u1*u2*v2*(e*vH*w1*u2*w2-e*uH*w1*v2*w2)+v1*v1*v2*(uH*v2*v2*v2-vH*u2*v2*v2+uH*u2*u2*v2-vH*u2*u2*u2)+u1*u1*v2*(uH*v2*v2*v2-vH*u2*v2*v2+uH*u2*u2*v2-vH*u2*u2*u2))/d2;
			var Y2 = -(v1*u2*v2*(e*vH*w1*u2*w2-e*uH*w1*v2*w2)+u1*u2*u2*(e*vH*w1*u2*w2-e*uH*w1*v2*w2)+v1*v1*u2*(uH*v2*v2*v2-vH*u2*v2*v2+uH*u2*u2*v2-vH*u2*u2*u2)+u1*u1*(uH*u2*v2*v2*v2-vH*u2*u2*v2*v2+uH*u2*u2*u2*v2-vH*u2*u2*u2*u2))/d2;
			var x0 = x1-X1, y0 = y1-Y1, x2 = X2+x0, y2 = Y2+y0;
			var theta = Math.atan2(Y2,X2)-Math.atan2(Y1,X1);
			var w0 = rotateVector(u1,v1,theta);
			var dot = w0[0]*u2-w0[1]*v2;
			if (dot >= 0)
				return Math.hypot(X1,Y1);
		}
	}
	return Infinity;
}
function rotateVector(u,v,theta) {
	var cs = Math.cos(theta), sn = Math.sin(theta);
	return [u*cs-v*sn, u*sn+v*cs];
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
		for (var type in oMap.decor) {
			for (var i=0;i<oMap.decor[type].length;i++) {
				var oBox = oMap.decor[type][i];
				var hitboxSize = decorBehaviors[type].hitbox||DEFAULT_DECOR_HITBOX;
				var lines = [{
					"x1" : oBox[0]-hitboxSize,
					"y1" : oBox[1]-hitboxSize,
					"x2" : oBox[0]+hitboxSize,
					"y2" : oBox[1]-hitboxSize
				},{
					"x1" : oBox[0]-hitboxSize,
					"y1" : oBox[1]-hitboxSize,
					"x2" : oBox[0]-hitboxSize,
					"y2" : oBox[1]+hitboxSize
				},{
					"x1" : oBox[0]-hitboxSize,
					"y1" : oBox[1]+hitboxSize,
					"x2" : oBox[0]+hitboxSize,
					"y2" : oBox[1]+hitboxSize
				},{
					"x1" : oBox[0]+hitboxSize,
					"y1" : oBox[1]-hitboxSize,
					"x2" : oBox[0]+hitboxSize,
					"y2" : oBox[1]+hitboxSize
				}];
				var colLine = getLineHorizontality(iX,iY, nX,nY, lines);
				if (colLine && (colLine.t < nearCol.t))
					nearCol = colLine;
			}
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
function nearestAngle(angle1,angle2, modulo) {
	return angle1 + modulo*Math.round((angle2-angle1)/modulo);
}
function getNearestHoleDist(iX,iY, stopAt) {
	var res = stopAt || Infinity;
	if (!oMap.trous)
		return res;
	for (var j=0;j<4;j++) {
		var oRectangles = oMap.trous[j].rectangle;
		for (var i=0;i<oRectangles.length;i++) {
			var oHole = oRectangles[i][0];
			res = getHoleSegmentDist(res, iX,iY, oHole[0],oHole[1], oHole[2],0);
			res = getHoleSegmentDist(res, iX,iY, oHole[0],oHole[1], 0,oHole[3]);
			res = getHoleSegmentDist(res, iX,iY, oHole[0],oHole[1]+oHole[3], oHole[2],0);
			res = getHoleSegmentDist(res, iX,iY, oHole[0]+oHole[2],oHole[1], 0,oHole[3]);
			if (res < stopAt)
				return res;
		}
		var oPolygons = oMap.trous[j].polygon;
		for (var i=0;i<oPolygons.length;i++) {
			var oHole = oPolygons[i][0];
			for (var k=0;k<oHole.length;k++) {
				var knc = (k+1)%oHole.length;
				res = getHoleSegmentDist(res, iX,iY, oHole[k][0],oHole[k][1], oHole[knc][0],oHole[knc][1]);
			}
			if (res < stopAt)
				return res;
		}
	}
	return res;
}
function getHoleSegmentDist(currentRes, x,y, x1,y1, u1,v1) {
	var l = projete(x,y, x1,y1,x1+u1,y1+v1);
	if (l < 0) l = 0;
	if (l > 1) l = 1;
	var x0 = x1 + l*u1, y0 = y1 + l*v1;
	var u = x0-x, v = y0-y;
	var res = u*u + v*v;
	if (res > currentRes)
		return currentRes;
	if (canMoveTo(x,y, u,v))
		return res;
	return currentRes;
}

var touchedObject; // TODO make this a local var
function objet(iX, iY) {
	for (var i=0;i<oMap.arme.length;i++) {
		var oBox = oMap.arme[i];
		if (iX > oBox[0] - 7 && iX < oBox[0] + 7 && iY > oBox[1] - 7 && iY < oBox[1] + 7 && isNaN(oBox[2])) {
			for (var j=0;j<strPlayer.length;j++)
				oBox[2][j].div.style.display = "none";
			oBox[2] = 20;
			touchedObject = i;
			return true;
		}
	}
	return false;
}

function touche_piece(iX, iY) {
	if (oMap.coins) {
		for (var i=0;i<oMap.coins.length;i++) {
			var oBox = oMap.coins[i];
			if (iX > oBox.x - 5 && iX < oBox.x + 5 && iY > oBox.y - 5 && iY < oBox.y + 5) {
				oBox.sprite[0].suppr();
				oMap.coins.splice(i,1);
				return true;
			}
		}
	}
	return false;
}

function sauts(iX, iY, iI, iJ) {
	if (!oMap.sauts)
		return false;
	var aPos = [iX, iY], aMove = [iI, iJ];
	var dir = [(iI>0), (iJ>0)];
	for (var i=0;i<oMap.sauts.length;i++) {
		var oBox = oMap.sauts[i];
		if (pointInRectangle(iX,iY, oBox))
			return oBox[4];
		for (var j=0;j<2;j++) {
			var l = dir[j];
			if ((l ? ((aPos[j] <= oBox[j])&&((aPos[j]+aMove[j]) >= oBox[j])):((aPos[j] >= (oBox[j]+oBox[j+2]))&&((aPos[j]+aMove[j]) <= (oBox[j]+oBox[j+2]))))) {
				var dim = 1-j;
				var croiseJ = aPos[dim] + ((l?oBox[j]:oBox[j]+oBox[j+2])-aPos[j])*aMove[dim]/aMove[j];
				if ((croiseJ >= oBox[dim]) && (croiseJ <= (oBox[dim]+oBox[dim+2])))
					return oBox[4];
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
function getOffroadProps(oKart,hpType) {
	switch (hpType) {
		case "herbe" :
			return {speed:1.9+oKart.speedinc/2};
		case "glace" :
			return {speed:2.8+oKart.speedinc/2,sliding:8};
		case "eau" :
			return {speed:2.7,sliding:5};
		case "choco" :
			return {speed:2.1,sliding:4};
	}
}

function accelere(iX, iY, iI, iJ) {
	if (!oMap.accelerateurs) return false;
	var nX = iX+iI, nY = iY+iJ;
	var aPos = [iX, iY], aMove = [iI, iJ];
	var dir = [(iI>0), (iJ>0)];
	for (var i=0;i<oMap.accelerateurs.length;i++) {
		var oBox = oMap.accelerateurs[i];
		if (pointInRectangle(nX,nY, oBox))
			return true;
		for (var j=0;j<2;j++) {
			var l = dir[j];
			if ((l ? ((aPos[j] <= oBox[j])&&((aPos[j]+aMove[j]) >= oBox[j])):((aPos[j] >= (oBox[j]+oBox[j+2]))&&((aPos[j]+aMove[j]) <= (oBox[j]+oBox[j+2]))))) {
				var dim = 1-j;
				var croiseJ = aPos[dim] + ((l?oBox[j]:oBox[j]+oBox[j+2])-aPos[j])*aMove[dim]/aMove[j];
				if ((croiseJ >= oBox[dim]) && (croiseJ <= (oBox[dim]+oBox[dim+2])))
					return true;
			}
		}
	}
	return false;
}

function flowShift(iX,iY, iP) {
	if (oMap.flows) {
		var oRectangles = oMap.flows.rectangle;
		for (var i=0;i<oRectangles.length;i++) {
			var oFlow = oRectangles[i];
			if (pointInRectangle(iX,iY, oFlow[0]) && (!iP||oFlow[2]))
				return [oFlow[1][0],oFlow[1][1],0];
		}
		var oPolygons = oMap.flows.polygon;
		for (var i=0;i<oPolygons.length;i++) {
			var oFlow = oPolygons[i];
			if (pointInPolygon(iX,iY, oFlow[0]) && (!iP||oFlow[2]))
				return [oFlow[1][0],oFlow[1][1],0];
		}
	}
	if (oMap.spinners) {
		var oCircles = oMap.spinners;
		for (var i=0;i<oCircles.length;i++) {
			var oCircle = oCircles[i];
			var diffX = iX-oCircle[0], diffY = iY-oCircle[1];
			var r2 = diffX*diffX + diffY*diffY;
			if (r2 < oCircle[2]*oCircle[2])
				return [diffY*oCircle[3],-diffX*oCircle[3],oCircle[3]];
		}
	}
	return [0,0,0];
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
function inCannon(iX,iY) {
	if (!oMap.cannons) return false;
	var oRectangles = oMap.cannons.rectangle;
	for (var i=0;i<oRectangles.length;i++) {
		var cannon = oRectangles[i];
		if (pointInRectangle(iX,iY, cannon[0]))
			return cannon[1];
	}
	var oPolygons = oMap.cannons.polygon;
	for (var i=0;i<oPolygons.length;i++) {
		var cannon = oPolygons[i];
		if (pointInPolygon(iX,iY, cannon[0]))
			return cannon[1];
	}
	return false;
}

function getActualGameTimeMS() {
	if (timerMS != undefined)
		return timerMS;
	return (timer-1)*SPF;
}
function getActualGameTime() {
	return getActualGameTimeMS()/1000;
}
var lambdaReturnsTrue = function(scope){return true};
function addChallengeHud(key, options) {
	if (clHud[key]) return;
	var oChallengeCpt = document.createElement("div");
	oChallengeCpt.innerHTML = "<span>"+ options.title +":</span> <span>"+ options.value +"</span>"+ ((options.out_of!=null) ? "/<span>"+options.out_of+"</span>" : "");
	var oChallengeNodes = oChallengeCpt.getElementsByTagName("span");
	oChallengeCpts.appendChild(oChallengeCpt);
	clHud[key] = {
		"$cpt": oChallengeCpt,
		"$label": oChallengeNodes[0],
		"$value": oChallengeNodes[1],
		"$outOf": oChallengeNodes[2]
	}
}
function updateChallengeHud(key, value) {
	if (clHud[key])
		clHud[key].$value.innerText = value;
}
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
			return (getActualGameTime() <= scope.value);
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
		"initSelected": function(scope) {
			addChallengeHud("hits", {
				title: toLanguage("Hits","Touchés"),
				value: clLocalVars.nbHits,
				out_of: scope.value
			});
		},
		"success": function(scope) {
			if (clLocalVars.nbHits >= scope.value)
				return true;
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
		"initSelected": function(scope) {
			addChallengeHud("kills", {
				title: toLanguage("Defeated","Eliminés"),
				value: clLocalVars.nbKills,
				out_of: scope.value
			});
		},
		"success": function(scope) {
			if (clLocalVars.nbKills >= scope.value)
				return true;
		}
	},
	"survive": {
		"verify": "each_frame",
		"success": function(scope) {
			if (getActualGameTime() >= scope.value)
				return true;
		}
	},
	"reach_zone": {
		"verify": "each_frame",
		"initLocalVars": function(scope) {
			if (!scope.zones) scope.zones = classifyByShape(scope.value);
		},
		"success": function(scope) {
			var zones = scope.zones;
			var posX = oPlayers[0].x;
			var posY = oPlayers[0].y;
			var oRectangles = zones.rectangle;
			for (var i=0;i<oRectangles.length;i++) {
				if (pointInRectangle(posX,posY, oRectangles[i]))
					return true;
			}
			var oPolygons = zones.polygon;
			for (var i=0;i<oPolygons.length;i++) {
				if (pointInPolygon(posX,posY, oPolygons[i]))
					return true;
			}
		}
	},
	"reach_zones": {
		"verify": "each_frame",
		"initLocalVars": function(scope) {
			if (!scope.zones) scope.zones = classifyByShape(scope.value);
			clLocalVars.reached = [];
			clLocalVars.reached.length = scope.value.length;
			for (var i=0;i<clLocalVars.reached.length;i++)
				clLocalVars.reached[i] = false;
			clLocalVars.nbPass = 0;
		},
		"initSelected": function(scope) {
			addChallengeHud("zones", {
				title: toLanguage("Zones","Zones"),
				value: clLocalVars.nbPass,
				out_of: scope.value.length
			});
		},
		"success": function(scope) {
			var zones = scope.zones;
			var allZones = scope.value;
			var posX = oPlayers[0].x;
			var posY = oPlayers[0].y;
			var oRectangles = zones.rectangle;
			var reachedZones = [];
			for (var i=0;i<oRectangles.length;i++) {
				if (pointInRectangle(posX,posY, oRectangles[i]))
					reachedZones.push(allZones.indexOf(oRectangles[i]));
			}
			var oPolygons = zones.polygon;
			for (var i=0;i<oPolygons.length;i++) {
				if (pointInPolygon(posX,posY, oPolygons[i]))
					reachedZones.push(allZones.indexOf(oPolygons[i]));
			}
			reachedZones.sort();
			for (var i=0;i<reachedZones.length;i++) {
				var reachedZone = reachedZones[i];
				if (clLocalVars.reached[reachedZone]) continue;
				if (scope.ordered) {
					if (reachedZone && !clLocalVars.reached[reachedZone-1])
						break;
				}
				clLocalVars.reached[reachedZone] = true;
				clLocalVars.nbPass++;
				if (iSfx)
					playSoundEffect("musics/events/clpass.mp3");
				updateChallengeHud("zones", clLocalVars.nbPass);
				if (clLocalVars.nbPass >= allZones.length)
					return true;
			}
		}
	},
	"hit_items": {
		"verify": "each_item",
		"initLocalVars": function(scope) {
			clLocalVars.nbItems = 0;
			clLocalVars.itemsHit = [];
			clLocalVars.itemsHit.length = oMap.arme.length;
		},
		"initSelected": function(scope) {
			addChallengeHud("items", {
				title: toLanguage("Items","Objets"),
				value: clLocalVars.nbItems,
				out_of: oMap.arme.length
			});
		},
		"success": function(scope) {
			if (clLocalVars.nbItems >= oMap.arme.length)
				return true;
		}
	},
	"collect_coins": {
		"verify": "each_coin",
		"initLocalVars": function(scope) {
			clLocalVars.nbCoins = 0;
			if (!scope.nb)
				scope.nb = scope.value.length;
		},
		"initSelected": function(scope) {
			if (!oMap.coins) {
				oMap.coins = [];
				for (var i=0;i<scope.value.length;i++) {
					var oCoin = scope.value[i];
					var mCoin = {
						x: oCoin[0],
						y: oCoin[1],
						theta: 2*Math.PI*Math.random(),
						sprite: new Sprite("coin")
					};
					for (var j=0;j<oPlayers.length;j++) {
						mCoin.sprite[j].img.style.width = "100%";
						mCoin.sprite[j].w = 24;
						mCoin.sprite[j].h = 24;
					}
					oMap.coins.push(mCoin);
				}
			}
			addChallengeHud("coins", {
				title: toLanguage("Coins","Pièces"),
				value: clLocalVars.nbCoins,
				out_of: scope.nb
			});
		},
		"success": function(scope) {
			if (clLocalVars.nbCoins >= scope.nb)
				return true;
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
		"initSelected": function(scope, ruleVars) {
			if (ruleVars && ruleVars.nbcircuits) {
				addChallengeHud("races", {
					title: toLanguage("Race","Course"),
					value: ruleVars.nbcircuits,
					out_of: scope.value
				});
			}
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
		"initSelected": function(scope, ruleVars) {
			if (ruleVars && ruleVars.nbcircuits) {
				addChallengeHud("races", {
					title: toLanguage("Race","Course"),
					value: ruleVars.nbcircuits,
					out_of: scope.value
				});
			}
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
		"initSelected": function(scope, ruleVars) {
			if (ruleVars && ruleVars.nbcircuits) {
				addChallengeHud("races", {
					title: toLanguage("Race","Course"),
					value: ruleVars.nbcircuits,
					out_of: scope.value
				});
			}
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
			return (oPlayers[0].ballons.length+oPlayers[0].reserve >= scope.value);
		}
	},
	"balloons_lost": {
		"initSelected": function(scope, ruleVars) {
			addChallengeHud("balloons", {
				title: toLanguage("Balloons","Ballons"),
				value: clLocalVars.lostBalloons,
				out_of: scope.value
			});
		},
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
	"avoid_decors": {
		"initLocalVars": function(scope) {
			if (!clLocalVars.decorsHit)
				clLocalVars.decorsHit = {};
		},
		"success": function(scope) {
			for (var key in scope.value) {
				if (clLocalVars.decorsHit[key])
					return false;
			}
			return true;
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
		"initSelected": function(scope, ruleVars) {
			if (ruleVars) {
				addChallengeHud("falls", {
					title: toLanguage("Falls","Chutes"),
					value: clLocalVars.falls+ruleVars.falls,
					out_of: scope.value
				});
			}
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
	"backwards": {
		"initSelected": function(scope) {
			clLocalVars.backwardsStart = true;
		},
		"success": function(scope) {
			return !clLocalVars.forwards;
		}
	},
	"time": {
		"success": function(scope) {
			return (getActualGameTime() <= scope.value);
		}
	},
	"time_delay": {
		"initSelected": function(scope) {
			clLocalVars.delayedStart = scope.value;
		},
		"success": function(scope) {
			if (!clLocalVars.startedAt) return true;
			var seconds = (clLocalVars.startedAt-1)*SPF/1000;
			return (seconds >= scope.value);
		}
	},
	"mini_turbo": {
		"initRuleVars": function() {
			return {miniTurbo: 0};
		},
		"initSelected": function(scope, ruleVars) {
			if (ruleVars) {
				addChallengeHud("miniTurbo", {
					title: "Mini Turbos",
					value: clLocalVars.miniTurbo+ruleVars.miniTurbo,
					out_of: scope.value
				});
			}
		},
		"success": function(scope, ruleVars) {
			if (ruleVars) {
				if ((ruleVars.miniTurbo+clLocalVars.miniTurbo) >= scope.value)
					return true;
			}
		},
		"next_circuit": function(ruleVars) {
			if (ruleVars)
				ruleVars.miniTurbo += clLocalVars.miniTurbo;
		}
	},
	"super_turbo": {
		"initRuleVars": function() {
			return {superTurbo: 0};
		},
		"initSelected": function(scope, ruleVars) {
			if (ruleVars) {
				addChallengeHud("superTurbo", {
					title: "Super Turbos",
					value: clLocalVars.superTurbo+ruleVars.superTurbo,
					out_of: scope.value
				});
			}
		},
		"success": function(scope, ruleVars) {
			if (ruleVars) {
				if ((ruleVars.superTurbo+clLocalVars.superTurbo) >= scope.value)
					return true;
			}
		},
		"next_circuit": function(ruleVars) {
			if (ruleVars)
				ruleVars.superTurbo += clLocalVars.superTurbo;
		}
	},
	"stunts": {
		"initRuleVars": function() {
			return {stunts: 0};
		},
		"initSelected": function(scope, ruleVars) {
			if (ruleVars) {
				addChallengeHud("stunts", {
					title: toLanguage("Stunts","Figures"),
					value: clLocalVars.stunts+ruleVars.stunts,
					out_of: scope.value
				});
			}
		},
		"success": function(scope, ruleVars) {
			if (ruleVars) {
				if ((ruleVars.stunts+clLocalVars.stunts) >= scope.value)
					return true;
			}
		},
		"next_circuit": function(ruleVars) {
			if (ruleVars)
				ruleVars.stunts += clLocalVars.stunts;
		}
	},
	"position": {
		"success": function(scope) {
			if (oPlayers[0].place == scope.value)
				return true;
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
			if (challenge.succeeded && (challenge !== clSelected))
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
function isSameDistrib(d1,d2) {
	if (d1.length !== d2.length)
		return false;
	for (var i=0;i<d1.length;i++) {
		var d1i = d1[i], d2i = d2[i];
		for (var key in d1i) {
			if (d1i[key] !== d2i[key])
				return false;
		}
		for (var key in d2i) {
			if (d1i[key] !== d2i[key])
				return false;
		}
	}
	return true;
}
function reinitLocalVars() {
	clLocalVars = {
		drifted: false,
		stunted: false,
		itemsGot: false,
		itemsUsed: false,
		falls: 0,
		miniTurbo: 0,
		superTurbo: 0,
		stunts: 0,
		lostBalloons: 0,
		cheated: false
	};
	if (itemDistribution) {
		var modeItemDistributions = itemDistributions[getItemMode()];
		var isDefaultDistrib = false;
		for (var i=0;i<2;i++) {
			if (isSameDistrib(itemDistribution, modeItemDistributions[i].value)) {
				isDefaultDistrib = true;
				break;
			}
		}
		if (!isDefaultDistrib)
			clLocalVars.cheated = true;
	}
	clHud = {};
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
				if ((clSelected === challenge) && challengeRules[rule.type].initSelected) {
					var ruleVars = clRuleVars[challenge.id] ? clRuleVars[challenge.id][rule.type] : undefined;
					challengeRules[rule.type].initSelected(rule, ruleVars);
				}
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
		var chRules = listChallengeRules(challenge.data);
		var status = challengeRulesSatisfied(challenge,chRules);
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
var clSelectionFail = false;
function challengeHandleFail() {
	if (clSelectionFail) return;
	if (clSelected.succeeded) return;
	clSelectionFail = true;
	clHud = {};
	oChallengeCpts.innerHTML = "";
	if (timer > 1)
		showClFailedPopup();
}
function challengeRulesSatisfied(challenge, chRules) {
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
	if (challenge.succeeded && (challenge !== clSelected)) return;
	var wasSucceeded = challenge.succeeded;
	challenge.succeeded = true;
	if (clSelected == challenge) {
		clSelected = undefined;
		clHud = {};
	}
	if ("pending_completion" === challenge.status)
		challenge.status = "pending_publication";
	delete clRuleVars[challenge.id];
	if (wasSucceeded) {
		setTimeout(function() {
			showChallengePopup(challenge, {});
		}, 1);
		return;
	}
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
		if (data.rewards) {
			for (var i=0;i<data.rewards.length;i++) {
				var rewardId = data.rewards[i].id;
				for (var j=0;j<clRewards.length;j++) {
					if (clRewards[j].id == rewardId) {
						clRewards[j].unlocked = 1;
						break;
					}
				}
			}
		}
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
		$mkScreen.insertBefore(oDiv, oOtherPopup[0]);
	else
		$mkScreen.appendChild(oDiv);
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
	focusOnChallengeClose();
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
		$mkScreen.insertBefore(oDiv, oOtherPopup[0]);
	else
		$mkScreen.appendChild(oDiv);
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
	if (res.unlocked) {
		for (var i=0;i<res.unlocked.length;i++)
			showChallengeRewardPopup(res.unlocked[i]);
	}
	focusOnChallengeClose();
}
function showChallengeRewardPopup(reward) {
	var lastPopup = document.getElementById("challenge-popup-reward-"+reward.id);
	if (lastPopup) return;
	var oDiv = document.createElement("div");
	oDiv.id = "challenge-popup-reward-"+reward.id;
	oDiv.className = "challenge-popup";
	oDiv.style.width = (iScreenScale*56) +"px";
	oDiv.style.left = (iScreenScale*12) +"px";
	oDiv.style.top = Math.round(iScreenScale*4.5) +"px";
	oDiv.style.padding = Math.round(iScreenScale*1.5) +"px";
	oDiv.style.paddingBottom = (iScreenScale*5) +"px";
	oDiv.style.border = "inset "+ Math.round(iScreenScale*0.5) +"px #7B0";
	oDiv.style.fontSize = (iScreenScale*2) +"px";
	oDiv.style.opacity = 0;
	var challengeTitle = language ? 'New character unlocked!':'Nouveau perso débloqué !';
	var challengeCongrats = language ? 'You can now play with <strong>'+ reward.name +'</strong>!':'Vous pouvez désormais jouer avec <strong>'+ reward.name +'</strong> !';
	var challengeImg = document.createElement("img");
	challengeImg.src = getSpriteSrc(reward.sprites);
	challengeImg.alt = reward.name;
	challengeImg.className = "pixelated";
	challengeImg.style.visibility = "hidden";
	var challengeClose = language ? 'Close':'Fermer';
	oDiv.innerHTML = 
		'<div style="font-size: '+ Math.round(iScreenScale*2) +'px">'+
			'<img src="images/cups/cup1.png" alt="star" class="pixelated" style="width:'+ Math.round(iScreenScale*2.5) +'px" /> '+
			'<h1 class="challenge-popup-title" style="margin:'+ Math.round(iScreenScale/2) +'px 0; font-size: '+ Math.round(iScreenScale*3.25) +'px">'+ challengeTitle +'</h1>'+
		'</div>'+
		'<div class="challenge-popup-header" style="font-size: '+ Math.round(iScreenScale*2.25) +'px">'+challengeCongrats+'</div>'+
		'<div class="challenge-popup-reward-ch"></div>' +
		'<div class="challenge-popup-close" style="font-size:'+(iScreenScale*2)+'px;bottom:'+iScreenScale+'px;right:'+Math.round(iScreenScale*1.25)+'px">'+
			'<a href="javascript:closeChallengePopup(&quot;reward-'+reward.id+'&quot;);">'+ challengeClose +'</a>'+
		'</div>';
	oDiv.getElementsByClassName("challenge-popup-reward-ch")[0].appendChild(challengeImg);
	challengeImg.onload = function() {
		var oContainer = this.parentNode;
		var scaleRatio = iScreenScale/8;
		var oWidth = Math.round(scaleRatio*this.naturalWidth/24), oHeight = Math.round(scaleRatio*this.naturalHeight);
		oContainer.style.width = oWidth +"px";
		oContainer.style.height = oHeight +"px";
		this.style.width = (oWidth*24) +"px";
		this.style.height = oHeight +"px";
		this.style.visibility = "";
	}
	var oOtherPopup = document.getElementsByClassName("challenge-popup");
	if (oOtherPopup.length)
		$mkScreen.insertBefore(oDiv, oOtherPopup[0]);
	else
		$mkScreen.appendChild(oDiv);
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
function focusOnChallengeClose() {
	var $closeBtns = document.querySelectorAll(".challenge-popup .challenge-popup-close a");
	if ($closeBtns.length)
		$closeBtns[$closeBtns.length-1].focus();
	else {
		var resumeButton = document.getElementById("reprendre");
		if (resumeButton)
			resumeButton.focus();
		else {
			var oCtn = document.getElementById("octn");
			if (oCtn) oCtn.focus();
		}
	}
}
window.closeChallengePopup = function(id) {
	var challengePopup = document.getElementById("challenge-popup-"+id);
	if (challengePopup) {
		if (clHud && (Object.keys(clHud).length === 0) && oChallengeCpts.firstChild)
			oChallengeCpts.innerHTML = "";
		var opacity = 1;
		function fadeOutPopup() {
			if (opacity > 0) {
				challengePopup.style.opacity = opacity;
				opacity -= 0.2;
				setTimeout(fadeOutPopup,40);
			}
			else {
				$mkScreen.removeChild(challengePopup);
				focusOnChallengeClose();
			}
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
function showClSelectedPopup() {
	var $popup = document.createElement("div");
	$popup.style.fontSize = (2*iScreenScale) +"px";
	$popup.className = "clselected-popup";
	$popup.style.left = (27*iScreenScale) +"px";
	$popup.style.top = (iHeight*iScreenScale) +"px";
	$popup.innerHTML = 
		'<div class="clselected-close">'+
			'<a href="#null">&times;</a>'+
		'</div>'+
		'<div class="clselected-ctn">'+
			'<div>\u2714</div>'+
			'<div><strong>'+ toLanguage('Challenge selected :','Défi sélectionné:') +'</strong> '+ (clSelected.name || clSelected.description.main) +'</div>'+
		'</div>';
	$popup.querySelector(".clselected-close a").onclick = function() {
		document.body.removeChild($popup);
		return false;
	}
	document.body.appendChild($popup);
	var opacity = 1;
	function fadeOutPopup() {
		if (opacity > 0) {
			$popup.style.opacity = opacity;
			opacity -= 0.04;
			setTimeout(fadeOutPopup,40);
		}
		else
			document.body.removeChild($popup);
	}
	setTimeout(fadeOutPopup, 1500);
}
function showClFailedPopup() {
	var $popup = document.createElement("div");
	$popup.style.position = "absolute";
	$popup.style.color = "#C00";
	$popup.style.right = Math.round(iScreenScale/2) +"px";
	$popup.style.top = Math.round(iScreenScale*2.5) +"px";
	$popup.style.fontSize = Math.round(iScreenScale*1.8) +"px";
	$popup.style.display = "flex";
	$popup.style.alignItems = "center";
	$popup.style.fontFamily = "Courier New";
	$popup.innerHTML = '<strong style="color:#800;font-size:1.8em">&times;</strong>&nbsp;' + (language ? 'Challenge failed...':'Défi échoué...');
	var hudScreen = oChallengeCpts.parentNode;
	hudScreen.appendChild($popup);
	if (iSfx && !finishing && !oPlayers[0].cpu)
		playSoundEffect("musics/events/clfail.mp3");
}

var isMetaItem = 0;
var metaItemPosition = 0.5, metaItemRange = 1000;
var itemDistributions = {
	"BB": [{
		name: toLanguage("Standard", "Classique"),
		value: [{
			"fauxobjet": 4,
			"banane": 5,
			"carapacerouge": 1,
			"carapace": 4
		}, {
			"carapace": 4,
			"carapacerouge": 7,
			"bobomb": 2,
			"bananeX3": 1
		}, {
			"carapace": 1,
			"bobomb": 2,
			"carapace": 4,
			"carapaceX3": 2,
			"banane": 1,
			"fauxobjet": 1,
			"carapacerouge": 4
		}, {
			"carapacebleue": 0,
			"carapacerougeX3": 1,
			"carapacerouge": 2,
			"megachampi": 3,
			"etoile": 3,
			"champi": 3,
			"champior": 1,
			"champiX3": 1,
			"bloops": 1
		}]
	}, {
		name: toLanguage("Explosive mode", "Mode explosif"),
		value: [{
			"fauxobjet": 1,
			"banane": 3,
			"carapacerouge": 3,
			"carapace": 5
		}, {
			"bananeX3": 1,
			"carapacerouge": 12,
			"carapace": 6,
			"bobomb": 4
		}, {
			"carapacerouge": 8,
			"carapace": 5,
			"bobomb": 4,
			"carapaceX3": 3
		}, {
			"carapacerouge": 7,
			"carapacebleue": 4,
			"carapacerougeX3": 3,
			"megachampi": 5,
			"etoile": 5,
			"champi": 1,
			"champior": 1,
			"champiX3": 1,
			"bloops": 1
		}]
	}, {
		name: toLanguage("Shells", "Carapaces"),
		value: [{
			"carapacerouge": 1,
			"carapace": 4
		}, {
			"carapacerouge": 7,
			"carapace": 4
		}, {
			"carapacerouge": 4,
			"carapace": 4,
			"carapaceX3": 2
		}, {
			"carapacerouge": 6,
			"carapacebleue": 1,
			"carapacerougeX3": 3
		}]
	}, {
		name: toLanguage("Bob-ombs", "Bob-ombs"),
		value: [{
			"bobomb": 1
		}, {
			"bobomb": 1
		}, {
			"bobomb": 1
		}, {
			"bobomb": 1
		}]
	}, {
		name: toLanguage("Mushrooms", "Champis"),
		value: [{
			"champi": 1,
			"poison": 1
		}, {
			"megachampi": 1,
			"champi": 3,
			"champiX3": 1
		}, {
			"megachampi": 2,
			"champi": 2,
			"champior": 1,
			"champiX3": 2
		}, {
			"megachampi": 1,
			"champior": 1,
			"champiX3": 1
		}]
	}],
	"VS": [{
		name: toLanguage("Standard", "Classique"),
		value: [{
			"fauxobjet": 10,
			"banane": 6,
			"carapace": 6,
			"bananeX3": 2,
			"carapacerouge": 2
		}, {
			"carapace": 8,
			"bananeX3": 4,
			"carapacerouge": 5,
			"champi": 3
		}, {
			"carapaceX3": 7,
			"carapacerouge": 7,
			"poison": 5,
			"champi": 5,
			"bobomb": 3,
			"bloops": 3
		}, {
			"champi": 6,
			"poison": 4,
			"carapaceX3": 6,
			"carapacerouge": 5,
			"bobomb": 3,
			"bloops": 2
		}, {
			"champi": 8,
			"carapacerouge": 6,
			"bobomb": 3,
			"champiX3": 2
		}, {
			"bobomb": 4,
			"champi": 5,
			"carapacerouge": 4,
			"champiX3": 3,
			"carapacerougeX3": 3
		}, {
			"champi": 8,
			"carapacerougeX3": 7,
			"champiX3": 6,
			"megachampi": 3
		}, {
			"champiX3": 8,
			"carapacerougeX3": 8,
			"megachampi": 6,
			"etoile": 4,
			"champior": 2,
			"carapacebleue": 5
		}, {
			"megachampi": 7,
			"champiX3": 6,
			"etoile": 5,
			"champior": 3,
			"carapacebleue": 5
		}, {
			"megachampi": 8,
			"champiX3": 6,
			"etoile": 6,
			"billball": 3,
			"champior": 3,
			"carapacebleue": 5
		}, {
			"etoile": 6,
			"megachampi": 6,
			"billball": 5,
			"champior": 4,
			"eclair": 5
		}, {
			"billball": 4,
			"etoile": 3,
			"eclair": 6,
			"champior": 5
		}]
	}, {
		name: toLanguage("Aggressive mode", "Mode explosif"),
		"value": [{
			"fauxobjet": 5,
			"banane": 2,
			"carapace": 10,
			"bananeX3": 1,
			"carapacerouge": 3
		}, {
			"carapace": 10,
			"bananeX3": 4,
			"carapacerouge": 15,
			"carapaceX3": 4,
			"champi": 1
		}, {
			"carapacerouge": 10,
			"carapaceX3": 10,
			"champi": 2,
			"poison": 5,
			"bobomb": 10,
			"bloops": 3
		}, {
			"carapacerouge": 10,
			"carapaceX3": 12,
			"champi": 3,
			"poison": 4,
			"bobomb": 8,
			"bloops": 2
		}, {
			"carapacerouge": 12,
			"champi": 4,
			"bobomb": 8
		}, {
			"carapacerouge": 8,
			"champi": 5,
			"bobomb": 6,
			"champiX3": 1,
			"carapacerougeX3": 6
		}, {
			"champi": 4,
			"champiX3": 3,
			"carapacerougeX3": 10,
			"megachampi": 3
		}, {
			"champiX3": 4,
			"carapacerougeX3": 10,
			"megachampi": 6,
			"etoile": 4,
			"carapacebleue": 12
		}, {
			"champiX3": 3,
			"megachampi": 7,
			"etoile": 5,
			"champior": 1,
			"carapacebleue": 10
		}, {
			"champiX3": 3,
			"megachampi": 8,
			"etoile": 6,
			"champior": 1,
			"carapacebleue": 10,
			"billball": 5
		}, {
			"megachampi": 6,
			"etoile": 6,
			"champior": 1,
			"billball": 5,
			"eclair": 8
		}, {
			"etoile": 3,
			"champior": 2,
			"billball": 6,
			"eclair": 8
		}]
	}, {
		name:  toLanguage("Shells", "Carapaces"),
		value: [{
			"carapace": 6
		}, {
			"carapace": 8,
			"carapacerouge": 8
		}, {
			"carapace": 6,
			"carapacerouge": 5
		}, {
			"carapace": 6,
			"carapacerouge": 7
		}, {
			"carapace": 4,
			"carapacerouge": 5
		}, {
			"carapace": 4,
			"carapacerouge": 6,
			"carapaceX3": 2,
			"carapacerougeX3": 2
		}, {
			"carapace": 2,
			"carapacerouge": 4,
			"carapaceX3": 2,
			"carapacerougeX3": 2,
			"carapacebleue": 4
		}, {
			"carapace": 2,
			"carapacerouge": 2,
			"carapaceX3": 4,
			"carapacerougeX3": 4,
			"carapacebleue": 6
		}, {
			"carapacerouge": 2,
			"carapaceX3": 4,
			"carapacerougeX3": 4,
			"carapacebleue": 6
		}, {
			"carapaceX3": 6,
			"carapacerougeX3": 6,
			"carapacebleue": 4
		}, {
			"carapaceX3": 6,
			"carapacerougeX3": 6
		}, {
			"carapaceX3": 8,
			"carapacerougeX3": 8
		}]
	}, {
		name: toLanguage("Bob-ombs", "Bob-ombs"),
		value: [{
			"bobomb": 1
		}, {
			"bobomb": 1
		}, {
			"bobomb": 1
		}, {
			"bobomb": 1
		}, {
			"bobomb": 1
		}, {
			"bobomb": 1
		}, {
			"bobomb": 1
		}, {
			"bobomb": 1
		}, {
			"bobomb": 1
		}, {
			"bobomb": 1
		}, {
			"bobomb": 1
		}, {
			"bobomb": 1
		}]
	}, {
		name: toLanguage("Mushrooms", "Champis"),
		value: [{
			"champi": 1
		}, {
			"champi": 1,
			"poison": 1
		}, {
			"champi": 4,
			"poison": 1,
			"megachampi": 1
		}, {
			"champi": 3,
			"poison": 1,
			"megachampi": 1
		}, {
			"champi": 3,
			"poison": 1,
			"champiX3": 1,
			"megachampi": 2
		}, {
			"champi": 2,
			"champiX3": 1,
			"megachampi": 2
		}, {
			"champi": 1,
			"champiX3": 2,
			"megachampi": 3
		}, {
			"champiX3": 2,
			"megachampi": 2,
			"champior": 1
		}, {
			"champiX3": 1,
			"megachampi": 1,
			"champior": 1
		}, {
			"champiX3": 1,
			"champior": 1
		}, {
			"champiX3": 1,
			"champior": 2
		}, {
			"champior": 1
		}]
	}, {
		name: toLanguage("None", "Aucun"),
		value: []
	}]
};
var customItemDistrib = localStorage.getItem("itemsets");
if (customItemDistrib)
	customItemDistrib = JSON.parse(customItemDistrib);
else {
	customItemDistrib = {
		"VS": [],
		"BB": []
	}
}
function getItemMode() {
	return (course=="BB") ? "BB":"VS";
}

var COL_KART = 0, COL_OBJ = 1;
var collisionTest, collisionPlayer, collisionTeam, collisionDecor;
function isHitSound(oBox) {
	if (collisionTest==COL_OBJ)
		return true;
	if (collisionTeam==oBox.team)
		return {x:oBox.x,y:oBox.y};
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
	updateChallengeHud("hits", clLocalVars.nbHits);
	if ((course == "BB") && (kart.ballons.length == 1)) {
		if (clLocalVars.killed && clLocalVars.killed.indexOf(kart) == -1) {
			clLocalVars.killed.push(kart);
			clLocalVars.nbKills++;
			updateChallengeHud("kills", clLocalVars.nbKills);
		}
	}
	challengeCheck("each_hit");
}
function touche_banane(iX, iY, iP) {
	if (!iP) iP = [];
	for (var i=0;i<items["banane"].length;i++) {
		var oBox = items["banane"][i];
		if ((iP.indexOf(oBox) == -1) && !oBox.z) {
			if (iX > oBox.x-4 && iX < oBox.x+4 && iY > oBox.y-4 && iY < oBox.y + 4) {
				handleHit(oBox);
				detruit(oBox,isHitSound(oBox));
				return (collisionTeam!=oBox.team);
			}
		}
	}
	return false;
}
function touche_poison(iX, iY, iP) {
	if (!iP) iP = [];
	for (var i=0;i<items["poison"].length;i++) {
		var oBox = items["poison"][i];
		if ((iP.indexOf(oBox) == -1) && !oBox.z) {
			if (iX > oBox.x-4 && iX < oBox.x+4 && iY > oBox.y-4 && iY < oBox.y + 4) {
				handleHit(oBox);
				detruit(oBox,isHitSound(oBox));
				return (collisionTeam!=oBox.team);
			}
		}
	}
	return false;
}
function touche_champi(iX, iY) {
	for (var i=0;i<items["champi"].length;i++) {
		var oBox = items["champi"][i];
		if (iX > oBox.x-4 && iX < oBox.x+4 && iY > oBox.y-4 && iY < oBox.y + 4) {
			detruit(oBox);
			return true;
		}
	}
	return false;
}

function touche_fauxobjet(iX, iY, iP) {
	if (!iP) iP = [];
	for (var i=0;i<items["fauxobjet"].length;i++) {
		var oBox = items["fauxobjet"][i];
		if ((iP.indexOf(oBox) == -1) && !oBox.z) {
			if (iX > oBox.x-4 && iX < oBox.x+4 && iY > oBox.y-4 && iY < oBox.y + 4) {
				handleHit(oBox);
				detruit(oBox,isHitSound(oBox));
				return (collisionTeam!=oBox.team);
			}
		}
	}
	return false;
}

function touche_cverte(iX, iY, iP) {
	if (!iP) iP = [];
	for (var i=0;i<items["carapace"].length;i++) {
		var oBox = items["carapace"][i];
		if ((iP.indexOf(oBox) == -1) && !oBox.z) {
			if (iX > oBox.x-5 && iX < oBox.x+5 && iY > oBox.y-5 && iY < oBox.y + 5) {
				handleHit(oBox);
				detruit(oBox,isHitSound(oBox));
				return (collisionTeam!=oBox.team);
			}
		}
	}
	return false;
}

function touche_crouge(iX, iY, iP) {
	if (!iP) iP = [];
	for (var i=0;i<items["carapace-rouge"].length;i++) {
		var oBox = items["carapace-rouge"][i];
		if ((iP.indexOf(oBox) == -1) && !oBox.z) {
			var isHitbox = ((oBox.owner == -1) || (oBox.aipoint == -2));
			if (isHitbox ? (iX > oBox.x-5 && iX < oBox.x+5 && iY > oBox.y-5 && iY < oBox.y + 5) : (iX == oBox.x && iY == oBox.y)) {
				handleHit(oBox);
				detruit(oBox,isHitSound(oBox));
				return (collisionTeam!=oBox.team);
			}
		}
	}
	return false;
}
function touche_bobomb(iX, iY, iP) {
	if (!iP) iP = [];
	for (var i=0;i<items["bobomb"].length;i++) {
		var oBox = items["bobomb"][i];
		if (!oBox.z && (iP.indexOf(oBox) == -1)) {
			if (oBox.theta != -1) {
				var hitboxW = 18;
				if (oBox.cooldown >= 38)
					hitboxW = 0;
				else if (oBox.cooldown >= 30)
					hitboxW = 5;
				if (!oBox.countdown && ((oBox.x-iX)*(oBox.x-iX) + (oBox.y-iY)*(oBox.y-iY)) < (hitboxW*hitboxW)) {
					if (oBox.cooldown <= 0) {
						var res = (collisionTeam!=oBox.team) ? (oBox.cooldown < -5 ? 42 : 84):false;
						if (res) handleHit(oBox);
						return res;
					}
					else {
						oBox.cooldown = 1;
						if (isOnline)
							syncItems.push(oBox);
					}
				}
			}
			else {
				if (iX > oBox.x-5 && iX < oBox.x+5 && iY > oBox.y-5 && iY < oBox.y + 5) {
					for (j=0;j<aKarts.length;j++) {
						var k = aKarts[j].using.indexOf(oBox);
						if (k != -1) {
							throwItem(aKarts[j], {theta:1,countdown:0,cooldown:1},k);
							break;
						}
					}
				}
			}
		}
	}
	return false;
}

function touche_cbleue(iX, iY) {
	for (var i=0;i<items["carapace-bleue"].length;i++) {
		var oBox = items["carapace-bleue"][i];
		if (oBox.cooldown <= 0 && oBox.cooldown >= -10) {
			var hitboxW = 24;
			if (!oBox.countdown && ((oBox.x-iX)*(oBox.x-iX) + (oBox.y-iY)*(oBox.y-iY) < (hitboxW*hitboxW))) {
				var res = (collisionTeam!=oBox.team) ? (oBox.cooldown < -5 ? 42 : 84):false;
				if (res) handleHit(oBox);
				return res;
			}
		}
	}
	return false;
}

function touche_asset(aPosX,aPosY, iX,iY) {
	var turningAssets = ["pointers", "flippers"];
	for (var i=0;i<turningAssets.length;i++) {
		var key = turningAssets[i];
		if (oMap[key]) {
			var tau = 2*Math.PI;
			for (var i=0;i<oMap[key].length;i++) {
				var asset = oMap[key][i];
				var cX = asset[1][0], cY = asset[1][1], cR = asset[1][2]*(1-asset[2][0]);
				var r2 = (aPosX-cX)*(aPosX-cX) + (aPosY-cY)*(aPosY-cY);
				if (r2 < (cR*cR)) {
					var theta0 = asset[2][2], theta1 = Math.atan2(aPosY-cY,aPosX-cX);
					var omega = asset[2][3];
					if (((iX-cX)*(iX-cX) + (iY-cY)*(iY-cY)) < (cR*cR)) {
						var theta2 = Math.atan2(iY-cY,iX-cX);
						theta2 -= tau*Math.round((theta2-theta1)/tau);
						omega -= (theta2-theta1);
					}
					var r = Math.sqrt(r2), cL = r/cR, cH = asset[1][3]*(asset[1][4]*(1-cL) + asset[1][5]*cL);
					var cA = cH/r;
					var collides;
					if (omega > 0) {
						theta1 -= tau*Math.floor((theta1-theta0)/tau);
						collides = (theta1 < theta0+omega+cA);
					}
					else {
						theta1 -= tau*Math.ceil((theta1-theta0)/tau);
						collides = (theta1 > theta0+omega-cA);
					}
					if (collides)
						return [key,asset];
				}
			}
		}
	}

	{
		var key = "bumpers";
		if (oMap[key]) {
			for (var i=0;i<oMap[key].length;i++) {
				var asset = oMap[key][i];
				var cX = asset[1][0], cY = asset[1][1], cR = asset[1][2]/2;
				if ((iX-cX)*(iX-cX) + (iY-cY)*(iY-cY) < (cR*cR))
					return [key,asset];
			}
		}
	}

	{
		var key = "oils";
		if (oMap[key]) {
			for (var i=0;i<oMap[key].length;i++) {
				var asset = oMap[key][i];
				var cX = asset[1][0], cY = asset[1][1], cR = 4;
				if ((Math.abs(iX-cX) < cR) && (Math.abs(iY-cY) < cR))
					return [key,asset];
			}
		}
	}


	{
		var key = "flowers";
		if (oMap[key]) {
			for (var i=0;i<oMap[key].length;i++) {
				var asset = oMap[key][i];
				var flower = asset[1];
				var x = flower[0], y = flower[1], w = Math.round(flower[2]/2), h = Math.round(flower[3]/2);
				var oRect = [x-w,y-w,2*w,2*h];
				if (pointInRectangle(iX,iY, oRect))
					return [key,asset];
			}
		}
	}
	return false;
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
		var score = oKart.tours*oMap.checkpoint.length + getCpScore(oKart) - Math.abs(oKart[(iLine ? "y" : "x")]-oMap.checkpoint[dest][iLine]) / 10000;
		for (var i=0;i<aKarts.length;i++) {
			var kart = aKarts[i];
			dest = kart.demitours+1;
			if (dest >= oMap.checkpoint.length) dest = 0;
			iLine = oMap.checkpoint[dest][3];
			if (kart != oKart && kart.tours*oMap.checkpoint.length + getCpScore(kart) - Math.abs(kart[(iLine ? "y" : "x")]-oMap.checkpoint[dest][iLine]) / 10000 > score)
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
		document.getElementById("infoPlace"+j).innerHTML = place;
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
	var cPlace = Infinity;
	var oKart;
	for (var k=0;k<aKarts.length;k++) {
		if (aKarts[k].place < cPlace) {
			oKart = aKarts[k];
			cPlace = oKart.place;
		}
	}
	return distanceToKart(kart,oKart);
}
function distanceToSecond(kart) {
	var cPlace = Infinity;
	var oKart;
	for (var k=0;k<aKarts.length;k++) {
		if ((aKarts[k] != kart) && (aKarts[k].place < cPlace)) {
			oKart = aKarts[k];
			cPlace = oKart.place;
		}
	}
	return distanceToKart(oKart,kart);
}
function distanceToKart(kart,oKart) {
	var res = 0;
	var posX = kart.x, posY = kart.y;
	var tours = kart.tours;
	var checkpoint = kart.demitours;
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
function checkpoint(kart, fMoveX,fMoveY) {
	var aPos = [kart.x-fMoveX,kart.y-fMoveY], aMove = [fMoveX,fMoveY];
	var dir = [(fMoveX>0), (fMoveY>0)];
	var fast = (fMoveX*fMoveX + fMoveY*fMoveY > 200);
	var demitour = kart.demitours;
	if (!simplified) {
		var iCP = getNextCp(kart);
		var jCP = (iCP?iCP:oMap.checkpoint.length)-1;
	}
	for (var i=0;i<oMap.checkpoint.length;i++) {
		var oBox = oMap.checkpoint[i];
		var oRect = [oBox[0],oBox[1],15,15];
		oRect[3-oBox[3]] = oBox[2];
		var inRect = pointInRectangle(kart.x,kart.y, oRect);
		if (!inRect && fast) {
			var j = oBox[3];
			var l = dir[j];
			if ((l ? ((aPos[j] <= oBox[j])&&((aPos[j]+aMove[j]) >= oBox[j])):((aPos[j] >= (oBox[j]+oBox[j+2]))&&((aPos[j]+aMove[j]) <= (oBox[j]+oBox[j+2]))))) {
				var dim = 1-j;
				var croiseJ = aPos[dim] + ((l?oBox[j]:oBox[j]+oBox[j+2])-aPos[j])*aMove[dim]/aMove[j];
				if ((croiseJ >= oBox[dim]) && (croiseJ <= (oBox[dim]+oBox[dim+2])))
					inRect = true;
			}
		}
		if (inRect) {
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

function int8ToHexString(arr) {
	return [].slice.call(arr).map(function(x){return x.toString(16).padStart(2,"0")}).join("");
}
function hexStringToInt8(hex) {
	return new Uint8Array(hex.match(/../g).map(function(x) {return parseInt(x,16)})).buffer;
}
function itemDataToHex(type, data) {
	switch (type) {
	case "double":
		return int8ToHexString(new Uint8Array(new Float64Array([data]).buffer,0,8));
	case "float":
		return int8ToHexString(new Uint8Array(new Float32Array([data]).buffer,0,4));
	case "int":
		return int8ToHexString(new Uint8Array(new Int32Array([data]).buffer,0,4));
	case "short":
		return int8ToHexString(new Uint8Array(new Int16Array([data]).buffer,0,2));
	case "byte":
		return int8ToHexString(new Uint8Array(new Int8Array([data]).buffer,0,1));
	}
}
function hexToItemData(type, hex) {
	switch (type) {
	case "double":
		return new Float64Array(hexStringToInt8(hex))[0];
	case "float":
		return new Float32Array(hexStringToInt8(hex))[0];
	case "int":
		return new Int32Array(hexStringToInt8(hex))[0];
	case "short":
		return new Int16Array(hexStringToInt8(hex))[0];
	case "byte":
		return new Int8Array(hexStringToInt8(hex))[0];
	}
}
function itemDataLength(type) {
	switch (type) {
	case "double":
		return 16;
	case "float":
		return 8;
	case "int":
		return 8;
	case "short":
		return 4;
	case "byte":
		return 2;
	}
}

function resetDatas() {
	var oPlayer = oPlayers[0];
	var params = (course != "BB")
	 ? ["x","y","z","speed","speedinc","heightinc","rotation","rotincdir","rotinc","size","tourne","tombe","arme","tours","demitours","champi","etoile","megachampi","billball","place"]
	 : ["x","y","z","speed","speedinc","heightinc","rotation","rotincdir","rotinc","size","tourne","tombe","arme","ballons","reserve","champi","etoile","megachampi"];
	var payload = {
		player: [],
		item: [],
		lastcon: connecte
	};
	payload.player.length = params.length;
	for (var i=0;i<params.length;i++) {
		var param = params[i];
		var value;
		switch (param) {
		case "demitours":
			if (course != "BB")
				value = getCpScore(oPlayer);
			break;
		case "ballons":
			if (course == "BB")
				value = oPlayer.ballons.length;
			break;
		default:
			value = oPlayer[params[i]];
		}
		payload.player[i] = value;
	}
	var aSyncItems = Array.from(new Set(syncItems));
	var nSyncItems = [];
	for (var i=0;i<aSyncItems.length;i++) {
		var syncItem = aSyncItems[i];
		var itemData = "";
		if (syncItem.deleted) {
			if (!syncItem.id) continue;
		}
		else {
			var itemBehavior = itemBehaviors[syncItem.type];
			for (var j=0;j<itemBehavior.sync.length;j++) {
				var syncParams = itemBehavior.sync[j];
				itemData += itemDataToHex(syncParams.type,syncItem[syncParams.key]);
			}
		}
		var itemPayload = {
			data: itemData
		};
		if (oPlayer.using.indexOf(syncItem) !== -1)
			itemPayload.holder = 1;
		if (syncItem.id)
			itemPayload.id = syncItem.id;
		else {
			itemPayload.type = itemTypes.indexOf(syncItem.type);
			nSyncItems.push(syncItem);
		}
		payload.item.push(itemPayload);
	}
	syncItems.length = 0;
	if (course == "BB")
		payload.battle = 1;
	else {
		if (oMap.tours != 3)
			payload.laps = oMap.tours;
	}
	fetch('reload.php', {
		method: 'post',
		body: JSON.stringify(payload)
	}).then(function(response) {
		return response.json();
	}).then(function(rCode) {
		if (rCode != -1) {
			refreshDatas = true;
			var oCodes = rCode[1];
			var newItems = oCodes[0], updatedItems = oCodes[1];
			for (var i=0;i<newItems.length;i++) {
				var newItem = newItems[i];
				if (nSyncItems[i])
					nSyncItems[i].id = newItem;
			}
			var syncedItems = [];
			for (var i=0;i<updatedItems.length;i++) {
				var updatedItem = updatedItems[i];
				var uId = updatedItem[0];
				var uType = itemTypes[updatedItem[1]];
				var uHolder = updatedItem[2];
				if (!uType) continue;
				var uConn = updatedItem[3];
				var uData = updatedItem[4];
				var uItem = items[uType].find(function(item) {
					return (item.id == uId);
				});
				var toAdd = false;
				if (!uItem) {
					if (uData) {
						uItem = {
							id: uId,
							type: uType
						};
						toAdd = true;
					}
				}
				else {
					if (!uData) {
						supprime(uItem, false);
						uHolder = 0;
					}
				}
				if (uData) {
					var cur = 0;
					var itemBehavior = itemBehaviors[uType];
					for (var j=0;j<itemBehavior.sync.length;j++) {
						var syncParams = itemBehavior.sync[j];
						var dl = itemDataLength(syncParams.type);
						var dc = uData.substr(cur,dl);
						if (dc.length == dl) {
							uItem[syncParams.key] = hexToItemData(syncParams.type, dc);
						}
						cur += dl;
					}
					if (toAdd)
						addNewItem(null,uItem);
				}
				for (var j=0;j<aKarts.length;j++) {
					var oKart = aKarts[j];
					var oItemId = oKart.using.indexOf(uItem);
					if (oKart.id == uHolder) {
						if (oItemId == -1) {
							oKart.using.push(uItem);
							if ((oKart.using.length > 1) && !oKart.rotitem)
								oKart.rotitem = 0;
						}
					}
					else {
						if (oItemId != -1)
							oKart.using.splice(oItemId,1);
					}
				}
				if (uData)
					syncedItems.push({item:uItem,start:uConn,end:rCode[2]});
			}
			for (var i=0;i<syncedItems.length;i++) {
				var syncedItem = syncedItems[i];
				var uItem = syncedItem.item;
				var moveFn = itemBehaviors[uType].move;
				if (moveFn && (itemBehaviors[uType].onlineResync !== false)) {
					for (var k=syncedItem.start;k<syncedItem.end;k++) {
						if (uItem.deleted)
							break;
						moveFn(uItem);
					}
				}
			}
			var jCodes = rCode[0];
			for (var i=0;i<jCodes.length;i++) {
				var jCode = jCodes[i];
				var pID = jCode[0][0];
				for (var j=0;j<aKarts.length;j++) {
					var oKart = aKarts[j];
					if (oKart.id == pID) {
						var pCode = jCode[1];
						var aEtoile = oKart.etoile, aBillBall = oKart.billball, aTombe = oKart.tombe;
						for (var k=0;k<params.length;k++) {
							var param = params[k];
							var value = pCode[k];
							switch (param) {
							case "demitours":
								oKart.demitours = (getLastCp(oKart)+value)%oMap.checkpoint.length;
								break;
							case "ballons":
								if (course == "BB") {
									while (oKart.ballons.length < value) {
										if (!oKart.ballons.length) {
											oKart.sprite[0].div.style.opacity = 1;
											oKart.sprite[0].img.style.display = "";
											oPlanCharacters[j].style.display = "block";
											oPlanCharacters2[j].style.display = "block";
											oKart.loose = false;
										}
										addNewBalloon(oKart);
									}
									while (oKart.ballons.length > value) {
										var lg = oKart.ballons.length-1;
										oKart.ballons[lg][0].suppr();
										oKart.ballons.pop();
									}
								}
								break;
							default:
								oKart[params[k]] = value;
							}
						}
						if ((oKart.billball >= 25) && !aBillBall) {
							oKart.sprite[0].img.src = "images/sprites/sprite_billball.png";
							resetSpriteHeight(oKart.sprite[0]);
							oKart.aipoint = undefined;
						}
						else if ((oKart.etoile >= 50) && !aEtoile)
							oKart.sprite[0].img.src = getStarSrc(oKart.personnage);
						else if ((aEtoile && !oKart.etoile) || (aBillBall && !oKart.billball)) {
							oKart.sprite[0].img.src = getSpriteSrc(oKart.personnage);
							resumeSpriteSize(oKart.sprite[0]);
						}
						updateProtectFlag(oKart);
						if (aTombe && !oKart.tombe) {
							oKart.sprite[0].img.style.display = "block";
							if (course == "BB") {
								for (var k=0;k<oKart.ballons.length;k++)
									oKart.ballons[k][0].img.style.display = "block";
							}
						}
						if (!aTombe && oKart.tombe) {
							oKart.sprite[0].img.style.display = "none";
							oKart.frminv = 10;
							if (oKart.tombe > 2) {
								if (course == "BB") {
									for (var k=0;k<oKart.ballons.length;k++)
										oKart.ballons[k][0].img.style.display = "none";
								}
								if (oKart.marker)
									oKart.marker.div[0].style.display = "none";
							}
						}
						if (!oKart.turnSound && oKart.tourne) {
							oKart.turnSound = playDistSound(oKart,"musics/events/spin.mp3",(course=="BB")?80:50);
							if (!oKart.frminv) oKart.frminv = 10;
						}
						if (oKart.turnSound && !oKart.tourne)
							oKart.turnSound = undefined;
						for (var k=jCode[0][1];k<rCode[2];k++)
							move(j, true);
						break;
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
					infos0.style.color = primaryColor;
					var oTrs = new Array();
					var oTds = new Array();
					for (i=0;i<rCode[3].length;i++) {
						var pCode = rCode[3][i];
						var oTr = document.createElement("tr");
						oTds[i] = new Array();
						if (pCode[0] == identifiant) {
							oTr.style.backgroundColor = rankingColor(oPlayers[0].team);
							document.getElementById("infoPlace0").innerHTML = i+1;
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
						infos0.style.display = "none";
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

						var oTeamTable;
						if (iTeamPlay && shareLink.options && shareLink.options.localScore) {
							var teamsRecap = [0,0];
							for (var i=0;i<rCode[3].length;i++)
								teamsRecap[rCode[3][i][4]] += rCode[3][i][2];
							oTeamTable = createTeamTable(teamsRecap);
						}

						var forceClic2 = true;
						setTimeout(function() {
							infos0.style.display = "";
							if (oTeamTable)
								oTeamTable.style.visibility = "visible";
							if(!isChatting())
								oContinue.focus()
						}, 500);

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
					infos0.style.display = "";
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
		else {
			iDeco();
			interruptGame();
		}
	}).catch(function(e) {
		console.error(e);
		if (!refreshDatas) {
			refreshDatas = true;
			for (var i=aSyncItems.length-1;i>=0;i--)
				syncItems.unshift(aSyncItems[i]);
		}
	});
	refreshDatas = false;
}

function resetFall(oKart) {
	delete oKart.aX;
	delete oKart.aY;
	delete oKart.aRotation;
	for (var i=0;i<oPlayers.length;i++) {
		if (oKart == oPlayers[i])
			oContainers[i].style.opacity = 1;
		oKart.sprite[i].img.style.display = "block";
	}
}

function loseBall(i) {
	if (course == "BB") {
		var lg = aKarts[i].ballons.length-1;
		if (!aKarts[i].tourne && aKarts[i].ballons[lg]) {
			aKarts[i].ballons[lg][0].suppr();
			aKarts[i].ballons.pop();
			if (!aKarts[i].cpu) {
				clLocalVars.lostBalloons++;
				updateChallengeHud("balloons", clLocalVars.lostBalloons);
			}
			if (isOnline && !i && !aKarts[i].ballons.length) {
				supprArme(i);
				document.getElementById("infoPlace0").style.visibility = "hidden";
			}
		}
	}
}

function showTimer(timeMS) {
	var tps = timeStr(timeMS);
	for (var i=0;i<strPlayer.length;i++)
		document.getElementById("temps"+i).innerHTML = tps;
}

function move(getId, triggered) {
	var oKart = aKarts[getId];
	collisionTest = COL_KART;
	collisionPlayer = oKart;
	collisionTeam = (oKart.team==-1) ? undefined:oKart.team;
	clLocalVars.currentKart = oKart;
	var oKart = aKarts[getId];
	if ((getId<strPlayer.length)) {
		if (!oKart.cpu && !finishing) {
			showTimer(timer*SPF);
			if (!getId)
				timer++;

			if (oKart.time) {
				oKart.time--;
				
				if (oKart.time && !oPlayers[getId].changeView)
					document.getElementById("lakitu"+getId).style.display = "block";
				else
					document.getElementById("lakitu"+getId).style.display = "none";

				if (oLapTimeDiv) {
					if (oKart.time < 25) {
						if (oKart.time < 5) {
							oContainers[0].removeChild(oLapTimeDiv);
							oLapTimeDiv = undefined;
						}
						else {
							oLapTimeDiv.style.visibility = ((oKart.time%6)<4) ? "visible":"hidden";
						}
					}
				}
			}
		}
		else if (oKart.tours == (oMap.tours+1)) {
			if (!oKart.changeView)
				oKart.changeView = 0;
			if (oKart.changeView < 180)
				oKart.changeView += 15;
			oKart.progressiveView = true;
		}
	}

	if (oKart.tombe) {
		oKart.tombe--;
		updateDriftSize(getId);
		oKart.size = 1;
		oKart.mini = 0;
		if (oKart.tombe == 19) {
			for (var i=0;i<strPlayer.length;i++) {
				oKart.sprite[i].img.style.display = "none";
				oKart.sprite[i].img.style.opacity = "";
				oKart.sprite[i].div.style.backgroundImage = "";
				if (course == "BB") {
					for (var j=0;j<oKart.ballons.length;j++)
						oKart.ballons[j][i].img.style.display = "none";
				}
				if (oKart.marker)
					oKart.marker.div[i].style.display = "none";
			}
			playIfShould(oKart, "musics/events/rescue.mp3");
		}
		else if (oKart.tombe == 2) {
			if (course == "BB") {
				for (var i=0;i<strPlayer.length;i++) {
					for (var j=0;j<oKart.ballons.length;j++)
						oKart.ballons[j][i].img.style.display = "block";
				}
			}
		}
		else if (!oKart.tombe) {
			resetFall(oKart);
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
		}
		return;
	}

	if (oKart.rotincdir) {
		oKart.rotinc += 2 * oKart.rotincdir;
		if (oKart.driftinc && ((oKart.driftinc>0)!=(oKart.rotincdir>0))) {
			var maxValue = Math.max(1.1,1.6-Math.max(0,(oKart.driftcpt-20)/80));
			if (oKart.driftinc > 0)
				oKart.rotinc = Math.max(oKart.rotinc,-maxValue);
			else
				oKart.rotinc = Math.min(oKart.rotinc,maxValue);
		}
	}
	else {
		if (oKart.rotinc < 0)
			oKart.rotinc = Math.min(0, oKart.rotinc + 1);
		else if (oKart.rotinc > 0)
			oKart.rotinc = Math.max(0, oKart.rotinc - 1);
	}
	handleDriftCpt(getId);

	if (oKart.cpu) {
		oKart.rotinc = Math.min(oKart.rotinc, fMaxRotInCp);
		oKart.rotinc = Math.max(oKart.rotinc, -fMaxRotInCp);
	}
	else {
		oKart.rotinc = Math.min(oKart.rotinc, fMaxRotInc);
		oKart.rotinc = Math.max(oKart.rotinc, -fMaxRotInc);
	}

	if (oKart.shift)
		oKart.rotation += oKart.shift[2]*180/Math.PI;
	if (oKart.tourne) {
		oKart.figuring = false;
		oKart.figstate = 0;
		if (!oKart.z)
			oKart.speed = (oKart.speed-Math.max(0,oKart.speedinc+0.1))/1.5;
		oKart.tourne -= 2;
		if (!oKart.tourne) {
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
		}
	}
	else {
		if (oKart.figstate) {
			oKart.figstate -= 1 + Math.round((11-Math.abs(11-oKart.figstate))*0.5);
			if (oKart.figstate < 0)
				oKart.figstate = 0;
			if (!oKart.figuring && oKart.figstate < 8) {
				oKart.figuring = true;
				if (oKart == oPlayers[0]) {
					clLocalVars.stunts++;
					var ruleVars;
					if (clSelected && clRuleVars[clSelected.id] && (ruleVars = clRuleVars[clSelected.id].stunts))
						updateChallengeHud("stunts", ruleVars.stunts+clLocalVars.stunts);
				}
			}
		}
		else if (oKart.speed && !oKart.billball && !oKart.cannon)
			oKart.rotation += (oKart.rotinc+(oKart.driftinc||0)*1.5)*((((oKart.speedinc<0)||(oKart.speedinc==0&&oKart.speed<0))?-1:1))*Math.abs(Math.cos(angleDrift(oKart)*Math.PI/180));
		if (oKart.frminv)
			oKart.frminv--;
	}
	if (oKart.rotation < 0)
		oKart.rotation += 360;
	if (oKart.rotation > 360)
		oKart.rotation -= 360;

	if (kartIsPlayer(oKart)) {
		if (!clLocalVars.startedAt && oKart.speed > 1)
			clLocalVars.startedAt = timer;
		if (!clLocalVars.forwards) {
			if (oKart.speed > 0 && (oKart.speedinc > 0 || oKart.turbodrift))
				clLocalVars.forwards = true;
		}
	}

	var fMaxKartSpeed = oKart.maxspeed * oKart.size;

	if (oKart.speed > fMaxKartSpeed)
		oKart.speed = fMaxKartSpeed;
	if (oKart.speed < -fMaxKartSpeed/4)
		oKart.speed = -fMaxKartSpeed/4;

	var fMoveDir = kartInstantSpeed(oKart);
	var fMoveX = fMoveDir[0];
	var fMoveY = fMoveDir[1];

	var fNewPosX = oKart.x + fMoveX;
	var fNewPosY = oKart.y + fMoveY;
	
	var aPosX = oKart.x, aPosY = oKart.y;

	if (!oKart.z && !oKart.heightinc) {
		oKart.speed += oKart.speedinc;
		if ((isCup && oMap.skin != 22 && oMap.skin != 30) || (!isCup && oMap.smartjump)) {
			var hpType, hpProps;
			if (oKart.cpu && ((tombe(fNewPosX, fNewPosY) && !sauts(aPosX, aPosY, fMoveX, fMoveY)) || ((hpType=ralenti(fNewPosX, fNewPosY)) && (hpProps=getOffroadProps(oKart,hpType)) && ((oKart.speed-oKart.speedinc*1.01) > hpProps.speed) && !oKart.protect && !oKart.champi))) {
			//if (oKart.cpu && ((tombe(fNewPosX, fNewPosY) && !sauts(aPosX, aPosY, fMoveX, fMoveY)))) {
				oKart.z = 1;
				oKart.heightinc = 0.5;
				oKart.jumped = true;
			}
		}
	}
	else {
		oKart.z += 0.7 * oKart.heightinc * Math.abs(oKart.heightinc);
		oKart.heightinc -= 0.5;
		if (oKart.z <= 0) {
			oKart.heightinc = 0;
			oKart.z = 0;
			delete oKart.jumped;
			if (kartIsPlayer(oKart)) {
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
			document.getElementById("drift"+ getId).style.top = Math.round(iScreenScale*(32-correctZ(oKart.z)) + (oKart.sprite[getId].h-32)*fSpriteScale*0.15) + "px";
	}
	
	if ((!getId || !isOnline || finishing) && !oKart.loose) {
		var oKartItems = oKart.using;
		if (oKart.tourne) {
			oKartItems = [];
			for (var i=0;i<aKarts.length;i++)
				oKartItems = oKartItems.concat(aKarts[i].using);
		}
		var pExplose = touche_bobomb(fNewPosX, fNewPosY, oKartItems) + touche_cbleue(fNewPosX, fNewPosY);
		if (pExplose && !oKart.tourne && !oKart.protect && !oKart.fell) {
			loseBall(getId);
			oKart.spin(pExplose);
			loseUsingItems(oKart);
			stopDrifting(getId);
			if (pExplose >= 84) {
				oKart.champi = 0;
				oKart.speed = 0;
				oKart.heightinc = 3;
				supprArme(aKarts.indexOf(oKart));
			}
		}
		else if (oKart.z < 5) {
			if ((touche_fauxobjet(fNewPosX, fNewPosY, oKartItems) || (touche_cverte(fNewPosX, fNewPosY, oKartItems) || touche_cverte(oKart.x, oKart.y, oKartItems)) || touche_crouge(oKart.x, oKart.y, oKartItems)) && !oKart.protect && !oKart.frminv) {
				loseBall(getId);
				stopDrifting(getId);
				oKart.spin(42);
				loseUsingItem(oKart);
			}
			else if (touche_banane(fNewPosX, fNewPosY, oKartItems) && !oKart.protect && !oKart.frminv) {
				loseBall(getId);
				stopDrifting(getId);
				oKart.spin(20);
				loseUsingItem(oKart);
			}
			else if (touche_poison(fNewPosX, fNewPosY, oKartItems) && !oKart.protect && !oKart.frminv) {
				loseBall(getId);
				stopDrifting(getId);
				oKart.spin(20);
				loseUsingItems(oKart);
				oKart.size = 0.6;
				oKart.mini = Math.max(oKart.mini, 60);
				updateDriftSize(getId);
			}
			else if (touche_champi(fNewPosX, fNewPosY) && !oKart.tourne) {
				oKart.champi = 20;
				oKart.maxspeed = 11;
				oKart.speed = 11;
				playIfShould(oKart,"musics/events/boost.mp3");
			}
			else if (!oKart.tourne && (oKart.z < 1.2)) {
				var hittable = !oKart.protect && !oKart.frminv;
				var asset = touche_asset(aPosX,aPosY,fNewPosX,fNewPosY);
				var stopped = true;
				var decorHit = false;
				if (asset) {
					var decorType = asset[1][0].src;
					switch (asset[0]) {
					case "oils":
						if (hittable && (Math.abs(oKart.speed)>0.5) && !oKart.tourne) {
							stopDrifting(getId);
							loseBall(getId);
							oKart.spin(20);
						}
						stopped = false;
						break;
					case "pointers":
						decorType = 'assets/pivothand';
						if (hittable) {
							stopDrifting(getId);
							loseBall(getId);
							oKart.spin(42);
							if (oKart.cpu)
								oKart.frminv = 16;
						}
						else
							stopped = false;
						break;
					case "flippers":
						if (hittable) {
							stopDrifting(getId);
							loseBall(getId);
							oKart.spin(42);
						}
						oKart.speed *= -1;
						var pushSpeed = 8;
						switch (asset[1][3][0]) {
						case 1:
							pushSpeed = 12;
							break;
						case 2:
							pushSpeed = 4;
							break;
						}
						oKart.shift = [-pushSpeed*direction(0,oKart.rotation),-pushSpeed*direction(1,oKart.rotation),0];
						break;
					case "bumpers":
						hittable = false;
						oKart.speed *= -1;
						var pushSpeed = 8;
						var ux = fMoveX, uy = fMoveY;
						var bumper = asset[1];
						if (bumper[3]) {
							ux += bumper[3][0]*Math.sin(bumper[3][1])*bumper[2][5];
							uy -= bumper[3][0]*Math.cos(bumper[3][1])*bumper[2][5];
						}
						var nPosX = aPosX+ux, nPosY = aPosY+uy;
						var cx = bumper[1][0], cy = bumper[1][1];
						var rx = (aPosX-cx), ry = (aPosY-cy);
						var rr = Math.hypot(rx,ry);
						var nx = rx/rr, ny = ry/rr;
						var un = ux*nx + uy*ny;
						var ax = nPosX-un*nx, ay = nPosY-un*ny;
						ux = aPosX+2*(ax-aPosX)-nPosX;
						uy = aPosY+2*(ay-aPosY)-nPosY;
						var uu = Math.hypot(ux,uy);
						if (uu)
							oKart.shift = [pushSpeed*ux/uu,pushSpeed*uy/uu,0];
						else
							oKart.shift = [-pushSpeed*direction(0,rotation),-pushSpeed*direction(1,rotation),0];
						if (oKart.cpu) {
							oKart.bounced = true;
							oKart.bouncedsince = 0;
						}
						break;
					case "flowers":
						stopped = false;
						hittable = false;
						decorHit = true;
						break;
					}
					if (stopped) {
						decorHit = true;
						stopDrifting(getId);
						fNewPosX = aPosX;
						fNewPosY = aPosY;
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
					if (hittable) {
						decorHit = true;
						loseUsingItem(oKart);
					}
					if (decorHit) {
						if (clLocalVars.decorsHit && !oKart.cpu) {
							clLocalVars.decorsHit[decorType] = true;
						}
					}
				}
			}
			if (!oKart.cpu) {
				while (touche_piece(oKart.x,oKart.y)) {
					clLocalVars.nbCoins++;
					updateChallengeHud("coins", clLocalVars.nbCoins);
					challengeCheck("each_coin");
					playIfShould(oKart,"musics/events/coin.mp3");
				}
			}
		}
	}

	var rScroller, rHeight, rSize;
	if (objet(fNewPosX, fNewPosY)) {
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
				var forbiddenItems = {};
				if ((oKart.tours == 1) && (getCpScore(oKart) <= (getCpDiff(oKart)/2))) {
					forbiddenItems["carapacebleue"] = 1;
					forbiddenItems["eclair"] = 1;
					forbiddenItems["bloops"] = 1;
					forbiddenItems["carapaceX3"] = 1;
					forbiddenItems["carapacerougeX3"] = 1;
				}
				if (oKart.place == 1)
					forbiddenItems["carapacebleue"] = 1;
				
				var preventDuplicateItems = {
					carapacebleue: 1,
					eclair: 1,
					bloops: 1,
					bananeX3: 1
				};
				for (var i=0;i<aKarts.length;i++) {
					if (preventDuplicateItems[aKarts[i].arme])
						forbiddenItems[aKarts[i].arme] = 1;
				}
				if (items["carapace-bleue"].length)
					forbiddenItems["carapacebleue"] = 1;
				if ((oKart.place < aKarts.length) || items.eclair.length)
					forbiddenItems["eclair"] = 1;
				if (items.bloops.length)
					forbiddenItems["bloops"] = 1;
				if (forbiddenItems[iObj] && otherObjects(oKart, forbiddenItems)) {
					do {
						iObj = randObj(oKart);
					} while (forbiddenItems[iObj]);
				}
			}
			else {
				if (oKart.ballons.length)
					iObj = randObj(oKart);
				else {
					var ghostItems = ["fauxobjet", "banane", "carapace", "bobomb"];
					iObj = ghostItems[Math.floor(Math.random()*ghostItems.length)];
				}
			}
			/*if (oKart === oPlayers[0]) { // Uncomment to test all objs
				if (!window.aaa) {
					window.aaa = [];
					for (var i=0;i<itemDistribution.length;i++) {
						for (var key in itemDistribution[i]) {
							if (window.aaa.indexOf(key) == -1)
								window.aaa.push(key);
						}
					}
					Array.from(new Set(itemDistribution));
					window.bbb = 0;
				}
				iObj = window.aaa[window.bbb];
				window.bbb++;
				if (window.bbb >= window.aaa.length)
					window.bbb = 0;
			}*/
			oKart.arme = iObj;
			if (shouldPlaySound(oKart))
				oKart.rouletteSound = playSoundEffect("musics/events/roulette.mp3");
			if (kartIsPlayer(oKart)) {
				var rScroller = document.getElementById("scroller"+getId);
				var rTurner = rScroller.getElementsByTagName("div")[0];
				var rHeight = rTurner.offsetHeight;
				var rSize = iScreenScale*7;
				if (!rTurner.dataset)
					rTurner.dataset = {};
				rTurner.dataset.h = rHeight;
				rTurner.dataset.s = rSize;
				document.getElementById("scroller"+getId).getElementsByTagName("div")[0].style.top = -Math.floor(Math.random()*rHeight) +"px";
				document.getElementById("scroller"+getId).style.visibility="visible";
				clLocalVars.itemsGot = true;
			}
		}
		if (clLocalVars.itemsHit && !oKart.cpu) {
			if (!clLocalVars.itemsHit[touchedObject]) {
				clLocalVars.itemsHit[touchedObject] = true;
				clLocalVars.nbItems++;
				updateChallengeHud("items", clLocalVars.nbItems);
				challengeCheck("each_item");
			}
		}
	}
	if (oKart.arme && oKart.roulette != 25) {
		oKart.roulette++;
		if (oKart.roulette >= 25) {
			oKart.roulette = 25;
			if (kartIsPlayer(oKart)) {
				updateObjHud(getId);
				if (oKart.rouletteSound) {
					removeIfExists(oKart.rouletteSound);
					playSoundEffect("musics/events/gotitem.mp3");
					oKart.rouletteSound = undefined;
				}
			}
		}
	}

	collisionDecor = null;
	if (oKart.cannon || canMoveTo(aPosX,aPosY,oKart.z, fMoveX,fMoveY, oKart.protect)) {
		oKart.x = fNewPosX;
		oKart.y = fNewPosY;
		if (oKart.cpu)
			delete oKart.collided;
	}
	else {
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
		if (oKart.cpu) {
			oKart.collided = true;
			oKart.horizontality = horizontality;
		}
		var fDirX = fNewPosX-oKart.x, fDirY = fNewPosY-oKart.y;
		var s = (fDirX*horizontality[0] + fDirY*horizontality[1]);
		if (oKart.speed > oKart.speedinc)
			oKart.speed = Math.min(oKart.speed*0.75,oKart.speed+0.5-oKart.speedinc);
		if (!collisionDecor && (oKart.speed > 3) && (oKart.driftcpt >= 5) && (oKart.driftcpt < fTurboDriftCpt2)) {
			if ((oKart.driftcpt < fTurboDriftCpt) || (oKart.driftcpt >= (fTurboDriftCpt+5)))
				oKart.driftcpt -= 5;
		}
		for (var i=5;i>0;i--) {
			oKart.x += horizontality[0]*s*i/5;
			oKart.y += horizontality[1]*s*i/5;
			if (canMoveTo(aPosX,aPosY,oKart.z, oKart.x-aPosX,oKart.y-aPosY, oKart.protect))
				break;
			else {
				oKart.x = aPosX;
				oKart.y = aPosY;
			}
		}
		if (oKart.speedinc <= 0)
			oKart.speed = Math.hypot(oKart.x-aPosX,oKart.y-aPosY);
	}
	if (collisionDecor) {
		if (clLocalVars.decorsHit && !oKart.cpu)
			clLocalVars.decorsHit[collisionDecor] = true;
		var collisionSpin = decorBehaviors[collisionDecor].spin;
		if (collisionSpin && !oKart.tourne && !oKart.protect && !oKart.frminv) {
			var minSpeed = decorBehaviors[collisionDecor].minSpeedToSpin||2.5;
			if (Math.abs(oKart.speed) > minSpeed) {
				loseBall(getId);
				stopDrifting(getId);
				oKart.spin(collisionSpin);
				oKart.frminv = 24;
				oKart.speed = 2.5*Math.sign(oKart.speed);
				loseUsingItems(oKart);
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
		var newShift;
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
			if ((!isOnline || !getId) && (page!="MK" || course!="BB" || !oKart.cpu || (oMap.map>=65)))
				fTombe = tombe(oKart.x, oKart.y, oMap.checkpoint&&oKart.demitours ? oMap.checkpoint[(oKart.demitours+1!=oMap.checkpoint.length) ? oKart.demitours+1 : 0][3] : 0);
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
				oKart.aX = oKart.x;
				oKart.aY = oKart.y;
				oKart.aRotation = oKart.rotation;
				oKart.x = fTombe[0];
				oKart.y = fTombe[1];
				oKart.rotation = fTombe[2]*90;
				oKart.speed = 0;
				oKart.protect = false;
				oKart.figuring = false;
				oKart.figstate = 0;
				oKart.fell = true;
				oKart.champi = 0;
				if (oKart.cpu)
					oKart.aipoint = undefined;
				oKart.tombe = 20;
				oKart.frminv = 10;
				oKart.ctrled = true;
				oKart.z = 10;
				oKart.tourne = 0;
				stopDrifting(getId);
				supprArme(getId);
				deleteUsingItems(oKart);
				for (var i=0;i<oPlayers.length;i++) {
					if ((i != getId) || (1/*nbFrames*/ == 1)) {
						oKart.sprite[i].img.style.display = "none";
						oKart.sprite[i].div.style.backgroundImage = "";
					}
					if (oKart.etoile)
						oKart.sprite[i].img.src = getSpriteSrc(oKart.personnage);
				}
				resetPowerup(oKart);
				if (!oKart.cpu) {
					clLocalVars.falls++;
					var ruleVars;
					if (clSelected && clRuleVars[clSelected.id] && (ruleVars = clRuleVars[clSelected.id].falls))
						updateChallengeHud("falls", clLocalVars.falls+ruleVars.falls);
				}
				playIfShould(oKart, "musics/events/fall.mp3");
			}
			else {
				if (!oKart.protect && !oKart.champi && !oKart.figuring && oKart.speed > 1.5 && !(oKart.turbodrift>oKart.turbodrift0*0.8) && (hpType=ralenti(fNewPosX, fNewPosY))) {
					var hpProps = getOffroadProps(oKart,hpType);
					if (hpProps.sliding)
						oKart.sliding = hpProps.sliding;
					if (oKart.speed > hpProps.speed)
						oKart.speed = hpProps.speed;
					stopDrifting(getId);
				}
				if (!oKart.tourne)
					newShift = flowShift(fNewPosX, fNewPosY, oKart.protect);
				if (newShift) {
					if (oKart.cpu) {
						if (newShift[0]*newShift[0] + newShift[1]*newShift[1] >= 100) {
							var fNewMoveX = fMoveX+newShift[0], fNewMoveY = fMoveY+newShift[1];
							if (fNewMoveX*fMoveX + fNewMoveY*fMoveY < 0) {
								oKart.collided = true;
								oKart.horizontality = [newShift[1],-newShift[0]];
							}
						}
					}
				}
			}
			oKart.figuring = false;
			oKart.figstate = 0;
		}
		if (newShift) {
			if (!oKart.shift)
				oKart.shift = [0,0,0];
			for (var i=0;i<3;i++)
				oKart.shift[i] = oKart.shift[i]*0.7+newShift[i]*0.3;
			if ((oKart.shift[0]*oKart.shift[0] + oKart.shift[1]*oKart.shift[1] + 300*oKart.shift[2]*oKart.shift[2]) < 0.01)
				delete oKart.shift;
		}
		else
			delete oKart.shift;
	}
	if (!oKart.cannon) {
		var cannon = inCannon(oKart.x,oKart.y);
		if (cannon && (cannon[0]||cannon[1])) {
			stopDrifting(getId);
			oKart.cannon = [oKart.x+cannon[0],oKart.y+cannon[1],oKart.x,oKart.y];
			oKart.protect = true;
			oKart.jumped = true;
			if (oKart.tourne)
				oKart.tourne = 2;
			delete oKart.shift;
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
	}

	if (oKart.using.length) {
		var rotItem = 0;
		var l = 5;
		var dtheta = 360/oKart.using.length;
		var isBanana = (oKart.using[0].type === "banane");
		if (oKart.rotitem !== undefined) {
			rotItem = oKart.rotitem;
			if (isBanana)
				l = 4;
			else
				l = 4.5;
			if (!triggered)
				oKart.rotitem -= 30;
		}
		for (var i=0;i<oKart.using.length;i++) {
			var oArme = oKart.using[i];
			if (isBanana) {
				oArme.x = (oKart.x - l * (0.7+(oKart.using.length-i)*0.35) * direction(0, oKart.rotation));
				oArme.y = (oKart.y - l * (0.7+(oKart.using.length-i)*0.35) * direction(1, oKart.rotation));
			}
			else {
				oArme.x = (oKart.x - l * direction(0, oKart.rotation+rotItem+i*dtheta));
				oArme.y = (oKart.y - l * direction(1, oKart.rotation+rotItem+i*dtheta));
			}
			oArme.z = oKart.z;
		}
	}
	else
		delete oKart.rotitem;
	if (course != "BB") {
		if (checkpoint(oKart, fMoveX,fMoveY)) {
			var nbjoueurs = aKarts.length;
			oKart.demitours = getNextCp(oKart);
			oKart.tours++;

			var lastCp = oMap.checkpoint[0];
			if (oMap.sections)
				lastCp = oMap.checkpoint[oMap.checkpoint.length-1];
			var distToCp, dSpeed;
			if (lastCp[3]) {
				var distToCp = (aPosY<lastCp[1]) ? (lastCp[1]-aPosY) : (aPosY-lastCp[1]-15);
				dSpeed = fMoveY;
			}
			else {
				var distToCp = (aPosX<lastCp[0]) ? (lastCp[0]-aPosX) : (aPosX-lastCp[0]-15);
				dSpeed = fMoveX;
			}
			var dt = distToCp/Math.abs(dSpeed);
			dt = Math.max(0,Math.min(dt,1));
			if (isNaN(dt-dt)) dt = 0.5;

			var lapTimer = Math.round((timer+dt-1)*SPF);
			if ((oKart == oPlayers[0]) && !oKart.cpu) {
				var lapTimerSum = 0;
				for (var i=0;i<lapTimers.length;i++)
					lapTimerSum += lapTimers[i];
				lapTimers.push(lapTimer-lapTimerSum);
			}

			if (oKart.tours == (oMap.tours+1)) {
				oKart.place = 0;
				for (var i=0;i<nbjoueurs;i++) {
					if (aKarts[i].tours > oMap.tours)
						oKart.place++;
				}
				if (kartIsPlayer(oKart) && !finishing) {
					timerMS = lapTimer;
					showTimer(timerMS);

					if (course != "CM")
						document.getElementById("infoPlace"+getId).innerHTML = oKart.place;
					while (oKart.using.length) {
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
					oKart.lastAItime = 0;
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
								var maxPts = Math.round(aKarts.length*1.25);
								for (var i=0;i<nbjoueurs;i++) {
									for (var j=0;j<nbjoueurs;j++) {
										var joueur = aKarts[j].personnage;
										if (aKarts[j].place == i+1) {
											var isRedTeam = (aKarts[j].team==1) ? 1:0;
											var xPts = (aKarts.length-i-1)/(aKarts.length-1);
											var ptsInc = Math.round(maxPts*(Math.exp(xPts)-1)/(Math.E-1));
											if (aKarts.length == 12) {
												// hardcoded scores to fit wii point system
												ptsInc = [15,12,10,8,7,6,5,4,3,2,1,0][i];
											}
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
								document.getElementById("infos0").style.color = primaryColor;
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
								document.getElementById("infos0").style.display = "";
								var aScroll = document.body.scrollTop;
								oContinue.focus();
								document.body.scrollTop = aScroll;
							}
							else {
								document.getElementById("infos0").style.fontSize = (iScreenScale * 5) +"px";
								document.getElementById("infos0").style.fontWeight = "bold";
								document.getElementById("infos0").style.color = "white";
								var sThickness = Math.ceil(iScreenScale/4) +"px";
								var oShadow = "black";
								sShadow = "-"+sThickness+" 0 "+oShadow+", 0 "+sThickness+" "+oShadow+", "+sThickness+" 0 "+oShadow+", 0 -"+sThickness+" "+oShadow;
								var lapTimesHtml = '';
								var minTimer = lapTimers[0];
								for (var i=1;i<lapTimers.length;i++)
									minTimer = Math.min(minTimer,lapTimers[i]);
								for (var i=0;i<lapTimers.length;i++) {
									sThickness = Math.ceil(iScreenScale/8) +"px";
									oShadow = (lapTimers[i]==minTimer) ? "#e99c00":"black";
									var lShadow = "-"+sThickness+" 0 "+oShadow+", 0 "+sThickness+" "+oShadow+", "+sThickness+" 0 "+oShadow+", 0 -"+sThickness+" "+oShadow;
									lapTimesHtml += '<div style="color:'+((lapTimers[i]==minTimer) ? '#fffdbe':'#DDD')+';text-shadow:'+lShadow+'">'+(i+1)+'. ' + timeStr(lapTimers[i]) +'</div>';
								}
								document.getElementById("infos0").style.top = (iScreenScale*7 + 10) +"px";
								document.getElementById("infos0").innerHTML = '<tr><td style="text-decoration: blink;font-family:Courier New;font-size:'+Math.round(iScreenScale*4)+'px;text-shadow:'+sShadow+'">'+ document.getElementById("temps0").innerHTML +'</td></tr><tr><td id="continuer"></td></tr><tr><td style="padding-top:'+(iScreenScale)+';font-size:'+Math.round(iScreenScale*2.5)+'px">'+lapTimesHtml+'</td></tr>';
								document.getElementById("infos0").style.display = "";
								var oContinue = document.createElement("input");
								oContinue.type = "button";
								oContinue.id = "octn";
								oContinue.value = toLanguage("CONTINUE", "CONTINUER");
								oContinue.style.width = "100%";
								oContinue.style.height = "100%";
								oContinue.style.fontSize = iScreenScale*3 +"pt";
								oContinue.onclick = function() {
									document.getElementById("infos0").style.display = "none";
									
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
									aPara1.innerHTML = toLanguage('Save the time to the <a href="classement.php" target="_blank" style="color: orange">record list</a> ?', 'Enregistrer le temps dans la <a href="'+ rankingsLink(oMap) +'" target="_blank" style="color: orange">liste des records</a> ?');
									aPara1.style.margin = iScreenScale +"px";
									var aPara2 = aPara1.cloneNode(false);
									var oSave = document.createElement("input");
									oSave.type = "button";
									oSave.value = "  "+ toLanguage("Yes", "Oui") +"  ";
									oSave.style.marginRight = iScreenScale +"px";

									oSave.style.fontSize = (iScreenScale*4) +"px";
									oSave.onmouseover = function() {
										this.style.fontSize = (iScreenScale*5) +"px";
										oRetour.style.fontSize = (iScreenScale*4) +"px"
									};
									oSave.onclick = function() {
										$mkScreen.removeChild(oForm);
										continuer();
										document.getElementById("enregistrer").getElementsByTagName("input")[0].onclick();
									};
									aPara2.appendChild(oSave);
									var oRetour = document.createElement("input");
									oRetour.type = "button";
									oRetour.value = "  "+ toLanguage("No", "Non") +"  ";
									oRetour.style.fontSize = (iScreenScale*4) +"px";
									oRetour.style.marginLeft = iScreenScale +"px";
									oRetour.onmouseover = function() {
										this.style.fontSize = (iScreenScale*5) +"px";
										oSave.style.fontSize = (iScreenScale*4) +"px"
									};
									oRetour.onclick = function() {
										$mkScreen.removeChild(oForm);
										document.getElementById("infos0").style.display = "";
										continuer();
									};
									aPara2.appendChild(oRetour);

									oForm.appendChild(aPara1);
									oForm.appendChild(aPara2);
									$mkScreen.appendChild(oForm);
									
									if (page == "MK" && timerMS >= gRecord)
										oRetour.focus();
									else
										oSave.focus();
								};
								document.getElementById("continuer").appendChild(oContinue);
								document.getElementById("infos0").style.display = "";
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
							infos0.style.display = "";
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
				var oCompteurTours = document.querySelectorAll("#compteur"+getId+" .tour");
				for (var i=0;i<oCompteurTours.length;i++)
					oCompteurTours[i].innerHTML = oKart.tours;
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
				if (course == "CM") {
					if (oLapTimeDiv)
						oContainers[0].removeChild(oLapTimeDiv);
					oLapTimeDiv = document.createElement("div");
					oLapTimeDiv.style.position = "absolute";
					oLapTimeDiv.style.zIndex = 20000;
					oLapTimeDiv.style.left = Math.round(iScreenScale*iWidth/2) +"px";
					oLapTimeDiv.style.top = Math.round(iScreenScale*iHeight/2) +"px";
					oLapTimeDiv.style.textAlign = "center";
					oLapTimeDiv.style.transform = oLapTimeDiv.style.WebkitTransform = oLapTimeDiv.style.MozTransform = "translate(-50%, -100%)";
					var oLapTimeText = document.createElement("div");
					oLapTimeText.className = "lap-time";
					oLapTimeText.innerHTML = timeStr(lapTimers[lapTimers.length-1]);
					oLapTimeText.style.fontSize = (iScreenScale*4) +"px";
					oLapTimeText.style.fontFamily = "Courier New";
					oLapTimeText.style.color = "#DDD";
					var sThickness = Math.ceil(iScreenScale/4) +"px";
					var oShadow = "black";
					oLapTimeText.style.textShadow = "-"+sThickness+" 0 "+oShadow+", 0 "+sThickness+" "+oShadow+", "+sThickness+" 0 "+oShadow+", 0 -"+sThickness+" "+oShadow;
					oLapTimeDiv.appendChild(oLapTimeText);

					if (iLapTimes) {
						var timeDiff = 0;
						for (var i=0;i<lapTimers.length;i++)
							timeDiff += lapTimers[i]-iLapTimes[i];
						var oTimeDiffText = document.createElement("div");
						oTimeDiffText.innerHTML = ((timeDiff>=0) ? "+":"-") +" "+ timeStr(Math.abs(timeDiff));
						oTimeDiffText.style.fontSize = Math.round(iScreenScale*2.5) +"px";
						oTimeDiffText.style.fontFamily = "Courier New";
						oTimeDiffText.style.color = (timeDiff>=0) ? "#FDD":"#DFD";
						sThickness = Math.ceil(iScreenScale/8) +"px";
						oShadow = (timeDiff>=0) ? "#C00":"#0A0";
						oTimeDiffText.style.textShadow = "-"+sThickness+" 0 "+oShadow+", 0 "+sThickness+" "+oShadow+", "+sThickness+" 0 "+oShadow+", 0 -"+sThickness+" "+oShadow;
						oLapTimeDiv.appendChild(oTimeDiffText);
					}

					oContainers[0].appendChild(oLapTimeDiv);
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
				var positions_ = "";
				var iPlace = 1;
				for (var i=0;i<aKarts.length;i++) {
					var isRedTeam = (aKarts[i].team==1) ? 1:0;
					var ptsInc = (aKarts[i] == gagnant);
					var joueur = aKarts[i].personnage;
					var actualPlace = ptsInc?0:iPlace;
					var positionsHtml = '<tr id="fJ'+actualPlace+'" style="background-color:'+ (i<strPlayer.length ? (i ? (isRedTeam?'brown':'navy') : (rankingColor(aKarts[i].team))) : (isRedTeam?'red':'transparent')) +'"><td>'+ toPlace(actualPlace+1) +' </td><td class="maj" id="j'+actualPlace+'">'+ toPerso(joueur) +'</td><td id="pts'+actualPlace+'">'+ aScores[i] + (!ptsInc ? "" : "<small>+1</small>")+'</td></tr>';
					if (ptsInc)
						positions_ = positionsHtml+positions_;
					else {
						positions_ += positionsHtml;
						iPlace++;
					}
					aScores[i] += ptsInc;
				}
				positions += positions_;

				positions += '<tr><td colspan="3" id="continuer"></td></tr>';
				document.getElementById("infos0").style.border = "solid 1px black";
				document.getElementById("infos0").style.opacity = 0.7;
				document.getElementById("infos0").style.fontSize = Math.round(iScreenScale*1.77-0.5) +"pt";
				document.getElementById("infos0").style.fontFamily = "Courier";
				document.getElementById("infos0").style.top = iScreenScale * 3 +"px";
				document.getElementById("infos0").style.left = Math.round(iScreenScale*24+10 + (strPlayer.length-1)/2*(iWidth*iScreenScale+2)) +"px";
				document.getElementById("infos0").style.backgroundColor = iTeamPlay ? "blue":"#063";
				document.getElementById("infos0").style.color = primaryColor;
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
				document.getElementById("infos0").style.display = "";
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
		if (course == "BB")
			oKart.maxspeed = 5.7;
		else {
			var oPlayerPlace = oPlayers[0].place;
			var rSpeed = iDificulty, influence = 1;
			var nCpus = aKarts.length-1-oPlayers.length;
			if (nCpus > 0) {
				var apparentId = getId-oPlayers.length;
				var firstCpu = aKarts[oPlayers.length];
				if ((firstCpu.place < oKart.place) && (firstCpu.place < oPlayerPlace)) {
					var distToFirst = oKart.distToFirstCache;
					if (distToFirst) {
						oKart.distToFirstTtl--;
						if (oKart.distToFirstTtl <= 0)
							oKart.distToFirstCache = 0;
					}
					else {
						distToFirst = distanceToKart(oKart,firstCpu);
						oKart.distToFirstCache = distToFirst;
						oKart.distToFirstTtl = 15+Math.floor(15*Math.random());
					}
					var d = 1/(1+distToFirst/(42*nCpus));
					apparentId = d*(1+apparentId) - 1;
				}
				else
					oKart.distToFirstCache = 0;
				influence = Math.pow(0.96, 6*(apparentId/nCpus-0.5));
			}
			rSpeed *= influence*iDificulty/5;
			var rRatio = 1.25;
			if ((iDificulty > 4.75) && (aKarts.length > 8))
				rRatio *= Math.log(1+100*aKarts.length/8)/5.5;
			if (oKart.maxspeed > rSpeed*rRatio) oKart.maxspeed = rSpeed*rRatio;
			else if (oKart.maxspeed < rSpeed) oKart.maxspeed = rSpeed;
			if (oKart.place <= oPlayerPlace)
				oKart.maxspeed -= (oKart.maxspeed*influence-rSpeed*oKart.size)/100;
			else
				oKart.maxspeed += (rSpeed*rRatio*1.12*oKart.size-oKart.maxspeed*influence)/100;
		}
	}
	else
		oKart.maxspeed = 5.4 * oKart.stats.speed;

	if (oKart.turbodrift) {
		var nSpeed = 8;
		if (oKart.turbodrift > 15) {
			nSpeed += Math.pow(2*(oKart.turbodrift-15)/15,2);
			oKart.turbodrift--;
			oKart.turbodrift0--;
		}
		if (oKart.speed > -nSpeed) {
			oKart.maxspeed = nSpeed;
			oKart.speed = Math.max(nSpeed, oKart.speed);
		}
		oKart.turbodrift--;
	}
	if (oKart.champi) {
		oKart.maxspeed = 11;
		oKart.champi--;
	}
	if (oKart.billball) {
		oKart.z = 2;
		oKart.heightinc = 0;
		oKart.speed = 11;

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
				fAngle = (fAngle > 0) ? 30:-30;
			}
			else
				fAngle = (fAngle > 0) ? 15:-15;
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
				resumeSpriteSize(oKart.sprite[i]);
			}
			oKart.size = 1;
			oKart.mini = 0;
			oKart.z = 0;
			updateDriftSize(getId);
			oKart.jumped = false;
			delete oKart.billjump;
			updateProtectFlag(oKart);
			if (!oKart.cpu)
				delete oKart.aipoint;
		}
	}
	if (oKart.champior) {
		oKart.champior--;
		if (oKart.champior <= 0) {
			delete oKart.champior;
			supprArme(getId);
		}
	}
	if (oKart.etoile) {
		oKart.maxspeed *= 1.35;
		oKart.etoile--;
		if (oKart.etoile < 15) {
			for (var i=0;i<strPlayer.length;i++)
				oKart.sprite[i].img.src = (oKart.etoile % 2 ? getStarSrc(oKart.personnage) : getSpriteSrc(oKart.personnage));
			if (!oKart.etoile) {
				updateProtectFlag(oKart);
				var maxSpeedInc = oKart.cpu ? 1 : oKart.stats.acceleration*oKart.size;
				oKart.speedinc = Math.min(oKart.speedinc, maxSpeedInc);
				stopStarMusic(oKart);
			}
		}
	}
	if (oKart.megachampi) {
		oKart.megachampi--;
		if (oKart.megachampi > 71)
			oKart.size *= 1.05;
		else if (oKart.megachampi < 8) {
			oKart.size /= 1.05;
			if (!oKart.megachampi) {
				updateProtectFlag(oKart);
				stopMegaMusic(oKart);
			}
		}
		updateDriftSize(getId);
	}
	if (oKart.mini) {
		oKart.mini--;
		if (oKart.mini < 1) {
			oKart.mini = 0;
			if (!isOnline || !getId)
				oKart.size = 1;
			updateDriftSize(aKarts.indexOf(oKart));
		}
	}
	if (oKart.cannon) {
		oKart.speed = (oKart.speed*3+20)/4;
		if (oKart.billball)
			oKart.speed = 20;
		oKart.maxspeed = oKart.speed/oKart.size;
		if (!oKart.speedinc)
			oKart.speedinc = 0.01;
		oKart.z = (oKart.z*3+4)/4;
		oKart.heightinc = 0;
		oKart.rotinc = 0;
		var x0 = oKart.cannon[2]-oKart.x, y0 = oKart.cannon[3]-oKart.y;
		var x1 = oKart.cannon[0]-oKart.cannon[2], y1 = oKart.cannon[1]-oKart.cannon[3];
		var d1 = Math.hypot(x1,y1);
		var l = Math.hypot(x0,y0)/d1;
		if (l < 0.2) {
			var x2 = oKart.cannon[0]-oKart.x, y2 = oKart.cannon[1]-oKart.y;
			oKart.rotation = nearestAngle(Math.atan2(x2,y2)*180/Math.PI,oKart.rotation, 360);
		}
		else if (l*d1 >= (d1-40)) {
			if (Math.abs(oKart.speedinc) <= 0.01)
				oKart.speedinc = 0;
			delete oKart.cannon;
			oKart.fell = true;
			updateProtectFlag(oKart);
		}
	}

	if (!oKart.z && accelere(aPosX, aPosY, fMoveX, fMoveY)) {
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

function handleDriftCpt(getId) {
	var oKart = aKarts[getId];
	if (oKart.driftinc) {
		if (oKart.rotincdir) {
			if ((oKart.rotincdir>0) == (oKart.driftinc>0)) {
				oKart.drift += oKart.driftinc;
				if (oKart.driftinc > 0) {
					if (oKart.drift > 6)
						oKart.drift = 6;
					else if (oKart.drift < 0)
						oKart.drift = Math.ceil(oKart.drift/2);
				}
				else {
					if (oKart.drift < -6)
						oKart.drift = -6;
					else if (oKart.drift > 0)
						oKart.drift = Math.floor(oKart.drift/2);
				}
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
				oKart.driftcpt += 6*Math.max(0.7,Math.pow(Math.abs(oKart.rotincdir)/0.6,0.8));
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
				oKart.drift = Math.min(0, oKart.drift + 1);
			else if (oKart.drift > 0)
				oKart.drift = Math.max(0, oKart.drift - 1);
		}
	}
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
function dirShoot(oKart, backwards, iStrength) {
	var relSpeed = backwards ? [0,0] : kartInstantSpeed(oKart);
	var oAngleView = angleShoot(oKart, backwards);
	relSpeed[0] += iStrength*direction(0, oAngleView);
	relSpeed[1] += iStrength*direction(1, oAngleView);
	return relSpeed;
}
function tendsToSpeed(fSprite, lSpeed, tRes) {
	var cSpeed = Math.hypot(fSprite.vx,fSprite.vy);
	if (cSpeed) {
		var nSpeed = cSpeed*(1-tRes) + lSpeed*tRes;
		fSprite.vx *= nSpeed/cSpeed;
		fSprite.vy *= nSpeed/cSpeed;
	}
}
function kartInstantSpeed(oKart) {
	var effRotation = oKart.rotation-angleDrift(oKart);
	var fMoveX = oKart.speed * direction(0, effRotation);
	var fMoveY = oKart.speed * direction(1, effRotation);
	if (oKart.shift) {
		fMoveX += oKart.shift[0];
		fMoveY += oKart.shift[1];
	}
	return [fMoveX,fMoveY];
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
function updateObjHud(ID) {
	document.getElementById("scroller"+ID).style.visibility="hidden";
	var oArme = aKarts[ID].arme;
	document.getElementById("roulette"+ID).innerHTML = '<img alt="'+oArme+'" class="pixelated" src="images/items/'+oArme+'.png" style="height: '+ Math.round(iScreenScale*4) +'px;" />';
}
function timeStr(timeMS) {
	var timeMins = Math.floor(timeMS/60000);
	timeMS -= timeMins*60000;
	timeMins += "";
	var timeSecs = Math.floor(timeMS/1000);
	timeMS -= timeSecs*1000;
	timeSecs += "";
	if (timeSecs.length < 2)
		timeSecs = "0"+ timeSecs;
	timeMS += "";
	while (timeMS.length < 3)
		timeMS = "0"+ timeMS;
	return timeMins +"'"+ timeSecs +"&quot;"+ timeMS;
}

var clLocalVars, clHud, clSelected;
//clSelected = challenges["track"]["7037"]["list"][3];

function openCheats() {
	var cheatCode = prompt("MKPC Console command");
	if (!cheatCode)
		return false;
	if (!processCode(cheatCode))
		alert("Invalid command");
	else {
		clLocalVars.cheated = true;
		if (clSelected)
			challengeHandleFail();
	}
}
function processCode(cheatCode) {
	if (cheatCode.charAt(0) != "/")
		return false;
	cheatCode = cheatCode.substring(1);
	var oPlayer = oPlayers[0];
	var isObject = /^give (\w+)$/g.exec(cheatCode);
	if (isObject) {
		var wObject = isObject[1];
		var isExistingObj = false;
		for (var i=0;i<itemDistribution.length;i++) {
			if (itemDistribution[i][wObject]) {
				isExistingObj = true;
				break;
			}
		}
		if (!isExistingObj)
			return false;
		oPlayer.arme = wObject;
		oPlayer.roulette = 25;
		updateObjHud(0);
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
		var oCompteurTours = document.querySelectorAll("#compteur0 .tour");
		for (var i=0;i<oCompteurTours.length;i++)
			oCompteurTours[i].innerHTML = oPlayer.tours;
		return true;
	}
	if (cheatCode == "pos") {
		alert("x: "+ Math.round(oPlayer.x)+"\ny: "+Math.round(oPlayer.y)+"\ntheta: "+Math.round(oPlayer.rotation));
		return true;
	}
	else if (cheatCode == "xs") {
		oPlayer.size = 0.6;
		oPlayer.mini = 0;
		return true;
	}
	else if (cheatCode == "md") {
		oPlayer.size = 1;
		oPlayer.mini = 0;
		return true;
	}
	else if (cheatCode == "xl") {
		oPlayer.size = Math.pow(1.05,8);
		oPlayer.mini = 0;
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
				updateBalloonHud(document.getElementById("compteur0"),oPlayer);
				return true;
			}
		}
	}
	return false;
}

function distToAiPoints(x,y, aipoints) {
	var d2 = Infinity;
	for (var i=0;i<aipoints.length;i++) {
		var inc = (i+1)%aipoints.length;
		var x1 = aipoints[i][0], y1 = aipoints[i][1], x2 = aipoints[inc][0], y2 = aipoints[inc][1];
		var l = projete(x,y, x1,y1,x2,y2);
		if (l < 0) l = 0;
		if (l > 1) l = 1;
		var xL = x1 + (x2-x1)*l, yL = y1 + (y2-y1)*l;
		d2 = Math.min(d2,(xL-x)*(xL-x)+(yL-y)*(yL-y));
	}
	return d2;
}

var fMaxRotInCp = 10;
function ai(oKart) {
	var simpleBattle = (isBattle && simplified);
	var completeBattle = (isBattle && complete);
	var completeCircuit = (!isBattle && complete);
	if (oKart.aipoint == undefined) {
		if (course != "BB") {
			var minDist = Infinity;
			for (var i=0;i<oKart.aipoints.length;i++) {
				var iPt = oKart.aipoints[i];
				var diffX = iPt[0]-oKart.x, diffY = iPt[1]-oKart.y;
				var gDist = Math.sqrt(diffX*diffX + diffY*diffY), aDist = gDist*(Math.abs(nearestAngle(Math.atan2(diffX,diffY)-oKart.rotation*Math.PI/180,0,2*Math.PI)));
				var pDist = gDist*2 + aDist;
				if (pDist < minDist) {
					oKart.aipoint = i;
					oKart.lastAItime = 0;
					minDist = pDist;
				}
			}
		}
		else if (simpleBattle) {
			oKart.aipoint = Math.floor(oKart.x/100)+Math.floor(oKart.y/100)*6;
			oKart.lastAItime = 0;
			oKart.lastAI = -1;
		}
	}
	if (oKart.billball)
		return;
	if (oKart.tombe)
		return;
	if ((oMap.sections && oKart.tours > oMap.tours) || (oKart.ballons && !oKart.ballons.length)) {
		oKart.speedinc = 0;
		oKart.rotinc = 0;
		oKart.rotincdir = 0;
		return;
	}
	if (oKart.tourne) {
		oKart.speedinc = (!oKart.z&&(oKart.speed>1)) ? 1:0;
		oKart.rotinc = 0;
		oKart.rotincdir = 0;
		return;
	}
	if (!oKart.aipoints.length) return;

	var distToAim = 0, angleToAim = 2*Math.PI, speedToAim = 0; // used for item

	for (var f=0;f<oKart.aipoints.length;f++) {
		var lastAi, currentAi, nextAi;
		var aiId = oKart.aipoint;
		if (course != "BB") {
			var lastAiId = aiId-1;
			if (lastAiId < 0) lastAiId += oKart.aipoints.length;
			var nextAiId = aiId+1;
			if (nextAiId >= oKart.aipoints.length) nextAiId = 0;
			lastAi = oKart.aipoints[lastAiId];
			currentAi = oKart.aipoints[aiId];
			nextAi = oKart.aipoints[nextAiId];
		}
		else {
			if (simpleBattle) {
				lastAi = [(oKart.lastAI%6)*100+50,Math.floor(oKart.lastAI/6)*100+50];
				currentAi = [(oKart.aipoint%6)*100+50,Math.floor(oKart.aipoint/6)*100+50];
				nextAi = [lastAi[0] + (currentAi[0]-lastAi[0])*2, lastAi[1] + (currentAi[1]-lastAi[1])*2];
				var nbPos = Math.floor(oKart.x/100)+Math.floor(oKart.y/100)*6;
				if (nbPos != oKart.lastAI) {
					if (oMap.skin != 27 || ((currentAi[0]-oKart.x)*(currentAi[0]-oKart.x) + (currentAi[1]-oKart.y)*(currentAi[1]-oKart.y)) < 1500 || oKart.speed < 0 || oKart.tombe) {
						var chemins = oKart.aipoints[nbPos];
						if (!chemins || !chemins.length) {
							chemins = [];
							if (nbPos%6)
								chemins.push(nbPos-1);
							if (nbPos%6 < 5)
								chemins.push(nbPos+1);
							if (nbPos >= 6)
								chemins.push(nbPos-6);
							if (nbPos < 30)
								chemins.push(nbPos+6);
							var lastAiC = chemins.indexOf(oKart.lastAI);
							if (lastAiC != -1)
								chemins.splice(lastAiC,1);
						}
						var origine = oKart.lastAI;
						oKart.lastAI = nbPos;
						do {
							oKart.aipoint = chemins[Math.floor(Math.random()*chemins.length)];
						} while ((origine == oKart.aipoint) && (chemins.length > 1));
					}
					oKart.nextAiStop = undefined;
					oKart.randShift = undefined;
				}
			}
			else {
				if (oKart.aipoint == undefined) {
					oKart.lastAI = undefined;
					oKart.nextAI = undefined;
					var minDist = Infinity;
					for (var i=0;i<oKart.aipoints.length;i++) {
						if (i != oKart.lastAI) {
							var iPt = oKart.aipoints[i];
							if (iPt[0] == 0) {
								var diffX = iPt[1]-oKart.x, diffY = iPt[2]-oKart.y;
								var gDist = Math.sqrt(diffX*diffX + diffY*diffY), aDist = gDist*(Math.abs(nearestAngle(Math.atan2(diffX,diffY)-oKart.rotation*Math.PI/180,0,2*Math.PI)));
								var pDist = gDist*2 + aDist;
								if (pDist < minDist) {
									oKart.aipoint = i;
									oKart.lastAItime = 0;
									minDist = pDist;
								}
							}
						}
					}
				}
				currentAi = oKart.aipoints[oKart.aipoint].slice(1);
				if (oKart.lastAI)
					lastAi = oKart.aipoints[oKart.lastAI].slice(1);
				else {
					if (!oKart.lastAIpt)
						oKart.lastAIpt = [oKart.x,oKart.y];
					lastAi = oKart.lastAIpt;
				}
				if (!oKart.nextAI) {
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
					if (chemins.length) {
						do {
							oKart.nextAI = chemins[Math.floor(Math.random()*chemins.length)];
						} while ((oKart.lastAI == oKart.nextAI) && (chemins.length > 1));
					}
					else
						oKart.nextAI = -1;
				}
				if (oKart.nextAI != -1)
					nextAi = oKart.aipoints[oKart.nextAI].slice(1);
				else
					nextAi = lastAi;
			}
		}

		oKart.speedinc = 1;
		oKart.lastAItime++;

		var u = currentAi[0]-lastAi[0], v = currentAi[1]-lastAi[1], w = Math.hypot(u,v);
		var u2 = nextAi[0]-currentAi[0], v2 = nextAi[1]-currentAi[1];
		var maxOmega = fMaxRotInCp*Math.PI/180;
		var hMargin = 20;
		if (completeBattle)
			hMargin = 15;

		if (!oKart.nextAiStop) {
			oKart.nextAiStop = nextAiStop(lastAi[0],lastAi[1], u,v, currentAi[0],currentAi[1], u2,v2, oKart.maxspeed/maxOmega);
			var minAiStop = 1-50/w;
			if (oKart.nextAiStop < minAiStop)
				oKart.nextAiStop = minAiStop;
		}
		if (!oKart.randShift) {
			oKart.randShift = Math.random()*10-5;
			if (oMap.randshift != undefined)
				oKart.randShift *= oMap.randshift;
			if (completeBattle)
				oKart.randShift /= 1.5;
			else if (completeCircuit) {
				var maxShift = w/10;
				if (Math.abs(oKart.randShift) > maxShift)
					oKart.randShift = maxShift*Math.sign(oKart.randShift);
			}
		}
		var l = projete(oKart.x,oKart.y, lastAi[0],lastAi[1], currentAi[0],currentAi[1]);
		var projX = lastAi[0] + l*(currentAi[0]-lastAi[0]), projY = lastAi[1] + l*(currentAi[1]-lastAi[1]);
		var h = Math.hypot(oKart.x-projX,oKart.y-projY);
		if (!u && !v) {
			h = 0;
			oKart.nextAiStop = 0;
			l = 1;
		}
		if (l >= oKart.nextAiStop) {
			if (h < hMargin) {
				if (!simpleBattle) {
					if (course != "BB") {
						oKart.aipoint++;
						if (oKart.aipoint >= oKart.aipoints.length)
							oKart.aipoint = 0;
					}
					else {
						oKart.lastAI = oKart.aipoint;
						delete oKart.lastAIpt;
						if (oKart.nextAI != -1)
							oKart.aipoint = oKart.nextAI;
						else
							oKart.aipoint = undefined;
						oKart.nextAI = undefined;
					}
					oKart.nextAiStop = undefined;
					oKart.randShift = undefined;
					oKart.lastAItime = 0;
					continue;
				}
			}
		}
		var someCollision = false;
		if (oKart.bounced) {
			if (!oKart.bouncedsince)
				oKart.rotinc = fMaxRotInCp*Math.random();
			else
				oKart.rotinc = 0;
			oKart.bouncedsince++;
			if (oKart.bouncedsince == 10) {
				delete oKart.bouncedsince;
				delete oKart.bounced;
			}
			someCollision = true;
		}
		else if (oKart.collided) {
			delete oKart.recovering;
			delete oKart.minDecor;
			if (!oKart.recovertime)
				oKart.recovertime = 21+Math.floor(Math.random()*10);
			if (!oKart.collidesince) {
				oKart.collidesince = 1;
				if (!oKart.decision) {
					var xp = direction(0,oKart.rotation), yp = direction(1,oKart.rotation);
					var xc = oKart.horizontality[0], yc = oKart.horizontality[1];
					oKart.decision = (xp*xc+yp*yc>0)!=(xp*yc-yp*xc>0) ? 1:-1;
				}
			}
			else if (oKart.collidesince > oKart.recovertime) {
				oKart.randShift = Math.random()*oKart.recovertime/2-oKart.recovertime/4;
				oKart.recovertime += 30;
				oKart.decision = -oKart.decision;
				oKart.collidesince = 1;
				if (simpleBattle)
					oKart.lastAI = oKart.aipoint;
			}
			oKart.collidesince++;
			if (oKart.horizontality) {
				var xp = direction(0,oKart.rotation), yp = direction(1,oKart.rotation);
				var xc = oKart.horizontality[0], yc = oKart.horizontality[1];
				if ((Math.abs(xp*yc-yp*xc) > 0.1) || (oKart.lastAItime > 150))
					oKart.rotinc = oKart.decision*fMaxRotInCp;
			}
			else
				oKart.rotinc = oKart.decision*fMaxRotInCp;
			someCollision = true;
		}
		else {
			distToAim = (oKart.nextAiStop-l)*w;
			if (oKart.collidesince) {
				if (!oKart.recovering)
					oKart.recovering = 1;
				oKart.recovering++;
				someCollision = true;
				if (oKart.recovering >= 20) {
					delete oKart.recovering;
					delete oKart.recovertime;
					delete oKart.decision;
					delete oKart.collidesince;
				}
			}
			var aimX, aimY, nTheta;
			if (h < hMargin) {
				aimX = currentAi[0]+oKart.randShift*v/w;
				aimY = currentAi[1]-oKart.randShift*u/w;
				nTheta = Math.atan2(aimX-oKart.x, aimY-oKart.y);
			}
			else {
				var aimCircle = followCircleWithAngle(oKart.x,oKart.y,currentAi[0],currentAi[1],u,v);
				var xH = lastAi[0]+(oKart.x-projX)*hMargin/h, yH = lastAi[1]+(oKart.y-projY)*hMargin/h;
				var aimPoint = intersectionLineCircle(aimCircle[0],aimCircle[1],aimCircle[2],xH,yH,u,v);
				aimX = xH+aimPoint*u;
				aimY = yH+aimPoint*v;
				var uAim = aimCircle[1]-oKart.y, vAim = oKart.x-aimCircle[0];
				if (uAim*(projX-oKart.x) + vAim*(projY-oKart.y) < 0) {
					uAim = -uAim;
					vAim = -vAim;
				}
				nTheta = Math.atan2(uAim,vAim);
			}
			var decorT = 16;
			var oX = oKart.x, oY = oKart.y, gX = aimX, gY = aimY;
			var gD = Math.hypot(gX-oX, gY-oY);
			var vX = (gX-oX)*oKart.speed/gD, vY = (gY-oY)*oKart.speed/gD;
			var minDecor, minT = 1;
			if (!isCup) {
				for (var type in decorPos) {
					var hitboxSize = decorBehaviors[type].hitbox||DEFAULT_DECOR_HITBOX;
					hitboxSize *= 1.1;
					for (var i=0;i<decorPos[type].length;i++) {
						var iDecor = decorPos[type][i];
						var decorLines = [
							[iDecor.x-hitboxSize,iDecor.y-hitboxSize,hitboxSize*2,0],
							[iDecor.x-hitboxSize,iDecor.y-hitboxSize,0,hitboxSize*2],
							[iDecor.x-hitboxSize,iDecor.y+hitboxSize,hitboxSize*2,0],
							[iDecor.x+hitboxSize,iDecor.y-hitboxSize,0,hitboxSize*2]
						];
						var uX = vX-iDecor.vX, uY = vY-iDecor.vY;
						for (var j=0;j<decorLines.length;j++) {
							var decorLine = decorLines[j];
							var inter = secants(oX,oY,oX+uX*decorT,oY+uY*decorT,decorLine[0],decorLine[1],decorLine[0]+decorLine[2],decorLine[1]+decorLine[3]);
							if (inter && (inter[0] < minT)) {
								minDecor = {
									type: type,
									i: i,
									line: decorLine,
									inter: inter,
									box: [decorLine[0]+iDecor.vX*decorT*inter[1],decorLine[1]+iDecor.vY*decorT*inter[1],decorLine[2],decorLine[3]]
								};
								minT = inter[0];
							}
						}
					}
				}
			}
			if (minDecor) {
				var aimX1 = minDecor.box[0], aimY1 = minDecor.box[1];
				var aimX2 = minDecor.box[0]+minDecor.box[2], aimY2 = minDecor.box[1]+minDecor.box[3];
				var d1 = distToAiPoints(aimX1,aimY1, oKart.aipoints);
				var d2 = distToAiPoints(aimX2,aimY2, oKart.aipoints);
				aimX1 -= minDecor.box[2]/1.5;
				aimY1 -= minDecor.box[3]/1.5;
				aimX2 += minDecor.box[2]/1.5;
				aimY2 += minDecor.box[3]/1.5;
				if (d1 < d2) {
					aimX = aimX1;
					aimY = aimY1;
				}
				else {
					aimX = aimX2;
					aimY = aimY2;
				}
				nTheta = Math.atan2(aimX-oKart.x,aimY-oKart.y);
				oKart.minDecor = {
					x: aimX,
					y: aimY,
					t: 10
				}
			}
			else if (oKart.minDecor) {
				var nAimX = oKart.minDecor.x;
				var nAimY = oKart.minDecor.y;
				oKart.minDecor.t--;
				if (oKart.minDecor.t < 0)
					delete oKart.minDecor;
				else if ((oKart.x-nAimX)*(oKart.x-nAimX) + (oKart.y-nAimY)*(oKart.y-nAimY) < 300)
					delete oKart.minDecor;
				else {
					var cosTheta = direction(0,oKart.rotation), sinTheta = direction(1,oKart.rotation);
					var dirX = nAimX-oKart.x, dirY = nAimY-oKart.y;
					if (dirX*cosTheta + dirY*sinTheta < 0)
						delete oKart.minDecor;
				}
				if (oKart.minDecor) {
					aimX = nAimX;
					aimY = nAimY;
				}
			}
			var actualTheta = oKart.rotation, actualSpeed = oKart.speed;
			if (oKart.shift && !isCup) {
				var actualShift = flowShift(oKart.x,oKart.y, oKart.protect);
				if (actualShift) {
					var fMoveDir = kartInstantSpeed(oKart);
					if (actualShift[2])
						actualSpeed = Math.min(actualSpeed,Math.hypot(fMoveDir[0],fMoveDir[1]));
					else {
						actualTheta = Math.atan2(fMoveDir[0],fMoveDir[1])*180/Math.PI;
						if (actualShift[0]*actualShift[0] + actualShift[1]*actualShift[1] >= 10)
							actualTheta += (actualTheta-oKart.rotation)*0.15;
					}
					if (isNaN(actualTheta)) actualTheta = oKart.rotation;
				}
			}
			nTheta = nearestAngle(nTheta*180/Math.PI, actualTheta, 360);
			if (isNaN(nTheta)) nTheta = actualTheta;
			if (isNaN(aimX)) aimX = currentAi[0];
			if (isNaN(aimY)) aimY = currentAi[1];
			var diffTheta = nTheta-actualTheta;
			oKart.rotinc = Math.max(Math.min(diffTheta, fMaxRotInCp), -fMaxRotInCp);
			var diffThetaAbs = Math.abs(diffTheta);
			angleToAim = diffThetaAbs;
			if (isBattle)
				diffThetaAbs *= Math.pow(diffThetaAbs/10,1.5);
			distToAim = Math.hypot(aimX-oKart.x,aimY-oKart.y);
			var dirToAim = Math.atan2(aimX-lastAi[0],aimY-lastAi[1])-oKart.rotation;
			var rAngle = (Math.abs(Math.sin(dirToAim))+diffThetaAbs*Math.PI/180/2)/maxOmega;
			var speedToAim = distToAim/rAngle;
			if (course == "BB") {
				var rAngle0 = Math.max(5,rAngle/1.57);
				if (rAngle > rAngle0) {
					var maxHoleDist = 40, maxHoleDist2 = maxHoleDist*maxHoleDist;
					var nearestHoleDist = getNearestHoleDist(oKart.x,oKart.y, maxHoleDist2);
					if (nearestHoleDist < maxHoleDist2)
						speedToAim = distToAim/Math.pow(rAngle0*Math.tan(rAngle/rAngle0),1.5);
					else
						speedToAim = distToAim/Math.pow(rAngle,1.2);
				}
			}
			if (diffThetaAbs > fMaxRotInCp) {
				if (h < hMargin) {
					var marginSpeed = aiMarginLimitSpeed(oKart.x,oKart.y,direction(0,oKart.rotation),direction(1,oKart.rotation),currentAi[0],currentAi[1],u,v,hMargin*2,maxOmega);
					if (marginSpeed < speedToAim) speedToAim = marginSpeed;
				}
				var maxSpeed = speedToAim;
				if (oKart.bloops && oKart.bloops.effective(oKart))
					maxSpeed = Math.min(maxSpeed,3);
				maxSpeed /= 0.9;
				if (!maxSpeed)
					maxSpeed = 0.01;
				if ((oMap.skin == 32) && (actualSpeed > 6) && (maxSpeed < (actualSpeed-1))) {
					oKart.speed = Math.max(maxSpeed,oKart.speed-2);
					oKart.speedinc = 0;
				}
				else
					oKart.speedinc = Math.max(Math.min(maxSpeed-actualSpeed,1),-1);
				if (oKart.speedinc < 0)
					oKart.rotinc = -oKart.rotinc;
			}
		}
		if (someCollision && (oKart.lastAItime > 250+Math.random()*50) && !simpleBattle)
			oKart.aipoint = undefined;
		break;
	}
	if ((oKart.roulette == 25 || oKart.using[0]) && !oKart.tourne && !oKart.cannon) {
		var useRandomly = false;
		function isPlayerTargetable(minDist,maxDist,minAngle,maxAngle, reverse) {
			var minDist2 = minDist*minDist, maxDist2 = maxDist*maxDist;
			for (var i=0;i<strPlayer.length;i++) {
				var iKart = aKarts[i];
				if (!iKart.loose && !iKart.protect) {
					var dDist2 = (iKart.x-oKart.x)*(iKart.x-oKart.x) + (iKart.y-oKart.y)*(iKart.y-oKart.y);
					if ((dDist2 >= minDist2) && (dDist2 < maxDist2)) {
						var iAngle = Math.atan2(iKart.x-oKart.x, iKart.y-oKart.y)*180/Math.PI;
						if (reverse)
							iAngle += 180;
						var dAngle = Math.abs(nearestAngle(iAngle-oKart.rotation, 0,360));
						if ((dAngle >= minAngle) && (dAngle < maxAngle))
							return true;
					}
				}
			}
			return false;
		}
		if (oKart.using.length) {
			switch(oKart.using[0].type) {
			case "carapace-rouge":
				if ((course == "BB") && (oKart.using.length < 2)) {
					if (isPlayerTargetable(15,100, 0,30))
						arme(aKarts.indexOf(oKart));
				}
				else
					useRandomly = true;
				break;
			case "carapace":
				if ((course == "BB") && (oKart.using.length < 2)) {
					if (isPlayerTargetable(0,20, 0,30) || isPlayerTargetable(0,150, 0,15))
						arme(aKarts.indexOf(oKart));
					if (isPlayerTargetable(0,20, 0,30, true) || isPlayerTargetable(0,80, 0,15, true))
						arme(aKarts.indexOf(oKart), true);
				}
				else
					useRandomly = true;
				break;
			default:
				useRandomly = true;
			}
		}
		else {
			switch (oKart.arme) {
			case "megachampi":
			case "etoile":
				if ((speedToAim >= 11) && (distToAim >= 100) && (angleToAim <= 10))
					arme(aKarts.indexOf(oKart));
				break;
			case "champi":
			case "champiX2":
			case "champiX3":
				if (!oKart.champi && (speedToAim >= 11) && (distToAim >= 100) && (angleToAim <= 10))
					arme(aKarts.indexOf(oKart));
				break;
			case "champior":
				if (oKart.champior) {
					if (((speedToAim >= 8) || (speedToAim-oKart.speed >= 5)) && (angleToAim <= 10))
						arme(aKarts.indexOf(oKart));
				}
				else {
					if ((speedToAim >= 11) && (distToAim >= 100) && (angleToAim <= 10))
						arme(aKarts.indexOf(oKart));
				}
				break;
			default:
				useRandomly = true;
			}
		}
		if (useRandomly) {
			if (Math.random() > 0.98) {
				var backwards = (((oKart.place<oPlayers[0].place)||(course=="BB")) && (Math.random() > 0.5));
				arme(aKarts.indexOf(oKart), backwards);
			}
		}
	}
	if (oMap.jumpable && (iDificulty > 4)) {
		if (oKart.z && !oKart.jumped && !oKart.billball && !oKart.figstate && !oKart.figuring && !oKart.tourne && (oKart.heightinc > 0)) {
			if (speedToAim >= 8) {
				if (!oMap.jumpexc || oMap.jumpexc.indexOf(oKart.aipoint) == -1)
					oKart.figstate = 21;
			}
		}
	}
}
function moveItems() {
	collisionTest = COL_OBJ;
	collisionTeam = undefined;
	clLocalVars.currentKart = undefined;

	for (var key in itemBehaviors) {
		var moveFn = itemBehaviors[key].move;
		if (moveFn) {
			var kItems = items[key];
			for (var i=kItems.length-1;i>=0;i--) {
				if (kItems[i])
					moveFn(kItems[i]);
			}
		}
	}
}
function moveDecor() {
	decorPos = {};
	for (var type in oMap.decor) {
		if (decorBehaviors[type].dodgable) {
			decorPos[type] = [];
			var decor = oMap.decor[type];
			for (var i=0;i<decor.length;i++)
				decorPos[type].push({aX:decor[i][0],aY:decor[i][1],x:decor[i][0],y:decor[i][1],vX:0,vY:0});
		}
	}
	var decorIncs = {};
	for (var type in oMap.decor) {
		var decor = oMap.decor[type];
		var decorBehavior = decorBehaviors[type];
		if (decorBehavior.move) {
			var actualType = getDecorActualType(decorBehavior);
			var inc = 0;
			if (decorIncs[actualType])
				inc = decorIncs[actualType];
			else
				decorIncs[actualType] = 0;
			
			for (var i=0;i<decor.length;i++)
				decorBehavior.move(decor[i],i,i+inc);
			
			decorIncs[actualType] += decor.length;
		}
	}
	var tau = 2*Math.PI;
	for (var type in decorPos) {
		var decor = oMap.decor[type];
		for (var i=0;i<decor.length;i++) {
			decorPos[type][i].x = decor[i][0];
			decorPos[type][i].y = decor[i][1];
			decorPos[type][i].vX = decorPos[type][i].x - decorPos[type][i].aX;
			decorPos[type][i].vY = decorPos[type][i].y - decorPos[type][i].aY;
		}
	}
	if (oMap.pointers) {
		for (var i=0;i<oMap.pointers.length;i++) {
			var pointer = oMap.pointers[i];
			pointer[2][2] += pointer[2][3];
			pointer[2][2] %= tau;
			pointer[0].redraw(pointer);
		}
	}
	if (oMap.flippers) {
		for (var i=0;i<oMap.flippers.length;i++) {
			var flipper = oMap.flippers[i];
			var state = flipper[3][0];
			switch (state) {
			case 0:
				if (--flipper[3][1] <= 0) {
					flipper[3][0] = 1;
					flipper[3][1] = flipper[2][2];
					flipper[2][3] = flipper[2][4]*0.13;
				}
				break;
			case 1:
			case 2:
				var aim = (state==1) ? flipper[3][1]+flipper[2][4] : flipper[3][1];
				flipper[2][2] += flipper[2][3];
				if (flipper[2][2]*flipper[2][3] >= aim*flipper[2][3]) {
					flipper[2][2] = aim;
					if (state == 1) {
						flipper[3][0] = 2;
						flipper[2][3] = -flipper[2][3];
					}
					else {
						flipper[3][0] = 0;
						flipper[2][3] = 0;
						flipper[3][1] = 1 + Math.floor(Math.random()*50);
					}
				}
				break;
			}
			if (state)
				flipper[0].redraw(flipper);
		}
	}
	if (oMap.bumpers) {
		for (var i=0;i<oMap.bumpers.length;i++) {
			var bumper = oMap.bumpers[i];
			if (bumper[2][5]) {
				if (!bumper[3]) {
					var distanceToCenter = Math.hypot(bumper[1][0]-bumper[2][3],bumper[1][1]-bumper[2][4]);
					var angleToCenter = Math.atan2(bumper[1][1]-bumper[2][4],bumper[1][0]-bumper[2][3]);
					bumper[3] = [distanceToCenter,angleToCenter];
				}
				bumper[3][1] += bumper[2][5];
				bumper[3][1] %= tau;
				bumper[1][0] = bumper[2][3] + bumper[3][0]*Math.cos(bumper[3][1]);
				bumper[1][1] = bumper[2][4] + bumper[3][0]*Math.sin(bumper[3][1]);
				bumper[0].redraw(bumper);
			}
		}
	}
	if (oMap.sea) {
		var oSea = oMap.sea;
		var lastProgress = oSea.progress;
		var tLow = 120, tHigh = tLow, tTransition = 30, tTransition2 = tTransition, tTotal = tLow+tHigh+tTransition+tTransition2;
		var ti = (timer+tLow/2)%tTotal;
		if (ti < tLow)
			oSea.progress = 1;
		else if (ti < (tLow+tTransition))
			oSea.progress = 0.5-Math.sin(Math.PI*((ti-tLow)/tTransition-0.5))*0.5;
		else if (ti < (tLow+tHigh+tTransition))
			oSea.progress = 0;
		else
			oSea.progress = 0.5+Math.sin(Math.PI*((ti-tLow-tHigh-tTransition)/tTransition2-0.5))*0.5;
		if (oSea.progress !== lastProgress) {
			oMap.horspistes.eau = {rectangle:[],polygon:oSea.offroad0.slice()};
			if (oSea.progress) {
				var waterL = oSea.progress*0.99;
				for (var i=0;i<oSea.waves.length;i++)
					oMap.horspistes.eau.polygon.push(oSea.polygon(i, 0,waterL));
			}
		}
	}
	if (oMap.coins) {
		for (var i=0;i<oMap.coins.length;i++) {
			var oCoin = oMap.coins[i];
			oCoin.theta += 0.25;
			if (oCoin.theta >= tau)
				oCoin.theta -= tau;
		}
	}
}

var cycleHandler;
function cycle() {
	cycleHandler = setInterval(runOneFrame,SPF);
	runOneFrame();
}
var decorPos = {};
function runOneFrame() {
	if (course != "CM") {
		for (var i=0;i<aKarts.length;i++)
			colKart(i);
	}
	for (var i=0;i<aKarts.length;i++) {
		var oKart = aKarts[i];
		if (oKart.pushVector) {
			var maxPush = 6;
			var modPush2 = oKart.pushVector[0]*oKart.pushVector[0] + oKart.pushVector[1]*oKart.pushVector[1];
			if (modPush2 > maxPush*maxPush) {
				var modPush = Math.sqrt(modPush2);
				oKart.pushVector[0] *= maxPush/modPush;
				oKart.pushVector[1] *= maxPush/modPush;
			}
			if (!oKart.shift)
				oKart.shift = [0,0,0];
			oKart.shift[0] += oKart.pushVector[0];
			oKart.shift[1] += oKart.pushVector[1];
			delete oKart.pushVector;
		}
	}
	for (var i=0;i<aKarts.length;i++) {
		var oKart = aKarts[i];
		if (i && (course == "CM") && !oKart.cpu) {
			var jTrajet = jTrajets[i-1];
			if (timer <= jTrajet.length) {
				var getInfos = jTrajet[timer-1];
				if (oKart.tombe) {
					oKart.tombe--;
					if (!oKart.tombe)
						resetFall(oKart);
				}
				var aX = oKart.x;
				var aY = oKart.y;
				var aRotation = oKart.rotation;
				oKart.x = getInfos[0];
				oKart.y = getInfos[1];
				oKart.z = getInfos[2];
				oKart.rotation = getInfos[3];
				if (getInfos[4] && (getInfos[4][0] == "1")) {
					oKart.tombe = 20;
					oKart.aX = aX;
					oKart.aY = aY;
					oKart.aRotation = aRotation;
					oKart.sprite[0].img.style.display = "none";
				}
				continue;
			}
			else {
				oKart.cpu = true;
				oKart.aipoint = 0;
				oKart.lastAItime = 0;
				oKart.arme = false;
			}
		}
		if (!oKart.loose || isOnline) {
			if (oKart.cpu)
				ai(oKart);
			move(i);
			if (course == "CM" && !oKart.cpu) {
				var trajetplus = [Math.round(oKart.x),Math.round(oKart.y),oKart.z,Math.round(oKart.rotation)];
				var trajetflags = "0000".split("");
				if (oKart.tombe == 20)
					trajetflags[0] = "1";
				if (oKart.ctrl) {
					trajetflags[1] = "1";
					if (oKart.rotincdir > 0)
						trajetflags[2] = "1";
					else if (oKart.rotincdir < 0)
						trajetflags[3] = "1";
				}
				if (trajetflags.indexOf("1") != -1)
					trajetplus.push(trajetflags.join(""));
				iTrajet.push(trajetplus);
			}
		}
	}
	if (course != "CM") {
		for (var i=0;i<aKarts.length;i++)
			places(i);
	}
	moveItems();
	moveDecor();
	if (!oPlayers[0].cpu) {
		if (clSelected && !clSelectionFail) {
			if (false === challengeRulesSatisfied(clSelected, clSelected.data.constraints))
				challengeHandleFail();
		}
		challengeCheck("each_frame");
	}
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
			var $decompte = document.getElementById("decompte0");
			if ($decompte.innerHTML > 1)
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
			cp[playerName] = [0.5,0.5,0.5,0.5];
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
				cp[playerName][3] = perso.mass;
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
function getCustomDecorData(customData,callback) {
	var id = customData.id, type = customData.type;
	var retreivingData = false;
	if (customDecorData[id]) {
		if (customDecorData[id].data !== undefined) {
			callback(customDecorData[id].data);
			return;
		}
		retreivingData = true;
	}
	else {
		customDecorData[id] = {
			callbacks: []
		};
	}
	customDecorData[id].callbacks.push(callback);
	if (retreivingData) return;
	xhr("getDecorData.php?id="+id+"&full", null, function(res) {
		var data;
		try {
			data = JSON.parse(res);
		}
		catch (e) {
			data = {
				"id":id,
				"hd":"images/sprites/sprite_"+type+".png",
				"map":"images/map_icons/"+type+".png",
				"size":{"hd":{"w":32,"h":32}},
				"original_size":{"hd":{"w":32,"h":32}}
			};
		};
		customDecorData[id].data = data;
		var customDecorCallbacks = customDecorData[id].callbacks;
		for (var i=0;i<customDecorCallbacks.length;i++)
			customDecorData[id].callbacks[i](data);
		customDecorCallbacks.length = 0;
		return true;
	});
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
		var localScore = +this.elements["option-localScore"].checked ? 1:0;
		if (!friendly)
			localScore = 0;
		var minPlayers = +this.elements["option-minPlayers"].value;
		var maxPlayers = +this.elements["option-maxPlayers"].value;
		var itemDistrib = JSON.parse(this.elements["option-itemDistrib"].value);
		onProceed({
			team: team,
			manualTeams: manualTeams,
			friendly: friendly,
			localScore: localScore,
			minPlayers: minPlayers,
			maxPlayers: maxPlayers,
			itemDistrib: itemDistrib
		});
		oScr.innerHTML = "";
		oContainers[0].removeChild(oScr);
	};

	var oScroll = document.createElement("div");
	oScroll.style.height = ((isOnline ? 24:20)*iScreenScale) +"px";
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
	oCheckbox.onchange = function() {
		if (this.checked)
			document.getElementById("option-manualTeams-ctn").style.display = "";
		else
			document.getElementById("option-manualTeams-ctn").style.display = "none";
	}
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
	oCheckbox.onchange = function() {
		if (this.checked && isOnline)
			document.getElementById("option-localScore-ctn").style.display = "";
		else
			document.getElementById("option-localScore-ctn").style.display = "none";
	}
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

	var oTr = document.createElement("tr");
	oTr.id = "option-localScore-ctn";
	if (!gameOptions || !gameOptions.friendly)
		oTr.style.display = "none";
	var oTd = document.createElement("td");
	oTd.style.textAlign = "center";
	oTd.style.width = (iScreenScale*8) +"px";
	var oCheckbox = document.createElement("input");
	oCheckbox.style.position = "relative";
	oCheckbox.style.left = Math.round(iScreenScale*1.5) +"px";
	oCheckbox.style.transform = oCheckbox.style.WebkitTransform = oCheckbox.style.MozTransform = "scale("+ Math.round(iScreenScale/3) +")";
	oCheckbox.id = "option-localScore";
	oCheckbox.name = "option-localScore";
	oCheckbox.type = "checkbox";
	if (gameOptions && gameOptions.friendly && gameOptions.localScore && isOnline)
		oCheckbox.checked = true;
	oTd.appendChild(oCheckbox);
	oTr.appendChild(oTd);

	var oTd = document.createElement("td");
	var oLabel = document.createElement("label");
	oLabel.style.cursor = "pointer";
	oLabel.style.display = "inline-block";
	oLabel.setAttribute("for", "option-localScore");
	var oH1 = document.createElement("h1");
	oH1.style.marginTop = 0;
	oH1.style.marginLeft = Math.round(iScreenScale*1.5) +"px";
	oH1.style.fontSize = (3*iScreenScale) +"px";
	oH1.style.marginBottom = "0px";
	oH1.innerHTML = toLanguage("Local scoring","Classement local");
	oLabel.appendChild(oH1);
	var oDiv = document.createElement("div");
	oDiv.style.paddingLeft = Math.round(iScreenScale*1.5) +"px";
	oDiv.style.fontSize = (2*iScreenScale) +"px";
	oDiv.style.color = "white";
	oDiv.innerHTML = toLanguage("If enabled, an ranking internal to this room will be calculated instead of the classic online mode ranking.", "Si activé, un classement interne à la partie privée sera calculé à la place du classement en ligne classique.");
	oLabel.appendChild(oDiv);
	oTd.appendChild(oLabel);
	oTd.style.paddingBottom = Math.round(iScreenScale*1.5) +"px";
	oTr.appendChild(oTd);
	oTable.appendChild(oTr);

	var oTr = document.createElement("tr");
	if (!isOnline) oTr.style.display = "none";
	var oTd = document.createElement("td");
	oTd.setAttribute("colspan", 2);

	var cDiv = document.createElement("div");
	cDiv.style.display = "flex";
	cDiv.style.flexDirection = "row";
	cDiv.style.alignItems = "center";
	var tDiv = document.createElement("div");
	tDiv.style.paddingLeft = (iScreenScale*3) +"px";
	tDiv.style.paddingRight = (iScreenScale*3) +"px";
	var oLabel = document.createElement("label");
	oLabel.style.cursor = "pointer";
	oLabel.setAttribute("for", "option-minPlayers");

	var oH1 = document.createElement("h1");
	oH1.style.fontSize = (3*iScreenScale) +"px";
	oH1.innerHTML = toLanguage("Minimum number of players", "Nombre de joueurs minimum");
	oH1.style.marginTop = iScreenScale +"px";
	oH1.style.marginBottom = "0px";
	oLabel.appendChild(oH1);
	var oDiv = document.createElement("div");
	oDiv.style.fontSize = (2*iScreenScale) +"px";
	oDiv.style.color = "white";
	oDiv.innerHTML = toLanguage("Minimum number of players before the game begins.", "Nombre minimum de joueurs pour que la partie commence");
	oLabel.appendChild(oDiv);
	tDiv.appendChild(oLabel);
	cDiv.appendChild(tDiv);

	var tDiv = document.createElement("div");
	tDiv.style.display = "inline-block";
	var oInput = document.createElement("input");
	oInput.id = "option-minPlayers";
	oInput.name = "option-minPlayers";
	oInput.type = "number";
	oInput.setAttribute("min", 2);
	oInput.setAttribute("max", 99);
	oInput.setAttribute("step", 1);
	oInput.setAttribute("required", true);
	oInput.style.backgroundColor = "#F6F6F6";
	oInput.style.width = (iScreenScale*6) +"px";
	if (gameOptions && gameOptions.minPlayers)
		oInput.value = gameOptions.minPlayers;
	else
		oInput.value = defaultGameOptions.minPlayers;
	oInput.style.fontSize = (iScreenScale*3) +"px";
	oInput.style.marginTop = Math.round(iScreenScale*1.5) +"px";
	tDiv.appendChild(oInput);
	cDiv.appendChild(tDiv);
	oTd.appendChild(cDiv);
	oTr.appendChild(oTd);
	oTable.appendChild(oTr);

	var oTr = document.createElement("tr");
	if (!isOnline) oTr.style.display = "none";
	var oTd = document.createElement("td");
	oTd.setAttribute("colspan", 2);

	var cDiv = document.createElement("div");
	cDiv.style.display = "flex";
	cDiv.style.flexDirection = "row";
	cDiv.style.alignItems = "center";
	var tDiv = document.createElement("div");
	tDiv.style.paddingLeft = (iScreenScale*3) +"px";
	tDiv.style.paddingRight = (iScreenScale*3) +"px";
	var oLabel = document.createElement("label");
	oLabel.style.cursor = "pointer";
	oLabel.setAttribute("for", "option-maxPlayers");

	var oH1 = document.createElement("h1");
	oH1.style.fontSize = (3*iScreenScale) +"px";
	oH1.innerHTML = toLanguage("Maximum number of players", "Nombre de joueurs maximum");
	oH1.style.marginTop = iScreenScale +"px";
	oH1.style.marginBottom = "0px";
	oLabel.appendChild(oH1);
	var oDiv = document.createElement("div");
	oDiv.style.fontSize = (2*iScreenScale) +"px";
	oDiv.style.color = "white";
	oDiv.innerHTML = toLanguage("Maximum number of players that can join the game.", "Nombre maximum de joueurs qui peuvent rejoindre la partie");
	oLabel.appendChild(oDiv);
	tDiv.appendChild(oLabel);
	cDiv.appendChild(tDiv);

	var tDiv = document.createElement("div");
	tDiv.style.display = "inline-block";
	var oInput = document.createElement("input");
	oInput.id = "option-maxPlayers";
	oInput.name = "option-maxPlayers";
	oInput.type = "number";
	oInput.setAttribute("min", 2);
	oInput.setAttribute("max", 99);
	oInput.setAttribute("step", 1);
	oInput.setAttribute("required", true);
	oInput.style.backgroundColor = "#F6F6F6";
	oInput.style.width = (iScreenScale*6) +"px";
	if (gameOptions && gameOptions.maxPlayers)
		oInput.value = gameOptions.maxPlayers;
	else
		oInput.value = defaultGameOptions.maxPlayers;
	oInput.style.fontSize = (iScreenScale*3) +"px";
	oInput.style.marginTop = Math.round(iScreenScale*1.5) +"px";
	tDiv.appendChild(oInput);
	cDiv.appendChild(tDiv);
	oTd.appendChild(cDiv);
	oTr.appendChild(oTd);
	oTable.appendChild(oTr);
	
	var oTr = document.createElement("tr");
	if (!isOnline) oTr.style.display = "none";
	var oTd = document.createElement("td");
	oTd.setAttribute("colspan", 2);

	var cDiv = document.createElement("div");
	cDiv.style.display = "flex";
	cDiv.style.flexDirection = "row";
	cDiv.style.alignItems = "center";
	var tDiv = document.createElement("div");
	tDiv.style.paddingLeft = (iScreenScale*3) +"px";
	tDiv.style.paddingRight = (iScreenScale*3) +"px";
	var oLabel = document.createElement("label");
	oLabel.style.cursor = "pointer";
	oLabel.setAttribute("for", "option-itemDistrib");

	var oH1 = document.createElement("h1");
	oH1.style.fontSize = (3*iScreenScale) +"px";
	oH1.innerHTML = toLanguage("Item distribution", "Distribution des objets");
	oH1.style.marginTop = iScreenScale +"px";
	oH1.style.marginBottom = "0px";
	oLabel.appendChild(oH1);
	var oDiv = document.createElement("div");
	oDiv.style.fontSize = (2*iScreenScale) +"px";
	oDiv.style.color = "white";
	oDiv.innerHTML = toLanguage("", "");
	oLabel.appendChild(oDiv);
	tDiv.appendChild(oLabel);
	cDiv.appendChild(tDiv);

	var tDiv = document.createElement("div");
	tDiv.style.display = "inline-block";
	var oSelect = document.createElement("select");
	oSelect.id = "option-itemDistrib";
	oSelect.name = "option-itemDistrib";
	oSelect.style.backgroundColor = "black";
	oSelect.style.width = (iScreenScale*24) +"px";
	oSelect.style.fontSize = Math.round(iScreenScale*2.5) +"px";
	oSelect.style.marginTop = Math.round(iScreenScale*1.5) +"px";

	var itemMode = getItemMode();
	for (var i=0;i<itemDistributions[itemMode].length;i++) {
		var oOption = document.createElement("option");
		oOption.value = i;
		oOption.innerHTML = itemDistributions[itemMode][i].name;
		oSelect.appendChild(oOption);
	}
	for (var i=0;i<customItemDistrib[itemMode].length;i++) {
		var oOption = document.createElement("option");
		oOption.value = JSON.stringify(customItemDistrib[itemMode][i].value);
		oOption.innerHTML = customItemDistrib[itemMode][i].name;
		oSelect.appendChild(oOption);
	}
	if (gameOptions && gameOptions.itemDistrib) {
		var oValue = JSON.stringify(gameOptions.itemDistrib);
		oSelect.value = oValue;
		if (oSelect.value !== oValue) {
			var oOption = document.createElement("option");
			oOption.value = oValue;
			oOption.innerHTML = toLanguage("Custom", "Personnalisé");
			oSelect.insertBefore(oOption, oSelect.firstChild);
			oSelect.selectedIndex = 0;
		}
	}
	var oOption = document.createElement("option");
	oOption.value = -1;
	oOption.innerHTML = toLanguage("Custom...", "Personnalisé...");
	oSelect.appendChild(oOption);
	oSelect.currentValue = oSelect.value;
	oSelect.onchange = function() {
		if (this.value == -1) {
			this.value = this.currentValue;
			if (isNaN(this.currentValue))
				selectedItemDistrib = JSON.parse(this.currentValue);
			else
				selectedItemDistrib = itemDistributions[itemMode][this.currentValue];
			var that = this;
			selectItemScreen(oScr, function(newDistribution) {
				var firstOption = that.querySelector("option");
				if (!isNaN(firstOption.value)) {
					firstOption = document.createElement("option");
					firstOption.innerHTML = toLanguage("Custom", "Personnalisé");
					that.insertBefore(firstOption, that.firstChild);
				}
				firstOption.value = JSON.stringify(newDistribution.value);
				that.selectedIndex = 0;
				that.currentValue = that.value;
			}, {untitled: true});
		}
		else
			this.currentValue = this.value;
	}
	tDiv.appendChild(oSelect);
	cDiv.appendChild(tDiv);
	oTd.appendChild(cDiv);
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
	var oScr = document.createElement("div");

	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";

	var oTitle = new Image();
	oTitle.src = "images/mariokart.png";
	oTitle.style.position = "absolute";
	oTitle.style.width = Math.round(42.5*iScreenScale)+"px";
	oTitle.style.height = (5*iScreenScale)+"px";
	oTitle.style.left = Math.round((iWidth-42.5)/2*iScreenScale)+"px";
	oTitle.style.top = (iScreenScale*2)+"px";
	oScr.appendChild(oTitle);

	var oStyle = oScr.style;

	oStyle.width = (iWidth*iScreenScale)+"px";
	oStyle.height = (iHeight*iScreenScale)+"px";
	oStyle.border = "solid 1px black";
	oStyle.backgroundColor = "black";
	oContainers[0].appendChild(oScr);

	if (page == "MK") {
		var oButtonsTop = 11;

		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = "Grand Prix";
		oPInput.style.fontSize = (3*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (10*iScreenScale)+"px";
		oPInput.style.top = (oButtonsTop*iScreenScale)+"px";
		oPInput.style.width = (29*iScreenScale)+"px";

		oPInput.onclick = function() {
			course = "GP";
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
		oPInput.style.left = (41*iScreenScale)+"px";
		oPInput.style.top = (oButtonsTop*iScreenScale)+"px";
		oPInput.style.width = (29*iScreenScale)+"px";
		oPInput.onclick = function() {
			course = "CM";
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
		oPInput.style.left = iScreenScale +"px";
		oPInput.style.top = ((oButtonsTop+8)*iScreenScale)+"px";
		oPInput.style.width = (29*iScreenScale)+"px";

		oPInput.onclick = function() {
			course = "VS";
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
		oPInput.style.top = ((oButtonsTop+8)*iScreenScale)+"px";
		oPInput.style.width = (29*iScreenScale)+"px";
		oPInput.onclick = function() {
			course = "BB";
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
		oPInput.style.top = ((oButtonsTop+16)*iScreenScale)+"px";
		oPInput.style.width = (29*iScreenScale)+"px";
		oPInput.onclick = function() {
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
		oPInput.style.left = (41*iScreenScale)+"px";
		oPInput.style.top = ((oButtonsTop+16)*iScreenScale)+"px";
		oPInput.style.width = (29*iScreenScale)+"px";
		oPInput.onclick = function() {
			course = "VS";
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
		oPInput.style.top = (35*iScreenScale + 2)+"px";
		oPInput.onclick = function() {
			document.location.href = "index.php";
		}
		oScr.appendChild(oPInput);
	}
	else {
		var oButtonsTop = 12;

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
				oCup.style.top = Math.round((oButtonsTop+4)*iScreenScale)+"px";
			}
			else {
				oCup.style.left = (5*iScreenScale)+"px";
				oCup.style.top = Math.round(oButtonsTop*iScreenScale)+"px";
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
				oTitle.style.top = (iScreenScale*3)+"px";
				oPInput.style.left = (20*iScreenScale)+"px";
				oPInput.style.top = Math.round((oButtonsTop+i*6.5)*iScreenScale)+"px";
				oPInput.style.width = (38*iScreenScale)+"px";
				oPInput.style.fontSize = Math.round(3.5*iScreenScale)+"px";
			}
			else if (oModes.length == 4) {
				oTitle.style.top = (iScreenScale*4)+"px";
				oPInput.style.left = ((8+(i%2)*36)*iScreenScale)+"px";
				oPInput.style.top = ((oButtonsTop+4+Math.floor(i/2)*8)*iScreenScale)+"px";
				oPInput.style.width = (28*iScreenScale)+"px";
				oPInput.style.fontSize = (3*iScreenScale)+"px";
			}
			else {
				var buttonsPos = [[10,oButtonsTop-1],[40,oButtonsTop-1],[25,oButtonsTop+7],[10,oButtonsTop+15],[40,oButtonsTop+15]];
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
	oSelected.style.border = "solid 1px "+ primaryColor;
	oButton.style.border = "solid 1px transparent";
	oButton.style.cursor = "pointer";
	oButton.onmouseover = function() {
		oButton.style.border = "solid 1px "+ primaryColor;
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

function selectNbJoueurs(force) {
	if (clSelected && !force) {
		selectPlayerScreen(0);
		return;
	}

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
				clSelected = null;
				var oContainer2 = oContainers[0].cloneNode(false);
				oContainer2.style.left = (12+iWidth*iScreenScale)+"px";
				oContainers.push(oContainer2);
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

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = language ? "VS mode":"Course VS";
	oPInput.style.fontSize = Math.round(3.5*iScreenScale)+"px";
	oPInput.style.position = "absolute";
	oPInput.style.left = (22*iScreenScale)+"px";
	oPInput.style.top = (17*iScreenScale)+"px";
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
	oPInput.style.top = (25*iScreenScale)+"px";
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
	var force = (IdJ === -1);
	if (force) IdJ = 0;
	if (!IdJ) {
		strPlayer = [];
		aPlayers = [];
		for (joueurs in cp)
			aPlayers.push(joueurs);
		updateCommandSheet();
	}
	var itemMode = getItemMode();
	var modeItemDistributions = itemDistributions[itemMode].concat(customItemDistrib[itemMode]);
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

	var shrinkAll = (!isOnline && ((course == "VS") || (course == "BB")) && !clSelected);

	var oTitle;
	if (isCustomSel) {
		var oMsg;
		if (IdJ >= oContainers.length)
			oMsg = toLanguage("Choose CPU", "Choisissez ordi") + " "+ (IdJ+1-oContainers.length);
		else if (oContainers.length == 1)
			oMsg = toLanguage("Choose player", "Choisissez joueur");
		else
			oMsg = toLanguage("Choose player ", "Choisissez joueur ") + (IdJ+1);
		oTitle = toTitle(oMsg, -1);
		oTitle.style.color = "#F90";
	}
	else
		oTitle = toTitle(toLanguage("Choose a player", "Choisissez un joueur"), -1);
	if (shrinkAll)
		oTitle.style.fontSize = Math.round(7.5*iScreenScale)+"px";
	oScr.appendChild(oTitle);

	var baseY = shrinkAll ? 8:10;
	
	var cTable = document.createElement("table");
	cTable.style.display = "none";
	cTable.style.position = "absolute";
	cTable.style.zIndex = 2;
	cTable.style.top = (36*iScreenScale+16)+"px";
	cTable.style.left = (25*iScreenScale-60)+"px";
	cTable.style.textAlign = "left";
	cTable.style.fontSize = 2*iScreenScale+"px";
	cTable.style.color = "white";
	cTable.style.backgroundColor = "black";
	cTable.style.backgroundColor = "rgba(0,0,0, 0.8)";
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
	
	var sCaracteristiques = [toLanguage("Acceleration", "Accélération"), toLanguage("Max speed", "Vitesse max"), toLanguage("Handling", "Maniabilité"), toLanguage("Weight", "Poids")];
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
	var jScreenScale = iScreenScale;
	var oItemSelect;
	function tourner(kart) {
		var size = Math.round(jScreenScale*5*(kart.naturalWidth/768));
		var rotation = parseFloat(kart.style.left);
		if (rotation > -21*size)
			kart.style.left = (rotation - size) +"px";
		else if (kart.naturalWidth) {
			var cWidth = Math.min(5*kart.naturalWidth/768,5.8);
			kart.style.left = -Math.round(jScreenScale*(5*kart.naturalWidth/768-cWidth)/2) +"px";
		}
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
		oDiv.style.width = (5 * jScreenScale) + "px";
		oDiv.style.height = (5 * jScreenScale) + "px";
		oDiv.style.borderTop = "double 4px black"; 
		oDiv.style.borderLeft = "double 4px #F8F8F8"; 
		oDiv.style.borderRight = "double 4px #F8F8F8"; 
		oDiv.style.borderBottom = "solid 5px #00B800";

		var cDiv = document.createElement("div");
		cDiv.style.position = "absolute";
		cDiv.style.display = "inline-block";
		cDiv.style.width = (5 * jScreenScale) + "px";
		cDiv.style.height = (5 * jScreenScale) + "px";
		cDiv.style.overflow = "hidden";

		var oPImg = new Image();
		oPImg.style.height = (5 * jScreenScale) +"px";
		oPImg.style.position = "absolute";
		oPImg.className = "pixelated";
		if (pUnlocked[i]) {
			var libre = true;
			var oJoueur = aPlayers[i];
			for (var j=0;j<strPlayer.length;j++) {
				if (strPlayer[j] == oJoueur) {
					libre = false;
					break;
				}
			}
			oPImg.src = (libre ? getSpriteSrc(oJoueur):getStarSrc(oJoueur));
			oPImg.alt = aPlayers[i];
			oPImg.nb = i;
			oPImg.style.left = -(30 * jScreenScale) +"px";
			oPImg.style.cursor = "pointer";
			oPImg.id = "perso-selector-"+oJoueur;
			oPImg.j = IdJ;
			oPImg.onload = function() {
				var cWidth = Math.min(5*this.naturalWidth/768,5.8);
				var cHeight = Math.min(5*this.naturalHeight/32,5.8);
				cDiv.style.width = Math.round(cWidth*jScreenScale)+"px";
				cDiv.style.height = Math.round(cHeight*jScreenScale)+"px";
				this.style.width = (24*Math.round(5*this.naturalWidth/768*jScreenScale))+"px";
				this.style.height = Math.round(5*this.naturalHeight/32*jScreenScale)+"px";
				this.style.left = -Math.round(6*Math.round(5*jScreenScale*this.naturalWidth/768) + jScreenScale*(5*this.naturalWidth/768-cWidth)/2) +"px";
				this.style.top = Math.round((cHeight-5*this.naturalHeight/32)*jScreenScale/2)+"px";
				cDiv.style.left = Math.round((5-cWidth)/2*jScreenScale)+"px";
				cDiv.style.top = Math.round(1+(5-cHeight)/2*jScreenScale)+"px";
			}
			cDiv.onmouseover = function() {
				cTable.style.display = "block";
				var cImg = cDiv.firstChild;
				hTd2.innerHTML = toPerso(cImg.alt);
				for (var i=0;i<dCaracteristiques.length;i++)
					dCaracteristiques[i].style.width = (5*iScreenScale)*(cp[cImg.alt][i]*8+1)+"px";
				clearTimeout(rotateHandler);
				tourner(cImg);
			}
			cDiv.onmouseout = function() {
				cTable.style.display = "none";
				var cImg = cDiv.firstChild;
				if (cImg.naturalWidth) {
					var cWidth = Math.min(5*cImg.naturalWidth/768,5.8);
					cImg.style.left = -Math.round(6*Math.round(5*jScreenScale*cImg.naturalWidth/768) + jScreenScale*(5*cImg.naturalWidth/768-cWidth)/2) +"px";
				}
				else
					cImg.style.left = -(30 * jScreenScale) +"px";
				clearTimeout(rotateHandler);
			}
			cDiv.onclick = function() {
				clearTimeout(rotateHandler);
				var cImg = cDiv.firstChild;
				strPlayer[cImg.j] = cImg.alt;
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
				cImg.j++;
				oContainers[0].removeChild(oScr);
				document.body.removeChild(cTable);
				addMyPersos = function(){};
				if (oItemSelect) {
					selectedItemDistrib = modeItemDistributions[oItemSelect.value].value;
					localStorage.setItem("itemset."+itemMode, +oItemSelect.value);
				}
				else
					selectedItemDistrib = modeItemDistributions[0].value;
				if (cImg.j == (isCustomSel ? nbSels:oContainers.length)) {
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
											break;
										}
									}
								}
							}
							var oSuppr = (course!="GP") ? aPlayers.length-fInfos.nbPlayers+strPlayer.length:aPlayers.length-7;
							aPlayers.splice(0,oSuppr);
						}
						aPlaces = [];
						resetScores();
						if (course != "GP") {
							selectedPlayers = fInfos.nbPlayers;
							selectedTeams = fInfos.teams;
							xhr("updateCourseOptions.php", newOptions, function(reponse) {
								return (reponse == 1);
							});
						}
					}
					if (isOnline) {
						var shownOptions = {};
						var autoAcceptedRules = {
							minPlayers:1,
							maxPlayers:1,
							localScore:1,
							friendly:1,
						};
						for (var key in shareLink.options) {
							if (!autoAcceptedRules[key])
								shownOptions[key] = shareLink.options[key];
						}
						if (isCustomOptions(shownOptions) && !shareLink.accepted && (shareLink.player != identifiant))
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
					selectPlayerScreen(cImg.j,undefined,nbSels);
				var cpId = /^cp-\w+-(\d+)$/g.exec(cImg.alt);
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
		oDiv.style.left = Math.round((7.2*(i%8)+8) * iScreenScale) +"px";
		oDiv.style.top = ((baseY+Math.floor(i/8)*7)*iScreenScale - 8)+"px";
		oScr.appendChild(oDiv);
	}
	var pDiv = document.createElement("div");
	pDiv.style.backgroundColor = "#78D0F8";
	pDiv.style.position = "absolute";
	pDiv.style.width = (5 * iScreenScale) + "px";
	pDiv.style.height = (5 * iScreenScale) + "px";
	pDiv.style.left = (67 * iScreenScale) +"px";
	pDiv.style.top = ((baseY+14) * iScreenScale - 8)+"px";
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
		var aDeconnexion = document.createElement("a");
		aDeconnexion.style.color = "white";
		aDeconnexion.style.fontSize = (iScreenScale*2) +"px";
		aDeconnexion.style.position = "absolute";
		aDeconnexion.style.left = (iScreenScale*24) +"px";
		aDeconnexion.style.top = (iScreenScale*34) +"px";
		aDeconnexion.innerHTML = toLanguage("Deconnection", "Déconnexion");
		aDeconnexion.href = "#null";
		aDeconnexion.onclick = function() {
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			document.body.removeChild(cTable);
			displayCommands();
			mPseudo = "";
			mCode = "";
			connexion();
			xhr('deco.php', null, function() {
				return true;
			});
			return false;
		};
		oScr.appendChild(aDeconnexion);
		
		var eClassement = document.createElement("a");
		eClassement.style.fontSize = (iScreenScale*2) +"px";
		eClassement.style.position = "absolute";
		eClassement.style.left = (iScreenScale*41) +"px";
		eClassement.style.top = (iScreenScale*34) +"px";
		eClassement.innerHTML = toLanguage("Ranking", "Classement");
		if (shareLink.options && shareLink.options.localScore) {
			eClassement.title = toLanguage("Private game ranking","Classement partie privée");
			eClassement.style.color = "#CF8";
			eClassement.setAttribute("href", "localscores.php"+window.location.search);
		}
		else {
			eClassement.style.color = "white";
			eClassement.setAttribute("href", "bestscores.php" + ((course=="BB")?"?battle":""));
		}
		oScr.appendChild(eClassement);

		if (shareLink.key) {
			if (shareLink.player == identifiant) {
				var oPInput = document.createElement("input");
				oPInput.type = "button";
				oPInput.value = toLanguage("Private game options...", "Options partie privée...");
				oPInput.style.fontSize = (2*iScreenScale)+"px";
				oPInput.style.position = "absolute";
				oPInput.style.left = (56*iScreenScale)+"px";
				oPInput.style.top = (34*iScreenScale)+"px";
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
								shareLink.options.localScore = options.localScore;
								shareLink.options.friendly = options.friendly;
								shareLink.options.minPlayers = options.minPlayers;
								shareLink.options.maxPlayers = options.maxPlayers;
								shareLink.options.itemDistrib = options.itemDistrib;
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
			oPInput.style.top = (34*iScreenScale)+"px";
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
		oForm.style.top = ((baseY+21)*iScreenScale-5) +"px";
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
			iDificulty = selectedDifficulty;
			selectedPlayers = fInfos.nbPlayers;
			selectedTeams = fInfos.teams;
			if (oItemSelect)
				localStorage.setItem("itemset."+itemMode, +oItemSelect.value);
			clearTimeout(rotateHandler);
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			selectPlayerScreen(IdJ,false,fInfos.nbPlayers);
			return false;
		};
		oForm.appendChild(oChoosePerso);

		if (!clSelected) {
			oForm.appendChild(document.createElement("br"));
			oForm.appendChild(document.createTextNode(toLanguage("Items", "Objets ")+ ": "));

			oItemSelect = document.createElement("select");
			oItemSelect.name = "item";
			oItemSelect.style.width = (iScreenScale*16) +"px";
			oItemSelect.style.fontSize = iScreenScale*2 +"px";
			for (var i=0;i<modeItemDistributions.length;i++) {
				var oItemOption = document.createElement("option");
				oItemOption.value = i;
				oItemOption.innerHTML = modeItemDistributions[i].name;
				oItemSelect.appendChild(oItemOption);
			}
			var oItemOption = document.createElement("option");
			oItemOption.value = -1;
			oItemOption.innerHTML = toLanguage("Custom...", "Personnalisé...");
			oItemSelect.appendChild(oItemOption);
			oItemSelect.currentValue = oItemSelect.value;
			oItemSelect.onchange = function() {
				if (this.value == -1) {
					this.value = this.currentValue;
					selectedItemDistrib = modeItemDistributions[this.currentValue].value;
					selectItemScreen(oScr, function(newDistribution) {
						customItemDistrib[itemMode].push(newDistribution);
						localStorage.setItem("itemsets", JSON.stringify(customItemDistrib));
						localStorage.setItem("itemset."+itemMode, modeItemDistributions.length);
						oScr.innerHTML = "";
						oContainers[0].removeChild(oScr);
						selectPlayerScreen(IdJ,newP,nbSels);
					});
				}
				else {
					this.currentValue = this.value;
					selectedItemDistrib = modeItemDistributions[this.value].value;
					oItemCustomActions.style.display = (this.value >= itemDistributions[itemMode].length) ? "inline-block" : "none";
				}
			}
			oForm.appendChild(oItemSelect);

			var oItemCustomActions = document.createElement("div");
			oItemCustomActions.style.display = "none";
			oItemCustomActions.style.marginLeft = (iScreenScale*1) +"px";

			var oItemCustomEdit = document.createElement("input");
			oItemCustomEdit.type = "button";
			oItemCustomEdit.style.backgroundColor = "rgb(51, 160, 51)";
			oItemCustomEdit.style.color = "white";
			oItemCustomEdit.style.fontSize = (iScreenScale*2) +"px";
			oItemCustomEdit.value = "\u270E";
			oItemCustomEdit.onclick = function() {
				selectItemScreen(oScr, function(newDistribution) {
					customItemDistrib[itemMode][oItemSelect.value-itemDistributions[itemMode].length] = newDistribution;
					localStorage.setItem("itemsets", JSON.stringify(customItemDistrib));
					localStorage.setItem("itemset."+itemMode, +oItemSelect.value);
					oScr.innerHTML = "";
					oContainers[0].removeChild(oScr);
					selectPlayerScreen(IdJ,newP,nbSels);
				}, modeItemDistributions[oItemSelect.value]);
			};
			oItemCustomActions.appendChild(oItemCustomEdit);

			var oItemCustomDel = document.createElement("input");
			oItemCustomDel.type = "button";
			oItemCustomDel.style.marginLeft = Math.round(iScreenScale*0.5) +"px";
			oItemCustomDel.style.backgroundColor = "rgb(204, 51, 51)";
			oItemCustomDel.style.color = "white";
			oItemCustomDel.value = "\xD7";
			oItemCustomDel.style.fontSize = (iScreenScale*2) +"px";
			oItemCustomDel.onclick = function() {
				var itemSetName = modeItemDistributions[oItemSelect.value].name;
				if (confirm(toLanguage('Delete item set "'+ itemSetName +'"?', 'Supprimer le set "'+ itemSetName +'" ?'))) {
					customItemDistrib[itemMode].splice(oItemSelect.value-itemDistributions[itemMode].length, 1);
					localStorage.setItem("itemsets", JSON.stringify(customItemDistrib));
					localStorage.setItem("itemset."+itemMode, 0);
					oScr.innerHTML = "";
					oContainers[0].removeChild(oScr);
					selectPlayerScreen(IdJ,newP,nbSels);
				}
			}
			oItemCustomActions.appendChild(oItemCustomDel);
			
			oForm.appendChild(oItemCustomActions);

			var selectedItemSetId = localStorage.getItem("itemset."+itemMode);
			if (selectedItemSetId) {
				oItemSelect.selectedIndex = selectedItemSetId;
				oItemSelect.onchange();
			}
		}
		
		oScr.appendChild(oForm);

		if (isCustomSel) {
			oForm.style.display = "none";
			var oStepCtn = document.createElement("div");
			oStepCtn.style.position = "absolute";
			oStepCtn.style.left = "0px";
			oStepCtn.style.width = (iWidth*iScreenScale) +"px";
			oStepCtn.style.textAlign = "center";
			oStepCtn.style.top = ((baseY+22)*iScreenScale) +"px";
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
	if (isOnline)
		oPInput.style.top = (34*iScreenScale)+"px";
	oPInput.onclick = function() {
		oScr.innerHTML = "";
		oContainers[0].removeChild(oScr);
		document.body.removeChild(cTable);
		if (isCustomSel)
			selectPlayerScreen(0);
		else {
			displayCommands();
			if (isOnline)
				quitter();
			else if (course == "VS" || course == "BB") {
				for (var i=1;i<oContainers.length;i++)
					oContainers.splice(i,1);
				selectNbJoueurs(true);
			}
			else
				selectTypeScreen();
		}
	}
	if (isCustomSel)
		oPInput.style.color = "#F90";
	oScr.appendChild(oPInput);

	if (clSelected && clSelected.autoset && clSelected.autoset.selectedPerso && !force) {
		var persoSelector = document.getElementById("perso-selector-"+clSelected.autoset.selectedPerso);
		if (persoSelector && persoSelector.parentNode && persoSelector.parentNode.onclick) {
			persoSelector.parentNode.onclick();
			return;
		}
	}

	function addMyPersos(newPersos) {
		var lastCp = cp;
		cp = {};
		for (var joueurs in baseCp)
			cp[joueurs] = baseCp[joueurs];
		customPersos = {};
		for (var i=0;i<newPersos.length;i++) {
			var newPerso = newPersos[i];
			cp[newPerso["sprites"]] = [newPerso["acceleration"],newPerso["speed"],newPerso["handling"],newPerso["mass"]];
			customPersos[newPerso["sprites"]] = newPerso;
		}
		aPlayers = [];
		for (joueurs in cp)
			aPlayers.push(joueurs);
		for (var joueurs in lastCp) {
			if (!cp[joueurs])
				cp[joueurs] = lastCp[joueurs];
		}
		for (var i=0;i<newPersos.length;i++) {
			var inc = nBasePersos+i;
			pUnlocked[inc] = 1;
			var oDiv = createPersoSelector(inc);
			if (newP && !i && oDiv.firstChild.onclick) {
				oDiv.firstChild.onclick();
				return;
			}
			oDiv.style.left = 67*iScreenScale +"px";
			oDiv.style.top = ((baseY+i*7)*iScreenScale - 8)+"px";
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
function selectItemScreen(oScr, callback, options) {
	options = options || {};
	var oScr2 = document.createElement("div");
	oScr2.style.position = "absolute";
	oScr2.style.width = "100%";
	oScr2.style.height = "100%";
	oScr2.style.left = "0px";
	oScr2.style.top = "0px";
	oScr2.style.zIndex = 2;
	oScr2.style.backgroundColor = "#000";

	var oBorder = "solid 1px yellow";

	var oTableItems = document.createElement("table");
	oTableItems.style.color = "white";
	oTableItems.style.textAlign = "center";
	oTableItems.style.position = "absolute";
	oTableItems.style.left = (iScreenScale*5) +"px";
	oTableItems.setAttribute("cellpadding", 0);
	oTableItems.setAttribute("cellspacing", 0);
	var itemMode = getItemMode();
	var itemDistribution0 = itemDistributions[itemMode][0].value;
	var possibleItems = {};
	for (var i=0;i<itemDistribution0.length;i++) {
		for (var item in itemDistribution0[i])
			possibleItems[item] = 1;
	}
	var currentDistribution = selectedItemDistrib;
	if (!currentDistribution.length)
		currentDistribution = itemDistribution0;
	possibleItems = Object.keys(possibleItems);
	var oTr = document.createElement("tr");
	oTr.appendChild(document.createElement("td"));
	for (var i=0;i<possibleItems.length;i++) {
		var oTd = document.createElement("td");
		oTd.style.paddingBottom = Math.round(iScreenScale/2) +"px";
		var oImg = document.createElement("img");
		oImg.src = "images/items/"+possibleItems[i]+".png";
		oImg.alt = possibleItems[i];
		oImg.style.width = (iScreenScale*2) +"px";
		oTd.appendChild(oImg);
		oTr.appendChild(oTd);
	}
	oTableItems.appendChild(oTr);
	for (var i=0;i<currentDistribution.length;i++) {
		var oTr = document.createElement("tr");
		var oTd = document.createElement("td");
		oTd.style.paddingRight = (iScreenScale) +"px";
		oTd.style.fontSize = (iScreenScale*2) +"px";
		oTd.innerHTML = "#"+(i+1);
		oTr.appendChild(oTd);
		var jDistribution = currentDistribution[i];
		for (var j=0;j<possibleItems.length;j++) {
			var itemName = possibleItems[j];
			var oTd = document.createElement("td");
			oTd.style.padding = "0px";
			var oInput = document.createElement("input");
			oInput.type = "number";
			oInput.value = jDistribution[itemName] || "";
			oInput.style.width = Math.round(iScreenScale*3.5) +"px";
			oInput.className = "noarrow";
			oInput.style.backgroundColor = "black";
			oInput.style.color = "white";
			oInput.style.textAlign = "center";
			oInput.style.border = "solid 1px white";
			oInput.style.fontSize = Math.round(iScreenScale*1.8) +"px";
			oInput.style.maxHeight = Math.round(iScreenScale*2.5+1) +"px";
			oInput.setAttribute("min", 0);
			oInput.setAttribute("max", 99);
			oInput.setAttribute("step", 1);
			oInput.setAttribute("form", "iten-distribution-form");
			oInput.disabled = options.readOnly;
			oTd.style.padding = "0px";
			oTd.appendChild(oInput);
			oTr.appendChild(oTd);
		}
		oTableItems.appendChild(oTr);
	}
	oScr2.appendChild(oTableItems);

	var oPInput = document.createElement("input");
	oPInput.type = "button";
	oPInput.value = toLanguage("Back", "Retour");
	oPInput.style.position = "absolute";
	oPInput.style.left = (2*iScreenScale)+"px";
	oPInput.style.top = (36*iScreenScale)+"px";
	oPInput.style.fontSize = (2*iScreenScale)+"px";
	oPInput.onclick = function() {
		oScr.removeChild(oScr2);
	}
	oScr2.appendChild(oPInput);

	function getDistributionValue(checkValidity) {
		var res = [];
		var oInputs = oTableItems.getElementsByTagName("input");
		var inc = 0;
		for (var i=0;i<currentDistribution.length;i++) {
			var iDistrib = {};
			var isOneItem = false;
			for (var j=0;j<possibleItems.length;j++) {
				var oInput = oInputs[inc];
				if (oInput.value > 0) {
					iDistrib[possibleItems[j]] = +oInput.value;
					isOneItem = true;
				}
				inc++;
			}
			if (checkValidity && !isOneItem) {
				alert(toLanguage("You must enter at least 1 item for rank #" + (i+1), "Vous devez spécifier au moins 1 objet pour la position #" + (i+1)));
				return null;
			}
			res.push(iDistrib);
		}
		return res;
	}

	var oSetName = document.createElement("form");
	oSetName.style.position = "absolute";
	oSetName.id = "iten-distribution-form";
	oSetName.style.right = (5*iScreenScale)+"px";
	oSetName.style.top = (35*iScreenScale+4)+"px";
	oSetName.style.fontSize = Math.round(iScreenScale*2.5) +"px";
	if (!options.untitled)
		oSetName.innerHTML = toLanguage("Set name:&nbsp;","Nom du set :&nbsp;");
	oSetName.onsubmit = function() {
		var newDistribution = {
			"name": oNInput.value,
			"value": getDistributionValue(true)
		};
		if (!newDistribution.value)
			return false;
		oScr.removeChild(oScr2);
		try {
			callback(newDistribution);
		}
		catch (e) {
			console.error(e);
		}
		return false;
	}
	var oNInput = document.createElement("input");
	oNInput.style.width = (iScreenScale*15) +"px";
	oNInput.style.backgroundColor = "black";
	oNInput.style.color = "white";
	oNInput.style.border = "solid 1px white";
	oNInput.type = "text";
	oNInput.setAttribute("required", true);
	oNInput.style.fontSize = (iScreenScale*2) +"px";
	if (options.name)
		oNInput.value = options.name;
	else {
		var distribNames = {};
		var modeItemDistrib = customItemDistrib[itemMode];
		for (var i=0;i<modeItemDistrib.length;i++)
			distribNames[modeItemDistrib[i].name] = 1;
		var d;
		for (d=1;distribNames["Distribution "+d];d++);
		oNInput.value = "Distribution "+ d;
	}
	if (options.untitled)
		oNInput.style.display = "none";
	oSetName.appendChild(oNInput);
	var oVInput = document.createElement("input");
	oVInput.type = "submit";
	oVInput.style.fontSize = (iScreenScale*2) +"px";
	oVInput.value = toLanguage("Validate!","Valider !");
	oVInput.style.marginLeft = iScreenScale +"px";
	oSetName.appendChild(oVInput);

	var oLink = document.createElement("a");
	oLink.href = "#null";
	oLink.style.color = "#CCF";
	oLink.innerHTML = toLanguage("Export/Import...", "Exporter/importer");
	oLink.style.fontSize = Math.round(iScreenScale*1.4) +"px";
	oLink.style.marginLeft = (2*iScreenScale)+"px";
	oLink.style.position = "relative";
	oLink.style.top = -Math.round(iScreenScale/2)+"px";
	oLink.onclick = function(e) {
		e.preventDefault();
		var currentDistrib = JSON.stringify(getDistributionValue());
		var importedDistrib = prompt(toLanguage("Export: copy this text to share this distribution with other players.\nImport: paste a new text to import other's distribution.", "Exporter : copiez ce texte pour partager cette distribution avec d'autres joueurs.\nImporter : collez un nouveau texte pour importer la distribution d'un autre."), currentDistrib);
		if (importedDistrib && importedDistrib !== currentDistrib) {
			importedDistrib = JSON.parse(importedDistrib);
			var oInputs = oTableItems.getElementsByTagName("input");
			var inc = 0;
			for (var i=0;i<importedDistrib.length;i++) {
				var jDistribution = importedDistrib[i];
				for (var j=0;j<possibleItems.length;j++) {
					var itemName = possibleItems[j];
					oInputs[inc].value = jDistribution[itemName] || "";
					inc++;
				}
			}
		}
	}
	oSetName.appendChild(oLink);
	
	oScr2.appendChild(oSetName);

	if (options.readOnly)
		oSetName.style.display = "none";

	oScr.appendChild(oScr2);
}
var defaultGameOptions = {
	team: false,
	manualTeams: false,
	localScore: false,
	friendly: false,
	minPlayers: 2,
	maxPlayers: 12,
	itemDistrib: 0
};
function isCustomOptions(linkOptions) {
	if (linkOptions) {
		for (var key in defaultGameOptions) {
			if ((linkOptions[key] !== undefined) && (linkOptions[key] != defaultGameOptions[key]))
				return true;
		}
	}
	return false;
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
		selectPlayerScreen(-1);
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
		selectPlayerScreen(-1);
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

	if (shareLink.options.itemDistrib) {
		var oTr = document.createElement("tr");
		var oTd = document.createElement("td");
		var oLabel = document.createElement("label");
		oLabel.setAttribute("for", "option-friendly");
		oTd.appendChild(oLabel);

		var oH1 = document.createElement("h1");
		oH1.style.fontSize = (3*iScreenScale) +"px";
		oH1.innerHTML = toLanguage("Item distribution", "Distribution des objets");
		oH1.style.marginBottom = "0px";
		oLabel.appendChild(oH1);
		var oDiv = document.createElement("div");
		oDiv.style.fontSize = (2*iScreenScale) +"px";
		oDiv.style.color = "white";
		if (isNaN(shareLink.options.itemDistrib)) {
			oDiv.innerHTML = toLanguage('Custom distribution <a href="#null">[Show]</a>', 'Distribution personnalisée <a href="#null">[Voir]</a>');
			var oLink = oDiv.querySelector("a");
			oLink.style.color = "#CCF";
			oLink.onclick = function() {
				selectedItemDistrib = shareLink.options.itemDistrib;
				selectItemScreen(oScr, function() {}, {
					readOnly: true
				});
				return false;
			}
		}
		else {
			var itemMode = getItemMode();
			var itemDistrib = itemDistributions[itemMode][shareLink.options.itemDistrib];
			if (itemDistrib.value.length)
				oDiv.innerHTML = itemDistrib.name;
			else
				oDiv.innerHTML = toLanguage("No item", "Aucun objet");
		}
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

	var mainClId;

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
				if (creationChallenges.main)
					mainClId = creationChallenges.id;
				else {
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
						oH1.innerText = challenge.name;
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
							$fancyTitle.style.position = "fixed";
							$fancyTitle.style.padding = Math.round(iScreenScale/2)+"px "+iScreenScale+"px";
							$fancyTitle.style.borderRadius = iScreenScale+"px";
							$fancyTitle.style.zIndex = 10;
							$fancyTitle.style.backgroundColor = "rgba(51,160,51, 0.95)";
							$fancyTitle.style.color = "white";
							$fancyTitle.style.fontSize = Math.round(iScreenScale*1.8) +"px";
							$fancyTitle.style.lineHeight = Math.round(iScreenScale*2) +"px";
							$fancyTitle.style.visibility = "hidden";
							$mkScreen.appendChild($fancyTitle);
							var rect = this.getBoundingClientRect();
							$fancyTitle.style.left = Math.round(rect.left + (this.scrollWidth-$fancyTitle.scrollWidth)/2)+"px";
							$fancyTitle.style.top = (rect.top + this.scrollHeight + 5)+"px";
							$fancyTitle.style.visibility = "visible";
						};
						oIcons.onmouseout = function(e) {
							if (!$fancyTitle) return;
							$mkScreen.removeChild($fancyTitle);
							$fancyTitle = undefined;
						};
						oTd.appendChild(oIcons);
					}

					function selectChallenge(challenge,trackId,trackType) {
						oScr.innerHTML = "";
						oContainers[0].removeChild(oScr);
						clSelected = challenge;
						clSelected.trackId = trackId;
						clSelected.trackType = trackType;
						localStorage.removeItem("itemset."+getItemMode());
						xhr("challengeTry.php", "challenge="+challenge.id, function(res) {
							if (!res)
								return false;
							try {
								res = JSON.parse(res);
							}
							catch (e) {
								return false;
							}
							clSelected.autoset = res;
							course = "";
							for (var k in res)
								window[k] = res[k];
							if (course)
								selectPlayerScreen(0);
							else
								selectMainPage();
							delete window.selectedPerso;
							showClSelectedPopup();
							return true;
						});
					}

					if (challenge.succeeded) {
						var oLink = document.createElement("a");
						oLink.href = "#null";
						oLink.innerHTML = toLanguage("Replay","Rejouer");
						oLink.style.color = "white";
						oLink.style.fontSize = Math.round(iScreenScale*1.7) +"px";
						(function(challenge,trackId,trackType) {
							oLink.onclick = function() {
								selectChallenge(challenge,trackId,trackType);
								return false;
							};
						})(challenge,cid,type);
						oTd.appendChild(oLink);
					}
					else {
						var oInput = document.createElement("input");
						oInput.type = "button";
						oInput.value = toLanguage("Take up", "Relever");
						oInput.style.width = (iScreenScale*11) +"px";
						oInput.style.fontSize = Math.round(iScreenScale*2.4) +"px";
						(function(challenge,trackId,trackType) {
							oInput.onclick = function() {
								selectChallenge(challenge,trackId,trackType);
							};
						})(challenge,cid,type);
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

	if (aChallenge) {
		if (myCircuit) {
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
		if (clRewards.length) {
			var oLink = document.createElement("a");
			oLink.href = "persoLocked.php?cl="+mainClId;
			oLink.target = "_blank";
			oLink.style.color = "white";
			oLink.style.textDecoration = "none";
			oLink.onclick = function() {
				window.open(this.href,'persoLock','scrollbars=1, resizable=1, width=500, height=500');
				return false;
			}
			var oImg = document.createElement("img");
			oImg.src = "images/challenges/unlocking.png";
			oImg.alt = toLanguage("Unlocking characters", "Persos à débloquer");
			oImg.style.height = (2*iScreenScale) +"px";
			oImg.style.marginRight = Math.round(0.55*iScreenScale)+"px";
			oImg.style.position = "relative";
			oImg.style.top = Math.round(0.2*iScreenScale)+"px";
			oLink.appendChild(oImg);
			var nbUnlocked = 0;
			if (myCircuit)
				oLink.innerHTML += clRewards.length;
			else {
				for (var i=0;i<clRewards.length;i++) {
					if (clRewards[i].unlocked)
						nbUnlocked++;
				}
				oLink.innerHTML += nbUnlocked+"/"+clRewards.length;
			}
			oLink.style.fontSize = (2*iScreenScale)+"px";
			oLink.style.position = "absolute";
			oLink.style.right = ((myCircuit ? 22:2)*iScreenScale)+"px";
			oLink.style.top = (35*iScreenScale)+"px";
			if (!oLink.dataset)
				oLink.dataset = {};
			var remainingLocks = clRewards.length-nbUnlocked;
			var charsS = (remainingLocks>1) ? "s":"";
			if (remainingLocks <= 0) {
				oLink.dataset.title = toLanguage("All characters unlocked,<br />congratulations!","Tous les persos ont été<br />débloqués, félicitations !");
				oLink.style.color = "#0F8";
				oLink.style.fontWeight = "bold";
			}
			else if (nbUnlocked)
				oLink.dataset.title = toLanguage(remainingLocks, "Plus que " + remainingLocks) + " " + toLanguage("character"+charsS+" left to unlock", "perso"+charsS+" à débloquer");
			else
				oLink.dataset.title = remainingLocks + " " + toLanguage("character"+charsS+" to unlock", "perso"+charsS+" à débloquer");
			var $fancyTitle;
			oLink.onmouseover = function() {
				this.style.opacity = 0.7;
				if ($fancyTitle) return;
				$fancyTitle = document.createElement("div");
				$fancyTitle.className = "ranking_activeplayertitle";
				$fancyTitle.style.textAlign = "center";
				$fancyTitle.innerHTML = this.dataset.title;
				$fancyTitle.style.position = "fixed";
				$fancyTitle.style.padding = Math.round(iScreenScale/2)+"px "+iScreenScale+"px";
				$fancyTitle.style.borderRadius = iScreenScale+"px";
				$fancyTitle.style.zIndex = 10;
				$fancyTitle.style.backgroundColor = "rgba(102,153,160, 0.95)";
				$fancyTitle.style.color = "white";
				$fancyTitle.style.fontSize = Math.round(iScreenScale*1.8) +"px";
				$fancyTitle.style.lineHeight = Math.round(iScreenScale*2) +"px";
				$fancyTitle.style.visibility = "hidden";
				$mkScreen.appendChild($fancyTitle);
				var rect = this.getBoundingClientRect();
				$fancyTitle.style.left = Math.round(rect.left + (this.scrollWidth-$fancyTitle.scrollWidth)/2)+"px";
				$fancyTitle.style.top = (rect.top - $fancyTitle.scrollHeight - 5)+"px";
				$fancyTitle.style.visibility = "visible";
			}
			oLink.onmouseout = function() {
				this.style.opacity = "";
				if (!$fancyTitle) return;
				$mkScreen.removeChild($fancyTitle);
				$fancyTitle = undefined;
			}
			oScr.appendChild(oLink);
		}
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
	
	var oRequiredPlayers = document.createElement("div");
	oRequiredPlayers.style.position = "absolute";
	oRequiredPlayers.style.left = "0px";
	oRequiredPlayers.style.top = Math.round(iScreenScale*17.5) +"px";
	oRequiredPlayers.style.width = (iScreenScale*iWidth) +"px";
	oRequiredPlayers.style.textAlign = "center";
	oRequiredPlayers.style.fontSize = (iScreenScale*2) +"px";
	oRequiredPlayers.style.color = "#0B0";
	oRequiredPlayers.style.display = "none";
	oRequiredPlayers.style.backgroundColor = "rgba(0,0,0, 0.7)";
	oRequiredPlayers.innerHTML = toLanguage('<span id="nb-pending-players" style="color:#0E0"></span> currently waiting, <span id="nb-missing-players" style="color:#0E0"></span> left before the game begins...', '<span id="nb-pending-players" style="color:#0E0"></span> actuellement en attente. Plus que <span id="nb-missing-players" style="color:#0E0"></span> pour que la partie commence');
	
	oScr.appendChild(oRequiredPlayers);
	
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
	else if (noDS)
		courseParams += "nods";
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
							oRequiredPlayers.style.display = "none";
							oActivePlayers.style.display = "block";
						}
						else if (reponse.pending_players) {
							document.getElementById("nb-pending-players").innerHTML = reponse.pending_players +" "+ toLanguage("player","joueur") + (reponse.pending_players>1 ? "s":"");
							var missingPlayers = reponse.min_players-reponse.pending_players;
							if (missingPlayers < 1) missingPlayers = 1;
							document.getElementById("nb-missing-players").innerHTML = missingPlayers +" "+ toLanguage("player","joueur") + (missingPlayers>1 ? "s":"");
							oActivePlayers.style.display = "none";
							oRequiredPlayers.style.display = "block";
						}
						else {
							oActivePlayers.style.display = "none";
							oRequiredPlayers.style.display = "none";
						}
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
			choose(NBCIRCUITS + Math.ceil(Math.random()*12));
	}
	else if (isSingle)
		choose(1);
	else if (isBattle)
		choose(NBCIRCUITS+Math.ceil(Math.random()*12),true);
	else
		choose(Math.ceil(Math.random()*NBCIRCUITS),true);
}

function selectMapScreen(force) {
	if (isOnline) {
		setSRest();
		document.getElementById("waitrace").style.visibility = "visible";
	}
	if ((isCup&&!isMCups) || (isBattle&&isCup)) {
		selectRaceScreen(0);
		return;
	}
	else {
		if (clSelected && !force) {
			switch (clSelected.trackType) {
			case "cup":
				var cupId = cupIDs.indexOf(clSelected.trackId);
				if (cupId !== -1) {
					selectRaceScreen(cupId*4);
					return;
				}
				break;
			case "track":
				var oMapId = aAvailableMaps.find(function(circuitId) {
					var m = oMaps[circuitId];
					var mId = (page === "CI") ? m.id:m.map;
					return (mId == clSelected.trackId);
				});
				if (oMapId) {
					var oMap = oMaps[oMapId];
					var cupId = Math.floor((oMap.ref-1)/4);
					selectRaceScreen(cupId*4);
					return;
				}
				break;
			}
		}
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
			coupes = ["snes","gba","ds"];
			nbcoupes = 3;
		}
		var nb_lines = Math.ceil(nbcoupes/cups_per_line);
		if (cupOpts.lines)
			nb_lines = cupOpts.lines.length;
		cups_per_line = Math.ceil(nbcoupes/nb_lines);
		var max_cups_per_line = cups_per_line;
		if (cupOpts.lines) {
			max_cups_per_line = 0;
			for (var i=0;i<cupOpts.lines.length;i++)
				max_cups_per_line = Math.max(max_cups_per_line,cupOpts.lines[i]);
		}
		var cup_width = Math.min(Math.round(10.5/Math.pow(Math.max(nbcoupes/5,nb_lines,0.5),0.6)),16/nb_lines,60/max_cups_per_line);
		var cup_margin_x = Math.min(4,20/max_cups_per_line), cup_margin_y = (4/nb_lines);
		var cup_offset_x = 1, cup_offset_y = 38;
		if (course == "BB") {
			cup_width = Math.round(cup_width*1.5);
			cup_margin_x = Math.round(cup_margin_x*2);
			cup_offset_y -= 3;
		}
		var currentLine = 0, currentRow = 0;
		for (var i=0;i<nbcoupes;i++) {
			var cup_i, cup_j, cups_in_line;
			if (cupOpts.lines) {
				cups_in_line = cupOpts.lines[currentRow];
				cup_i = currentLine;
				cup_j = currentRow;
				currentLine++;
				if (currentLine >= cupOpts.lines[currentRow]) {
					currentLine = 0;
					currentRow++;
				}
			}
			else {
				cups_in_line = Math.min(cups_per_line, nbcoupes-(i-i%cups_per_line));
				cup_i = i%cups_per_line;
				cup_j = Math.floor(i/cups_per_line);
			}
			var oPImg = document.createElement("img");
			oPImg.className = "pixelated";

			oPImg.style.width = (cup_width * iScreenScale) + "px";
			oPImg.style.height = (cup_width * iScreenScale) + "px";
			oPImg.style.cursor = "pointer";
			oPImg.style.position = "absolute";
			var cup_x = ((iWidth+cup_margin_x+cup_offset_x)/2+((cup_i-cups_in_line/2)*(cup_width+cup_margin_x)));
			var cup_y = ((cup_margin_y+cup_offset_y)/2+((cup_j-nb_lines/2)*(cup_width+cup_margin_y)));
			oPImg.style.left = Math.round(cup_x*iScreenScale)+"px";
			oPImg.style.top = Math.round(cup_y*iScreenScale)+"px";
			if (cupOpts.icons) {
				if (typeof cupOpts.icons[i] === "number")
					oPImg.src = "images/cups/"+ coupes[cupOpts.icons[i]] +".gif";
				else {
					(function(cup_x,cup_y) {
						oPImg.onload = function() {
							if (this.naturalWidth > this.naturalHeight) {
								this.style.width = (cup_width * iScreenScale) + "px";
								this.style.top = Math.round((cup_y + cup_width*(1-this.naturalHeight/this.naturalWidth)/2)*iScreenScale)+"px";
								this.style.height = "auto";
							}
							else {
								this.style.width = "auto";
								this.style.height = (cup_width * iScreenScale) + "px";
								this.style.left = Math.round((cup_x + cup_width*(1-this.naturalWidth/this.naturalHeight)/2)*iScreenScale)+"px";
							}
						}
						oPImg.src = cupOpts.icons[i];
					})(cup_x,cup_y);
				}
			}
			else
				oPImg.src = "images/cups/"+ coupes[i] +".gif";

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
				var labels = [toLanguage("SNES Stages", "Arènes SNES"), toLanguage("GBA Stages", "Arènes GBA"), toLanguage("DS Stages", "Arènes DS")];
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
		return "classement.php?map="+ oMap.map;
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
		$mkScreen.appendChild(oContainers[i]);
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
						selectMapScreen(true);
				}
				else if (!isCup)
					selectMapScreen(true);
				else if (!pause) selectGamersScreen();
				else {removeMenuMusic(false);quitter();}
			}
		}
		oScr.appendChild(oPInput);
			
		var mScreenScale = iScreenScale;

		var trackSelected = clSelected && (clSelected.trackType === "track") ? clSelected.trackId : null;
		var divSelected;
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
			var oMap = oMaps[mDiv.map];
			var mId = (page === "CI") ? oMap.id : oMap.map;
			if (mId == trackSelected)
				divSelected = mDiv;
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

		if (divSelected) {
			divSelected.click();
			return;
		}
	}
	else {
		if (course == "GP") {
			if (page != "MK")
				iDificulty = 5;
			else {
				var cupNb = Math.floor(cup/4)%5;
				if (cup < 40)
					iDificulty = Math.min(4.5+cupNb/7,5);
				else
					iDificulty = Math.min(4.6+cupNb/7,5);
			}
		}
		cup++;
		strMap = "map"+ cup;
		appendContainers();
		resetGame(strMap);
		return;
	}

	updateMenuMusic(1);
}

var startMusicHandler;
function choose(map,rand) {
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
					var isRandom = choixJoueurs[i][3];
					oTd.innerHTML = isChoix ? (isRandom ? "???":lCircuits[isChoix-1]) : toLanguage("Not chosen","Non choisi");
					oTr.appendChild(oTd);
					oTBody.appendChild(oTr);
				}
				if (rCode[1] == -1)
					setTimeout(waitForChoice, 1000);
				else {
					if (choixJoueurs.length >= rCode[4].minPlayers) {
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
								aPlaces.push(choixJoueurs[i][4]);
								aPseudos.push(choixJoueurs[i][5]);
								aTeams.push(choixJoueurs[i][6]);
							}
							else {
								aPlaces.unshift(choixJoueurs[i][4]);
								aPseudos.unshift(choixJoueurs[i][5]);
								aTeams.unshift(choixJoueurs[i][6]);
							}
						}
						selectedTeams = (aTeams.indexOf(-1) == -1);
						if (!selectedTeams)
							aTeams.length = 0;
						if (shareLink.options && shareLink.options.itemDistrib) {
							if (isNaN(shareLink.options.itemDistrib))
								selectedItemDistrib = shareLink.options.itemDistrib;
							else {
								var itemMode = getItemMode();
								selectedItemDistrib = itemDistributions[itemMode][shareLink.options.itemDistrib].value;
							}
						}
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
						//rCode[2] = 0; // TODO remove
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
								setTimeout(function(){$mkScreen.removeChild(oTable);proceedOnlineRaceSelection(rCode)}, 500);
							if (cID == 1)
								trs[cCursor].getElementsByTagName("td")[0].innerHTML = lCircuits[choixJoueurs[cCursor][2]-1];
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
						if (choixJoueurs.length > 1)
							oDiv.innerHTML = toLanguage("Sorry, there are not enough players to begin the race...", "D&eacute;sol&eacute;, il n'y a pas assez de joueurs pour commencer la course...");
						else
							oDiv.innerHTML = toLanguage("Sorry, all your opponents have left the race...", "D&eacute;sol&eacute;, tous vos adversaires ont quitt&eacute; la course...");
						
						oDiv.appendChild(document.createElement("br"));
						
						var nSearch = document.createElement("a");
						nSearch.style.color = "white";
						nSearch.innerHTML = toLanguage("Search for new players", "Rechercher de nouveaux joueurs");
						nSearch.setAttribute("href", "#null");
						nSearch.onclick = function() {
							$mkScreen.removeChild(oTable);
							$mkScreen.removeChild(oDiv);
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
						
						$mkScreen.appendChild(oDiv);
						
						if (choixJoueurs.length <= 1)
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
	xhr("chooseMap.php", "joueur="+strPlayer+"&map="+map+(course=="BB"?"&battle":"")+(rand?"&rand":""), refreshTab);
	function waitForChoice() {
		xhr("getMap.php", (course=="BB"?"battle":""), refreshTab);
	}
	oTable.appendChild(oTBody);
	$mkScreen.appendChild(oTable);
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
					oPlayerName.innerHTML = player[5];
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
			playersTeams[choosedTeams[i].id][6] = choosedTeams[i].team;
		for (var i=0;i<strPlayer.length;i++)
			aTeams[i] = playersTeams[identifiant][6];
		for (var i=0;i<aPlayers.length;i++) {
			var id = aIDs[i];
			var inc = i+strPlayer.length;
			aTeams[inc] = playersTeams[id][6];
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
			$mkScreen.removeChild(oDiv);
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
		
		$mkScreen.appendChild(oDiv);

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
		teams[choixJoueurs[i][6]].push(choixJoueurs[i]);

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
	$mkScreen.appendChild(oDiv);
	chatting = false;
	window.onbeforeunload = undefined;
}

var dRestHandlers = {};
function dRest(type) {
	if (!type) type = "race";
	if (isOnline) {
		var tRest = document.getElementById(type+"countdown").innerHTML - 1;
		document.getElementById(type+"countdown").innerHTML = tRest;
		if (tRest && (document.getElementById("wait"+type).style.visibility == "visible")) {
			if (!dRestHandlers[type])
				dRestHandlers[type] = setInterval(function(){dRest(type)}, 1000);
			return;
		}
	}
	if (dRestHandlers[type]) {
		clearTimeout(dRestHandlers[type]);
		delete dRestHandlers[type];
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
	oTable.style.width = (iScreenScale*35) +"px";
	
	var oGhost = document.createElement("tr");
	var oPersoImage = document.createElement("td");
	oPersoImage.style.width = (5 * iScreenScale) + "px";
	oPersoImage.style.paddingRight = "6px";
	
	var cDiv = document.createElement("div");
	cDiv.style.textAlign = "center";
	var oDiv = document.createElement("div");
	oDiv.style.position = "relative";
	oDiv.style.width = (5 * iScreenScale) + "px";
	oDiv.style.height = (5 * iScreenScale) + "px";
	oDiv.style.marginLeft = "auto";
	oDiv.style.marginRight = "auto";
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
	cDiv.appendChild(oDiv);
	
	var oSpan = document.createElement("span");
	oSpan.style.display = "inline-block";
	oSpan.style.color = "white";
	oSpan.style.maxWidth = (iScreenScale*15) +"px";
	oSpan.style.fontSize = (iScreenScale*2) +"px";
	oSpan.style.overflow = "hidden";
	oSpan.style.textOverflow = "ellipsis";
	oSpan.style.whiteSpace = "nowrap";
	cDiv.appendChild(oSpan);

	oPersoImage.appendChild(cDiv);
	oGhost.appendChild(oPersoImage);
	
	var oTimeTd = document.createElement("td");
	oTimeTd.style.textAlign = "center";

	var oPersoTime = document.createElement("span");
	oPersoTime.style.fontSize = Math.round(iScreenScale*5.5) + "px";
	oPersoTime.style.color = "white";
	oPersoTime.style.marginLeft = (iScreenScale*2) +"px";
	oTimeTd.appendChild(oPersoTime);

	var oPersoLapTimes = document.createElement("img");
	oPersoLapTimes.src = "images/about.png";
	oPersoLapTimes.style.position = "relative";
	oPersoLapTimes.style.top = -Math.round(iScreenScale*0.5) +"px";
	oPersoLapTimes.style.width = Math.round(iScreenScale*2.5) +"px";
	oPersoLapTimes.style.marginLeft = iScreenScale +"px";
	oPersoLapTimes.style.marginRight = (iScreenScale*2) +"px";
	if (!oPersoLapTimes.dataset) oPersoLapTimes.dataset = {};
	oTimeTd.appendChild(oPersoLapTimes);

	var $fancyTitle;
	oPersoLapTimes.onmouseover = function(e) {
		if ($fancyTitle) return;
		$fancyTitle = document.createElement("div");
		$fancyTitle.className = "ranking_activeplayertitle";
		$fancyTitle.innerHTML = this.dataset.title;
		$fancyTitle.style.position = "absolute";
		$fancyTitle.style.padding = Math.round(iScreenScale/2)+"px "+iScreenScale+"px";
		$fancyTitle.style.borderRadius = iScreenScale+"px";
		$fancyTitle.style.zIndex = 10;
		$fancyTitle.style.backgroundColor = "rgba(60,109,165, 0.95)";
		$fancyTitle.style.color = "white";
		$fancyTitle.style.fontSize = Math.round(iScreenScale*1.8) +"px";
		$fancyTitle.style.lineHeight = Math.round(iScreenScale*2) +"px";
		$fancyTitle.style.visibility = "hidden";
		$mkScreen.appendChild($fancyTitle);
		var rect = this.getBoundingClientRect();
		$fancyTitle.style.left = Math.round(rect.left + (this.scrollWidth-$fancyTitle.scrollWidth)/2)+"px";
		$fancyTitle.style.top = (rect.top + this.scrollHeight + 5)+"px";
		$fancyTitle.style.visibility = "visible";
	};
	oPersoLapTimes.onmouseout = function(e) {
		if (!$fancyTitle) return;
		$mkScreen.removeChild($fancyTitle);
		$fancyTitle = undefined;
	};

	if (ghostsData)
		gRecord = ghostsData[2];
	else
		gRecord = undefined;
	function writeTime(perso,pseudo,time,times,oImg,oDiv) {
		if (!oImg) oImg = oPImg;
		if (!oDiv) oDiv = oPersoTime;
		oImg.src = getSpriteSrc(perso);
		oDiv.innerHTML = timeStr(time);
		oSpan.innerText = pseudo;
		oSpan.title = pseudo;
		oTable.style.left = Math.round((iScreenScale*iWidth+20 - oTable.offsetWidth)/2) +"px";
		oTable.style.top = Math.round((iScreenScale*28 - oTable.offsetHeight)/2) +"px";
		if (oDiv == oPersoTime) {
			var iconTitle = '<small style="color:#CCF">'+toLanguage("Lap times:", "Temps au tour :")+'</small>';
			for (var j=0;j<times.length;j++)
				iconTitle += '<br /><span style="color:#CCF;display:inline-block">'+(j+1)+'.</span> '+timeStr(times[j]);
			oPersoLapTimes.dataset.title = iconTitle;
		}
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
			writeTime(gTimes[gIDs[inc]][1],gTimes[gIDs[inc]][2],gTimes[gIDs[inc]][3],gTimes[gIDs[inc]][4],iPImg,iPersoTime);

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
					writeTime(gTimes[gIDs[inc]][1],gTimes[gIDs[inc]][2],gTimes[gIDs[inc]][3],gTimes[gIDs[inc]][4],iPImg,iPersoTime);
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
					writeTime(gTimes[gIDs[inc]][1],gTimes[gIDs[inc]][2],gTimes[gIDs[inc]][3],gTimes[gIDs[inc]][4],iPImg,iPersoTime);
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
					if (gTimes[j][3] < gTimes[m][3])
						m = j;
				}
				var c = gTimes[m];
				gTimes[m] = gTimes[i];
				gTimes[i] = c;
			}
			if (gID == -1) {
				if (ghostsData) {
					gID = gTimes.length-1;
					while (gID && (gTimes[gID][3] >= ghostsData[2]))
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
			fGauche.style.left = (10*iScreenScale)+"px";
			fGauche.style.top = Math.round(10.5*iScreenScale)+"px";
			fGauche.onclick = function() {
				gID--;
				if (gID < 0)
					gID = gTimes.length-1;
				writeTime(gTimes[gID][1],gTimes[gID][2],gTimes[gID][3],gTimes[gID][4]);
			}
			oScr.appendChild(fGauche);
			
			var fDroite = document.createElement("input");
			fDroite.id = "fDroite";
			fDroite.type = "button";
			fDroite.value = "\u2192";
			fDroite.style.fontSize = (6*iScreenScale)+"px";
			fDroite.style.position = "absolute";
			fDroite.style.left = (65*iScreenScale)+"px";
			fDroite.style.top = Math.round(10.5*iScreenScale)+"px";
			fDroite.onclick = function() {
				gID++;
				if (gID >= gTimes.length)
					gID = 0;
				writeTime(gTimes[gID][1],gTimes[gID][2],gTimes[gID][3],gTimes[gID][4]);
			}
			oScr.appendChild(fDroite);
			if (ghostsData)
				oScr.style.visibility = "visible";
			else
				oContainers[0].appendChild(oScr);
			writeTime(gTimes[gID][1],gTimes[gID][2],gTimes[gID][3],gTimes[gID][4]);
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
			gOverwriteRecord = 1;
			if (replay) {
				strPlayer[0] = ghostsData[0];
				iRecord = ghostsData[2];
				iLapTimes = ghostsData[3];
				iTrajet = ghostsData[4];
			}
			else {
				gPersos = [ghostsData[0]];
				iLapTimes = ghostsData[3];
				jTrajets = [ghostsData[4]];
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
						iRecord = gTimes[gID][3];
						iLapTimes = gTimes[gID][4];
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
							iLapTimes = gTimes[gID][4];
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
	
	var gTimes;
	var gID = -1;
	var gIDs;
	
	oGhost.appendChild(oTimeTd);
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
			writeTime(ghostsData[0],ghostsData[1],ghostsData[2],ghostsData[3]);
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
		oContainers[0].appendChild(oScr);
		if (ghostsData)
			writeTime(ghostsData[0],ghostsData[1],ghostsData[2],ghostsData[3]);
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
function displayCommands(html) {
	var $commandes = document.getElementById("commandes");
	if ($commandes) {
		var emptyCommands = !html;
		$commandes.innerHTML = (html||"")+'<img src="images/edit-controls.png" alt="Edit" id="commandes-edit"'+ (emptyCommands ? ' class="nocommand"':'') +' title="'+toLanguage("More settings","Plus de paramètres")+'" />';
		document.getElementById("commandes-edit").onclick = function() {
			editCommands();
		};
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
	displayCommands('<strong>'+ toLanguage('Move', 'Se diriger') +'</strong> : '+ aTouches(aKeyName("up")+aKeyName("left")+aKeyName("down")+aKeyName("right"), "ESDF") +'<br /><strong>'+ toLanguage('Use item', 'Utiliser un objet') +'</strong> : '+ aTouches(aKeyName("item"), toLanguage("A","Q")) +'<br /><strong>'+ toLanguage("Item backwards", "Objet en arrière") +'</strong> : '+ aTouches(aKeyName("item_back"), toLanguage("W", "A")) +'<br /><strong>'+ toLanguage('Jump/drift', 'Sauter/déraper') +'</strong> : '+ aTouches(aKeyName("jump"), "G") + ((course=="BB") ? ('<br /><strong>'+ toLanguage('Inflate a balloon', 'Gonfler un ballon') +'</strong> : '+ aTouches(aKeyName("balloon"), "R")):'') +'<br /><strong>'+ toLanguage('Rear/Front view', 'Vue arri&egrave;re/avant') +'</strong> : '+ aTouches(aKeyName("rear"), toLanguage("W","Z")) +'<br /><strong>'+ toLanguage('Pause', 'Mettre en pause') +'</strong> : '+ aKeyName("pause") +'<br /><strong>'+ toLanguage('Quit', 'Quitter') +'</strong> : '+ aKeyName("quit"));
}
function editCommands(reload,currentTab) {
	currentTab = currentTab || 0;
	var $controlEditorMask = document.getElementById("control-editor-mask");
	if ($controlEditorMask) {
		document.body.removeChild($controlEditorMask);
		if (!reload) {
			if (document.querySelector("#commandes strong"))
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
	$controlTitle.innerHTML = toLanguage("Game settings", "Paramètres du jeu");
	$controlHeader.appendChild($controlTitle);
	var $controlClose = document.createElement("button");
	$controlClose.className = "control-close";
	$controlClose.innerHTML = "&times;";
	$controlClose.onclick = function() {
		editCommands();
	};
	$controlHeader.appendChild($controlClose);
	$controlEditor.appendChild($controlHeader);
	var $controlTabs = document.createElement("div");
	$controlTabs.className = "control-tabs";
	var controlTabs = [toLanguage("Edit controls", "Modifier les contrôles"), toLanguage("Advanced settings", "Paramètres avancés")];
	for (var i=0;i<controlTabs.length;i++) {
		(function(i) {
			var $controlTab = document.createElement("div");
			if (!i) $controlTab.className = "control-tab-active";
			$controlTab.innerHTML = controlTabs[i];
			$controlTab.onclick = function() {
				document.querySelector(".control-tabs .control-tab-active").classList.remove("control-tab-active");
				this.classList.add("control-tab-active");
				document.querySelector(".control-window .control-window-active").classList.remove("control-window-active");
				document.querySelectorAll(".control-window > div")[i].classList.add("control-window-active");
				currentTab = i;
			}
			$controlTabs.appendChild($controlTab);
		})(i);
	}
	$controlEditor.appendChild($controlTabs);
	var $controlWindows = document.createElement("div");
	$controlWindows.className = "control-window";
	var $controlCommands = document.createElement("div");
	$controlCommands.className = "control-window-active";
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
	$controlCommands.appendChild($controlEditorGrid);
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
			editCommands(true,currentTab);
		}
		return false;
	};
	$controlReset.appendChild($controlResetBtn);
	$controlCommands.appendChild($controlReset);
	$controlWindows.appendChild($controlCommands);
	var $controlSettings = document.createElement("div");
	$controlSettings.className = "control-settings";
	var $controlSettingsInfo = document.createElement("div");
	$controlSettingsInfo.className = "control-settings-info";
	$controlSettingsInfo.innerHTML = toLanguage("Those settings allow you to disable some graphics elements from the game. Use them if you experience some lag for example.", "Ces paramètres vous permettent de désactiver certains éléments graphiques du jeu. Utilisez-les si vous avez des problèmes de lags par exemple.");
	$controlSettings.appendChild($controlSettingsInfo);
	var allSettings = {
		'ld' : toLanguage('Don\'t display heavy elements (trees, decors)', 'Désactiver l\'affichage des éléments lourds (arbres, décors)'),
		'nogif' : toLanguage('Disable animation in gif-format tracks', 'Désactiver les animations des circuits au format gif'),
		'nomap' : toLanguage('Disable mini-map display', 'Désactiver l\'affichage de la mini-map')
	};
	var currentSettings = localStorage.getItem("settings");
	currentSettings = currentSettings ? JSON.parse(currentSettings) : {};
	for (var key in allSettings) {
		(function(key) {
			var $controlSetting = document.createElement("label");
			var $controlCheckbox = document.createElement("input");
			$controlCheckbox.type = "checkbox";
			$controlCheckbox.checked = !!currentSettings[key];
			$controlSetting.appendChild($controlCheckbox);
			var $controlText = document.createElement("span");
			$controlText.innerHTML = allSettings[key];
			$controlSetting.appendChild($controlText);
			$controlCheckbox.onclick = function() {
				if (this.checked)
					currentSettings[key] = 1;
				else
					delete currentSettings[key];
				localStorage.setItem("settings", JSON.stringify(currentSettings));
			}
			$controlSettings.appendChild($controlSetting);
		})(key);
	}
	var $controlReset = document.createElement("div");
	$controlReset.className = "control-reset";
	var $controlResetBtn = document.createElement("a");
	$controlResetBtn.href = "#null";
	$controlResetBtn.innerHTML = toLanguage("Reset settings", "Rétablir les paramètres par défaut");
	$controlResetBtn.onclick = function() {
		if (confirm(toLanguage("Reset settings to default?", "Réinitiliser les paramètres à ceux par défaut ?"))) {
			localStorage.removeItem("settings");
			editCommands(true,currentTab);
		}
		return false;
	};
	$controlReset.appendChild($controlResetBtn);
	$controlSettings.appendChild($controlReset);
	$controlWindows.appendChild($controlSettings);
	$controlEditor.appendChild($controlWindows);
	$controlEditorMask.appendChild($controlEditor);
	document.body.appendChild($controlEditorMask);
	$controlWindows.style.width = $controlWindows.scrollWidth +"px";
	$controlWindows.style.height = $controlWindows.scrollHeight +"px";
	if (currentTab)
		document.querySelectorAll(".control-tabs > div")[currentTab].click();
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
		cheat:[120,33,57,105]
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
	oTitle.style.color = primaryColor;
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
		if (sPerso == "bowser_skelet")
			return "dry bowser";
		if (sPerso == "flora_piranha")
			return "petey piranha";
	}
	else {
		if (sPerso == "frere_marto")
			return "frère marto";
	}
	sPerso = sPerso.replace(/_/g, " ");
	return sPerso;
}

formulaire = document.forms.modes;
if (pause) {
	formulaire.screenscale.disabled = false;
	formulaire.quality.disabled = false;
	formulaire.music.disabled = false;
	formulaire.sfx.disabled = false;
	if (isSingle && !isOnline)
		choose(1);
	else if (fInfos.map != undefined)
		loadMap(fInfos.map);
	else if (course == "VS")
		selectMapScreen();
	else if (course == "BB") {
		if (isCup)
			selectRaceScreen(NBCIRCUITS);
		else
			selectMapScreen();
	}
	else if (fInfos.player)
		selectMapScreen();
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
		[12, toLanguage("Very large","Tr&egrave;s large")],
		[-1, toLanguage("Full (F11)","Plein (F11)")]
	], (+$mkScreen.dataset.lastsc)||iScreenScale);
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
	if (!window.fsevent) {
		window.fsevent = function(e) {
			if (e.keyCode == 122) {
				e.preventDefault();
				var aScreenScale = iScreenScale;
				iScreenScale = +formulaire.screenscale.value;
				formulaire.screenscale.value = -1;
				formulaire.screenscale.onchange();
				if (formulaire.screenscale.disabled)
					iScreenScale = aScreenScale;
				return false;
			}
		}
		window.addEventListener("keydown", window.fsevent);
	}

	if (typeof course === 'undefined')
		course = "";
	var $commandes = document.getElementById("commandes");
	if ($commandes && $commandes.innerHTML.length < 10)
		displayCommands();
	
	if (isFirstLoad) {
		isFirstLoad = false;
		if (hasChallenges()) {
			xhr("getClSelected.php", null, function(res) {
				if (!res)
					return true;
				try {
					res = JSON.parse(res);
				}
				catch (e) {
					return true;
				}
				if (res.id) {
					for (var type in challenges) {
						for (var cid in challenges[type]) {
							var creationChallenges = challenges[type][cid];
							var challengesList = creationChallenges.list;
							for (var i=0;i<challengesList.length;i++) {
								var challenge = challengesList[i];
								if (challenge.id == res.id) {
									if (!clSelected && !challenge.succeeded) {
										clSelected = challenge;
										clSelected.trackType = type;
										clSelected.trackId = cid;
										clSelected.autoset = res.autoset;
										localStorage.removeItem("itemset."+getItemMode());
										if (!course && clSelected.autoset.course) {
											var oScr = oContainers[0].childNodes[0];
											if (oScr) {
												oScr.innerHTML = "";
												oContainers[0].removeChild(oScr);
												course = clSelected.autoset.course;
												selectPlayerScreen(0);
											}
										}
										showClSelectedPopup();
									}
									return true;
								}
							}
						}
					}
				}
				return true;
			});
		}
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
		if (e.keyCode == 38)
			this.blur();
		else
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
						setTimeout(refreshChat, 1000);
					}
					else
						chatting = false;
					return true;
				}
				return false;
			});
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