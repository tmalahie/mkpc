<?php
include('session.php');
include('language.php');
include('initdb.php');
require_once('utils-challenges.php');
if (isset($_GET['moderate'])) {
	require_once('getRights.php');
	if (hasRight('clvalidator'))
		$moderate = true;
}
elseif (isset($_GET['remoderate'])) {
	require_once('getRights.php');
	if (hasRight('clvalidator'))
		$remoderate = true;
}
$rateChallenges = isset($_GET['rate']);
$chSelect = 'c.*,l.type,l.circuit';
$chJoin = array();
$chJoin[] = 'INNER JOIN mkclrace l ON c.clist=l.id';
$chWhere = array();
$chWhere[] = 'l.type!=""';
if (isset($moderate)) {
	$chWhere[] = 'c.status="pending_moderation"';
	$chOrder = 'c.date';
	$challengeTitle = $language ? 'Challenges pending moderation':'Défis en attente de validation';
}
else {
	if (isset($remoderate)) {
		$chWhere[] = '(c.status="active" OR (c.status="pending_completion" AND validation!=""))';
	}
	else {
		$chWhere[] = 'c.status="active"';
	}
	if (empty($_GET['ordering']) || ('rating' !== $_GET['ordering'])) {
		$chOrder = 'c.date DESC';
		if (isset($remoderate))
			$challengeTitle = $language ? 'Undo a challenge validation':'Annuler la validation d\'un défi';
		else
			$challengeTitle = $language ? 'Last published challenges':'Derniers défis publiés';
	}
	else {
		$chOrder = 'c.avgrating DESC, c.nbratings DESC, c.date DESC';
		if (isset($moderate))
			$challengeTitle = $language ? 'Challenges pending moderation':'Défis en attente de validation';
		else
			$challengeTitle = $language ? 'Top rated challenges':'Défis les mieux notés';
	}
}
if (isset($_GET['difficulty']) && ($_GET['difficulty'] != ''))
	$chWhere[] = 'c.difficulty="'. $_GET['difficulty'] .'"';
if (!empty($_GET['hide_succeeded']) && $id) {
	$chJoin[] = 'LEFT JOIN mkclwin w ON w.challenge=c.id AND w.player='.$id;
	$chWhere[] = 'w.player IS NULL';
}
if (isset($_GET['author'])) {
	if ($getProfile = mysql_fetch_array(mysql_query('SELECT identifiant,identifiant2,identifiant3,identifiant4 FROM `mkprofiles` WHERE id="'. $_GET['author'] .'"'))) {
		$chWhere[] = 'l.identifiant='.$getProfile['identifiant'];
		$chWhere[] = 'l.identifiant2='.$getProfile['identifiant2'];
		$chWhere[] = 'l.identifiant3='.$getProfile['identifiant3'];
		$chWhere[] = 'l.identifiant4='.$getProfile['identifiant4'];
		if ($username = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $_GET['author'] .'"')))
			$challengeTitle = $language ? 'Challenges list of '.$username['nom']:'Liste des défis de '.$username['nom'];
	}
}
if ($rateChallenges || isset($_GET['winner'])) {
	$winner = $rateChallenges ? $id : $_GET['winner'];
	$chJoin[] = 'INNER JOIN mkclwin w2 ON w2.challenge=c.id AND w2.player="'.$winner.'" AND w2.creator=0';
	$chOrder = 'w2.date DESC';
	if ($rateChallenges) {
		$challengeTitle = $language ? 'Rate completed challenges':'Noter les défis réussis';
		$chSelect .= ',w2.rating AS avgrating,(w2.rating>0) AS nbratings';
	}
	else {
		if ($username = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $_GET['winner'] .'"')))
			$challengeTitle = $language ? 'Challenges completed by '.$username['nom']:'Défis réussis par '.$username['nom'];
	}
}
$lastChallenges = mysql_query('SELECT '.$chSelect.' FROM mkchallenges c '. implode(' ', $chJoin) .' WHERE '. implode(' AND ', $chWhere) .' ORDER BY '. $chOrder);
$challenges = array();
while ($challenge = mysql_fetch_array($lastChallenges))
	$challenges[] = $challenge;
