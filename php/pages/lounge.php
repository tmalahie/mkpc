<?php
include('../includes/session.php');
include('../includes/language.php');
$isLoungeMod = false;
if ($id) {
	include('../includes/initdb.php');
	require_once('../includes/getRights.php');
	require_once('../includes/lounge/common.php');
	$isLoungeMod = hasRight('lounge');
	mysql_close();
}
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
<script type="text/javascript" src="scripts/notify.js"></script>
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
			<button type="button" class="lounge-tab" data-tab="leaderboard"><?php echo $language ? 'Leaderboard':'Classement'; ?></button>
			<button type="button" class="lounge-tab" data-tab="howitworks">?</button>
<?php if ($isLoungeMod) { ?>
			<a class="lounge-tab lounge-modlink" href="admin-lounge.php" target="_top"><?php
				echo $language ? 'Moderation':'Mod&eacute;ration'; ?></a>
<?php } ?>
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

	<section class="lounge-tabpanel" data-panel="leaderboard">
		<div id="lounge-leaderboard">
			<span class="lounge-loading"><?php echo $language ? 'Loading...':'Chargement...'; ?></span>
		</div>
	</section>

	<section class="lounge-tabpanel" data-panel="howitworks">
		<div class="lounge-rules">
		<h2><?php echo $language ? 'Lounge rules':'R&egrave;gles du lounge'; ?></h2>
		<?php if ($language) { ?>
		<p>The CT Lounge is ranked matchmaking. Pick a tier, wait for the lineup to fill, vote on the game mode, then play a 12-race mogi on the CT Project multicup. Your MMR moves with your final standing.</p>
		<ul>
			<li><strong>Getting in.</strong> You need <?= LOUNGE_MIN_VS_POINTS ?> points in online VS mode and an account at least <?= LOUNGE_MIN_ACCOUNT_AGE_DAYS ?> days old.</li>
			<li><strong>Lineups</strong> start at 4 players and hold up to 8. Once 4 have gathered you have a few minutes for others to join, then everyone votes on the mode.</li>
			<li><strong>You cannot leave</strong> a lineup in the 15 seconds after joining it, or at all once the vote has started.</li>
			<li><strong>Courses</strong> may only be played once per mogi.</li>
			<li><strong>Leaving mid-mogi</strong> replaces you with a bot that keeps your name and your score, and costs you MMR: 25 if you miss more than 4 races, 10 otherwise. You can come back and take your kart over again.</li>
			<li><strong>Strikes</strong> are given for going AFK or not showing up. Enough of them bans you from ranked for a while.</li>
		</ul>
		<p>The full rule book is on the <a href="https://docs.google.com/document/d/1nxnpWSubiO-PoeHgPMkaLuloM1Vw8zvLD27PRlIU1OM/edit" target="_blank" rel="noopener">CT Lounge rules document</a>.</p>
		<?php } else { ?>
		<p>Le CT Lounge est un matchmaking class&eacute;. Choisissez un tier, attendez que l'effectif se remplisse, votez pour le mode de jeu, puis jouez un mogi de 12 courses sur la multicoupe CT Project. Votre MMR &eacute;volue selon votre classement final.</p>
		<ul>
			<li><strong>Pour entrer.</strong> Il faut <?= LOUNGE_MIN_VS_POINTS ?> points en mode en ligne VS et un compte d'au moins <?= LOUNGE_MIN_ACCOUNT_AGE_DAYS ?> jours.</li>
			<li><strong>Les effectifs</strong> d&eacute;marrent &agrave; 4 joueurs et montent jusqu'&agrave; 8. Une fois 4 joueurs r&eacute;unis, quelques minutes laissent le temps aux autres d'arriver, puis tout le monde vote pour le mode.</li>
			<li><strong>Impossible de quitter</strong> une file dans les 15 secondes qui suivent l'inscription, ni du tout une fois le vote commenc&eacute;.</li>
			<li><strong>Un circuit</strong> ne peut &ecirc;tre jou&eacute; qu'une seule fois par mogi.</li>
			<li><strong>Partir en cours de mogi</strong> vous fait remplacer par un bot qui garde votre nom et votre score, et co&ucirc;te du MMR&nbsp;: 25 si vous ratez plus de 4 courses, 10 sinon. Vous pouvez revenir reprendre votre kart.</li>
			<li><strong>Des strikes</strong> sont donn&eacute;s en cas d'AFK ou d'absence. Au bout de plusieurs, le classé vous est ferm&eacute; temporairement.</li>
		</ul>
		<p>Le r&egrave;glement complet est dans le <a href="https://docs.google.com/document/d/1nxnpWSubiO-PoeHgPMkaLuloM1Vw8zvLD27PRlIU1OM/edit" target="_blank" rel="noopener">document de r&egrave;gles du CT Lounge</a>.</p>
		<?php } ?>
		</div>
	</section>
</div>
<?php } ?>
</body>
</html>
