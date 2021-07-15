<?php
$characterRoster = null;
if (!empty($cOptions)) {
    $cOptionsJson = json_decode($cOptions);
    if (isset($cOptionsJson->persos)) {
        $customPersos = array();
        foreach ($cOptionsJson->persos as $perso) {
            if (is_int($perso))
                $customPersos[] = $perso;
        }
        if (!empty($customPersos)) {
            $customPersosString = implode(',', $customPersos);
            $getCustomPersos = mysql_query('SELECT * FROM mkchars WHERE id IN ('. $customPersosString .')');
            $customPersosById = array();
            require_once('persos.php');
            $aId = isset($id) ? $id : null;
            if (isset($_SESSION['mkid']))
                $id = $_SESSION['mkid'];
            else
                include('session.php');
            $unlockedChars = array();
            if ($id) {
                $getUnlockedChars = mysql_query('SELECT charid FROM mkclrewards r INNER JOIN mkclrewarded w ON w.reward=r.id AND w.player='. $id .' AND charid IN ('. $customPersosString .')');
                while ($unlockedChar = mysql_fetch_array($getUnlockedChars))
                    $unlockedChars[$unlockedChar['charid']] = 1;
            }
            while ($data = mysql_fetch_array($getCustomPersos)) {
                $spriteSrcs = get_sprite_srcs($data['sprites']);
                $customPersosById[$data['id']] = array (
                    'id' => +$data['id'],
                    'name' => iconv('utf-8', 'windows-1252', $data['name']),
                    'shared' => +($data['author'] !== null),
                    'sprites' => $data['sprites'],
                    'acceleration' => +$data['acceleration'],
                    'speed' => +$data['speed'],
                    'handling' => +$data['handling'],
                    'mass' => +$data['mass'],
                    'map' => $spriteSrcs['map'],
                    'podium' => $spriteSrcs['podium'],
                    'music' => get_perso_music($data),
                    'unlocked' => +(($data['author'] !== null) || isset($unlockedChars[$data['id']]) || ($data['identifiant'] == $identifiants[0]))
                );
            }
            $id = $aId;
        }
        $characterRoster = array();
        foreach ($cOptionsJson->persos as $perso) {
            if (is_int($perso)) {
                if (isset($customPersosById[$perso]))
                    $characterRoster[] = $customPersosById[$perso];
            }
            else {
                $characterRoster[] = array(
                    'sprites' => $perso
                );
            }
        }
    }
}
if (null !== $characterRoster)
    echo 'var characterRoster='. json_encode($characterRoster) .';';