$nbChallenges = count($challenges);
$currentPage = isset($_GET['page']) ? $_GET['page']:1;
$challengesPerPage = 20;
$challenges = array_slice($challenges,($currentPage-1)*$challengesPerPage,$challengesPerPage);
$challengeParams = array(
	'rating' => true,
	'circuit' => true
);
if (!isset($moderate) && !isset($remoderate) && $id) {
	$challengeParams['winners'] = true;
	$challengeParams['id'] = $id;
}
foreach ($challenges as $i => $challenge)
	$challenges[$i] = getChallengeDetails($challenges[$i], $challengeParams);
$nbPages = ceil($nbChallenges/$challengesPerPage);
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $challengeTitle; ?> - Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/creations.css" />
<link rel="stylesheet" type="text/css" href="styles/challenge-creations.css" />

<?php
include('o_online.php');
?>

<script type="text/javascript">
var language = <?php echo $language ? 'true':'false'; ?>;
function editDifficulty(id) {
	var $challenge = document.getElementById("challenges-item-"+id);
	var $challengeDifficulty = $challenge.getElementsByClassName("challenges-item-difficulty");
	$challengeDifficulty = $challengeDifficulty[0];
	$challengeDifficulty.className += " challenges-item-editting";
}
function uneditDifficulty(id) {
	var $challenge = document.getElementById("challenges-item-"+id);
	var $challengeDifficulty = $challenge.getElementsByClassName("challenges-item-difficulty");
	$challengeDifficulty = $challengeDifficulty[0];
	$challengeDifficulty.className = $challengeDifficulty.className.replace(/challenges-item-editting ?/g, "").trim();
	var $challengeSelect = $challenge.getElementsByClassName("challenges-item-difficulty-select");
	$challengeSelect = $challengeSelect[0];
	$challengeSelect.selectedIndex = $challengeSelect.dataset.defaultvalue;
}
function acceptChallenge(id) {
	var $challenge = document.getElementById("challenges-item-"+id);
	var $challengeSelect = $challenge.getElementsByClassName("challenges-item-difficulty-select");
	$challengeSelect = $challengeSelect[0];
	var lastDifficulty = $challengeSelect.dataset.defaultvalue;
	var newDifficulty = $challengeSelect.selectedIndex;
	var difficultyChanged = (lastDifficulty != newDifficulty);
	if (difficultyChanged) {
		o_prompt(language
			? "Please confirm challenge <strong>approval</strong>.<br />Optional: explain why you changed challenge difficulty:"
			: "Veuillez confirmer la <strong>validation</strong> du défi.<br />Facultatif&nbsp;: expliquez le changement de difficulté&nbsp;:",
			"",
			function(msg) {
				var data = {"challenge":id,"accept":1,"difficulty":newDifficulty};
				if (msg) data["msg"] = msg;
				challengeModerate(data);
			}
		);
	}
	else {
		o_confirm(language
			? "Please confirm challenge <strong>approval</strong>"
			: "Veuillez confirmer la <strong>validation</strong> du défi",
			function(ok) {
				if (ok) {
					var data = {"challenge":id,"accept":1};
					challengeModerate(data);
				}
			}
		);
	}
}
function rejectChallenge(id) {
	var $challenge = document.getElementById("challenges-item-"+id);
	var $challengeSelect = $challenge.getElementsByClassName("challenges-item-difficulty-select");
	$challengeSelect = $challengeSelect[0];
	o_prompt(language
		? "Please confirm challenge <strong>rejection</strong>.<br />Optional: explain why you rejected challenge:"
		: "Veuillez confirmer la <strong>non-validation</strong> du défi.<br />Facultatif&nbsp;: donnez les raisons du refus&nbsp;:",
		"",
		function(msg) {
			var data = {"challenge":id,"accept":0};
			if (msg) data["msg"] = msg;
			challengeModerate(data);
		}
	);
}
function remoderateChallenge(id) {
	o_confirm(o_language ? "Put this challenge back to the &quot;pending moderation&quot; list?" : "Repasser ce défi dans la liste des défis à modérer ?", function(valided) {
        if (valided) {
			var data = {"challenge":id};
			challengeModerate(data, "challengeRemoderate.php");
        }
    });
}
function previewRating(id,rating) {
	var $challenge = document.getElementById("challenges-item-"+id);
	var $challengeRating = $challenge.getElementsByClassName("challenges-item-rating");
	$challengeRating = $challengeRating[0];
	var $challengeStars = $challengeRating.getElementsByTagName("img");
	for (var i=0;i<$challengeStars.length;i++)
		$challengeStars[i].src = "images/star"+ (i<rating ? 1:0) +".png";
}
function unpreviewRating(id) {
	var $challenge = document.getElementById("challenges-item-"+id);
	var $challengeRating = $challenge.getElementsByClassName("challenges-item-rating");
	$challengeRating = $challengeRating[0];
	var rating = +$challengeRating.getAttribute("data-rating");
	var $challengeStars = $challengeRating.getElementsByTagName("img");
	for (var i=0;i<$challengeStars.length;i++)
		$challengeStars[i].src = "images/star"+ (i<rating ? 1:0) +".png";
}
function rateChallenge(id,rating) {
	var $challenge = document.getElementById("challenges-item-"+id);
	var $challengeRating = $challenge.getElementsByClassName("challenges-item-rating");
	$challengeRating = $challengeRating[0];
	var lastRating = +$challengeRating.getAttribute("data-rating");
	if (lastRating == rating)
		rating = 0;
	$challengeRating.setAttribute("data-rating", rating);
	var $challengeStars = $challengeRating.getElementsByTagName("img");
	for (var i=0;i<$challengeStars.length;i++)
		$challengeStars[i].src = "images/star"+ (i<rating ? 1:0) +".png";
	var $challengeThanks = $challenge.getElementsByClassName("challenges-item-rating-thanks");
	$challengeThanks = $challengeThanks[0];
	$challengeThanks.style.display = "";
	o_xhr("challengeRate.php", "challenge="+id+"&rating="+rating, function(reponse) {
		if (reponse == 1) {
			$challengeThanks.style.display = "inline-block";
			return true;
		}
		return false;
	});
}
function challengeModerate(data, url) {
	if (!url) url = "challengeModerate.php";
	var rawdata = "";
	for (var key in data) {
		if (rawdata) rawdata += "&";
		rawdata += key+"="+encodeURIComponent(data[key]);
	}
	o_xhr(url, rawdata, function(res) {
		return (res == 1);
	});
	var id = data.challenge;
	var $challenge = document.getElementById("challenges-item-"+id);
	function fadeOut($elt) {
		var opacity = 1;
		function fadeOutAux() {
			opacity -= 0.1;
			if (opacity <= 0)
				$elt.parentNode.removeChild($elt);
			else {
				$elt.style.opacity = opacity;
				setTimeout(fadeOutAux, 40);
			}
		}
		fadeOutAux();
	}
	fadeOut($challenge);
}
</script>
</head>
<body>
<?php
include('header.php');
$page = 'game';
include('menu.php');
?>
<main>
	<div class="challenges-list-ctn">
		<h1><?php echo $challengeTitle; ?></h1>
		<?php
		if (isset($moderate)) {
			if ($language) {
				?>
				<p>
					Welcome to the challenge moderation page. Before you begin, please read the <a href="javascript:document.getElementById('validation-hints').style.display=document.getElementById('validation-hints').style.display?'':'block';void(0)">validation tips</a>.
					<div id="validation-hints">
						For each challenge, you have 3 options:
						<ul>
							<li>Accept challenge, by clicking on <button class="challenges-item-accept">&check;</button></li>
							<li>Reject challenge, by clicking on <button class="challenges-item-reject">&times;</button></li>
							<li>Accept challenge, but change difficulty level</li>
						</ul>
						Here are the reasons why you would reject a challenge:
						<ul>
							<li>Challenge pointless or with no difficulty (&quot;Complete track&quot; without constraint, on an easy track)</li>
							<li>Spam (12 times the same challenge, or simillar challenges posted by the same person)</li>
							<li>Obvious constraint missing (&quot;CPUs in difficult mode&quot;). In the case, precise it on the rejection message.</li>
							<li>Challenge name with insults or inappropriate words.</li>
						</ul>
						You can also change the difficulty if you find it unsuitable for the challenge. Try to make it consistent with the reference scale:
						<ul>
							<li>A challenge <span class="challenges-item-difficulty-0">easy</span> has to be feasible for a beginner (&quot;Complete Mario Circuit 1 in Time Trial in less than 55s&quot;)</li>
							<li>A challenge <span class="challenges-item-difficulty-1">medium</span> is typically difficult for a beginner but easy for an experimented player (&quot;Complete Mario Circuit 1 in TT in less than 45s&quot;)</li>
							<li>A challenge <span class="challenges-item-difficulty-2">difficult</span> would be difficult for an experimented player but completable in several trials (&quot;Complete Mario Circuit 1 in TT in less than 39s&quot;)</li>
							<li>A challenge <span class="challenges-item-difficulty-3">extreme</span> will require to try-hard even for an experimented player (&quot;Complete Mario Circuit 1 in TT in less than 38s&quot;)</li>
							<li>A challenge <span class="challenges-item-difficulty-4">impossible</span> will require to try-hard and may typically take several hours (or even days) before succeeding (&quot;Complete Mario Circuit 1 in TT in less than 37s&quot;)</li>
						</ul>
					</div>
				</p>
				<?php
			}
			else {
				?>
				<p>
					Bienvenue dans la page de modération des défis. Avant de commencer, merci de lire les <a href="javascript:document.getElementById('validation-hints').style.display=document.getElementById('validation-hints').style.display?'':'block';void(0)">conseils de validation</a>.
					<div id="validation-hints">
						Pour chaque défi, vous avez 3 possibilités :
						<ul>
							<li>Accepter le défi, en cliquant sur <button class="challenges-item-accept">&check;</button></li>
							<li>Refuser le défi, en cliquant sur <button class="challenges-item-reject">&times;</button></li>
							<li>Accepter le défi, mais modifier le niveau de difficulté</li>
						</ul>
						Voici les raisons pour lesquelles vous pouvez refuser un défi&nbsp;:
						<ul>
							<li>Défi sans intérêt ou avec aucune difficulté (&quot;Finir le circuit&quot; sans contraintes, sur un circuit facile)</li>
							<li>Spam (12 fois le même défi, ou des défis simillaires publiées par la même personne)</li>
							<li>Contrainte évidente manquante (&quot;Ordis en mode difficile&quot;). Dans ce cas, précisez-le dans le message de refus.</li>
							<li>Nom de défi avec des insultes ou des mots obscènes</li>
						</ul>
						Vous pouvez également modifier la difficulté si vous la jugez inadaptée au défi. Essayez de vous confortez à cette échelle de référence&nbsp;:
						<ul>
							<li>Un défi <span class="challenges-item-difficulty-0">facile</span> doit être faisable par un débutant (&quot;Finir le Circuit Mario 1 en Contre-La-Montre en moins de 55s&quot;)</li>
							<li>Un défi <span class="challenges-item-difficulty-1">moyen</span> est typiquement difficile pour un débutant mais facile pour un joueur expérimenté (&quot;Finir le Circuit Mario 1 en CLM en moins de 45s&quot;)</li>
							<li>Un défi <span class="challenges-item-difficulty-2">difficile</span> sera difficile pour un joueur expérimenté mais réussissable en plusieurs essais (&quot;Finir le Circuit Mario 1 en CLM en moins de 39s&quot;)</li>
							<li>Un défi <span class="challenges-item-difficulty-3">extrême</span> nécessitera de try-harder même pour un joueur expérimenté (&quot;Finir le Circuit Mario 1 en CLM en moins de 38s&quot;)</li>
							<li>Un défi <span class="challenges-item-difficulty-4">impossible</span> nécessite de try-harder et peut typiquement prendre plusieurs heures (voire jours) avant de réussir (&quot;Finir le Circuit Mario 1 en CLM en moins de 37s&quot;)</li>
						</ul>
					</div>
				</p>
				<?php
			}
		}
		elseif (isset($remoderate)) {
			if ($language) {
				?>
				A challenge you accepted or rejected by mistake? A difficulty to change? You're in the right place!
				<?php
			}
			else {
				?>
				Un défi que vous avez accepté ou refusé par erreur ? Une difficulté à changer ? C'est ici que ça se passe !
				<?php
			}
		}
		elseif (!$rateChallenges) {
			?>
			<div class="challenges-list-sublinks">
				<img src="images/cups/cup2.png" alt="Cup" /> <a href="challengeRanking.php"><?php echo $language ? 'Challenges leaderboard':'Classement des défis'; ?></a> &nbsp;
				<img src="images/ministar0.png" alt="Star" style="height:14px" /> <a href="challengesList.php?rate"><?php echo $language ? 'Rate challenges':'Noter les défis'; ?></a>
			</div>
			<form method="get" class="challenges-list-search" action="challengesList.php">
				<p>
					<label><?php echo $language ? 'Filter:':'Filtrer :'; ?>
					<?php
					if (isset($_GET['author']))
						echo '<input type="hidden" name="author" value="'.htmlspecialchars($_GET['author']).'" />';
					if (isset($_GET['winner']))
						echo '<input type="hidden" name="winner" value="'.htmlspecialchars($_GET['winner']).'" />';
					?>
					<select name="difficulty">
						<option value=""><?php echo $language ? 'Difficulty...':'Difficulté...'; ?></option>
						<?php
						$challengeDifficulties = getChallengeDifficulties();
						$selectedDifficulty = (isset($_GET['difficulty']) && ($_GET['difficulty']!=='')) ? $_GET['difficulty']:-1;
						foreach ($challengeDifficulties as $i => $name)
							echo '<option value="'. $i .'"'. ($i==$selectedDifficulty ? ' selected="selected"':'') .'>'. htmlspecialchars($name) .'</option>';
						?>
					</select></label>
					&nbsp;
					<label><input type="checkbox" name="hide_succeeded"<?php if (!empty($_GET['hide_succeeded'])) echo ' checked="checked"'; ?> /><?php echo $language ? 'Hide succeeded challenges':'Masquer les défis réussis'; ?></label>
				</p>
				<p>
					<label><?php echo $language ? 'Show first:':'Afficher en premier :'; ?>
					<select name="ordering">
						<option value="latest"><?php echo $language ? 'Most recent challenges':'Défis les plus récents'; ?></option>
						<option value="rating"<?php if (!empty($_GET['ordering']) && ('rating'===$_GET['ordering'])) echo ' selected="selected"'; ?>><?php echo $language ? 'Top rated challenges':'Défis les mieux notés'; ?></option>
					</select></label>
					&nbsp;<input type="submit" value="Ok" />
				</p>
			</form>
			<?php
		}
		?>
		<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
		<!-- Forum MKPC -->
		<div class="pub"><ins class="adsbygoogle"
		     style="display:inline-block;width:728px;height:90px"
		     data-ad-client="ca-pub-1340724283777764"
		     data-ad-slot="4919860724"></ins></div>
		<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
		</script>
		<?php
		if ($nbChallenges) {
			require_once('utils-date.php');
			?>
			<div class="challenges-list">
			<?php
			foreach ($challenges as $challenge) {
				$circuit = $challenge['circuit'];
				if (empty($circuit))
					continue;
				$isCup = (strpos($circuit['cicon'], ',') !== false);
				?>
				<a class="challenges-list-item<?php
					if ($rateChallenges)
						echo ' challenges-list-item-rate';
					else {
						if (isset($moderate) || isset($remoderate))
							echo ' challenges-list-item-moderate';
						if (isset($challenge['succeeded']))
							echo ' challenges-list-item-success';
					}
					?>" id="challenges-item-<?php echo $challenge['id']; ?>" href="<?php echo 'challengeTry.php?challenge='. $challenge['id']; ?>" id="challenges-item-<?php echo $challenge['id']; ?>">
					<div class="challenges-item-circuit creation_icon <?php echo ($isCup ? 'creation_cup':'single_creation'); ?>"<?php
						if (isset($circuit['icon'])) {
							$allMapSrcs = $circuit['icon'];
							foreach ($allMapSrcs as $i=>$iMapSrc)
								$allMapSrcs[$i] = "url('images/creation_icons/$iMapSrc')";
							echo ' style="background-image:'.implode(',',$allMapSrcs).'"';
						}
						else
							echo ' data-cicon="'.$circuit['cicon'].'"';
					?>><?php
					if (isset($challenge['succeeded']) && !$rateChallenges)
						echo '<div class="challenges-item-success">✔</div>';
					?></div>
					<div class="challenges-item-description">
						<div>
						<?php
						if ($challenge['name'])
							echo '<h2>'. htmlspecialchars($challenge['name']) .'</h2>';
						echo '<h3>';
						if ($circuit['name'])
							echo '<strong>'. $circuit['name'] .'</strong> : ';
						echo $challenge['description']['main'];
						echo '</h3>';
						if (isset($challenge['description']['extra']))
							echo '<h4>'. $challenge['description']['extra'] .'</h4>';
						?>
						</div>
					</div>
					<div class="challenges-item-action"<?php if ($rateChallenges||isset($moderate)||isset($remoderate)) echo ' onclick="return false"'; ?>>
						<?php
						if ($rateChallenges) {
							$note = $challenge['rating']['avg'];
							?>
							<div class="challenges-item-difficulty challenges-item-difficulty-<?php echo $challenge['difficulty']['level']; ?>" >
								<img src="images/challenges/difficulty<?php echo $challenge['difficulty']['level']; ?>.png" alt="<?php echo $challenge['difficulty']['name']; ?>" />
								<?php echo $challenge['difficulty']['name']; ?>
								<div class="challenges-item-rating-thanks"><?php echo $language ? 'Thanks':'Merci'; ?></div>
							</div>
							<div class="challenges-item-rating" onmouseout="unpreviewRating(<?php echo $challenge['id']; ?>)" data-rating="<?php echo $note; ?>">
								<?php
								for ($i=1;$i<=5;$i++)
									echo '<img src="images/star'.($i<=$note ? 1:0).'.png" alt="Star" onmouseover="previewRating('. $challenge['id'] .','.$i.')" onclick="rateChallenge('. $challenge['id'] .','.$i.')" />';
								?>
							</div>
							<?php
							if ($circuit['author']) {
								?>
								<div class="challenges-item-author">
									<?php echo ($language ? 'By':'Par').' <strong>'. $circuit['author'] .'</strong>'; ?>
								</div>
								<?php
							}
						}
						elseif (isset($moderate)) {
							?>
						<div class="challenges-item-difficulty challenges-item-difficulty-<?php echo $challenge['difficulty']['level']; ?>" >
							<div class="challenges-item-difficulty-value">
								<img src="images/challenges/difficulty<?php echo $challenge['difficulty']['level']; ?>.png" alt="<?php echo $challenge['difficulty']['name']; ?>" />
								<?php echo $challenge['difficulty']['name']; ?>
								<span class="challenge-item-link" onclick="editDifficulty(<?php echo $challenge['id']; ?>)"><?php echo $language ? 'Edit':'Modifier'; ?></span>
							</div>
							<div class="challenges-item-difficulty-edit">
								<?php
								$challengeDifficulties = getChallengeDifficulties();
								$selectedDifficulty = $challenge['difficulty']['level'];
								?>
								<select class="challenges-item-difficulty-select" data-defaultvalue="<?php echo $selectedDifficulty; ?>">
									<?php
									foreach ($challengeDifficulties as $i => $name)
										echo '<option value="'. $i .'"'. ($i==$selectedDifficulty ? ' selected="selected"':'') .'>'. htmlspecialchars($name) .'</option>';
									?>
								</select>
								<span class="challenge-item-link" onclick="uneditDifficulty(<?php echo $challenge['id']; ?>)"><?php echo $language ? 'Undo':'Annuler'; ?></span>
							</div>
						</div>
						<?php
						if ($circuit['author']) {
							?>
							<div class="challenges-item-author">
								<?php echo ($language ? 'By':'Par').' <strong>'. $circuit['author'] .'</strong>'; ?>
							</div>
							<?php
						}
						?>
						<div class="challenges-item-moderation">
							<button class="challenges-item-accept" onclick="acceptChallenge(<?php echo $challenge['id']; ?>)">&check;</button>
							<button class="challenges-item-reject" onclick="rejectChallenge(<?php echo $challenge['id']; ?>)">&times;</button>
						</div>
							<?php
						}
						elseif (isset($remoderate)) {
							?>
							<div class="challenge-item-remoderate">
							<?php
							if ($challenge['status'] === 'active') {
								?>
								<span class="challenges-item-difficulty challenges-item-difficulty-<?php echo $challenge['difficulty']['level']; ?>" >
									<img src="images/challenges/difficulty<?php echo $challenge['difficulty']['level']; ?>.png" alt="<?php echo $challenge['difficulty']['name']; ?>" />
									<?php echo $challenge['difficulty']['name']; ?>
								</span><br />
								<span class="challenges-item-accepted">
									<?php echo $language ? 'Accepted':'Accepté'; ?>
								</span>
								<?php
							}
							else {
								?>
								<span class="challenges-item-rejected">
									<?php echo $language ? 'Rejected':'Refusé'; ?>
								</span>
								<?php
							}
							?><br />
							<span class="challenge-item-link" onclick="remoderateChallenge(<?php echo $challenge['id']; ?>)"><?php echo $language ? 'Undo':'Annuler'; ?></span>
							</div>
							<?php
						}
						else {
							$note = $challenge['rating']['avg'];
							$nbNotes = $challenge['rating']['nb'];
							$noteTitle = $nbNotes ? (round($note*100)/100).'/5 '. ($language ? 'on':'sur') .' '. $nbNotes .' vote'. ($nbNotes>1 ? 's':'') : ($language ? 'Unrated':'Non noté');
						?>
						<div class="challenges-item-difficulty challenges-item-difficulty-<?php echo $challenge['difficulty']['level']; ?>" >
							<img src="images/challenges/difficulty<?php echo $challenge['difficulty']['level']; ?>.png" alt="<?php echo $challenge['difficulty']['name']; ?>" />
							<?php echo $challenge['difficulty']['name']; ?>
						</div>
						<div class="challenges-item-rating">
							<table>
								<tr title="<?php echo $noteTitle; ?>">
									<?php
									for ($i=1;$i<=$note;$i++)
										echo '<td class="star1"></td>';
									$rest = $note-floor($note);
									if ($rest) {
										$w1 = 3+round(9*$rest);
										echo '<td class="startStar" style="width: '. $w1 .'px;"></td>';
										echo '<td class="endStar" style="width: '. (15-$w1) .'px;"></td>';
										$note++;
									}
									for ($i=$note;$i<5;$i++)
										echo '<td class="star0"></td>';
									?>
								</tr>
							</table>
						</div>
						<?php
						if ($circuit['author']) {
							?>
						<div class="challenges-item-author">
							<?php echo ($language ? 'By':'Par').' <strong>'. $circuit['author'] .'</strong>'; ?>
						</div>
							<?php
							}
						}
						?>
					</div>
				</a>
				<?php
			}
			?>
			</div>
			<?php
			if ($nbPages > 1) {
				?>
				<div class="challengePages"><p>
					Page : <?php
					$get = $_GET;
					require_once('utils-paging.php');
					$allPages = makePaging($currentPage,$nbPages,4);
					foreach ($allPages as $i=>$block) {
						if ($i)
							echo '...&nbsp; &nbsp;';
						foreach ($block as $p) {
							$get['page'] = $p;
							if ($p == $currentPage)
								echo $p;
							else
								echo '<a href="?'. http_build_query($get) .'">'. $p .'</a>';
							echo ' &nbsp; ';
						}
					}
					?>
				</p></div>
				<?php
			}
		}
		else
			echo '<h2><em>'. ($language ? 'No challenge for this search':'Aucun défi trouvé') .'</em></h2>';
		?>
		<p>
			<?php
			if ($rateChallenges) {
				?>
				<a href="challengesList.php"><?php echo $language ? 'Back to challenges list':'Retour à la liste des défis'; ?></a><br />
				<?php
			}
			if (isset($moderate)) {
				?>
				<a href="challengesList.php?remoderate"><?php echo $language ? 'Undo a challenge validation mistake':'Annuler une erreur de validation'; ?></a><br />
				<a href="admin-logs.php?role=clvalidator"><?php echo $language ? 'See challenge moderation history':'Historique des validations de défis'; ?></a><br />
				<?php
			}
			elseif (isset($remoderate)) {
				?>
				<a href="challengesList.php?moderate"><?php echo $language ? 'Back to moderation list':'Retour aux défis à valider'; ?></a><br />
				<?php
			}
			?>
			<a href="index.php"><?php echo $language ? 'Back to the Mario Kart PC':'Retour à Mario Kart PC'; ?></a>
		</p>
	</div>
</main>
<script type="text/javascript" src="scripts/posticons.js"></script>
</body>
</html>
<?php
include('footer.php');
mysql_close();
?>