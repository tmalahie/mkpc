<?php
require_once('../includes/apc.php');
include('../includes/session.php');
if ($id)
    apcu_store("forum_rules_ack_$id", 1, 7*24*3600);
header('Location: topic.php?topic=19829');