import type { AppProps } from 'next/app'
import { Cookies, CookiesProvider } from "react-cookie"
import { AppWrapper, AppContext } from '../hooks/useAppContext'
import {
  ApolloClient,
  InMemoryCache,
  ApolloProvider,
} from "@apollo/client";

function MyApp({ Component, pageProps }: AppProps) {
  const context = pageProps.appContext as AppContext;
  const client = new ApolloClient({
    uri: '/api/graphql',
    cache: new InMemoryCache()
  });

  return <ApolloProvider client={client}>
    <AppWrapper context={context}>
      <CookiesProvider cookies={new Cookies(context?.cookies)}>
        <Component {...pageProps} />
      </CookiesProvider>
    </AppWrapper>
  </ApolloProvider>
}

export default MyApp
