<?php
require_once('utils-date.php');
require_once('language.php');
function bbcode($msg) {
	global $language, $isNews;
	static $smileyDict = null;

	if (null === $smileyDict) {
		include('smileys.php');
		$smileyDict = [];
	
		// img smileys
		foreach ($smileys as $i => $s) {
			$smileyDict[$s] = '<img src="images/smileys/smiley' . $i . '.png" alt="' . $s . '" />';
		}
	
		// gif smileys
		foreach ($smileyNames as $i => $n) {
			$smileyDict[":$n:"] = $smileyDict[":$i:"] = "<img src=\"images/smileys/smiley$i.gif\" alt=\"$n\" />";
		}
		
	}

	$tag = function($tag, $htmlReplace, $criteria = '.*') use (&$msg) {
		$pattern = "#\[$tag\]($criteria)\[/$tag\]#isU";
		$msg = preg_replace($pattern, $htmlReplace, $msg);
	};
	$tagopt = function($tag, $htmlReplace, $criteria = '.*') use (&$msg) {
		$pattern = "#\[$tag=([^\]]+)\]($criteria)\[/$tag\]#isU";
		$msg = preg_replace($pattern, $htmlReplace, $msg);
	};

	$msg = htmlspecialchars($msg);

	$tag('center',   '<div style="text-align: center;">$1</div>');
	$tag('right',    '<div style="text-align: right;">$1</div>');
	$tag('left',     '<div style="text-align: left;">$1</div>');
	$tag('b',        '<strong>$1</strong>');
	$tag('i',        '<em>$1</em>');
	$tag('u',        '<u>$1</u>');
	$tag('s',        '<s>$1</s>');
	$tag('url',      '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>', 'http[^\[]*');
	$tag('img',      '<img src="$1" alt="$1" />', 'http[^\[]*');
	$tag('video',    '<video controls src="$1">' . F_('Your browser does not support the {t} element.', t: 'video') . '</video>', 'http[^\[]*');
	$tag('audio',    '<audio controls src="$1">' . F_('Your browser does not support the {t} element.', t: 'audio') . '</audio>', 'http[^\[]*');

	$tagopt('url',   '<a href="$1" class="type1" target="_blank" rel="noopener noreferrer">$2</a>');
	$tagopt('color', '<span style="color: $1">$2</span>');
	$tagopt('font',  '<span style="font-family: $1">$2</span>');
	$tagopt('size',  '<span style="font-size: $1pt;">$2</span>');

	// yt embed
	$msg = preg_replace(
		'#\[yt\].*(?:youtu.be/|v/|u/\w/|embed/|watch\?v=)([^\#\&\?\[]*).*\[/yt\]#',
		'<iframe src="https://www.youtube.com/embed/$1" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe>',
		$msg
	);

	// language tabs
	$msg = preg_replace(
		'#\[(fr|en)\](.*)\[/\1\][ \r\n\t]*\[(fr|en)\](.*)\[/\3\]#isU',
		'<div class="tr-ctn tr-ctn-' . ($language ? 'en' : 'fr') . '"><div class="tr-tabs"><div class="tr-tab-$1" onclick="this.parentNode.parentNode.className=\'tr-ctn tr-ctn-$1\'"></div><div class="tr-tab-$3" onclick="this.parentNode.parentNode.className=\'tr-ctn tr-ctn-$3\'"></div></div><div class="tr-msgs"><div class="tr-msg-$1">$2</div><div class="tr-msg-$3">$4</div></div></div>',
		$msg
	);

	// nested quotes
	do {
		$prev = $msg;
		$tag('quote', '<div class="quote1">$1</div>');
		$tagopt('quote', '<div class="quote1"><div class="quote1author"><a href="profil.php?pseudo=$1" target="_blank">$1</a> ' . ($language ? 'wrote' : 'a écrit ') . ':</div>$2</div>');
	} while ($msg !== $prev);

	// nested spoilers
	do {
		$prev = $msg;
		$tag('spoiler',
			'<div class="spoiler1"><div class="spoiler1disp">Spoiler [<a class="spoiler1show" href="#null" onclick="this.parentNode.parentNode.className=\'spoiler1 spoiler1shown\';return false">' .
			($language ? 'Show' : 'Afficher') .
			'</a><a class="spoiler1hide" href="#null" onclick="this.parentNode.parentNode.className=\'spoiler1\';return false">' .
			($language ? 'Hide' : 'Masquer') .
			'</a>]</div><div class="spoiler1cont">$1</div></div>'
		);
	} while ($msg !== $prev);

	// mentions
	$msg = preg_replace('#\B@([a-zA-Z0-9\-_]+?)#isU', '<a class="ref1" href="profil.php?pseudo=$1" target="_blank">@$1</a>', $msg);

	// whitespace + line breaks
	$msg = str_replace('  ', ' &nbsp;', $msg);
	$msg = nl2br($msg);
	$msg = str_replace('\t', ' &nbsp; &nbsp; &nbsp; &nbsp;', $msg);

	// smileys
	$msg = strtr($msg, $smileyDict);

	return $msg;
}

