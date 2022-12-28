<?php
include('language.php');
function languageCode() {
    global $language;
    return $language ? 'en' : 'fr';
}
function translate($key, $context=array()) {
    $lang = languageCode();
    $translationsForLang = json_decode(file_get_contents("translations/$lang.json"));
    if (!isset($translationsForLang->{$key}))
        return $key;
    $res = $translationsForLang->{$key};
    foreach ($context as $key=>$value)
        $res = str_replace('{'.$key.'}', $value, $res);
    return $res;
}