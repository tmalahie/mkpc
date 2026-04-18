<?php
if (isset($_GET['id'])) {
    $decorId = intval($_GET['id']);
    $decorOptions = array('hitbox', 'spin', 'unbreaking', 'breaking');
    $rawItems = isset($_POST['items']) ? $_POST['items'] : '';
    include('../includes/initdb.php');
    include('../includes/getId.php');
    $newOptions = array();
    if ($row = mysql_fetch_array(mysql_query('SELECT options FROM `mkdecors` WHERE id="'. $decorId .'" AND identifiant="'. $identifiants[0] .'"'))) {
        if ($row['options'])
            $newOptions = json_decode($row['options'], true) ?: array();
    }
    foreach ($decorOptions as $option)
        unset($newOptions[$option]);
    unset($newOptions['items'], $newOptions['hitboxW']);
    foreach ($decorOptions as $option) {
        if (isset($_POST[$option]) && in_array($_POST[$option], array('0','1')))
            $newOptions[$option] = intval($_POST[$option]);
    }
    if (!empty($rawItems))
        $newOptions['items'] = json_decode($rawItems);
    if (isset($_POST['hitbox-cb']) && isset($_POST['hitboxW']))
        $newOptions['hitboxW'] = intval($_POST['hitboxW']);
    $newOptionsJson = !empty($newOptions) ? json_encode($newOptions) : '';
    mysql_query('UPDATE `mkdecors` SET options="'. mysql_real_escape_string($newOptionsJson) .'" WHERE id="'. $decorId .'" AND identifiant="'. $identifiants[0] .'"');
    mysql_close();
    header('location: decorOptions.php?id='. $decorId);
}