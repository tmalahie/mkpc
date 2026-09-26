<?php
// Was the MySQL event `mkemptytrash`: deletes for good the tracks whose time in the trash is up,
// with their pieces, the cups and challenges built on them, and multicups left with no cup.
require_once(__DIR__ .'/bootstrap.php');
exit(cron_run('empty-trash', array(
	'SET @now=NOW()',
	'DELETE mkp,mka,mkb,mkc,mkd,mke,mkf,mkg,mkh,mki,mkj,mko,mkt
	FROM `mktrackbin` b
	LEFT JOIN mkcircuits c ON b.circuit=c.id AND b.type="mkcircuits"
	LEFT JOIN mkp ON mkp.circuit=c.id
	LEFT JOIN mka ON mka.circuit=c.id
	LEFT JOIN mkb ON mkb.circuit=c.id
	LEFT JOIN mkc ON mkc.circuit=c.id
	LEFT JOIN mkd ON mkd.circuit=c.id
	LEFT JOIN mke ON mke.circuit=c.id
	LEFT JOIN mkf ON mkf.circuit=c.id
	LEFT JOIN mkg ON mkg.circuit=c.id
	LEFT JOIN mkh ON mkh.circuit=c.id
	LEFT JOIN mki ON mki.circuit=c.id
	LEFT JOIN mkj ON mkj.circuit=c.id
	LEFT JOIN mko ON mko.circuit=c.id
	LEFT JOIN mkt ON mkt.circuit=c.id
	WHERE b.delete_at<@now',
	'DELETE b,c,
		p1,p2,p3,p4,
		t,
		r,r1,r2,r3,r4,
		h,h1,h2,h3,h4
	FROM `mktrackbin` b
	LEFT JOIN mkcircuits c ON b.circuit=c.id AND b.type="mkcircuits"
	LEFT JOIN mkcups p1 ON p1.circuit0=c.id AND p1.mode=0
	LEFT JOIN mkcups p2 ON p2.circuit1=c.id AND p2.mode=0
	LEFT JOIN mkcups p3 ON p3.circuit2=c.id AND p3.mode=0
	LEFT JOIN mkcups p4 ON p4.circuit3=c.id AND p4.mode=0
	LEFT JOIN mkmcups_tracks t ON t.cup IN (p1.id,p2.id,p3.id,p4.id)
	LEFT JOIN mkclrace r ON r.type="mkcircuits" AND r.circuit=c.id
	LEFT JOIN mkclrace r1 ON r1.type="mkcups" AND r1.circuit=p1.id
	LEFT JOIN mkclrace r2 ON r2.type="mkcups" AND r2.circuit=p2.id
	LEFT JOIN mkclrace r3 ON r3.type="mkcups" AND r3.circuit=p3.id
	LEFT JOIN mkclrace r4 ON r4.type="mkcups" AND r4.circuit=p4.id
	LEFT JOIN `mkchallenges` h ON h.clist=r.id
	LEFT JOIN `mkchallenges` h1 ON h1.clist=r1.id
	LEFT JOIN `mkchallenges` h2 ON h2.clist=r2.id
	LEFT JOIN `mkchallenges` h3 ON h3.clist=r3.id
	LEFT JOIN `mkchallenges` h4 ON h4.clist=r4.id
	WHERE b.delete_at<@now',
	'DELETE c FROM `mkmcups` c LEFT JOIN `mkmcups_tracks` t ON c.id=t.mcup WHERE t.mcup IS NULL'
)));
