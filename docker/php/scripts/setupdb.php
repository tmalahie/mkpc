<?php
$dbh = new PDO('mysql:host=db', 'root', 'root');
$dbh->query(file_get_contents('setup.sql'));