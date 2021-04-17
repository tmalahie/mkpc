var selectedCircuits = [];
var loadingMsg = language ? "Loading":"Chargement";
var isMCups = (ckey === "mid");
function getSubmitMsg() {
	if (isMCups) {
		if (selectedCircuits.length < 2)
			return (language ? "You must select at least 2 cups":"Vous devez sélectionner au moins 2 coupes");
		if (selectedCircuits.length > allCups.length)
			return (language ? "You can select at most 18 cups":"Vous pouvez sélectionner 18 coupes au maximum");
		if (actualLines.length > 4)
			return (language ? "Please define at most 4 lines of cup":"Veuillez définir au plus 4 lignes de coupe");
		for (var i=0;i<actualLines.length;i++) {
			if (actualLines[i] > 8)
				return (language ? "Please define at most 8 cups per line":"Veuillez définir au plus 8 coupes par ligne");
		}
		return "";
	}
	return (selectedCircuits.length != 4) ? (language ? ("You must select 4 "+(isBattle ? "arenas":"circuits")):("Vous devez sélectionner 4 "+(isBattle ? "arènes":"circuits"))):"";
}
function selectCircuit(tr, isAuto) {
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
		resetCupOptions();
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
		if (isMCups) {
			var $cupOptions = document.getElementById("cup-options");
			if ($cupOptions.value) {
				try {
					var cupOptions = JSON.parse($cupOptions.value);
					if (cupOptions) {
						if (cupOptions.icons) cupIcons = cupOptions.icons;
						if (cupOptions.lines) cupLines = cupOptions.lines;
					}
				}
				catch (e) {
				}
			}
		}
	}
	resetCupOptions();
	updateGUI();
	var $prettyTitles = document.querySelectorAll(".pretty-title");
	for (var i=0;i<$prettyTitles.length;i++) {
		(function($prettyTitle) {
			var $fancyTitle = document.createElement("div");
			$fancyTitle.className = "fancy-title";
			if (!$prettyTitle.dataset) $prettyTitle.dataset = {};
			$prettyTitle.dataset.title = "";
			$prettyTitle.parentNode.onmouseover = function() {
				if ($prettyTitle.dataset.title) {
					$fancyTitle.innerHTML = $prettyTitle.dataset.title;
					$fancyTitle.style.display = "block";
					var rect = $prettyTitle.getBoundingClientRect();
					$fancyTitle.style.left = Math.max(0,(rect.left + ($prettyTitle.scrollWidth-$fancyTitle.scrollWidth)/2))+"px";
					$fancyTitle.style.top = (rect.top - $prettyTitle.scrollHeight)+"px";
				}
			};
			$prettyTitle.parentNode.onmouseout = function() {
				$fancyTitle.style.display = "none";
			};
			document.body.appendChild($fancyTitle);
		})($prettyTitles[i]);
	}
}
function showEditorContent(id) {
	if (id == 1)
		updateCupImgGUI();
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
var actualIcons, actualLines;
var allCups = ["champi", "etoile", "carapace", "carapacebleue", "speciale", "carapacerouge", "banane", "feuille", "megachampi", "eclair", "upchampi", "fireflower", "bobomb", "minichampi", "egg", "iceflower", "plume", "cloudchampi"];
function resetCupOptions() {
	if (isMCups) {
		var nbCups = Math.min(selectedCircuits.length,allCups.length);
		var cups_per_line = 6;
		var nb_lines = Math.ceil(nbCups/cups_per_line);
		cups_per_line = Math.ceil(nbCups/nb_lines);
		actualIcons = [];
		for (var i=0;i<cupIcons.length;i++)
			actualIcons[i] = cupIcons[i];
		var cup = 0;
		while (actualIcons.length < nbCups) {
			for (;cup<allCups.length;cup++) {
				if (actualIcons.indexOf(cup) == -1) {
					actualIcons.push(cup);
					break;
				}
			}
			if (cup >= allCups.length) break;
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

		var cupOptions = {};
		if (cupIcons.length)
			cupOptions.icons = actualIcons;
		if (cupLines.length)
			cupOptions.lines = actualLines;
		var cupOptionsJSON = JSON.stringify(cupOptions);
		if (cupOptionsJSON === "{}")
			cupOptionsJSON = "";
		document.getElementById("cup-options").value = cupOptionsJSON;
	}
}
function resetCupAppearance() {
	if (confirm(language ? "Reset cup appearance to default?":"Rétablir l'apparence par défaut ?")) {
		cupLines = [];
		cupIcons = [];
		updateCupImgGUI();
	}
}
function updateCupImgGUI() {
	resetCupOptions();
	var $cupAppearance = document.getElementById("cup-appearance");
	$cupAppearance.innerHTML = "";
	nbRowsInLine = 0;
	var currentLine = 0;
	for (var i=0;i<actualIcons.length;i++) {
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
		if (i < (actualIcons.length-1)) {
			var $cursor = document.createElement("div");
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
			nbRowsInLine++;
			if (nbRowsInLine >= actualLines[currentLine]) {
				$cupAppearance.appendChild(document.createElement("br"));
				nbRowsInLine = 0;
				currentLine++;
			}
			if (!$cursor.dataset) $cursor.dataset = {};
			$cursor.dataset.line = currentLine;
			$cursor.dataset.row = nbRowsInLine;
		}
	}
	document.getElementById("reset-cup-appearance").style.display = ((cupLines.length || cupIcons.length)) ? "":"none";
	updateSubmitMsg();
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
			var oldCup = cupIcons.indexOf(id);
			if (oldCup != -1)
				cupIcons[oldCup] = cupIcons[cup];
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
	function closeMask() {
		document.removeEventListener("keydown", hideOnEscape);
		document.body.removeChild($mask);
		$editingCursor.className = "cup-appearance-cursor";
		clearInterval(blinkToggle);
	}
	function addNewLine() {
		cupLines = actualLines;
		cupLines[line] -= row;
		cupLines.splice(line, 0, row);
		cupLines[line] = row;
		closeMask();
		updateCupImgGUI();
	}
	function rmNewLine() {
		cupLines = actualLines;
		cupLines[line-1] += cupLines[line];
		cupLines.splice(line, 1);
		closeMask();
		updateCupImgGUI();
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
	oContextMenu.style.position = "absoulte";
	oContextMenu.className = "editor-mask-contextmenu";
	var oContextMenuItem = document.createElement("div");
	oContextMenuItem.innerHTML = language ? "Cancel" : "Annuler";
	oContextMenuItem.onclick = function() {
		closeMask();
	}
	oContextMenuItem.oncontextmenu = function() {
		closeMask();
		return false;
	}
	oContextMenu.appendChild(oContextMenuItem);
	var oContextMenuItem = document.createElement("div");
	if (eol) {
		oContextMenuItem.innerHTML = language ? "<strong>Remove line</strong>" : "<strong>Supprimer ligne</strong>";
		oContextMenuItem.onclick = function() {
			rmNewLine();
		}
		oContextMenuItem.oncontextmenu = function() {
			rmNewLine();
			return false;
		}
	}
	else {
		oContextMenuItem.innerHTML = language ? "<strong>New line here</strong>" : "<strong>Nouvelle ligne ici</strong>";
		oContextMenuItem.onclick = function() {
			addNewLine();
		}
		oContextMenuItem.oncontextmenu = function() {
			addNewLine();
			return false;
		}
	}
	oContextMenu.appendChild(oContextMenuItem);
	oContextMenu.onclick = function(e) {
		e.stopPropagation();
	};
	oContextMenu.appendChild(oContextMenuItem);
	oContextMenu.style.visibility = "hidden";
	$mask.appendChild(oContextMenu);
	oContextMenu.style.left = Math.min(e.clientX, (window.innerWidth||screen.width)-oContextMenu.scrollWidth) +"px";
	oContextMenu.style.top = (e.clientY-oContextMenu.scrollHeight) +"px";
	oContextMenu.style.visibility = "";
	document.addEventListener("keydown", hideOnEscape);
	$mask.onclick = closeMask;
}