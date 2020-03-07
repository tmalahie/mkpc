/**
Vérifie si un texte correspond à une recherche

value : Le texte recherché
search : La recherche entrée
**/
function match(value,search) {
	var keyWords = search.split(" "), valueWords = value.split(" ");
	for (var i=0;i<keyWords.length;i++) {
		var found = false;
		for (var j=0;j<valueWords.length;j++) {
			if (pullSpecialChars(valueWords[j]).indexOf(pullSpecialChars(keyWords[i])) != -1) {
				found = true;
				break;
			}
		}
		if (!found)
			return false;
	}
	return true;
}
/**
Supprime les caractères spéciaux d'une chaine (idéal pour tester une égalité entre 2 chaînes)
**/
function pullSpecialChars(word) {
	return word.toLowerCase()
	.replace("à","a")
	.replace("â","a")
	.replace("ä","a")
	.replace("é","e")
	.replace("è","e")
	.replace("ê","e")
	.replace("ë","a")
	.replace("î","i")
	.replace("ï","i")
	.replace("ô","o")
	.replace("ö","o")
	.replace("ù","u")
	.replace("û","u")
	.replace("ü","u")
	.replace("ç","c")
	.replace("ñ","n");
}
var ACbinds = new Array();
/**
/**
Permet d'associer à une zone de texte (inout) une fonctionnalité d'autocomplétion "à la Google".
Lorsque l'utilisateur rentre qqch dans la zone de texte, une liste de suggestions associées lui est affichée en dessous.

input : L'objet DOM de la balise input
values : Un tableau contenant les recherches suggérées
- params (facultatif) : Un objet contenant les paramètres de l'autocomplétion
	- params.maxResults : Le nombre de suggestions maximum à afficher
	- params.align : La position de la zone de suggestions par rapport à la zone de texte.
	2 valeurs possible : top, bottom (défaut : bottom)
	- params.onSelect (facultatif) : La fonction appelée lors d'un choix de sélection. Elle prend 2 arguments :
		- l'ID du choix de la sélection
		- Le texte sélectionné
	- params.onCreateSuggestion : La fonction indiquant ce qui doit être affiché dans la liste de suggestions.
		Concrètement, Cette liste est un tableau (balise <table>), chaque suggestion est une ligne du tableau (balise <tr>)
		La fonction Prend en argument l'id et la valeur de la suggestion,
		Elle retourne une balise <tr> contenant une ou plusieurs balises <td> avec les éléments à afficher.
		Par défaut, cette fonction retourne '<tr><td>'+ value +'</td></tr>', où value est la valeur de la suggestion (2e argument de la fonction)
**/
function autocompletion(input,values, params) {
	if (!params)
		params = {};
	function addDefaultParam(attr,value) {
		if (params[attr] == undefined)
			params[attr] = value;
	}
	function nothing(){}
	addDefaultParam("align","bottom");
	addDefaultParam("onSelect",nothing);
	addDefaultParam("matcher",match);
	addDefaultParam("onCreateSuggestion",function(id,value) {
		return '<tr><td>'+ value +'</td></tr>';
	});
	addDefaultParam("maxResults",Infinity);
	for (var i=0;i<ACbinds.length;i++) {
		if (ACbinds[i].input == input) {
			ACbinds[i].elt.remove();
			ACbinds.splice(i,1);
			i--;
		}
	}
	$(input).attr("autocomplete","off");
	var tableSuggestions = $('<table class="tableSuggestions"></table>');
	$("body").append(tableSuggestions);
	function setHoverSuggestion(inc,tableSuggestion) {
		tableSuggestion.removeClass("suggestion");
		tableSuggestion.addClass("suggestion_selected");
		currentSelection = {
			"id" : inc,
			"elt" : tableSuggestion
		};
	}
	function updateHoverSuggestion(inc,tableSuggestion) {
		if (currentSelection)
			setOutSuggestion(currentSelection.id,currentSelection.elt);
		setHoverSuggestion(inc,tableSuggestion);
	}
	function setOutSuggestion(inc,tableSuggestion) {
		tableSuggestion.removeClass("suggestion_selected");
		tableSuggestion.addClass("suggestion");
	}
	var currentSelection, suggestionsAdded;
	var submitting = false, submitted = false;
	var oneSuggestion = false;
	function addSuggestion(inc,id) {
		var tableSuggestion = $(params.onCreateSuggestion(id,values[id]));
		tableSuggestion.addClass("suggestion");
		tableSuggestion.bind("mouseover", function() {
			updateHoverSuggestion(inc,tableSuggestion);
		});
		tableSuggestion.bind("mousedown", function() {
			submitting = true;
		});
		tableSuggestion.bind("click", function() {
			submitSelection(id);
		});
		tableSuggestion.css("font-size", $(input).css("font-size"));
		tableSuggestion.css("font-weight", $(input).css("font-weight"));
		tableSuggestion.css("font-family", $(input).css("font-family"));
		tableSuggestions.append(tableSuggestion);
		return tableSuggestion;
	}
	function submitSelection(id) {
		$(input).val(values[id]);
		tableSuggestions.css("display", "none");
		oneSuggestion = false;
		params.onSelect(id,values[id]);
	}
	function updateSuggestions() {
		var offset = $(input).offset();
		var oLeft = offset.left - $(document).scrollLeft();
		var oTop = offset.top - $(document).scrollTop();
		var pLeft = parseInt($(input).css("padding-left")), pTop = parseInt($(input).css("padding-top")), pRight = parseInt($(input).css("padding-right")), pBottom = parseInt($(input).css("padding-bottom"));
		var oWidth = $(input).width()+4 + pLeft+pRight;
		var oHeight = $(input).height()+4 + pTop+pBottom;
		tableSuggestions.css({
			"position" : "fixed",
			"z-index" : 5,
			"width" : oWidth +"px",
			"height" : oHeight +"px",
			"display" : "none"
		});
		if (params.align == "bottom") {
			tableSuggestions.css({
				"left" : (oLeft-pLeft+2) +"px",
				"top" : (oTop+oHeight+2) +"px"
			});
		}
		else {
			tableSuggestions.css({
				"left" : (oLeft-pLeft+2) +"px",
				"bottom" : (oTop-2) +"px"
			});
		}
		tableSuggestions.html("");
		var value = $(input).val();
		oneSuggestion = false;
		var inc = 0;
		suggestionsAdded = new Array();
		if (value.length) {
			for (var i=0;(i<values.length)&&(inc<params.maxResults);i++) {
				if (params.matcher(values[i],value)) {
					suggestionsAdded.push({
						"id" : i,
						"elt" : addSuggestion(inc,i)
					});
					inc++;
					oneSuggestion = true;
				}
			}
		}
		if (currentSelection && suggestionsAdded[currentSelection.id])
			setHoverSuggestion(currentSelection.id,suggestionsAdded[currentSelection.id].elt);
		else
			currentSelection = undefined;
		if (oneSuggestion)
			tableSuggestions.css("display", "");
		else
			tableSuggestions.css("display", "none");
	}
	$(input).unbind("keyup");
	$(input).bind("keyup",function() {
		if (submitted) {
			submitted = false;
			return;
		}
		updateSuggestions();
	});
	$(input).unbind("click");
	$(input).bind("click", function() {
		updateSuggestions();
	});
	$(input).unbind("blur");
	$(input).bind("blur", function() {
		if (submitting) {
			$(input).focus();
			submitting = false;
			return;
		}
		tableSuggestions.css("display", "none");
		oneSuggestion = false;
	});
	$(input).unbind("keydown");
	$(input).bind("keydown", function(e) {
		if (oneSuggestion) {
			switch (e.keyCode) {
			case 38 : // UP
				var nID;
				if (currentSelection) {
					if (nID != 0)
						nID = currentSelection.id-1;
					else
						nID = suggestionsAdded.length-1;
				}
				else
					nID = suggestionsAdded.length-1;
				updateHoverSuggestion(nID,suggestionsAdded[nID].elt);
				return false;
			case 40 : // DOWN
				var nID;
				if (currentSelection) {
					nID = currentSelection.id+1;
					if (nID == suggestionsAdded.length)
						nID = 0;
				}
				else
					nID = 0;
				updateHoverSuggestion(nID,suggestionsAdded[nID].elt);
				return false;
			case 13 : // Enter
				submitSelection(suggestionsAdded[currentSelection.id].id);
				submitted = true;
				return false;
			}
		}
	});
	ACbinds.push({
		"input" : input,
		"elt" : tableSuggestions,
		"values" : values,
		"params" : params
	});
}
/**
Modifie la liste des valeurs de suggestions pour l'auto-complétion
input : L'objet DOM de la balise input
values : Les nouvelles valeurs
**/
function updateautocompletion(input,values) {
	for (var i=0;i<ACbinds.length;i++) {
		var ACbind = ACbinds[i];
		if (ACbind.input == input)
			autocompletion(input,values,ACbind.params);
	}
}