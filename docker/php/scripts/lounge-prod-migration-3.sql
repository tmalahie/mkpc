-- CT Lounge, third pass: where each player stood on the ladder either side of a match.
-- Safe to re-run. Existing rows keep NULL - a ladder place cannot be reconstructed after the
-- fact, so "best/worst ranking" only counts matches played from here on.
ALTER TABLE `mklounge_match_players`
  ADD COLUMN IF NOT EXISTS `place_before` smallint(5) unsigned DEFAULT NULL AFTER `last_race`,
  ADD COLUMN IF NOT EXISTS `place_after` smallint(5) unsigned DEFAULT NULL AFTER `place_before`;
