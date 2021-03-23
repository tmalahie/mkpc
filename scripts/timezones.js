var tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
if (tz && (tz != last_tz)) {
	var wrongTzs = ["CET", "CEST", "PST8PDT", "Etc/Unknown", "Asia/Dubai", "America/Sao_Paulo", "Asia/Riyadh", "Africa/Casablanca", "America/Fortaleza", "America/Belem", "Pacific/Majuro", "Asia/Tomsk", "Indian/Reunion", "Indian/Mauritius", "America/Cayenne", "Asia/Krasnoyarsk", "America/Lima", "Pacific/Port_Moresby", "Pacific/Noumea", "Asia/Qatar", "Asia/Srednekolymsk", "Asia/Katmandu", "Europe/Saratov", "Europe/Astrakhan"];
	if ((wrongTzs.indexOf(tz) === -1) && !tz.match(/^Etc\/GMT[+\-]\d+$/g)) {
		o_xhr('setTimezone.php', 'tz='+encodeURIComponent(tz), function(res) {
			return true;
		});
	}
}