function print_league($pts, $icon) {
	$rk = get_league_rank($pts);
	echo <<<HTML
		<div class="player-league" style="color:{$rk['color']}">
			<img src="images/{$icon}_pts.png" alt="{$icon}" class="mPtsIc" />
			{$pts} pts &#9733;&nbsp;{$rk['name']}
		</div>
	HTML;
}

function print_forum_msg($message, $options=array()) {
	global $id, $language;

	$topicId = (int) ($_GET['topic'] ?? $message['topic'] ?? 0);

	$mayEdit = !empty($options['mayEdit']);
	$mayQuote = $options['mayQuote'] ?? $mayEdit;
	$mayReact = $options['mayReact'] ?? $mayQuote;
	$mayReport = $options['mayReport'] ?? $mayEdit;
	$canModerate = $options['canModerate'] ?? false;

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
			echo '<div class="mDate">'.pretty_dates($message['date']).($message['infosDate'] ?? '').'</div>';
			if ($id) {
				echo '<div class="mOptions">';

				// report button
				if ($mayReport) {
					echo '<a href="#null" onclick="reportMsg('.$topicId.','.$message['id'].');return false" class="mReport" title="'. ($language ? 'Report as inappropriate':'Signaler comme inapproprié') .'">';
					echo '<img src="images/forum/report.png" alt="'. ($language ? 'Report':'Signaler') .'" />';
					echo '</a>';
				}
				
				// edit button
				if ($mayEdit) {
					if ($message['id'] != 1) { // message edit
						echo '<a href="edit.php?id='. $message['id'] .'&amp;topic='. $topicId .'" class="mEdit" title="'. ($language ? 'Edit':'Modifier') .'">';
						echo '<img src="images/forum/edit.png" alt="'. ($language ? 'Edit':'Modifier') .'" />';
						echo '</a>';
						echo '<a href="delete.php?id='. $message['id'] .'&amp;topic='. $topicId .'&amp;token='. $_SESSION['csrf'] .'" onclick="return confirmSuppr()" class="mDelete" title="'. ($language ? 'Delete':'Supprimer') .'">';
						echo '<img src="images/forum/delete.png" alt="'. ($language ? 'Delete':'Supprimer') .'" />';
						echo '</a>';
					} else { // topic edit
						echo '<a href="edittopic.php?topic='. $topicId .'" class="mEdit" title="'. ($language ? 'Edit':'Modifier') .'">';
						echo '<img src="images/forum/edit.png" alt="'. ($language ? 'Edit':'Modifier') .'" />';
						echo '</a>';
						echo '<a href="supprtopic.php?topic='. $topicId .'&amp;token='. $_SESSION['csrf'] .'" onclick="return confirm(\''. ($language ? 'Warning, this will delete the topic. Continue ?':'Attention, cela supprimera le topic. Continuer ?') .'\')" class="mDelete" title="'. ($language ? 'Delete':'Supprimer') .'">';
						echo '<img src="images/forum/delete.png" alt="'. ($language ? 'Delete':'Supprimer') .'" />';
						echo '</a>';
					}
				}
				
				// quote button
				if ($mayQuote) {
					echo '<a href="repondre.php?topic='. $topicId .'&amp;quote='. $message['id'] .'" class="mQuote" title="'. ($language ? 'Quote':'Citer') .'">';
					echo '<img src="images/forum/quote.png" alt="'. ($language ? 'Quote':'Citer') .'" />';
					echo '</a>';
				}
				
				// react button
				if ($mayReact) {
					echo '<a href="#null" onclick="openReactions(\'topic\',\''.$topicId.','.$message['id'] .'\',this);return false" class="mReact" title="'. ($language ? 'Add reaction':'Ajouter une réaction') .'">';
					echo '<img src="images/forum/react.png" alt="'. ($language ? 'React':'Réagir') .'" />';
					echo '</a>';
				}
				
				// mod only - archive report button
				if ($canModerate && empty($options['archived'])) {
					echo '<a href="#null" onclick="archiveReport('.$message['reportid'].');return false" class="mArchive" title="'. ($language ? 'Archive report':'Archiver le signalement') .'">';
					echo '<img src="images/forum/archive.png" alt="'. ($language ? 'Archive':'Archiver') .'" />';
					echo '</a>';
				}
				
				// mod only - unarchive report button
				if ($canModerate && !empty($options['archived'])) {
					echo '<a href="#null" onclick="unarchiveReport('.$message['reportid'].');return false" class="mArchive" title="'. ($language ? 'Unarchive report':'Désarchiver le signalement') .'">';
					echo '<img src="images/forum/unarchive.png" alt="'. ($language ? 'Unarchive':'Désarchiver') .'" />';
					echo '</a>';
				}
				
				echo '</div>';
			}
		echo '</div>';
		echo '<div class="mBody">'. bbcode($message['message']) .'</div>';
		echo '<div class="mReactions">';
		if (isset($message['reactions']))
			printReactions('topic', $message['topic'].','.$message['id'], $message['reactions'], $id&&$mayReact);
		echo '</div>';
		echo '</div>';
	echo '</div>';
}
?>