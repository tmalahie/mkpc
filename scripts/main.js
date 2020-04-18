function deleteNotif(ids) {
	var data = "";
	for (var i=0;i<ids.length;i++) {
		if (i)
			data += "&";
		data += "id"+ i +"="+ ids[i];
	}
	o_xhr("supprNotif.php", data, function(res) {
		return (res == 1);
	});
}
function deleteNotifs() {
	o_xhr("supprNotifs.php", "", function(res) {
		return (res == 1);
	});		
}
function closeNotif(e,elt) {
	e.preventDefault();
	var iLink = elt.parentNode.parentNode;
	deleteNotif(iLink.dataset.ids.split(","));
	var notifsList = document.getElementById("notifs-list");
	notifsList.removeChild(iLink);
	var nbNotifs = document.getElementById("notifs-nb-alert").innerHTML-1;
	if (!(nbNotifs>=0))
		nbNotifs = notifsList.getElementsByClassName("notif-container").length;
	document.getElementById("nb-notifs").innerHTML = "<strong>"+ nbNotifs +"</strong> notification"+ (nbNotifs>1 ? "s":"");
	document.getElementById("notifs-nb-alert").innerHTML = nbNotifs;
	if (!nbNotifs)
		document.getElementById("notifs-bubble").className = "no-notifs";
}
function closeNotifs() {
	deleteNotifs();
	document.getElementById("notifs-bubble").className = "no-notifs";
}
window.mainVersion = 2;