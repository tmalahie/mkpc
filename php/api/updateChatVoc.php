<?php
header('Content-Type: text/plain');
if (isset($_POST['peer']) && isset($_POST['muted'])) {
    include('../includes/session.php');
    if ($id) {
        include('../includes/initdb.php');
        $muted = intval($_POST['muted']);
        $peer = intval($_POST['peer']);
        mysql_query('UPDATE `mkchatvoc` SET muted="'. $muted .'" WHERE id="'. $peer .'" AND player="'. $id .'"');
    }
    echo 1;
}
?>