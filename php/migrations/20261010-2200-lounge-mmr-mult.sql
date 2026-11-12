-- Lorenzi's #mult: what a mogi's MMR changes are multiplied by, for events that count double,
-- half or not at all. Penalties and compensations are not multiplied.
ALTER TABLE `mklounge_matches`
  ADD COLUMN IF NOT EXISTS `mmr_mult` double NOT NULL DEFAULT 1 AFTER `mode`;
