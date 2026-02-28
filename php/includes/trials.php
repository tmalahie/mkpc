<?php
include('getId.php');
$trialConfigs = array(
    'hidemsgcount' => array(
        'eval' => function(&$scope) {        
            if (in_array($scope['id'], array(1,2457,2843,3567,3586,5164,36925,40396,40764,73585,142608)))
                return withoutTracking(false);
            return $scope['range']() < 0.5;
        }
    )
);

function trialRange($key, $identifiant) {
    $fullKey = "$key.$identifiant";
    $crc = crc32($fullKey);
    return (float)($crc % 10000) / 10000;
}

function evaluateTrial($key) {
    global $id, $identifiants, $trialConfigs;
    $identifiant = $identifiants[0];
    if (!isset($trialConfigs[$key]))
        return false;
    $trialConfig = $trialConfigs[$key];
    $scope = array(
        'id' => $id,
        'identifiant' => $identifiant,
        'range' => function() use ($key, $identifiant) {
            return trialRange($key, $identifiant);
        }
    );
    $res = $trialConfig['eval']($scope);
    $shouldTrack = true;
    if (is_array($res)) {
        if (isset($res['tracking']) && !$res['tracking'])
            $shouldTrack = false;
        $res = $res['value'];
    }
    if ($shouldTrack) {
        mysql_query(
            'INSERT INTO mktrialtracking (trial_key, user, identifiant, trial_group)
            VALUES ("' . $key . '", ' . intval($id) . ', ' . $identifiant . ', ' . ($res ? '1' : '0') . ')
            ON DUPLICATE KEY UPDATE trial_group = VALUES(trial_group)'
        );
    }
    return $res;
}
$trialsCache = array();
function isTrialEnabled($key) {
    global $trialsCache;
    if (!isset($trialsCache[$key]))
        $trialsCache[$key] = evaluateTrial($key);
    return $trialsCache[$key];
}
function isTrialDisabled($key) {
    return !isTrialEnabled($key);
}

function withoutTracking($res) {
    return array(
        'tracking' => false,
        'value' => $res
    );
}