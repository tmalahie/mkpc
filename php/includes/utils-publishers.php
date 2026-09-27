<?php
function recordPublisher($type, $creationId, $playerId) {
	mysql_query('INSERT INTO `mkpublishers` SET type="'. $type .'",creation_id="'. $creationId .'",publisher="'. $playerId .'",last_editor="'. $playerId .'" ON DUPLICATE KEY UPDATE last_editor=VALUES(last_editor),updated_at=CURRENT_TIMESTAMP()');
}
function forgetPublisher($type, $creationId) {
	mysql_query('DELETE FROM `mkpublishers` WHERE type="'. $type .'" AND creation_id="'. $creationId .'"');
}
function getPublishers($type, $creationId) {
	return mysql_fetch_array(mysql_query('SELECT publisher,last_editor FROM `mkpublishers` WHERE type="'. $type .'" AND creation_id="'. $creationId .'"'));
}
?>