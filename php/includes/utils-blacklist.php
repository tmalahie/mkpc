<?php
define('NICK_SEPARATORS', '\-_');
define('CHAT_SEPARATORS', '^a-zA-Z0-9\x80-\xff');
define('NICK_EXPRESSION_SYNTAX', '#^(?:(?:[a-z0-9_\-]|\[[a-z0-9_]+\])\+?)+$#');
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
function isValidNickExpression($expression) {
	return (bool) preg_match(NICK_EXPRESSION_SYNTAX, $expression);
}
function nickExpressionPattern($expression, $ignoreSeparators) {
	if (!isValidNickExpression($expression))
		return preg_quote($expression, '#');
	preg_match_all('#(\[[a-z0-9_]+\]|[a-z0-9_\-])(\+?)#', $expression, $tokens, PREG_SET_ORDER);
	$parts = array();
	foreach ($tokens as $token)
		$parts[] = ($token[1][0] === '[' ? $token[1] : preg_quote($token[1], '#')) . $token[2];
	return implode($ignoreSeparators ? '['. NICK_SEPARATORS .']*' : '', $parts);
}
function chatWordPatterns($words) {
	$patterns = array();
	foreach ($words as $word)
		$patterns[$word] = fuzzyWordPattern($word, CHAT_SEPARATORS);
	return $patterns;
}
function blacklistRegex($pattern, $wholeWords) {
	return '#'. ($wholeWords ? '\b('. $pattern .')\b':'('. $pattern .')') .'#i';
}
function blacklistPatternGroups($patterns) {
	$groups = array();
	$group = array();
	$length = 0;
	foreach ($patterns as $word => $pattern) {
		if ($pattern === '')
			continue;
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
function stripWhitelistedWords($text) {
	foreach (getWhitelistedWords() as $word)
		$text = str_ireplace($word, WHITELIST_PLACEHOLDER, $text);
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
