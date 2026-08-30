USE mkpc;

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
  `label_en` varchar(32) NOT NULL,
  `label_fr` varchar(32) NOT NULL,
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
  `label_en` varchar(32) NOT NULL,
  `label_fr` varchar(32) NOT NULL,
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
  `placed` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`player`,`season`),
  KEY `season_mmr` (`season`,`mmr`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `mklounge_queues` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `season` int(10) unsigned NOT NULL,
  `tier` int(10) unsigned NOT NULL,
  `status` enum('open','locked','voting','launching','launched','finished','cancelled') NOT NULL DEFAULT 'open',
  `opened_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `locked_at` timestamp NULL DEFAULT NULL,
  `ready_at` timestamp NULL DEFAULT NULL,
  `launched_at` timestamp NULL DEFAULT NULL,
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
  `voted_pow` tinyint(1) DEFAULT NULL,
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
  `pow` tinyint(1) NOT NULL DEFAULT 0,
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
  `strike_reason` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`match`,`player`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `mklounge_seasons` (`id`,`name`,`multicup_id`) VALUES
  (1, 'Season 1', 10813);

-- Ranks and their colours mirror the production ladder (gb.hlorenzi.com/reg/oAFkjh).
-- Reference data: replaced wholesale so a re-run picks up threshold changes.
DELETE FROM `mklounge_ranks`;
INSERT INTO `mklounge_ranks` (`code`,`label_en`,`label_fr`,`min_mmr`,`color`,`ordering`) VALUES
  ('iron',     'Iron',     'Fer',      0,    '#796f6f', 0),
  ('bronze',   'Bronze',   'Bronze',   500,  '#cd7f32', 1),
  ('silver',   'Silver',   'Argent',   900,  '#a7b4b4', 2),
  ('gold',     'Gold',     'Or',       1200, '#f4d80b', 3),
  ('platinum', 'Platinum', 'Platine',  1400, '#9aabc6', 4),
  ('emerald',  'Emerald',  'Emeraude', 1600, '#27dd6a', 5),
  ('diamond',  'Diamond',  'Diamant',  1800, '#31f4ff', 6),
  ('master',   'Master',   'Maitre',   2000, '#4c4c4c', 7),
  ('gm',       'GM',       'GM',       2500, '#a32937', 8);

-- min_players: Tier All needs 6 to gather, every other tier 4 (rule 3aa), so that
-- players are pushed towards their own tier rather than all piling into Tier All.
INSERT IGNORE INTO `mklounge_tiers` (`code`,`label_en`,`label_fr`,`min_mmr`,`max_mmr`,`min_players`,`ordering`) VALUES
  ('all', 'Tier All', 'Tier All', 0,    NULL, 6, 0),
  ('C',   'Tier C',   'Tier C',   0,    999,  4, 1),
  ('B',   'Tier B',   'Tier B',   1000, 1999, 4, 2),
  ('A',   'Tier A',   'Tier A',   2000, 2999, 4, 3),
  ('X',   'Tier X',   'Tier X',   3000, NULL, 4, 4);
