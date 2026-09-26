-- CT Lounge, fourth pass: the event log a mogi can be investigated from.
-- Safe to re-run. Pruned by the lounge itself past LOUNGE_EVENT_RETENTION_DAYS.
CREATE TABLE IF NOT EXISTS `mklounge_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp(3) NOT NULL DEFAULT current_timestamp(3),
  `event` varchar(32) NOT NULL,
  `request` char(8) NOT NULL,
  `source` varchar(48) NOT NULL,
  `actor` int(10) unsigned DEFAULT NULL,
  `player` int(10) unsigned DEFAULT NULL,
  `queue` int(10) unsigned DEFAULT NULL,
  `privgame_key` int(10) unsigned DEFAULT NULL,
  `data` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`),
  KEY `player` (`player`,`id`),
  KEY `queue` (`queue`,`id`),
  KEY `privgame_key` (`privgame_key`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
