// src/context/state.js
import { createContext, ReactNode, useContext } from 'react';

const appContext = createContext<AppContext>(null);

export type AppContext = {
  cookies?: string;
}
const AppCtx = createContext<AppContext>(null);
type Props = {
  context: AppContext;
  children: ReactNode;
}
export function AppWrapper({ context, children }: Props) {
  return (
    <AppCtx.Provider value={context}>
      {children}
    </AppCtx.Provider>
  );
}

export function useAppContext() {
  return useContext(AppCtx);
}