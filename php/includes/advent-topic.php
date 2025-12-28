<?php
$adventTopicId = 19219;
$adventTopicUrl = "topic.php?topic=$adventTopicId";
$now = new DateTime('now', new DateTimeZone('Europe/Paris'));
$adventEnabled = !empty($id) && $now->format('n') == 12;