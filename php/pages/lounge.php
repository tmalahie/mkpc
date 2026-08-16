<?php
include('../includes/session.php');
include('../includes/language.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>CT Lounge - Mario Kart PC</title>
<link rel="stylesheet" type="text/css" href="styles/lounge.css?reload=1" />
<script type="text/javascript">
var language = <?php echo $language ? 'true':'false'; ?>;
var mId = <?php echo $id ? intval($id) : 'null'; ?>;
var mResultKey = <?php echo isset($_GET['key']) ? intval($_GET['key']) : 'null'; ?>;
var mPerso = <?php echo isset($_GET['perso']) ? json_encode(preg_replace('#[^\w\-]#', '', $_GET['perso'])) : 'null'; ?>;
</script>
<script type="text/javascript" src="scripts/xhr.js"></script>
<script type="text/javascript" src="scripts/lounge.js?reload=1" defer></script>
</head>
<body>
<?php if (!$id) { ?>
<div class="lounge-gate">
	<h1>CT Lounge</h1>
	<p><?php echo $language
		? 'You must be logged in to access the ranked lounge.'
		: 'Vous devez &ecirc;tre connect&eacute; pour acc&eacute;der au lounge class&eacute;.'; ?></p>
	<p><a href="forum.php" target="_top"><?php echo $language ? 'Log in or sign up':'Se connecter / S\'inscrire'; ?></a></p>
</div>
<?php } else { ?>
<div id="lounge">
	<header class="lounge-header">
		<h1>CT Lounge</h1>
		<nav class="lounge-tabs">
			<button type="button" class="lounge-tab is-active" data-tab="queueup"><?php echo $language ? 'Queue Up':'File d\'attente'; ?></button>
			<button type="button" class="lounge-tab" data-tab="howitworks">?</button>
		</nav>
	</header>

	<section class="lounge-tabpanel is-active" data-panel="queueup">
		<div class="lounge-playerstrip" id="lounge-playerstrip">
			<span class="lounge-loading"><?php echo $language ? 'Loading...':'Chargement...'; ?></span>
		</div>
		<div class="lounge-tiers" id="lounge-tiers"></div>
		<div class="lounge-waiting" id="lounge-queueup" style="display:none"></div>
		<div class="lounge-results" id="lounge-results" style="display:none"></div>
	</section>

	<section class="lounge-tabpanel" data-panel="howitworks">
		<h2><?php echo $language ? 'How the lounge works':'Comment fonctionne le lounge'; ?></h2>
		<?php if ($language) { ?>
		<p>The CT Lounge is a ranked matchmaking system. Pick a tier, wait for enough players to join, vote on the game mode, then play a 12-race mogi on the CT Project multicup. Your MMR goes up or down depending on the result.</p>
		<ul>
			<li><strong>Tiers</strong> are based on your MMR. The "All" tier is open to everyone.</li>
			<li><strong>Mogis</strong> last 12 races and start once 4&ndash;8 players are queued.</li>
			<li><strong>Strikes</strong> are given for AFK / not voting / leaving mid-game. Too many strikes lead to a temporary ban.</li>
			<li><strong>MMR</strong> is computed at the end of each mogi from your final standing.</li>
		</ul>
		<?php } else { ?>
		<p>Le CT Lounge est un syst&egrave;me de matchmaking class&eacute;. Choisissez un tier, attendez que d'autres joueurs rejoignent, votez pour le mode de jeu, puis jouez un mogi de 12 courses sur la multicoupe CT Project. Votre MMR augmente ou diminue selon le r&eacute;sultat.</p>
		<ul>
			<li>Les <strong>tiers</strong> sont bas&eacute;s sur votre MMR. Le tier "All" est ouvert &agrave; tous.</li>
			<li>Un <strong>mogi</strong> dure 12 courses et d&eacute;marre d&egrave;s que 4 &agrave; 8 joueurs sont en file.</li>
			<li>Des <strong>strikes</strong> sont donn&eacute;s pour AFK, vote manqu&eacute; ou abandon en cours de partie. Trop de strikes entra&icirc;nent un bannissement temporaire.</li>
			<li>Le <strong>MMR</strong> est calcul&eacute; en fin de mogi selon votre classement final.</li>
		</ul>
		<?php } ?>
	</section>
</div>
<?php } ?>
</body>
</html>
