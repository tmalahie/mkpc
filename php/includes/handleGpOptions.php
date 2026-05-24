<?php
// Resolves the custom characters referenced as GP CPU drivers (gp.cpus[].driver)
// into full character data. Drivers are stored as the stable numeric character
// id; the sprites path (which changes when the image is updated) is deduced here
// at build time. Scans both the cup's own options ($cOptions) and, for
// multicups, each sub-cup's options ($cupPayloads). Emits `var cupCustomChars`.
if (!function_exists('collectGpDriverIds')) {
    function collectGpDriverIds($opts, &$ids) {
        if (is_string($opts)) $opts = json_decode($opts);
        if (!$opts) return;
        $gp = is_object($opts) ? (isset($opts->gp) ? $opts->gp : null) : (isset($opts['gp']) ? $opts['gp'] : null);
        if (!$gp) return;
        $cpus = is_object($gp) ? (isset($gp->cpus) ? $gp->cpus : null) : (isset($gp['cpus']) ? $gp['cpus'] : null);
        if (!$cpus) return;
        foreach ($cpus as $cpu) {
            $driver = is_object($cpu) ? (isset($cpu->driver) ? $cpu->driver : null) : (isset($cpu['driver']) ? $cpu['driver'] : null);
            if (is_int($driver))
                $ids[$driver] = 1;
        }
    }
}
$gpDriverIds = array();
if (isset($cOptions))
    collectGpDriverIds($cOptions, $gpDriverIds);
if (isset($cupPayloads) && is_array($cupPayloads)) {
    foreach ($cupPayloads as $cupPayload) {
        if (isset($cupPayload['options']))
            collectGpDriverIds($cupPayload['options'], $gpDriverIds);
    }
}
$cupCustomChars = array();
if (!empty($gpDriverIds)) {
    require_once('persos.php');
    $idsStr = implode(',', array_map('intval', array_keys($gpDriverIds)));
    $getChars = mysql_query('SELECT * FROM mkchars WHERE id IN ('. $idsStr .')');
    while ($data = mysql_fetch_array($getChars)) {
        $spriteSrcs = get_sprite_srcs($data['sprites']);
        $cupCustomChars[$data['id']] = array(
            'id' => +$data['id'],
            'name' => $data['name'],
            'sprites' => $data['sprites'],
            'acceleration' => +$data['acceleration'],
            'speed' => +$data['speed'],
            'handling' => +$data['handling'],
            'mass' => +$data['mass'],
            'map' => $spriteSrcs['map'],
            'podium' => $spriteSrcs['podium'],
            'music' => get_perso_music($data)
        );
    }
}
echo 'var cupCustomChars = '. json_encode($cupCustomChars, JSON_UNESCAPED_UNICODE) .';';
