var selectedCircuits = [];
var loadingMsg = language ? "Loading":"Chargement";
var isMCups = (ckey === "mid");
function getSubmitMsg() {
	if (readOnly)
		return language ? "Read-only":"Lecture seule";
	if (isMCups) {
		if (selectedCircuits.length < 2)
			return (language ? "You must select at least 2 cups":"Vous devez sélectionner au moins 2 coupes");
		if (selectedCircuits.length > 40)
			return (language ? "You can select at most 40 cups":"Vous pouvez sélectionner 40 coupes au maximum");
		for (var i=0;i<actualLines.length;i++) {
			if (actualLines[i] > 8)
				return (language ? "Please define at most 8 cups per line":"Veuillez définir au plus 8 coupes par ligne");
		}
		for (var i=0;i<=cupPages.length;i++) {
			var minLine = getBeginCupPage(), maxLine = getEndCupPage();
			if ((maxLine - minLine) > 4)
				return (language ? "Please define at most 4 lines of cup":"Veuillez définir au plus 4 lignes de coupe");
			if (arraySum(actualLines.slice(minLine, maxLine)) > allCups.length)
				return (language ? "Please define at most 18 cups per page":"Veuillez définir au plus 18 coupes par page");
		}
		if (persoList) {
			if (!persoList.length)
				return (language ? "Please define at least 1 character":"Veuillez définir au moins 1 perso");
		}
		return "";
	}
	if (selectedCircuits.length != 4)
		return (language ? ("You must select 4 "+(isBattle ? "arenas":"circuits")):("Vous devez sélectionner 4 "+(isBattle ? "arènes":"circuits")));
	return "";
}
function selectCircuit(tr, isAuto) {
	if (readOnly && !isAuto) return;
	var id = tr.dataset.id;
	var selectionID = selectedCircuits.indexOf(id);
	var orderTD = tr.getElementsByClassName("td-preview")[0];
	if (selectionID == -1) {
		selectedCircuits.push(id);
		tr.className = "selected";
		orderTD.innerHTML = "#"+ selectedCircuits.length;
	}
	else {
		selectedCircuits.splice(selectionID,1);
		tr.className = "";
		orderTD.innerHTML = "";
		for (var i=0;i<selectedCircuits.length;i++) {
			var orderTDi = document.getElementById("circuit"+ selectedCircuits[i]).getElementsByClassName("td-preview")[0];
			orderTDi.innerHTML = "#"+ (i+1);
		}
	}
	if (!isAuto) {
		resetCupOptions(true);
		updateGUI();
	}
}
function selectTr(id) {
	selectCircuit(document.getElementById("circuit"+ id), true);
}
function initGUI() {
	if (editting) {
		for (var i=0;i<cids.length;i++)
			selectTr(cids[i]);
		var $cupOptions = document.getElementById("cup-options");
		if ($cupOptions && $cupOptions.value) {
			try {
				var cupOptions = JSON.parse($cupOptions.value);
				if (cupOptions && cupOptions.keyid) {
					cupOptions = JSON.parse(sessionStorage.getItem("cupopt."+cupOptions.keyid));
					$cupOptions.value = JSON.stringify(cupOptions);
				}
				if (cupOptions) {
					if (cupOptions.icons) cupIcons = cupOptions.icons;
					if (cupOptions.lines) cupLines = cupOptions.lines;
					if (cupOptions.pages) cupPages = cupOptions.pages;
					if (typeof characterRoster !== "undefined")
						persoList = characterRoster;
					if (cupOptions.customchars === 0) {
						var $customChars = document.getElementById("customchars");
						if ($customChars) $customChars.checked = false;
					}
					if (cupOptions.gp) gpOpts = cupOptions.gp;
				}
			}
			catch (e) {
			}
		}
	}
	resetCupOptions();
	updateGUI();
	if (!readOnly) {
		var $submits = document.querySelectorAll(".submit-selection");
		for (var i=0;i<$submits.length;i++)
			$submits[i].dataset.title = "";
	}
	var $prettyTitles = document.querySelectorAll(".pretty-title");
	for (var i=0;i<$prettyTitles.length;i++) {
		var $prettyTitle = $prettyTitles[i];
		initFancyTitle($prettyTitle);
	}
}
function initFancyTitle($prettyTitle) {
	var $fancyTitle = document.createElement("div");
	$fancyTitle.className = "fancy-title";
	if (!$prettyTitle.dataset) $prettyTitle.dataset = {};
	var fancyInterval;
	$prettyTitle.parentNode.onmouseover = function() {
		if ($prettyTitle.dataset.title) {
			$fancyTitle.innerHTML = $prettyTitle.dataset.title;
			document.body.appendChild($fancyTitle);
			var rect = $prettyTitle.getBoundingClientRect();
			$fancyTitle.style.left = Math.max(0,(rect.left + ($prettyTitle.scrollWidth-$fancyTitle.scrollWidth)/2))+"px";
			$fancyTitle.style.top = (rect.top - $fancyTitle.scrollHeight - 5)+"px";

			if (fancyInterval) return;
			fancyInterval = setInterval(function() {
				if (!$fancyTitle) return;
				if (document.body.contains($prettyTitle)) return;
				document.body.removeChild($fancyTitle);
				clearInterval(fancyInterval);
				$fancyTitle = undefined;
			}, 1000);
		}
	};
	$prettyTitle.parentNode.onmouseout = function() {
		if (document.body.contains($fancyTitle))
			document.body.removeChild($fancyTitle);
		clearInterval(fancyInterval);
		fancyInterval = undefined;
	};
}
function showEditorContent(id) {
	if (id == 1) {
		if (isMCups) {
			updateCupImgGUI();
			updateCupPersosGUI();
		}
		updateGpOptionsGUI();
	}
	document.querySelector(".editor-content.editor-content-active").classList.remove("editor-content-active");
	document.querySelectorAll(".editor-content")[id].classList.add("editor-content-active");
}
function updateGUI() {
	var cidCtn = document.getElementById("cid-ctn");
	for (var i=0;i<selectedCircuits.length;i++) {
		var cidI = document.getElementById(ckey + i);
		if (!cidI) {
			cidI = document.createElement("input");
			cidI.type = "hidden";
			cidI.id = ckey + i;
			cidI.name = ckey + i;
			cidCtn.appendChild(cidI);
		}
		cidI.value = selectedCircuits[i];
	}
	for (var i=selectedCircuits.length;document.getElementById(ckey + i);i++)
		cidCtn.removeChild(document.getElementById(ckey + i));
	updateSubmitMsg();
}
function updateSubmitMsg() {
	var errorMsg = getSubmitMsg();
	document.getElementById("nb-selected").innerHTML = selectedCircuits.length;
	var $submits = document.querySelectorAll(".submit-selection");
	for (var i=0;i<$submits.length;i++) {
		$submits[i].disabled = !!errorMsg;
		$submits[i].dataset.title = errorMsg;
	}
}
function previewImg(e,src) {
	apercu(src);
	e.stopPropagation();
}
var cupIcons = [];
var cupLines = [];
var persoList;
var gpOpts = {};
var actualIcons, actualLines;
var allCups = ["champi", "etoile", "carapace", "carapacebleue", "speciale", "carapacerouge", "banane", "feuille", "megachampi", "eclair", "upchampi", "fireflower", "bobomb", "minichampi", "egg", "iceflower", "plume", "cloudchampi"];
var cupPage = 0;
var cupPages = [];
function resetCupOptions(full) {
	var $cupOptions = document.getElementById("cup-options");
	if (!$cupOptions) return;
	if (isMCups) {
		var nbCups = selectedCircuits.length;
		var cups_per_line = 6;
		var nb_lines = Math.ceil(nbCups/cups_per_line);
		if (nbCups < allCups.length)
			cups_per_line = Math.ceil(nbCups/nb_lines);
		actualIcons = [];
		var usedIcons = {};
		for (var i=0;i<cupIcons.length;i++) {
			actualIcons[i] = cupIcons[i];
			usedIcons[actualIcons[i]] = 1;
		}
		var cup = 0;
		while (actualIcons.length < nbCups) {
			for (;cup<allCups.length;cup++) {
				if (!usedIcons[cup]) {
					actualIcons.push(cup);
					usedIcons[cup] = 1;
					break;
				}
			}
			if (cup >= allCups.length) {
				usedIcons = {};
				cup = 0;
			}
		}
		actualIcons.length = nbCups;

		actualLines = [];
		var nbCells = 0;
		for (var i=0;i<cupLines.length;i++) {
			actualLines.push(cupLines[i]);
			nbCells += cupLines[i];
		}
		if (nbCells < actualIcons.length) {
			if (!(actualLines[actualLines.length-1] < cups_per_line))
				actualLines.push(0);
			actualLines[actualLines.length-1] += actualIcons.length-nbCells;
			while (actualLines[actualLines.length-1] > cups_per_line) {
				actualLines.push(actualLines[actualLines.length-1]-cups_per_line)
				actualLines[actualLines.length-2] = cups_per_line;
			}
		}
		else {
			while (nbCells > actualIcons.length)
				nbCells -= actualLines.pop();
			if (nbCells < actualIcons.length) {
				actualLines.push(actualIcons.length-nbCells);
				nbCells = actualIcons.length;
			}
		}
		for (var i=cupPages.length-1;i>=0;i--) {
			if (cupPages[i] < actualLines.length) break;
			cupPages.pop();
		}
		if (cupPage > cupPages.length) cupPage = cupPages.length;
		if (full) {
			for (var i=0;i<=cupPages.length;i++) {
				var minCup = getBeginCupPage(i), maxCup = getEndCupPage(i);
				var cupDiff = 0;
				for (var j=minCup;j<maxCup;j++) {
					cupDiff += actualLines[j];
					if (cupDiff > allCups.length) {
						cupPages.push(j);
						break;
					}
				}
			}
		}
	}

	var cupOptions = {};
	if (isMCups) {
		if (cupIcons.length)
			cupOptions.icons = actualIcons;
		if (cupLines.length)
			cupOptions.lines = actualLines;
		if (cupPages.length) {
			cupOptions.pages = cupPages;
			cupOptions.lines = actualLines;
		}
	}
	if (persoList) {
		cupOptions.persos = persoList.map(function(data) {
			if (data.id)
				return data.id;
			return data.sprites;
		});
	}
	var $customChars = document.getElementById("customchars");
	if ($customChars && !$customChars.checked)
		cupOptions.customchars = 0;
	if (gpOpts && hasGpOpts(gpOpts))
		cupOptions.gp = gpOpts;
	var cupOptionsJSON = JSON.stringify(cupOptions);
	if (cupOptionsJSON === "{}")
		cupOptionsJSON = "";
	$cupOptions.value = cupOptionsJSON;
}
function hasGpOpts(opts) {
	if (!opts) return false;
	for (var k in opts) {
		if (opts[k] != null && opts[k] !== "")
			return true;
	}
	return false;
}
function resetCupAppearance() {
	if (confirm(language ? "Reset cup appearance to default?":"Rétablir l'apparence par défaut ?")) {
		cupLines = [];
		cupIcons = [];
		cupPages = [];
		cupPage = 0;
		updateCupImgGUI();
	}
}
function resetCharacterRoster() {
	if (confirm(language ? "Reset character roster to default?":"Rétablir la liste des persos par défaut ?")) {
		persoList = undefined;
		updateCupPersosGUI();
	}
}
function resetGpOptions() {
	if (confirm(language ? "Reset Grand Prix options to default?":"Rétablir les options Grand Prix par défaut ?")) {
		gpOpts = {};
		updateGpOptionsGUI();
		resetCupOptions();
		updateSubmitMsg();
	}
}
var GP_DEFAULT_NB_CPUS = 7;
var GP_MIN_CPUS = 1;
var GP_MAX_CPUS = 11;
var GP_DEFAULT_DIFFICULTY = 2;
var gpDifficultyLabels = ["Easy", "Medium", "Difficult", "Extreme", "Impossible"];
var gpDifficultyLabelsFr = ["Facile", "Moyen", "Difficile", "Extrême", "Impossible"];
function gpDefaultLabel(name) {
	return (language ? "Default (" : "Défaut (") + name + ")";
}
var gpItemDistribNames = language
	? ["Standard", "Aggressive mode", "Bob-ombs", "Mushrooms", "None"]
	: ["Classique", "Mode explosif", "Bob-ombs", "Champis", "Aucun"];
