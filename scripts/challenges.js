var $fancyTitle;
function initPrettyTitle(prettyTitle) {
    if (!prettyTitle.dataset) prettyTitle.dataset = {};
    prettyTitle.dataset.title = prettyTitle.title;
    prettyTitle.title = "";
    prettyTitle.onmouseover = function(e) {
        if ($fancyTitle) return;
        $fancyTitle = document.createElement("div");
        $fancyTitle.className = "challenge-title-fancy";
        $fancyTitle.innerHTML = this.dataset.title;
        $fancyTitle.style.visibility = "hidden";
        document.body.appendChild($fancyTitle);
        var rect = this.getBoundingClientRect();
        $fancyTitle.style.left = Math.round(rect.left + (this.scrollWidth-$fancyTitle.scrollWidth)/2)+"px";
        $fancyTitle.style.top = (rect.top - $fancyTitle.scrollHeight - 3)+"px";
        $fancyTitle.style.visibility = "visible";
    };
    prettyTitle.onmouseout = function(e) {
        if (!$fancyTitle) return;
        document.body.removeChild($fancyTitle);
        $fancyTitle = undefined;
    };
}
function initPrettyTitles() {
	var prettyTitles = document.getElementsByClassName("pretty-title");
	for (var i=0;i<prettyTitles.length;i++) {
        initPrettyTitle(prettyTitles[i]);
	}
}