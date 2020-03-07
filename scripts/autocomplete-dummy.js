function autocompleteDummy(selector,values, options) {
	var valuesLowered = [];
	valuesLowered.length = values.length;
	for (var i=0;i<values.length;i++)
		valuesLowered[i] = values[i].toLowerCase();
	var params = {
		selector: "#joueur",
		minChars: 1,
		source: function(term, suggest) {
			var res = [];
			var termLowered = term.toLowerCase();
			for (var i=0;i<valuesLowered.length;i++) {
				var value = valuesLowered[i];
				if (value.indexOf(termLowered) == 0) {
					res.push(values[i]);
					if (res.length >= 10)
						break;
				}
			}
			suggest(res);
		}
	};
	if (options) {
		for (var key in options)
			params[key] = options[key];
	}
	new autoComplete(params);
}