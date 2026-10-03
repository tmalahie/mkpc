<?php
header('Content-Type: application/json');
include('../../includes/session.php');
if (!$id) {
	echo json_encode(array('error' => 'auth'));
	exit;
}
include('../../includes/initdb.php');
include('../../includes/lounge/common.php');

lounge_accept_rules($id);
echo json_encode(array('ok' => true, 'rules_accepted' => lounge_has_accepted_rules($id)));
mysql_close();
