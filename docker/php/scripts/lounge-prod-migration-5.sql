-- CT Lounge, fifth pass: indexes for the lookups the lounge tick and the profile make on
-- every poll, which otherwise scan tables that only ever grow. Safe to re-run.
ALTER TABLE `mklounge_queues`
  ADD KEY IF NOT EXISTS `status` (`status`),
  ADD KEY IF NOT EXISTS `privgame_key` (`privgame_key`);
ALTER TABLE `mklounge_matches`
  ADD KEY IF NOT EXISTS `privgame_key` (`privgame_key`);
ALTER TABLE `mklounge_match_players`
  ADD KEY IF NOT EXISTS `player` (`player`,`match`);
