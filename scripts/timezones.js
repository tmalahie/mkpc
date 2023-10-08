var tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
if (tz === 'CET') {
	var tzOffset = new Date().getTimezoneOffset();
	if (tzOffset === -120)
		tz = 'CEST';
}
if (tz && (tz != last_tz)) {
	o_xhr('setTimezone.php', 'tz='+encodeURIComponent(tz), function(res) {
		return true;
	});
}