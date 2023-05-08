<?php
if ($language) {
	$categoryFields = 'name AS nom, summary AS description';
	$orderingField = 'ordering';
}
else {
	$categoryFields = 'nom, description';
	$orderingField = 'ordre';
}
?>