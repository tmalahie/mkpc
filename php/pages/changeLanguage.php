<?php
if (isset($_GET['nLanguage']))
	setcookie('language', $_GET['nLanguage'], 4294967295,'/');
if (!empty($_SERVER['HTTP_REFERER']))
	header('Location: '. $_SERVER['HTTP_REFERER']);
else {
	if (isset($_GET['page'])) {
		switch ($_GET['page']) {
		case 'forum' :
			header('Location: forum.php');
			break;
		case 'game' :
			header('Location: mariokart.php');
			break;
		default :
			header('Location: index.php');
			break;
		}
	}
	else
		header('Location: index.php');
}
?>