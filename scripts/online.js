function o_xhr(page, send, onload, backoff) {
	if (!backoff)
		backoff = 1000;
	var xhr_object;
	if (window.XMLHttpRequest || window.ActiveXObject) {
		if (window.ActiveXObject) {
			try {
				xhr_object = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch(e) {
				xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
			}
		}
		else
			xhr_object = new XMLHttpRequest(); 
	}
	xhr_object.open("POST", "api/"+page, true);
	xhr_object.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr_object.setRequestHeader("If-Modified-Since", "Wed, 15 Nov 1995 00:00:00 GMT");
	try {
		xhr_object.onload = function () {
			if (xhr_object.status == 200) {
				if (!onload(xhr_object.responseText)) {
					setTimeout(function() {
						o_xhr(page,send,onload,backoff*2);
					}, backoff);
				}
			}
			else
				xhr_object.onerror();
		};
		xhr_object.onerror = function () {
			setTimeout(function() {
				o_xhr(page,send,onload, backoff*2);
			}, backoff);
		};
	}
	catch (e) {
		xhr_object.onreadystatechange = function () {
			if ((xhr_object.readyState == 4) && !onload(xhr_object.responseText)) {
				setTimeout(function() {
					o_xhr(page,send,onload,backoff*2);
				}, backoff);
			}
		};
	}
	xhr_object.send(send);
}

var o_focused = true;
window.onfocus = function() {
	o_focused = true;
};
window.onblur = function() {
	o_focused = false;
};

function o_inviteonline(memberID,battle) {
	o_xhr("invitations.php", "j0="+ memberID + (battle ? "&battle":""), function(reponse) {
		if (reponse == 1) {
			var o_msgId = displayMsg(o_language ? "Invitation sent":"Invitation envoy&eacute;e", false);
			if (o_msgId != -1) {
				var o_msg = document.getElementById("comsg"+ o_msgId);
				o_disappear(o_msg);
			}
			return true;
		}
		return false;
	});
}

function o_initdataset(o_div) {
	if (!o_div.dataset)
		o_div.dataset = {};
}

function o_refreshtable() {
	var o_table = document.getElementById("cotable");
	if (o_table) {
		o_table.innerHTML = "";
		if (oListJoueurs.length) {
			o_table.className = "";
			for (var i=0;i<oListJoueurs.length;i++) {
				var oJoueur = oListJoueurs[i];
				var o_selector = document.createElement("div");
				o_initdataset(o_selector);
				o_selector.dataset.member = oJoueur[0];
				o_selector.dataset.pseudo = oJoueur[1];
				o_selector.dataset.state = oJoueur[2];
				if (oIgnores.indexOf(oJoueur[0]) != -1)
					o_selector.dataset.ignored = 1;
				o_selector.dataset.action = 0;
				o_selector.className = "coselectable";
				var o_status = document.createElement("span");
				var o_statusclass, o_statustitle;
				switch (oJoueur[2]) {
				case 0:
					o_statusclass = "bgnotconnected";
					o_statustitle = (o_language ? 'Offline':'Hors-ligne');
					break;
				case 1:
					o_statusclass = "bgmuted";
					o_statustitle = (o_language ? 'Do not disturb':'Ne pas déranger');
					break;
				case 2:
					o_statusclass = "bgconnected";
					o_statustitle = (o_language ? 'Connected':'Connecté');
					break;
				}
				o_status.className = "costatus "+ o_statusclass;
				o_status.title = o_statustitle;
				o_selector.appendChild(o_status);
				var o_nick = document.createElement("span");
				o_nick.innerHTML = oJoueur[1];
				o_selector.appendChild(o_nick);
				if (o_selector.dataset.ignored == 1)
					o_selector.className += " coignored";
				if (oJoueur[3]) {
					var o_flag = document.createElement("img");
					o_flag.src = "images/flags/"+ oJoueur[3] +".png";
					o_flag.alt = oJoueur[3];
					o_flag.className = "coflag";
					o_flag.style.visibility = "hidden";
					o_flag.onload = function() {
						this.style.top = Math.round((o_selector.offsetHeight-this.offsetHeight-1)/2) +"px";
						this.style.visibility = "visible";
					};
					o_flag.onerror = function() {
						o_selector.removeChild(this);
					};
					o_selector.appendChild(o_flag);
				}
				o_selector.oncontextmenu = function(e) {
					return false;
				};
				o_selector.onmouseup = function(e) {
					var o_liste = document.getElementById("coliste");
					if (this.dataset.action == 0) {
						this.dataset.action = 1;
						var o_selector = this;
						var o_ctxtmenu, o_ctxtmenu2;
						var o_ctxtmenu_y;
						var a_body_click = document.body.onclick;
						function removeCtxtMenu() {
							o_selector.dataset.action = 0;
							if (o_ctxtmenu) {
								o_liste.removeChild(o_ctxtmenu);
								o_ctxtmenu = null;
							}
							if (o_ctxtmenu2) {
								o_liste.removeChild(o_ctxtmenu2);
								o_ctxtmenu2 = null;
							}
							document.body.onclick = a_body_click;
							if (o_table.onscroll == removeCtxtMenu)
								o_table.onscroll = undefined;
						}
						o_table.onscroll = removeCtxtMenu;
						var memberID = this.dataset.member*1;
						var memberPseudo = this.dataset.pseudo;
						var memberStatus = this.dataset.state;
						o_ctxtmenu = document.createElement("div");
						o_ctxtmenu.className = "cotxtmenu";
						if (this.dataset.ignored == 1) {
							var o_unignore = document.createElement("div");
							o_unignore.innerHTML = o_language ? "Unignore":"D&eacute;signorer";
							o_unignore.onclick = function(e) {
								o_xhr("unignore.php", "member="+ memberID, function(reponse) {
									if (reponse == 1) {
										var ignoreID = oIgnores.indexOf(memberID);
										if (ignoreID != -1) {
											oIgnores.splice(ignoreID,1);
											o_refreshtable();
										}
										return true;
									}
									return false;
								});
								removeCtxtMenu();
								e.stopPropagation();
								return false;
							};
							o_ctxtmenu.appendChild(o_unignore);
						}
						else {
							var o_talk = document.createElement("div");
							o_talk.innerHTML = o_language ? "Talk":"Discuter";
							o_talk.style.fontWeight = "bold";
							o_talk.onclick = function(e) {
								removeCtxtMenu();
								o_launchchat(memberID,memberPseudo);
								e.stopPropagation();
								return false;
							};
							var o_invite;
							if (memberStatus == 2) {
								o_invite = document.createElement("div");
								o_invite.innerHTML = o_language ? "Invite online":"Inviter en online";
								var that = this;
								o_invite.onclick = function(e) {
									e.stopPropagation();
									if (o_ctxtmenu2) {
										o_liste.removeChild(o_ctxtmenu2);
										o_ctxtmenu2 = null;
										return;
									}
									o_ctxtmenu2 = document.createElement("div");
									o_ctxtmenu2.className = "cotxtmenu2";
									o_ctxtmenu2.style.top = (o_ctxtmenu_y+30) +"px";
									var o_invitebattle = document.createElement("div");
									o_invitebattle.innerHTML = o_language ? "Battle":"Bataille";
									o_invitebattle.onclick = function() {
										o_inviteonline(memberID,1);
										removeCtxtMenu();
										e.stopPropagation();
									}
									var o_invitebattle_pts = document.createElement("div");
									o_invitebattle_pts.innerHTML = "...";
									o_invitebattle.appendChild(o_invitebattle_pts);
									o_ctxtmenu2.appendChild(o_invitebattle);
									var o_invitevs = document.createElement("div");
									o_invitevs.innerHTML = o_language ? "Race":"Course";
									o_invitevs.onclick = function() {
										o_inviteonline(memberID);
										removeCtxtMenu();
										e.stopPropagation();
									}
									var o_invitevs_pts = document.createElement("div");
									o_invitevs_pts.innerHTML = "...";
									o_invitevs.appendChild(o_invitevs_pts);
									o_ctxtmenu2.appendChild(o_invitevs);
									o_liste.appendChild(o_ctxtmenu2);
									o_xhr("getPts.php", "id="+ memberID, function(reponse) {
										if (!reponse) return false;
										var pts;
										try {
											pts = eval(reponse);
										}
										catch (e) {
											return false;
										}
										o_invitevs_pts.innerHTML = pts[0] +" pts";
										o_invitebattle_pts.innerHTML = pts[1] +" pts";
										return true;
									});
								};
							}
							var o_profile = document.createElement("div");
							o_profile.innerHTML = (o_language ? "See profile":"Voir le profil");
							if (!o_profile.dataset)
								o_profile.dataset = {};
							o_profile.dataset.id = memberID;
							o_profile.onclick = function() {
								document.location.href = "profil.php?id="+ this.dataset.id;
							}

							var o_ignore = document.createElement("div");
							o_ignore.innerHTML = o_language ? "Ignore":"Ignorer";
							o_ignore.onclick = function(e) {
								o_confirm(o_language ? "Ignore "+ memberPseudo +"?<br />You won't be able to send or receive messages from him." : "Ignorer "+ memberPseudo +" ?<br />Vous ne pourrez plus envoyer ni recevoir de messages de lui.", function(res) {
									if (res) {
										o_xhr("ignore.php", "member="+ memberID, function(reponse) {
											if (reponse == 1) {
												var ignoreID = oIgnores.indexOf(memberID);
												if (ignoreID == -1) {
													oIgnores.push(memberID);
													o_refreshtable();
													o_stopchat(memberID);
												}
												return true;
											}
											return false;
										});
									}
								});
								removeCtxtMenu();
								e.stopPropagation();
							};

							o_ctxtmenu.appendChild(o_ignore);
							o_ctxtmenu.appendChild(o_profile);
							if (o_invite)
								o_ctxtmenu.appendChild(o_invite);
							o_ctxtmenu.appendChild(o_talk);
						}
						o_liste.appendChild(o_ctxtmenu);
						o_ctxtmenu_y = o_selector.offsetTop-o_table.scrollTop+10-o_ctxtmenu.scrollHeight;
						if (o_ctxtmenu_y <= -o_liste.offsetTop)
							o_ctxtmenu_y += o_ctxtmenu.scrollHeight;
						o_ctxtmenu.style.top = o_ctxtmenu_y +"px";

						setTimeout(function() {
							a_body_click = document.body.onclick;
							document.body.onclick = removeCtxtMenu;
						}, 1);
						e.preventDefault();
						return false;
					}
				};
				o_table.appendChild(o_selector);
			}
		}
		else {
			o_table.className = "noconnect";
			o_table.innerHTML = o_language ? "Nobody is currently connected":"Aucun joueur connect&eacute; pour l'instant";
		}
	}
}

function o_fill_chats(oMask,lastMembers) {
	var oContainer = oMask.getElementsByClassName("o_dialog_msg")[0];
	oContainer.className = "o_chat_lastchats";
	var oH1 = document.createElement("h1");
	oH1.innerHTML = (o_language ? "All my conversations":"Toutes mes conversations") + " ("+ lastMembers.length +")";
	oContainer.appendChild(oH1);
	if (lastMembers.length) {
		var oLastConvs = document.createElement("div");
		oLastConvs.className = "o_chat_lastconvs";
		var oHistory = document.createElement("div");
		oHistory.className = "o_chat_history";
		for (var i=0;i<lastMembers.length;i++) {
			var lastMember = lastMembers[i];
			var oConv = document.createElement("div");
			var oPseudo = document.createElement("h2");
			oPseudo.innerHTML = lastMember[1] +' <em>'+ lastMember[3] +' message'+ (lastMember[3]>=2 ? 's':'') +'</em>';
			oConv.appendChild(oPseudo);
			var oMsg = document.createElement("div");
			if (lastMember[4] == 0)
				oMsg.className = "o_msg_new";
			oMsg.innerHTML = lastMember[2];
			oConv.appendChild(oMsg);
			if (!oConv.dataset)
				oConv.dataset = {};
			oConv.dataset.i = i;
			oConv.onclick = function() {
				var i = this.dataset.i;
				o_launchchat(lastMembers[i][0],lastMembers[i][1]);
				document.body.removeChild(oMask);
			};
			oHistory.appendChild(oConv);
		}
		oLastConvs.appendChild(oHistory);
		oContainer.appendChild(oLastConvs);
	}
	else {
		var oNoConv = document.createElement("div");
		oNoConv.className = "o_noconv";
		oNoConv.innerHTML = o_language ? "No conversation yet" : "Aucune conversation pour l'instant";
		oContainer.appendChild(oNoConv);
	}
	oH1 = document.createElement("h1");
	oH1.innerHTML = (o_language ? "New conversation":"Nouvelle conversation");
	oContainer.appendChild(oH1);
	var oForm = document.createElement("form");
	oForm.className = "o_new_conv";
	var oLabel = document.createElement("label");
	oLabel.setAttribute("for", "o_new_conv_input");
	oLabel.innerHTML = o_language ? "Nick:" : "Pseudo :";
	oForm.appendChild(oLabel);
	var oInput = document.createElement("input");
	oInput.type = "text";
	oInput.setAttribute("size", "15");
	oInput.name = "o_new_conv_input";
	oInput.id = "o_new_conv_input";
	oForm.appendChild(oInput);
	var oValid = document.createElement("input");
	oValid.type = "submit";
	oValid.className = "o_dialog_submit";
	oValid.value = "Ok";
	oForm.appendChild(oValid);
	oForm.onsubmit = function() {
		oMask.onclick();
		var pseudo = oInput.value;
		if (pseudo) {
			function redoChat() {
				o_my_chats(lastMembers);
				var oInput = document.getElementById("o_new_conv_input");
				oInput.value = pseudo;
				oInput.select();
			}
			o_xhr("findIDByPseudo.php", "pseudo="+ pseudo, function(memberID) {
				if (!memberID)
					return false;
				if (memberID == -1)
					o_alert(o_language ? "This nick doesn't exist":"Ce pseudo n'existe pas.", redoChat);
				else if (memberID == -2)
					o_launchchat(o_id,pseudo);
				else
					o_launchchat(memberID,pseudo);
				return true;
			});
		}
		return false;
	};
	oContainer.appendChild(oForm);
}

function o_my_chats(lastMembers) {
	var oMask = o_dialog();
	var oDialog = oMask.getElementsByTagName("div")[0];
	oDialog.style.width = "400px";
	oDialog.style.maxWidth = "100%";
	oDialog.style.maxHeight = "95%";
	oDialog.style.overflowY = "auto";
	var oContainer = oMask.getElementsByClassName("o_dialog_msg")[0];
	if (lastMembers)
		o_fill_chats(oMask,lastMembers);
	else {
		oContainer.innerHTML = o_language ? "Loading..." : "Chargement...";
		o_xhr("getLastChats.php", null, function(lastMembers) {
			if (!lastMembers)
				return false;
			try {
				lastMembers = eval(lastMembers);
			}
			catch (e) {
				return false;
			}
			oContainer.innerHTML = "";
			o_fill_chats(oMask,lastMembers);
			return true;
		});
	}
}

function o_invitesb() {
	if (!o_online) {
		o_alert(o_language ? "You are offline !":"Vous \xEAtes hors-ligne !");
		return;
	}
	if (!document.getElementById("coliste")) {
		var o_liste = document.createElement("div");
		o_liste.id = "coliste";
		var o_close = document.createElement("div");
		o_close.className = "closelister";
		o_close.innerHTML = '<a href="javascript:o_closelist()">&times;</a>';
		o_liste.appendChild(o_close);
		var o_infos = document.createElement("div");
		o_infos.className = "chatinfos";
		o_infos.innerHTML = o_language ? "Chat with other members or invite them in <a href=\"online.php\" target=\"_blank\">online mode</a>.":"Ici, discutez avec les autres membres, ou invitez-les &agrave; vous affronter dans le <a href=\"online.php\" target=\"_blank\">mode en ligne</a>.";
		o_liste.appendChild(o_infos);
		var o_table = document.createElement("div");
		o_table.id = "cotable";
		o_liste.appendChild(o_table);
		var o_decochat = document.createElement("div");
		o_decochat.className = "chatinfos";
		o_decochat.style.textAlign = "center";
		o_decochat.innerHTML = o_language ? '<a href="javascript:o_my_chats()">All my conversations</a>':'<a href="javascript:o_my_chats()">Toutes mes conversations</a>';
		o_liste.appendChild(o_decochat);
		
		document.body.appendChild(o_liste);

		o_refreshtable();
	}
}

function o_inserttags(msg) {
	msg = msg.replace(/(https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&;\/\/=]*))/g, '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>');
	msg = msg.replace(/([a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4})/gi, '<a href="mailto:$1">$1</a>');
	var smileyNames = ['mdr','lol','rire','love','bisou','fete','hb','anniv','coucou','hey','banane','facepalm','tusors','paf','dodo','youpi','tching','magic','fou','super','paratroopa','toad','yoshi','magicien','aie','miam','crepe','guitare','noel','ordi','merci','welcome','help','cool','boulet','snif','raah','genial','bravo','hap','up'];
	for (var i=0;i<smileyNames.length;i++) {
		var newStr = '<img src="images/smileys/smiley'+ i +'.gif" alt="'+ smileyNames[i] +'" />';
		msg = msg.split(":"+smileyNames[i]+":").join(newStr);
	}
	var smileysList = [':)',':D',';)',':O',':P',':S',':(','8)',':$',':}','|)','*['];
	for (var i=0;i<smileysList.length;i++) {
		var newStr = '<img src="images/smileys/smiley'+ i +'.png" class="osmiley" alt="Smiley" />';
		msg = msg.split(smileysList[i]).join(newStr);
		msg = msg.split(smileysList[i].toLowerCase()).join(newStr);
	}
	msg = msg.replace(new RegExp('\\:([1-3]?\\d|4[0-0])\\:', 'g'), '<img src="images/smileys/smiley$1.gif" alt="Smiley $1" />');
	return msg;
}

