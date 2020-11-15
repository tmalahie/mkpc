<?php
$distrib = isset($_POST['distribution']) ? $_POST['distribution']:null;
include('initdb.php');
if (!isset($_GET['id'])) exit;
if ($distrib) {
    $newSettings = array(
        'range' => +$_POST['range'],
        'position' => $_POST['position']/100,
        'distribution' => json_decode($distrib)
    );
    if ($newSettings['distribution'])
        mysql_query('UPDATE metaitem SET settings="'. mysql_real_escape_string(json_encode($newSettings)) .'" WHERE id="'. $_GET['id'] .'"');
    else
        echo '<div style="color:red">Distribution invalide</div>';
}
$getItemSettings = mysql_query('SELECT * FROM metaitem WHERE id="'. $_GET['id'] .'"');
if ($itemSettings = mysql_fetch_array($getItemSettings)) {
    $settings = json_decode($itemSettings['settings']);
    if ($settings) {
        if ($distrib)
            $settings->distribution = $distrib;
        else
            $settings->distribution = json_encode($settings->distribution, JSON_PRETTY_PRINT);
    }
}
else
    exit;
?>
<form method="post">
    <p>
        Distribution basée sur :<br />
        la position à <input type="number" id="position" name="position" style="width:50px" value="<?php echo round(100*$settings->position) ?>" onchange="document.getElementById('distance').value=100-this.value" /> %<br />
        et la distance à <input type="number" id="distance" name="distance" style="width:50px" value="<?php echo round(100*(1-$settings->position)) ?>" onchange="document.getElementById('position').value=100-this.value" /> %
        <br /><br />
        Distance caractéristique (*) : <input type="number" name="range" style="width: 60px" value="<?php echo $settings->range; ?>" /><br />
        (*) = distance à partir de laquelle on récupère des items de dernier
        <br /><br />
        Distribution des objets :<br />
        <textarea name="distribution" style="width: 400px;height: 200px"><?php
        echo $settings->distribution;
        ?></textarea>
    </p>
    <p>
        <input type="submit" value="Valider les paramètres" /> - 
        <a href="resetMetaItem.php?id=<?php echo $_GET['id']; ?>" onclick="return confirm('Réinitialiser les paramètres ?')">Réinitialiser</a>
    </p>
</form>
<a href="mariokart.php?metakey=<?php echo $_GET['id']; ?>">Retour au jeu</a>
<?php
mysql_close();