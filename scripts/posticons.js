var iconDelayDt = 100;
function setCircuitImgs(inc,icCircuit) {
	var imgsData = icCircuit.dataset.cicon;
	if (imgsData) {
		var bgs = [];
		var imgs = imgsData.split(",");
		for (var j=0;j<imgs.length;j++)
			bgs[j] = "url('"+imgs[j]+"')";
		var res = inc;
		for (var j=0;j<bgs.length;j++) {
			setCircuitImg(res,icCircuit,bgs,j);
			res++;
		}
		return res;
	}
	else {
		icCircuit.parentNode.removeChild(icCircuit);
		return inc;	
	}
}
function setCircuitImg(inc,icCircuit,bgs,j) {
	var bgsIncomplete = [];
	for (var i=0;i<bgs.length;i++)
		bgsIncomplete[i] = "url('images/uploads/overload.png')";
	icCircuit.style.backgroundImage = bgsIncomplete.join(",");
	for (var i=0;i<=j;i++)
		bgsIncomplete[i] = bgs[i];
	setTimeout(function() {
		icCircuit.style.backgroundImage = bgsIncomplete.join(",");
	}, iconDelayDt*inc);
	delete icCircuit.dataset.cicon;
}
function loadCircuitImgs() {
	var icCircuits = document.querySelectorAll("[data-cicon]");
	var inc = 0;
	for (var i=0;i<icCircuits.length;i++) {
		var icCircuit = icCircuits[i];
		var nInc = setCircuitImgs(inc,icCircuit);
		if (nInc == inc)
			continue;
		inc = nInc;
	}
}
if ("loading" !== document.readyState)
	loadCircuitImgs();
else
	document.addEventListener("DOMContentLoaded", loadCircuitImgs);