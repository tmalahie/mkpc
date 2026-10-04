<?php
include('../includes/session.php');
if (!$id) {
	echo t('admin.you_arent_logged');
	exit;
}
include('../includes/language.php');
include('../includes/initdb.php');
if (!$id) {
	echo t('admin.you_arent_logged');
	mysql_close();
	exit;
}
require_once('../includes/getRights.php');
if (!hasRight('manager')) {
	echo t('admin.you_arent_admin');
	mysql_close();
	exit;
}
if (hasRight('admin')) {
	$roleWithName = t('admin.administrator_rank');
}
elseif (hasRight('moderator')) {
	$roleWithName = t('admin.moderator_rank');
}
else {
	$roleWithName = t('admin.event_host_rank');
}
?>
<!DOCTYPE html>
<html lang="<?= $locale ?>">
<head>
<title>Admin - Mario Kart PC</title>
<?php
include('../includes/heads.php');
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
include('../includes/o_online.php');
?>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'forum';
include('../includes/menu.php');
?>
<main>
	<h1><?= t('admin.admin_page') ?></h1>
	<p class="success">
		<?= t('admin.your_gives_you_following_rights', roleWithName: $roleWithName) ?>
	</p>
	<h2><?= t('admin.member_management') ?></h2>
	<ul>
		<?php
		if (hasRight('admin')) {
			?>
		<li>
			<a class="action-ctn" href="roles.php">
				<div class="action-title"><?= t('admin.manage_staff_roles') ?></div>
				<div class="action-desc"><?= t('admin.see_who_moderator_event_host') ?></div>
			</a>
		</li>
			<?php
		}
		if (hasRight('moderator')) {
			?>
		<li>
			<a class="action-ctn" href="edit-pseudo.php">
				<div class="action-title"><?= t('admin.edit_members_username') ?></div>
				<div class="action-desc"><?= t('admin.can_useful_if_member_has') ?></div>
			</a>
		</li>
		<li>
			<a class="action-ctn" href="nick-history.php">
				<div class="action-title"><?= t('admin.see_username_change_history') ?></div>
				<div class="action-desc"><?= t('admin.monitor_people_who_would_abuse') ?></div>
			</a>
		</li>
		<li>
			<a class="action-ctn" href="nick-blacklist.php">
				<div class="action-title"><?= t('admin.manage_forbidden_words_usernames') ?></div>
				<div class="action-desc"><?= t('admin.members_can_no_longer_register') ?></div>
			</a>
		</li>
			<?php
		}
		if (hasRight('organizer')) {
			if (!hasRight('moderator')) {
			?>
			<li>
				<a class="action-ctn" href="updatepts.php">
					<div class="action-title"><?= t('admin.give_remove_points_online_mode') ?></div>
					<div class="action-desc"><?= t('admin.points_reason') ?></div>
				</a>
			</li>
			<?php
			}
		?>
		<li>
			<a class="action-ctn" href="awards.php">
				<div class="action-title"><?= t('admin.award_reward') ?></div>
				<div class="action-desc"><?= t('admin.following_official_event_oscars_festival') ?></div>
			</a>
		</li>
			<?php
		}
		?>
		<li>
			<a class="action-ctn" href="doublecomptes.php">
				<div class="action-title"><?= t('admin.see_alt_accounts') ?></div>
				<div class="action-desc"><?= t('admin.if_new_member_seems_suspicious') ?></div>
			</a>
		</li>
		<?php
		if (hasRight('moderator')) {
			?>
		<li>
			<a class="action-ctn" href="edit-user.php">
				<div class="action-title"><?= t('admin.edit_member_profile') ?></div>
				<div class="action-desc"><?= t('admin.can_useful_if_troll_member') ?></div>
			</a>
		</li>
		<li>
			<a class="action-ctn" href="ban-player.php">
				<div class="action-title"><?= t('admin.ban_warn_member') ?></div>
				<div class="action-desc"><?= t('admin.warn_user_innapropriate_behavior_ban') ?></div>
			</a>
		</li>
		<li>
			<a class="action-ctn" href="sanction-logs.php">
				<div class="action-title"><?= t('admin.see_members_infraction_log') ?></div>
				<div class="action-desc"><?= t('admin.every_warn_ban_member_has') ?></div>
			</a>
		</li>
			<?php
		}
		?>
	</ul>
	<?php
	if (hasRight('moderator')) {
		?>
	<h2><?= t('common.online_mode') ?></h2>
	<ul>
		<li>
			<a class="action-ctn" href="updatepts.php">
				<div class="action-title"><?= t('admin.online_give_remove_points') ?></div>
				<div class="action-desc"><?= t('admin.points_reason') ?></div>
			</a>
		</li>
		<li>
			<a class="action-ctn" href="chat-blacklist.php">
				<div class="action-title"><?= t('admin.manage_forbidden_watched_words_online') ?></div>
				<div class="action-desc"><?= t('admin.all_messages_containing_forbidden_words') ?></div>
			</a>
		</li>
		<li>
			<a class="action-ctn" href="chat-logs.php">
				<div class="action-title"><?= t('admin.see_online_mode_chat_logs') ?></div>
				<div class="action-desc">
					<?= t('admin.see_messages_member_online_mode') ?>
					<br />
					<?= t('admin.you_can_mute_members_case') ?>
				</div>
			</a>
		</li>
	</ul>
	<h2><?= t('admin.share_management') ?></h2>
	<ul>
		<li>
			<a class="action-ctn" href="creations.php?admin=1">
				<div class="action-title"><?= t('admin.delete_custom_track') ?></div>
				<div class="action-desc"><?= t('admin.if_content_track_inappropriate_case') ?></div>
			</a>
		</li>
		<li>
			<a class="action-ctn" href="creation-ratings.php">
				<div class="action-title"><?= t('admin.manage_ratings_tracks') ?></div>
				<div class="action-desc"><?= t('admin.monitor_eradicate_1_star_trolls') ?></div>
			</a>
		</li>
		<li>
			<a class="action-ctn" href="adminPersos.php">
				<div class="action-title"><?= t('admin.delete_character') ?></div>
				<div class="action-desc"><?= t('admin.case_plagiarism_if_eventual_cheating') ?></div>
			</a>
		</li>
		<li>
			<a class="action-ctn" href="findByCreation.php">
				<div class="action-title"><?= t('admin.find_author_given_creation') ?></div>
				<div class="action-desc"><?= t('admin.find_creator_circuit_published_anonymous') ?></div>
			</a>
		</li>
	</ul>
		<?php
	}
	?>
	<h2><?= t('admin.other_rights') ?></h2>
	<ul>
		<?php
		if (hasRight('moderator')) {
			?>
		<li>
			<div class="action-ctn">
				<div class="action-title"><?= t('admin.moderate_message_forum') ?></div>
				<div class="action-desc"><?= t('admin.do_this_go_message_question') ?></div>
			</div>
		</li>
		<li>
			<div class="action-ctn">
				<div class="action-title"><?= t('admin.moderate_comment_custom_track') ?></div>
				<div class="action-desc"><?= t('admin.go_track_question_click_edit') ?></div>
			</div>
		</li>
		<li>
			<div class="action-ctn">
				<div class="action-title"><?= t('admin.moderate_comment_news') ?></div>
				<div class="action-desc"><?= t('admin.go_news_question_click_edit') ?></div>
			</div>
		</li>
		<li>
			<a class="action-ctn" href="classement.php?moderate=1">
				<div class="action-title"><?= t('admin.moderate_time_trial_record') ?></div>
				<div class="action-desc"><?= t('admin.time_trial_leaderboard_click_moderate') ?></div>
		</a>
		</li>
		<li>
			<a class="action-ctn" href="adminReports.php">
				<div class="action-title"><?= t('admin.see_forum_reported_messages') ?></div>
				<div class="action-desc"><?= t('admin.quickly_perform_actions_what_members') ?></div>
			</a>
		</li>
			<?php
		}
		?>
		<li>
			<a class="action-ctn" href="admin-logs.php">
				<div class="action-title"><?= t('admin.see_admin_logs') ?></div>
				<div class="action-desc"><?= t('admin.retrace_understand_different_actions_done') ?></div>
			</a>
		</li>
	</ul>
	<p><a href="forum.php"><?= t('common.back_forum') ?></a><br />
	<a href="index.php"><?= t('common.back_mario_kart_pc') ?></a></p>
</main>
<?php
include('../includes/footer.php');
?>
<?php
mysql_close();
?>
</body>
</html>