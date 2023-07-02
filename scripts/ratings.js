if (typeof cNote !== 'undefined') {
    var aNote = cNote;
}
function previewMark(note) {
    for (i=1;i<=note;i++)
        document.getElementById("star"+ i).src = "images/star1.png";
    for (i=note+1;i<=5;i++)
        document.getElementById("star"+ i).src = "images/star0.png";
}
function updateMark() {
    previewMark(cNote);
}
var sendingMark = false;
function setMark(nNote) {
    if (sendingMark) return;
    cNote = (cNote != nNote) ? nNote:0;
    if (cNote != aNote) {
        previewMark(cNote);
        sendMark();
    }
}
function sendMark() {
    sendingMark = true;
    document.getElementById("markMsg").innerHTML = language ? 'Sending...':'Envoi en cours...';
    xhr("sendMark.php", ratingParams+"&rating="+cNote, function(reponse) {
        if (reponse == 1) {
            sendingMark = false;
            aNote = cNote;
            document.getElementById("markMsg").innerHTML = (aNote>0) ? (language ? 'Thanks for your vote':'Merci de votre vote') : (language ? 'Vote removed successfully':'Vote supprim&eacute; avec succ&egrave;s');
            return true;
        }
        return false;
    });
}
function reportCourse(theCourse) {
    reportContent(commentType,commentCircuit, theCourse);
}