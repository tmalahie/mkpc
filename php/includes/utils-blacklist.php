<?php
define('NICK_SEPARATORS', '\-_');
define('CHAT_SEPARATORS', '^a-zA-Z0-9\x80-\xff');
define('NICK_EXPRESSION_SYNTAX', '#^(?:(?:[a-z0-9_\-]|\[[a-z0-9_]+\])\+?)+$#');
define('CHAT_EXPRESSION_SYNTAX', '#^(?:(?:\[[^\[\]+]+\]|[^\[\]+])\+?)+$#u');
define('WHITELIST_PLACEHOLDER', "\x01");
define('MAX_PATTERN_LENGTH', 4000);

function isValidNickExpression($expression) {
	return (bool) preg_match(NICK_EXPRESSION_SYNTAX, $expression);
}
function isValidChatExpression($expression) {
	return (trim($expression) !== '') && preg_match(CHAT_EXPRESSION_SYNTAX, $expression);
}
function expressionCharPattern($char, $htmlEncoded) {
	$options = array($char);
	if ($htmlEncoded) {
		$encoded = htmlentities($char, ENT_QUOTES);
		if ($encoded !== $char)
			$options[] = $encoded;
	}
	$quoted = array();
	foreach ($options as $option)
		$quoted[] = preg_quote($option, '#');
	if ((count($quoted) == 1) && (strlen($char) == 1))
		return $quoted[0];
	return '(?:'. implode('|', $quoted) .')';
}
function expressionPattern($expression, $separators, $htmlEncoded=false) {
	preg_match_all('#(\[[^\[\]+]+\]|[^\[\]+])(\+?)#u', $expression, $tokens, PREG_SET_ORDER);
	$parts = array();
	foreach ($tokens as $token) {
		if (($token[1][0] === '[') && (strlen($token[1]) > 1)) {
			$chars = preg_split('//u', substr($token[1], 1, -1), -1, PREG_SPLIT_NO_EMPTY);
			$charPatterns = array();
			foreach ($chars as $char)
				$charPatterns[] = expressionCharPattern($char, $htmlEncoded);
			if (strlen(implode('', $charPatterns)) == count($charPatterns))
				$part = '['. implode('', $charPatterns) .']';
			else
				$part = '(?:'. implode('|', $charPatterns) .')';
		}
		else
			$part = expressionCharPattern($token[1], $htmlEncoded);
		$parts[] = $part . $token[2];
	}
	if ($separators === null)
		return implode('', $parts);
	if ($htmlEncoded)
		return implode('(?:['. $separators .']|&[a-zA-Z0-9#]+;)*', $parts);
	return implode('['. $separators .']*', $parts);
}
function nickExpressionPattern($expression, $ignoreSeparators) {
	if (!isValidNickExpression($expression))
		return preg_quote($expression, '#');
	return expressionPattern($expression, $ignoreSeparators ? NICK_SEPARATORS : null);
}
function chatExpressionPattern($expression, $ignoreSeparators, $htmlEncoded=false) {
	if (!isValidChatExpression($expression))
		return preg_quote($expression, '#');
	return expressionPattern($expression, $ignoreSeparators ? CHAT_SEPARATORS : null, $htmlEncoded);
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
function getChatPatternGroups() {
	static $groups = null;
	if ($groups === null) {
		$patterns = array('mute' => array(), 'block' => array(), 'none' => array());
		$getBlacklist = mysql_query('SELECT word,action,ignore_separators FROM `mkbadwords`');
		while ($badWord = mysql_fetch_array($getBlacklist))
			$patterns[$badWord['action']][$badWord['word']] = chatExpressionPattern($badWord['word'], $badWord['ignore_separators']);
		$groups = array();
		foreach ($patterns as $action => $actionPatterns)
			$groups[$action] = blacklistPatternGroups($actionPatterns);
	}
	return $groups;
}
function matchChatBlacklist($message) {
	$message = stripWhitelistedWords($message);
	foreach (getChatPatternGroups() as $action => $groups) {
		if ($found = findBlacklistedWord($message, $groups, true)) {
			$found['action'] = $action;
			return $found;
		}
	}
	return null;
}
?>
