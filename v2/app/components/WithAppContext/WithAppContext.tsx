import { NextPage } from "next";
import { AppContext } from "../../hooks/useAppContext";

function getNavigatorLanguage() {
  if (typeof navigator !== "undefined")
    return navigator.language;
  return "en";
}

function WithAppContext(Component: NextPage) {
  const { getInitialProps } = Component;

  Component.getInitialProps = async (ctx) => {
    const { req } = ctx;
    const appContext: AppContext = {
      cookies: req?.headers.cookie,
      lang: req?.headers["accept-language"] ?? getNavigatorLanguage()
    };
    return { appContext, ...getInitialProps?.(ctx) };
  }

  return Component;
}

export default WithAppContext;