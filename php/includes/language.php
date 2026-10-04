<?php
if (isset($_COOKIE['language'])) {
	$language = ($_COOKIE['language']==1) ? 1:0;
	$locale = $language == 0 ? "fr" : "en";
} else {
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
	$locale = findAcceptedLanguage(array('fr','en'),'en');
	$language = ($locale == 'fr') ? 0:1;
	setcookie('language', $language, 4294967295,'/');
}

// The catalogs are lang/<locale>.json, read on every request: an edit shows up on the next page load.
function translationCatalog(string $catalogLocale): array {
	static $catalogs = array();
	if (!isset($catalogs[$catalogLocale])) {
		$json = @file_get_contents(__DIR__ .'/../../lang/'. $catalogLocale .'.json');
		$catalogs[$catalogLocale] = ($json !== false) ? (json_decode($json, true) ?: array()) : array();
	}
	return $catalogs[$catalogLocale];
}

// t('home.many_more', url: 'credits.php') looks the key up in the current locale, falls back
// to English and then to the key itself, and formats the message with ICU MessageFormat:
// {url} is a placeholder, {count, plural, one {# message} other {# messages}} a plural.
function t(string $key, ...$params): string {
	global $locale;
	$messageLocale = $locale;
	$catalog = translationCatalog($messageLocale);
	if (!isset($catalog[$key])) {
		$messageLocale = 'en';
		$catalog = translationCatalog($messageLocale);
		if (!isset($catalog[$key]))
			return $key;
	}
	if (empty($params))
		return $catalog[$key];
	return formatTranslation($messageLocale, $catalog[$key], $params);
}

function formatTranslation(string $messageLocale, string $message, array $params): string {
	foreach ($params as $name => $value) {
		// Only arguments formatted as numbers ({n, plural, ...}, {n, number}) get locale grouping:
		// a bare {id} placeholder is often inside a URL, where "12,345" would break it.
		if ((is_int($value) || is_float($value)) && !preg_match('/\{\s*'. preg_quote($name, '/') .'\s*,/', $message))
			$params[$name] = (string) $value;
	}
	// ICU treats a single quote as an escape character; doubling every quote makes them all literal.
	$formatter = MessageFormatter::create($messageLocale, str_replace("'", "''", $message));
	if (!$formatter)
		return $message;
	$formatted = $formatter->format($params);
	return ($formatted === false) ? $message : $formatted;
}
