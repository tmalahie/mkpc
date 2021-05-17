var topicWidth = 0;
function updateForumWidth() {
	if (window.fullScreen || (window.innerWidth == screen.width && window.innerHeight == screen.height)) return;
	if (window.innerWidth == topicWidth) return;
	topicWidth = window.innerWidth;
	var messages = document.getElementsByClassName("mBody");
	var htmls = [];
	for (var i=0;i<messages.length;i++) {
		var message = messages[i];
		htmls[i] = message.innerHTML;
		message.style.width = "";
		message.innerHTML = "";
	}
	for (var i=0;i<messages.length;i++) {
		var message = messages[i];
		message.style.width = message.scrollWidth +"px";
		message.innerHTML = htmls[i];
	}
}
var postResizeHandler;
function postUpdateForumWidth() {
	clearTimeout(postResizeHandler);
	if (window.fullScreen || (window.innerWidth == screen.width && window.innerHeight == screen.height)) return;
	postResizeHandler = setTimeout(updateForumWidth,100);
}
function followTopic(id, follow, userIsPoster) {
	o_xhr("follow.php", "topic="+ id + (follow ? "&follow=1":""), function(reponse) {
		if (reponse == 1) {
			if (!userIsPoster) {
				var nbFollowersDivs = document.getElementsByClassName("nb_followers");
				for (var i=0;i<nbFollowersDivs.length;i++) {
					if (follow)
						nbFollowersDivs[i].innerHTML++;
					else
						nbFollowersDivs[i].innerHTML--;
				}
			}
			return true;
		}
		return false;
	});
	var followerCheckboxs = document.getElementsByClassName("follow_topic_checkbox");
	for (var i=0;i<followerCheckboxs.length;i++)
		followerCheckboxs[i].checked = follow;
}
function confirmSuppr() {
	return confirm(o_language ? 'Are you sure you want to delete this message ?':'Voulez-vous vraiment supprimer ce message ?');
}
function openReactions(type,link, $elt) {
	var $reactions = document.getElementById("message-reactions");
	$reactions.dataset.type = type;
	$reactions.dataset.link = link;
	var $reactionsDialog = $reactions.querySelector(".message-reactions-dialog");
	$reactions.style.display = "block";
	var eltPos = $elt.getBoundingClientRect();
	var dialogPos = $reactionsDialog.getBoundingClientRect();
	$reactionsDialog.style.left = Math.max(eltPos.x+eltPos.width-dialogPos.width, 2) +"px";
	$reactionsDialog.style.top = Math.max(eltPos.y-dialogPos.height-1, 5) +"px";
}
function sendReaction(type,link, $elt) {
	$elt.onclick = undefined;
	addReaction($elt.dataset.name, type, link, $elt.dataset.checked);
}
function addReaction(key, type, link, rm) {
	var $reactions = document.getElementById("message-reactions");
	link = link || $reactions.dataset.link;
	type = type || $reactions.dataset.type;
	o_xhr("sendReaction.php", "type="+type+"&link="+link+"&reaction="+key+(rm ? "&delete":""), function(responseHTML) {
		var $mReactions;
		switch (type) {
		case "topic":
			var msgId = link.split(",")[1];
			$mReactions = document.querySelector(".fMessage[data-msg='"+msgId+"'] .mReactions");
			break;
		case "news":
			$mReactions = document.querySelector(".news-reactions");
			break;
		case "newscom":
			$mReactions = document.querySelector(".news-comment[data-id='"+link+"'] .news-comment-reactions");
			break;
		}
		if ($mReactions)
			$mReactions.innerHTML = responseHTML;
		hideReactionDetails();
		return true;
	});
	closeReactions();
}
function closeReactions() {
	document.getElementById("message-reactions").style.display = "none";
}
function showReactionDetails($elt) {
	var reaction = $elt.dataset.name;
	var names = $elt.dataset.list.split(",");
	var namesString = "";
	for (var i=0;i<names.length;i++) {
		if (i) {
			if (i < (names.length-1))
				namesString += ", ";
			else
				namesString += o_language ? " and " : " et ";
		}
		namesString += "<strong>"+names[i]+"</strong>";
	}
	var $details = document.getElementById("message-reactions-details");
	var $detailsImg = $details.querySelector("img");
	var $detailsDiv = $details.querySelector("div");
	$detailsDiv.innerHTML = namesString + " " + (o_language ? "reacted with":(names.length>=2 ? "ont réagi avec":"a réagi avec")) + " :"+reaction+":";
	$detailsDiv.style.width = (names.join(", ").length > 50) ? "360px":"";
	$detailsImg.src = "images/forum/reactions/"+reaction+".png";
	$detailsImg.alt = reaction;
	$details.classList.add("show");
	var eltPos = $elt.getBoundingClientRect();
	var detailsPos = $details.getBoundingClientRect();
	var left = (eltPos.x - Math.round((detailsPos.width-eltPos.width)/2));
	$details.style.left = Math.max(Math.min(left, document.body.clientWidth-detailsPos.width-2), 2) +"px";
	var top = eltPos.y-detailsPos.height;
	if (top >= 0)
		$details.style.top = (top-2) +"px";
	else
		$details.style.top = (eltPos.y+eltPos.height+2) +"px";
}
function hideReactionDetails() {
	var $details = document.getElementById("message-reactions-details");
	$details.classList.remove("show");
}
document.addEventListener("keydown", function(e) {
	if (e.keyCode == 27) {
		var $reactions = document.getElementById("message-reactions");
		if ($reactions.style.display === "block") {
			e.preventDefault();
			$reactions.style.display = "none";
		}
	}
});
document.addEventListener("DOMContentLoaded", updateForumWidth);
window.addEventListener("resize", postUpdateForumWidth);