function clickToTopic(fMessage, topicID,msgID) {
	fMessage.onclick = function() {
		open("topic.php?topic="+topicID+"&message="+msgID);
	};
}
function preventPropagation(event) {
	event.stopPropagation();
}
document.addEventListener("DOMContentLoaded", function() {
	var fTopics = document.querySelectorAll(".fMessages");
	for (var i=0;i<fTopics.length;i++) {
		var fTopic = fTopics[i];
		var topicID = fTopic.dataset.topic;
		var fMessages = fTopic.querySelectorAll(".fMessage");
		for (var j=0;j<fMessages.length;j++) {
			var fMessage = fMessages[j];
			var msgID = fMessage.dataset.msg;
			clickToTopic(fMessage, topicID,msgID);
			var fLinks = fMessage.querySelectorAll("a");
			for (var k=0;k<fLinks.length;k++)
				fLinks[k].onclick = preventPropagation;
		}
	}
});