function getCustomItemDistribs() {
	try {
		var raw = localStorage.getItem("itemsets");
		if (!raw) return [];
		var parsed = JSON.parse(raw);
		return (parsed && parsed.VS) ? parsed.VS : [];
	}
	catch (e) {
		return [];
	}
}
var gpCcOptions = [
	{ value: "50",    label: "50cc" },
	{ value: "100",   label: "100cc" },
	{ value: "150",   label: "150cc" },
	{ value: "150m",  label: language ? "150cc mirror":"150cc miroir" },
	{ value: "200",   label: "200cc" }
];
function defaultGpCpu() {
	return { driver: "", difficulty: null };
}
function defaultGpPoints(nbRacers) {
	var base = [10, 8, 6, 4, 3, 2, 1];
	var res = [];
	for (var i=0;i<nbRacers;i++) {
		if (i < base.length) res.push(base[i]);
		else res.push(0);
	}
	return res;
}
function ensureGpCpus() {
	if (gpOpts.cpus) return;
	gpOpts.cpus = [];
	for (var i=0;i<GP_DEFAULT_NB_CPUS;i++)
		gpOpts.cpus.push(defaultGpCpu());
}
function syncGpPoints() {
	if (!gpOpts.points) return;
	var target = (gpOpts.cpus ? gpOpts.cpus.length : GP_DEFAULT_NB_CPUS) + 1;
	while (gpOpts.points.length < target) gpOpts.points.push(0);
	if (gpOpts.points.length > target) gpOpts.points.length = target;
}
function ordinalLabel(i) {
	if (language) {
		var n = i+1;
		if (n === 1) return "1st";
		if (n === 2) return "2nd";
		if (n === 3) return "3rd";
		return n + "th";
	}
	return (i+1) + (i === 0 ? "er" : "e");
}
function gpDriverList() {
	var res = [];
	for (var p in cp) {
		var name = (typeof cpNames !== "undefined" && cpNames[p]) ? cpNames[p] : p;
		res.push({ id: p, name: name });
	}
	return res;
}
function updateGpOptionsGUI() {
	var $gpOptions = document.getElementById("gp-options");
	if (!$gpOptions) return;
	$gpOptions.innerHTML = "";

	function appendOption($select, value, label, selectedValue) {
		var $opt = document.createElement("option");
		$opt.value = value;
		$opt.innerHTML = label;
		if (String(selectedValue) === String(value))
			$opt.selected = "selected";
		$select.appendChild($opt);
	}
	function makeBaseRow(labelText) {
		var $row = document.createElement("label");
		$row.className = "gp-base-row";
		var $label = document.createElement("span");
		$label.className = "gp-base-label";
		$label.appendChild(document.createTextNode(labelText));
		$row.appendChild($label);
		return $row;
	}

	var diffLabels = language ? gpDifficultyLabels : gpDifficultyLabelsFr;
	var globalDifficulty = (gpOpts.difficulty != null) ? gpOpts.difficulty : GP_DEFAULT_DIFFICULTY;
	var defaultLabel = language ? "Default" : "Par défaut";
	var customLabel = language ? "Custom..." : "Personnalisé...";

	var $base = document.createElement("fieldset");
	$base.className = "gp-options-base";
	var $baseLegend = document.createElement("legend");
	$base.appendChild($baseLegend);
	var $baseHelp = document.createElement("p");
	$baseHelp.className = "gp-options-help";
	$baseHelp.innerHTML = ('<img src="images/cups/cup1.png" alt="" />') + (language ? "These settings only apply to <strong>Grand Prix</strong> mode." : "Ces réglages s'appliquent uniquement au mode <strong>Grand Prix</strong>.");
	$base.appendChild($baseHelp);

	// Difficulty
	var $diffRow = makeBaseRow(language ? "Difficulty:" : "Difficulté :");
	var $diffGlobal = document.createElement("select");
	var diffGlobalSelected = (gpOpts.difficulty != null && gpOpts.difficulty !== GP_DEFAULT_DIFFICULTY) ? gpOpts.difficulty : "";
	for (var i=0;i<diffLabels.length;i++) {
		var diffIsDefault = (i === GP_DEFAULT_DIFFICULTY);
		appendOption($diffGlobal, diffIsDefault ? "" : i, diffIsDefault ? gpDefaultLabel(diffLabels[i]) : diffLabels[i], diffGlobalSelected);
	}
	$diffGlobal.onchange = function() {
		gpOpts.difficulty = (this.value === "") ? null : +this.value;
		updateGpOptionsGUI();
		resetCupOptions();
		updateSubmitMsg();
	};
	$diffRow.appendChild($diffGlobal);
	$base.appendChild($diffRow);

	// Speed class
	var $ccRow = makeBaseRow(language ? "Speed class:" : "Cylindrée :");
	var $cc = document.createElement("select");
	var ccRaw = (gpOpts.cc != null) ? (gpOpts.cc + (gpOpts.mirror ? "m":"")) : "";
	var ccSelected = (ccRaw === "150") ? "" : ccRaw;
	for (var i=0;i<gpCcOptions.length;i++) {
		var ccIsDefault = (gpCcOptions[i].value === "150");
		appendOption($cc, ccIsDefault ? "" : gpCcOptions[i].value, ccIsDefault ? gpDefaultLabel(gpCcOptions[i].label) : gpCcOptions[i].label, ccSelected);
	}
	$cc.onchange = function() {
		if (this.value === "") {
			gpOpts.cc = null;
			gpOpts.mirror = null;
		}
		else {
			gpOpts.cc = parseInt(this.value, 10);
			gpOpts.mirror = (this.value.charAt(this.value.length-1) === "m") ? 1 : null;
		}
		resetCupOptions();
		updateSubmitMsg();
	};
	$ccRow.appendChild($cc);
	$base.appendChild($ccRow);

	// CPU drivers (Default / Custom...)
	var $cpusRow = makeBaseRow(language ? "CPU drivers:" : "Persos CPU :");
	var $cpusSelect = document.createElement("select");
	$cpusSelect.className = "gp-cpus-mode";
	var cpusMode = gpOpts.cpus ? "custom" : "default";
	appendOption($cpusSelect, "default", language ? "Default (random)" : "Défault (aléatoire)", cpusMode);
	appendOption($cpusSelect, "custom", customLabel, cpusMode);
	$cpusSelect.onchange = function() {
		if (this.value === "custom")
			ensureGpCpus();
		else
			gpOpts.cpus = null;
		syncGpPoints();
		updateGpOptionsGUI();
		resetCupOptions();
		updateSubmitMsg();
	};
	$cpusRow.appendChild($cpusSelect);
	$base.appendChild($cpusRow);

	if (gpOpts.cpus) {
		var $cpuList = document.createElement("div");
		$cpuList.className = "gp-cpu-list";
		var $cpusHelp = document.createElement("p");
		$cpusHelp.className = "gp-options-help";
		$cpusHelp.innerHTML = language ? "Select CPU drivers and override difficulty per CPU if needed" : "Sélectionnez les persos CPU et surchargez leur difficulté au besoin";
		$cpuList.appendChild($cpusHelp);

		var drivers = gpDriverList();
		for (var i=0;i<gpOpts.cpus.length;i++) (function(i) {
			var cpu = gpOpts.cpus[i];
			var $row = document.createElement("div");
			$row.className = "gp-cpu-row";
			var $label = document.createElement("span");
			$label.className = "gp-cpu-label";
			$label.innerHTML = (language ? "CPU " : "CPU ") + (i+1) + ":";
			$row.appendChild($label);

			var $driver = document.createElement("select");
			appendOption($driver, "", language ? "Random" : "Aléatoire", cpu.driver || "");
			for (var d=0;d<drivers.length;d++)
				appendOption($driver, drivers[d].id, drivers[d].name, cpu.driver || "");
			$driver.onchange = function() {
				cpu.driver = this.value;
				resetCupOptions();
			};
			$row.appendChild($driver);

			var $diff = document.createElement("select");
			var diffSelected = (cpu.difficulty != null && cpu.difficulty !== globalDifficulty) ? cpu.difficulty : "";
			for (var d=0;d<diffLabels.length;d++) {
				var dIsDefault = (d === globalDifficulty);
				appendOption($diff, dIsDefault ? "" : d, dIsDefault ? gpDefaultLabel(diffLabels[d]) : diffLabels[d], diffSelected);
			}
			$diff.onchange = function() {
				cpu.difficulty = (this.value === "") ? null : +this.value;
				resetCupOptions();
			};
			$row.appendChild($diff);
			$cpuList.appendChild($row);
		})(i);

		var $cpusButtons = document.createElement("div");
		$cpusButtons.className = "gp-cpu-buttons";
		var $addCpu = document.createElement("button");
		$addCpu.type = "button";
		$addCpu.className = "gp-add-cpu";
		$addCpu.innerHTML = language ? "Add CPU" : "Ajouter un CPU";
		$addCpu.disabled = (gpOpts.cpus.length >= GP_MAX_CPUS);
		$addCpu.onclick = function() {
			if (gpOpts.cpus.length >= GP_MAX_CPUS) return;
			gpOpts.cpus.push(defaultGpCpu());
			syncGpPoints();
			updateGpOptionsGUI();
			resetCupOptions();
		};
		$cpusButtons.appendChild($addCpu);
		var $rmCpu = document.createElement("button");
		$rmCpu.type = "button";
		$rmCpu.className = "gp-remove-cpu";
		$rmCpu.innerHTML = language ? "Remove CPU" : "Retirer un CPU";
		$rmCpu.disabled = (gpOpts.cpus.length <= GP_MIN_CPUS);
		$rmCpu.onclick = function() {
			if (gpOpts.cpus.length <= GP_MIN_CPUS) return;
			gpOpts.cpus.pop();
			syncGpPoints();
			updateGpOptionsGUI();
			resetCupOptions();
		};
		$cpusButtons.appendChild($rmCpu);
		$cpuList.appendChild($cpusButtons);
		$base.appendChild($cpuList);
	}

	// Item distribution
	var $itemsRow = makeBaseRow(language ? "Items distribution:" : "Distribution des objets :");
	var $items = document.createElement("select");
	$items.id = "gp-items";
	var itemsSel = "";
	if (gpOpts.items) {
		if (gpOpts.items.index != null)
			itemsSel = (gpOpts.items.index === 0) ? "" : "b" + gpOpts.items.index;
		else if (gpOpts.items.value) itemsSel = "c" + (gpOpts.items.name || "");
	}
	for (var i=0;i<gpItemDistribNames.length;i++) {
		var itemIsDefault = (i === 0);
		appendOption($items, itemIsDefault ? "" : ("b" + i), itemIsDefault ? gpDefaultLabel(gpItemDistribNames[i]) : gpItemDistribNames[i], itemsSel);
	}
	var customs = getCustomItemDistribs();
	var customNames = {};
	if (customs.length) {
		var $optGroup = document.createElement("optgroup");
		$optGroup.label = language ? "Custom" : "Personnalisés";
		for (var i=0;i<customs.length;i++) {
			customNames[customs[i].name] = 1;
			var $opt = document.createElement("option");
			$opt.value = "c" + customs[i].name;
			$opt.innerHTML = customs[i].name;
			if ("c" + customs[i].name === itemsSel)
				$opt.selected = "selected";
			$optGroup.appendChild($opt);
		}
		$items.appendChild($optGroup);
	}
	// Orphan fallback: if the saved custom distribution no longer exists in
	// localStorage (user deleted it), keep a "Custom" option carrying the saved
	// data so the cup setting isn't silently lost.
	if (gpOpts.items && gpOpts.items.value && !customNames[gpOpts.items.name || ""]) {
		var $orphan = document.createElement("option");
		$orphan.value = "o";
		$orphan.innerHTML = language ? "Custom" : "Personnalisé";
		$orphan.selected = "selected";
		$items.insertBefore($orphan, $items.firstChild.nextSibling);
		itemsSel = "o";
	}
	$items.onchange = function() {
		if (this.value === "")
			gpOpts.items = null;
		else if (this.value === "o")
			return; // orphan placeholder; keep gpOpts.items as-is
		else if (this.value.charAt(0) === "b")
			gpOpts.items = { index: parseInt(this.value.substring(1), 10) };
		else {
			var name = this.value.substring(1);
			var customs = getCustomItemDistribs();
			var picked = null;
			for (var i=0;i<customs.length;i++) {
				if (customs[i].name === name) { picked = customs[i]; break; }
			}
			gpOpts.items = picked ? { name: picked.name, value: picked.value } : null;
		}
		resetCupOptions();
	};
	$itemsRow.appendChild($items);
	$base.appendChild($itemsRow);

	// Point distribution (Default / Custom...)
	var $ptsRow = makeBaseRow(language ? "Points distribution:" : "Distribution des points :");
	var $ptsSelect = document.createElement("select");
	$ptsSelect.className = "gp-points-mode";
	var ptsMode = gpOpts.points ? "custom" : "default";
	appendOption($ptsSelect, "default", defaultLabel, ptsMode);
	appendOption($ptsSelect, "custom", customLabel, ptsMode);
	$ptsSelect.onchange = function() {
		if (this.value === "custom") {
			var nbCpus = gpOpts.cpus ? gpOpts.cpus.length : GP_DEFAULT_NB_CPUS;
			gpOpts.points = defaultGpPoints(nbCpus + 1);
		}
		else
			gpOpts.points = null;
		updateGpOptionsGUI();
		resetCupOptions();
	};
	$ptsRow.appendChild($ptsSelect);
	$base.appendChild($ptsRow);

	if (gpOpts.points) {
		var $ptsList = document.createElement("div");
		$ptsList.className = "gp-points-list";
		for (var i=0;i<gpOpts.points.length;i++) (function(i) {
			var $row = document.createElement("div");
			$row.className = "gp-point-row";
			var $label = document.createElement("label");
			$label.htmlFor = "gp-point-" + i;
			$label.innerHTML = ordinalLabel(i) + ":";
			$row.appendChild($label);
			var $input = document.createElement("input");
			$input.id = "gp-point-" + i;
			$input.type = "number";
			$input.min = 0;
			$input.value = gpOpts.points[i];
			$input.oninput = function() {
				var v = parseInt(this.value, 10);
				if (isNaN(v) || v < 0) v = 0;
				gpOpts.points[i] = v;
				resetCupOptions();
			};
			$row.appendChild($input);
			$ptsList.appendChild($row);
		})(i);
		$base.appendChild($ptsList);
	}

	$gpOptions.appendChild($base);

	var $reset = document.getElementById("reset-gp-options");
	if ($reset) $reset.style.display = hasGpOpts(gpOpts) ? "" : "none";
}
function updateCupImgGUI() {
	resetCupOptions();
	var $cupAppearance = document.getElementById("cup-appearance");
	$cupAppearance.innerHTML = "";
	nbRowsInLine = 0;
	var currentLine = 0;
	var minLine = getBeginCupPage(), maxLine = getEndCupPage();
	for (var i=0;i<actualIcons.length;i++) {
		var inPage = (currentLine >= minLine && currentLine < maxLine);
		if (inPage) {
			var $cupImg = document.createElement("img");
			$cupImg.className = "pixelated";
			if (typeof actualIcons[i] === "number")
				$cupImg.src = "images/cups/"+ allCups[actualIcons[i]] +".gif";
			else {
				$cupImg.src = actualIcons[i];
				$cupImg.onload = function() {
					if (this.naturalWidth > this.naturalHeight) {
						this.style.width = "50px";
						this.style.height = "auto";
					}
					else {
						this.style.width = "auto";
						this.style.height = "50px";
					}
				}
			}
			if (!$cupImg.dataset) $cupImg.dataset = {};
			$cupImg.dataset.id = i;
			$cupImg.onclick = function() {
				this.className = "editing-cup-img";
				selectCupImg(+this.dataset.id);
			};
			$cupAppearance.appendChild($cupImg);
		}
		if (i < (actualIcons.length-1)) {
			var $cursor = null;
			if (inPage) {
				$cursor = document.createElement("div");
				$cursor.className = "cup-appearance-cursor";
				$cursor.title = language ? "Add/remove line break" : "Ajouter/Supprimer un retour à la ligne";
				$cursor.onclick = function(e) {
					selectLineCursor(this,e);
				}
				$cursor.oncontextmenu = function(e) {
					selectLineCursor(this,e);
					return false;
				}
				$cursor.innerHTML = "<div></div>";
				$cupAppearance.appendChild($cursor);
			}
			nbRowsInLine++;
			if (nbRowsInLine >= actualLines[currentLine]) {
				if (inPage) $cupAppearance.appendChild(document.createElement("br"));
				nbRowsInLine = 0;
				currentLine++;
			}
			if ($cursor) {
				if (!$cursor.dataset) $cursor.dataset = {};
				$cursor.dataset.line = currentLine;
				$cursor.dataset.row = nbRowsInLine;
			}
		}
	}
	updateCupPageGUI();
	document.getElementById("reset-cup-appearance").style.display = ((cupLines.length || cupIcons.length || cupPages.length)) ? "":"none";
	updateSubmitMsg();
}
function updateCupPageGUI() {
	if (!cupPages.length) {
		document.getElementById("cup-appearance-page").style.display = "none";
		return;
	}
	document.getElementById("cup-appearance-page").style.display = "";
	document.getElementById("cup-appearance-page-prev").disabled = (cupPage <= 0);
	document.getElementById("cup-appearance-page-next").disabled = (cupPage >= cupPages.length);
}
function selectCupPage(p) {
	cupPage = p;
	updateCupImgGUI();
}
function prevCupPage() {
	selectCupPage(cupPage-1);
}
function nextCupPage() {
	selectCupPage(cupPage+1);
}
function addCupPage(line) {
	cupPages.splice(cupPage,0,line);
	updateCupImgGUI();
}
function getBeginCupPage(p) {
	if (p === undefined) p = cupPage;
	if (p > 0)
		return cupPages[p-1];
	return 0;
}
function getBeginCupPageId(p) {
	if (p === undefined) p = cupPage;
	if (p > 0)
		return arraySum(actualLines.slice(0,cupPages[p-1]));
	return 0;
}
function getEndCupPage(p) {
	if (p === undefined) p = cupPage;
	if (p < cupPages.length)
		return cupPages[p];
	return actualLines.length;
}
function getEndCupPageId(p) {
	if (p === undefined) p = cupPage;
	if (p < cupPages.length)
		return arraySum(actualLines.slice(0,cupPages[p]));
	return selectedCircuits.length;
}
function arraySum(arr) {
	var sum = 0;
	for (var i=0;i<arr.length;i++)
		sum += arr[i];
	return sum;
}
function updateCupPersosGUI() {
	resetCupOptions();
	var pList = getPersosList();
	var $characterRoster = document.getElementById("character-roster");
	$characterRoster.innerHTML = "";
	var nbPersos = Math.min(24 * 5, pList.length+1);
	for (let i=0;i<nbPersos;i++) {
		var oPerso = pList[i];
		var oDiv = document.createElement("div");
		oDiv.className = "character-tile";
		var oDiv2 = document.createElement("div");
		if (oPerso) {
			var oCross = document.createElement("a");
			oCross.href = "#null";
			oCross.innerHTML = "&times";
			oCross.onclick = function(e) {
				e.stopPropagation();
				var pList = getPersosList();
				pList.splice(i, 1);
				persoList = pList;
				updateCupPersosGUI();
				return false;
			};
			oDiv.appendChild(oCross);
			var oImg = document.createElement("img");
			if (oPerso.id) {
				oImg.src = "images/sprites/uploads/"+oPerso.sprites+".png";
				oDiv.title = oPerso.name;
			}
			else
				oImg.src = "images/sprites/sprite_"+oPerso.sprites+".png";
			oImg.alt = oPerso.sprites;
			oDiv2.appendChild(oImg);
		}
		else
			oDiv2.innerHTML = "<span>+</span>";
		oDiv.appendChild(oDiv2);
		oDiv.onclick = function() {
			selectPersoImg(i);
		};
		$characterRoster.appendChild(oDiv);

		if ((i % 24) == 23) {
			var separator = document.createElement("hr");
			$characterRoster.appendChild(separator);
		}
	}
	document.getElementById("reset-character-roster").style.display = persoList ? "":"none";
	updateSubmitMsg();
}
function getPersosList() {
	if (persoList)
		return persoList;
	var res = [];
	for (var perso in cp)
		res.push({sprites:perso});
	return res;
}
function selectCupImg(cup) {
	var $mask = document.createElement("div");
	$mask.className = "editor-mask editor-mask-dark";
	document.body.appendChild($mask);
	function closeMask() {
		document.removeEventListener("keydown", hideOnEscape);
		document.body.removeChild($mask);
		var $editingImg = document.querySelector(".editing-cup-img");
		if ($editingImg) $editingImg.className = "";
	}
	function hideOnEscape(e) {
		switch (e.keyCode) {
		case 27:
			closeMask();
		}
	}
	document.addEventListener("keydown", hideOnEscape);
	$mask.onclick = closeMask;
	var oCupSelector = document.createElement("div");
	oCupSelector.className = "editor-mask-content";
	oCupSelector.innerHTML	= '<h3>'+ (language ? "Cup image selection..." : "Sélection de l'image...") +'</h3>'
							+ '<div id="cup-selection-choices"></div>'
							+ '<form name="cup-selection-custom" id="cup-selection-custom">'
								+ '<em>'+ (language ? 'OR':'OU') +'</em> '
								+ (language ? 'custom image':'image personnalisée') +' : '
								+ '<input type="url" name="cup-selection-url" required="required" placeholder="https://www.mariowiki.com/images/c/c5/Thunder_Cloud_Artwork_-_Mario_Kart_Wii.png" />'
								+ '<input type="submit" value="Ok" />'
							+ '</form>';
	oCupSelector.onclick = function(e) {
		e.stopPropagation();
	}
	$mask.appendChild(oCupSelector);
	var $cupSelectionChoices = document.getElementById("cup-selection-choices");
	for (var i=0;i<allCups.length;i++) {
		var oImg = document.createElement("img");
		oImg.src = "images/cups/"+ allCups[i] +".gif";
		if (!oImg.dataset) oImg.dataset = {};
		oImg.dataset.id = i;
		oImg.onclick = function() {
			cupIcons = actualIcons;
			var id = +this.dataset.id;
			var minCup = getBeginCupPageId(), maxCup = getEndCupPageId();
			var oldCup = cupIcons.slice(minCup,maxCup).indexOf(id);
			if (oldCup != -1)
				cupIcons[minCup+oldCup] = cupIcons[cup];
			cupIcons[cup] = id;
			closeMask();
			updateCupImgGUI();
		};
		$cupSelectionChoices.appendChild(oImg);
	}
	var $customUrlform = document.forms["cup-selection-custom"];
	$customUrlform.onsubmit = function(e) {
		e.preventDefault();
		cupIcons = actualIcons;
		cupIcons[cup] = this.elements["cup-selection-url"].value;
		closeMask();
		updateCupImgGUI();
	}
	if (typeof actualIcons[cup] === "string")
		$customUrlform.elements["cup-selection-url"].value = actualIcons[cup];
}
function selectLineCursor($editingCursor,e) {
	$editingCursor.className = "cup-appearance-cursor editing-cup-cursor";
	var line = +$editingCursor.dataset.line, row = +$editingCursor.dataset.row;
	var eol = !row;
	var $mask = document.createElement("div");
	$mask.className = "editor-mask";
	var blinkToggle = setInterval(function() {
		if ($editingCursor.className == "cup-appearance-cursor")
			$editingCursor.className = "cup-appearance-cursor editing-cup-cursor";
		else
			$editingCursor.className = "cup-appearance-cursor";
	}, 500);
	var items = [{
		label: language ? "Cancel" : "Annuler",
		select: function(){}
	}];
	function addNewLine() {
		cupLines = actualLines;
		cupLines[line] -= row;
		cupLines.splice(line, 0, row);
		cupLines[line] = row;
		for (var i=cupPage;i<cupPages.length;i++)
			cupPages[i]++;
		updateCupImgGUI();
	}
	function addNewPage() {
		addCupPage(line);
	}
	function rmNewLine() {
		cupLines = actualLines;
		cupLines[line-1] += cupLines[line];
		cupLines.splice(line, 1);
		var isEOL = (cupPages[cupPage] === line);
		for (var i=cupPage;i<cupPages.length;i++)
			cupPages[i]--;
		if (isEOL)
			cupPages.splice(cupPage,1);
		updateCupImgGUI();
	}
	if (eol) {
		if (cupPages[cupPage] !== line) {
			items.push({
				label: language ? '<span title="Split cups into multiple pages in selection screen. Do it for large multicups">New page here</span>' : '<span title="Séparer les coupes en plusieurs pages dans l\'écran de sélection. Utile pour les grosses multicoupes">Nouvelle page ici</span>',
				select: addNewPage
			});
		}
		items.push({
			label: language ? "<strong>Remove line</strong>" : "<strong>Supprimer ligne</strong>",
			select: rmNewLine
		});
	}
	else {
		items.push({
			label: language ? "<strong>New line here</strong>" : "<strong>Nouvelle ligne ici</strong>",
			select: addNewLine
		});
	}
	createContextMenu({
		event: e,
		items: items,
		onclose: function() {
			$editingCursor.className = "cup-appearance-cursor";
			clearInterval(blinkToggle);
		}
	});
}
function createContextMenu(options) {
	var e = options.event;
	var items = options.items;
	var $mask = document.createElement("div");
	$mask.className = "editor-mask";
	function closeMask() {
		document.removeEventListener("keydown", hideOnEscape);
		document.body.removeChild($mask);
		if (options.onclose)
			options.onclose();
	}
	function hideOnEscape(e) {
		switch (e.keyCode) {
		case 27:
			closeMask();
			break;
		}
	}
	document.body.appendChild($mask);
	var oContextMenu = document.createElement("div");
	for (let item of items) {
		oContextMenu.style.position = "absoulte";
		oContextMenu.className = "editor-mask-contextmenu";
		var oContextMenuItem = document.createElement("div");
		oContextMenuItem.innerHTML = item.label;
		oContextMenuItem.onclick = function() {
			item.select();
			closeMask();
		}
		oContextMenuItem.oncontextmenu = function() {
			item.select();
			closeMask();
			return false;
		}
		oContextMenu.appendChild(oContextMenuItem);
	}
	oContextMenu.onclick = function(e) {
		e.stopPropagation();
	};
	oContextMenu.style.visibility = "hidden";
	$mask.appendChild(oContextMenu);
	oContextMenu.style.left = Math.min(e.clientX, (window.innerWidth||screen.width)-oContextMenu.scrollWidth) +"px";
	oContextMenu.style.top = (e.clientY-oContextMenu.scrollHeight) +"px";
	oContextMenu.style.visibility = "";
	document.addEventListener("keydown", hideOnEscape);
	$mask.onclick = closeMask;
}
var customCharacters;
function selectPersoImg(pos) {
	var $mask = document.createElement("div");
	$mask.className = "editor-mask editor-mask-dark";
	document.body.appendChild($mask);
	function closeMask() {
		document.removeEventListener("keydown", hideOnEscape);
		document.body.removeChild($mask);
		var $editingImg = document.querySelector(".editing-cup-img");
		if ($editingImg) $editingImg.className = "";
	}
	function hideOnEscape(e) {
		switch (e.keyCode) {
		case 27:
			closeMask();
		}
	}
	document.addEventListener("keydown", hideOnEscape);
	$mask.onclick = closeMask;
	var oPersoSelector = document.createElement("div");
	oPersoSelector.className = "editor-mask-content";
	oPersoSelector.innerHTML = '<h3>'+ (language ? "Character selection..." : "Sélection du perso...") +'</h3>'
							+ '<a class="perso-selection-close" href="javascript:void(0)">&times;</a>'
							+ '<div class="perso-selection-standard"></div>'
								+ '<h4>'+ (language ? "Basic characters":"Persos de base") +'</h4>'
								+ '<div class="perso-selection-choices" id="perso-selection-standard-choices"></div>'
							+ '<div class="perso-selection-custom">'
								+ '<h4>'+ (language ? "Custom characters":"Persos custom") +'</h4>'
								+ '<div class="perso-selection-custom-explain"></div>'
								+ '<div id="perso-info">'
									+'<div>'
										+ '<div id="perso-info-name">Mario</div>'
										+ '<div class="perso-info-share" id="perso-info-shared">✓ '+ (language ? "Shared character":"Perso partagé") +'</div>'
										+ '<div class="perso-info-share" id="perso-info-unshared">🔒 '+ (language ? "Non-shared character":"Perso non partagé") +'</div>'
									+'</div>'
								+ '</div>'
								+ '<div class="perso-selection-choices" id="perso-selection-custom-choices"></div>'
								+ '<div class="perso-selection-collab">'
									+ '<div class="perso-selection-collab-toggle">+ <a href="javascript:void(0)">'+ (language ? "Select a character from another member..." : "Sélectionner le perso d'un autre membre...") +'</a></div>'
									+ '<form id="perso-selection-collab">'
										+ '<label>'
											+ '<span>'+ (language ? 'Collaboration link' : 'Lien de collaboration') + '<span class="pretty-title-ctn"><a href="javascript:void(0)">[?]</a></span>:&nbsp;</span>'
											+ '<input type="url" name="collab-link" required="required" placeholder="'+ collabCharPlaceholder +'" />'
											+ '<button type="submit">Ok</button>'
										+ '</label>'
									+ '</form>'
								+ '</div>'
							+ '</div>';

	oPersoSelector.onclick = function(e) {
		e.stopPropagation();
	}
	oPersoSelector.querySelector(".perso-selection-close").onclick = closeMask;
	$mask.appendChild(oPersoSelector);
	var $persoSelectionChoices = document.getElementById("perso-selection-standard-choices");
	function appendPersoChoice($div, src, data) {
		var oDiv = document.createElement("div");
		var oDiv2 = document.createElement("div");
		var oImg = document.createElement("img");
		oImg.src = src;
		oImg.onclick = function() {
			doSelectPerso(data);
		};
		oDiv2.appendChild(oImg);
		oDiv.appendChild(oDiv2);
		$div.appendChild(oDiv);
		return oDiv;
	}
	function doSelectPerso(data) {
		var perso = data.sprites;
		var pList = getPersosList();
		var persoCurrentElt = perso && pList.find(function(p) { return p && p.sprites === perso });
		var persoPos = persoCurrentElt ? pList.indexOf(persoCurrentElt) : -1;
		var currentPerso = pList[pos];
		if ((persoPos !== -1) && !currentPerso) {
			pos = pList.length - 1;
			currentPerso = pList[pos];
		}
		pList[pos] = data;
		if (persoPos !== -1)
			pList[persoPos] = currentPerso;
		persoList = pList;
		closeMask();
		updateCupPersosGUI();
	}
	for (var perso in cp) {
		appendPersoChoice($persoSelectionChoices, "images/sprites/sprite_"+ perso +".png", {
			sprites : perso
		});
	}
	function appendCustomCharacters() {
		var $persoSelectionChoices = document.getElementById("perso-selection-custom-choices");
		for (var i=0;i<customCharacters.length;i++) {
			let customCharacter = customCharacters[i];
			var oDiv = appendPersoChoice($persoSelectionChoices, customCharacter.ld, customCharacter);
			oDiv.onmouseover = function() {
				oPersoSelector.querySelector("#perso-info-name").innerText = customCharacter.name;
				if (customCharacter.shared) {
					oPersoSelector.querySelector("#perso-info-shared").style.display = "block";
					oPersoSelector.querySelector("#perso-info-unshared").style.display = "none";
				}
				else {
					oPersoSelector.querySelector("#perso-info-shared").style.display = "none";
					oPersoSelector.querySelector("#perso-info-unshared").style.display = "block";
				}
				oPersoSelector.querySelector("#perso-info").style.display = "block";
			};
			oDiv.onmouseout = function() {
				oPersoSelector.querySelector("#perso-info").style.display = "";
			};
		}
		if (customCharacters.length) {
			oPersoSelector.querySelector(".perso-selection-custom-explain").innerHTML = language
				? "Select here a character from the character editor. If the character hasn't been shared, it will appear as locked for other members."
				: "Sélectionnez ici un perso de l'éditeur de persos. Si le perso n'a pas été partagé, il apparaitra comme à débloquer pour les autres membres";
		}
		else {
			oPersoSelector.querySelector(".perso-selection-custom-explain").innerHTML = language
				? "You haven't created any character yet. Click <a href=\"persoEditor.php\" target=\"_blank\">here</a> to create some."
				: "Vous n'avez pas créé de perso. Cliquez <a href=\"persoEditor.php\" target=\"_blank\">ici</a> pour en créer.";
		}
	}
	if (customCharacters)
		appendCustomCharacters();
	else {
		o_xhr("myPlayablePersos.php", "", function(res) {
			try {
				customCharacters = JSON.parse(res);
			}
			catch (e) {
				return false;
			}
			appendCustomCharacters();
			return true;
		});
	}

	var $oPersoCollabForm = oPersoSelector.querySelector("#perso-selection-collab");
	oPersoSelector.querySelector(".perso-selection-collab-toggle a").onclick = function(e) {
		$oPersoCollabForm.className = $oPersoCollabForm.className ? "" : "shown";
		if ($oPersoCollabForm.className) {
			$oPersoCollabForm.elements["collab-link"].focus();
			var $persoCollabExplain = $oPersoCollabForm.querySelector("#perso-selection-collab > label a");
			if (!$persoCollabExplain.dataset.title) {
				$persoCollabExplain.dataset.title = '<div class="fancy-title-collab">'+ (language ? "Enter the characters's collaboration link here.<br />To get this link, the character owner will simply need to select the character in the editor and click on &quot;Collaborate&quot;" : "Saisissez ici le lien de collaboration du perso.<br />Pour obtenir ce lien, le propriétaire du perso devra simplement sélectionner le perso dans l'éditeur et cliquer sur &quot;Collaborer&quot;") +'</div>';
				initFancyTitle($persoCollabExplain);
			}
		}
	};
	$oPersoCollabForm.onsubmit = function(e) {
		e.preventDefault();
		var url = $oPersoCollabForm.elements["collab-link"].value;
		var creationId, creationKey;
		try {
			var urlParams = new URLSearchParams(new URL(url).search);
			creationId = urlParams.get('id');
			creationKey = urlParams.get('collab');
		}
		catch (e) {
		}
		if (!creationKey) {
			alert(language ? "Invalid URL" : "URL invalide");
			return;
		}
		var $submitBtn = $oPersoCollabForm.querySelector('button[type="submit"]');
		$submitBtn.disabled = true;
		o_xhr("importCollabPerso.php", "id="+creationId+"&collab="+creationKey, function(res) {
			$submitBtn.disabled = false;
			if (!res) {
				alert(language ? "Invalid link" : "Lien invalide");
				return true;
			}
			res = JSON.parse(res);
			doSelectPerso(res);

			return true;
		});
	};
}
function handleFormSubmit(e) {
	var $form = e.target;
	var optVal = $form.elements["opt"].value;
	if (optVal) {
		var key = Math.random().toString(16).substring(2);
		sessionStorage.setItem("cupopt."+key, optVal);
		var optValJson = JSON.parse(optVal);
		var slim = { "keyid":key };
		if (optValJson.persos) slim.persos = optValJson.persos;
		if (optValJson.gp) slim.gp = optValJson.gp;
		if (optValJson.customchars === 0) slim.customchars = 0;
		$form.elements["opt"].value = JSON.stringify(slim);
	}
}
function selectOptionTab(id) {
	document.querySelector(".option-tab-selected").classList.remove("option-tab-selected");
	document.querySelectorAll("#option-tabs > div")[id].classList.add("option-tab-selected");
	document.querySelector(".option-container-selected").classList.remove("option-container-selected");
	document.querySelectorAll("#option-containers > div")[id].classList.add("option-container-selected");
}
function showCollabImportPopup(e) {
	e.preventDefault();
	var $collabPopup = document.getElementById("collab-popup");
	$collabPopup.dataset.state = "open";

	closeCollabImportPopup = function() {
		document.removeEventListener("keydown", hideOnEscape);
		delete $collabPopup.dataset.state;
	}
	function hideOnEscape(e) {
		switch (e.keyCode) {
		case 27:
			closeCollabImportPopup();
		}
	}
	document.addEventListener("keydown", hideOnEscape);
	$collabPopup.querySelector('input[name="collablink"]').focus();
}
var closeCollabImportPopup;
function importCollabTrack(e) {
	e.preventDefault();
	var $form = e.target;
	var url = $form.elements["collablink"].value;
	var creationId, creationType, creationKey, creationMode;
	try {
		var urlParams = new URLSearchParams(new URL(url).search);
		if (isMCups) {
			creationType = "mkcups";
			creationId = urlParams.get('cid');
			creationMode = isBattle * 2 + complete;
		}
		else if (complete) {
			creationType = isBattle ? "arenes" : "circuits";
			creationId = urlParams.get('i');
		}
		else {
			creationType = "mkcircuits";
			creationId = urlParams.get('id');
		}
		creationKey = urlParams.get('collab');
	}
	catch (e) {
	}
	if (!creationKey) {
		alert(language ? "Invalid URL" : "URL invalide");
		return;
	}
	var $collabPopup = document.getElementById("collab-popup");
	$collabPopup.dataset.state = "loading";
	o_xhr("importCollabTrack.php", "type="+creationType+"&id="+creationId+"&collab="+creationKey+((creationMode!=null) ? ("&mode="+creationMode):""), function(res) {
		if (!res) {
			alert(language ? "Invalid link" : "Lien invalide");
			$collabPopup.dataset.state = "open";
			return true;
		}
		if (!document.getElementById("circuit"+creationId)) {
			var template = document.createElement('template');
			template.innerHTML = res.trim();
			var $tr = template.content.cloneNode(true).firstChild;
			var $table = document.getElementById("table-circuits");
			var $tbody = $table.querySelector("tbody");
			$tbody.insertBefore($tr, $tbody.firstChild);
			loadCircuitImgs();
		}

		closeCollabImportPopup();
		$form.reset();
		sessionStorage.setItem("collab.track."+creationType+"."+creationId+".key", creationKey);
		return true;
	});
}

document.addEventListener("DOMContentLoaded", initGUI);
