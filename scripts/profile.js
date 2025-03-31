function helpLeagues() {
	window.open('helpLeagues.php','gerer','scrollbars=1, resizable=1, width=500, height=400');
	void 0;
}
function helpRanks() {
	window.open('helpRanks.php','gerer','scrollbars=1, resizable=1, width=500, height=400');
	void 0;
}
function spriteLoad(img) {
	var w = img.naturalWidth, h = img.naturalHeight;
	if (w != 768 || h != 32) {
		var div = img.parentNode;
		// TODO: this works because 768 = 24*32, but it's a coincidence
		div.style.width = Math.round(w/h)+"px";
		img.style.left = -Math.round(6*w/h)+"px";
	}
}
document.addEventListener("DOMContentLoaded", function() {
	var profileCircuits = document.querySelectorAll(".profile-circuits td");
	for (var i=0;i<profileCircuits.length;i++) {
		var profileCircuit = profileCircuits[i];
		if (profileCircuit.className)
			profileCircuit.style.width = profileCircuit.style.height = profileCircuit.scrollWidth +"px";
		else
			break;
	}
	document.querySelectorAll(".profile-circuits")[0].style.width = "auto";
});