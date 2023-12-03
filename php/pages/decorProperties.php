<?php
if (isset($_GET['id'])) {
    include('../includes/initdb.php');
    include('../includes/getId.php');
    $decorId = intval($_GET['id']);
    $decorOptions = array('hitbox', 'spin', 'unbreaking', 'breaking');
    $newOptions = array();
    foreach ($decorOptions as $option) {
        if (isset($_POST[$option]) && in_array($_POST[$option], array('0','1')))
            $newOptions[$option] = intval($_POST[$option]);
    }
    if (!empty($_POST['items']))
        $newOptions['items'] = explode(',', $_POST['items']);
    $newOptionsJson = '';
    if (!empty($newOptions))
        $newOptionsJson = json_encode($newOptions);
    mysql_query('UPDATE `mkdecors` SET options="'. mysql_real_escape_string($newOptionsJson) .'" WHERE id="'. $decorId .'" AND identifiant="'. $identifiants[0] .'"');
    mysql_close();
    header('location: decorOptions.php?id='. $decorId);
}