-- CT Lounge — bring production up to the feat/ranked schema.
-- Checked against production on 2026-09-14: mkgamedata.tracks and the `lounge` privilege
-- already exist, the nine mklounge_ranks rows already match, and every mklounge_queues row
-- is finished or cancelled, so nothing in flight is disturbed.
-- Safe to re-run: every statement is idempotent except the two ALTER ... MODIFY enums
-- (harmless to repeat) and the tier UPDATEs (idempotent by value).

-- 1. Notification type. Both enums keep their own charset, and mknotifs keeps the
--    ranked_match_ready value production carries and the dev dump does not (0 rows use it,
--    but dropping an enum value silently blanks any row that did).
ALTER TABLE `mknotifs` MODIFY `type`
  enum('answer_comment','answer_forum','circuit_comment','news_moderated','news_comment',
       'answer_newscom','forum_mention','forum_quote','follower_topic','follower_circuit',
       'follower_news','follower_perso','new_followtopic','new_followuser','currently_online',
       'challenge_moderated','follower_challenge','new_record','new_reaction','admin_report',
       'award','ranked_match_ready','lounge_queue')
  CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

ALTER TABLE `mknotifmute` MODIFY `type`
  enum('answer_comment','answer_forum','circuit_comment','news_moderated','news_comment',
       'answer_newscom','forum_mention','forum_quote','follower_topic','follower_circuit',
       'follower_news','follower_perso','new_followtopic','new_followuser','currently_online',
       'challenge_moderated','follower_challenge','new_record','reaction_topic',
       'reaction_newscom','reaction_news','reaction_trackcom','admin_report','award',
       'lounge_queue')
  CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

-- 2. Tables that did not exist yet.
CREATE TABLE IF NOT EXISTS `mklounge_state` (
  `name` varchar(48) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mklounge_discord_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `channel` varchar(32) NOT NULL,
  `content` text NOT NULL,
  `message_id` varchar(32) NOT NULL DEFAULT '',
  `action` varchar(8) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Columns added since the version production was cut from.
-- Cosmetic, so a future SHOW CREATE TABLE diff against the dev schema comes back empty.
ALTER TABLE `mklounge_seasons` MODIFY `multicup_id` int(10) unsigned NOT NULL DEFAULT 0;

ALTER TABLE `mklounge_players`
  ADD COLUMN IF NOT EXISTS `rules_accepted_at` timestamp NULL DEFAULT NULL AFTER `banned_until`,
  ADD COLUMN IF NOT EXISTS `unlock_dismissed_at` timestamp NULL DEFAULT NULL AFTER `rules_accepted_at`;

ALTER TABLE `mklounge_queues`
  MODIFY `status` enum('open','locked','voting','drafting','launching','launched','finished','cancelled') NOT NULL DEFAULT 'open',
  ADD COLUMN IF NOT EXISTS `discord_here_at` timestamp NULL DEFAULT NULL AFTER `launched_at`,
  ADD COLUMN IF NOT EXISTS `mode` varchar(8) DEFAULT NULL AFTER `discord_here_at`,
  ADD COLUMN IF NOT EXISTS `draft_turn_at` timestamp NULL DEFAULT NULL AFTER `mode`;

ALTER TABLE `mklounge_queue_members`
  ADD COLUMN IF NOT EXISTS `team` tinyint(4) DEFAULT NULL AFTER `voted_mode`;

ALTER TABLE `mklounge_match_players`
  ADD COLUMN IF NOT EXISTS `mmr_penalty` double DEFAULT NULL AFTER `mmr_delta`,
  ADD COLUMN IF NOT EXISTS `races_played` smallint(5) unsigned NOT NULL DEFAULT 0 AFTER `mmr_penalty`,
  ADD COLUMN IF NOT EXISTS `last_race` smallint(5) unsigned NOT NULL DEFAULT 0 AFTER `races_played`;

-- 4. Tier bands: the ranges staff gave on 2026-09-14, replacing the disjoint guess.
--    They overlap on purpose, so a player sees more than one tier unlocked.
UPDATE `mklounge_tiers` SET `min_mmr`=0,    `max_mmr`=NULL WHERE `code`='all';
UPDATE `mklounge_tiers` SET `min_mmr`=0,    `max_mmr`=1199 WHERE `code`='C';
UPDATE `mklounge_tiers` SET `min_mmr`=500,  `max_mmr`=1799 WHERE `code`='B';
UPDATE `mklounge_tiers` SET `min_mmr`=900,  `max_mmr`=NULL WHERE `code`='A';
UPDATE `mklounge_tiers` SET `min_mmr`=1600, `max_mmr`=NULL WHERE `code`='X';
UPDATE `mklounge_tiers` SET `min_players`=4;

-- 5. The six moderators staff named. 3586 is FwaysZedong, which is Fways - not the account
--    literally named `Fways` (227719).
INSERT IGNORE INTO `mkrights` (`player`, `privilege`)
  SELECT `id`, 'lounge' FROM `mkjoueurs`
  WHERE `id` IN (3586, 5164, 40764, 54725, 73585, 142608);
