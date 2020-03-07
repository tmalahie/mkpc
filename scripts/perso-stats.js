function updateCursors() {
	var cheated = true;
	for (var perso in cp) {
		cheated = false;
		for (var i=0;i<statTypes.length;i++) {
			var statType = statTypes[i];
			var statVal = parseInt(document.getElementById(statType).value);
			if (statVal > Math.round(cp[perso][i]*statsGradient)) {
				cheated = true;
				break;
			}
		}
		if (!cheated)
			break;
	}
	var sameAs = [];
	for (var perso in cp) {
		var same = true;
		for (var i=0;i<statTypes.length;i++) {
			var statType = statTypes[i];
			var statVal = parseInt(document.getElementById(statType).value);
			if (statVal != Math.round(cp[perso][i]*statsGradient)) {
				same = false;
				break;
			}
		}
		if (same)
			sameAs.push(perso);
	}
	var statsTemplate = document.getElementById("stats-template");
	if (sameAs.length) {
		var id = statsTemplate.selectedIndex;
		var options = statsTemplate.getElementsByTagName("option");
		var option = options[id];
		if (sameAs.indexOf(option.value) == -1) {
			for (var i=0;i<options.length;i++) {
				if (sameAs.indexOf(options[i].value) != -1) {
					statsTemplate.selectedIndex = i;
					break;
				}
			}
		}
	}
	if (cheated) {
		document.forms["perso-form"].className = "perso-form statswrong";
		document.getElementById("perso-submit").disabled = true;
	}
	else {
		document.forms["perso-form"].className = "perso-form";
		document.getElementById("perso-submit").disabled = false;
	}
}
function toPerso(sPerso) {
	if (language) {
		if (sPerso == "maskass")
			return "Shy guy";
		if (sPerso == "skelerex")
			return "Dry bones";
		if (sPerso == "harmonie")
			return "Rosalina";
		if (sPerso == "roi_boo")
			return "King boo";
		if (sPerso == "frere_marto")
			return "Hammer bro";
		if (sPerso == "bowser_skelet")
			return "Dry Bowser";
		if (sPerso == "flora_piranha")
			return "Petey Piranha";
	}
	else {
		if (sPerso == "frere_marto")
			return "Frère marto";
	}
	sPerso = sPerso.replace(/_/g, " ");

	return sPerso.charAt(0).toUpperCase() + sPerso.substring(1);
}
window.onload = function() {
	for (var i=0;i<statTypes.length;i++) {
		var statType = statTypes[i];
		document.getElementById(statType).oninput = updateCursors;
	}
	var statsTemplate = document.getElementById("stats-template");
	var inc = 0;
	for (var player in cp) {
		if (pUnlocked[inc]) {
			var statTemplate = document.createElement("option");
			statTemplate.value = player;
			if (!statTemplate.dataset)
				statTemplate.dataset = {};
			for (var i=0;i<statTypes.length;i++)
				statTemplate.dataset[statTypes[i]] = cp[player][i];
			statTemplate.innerHTML = toPerso(player);
			statsTemplate.appendChild(statTemplate);
		}
		inc++;
	}
	statsTemplate.onchange = function() {
		var id = this.selectedIndex;
		if (id) {
			var option = this.getElementsByTagName("option")[id];
			var optionData = option.dataset;
			for (var i=0;i<statTypes.length;i++) {
				var statType = statTypes[i];
				var statRange = statsRange[statType];
				var optionValue = optionData[statType];
				document.getElementById(statType).value = Math.round(optionValue*statsGradient);
			}
			updateCursors();
		}
	}
	updateCursors();
};