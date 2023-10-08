function sendTz() {
	if (!window.jstz) {
		setTimeout(sendTz, 100);
		return;
	}
	var tz = window.jstz.determine().name();
	var tz0 = Intl.DateTimeFormat().resolvedOptions().timeZone;
	if (tz && (tz != last_tz) && (tz0 != last_tz)) {
		o_xhr('setTimezone.php', 'tz='+encodeURIComponent(tz)+'&tz0='+encodeURIComponent(tz0), function(res) {
			return true;
		});
	}
}
sendTz();