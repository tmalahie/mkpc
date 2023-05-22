<?php
if (isset($_COOKIE['language'])) {
	$language = ($_COOKIE['language']==1) ? 1:0;
	$acceptedLanguage = $language == 0 ? "fr" : "en";
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
	$acceptedLanguage = findAcceptedLanguage(array('fr','en'),'en');
	$language = ($acceptedLanguage == 'fr') ? 0:1;
	setcookie('language', $language, 4294967295,'/');
}

// gettext setup
setlocale(LC_MESSAGES, $acceptedLanguage == "fr" ? "fr_FR.UTF-8" : "en_GB.UTF-8");
bindtextdomain("mkpc", "../../po");
textdomain("mkpc");

// creates a new array with same values,
// but keys are wrapped in braces.
// For example:
//   var_dump(wrap_array_keys_in_braces(["aaa" => 3]))
//   ["{aaa}" => 3]
// Can be used to implement python-style brace formating using strtr.
function wrap_array_keys_in_braces($array) {
	$array_with_braces = [];
	foreach ($array as $key => $value) {
		$array_with_braces["{{$key}}"] = $value;
	}
	return $array_with_braces;
}

// wrappers for gettext functions without PHP binding

// gettext with context ("particular")
// example: pgettext("context", "my message") yields
//   msgctx: "context"
//   msgid: "my message"
//   msgstr: "mon message"
function pgettext(string $context, string $msgid)
{
	$contextString = "{$context}\004{$msgid}";
	$translation = dcgettext('mkpc', $contextString, LC_MESSAGES);
	if ($translation == $contextString)
		return $msgid;
	else
		return $translation;
}
function P_()
{
	return call_user_func_array("pgettext", func_get_args());
}

// gettext with formatting
// example: F_("Hello {name}!", name: "wargor"]) yields
//   msgid: "Hello {name}!"
//   msgstr: "Salut {name} !"
// furthermore, strtr is automatically called to format the resulting string.
function F_(string $msgid, ...$replacePairs)
{
	return strtr(gettext($msgid), wrap_array_keys_in_braces($replacePairs));
}

// gettext with formatting and plural. Will used $replacePairs["count"] for plural.
// example: FN_("There is {count} message for {name}",
//              "There are {count} messages for {name}",
//              count: 2, name: "wargor")
// will yield
//    msgid "There is {count} message for {name}"
//    msgid_plural "There are {count} messages for {name}"
//    msgstr[0] "Il y a {count} message pour {name}"
//    msgstr[1] "Il y a {count} messages pour {name}"
// furthermore, the "count" parameter will be automatically used to detect the plural,
// and the string will be formated.
function FN_(string $singular, string $plural, ...$replacePairs)
{
	return strtr(
		ngettext($singular, $plural, $replacePairs["count"]),
		wrap_array_keys_in_braces($replacePairs),
	);
}
