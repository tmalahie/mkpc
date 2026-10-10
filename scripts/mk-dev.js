/*
 * Dev-only tools for the game runtime (scripts/mk.js), for the console and Playwright.
 *
 * php/includes/mk/main.php loads this file, and sets MK_DEV = true, on every host
 * except production. Nothing here ships: the production bundle is minified with
 * --define MK_DEV=false, which drops every guarded hook in mk.js.
 *
 * Reading and changing the game state
 *   The game state (aKarts, oPlayers, oMap, course...) lives inside function MarioKart(),
 *   out of reach of window. mkDevEval(code) evaluates code inside the current MarioKart()
 *   call, so mkDevEval("aKarts") returns the live array and writing to it changes the game.
 *   mk.js re-attaches mkDevEval on every MarioKart() call, so it always targets the current game.
 *   From Playwright, return plain values: page.evaluate serializes its result, and a live kart
 *   holds DOM nodes and sprites. mkDev.karts() is the serializable snapshot.
 *
 * Race events
 *   mkDevEvents records the race flow as {t, type, ...data}, also printed with console.debug:
 *     screen          name: main, characterSelect, cupSelect, trackSelect, lobby, trackVote,
 *                     loading, countdown, race, results
 *     raceStart       race, map, replay
 *     raceEnd         race, online (and ranking, the player ids in order, online)
 *     spectatorOn/Off id
 *     afkSpectator    the automatic switch to spectator after a race without input
 *     chooseResponse  selected, track, players, rules (each online track-vote poll)
 *     disconnected, redirect (reason)
 *   Wait on them with page.waitForFunction(() => mkDevEvents.some(e => e.type === "raceStart" && e.race === 2)).
 *   tests/mk-dev.spec.ts shows the patterns.
 *
 * Helpers (window.mkDev)
 *   Cheats and race helpers act on the local player (oPlayers[0]), like the in-game cheat console,
 *   but also work where it is disabled (GP, time trial, online) and don't void challenges.
 *   They throw when the game refuses, instead of returning an error string.
 *
 * Adding tools
 *   Add a helper here whenever a test or a debugging session needs one: a new reader,
 *   a shortcut through a menu, a way to force a game state. Keep mk.js changes to one-line
 *   hooks guarded by `typeof MK_DEV !== "undefined" && MK_DEV`, and put the logic in this file.
 *   A helper that needs MarioKart's variables can be written as a normal function and compiled
 *   in the game's scope with inGame(fn), like startRace below.
 */

var mkDevAttach = "window.mkDevEval = function(code) { return eval(code); };";

window.mkDevEvents = [];
function mkDevLog(type, data) {
	var event = {t: Date.now(), type: type};
	for (var key in data)
		event[key] = data[key];
	mkDevEvents.push(event);
	console.debug("[mkDev]", type, event);
}

// Compiles fn inside the current MarioKart() scope, so its body can use the game's variables
// and functions. fn must not use anything from this file's scope.
function inGame(fn) {
	return mkDevEval("(" + fn + ")");
}

function goToFinishLine() {
	var finishLine = inGame(function() {
		var oPlayer = oPlayers[0];
		return getCurrentLMap(getCurrentLapId(oPlayer)).checkpointCoords[getNextCp(oPlayer)].O;
	})();
	cheatOrThrow("/tp " + finishLine[0] + " " + finishLine[1] + " cur");
}

function cheatOrThrow(code) {
	var result = mkDev.cheat(code);
	if (result !== true)
		throw new Error("mkDev: " + (typeof result === "string" ? result : code + " failed"));
}

