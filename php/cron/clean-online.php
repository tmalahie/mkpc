<?php
// Was the MySQL event `mkcleanonline`: closes the online rooms nobody is playing in any more,
// after copying them to the history, and clears everything that hung off them.
require_once(__DIR__ .'/bootstrap.php');
exit(cron_run('clean-online', array(
	'SET @max_recency=(UNIX_TIMESTAMP()-300)*1000',
	'SET @max_recency_older=(UNIX_TIMESTAMP()-2400)*1000',
	'INSERT IGNORE INTO mkracehist SELECT * FROM mariokart',
	'DELETE m FROM mariokart m INNER JOIN
	(SELECT m.id,IFNULL(MAX(p.connecte)*67,0) AS maxt1, (CASE WHEN m.time<100000000000 THEN m.time*1000 ELSE m.time END) AS maxt2 FROM mariokart m LEFT JOIN mkplayers p ON p.course=m.id GROUP BY m.id HAVING((maxt1<@max_recency AND maxt2<@max_recency) OR maxt2<@max_recency_older)) t ON t.id=m.id',
	'DELETE p FROM mkplayers p LEFT JOIN mariokart m ON p.course=m.id WHERE m.id IS NULL',
	'DELETE i FROM items i LEFT JOIN mariokart m ON i.course=m.id WHERE m.id IS NULL',
	'DELETE v,p FROM mkchatvoc v LEFT JOIN mariokart m ON v.course=m.id LEFT JOIN mkchatvocpeer p ON v.id=p.sender OR v.id=p.receiver WHERE m.id IS NULL',
	'DELETE s FROM mkspectators s LEFT JOIN mariokart m ON s.course=m.id WHERE m.id IS NULL',
	'UPDATE mkjoueurs j LEFT JOIN mariokart m ON j.course=m.id SET j.course=0 WHERE m.id IS NULL'
)));
