if (typeof window.cNote !== 'undefined') {
    window.aNote = window.cNote;
}
function previewMark(note) {
    for (let i = 1; i <= 5; i++) {
        const star = document.getElementById("star" + i);
        star.src = `images/star${+(i <= note)}.png`;
    }
}
function updateMark() {
    previewMark(window.cNote);
}
function setMark(nNote) {
    window.cNote = (window.cNote != nNote) ? nNote : 0;
    if (window.cNote != aNote) {
        document.getElementById("submitMark").disabled = false;
        document.getElementById("submitMark").className = "";
    }
    else {
        document.getElementById("submitMark").disabled = true;
        document.getElementById("submitMark").className = "cannotChange";
    }
    previewMark(window.cNote);
}
function sendMark() {
    document.getElementById("markMsg").innerHTML = language ? 'Sending...' : 'Envoi en cours...';
    document.getElementById("submitMark").disabled = true;
    document.getElementById("submitMark").className = "cannotChange";
    xhr("sendMark.php", ratingParams+`&rating=${window.cNote}`, function(response) {
        if (response == 1) {
            aNote = window.cNote;
            document.getElementById("markMsg").innerHTML = (aNote > 0) ? (language ? 'Thanks for your vote':'Merci de votre vote') : (language ? 'Vote removed successfully':'Vote supprim&eacute; avec succ&egrave;s');
            return true;
        }
        return false;
    });
}