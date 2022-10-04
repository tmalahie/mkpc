var selectedCircuits = [];
var loadingMsg = language ? "Loading":"Chargement";
var isMCups = (ckey === "mid");
function getSubmitMsg() {
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
	return (selectedCircuits.length != 4) ? (language ? "You must select 4 circuits":"Vous devez sélectionner 4 circuits"):"";
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
		if (isMCups) {
			var $cupOptions = document.getElementById("cup-options");
			if ($cupOptions.value) {
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
						if (cupOptions.customchars === 0)
							document.getElementById("customchars").checked = false;
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
	if (id == 1) {
		updateCupImgGUI();
		updateCupPersosGUI();
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
var actualIcons, actualLines;
var allCups = ["champi", "etoile", "carapace", "carapacebleue", "speciale", "carapacerouge", "banane", "feuille", "megachampi", "eclair", "upchampi", "fireflower", "bobomb", "minichampi", "egg", "iceflower", "plume", "cloudchampi"];
var cupPage = 0;
var cupPages = [];
function resetCupOptions(full) {
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

		var cupOptions = {};
		if (cupIcons.length)
			cupOptions.icons = actualIcons;
		if (cupLines.length)
			cupOptions.lines = actualLines;
		if (cupPages.length) {
			cupOptions.pages = cupPages;
			cupOptions.lines = actualLines;
		}
		if (persoList) {
			cupOptions.persos = persoList.map(function(data) {
				if (data.id)
					return data.id;
				return data.sprites;
			});
		}
		if (!document.getElementById("customchars").checked)
			cupOptions.customchars = 0;
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
	var nbPersos = Object.keys(cp).length;
	var $characterRoster = document.getElementById("character-roster");
	$characterRoster.innerHTML = "";
	nbPersos = Math.min(nbPersos, pList.length+1);
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
							+ '<div class="perso-selection-standard"></div>'
								+ '<h4>'+ (language ? "Basic characters":"Persos de base") +'</h4>'
								+ '<div class="perso-selection-choices" id="perso-selection-standard-choices"></div>'
							+ '<div id="perso-selection-custom" style="display:none">'
								+ '<h4>'+ (language ? "Custom characters":"Persos custom") +'</h4>'
								+ '<div class="perso-selection-custom-explain">'+ (language
									? "Select here a character from the character editor. If the character hasn't been shared, he will appear as locked for other members."
									: "Sélectionnez ici un perso de l'éditeur de persos. Si le perso n'a pas été partagé, il apparaitra comme à débloquer pour les autres membres"
								) +'</div>'
								+ '<div id="perso-info">'
									+'<div>'
										+'<div id="perso-info-name">Mario</div>'
										+'<div class="perso-info-share" id="perso-info-shared">✓ '+ (language ? "Shared character":"Perso partagé") +'</div>'
										+'<div class="perso-info-share" id="perso-info-unshared">🔒 '+ (language ? "Non-shared character":"Perso non partagé") +'</div>'
									+'</div>'
								+ '</div>'
								+ '<div class="perso-selection-choices" id="perso-selection-custom-choices"></div>'
							+ '</div>';
	oPersoSelector.onclick = function(e) {
		e.stopPropagation();
	}
	$mask.appendChild(oPersoSelector);
	var $persoSelectionChoices = document.getElementById("perso-selection-standard-choices");
	function appendPersoChoice($div, src, data) {
		var oDiv = document.createElement("div");
		var oDiv2 = document.createElement("div");
		var oImg = document.createElement("img");
		oImg.src = src;
		oImg.onclick = function() {
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
		};
		oDiv2.appendChild(oImg);
		oDiv.appendChild(oDiv2);
		$div.appendChild(oDiv);
		return oDiv;
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
		if (customCharacters.length)
			document.getElementById("perso-selection-custom").style.display = "";
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
}
function handleFormSubmit(e) {
	var $form = e.target;
	var optVal = $form.elements["opt"].value;
	if (optVal) {
		var key = Math.random().toString(16).substring(2);
		sessionStorage.setItem("cupopt."+key, optVal);
		var optValJson = JSON.parse(optVal);
		$form.elements["opt"].value = JSON.stringify({ "keyid":key, persos: optValJson.persos });
	}
}
function showCustomCharToggleHelp() {
	alert(language ? "If unchecked, you don't give access to the character editor in the character selector screen. Only the characters in the list above can be selected." : "Si décoché, vous ne donnez pas accès à l'éditeur de persos dans l'écran de sélection du perso. Seuls les persos de la liste ci-dessus pourront être sélectionnés.");
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
}
var closeCollabImportPopup;
function importCollabTrack(e) {
	e.preventDefault();
	var $form = e.target;
	var url = $form.elements["collablink"].value;
	var creationId, creationType, creationKey;
	try {
		var urlParams = new URLSearchParams(new URL(url).search);
		if (isMCups) {
			creationType = "mkcups";
			creationId = urlParams.get('cid');
		}
		else if (complete) {
			creationType = "circuits";
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
		alert("Invalid URL");
		return;
	}
	var $collabPopup = document.getElementById("collab-popup");
	$collabPopup.dataset.state = "loading";
	o_xhr("importCollabTrack.php", "type="+creationType+"&id="+creationId+"&collab="+creationKey+(isMCups ? ("&mode="+complete):""), function(res) {
		if (!res) {
			alert("Invalid link");
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
		}

		closeCollabImportPopup();
		$form.reset();
		sessionStorage.setItem("collab.track."+creationType+"."+creationId+".key", creationKey);
		return true;
	});
}

document.addEventListener("DOMContentLoaded", initGUI);