var iconDelayDt = 100;
function setCircuitImgs(inc,icCircuit) {
	var imgsData = icCircuit.dataset.cicon;
	delete icCircuit.dataset.cicon;
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
	else
		return inc;
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
}
function loadCircuitImgs() {
	var icCircuits = document.querySelectorAll("[data-cicon]");
	var inc = 0;
	for (var i=0;i<icCircuits.length;i++)
		inc = setCircuitImgs(inc,icCircuits[i]);
}
if ("loading" !== document.readyState)
	loadCircuitImgs();
else
	document.addEventListener("DOMContentLoaded", loadCircuitImgs);