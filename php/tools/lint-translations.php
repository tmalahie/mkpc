<?php
// Checks the translation catalogs in lang/ against each other and against the PHP code.
//   php php/tools/lint-translations.php
// Errors (exit code 1): invalid JSON or ICU syntax, keys used in the code but missing from
// lang/en.json, unused keys, keys or placeholders that differ from English, gettext calls.
// Warnings: keys English has but another language hasn't, which then shows the English text.
if (PHP_SAPI !== 'cli') {
	http_response_code(404);
	exit;
}

$root = realpath(__DIR__ .'/../..');
$errors = array();
$warnings = array();

function icuArguments(string $message, int &$i = 0, bool $nested = false): array {
	$args = array();
	$length = strlen($message);
	while ($i < $length) {
		$c = $message[$i];
		if ($c === '}' && $nested)
			return $args;
		if ($c !== '{') {
			$i++;
			continue;
		}
		$end = strcspn($message, ',}', $i+1) + $i+1;
		$args[] = trim(substr($message, $i+1, $end-$i-1));
		$i = $end;
		if (($message[$i] ?? '}') === '}') {
			$i++;
			continue;
		}
		$typeEnd = strcspn($message, ',}', $i+1) + $i+1;
		$type = trim(substr($message, $i+1, $typeEnd-$i-1));
		$i = $typeEnd;
		if (in_array($type, array('plural', 'select', 'selectordinal'))) {
			$i++;
			while ($i < $length && $message[$i] !== '}') {
				if ($message[$i] === '{') {
					$i++;
					$args = array_merge($args, icuArguments($message, $i, true));
				}
				$i++;
			}
			$i++;
		}
		else {
			$i = strpos($message, '}', $i) + 1;
		}
	}
	return $args;
}

$catalogs = array();
foreach (glob("$root/lang/*.json") as $file) {
	$locale = basename($file, '.json');
	$catalog = json_decode(file_get_contents($file), true);
	if (!is_array($catalog)) {
		$errors[] = "lang/$locale.json: invalid JSON (". json_last_error_msg() .")";
		continue;
	}
	foreach ($catalog as $key => $message) {
		if (!is_string($message)) {
			$errors[] = "lang/$locale.json: $key is not a string";
			unset($catalog[$key]);
			continue;
		}
		if (strpos($message, '{') !== false && !MessageFormatter::create($locale, str_replace("'", "''", $message)))
			$errors[] = "lang/$locale.json: $key is not a valid ICU message (". intl_get_error_message() .")";
	}
	$catalogs[$locale] = $catalog;
}
if (!isset($catalogs['en']))
	$errors[] = 'lang/en.json is missing';
$english = $catalogs['en'] ?? array();

foreach ($catalogs as $locale => $catalog) {
	if ($locale === 'en')
		continue;
	foreach ($catalog as $key => $message) {
		if (!isset($english[$key])) {
			$errors[] = "lang/$locale.json: $key does not exist in lang/en.json";
			continue;
		}
		$expected = array_unique(icuArguments($english[$key]));
		$actual = array_unique(icuArguments($message));
		sort($expected);
		sort($actual);
		if ($expected !== $actual)
			$errors[] = "lang/$locale.json: $key uses {". implode('}, {', $actual) ."} but English uses {". implode('}, {', $expected) .'}';
	}
	$missing = array_diff_key($english, $catalog);
	if ($missing)
		$warnings[] = "lang/$locale.json: ". count($missing) ." untranslated key(s), shown in English: ". implode(', ', array_keys($missing));
}

$usedKeys = array();
$files = new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$root/php")), '/\.php$/');
foreach ($files as $file) {
	$path = substr($file->getPathname(), strlen($root)+1);
	if ($path === 'php/includes/language.php' || $path === 'php/tools/lint-translations.php')
		continue;
	foreach (file($file->getPathname()) as $n => $line) {
		$where = "$path:". ($n+1);
		if (preg_match_all('/(?<![\w$>:\\\\])t\(\s*([\'"])([^\'"]*)\1/', $line, $matches)) {
			foreach ($matches[2] as $key) {
				$usedKeys[$key] = true;
				if (!isset($english[$key]))
					$errors[] = "$where: t('$key') is missing from lang/en.json";
			}
		}
		if (preg_match('/(?<![\w$>:\\\\])t\(\s*[^\'"\s]/', $line))
			$errors[] = "$where: t() needs a literal key so that this check can find it";
		if (preg_match('/(?<![\w$>:\\\\])(_|P_|F_|FN_)\(/', $line))
			$errors[] = "$where: gettext call, use t() and lang/*.json instead";
	}
}
foreach (array_diff_key($english, $usedKeys) as $key => $message)
	$errors[] = "lang/en.json: $key is not used anywhere";

foreach ($warnings as $warning)
	echo "warning: $warning\n";
foreach ($errors as $error)
	echo "error: $error\n";
echo count($english) .' keys, '. count($errors) .' error(s), '. count($warnings) ." warning(s)\n";
exit($errors ? 1 : 0);
