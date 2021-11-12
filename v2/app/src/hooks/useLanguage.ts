export function plural(text, nb) {
  return text.replace(/%n/g, nb).replace(/%s/g, (nb>=2) ? "s":"");
}

function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(';').shift();
}
function findLanguage() {
  let res = +getCookie('language');
  if (isNaN(res)) {
    res = navigator.language.split('-')[0].startsWith("fr") ? 0 : 1;
  }
  return res;
}
export const language = findLanguage();
function useLanguage() {
  return language;
}
export default useLanguage;