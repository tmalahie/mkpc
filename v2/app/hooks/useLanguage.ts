import { useMemo } from "react";
import { useCookies } from "react-cookie"

export function plural(text, nb) {
  return text.replace(/%n/g, nb).replace(/%s/g, (nb >= 2) ? "s" : "");
}

function useLanguage() {
  const [cookie] = useCookies(["language"])

  return useMemo(() => {
    let res = +cookie.language;
    if (isNaN(res)) {
      res = 0;//navigator.language.split('-')[0].startsWith("fr") ? 0 : 1;
    }
    return res;
  }, [cookie.language]);
}
export default useLanguage;