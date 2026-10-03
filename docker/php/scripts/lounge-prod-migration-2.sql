-- CT Lounge, second pass. Checked against the test environment on 2026-09-15: everything from
-- lounge-prod-migration.sql is already applied - both notification enums carry lounge_queue,
-- every table and column matches - and the one remaining difference is the rank and tier names.
-- Safe to re-run: the names are set from their code, not copied out of the column being dropped.

-- One name per rank and tier instead of two. The ladder has always said Master, Diamond,
-- Emerald in both languages, so the French column was translationese nobody used.
ALTER TABLE `mklounge_ranks` ADD COLUMN IF NOT EXISTS `label` varchar(32) NOT NULL AFTER `code`;
ALTER TABLE `mklounge_tiers` ADD COLUMN IF NOT EXISTS `label` varchar(32) NOT NULL AFTER `code`;
UPDATE `mklounge_ranks` SET `label`=CASE `code`
  WHEN 'iron' THEN 'Iron'         WHEN 'bronze' THEN 'Bronze'
  WHEN 'silver' THEN 'Silver'     WHEN 'gold' THEN 'Gold'
  WHEN 'platinum' THEN 'Platinum' WHEN 'emerald' THEN 'Emerald'
  WHEN 'diamond' THEN 'Diamond'   WHEN 'master' THEN 'Master'
  WHEN 'gm' THEN 'GM'             ELSE `label` END;
UPDATE `mklounge_tiers` SET `label`=CONCAT('Tier ', IF(`code`='all', 'All', `code`));
ALTER TABLE `mklounge_ranks` DROP COLUMN IF EXISTS `label_en`, DROP COLUMN IF EXISTS `label_fr`;
ALTER TABLE `mklounge_tiers` DROP COLUMN IF EXISTS `label_en`, DROP COLUMN IF EXISTS `label_fr`;
