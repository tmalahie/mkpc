<?php
if (function_exists('mysql_connect'))
    $dbh = 1;
else {
    function mysql_connect($host, $user, $password) {
        global $dbconn;
        $dbconn = array(
            'host' => $host,
            'user' => $user,
            'password' => $password
        );
    }
    function mysql_select_db($db) {
        global $dbh, $dbconn;
        $dbh = new PDO('mysql:host='.$dbconn['host'].';dbname='.$db, $dbconn['user'], $dbconn['password']);
        unset($dbconn);
    }
    function mysql_set_charset($cs) {
        global $dbh;
        $dbh->exec("set names $cs");
    }
    function mysql_query($q) {
        global $dbh;
        return $dbh->query($q);
    }
    function mysql_close() {
        global $dbh;
        unset($dbh);
    }
    function mysql_fetch_array($q) {
        return $q ? $q->fetch() : null;
    }
    function mysql_real_escape_string($s) {
        global $dbh;
        return substr($dbh->quote($s), 1, -1);
    }
    function mysql_numrows($q) {
        return $q ? $q->rowCount() : 0;
    }
    function mysql_affected_rows($q) {
        return $q ? $q->rowCount() : 0;
    }
    function mysql_insert_id() {
        global $dbh;
        return $dbh->lastInsertId();
    }
    function mysql_error() {
        global $dbh;
        $res = $dbh->errorInfo();
        return $res ? $res[2] : null;
    }
}