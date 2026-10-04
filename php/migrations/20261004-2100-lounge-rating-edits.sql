-- What lounge moderators can change about a season's ratings, kept apart from the computed
-- values so the whole season can be replayed with them: a player's starting rating, and a
-- compensation added to a player's rating at a given mogi - and, for a table typed in rather
-- than played on the site, its score per GP.
ALTER TABLE `mklounge_players`
  ADD COLUMN IF NOT EXISTS `placement` double DEFAULT NULL AFTER `peak_mmr`;
ALTER TABLE `mklounge_match_players`
  ADD COLUMN IF NOT EXISTS `mmr_adjust` double DEFAULT NULL AFTER `mmr_penalty`,
  ADD COLUMN IF NOT EXISTS `gp_scores` varchar(255) DEFAULT NULL AFTER `final_position`;
