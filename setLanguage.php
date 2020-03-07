<?php
if (isset($_POST['nLanguage'])) {
	setcookie('language', $_POST['nLanguage'], 4294967295,'/');
	echo 1;
}
?>