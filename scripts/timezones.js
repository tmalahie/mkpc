var tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
if (tz && (tz != last_tz)) {
	o_xhr('setTimezone.php', 'tz='+encodeURIComponent(tz), function(res) {
		return true;
	});
}