<?php
if (isset($_COOKIE['language']))
	$language = ($_COOKIE['language']==1) ? 1:0;
else {
	function findAcceptedLanguage($availableLanguages, $default) {
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
			$languages = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
		else
			$languages = array();
		$nbLanguages = count($availableLanguages);
		$id = $nbLanguages;
		foreach ($languages as $languageInfo) {
			$language = substr($languageInfo, 0,2);
			$i = array_search($language,$availableLanguages);
			if (($i !== false) && ($i < $id))
				$id = $i;
		}
		return ($id==$nbLanguages) ? $default:$availableLanguages[$id];
	}
	$language = (findAcceptedLanguage(array('fr','en'),'fr') == 'fr') ? 0:1;
	setcookie('language', $language, 4294967295,'/');
}
?>