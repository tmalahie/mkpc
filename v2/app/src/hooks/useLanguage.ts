function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(';').shift();
}
function findLanguage() {
  var res = +getCookie('language');
  if (isNaN(res)) {
    res = navigator.language.split('-')[0].startsWith("fr") ? 0 : 1;
  }
  return res;
}
var language = findLanguage();
export function useLanguage() {
  return language;
}