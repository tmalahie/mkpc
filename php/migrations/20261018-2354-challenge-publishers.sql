ALTER TABLE `mkpublishers` MODIFY `type` enum('arenes','circuits','mkcircuits','mkcups','mkmcups','mkchars','mkchallenges') NOT NULL;

INSERT IGNORE INTO `mkpublishers` (type,creation_id,publisher,last_editor,published_at,updated_at)
SELECT 'mkchallenges', c.id, MIN(w.player), MIN(w.player), c.date, c.date
FROM `mkchallenges` c
INNER JOIN `mkclwin` w ON w.challenge=c.id AND w.creator=1
WHERE c.status IN ('pending_moderation','active','deleted') OR EXISTS (SELECT 1 FROM `mkclvalidations` v WHERE v.challenge=c.id)
GROUP BY c.id;

INSERT IGNORE INTO `mkpublishers` (type,creation_id,publisher,last_editor,published_at,updated_at)
SELECT 'mkchallenges', x.id, x.player, x.player, x.date, x.date
FROM (
	SELECT c.id, c.date, (
		SELECT p.id FROM `mkprofiles` p INNER JOIN `mkjoueurs` j ON p.id=j.id
		WHERE p.identifiant=l.identifiant AND p.identifiant2=l.identifiant2 AND p.identifiant3=l.identifiant3 AND p.identifiant4=l.identifiant4
		ORDER BY j.deleted, j.pts_challenge DESC, p.id DESC LIMIT 1
	) AS player
	FROM `mkchallenges` c
	INNER JOIN `mkclrace` l ON l.id=c.clist
	WHERE c.status IN ('pending_moderation','active','deleted') OR EXISTS (SELECT 1 FROM `mkclvalidations` v WHERE v.challenge=c.id)
) x
WHERE x.player IS NOT NULL;
