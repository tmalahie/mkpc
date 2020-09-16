<?php
require_once('utils-date.php');
function bbcode($msg) {
	global $language, $isNews;
	static $smileyDict = null;
	if (null === $smileyDict) {
		include('smileys.php');
		$smileyDict = array();
		for ($i=0;$i<$nbSmileys;$i++)
			$smileyDict[$smileys[$i]] = '<img src="images/smileys/smiley'.$i.'.png" alt="'. $smileys[$i] .'" />';
		for ($i=0;$i<$nbSmileys2;$i++)
			$smileyDict[':'.$smileyNames[$i].':'] = '<img src="images/smileys/smiley'.$i.'.gif" alt="'. $smileyNames[$i] .'" />';
		for ($i=0;$i<$nbSmileys2;$i++)
			$smileyDict[":$i:"] = '<img src="images/smileys/smiley'.$i.'.gif" alt="'. $smileyNames[$i] .'" />';
	}
	$msg = htmlspecialchars($msg);
	$msg = preg_replace('#\[center\](.*)\[/center\]#isU', '<div style="text-align: center;">$1</div>', $msg);
	$msg = preg_replace('#\[right\](.*)\[/right\]#isU', '<div style="text-align: right;">$1</div>', $msg);
	$msg = preg_replace('#\[left\](.*)\[/left\]#isU', '<div style="text-align: left;">$1</div>', $msg);
	$msg = preg_replace('#\[b\](.*)\[/b\]#isU', '<strong>$1</strong>', $msg);
	$msg = preg_replace('#\[i\](.*)\[/i\]#isU', '<em>$1</em>', $msg);
	$msg = preg_replace('#\[u\](.*)\[/u\]#isU', '<u>$1</u>', $msg);
	$msg = preg_replace('#\[s\](.*)\[/s\]#isU', '<s>$1</s>', $msg);
	$msg = preg_replace('#\[url\](http[^\[]*)\[/url\]#isU', '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>', $msg);
	$msg = preg_replace('#\[img\](http[^\[]*)\[/img\]#isU', '<img src="$1" alt="$1" />', $msg);
	$msg = preg_replace('#\[url=(http[^\]]+)\](.*)\[/url\]#isU', '<a href="$1" class="type1" target="_blank" rel="noopener noreferrer">$2</a>', $msg);
	$msg = preg_replace('#\[color=([^\]]+)\](.*)\[/color\]#isU', '<span style="color: $1">$2</span>', $msg);
	$msg = preg_replace('#\[font=([a-zA-Z ]+)\](.*)\[/font\]#isU', '<span style="font-family: $1">$2</span>', $msg);
	$msg = preg_replace('#\[size=([0-9]{1,2})\](.*)\[/size\]#isU', '<span style="font-size: $1pt;">$2</span>', $msg);
	$msg = preg_replace('#\[yt\].*(?:youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=)([^\#\&\?\[]*).*\[\/yt\]#', '<iframe src="https://www.youtube.com/embed/$1" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe>', $msg);
	$msg = preg_replace('#\[(fr|en)\](.*)\[/\1\][ \r\n\t]*\[(fr|en)\](.*)\[/\3\]#isU', '<div class="tr-ctn tr-ctn-'.($language ? 'en':'fr').'"><div class="tr-tabs"><div class="tr-tab-$1" onclick="this.parentNode.parentNode.className=\'tr-ctn tr-ctn-$1\'"></div><div class="tr-tab-$3" onclick="this.parentNode.parentNode.className=\'tr-ctn tr-ctn-$3\'"></div></div><div class="tr-msgs"><div class="tr-msg-$1">$2</div><div class="tr-msg-$3">$4</div></div></div>', $msg);
	do {
		$aMsg = $msg;
		$msg = preg_replace('#\[quote\](.*)\[\/quote\]#isU', '<div class="quote1">$1</div>', $msg);
		$msg = preg_replace('#\[quote=(.+)\](.*)\[\/quote\]#isU', '<div class="quote1"><div class="quote1author"><a href="profil.php?pseudo=$1" target="_blank">$1</a> '. ($language ? 'wrote':'a écrit ') .':</div>$2</div>', $msg);
	} while ($msg != $aMsg);
	do {
		$aMsg = $msg;
		$msg = preg_replace('#\[spoiler\](.*)\[\/spoiler\]#isU', '<div class="spoiler1"><div class="spoiler1disp">Spoiler [<a class="spoiler1show" href="#null" onclick="this.parentNode.parentNode.className=\'spoiler1 spoiler1shown\';return false">'. ($language ? 'Show':'Afficher') .'</a><a class="spoiler1hide" href="#null" onclick="this.parentNode.parentNode.className=\'spoiler1\';return false">'. ($language ? 'Hide':'Masquer') .'</a>]</div><div class="spoiler1cont">$1</div></div>', $msg);
	} while ($msg != $aMsg);
	$msg = preg_replace('#\B@([a-zA-Z0-9\-_]+?)#isU', '<a class="ref1" href="profil.php?pseudo=$1" target="_blank">@$1</a>', $msg);
	$msg = str_replace('  ', ' &nbsp;', $msg);
	$msg = nl2br($msg);
	$msg = str_replace('\t', ' &nbsp; &nbsp; &nbsp; &nbsp;', $msg);
	$msg = strtr($msg,$smileyDict);
	return $msg;
}
function print_league($pts,$ic) {
	echo '<div class="player-league" style="color:'.get_league_color($pts).'">';
	echo '<img src="images/'.$ic.'_pts.png" alt="'.$ic.'" class="mPtsIc" />';
		echo $pts . ' pts ';
		echo '&#9733;&nbsp;'.get_league_name($pts);
	echo '</div>';
}
function print_forum_msg($message,$mayEdit,$mayQuote=null) {
	global $id, $language;
	if (null===$mayQuote)
		$mayQuote = $mayEdit;
	echo '<div class="fMessage" data-msg="'. $message['id'] .'">';
	echo '<div class="mAuteur">';
	if ($getAuteur = mysql_fetch_array(mysql_query('SELECT j.nom,r.privilege,p.nick_color,j.pts_vs,j.pts_battle,j.deleted FROM `mkjoueurs` j INNER JOIN `mkprofiles` p ON j.id=p.id LEFT JOIN `mkrights` r ON j.id=r.player AND r.privilege IN ("admin","moderator","organizer") WHERE j.id='. $message['auteur']))) {
		$byAuthor = '<strong>'. get_pseudo_bbcolor($getAuteur['nick_color']) .'</strong>';
		switch ($getAuteur['privilege']) {
		case 'admin':
			$byAuthor .= ' <strong><em>(admin)</em></strong>';
			break;
		case 'moderator':
			$byAuthor .= ' <strong><em>('. ($language ? 'moderator':'modérateur') .')</em></strong>';
			break;
		case 'organizer':
			$byAuthor .= ' <strong><em>('. ($language ? 'event&nbsp;host':'animateur') .')</em></strong>';
			break;
		}
		echo '<div class="mPseudo"><a href="profil.php?id='.$message['auteur'].'" title="'. $getAuteur['nom'] .'">'.$byAuthor.'</a></div>';
		echo '<a href="profil.php?id='.$message['auteur'].'">';
		print_avatar($message['auteur'], 100);
		echo '</a>';
		print_nb_msgs($message['auteur']);
		if (!$getAuteur['deleted']) {
			print_league($getAuteur['pts_vs'],'vs');
			print_league($getAuteur['pts_battle'],'battle');
			print_flag($message['auteur']);
		}
	}
	else {
		echo '<div class="mPseudo"><em>Compte supprimé</em></div>';
		print_avatar($message['auteur'], 100);
	}
	echo '</div>';
	echo '<div class="mContent">';
		echo '<div class="mHeader'. ($mayEdit ? ' mCanEdit':'') .'">';
			echo '<div class="mDate">'
			. pretty_dates($message['date'])
			. (isset($message['infosDate']) ? $message['infosDate']:'')
			. '</div>';
			echo '<div class="mOptions">'
			. ($mayEdit ? (($message['id']!=1) ? '<a href="edit.php?id='. $message['id'] .'&amp;topic='. $_GET['topic'] .'" class="mEdit">'. ($language ? 'Edit':'Modifier') .'</a> &nbsp; <a href="delete.php?id='. $message['id'] .'&amp;topic='. $_GET['topic'] .'&amp;token='. $_SESSION['csrf'] .'" onclick="return confirmSuppr()" class="mDelete">'. ($language ? 'Delete':'Supprimer') .'</a>' : '<a href="edittopic.php?topic='. $_GET['topic'] .'" class="mEdit">'. ($language ? 'Edit':'Modifier') .'</a> &nbsp; <a href="supprtopic.php?topic='. $_GET['topic'] .'&amp;token='. $_SESSION['csrf'] .'" class="mDelete" onclick="return confirm(\''. ($language ? 'Warning, this will delete the topic. Continue ?':'Attention, cela supprimera le topic. Continuer ?') .'\')">'. ($language ? 'Delete':'Supprimer') .'</a>'):null)
			. ($id&&$mayQuote ? '<a href="repondre.php?topic='. $_GET['topic'] .'&amp;quote='. $message['id'] .'" class="mQuote">'. ($language ? 'Quote':'Citer') .'</a>':'')
			. '</div>';
		echo '</div>';
		echo '<div class="mBody">'. bbcode($message['message']) .'</div>';
		echo '</div>';
	echo '</div>';
	$clair = !$clair;
}
?>