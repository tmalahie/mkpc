var language = window.navigator.userLanguage || window.navigator.language || "fr-FR";
var isFR = language.startsWith("fr");
document.getElementById(isFR ? "error-fr":"error-en").style.display = "block";