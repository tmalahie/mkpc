<?php
include('session.php');
$res = 0;
if (isset($_POST['peer']) && isset($_POST['sync'])) {
    include('session.php');
	if ($id) {
    	include('initdb.php');
	    if ($getCourse = mysql_fetch_array(mysql_query('SELECT course FROM `mkjoueurs` WHERE id="'.$id.'"'))) {
		    if ($getReceiver = mysql_fetch_array(mysql_query('SELECT id FROM mkchatvoc WHERE id="'.$_POST['peer'] .'" AND course="'. $getCourse['course'] .'" AND syncid="'. $_POST['sync'] .'"')))
                mysql_query('DELETE v,p FROM `mkchatvoc` v LEFT JOIN `mkchatvocpeer` p ON v.id=p.sender OR v.id=p.receiver WHERE v.id='.$getReceiver['id']);
	    }
    	mysql_close();
    }
	echo 1;
}
?>