function delComment(id) {
	if (confirm(o_language ? 'Delete this comment?' : 'Supprimer ce commentaire ?'))
		document.location.href = 'delNewscom.php?id=' + id;
}
function editComment(id) {
	$("#news-comment-ctn-"+id).addClass("news-comment-editting");
	$("#news-comment-ctn-"+id+" .news-comment textarea").select();
}
function undoEditComment(id) {
	$("#news-comment-ctn-"+id).removeClass("news-comment-editting");
}
function rejectNews(id) {
	o_prompt(o_language ? 'Please enter a short message to explain your rejection:' : 'Entrez un court message expliquant votre refus :', '', function(res) {
		document.location.href = "rejectNews.php?id="+id+"&reason="+encodeURIComponent(res);
	});
}