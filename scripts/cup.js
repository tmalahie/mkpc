var selectedCircuits = [];
var loadingMsg = language ? "Loading":"Chargement";
function selectCircuit(tr) {
	var id = tr.dataset.id;
	var selectionID = selectedCircuits.indexOf(id);
	var orderTD = tr.getElementsByClassName("td-preview")[0];
	if (selectionID == -1) {
		selectedCircuits.push(id);
		tr.className = "selected";
		orderTD.innerHTML = "#"+ selectedCircuits.length;
	}
	else {
		selectedCircuits.splice(selectionID,1);
		tr.className = "";
		orderTD.innerHTML = "";
		for (var i=0;i<selectedCircuits.length;i++) {
			var orderTDi = document.getElementById("circuit"+ selectedCircuits[i]).getElementsByClassName("td-preview")[0];
			orderTDi.innerHTML = "#"+ (i+1);
		}
	}
	updateGUI();
}
function selectTr(id) {
	selectCircuit(document.getElementById("circuit"+ id));
}
function initGUI() {
	if (editting) {
		for (var i=0;i<cids.length;i++)
			selectTr(cids[i]);
	}
	else
		updateGUI();
}
function updateGUI() {
	var cidCtn = document.getElementById("cid-ctn");
	for (var i=0;i<selectedCircuits.length;i++) {
		var cidI = document.getElementById(ckey + i);
		if (!cidI) {
			cidI = document.createElement("input");
			cidI.type = "hidden";
			cidI.id = ckey + i;
			cidI.name = ckey + i;
			cidCtn.appendChild(cidI);
		}
		cidI.value = selectedCircuits[i];
	}
	for (var i=selectedCircuits.length;document.getElementById(ckey + i);i++)
		cidCtn.removeChild(document.getElementById(ckey + i));
	var errorMsg = getSubmitMsg();
	document.getElementById("nb-selected").innerHTML = selectedCircuits.length;
	document.getElementById("submit-selection").disabled = !!errorMsg;
	document.getElementById("submit-selection").title = errorMsg;
}
function previewImg(e,src) {
	apercu(src);
	e.stopPropagation();
}