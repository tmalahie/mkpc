USE mkpc;

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

CREATE TABLE IF NOT EXISTS `mklounge_settings` (
  `name` varchar(48) NOT NULL,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mklounge_seasons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `multicup_id` int(10) unsigned NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ended_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ended_at` (`ended_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `mklounge_tiers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(8) NOT NULL,
  `label` varchar(32) NOT NULL,
  `min_mmr` int(11) NOT NULL DEFAULT 0,
  `max_mmr` int(11) DEFAULT NULL,
  `min_players` tinyint(3) unsigned NOT NULL DEFAULT 4,
  `ordering` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `mklounge_ranks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(16) NOT NULL,
  `label` varchar(32) NOT NULL,
  `min_mmr` int(11) NOT NULL DEFAULT 0,
  `color` varchar(7) NOT NULL DEFAULT '#ffffff',
  `ordering` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `min_mmr` (`min_mmr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `mklounge_players` (
  `player` int(11) NOT NULL,
  `season` int(10) unsigned NOT NULL,
  `mmr` double NOT NULL DEFAULT 600,
  `peak_mmr` double NOT NULL DEFAULT 600,
  `games` int(11) NOT NULL DEFAULT 0,
  `wins` int(11) NOT NULL DEFAULT 0,
  `total_score` int(11) NOT NULL DEFAULT 0,
  `strikes` int(11) NOT NULL DEFAULT 0,
  `banned_until` timestamp NULL DEFAULT NULL,
  `rules_accepted_at` timestamp NULL DEFAULT NULL,
  `unlock_dismissed_at` timestamp NULL DEFAULT NULL,
  `placed` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`player`,`season`),
  KEY `season_mmr` (`season`,`mmr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `mklounge_queues` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `season` int(10) unsigned NOT NULL,
  `tier` int(10) unsigned NOT NULL,
  `status` enum('open','locked','voting','drafting','launching','launched','finished','cancelled') NOT NULL DEFAULT 'open',
  `opened_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `locked_at` timestamp NULL DEFAULT NULL,
  `ready_at` timestamp NULL DEFAULT NULL,
  `launched_at` timestamp NULL DEFAULT NULL,
  `discord_here_at` timestamp NULL DEFAULT NULL,
  `mode` varchar(8) DEFAULT NULL,
  `draft_turn_at` timestamp NULL DEFAULT NULL,
  `privgame_key` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `season_tier_status` (`season`,`tier`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `mklounge_queue_members` (
  `queue` int(10) unsigned NOT NULL,
  `player` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_heartbeat` timestamp NOT NULL DEFAULT current_timestamp(),
  `confirmed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `perso` varchar(250) DEFAULT NULL,
  `voted_mode` varchar(8) DEFAULT NULL,
  `team` tinyint(4) DEFAULT NULL,
  `dropped_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`queue`,`player`),
  KEY `player_active` (`player`,`dropped_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `mklounge_matches` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `queue` int(10) unsigned NOT NULL,
  `season` int(10) unsigned NOT NULL,
  `tier` int(10) unsigned NOT NULL,
  `privgame_key` int(10) unsigned NOT NULL,
  `mode` varchar(8) NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ended_at` timestamp NULL DEFAULT NULL,
  `cancelled_reason` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `queue` (`queue`),
  KEY `season_started` (`season`,`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `mklounge_match_players` (
  `match` int(10) unsigned NOT NULL,
  `player` int(11) NOT NULL,
  `perso` varchar(250) DEFAULT NULL,
  `team` tinyint(3) unsigned DEFAULT NULL,
  `final_score` int(11) DEFAULT NULL,
  `final_position` tinyint(3) unsigned DEFAULT NULL,
  `mmr_before` double DEFAULT NULL,
  `mmr_after` double DEFAULT NULL,
  `mmr_delta` double DEFAULT NULL,
  `mmr_penalty` double DEFAULT NULL,
  `races_played` smallint(5) unsigned NOT NULL DEFAULT 0,
  `last_race` smallint(5) unsigned NOT NULL DEFAULT 0,
  `strike_reason` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`match`,`player`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `mklounge_seasons` (`id`,`name`,`multicup_id`) VALUES
  (1, 'Season 1', 10813);

-- Ranks and their colours mirror the production ladder (gb.hlorenzi.com/reg/oAFkjh).
-- Reference data: replaced wholesale so a re-run picks up threshold changes.
DELETE FROM `mklounge_ranks`;
-- Names stay English in both languages: the ladder has always called them Master, Diamond,
-- Emerald, and the French renderings read as translationese to the players who use them.
INSERT INTO `mklounge_ranks` (`code`,`label`,`min_mmr`,`color`,`ordering`) VALUES
  ('iron',     'Iron',     0,    '#796f6f', 0),
  ('bronze',   'Bronze',   500,  '#cd7f32', 1),
  ('silver',   'Silver',   900,  '#a7b4b4', 2),
  ('gold',     'Gold',     1200, '#f4d80b', 3),
  ('platinum', 'Platinum', 1400, '#9aabc6', 4),
  ('emerald',  'Emerald',  1600, '#27dd6a', 5),
  ('diamond',  'Diamond',  1800, '#31f4ff', 6),
  ('master',   'Master',   2000, '#4c4c4c', 7),
  ('gm',       'GM',       2500, '#a32937', 8);

-- Bands are the rank ranges the ladder's staff gave, read off mklounge_ranks above:
-- C is Iron to Silver, B Bronze to Emerald, A Silver upwards, X Emerald upwards. They
-- overlap on purpose - a Diamond player chooses between A and X - so a max_mmr of NULL
-- means "no ceiling", not "top tier". Expected to be retuned for the next season.
-- min_players is 4 everywhere, including Tier All: rule 3aa still reads 6 there, but the
-- ladder lowered it to 4 for lack of activity and never wrote it back.
INSERT IGNORE INTO `mklounge_tiers` (`code`,`label`,`min_mmr`,`max_mmr`,`min_players`,`ordering`) VALUES
  ('all', 'Tier All', 0,    NULL, 4, 0),
  ('C',   'Tier C',   0,    1199, 4, 1),
  ('B',   'Tier B',   500,  1799, 4, 2),
  ('A',   'Tier A',   900,  NULL, 4, 3),
  ('X',   'Tier X',   1600, NULL, 4, 4);
