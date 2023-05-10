<?php
include('../includes/getId.php');
include('../includes/initdb.php');
require_once('../includes/persos.php');
function toSQLSearch($search) {
    $search = str_replace('"', '""', $search);
    $search = str_replace('\\', '\\\\\\\\', $search);
    $search = str_replace('%', '\\%', $search);
    $search = '%'. $search .'%';
    return $search;
}
if (isset($_POST['page']) && isset($_POST['sort'])) {
    header('Content-Type: application/json');
    $page = intval($_POST['page']);
    $resPerPage = 100;
    switch ($_POST['sort']) {
    case 'rating':
        $order = 'avgrating DESC, nbratings DESC, id DESC';
        break;
    case 'playcount':
        $order = 'playcount DESC, id DESC';
        break;
    default:
        $order = 'publication_date DESC, id DESC';
    }
    $where = 'author IS NOT NULL';
    if (isset($_POST['name']))
        $where .= ' AND name LIKE "'. toSQLSearch($_POST['name']) .'"';
    if (isset($_POST['author']))
        $where .= ' AND author LIKE "'. toSQLSearch($_POST['author']) .'"';
    $allPersos = mysql_query('SELECT * FROM `mkchars` WHERE '.$where.' ORDER BY '.$order.' LIMIT '. ($page*$resPerPage) .','.$resPerPage);
    $res = array();
    while ($perso = mysql_fetch_array($allPersos))
        $res[] = array(get_perso_payload($perso),array(+$perso['acceleration'],+$perso['speed'],+$perso['handling'],+$perso['mass']));
    $next = (count($res) >= $resPerPage);
    echo json_encode(array(
        'list' => $res,
        'next' => $next
    ));
}