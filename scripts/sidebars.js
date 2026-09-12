var onlineModeIds = ["vs","battle","clm150","clm200"];
var currenttabcc = 2;

function dispRankTab(mode) {
    if (mode >= 2)
        currenttabcc = mode;
    var onlineModeId = onlineModeIds[mode];
    document.getElementById("rankings_section").className = "subsection rank_" + onlineModeId;
    document.querySelectorAll(".ranking_tab.tab_"+onlineModeId+" .ranking_badge").forEach(function(badge) {
        badge.style.display = "none";
    });
}


document.querySelectorAll("#right_section .sidebar_title a").forEach(function(a) {
	a.addEventListener('mouseover', function() {
		this.parentElement.parentElement.parentElement.parentElement.style.backgroundImage = "url('images/sidebar_hover.png')";
		this.parentElement.parentElement.parentElement.parentElement.style.backgroundColor = "#80FF00";
	});
	a.addEventListener('mouseout', function() {
		this.parentElement.parentElement.parentElement.parentElement.style.backgroundImage = "";
		this.parentElement.parentElement.parentElement.parentElement.style.backgroundColor = "";
	});
});

document.querySelectorAll(".flag_counter img").forEach(function(img) {
	img.addEventListener('load', function() {
		this.parentElement.classList.add("flag_loaded");
	});
});

document.querySelectorAll(".ranking_activeplayernb, .ranking_fancytitle").forEach(function(elt) {
	var lines = elt.getAttribute("title").split(", ");
	elt.setAttribute("title", "");
	var fancyTitle, hideTimer;
	function placeFancyTitle() {
		var eltPos = elt.getBoundingClientRect();
		fancyTitle.style.left = Math.round(eltPos.left + (elt.offsetWidth-fancyTitle.offsetWidth)/2) - 3 + "px";
		fancyTitle.style.top = eltPos.top-fancyTitle.offsetHeight-2 + "px";
	}
	elt.addEventListener('mouseover', function() {
		// Coming back before the last one finished fading: keep the node and fade it back in.
		// Letting its removal go ahead would take this one away instead, which is what made a
		// second hover show nothing at all.
		if (hideTimer) {
			clearTimeout(hideTimer);
			hideTimer = undefined;
		}
		if (fancyTitle) {
			placeFancyTitle();
			fancyTitle.style.opacity = 1;
			return;
		}
		fancyTitle = document.createElement("div");
		fancyTitle.className = "ranking_activeplayertitle";
		// one node per name rather than one blob of markup: these are member-supplied
		lines.forEach(function(line, i) {
			if (i) fancyTitle.appendChild(document.createElement("br"));
			fancyTitle.appendChild(document.createTextNode(line));
		});
		fancyTitle.style.opacity = 0;
		document.body.appendChild(fancyTitle);
		placeFancyTitle();
		fancyTitle.style.opacity = 1;
	});
	elt.addEventListener('mouseout', function() {
		if (!fancyTitle) return;
		fancyTitle.style.opacity = 0;
		// the node this timer was started for, so it can never remove a later one
		var fading = fancyTitle;
		hideTimer = setTimeout(function() {
			document.body.removeChild(fading);
			if (fancyTitle === fading)
				fancyTitle = undefined;
			hideTimer = undefined;
		}, 200);
	});
});