var ACbinds = new Array();

/**
 * Checks if a text matches a search query.
 * @param {string} value
 * @param {string} search
 * @returns {boolean}
 */
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
 * Pulls special characters from a word and converts it to lowercase.
 * @param {String} word 
 * @returns {String}
 */
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

/**
 * Binds a Google-style autocomplete feature to a text box.
 * Displays a list of suggestions below the input field as the user types.
 *
 * @param {HTMLElement} input - The DOM object of the input tag.
 * @param {Array} values - An array containing the suggested searches.
 * @param {Object} params - Optional parameters for customization.
 *   - maxResults: Maximum number of suggestions to display (default: Infinity).
 *   - align: Position of the suggestion box ('top' or 'bottom', default: 'bottom').
 *   - onSelect: Callback when a suggestion is selected (default: no-op).
 *   - onCreateSuggestion: Function to customize suggestion display (default: basic <tr><td>).
 *   - matcher: Function to match input with suggestions (default: `match` function).
 */
function autocompletion(input, values, params={}) {
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
 * Updates the list of suggestion values for autocomplete.
 * @param {HTMLElement} input 
 * @param {Array} values
 */
function updateautocompletion(input,values) {
	for (var i=0;i<ACbinds.length;i++) {
		var ACbind = ACbinds[i];
		if (ACbind.input == input)
			autocompletion(input,values,ACbind.params);
	}
}