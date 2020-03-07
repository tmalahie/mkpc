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
document.addEventListener("DOMContentLoaded", updateForumWidth);
window.addEventListener("resize", postUpdateForumWidth);