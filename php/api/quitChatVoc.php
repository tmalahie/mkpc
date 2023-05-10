<?php
header('Content-Type: text/plain');
if (isset($_POST['peer'])) {
    include('../includes/session.php');
    if ($id) {
        include('../includes/initdb.php');
        $peer = intval($_POST['peer']);
        mysql_query('DELETE v,p FROM `mkchatvoc` v LEFT JOIN `mkchatvocpeer` p ON v.id=p.sender OR v.id=p.receiver WHERE v.player='.$id.' AND v.id='.$peer);
    }
    echo 1;
}
?>