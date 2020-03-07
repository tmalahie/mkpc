jQuery(function() {
	jQuery("#right_section .sidebar_title a").hover(
		function() {
			jQuery(this).parent().parent().parent().parent().css("background-image", "url('images/sidebar_hover.png')");
			jQuery(this).parent().parent().parent().parent().css("background-color", "#80FF00");
		},
		function() {
			jQuery(this).parent().parent().parent().parent().css("background-image", "");
			jQuery(this).parent().parent().parent().parent().css("background-color", "");
		}
	);
	jQuery(".flag_counter img").load(function() {
		jQuery(".flag_counter").addClass("flag_loaded");
	});
	jQuery(".ranking_activeplayernb").each(function(id,elt) {
		var $elt = jQuery(elt);
		var title = $elt.attr("title");
		title = title.replace(/, /g, "<br />");
		$elt.attr("title", "");
		var $fancyTitle;
		$elt.mouseover(function(e) {
			if ($fancyTitle) return;
			$fancyTitle = jQuery("<div></div>");
			$fancyTitle.addClass("ranking_activeplayertitle");
			$fancyTitle.html(title);
			$fancyTitle.css("visibility", "hidden");
			jQuery("body").append($fancyTitle);
			var eltPos = $elt.offset();
			$fancyTitle.css("left", Math.round(eltPos.left + ($elt.width()-$fancyTitle.width())/2) - 3);
			$fancyTitle.css("top", eltPos.top-$fancyTitle.height()-5);
			$fancyTitle.hide().css("visibility", "visible").fadeIn(200);
		});
		$elt.mouseout(function(e) {
			if (!$fancyTitle) return;
			$fancyTitle.fadeOut(200, function() {
				jQuery(this).remove();
			});
			$fancyTitle = undefined;
		});
	});
});
var onlineModeIds = ["vs","battle","clm"];
function dispRankTab(mode) {
	var onlineModeId = onlineModeIds[mode];
	document.getElementById("rankings_section").className = "subsection rank_" + onlineModeId;
	jQuery(".ranking_tab.tab_"+onlineModeId+" .ranking_badge").hide(); // TODO works only because 2 tabs
}