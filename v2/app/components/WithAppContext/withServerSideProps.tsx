import { NextPage } from "next";
import { AppContext } from "../../hooks/useAppContext";
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import nextI18NextConfig from '../../next-i18next.config.js';
import { getHeaderLanguage } from "../../hooks/useLanguage";
import { Cookies } from "react-cookie"

type AppContextOptions = { 
  localesNs?: string[],
  getStaticProps?: (ctx) => any
};

function withServerSideProps(options: AppContextOptions = {}) {
  return async (ctx) => {
    const { req } = ctx;
    const appContext: AppContext = {
      cookies: req?.headers.cookie ?? '{}',
      lang: req?.headers["accept-language"]
    };
    const cookieVals = new Cookies(appContext.cookies);
    const language = +cookieVals.get("language") ?? getHeaderLanguage(appContext.lang);

    const props = {
      ...options.getStaticProps?.(ctx),
      ...(await serverSideTranslations(language ? "en" : "fr", options.localesNs ?? ["common"], nextI18NextConfig)),
      appContext
    };
    return { props };
  }
}

export default withServerSideProps;