import { useEffect, useMemo, useState } from 'react';
import placeholder from "../images/main/placeholder.png"

type SmoothParams<T> = {
  placeholder?: () => T;
  retryDelay?: number;
  retryDelayMultiplier?: number;
  retryCount?: number;
  requestOptions?: RequestInit;
}

let seed = 1;
function randomWithSeed() {
  let x = Math.sin(seed++) * 10000;
  return x - Math.floor(x);
}
function rand(min: number, max: number) {
  return Math.floor(randomWithSeed() * (max - min)) + min;
}

function placeholderText(minLength: number, maxLength: number = minLength) {
  return Array(rand(minLength, maxLength + 1)).fill('-').join('');
}
function getNumberLength(nb: number) {
  return nb.toString().length;
}
function placeholderNb(min: number, max: number = min) {
  return Math.pow(10, rand(getNumberLength(min), getNumberLength(max) + 1));
}
function placeholderImg() {
  return placeholder.src;
}
function placeholderArray<T>(length: number, elt: (i: number) => T): T[] {
  seed = 1;
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

function useSmoothFetch<T>(input: RequestInfo, { placeholder, retryDelay = 1000, retryDelayMultiplier = 2, retryCount = Infinity, requestOptions }: SmoothParams<T> = {}) {
  const placeholderVal = useMemo(() => placeholder ? placeholder() : undefined, []);
  const [state, setState] = useState({
    data: placeholderVal,
    loading: true,
    error: null
  });
  const [currentRetryCount, setCurrentRetryCount] = useState(0);
  const [currentRetryDelay, setCurrentRetryDelay] = useState(retryDelay);

  useEffect(() => {
    fetch(input, requestOptions)
      .then(res => res.json())
      .then(data => setState({ data, loading: false, error: null }))
      .catch(error => {
        if (currentRetryCount < retryCount) {
          setTimeout(() => {
            setCurrentRetryCount(currentRetryCount + 1);
            setCurrentRetryDelay(currentRetryDelay * retryDelayMultiplier);
          }, retryDelay);
        }
        else {
          setState({ data: placeholderVal, loading: false, error });
        }
      });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [input, currentRetryCount, currentRetryDelay]);

  return state;
}

export default useSmoothFetch;