// Background-tab alerts. alert() is deferred by browsers until the tab regains focus, so
// anything time-sensitive has to go through the Notification API, with a flashing tab title
// as the fallback when permission was denied.
var mkNotify = (function() {
	var FLASH_INTERVAL = 1000;

	var flashTimer = null;
	var flashDoc = null;
	var flashBaseTitle = null;
	var flashText = "";
	var flashShown = false;
	var liveNotification = null;
	var watching = false;

	function topDocument() {
		try {
			var doc = window.top.document;
			if (doc && typeof doc.title === "string")
				return doc;
		} catch (e) {}
		return document;
	}

	function supported() {
		return (typeof Notification !== "undefined");
	}

	function permission() {
		return supported() ? Notification.permission : "unsupported";
	}

	function request(cb) {
		if (!supported() || Notification.permission !== "default") {
			if (cb) cb(permission());
			return;
		}
		var done = false;
		function settle() {
			if (done) return;
			done = true;
			if (cb) cb(permission());
		}
		try {
			var res = Notification.requestPermission(settle);
			if (res && res.then) res.then(settle, settle);
		}
		catch (e) { settle(); }
	}

	function away() {
		var doc = topDocument();
		if (doc.hidden) return true;
		return (typeof doc.hasFocus === "function") && !doc.hasFocus();
	}

	function playSound(src, volume) {
		try {
			var audio = new Audio(src);
			if (volume != null) audio.volume = volume;
			var played = audio.play();
			if (played && played["catch"]) played["catch"](function() {});
			return audio;
		}
		catch (e) { return null; }
	}

	function flashTick() {
		flashShown = !flashShown;
		flashDoc.title = flashShown ? flashText : flashBaseTitle;
	}

	function startFlash(text) {
		stopFlash();
		flashDoc = topDocument();
		flashBaseTitle = flashDoc.title;
		flashText = text;
		flashShown = false;
		flashTick();
		flashTimer = setInterval(flashTick, FLASH_INTERVAL);
	}

	function stopFlash() {
		if (flashTimer) {
			clearInterval(flashTimer);
			flashTimer = null;
		}
		if (flashDoc && flashBaseTitle !== null)
			flashDoc.title = flashBaseTitle;
		flashDoc = null;
		flashBaseTitle = null;
	}

	function clear() {
		stopFlash();
		if (liveNotification) {
			try { liveNotification.close(); } catch (e) {}
			liveNotification = null;
		}
	}

	function onBack() {
		if (!away()) clear();
	}

	function watch() {
		if (watching) return;
		watching = true;
		var doc = topDocument();
		var win = doc.defaultView || window;
		doc.addEventListener("visibilitychange", onBack);
		win.addEventListener("focus", onBack);
	}

	function fire(opts) {
		opts = opts || {};
		if (opts.sound)
			playSound(opts.sound, opts.volume);
		if (!away()) return false;
		watch();
		if (opts.flash)
			startFlash(opts.flash);
		if (permission() !== "granted") return false;
		try {
			liveNotification = new Notification(opts.title, {
				body: opts.body || "",
				tag: opts.tag || "mkpc",
				icon: opts.icon || "images/mkpc_box.png",
				renotify: !!opts.tag,
				silent: true
			});
			liveNotification.onclick = function() {
				try { window.top.focus(); } catch (e) { window.focus(); }
				clear();
			};
		}
		catch (e) { return false; }
		return true;
	}

	return {
		supported: supported,
		permission: permission,
		request: request,
		away: away,
		playSound: playSound,
		fire: fire,
		clear: clear
	};
})();
