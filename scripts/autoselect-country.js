var xhr = new XMLHttpRequest();

xhr.onreadystatechange = function() {
	if (xhr.readyState == XMLHttpRequest.DONE) {
		if (xhr.status == 200) {
			var countryData = JSON.parse(xhr.responseText);
			if (typeof(countryData) == "object" && countryData.countryCode)
				document.forms[0].country.value = countryData.countryCode.toLowerCase();
		}
	}
};

xhr.open("GET", "findCountryByIp.php", true);
xhr.send();