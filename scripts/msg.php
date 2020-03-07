<?php
header('Content-Type: text/javascript; charset=iso-8859-1');
include('../language.php');
include('../session.php');
include('../smileys.php');
?>
function helpBbCode() {
	window.open('<?php echo $language ? 'helpBbCode':'aideBbCode'; ?>.html','gerer','scrollbars=1, resizable=1, width=500, height=500');
	void(0);
}
function ajouter(smiley) {
	var field = document.forms[0].message;
	field.focus();
	
	if (window.ActiveXObject) {
		var textRange = document.selection.createRange();            
		var currentSelection = textRange.text;
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
	var smileyNames = [<?php
	for ($i=0;$i<$nbSmileys2;$i++)
		echo ($i ? ',':'') . '"'. $smileyNames[$i] .'"';
	?>];
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
	for (var i=0;i<smileyNames.length;i++) {
		var smiley = document.createElement("img");
		smiley.src = "images/smileys/smiley"+ i +".gif";
		smiley.alt = smileyNames[i];
		if (!smiley.dataset)
			smiley.dataset = {};
		smiley.dataset.i = smileyNames[i];
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
	if (content) {
		var now = new Date();
		document.querySelectorAll('.mDate')[0].innerHTML = "<?php echo $language ? 'Today at "+ now.getHours() +":"+ zerofill(now.getMinutes()) +":"+ zerofill(now.getSeconds()) +"':'Aujourd\'hui &agrave; "+ now.getHours() +":"+ zerofill(now.getMinutes()) +":"+ zerofill(now.getSeconds()) +"'; ?>";
		content = htmlspecialchars(content);
		content = content.replace(/\[center\]([\s\S]*?)\[\/center\]/g, '<div style="text-align: center;">$1</div>');
		content = content.replace(/\[right\]([\s\S]*?)\[\/right\]/g, '<div style="text-align: right;">$1</div>');
		content = content.replace(/\[left\]([\s\S]*?)\[\/left\]/g, '<div style="text-align: left;">$1</div>');
		content = content.replace(/\[b\]([\s\S]*?)\[\/b\]/g, '<strong>$1</strong>');
		content = content.replace(/\[i\]([\s\S]*?)\[\/i\]/g, '<em>$1</em>');
		content = content.replace(/\[u\]([\s\S]*?)\[\/u\]/g, '<u>$1</u>');
		content = content.replace(/\[s\]([\s\S]*?)\[\/s\]/g, '<s>$1</s>');
		content = content.replace(/\[url\]([^\[]*?)\[\/url\]/g, '<a href="$1" target="_blank">$1</a>');
		content = content.replace(/\[img\]([^\[]*?)\[\/img\]/g, '<img src="$1" alt="$1" />');
		content = content.replace(/\[url=([^\]]+)\]([\s\S]*?)\[\/url\]/g, '<a href="$1" class="type1" target="_blank">$2</a>');
		content = content.replace(/\[color=([^\]]+)\]([\s\S]*?)\[\/color\]/g, '<span style="color: $1">$2</span>');
		content = content.replace(/\[font=([a-zA-Z ]+)\]([\s\S]*?)\[\/font\]/g, '<span style="font-family: $1">$2</span>');
		content = content.replace(/\[size=([0-9]{1,2})\]([\s\S]*?)\[\/size\]/g, '<span style="font-size: $1pt;">$2</span>');
		content = content.replace(/\[yt\].*(?:youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=)([^#\&\?\[]*).*\[\/yt\]/g, '<iframe src="https://www.youtube.com/embed/$1" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe>');
		content = content.replace(/\[yt\].*(?:youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=)([^#\&\?\[]*).*\[\/yt\]/g, '<iframe src="https://www.youtube.com/embed/$1" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe>');
		content = content.replace(/\[(fr|en)\]([\s\S]*?)\[\/\1\][ \r\n\t]*\[(fr|en)\]([\s\S]*?)\[\/\3\]/g, '<div class="tr-ctn tr-ctn-<?php echo ($language ? 'en':'fr'); ?>"><div class="tr-tabs"><div class="tr-tab-$1" onclick="this.parentNode.parentNode.className=\'tr-ctn tr-ctn-$1\'"></div><div class="tr-tab-$3" onclick="this.parentNode.parentNode.className=\'tr-ctn tr-ctn-$3\'"></div></div><div class="tr-msgs"><div class="tr-msg-$1">$2</div><div class="tr-msg-$3">$4</div></div></div>');
		var aContent;
		do {
			aContent = content;
			content = content.replace(/\[quote\]([\s\S]*?)\[\/quote\]/g, '<div class="quote1">$1</div>');
			content = content.replace(/\[quote=([\s\S]+?)\]([\s\S]*?)\[\/quote\]/g, '<div class="quote1"><div class="quote1author"><a href="profil.php?pseudo=$1" target="_blank">$1</a> <?php echo $language ? 'wrote':'a &eacute;crit '; ?>:</div>$2</div>');
		} while (content != aContent);
		do {
			aContent = content;
			content = content.replace(/\[spoiler\]([\s\S]*?)\[\/spoiler\]/g, '<div class="spoiler1"><div class="spoiler1disp">Spoiler [<a class="spoiler1show" href="#null" onclick="this.parentNode.parentNode.className=\'spoiler1 spoiler1shown\';return false"><?php echo $language ? 'Show':'Afficher'; ?></a><a class="spoiler1hide" href="#null" onclick="this.parentNode.parentNode.className=\'spoiler1\';return false"><?php echo $language ? 'Hide':'Masquer'; ?></a>]</div><div class="spoiler1cont">$1</div></div>');
		} while (content != aContent);
		content = content.replace(/\B@([a-zA-Z0-9\-_]+)/g, '<a class="ref1" href="profil.php?pseudo=$1" target="_blank">@$1</a>');
		content = replaceAll(replaceAll(replaceAll(content, "  ", " &nbsp;"), "\n", "<br />"), "\t", " &nbsp; &nbsp; &nbsp; &nbsp;");
		<?php
		for ($i=0;$i<$nbSmileys2;$i++)
			echo 'content = replaceAll(content, ":'. $smileyNames[$i] .':", \'<img src="images/smileys/smiley'. $i .'.gif" alt="'. $smileyNames[$i] .'" />\');';
		for ($i=0;$i<$nbSmileys;$i++)
			echo 'content = replaceAll(content, "'. $smileys[$i] .'", \'<img src="images/smileys/smiley'. $i .'.png" alt="'. $smileys[$i] .'" />\');';
		for ($i=0;$i<$nbSmileys2;$i++)
			echo 'content = replaceAll(content, ":'. $i .':", \'<img src="images/smileys/smiley'. $i .'.gif" alt="Smiley '. $smileyNames[$i] .'" />\');';
		?>
		document.getElementById("fMessages").style.display = "block";
		var mContent = document.querySelectorAll(".mBody")[0];
		mContent.innerHTML = "";
		mContent.style.width = "";
		mContent.style.width = mContent.scrollWidth +"px";
		mContent.innerHTML = content;
	}
	else
		document.getElementById("fMessages").style.display = "none";
}