var mkDevAttach = "window.mkDevEval = function(code) { return eval(code); };";

window.mkDevEvents = [];
function mkDevLog(type, data) {
	var event = {t: Date.now(), type: type};
	for (var key in data)
		event[key] = data[key];
	mkDevEvents.push(event);
	console.debug("[mkDev]", type, event);
}

window.mkDev = {
	karts: function() {
		var aKarts = mkDevEval("aKarts") || [];
		return aKarts.map(function(oKart) {
			return {
				id: oKart.id,
				nick: oKart.nick,
				character: oKart.personnage,
				controller: oKart.controller,
				cpu: !!oKart.cpu,
				team: oKart.team,
				place: oKart.place,
				lap: oKart.tours
			};
		});
	},
	spectatorId: function() {
		return mkDevEval("onlineSpectatorId");
	},
	raceCount: function() {
		return mkDevEval("iRaceCount");
	},
	events: function(type) {
		return mkDevEvents.filter(function(event) {
			return event.type === type;
		});
	},
	lastEvent: function(type) {
		var events = this.events(type);
		return events[events.length-1];
	}
};
