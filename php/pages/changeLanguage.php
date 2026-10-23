<?php
if (isset($_GET['nLanguage']))
	setcookie('language', $_GET['nLanguage'], 4294967295,'/');
$referer = !empty($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER']) : null;
$selfHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$refererHost = $referer && isset($referer['host']) ? $referer['host'] . (isset($referer['port']) ? ':'. $referer['port'] : '') : null;
if ($refererHost !== null && strcasecmp($refererHost, $selfHost) === 0)
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