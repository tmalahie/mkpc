<?php
include('../includes/language.php');
$isBattle = isset($_GET['battle']);
$table = $isBattle ? 'arenes':'circuits';
if (isset($_GET['i'])) {
    include('../includes/getId.php');
    include('../includes/initdb.php');
    $cID = intval($_GET['i']);
    $nID = $cID;
    $error = null;
    if ($circuit = mysql_fetch_array(mysql_query('SELECT id,img_data FROM `'.$table.'` WHERE id="'.$cID.'" AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]))) {
        $circuitImg = json_decode($circuit['img_data']);
        $circuitFilesData = array();
        include('../includes/file-quotas.php');
        $filesize = file_total_size();
        if ($circuitImg->local) {
            $circuitPath = CIRCUIT_BASE_PATH.$circuitImg->url;
            $filesize += filesize($circuitPath);
            if ($filesize >= MAX_FILE_SIZE)
                $error = $language ? 'You have exceeded your quota of '.filesize_str(MAX_FILE_SIZE).'. Delete tracks or use the "Paste image URL" option to save space.':'Vous avez dépassé votre quota de '.filesize_str(MAX_FILE_SIZE).'. Supprimez des circuits ou utilisez l\'option "Coller l\'URL de l\'image" pour gagner de l\'espace.';
            $ext = $circuitImg->ext;
            $circuitFilesData['0'] = array(
                'path' => $circuitPath,
                'suffix' => ".$ext"
            );
        }
        if (!$error) {
            if (isset($circuitImg->lapOverrides)) {
                foreach ($circuitImg->lapOverrides as $lapId => $lapOverride) {
                    if ($lapOverride->local) {
                        $circuitPath = CIRCUIT_BASE_PATH.$lapOverride->url;
                        $filesize += filesize($circuitPath);
                        if ($filesize >= MAX_FILE_SIZE)
                            $error = $language ? 'You have exceeded your quota of '.filesize_str(MAX_FILE_SIZE).'. Delete tracks or use the "Paste image URL" option to save space.':'Vous avez dépassé votre quota de '.filesize_str(MAX_FILE_SIZE).'. Supprimez des circuits ou utilisez l\'option "Coller l\'URL de l\'image" pour gagner de l\'espace.';
                        $ext = $lapOverride->ext;
                        $circuitFilesData[$lapId] = array(
                            'path' => $circuitPath,
                            'suffix' => "-$lapId.$ext"
                        );
                    }
                }
            }
        }
        if (!$error) {
            mysql_query('INSERT INTO `'.$table.'` SET identifiant='.$identifiants[0].',identifiant2='.$identifiants[1].',identifiant3='.$identifiants[2].',identifiant4='.$identifiants[3]);
            $nID = mysql_insert_id();
            $newCircuitImg = json_decode($circuit['img_data']);
            $src = $isBattle ? 'course':'map';
            foreach ($circuitFilesData as $lapId => $circuitFileData) {
                $newFilename = $src.$nID.$circuitFileData['suffix'];
                if ($lapId)
                    $newCircuitImg->lapOverrides->$lapId->url = $newFilename;
                else
                    $newCircuitImg->url = $newFilename;
                $newCircuitPath = CIRCUIT_BASE_PATH.$newFilename;
                copy($circuitFileData['path'], $newCircuitPath);
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