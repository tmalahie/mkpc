import { useMemo } from "react";
import { useCookies } from "react-cookie"
import { useAppContext } from "./useAppContext";

export function plural(text, nb) {
  return text.replace(/%n/g, nb).replace(/%s/g, (nb >= 2) ? "s" : "");
}

export function getHeaderLanguage(headerLang) {
  return headerLang?.split('-')[0].startsWith("fr") ? 0 : 1;
}

function useLanguage() {
  const [cookie, setCookie] = useCookies(["language"])
  const { lang } = useAppContext();

  return useMemo(() => {
    let res = +cookie.language;
    if (isNaN(res)) {
      res = getHeaderLanguage(lang);
      setCookie("language", res);
    }
    return res;
  }, [cookie.language, lang]);
}
export default useLanguage;