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
		},
		onSelect: function() {
			try {
				var input = document.querySelector(selector);
				input.form.submit();
				input.blur();
			}
			catch (e) {
			}
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
	event.preventDefault();
}