-- Season 1's ranks, as staff set them on the ladder: the lower ones split in two, and Amethyst
-- added between Emerald and Diamond. A rank is looked up by its threshold, so replacing the
-- rows replaces the ladder.
DELETE FROM `mklounge_ranks`;
INSERT INTO `mklounge_ranks` (id, code, label, min_mmr, color, ordering) VALUES
	(1, 'iron1', 'Iron 1', 0, '#655d5d', 0),
	(2, 'iron2', 'Iron 2', 200, '#796f6f', 1),
	(3, 'bronze1', 'Bronze 1', 400, '#bb742d', 2),
	(4, 'bronze2', 'Bronze 2', 650, '#cd7f32', 3),
	(5, 'silver1', 'Silver 1', 900, '#99a3a3', 4),
	(6, 'silver2', 'Silver 2', 1050, '#a7b4b4', 5),
	(7, 'gold1', 'Gold 1', 1200, '#e9d11c', 6),
	(8, 'gold2', 'Gold 2', 1350, '#f4d80b', 7),
	(9, 'platinum', 'Platinum', 1500, '#9aabc6', 8),
	(10, 'emerald', 'Emerald', 1800, '#27dd6a', 9),
	(11, 'amethyst', 'Amethyst', 2100, '#bd46ff', 10),
	(12, 'diamond', 'Diamond', 2400, '#31f4ff', 11),
	(13, 'master', 'Master', 2700, '#000000', 12),
	(14, 'gm', 'GM', 3000, '#a32937', 13);
