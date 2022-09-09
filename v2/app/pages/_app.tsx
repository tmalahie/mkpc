import type { AppProps } from 'next/app'
import { appWithTranslation } from 'next-i18next'; 
import { Cookies, CookiesProvider } from "react-cookie"
import { AppWrapper, AppContext } from '../hooks/useAppContext'

function MyApp({ Component, pageProps }: AppProps) {
  const context = pageProps.appContext as AppContext;

  return <AppWrapper context={context}>
    <CookiesProvider cookies={new Cookies(context?.cookies)}>
      <Component {...pageProps} />
    </CookiesProvider>
  </AppWrapper>
}

export default appWithTranslation(MyApp)
