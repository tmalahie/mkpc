var aNote = cNote;
function previewMark(note) {
    for (i=1;i<=note;i++)
        document.getElementById("star"+ i).src = "images/star1.png";
    for (i=note+1;i<=5;i++)
        document.getElementById("star"+ i).src = "images/star0.png";
}
function updateMark() {
    previewMark(cNote);
}
function setMark(nNote) {
    cNote = (cNote != nNote) ? nNote:0;
    if (cNote != aNote) {
        document.getElementById("submitMark").disabled = false;
        document.getElementById("submitMark").className = "";
    }
    else {
        document.getElementById("submitMark").disabled = true;
        document.getElementById("submitMark").className = "cannotChange";
    }
    previewMark(cNote);
}
function sendMark() {
    document.getElementById("markMsg").innerHTML = language ? 'Sending...':'Envoi en cours...';
    document.getElementById("submitMark").disabled = true;
    document.getElementById("submitMark").className = "cannotChange";
    xhr("sendMark.php", ratingParams+"&rating="+cNote, function(reponse) {
        if (reponse == 1) {
            aNote = cNote;
            document.getElementById("markMsg").innerHTML = (aNote>0) ? (language ? 'Thanks for your vote':'Merci de votre vote') : (language ? 'Vote removed successfully':'Vote supprim&eacute; avec succ&egrave;s');
            return true;
        }
        return false;
    });
}