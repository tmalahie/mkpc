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

document.querySelectorAll(".ranking_activeplayernb").forEach(function(elt) {
	var title = elt.getAttribute("title");
	title = title.replace(/, /g, "<br />");
	elt.setAttribute("title", "");
	var fancyTitle;
	elt.addEventListener('mouseover', function() {
		if (fancyTitle) return;
		fancyTitle = document.createElement("div");
		fancyTitle.className = "ranking_activeplayertitle";
		fancyTitle.innerHTML = title;
		fancyTitle.style.opacity = 0;
		document.body.appendChild(fancyTitle);
		var eltPos = elt.getBoundingClientRect();
		fancyTitle.style.left = Math.round(eltPos.left + (elt.offsetWidth-fancyTitle.offsetWidth)/2) - 3 + "px";
		fancyTitle.style.top = eltPos.top-fancyTitle.offsetHeight-2 + "px";
		fancyTitle.style.opacity = 1;
	});
	elt.addEventListener('mouseout', function() {
		if (!fancyTitle) return;
		fancyTitle.style.opacity = 0;
		setTimeout(function() {
			if (fancyTitle) {
				document.body.removeChild(fancyTitle);
				fancyTitle = undefined;
			}
		}, 200);
	});
});