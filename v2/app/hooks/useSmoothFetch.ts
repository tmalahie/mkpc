import { useCallback, useEffect, useMemo, useState } from 'react';
import placeholder from "../images/main/placeholder.png"
export { postData, putData, deleteData } from "./useFetch";

type SmoothParams<T> = {
  placeholder?: () => T;
  retryDelay?: number;
  retryDelayMultiplier?: number;
  retryCount?: number;
  requestOptions?: RequestInit;
  reloadDeps?: any[],
  cacheKey?: string,
  disabled?: boolean,
  onSuccess?: (data: T) => void;
}

let seed = 1;
function randomWithSeed() {
  let x = Math.sin(seed++) * 10000;
  return x - Math.floor(x);
}
function rand(min: number, max: number) {
  return Math.floor(randUnif(min, max));
}
function randUnif(min: number, max: number) {
  return randomWithSeed() * (max - min) + min;
}

function addRandomSpaces(text: string) {
  let res = "";
  let wordLength: number;
  for (let c = 0; c < text.length; c += wordLength) {
    if (c)
      res += " ";
    wordLength = rand(10, 30);
    res += text.substring(c, c + wordLength - 1);
  }
  return res;
}
function placeholderText(minLength: number, maxLength: number = minLength) {
  return addRandomSpaces(Array(rand(minLength, maxLength + 1)).fill('-').join(''));
}
function placeholderNb(min: number, max: number = min) {
  return Math.floor(Math.pow(10, randUnif(Math.log10(min), Math.log10(max))));
}
function placeholderImg() {
  return placeholder.src;
}
function placeholderArray<T>(length: number, elt: (i: number) => T): T[] {
  let res = Array(length);
  for (let i = 0; i < length; i++) {
    res[i] = elt(i);
  }
  return res;
}
function randomDate() {
  const todayMidnight = new Date();
  todayMidnight.setHours(rand(0, 24), rand(0, 60), rand(0, 60));
  return todayMidnight;
}
function placeholderDate() {
  return randomDate().toISOString();
}
function placeholderTimestamp() {
  return randomDate().getTime();
}
export const Placeholder = {
  text: placeholderText,
  number: placeholderNb,
  array: placeholderArray,
  date: placeholderDate,
  timestamp: placeholderTimestamp,
  img: placeholderImg
}

type CacheHandler = Record<string, {
  state: any,
  setStates: Array<(newState: any) => void>
}>
const cacheHandler: CacheHandler = {}
function useSmoothFetch<T>(input: RequestInfo, { placeholder, retryDelay = 1000, retryDelayMultiplier = 2, retryCount = Infinity, requestOptions, reloadDeps = [], cacheKey, disabled, onSuccess }: SmoothParams<T> = {}) {
  const placeholderVal = useMemo(() => {
    if (!disabled && cacheKey && cacheHandler[cacheKey]) {
      const { state } = cacheHandler[cacheKey];
      if (!state.loading && !state.error)
        return state.data as T;
    }
    if (placeholder) {
      seed = 1;
      return placeholder();
    }
  }, []);
  const [state, setState] = useState({
    data: placeholderVal,
    loading: true,
    error: null
  });

  const setAllStates = useCallback((newState: typeof state) => {
    if (!cacheKey) {
      setState(newState);
      return;
    }
    cacheHandler[cacheKey].state = newState;
    for (const stateCallback of cacheHandler[cacheKey].setStates)
      stateCallback(newState);
  }, [setState, cacheKey]);

  function doFetch(currentRetryCount, currentRetryDelay) {
    setAllStates({
      data: placeholderVal,
      loading: true,
      error: null
    });
    fetch(input, requestOptions)
      .then(res => {
        if (res.ok) {
          if (res.status === 204)
            return null;
          return res.json();
        }
        else {
          console.error(res);
          throw new Error(res.statusText);
        }
      })
      .then(data => {
        setAllStates({ data, loading: false, error: null })
        if (cacheKey)
          cacheHandler[cacheKey].setStates.length = 0;
        onSuccess?.(data);
      })
      .catch(error => {
        if (currentRetryCount < retryCount) {
          setTimeout(() => {
            doFetch(currentRetryCount + 1, currentRetryDelay * retryDelayMultiplier);
          }, currentRetryDelay);
        }
        else {
          setAllStates({ data: placeholderVal, loading: false, error });
        }
      });
  }
  useEffect(() => {
    if (disabled)
      return;
    if (cacheKey) {
      if (cacheHandler[cacheKey]) {
        setState(cacheHandler[cacheKey].state);
        cacheHandler[cacheKey].setStates.push(setState);
        return;
      }
      cacheHandler[cacheKey] = {
        state,
        setStates: [setState]
      }
    }
    doFetch(0, retryDelay);
  }, reloadDeps);

  return state;
}

export default useSmoothFetch;