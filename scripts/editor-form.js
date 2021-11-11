var loadDt = 0;
function previewCircuit(elt) {
	var id = elt.getAttribute("data-id");
	if (elt.getAttribute("data-pending"))
		document.getElementById("editor-track-action-access").style.display = "none";
	else
		document.getElementById("editor-track-action-access").style.display = "";
	document.getElementById("editor-track-action-access").href = (isBattle?"battle":"map")+".php?i="+id;
	document.getElementById("editor-track-action-edit").href = (isBattle?"course":"draw")+".php?i="+id;
	document.getElementById("editor-track-action-duplicate").href = "duplicateCircuit.php?i="+id+(isBattle?"&battle":"");
	document.getElementById("editor-track-action-delete").href = (isBattle?"clear":"suppr")+".php?i="+id+"&token="+csrf;
	document.getElementById("editor-track-name").innerText = elt.getAttribute("data-name");
	document.getElementById("editor-track-img").src = elt.getAttribute("data-src");
	document.getElementById("editor-track-preview-mask").style.display = "block";
	document.onkeydown = function(e) {
		if (e.keyCode == 27)
			closePreview();
	}
}
function closePreview() {
	document.getElementById("editor-track-preview-mask").style.display = "none";
	document.onkeydown = undefined;
}
document.addEventListener("DOMContentLoaded", function() {
	var $tabSelectors = document.querySelectorAll(".editor-upload-tabs");
	for (var i=0;i<$tabSelectors.length;i++) {
		(function($tabSelector) {
			var $tabContainer = $tabSelector.parentNode;
			var $tabLinks = $tabSelector.querySelectorAll(".editor-upload-tab");
			var $tabContents = $tabContainer.querySelector(".editor-upload-inputs");
			var $tabInputs = $tabContents.querySelectorAll(".editor-upload-input");
			for (var j=0;j<$tabLinks.length;j++) {
				(function(j) {
					var $tabLink = $tabLinks[j];
					$tabLink.onclick = function() {
						var $shownTabs = $tabSelector.querySelectorAll(".editor-upload-tab-selected");
						for (var k=0;k<$shownTabs.length;k++)
							$shownTabs[k].classList.remove("editor-upload-tab-selected");
						$tabLinks[j].classList.add("editor-upload-tab-selected");
						var $shownContents = $tabContents.querySelectorAll(".editor-upload-input-selected");
						for (var k=0;k<$shownContents.length;k++) {
							$shownContents[k].classList.remove("editor-upload-input-selected");
							$shownContents[k].querySelector("input").value = "";
							$shownContents[k].querySelector("input").required = false;
						}
						$tabInputs[j].classList.add("editor-upload-input-selected");
						$tabInputs[j].querySelector("input").required = true;
						$tabInputs[j].querySelector("input").focus();
					};
				})(j);
			}
		})($tabSelectors[i]);
	}
});
function showImportHelp() {
	alert(language ?
		"If checked, the image will be hosted on MKPC at the circuit creation, which will avoid problems in case of deletion of the remote image.\nCaution, if you choose this option, your storage quota will be consumed." :
		"Si activé, l'image sera hébergée sur MKPC dès la création du circuit, ce qui évite les problèmes en cas de suppression de l'image distante.\nAttention, si vous choisissez cette option, votre quota de stockage sera consommé."
	);
}