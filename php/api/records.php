<?php
header('Content-Type: text/plain');

function fail($code = -1) {
    mysql_close();
    echo $code;
    exit;
}

$body = file_get_contents('php://input');

// Check if all required POST parameters are set
if (!isset($_POST["name"], $_POST["perso"], $_POST["time"])) {
    fail();
}

setcookie('mkrecorder', $_POST['name'], 4294967295, '/');

include('../includes/initdb.php');
include('../includes/getId.php');
include('../includes/session.php');
include('../includes/utils-hash.php');

// Validate the hash
if (!isHashValid($body)) {
    logHashInvalid($body);
    fail();
}

// Check if the user is banned
if ($id) {
    $isBanned = mysql_fetch_array(mysql_query("
        SELECT banned
        FROM mkjoueurs
        WHERE id='$id'
    "));
    if ($isBanned && $isBanned['banned']) {
        fail();
    }
}

// Validate the CC value
$cc = $_POST['cc'] ?? 150;
if (!in_array($cc, [150, 200])) {
    fail();
}

$map = null;
$type = '';

// Determine the map type and ID
if (isset($_POST['circuit'])) {
    $map = $_POST["circuit"];
} elseif (isset($_POST['creation'])) {
    $map = $_POST['creation'];
    if (!mysql_numrows(mysql_query("
        SELECT *
        FROM mkcircuits
        WHERE id='$map'
    "))) {
        $map = null;
    } else {
        $type = 'mkcircuits';
    }
} elseif (isset($_POST['map'])) {
    $map = $_POST['map'];
    if (!mysql_numrows(mysql_query("
        SELECT *
        FROM circuits
        WHERE id='$map'
    "))) {
        $map = null;
    } else {
        $type = 'circuits';
    }
}

// If a valid map is set, process the score
if (!isset($map)) {
    fail(0);
}

$name = ucwords($_POST["name"]);
if (strlen($name) > 30) {
    fail(2);
}

$time = round($_POST["time"]);

// Check if the nickname already exists for another user
$existingNick = mysql_fetch_array(mysql_query("
    SELECT j.id
    FROM mkjoueurs j
    INNER JOIN mkprofiles p ON j.id=p.id
    INNER JOIN mkrecords r ON r.name=j.nom AND p.identifiant=r.identifiant AND p.identifiant2=r.identifiant2 AND p.identifiant3=r.identifiant3 AND p.identifiant4=r.identifiant4
    WHERE j.nom='$name'
    AND j.id!='$id'
    AND (p.identifiant!='{$identifiants[0]}' OR p.identifiant2!='{$identifiants[1]}' OR p.identifiant3!='{$identifiants[2]}' OR p.identifiant4!='{$identifiants[3]}')
    LIMIT 1
"));

if ($existingNick) {
    fail(1);
}

$player = 0;
$getByName = mysql_fetch_array(mysql_query("
    SELECT j.id
    FROM mkjoueurs j
    INNER JOIN mkprofiles p ON j.id=p.id
    WHERE j.nom='$name'
    AND identifiant={$identifiants[0]}
    AND identifiant2={$identifiants[1]}
    AND identifiant3={$identifiants[2]}
    AND identifiant4={$identifiants[3]}
"));
if ($getByName) {
    $player = $getByName['id'];
} elseif ($id) {
    $player = $id;
} else {
    $getByIp = mysql_fetch_array(mysql_query("
        SELECT id
        FROM mkprofiles
        WHERE identifiant={$identifiants[0]}
        AND identifiant2={$identifiants[1]}
        AND identifiant3={$identifiants[2]}
        AND identifiant4={$identifiants[3]}
        ORDER BY nbmessages DESC
        LIMIT 1
    "));
    if ($getByIp) {
        $player = $getByIp['id'];
    }
}

// Update existing records for the player on this track if the new time is better
mysql_query("
    UPDATE mkrecords
    SET best=0
    WHERE class='$cc'
    AND type='$type'
    AND circuit='$map'
    AND player='$player'
    AND identifiant='{$identifiants[0]}'
    AND identifiant2='{$identifiants[1]}'
    AND identifiant3='{$identifiants[2]}'
    AND identifiant4='{$identifiants[3]}'
    AND time > $time
");

// Check if there is a better score already
$isBestScore = mysql_query("
    SELECT id
    FROM mkrecords
    WHERE class='$cc'
    AND type='$type'
    AND circuit='$map'
    AND player='$player'
    AND identifiant='{$identifiants[0]}'
    AND identifiant2='{$identifiants[1]}'
    AND identifiant3='{$identifiants[2]}'
    AND identifiant4='{$identifiants[3]}'
    AND best=1
");

if (mysql_fetch_array($isBestScore)) {
    fail(0);
}

// Insert the new best score
mysql_query("
    INSERT INTO mkrecords
    SET name='$name',
    identifiant='{$identifiants[0]}',
    identifiant2='{$identifiants[1]}',
    identifiant3='{$identifiants[2]}',
    identifiant4='{$identifiants[3]}',
    player=$player,
    perso='{$_POST["perso"]}',
    class='$cc',
    type='$type',
    circuit='$map',
    time='$time',
    best=1
");
$rId = mysql_insert_id();
$rank = mysql_numrows(mysql_query("
    SELECT *
    FROM mkrecords
    WHERE class='$cc'
    AND circuit='$map'
    AND type='$type'
    AND time < $time
    AND best=1
"));

// Send notifications for top 5 records on standard tracks
if (!$type && ($rank < 5)) {
    $playersToAlert = mysql_query("
        SELECT identifiant,identifiant2,identifiant3,identifiant4,MIN(time) AS record
        FROM (
            SELECT identifiant,identifiant2,identifiant3,identifiant4,time
            FROM mkrecords
            WHERE circuit='$map'
            AND class='$cc'
            AND type='$type'
            AND best=1
            ORDER BY time
            LIMIT 6
        ) t
        GROUP BY identifiant,identifiant2,identifiant3,identifiant4
    ");
    $getLastRecord = mysql_query("
        SELECT MIN(time) AS record
        FROM mkrecords
        WHERE identifiant={$identifiants[0]}
        AND identifiant2={$identifiants[1]}
        AND identifiant3={$identifiants[2]}
        AND identifiant4={$identifiants[3]}
        AND class='$cc'
        AND circuit='$map'
        AND player='$player'
        AND id!='$rId'
    ");
    $lastRecord = mysql_fetch_array($getLastRecord);
    $lastTime = $lastRecord['record'] ?? INF; // Use INF if no previous record

    while ($player = mysql_fetch_array($playersToAlert)) {
        if (($player['record'] <= $lastTime) && ($player['record'] > $time) && ($player['identifiant'] != $identifiants[0]) || ($player['identifiant2'] != $identifiants[1]) || ($player['identifiant3'] != $identifiants[2]) || ($player['identifiant4'] != $identifiants[3])) {
            mysql_query("
                INSERT INTO mknotifs
                SET type='new_record',
                identifiant='{$player['identifiant']}',
                identifiant2='{$player['identifiant2']}',
                identifiant3='{$player['identifiant3']}',
                identifiant4='{$player['identifiant4']}',
                link='$rId'
            ");
        }
    }
}

// Update time trial rankings for standard tracks
if (!$type) {
    mysql_query('SET @i=0, @c=0, @d=0, @t=0');
    mysql_query('START TRANSACTION');
    mysql_query("
        DELETE FROM mkttranking
        WHERE class='$cc'
    ");
    mysql_query("
        INSERT INTO mkttranking
        SELECT
            player,
            '$cc' AS class,
            SUM(
                CASE
                    WHEN rank = 1 THEN 10
                    ELSE
                        CASE
                            WHEN nb = 2 THEN 0
                            ELSE ROUND(8 * POW(1 - (rank - 2) / (nb - 2), 4 / 3))
                        END
                END
            ) AS score
        FROM (
            SELECT
                MIN(r.rank) AS rank,
                c.nb,
                r.circuit,
                r.player
            FROM (
                SELECT
                    (@d := (CASE
                            WHEN circuit = @c AND time = @t THEN @d + 1
                            ELSE 0
                        END)) AS _,
                    (@i := (CASE
                            WHEN circuit = @c THEN @i + 1
                            ELSE 1
                        END)) - @d AS rank,
                    (@c := circuit) AS circuit,
                    player,
                    (@t := time) AS t
                FROM mkrecords
                WHERE class = '$cc'
                AND type = '$type'
                AND best = 1
                ORDER BY circuit, time
            ) r
            INNER JOIN (
                SELECT
                    circuit,
                    COUNT(*) AS nb
                FROM mkrecords
                WHERE class = '$cc'
                AND type = '$type'
                AND best = 1
                GROUP BY circuit
            ) c ON r.circuit = c.circuit
            GROUP BY r.player, r.circuit
            HAVING r.player != 0
        ) t
        GROUP BY player
        HAVING score > 0
        ORDER BY score DESC
    ");
    mysql_query('COMMIT');
}

echo json_encode([
    1 + $rank,
    mysql_numrows(mysql_query("
        SELECT *
        FROM mkrecords
        WHERE class='$cc'
        AND circuit='$map'
        AND type='$type'
        AND best=1
    "))
]);

mysql_close();
?>