import '../styles/globals.css'
import type { AppProps } from 'next/app'
import { CookiesProvider } from "react-cookie"

function MyApp({ Component, pageProps }: AppProps) {
  return <CookiesProvider>
    <Component {...pageProps} />
  </CookiesProvider>
}

export default MyApp
