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
<link rel="stylesheet" type="text/css" href="styles/lounge.css?reload=5" />
<script type="text/javascript">
var language = <?php echo $language ? 'true':'false'; ?>;
var mId = <?php echo $id ? intval($id) : 'null'; ?>;
var mResultKey = <?php echo isset($_GET['key']) ? intval($_GET['key']) : 'null'; ?>;
var mPerso = <?php echo isset($_GET['perso']) ? json_encode(preg_replace('#[^\w\-]#', '', $_GET['perso'])) : 'null'; ?>;
</script>
<script type="text/javascript" src="scripts/xhr.js"></script>
<script type="text/javascript" src="scripts/notify.js"></script>
<script type="text/javascript" src="scripts/lounge.js?reload=2" defer></script>
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
			<a class="lounge-tab lounge-modlink" href="admin-lounge.php" target="_blank"><?php
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
		<div class="lounge-bar"><?php echo $language ? 'Season leaderboard':'Classement de la saison'; ?></div>
		<div id="lounge-leaderboard">
			<span class="lounge-loading"><?php echo $language ? 'Loading...':'Chargement...'; ?></span>
		</div>
	</section>

	<section class="lounge-tabpanel" data-panel="howitworks">
		<div class="lounge-bar"><?php echo $language ? 'Lounge rules':'R&egrave;gles du lounge'; ?></div>
		<div class="lounge-rules">
		<?php $discordLogo = '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M20.317 4.3697a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189Z"/></svg>'; ?>
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
		<p>The rest of the lounge lives on Discord: players gather there between mogis, results are posted, and the staff answers questions.</p>
		<p><a class="lounge-discord" href="<?= LOUNGE_DISCORD_INVITE ?>" target="_blank" rel="noopener"><?= $discordLogo ?>Join the CT Lounge Discord server</a></p>
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
		<p>Le reste du lounge se passe sur Discord&nbsp;: les joueurs s'y retrouvent entre deux mogis, les r&eacute;sultats y sont publi&eacute;s, et le staff y r&eacute;pond aux questions.</p>
		<p><a class="lounge-discord" href="<?= LOUNGE_DISCORD_INVITE ?>" target="_blank" rel="noopener"><?= $discordLogo ?>Rejoindre le serveur Discord du CT Lounge</a></p>
		<?php } ?>
		</div>
	</section>
</div>
<?php } ?>
</body>
</html>
