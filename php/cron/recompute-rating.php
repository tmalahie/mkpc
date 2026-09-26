<?php
// Was the MySQL event `mkrecomputerating`: recomputes every creation's rating score from its
// votes, recent votes weighing more than old ones.
require_once(__DIR__ .'/bootstrap.php');
exit(cron_run('recompute-rating', array(
	'DROP TEMPORARY TABLE IF EXISTS tmp_rating',
	'CREATE TEMPORARY TABLE tmp_rating
	SELECT p.type,p.circuit,p.rating,p.tscore,p.nb_of_rating AS nb_of_rating,p.nb_of_rating*1/(1+POW(TIMESTAMPDIFF(SECOND,IFNULL(c1.publication_date,IFNULL(c2.publication_date,IFNULL(c3.publication_date,IFNULL(c4.publication_date,IFNULL(c5.publication_date,"2018-01-01"))))),NOW())/(76*3600),2)) AS weight FROM
	(SELECT t.type,t.circuit,t.nb,o.rating,o.tscore,COUNT(r.rating) AS nb_of_rating FROM
	((SELECT type,circuit,COUNT(id) AS nb FROM mkratings GROUP BY type,circuit) t
	INNER JOIN
	(SELECT rating,tscore FROM mkratingoptions) o
	LEFT JOIN
	(SELECT type,circuit,rating,date FROM mkratings) r ON t.type=r.type AND t.circuit=r.circuit AND r.rating=o.rating) GROUP BY t.type,t.circuit,o.rating) p
	LEFT JOIN mkcircuits c1 ON p.type="mkcircuits" AND p.circuit=c1.id
	LEFT JOIN circuits c2 ON p.type="circuits" AND p.circuit=c2.id
	LEFT JOIN arenes c3 ON p.type="arenes" AND p.circuit=c3.id
	LEFT JOIN mkcups c4 ON p.type="mkcups" AND p.circuit=c4.id
	LEFT JOIN mkmcups c5 ON p.type="mkmcups" AND p.circuit=c5.id',
	'SET @K=5',
	'SET @za2=1.65',
	'DROP TEMPORARY TABLE IF EXISTS tmp_tscore',
	'CREATE TEMPORARY TABLE tmp_tscore
	SELECT s.type,s.circuit, s.sigma2 - @za2*SQRT((s.sigma1-s.sigma2*s.sigma2)/(s.sum+@K+1)) AS tscore
	FROM
	(SELECT p.type,p.circuit,p2.sum,
	SUM((p.tscore*p.tscore)*(p.weight+1)/(p2.sum+@K)) AS sigma1,
	SUM(p.tscore*(p.weight+1)/(p2.sum+@K)) AS sigma2
	FROM tmp_rating p INNER JOIN (SELECT type,circuit,SUM(weight) AS sum FROM tmp_rating GROUP BY type,circuit) p2 ON p.type=p2.type AND p.circuit=p2.circuit
	GROUP BY p.type,p.circuit) s',
	'UPDATE tmp_tscore p
	LEFT JOIN mkcircuits c1 ON p.type="mkcircuits" AND p.circuit=c1.id
	LEFT JOIN circuits c2 ON p.type="circuits" AND p.circuit=c2.id
	LEFT JOIN arenes c3 ON p.type="arenes" AND p.circuit=c3.id
	LEFT JOIN mkcups c4 ON p.type="mkcups" AND p.circuit=c4.id
	LEFT JOIN mkmcups c5 ON p.type="mkmcups" AND p.circuit=c5.id
	SET c1.tscore=p.tscore,c2.tscore=p.tscore,c3.tscore=p.tscore,c4.tscore=p.tscore,c5.tscore=p.tscore'
)));
