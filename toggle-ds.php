<?php
if (isset($_GET['enable'])) {
    setcookie('ds', '1', 4294967295, '/');
    define('IS_DS', 1);
}
elseif (isset($_GET['disable'])) {
    setcookie('ds', null, 0, '/');
    define('IS_DS', 0);
}
else
    require_once('isDS.php');
?>
Version DS : <?php
if (IS_DS)
    echo '<span style="color:green">Activée</span>';
else
    echo '<span style="color:red">Désactivée</span>';
?>
<br />
<button onclick="document.location.href='?<?php echo IS_DS ? 'disable':'enable'; ?>'"><?php echo IS_DS ? 'Désactiver':'Activer'; ?></button>
<br /><br /><a href="index.php">Retour</a>