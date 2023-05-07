<?php
include('session.php');
if (!$id) {
	echo _("You aren't logged in");
	exit;
}
include('language.php');
include('initdb.php');
if (!$id) {
	echo _("You aren't logged in");
	mysql_close();
	exit;
}
require_once('getRights.php');
if (!hasRight('manager')) {
	echo _("You aren't admin");
	mysql_close();
	exit;
}
if (hasRight('admin')) {
	$roleWithName = _("administrator rank");
}
elseif (hasRight('moderator')) {
	$roleWithName = _("moderator rank");
}
else {
	$roleWithName = _('event host rank');
}
?>
<!DOCTYPE html>
<html lang="<?= P_("html language", "en") ?>">
<head>
<title>Admin - Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<style type="text/css">
h2 {
	margin-bottom: 5px;
}
ul {
	display: inline-block;
	margin-top: 0px;
	padding-left: 10px;
	padding-right: 10px;
}
li {
	list-style: none;
}
.action-ctn {
	display: block;
	color: black;
	text-decoration: none;
	background-color: #FD9;
	margin: 8px 0;
	padding: 4px 6px;
	border-radius: 5px;
}
a.action-ctn:hover {
	background-color: #FEA;
	color: black;
}
.action-title {
	font-weight: bold;
	display: block;
	color: #F60;
	font-size: 1.2em;
}
.action-title strong {
	color: #C33;
}
.action-desc {
	color: #966;
}
</style>
<?php
include('o_online.php');
?>
</head>
<body>
<?php
include('header.php');
$page = 'forum';
include('menu.php');
?>
<main>
	<h1><?= _('Admin page') ?></h1>
	<p class="success">
		<?= F_("Your {roleWithName} gives you the following rights. Make good use of them!", roleWithName: $roleWithName) ?>
	</p>
	<h2><?= _("Member management") ?></h2>
	<ul>
		<?php
		if (hasRight('moderator')) {
			?>
		<li>
			<a class="action-ctn" href="edit-pseudo.php">
				<div class="action-title"><?= _("Edit a member's <strong>nick</strong>") ?></div>
				<div class="action-desc"><?= _("Can be useful if a member has a troll nick for example") ?></div>
			</a>
		</li>
		<li>
			<a class="action-ctn" href="nick-history.php">
				<div class="action-title"><?= _("See <strong>nick change history</strong>") ?></div>
				<div class="action-desc"><?= _("To monitor people who would abuse of this option.") ?></div>
			</a>
		</li>
			<?php
		}
		if (hasRight('organizer')) {
			if (!hasRight('moderator')) {
			?>
			<li>
				<a class="action-ctn" href="updatepts.php">
					<div class="action-title"><?= _("Give / Remove <strong>points</strong> in <strong>online mode</strong>") ?></div>
					<div class="action-desc"><?= _("As a reward for a tournament, or as punishment after a cheat...") ?></div>
				</a>
			</li>
			<?php
			}
		?>
		<li>
			<a class="action-ctn" href="awards.php">
				<div class="action-title"><?= _("Award a <strong>reward</strong>") ?></div>
				<div class="action-desc"><?= _("Following an official event (oscars, festival, ...)") ?></div>
			</a>
		</li>
			<?php
		}
		?>
		<li>
			<a class="action-ctn" href="doublecomptes.php">
				<div class="action-title"><?= _("See <strong>double accounts</strong>") ?></div>
				<div class="action-desc"><?= _("If a &quot;new&quot; member seems suspicious... (tool not 100% reliable)") ?></div>
			</a>
		</li>
		<?php
		if (hasRight('moderator')) {
			?>
		<li>
			<a class="action-ctn" href="edit-user.php">
				<div class="action-title"><?= _("Edit member <strong>profile</strong>") ?></div>
				<div class="action-desc"><?= _("Can be useful if a troll member has put an inappropriate description for example, or a fake country") ?></div>
			</a>
		</li>
		<li>
			<a class="action-ctn" href="ban-player.php">
				<div class="action-title"><?= _("<strong>Ban</strong> or <strong>warn</strong> a member") ?></div>
				<div class="action-desc"><?= _("Warn a user for innapropriate behavior, or ban them if he persists. Banned members are unable to post anything on the site") ?></div>
			</a>
		</li>
			<?php
		}
		?>
	</ul>
	<?php
	if (hasRight('moderator')) {
		?>
	<h2><?= _('Online mode') ?></h2>
	<ul>
		<li>
			<a class="action-ctn" href="updatepts.php">
				<div class="action-title"><?= _("Give / Remove <strong>points</strong> in online mode") ?></div>
				<div class="action-desc"><?= _("As a reward for a tournament, or as punishment after a cheat...") ?></div>
			</a>
		</li>
		<li>
			<a class="action-ctn" href="chat-blacklist.php">
				<div class="action-title"><?= _("Manage <strong>forbidden/watched words</strong> in online chat") ?></div>
				<div class="action-desc"><?= _("All messages containing forbidden words will be blocked and/or logged") ?></div>
			</a>
		</li>
		<li>
			<a class="action-ctn" href="chat-logs.php">
				<div class="action-title"><?= _("See online mode <strong>chat logs</strong>") ?></div>
				<div class="action-desc">
					<?= _("See the messages of the member in the online mode") ?>
					<br />
					<?= _("You can mute members in case of abuse") ?>
				</div>
			</a>
		</li>
	</ul>
	<h2><?= _('Share management') ?></h2>
	<ul>
		<li>
			<a class="action-ctn" href="creations.php?admin=1">
				<div class="action-title"><?= _("Delete a <strong>custom track</strong>") ?></div>
				<div class="action-desc"><?= _("If the content of the track is inappropriate or in case of plagiarism") ?></div>
			</a>
		</li>
		<li>
			<a class="action-ctn" href="creation-ratings.php">
				<div class="action-title"><?= _("Manage <strong>ratings</strong> on tracks") ?></div>
				<div class="action-desc"><?= _("To monitor and eradicate 1-star trolls...") ?></div>
			</a>
		</li>
		<li>
			<a class="action-ctn" href="adminPersos.php">
				<div class="action-title"><?= _("Delete a <strong>character</strong>") ?></div>
				<div class="action-desc"><?= _("In case of plagiarism or if eventual cheating (invisible character...)") ?></div>
			</a>
		</li>
		<li>
			<a class="action-ctn" href="findByCreation.php">
				<div class="action-title"><?= _("Find the <strong>author</strong> of a given creation") ?></div>
				<div class="action-desc"><?= _("Find the creator of a circuit published by an anonymous user") ?></div>
			</a>
		</li>
	</ul>
		<?php
	}
	?>
	<h2><?= _('Other rights') ?></h2>
	<ul>
		<?php
		if (hasRight('moderator')) {
			?>
		<li>
			<div class="action-ctn">
				<div class="action-title"><?= _("Moderate a message on the <strong>forum</strong>") ?></div>
				<div class="action-desc"><?= _("To do this, go to the message in question and click on &quot;Edit&quot; or &quot;Delete&quot;") ?></div>
			</div>
		</li>
		<li>
			<div class="action-ctn">
				<div class="action-title"><?= _("Moderate a <strong>comment</strong> on a <strong>custom track</strong>") ?></div>
				<div class="action-desc"><?= _("Go to the track in question and click on &quot;Edit&quot; or &quot;Delete&quot;") ?></div>
			</div>
		</li>
		<li>
			<div class="action-ctn">
				<div class="action-title"><?= _("Moderate a <strong>comment</strong> on a <strong>news</strong>") ?></div>
				<div class="action-desc"><?= _("Go to the news in question and click on &quot;Edit&quot; or &quot;Delete&quot;") ?></div>
			</div>
		</li>
		<li>
			<a class="action-ctn" href="classement.php?moderate=1">
				<div class="action-title"><?= _("Moderate a <strong>time trial</strong> record") ?></div>
				<div class="action-desc"><?= _("From the time trial leaderboard, click on &quot;Moderate records&quot;") ?></div>
		</a>
		</li>
		<li>
			<a class="action-ctn" href="adminReports.php">
				<div class="action-title"><?= _("See forum <strong>reported messages</strong>") ?></div>
				<div class="action-desc"><?= _("To quickly perform actions on what members reported") ?></div>
			</a>
		</li>
			<?php
		}
		?>
		<li>
			<a class="action-ctn" href="admin-logs.php">
				<div class="action-title"><?= _("See <strong>admin logs</strong>") ?></div>
				<div class="action-desc"><?= _("To retrace and understand the different actions done by MKPC staff") ?></div>
			</a>
		</li>
	</ul>
	<p><a href="forum.php"><?= _('Back to the forum') ?></a><br />
	<a href="index.php"><?= _('Back to Mario Kart PC') ?></a></p>
</main>
<?php
include('footer.php');
?>
<?php
mysql_close();
?>
</body>
</html>