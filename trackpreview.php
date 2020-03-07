<?php
if (isset($_GET['id']) && isset($_GET['type'])) {
	include('getSrcFromType.php');
	if (isset($isrc) && is_numeric($id))
		include($isrc .'.php');
}
?>