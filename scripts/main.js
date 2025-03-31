function deleteNotif(ids) {
    o_xhr(
        "supprNotif.php",
        ids.map((id, index) => `id${index}=${id}`).join("&"),
        res => res == 1
    );
}

function deleteNotifs() {
    o_xhr(
        "supprNotifs.php", 
        "", 
        res => res == 1
    );
}

function closeNotif(e, elt) {
    e.preventDefault();
    let iLink = elt.parentNode.parentNode;
    deleteNotif(iLink.dataset.ids.split(","));
    let notifsList = document.getElementById("notifs-list");
    notifsList.removeChild(iLink);

    let nbNotifs = document.getElementById("notifs-nb-alert").innerHTML - 1;
    if (nbNotifs < 0) {
        nbNotifs = notifsList.getElementsByClassName("notif-container").length;
    }

    document.getElementById("nb-notifs").innerHTML = `<strong>${nbNotifs}</strong> notification${nbNotifs > 1 ? "s" : ""}`;
    document.getElementById("notifs-nb-alert").innerHTML = nbNotifs;

    if (!nbNotifs) {
        document.getElementById("notifs-bubble").className = "no-notifs";
    }
}

function closeNotifs() {
    deleteNotifs();
    document.getElementById("notifs-bubble").className = "no-notifs";
}