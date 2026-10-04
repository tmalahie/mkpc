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
	// Numbers print without thousands separators, like everywhere else on the site
	// (and a bare {id} is often inside a URL, where "12,345" would break it).
	foreach ($params as $name => $value) {
		if ((is_int($value) || is_float($value)) && !preg_match('/\{\s*'. preg_quote($name, '/') .'\s*,/', $message))
			$params[$name] = (string) $value;
	}
	// ICU treats a single quote as an escape character; doubling every quote makes them all literal.
	$formatter = MessageFormatter::create($messageLocale, str_replace("'", "''", ungroupPluralNumbers($message)));
	if (!$formatter)
		return $message;
	$formatted = $formatter->format($params);
	return ($formatted === false) ? $message : $formatted;
}

// Rewrites each # of a plural as {count, number, ::group-off}, so that "9532 messages" isn't printed "9,532 messages".
function ungroupPluralNumbers(string $message): string {
	if (strpos($message, '#') === false)
		return $message;
	$result = '';
	$stack = array();
	$length = strlen($message);
	for ($i = 0; $i < $length; $i++) {
		$c = $message[$i];
		$top = end($stack);
		if ($c === '{') {
			if ($top && $top['type'] === 'choice') {
				$stack[] = array('type' => 'branch', 'plural' => $top['plural']);
			}
			elseif (preg_match('/\G\{\s*(\w+)\s*,\s*(plural|selectordinal|select)\s*,/', $message, $match, 0, $i)) {
				$plural = ($match[2] === 'select') ? ($top['plural'] ?? null) : $match[1];
				$stack[] = array('type' => 'choice', 'plural' => $plural);
				$result .= $match[0];
				$i += strlen($match[0]) - 1;
				continue;
			}
			else {
				$stack[] = array('type' => 'argument', 'plural' => null);
			}
		}
		elseif ($c === '}') {
			array_pop($stack);
		}
		elseif ($c === '#' && $top && $top['type'] === 'branch' && $top['plural'] !== null) {
			$result .= '{'. $top['plural'] .', number, ::group-off}';
			continue;
		}
		$result .= $c;
	}
	return $result;
}
