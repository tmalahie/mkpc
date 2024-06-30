<?php
if (isset($_POST['id'])) {
    include('../includes/language.php');
    include('../includes/initdb.php');
    include('../includes/getId.php');
    require_once('../includes/collabUtils.php');
    $itemType = 'mkchars';
    $itemId = $_POST['id'];
    $itemLabel = $language ? "character" : "perso";
    $theItemLabel = $language ? "the character" : "le perso";
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
        'class' => 'collab-perso'
    ));
}