window.mkDev = {
	// Serializable snapshot of every kart.
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
	// The live kart object: writes change the game. Some fields are derived every frame
	// (place) or drive the HUD (tours, arme): prefer the helpers below for those.
	kart: function(id) {
		return mkDevEval("aKarts").find(function(oKart) {
			return oKart.id === id;
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
	},

	// Runs an in-game cheat console command (see processCode in mk.js) on the local player,
	// e.g. "/give champi", "/tp 100 200 90", "/lap 2 3", "/size xl", "/balloon 2".
	// Returns true, or the game's error message.
	cheat: function(code) {
		return mkDevEval("processCode")(code);
	},
	// item: an item of the current distribution ("champi", "banane", "carapace"...).
	// slot 1 replaces the current item, otherwise it goes to the free slot.
	giveItem: function(item, slot) {
		cheatOrThrow("/give " + item + (slot ? " " + slot : ""));
	},
	// lap starts at 1. checkpoint is 1-based and defaults to the lap's last checkpoint, so the
	// player completes that lap at the next finish line crossing. Moves the player to it.
	setLap: function(lap, checkpoint) {
		cheatOrThrow("/lap " + lap + (checkpoint ? " " + checkpoint : ""));
	},
	teleport: function(x, y, rotation) {
		cheatOrThrow("/tp " + x + " " + y + (rotation != null ? " " + rotation : ""));
	},
	// Puts the player on its last checkpoint and onto the finish line: the race (or the player's
	// race, online) ends on the next frame. Not for battle mode.
	// Online, the server takes at most one more lap per update (reload.php), so a jump to the
	// last lap is ignored there: finish lap by lap with finishLap() instead.
	finishRace: function() {
		cheatOrThrow("/lap");
		goToFinishLine();
	},
	// Completes the current lap at the next frame, the way finishRace() completes the race.
	finishLap: function() {
		cheatOrThrow("/lap " + mkDevEval("oPlayers[0].tours"));
		goToFinishLine();
	},

	// Skips the menus and starts a local race, as if picked from the menus. Call it from a menu
	// screen, e.g. right after page.goto("/mariokart.php"). Options, all optional:
	//   mode        "VS" (default), "GP", "CM" (time trial) or "BB" (battle)
	//   character   the player's character, default "mario"
	//   map         n for oMaps["map"+n]: on mariokart.php 1-56 are tracks and 57-68 arenas.
	//               Defaults to the first track, or the first arena in battle. Ignored on a
	//               single-track page.
	//   cc          50, 100, 150 (default), 200...; mirror: true for mirror mode
	//   players     total number of karts in VS and battle (default 8 and 4); GP always uses 8
	//   opponents   CPU characters, instead of a random roster
	//   difficulty  CPU level from the menu: 0 easy, 1 medium, 2 hard (default)
	//   doubleItems false to disable double items
	startRace: function(options) {
		options = options || {};
		if (mkDevEval("isOnline"))
			throw new Error("mkDev.startRace: online races start from matchmaking");
		if (mkDevEval("aKarts.length"))
			throw new Error("mkDev.startRace: call it from a menu screen, e.g. after reloading the page");
		inGame(function(options) {
			for (var i=0;i<oContainers.length;i++) {
				if (oContainers[i].parentNode)
					oContainers[i].parentNode.removeChild(oContainers[i]);
			}
			course = options.mode || "VS";
			strPlayer = [options.character || "mario"];
			var map = options.map;
			if (map == null) {
				var mapKey = aAvailableMaps.find(function(key) {
					return (course == "BB") == !oMaps[key].checkpoint;
				}) || aAvailableMaps[0];
				map = oMaps[mapKey].ref;
			}
			fInfos = {
				player: strPlayer,
				map: map,
				cc: getRelSpeedFromCc(options.cc || 150),
				mirror: !!options.mirror,
				double_items: options.doubleItems !== false,
				difficulty: 4 + (options.difficulty != null ? options.difficulty : 2)*0.5,
				nbPlayers: options.players || (course == "BB" ? 4 : 8),
				ptsdistrib: ptDistributions[0],
				perso: []
			};
			selectedTeams = 0;
			if (course == "CM")
				aPlayers = [];
			else if (options.opponents)
				aPlayers = options.opponents.slice();
			else
				buildCpuRoster();
			aTeams = [];
			aPlaces = [];
			resetScores();
			pause = true;
			MarioKart();
		})(options);
	}
};
