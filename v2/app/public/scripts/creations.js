function apercu(src) {
	var srcs;
	if (typeof(src) == "object") {
		srcs = src;
		src = srcs[0];
	}
	else
		srcs = [src];
	var oGris = document.createElement("div");
	oGris.style.position = "fixed";
	oGris.style.left = "0px";
	oGris.style.top = "0px";
	oGris.style.width = "100%";
	oGris.style.height = "100%";
	oGris.style.textAlign = "center";
	oGris.style.verticalAlign = "middle";
	oGris.style.backgroundColor = "black";
	oGris.style.zIndex = 10;
	var okd = document.onkeydown;
	var oLeftArrow, oRightArrow;
	var aHandler;
	function exit() {
		if (aHandler)
			clearTimeout(aHandler);
		document.body.removeChild(oGris);
		document.body.removeChild(oBigImg);
		if (oLeftArrow)
			document.body.removeChild(oLeftArrow);
		if (oRightArrow)
			document.body.removeChild(oRightArrow);
		document.onkeydown = okd;
	}
	oGris.onclick = exit;
	document.onkeydown = function(e) {
		if (e.keyCode == 27)
			exit();
	};
	var deroulement = 0, loaded = false;
	var oBigImg = document.createElement("img");
	oBigImg.src = src;
	oBigImg.setAttribute("alt", "Apercu circuit");
	oBigImg.style.position = "fixed";
	oBigImg.style.zIndex = 20;
	oBigImg.style.border = "solid 1px black";
	oBigImg.style.width = "0px";
	var maxWidth, ratio;
	var aLoading = true;
	function updateDims() {
		var nWidth = maxWidth, nHeight = Math.round(nWidth*ratio);
		oBigImg.style.width = Math.round(nWidth) +"px";
		oBigImg.style.left = Math.round((oGris.offsetWidth-nWidth)/2) +"px";
		oBigImg.style.top = Math.round((oGris.offsetHeight-nHeight)/2) +"px";
	}
	function windowWidth() {
		var w = window,
	    d = document,
	    e = d.documentElement,
	    g = d.getElementsByTagName('body')[0],
	    r = w.innerWidth || e.clientWidth || g.clientWidth;
	    return r;
	}
	function windowHeight() {
		var w = window,
	    d = document,
	    e = d.documentElement,
	    g = d.getElementsByTagName('body')[0],
	    r = w.innerHeight|| e.clientHeight|| g.clientHeight;
	    return r;
	}
	oBigImg.onload = function() {
		var wMax = Math.min(602,windowWidth(),windowHeight());
		ratio = oBigImg.naturalHeight/oBigImg.naturalWidth;
		maxWidth = (oBigImg.naturalWidth > oBigImg.naturalHeight) ? wMax:Math.round(wMax/ratio);
		if (deroulement == 10) {
			oGris.innerHTML = "";
			defile();
		}
		else if (deroulement == 20)
			updateDims();
		aLoading = false;
	}
	document.body.appendChild(oBigImg);
	function defile() {
		aHandler = null;
		if (deroulement < 10)
			oGris.style.opacity = deroulement/20;
		else {
			var nWidth = (deroulement-10)*maxWidth/10, nHeight = Math.round(nWidth*ratio);
			oBigImg.style.width = Math.round(nWidth) +"px";
			oBigImg.style.left = Math.round((oGris.offsetWidth-nWidth)/2) +"px";
			oBigImg.style.top = Math.round((oGris.offsetHeight-nHeight)/2) +"px";
		}
		if (deroulement < 20) {
			deroulement++;
			if (maxWidth || (deroulement != 10))
				aHandler = setTimeout(defile, 50);
			else if (!maxWidth) {
				oGris.style.lineHeight = (oGris.offsetHeight-20) +"px";
				oGris.style.color = "white";
				oGris.style.fontSize = "40px";
				oGris.style.fontWeight = "bold";
				oGris.style.fontFamily = "Modern";
				oGris.innerHTML = loadingMsg +"...";
			}
		}
		else {
			if (srcs.length > 1) {
				aHandler = setTimeout(function() {
					var aID = 0;

					oLeftArrow = document.createElement("img");
					oLeftArrow.src = "images/previous.png";
					oLeftArrow.style.position = "fixed";
					oLeftArrow.style.left = "5%";
					oLeftArrow.style.top = "45%";
					oLeftArrow.style.height = "10%";
					oLeftArrow.style.cursor = "pointer";
					oLeftArrow.style.zIndex = 21;
					oLeftArrow.onmouseover = function(e) {
						oLeftArrow.src = "images/previoush.png";
					};
					oLeftArrow.onmouseout = function(e) {
						oLeftArrow.src = "images/previous.png";
					};
					oLeftArrow.onclick = function(e) {
						if (!aLoading) {
							aLoading = true;
							aID--;
							if (aID < 0) aID += srcs.length;
							oBigImg.src = srcs[aID];
						}
					};
					document.body.appendChild(oLeftArrow);

					oRightArrow = document.createElement("img");
					oRightArrow.src = "images/next.png";
					oRightArrow.style.position = "fixed";
					oRightArrow.style.right = "5%";
					oRightArrow.style.top = "45%";
					oRightArrow.style.height = "10%";
					oRightArrow.style.cursor = "pointer";
					oRightArrow.style.zIndex = 21;
					oRightArrow.onmouseover = function(e) {
						oRightArrow.src = "images/nexth.png";
					};
					oRightArrow.onmouseout = function(e) {
						oRightArrow.src = "images/next.png";
					};
					oRightArrow.onclick = function(e) {
						if (!aLoading) {
							aLoading = true;
							aID++;
							if (aID == srcs.length) aID = 0;
							oBigImg.src = srcs[aID];
						}
					};
					document.body.appendChild(oRightArrow);
				}, 200);
			}
		}
	}
	document.body.appendChild(oGris);
	defile();
}