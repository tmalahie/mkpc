document.getElementById((
        window.navigator.userLanguage 
        || window.navigator.language 
        || "fr-FR"
    ).startsWith("fr") ? "error-fr" : "error-en"
).style.display = "block";