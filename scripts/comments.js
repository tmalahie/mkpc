function updateCircuitNote() {
	var commentNote = $("#comments-circuitnote");
	if (circuitNotes) {
		var r1 = 204, g1 = 204, b1 = 204;
		var r2 = 255, g2 = 255, b2 = 0;
		var t = circuitNote/4;
		function average(a,b) {
			return a + Math.round(t*(b-a));
		}
		var r = average(r1,r2), g = average(g1,g2), b = average(b1,b2);
		commentNote.css("color", "rgb("+ r +","+ g +","+ b +")");
		commentNote.html(Math.round(circuitNote*100)/100 +"&nbsp;/&nbsp;5&nbsp;"+ (language ? "on":"sur") +"&nbsp;"+ circuitNotes +"&nbsp;"+ (language ? ("view"+ (circuitNote>1 ? "s":"")) : "avis"));
	}
	else {
		commentNote.css("color", "white");
		commentNote.html(language ? "Unrated":"Non not&eacute;");
	}
}
function updateCircuitDate() {
	if (circuitDate)
		$("#comments-circuitdate").html(circuitDate);
	else {
		$("#comments-circuitauthor").css("padding-top", "2px");
		$("#comments-circuitstar").css("padding-top", "6px");
		$("#comments-circuitnote").css("padding-top", "8px");
	}
}
(function() {
	$("#comments-section").append(
		'<div id="comments-infos">'+
			'<div id="comments-closectn">'+
				'<a id="comments-close" href="#null">&times;</a>'+
				'<a id="comments-open" href="#null">□</a>'+
			'</div>'+
			(circuitName ? '<h2>'+ circuitName +'</h2>':'')+
			'<table>'+
				'<tr><td id="comments-circuitauthor" rowspan="2">'+
					'<div>'+ (language ? 'By':'Par') +' '+ (circuitUser ? '<a href="profil.php?id='+circuitUser+'">':'') + (circuitAuthor ? circuitAuthor.replace(/ /g,'&nbsp;'): (language ? "Anonymous":"Anonyme")) + (circuitUser ? '</a>':'') +'</div>'+
				'</td><td id="comments-circuitstar">'+
					'<img src="images/cstar'+ (circuitNotes ? 1:0) +'.png" alt="Note" />'+
				'</td><td id="comments-circuitnote"></td></tr>'+
				'<tr><td id="comments-circuittime">'+
					(circuitDate ? '<img src="images/records.png" alt="Date" />':'') +
				'</td><td id="comments-circuitdate"></td></tr>'+
			'</table>'+
		'</div>'
	);
	updateCircuitNote();
	updateCircuitDate();
	$("#comments-close").click(function() {
		$("#comments-section").addClass("comments-closed");
		return false;
	});
	$("#comments-open").click(function() {
		$("#comments-section").removeClass("comments-closed");
		return false;
	});
	$("#comments-section").append('<h1>'+ (language ? 'Comments':'Commentaires') +' (<span id="comments-nb"></span>)</h1>');
	$("#comments-section").append('<div id="comments-none">'+ (language ? 'No comments yet. Be the first one to give your opinion !':'Aucun commentaire. Soyez le premier &agrave; donner votre avis !</div>'));
	var comments = $('<div id="comments"></div>');
	$("#comments-section").append(comments);
	var myCommentID, myCommentName, myCommentAdmin;
	function createComment(className) {
		var res = $ (
			'<div class="comment-container comment-'+ className +'">'+
				'<div class="comment-header"></div>'+
				'<div class="comment-message">'+
					'<textarea placeholder="'+ (language ? 'Your message':'Votre message') +'..." class="comment-textarea comment-posting"></textarea>'+
					'<span class="comment-value comment-posted"></span>'+
				'</div>'+
				'<div class="comment-reactions comment-posted"></div>'+
				'<div class="comment-options">'+
					'<div class="comment-posting">'+
						'<input type="button" class="comment-send" value="'+ (language ? 'Send':'Envoyer') +'" />'+
						'<img src="images/forum/delete.png" class="comment-undo" alt="'+ (language ? 'Undo':'Annuler') +'" title="'+ (language ? 'Undo':'Annuler') +'" />'+
					'</div>'+
					'<div class="comment-posted">'+
						'<img src="images/forum/edit.png" class="comment-edit comment-alter" alt="'+ (language ? 'Edit':'Modifier') +'" title="'+ (language ? 'Edit':'Modifier') +'" />'+
						'<img src="images/forum/delete.png" class="comment-suppr comment-alter" alt="'+ (language ? 'Delete':'Supprimer') +'" title="'+ (language ? 'Delete':'Supprimer') +'" />'+
						'<img src="images/forum/react.png" class="comment-react" alt="'+ (language ? 'React':'Réagir') +'" title="'+ (language ? 'Add reaction':'Ajouter une réaction') +'" />'+
					'</div>'+
				'</div>'+
			'</div>'
		);
		res.find(".comment-textarea").keydown(function(e) {
			e.stopPropagation();
		});
		return res;
	}
	function createNewComment() {
		var res = createComment("new");
		res.find(".comment-undo").hide();
		res.find(".comment-header").html('<strong>'+ (language ? "Post a comment":"Poster un commentaire") +' :</strong>');
		setNewEvents(res);
		return res;
	}
	function nl2br(str) {
		return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;").replace(/(https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/\/=]*))/g, '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>').replace(/([a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4})/gi, '<a href="mailto:$1">$1</a>').replace(/\r?\n/g, '<br />').replace(/  /g, ' &nbsp;');
	}
	function br2nl(str) {
		return str.replace(/ &nbsp;/g, '  ').replace(/<br ?\/?>/g, '\r\n').replace(/<([^>]+)>/g,"").replace(/&#039;/g, "'").replace(/&quot;/g, '"').replace(/&gt;/g, ">").replace(/&lt;/g, "<").replace(/&amp;/g, "&");
	}
	function setNewClass(commentCtn) {
		commentCtn.removeClass("comment-sent");
		commentCtn.addClass("comment-new");
	}
	function setSendClass(commentCtn) {
		commentCtn.removeClass("comment-new");
		commentCtn.addClass("comment-sent");
	}
	function setNewEvents(commentCtn) {
		var commentSend = commentCtn.find(".comment-send");
		commentSend.click(function() {
			var commentMessage = commentCtn.find(".comment-textarea");
			if (!commentMessage.val())
				return;
			commentSend.prop("disabled", true);
			commentMessage.prop("disabled", true);
			$.post("commentCircuit.php", {"circuit":commentCircuit,"type":commentType,"message":commentMessage.val()}).success(function(res) {
				commentSend.unbind("click");
				commentSend.prop("disabled", false);
				commentMessage.prop("disabled", false);
				commentCtn.find(".comment-undo").show();
				setSentEvents(commentCtn);
				setReactEvents(commentCtn);
				setEditEvents(commentCtn);
				commentCtn.data("id",res);
				commentCtn.attr("data-id", res);
				commentCtn.find(".comment-value").html(nl2br(commentCtn.find(".comment-textarea").val()));
				setPostTime(commentCtn, new Date(),myCommentName,myCommentID);
				setSendClass(commentCtn);
				comments.prepend(createNewComment());
				updateNbComments();
			}).fail(function() {
				alert(language ? "An error occured when sending the comment. Please try again":"Une erreur est survenue lors de l'envoi du commentaire. Veuillez réessayer.");
				commentSend.prop("disabled", false);
				commentMessage.prop("disabled", false);
			});
		});
	}
	function setSentEvents(commentCtn) {
		var editButton = commentCtn.find(".comment-edit");
		editButton.click(function() {
			commentCtn.find(".comment-textarea").val(br2nl(commentCtn.find(".comment-value").html()));
			setNewClass(commentCtn);
			commentCtn.find(".comment-textarea").select();
		});
		var delButton = commentCtn.find(".comment-suppr");
		delButton.click(function() {
			if (confirm(language ? "Are you sure you want to delete this comment ?":"Voulez-vous vraiment supprimer ce commentaire ?")) {
				$.post("supprComment.php", {"id_msg":commentCtn.data("id")}).success(function() {
					commentCtn.remove();
					updateNbComments();
				}).fail(function() {
					alert(language ? "An error occured when deleting the comment. Please try again":"Une erreur est survenue lors de la suppression du commentaire. Veuillez réessayer.");
				});
			}
		});
	}
	function setReactEvents(commentCtn) {
		var reactButton = commentCtn.find(".comment-react");
		reactButton.click(function() {
			openReactions('trackcom',commentCtn.data("id"),this);
		});
	}
	function setEditEvents(commentCtn) {
		var commentSend = commentCtn.find(".comment-send");
		var commentUndo = commentCtn.find(".comment-undo");
		commentSend.click(function() {
			var commentMessage = commentCtn.find(".comment-textarea");
			if (!commentMessage.val())
				return;
			commentSend.prop("disabled", true);
			commentUndo.prop("disabled", true);
			commentMessage.prop("disabled", true);
			$.post("editComment.php", {"id_msg":commentCtn.data("id"),"message":commentMessage.val()}).success(function(res) {
				commentSend.prop("disabled", false);
				commentUndo.prop("disabled", false);
				commentMessage.prop("disabled", false);
				commentCtn.find(".comment-value").html(nl2br(commentCtn.find(".comment-textarea").val()));
				setSendClass(commentCtn);
			}).fail(function() {
				alert(language ? "An error occured when editting the comment. Please try again":"Une erreur est survenue lors de la modification du commentaire. Veuillez réessayer.");
				commentSend.prop("disabled", false);
				commentUndo.prop("disabled", false);
				commentMessage.prop("disabled", false);
			});
		});
		commentUndo.click(function() {
			setSendClass(commentCtn);
		});
	}
	function zerofill(nb) {
		nb += "";
		while (nb.length < 2)
			nb = "0"+ nb;
		return nb;
	}
	function setPostTime(commentCtn, time,author,authorID) {
		var h = zerofill(time.getHours()), mn = zerofill(time.getMinutes()), d = zerofill(time.getDate()), m = zerofill(time.getMonth()+1), y = time.getFullYear();
		var now = new Date(), d0 = zerofill(now.getDate()), m0 = zerofill(now.getMonth()+1), y0 = now.getFullYear();
		now.setDate(now.getDate() - 1); var d1 = zerofill(now.getDate()), m1 = zerofill(now.getMonth()+1), y1 = now.getFullYear();
		var ymd;
		if (d==d0 && m==m0 && y==y0)
			ymd = language ? "Today":"Aujourd'hui";
		else if (d==d1 && m==m1 && y==y1)
			ymd = language ? "Yesterday":"Hier";
		else
			ymd = language ? "On "+y+"-"+m+"-"+d:"Le "+y+"-"+m+"-"+d;
		commentCtn.find(".comment-header").html(language ? ymd+' at '+h+':'+mn+ (author ? ' by <a href="profil.php?id='+ authorID +'">'+author+'</a>':''):ymd+' &agrave; '+h+':'+mn+ (author ? ' par <a href="profil.php?id='+ authorID +'">'+author+'</a>':''));
	}
	$.post("getComments.php", {"circuit":commentCircuit,"type":commentType}, function(res) {
		res = JSON.parse(res);
		if (res.id) {
			myCommentID = res.id;
			myCommentName = res.pseudo;
			myCommentAdmin = res.admin;
			comments.append(createNewComment());
		}
		else if (!res.banned) {
			comments.append('<div id="comment-connect"><a href="forum.php" target="_blank">'+ (language ? 'Log-in':'Connectez-vous') +'</a> '+ (language ? 'to post a comment':'pour poster un commentaire') +'</div>');
		}
		else
			comments.append('<div />');
		for (var i=0;i<res.comments.length;i++) {
			var commentData = res.comments[i];
			var postedComment = createComment("sent");
			setPostTime(postedComment, new Date(commentData.date*1000),commentData.auteur,commentData.auteurID);
			postedComment.find(".comment-value").html(nl2br(commentData.message));
			postedComment.find(".comment-reactions").html(commentData.reactions);
			postedComment.attr("data-id", commentData.id);
			if ((commentData.auteurID == myCommentID) || myCommentAdmin) {
				postedComment.data("id",commentData.id);
				setSentEvents(postedComment);
				setReactEvents(postedComment);
				setEditEvents(postedComment);
			}
			else if (myCommentID) {
				postedComment.find(".comment-options .comment-alter").hide();
				setReactEvents(postedComment);
			}
			else
				postedComment.find(".comment-options .comment-posted").hide();
			comments.append(postedComment);
		}
		updateNbComments();
		var dHeight = $(document).height();
		$("#comments-section").css("visibility", "hidden");
		$("#comments-section").show();
		comments.css("max-height", dHeight-$("#comments-infos").height()-180);
		$("#comments-section").css("visibility", "visible");
	});
	function updateNbComments() {
		var nbComments = comments.children().length-1;
		$("#comments-nb").text(nbComments);
		if (nbComments)
			$("#comments-none").hide();
		else
			$("#comments-none").show();
	}
})();