<?php
define('NICK_SEPARATORS', '\-_');
define('CHAT_SEPARATORS', '^a-zA-Z0-9\x80-\xff');
define('WHITELIST_PLACEHOLDER', "\x01");
define('MAX_PATTERN_LENGTH', 4000);

function fuzzyWordPattern($word, $separators) {
	$chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
	if ($chars === false)
		$chars = str_split($word);
	$parts = array();
	foreach ($chars as $char) {
		$quoted = preg_quote($char, '#');
		$parts[] = (strlen($char) > 1 ? '(?:'. $quoted .')':$quoted) .'+';
	}
	return implode('['. $separators .']*', $parts);
}
function blacklistRegex($pattern, $wholeWords) {
	return '#'. ($wholeWords ? '\b('. $pattern .')\b':'('. $pattern .')') .'#i';
}
function blacklistPatternGroups($words, $separators) {
	$groups = array();
	$group = array();
	$length = 0;
	foreach ($words as $word) {
		if ($word === '')
			continue;
		$pattern = fuzzyWordPattern($word, $separators);
		if ($length && $length + strlen($pattern) > MAX_PATTERN_LENGTH) {
			$groups[] = $group;
			$group = array();
			$length = 0;
		}
		$group[$word] = $pattern;
		$length += strlen($pattern) + 1;
	}
	if ($group)
		$groups[] = $group;
	return $groups;
}
function getWhitelistedWords() {
	static $whitelist = null;
	if ($whitelist === null) {
		$whitelist = array();
		$getWhitelist = mysql_query('SELECT word FROM `mkgoodwords`');
		while ($goodWord = mysql_fetch_array($getWhitelist))
			if ($goodWord['word'] !== '')
				$whitelist[] = $goodWord['word'];
	}
	return $whitelist;
}
function stripWhitelistedWords($text, $separators) {
	foreach (getWhitelistedWords() as $word) {
		$stripped = preg_replace('#'. fuzzyWordPattern($word, $separators) .'#i', WHITELIST_PLACEHOLDER, $text);
		if ($stripped !== null)
			$text = $stripped;
	}
	return $text;
}
function findBlacklistedWord($text, $groups, $wholeWords) {
	foreach ($groups as $group) {
		if (!preg_match(blacklistRegex(implode('|', $group), $wholeWords), $text))
			continue;
		foreach ($group as $word => $pattern)
			if (preg_match(blacklistRegex($pattern, $wholeWords), $text, $found))
				return array('word' => $word, 'match' => $found[1]);
	}
	return null;
}
?>