function o_todate(sqlDate) {
	var ymdhi = /(\d{4})\-(\d{2})\-(\d{2}) (\d{2}):(\d{2}):(\d{2})/g.exec(sqlDate);
	return new Date(ymdhi[1],ymdhi[2]-1,ymdhi[3],ymdhi[4],ymdhi[5],ymdhi[6],0);
}
function o_zerofill(nb) {
	nb += "";
	while (nb.length < 2)
		nb = "0" + nb;
	return nb;
}
function o_tohumandate(d) {
	var now = new Date();
	var isToday = ((now.getDate() == d.getDate()) && (now.getMonth() == d.getMonth()) && (now.getYear() == d.getYear()));
	var strHour = d.getHours() +":"+ o_zerofill(d.getMinutes());
	if (isToday)
		return strHour;
	else if (o_language)
		return o_zerofill(d.getMonth()+1) +"-"+ o_zerofill(d.getDate()) +","+ strHour;
	else
		return o_zerofill(d.getDate()) +"/"+ o_zerofill(d.getMonth()+1) +", "+ strHour;
}

var MSGS_PACKET_SIZE = 15;
var MSGS_PADDINGTOP = 10;
var SCROLL_MARGIN = 8;
function o_addmsg(memberID,msgData,toBeginning) {
	var msgID = msgData[0], sender = msgData[1], msgContent = msgData[2], datehour = msgData[3];
	var o_chat = document.getElementById("chatwindow"+ memberID);
	var o_list = o_chat.getElementsByClassName("chatmsgs")[0];
	var o_msg = document.createElement("div");
	o_msg.className = "chatmsg "+ ((sender==memberID) ? "coreceiver":"cosender");
	o_initdataset(o_msg);
	o_msg.dataset.id = msgID;
	var o_msgdate = o_todate(datehour);
	o_msg.dataset.date = o_msgdate.getTime();
	var o_msgcontent = document.createElement("div");
	o_msgcontent.innerHTML = o_inserttags(msgContent);
	o_msg.appendChild(o_msgcontent);
	var o_msghour = document.createElement("div");
	o_msghour.className = "chathour";
	o_msghour.innerHTML = o_tohumandate(o_msgdate);
	o_msg.appendChild(o_msghour);
	if (toBeginning)
		o_list.insertBefore(o_msg, o_list.firstChild);
	else
		o_list.appendChild(o_msg);
	return o_msg;
}
function o_insertmsgs(memberID,messages) {
	if (!messages.length)
		return;
	var o_chat = document.getElementById("chatwindow"+ memberID);
	var o_list = o_chat.getElementsByClassName("chatmsgs")[0];
	var o_msgs = o_chat.getElementsByClassName("chatmsg");
	var are_msgs = (o_msgs.length > 0);
	var prevHeight = o_list.scrollHeight;
	if (!are_msgs)
		prevHeight = 0;
	for (var i=0;i<messages.length;i++)
		o_addmsg(memberID,messages[i],true);
	o_updatechatactivity(o_chat);
	var scrollMore = o_list.scrollHeight - prevHeight;
	var deltaScroll = are_msgs ? 1:100;
	if (messages.length < MSGS_PACKET_SIZE) {
		o_list.className += " fullyloaded";
		if (o_list.scrollTop < MSGS_PADDINGTOP)
			scrollMore -= MSGS_PADDINGTOP;
		setTimeout(function() {
			o_list.scrollTop += scrollMore;
		}, deltaScroll);
	}
	setTimeout(function() {
		o_list.scrollTop += scrollMore;
		o_list.onscroll = function() {
			if (o_list.scrollTop < MSGS_PADDINGTOP) {
				o_list.onscroll = null;
				o_loadprevious(memberID);
			}
		};
	}, deltaScroll);
}
function o_appendmsgs(memberID,messages) {
	var o_chat = document.getElementById("chatwindow"+ memberID);
	var o_list = o_chat.getElementsByClassName("chatmsgs")[0];
	var isBottomScrolled = (o_list.scrollTop>=(o_list.scrollHeight-o_list.clientHeight-SCROLL_MARGIN));
	var lastID = o_lastmsgid(o_chat);
	var isNewMsg = false;
	for (var i=0;i<messages.length;i++) {
		if (messages[i][0] > lastID) {
			var o_msg = o_addmsg(memberID,messages[i],false);
			if (messages[i][1] == memberID)
				isNewMsg = true;
			else
				isNewMsg = false;
		}
	}
	if (isNewMsg) {
		if (!o_focused) {
			displayTitle(o_language ? ('New message from '+ o_chat.dataset.pseudo):('Nouveau message de '+ o_chat.dataset.pseudo));
			o_newmsgsound();
		}
	}
	if (messages.length)
		o_updatechatactivity(o_chat);
	if (isBottomScrolled)
		o_list.scrollTop = o_list.scrollHeight;
}
function o_notify_msg(key,pseudo,msg) {
	if (!o_focused) {
		msg = msg.replace(/&#039;/g, "'").replace(/&quot;/g, '"').replace("<br />", "\n").replace(/&gt;/g, ">").replace(/&lt;/g, "<").replace(/&amp;/g, "&");
		b_notify("o_msg."+key,pseudo,msg);
	}
}
function o_notify_online(key,pseudo,battle) {
	if (!o_focused)
		b_notify("o_online."+key,pseudo, (o_language ? 'Wants to play an online '+(battle?"battle":"race")+' with you.' : 'Propose de vous affronter sur une '+(battle?"bataille":"course")+' en ligne.'));
}
function o_newmsgsound() {
	if (o_active) {
		if (!o_focused) {
			var pEmbed = document.getElementsByTagName("embed");
			if (pEmbed.length)
				document.body.removeChild(pEmbed[0]);
			var newMsg = document.createElement("embed");
			newMsg.src = "musics/new.mp3";
			newMsg.setAttribute("autostart", "true");
			newMsg.style.position = "absolute";
			newMsg.style.left = "-1000px";
			newMsg.style.top = "-1000px";
			document.body.appendChild(newMsg);
		}
	}
}
function o_loadprevious(memberID) {
	var o_chat = document.getElementById("chatwindow"+ memberID);
	var o_msgs = o_chat.getElementsByClassName("chatmsg");
	var lastID = o_msgs[0].dataset.id;
	o_xhr("loadprevious.php", "member="+ memberID +"&lastID="+ lastID, function(reponse) {
		if (!reponse)
			return false;
		try {
			reponse = eval(reponse);
		}
		catch (e) {
			return false;
		}
		o_insertmsgs(memberID,reponse);
		return true;
	});
}
function o_lastmsgid(o_chat) {
	var o_msgs = o_chat.getElementsByClassName("chatmsg");
	if (o_msgs.length)
		return o_msgs[o_msgs.length-1].dataset.id;
	return 0;
}

var o_chats = [];
var o_chats_ctn;
function o_launchchat(memberID,memberPseudo) {
	memberID = +memberID;
	o_xhr("startchat.php", "member="+ memberID, function(reponse) {
		if (!reponse)
			return false;
		try {
			reponse = eval(reponse);
		}
		catch (e) {
			return false;
		}
		var ignoreID = oIgnores.indexOf(memberID);
		if (ignoreID != -1) {
			oIgnores.splice(ignoreID,1);
			o_refreshtable();
		}
		o_startchat({
			member: {
				id: memberID,
				pseudo: memberPseudo
			},
			messages: reponse,
			autofocus: true
		});
		return true;
	});
}
function o_updateinputsize(o_input) {
	o_input.style.height = "";
	if (o_input.value)
		o_input.style.height = (o_input.scrollHeight+4) +"px";
}
function o_startchat(opts) {
	var memberID = opts.member.id;
	var pseudo = opts.member.pseudo;
	var messages = opts.messages;
	var o_chat = document.getElementById("chatwindow"+ memberID);
	if (o_chat) {
		if (o_chat.dataset.reduced)
			o_restorechat(o_chat);
		return;
	}
	o_chat = document.createElement("div");
	o_chat.className = "chatwindow";
	o_chat.id = "chatwindow"+ memberID;
	o_initdataset(o_chat);
	o_chat.dataset.member = memberID;
	o_chat.dataset.pseudo = pseudo;
	o_chat.dataset.typing = -1;
	if (opts.reduced)
		o_reducechat(o_chat);
	var o_header = document.createElement("div");
	o_header.className = "chatbar";
	var o_title = document.createElement("div");
	o_title.innerHTML = '<span class="chatbullet">\u2022</span><a href="profil.php?id='+ memberID +'">'+ pseudo +'</a>';
	var o_chat_x = 0;
	var o_chat_y = 0;
	o_chat.style.left = o_chat_x +"px";
	o_chat.style.top = o_chat_y +"px";
	o_header.onmousedown = function(e) {
		function moveWindow(e2) {
			o_chat.style.left = (o_chat_x + e2.clientX-e.clientX) +"px";
			o_chat.style.top = (o_chat_y + e2.clientY-e.clientY) +"px";
			e2.preventDefault();
		};
		function releaseWindow(e2) {
			o_chat_x += (e2.clientX-e.clientX);
			o_chat_y += (e2.clientY-e.clientY);
			o_chat.style.left = o_chat_x +"px";
			o_chat.style.top = o_chat_y +"px";
			document.removeEventListener("mousemove", moveWindow);
			document.removeEventListener("mouseup", releaseWindow);
			e2.preventDefault();
		};
		document.addEventListener("mousemove", moveWindow);
		document.addEventListener("mouseup", releaseWindow);
	};
	o_header.appendChild(o_title);
	var o_close = document.createElement("a");
	o_close.href = "#null";
	o_close.className = "close";
	o_close.innerHTML = "&times;";
	o_close.onmousedown = function(e) {
		e.stopPropagation();
	}
	o_close.onclick = function() {
		o_closechat(o_chat);
		var lastID = o_lastmsgid(o_chat);
		o_xhr("stopchat.php", "member="+ memberID +"&lastID="+ lastID, function(reponse) {
			return (reponse == 1);
		});
		return false;
	};
	o_header.appendChild(o_close);
	var o_reduce = document.createElement("a");
	o_reduce.href = "#null";
	o_reduce.className = "reduce";
	o_reduce.innerHTML = "_";
	o_reduce.onmousedown = function(e) {
		e.stopPropagation();
	}
	o_reduce.onclick = function() {
		o_reducechat(o_chat);
		o_xhr("reducechat.php", "member="+ memberID +"&state=1", function(reponse) {
			return (reponse == 1);
		});
		return false;
	};
	o_header.appendChild(o_reduce);
	var o_restore = document.createElement("a");
	o_restore.href = "#null";
	o_restore.className = "restore";
	o_restore.innerHTML = "&#9633;";
	o_restore.onmousedown = function(e) {
		e.stopPropagation();
	}
	o_restore.onclick = function() {
		o_restorechat(o_chat);
		return false;
	};
	o_header.appendChild(o_restore);
	o_chat.appendChild(o_header);
	o_changestatut(o_chat);
	var o_list = document.createElement("div");
	o_list.className = "chatmsgs";
	o_chat.appendChild(o_list);

	var o_writting = document.createElement("div");
	o_writting.className = "chatwritting";
	o_writting.innerHTML = pseudo + (o_language ? " is typing..." : " écrit...");
	o_chat.appendChild(o_writting);

	var o_answer = document.createElement("div");
	o_answer.className = "chatans";
	var o_input = document.createElement("textarea");
	o_input.name = "o_msginput";
	o_input.setAttribute("rows", "1");
	function sendMsg() {
		o_resettitle();
		var answerValue = o_input.value;
		if (answerValue) {
			o_input.value = "";
			o_updateinputsize(o_input);
			var lastID = o_lastmsgid(o_chat);
			o_chat.dataset.typing = -1;
			o_xhr("sendmsg.php", "member="+ memberID +"&message="+ encodeURIComponent(answerValue) +"&lastID="+ lastID, function(reponse) {
				if (!reponse)
					return false;
				try {
					reponse = eval(reponse);
				}
				catch (e) {
					return false;
				}
				o_appendmsgs(memberID,reponse,true);
				o_chat.dataset.typing = (o_chat.getElementsByTagName("textarea")[0].value ? 1:0);
				return true;
			});
		}
	}
	o_input.onkeydown = function(e) {
		if ((e.keyCode == 13) && !e.shiftKey) {
			sendMsg();
			return false;
		}
		e.stopPropagation();
	};
	o_input.oninput = function(e) {
		o_chat.dataset.typing = o_input.value ? 1:0;
		o_updateinputsize(this);
	};
	o_answer.appendChild(o_input);
	o_chat.appendChild(o_answer);
	o_chats_ctn.insertBefore(o_chat, o_chats_ctn.firstChild);
	o_chats.push(o_chat);
	o_insertmsgs(memberID,messages);
	o_updateinputsize(o_input);
	o_input.onfocus = function() {
		o_resettitle();
	};
	if (opts.autofocus)
		o_input.focus();
}
function o_stopchat(memberID) {
	var o_chat = document.getElementById("chatwindow"+ memberID);
	if (o_chat)
		o_closechat(o_chat);
}
function o_closechat(o_chat) {
	o_chats_ctn.removeChild(o_chat);
	if (o_chats.indexOf)
		o_chats.splice(o_chats.indexOf(o_chat),1);
}
function o_reducechat(o_chat) {
	if (o_chat.className.indexOf("chatreduced") == -1)
		o_chat.className += " chatreduced";
	o_chat.dataset.reduced = 1;
}
function o_restorechat(o_chat) {
	o_chat.className = o_chat.className.replace(" chatreduced", "");
	o_chat.dataset.reduced = "";
	var o_list = o_chat.getElementsByClassName("chatmsgs")[0];
	o_list.scrollTop = o_list.scrollHeight;
	var o_input = o_chat.querySelector('textarea[name="o_msginput"]');
	if (o_input) o_input.focus();
	o_xhr("reducechat.php", "member="+ o_chat.dataset.member +"&state=0", function(reponse) {
		return (reponse == 1);
	});
}
function o_updatestatut(memberID) {
	var o_chat = document.getElementById("chatwindow"+ memberID);
	if (o_chat)
		o_changestatut(o_chat);
}
function o_changestatut(o_chat) {
	var o_bullet = o_chat.getElementsByClassName("chatbullet")[0];
	var memberID = o_chat.dataset.member*1;
	var memberStatus;
	switch (o_getstatus(memberID)) {
	case 0:
		memberStatus = "coffline";
		break;
	case 1:
		memberStatus = "comuted";
		break;
	case 2:
		memberStatus = "conline";
		break;
	}
	o_bullet.className = "chatbullet "+ memberStatus;
}
function o_isconnected(memberID) {
	if (memberID == o_id)
		return true;
	for (var i=0;i<oListJoueurs.length;i++) {
		if (oListJoueurs[i][0] == memberID)
			return true;
	}
	return false;
}
function o_getstatus(memberID) {
	if (memberID == o_id)
		return o_online+o_active;
	for (var i=0;i<oListJoueurs.length;i++) {
		if (oListJoueurs[i][0] == memberID)
			return oListJoueurs[i][2];
	}
	return 0;
}

function o_closelist() {
	document.body.removeChild(document.getElementById("coliste"));
}
function close_jco(o_msgId) {
	document.body.removeChild(document.getElementById("comsg"+ o_msgId));
	for (var i=o_msgId+1;document.getElementById("comsg"+ i);i++) {
		document.getElementById("comsg"+ i).getElementsByTagName("a")[0].href = "javascript:close_jco("+ (i-1) +")";
		document.getElementById("comsg"+ i).style.bottom = (40+50*i)+"px";
		document.getElementById("comsg"+ i).id = "comsg"+ (i-1);
	}
	o_resettitle();
}
function close_allco() {
	var comsgs = document.getElementsByClassName("comsg");
	for (var i=comsgs.length-1;i>=0;i--)
		document.body.removeChild(comsgs[i]);
	o_resettitle();
}

function o_resettitle() {
	o_msg_id++;
	document.title = o_title;
}

function setDecoLink() {
	document.getElementById("statutco").className = o_online ? (o_active?'isconnected':'ismuted'):'isnotconnected';
}
function setDecoStatus(n_online) {
	o_online = (n_online>0) ? 1:0;
	o_active = (n_online==2) ? 1:0;
}
var o_msg_id = 0;
var o_title = "";
function displayTitle(m) {
	if (o_active) {
		m = m.replace(/<[^>]+\>/g, "");
		m = m.replace(/&nbsp;/g, " ");
		m = m.replace(/&([a-zA-Z])[a-zA-Z]+;/g, "$1");
		document.title = m;
		o_msg_id++;
		var aMsgId = o_msg_id;
		var status = 0;
		function blinkTitle() {
			if (o_msg_id == aMsgId) {
				switch (status) {
				case 0 :
					document.title = m;
					setTimeout(blinkTitle, 1000);
					status = 1;
					break;
				case 1 :
					document.title = o_title;
					setTimeout(blinkTitle, 1000);
					status = 0;
				}
			}
		}
		blinkTitle();
	}
}
function displayMsg(m,changetitle) {
	if (o_active) {
		var o_msgId = document.getElementsByClassName("comsg").length;
		var o_comsgdiv = document.createElement("div");
		o_initdataset(o_comsgdiv);
		o_comsgdiv.className = "comsg";
		o_comsgdiv.id = "comsg"+ o_msgId;
		o_comsgdiv.style.bottom = (90 + 50*o_msgId) +"px";
		var o_close = document.createElement("div");
		o_close.className = "closelister";
		o_close.innerHTML = '<a href="javascript:close_jco('+ o_msgId +')">&times;</a>';
		o_comsgdiv.appendChild(o_close);
		var o_msg = document.createElement("div");
		o_msg.className = "comsg-value";
		o_msg.innerHTML = m;
		o_comsgdiv.appendChild(o_msg);
		document.body.appendChild(o_comsgdiv);
		if (changetitle)
			displayTitle(m);
		return o_msgId;
	}
	return -1;
}

function o_deco() {
	var n_online = document.getElementById("statutco").selectedIndex;
	var a_online = o_online+o_active;
	if (n_online != a_online) {
		setDecoStatus(n_online);
		setDecoLink();
		o_xhr("codeco.php", "connecte="+ n_online, function(reponse) {
			if (reponse == "1") {
				o_refreshstate = -1;
				if (!a_online)
					o_chatrefresh();
				return true;
			}
			return false;
		});
		if (!o_online) {
			if (o_handler) {
				clearTimeout(o_handler);
				o_handler = null;
			}
			for (var i=o_chats.length-1;i>=0;i--)
				o_closechat(o_chats[i]);
			if (document.getElementById("coliste"))
				o_closelist();
		}
		if (!o_active) {
			close_allco();
			oConvs.length = 0;
		}
	}
}
function o_getCookie(name) {
	const value = `; ${document.cookie}`;
	const parts = value.split(`; ${name}=`);
	if (parts.length === 2) return parts.pop().split(';').shift();
}
function o_getLanguage() {
	var res = o_getCookie("language");
	if (isNaN(res)) return 1;
	return +res;
}

var o_loaded = false;
var o_pseudo, o_language = +o_getLanguage();
var o_online, o_active, oListJoueurs = new Array(), oDemandes = new Array(), oReponses = new Array(), oConvs = new Array(), oIgnores = new Array(), oLastActivities = {};
var o_handler;
function o_loadOnline() {
	if (o_loaded)
		return;
	o_loaded = true;
	o_title = document.title;
	if (!document.getElementsByClassName) {
		function findByClass(classname) {
			var a = [];
		    var re = new RegExp('(^| )'+classname+'( |$)');
		    var els = this.getElementsByTagName("*");
		    for(var i=0,j=els.length; i<j; i++)
		        if(re.test(els[i].className))a.push(els[i]);
		    return a;
		}
		document.getElementsByClassName = findByClass;
		var all = document.getElementsByTagName("*");
		for (var i=0;i<all.length;i++)
			all[i].getElementsByClassName = findByClass;
		Element.prototype.getElementsByClassName = findByClass;
	}
	o_xhr("getpseudo.php", "", function(reponse) {
		if (reponse) {
			var oPseudoInfos;
			try {
				oPseudoInfos = eval(reponse);
			}
			catch (e) {
				return false;
			}
			if (!oPseudoInfos.length)
				return true;
			o_id = oPseudoInfos[0];
			o_pseudo = oPseudoInfos[1];
			o_language = oPseudoInfos[2];
			setDecoStatus(oPseudoInfos[3]);
			var o_div = document.createElement("div");
			o_div.id = "connect";
			o_div.innerHTML = '<div id="pseudostatus"><a title="'+ (o_language ? 'See my profile':'Voir mon profil') +'" href="profil.php?id='+ oPseudoInfos[0] +'">'+ oPseudoInfos[1] +'</a> : <select id="statutco" class="isnotconnected" onchange="o_deco()"><option value="0" class="isnotconnected">'+ (o_language ? 'Offline':'Hors-ligne') +'</option><option value="1" class="ismuted">'+ (o_language ? 'Do not disturb':'Ne pas d&eacute;ranger') +'</option><option value="2" class="isconnected">'+ (o_language ? 'Connected':'Connect&eacute;') +'</option></select></div><a class="coselectable" href="javascript:o_invitesb()">'+ (o_language ? 'Members Area':'Espace membres') +'</a><a class="coselectable" href="https://discord.gg/VkeAxaj" target="_blank">'+ (o_language ? 'Discord Server':'Serveur Discord') +'</a>';
			o_chats_ctn = document.createElement("div");
			o_chats_ctn.id = "chatsctn";
			document.body.appendChild(o_chats_ctn);
			var o_convs = oPseudoInfos[4];
			for (var i=0;i<o_convs.length;i++) {
				var o_conv = o_convs[i];
				o_startchat({
					member: {
						id: o_conv[0],
						pseudo: o_conv[1]
					},
					messages: o_conv[2],
					reduced: o_conv[3]
				});
			}
			oIgnores = oPseudoInfos[5];
			o_xhr("coreload.php", "", function(reponse) {
				if (reponse) {
					var oJoueursInfos;
					try {
						oJoueursInfos = eval(reponse);
					}
					catch (e) {
						return false;
					}
					oListJoueurs = oJoueursInfos[0];
					for (var i=0;i<oListJoueurs.length;i++) {
						var o_chat = document.getElementById("chatwindow"+ oListJoueurs[i][0]);
						if (o_chat) {
							o_changestatut(o_chat);
							o_updatechatactivity(o_chat);
						}
					}
					document.body.appendChild(o_div);
					document.getElementById("statutco").selectedIndex = oPseudoInfos[3];
					document.getElementById("statutco").style.display = "inline-block";
					setDecoLink();
					o_refreshstate = 0;
					o_chatrefresh();
					return true;
				}
				return false;
			});
			if (window.Notification && (Notification.permission !== "granted"))
				Notification.requestPermission();
			return true;
		}
		return false;
	});
}

function inArray0(a,elt) {
	for (var i=0;i<a.length;i++) {
		if (a[i][0] == elt)
			return true;
	}
	return false;
}
function indexOf0(a,elt) {
	for (var i=0;i<a.length;i++) {
		if (a[i][0] == elt)
			return a[i];
	}
	return null;
}
function inArray03(a,elt) {
	for (var i=0;i<a.length;i++) {
		if ((a[i][0] == elt[0]) && (a[i][3] == elt[3]))
			return true;
	}
	return false;
}
function inArray04(a,elt) {
	for (var i=0;i<a.length;i++) {
		if ((a[i][0] == elt[0]) && (a[i][4] == elt[4]))
			return true;
	}
	return false;
}

function o_prompt(msg,hint, onValid) {
	var oMask = document.createElement("div");
	oMask.className = "o_mask";
	var oDialog = document.createElement("form");
	oDialog.method = "POST";
	oDialog.action = "";
	oDialog.className = "o_dialog_box";
	oDialog.onsubmit = function() {
		document.body.removeChild(oMask);
		onValid(oInput.value);
		return false;
	}
	oDialog.onclick = function(event) {
		event.stopPropagation();
	}
	var oCross = document.createElement("a");
	oCross.className = "o_dialog_cross";
	oCross.href = "null";
	oCross.innerHTML = "&times";
	oDialog.appendChild(oCross);
	var oMessage = document.createElement("div");
	oMessage.className = "o_dialog_msg";
	oMessage.innerHTML = msg;
	oDialog.appendChild(oMessage);
	var oInputCtn = document.createElement("div");
	oInputCtn.className = "o_dialog_input";
	var oInput = document.createElement("input");
	oInput.type = "text";
	oInput.placeholder = hint;
	oInput.onkeydown = function(event) {
		if (event.keyCode == 27) {
			document.body.removeChild(oMask);
			return false;
		}
	}
	oInputCtn.appendChild(oInput);
	oDialog.appendChild(oInputCtn);
	var oButtonCtn = document.createElement("div");
	oButtonCtn.className = "o_dialog_buttons";
	var oValid = document.createElement("input");
	oValid.type = "submit";
	oValid.className = "o_dialog_submit";
	oValid.value = "Ok";
	oButtonCtn.appendChild(oValid);
	var oCancel = document.createElement("input");
	oCancel.type = "button";
	oCancel.value = o_language ? "Cancel":"Annuler";
	oCancel.className = "o_dialog_cancel";
	oCancel.onclick = oMask.onclick = oCross.onclick = function() {
		document.body.removeChild(oMask);
		return false;
	};
	oButtonCtn.appendChild(oCancel);
	oDialog.appendChild(oButtonCtn);
	oMask.appendChild(oDialog);
	document.body.appendChild(oMask);
	setTimeout(function() {
		oInput.focus();
	}, 1);
	return oMask;
}
function o_confirm(msg, onValid) {
	var oMask = document.createElement("div");
	oMask.className = "o_mask";
	var oDialog = document.createElement("div");
	oDialog.className = "o_dialog_box";
	oDialog.onclick = function(event) {
		event.stopPropagation();
	}
	var oCross = document.createElement("a");
	oCross.className = "o_dialog_cross";
	oCross.href = "null";
	oCross.innerHTML = "&times";
	oDialog.appendChild(oCross);
	var oMessage = document.createElement("div");
	oMessage.className = "o_dialog_msg";
	oMessage.innerHTML = msg;
	oDialog.appendChild(oMessage);
	var oButtonCtn = document.createElement("div");
	oButtonCtn.className = "o_dialog_buttons";
	var oValid = document.createElement("input");
	oValid.type = "button";
	oValid.value = "Ok";
	oValid.className = "o_dialog_submit";
	oValid.onclick = function() {
		document.body.removeChild(oMask);
		onValid(true);
		return false;
	};
	oButtonCtn.appendChild(oValid);
	var oCancel = document.createElement("input");
	oCancel.type = "button";
	oCancel.value = o_language ? "Cancel":"Annuler";
	oCancel.className = "o_dialog_cancel";
	oCancel.onclick = oMask.onclick = oCross.onclick = function() {
		document.body.removeChild(oMask);
		onValid(false);
		return false;
	};
	oButtonCtn.appendChild(oCancel);
	oDialog.appendChild(oButtonCtn);
	oMask.appendChild(oDialog);
	document.body.appendChild(oMask);
	setTimeout(function() {
		oValid.focus();
	}, 1);
	return oMask;
}
function o_dialog() {
	var oMask = document.createElement("div");
	oMask.className = "o_mask";
	var oDialog = document.createElement("div");
	oDialog.className = "o_dialog_box";
	oDialog.onclick = function(event) {
		event.stopPropagation();
	}
	var oCross = document.createElement("a");
	oCross.className = "o_dialog_cross";
	oCross.href = "null";
	oCross.innerHTML = "&times";
	oMask.onclick = oCross.onclick = function() {
		document.body.removeChild(oMask);
		if (document.onkeydown == nKeydown)
			document.onkeydown = aKeydown;
		return false;
	};
	oDialog.appendChild(oCross);
	var oMessage = document.createElement("div");
	oMessage.className = "o_dialog_msg";
	oDialog.appendChild(oMessage);
	oMask.appendChild(oDialog);
	document.body.appendChild(oMask);
	var aKeydown = document.onkeydown;
	var nKeydown = function(e) {
		if (e.keyCode == 27) {
			document.body.removeChild(oMask);
			if (document.onkeydown == nKeydown)
				document.onkeydown = aKeydown;
			return false;
		}
	};
	document.onkeydown = nKeydown;
	return oMask;
}
function o_alert(msg, onValid) {
	if (!onValid)
		onValid = function(){};
	var oMask = document.createElement("div");
	oMask.className = "o_mask";
	var oDialog = document.createElement("div");
	oDialog.className = "o_dialog_box";
	oDialog.onclick = function(event) {
		event.stopPropagation();
	}
	var oCross = document.createElement("a");
	oCross.className = "o_dialog_cross";
	oCross.href = "null";
	oCross.innerHTML = "&times";
	oDialog.appendChild(oCross);
	var oMessage = document.createElement("div");
	oMessage.className = "o_dialog_msg";
	oMessage.innerHTML = msg;
	oDialog.appendChild(oMessage);
	var oButtonCtn = document.createElement("div");
	oButtonCtn.className = "o_dialog_buttons";
	var oValid = document.createElement("input");
	oValid.type = "button";
	oValid.value = "Ok";
	oValid.className = "o_dialog_submit";
	oValid.onclick = function() {
		document.body.removeChild(oMask);
		onValid();
		return false;
	};
	oButtonCtn.appendChild(oValid);
	oMask.onclick = oCross.onclick = function() {
		document.body.removeChild(oMask);
		onValid();
		return false;
	};
	oDialog.appendChild(oButtonCtn);
	oMask.appendChild(oDialog);
	document.body.appendChild(oMask);
	setTimeout(function() {
		oValid.focus();
	}, 1);
	return oMask;
}
function o_repond(dem,name,rep,elt,battle) {
	o_prompt(rep ? (o_language ? "Send an additionnal message to "+ name +" (optional)":"Envoyer un message en compl\xE9ment (facultatif)") : (o_language ? "Send a message to explain your rejection (optional)":"Envoyer un message pour expliquer votre refus (facultatif)"),
		o_language ? "Leave empty to send nothing":"Laisser vide pour ne rien envoyer",
		function(msg) {
			o_send_answer(dem,rep,msg,battle);
			deleteCross(elt);
		});
}
function o_disappear(o_msg,time) {
	if (undefined === time)
		time = 7000;
	var o_fhandler;
	function o_fadeout(o_msg) {
		if (document.body.contains(o_msg)) {
			var opacity = o_msg.style.opacity;
			opacity -= 0.05;
			if (opacity > 0) {
				o_msg.style.opacity = opacity;
				o_fhandler = setTimeout(function() {
					o_fadeout(o_msg);
				}, 100);
			}
			else
				eval(o_msg.getElementsByTagName("a")[0].href);
		}
	}
	function o_postfade(o_msg,time) {
		o_fhandler = setTimeout(function() {
			o_msg.style.opacity = 1;
			o_fadeout(o_msg);
		}, time);
	}
	o_msg.onmouseover = function() {
		o_msg.style.opacity = 1;
		clearTimeout(o_fhandler);
	}
	o_msg.onmouseout = function() {
		o_postfade(o_msg,time);
	}
	o_postfade(o_msg,time);
}
function o_send_answer(dem,rep,msg,battle) {
	o_xhr("repond.php", "demande="+ dem +"&rep="+ rep +"&msg="+ encodeURIComponent(msg) + (battle ? "&battle":""), function(reponse) {
		if (reponse == 1) {
			if (rep)
				displayMsg(o_language ? 'Invitation accepted! <a href="online.php'+(battle?"?battle":"")+'">Click here</a> to go to the online mode!':'Demande accept&eacute;e&nbsp;! <a href="online.php'+(battle?"?battle":"")+'">Cliquez ici</a> pour acc&eacute;der au mode en ligne&nbsp;!', false);
			return true;
		}
		return false;
	});
}
function deleteCross(elt) {
	for (var i=0;document.getElementById("comsg"+ i);i++) {
		var o_inputs = document.getElementById("comsg"+ i).getElementsByTagName("input");
		for (var j=0;j<o_inputs.length;j++) {
			if (o_inputs[j] == elt) {
				eval(document.getElementById("comsg"+ i).getElementsByTagName("a")[0].href);
				break;
			}
		}
	}
}

var o_refreshstate = 0;
function o_refresh() {
	var data = new Array();
	for (var i=0;i<o_chats.length;i++) {
		var o_chat = o_chats[i];
		o_addmsgdata(i,data,o_chat);
	}
	o_xhr("coreload.php", data.join("&"), function(reponse) {
		if (reponse) {
			var oJoueursInfos;
			try {
				oJoueursInfos = eval(reponse);
			}
			catch (e) {
				return false;
			}
			var newPlayers = [];
			var oldPlayers = [];
			var updatedPlayers = [];
			for (var i=0;i<oJoueursInfos[0].length;i++) {
				var currentPlayer = indexOf0(oListJoueurs,oJoueursInfos[0][i][0]);
				if (!currentPlayer)
					newPlayers.push(oJoueursInfos[0][i]);
				else if (currentPlayer[2] != oJoueursInfos[0][i][2])
					updatedPlayers.push(oJoueursInfos[0][i]);
			}
			for (var i=0;i<oListJoueurs.length;i++) {
				if (!inArray0(oJoueursInfos[0],oListJoueurs[i][0]))
					oldPlayers.push(oListJoueurs[i]);
			}
			oListJoueurs = oJoueursInfos[0];
			for (var i=0;i<newPlayers.length;i++) {
				var memberID = newPlayers[i][0];
				o_updateactivity(memberID, 15);
				/*if ((oIgnores.indexOf(memberID) == -1) && (newPlayers[i][2] == 2)) {
					var comsgs = document.getElementsByClassName("comsg");
					for (var j=comsgs.length-1;j>=0;j--) {
						if (comsgs[j].dataset.connectID == memberID)
							eval(comsgs[j].getElementsByTagName("a")[0].href);
					}
					var o_msgId = displayMsg(o_language ? '<b>'+ newPlayers[i][1] +'</b> just connected<br /><input type="button" class="cotalk" value="Talk" /> <input type="button" class="coinvite" value="Invite online" />':'<b>'+ newPlayers[i][1] +'</b> vient de se connecter<br /><br /><input type="button" class="cotalk" value="Discuter" /> <input type="button" class="coinvite" value="Inviter en online" />', false);
					if (o_msgId != -1) {
						var o_msg = document.getElementById("comsg"+ o_msgId);
						o_disappear(o_msg);
						o_msg.dataset.connectID = memberID;
						var o_talk = o_msg.getElementsByClassName("cotalk")[0];
						o_initdataset(o_talk);
						o_talk.dataset.member = newPlayers[i][0];
						o_talk.dataset.pseudo = newPlayers[i][1];
						o_talk.onclick = function() {
							o_launchchat(this.dataset.member,this.dataset.pseudo);
							deleteCross(this);
						};
						var o_invite = o_msg.getElementsByClassName("coinvite")[0];
						o_initdataset(o_invite);
						o_invite.dataset.member = newPlayers[i][0];
						o_invite.onclick = function() {
							o_inviteonline(this.dataset.member);
							deleteCross(this);
						};
					}
				}*/
				o_updatestatut(memberID);
			}
			for (var i=0;i<oldPlayers.length;i++) {
				var memberID = oldPlayers[i][0];
				o_stopactivity(memberID);
				/*if ((oIgnores.indexOf(memberID) == -1) && (oldPlayers[i][2] == 2)) {
					var comsgs = document.getElementsByClassName("comsg");
					for (var j=comsgs.length-1;j>=0;j--) {
						if (comsgs[j].dataset.connectID == memberID)
							eval(comsgs[j].getElementsByTagName("a")[0].href);
					}
					var o_msgId = displayMsg(o_language ? "<b>"+ oldPlayers[i][1] +"</b> just disconnected":"<b>"+ oldPlayers[i][1] +"</b> vient de se d&eacute;connecter", false);
					if (o_msgId != -1) {
						var o_msg = document.getElementById("comsg"+ o_msgId);
						o_disappear(o_msg);
						o_msg.dataset.connectID = memberID;
					}
				}*/
				o_updatestatut(memberID);
			}
			for (var i=0;i<updatedPlayers.length;i++)
				o_updatestatut(updatedPlayers[i][0]);
			if (newPlayers.length || oldPlayers.length || updatedPlayers.length)
				o_refreshtable();
			var newDemandes = [];
			for (var i=0;i<oJoueursInfos[1].length;i++) {
				if (!inArray03(oDemandes,oJoueursInfos[1][i]))
					newDemandes.push(oJoueursInfos[1][i]);
			}
			for (var i=0;i<newDemandes.length;i++) {
				o_updateactivity(newDemandes[i][0], 10);
				var o_msgId = displayMsg(o_language ? '<b>'+ newDemandes[i][1] +'</b> ('+ newDemandes[i][2] +' pts) wants to play an online <b>'+(newDemandes[i][3]?"battle":"race")+'</b> with you<br /><input type="button" value="Accept" onclick="o_send_answer('+ newDemandes[i][0] +',1,\'\','+ newDemandes[i][3] +');deleteCross(this)" /> - <input type="button" value="Reject" onclick="o_repond('+ newDemandes[i][0] +',\''+ newDemandes[i][1] +'\',0,this,'+ newDemandes[i][3] +')" />':'<b>'+ newDemandes[i][1] +'</b> ('+ newDemandes[i][2] +' pts) propose de vous affronter sur une <b>'+(newDemandes[i][3]?"bataille":"course")+'</b> en ligne<br /><input type="button" value="Accepter" onclick="o_send_answer('+ newDemandes[i][0] +',1,\'\','+ newDemandes[i][3] +');deleteCross(this)" /> - <input type="button" value="Refuser" onclick="o_repond('+ newDemandes[i][0] +',\''+ newDemandes[i][1] +'\',0,this,'+ newDemandes[i][3] +')" />', true);
				if (o_msgId != -1) {
					var cross = document.getElementById("comsg"+ o_msgId).getElementsByTagName("a")[0];
					cross.player = newDemandes[i][0];
					cross.battle = newDemandes[i][3];
					cross.onclick = function() {
						o_send_answer(this.player,0,"",this.battle);
					}
					o_notify_online(newDemandes[i][0],newDemandes[i][1],newDemandes[i][3]);
				}
			}
			if (newDemandes.length)
				o_newmsgsound();
			oDemandes = oJoueursInfos[1];
			var newReponses = [];
			for (var i=0;i<oJoueursInfos[2].length;i++) {
				if (!inArray04(oReponses,oJoueursInfos[2][i]))
					newReponses.push(oJoueursInfos[2][i]);
			}
			for (var i=0;i<newReponses.length;i++) {
				var a_active = o_active;
				o_active = 1;
				var o_msgId = displayMsg(newReponses[i][2] ? (o_language ? newReponses[i][1] +' <b>accepted</b> your invitation'+ (newReponses[i][3] ? ' with the following messaage: <p class="o_msg"><span>'+ newReponses[i][3] +'</span></p>':'! ') +'<a href="online.php'+(newReponses[i][4]?'?battle':'')+'">Click here</a> to go to the online mode!':newReponses[i][1] +'</b> a <b>accept&eacute;</b> votre demande'+ (newReponses[i][3] ? ' avec le message suivant&nbsp;: <p class="o_msg"><span>'+ newReponses[i][3] +'</span></p>':'&nbsp;! ') +'<a href="online.php'+(newReponses[i][4]?'?battle':'')+'">Cliquez ici</a> pour acc&eacute;der au mode en ligne&nbsp;!'):(o_language ? 'Sorry, '+ newReponses[i][1] +' <b>rejected</b> your invitation'+ (newReponses[i][3] ? ' for the following reason: <p class="o_msg"><span>'+ newReponses[i][3] +'</span></p>':'...'):'Dommage, '+ newReponses[i][1] +' a <b>refus&eacute;</b> votre demande'+ (newReponses[i][3] ? ' pour la raison suivante&nbsp;: <p class="o_msg"><span>'+ newReponses[i][3] +'</span></p>':'...')), true);
				var cross = document.getElementById("comsg"+ o_msgId).getElementsByTagName("a")[0];
				o_initdataset(cross);
				cross.dataset.member = newReponses[i][0];
				cross.dataset.battle = newReponses[i][4]?1:"";
				cross.onclick = function() {
					o_xhr("markasseen.php", "member="+ this.dataset.member + (this.dataset.member?"&battle":""), function(res) {
						return (res == 1);
					});
				};
				o_active = a_active;
			}
			oReponses = oJoueursInfos[2];
			var newMsgs = oJoueursInfos[3];
			var newConvs = o_getnewconvs(newMsgs);
			if (o_active) {
				for (var i=0;i<oJoueursInfos[4].length;i++) {
					if (!inArray0(oConvs,oJoueursInfos[4][i][0]))
						newConvs.push(oJoueursInfos[4][i]);
				}
				oConvs = oJoueursInfos[4];
				o_handlenewconvs(newConvs);
			}
			var oWritting = oJoueursInfos[5];
			o_handlewritting(oWritting);
			if (!o_handler)
				o_handler = setTimeout(o_chatrefresh, 1000);
			return true;
		}
		return false;
	});
}
function o_chatrefresh() {
	o_handler = null;
	if (o_online) {
		o_refreshstate++;
		if (o_refreshstate >= (o_active ? 5:10))
			o_refreshstate = 0;
		if (!o_refreshstate)
			return o_refresh();
		var data = new Array();
		var now = new Date().getTime();
		for (var i=0;i<o_chats.length;i++) {
			var o_chat = o_chats[i];
			if (oLastActivities[o_chat.dataset.member] > now)
				o_addmsgdata(i,data,o_chat);
		}
		if (!data.length) {
			if (!o_handler)
				o_handler = setTimeout(o_chatrefresh, 1000);
			return;
		}
		o_xhr("chatreload.php", data.join("&"), function(reponse) {
			if (reponse) {
				var oJoueursInfos;
				try {
					oJoueursInfos = eval(reponse);
				}
				catch (e) {
					return false;
				}
				var newMsgs = oJoueursInfos[0];
				var newConvs = o_getnewconvs(newMsgs);
				o_handlenewconvs(newConvs);
				var oWritting = oJoueursInfos[1];
				o_handlewritting(oWritting);
				if (!o_handler)
					o_handler = setTimeout(o_chatrefresh, 1000);
				return true;
			}
			return false;
		});
	}
}
function o_addmsgdata(inc,data,o_chat) {
	data.push("c"+ inc +"="+ o_chat.dataset.member);
	if (!o_chat.dataset.reduced) {
		var lastID = o_lastmsgid(o_chat);
		data.push("m"+ inc +"="+ lastID);
	}
	if (o_chat.dataset.typing != -1) {
		data.push("w"+ inc +"="+ o_chat.dataset.typing);
		o_chat.dataset.typing = -1;
	}
}
function o_getnewconvs(newMsgs) {
	var newConvs = new Array();
	for (var chatID in newMsgs) {
		var newMsgList = newMsgs[chatID];
		var memberID = chatID.substring(1)*1;
		var o_cw = document.getElementById("chatwindow"+ memberID);
		if (o_cw) {
			o_appendmsgs(memberID,newMsgList,false);
			if (newMsgList.length && o_cw.dataset.reduced) {
				var newMsg = newMsgList[newMsgList.length-1];
				newConvs.push([newMsg[0],newMsg[1],o_cw.dataset.pseudo,newMsg[2]]);
			}
		}
	}
	return newConvs;
}
function o_handlenewconvs(newConvs) {
	for (var i=0;i<newConvs.length;i++) {
		var memberID = newConvs[i][1];
		var comsgs = document.getElementsByClassName("comsg");
		for (var j=comsgs.length-1;j>=0;j--) {
			if (comsgs[j].dataset.memberAnswer == memberID)
				eval(comsgs[j].getElementsByTagName("a")[0].href);
		}
	}
	for (var i=0;i<newConvs.length;i++) {
		var newConv = newConvs[i];
		var o_msgId = displayMsg(o_language ? ('New message from <strong>'+ newConv[2] + '</strong>:<p class="o_msg"><span>'+ newConv[3] +'</span></p><input type="button" class="o_ansnew" value="Answer" />'):('Nouveau message de <strong>'+ newConv[2] + '</strong>&nbsp;:<p class="o_msg"><span>'+ newConv[3] +'</span></p><input type="button" class="o_ansnew" value="R&eacute;pondre" />'), true);
		if (o_msgId != -1) {
			var o_msg = document.getElementById("comsg"+ o_msgId);
			o_msg.dataset.memberAnswer = newConv[1];
			var cross = o_msg.getElementsByTagName("a")[0];
			o_initdataset(cross);
			cross.dataset.answer = newConv[0];
			cross.onclick = function() {
				o_xhr("markasread.php", "answer="+ this.dataset.answer, function(res) {
					return (res == 1);
				});
			};
			var answer = o_msg.getElementsByClassName("o_ansnew")[0];
			o_initdataset(answer);
			answer.dataset.member = newConv[1];
			answer.dataset.pseudo = newConv[2];
			answer.onclick = function() {
				o_launchchat(this.dataset.member,this.dataset.pseudo);
				deleteCross(this);
			};
			o_notify_msg(newConv[1],newConv[2],newConv[3]);
		}
	}
	if (newConvs.length)
		o_newmsgsound();
}
function o_handlewritting(oWritting) {
	for (var i=0;i<oWritting.length;i++) {
		var memberID = oWritting[i];
		o_updateactivity(memberID, 15);
		var o_chat = document.getElementById("chatwindow"+ memberID);
		if (o_chat) {
			if (o_chat.className.indexOf("chat_isw") == -1) {
				var o_list = o_chat.getElementsByClassName("chatmsgs")[0];
				var scrolledToBottom = (o_list.scrollTop>=o_list.scrollHeight-o_list.clientHeight-SCROLL_MARGIN);
				o_chat.className += " chat_isw";
				if (scrolledToBottom)
					o_list.scrollTop = o_list.scrollHeight;
			}
		}
	}
	for (var i=0;i<o_chats.length;i++) {
		var o_chat = o_chats[i];
		if (oWritting.indexOf(o_chat.dataset.member*1) == -1)
			o_chat.className = o_chat.className.replace(" chat_isw", "");
	}
}
function o_updatechatactivity(o_chat) {
	var memberID = o_chat.dataset.member;
	if (o_isconnected(memberID)) {
		var o_msgs = o_chat.getElementsByClassName("chatmsg");
		if (o_msgs.length) {
			var o_msgdate = o_msgs[o_msgs.length-1].dataset.date*1;
			o_updateactivityto(memberID, o_msgdate+40000)
		}
	}
}
function o_updateactivity(memberID,expireIn) {
	o_updateactivityto(memberID,new Date().getTime() + expireIn*1000);
}
function o_updateactivityto(memberID,timestamp) {
	if (oLastActivities[memberID] >= timestamp)
		return;
	oLastActivities[memberID] = timestamp;
}
function o_stopactivity(memberID) {
	delete oLastActivities[memberID];
}
function b_notify(key,title,msg) {
	if (o_active) {
		if (window.Notification) {
			var notification = new Notification(title, {
				icon: 'images/mkpc_box.jpg',
				body: msg,
				tag: key
			});
			notification.onclick = function() {
				window.focus();
				notification.close();
			};
			setTimeout(function() {
				notification.close();
			}, 8000);
		}
	}
}
function waitForLoad() {
	if (document.body)
		o_loadOnline();
	else
		setTimeout(waitForLoad, 1000);
}
waitForLoad();
document.addEventListener("DOMContentLoaded", o_loadOnline);
window.chatVersion = 2;