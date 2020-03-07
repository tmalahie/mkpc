<?php
include('initdb.php');
?>
<html>
<head>
<style type="text/css">
#topic {
	height: 400px;
	overflow-y: scroll;
}
#subject {
	font-size: 50px;
}
#message {
	font-size: 25px;
}
button {
	width: 150px;
	height: 80px;
	font-size: 30px;
}
</style>
<script type="text/javascript" src="scripts/jquery.min.js"></script>
<script type="text/javascript">
var topics = [<?php
$subjects = mysql_query('SELECT id,titre FROM `mktopics` WHERE language=1');
while ($subject = mysql_fetch_array($subjects)) {
	$messages = mysql_query('SELECT auteur,message FROM `mkmessages` WHERE id=1 AND topic='. $subject['id']);
	if ($message = mysql_fetch_array($messages))
		echo '['. $subject['id'] .',"'.htmlentities(addslashes($subject['titre'])).'","'.preg_replace('#\r?\n#'," ",htmlentities($message['message']).'"],');
}
?>];
var id = 0;
function showTopic() {
	var topic = topics[id];
	if (topic) {
		$("#subject").attr("href", "topic.php?topic="+ topic[0]);
		$("#subject").html(topic[1]);
		$("#message").html(topic[2]);
		$("#progression").text((id+1)+"/"+topics.length);
		$("#topic").show();
	}
	else
		$("#topic").hide();
}
function prevTopic() {
	id--;
	showTopic();
}
function nextTopic() {
	id++;
	showTopic();
}
function selectCat(cat) {
	var topic = topics[id];
	var topicID = topics[id][0];
	$.get("setCategory.php?topic="+ topicID +"&category="+ cat, function(r) {
		if (topicID == topics[id][0]) {
			id++;
			showTopic();
		}
	});
}
document.onkeydown = function(e) {
	switch (e.keyCode) {
		case 46 :
			supprTopic();
			break;
		case 37 :
			prevTopic();
			break;
		case 39 :
			nextTopic();
			break;
	}
}
$(showTopic);
</script>
</head>
<body>
<h3 id="progression">0/0</h3>
<div id="topic">
	<a id="subject" href="#null" target="_blank"></a>
	<div id="message"></div>
</div>
<p>
	<button onclick="prevTopic()">&lt;</button>
	<button onclick="selectCat(4)">Intern</button>
	<button onclick="nextTopic()">&gt;</button>
</p>
</body>
</html>