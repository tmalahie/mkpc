-- What each race was worth, kept alongside the finishing position `mkmatches` already holds.
-- Safe to re-run. Existing rows keep NULL points: the two columns are filled from the race
-- that writes the row, and nothing can reconstruct them afterwards.
--
-- `link` is the private game the race belonged to (0 for a public one). `mkmatches`.`course`
-- is the room, and rooms live in a MEMORY table - the id is gone, and reusable, the moment
-- the game ends. The link is what a custom game's races can still be grouped by a week later.
--
-- On a table this size (~3.3M rows in production) the ALTER rebuilds it, so expect it to
-- take a minute and to need the room for a second copy.
ALTER TABLE `mkmatches`
  ADD COLUMN IF NOT EXISTS `link` int(10) unsigned NOT NULL DEFAULT 0 AFTER `course`,
  ADD COLUMN IF NOT EXISTS `pts_before` int(11) DEFAULT NULL AFTER `rank`,
  ADD COLUMN IF NOT EXISTS `pts_inc` smallint(6) DEFAULT NULL AFTER `pts_before`,
  ADD KEY IF NOT EXISTS `link` (`link`);
