var tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
if (tz && (tz != last_tz) && (tz != "CET") && (tz != "CEST") && (tz != "PST8PDT") && (tz != "Etc/Unknown")) {
	o_xhr('setTimezone.php', 'tz='+encodeURIComponent(tz), function(res) {
		return true;
	});
}