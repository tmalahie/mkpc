<?php
if (isset($_POST['id']) && isset($_POST['type'])) {
    include('language.php');
    include('initdb.php');
    require_once('collabUtils.php');
    $itemType = $_POST['type'];
    $itemId = $_POST['id'];
    switch ($itemType) {
    case 'arenes':
        $itemCategory = 'arena';
        break;
    case 'mkcups':
        $itemCategory = 'cup';
        break;
    case 'mkmcups':
        $itemCategory = 'multicup';
        break;
    case 'mkcircuits':
        if ($getType = mysql_fetch_array(mysql_query('SELECT type FROM mkcircuits WHERE id="'. $itemId .'"')))
            $itemCategory = $getType['type'] ? 'arena' : 'circuit';
        else
            $itemCategory = 'circuit';
        break;
    default:
        $itemCategory = 'circuit';
        break;
    }
    switch ($itemCategory) {
    case 'circuit':
        $itemLabel = $language ? "circuit" : "circuit";
        $theItemLabel = $language ? "the circuit" : "le circuit";
        break;
    case 'arena':
        $itemLabel = $language ? "arena" : "arène";
        $theItemLabel = $language ? "the arena" : "l'arène";
        break;
    case 'cup':
        $itemLabel = $language ? "cup" : "coupe";
        $theItemLabel = $language ? "the cup" : "la coupe";
        break;
    case 'multicup':
        $itemLabel = $language ? "multicup" : "multicoupe";
        $theItemLabel = $language ? "the multicup" : "la multicoupe";
        break;
    }
    $rights = array(
        array (
            'key' => 'view',
            'label' => $language ? "See $theItemLabel in editor" : "Voir $theItemLabel dans l'éditeur",
            'default_val' => true
        ),
        array (
            'key' => 'edit',
            'label' => $language ? "Edit $theItemLabel" : "Modifier $theItemLabel",
            'depends_on' => 'view'
        )
    );
    if ($itemCategory === 'circuit') {
        $rights[] = array(
            'key' => 'use',
            'label' => $language ? "Use $theItemLabel in cups" : "Utiliser $theItemLabel dans les coupes"
        );
    }
    if ($itemCategory === 'cup') {
        $rights[] = array(
            'key' => 'use',
            'label' => $language ? "Use $theItemLabel in multicups" : "Utiliser $theItemLabel dans les multicoupes"
        );
    }
    printCollabPopup(array(
        'type' => $itemType,
        'id' => $itemId,
        'item_label' => $itemLabel,
        'rights' => $rights,
        'class' => 'collab-track'
    ));
}