var pause;
var aPlayers = new Array();
var fInfos;
var formulaire;
var course;
var baseCp;
var nBasePersos, customPersos;
function tourner(kart)
{
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

function defileMaps(fMap)	{
if (document.getElementById("maps") && document.getElementById("maps").alt == fMap)	{
	if (fMap % 4 != 0)
	fMap++;
	else
	fMap -= 3;
	document.getElementById("maps").alt = fMap;
	document.getElementById("maps").src = "images/selectors/select_map"+ fMap +".png";
	setTimeout("defileMaps("+ fMap +")", 1000);
	}
}

var selectPerso;

function MarioKart() {

var oMaps = listMaps();

for (circuits in oMaps) {
	var oMap = oMaps[circuits];
	if (!oMap.horspistes) {
		oMap.horspistes = {};
		if (oMap.horspiste) {
			oMap.horspistes["herbe"] = oMap.horspiste;
			delete oMap.horspiste;
		}
	}
}

var isCup = (oMaps.length > 1);

var iWidth = 80;
var iHeight = 39;
var iQuality = optionOf("quality");
var bMusic = !!optionOf("music");
var iSfx = !!optionOf("sfx");
var gameMenu;

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

var oMap;

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

var objets = [
	"fauxobjet", "fauxobjet",  "fauxobjet", "fauxobjet", "banane", "banane", "banane", "banane", "banane", "banane", "banane", "carapace", "carapace", "carapace", "carapace",	// 1er
	"carapace", "carapace", "carapace", "carapace", "carapace", "bobomb", "bobomb", "bobomb", "bobomb", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge",	// 2e
	"bobomb", "bobomb", "bobomb", "bobomb", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacebleue", "carapacebleue", "carapacebleue", "carapacebleue",	// 3e
	"bobomb", "carapacebleue", "carapacebleue", "carapacebleue", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "megachampi", "megachampi", "champi",	// 4e
	"carapacerouge", "carapacerouge", "carapacerouge", "carapacerouge", "megachampi", "megachampi", "megachampi", "megachampi", "megachampi", "champi", "champi", "champi", "champi", "champi", "champi",	// 5e
	"megachampi", "megachampi", "megachampi", "megachampi", "megachampi", "megachampi", "megachampi", "megachampi", "megachampi", "megachampi", "etoile", "etoile", "etoile", "etoile", "etoile",	// 6e
	"megachampi", "megachampi", "megachampi", "megachampi", "megachampi", "megachampi", "megachampi", "etoile", "etoile", "etoile", "etoile", "etoile", "etoile", "billball", "billball", // 7e
	"megachampi", "megachampi", "etoile", "etoile", "etoile", "etoile", "etoile", "billball", "billball", "billball", "billball", "billball", "eclair", "eclair", "eclair" // 8e
];

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
var iDificulty = 5;

var iTrajet;
var gRecord;
if (pause) {
	strPlayer = fInfos[0];
	if (course != "CM")
		iDificulty = fInfos[1];
	else {
		iTrajet = fInfos[2];
		gRecord = fInfos[4];
	}
}

var oMapImg;

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
	var oPlayer = oPlayers[0];
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
			if (aKarts[i].loose)
				iPlanCharacters[i].style.display = "none";
			else {
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
		if (oMap.decor.length != iPlanDecor.length) {
			syncObjects(iPlanDecor,oMap.decor,oMap.decor_src, iObjWidth,iPlanCtn);
			for (var i=0;i<oMap.decor.length;i++)
				setObject(iPlanDecor[i],oMap.decor[i][0],oMap.decor[i][1], iObjWidth,iPlanSize);
		}
	}
	setDecorPos(oPlanDecor, oObjWidth, oPlanCtn, oPlanSize);
	setDecorPos(oPlanDecor2, oObjWidth2, oPlanCtn2, oPlanSize2);

	syncObjects(oPlanFauxObjets,fauxobjets,"objet", oObjWidth,oPlanCtn);
	syncObjects(oPlanFauxObjets2,fauxobjets,"objet", oObjWidth2,oPlanCtn2);
	for (var i=0;i<fauxobjets.length;i++) {
		setObject(oPlanFauxObjets[i],fauxobjets[i][1],fauxobjets[i][2], oObjWidth,oPlanSize);
		setObject(oPlanFauxObjets2[i],fauxobjets[i][1],fauxobjets[i][2], oObjWidth2,oPlanSize2);
		oPlanFauxObjets[i].style.zIndex = oPlanFauxObjets2[i].style.zIndex = 2;
	}
	syncObjects(oPlanBananes,bananes,"banane", oObjWidth,oPlanCtn);
	syncObjects(oPlanBananes2,bananes,"banane", oObjWidth2,oPlanCtn2);
	for (var i=0;i<bananes.length;i++) {
		setObject(oPlanBananes[i],bananes[i][1],bananes[i][2], oObjWidth,oPlanSize);
		setObject(oPlanBananes2[i],bananes[i][1],bananes[i][2], oObjWidth2,oPlanSize2);
		oPlanBananes[i].style.zIndex = oPlanBananes2[i].style.zIndex = 2;
	}

	function setBobombPos(iPlanBobOmbs, iObjWidth,iPlanCtn, iPlanSize, iExpWidth) {
		syncObjects(iPlanBobOmbs,bobombs,"bob-omb", iObjWidth,iPlanCtn);
		for (var i=0;i<bobombs.length;i++) {
			if (bobombs[i][6] <= 0) {
				posImg(iPlanBobOmbs[i], bobombs[i][1],bobombs[i][2],Math.round(oPlayer.rotation), iExpWidth,iPlanSize).src = "images/map_icons/explosion.png";
				iPlanBobOmbs[i].style.width = iExpWidth +"px";
				iPlanBobOmbs[i].style.opacity = Math.max(1+bobombs[i][6]/10, 0);
			}
			else
				setObject(iPlanBobOmbs[i],bobombs[i][1],bobombs[i][2], iObjWidth,iPlanSize).style.zIndex = 2;
		}
	}
	setBobombPos(oPlanBobOmbs, oObjWidth,oPlanCtn, oPlanSize, oExpWidth);
	setBobombPos(oPlanBobOmbs2, oObjWidth2,oPlanCtn2, oPlanSize2, oExpWidth2);

	syncObjects(oPlanCarapaces,carapaces,"carapace", oObjWidth,oPlanCtn);
	syncObjects(oPlanCarapaces2,carapaces,"carapace", oObjWidth2,oPlanCtn2);
	for (var i=0;i<carapaces.length;i++) {
		setObject(oPlanCarapaces[i],carapaces[i][1],carapaces[i][2], oObjWidth,oPlanSize);
		setObject(oPlanCarapaces2[i],carapaces[i][1],carapaces[i][2], oObjWidth2,oPlanSize2).style.zIndex = 2;
	}

	syncObjects(oPlanCarapacesRouges,carapacesRouge,"carapace-rouge", oObjWidth,oPlanCtn);
	syncObjects(oPlanCarapacesRouges2,carapacesRouge,"carapace-rouge", oObjWidth2,oPlanCtn2);
	for (var i=0;i<carapacesRouge.length;i++) {
		setObject(oPlanCarapacesRouges[i],carapacesRouge[i][1],carapacesRouge[i][2], oObjWidth,oPlanSize);
		setObject(oPlanCarapacesRouges2[i],carapacesRouge[i][1],carapacesRouge[i][2], oObjWidth2,oPlanSize2).style.zIndex = 2;
		if (carapacesRouge[i][5])
			oPlanCarapacesRouges[i].style.zIndex = 2;
	}

	function setCarapacesBleuesPos(iPlanCarapacesBleues, iObjWidth,iPlanSize,iExpWidth,iPlanCtn) {
		syncObjects(iPlanCarapacesBleues,carapacesBleue,"carapace-bleue",iObjWidth,iPlanCtn);
		for (var i=0;i<carapacesBleue.length;i++) {
			if (carapacesBleue[i][5] <= 0) {
				posImg(iPlanCarapacesBleues[i], carapacesBleue[i][1],carapacesBleue[i][2],Math.round(oPlayer.rotation), iExpWidth,iPlanSize).src = "images/map_icons/explosionB.png";
				iPlanCarapacesBleues[i].style.width = iExpWidth +"px";
				iPlanCarapacesBleues[i].style.opacity = Math.max(1+carapacesBleue[i][5]/10, 0);
			}
			else
				setObject(iPlanCarapacesBleues[i],carapacesBleue[i][1],carapacesBleue[i][2], iObjWidth,iPlanSize).style.zIndex = 2;
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


function resetGame(i) {
	oMap = oMaps[i];
	loadMap();
}

function loadMap() 
{
	oMapImg = new Image();

	oMapImg.onload = startGame;

	oMapImg.src = "images/uploads/map"+ oMap.id +"."+ oMap.ext;

	document.body.style.cursor = "progress";

	formulaire.screenscale.disabled = true;
	formulaire.quality.disabled = true;
	formulaire.music.disabled = true;
	formulaire.sfx.disabled = true;
	
	for (var i=0;i<strPlayer.length;i++) {
		var	iScreenMore = i*(iWidth*iScreenScale+2);
		document.getElementById("compteur"+i).style.left = (12 + iScreenMore) + "px";
		document.getElementById("compteur"+i).style.top = iScreenScale * 35 + 10 +"px";
		document.getElementById("compteur"+i).style.fontSize = iScreenScale * 2+"pt";
		document.getElementById("compteur"+i).innerHTML = (oMap.sections ? "Section":toLanguage("Lap","Tour")) +' <span id="tour'+i+'">1</span>/'+ oMap.tours;
		document.getElementById("temps"+i).style.left = (56*iScreenScale + iScreenMore) +"px";
		document.getElementById("temps"+i).style.fontSize = iScreenScale * 2 +"pt";
		document.getElementById("lakitu"+i).style.width = iScreenScale * 9 +"px";
		document.getElementById("lakitu"+i).style.height = Math.round(iScreenScale*6.6) +"px";
		document.getElementById("lakitu"+i).style.fontSize = Math.round(iScreenScale*2.3) +"px";
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
		document.getElementById("infoPlace"+i).style.display = "block";
		document.getElementById("objet"+i).style.left =  (iScreenMore+12) +"px";
		document.getElementById("objet"+i).style.width =  iScreenScale * 10 +"px";
		document.getElementById("objet"+i).style.height =  iScreenScale * 10 +"px";
		document.getElementById("objet"+i).style.visibility =  "visible";
		document.getElementById("scroller"+i).style.width = iScreenScale * 8 +"px";
		document.getElementById("scroller"+i).style.height = iScreenScale * 8 +"px";
		document.getElementById("scroller"+i).width = iScreenScale * 8 +"px";
		document.getElementById("scroller"+i).height = iScreenScale * 8 +"px";
		document.getElementById("scroller"+i).style.top = (20-(8-iScreenScale)*1.25)+"px";
		document.getElementById("scroller"+i).style.left = (21-(8-iScreenScale)*1.25 + iScreenMore)+"px";
		document.getElementById("scroller"+i).getElementsByTagName("div")[0].style.left = Math.round(iScreenScale*0.1 + 1) +"px";
		document.getElementById("mariokartcontainer").style.top = iScreenScale * 31 + 10 +"px";

		var lObjet = iScreenScale * 8 - 3;
	}
	for (var j=0;j<document.getElementsByClassName("aObjet").length;j++)
		document.getElementsByClassName("aObjet")[j].style.width = lObjet +"px";

	if (bMusic) {
		removeMenuMusic();
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
	}
}

var vitesse;
var time = 0;
var timer = 0;
var millisec = 0;
var sec = 0;
var min = 0;
iScreenScale = optionOf("screenscale");


var fMaxRotInc = 6;

function arme(ID)
{
var oKart = aKarts[ID];
if (!oKart.using[0])	{
if (oKart.roulette != 25) return;
var tpsUse;
	switch(oKart.arme)	{
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
		oKart.sprite[0].img.onload = function() {
			bCounting = false;
			this.onload = undefined;
			reprendre(false);
		}
		pause = true;
		bCounting = true;
		if (shouldPlayMusic(oKart) && !oPlayers[1])
			postStartMusic("musics/events/starman.mp3");
	}
	oKart.speedinc *= 5;
	oKart.protect = true;
	break;

	case "megachampi" :
	tpsUse = 50;
	oKart.size = 1;
	updateDriftSize(ID);
	oKart.protect = true;
	if (shouldPlayMusic(oKart) && !oPlayers[1])
		postStartMusic("musics/events/megamushroom.mp3");
	break;
	
	case "billball" :
	tpsUse = 50;
	for (var i=0;i<strPlayer.length;i++)
		oKart.sprite[i].img.src = "images/sprites/sprite_billball_smooth.png";
	if (!oKart.cpu) {
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
	break;

	case "eclair" :
	tpsUse = 100;
	document.getElementById("mariokartcontainer").style.opacity = 0.7;
	if (iSfx && !oPlayers[0].cpu)
		playSoundEffect("musics/events/lightning.mp3");
	break;

	case "banane" :
	oKart.using = [bananes, (bananes.length), "banane"];
	bananes.push([new Sprite("banane"), (oKart.x - 5 * direction(0, oKart.rotation)), (oKart.y - 5 * direction(1, oKart.rotation)), oKart.z]);
	playIfShould(oKart,"musics/events/item_store.mp3");
	break;

	case "fauxobjet" :
	oKart.using = [fauxobjets, (fauxobjets.length), "fauxobjet"];
	fauxobjets.push([new Sprite("objet"), (oKart.x - 5 * direction(0, oKart.rotation)), (oKart.y - 5 * direction(1, oKart.rotation)), oKart.z]);
	playIfShould(oKart,"musics/events/item_store.mp3");
	break;

	case "carapace" :
	oKart.using = [carapaces, (carapaces.length), "carapace"];
	carapaces.push([new Sprite("carapace"), (oKart.x - 5 * direction(0, oKart.rotation)), (oKart.y - 5 * direction(1, oKart.rotation)), oKart.z]);
	playIfShould(oKart,"musics/events/item_store.mp3");
	break;

	case "carapacerouge" :
	oKart.using = [carapacesRouge, (carapacesRouge.length), "carapacerouge"];
	carapacesRouge.push([new Sprite("carapace-rouge"), (oKart.x - 5 * direction(0, oKart.rotation)), (oKart.y - 5 * direction(1, oKart.rotation)), oKart.z]);
	playIfShould(oKart,"musics/events/item_store.mp3");
	break;

	case "carapacebleue" :
	var cible;
	var cPlace;
	for (cPlace=1;cible==undefined;cPlace++) {
		for (var i=0;i<aKarts.length;i++)	{
			if (aKarts[i].place == cPlace)	{
				if (aKarts[i].tours <= oMap.tours) {
					cible = i;
					i = aKarts.length;
				}
				else {
					cPlace++;
					i = -1;
				}
			}
		}
	}
	carapacesBleue.push([new Sprite("carapace-bleue"), oKart.x,oKart.y, cible, 0, 5]);
	playDistSound(oKart,"musics/events/throw.mp3",50);
	break;

	case "bobomb" :
	oKart.using = [bobombs, (bobombs.length), "bobomb"];
	bobombs.push([new Sprite("bob-omb"), (oKart.x - 5 * direction(0, oKart.rotation)), (oKart.y - 5 * direction(1, oKart.rotation)), oKart.z]);
	playIfShould(oKart,"musics/events/item_store.mp3");
	break;
	}

	if (tpsUse)
	oKart[oKart.arme] = tpsUse;

	stopDrifting(ID);

	supprArme(ID);
}

else	{
	var posX = oKart.x;
	var posY = oKart.y;

	switch(oKart.using[2])	{
	case "banane" :
	var decalage = 30/(oKart.speed+5);
	var fPosX = posX - decalage * direction(0, oKart.rotation);
	var fPosY = posY - decalage * direction(1, oKart.rotation);
	if (!tombe(Math.round(fPosX),Math.round(fPosY)))
	bananes.push([new Sprite("banane"),fPosX, fPosY, 0]);
	playIfShould(oKart,"musics/events/put.mp3");
	break;


	case "fauxobjet" :
	var decalage = 30/(oKart.speed+5);
	fauxobjets.push([new Sprite("objet"),posX - decalage * direction(0, oKart.rotation),posY - decalage * direction(1, oKart.rotation), 0]);
	playIfShould(oKart,"musics/events/put.mp3");
	break;

	case "carapace" :
	carapaces.push([new Sprite("carapace"),posX + Math.max(oKart.speed*4-5, 15) * direction(0, oKart.rotation),posY + Math.max(oKart.speed*4-5, 15) * direction(1, oKart.rotation),0,oKart.rotation,10,0]);
	playDistSound(oKart,"musics/events/throw.mp3",50);
	break;

	case "carapacerouge" :
	carapacesRouge.push([new Sprite("carapace-rouge"),posX + Math.max(oKart.speed*4-5, 15) * direction(0, oKart.rotation),posY + Math.max(oKart.speed*4-5, 15) * direction(1, oKart.rotation),0,0,oKart.rotation,ID]);
	playDistSound(oKart,"musics/events/throw.mp3",50);
	break;


	case "bobomb" :
	bobombs.push([new Sprite("bob-omb"),posX,posY,0,oKart.rotation,15,30]);
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
	return iSfx && !oKart.cpu && !oKart.loose;
}
function shouldPlayMusic(oKart) {
	return bMusic && !oKart.cpu && !oKart.loose;
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
var mapMusic, endingMusic, carEngine, carEngine2, carEngine3, carDrift, carSpark;
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
	for (var i=0;i<oMap.decor.length;i++)
		oMap.decor[i][2] = new Sprite(oMap.decor_src);

	for (var i=0;i<oMap.arme.length;i++)
	oMap.arme[i][2] = 0;

	var cp0 = oMap.sections ? oMap.checkpoint.length-1:0;

	for (var i=0;i<strPlayer.length;i++) {
		var oPlayer = {
			x : oMap.startposition[0] + ((aPlayers.length-i)%2 ? 0 : 18),
			y : oMap.startposition[1] - (5-aPlayers.length+i) * 12,
			z : 0,

			personnage : strPlayer[i],

			speed : 0,
			speedinc : 0,
			heightinc : 0,

			rotation : 180,
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
			billball : 0,
			using : [false],

			time : 0,
			tours : 1,
			demitours : cp0,
			place : aPlayers.length-i,
			pts : cp[strPlayer[i]][3],
		};
		oPlayers.push(oPlayer);
		aKarts.push(oPlayer);
	}
		
	if (course != "CM") {
		for (var i=0;i<oMap.arme.length;i++)
			oMap.arme[i][2] = 0;
		var iAI = strPlayer.length;
		for (var i=0;i<aPlayers.length;i++) {
			var joueur = aPlayers[i];
			var depart = (iDificulty-4)*2+Math.round(Math.random());
			if (joueur != strPlayer[0] && joueur != strPlayer[1]) {
				var oEnemy = {

					speed : (depart<2) ? 0 : 5.7,
					speedinc : 0.5,
					heightinc : 0,

					rotation : 180,
					rotincdir : 0,
					rotinc : 0,

					x : oMap.startposition[0] + ((aPlayers.length-iAI)%2 ? 0 : 18),
					y : oMap.startposition[1] - (5-aPlayers.length+iAI) * 12,
					z : 0,

					size : 1,
					personnage : joueur,
					sprite : new Sprite(joueur),

					tourne : depart ? 0 : 42,
					tombe : 0,
					protect : false,

					roulette : 0,
					arme : false,

					champi : 0,
					etoile : 0,
					megachampi : 0,
					billball : 0,
					using : [false],

					cpu : true,
					aipoint : 0,
					aipoints : oMap.aipoints[iAI%oMap.aipoints.length],
					maxspeed : 5.7,

					tours : 1,
					demitours : cp0,
					place : aPlayers.length - iAI,
					pts : cp[joueur][3],
				};

				aKarts.push(oEnemy);
				iAI++;
			}
		}
	}
	else {
		oMap.arme = [];
		aKarts[0].arme = "champi";
		aKarts[0].roulette = 25;
		document.getElementById("roulette0").innerHTML = '<img alt="." src="images/objects/champi.gif" style="width: '+ Math.round(iScreenScale * 8 - 3)+'px;" />';
	}

	if (pause) {
		for (var i=0;i<aPlayers.length;i++) {
			for (var j=0;j<aKarts.length;j++) {
				var oKart = aKarts[j];
				if (oKart.personnage == aPlayers[i]) {
					oKart.place = i+1;
					oKart.x = oMap.startposition[0] + (i%2 ? 18:0);
					oKart.y = oMap.startposition[1] - (5-i-1) * 12;
					j = aKarts.length;
				}
			}
		}
	}
	if (course != "CM") {
		for (var i=0;i<strPlayer.length;i++) {
			document.getElementById("infoPlace"+i).innerHTML = toPlace(oPlayers[i].place);
			document.getElementById("infoPlace"+i).style.display = "block";
		}
	}
	else {
		for (var i=0;i<strPlayer.length;i++)
			document.getElementById("infoPlace"+i).style.display = "none";
	}

	if (strPlayer.length == 1) {
		oPlanWidth = Math.round(iScreenScale*19.4);
		oPlanWidth2 = (oMap.dimensions[0]>=oMap.dimensions[1]) ? oPlanWidth : Math.round(oPlanWidth*(oMap.dimensions[0]/oMap.dimensions[1]));
		oPlanHeight2 = (oMap.dimensions[0]<=oMap.dimensions[1]) ? oPlanWidth : Math.round(oPlanWidth*(oMap.dimensions[1]/oMap.dimensions[0]));
		oPlanSize = iScreenScale*59;
		oPlanSize2 = oPlanWidth2;
		oPlanRealSize = oMap.dimensions[0];
		oCharRatio = 0.8;
		oPlanRatio = 0.5;

		oPlanDiv = document.createElement("div");
		oPlanDiv.style.backgroundColor = "rgb("+ oMap.bgcolor +")";
		oPlanDiv.style.position = "absolute";
		oPlanDiv.style.left = (15 + iScreenScale*80) +"px";
		oPlanDiv.style.top = "10px";
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
		oPlanCtn.style.position ="absolute";
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
	}

	setTimeout(render, 500);

	if (bMusic) {
		var startingMusic = playSoundEffect("musics/events/start.mp3");
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
		if (!isMobile()) {
			var oDebug = document.createElement("input");
			document.body.appendChild(oDebug);
			oDebug.focus();
			oDebug.blur();
			document.body.removeChild(oDebug);
		}
	}

	var fncCount = function() {
		if (iCntStep)	{
			for (var i=0;i<strPlayer.length;i++)
				oCounts[i][0].scrollLeft = iCntStep * 12 * iScreenScale;
			if (iCntStep < 3)	{
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
					if (oPlayers[i].speed == 1)	{
					oPlayers[i].speed = 11;
					}
					else if (oPlayers[i].speed > 1) {
					spinKart(oPlayers[i], 42);
					oPlayers[i].speed = 0;
					oPlayers[i].speedinc = 0;
					}
				}
				if (bMusic || iSfx) {
					goMusic.play();
					goMusic.onended = function() {
						document.body.removeChild(countDownMusic);
						document.body.removeChild(goMusic);
					};
					setTimeout(function() {
						if (!oMusicEmbed)
							unpauseMusic(mapMusic);
					}, oMap.music ? 1000:500);
				}
				setTimeout(
					function() {
						for (var i=0;i<strPlayer.length;i++) {
							oContainers[i].removeChild(oCounts[i][0]);
							document.getElementById("infos"+i).style.visibility = "hidden";
						}
						document.getElementById("infos0").style.top = iScreenScale * 7 + 10 +"px";
						document.getElementById("infos0").style.left = Math.round(iScreenScale*20+10 + (strPlayer.length-1)/2*(iWidth*iScreenScale+2)) +"px";
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
								fInfos = [strPlayer, oMaps.indexOf(oMap), iDificulty, false, gRecord];
								document.getElementById("infos0").style.visibility = "hidden";
								document.getElementById("infos0").style.opacity = 0.8;
								document.getElementById("infos0").style.color = "#FF9900";
								document.getElementById("lakitu0").style.display = "none";
								document.getElementById("drift0").style.display = "none";
								if (strPlayer.length == 1)
									removePlan();
								oBgLayers.length = 0;
								document.onmousedown = undefined;
								setTimeout(MarioKart, 500);
							}
						document.getElementById("quitter").onclick = quitter;
						if (bMusic && !oMusicEmbed)
							unpauseMusic(mapMusic);
						bCounting = false;
					}, 1000
				);
		
		if (!pause || !fInfos[3]) {
			if (oPlayers.length == 1) {
				document.onkeydown = function(e) {
					switch (e.keyCode) {
						case 38: // up
							oPlayers[0].speedinc = cp[oPlayers[0].personnage][0]*oPlayers[0].size;
							return false;
						case 37: // left
							oPlayers[0].rotincdir = cp[oPlayers[0].personnage][2];
							if (!oPlayers[0].driftinc && !oPlayers[0].tourne && !oPlayers[0].fell && (e.ctrlKey!==false||e.altKey!==false)) {
								if (oPlayers[0].jumped)
									oPlayers[0].driftinc = 1;
							}
							return false;
						case 39: // right
							oPlayers[0].rotincdir = -cp[oPlayers[0].personnage][2];
							if (!oPlayers[0].driftinc && !oPlayers[0].tourne && !oPlayers[0].fell && (e.ctrlKey!==false||e.altKey!==false)) {
								if (oPlayers[0].jumped)
									oPlayers[0].driftinc = -1;
							}
							return false;
						case 40: // down
							oPlayers[0].speedinc -= 0.2;
							return false;
						case 17: // ctrl
						case 18: // alt
							if (pause) break;
							if (!oPlayers[0].z && !oPlayers[0].heightinc) {
								if (!oPlayers[0].driftinc && !oPlayers[0].tourne) {
									oPlayers[0].z = 1;
									oPlayers[0].heightinc = 0.5;
									oPlayers[0].jumped = true;
									if (oPlayers[0].rotincdir)
										oPlayers[0].driftinc = (oPlayers[0].rotincdir>0) ? 1:-1;
								}
							}
							else if (!oPlayers[0].jumped && !oPlayers[0].ctrled && !oPlayers[0].fell && !oPlayers[0].billball && !oPlayers[0].tourne && !oPlayers[0].figuring && !oPlayers[0].figstate)
								stuntKart(oPlayers[0]);
							break;
						case 80: // P
							if (!pause)	{
								if (!bCounting)	{
								document.getElementById("infos0").style.visibility = "visible";
								pause = true;
								pauseSounds();
								}
							}
							else
								reprendre(true);
							return false;
						case 120:
						case 33:
						case 57:
						case 105:
							if ((course != "GP") && (course != "CM") && (course != "CL"))
								openCheats();
							return false;
						}
					}

				document.onkeyup = function(e) {
					switch (e.keyCode) {
						case 32: // space
							if (!oPlayers[0].tourne && !pause)
							arme(0);
							break;
						case 38: // up
							oPlayers[0].speedinc = 0;
							break;
						case 37: // left
							oPlayers[0].rotincdir = 0;
							break;
						case 39: // right
							oPlayers[0].rotincdir = 0;
							break;
						case 40: // down
							oPlayers[0].speedinc = 0;
							break;
						case 17: // ctrl
						case 18: // alt
							if (pause) break;
							if (oPlayers[0].driftinc) {
								oPlayers[0].driftinc = 0;
								if (oPlayers[0].driftcpt >= 15) {
									oPlayers[0].turbodrift = 15;
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
						case 27: // escape
						if (!bCounting)
							quitter();
							break;
						case 88: // X
							if (!bCounting) {
								var nView = 180 - oPlayers[0].changeView;
								oPlayers[0].changeView = nView;
								oPlayers[0].sprite[0].setState(11);
							}
						}
					}
					
					document.onmousedown = function(e) {
						if (pause)
							return true;
						if (!oPlayers[0].tourne)
							arme(0);
						return false;
					}
				}
			else {
				document.onkeydown = function(e) {
					switch (e.keyCode) {
						case 38: // up
							oPlayers[0].speedinc = cp[oPlayers[0].personnage][0]*oPlayers[0].size;
							return false;
						case 37: // left
							oPlayers[0].rotincdir = cp[oPlayers[0].personnage][2];
							if (!oPlayers[0].driftinc && !oPlayers[0].tourne && !oPlayers[0].fell && (e.ctrlKey!==false||e.altKey!==false)) {
								if (oPlayers[0].jumped)
									oPlayers[0].driftinc = 1;
							}
							oPlayers[0].rotincdir = cp[oPlayers[0].personnage][2];
							return false;
						case 39: // right
							oPlayers[0].rotincdir = -cp[oPlayers[0].personnage][2];
							if (!oPlayers[0].driftinc && !oPlayers[0].tourne && !oPlayers[0].fell && (e.ctrlKey!==false||e.altKey!==false)) {
								if (oPlayers[0].jumped)
									oPlayers[0].driftinc = -1;
							}
							return false;
						case 40: // down
							oPlayers[0].speedinc -= 0.2;
							return false;
						case 69: // E
							oPlayers[1].speedinc = cp[oPlayers[1].personnage][0]*oPlayers[1].size;
							break;
						case 83: // S
							oPlayers[1].rotincdir = cp[oPlayers[1].personnage][2];
							if (!oPlayers[1].driftinc && !oPlayers[1].tourne && !oPlayers[1].fell) {
								if (oPlayers[1].jumped)
									oPlayers[1].driftinc = 1;
							}
							oPlayers[1].rotincdir = cp[oPlayers[1].personnage][2];
							break;
						case 70: // F
							oPlayers[1].rotincdir = -cp[oPlayers[1].personnage][2];
							if (!oPlayers[1].driftinc && !oPlayers[1].tourne && !oPlayers[1].fell) {
								if (oPlayers[1].jumped)
									oPlayers[1].driftinc = -1;
							}
							break;
						case 68: // D
							oPlayers[1].speedinc -= 0.2;
							break;
						case 17: // ctrl
						case 18: // alt
							if (pause) break;
							if (!oPlayers[0].z && !oPlayers[0].heightinc) {
								if (!oPlayers[0].driftinc && !oPlayers[0].tourne) {
									oPlayers[0].z = 1;
									oPlayers[0].heightinc = 0.5;
									oPlayers[0].jumped = true;
									if (oPlayers[0].rotincdir)
										oPlayers[0].driftinc = (oPlayers[0].rotincdir>0) ? 1:-1;
								}
							}
							else if (!oPlayers[0].jumped && !oPlayers[0].ctrled && !oPlayers[0].fell && !oPlayers[0].billball && !oPlayers[0].tourne && !oPlayers[0].figuring && !oPlayers[0].figstate)
								stuntKart(oPlayers[0]);
							break;
						case 80: // P
							if (!pause)	{
								if (!bCounting)	{
									document.getElementById("infos0").style.visibility = "visible";
									pause = true;
									pauseSounds();
								}
							}
							else
								reprendre(true);
							return false;
						case 71: // G
							if (pause) break;
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
						}
					}

				document.onkeyup = function(e) {
					switch (e.keyCode) {
						case 32: // space
							if (!oPlayers[0].tourne && !pause)
							arme(0);
							break;
						case 38: // up
							oPlayers[0].speedinc = 0;
							break;
						case 37: // left
							oPlayers[0].rotincdir = 0;
							break;
						case 39: // right
							oPlayers[0].rotincdir = 0;
							break;
						case 40: // down
							oPlayers[0].speedinc = 0;
							break;
						case toLanguage(65,81): // Q
							if (!oPlayers[1].tourne && !pause)
							arme(1);
							break;
						case 69: // E
							oPlayers[1].speedinc = 0;
							break;
						case 83: // S
							oPlayers[1].rotincdir = 0;
							break;
						case 70: // F
							oPlayers[1].rotincdir = 0;
							break;
						case 68: // D
							oPlayers[1].speedinc = 0;
							break;
						case 17: // ctrl
						case 18: // alt
							if (pause) break;
							if (oPlayers[0].driftinc) {
								oPlayers[0].driftinc = 0;
								if (oPlayers[0].driftcpt >= 15) {
									oPlayers[0].turbodrift = 15;
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
						case 71: // G
							if (oPlayers[1].driftinc) {
								oPlayers[1].driftinc = 0;
								if (oPlayers[1].driftcpt >= 15) {
									oPlayers[1].turbodrift = 15;
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
						case 27: // echap
						if (!bCounting)
							quitter();
							break;
						case 88: // X
							if (oPlayers[0].tours <= oMap.tours && !bCounting) {
								var nView = 180 - oPlayers[0].changeView;
								oPlayers[0].changeView = nView;
								oPlayers[0].sprite[0].setState(11);
							}
							break;
						case toLanguage(87,90): // Z
							if (!bCounting) {
								var nView = 180 - oPlayers[1].changeView;
								oPlayers[1].changeView = nView;
								oPlayers[1].sprite[0].setState(11);
							}
						}
					}
							
					document.onmousedown = function(e) {
						if (pause)
							return true;
						var idPlayer;
						if (e.button == 0)
							idPlayer = 0;
						else {
							idPlayer = 1;
							if (e.button == 2) {
								document.oncontextmenu = function() {
									this.oncontextmenu = null;
									return false;
								}
							}
						}
						if (!oPlayers[idPlayer].tourne)
							arme(idPlayer);
						return false;
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
					function revoir() {
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
							}
							else {
								if (oKart.aipoint == undefined) {
									oKart.aipoint = 0;
									oKart.arme = false;
									oKart.maxspeed = 5.7;
								}
								ai(oKart);
								move(i);
							}
						}
						timer++;
						if (!(timer%100))
							aKarts[0].changeView = Math.floor(Math.random()*18)*20;
 
						setTimeout((timer != iTrajet.length) ? revoir : function(){var oKart=aKarts[0];oKart.aipoint=0;oKart.changeView=180;oKart.maxspeed=5.7;oKart.speed=5.7;document.onkeyup=undefined;document.getElementById("infos0").style.visibility="visible";if(bMusic||iSfx){startEndMusic(0)}cycle()},67);
						render();
					}
					for (i=0;i<aKarts.length;i++)
						aKarts[i].cpu = true;
					revoir();
					pause = false;
					setTimeout(continuer,1000);
					document.onkeyup = function(e) {
						if (e.keyCode == 80 && !bCounting)
							document.getElementById("infos0").style.visibility = (document.getElementById("infos0").style.visibility == "hidden") ? "visible" : "hidden";
						else if (e.keyCode == 27)
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
				if (bMusic || iSfx) countDownMusic.play();
			}
		iCntStep++;
		setTimeout(fncCount,1000);
		}
	if (iSfx)
		setTimeout(startEngineSound,bMusic ? 2600:1100);
	setTimeout(fncCount,bMusic?3000:1500);

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
if (pause && fInfos[0][1]) {
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
	for (var i=0;i<strPlayer.length;i++) {
		var oViewCanvas = document.createElement("canvas");
		oViewCanvas.width=iViewCanvasWidth;
		oViewCanvas.height=iViewCanvasHeight;
		oViews.push(oViewCanvas);
	}
}

function reprendre(debug)	{
	setTimeout(function(){if (pause){pause=false;cycle()}}, 67);
	if (debug) {
		unpauseSounds();
		document.getElementById("infos0").style.visibility = "hidden";
	}
}

function quitter() {
	pause = true;
	removeGameMusics();
	for (var i=0;i<strPlayer.length;i++) {
		oContainers[i].innerHTML = "";
		document.getElementById("infos"+i).style.visibility = "hidden";
		document.getElementById("infoPlace"+i).style.display = "none";
		document.getElementById("compteur"+i).innerHTML = "";
		document.getElementById("temps"+i).innerHTML = "";
		document.getElementById("objet"+i).style.visibility =  "hidden";
		document.getElementById("roulette"+i).innerHTML = "";
		document.getElementById("lakitu"+i).style.display = "none";
		document.getElementById("drift"+i).style.display = "none";
		document.getElementById("infos"+i).style.opacity = 0.8;
		document.getElementById("infos"+i).style.color = "#FF9900";
		document.getElementById("scroller"+i).style.visibility="hidden";
	}
	document.getElementById("mariokartcontainer").style.opacity = 1;
	if (strPlayer.length == 1)
		removePlan();
	oBgLayers.length = 0;
	document.onmousedown = undefined;
	document.onkeydown = undefined;
	document.onkeyup = undefined;
	aPlayers = [];
	for (persos in cp)
		cp[persos][3] = 0;
	setTimeout(function(){pause=false;MarioKart()},500);
}


function classement()
{
var nbjoueurs = aKarts.length;
for (var i=0;i<nbjoueurs;i++) {
	var kart = aKarts[i];
	var ptsPlayer = cp[kart.personnage][3];
	var iPlaces = 1;
	for (var j=0;j<nbjoueurs;j++)	{
		var ptsKart = cp[aKarts[j].personnage][3];
		if (kart != aKarts[j] && ptsPlayer <= ptsKart)	{
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
	if (iPlaces == i)	 {
		iPlacement.push(aKarts[j].personnage);
		j = nbjoueurs;
		}
	}
}

document.getElementById("infos0").style.visibility="hidden";
for (var i=1;i<=iPlacement.length;i++) {
var tPlayer = iPlacement[(i-1)];
document.getElementById("fJ"+i).style.backgroundColor = (tPlayer != strPlayer[0]) ? ((tPlayer != strPlayer[1]) ? "" : "navy") : "#69F";
document.getElementById("fJ"+i).style.opacity = (tPlayer != strPlayer) ? "" : 0.8;
document.getElementById("j"+i).innerHTML = toPerso(tPlayer);
document.getElementById("pts"+i).innerHTML = cp[tPlayer][3];
	}
document.getElementById("octn").onclick = continuer;
	setTimeout(function() {
		document.getElementById("infos0").style.visibility = "visible";
		document.getElementById("octn").focus();
	}, 500);
}

function continuer()
{
document.getElementById("infos0").style.border = 0;
document.getElementById("infos0").style.top = iScreenScale * 10 + 10 +"px";
document.getElementById("infos0").style.left = Math.round(iScreenScale*20+10 + (strPlayer.length-1)/2*(iWidth*iScreenScale+2)) +"px";
document.getElementById("infos0").style.background = "transparent";
document.getElementById("infos0").style.fontSize = iScreenScale * 4 +"pt";
document.getElementById("infos0").innerHTML = '<tr><td id="continuer"></td></tr><tr><td'+ ((course != "CM") ? ' style="font-size: '+ iScreenScale * 3 +'px;">&nbsp;</td></tr>' : ' id="enregistrer"></td></tr><tr><td id="revoir"></td></tr><tr><td id="classement">') +'</td></tr><tr><td><input type="button" id="quitter" value="'+ toLanguage('QUIT', 'QUITTER') +'" style="font-size: '+ iScreenScale*3 +'pt; width: 100%;" /></td></tr>';

var oContinue = document.createElement("input");
oContinue.type = "button";
oContinue.style.fontSize = iScreenScale*3 + "pt";
oContinue.style.width = "100%";

if (course != "CM") {

	if (isCup) {
		if ((course == "GP") && (oMap == oMaps[3]))
			oContinue.value = toLanguage("           NEXT           ", "         SUIVANT          ");
		else
			oContinue.value = toLanguage("       NEXT RACE	   ", "COURSE SUIVANTE");
	}
	else
		oContinue.value = "        "+ toLanguage('  REPLAY', 'REJOUER') +"        ";
	oContinue.onclick = function() {
		pause = true;
		removeGameMusics();
		fInfos = [strPlayer, iDificulty];
		if (course == "GP") {
			fInfos[2] = oMaps.indexOf(oMap)+1;
			if (fInfos[2] >= oMaps.length) {
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
					if (nid) {
						xhr("cupsave.php", "cup="+ nid +"&pts="+(4-oPlace), function(reponse) {
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
									document.body.innerHTML += '<div style="position: absolute; left: '+ iScreenScale * 16 +'px; top: '+ iScreenScale * 30 +'px; text-align: center">Vous pouvez d&eacute;sormais<br />jouer avec '+ uwPerso +' !<br /><img src="'+ getWinnerSrc(newPerso) +'" style="width: '+ iScreenScale*4 +'px" /></div>';
								}
								return true;
							}
							return false;
						});
					}
					if (bMusic)
						startMusic("musics/menu/congrats.mp3",true,700);
				}
				else {
					if (bMusic)
						startMusic("musics/menu/toobad.mp3",true,700);
				}
				return;
			}
		}
		for (var i=0;i<strPlayer.length;i++) {
			oContainers[i].innerHTML = "";
			document.getElementById("infoPlace"+i).style.display = "none";
			document.getElementById("compteur"+i).innerHTML = "";
			document.getElementById("temps"+i).innerHTML = "";
			document.getElementById("objet"+i).style.visibility =  "hidden";
			document.getElementById("infos"+i).style.visibility = "hidden";
			document.getElementById("infos"+i).style.opacity = 0.8;
			document.getElementById("infos"+i).style.color = "#FF9900";
		}
		if (strPlayer.length == 1)
			removePlan();
		oBgLayers.length = 0;
		document.onmousedown = undefined;
		setTimeout(MarioKart, 500);
	}
}
else {
	var oSave = oContinue.cloneNode(false);
	var oReplay = oContinue.cloneNode(false);
	var oClassement = oContinue.cloneNode(false);

	oContinue.value = toLanguage('          RETRY          ', '     RÉESSAYER     ');
	oContinue.onclick = function() {
		pause = true;
		removeGameMusics();
		oContainers[0].innerHTML = "";
		document.getElementById("infoPlace0").style.display = "none";
		document.getElementById("compteur0").innerHTML = "";
		document.getElementById("temps0").innerHTML = "";
		document.getElementById("objet0").style.visibility = "hidden";
		fInfos = [strPlayer, oMaps.indexOf(oMap), iDificulty, false, gRecord];
		document.getElementById("infos0").style.visibility = "hidden";
		document.getElementById("infos0").style.opacity = 0.8;
		document.getElementById("infos0").style.color = "#FF9900";
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
				xhr("records.php", "nom="+nom+"&perso="+strPlayer[0]+"&map="+ oMap.id +"&temps="+iTrajet.length, function(reponse) {
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
			fInfos = [strPlayer, oMaps.indexOf(oMap), iTrajet, true, gRecord];
			document.getElementById("infos"+i).style.visibility = "hidden";
			document.getElementById("infos"+i).style.opacity = 0.8;
			document.getElementById("infos"+i).style.color = "#FF9900";
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
		open("classement.php?draw="+ oMap.id);
	}
	document.getElementById("classement").appendChild(oClassement);
}
document.getElementById("continuer").appendChild(oContinue);
oContinue.focus();
document.getElementById("quitter").onclick = quitter;
}


// setup canvas for holding the currently visible portion of the map
// this is the canvas used to draw from when rendering
var iViewCanvasHeight = 240; // these height, width and y-offset values 
var iViewCanvasWidth = 600; // have been adjusted to work with the current camera setup
var iViewYOffset = 10;
var oViews = new Array();


function Sprite(strSprite) 
{
	var oCtSprites = new Array();
	for (var i=0;i<strPlayer.length;i++) {
		this[i] = {};
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
		oContainers[i].appendChild(oSpriteCtr);
		
		this[i].i = i;

		this[i].draw = function(iX, iY, fScale, iZ) {
			if (!iZ)
				iZ = 0;
		
			var i = this.i;

			if (iY > iHeight * iScreenScale || (iY+iZ*iScreenScale) < 9 * iScreenScale || fScale < 0.18) {
				oCtSprites[i][0].style.display = "none";
				return;
			}

			oCtSprites[i][0].style.display = "block";

			var fSpriteSize = Math.round(32 * fSpriteScale * fScale);

			oCtSprites[i][0].style.left = Math.round(iX - fSpriteSize/2)+"px";
			oCtSprites[i][0].style.top = Math.round(iY - fSpriteSize/2)+"px";

			oCtSprites[i][1].style.height = fSpriteSize + "px";

			oCtSprites[i][0].style.width = fSpriteSize + "px";
			oCtSprites[i][0].style.height = fSpriteSize + "px";

			oCtSprites[i][1].style.left = -(fSpriteSize*oCtSprites[i][2])+"px";
		}

		this[i].setState = function(iState) {
			oCtSprites[this.i][2] = iState;
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
	for (var i=0;i<oContainers.length;i++) {
		oLayers[i] = document.createElement("div");
		oLayers[i].style.height = (10 * iScreenScale)+"px";
		oLayers[i].style.width = (iWidth * iScreenScale)+"px";
		oLayers[i].style.position = "absolute";
		(function(oLayer){setTimeout(function(){oLayer.style.backgroundImage="url('"+imageDims.src+"')"},500)})(oLayers[i]);
		oLayers[i].style.backgroundSize = "auto 100%";

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

	function render() {

		collisionTest = COL_OBJ;

		for (var i=0;i<strPlayer.length;i++) {
			if (oPlayers[i].tombe <= 10) {

				var posX = oPlayers[i].x;
				var posY = oPlayers[i].y;
				var oViewCanvas = oViews[i];
				var oViewCtx = oViewCanvas.getContext("2d");
		 
		 		var oFinish = oPlayers[i].tours == (oMap.tours+1);

				var fRotation = oPlayers[i].rotation;
				if (oFinish) {
					if (oPlayers[i].aTheta == undefined)
						oPlayers[i].aTheta = oPlayers[i].rotation;
					else {
						oPlayers[i].aTheta = oPlayers[i].aTheta + 360*Math.round((oPlayers[i].rotation-oPlayers[i].aTheta)/360);
						var mindth = Math.max(5,Math.abs(oPlayers[i].rotation-oPlayers[i].aTheta)-8);
						oPlayers[i].aTheta = oPlayers[i].aTheta + Math.min(Math.max(oPlayers[i].rotation-oPlayers[i].aTheta,-mindth),mindth);
					}
					fRotation = oPlayers[i].aTheta;
				}

				if (oFinish && oPlayers[i].changeView < 180)
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

						fSprite.sprite[i].draw(
							((iWidth/2) + fViewX) * iScreenScale, 
							(iHeight - iViewY) * iScreenScale,
							fFocal / (fFocal + fTransY) * fSprite.size,
							correctZ(fSprite.z)
						);

					}
				}
						for (var j=0;j<oMap.arme.length;j++)	{
						fSprite = oMap.arme[j];
						if (isNaN(fSprite[2]))	{
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
						else if (!i)
						{
					if (fSprite[2])fSprite[2]--;
					else fSprite[2] = new Sprite("objet");
						}
					}

						for (var j=0;j<oMap.decor.length;j++)	{
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

						for (var j=0;j<bananes.length;j++)	{
						fSprite = bananes[j];
						var fCamX = fSprite[1] - posX;
						var fCamY = fSprite[2] - posY;

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
							(iHeight - iViewY - correctZ(fSprite[3])) * iScreenScale,
							fFocal / (fFocal + (fTransY)) / 1.5
						);
					}

						for (var j=0;j<fauxobjets.length;j++)	{
						fSprite = fauxobjets[j];
						var fCamX = fSprite[1] - posX;
						var fCamY = fSprite[2] - posY;

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
							(iHeight - iViewY - correctZ(fSprite[3])) * iScreenScale,
							fFocal / (fFocal + (fTransY))
						);
					}

						for (var j=0;j<carapaces.length;j++)	{
						fSprite = carapaces[j];

						var fNewPosX;
						var fNewPosY;
				
						var fMoveX = 8 * direction(0, fSprite[4]), fMoveY = 8 * direction(1, fSprite[4]);
				
						if (!i && fSprite[4])	{
						fNewPosX = fSprite[1] + fMoveX;
						fNewPosY = fSprite[2] + fMoveY;

						fSprite[6] = fSprite[6] ? 0 : 1;
						for (var k=0;k<oPlayers.length;k++)
							fSprite[0][k].setState(fSprite[6]);
						}
						else	{
						fNewPosX = fSprite[1];
						fNewPosY = fSprite[2];
						}

						var fCamX = fSprite[1] - posX;
						var fCamY = fSprite[2] - posY;

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
							(iHeight - iViewY - correctZ(fSprite[3])) * iScreenScale,
							fFocal / (fFocal + (fTransY)) / 1.5
						);
		if (!i) {

			var roundX1 = Math.round(fSprite[1]);
			var roundY1 = Math.round(fSprite[2]);
			var roundX2 = Math.round(fNewPosX);
			var roundY2 = Math.round(fNewPosY);

			if ((fSprite[4] && tombe(roundX1, roundY1)) || touche_banane(roundX1, roundY1) || touche_banane(roundX2, roundY2) || touche_crouge(roundX1, roundY1) || touche_crouge(roundX2, roundY2) || touche_cverte(roundX1, roundY1, j) || touche_cverte(roundX2, roundY2, j))
			detruit(carapaces,j,true);

			else if (!fSprite[4] || canMoveTo(fSprite[1],fSprite[2], fMoveX,fMoveY)) {
				fSprite[1] = fNewPosX;
				fSprite[2] = fNewPosY;
				}
				else
				{
				fSprite[5]--;
				if (fSprite[5])	{
				var fNewTrajectoire = fSprite[4] - (fSprite[4] - 180)*2;
				fMoveX = 8 * direction(0, fNewTrajectoire);
				fMoveY = 8 * direction(1, fNewTrajectoire);
				if (canMoveTo(fSprite[1],fSprite[2], fMoveX,fMoveY))
				fSprite[4] = fNewTrajectoire;
				else	{
				fSprite[4] -= (fSprite[4] - 90)*2;
				if (fSprite[4] < 0) fSprite[4] += 360;
						}
					}
				else
				detruit(carapaces,j);
				}
			}
		}
				for (var j=0;j<carapacesRouge.length;j++)	{
					fSprite = carapacesRouge[j];
					
					if (!fSprite[0][i].div.style.opacity) {

						var fNewPosX;
						var fNewPosY;

						if (!i && fSprite[5])	{
							var fMoveX;
							var fMoveY;

						fSprite[4] = fSprite[4] ? 0 : 1;
						for (var k=0;k<oPlayers.length;k++)
							fSprite[0][k].setState(fSprite[4]);

							var iLocal = oMap.aipoints[0];
							if (fSprite[7] != undefined)	{
							fMoveX = iLocal[fSprite[7]][0] - fSprite[1];
							fMoveY = iLocal[fSprite[7]][1] - fSprite[2];
							var oBox = iLocal[fSprite[7]];
		if (fSprite[1] > oBox[0] - 10 && fSprite[1] < oBox[0] + 10 && fSprite[2] > oBox[1] - 10 && fSprite[2] < oBox[1] + 10)
								{
								if (fSprite[7] < iLocal.length - 1) fSprite[7]++;
								else fSprite[7] = 0;
								}
							var fNewMove = Math.sqrt(fMoveX*fMoveX + fMoveY*fMoveY)/8;
							fMoveX /= fNewMove;
							fMoveY /= fNewMove;
							}
							else	{
							for (var k=0;k<iLocal.length;k++)	{
							var oBox = iLocal[k];
							if (fSprite[1] > oBox[0] - 35 && fSprite[1] < oBox[0] + 35 && fSprite[2] > oBox[1] - 35 && fSprite[2] < oBox[1] + 35)	{
								if (k < iLocal.length - 1) fSprite[7] = k + 1;
								else fSprite[7] = 0;
								k = iLocal.length;
								}
							fMoveX = 8 * direction(0, fSprite[5]);
							fMoveY = 8 * direction(1, fSprite[5]);
							}
						}

						fNewPosX = fSprite[1] + fMoveX;
						fNewPosY = fSprite[2] + fMoveY;

						var tCible;
						var maxDist = 1000;

						for (var k=0;k<aKarts.length;k++)	{
						var pCible = aKarts[k];
						if (k != fSprite[6] && !aKarts[k].tombe)	{
						var fDist = Math.pow(pCible.x-fNewPosX, 2) + Math.pow(pCible.y-fNewPosY, 2);
						if (fDist < maxDist)	{
							fNewPosX = pCible.x;
							fNewPosY = pCible.y;
							maxDist = fDist;
							tCible = pCible;
							}
						}
						if (tCible && tCible.using[0] && (tCible.using[0] != fauxobjets))	{
							var rAngle = Math.atan2(fSprite[2]-fNewPosY,fSprite[1]-fNewPosX) - (90-tCible.rotation)*Math.PI/180;
							var pi2 = Math.PI*2;
							while (rAngle < 0)
								rAngle += pi2;
							while (rAngle > pi2)
								rAngle -= pi2;
							if (rAngle > Math.PI)
								rAngle = pi2-rAngle;
							if (Math.abs(rAngle) > 2) {
								fSprite[0][i].div.style.opacity = 0.8;
								fNewPosX -= 5 * direction(0,tCible.rotation);
								fNewPosY -= 5 * direction(1,tCible.rotation);
								detruit(tCible.using[0],tCible.using[1],true);
							}
							else {
								tCible.using[0][tCible.using[1]][1] -= 2 * direction(0,tCible.rotation);
								tCible.using[0][tCible.using[1]][2] -= 2 * direction(1,tCible.rotation);
							}
						}
					}
			fNewPosX = Math.round(fNewPosX);
			fNewPosY = Math.round(fNewPosY);
				}
			else	{
			fNewPosX = fSprite[1];
			fNewPosY = fSprite[2];
				}


			if (i || ((fSprite[6] == undefined || (!tombe(fNewPosX, fNewPosY) && canMoveTo(fSprite[1],fSprite[2], fMoveX,fMoveY))) && !touche_banane(fNewPosX, fNewPosY) && !touche_banane(fSprite[1], fSprite[2]) && !touche_crouge(fNewPosX, fNewPosY, j) && !touche_crouge(fSprite[1], fSprite[2], j) && !touche_cverte(fNewPosX, fNewPosY) && !touche_cverte(fSprite[1], fSprite[2]))) {
				fSprite[1] = fNewPosX;
				fSprite[2] = fNewPosY;

						var fCamX = fSprite[1] - posX;
						var fCamY = fSprite[2] - posY;

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
							(iHeight - iViewY - correctZ(fSprite[3])) * iScreenScale,
							fFocal / (fFocal + (fTransY)) / 1.5
						);
					}
				else
					fSprite[0][i].div.style.opacity = 0.8;
				}
				else if (!i) {
					var setOpac = fSprite[0][0].div.style.opacity-0.2;
					for (var k=0;k<strPlayer.length;k++)
						fSprite[0][k].div.style.opacity = setOpac;
					if (setOpac < 0.01)
						detruit(carapacesRouge,j);
				}
			}


						for (var j=0;j<carapacesBleue.length;j++)	{
						fSprite = carapacesBleue[j];

						var fCamX = fSprite[1] - posX;
						var fCamY = fSprite[2] - posY;

						var fRotRad = fRotation * Math.PI / 180;

						var fTransX = fCamX * Math.cos(fRotRad) - fCamY * Math.sin(fRotRad);
						var fTransY = fCamX * Math.sin(fRotRad) + fCamY * Math.cos(fRotRad);

						var iDeltaY = -iCamHeight;
						var iDeltaX = iCamDist + fTransY;

						var iViewY = ((iDeltaY / iDeltaX) * iCamDist + iCamHeight) - iViewHeight;
						var fViewX = -(fTransX / (fTransY + iCamDist)) * iCamDist;

						var fMoveX = fSprite[1] - aKarts[fSprite[3]].x;
						var fMoveY = fSprite[2] - aKarts[fSprite[3]].y;

						var size = 1;
					if (fSprite[5] > 0)	{
						if (!i) {
							if (Math.abs(fMoveX*fMoveY) > 100)	{
							var fNewMove = Math.sqrt(Math.pow(fMoveX,2) + Math.pow(fMoveY,2))/10;
							fMoveX /= fNewMove;
							fMoveY /= fNewMove;

							fSprite[4] = fSprite[4] ? 0 : 1;
							for (var k=0;k<oPlayers.length;k++)
								fSprite[0][k].setState(fSprite[4]);
							}
							else	{
							fSprite[0][i].setState(Math.round(Math.random()));
							fSprite[5]--;
							if (fSprite[5])	{
							fViewX += fSprite[5] - 2.5;
							iViewY -= Math.abs(5-fSprite[5]);
							}
							else	{
							var fLoad;
							for (var k=0;k<strPlayer.length;k++) {
								fSprite[0][k].img.src = "images/sprites/sprite_explosionB_smooth.png";
								if (fSprite[0][k].div.style.display == "block")
									fLoad = k;
							}
							if (fLoad != undefined) {
									fSprite[0][fLoad].img.onload = function() {
										bCounting = false;
										fSprite[0][fLoad].img.onload = undefined;
										reprendre(false);
										playDistSound(aKarts[fSprite[3]],"musics/events/boom.mp3",200);
									}
								bCounting = true;
								pause = true;
							}
							else
								playDistSound(aKarts[fSprite[3]],"musics/events/boom.mp3",200);
							fSprite[4] = 1;
							fMoveX *= aKarts[fSprite[3]].speed/2;
							fMoveY *= aKarts[fSprite[3]].speed/2;
							fSprite[0][i].setState(0);
							fSprite[0][i].div.style.opacity = 1;
								}
							}

							fSprite[1] -= fMoveX;
							fSprite[2] -= fMoveY;
						}
					}
					else	{
						if (!bCounting)
							size = 10;
						if (!i) {
							fSprite[5]--;
							fSprite[0][i].div.style.opacity -= 0.1;
							if (fSprite[5] < -10)	{
								detruit(carapacesBleue,j);
								size = false;
								}
							}
						}

					if (size)	{

						fSprite[0][i].div.style.zIndex = Math.round(10000 - fTransY);

						fSprite[0][i].draw(
							((iWidth/2) + fViewX) * iScreenScale, 
							(iHeight - iViewY - (fSprite[5] > 0 ? 15 + aKarts[fSprite[3]].speed : 0)) * iScreenScale,
							fFocal / (fFocal + (fTransY)) * size
						);
					}
				}

						for (var j=0;j<bobombs.length;j++)	{
						fSprite = bobombs[j];

						var fCamX = fSprite[1] - posX;
						var fCamY = fSprite[2] - posY;

						var fRotRad = fRotation * Math.PI / 180;

						var fTransX = fCamX * Math.cos(fRotRad) - fCamY * Math.sin(fRotRad);
						var fTransY = fCamX * Math.sin(fRotRad) + fCamY * Math.cos(fRotRad);

						var iDeltaY = -iCamHeight;
						var iDeltaX = iCamDist + fTransY;

						var iViewY = ((iDeltaY / iDeltaX) * iCamDist + iCamHeight) - iViewHeight;
						var fViewX = -(fTransX / (fTransY + iCamDist)) * iCamDist;

						var size = 1;
						var hauteur = 0;
					if (fSprite[4])	{
						if (fSprite[5])	{
							if (!i) {
							fSprite[5]--;
							var fMoveX = 15 * direction(0, fSprite[4]);
							var fMoveY = 15 * direction(1, fSprite[4]);

							var fNewPosX = fSprite[1] + fMoveX;
							var fNewPosY = fSprite[2] + fMoveY;

							fSprite[1] = fNewPosX;
							fSprite[2] = fNewPosY;
							}
							hauteur = fSprite[5];
						}
						else	{
						if (tombe(Math.round(fSprite[1]), Math.round(fSprite[2])))	{
						detruit(bobombs, j);
						size = false;
						}
						if (!i)
							fSprite[6]--;
						if (!fSprite[6])	{
							if (!i) {
							var fLoad;
							for (var k=0;k<strPlayer.length;k++) {
								fSprite[0][k].img.src = "images/sprites/sprite_explosion_smooth.png";
								if (fSprite[0][k].div.style.display == "block")
									fLoad = k;
							}
							if (fLoad != undefined) {
								fSprite[0][fLoad].img.onload = function() {
									bCounting = false;
									fSprite[0][fLoad].img.onload = undefined;
									reprendre(false);
									playDistSound({x:fSprite[1],y:fSprite[2]},"musics/events/boom.mp3",200);
								}
								bCounting = true;
								pause = true;
							}
							else
								playDistSound({x:fSprite[1],y:fSprite[2]},"musics/events/boom.mp3",200);
							fSprite[0][i].div.style.opacity = 1;
							}
						}
						if (fSprite[6] <= 0)	{
							if (!bCounting)
								size = 10;
								if (!i) {
								fSprite[0][i].div.style.opacity -= 0.1;
								if (fSprite[6] < -10)	{
									detruit(bobombs,j);
									size = false;
									}
								}
							}
						}
					}
						if (size)	{
						fSprite[0][i].div.style.zIndex = Math.round(10000 - fTransY);

						var spriteZ = correctZ(fSprite[3] + (- Math.abs(hauteur-8) + 8) * 2);

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

	function correctZ(z) {
		return 2*Math.pow(z/2,0.7);
	}

	function direction(fDir, rotation)	{
		return Math[["sin","cos"][fDir]](rotation * Math.PI / 180)
	}
	function randObj(oKart) {
		return objets[Math.floor(Math.random()*120/aKarts.length+(oKart.place-1)*120/aKarts.length)];
	}

	function detruit(cible, id, sound) {
		if (cible[id]) {
			cible[id][0][0].suppr();
			for (var i=0;i<aKarts.length;i++) {
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
	}

	function supprArme(i) {
		var oKart = aKarts[i];
		oKart.arme = false;
		oKart.roulette = 0;
		if (!oKart.cpu)	{
			document.getElementById("roulette"+i).innerHTML = "";
			document.getElementById("scroller"+i).style.visibility = "hidden";
			removeIfExists(oKart.rouletteSound);
		}
	}

	function stopDrifting(i) {
		var oKart = aKarts[i];
		if (!oKart.cpu) {
			oKart.driftinc = 0;
			oKart.driftcpt = 0;
			oKart.turbodrift = 0;
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
		for (var i=0;i<strPlayer.length;i++) {
			if (oKart != oPlayers[i] && Math.pow(oKart.x-oPlayers[i].x, 2) + Math.pow(oKart.y-oPlayers[i].y, 2) < 25 && Math.abs(oKart.z - oPlayers[i].z) < 2 && !oKart.tourne && Math.abs(oPlayers[i].speed - oKart.speed) < 2) {
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

	function canMoveTo(iX,iY, iI,iJ, iP) {

		var nX = iX+iI, nY = iY+iJ;

		for (var i=0;i<oMap.decor.length;i++) {
			var oBox = oMap.decor[i];
			if (oBox[3] && (oBox[3] >= 4))
				continue;
			if (iX > oBox[0]-5 && iX < oBox[0]+5 && iY > oBox[1]-5 && iY < oBox[1]+5) {
				if (iP) {
					oMap.decor[i][2][0].suppr();
					oMap.decor.splice(i,1);
				}
				return true;
			}
			if (nX > oBox[0]-5 && nX < oBox[0]+5 && nY > oBox[1]-5 && nY < oBox[1]+5 && (!oBox[3]||oBox[3]<4)) {
				if (iP) {
					oMap.decor[i][2][0].suppr();
					oMap.decor.splice(i,1);
					return true;
				}
				return false;
			}
		}

		if (!oMap.collision) return true;
		
		for (var i=0;i<oMap.collision.length;i++) {
			var oBox = oMap.collision[i];
			if (iX > oBox[0] && iX < oBox[2] && iY > oBox[1] && iY < oBox[3])
				return true;
		}
		
		var aPos = [iX, iY], aMove = [iI, iJ];
		var dir = [(iI>0), (iJ>0)];

		for (var i=0;i<oMap.collision.length;i++) {
			var oBox = oMap.collision[i];
			for (var j=0;j<2;j++) {
				var l = dir[j];
				if ((l ? ((aPos[j] <= oBox[j])&&((aPos[j]+aMove[j]) >= oBox[j])):((aPos[j] >= (oBox[j+2]))&&((aPos[j]+aMove[j]) <= (oBox[j+2]))))) {
					var dim = 1-j;
					var croiseJ = aPos[dim] + ((l?oBox[j]:oBox[j+2])-aPos[j])*aMove[dim]/aMove[j];
					if ((croiseJ >= oBox[dim]) && (croiseJ <= (oBox[dim+2])))
						return false;
				}
			}
		}
		return true;
	}

	function getLineHorizontality(iX,iY, iI,iJ, lines) {
		var nX = iX+iI, nY = iY+iJ;
		for (var i=0;i<lines.length;i++) {
			var line = lines[i];
			var cross = secants(iX,iY,nX,nY,line.x1,line.y1,line.x2,line.y2);
			if (cross) {
				return {
					"dir" : (line.x1 == line.x2) ? "v":"h",
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
			var colLine = getLineHorizontality(iX,iY, iI,iJ, lines);
			if (colLine && (colLine.t < nearCol.t))
				nearCol = colLine;
		}
		if (oMap.collision) {
			for (var i=0;i<oMap.collision.length;i++) {
				var oBox = oMap.collision[i];
				var lines = [{
					"x1" : oBox[0],
					"y1" : oBox[1],
					"x2" : oBox[2],
					"y2" : oBox[1]
				},{
					"x1" : oBox[0],
					"y1" : oBox[1],
					"x2" : oBox[0],
					"y2" : oBox[3]
				},{
					"x1" : oBox[2],
					"y1" : oBox[1],
					"x2" : oBox[2],
					"y2" : oBox[3]
				},{
					"x1" : oBox[0],
					"y1" : oBox[3],
					"x2" : oBox[2],
					"y2" : oBox[3]
				}];
				var colLine = getLineHorizontality(iX,iY, iI,iJ, lines);
				if (colLine && (colLine.t < nearCol.t))
					nearCol = colLine;
			}
		}
		return nearCol.dir;
	}

	function objet(iX, iY) {
		for (var i=0;i<oMap.arme.length;i++) {
			var oBox = oMap.arme[i];
			if (iX > oBox[0] - 7 && iX < oBox[0] + 7 && iY > oBox[1] - 7 && iY < oBox[1] + 7 && isNaN(oBox[2]))	{
					for (var i=0;i<strPlayer.length;i++)
						oBox[2][i].div.style.display = "none";
					oBox[2] = 20;
					return true;
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
			if (iX >= oBox[0] && iX < (oBox[2]+1) && iY >= oBox[1] && iY < (oBox[3]+1))
				return (oBox[2]-oBox[0] + oBox[3]-oBox[1]);
			for (var j=0;j<2;j++) {
				var l = dir[j];
				if (l ? ((aPos[j] <= oBox[j])&&((aPos[j]+aMove[j]) >= oBox[j])):((aPos[j] >= (oBox[j+2]+1))&&((aPos[j]+aMove[j]) <= (oBox[j+2]+1)))) {
					var dim = 1-j;
					var croiseJ = aPos[dim] + ((l?oBox[j]:oBox[j+2]+1)-aPos[j])*aMove[dim]/aMove[j];
					if ((croiseJ >= oBox[dim]) && (croiseJ <= oBox[dim+2]+1))
						return (oBox[2]-oBox[0] + oBox[3]-oBox[1]);
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
				if (iX >= oBox[0] && iX <= oBox[0] + oBox[2] && iY >= oBox[1] && iY <= oBox[1] + oBox[3])
					return type;
			}
		}
		return false;
	}
	function accelere(iX, iY) {
	if (!oMap.accelerateurs)	return false;
		for (var i=0;i<oMap.accelerateurs.length;i++) {
			var oBox = oMap.accelerateurs[i];
			if (iX > oBox[0] && iX < oBox[0] + 9 && iY > oBox[1] && iY < oBox[1] + 9)
					return true;
			}
		return false;
		}
	function tombe(iX, iY) {
		if (iX > oMap.dimensions[0] || iY > oMap.dimensions[1] || iX < 0 || iY < 0) return [oMap.startposition[0],oMap.startposition[1],2];

		if (!oMap.trous.length) return false;

		for (var i=0;i<oMap.trous.length;i++) {
			var oBox = oMap.trous[i];
			var fTrou;
			if (iX >= oBox[0] && iX <= oBox[2] && iY >= oBox[1] && iY <= oBox[3])
				return [oBox[4],oBox[5],oBox[6]];
		}
	}
	var COL_KART = 0, COL_OBJ = 1;
	var collisionTest;
	function touche_banane(iX, iY, iP) {
		for (var i=0;i<bananes.length;i++) {
			var oBox = bananes[i];
			if (i != iP && !oBox[3])	{
			if (iX > oBox[1]-4 && iX < oBox[1]+4 && iY > oBox[2]-4 && iY < oBox[2] + 4)	{
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
			if (i != iP && !oBox[3])	{
			if (iX > oBox[1]-4 && iX < oBox[1]+4 && iY > oBox[2]-4 && iY < oBox[2] + 4)	{
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
			if (i != iP && !oBox[3])	{
			if (iX > oBox[1]-5 && iX < oBox[1]+5 && iY > oBox[2]-5 && iY < oBox[2] + 5)	{
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
			if (!oBox[0][0].div.style.opacity) {
				if (i != iP && !oBox[3])	{
					if (oBox[5] && iX == oBox[1] && iY == oBox[2])	{
						for (var i=0;i<strPlayer.length;i++)
							oBox[0][i].div.style.opacity = 0.8;
						return true;
					}
					else if (!oBox[5] && iX > oBox[1]-5 && iX < oBox[1]+5 && iY > oBox[2]-5 && iY < oBox[2] + 5) {
						detruit(carapacesRouge,i,(collisionTest==COL_OBJ));
						return true;
					}
				}
			}
		}
	return false;
	}
	function touche_bobomb(iX, iY, iP) {
		for (var i=0;i<bobombs.length;i++) {
			var oBox = bobombs[i];
	if (!oBox[3] && i != iP)	{
		if (oBox[4])	{
				if (!oBox[5] && iX > oBox[1]-30 && iX < oBox[1]+30 && iY > oBox[2]-30 && iY < oBox[2]+30)	{
					if (oBox[6] <= 0)
					return (oBox[6] < -5 ? 42 : 84);
					else
					oBox[6] = 1;
					}
				}
			else	{
				if (iX > oBox[1]-5 && iX < oBox[1]+5 && iY > oBox[2]-5 && iY < oBox[2] + 5)	{
					for (j=0;j<aKarts.length;j++)	{
						if (i==aKarts[j].using[1])	{
							aKarts[j].using=[false]; j=aKarts.length
							}
						}
					oBox.push(1,0,1)
					}
				}
			}
		}
	return false;
	}
	function touche_cbleue(iX, iY) {
		for (var i=0;i<carapacesBleue.length;i++) {
			var oBox = carapacesBleue[i];
	if (oBox[5] < 0)	{
				if (iX > oBox[1]-30 && iX < oBox[1]+30 && iY > oBox[2]-30 && iY < oBox[2]+30)
					return (oBox[5] < -5 ? 42 : 84);
				}
			}
		return false;
		}

	function colKart(oKart)	{
	for (var i=0;i<aKarts.length;i++)	{
		var kart = aKarts[i];
		if (kart != oKart && Math.pow(kart.x-oKart.x, 2) + Math.pow(kart.y-oKart.y, 2) < 25 && !kart.tourne && (!kart.protect || (kart.megachampi && !oKart.megachampi))) {
			stopDrifting(i);
			spinKart(kart,62);
			if (kart.using[0]) {
				if (kart.using[0][kart.using[1]][3])
					kart.using[0][kart.using[1]][3] = 0;
				kart.using = [false];
			}
			supprArme(i);
			}
		}
	}

function spinKart(oKart,nb) {
	if (!oKart.tourne)
		playDistSound(oKart,"musics/events/spin.mp3",(course=="BB")?80:50);
	oKart.tourne = nb;
}
if (!Math.hypot) Math.hypot = function(x,y){return Math.sqrt(x*x+y*y)};
function distKart(obj) {
	var res = Infinity;
	for (var i=0;i<oPlayers.length;i++) {
		var oPlayer = oPlayers[i];
		if (!oPlayer.cpu) {
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
	playIfShould(oKart, "musics/events/stunt.mp3");
}

function places(j)	{
var oKart = aKarts[j];
var retour = true;
for (var i=0;i<strPlayer.length;i++) {
	if (!oPlayers[i].cpu)
		retour = false;
}
if (retour) return;
if (oKart.tours > oMap.tours || !oMap.checkpoint.length) return;
var dest = oKart.demitours+1;
if (dest == oMap.checkpoint.length) dest = 0;
var iLine = oMap.checkpoint[dest][3];
var score = oKart.tours * oMap.checkpoint.length  +  getCpScore(oKart)  -  Math.abs(oKart[(iLine ? "y" : "x")]-oMap.checkpoint[dest][iLine]) / 1000;
var place = 1;
for (var i=0;i<aKarts.length;i++)	{
	var kart = aKarts[i];
	dest = kart.demitours+1;
	if (dest == oMap.checkpoint.length) dest = 0;
	iLine = oMap.checkpoint[dest][3];
	if (kart != oKart && kart.tours * oMap.checkpoint.length  +  getCpScore(kart)  -  Math.abs(kart[(iLine ? "y" : "x")]-oMap.checkpoint[dest][iLine]) / 1000 > score)
	place++;
	}
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

	function checkpoint(kart, aPosX,aPosY) {
		if (!oMap.checkpoint.length) return false;
			var demitour = kart.demitours;
		var iCP = getNextCp(kart);
		var jCP = (iCP?iCP:oMap.checkpoint.length)-1;
		for (var i=0;i<oMap.checkpoint.length;i++) {
			var oBox = oMap.checkpoint[i];
			if (kart.x > oBox[0] && kart.x <= (oBox[3] ? oBox[2] : oBox[0]+15) && kart.y > oBox[1] && kart.y <= (oBox[3] ? oBox[1]+15 : oBox[2])) {
				if (aPosX > oBox[0] && aPosX <= (oBox[3] ? oBox[2] : oBox[0]+15) && aPosY > oBox[1] && aPosY <= (oBox[3] ? oBox[1]+15 : oBox[2]))
					continue;
				if (i==iCP && demitour==jCP)
				return true;
				else if (demitour == i-1 || demitour == i+1)	{
				kart.demitours = i;
				return false;
				}
				else if (i==0 && demitour == oMap.checkpoint.length-1)	{
				kart.demitours = i;
				return false;
				}
			}
		}
		return false;

	}

	function move(getId) {
collisionTest = COL_KART;
var oKart = aKarts[getId];
if (!oKart.cpu)	{
	if (!getId) {
		if (millisec >= 1000)	{
			millisec -= 1000;
			sec++;
			if (sec >= 60)	{
				sec = 0;
				min++;
				}
			}
		if (millisec < 10)
		millisec = "00"+ millisec;
		else if (millisec < 100)
		millisec = "0"+ millisec;
		if (sec < 10)	{
			sec = "0"+ sec;
			}
		var tps = toLanguage("&nbsp;Time", "Temps") +": "+ min +":"+ sec +"."+ millisec;
		for (var i=0;i<strPlayer.length;i++)
			document.getElementById("temps"+i).innerHTML = tps;
		timer++;
		millisec = parseInt(millisec);
		millisec += 67;
		sec = parseFloat(sec);
		millisec = parseFloat(millisec);
	}

	if (oKart.time)	{
		oKart.time--;
		document.getElementById("lakitu"+getId).style.left = Math.round(iScreenScale * (20-oKart.time/5) + 10 + getId * (iWidth*iScreenScale+2))+"px";
		document.getElementById("lakitu"+getId).style.top = Math.round((-(Math.abs(oKart.time - 20)) + 20) * (iScreenScale - 2)) +"px";

		if (oKart.time && !oPlayers[getId].changeView)
		document.getElementById("lakitu"+getId).style.display = "block";
		else
		document.getElementById("lakitu"+getId).style.display = "none";
		}
	}

	if (oKart.tombe)	{
		oKart.tombe--;
		oKart.size = 1;
		updateDriftSize(getId);
		if (oKart.tombe == 19)
			playIfShould(oKart, "musics/events/rescue.mp3");
		if (oKart.tombe == 0) {
			for (var i=0;i<strPlayer.length;i++)
				oKart.sprite[i].img.style.display = "block";
		}
		if (oKart == oPlayers[getId])
		oContainers[getId].style.opacity = Math.abs(oKart.tombe-10)/10;
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

		else if (oKart.tourne)	{
		oKart.figuring = false;
		oKart.figstate = 0;
		oKart.speed = oKart.speed / 1.2 - oKart.speedinc;
		oKart.rotincdir = 0;
		oKart.tourne -= 2;
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

		if (!oKart.cpu) {
			var oSprite = oKart.sprite[getId];
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
			else	{
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
			if (oKart.cpu && tombe(posx_arrondi, posy_arrondi) && !sauts(aPosX,aPosY,fMoveX,fMoveY)) {
				oKart.z = 1;
				oKart.heightinc = 0.5;
			}
		}
		else {
			oKart.z += 0.7 * oKart.heightinc * Math.abs(oKart.heightinc);
			oKart.heightinc -= 0.5;
			if (oKart.z <= 0) {
				oKart.heightinc = 0;
				oKart.z = 0;
				if (!oKart.cpu) {
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

		var pExplose = touche_bobomb(posx_arrondi, posy_arrondi, (oKart.using[0]==bobombs ? oKart.using[1]:-1)) + touche_cbleue(posx_arrondi, posy_arrondi);
		if (pExplose && !oKart.tourne && !oKart.protect) {
			spinKart(oKart, pExplose);
			if (oKart.using[0]) {
				oKart.using[0][oKart.using[1]][3] = 0;
				oKart.using = [false];
			}
			stopDrifting(getId);
			if (pExplose == 84)	{
				oKart.speed = 0;
				oKart.heightinc = 3;
				supprArme(getId);
			}
		}
	else if (oKart.z < 5)	{
		if ((touche_fauxobjet(posx_arrondi, posy_arrondi, (oKart.using[0]==fauxobjets ? oKart.using[1]:-1)) || (touche_cverte(posx_arrondi, posy_arrondi, (oKart.using[0]==carapaces ? oKart.using[1]:-1)) || touche_cverte(Math.round(oKart.x), Math.round(oKart.y), (oKart.using[0]==carapaces ? oKart.using[1]:-1))) || touche_crouge(Math.round(oKart.x), Math.round(oKart.y), (oKart.using[0]==carapacesRouge ? oKart.using[1]:-1))) && !oKart.protect)	{
			stopDrifting(getId);
			spinKart(oKart,42);
			oKart.using = [false];
		}
		else if (touche_banane(posx_arrondi, posy_arrondi, (oKart.using[0]==bananes ? oKart.using[1]:-1)) && !oKart.protect)	{
			stopDrifting(getId);
			spinKart(oKart,20);
			if (oKart.using[0]) {
				if (oKart.using[0][oKart.using[1]][3])
					oKart.using[0][oKart.using[1]][3] = 0;
				oKart.using = [false];
			}
		}
	}

	var rScroller, rHeight, rSize;
	if (!oKart.cpu) {
		var rScroller = document.getElementById("scroller"+getId).getElementsByTagName("div")[0];
		var rHeight = rScroller.offsetHeight;
		rSize = iScreenScale*8;
	}
	if (objet(posx_arrondi, posy_arrondi)) {
		if (!oKart.destroySound) {
			oKart.destroySound = playDistSound(oKart, "musics/events/item_destroy.mp3", 80);
			if (oKart.destroySound) {
				oKart.destroySound.onended = function() {
					document.body.removeChild(this);
					oKart.destroySound = undefined;
				}
			}
		}
		if (!oKart.arme && oKart.tours <= oMap.tours && !oKart.billball) {
			var iObj = randObj(oKart);
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
			oKart.arme = iObj;
			if (shouldPlaySound(oKart))
				oKart.rouletteSound = playSoundEffect("musics/events/roulette.mp3");
			if (!oKart.cpu) {
				document.getElementById("scroller"+getId).getElementsByTagName("div")[0].style.top = -Math.floor(Math.random()*rHeight) +"px";
				document.getElementById("scroller"+getId).style.visibility="visible";
			}
			if (!oKart.cpu)
				document.getElementById("scroller"+getId).style.visibility="visible";
		}
	}
	if (oKart.arme && oKart.roulette != 25)
		{
		if (!oKart.cpu) {
			var nTop = (parseInt(rScroller.style.top) + Math.round(iScreenScale*3.5));
			if (nTop > 0)
				nTop += rSize-rHeight;
			rScroller.style.top = nTop +"px";
		}
		oKart.roulette++;
		if (oKart.roulette >= 25)
			{
		oKart.roulette = 25;
		if (!oKart.cpu)	{
		document.getElementById("scroller"+getId).style.visibility="hidden";
		document.getElementById("roulette"+getId).innerHTML = '<img alt="." src="images/objects/'+ oKart.arme +'.gif" style="width: '+ Math.round(iScreenScale * 8 - 3)+'px;" />';
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
			if (oKart.cpu)
				oKart.horizontality = getHorizontality(aPosX,aPosY, fMoveX,fMoveY);
		}
		
		if (!oKart.speedinc)
			oKart.speed *= oKart.sliding ? 0.95:0.9;

	oKart.sliding = undefined;
	if (!oKart.z)	{
		if (!oKart.heightinc) {
			oKart.ctrled = false;
			oKart.fell = false;
		}
		if (oKart.figuring) {
			oKart.turbodrift = 15;
			oKart.driftcpt = 0;
		}
		var pJump = sauts(aPosX, aPosY, fMoveX, fMoveY);
		if (pJump && !oKart.tourne)	{
			oKart.heightinc = pJump / 30 + 1.5;
			oKart.speed = 11;
			oKart.figuring = false;
			oKart.figstate = 0;
			if (!oKart.bounceSound) {
				oKart.bounceSound = playIfShould(oKart, "musics/events/jump.mp3");
				if (oKart.bounceSound) {
					oKart.bounceSound.onended = function() {
						document.body.removeChild(this);
						oKart.bounceSound = undefined;
					}
				}
			}
		}
		else {
			var hpType;
			var fTombe = tombe(Math.round(oKart.x), Math.round(oKart.y));
			if (fTombe)	{
			oKart.x = fTombe[0];
			oKart.y = fTombe[1];
			oKart.rotation = fTombe[2]*90;
			oKart.speed = 0;
			oKart.rotinc = 0;
			oKart.rotincdir = 0;
			oKart.protect = false;
			oKart.figuring = false;
			oKart.figstate = 0;
			oKart.fell = true;
			stopDrifting(getId);
			supprArme(getId);
			if (oKart.using)
			detruit(oKart.using[0],oKart.using[1]);
			oKart.champi = 0;
			if (oKart.cpu && oKart.aipoints.length) {
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
						ai(getId);
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

			oKart.tombe = 20;
			oKart.ctrled = true;
			oKart.z = 10;
			oKart.tourne = 0;
			for (var i=0;i<strPlayer.length;i++) {
				oKart.sprite[i].div.style.backgroundImage = "";
				oKart.sprite[i].img.style.display = "none";
				if (oKart.etoile)
					oKart.sprite[i].img.src = getSpriteSrc(oKart.personnage);
				}
			resetPowerup(oKart);
			playIfShould(oKart, "musics/events/fall.mp3")
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


	if (oKart.using[0])	{
		var oArme = oKart.using[0][oKart.using[1]];
		oArme[1] = (oKart.x - 5 * direction(0, oKart.rotation));
		oArme[2] = (oKart.y - 5 * direction(1, oKart.rotation));
		oArme[3] = oKart.z;
	}

	if (checkpoint(oKart, aPosX,aPosY))	{
		var nbjoueurs = aKarts.length;

		oKart.demitours = getNextCp(oKart);
		oKart.tours++;
		if (oKart.tours == (oMap.tours+1)) {
			oKart.place = 0;
			for (var i=0;i<nbjoueurs;i++) {
				if (aKarts[i].tours > oMap.tours)
					oKart.place++;
			}
				
			if (!oKart.cpu) {
				if (course != "CM") {
					var nPlaces = new Array();
					aPlayers = [];
					for (var i=0;i<nbjoueurs;i++) {
						var iPlace = aKarts[i].place, nPlace = 0;
						for (var j=0;j<nbjoueurs;j++) {
							var jPlace = aKarts[j].place;
							if ((i>j) ? (jPlace<=iPlace):(jPlace<iPlace))
								nPlace++;
						}
						aPlayers[nPlace] = aKarts[i].personnage;
						nPlaces.push(nPlace+1);
					}
					for (var i=0;i<nbjoueurs;i++)
						aKarts[i].place = nPlaces[i];
					document.getElementById("infoPlace"+getId).innerHTML = toPlace(oKart.place);
					document.getElementById("lakitu"+getId).style.display = "none";
				}

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
				oKart.rotinc = 0;
				oKart.rotincdir = 0;
				oKart.aipoint = 0;
				oKart.maxspeed = 5.7;
				document.getElementById("lakitu"+getId).style.display = "none";
				if (!oPlayers[1-getId] || oPlayers[1-getId].cpu) {
					document.onkeydown = undefined;
					document.onkeyup = undefined;
					document.onmousedown = undefined;

					if (course != "CM") {

						var positions = '<tr style="font-size: '+ iScreenScale * 2 +'px; background-color: white; color: black;"><td>Places</td><td>'+ toLanguage('Player','Joueur') +'</td><td>Pts</td></tr>';
						for (var i=1;i<=nbjoueurs;i++) {
							for (var j=0;j<nbjoueurs;j++) {
								var joueur = aKarts[j].personnage;
								if (aKarts[j].place == i) {
									var ptsInc = [10,8,6,4,3,2,1,0][Math.round((i-1)*7/(aKarts.length-1))];
									positions += '<tr id="fJ'+i+'" '+ (j<strPlayer.length ? ' style="background-color: '+(j ? 'navy' : '#69F')+'"' : '') +'><td>'+ toPlace(i)+' </td><td class="maj" id="j'+i+'">'+ toPerso(joueur) +'</td><td id="pts'+i+'">'+ cp[joueur][3] +'<small>+'+ ptsInc +'</small></td></tr>';
									cp[joueur][3] += ptsInc;
									j = nbjoueurs;
								}
							}
						}

						positions += '<tr><td colspan="3" id="continuer"></td></tr>';
						document.getElementById("infos0").style.border = "solid 1px black";
						document.getElementById("infos0").style.opacity = 0.6;
						document.getElementById("infos0").style.fontSize = Math.round(iScreenScale*1.77-0.5) +"pt";
						document.getElementById("infos0").style.top = iScreenScale * 3 +"px";
						document.getElementById("infos0").style.left = Math.round(iScreenScale*26+10 + (strPlayer.length-1)/2*(iWidth*iScreenScale+2)) +"px";
						document.getElementById("infos0").style.backgroundColor = "blue";
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
						oContinue.focus();
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
							aPara2.appendChild(oSave);
							var oRetour = document.createElement("input");
							oRetour.type = "button";
							oRetour.value = "  "+ toLanguage("No", "Non") +"  ";
							oRetour.style.fontSize = (iScreenScale*4) +"px";
							oRetour.onmouseover = function() {
								this.style.fontSize = (iScreenScale*5) +"px";
								oSave.style.fontSize = (iScreenScale*4) +"px"
							};
							aPara2.appendChild(oRetour);

							oForm.appendChild(aPara1);
							oForm.appendChild(aPara2);
							document.body.appendChild(oForm);
							
							function askForRegister() {
								aPara1.innerHTML = toLanguage('Save the time to the <a href="classement.php?draw='+ oMap.id +'" target="_blank" style="color: orange">record list</a> ?', 'Enregistrer le temps dans la <a href="classement.php?draw='+ oMap.id +'" target="_blank" style="color: orange">liste des records</a> ?');
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
								oForm.style.visibility = "hidden";
								setTimeout(function() {
									oForm.style.visibility = "visible";
								}, 500);
							}
							
							askForRegister();
						};
						document.getElementById("continuer").appendChild(oContinue);
						document.getElementById("infos0").style.visibility = "visible";
						oContinue.focus();
					}

					if (bMusic||iSfx)
						startEndMusic(getId);
				}
			}
			if (oMap.sections)
				if (oKart.billball>1) oKart.billball = 1;
		}
		else if (!oKart.cpu) {
			document.getElementById("tour"+getId).innerHTML = oKart.tours;
			document.getElementById("lakitu"+getId).getElementsByTagName("div")[0].innerHTML = (oMap.sections ? "Sec":toLanguage("Lap","Tour")) + "<small>&nbsp;</small>" + oKart.tours;
			oKart.time = 40;
			if (bMusic || iSfx) {
				if (oKart.tours == oMap.tours) {
					var firstOne = true;
					for (var i=0;i<oPlayers.length;i++) {
						if ((oPlayers[i] != oKart) && (oPlayers[i].tours >= oMap.tours)) {
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

	if (oKart.cpu) {
		if (oKart.roulette == 25 || oKart.using[0] && !oKart.tourne) {

			if (oKart.using[0] || (oKart.arme != "champi" && oKart.arme != "megachampi" && oKart.arme != "etoile"))	{
				if (Math.random() > 0.98)
				arme(getId);
			}
		}
		
		var influence = Math.pow(0.96, getId-oPlayers.length-2);
		var rSpeed = iDificulty*influence*iDificulty/5;
		if (oKart.maxspeed > rSpeed*1.25) oKart.maxspeed = rSpeed*1.25;
		else if (oKart.maxspeed < rSpeed) oKart.maxspeed = rSpeed;
		if (oKart.place < oPlayers[0].place)
			oKart.maxspeed -= (oKart.maxspeed*influence-rSpeed*oKart.size)/100;
		else
			oKart.maxspeed += (rSpeed*1.4*oKart.size-oKart.maxspeed*influence)/100;
	}
	else oKart.maxspeed = 5.4 * cp[oKart.personnage][1];
	
	if (oKart.turbodrift) {
		oKart.maxspeed = 8;
		oKart.speed = 8;
		oKart.turbodrift--;
	}
		
	if (oKart.champi) {
		oKart.maxspeed = 11;
		oKart.champi--;
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
			if (demitour == oMap.checkpoint.length)
				demitour = 0;
			var oBox = oMap.checkpoint[demitour];
			iLocalX = (oBox[3] ? Math.round((oBox[0]+oBox[2])/2) : oBox[0]+8) - oKart.x;
			iLocalY = (oBox[3] ? oBox[1]+8 : Math.round((oBox[1]+oBox[2])/2)) - oKart.y;
			for (var i=0;i<oKart.aipoints.length;i++) {
				var oBox = oKart.aipoints[i];
				if (oKart.x > oBox[0] - 35 && oKart.x < oBox[0] + 35 && oKart.y > oBox[1] - 35 && oKart.y < oBox[1] + 35)	{
					oKart.aipoint = i + 1;
					if (oKart.aipoint == oKart.aipoints.length) oKart.aipoint = 0;
					iLocalX = oKart.aipoints[oKart.aipoint][0] - oKart.x;
					iLocalY = oKart.aipoints[oKart.aipoint][1] - oKart.y;
					i = oKart.aipoints.length;
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
			for (var i=0;i<strPlayer.length;i++)
				oKart.sprite[i].img.src = getSpriteSrc(oKart.personnage);
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
			for (var i=0;i<strPlayer.length;i++)
				oKart.sprite[i].img.src = (oKart.etoile % 2 ? getStarSrc(oKart.personnage) : getSpriteSrc(oKart.personnage));
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
		if (oKart.eclair > 98) {
			for (var i=0;i<aKarts.length;i++) {
				var kart = aKarts[i];
				if (kart != oKart) {
					if (!kart.protect) {
						kart.size = 0.6;
						updateDriftSize(i);
						kart.arme = false;
						if (kart.using[0]) {
							kart.using[0][kart.using[1]][3] = 0;
							kart.using = [false];
						}
						kart.champi = 0;
						spinKart(kart,20);
						kart.roulette = 0;
						stopDrifting(i);
						supprArme(i);
					}
					else
						kart.megachampi = (kart.megachampi<8 || kart.etoile ? kart.megachampi : 8);
				}
			}
		}
		else if (oKart.eclair < 1) {
			for (var i=0;i<aKarts.length;i++) {
				var kart = aKarts[i];
				if (kart != oKart && kart.size < 1)
				kart.size = 1;
				updateDriftSize(i);
			}
		}
		else if (oKart.eclair == 88)
			document.getElementById("mariokartcontainer").style.opacity = 1;
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
		if (iSfx && (oKart == oPlayers[0]) && !oKart.cpu) {
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
	}
	
	function angleDrift(oKart) {
		if (oKart.cpu)
			return 0;
		if (oKart.sliding)
			return oKart.rotinc*oKart.sliding;
		return oKart.drift*6;
	}
	function updateDriftSize(getId) {
		if (!aKarts[getId].cpu) {
			var k = aKarts[getId].size-1;
			getDriftImg(getId).style.left = -Math.round((iScreenScale*2)*k) + "px";
			getDriftImg(getId).style.top = Math.round((iScreenScale*2)*k) + "px";
			getDriftImg(getId).style.width = Math.round(iScreenScale * 8 + (iScreenScale*4)*k) + "px";
		}
	}
	function getDriftImg(getId) {
		return document.getElementById("drift"+ getId).getElementsByClassName("driftimg")[0];
	}
	function openCheats() {
		var cheatCode = prompt("MKPC Console command");
		if (!cheatCode)
			return false;
		if (!processCode(cheatCode))
			alert("Invalid command");
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
			document.getElementById("roulette0").innerHTML = '<img alt="." src="images/objects/'+ wObject +'.gif" style="width: '+ Math.round(iScreenScale * 8 - 3)+'px;" />';
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
		var isLap = /^lap(?: ([0-9]+|c))?(?: (\d+|c))?$/g.exec(cheatCode);
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
			if (t > oMap.tours) t = oMap.tours;
			if (c > oMap.checkpoint.length) c = oMap.checkpoint.length-1;
			oPlayer.tours = t;
			oPlayer.demitours = c;
			document.getElementById("tour0").innerHTML = oPlayer.tours;
			return true;
		}
		return false;
	}

	function ai(i) {
		var oKart = aKarts[i];
		if (oMap.sections && oKart.tours > oMap.tours) {
			oKart.speedinc = 0;
			oKart.rotincdir = 0;
			return;
		}
		if (!oKart.aipoints.length) return;
		var aCurPoint = oKart.aipoints[oKart.aipoint];

		var iLocalX = aCurPoint[0] - oKart.x;
		var iLocalY = aCurPoint[1] - oKart.y;

		iRotatedX = iLocalX * direction(1, oKart.rotation) - iLocalY * direction(0, oKart.rotation);
		iRotatedY = iLocalX * direction(0, oKart.rotation) + iLocalY * direction(1, oKart.rotation);

		var fAngle = Math.atan2(iRotatedX,iRotatedY) / Math.PI * 180;
		var pDist = iLocalX*iLocalX + iLocalY*iLocalY;

		oKart.speedinc = (oKart.speed >= 0 ? 1 : 0.2);
		
		if (oKart.speed < 0) {
			oKart.movesince = 12;
			if (oKart.retablit) {
				oKart.retablit = false;
				if (oKart.movesince) {
					oKart.movesince--;
					if (oKart.movesince < 6)
						oKart.reflexion = 0;
				}
				if (oKart.reflexion) {
					oKart.reflexion--;
					if (oKart.reflexion == 0)
						oKart.decision = -oKart.decision;
				}
				else {
					var xp = (direction(0,oKart.rotation)>0), yp = (direction(1,oKart.rotation)>0), vh = (oKart.horizontality == "v");
					if (vh)
						oKart.decision = (xp+yp)%2 ? 1:-1;
					else
						oKart.decision = (xp+yp)%2 ? -1:1;
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
				if (oKart.movesince < 6)
					oKart.reflexion = 0;
			}
			if (Math.abs(fAngle) > 7 + Math.random()*5) {
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
		}

		if (pDist < 300) {
				oKart.aipoint++;

			if (oKart.aipoint >= oKart.aipoints.length)
				oKart.aipoint = 0;
		}

	}


	function cycle() {

if (!pause)	{
	setTimeout(cycle,67);

		for (var i=0;i<aKarts.length;i++) {
		var oKart = aKarts[i];
			if (oKart.cpu)	{
				if (!oKart.billball)
					ai(i);
				pCol(oKart);
				}
			move(i);
			if (oKart.protect)
				colKart(oKart);
			if (course != "CM")
				places(i);
			else if (course == "CM" && !oKart.cpu) {
				var trajetplus = [oKart.x,oKart.y,oKart.rotation];
				if (oKart.z) {
					trajetplus.push(oKart.z);
					if (oKart.tombe == 20)
						trajetplus.push(true);
				}
				iTrajet.push(trajetplus);
			}
		}
		if (oMap.infoPlus)
			oMap.infoPlus({map:oMap,players:oPlayers});
		render();
	}
}

	document.onkeydown = function(e) {
		switch (e.keyCode) {
			case 38: // up
				oPlayers[0].speedinc = 1;
				if (document.getElementById("decompte0").innerHTML > 1)
					updateEngineSound(carEngine2);
				return false;
			case 37: // left
				oPlayers[0].rotincdir = 1;
				return false;
			case 39: // right
				oPlayers[0].rotincdir = -1;
				return false;
		}
		if (oPlayers[1]) {
			switch (e.keyCode) {
				case 69: // E
					oPlayers[1].speedinc = 1;
					break;
				case 83: // S
					oPlayers[1].rotincdir = 1;
					break;
				case 70: // F
					oPlayers[1].rotincdir = -1;
			}
		}
	}

	document.onkeyup = function(e) {
		switch (e.keyCode) {
			case 38: // up
				oPlayers[0].speedinc = 0;
				updateEngineSound(carEngine);
				break;
			case 37: // left
				oPlayers[0].rotincdir = 0;
				break;
			case 39: // right
				oPlayers[0].rotincdir = 0;
		}
		if (oPlayers[1]) {
			switch (e.keyCode) {
				case toLanguage(87,90): // Z
					oPlayers[1].speedinc = 0;
					break;
				case toLanguage(65,81): // Q
					oPlayers[1].rotincdir = 0;
					break;
				case 68: // D
					oPlayers[1].rotincdir = 0;
			}
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

		var oModes = [toLanguage("VS", "Course VS")];
		var oModeIds = ["VS"];
		if (isCup) {
			oModes.unshift("Grand Prix");
			oModeIds.unshift("GP");
		}
		if (nid || isCup) {
			oModes.push(toLanguage("Time Trial", "Contre-la-montre"));
			oModeIds.push("CM");
		}
		if (nid) {
			oModes.push(toLanguage("Online race", "Course en ligne"));
			oModeIds.push("CL");
		}
		if (isCup && cupScore) {
			var oCup = new Image();
			oCup.src = "images/cups/cup"+ (4-cupScore) +".png";
			oCup.style.width = Math.round(4 * iScreenScale) + "px";
			oCup.style.height = Math.round(4 * iScreenScale) + "px";
			oCup.style.position = "absolute"
			oCup.style.left = (3*iScreenScale)+"px";
			oCup.style.top = Math.round(18*iScreenScale)+"px";
			oScr.appendChild(oCup);
		}

		for (i=0;i<oModes.length;i++) {
			var oPInput = document.createElement("input");
			oPInput.type = "button";
			oPInput.value = oModes[i];
			oPInput.style.position = "absolute";
			if (oModes.length < 4) {
				oPInput.style.left = (20*iScreenScale)+"px";
				oPInput.style.top = ((14+i*7)*iScreenScale)+"px";
				oPInput.style.width = (38*iScreenScale)+"px";
				oPInput.style.fontSize = Math.round(3.5*iScreenScale)+"px";
			}
			else {
				oPInput.style.left = ((8+(i%2)*36)*iScreenScale)+"px";
				oPInput.style.top = ((18+Math.floor(i/2)*8)*iScreenScale)+"px";
				oPInput.style.width = (28*iScreenScale)+"px";
				oPInput.style.fontSize = (3*iScreenScale)+"px";
			}
			if (!oPInput.dataset)
				oPInput.dataset = {};
			oPInput.dataset.course = oModeIds[i];

			oPInput.onclick = function() {
				course = this.dataset.course;
				if (course == "CL")
					document.location.href = "online.php?"+ (isCup?"cid":"i") +"="+ nid;
				else {
					FBRoot.style.display = "none";
					oScr.innerHTML = "";

					oContainers[0].removeChild(oScr);
					if (course == "VS")
						selectNbJoueurs();
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
			document.location.href = "index.php";
		}
		oScr.appendChild(oPInput);

		FBRoot = document.getElementById("fb-root");
		var FBToLoad = !FBRoot;
		if (FBToLoad) {
			FBRoot = document.createElement("div");
			FBRoot.id = "fb-root";
			FBRoot.style.position = "absolute";
		}
		else
			FBRoot.style.display = "";
		FBRoot.style.left = (73*iScreenScale - 28)+"px";
		FBRoot.style.top = (35*iScreenScale + 12)+"px";
		FBRoot.style.transform = FBRoot.style.WebkitTransform = FBRoot.style.MozTransform = "scale("+ (iScreenScale/7) +")";
		if (FBToLoad) {
			FBRoot.id = "fb-root";
			var FBshare = document.createElement("div");
			FBshare.className = "fb-share-button";
			if (!FBshare.dataset)
				FBshare.dataset = {};
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

		oContainers[0].appendChild(oScr);

		updateMenuMusic(0);
	}
		
	function selectNbJoueurs() {
		var FBRoot;
		
		var oScr = document.createElement("div");
		var oStyle = oScr.style;

		oStyle.width = (iWidth*iScreenScale)+"px";
		oStyle.height = (iHeight*iScreenScale)+"px";
		oStyle.border = "solid 1px black";
		oStyle.backgroundColor = "black";

		oScr.appendChild(toTitle(toLanguage("Number of players", "Nombre de joueurs"), 0.5));

		for (i=1;i<=2;i++) {
			var oPInput = document.createElement("input");
			oPInput.type = "button";
			oPInput.value = i + (i<2 ? "  " : " ") + toLanguage("player","joueur") + (i<2 ? " " : "s");
			oPInput.style.fontSize = (4*iScreenScale)+"px";
			oPInput.style.position = "absolute";
			oPInput.style.left = (27*iScreenScale)+"px";
			oPInput.style.top = ((10+i*8)*iScreenScale)+"px";

			oPInput.onclick = function()	{
				FBRoot.style.display = "none";
				oScr.innerHTML = "";
				oContainers[0].removeChild(oScr);
				if (this.value.charAt(0) == "2") {
					var oContainer2 = oContainers[0].cloneNode(false);
					oContainer2.style.left = (10+iWidth*iScreenScale)+"px";
					oContainers.push(oContainer2);
					var fElements = ["temps", "compteur", "infos", "infoPlace", "lakitu", "drift", "scroller"];
					for (var i=0;i<fElements.length;i++) {
						var fElement = document.getElementById(fElements[i]+0).cloneNode(true);
						fElement.id = fElements[i]+1;
						document.body.appendChild(fElement);
					}
				}
				selectPlayerScreen(0);
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
			FBRoot.style.display = "none";
			oScr.innerHTML = "";

			oContainers[0].removeChild(oScr);

			if (isCup || nid)
				selectTypeScreen();
			else
				document.location.href = "index.php";
		}
		oScr.appendChild(oPInput);

		FBRoot = document.getElementById("fb-root");
		var FBToLoad = !FBRoot;
		if (FBToLoad) {
			FBRoot = document.createElement("div");
			FBRoot.id = "fb-root";
			FBRoot.style.position = "absolute";
		}
		else
			FBRoot.style.display = "";
		FBRoot.style.left = (73*iScreenScale - 28)+"px";
		FBRoot.style.top = (35*iScreenScale + 12)+"px";
		FBRoot.style.transform = FBRoot.style.WebkitTransform = FBRoot.style.MozTransform = "scale("+ (iScreenScale/7) +")";
		if (FBToLoad) {
			FBRoot.id = "fb-root";
			var FBshare = document.createElement("div");
			FBshare.className = "fb-share-button";
			if (!FBshare.dataset)
				FBshare.dataset = {};
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
		
		oContainers[0].appendChild(oScr);

		updateMenuMusic(0);
	}


	function selectPlayerScreen(IdJ,newP) {
		if (!IdJ) {
			strPlayer = [];
			aPlayers = [];
			for (joueurs in cp)
				aPlayers.push(joueurs);
		}

		var oScr = document.createElement("div");
		if (newP)
			oScr.style.visibility = "hidden";

		var oStyle = oScr.style;

		oStyle.width = (iWidth*iScreenScale)+"px";
		oStyle.height = (iHeight*iScreenScale)+"px";
		oStyle.border = "solid 1px black";
		oStyle.backgroundColor = "black";

		oScr.appendChild(toTitle(toLanguage("Choose a player", "Choisissez un joueur"), 0));

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
			for (var i=1;i<oContainers.length;i++)
				oContainers.splice(i,1);
			if (course == "VS")
				selectNbJoueurs();
			else
				selectTypeScreen();
		}

		oScr.appendChild(oPInput);


		vitesse = 15*iScreenScale;
	
		var cTable = document.createElement("table");
		cTable.style.display = "none";
		cTable.style.position = "absolute";
		cTable.style.top = (37*iScreenScale+20)+"px";
		cTable.style.left = (25*iScreenScale-60)+"px";
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
				var libre = true;
				var oJoueur = aPlayers[i];
				for (var j=0;j<strPlayer.length;j++) {
					if (strPlayer[j] == oJoueur) {
						libre = false;
						j = strPlayer.length;
					}
				}
				oPImg.src = (libre ? getSpriteSrc(oJoueur):getStarSrc(oJoueur));
				oPImg.alt = oJoueur;
				oPImg.nb = i;
				oPImg.style.left = -(30 * iScreenScale) +"px";
				if (libre)
					oPImg.style.cursor = "pointer";
				oPImg.j = IdJ;
				if (libre) {
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
						strPlayer[this.j] = this.alt;
						if (course != "GP") {
							if (document.forms[0].nbj && document.forms[0].nbj.className) {
								selectedDifficulty = parseInt(document.forms[0].nbj.className);
								iDificulty = 4+selectedDifficulty*0.5;
							}
						}
						else {
							iDificulty = 5;
							fInfos = 8;
						}
						oScr.innerHTML = "";
						this.j++;
						oContainers[0].removeChild(oScr);
						document.body.removeChild(cTable);
						if (this.j == oContainers.length) {
							if (course != "CM") {
								aPlayers = [];
								var i = 0;
								for (joueurs in cp) {
									if (pUnlocked[i]) {
										aPlayers.push(joueurs);
										i++;
									}
								}
								aPlayers.sort(function(){return 0.5-Math.random()});
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
								selectedPlayers = fInfos;
								aPlayers.splice(0,aPlayers.length-fInfos+strPlayer.length);
								for (var i=0;i<strPlayer.length;i++)
									aPlayers.push(strPlayer[i]);
								if (course != "GP") {
									xhr("updateCourseOptions.php", "difficulty="+ selectedDifficulty +"&players="+ fInfos, function(reponse) {
										return (reponse == 1);
									});
								}
							}
							else
								aPlayers = [strPlayer[0]];

							for (var i=1;i<oContainers.length;i++)
								document.getElementById("mariokartcontainer").appendChild(oContainers[i]);
							selectRaceScreen();
						}
						else
							selectPlayerScreen(this.j);
						var cpId = /^cp-\w+-(\d+)$/g.exec(this.alt);
						if (cpId)
							xhr("selectPerso.php", "id="+cpId[1], function(){return true});
					}
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
		
		oContainers[0].appendChild(oScr);
		if (course == "VS") {
			var oForm = document.createElement("form");
			oForm.onsubmit = function(){return false};
			oForm.style.position = "absolute";
			oForm.style.top = (32*iScreenScale) +"px";
			oForm.style.left = (18*iScreenScale) +"px";
			oForm.style.fontSize = (2*iScreenScale) +"px";
			oForm.innerHTML = toLanguage("Difficulty :", "Difficulté :");
			if (!IdJ) {
				iDificulty = selectedDifficulty;
				fInfos = selectedPlayers;
			}
			var iDifficulties = [toLanguage("Easy", "Facile"), toLanguage("Medium", "Moyen"), toLanguage("Difficult", "Difficile")];
			oForm.innerHTML = "Difficulté :";
			for (var i=0;i<3;i++)
				oForm.innerHTML += '<input type="radio" name="difficulty" value="'+ i +'" '+ (i==selectedDifficulty ? ' checked="checked"' : '') +' onchange="document.forms[0].nbj.className = parseInt(this.value)" />'+ iDifficulties[i] +' &nbsp; ';

			oForm.innerHTML += "<br />"+ toLanguage("Number of participants", "Nombre de participants") +" : ";
			var oSelect = document.createElement("select");
			oSelect.name = "nbj";
			oSelect.style.width = (iScreenScale*3+20) +"px";
			oSelect.style.fontSize = iScreenScale*2 +"px";
			oSelect.onchange = function(){ fInfos = parseInt(this.value) };
			for (var i=2;i<=8;i++) {
				var oOption = document.createElement("option");
				oOption.value = i;
				oOption.innerHTML = i;
				oSelect.appendChild(oOption);
			}
			oForm.appendChild(oSelect);
			oScr.appendChild(oForm);
			document.forms[0].nbj.selectedIndex = fInfos-2;
			document.forms[0].nbj.className = selectedDifficulty;
		}
		else if (course == "CM") {
			var oClassement = document.createElement("input");
			oClassement.type = "button";
			oClassement.value = toLanguage("Rankings", "Classement");
			oClassement.style.position = "absolute";
			oClassement.style.fontSize = (3*iScreenScale)+"px";
			oClassement.style.position = "absolute";
			oClassement.style.left = (30*iScreenScale-10)+"px";
			oClassement.style.top = (32*iScreenScale)+"px";
			oClassement.style.width = (20*iScreenScale)+"px";
			oClassement.onclick = function() {
				open("classement.php"+ (isCup ? "?ccup="+nid:"?draw="+nid));
			};
			oScr.appendChild(oClassement);
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
			oContainers[0].removeChild(oScr);
			xhr("selectPerso.php", "id="+persoId, function(res) {
				selectPlayerScreen(IdJ,true);
				return true;
			});
		};

		updateMenuMusic(1);
	}

function setMapSrc(oPImg,i) {
	setTimeout(function() {
		oPImg.src = "trackicon.php?id="+ oMaps[i].id +"&type=1";
	}, 100*i);
}

function selectRaceScreen() {
	if (isCup && (course != "GP")) {
		var oScr = document.createElement("div");
		var oStyle = oScr.style;

		oStyle.width = (iWidth*iScreenScale)+"px";
		oStyle.height = (iHeight*iScreenScale)+"px";
		oStyle.border = "solid 1px black";
		oStyle.backgroundColor = "black";
		
		oContainers[0].appendChild(oScr);

		oScr.appendChild(toTitle(toLanguage("Choose race", "Choisissez un circuit"), 0.5));

		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = toLanguage("Back", "Retour");
		oPInput.style.fontSize = (2*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (2*iScreenScale)+"px";
		oPInput.style.top = (35*iScreenScale)+"px";
		oPInput.onclick = function() {
			if (!pause) {
				oScr.innerHTML = "";
				oContainers[0].removeChild(oScr);
				selectPlayerScreen(0);
			}
			else {
				removeMenuMusic(false);
				quitter();
			}
		}
		oScr.appendChild(oPInput);
		
		var mScreenScale = iScreenScale;

		for (var i=0;i<4;i++) {
			var mDiv = document.createElement("div");
			mDiv.style.width = (25 * iScreenScale) + "px";
			mDiv.style.height = (10 * iScreenScale) + "px";
			mDiv.style.cursor = "pointer";
			mDiv.style.position = "absolute"
			mDiv.style.left = (((iWidth-113)/2+(i-Math.floor(i/2)*2)*25+(i-Math.floor(i/2)*2))*iScreenScale)+iScreenScale*30+"px";
			mDiv.style.top = (10*iScreenScale)+11*iScreenScale*Math.floor(i/2)+"px";
			mDiv.map = i;
			
			var oPImg = new Image();
			setMapSrc(oPImg, i);
			oPImg.style.width = "100%";
			oPImg.style.height = "100%";
			oPImg.style.border = "double 4px silver";
			mDiv.appendChild(oPImg);
			
			mDiv.appendChild(mapNameOf(mScreenScale, i));

			var mLink = document.createElement("a");
			mLink.style.position = "absolute";
			mLink.style.right = "-3px";
			mLink.style.top = "5px";
			mLink.style.backgroundColor = "rgba(0,50,128, 0.5)";
			mLink.style.padding = "4px";
			mLink.style.borderRadius = "50%";
			mLink.href = "?i=" + oMaps[i].id;
			mLink.title = toLanguage("Link tot this circuit", "Lien vers ce circuit");
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

			mDiv.onclick = function() {
				oScr.innerHTML = "";
				oContainers[0].removeChild(oScr);
				for (var i=1;i<oContainers.length;i++)
					document.getElementById("mariokartcontainer").appendChild(oContainers[i]);
				resetGame(this.map*1);
			}
			oScr.appendChild(mDiv);
		}

		var oPInput = document.createElement("input");
		oPInput.type = "button";
		oPInput.value = toLanguage("Random", "Aléatoire");
		oPInput.style.fontSize = (3*iScreenScale)+"px";
		oPInput.style.position = "absolute";
		oPInput.style.left = (34*iScreenScale-10)+"px";
		oPInput.style.top = (34*iScreenScale)+"px";
		oPInput.onclick = function() {
			oScr.innerHTML = "";
			oContainers[0].removeChild(oScr);
			for (var i=1;i<oContainers.length;i++)
				document.getElementById("mariokartcontainer").appendChild(oContainers[i]);
			resetGame(Math.floor(Math.random()*4));
		}
		oScr.appendChild(oPInput);
	}
	else
		resetGame(0);

	updateMenuMusic(1);
}

function mapNameOf(mScreenScale, mID) {
	var sMapName = oMaps[mID].name;
	var oMapName = document.createElement("div");
	var mapFS = Math.min(Math.max(9/Math.sqrt(sMapName.length), 1.4), 4);
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
		oMap = undefined;
		if (course == "CM")
			oMap = oMaps[fInfos[1]];
		else if (fInfos[2])
			oMap = oMaps[fInfos[2]];
		else if (!isCup)
			oMap = oMaps[0];
		if (oMap)
			loadMap();
		else {
			formulaire.screenscale.disabled = false;
			formulaire.quality.disabled = false;
			formulaire.music.disabled = false;
			formulaire.sfx.disabled = false;
			selectRaceScreen();
		}
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
		if (isCup || nid)
			selectTypeScreen();
		else {
			course = "VS";
			selectNbJoueurs();
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
		}
		window.turnEvents = true;
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
var tag = document.createElement('script');
tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);