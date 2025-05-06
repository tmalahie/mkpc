<?php
require_once('../includes/bbCode.php');

header('Content-Type: text/plain');

echo bbcode(file_get_contents('php://input'));