function autocompletePlayer(selector, options) {
	var params = {
		selector: selector,
		minChars: 1,
		source: function(term, suggest) {
			var cHandler = ++autoHandler;
			o_xhr('matchingPlayers.php', 'prefix='+encodeURIComponent(term), function(res) {
				if (cHandler == autoHandler)
					suggest(JSON.parse(res));
				return true;
			});
		}
	};
	if (options) {
		for (var key in options)
			params[key] = options[key];
	}
	var autoHandler = 0;
	new autoComplete(params);
}
function preventSubmit(event) {
	var form = event.target.form;
	form.setAttribute("novalidate", true);
	var lastEvent = form.onsubmit;
	form.onsubmit = function() {
		this.onsubmit = lastEvent;
		form.removeAttribute("novalidate");
		return false;
	};
	setTimeout(function() {
		form.onsubmit = lastEvent;
		form.removeAttribute("novalidate");
	}, 1);
}