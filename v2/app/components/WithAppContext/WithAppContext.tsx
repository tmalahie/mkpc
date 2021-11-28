import { NextPage } from "next";
import { AppContext } from "../../hooks/useAppContext";

function WithAppContext(Component: NextPage) {
  const { getInitialProps } = Component;

  Component.getInitialProps = async (ctx) => {
    const { req } = ctx;
    const appContext: AppContext = {
      cookies: req?.headers.cookie,
      lang: req?.headers["accept-language"] ?? navigator?.language
    };
    return { appContext, ...getInitialProps?.(ctx) };
  }

  return Component;
}

export default WithAppContext;