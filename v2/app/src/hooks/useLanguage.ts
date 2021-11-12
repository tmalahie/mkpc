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
let language = findLanguage();
function useLanguage() {
  return language;
}
export default useLanguage;