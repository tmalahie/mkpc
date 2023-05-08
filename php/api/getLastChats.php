<?php
header('Content-Type: text/plain');
include('../includes/session.php');
if ($id) {
	include('../includes/initdb.php');
	$lastChats = mysql_query(
		'SELECT t.last_id,t.other,c.message,(c.seen OR c.sender='.$id.') AS seen,t.nbmsgs FROM (
			SELECT MAX(t.max_id) AS last_id,other,SUM(t.nb) AS nbmsgs FROM (
				SELECT receiver AS other,MAX(id) AS max_id,COUNT(*) AS nb FROM mkchats WHERE sender='.$id.' GROUP BY receiver
				UNION
				SELECT sender AS other,MAX(id) AS max_id,COUNT(*) AS nb FROM mkchats WHERE receiver='.$id.' GROUP BY sender
			) t
			GROUP BY other
			HAVING(other NOT IN(SELECT ignored FROM `mkignores` WHERE ignorer='.$id.'))
		) t
		INNER JOIN mkchats c ON c.id=t.last_id
		ORDER BY last_id DESC'
	);
	/*$lastChats = mysql_query(
		'SELECT other,nom,message,nbmsgs,seen FROM (
			SELECT * FROM (
				SELECT * FROM (
					(SELECT id,receiver AS other,message,1 AS seen FROM mkchats WHERE sender='.$id.')
					UNION
					(SELECT id,sender AS other,message,seen FROM mkchats WHERE receiver='.$id.')
				) tmp ORDER BY id DESC
			) tmp2 INNER JOIN (
				SELECT MAX(id) AS maxid,COUNT(*) AS nbmsgs,
				(CASE WHEN sender='.$id.' THEN receiver ELSE sender END) AS other2
				FROM mkchats
				WHERE sender='.$id.' OR receiver='.$id.'
				GROUP BY other2
				HAVING(other2 NOT IN(SELECT ignored FROM `mkignores` WHERE ignorer='.$id.'))
			) tmp4 ON tmp2.id=tmp4.maxid AND tmp2.other=tmp4.other2
		) tmp3 INNER JOIN `mkjoueurs` ON other=mkjoueurs.id ORDER BY maxid DESC'
	);*/
	echo '[';
	$v = '';
	function controlLength($str,$maxLength) {
		$pts = '...';
		if (mb_strlen($str) > $maxLength)
			return mb_substr($str,0,$maxLength-mb_strlen($pts)).$pts;
		return $str;
	}
	function controlLengthUtf8($str,$len) {
		return controlLength($str,$len);
	}
	include('../includes/o_utils.php');
	while ($chat = mysql_fetch_array($lastChats)) {
		if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id='. $chat['other']))) {
		  echo $v;
		  echo '[';
		  echo $chat['other'].',"'.$getPseudo['nom'].'","'.parse_msg(controlLengthUtf8($chat['message'],50)).'",'.$chat['nbmsgs'].','.$chat['seen'];
	  	echo ']';
		  $v = ',';
	 }
	}
	echo ']';
	mysql_close();
}
?>