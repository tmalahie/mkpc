<?php
header('Content-Type: text/javascript; charset=iso-8859-1');
include('../php/includes/language.php');
include('../php/includes/session.php');
include('../php/includes/smileys.php');
?>

SMILEY_NAMES = <?= json_encode($smileyNames); ?>;

function helpBbCode() {
	window.open('<?= $language ? 'helpBbCode':'aideBbCode'; ?>.html','gerer','scrollbars=1, resizable=1, width=500, height=500');
	void(0);
}
function ajouter(smiley) {
	var field = document.forms[0].message;
	field.focus();
	
	if (window.ActiveXObject) {
		var textRange = document.selection.createRange();
		textRange.text = smiley;
		textRange.moveStart('character', 0);
		textRange.moveEnd('character', 0);
		textRange.select();
	}
	else {
		var startSelection = field.value.substring(0, field.selectionStart);
		var lSelection = field.selectionEnd-field.selectionStart;
		var endSelection = field.value.substring(field.selectionEnd);
		field.value = startSelection + smiley + endSelection;
		field.focus();
		var nCursor = startSelection.length+smiley.length;
		field.setSelectionRange(nCursor, nCursor);
	}
}
function closeSmileys() {
	document.onkeyup = undefined;
}
function ajouterPlus(smileyID) {
	ajouter(":"+smileyID+":");
	closeSmileys();
}
function closeSmileys() {
	document.onkeyup = undefined;
	document.body.removeChild(document.getElementById("smileys-list"));
}
function moresmileys() {
	var frame = document.createElement("div");
	frame.id = "smileys-list";
	frame.style.width = "640px";
	var oTimes = document.createElement("a");
	oTimes.className = "smileys-close";
	oTimes.href = "#null";
	oTimes.innerHTML = "&times;";
	oTimes.onclick = function() {
		closeSmileys();
		return false;
	}
	frame.appendChild(oTimes);
	document.onkeyup = function(e) {
		if (e.keyCode == 27) {
			closeSmileys();
			return false;
		}
	}
	for (var i=0;i<SMILEYNAMES.length;i++) {
		var smiley = document.createElement("img");
		smiley.src = "images/smileys/smiley"+ i +".gif";
		smiley.alt = SMILEYNAMES[i];
		if (!smiley.dataset)
			smiley.dataset = {};
		smiley.dataset.i = SMILEYNAMES[i];
		smiley.onclick = function() {
			ajouterPlus(this.dataset.i);
		};
		frame.appendChild(smiley);
	}
	document.body.appendChild(frame);
}
function zerofill(nb) {
	return (nb>=10) ? nb:"0"+nb;
}
function replaceAll(str, replace, with_this) {
	var iStr = 0;
    for (var i=0;i<str.length;i++) {
		if (str.charAt(i).toLowerCase() == replace.charAt(iStr).toLowerCase()) {
			iStr++;
			if (iStr == replace.length) {
				str = str.substring(0, i+1-iStr) + with_this + str.substring(i+1);
				i += with_this.length-iStr;
				iStr = 0;
			}
		}
		else {
			i -= iStr;
			iStr = 0;
		}
    }
	return str;
}
function insert(startTag, endTag) {
	var field = document.forms[0].message;
	var scroll = field.scrollTop;
	field.focus();
	if (window.ActiveXObject) {
		var textRange = document.selection.createRange();            
		var currentSelection = textRange.text;
		textRange.text = startTag + currentSelection + endTag;
		textRange.moveStart('character', -endTag.length-currentSelection.length);
		textRange.moveEnd('character', -endTag.length);
		textRange.select();  
	}
	else {
		var startSelection = field.value.substring(0, field.selectionStart);
		var currentSelection = field.value.substring(field.selectionStart, field.selectionEnd);
		var endSelection = field.value.substring(field.selectionEnd);
		field.value = startSelection + startTag + currentSelection + endTag + endSelection;
		field.focus();
		field.setSelectionRange(startSelection.length + startTag.length, startSelection.length + startTag.length + currentSelection.length);
	}  
	
	field.scrollTop = scroll;   
}
function insertTag(tag) {
	insert("["+ tag +"]", "[/"+ tag +"]");
}
function insertCustomTag(tag,value) {
	if (value == 'custom_picker') {
		var cPicker = document.getElementById("bbColPicker");
		cPicker.style.display = "inline-block";
		cPicker.focus();
		cPicker.click();
		return;
	}
	insert("["+ tag +"="+ value +"]", "[/"+ tag +"]");
}
function htmlspecialchars(str) {
	return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
}

function apercu() {
	var content = document.forms[0].message.value;
	if (!content) {
		document.getElementById("fMessages").style.display = "none";
		return;
	}

	var now = new Date();
	document.querySelectorAll('.mDate')[0].innerHTML = "<?php echo $language ? 'Today at "+ now.getHours() +":"+ zerofill(now.getMinutes()) +":"+ zerofill(now.getSeconds()) +"':'Aujourd\'hui &agrave; "+ now.getHours() +":"+ zerofill(now.getMinutes()) +":"+ zerofill(now.getSeconds()) +"'; ?>";
	o_xhr("bbcodeConvert.php", content, function(r) {
		content = r;

		var mContent = document.querySelectorAll(".mBody")[0];
		mContent.innerHTML = "";
		mContent.style.width = "";
		mContent.style.width = mContent.scrollWidth + "px";
		mContent.innerHTML = content;

		document.getElementById("fMessages").style.display = "block";
		return true;
	});
}