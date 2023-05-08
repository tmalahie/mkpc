<?php
$payload = json_decode(file_get_contents('php://input'),true);
if (isset($payload['event']) && isset($payload['metadata'])) {
    include('../includes/initdb.php');
    include('../includes/session.php');
    $metadata = $payload['metadata'];
    if ($id)
        $metadata['logged_in'] = true;
    mysql_query('INSERT INTO mkanalytics SET event="'. mysql_real_escape_string($payload['event']) .'", metadata="'. mysql_real_escape_string(json_encode($metadata)) .'"');
    mysql_close();
}