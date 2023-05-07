<?php
if (isset($_POST['id']) && isset($_POST['type'])) {
    include('language.php');
    include('initdb.php');
    require_once('collabUtils.php');
    $itemType = $_POST['type'];
    $itemId = $_POST['id'];
    $itemLabel = $language ? "background" : "arrière-plan";
    $theItemLabel = $language ? "the background" : "l'arrière-plan";
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
        ),
        array (
            'key' => 'use',
            'label' => $language ? "Use $theItemLabel in tracks" : "Utiliser $theItemLabel dans les circuits",
            'default_val' => true
        )
    );
    printCollabPopup(array(
        'type' => $itemType,
        'id' => $itemId,
        'item_label' => $itemLabel,
        'rights' => $rights,
        'class' => 'collab-bg'
    ));
}