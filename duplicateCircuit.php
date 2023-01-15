<?php
include('language.php');
$isBattle = isset($_GET['battle']);
$table = $isBattle ? 'arenes':'circuits';
if (isset($_GET['i'])) {
    include('getId.php');
    include('initdb.php');
    $cID = intval($_GET['i']);
    $nID = $cID;
    $error = null;
    if ($circuit = mysql_fetch_array(mysql_query('SELECT id,img_data FROM `'.$table.'` WHERE id="'.$cID.'" AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]))) {
        $circuitImg = json_decode($circuit['img_data']);
        $circuitPath = null;
        if ($circuitImg->local) {
			include('file-quotas.php');
            $circuitPath = CIRCUIT_BASE_PATH.$circuitImg->url;
            $filesize = filesize($circuitPath);
            $filesize += file_total_size();
            if ($poids >= MAX_FILE_SIZE)
                $error = $language ? 'You have exceeded your quota of '.filesize_str(MAX_FILE_SIZE).'. Delete tracks or use the &quot;Paste image URL&quot; option to save space.':'Vous avez dépassé votre quota de '.filesize_str(MAX_FILE_SIZE).'. Supprimez des circuits ou utilisez l\'option &quot;Coller l\'URL de l\'image&quot; pour gagner de l\'espace.';
        }
        if (!$error) {
            mysql_query('INSERT INTO `'.$table.'` SET identifiant='.$identifiants[0].',identifiant2='.$identifiants[1].',identifiant3='.$identifiants[2].',identifiant4='.$identifiants[3]);
            $nID = mysql_insert_id();
            $newCircuitImg = json_decode($circuit['img_data']);
            if ($circuitPath) {
                $ext = $circuitImg->ext;
                $src = $isBattle ? 'course':'map';
                $newCircuitImg->url = "$src$nID.$ext";
                $newCircuitPath = CIRCUIT_BASE_PATH.$newCircuitImg->url;
                copy($circuitPath, $newCircuitPath);
            }
            $imgDataRaw = mysql_real_escape_string(json_encode($newCircuitImg));
            mysql_query('UPDATE `'.$table.'` SET img_data="'.$imgDataRaw.'" WHERE id='.$nID);
            mysql_query('INSERT INTO `'.$table.'_data` (SELECT '.$nID.' AS id, data FROM `'.$table.'_data` WHERE id='.$cID.')');
        }
    }
    mysql_close();
    if (isset($error)) {
        header('Location: '.($isBattle ? 'course':'draw').'.php?error='.urlencode($error));
        exit;
    }
    header('Location: '.($isBattle ? 'course':'draw').'.php');
}
?>