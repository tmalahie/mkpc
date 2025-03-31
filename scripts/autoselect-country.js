fetch("api/findCountryByIp.php")
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(countryData => {
        if (countryData?.countryCode) {
            document.forms[0].country.value = countryData.countryCode.toLowerCase();
        }
    })
    .catch();