-- `placed` tells a staff placement from the start recorded when a player is first rated:
-- only the former puts a player on the ladder before their first mogi. Every placement so far
-- with no mogi behind it was set by staff.
UPDATE `mklounge_players` SET placed=1 WHERE placement IS NOT NULL AND games=0 AND placed=0;
