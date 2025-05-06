function autocompletePlayer(selector, options) {
	var params = {
		selector: selector,
		minChars: 1,
		source: function(term, suggest) {
			var baseUrl = window.location.protocol + "//" + window.location.host;
			var profileUrl = baseUrl + "/profil.php?id="
			var cHandler = ++autoHandler;
			if (term.startsWith(baseUrl)) {
				var profileId = term.substring(profileUrl.length);
				if (+profileId) {
					o_xhr('getUserById.php', 'id='+profileId, function(res) {
						if (cHandler == autoHandler) {
							var data = JSON.parse(res);
							if (data)
								suggest([data.name]);
						}
						return true;
					});
					return;
				}
			}
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
	if (options) Object.assign(params, options);
	var autoHandler = 0;
	new autoComplete(params);
}
function preventSubmit(event) {
	event.preventDefault();
}