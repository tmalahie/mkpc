-- Season 1 tiers, from staff on 2026-11-23: X Amethyst-GM, A Silver 2-GM, B Iron 1-Gold 1,
-- All everyone. Tier C is gone; it is only dropped while no queue or mogi points at it.
UPDATE `mklounge_tiers` SET min_mmr=0, max_mmr=NULL WHERE code='all';
UPDATE `mklounge_tiers` SET min_mmr=0, max_mmr=1349 WHERE code='B';
UPDATE `mklounge_tiers` SET min_mmr=1050, max_mmr=NULL WHERE code='A';
UPDATE `mklounge_tiers` SET min_mmr=2100, max_mmr=NULL WHERE code='X';
DELETE FROM `mklounge_tiers` WHERE code='C'
	AND id NOT IN (SELECT tier FROM `mklounge_queues`)
	AND id NOT IN (SELECT tier FROM `mklounge_matches`);
