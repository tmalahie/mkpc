import { useEffect } from "react";
import useLanguage from "./useLanguage";
import useScript from "./useScript";

function useCreations() {
  const language = useLanguage();

  useEffect(() => {
    window["loadingMsg"] = language ? 'Loading' : 'Chargement';
  }, [language]);
  useScript("/scripts/posticons.js");
  useScript("/scripts/creations.js");
  function previewCreation(creation) {
    window["apercu"](creation.srcs);
  }

  return { previewCreation };
}

export default useCreations;